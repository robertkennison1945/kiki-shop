<?php
/*
# ------------------------------------------------------------------------
# Vina Product Carousel for VirtueMart for Joomla 3
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
/*$doc = JFactory::getDocument();
$doc->addScript('modules/' . $module->module . '/assets/js/owl.carousel.js', 'text/javascript');
$doc->addStyleSheet('modules/' . $module->module . '/assets/css/owl.carousel.css');
$doc->addStyleSheet('modules/' . $module->module . '/assets/css/owl.theme.css');
$doc->addStyleSheet('modules/' . $module->module . '/assets/css/custom.css');*/

// Timthumb Class Path
$timthumb = 'modules/'.$module->module.'/libs/timthumb.php?a=c&amp;q=99&amp;z=0&amp;w='.$imageWidth.'&amp;h='.$imageHeight;
$timthumb = JURI::base() . $timthumb;

// Rating
$ratingModel = VmModel::getModel('ratings'); 
$ItemidStr = '';
$Itemid = shopFunctionsF::getLastVisitedItemId();
if(!empty($Itemid)){
	$ItemidStr = '&amp;Itemid='.$Itemid;
}

// Timer count --------------------------------------------------------------------------
$app 	  = JFactory::getApplication();
$template = $app->getTemplate();
$doc->addScript(JURI::base() . 'templates/' . $template . '/js/jquery.countdown.min.js');

//Include Helix3 plugin
$helix3_path = JPATH_PLUGINS.'/system/helix3/core/helix3.php';

if (file_exists($helix3_path)) {
    require_once($helix3_path);
    $helix3 = helix3::getInstance();
} else {
    die('Please install and activate helix plugin');
}

$vm_product_labels 		=  $helix3->getParam('vm_product_labels', 1);
$newLabel_date 			=  $helix3->getParam('vm_product_label_newdate', 1);
$newLabel_limit 		=  $helix3->getParam('vm_product_label_newlimit', 1);
$vm_product_quickview 	=  $helix3->getParam('vm_product_quickview', 1);
$vm_product_desc_limit 	=  $helix3->getParam('vm_product_desc_limit', 60);

// Get New Products
$db    = JFactory::getDBO();
$query = "SELECT virtuemart_product_id FROM #__virtuemart_products WHERE DATE(product_available_date) >= DATE_SUB(CURDATE(), INTERVAL ". $newLabel_date." DAY) ORDER BY product_available_date DESC LIMIT 0, " . $newLabel_limit;
$db->setQuery($query);
$newIds = $db->loadColumn();

// Add styles
$stylebgImage = ($bgImage != '') ? "background: url({$bgImage}) repeat scroll 0 0;" : '';
$stylebgImage .= ($isBgColor) ? "background-color: {$bgColor};" : '';
$styleisItemBgColor = ($isItemBgColor) ? "background-color: {$itemBgColor};" : "";

$style = '#vina-carousel-virtuemart'.$module->id .'{'
		. 'overflow: hidden;'
        . 'width:'.$moduleWidth.';'
        . 'height:'.$moduleHeight.';'
        . 'margin:'.$moduleMargin.';'
        . 'padding:'.$modulePadding.';'
		. $stylebgImage
        . '}'
		. '#vina-carousel-virtuemart'.$module->id .' .item {'
		. $styleisItemBgColor
		. 'margin:'. $itemMargin.';'
		. 'padding:'.$itemPadding.';'
		. 'color:'.$itemTextColor.';'
		. '}'
		. '#vina-carousel-virtuemart'.$module->id .' .item a{'
		. 'color:'.$itemLinkColor.';'
		. '}'; 
$document->addStyleDeclaration($style);
?>

<!-- HTML Block -->
<div id="vina-carousel-virtuemart<?php echo $module->id; ?>" class="vina-carousel-virtuemart owl-carousel has-counter">
	<?php
		$totalRow  = $itemInCol;
		$totalLoop = ceil(count($products)/$totalRow);
		$keyLoop   = 0;
		for($i = 0; $i < $totalLoop; $i ++) :
	?>
	
	<div class="item product">
		<div class="product-inner">
			<?php 
			for($m = 0; $m < $totalRow; $m ++) : 
				$product = $products[$keyLoop];
				$keyLoop = $keyLoop + 1;
				if(!empty($product)) :
			?>
			<?php
				$image  = $product->images[0];
				$pImage = (!empty($image)) ? JURI::base() . $image->file_url : '';
				$pImage = (!empty($pImage) && $resizeImage) ? $timthumb . '&amp;src=' . $pImage : $pImage;				

				if(!is_null($product->images[1])) {
					$image_second = $product->images[1];
					$pImage_second = (!empty($image_second)) ? JURI::base() . $image_second->file_url : $pImage;
					$pImage_second = (!empty($pImage_second) && $resizeImage) ? $timthumb . '&amp;src=' . $pImage_second : $pImage_second;	
				}
				
				$pLink  = JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $product->virtuemart_product_id . '&virtuemart_category_id=' . $product->virtuemart_category_id);
				$pName  = $product->product_name;
				$rating = shopFunctionsF::renderVmSubLayout('rating', array('showRating' => $productRating, 'product' => $product));
				$sDesc  = $product->product_s_desc;
				$pDesc  = (!empty($sDesc)) ? shopFunctionsF::limitStringByWord($sDesc, $vm_product_desc_limit, ' ...') : '';
				$detail = JHTML::link($pLink, '<i class="fa fa-search"></i><span>'.vmText::_('COM_VIRTUEMART_PRODUCT_DETAILS').'</span>', array('title' => vmText::_ ( 'COM_VIRTUEMART_PRODUCT_DETAILS' ), 'class' => 'product-details jutooltip'));
				$stock  = $productModel->getStockIndicator($product);
				$sLevel = $stock->stock_level;
				$sTip   = $stock->stock_tip;
				$handle = shopFunctionsF::renderVmSubLayout('stockhandle', array('product' => $product));
				$pPrice = shopFunctionsF::renderVmSubLayout('prices', array('product' => $product, 'currency' => $currency));
				$sPrice = $currency->createPriceDiv('salesPrice', '', $product->prices, FALSE, FALSE, 1.0, TRUE);
				$dPrice = $currency->createPriceDiv('salesPriceWithDiscount', '', $product->prices, FALSE, FALSE, 1.0, TRUE);
				
				// Timer
				$timer = $product->allPrices[0]["product_price_publish_down"];
				$new_timers = new DateTime($timer);
				$new_timer = $new_timers->format('Y/m/d');
				
				// Show Label Sale Or New		
				$isSaleLabel = (!empty($product->prices['discountAmount'])) ? 1 : 0;
				
				$pid = $product->virtuemart_product_id;
				$isNewLabel = in_array($pid, $newIds);
			?>
			<div class="item-i">
				<!-- Image Block -->
				<?php if($productImage && !empty($pImage)) : ?>
				<div class="image-block vm-product-media-container">
					<a href="<?php echo $pLink; ?>" title="<?php echo $pName; ?>">
						<!-- Check Product Label -->
						<?php if($vm_product_labels) {?>
						<div class="product-status">																						
							<?php if($isNewLabel && $isSaleLabel == 0) : ?>
								<div class="label-pro status-new"><span><?php echo JTEXT::_('VM_LANG_NEW'); ?></span></div>
							<?php endif; ?>
							<?php if($isNewLabel && $isSaleLabel != 0) : ?>
								<div class="label-pro status-new-sale"><span><?php echo JTEXT::_('VM_LANG_NEW'); ?></span></div>
							<?php endif; ?>
							<?php if($isSaleLabel != 0) : ?>
								<div class="label-pro status-sale"><span><?php echo JTEXT::_('VM_LANG_SALE'); ?></span></div>
							<?php endif; ?>
						</div>
						<?php } ?>
						<?php if(!empty($product->images[1])) :?>
							<div class="pro-image first-image">
								<img class="browseProductImage" src="<?php echo $pImage; ?>" alt="<?php echo $pName; ?>" title="<?php echo $pName; ?>" />
							</div>
							<div class="pro-image second-image">
								<img class="browseProductImage" src="<?php echo $pImage_second; ?>" alt="<?php echo $pName; ?>" title="<?php echo $pName; ?>" />				
							</div>			
						<?php else: ?>
							<div class="pro-image">
								<img class="browseProductImage" src="<?php echo $pImage; ?>" alt="<?php echo $pName; ?>" title="<?php echo $pName; ?>" />
							</div>
						<?php endif;?>						
					</a>
					
					<!-- View Details Button1 -->
					<?php if($viewDetails) : ?>
						<div class="vm-btn-quickview">
							<?php echo JHTML::link($pLink, '<span>'.vmText::_('COM_VIRTUEMART_PRODUCT_DETAILS').'</span>', array('title' => vmText::_ ( 'COM_VIRTUEMART_PRODUCT_DETAILS' ), 'class' => 'product-details')); ?>
						</div>
					<?php endif; ?>
				</div>
				<?php endif; ?>
				
				<!-- Text Block -->
				<div class="text-block">
					<!-- Timer -->
					<?php if( strtotime($new_timer) > strtotime('now') ) : ?>						
						<div class="timer-grid" data-countdown="<?php echo $new_timer;?>"></div>
					<?php endif;?>
					
					<!-- Product Name -->
					<?php if($productName) : ?>
						<h3 class="product-title"><a href="<?php echo $pLink; ?>" title="<?php echo $pName; ?>"><?php echo $pName; ?></a></h3>
					<?php endif; ?>
					
					<!-- Product Rating -->
					<?php if($productRating) : ?>							
						<?php if ($ratingModel) { ?>
							<div class="vm-product-rating-container">
								<?php
								$maxrating = VmConfig::get('vm_maximum_rating_scale',5);
								$rating = $ratingModel->getRatingByProduct($product->virtuemart_product_id);
								$reviews = $ratingModel->getReviewsByProduct($product->virtuemart_product_id);
								if(empty($rating->rating)) { ?>						
									<div class="ratingbox dummy" title="<?php echo vmText::_('COM_VIRTUEMART_UNRATED'); ?>" >
									</div>
								<?php } else {						
									$ratingwidth = $rating->rating * 16; ?>
									<div title=" <?php echo (vmText::_("COM_VIRTUEMART_RATING_TITLE") . round($rating->rating) . '/' . $maxrating) ?>" class="ratingbox" >
									  <div class="stars-orange" style="width:<?php echo $ratingwidth.'px'; ?>"></div>
									</div>
								<?php } ?> 
								<?php if(!empty($reviews)) {					
									$count_review = 0;
									foreach($reviews as $k=>$review) {
										$count_review ++;
									}										
								?>
									<span class="amount">
										<a href="<?php echo $url; ?>" target="_blank" ><?php echo $count_review.' '.JText::_('VM_LANG_REVIEWS');?></a>
									</span>
								<?php } ?>								
							</div>
						<?php } ?>
					<?php endif; ?>
					
					<!-- Product Stock -->
					<?php if($productStock) : ?>
					<div class="product-stock">
						<span class="vmicon vm2-<?php echo $sLevel; ?>" title="<?php echo $sTip; ?>"></span>
						<?php echo $handle; ?>
					</div>
					<?php endif; ?>
					
					<!-- Product Description -->
					<?php if($productDesc && !empty($pDesc)) : ?>
					<div class="product-description"><?php echo $pDesc; ?></div>
					<?php endif; ?>
					
					<!-- Product Price -->
					<?php if($productPrice) : ?>					
					<div class="product-price">
						<?php if($isSaleLabel!= 0) : ?>
								<?php echo $pPrice; ?>
						<?php else : ?>
							<?php echo $sPrice; ?>
						<?php endif; ?>
					</div>
					<?php endif; ?>
					
					<!-- Add to Cart Button & View Details Button -->
					<?php if($addtocart || $viewDetails) : ?>
						<div class="actions">
							<!-- Product Add To Cart -->
							<?php if($addtocart) : ?>
								<?php modVinaCarouselVirtueMartHelper::addtocart($product); ?>
							<?php endif; ?>
							
							<?php
								$detail_class = ' details-class';
								if(is_dir(JPATH_BASE."/components/com_wishlist/")) {
								$app = JFactory::getApplication();
								$detail_class ='';
							?>
								<!-- view Wishlist Button -->
								<div class="btn-wishlist">							
									<?php require(JPATH_BASE . "/templates/".$app->getTemplate()."/html/wishlist.php"); ?>													
								</div>						
							<?php } ?>
							
							<!-- View Details Button -->
							<?php if($viewDetails) : ?>
								<div class="vm-details-button<?php echo $detail_class;?>"><?php echo $detail; ?></div>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
			<?php endif; endfor; ?>
		</div>
	</div>
	<?php endfor; ?>
</div>

<!-- Javascript Block -->
<script type="text/javascript">
jQuery(document).ready(function($) {
	$('[data-countdown]').each(function() {
		var $this = $(this), 
			finalDate = $(this).data('countdown');
		$this.countdown(finalDate, function(event) {
			$this.html(event.strftime('<div class="day box-time-date"><span class="number">%D </span>Days</div> <div class="hour box-time-date"><span class="number">%H </span>Hrs</div><div class="min box-time-date"><span class="number"> %M</span> Mins</div> <div class="sec box-time-date"><span class="number">%S </span>Secs</div>'));
		});
	});
	$("#vina-carousel-virtuemart<?php echo $module->id; ?>").owlCarousel({
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

        autoPlay : 		<?php echo $autoPlay; ?>,
        stopOnHover : 	<?php echo ($stopOnHover) ? 'true' : 'false'; ?>,

        navigation : 	<?php echo ($navigation) ? 'true' : 'false'; ?>,
        rewindNav : 	<?php echo ($rewindNav) ? 'true' : 'false'; ?>,
        scrollPerPage : <?php echo ($scrollPerPage) ? 'true' : 'false'; ?>,

        pagination : 		<?php echo ($pagination) ? 'true' : 'false'; ?>,
        paginationNumbers : <?php echo ($paginationNumbers) ? 'true' : 'false'; ?>,

        responsive : 	<?php echo ($responsive) ? 'true' : 'false'; ?>,
        autoHeight : 	<?php echo ($autoHeight) ? 'true' : 'false'; ?>,
        mouseDrag : 	<?php echo ($mouseDrag) ? 'true' : 'false'; ?>,
        touchDrag : 	<?php echo ($touchDrag) ? 'true' : 'false'; ?>,		leftOffSet: 	<?php echo $leftOffSet; ?>,
	});
}); 
</script>