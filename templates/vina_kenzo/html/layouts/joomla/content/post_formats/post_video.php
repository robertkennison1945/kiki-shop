<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

if ( $displayData['params']->get('video') ) {
	
	$video = parse_url($displayData['params']->get('video'));

	switch($video['host']) {
		case 'youtu.be':
		$video_id 	= trim($video['path'],'/');
		$video_src 	= '//www.youtube.com/embed/' . $video_id;
		break;

		case 'www.youtube.com':
		case 'youtube.com':
		parse_str($video['query'], $query);
		$video_id 	= $query['v'];
		$video_src 	= '//www.youtube.com/embed/' . $video_id;
		break;

		case 'vimeo.com':
		case 'www.vimeo.com':
		$video_id 	= trim($video['path'],'/');
		$video_src 	= "//player.vimeo.com/video/" . $video_id;
	}

	if($video_src) {
		?>
		<div class="entry-post-format">
			<div class="entry-video embed-responsive embed-responsive-16by9">
				<iframe class="embed-responsive-item" src="<?php echo $video_src; ?>" style="border: 0px;" allowFullScreen></iframe>
			</div>
		</div>
		<?php
	}
	
}
