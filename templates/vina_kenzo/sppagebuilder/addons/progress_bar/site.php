<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2015 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('resticted aceess');

AddonParser::addAddon('sp_progress_bar','sp_progress_bar_addon');

function sp_progress_bar_addon($atts, $content){

	extract(spAddonAtts(array(
		"type" 		=> '',
		"progress" 	=> '',
		"text" 		=> '',
		"stripped"	=>'',
		"active"	=>'',
		"class"		=>''
		), $atts));
		
	$output ='<div class="progress-bar-addon">';	
	
	$output .= '<span class="progress-percent"><em>'.(int) $progress.'</em>%</span>';	
	$output .= '<div class="sppb-progress ' . $class . '">';	
	$output .= '<div class="sppb-progress-bar ' . $type . ' ' . $stripped . ' ' . $active . '" role="progressbar" aria-valuenow="' . (int) $progress . '" aria-valuemin="0" aria-valuemax="100" data-width="' . (int) $progress . '%" style="width:'. (int) $progress .'%;">';
	if($text) $output .= '<span class="title-progress">'.$text.'</span>';
	$output .= '</div>';
	$output .= '</div>';
	$output .= '</div>';
	return $output;
	
}