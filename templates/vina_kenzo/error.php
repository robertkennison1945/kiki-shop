<?php
/**
 * @package Helix3 Framework
 * Template Name - Shaper Helix - iii
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2015 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('resticted aceess');

$doc = JFactory::getDocument();
$params = JFactory::getApplication()->getTemplate('true')->params;

//Load Helix
$helix3_path = JPATH_PLUGINS.'/system/helix3/core/helix3.php';

if (file_exists($helix3_path)) {
    require_once($helix3_path);
    $this->helix3 = Helix3::getInstance();
} else {
    die('Please install and activate helix plugin');
}
//Favicon
if($favicon = $params->get('favicon')) {
    $doc->addFavicon( JURI::base(true) . '/' .  $favicon);
} else {
    $doc->addFavicon( $this->baseurl . '/templates/' . $this->template . '/images/favicon.ico' );
}

//Stylesheets
$doc->addStylesheet( $this->baseurl . '/templates/' . $this->template . '/css/bootstrap.min.css' );
$doc->addStylesheet( $this->baseurl . '/templates/' . $this->template . '/css/font-awesome.min.css' );
$doc->addStylesheet( $this->baseurl . '/templates/' . $this->template . '/css/template.css' );

$doc->setTitle($this->error->getCode() . ' - '.$this->title);
require_once(JPATH_LIBRARIES.'/joomla/document/html/renderer/head.php');
$header_renderer = new JDocumentRendererHead($doc);
$header_contents = $header_renderer->render(null);
	
?>
<!DOCTYPE html>
<html class="error-page" xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
	<head>
	  	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	    <meta name="viewport" content="width=device-width, initial-scale=1">
		<?php echo $header_contents; ?>
		<?php

		
		$this->helix3->addCSS('bootstrap.min.css, font-awesome.min.css')
        ->lessInit()->setLessVariables(array(
            'preset'=>$this->helix3->Preset(),
            'bg_color'=> $this->helix3->PresetParam('_bg'),
            'text_color'=> $this->helix3->PresetParam('_text'),
            'major_color'=> $this->helix3->PresetParam('_major'),
			'major2_color'=> $this->helix3->PresetParam('_major2')
            ))
        ->addLess('master', 'template')
        ->addLess('presets',  'presets/'.$this->helix3->Preset())
    	->addJS('jquery.countdown.min.js');
		?>
	</head>
	<body>
		<div class="error-page-inner">
			<div>
				<div class="container">
					
					
					<?php if($this->error->getCode() == 404) : ?>
						<img src="<?php echo $this->baseurl . '/templates/' . $this->template . '/images/general/404.png'?>" alt ="404">
					<?php else : ?>
						<p><i class="fa fa-exclamation-triangle"></i></p>
						<h1 class="error-code"><?php echo $this->error->getCode(); ?></h1>
					<?php endif;?>
					
					<p class="error-message"><?php echo $this->error->getMessage(); ?></p>
					<a class="btn btn-primary btn-lg" href="<?php echo $this->baseurl; ?>/" title="<?php echo JText::_('HOME'); ?>"><i class="fa fa-home"></i> <?php echo JText::_('HELIX_GO_BACK'); ?></a>
					<?php echo $doc->getBuffer('modules', '404', array('style' => 'sp_xhtml')); ?>
				</div>
			</div>
		</div>
	</body>
</html>