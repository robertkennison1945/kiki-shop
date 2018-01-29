<?php
/*
 * --------------------------------------------------------------------------------
   System - VinaGecko Promotions
 * --------------------------------------------------------------------------------
 * @package		Joomla! 3.6x
 * @author    	VinaGecko.com http://vinagecko.com
 * @copyright	Copyright(c) 2016 VinaGecko.com. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link		http://vinagecko.com
 * --------------------------------------------------------------------------------
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

class plgSystemVinaGecko extends JPlugin
{
	var $_body = NULL;
	var $link  = NULL;
	
	/**
	 * Constructor
	 *
	 * @access      protected
	 * @param       object  $subject The object to observe
	 * @param       array   $config  An array that holds the plugin configuration
	 * @since       1.5
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}
	
	public function getPromotionsHTML()
	{
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		
		$url  	= $this->params->def('promotions', 'http://vinagecko.net/promotions/');
		$xml	= $url . 'readme.xml';
		$folder = JPATH_BASE . '/cache/vinagecko/';
		$file 	= $folder . date('Ymd') .'/readme.xml';
		
		// check if cache file is exists or not
		if(!JFile::exists($file)) {
			JFolder::delete($folder);
			JFolder::create(dirname($file));
			$buffer = JFile::read($xml);
			JFile::write($file, $buffer);
			
			$content = simplexml_load_file($xml);
			foreach($content as $key => $value) {
				$temp 	= $url . $value->code;
				$buffer = JFile::read($temp);
				JFile::write($folder . date('Ymd') . '/data/' . $value->code, $buffer);
			}
		}
		
		// load HTML code
		$path  = $folder . date('Ymd') . '/data';
		$files = JFolder::files($path);
		$html  = array();
		
		if(!count($files)) return;
		
		foreach($files as $file) {
			$file    = $path . '/' . $file;
			$content = JFile::read($file);
			if(is_file($file) && strlen($content)) {
				$html[] = $content;
			}
		}
		
		$key   = array_rand($html);
		$value = $html[$key];
		
		return $value;
	}
	
	public function onAfterRender()
	{
        $app   = JFactory::getApplication();
		$html  = plgSystemVinaGecko::getPromotionsHTML();
		$color = $this->params->def('mainColor', '#62A9DD');
		$css   = $this->params->def('customStyle', '');
		
        if($app->isAdmin() || !strlen($html))
            return;
		
		$this->_body = JResponse::getBody();
		
		$cssFile = '<link rel="stylesheet" href="'. JURI::Base() .'plugins/system/vinagecko/assets/css/styles.css" type="text/css" />';
		$inlineCSS = '<style type="text/css">
		#vinagecko-promotions .vinagecko-buy-now a,
		#vinagecko-promotions .vinagecko-buy-now a:hover,
		#vinagecko-promotions .vinagecko-buy-now a :active,
		#vinagecko-promotions .vinagecko-button {
			background-color: '.$color.';
		}
		'. $css .'
		</style>';
		
		$this->_body = str_replace('</head>', $cssFile . $inlineCSS . '</head>', $this->_body);
		$this->_body = str_replace('</body>', $html . '</body>', $this->_body);
		
		JResponse::setBody($this->_body);
	}
}