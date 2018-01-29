<?php
/*
# ------------------------------------------------------------------------
# Vina Articles Carousel for Joomla 3
# ------------------------------------------------------------------------
# Copyright(C) 2014 www.VinaGecko.com. All Rights Reserved.
# @license http://www.gnu.org/licenseses/gpl-3.0.html GNU/GPL
# Author: VinaGecko.com
# Websites: http://vinagecko.com
# Forum: http://vinagecko.com/forum/
# ------------------------------------------------------------------------
*/

// no direct access
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.modal');
$document = JFactory::getDocument();
$doc = JFactory::getDocument();
$doc->addScript('modules/mod_vina_carousel_content/assets/js/owl.carousel.js', 'text/javascript');
$doc->addStyleSheet('modules/mod_vina_carousel_content/assets/css/owl.carousel.css');
$doc->addStyleSheet('modules/mod_vina_carousel_content/assets/css/owl.theme.css');

// Add styles
$stylebgImage = ($bgImage != '') ? "background: url({$bgImage}) repeat scroll 0 0;" : '';
$stylebgImage .= ($isBgColor) ? "background-color: {$bgColor};" : '';
$styleisItemBgColor = ($isItemBgColor) ? "background-color: {$itemBgColor};" : "";

$style = '#vina-carousel-content'.$module->id .'{'
		. 'overflow: hidden;'
        . 'width:'.$moduleWidth.';'
        . 'height:'.$moduleHeight.';'
        . 'margin:'.$moduleMargin.';'
        . 'padding:'.$modulePadding.';'
		. $stylebgImage
        . '}'
		. '#vina-carousel-content'.$module->id .' .item {'
		. $styleisItemBgColor
		. 'margin:'. $itemMargin.';'
		. 'padding:'.$itemPadding.';'
		. 'color:'.$itemTextColor.';'
		. '}'
		. '#vina-carousel-content'.$module->id .' .item a{'
		. 'color:'.$itemLinkColor.';'
		. '}'; 
$document->addStyleDeclaration($style);
?>

<div id="vina-carousel-content<?php echo $module->id; ?>" class="vina-carousel-content owl-carousel <?php echo $classSuffix; ?>">
	<!-- Items Block -->
	<?php 
		$thumb = JURI::base() . 'modules/mod_vina_carousel_content/libs/timthumb.php?a=c&amp;q=99&amp;z=0&amp;w='.$imagegWidth.'&amp;h='.$imagegHeight;
		foreach ($list as $item) :
			$title 	= $item->title;
			$link   = $item->link;			
			$images = json_decode($item->images);
			$image 	= $images->image_fulltext;
			$image  = (empty($image)) ? $images->image_intro : $image;
			$image 	= (strpos($image, 'http://') === FALSE) ? JURI::base() . $image : $image;
			$image 	= ($resizeImage) ? $thumb . '&amp;src=' . $image : $image;
			$category 	= $item->displayCategoryTitle;
			$hits  		= $item->displayHits;
			$introtext 	= $item->displayIntrotext;
			$created   	= $item->displayDate;
			
			// Post Format
			$post_attribs = new JRegistry(json_decode( $item->attribs ));
			$post_format = $post_attribs->get('post_format', 'standard');
			
	?>
	<div class="item">
		<div class="item-inner">
			<div class="blog-inner">
				<!-- Image Block -->
				<?php if($post_format == 'standard') { ?>
					<?php if($showImage && isset($images)) { ?>				
						<div class="image-block">
							<a href="<?php echo $link; ?>" title="<?php echo $title; ?>">							
								<img src="<?php echo $image; ?>" alt="<?php echo $title; ?>" title="<?php echo $title; ?>"/>							
							</a>
						</div>
					<?php } ?>
				<?php } else { ?>
					<?php echo JLayoutHelper::render('joomla.content.post_formats.post_' . $post_format, array('params' => $post_attribs, 'item' => $item)); ?>
				<?php } ?>
				
				
				<!-- Text Block -->
				<?php if($showTitle || $introText || $showCategory || $showCreatedDate || $showHits || $readmore) : ?>
				<div class="text-block">					
					
					<!-- Title Block -->
					<?php if($showTitle) :?>
					<h3 class="title">
						<a href="<?php echo $link; ?>" title="<?php echo $title; ?>"><?php echo $title; ?></a>
					</h3>
					<?php endif; ?>
					
					<!-- Intro text Block -->
					<?php if($introText) : ?>
					<div class="introtext"><?php echo $introtext; ?></div>
					<?php endif; ?>
					
					<?php if($readmore || $showCreatedDate) : ?>
						<div class="post-bottom">
							<!-- Readmore Block -->
							<?php if($readmore) : ?>
							<div class="readmore">
								<a class="buttonlight morebutton" href="<?php echo $link; ?>" title="<?php echo $title; ?>">
									<?php echo JText::_('VINA_CAROUSEL_READ_MORE'); ?>
								</a>
							</div>
							<?php endif; ?>
							
							<!-- Date -->				
							<?php if($showCreatedDate) : ?>
							<span class="date-time"><?php //echo JTEXT::_('VINA_PUBLISHED'); ?> <?php echo JHTML::_('date', $created, 'F d, Y');?></span>
							<?php endif; ?>
						</div>
					<?php endif; ?>
					
					<!-- Info Block -->
					<?php if($showCategory || $showHits) : ?>
					<div class="info">										
						<?php if($showCategory) : ?>
						<span><i class="fa fa-folder-o"></i> <?php echo JTEXT::_('VINA_CATEGORY'); ?>: <?php echo $category; ?></span>
						<?php endif; ?>
						
						<?php if($showHits) : ?>
						<span><i class="fa fa-thumbs-o-up"></i> <?php echo JTEXT::_('VINA_HITS'); ?>: <?php echo $hits; ?></span>
						<?php endif; ?>
					</div>
					<?php endif; ?>
				</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php endforeach; ?>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
	$("#vina-carousel-content<?php echo $module->id; ?>").owlCarousel({
		items : 			<?php echo $itemsVisible; ?>,
        itemsDesktop : 		<?php echo $itemsDesktop; ?>,
        itemsDesktopSmall : <?php echo $itemsDesktopSmall; ?>,
        itemsTablet : 		<?php echo $itemsTablet; ?>,
        itemsTabletSmall : 	<?php echo $itemsTabletSmall; ?>,
        itemsMobile : 		<?php echo $itemsMobile; ?>,
        singleItem : 		<?php echo ($singleItem) ? 'true' : 'false'; ?>,
        itemsScaleUp : 		<?php echo ($itemsScaleUp) ? 'true' : 'false'; ?>,

        slideSpeed : 		<?php echo $slideSpeed; ?>,
        paginationSpeed : 	<?php echo $paginationSpeed; ?>,
        rewindSpeed : 		<?php echo $rewindSpeed; ?>,

        autoPlay : 		<?php echo ($autoPlay) ? 'true' : 'false'; ?>,
        stopOnHover : 	<?php echo ($stopOnHover) ? 'true' : 'false'; ?>,

        navigation : 	<?php echo ($navigation) ? 'true' : 'false'; ?>,
        rewindNav : 	<?php echo ($rewindNav) ? 'true' : 'false'; ?>,
        scrollPerPage : <?php echo ($scrollPerPage) ? 'true' : 'false'; ?>,

        pagination : 		<?php echo ($pagination) ? 'true' : 'false'; ?>,
        paginationNumbers : <?php echo ($paginationNumbers) ? 'true' : 'false'; ?>,

        responsive : 	<?php echo ($responsive) ? 'true' : 'false'; ?>,
        autoHeight : 	<?php echo ($autoHeight) ? 'true' : 'false'; ?>,
        mouseDrag : 	<?php echo ($mouseDrag) ? 'true' : 'false'; ?>,
        touchDrag : 	<?php echo ($touchDrag) ? 'true' : 'false'; ?>,
	});
}); 
</script>