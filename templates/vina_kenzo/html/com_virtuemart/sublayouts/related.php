<?php defined('_JEXEC') or die('Restricted access');

$related = $viewData['related'];
$customfield = $viewData['customfield'];
$thumb = $viewData['thumb'];
//$showRating = $viewData['showRating'];
$ratingModel = VmModel::getModel('ratings');
$rating = $ratingModel->getRatingByProduct($related->virtuemart_product_id);
$reviews = $ratingModel->getReviewsByProduct($related->virtuemart_product_id);

$isSale = (!empty($related->prices['discountAmount'])) ? 1 : 0;
//juri::root() For whatever reason, we used this here, maybe it was for the mails
?>
<div class="vm-product-media-container">
	<?php echo JHtml::link (JRoute::_ ('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $related->virtuemart_product_id . '&virtuemart_category_id=' . $related->virtuemart_category_id), $thumb , array('title' => $related->product_name)); ?>	
</div>

<div class="text-block">
	<!-- Title Product-->
	<h2 class="product-title">
	<?php echo JHtml::link (JRoute::_ ('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $related->virtuemart_product_id . '&virtuemart_category_id=' . $related->virtuemart_category_id), $related->product_name, array('title' => $related->product_name)); ?>
	</h2>
	<!-- Rating Block -->
	<div class="vm-product-rating-container">
		<?php 
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
					<?php echo JHtml::link(JRoute::_ ('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $related->virtuemart_product_id . '&virtuemart_category_id=' . $related->virtuemart_category_id), $count_review.' '.JText::_('VM_LANG_REVIEWS'),'target = "_blank"'); ?>
				</span>
		<?php } ?>
	</div>

	<?php
	if($customfield->wPrice){	
		$currency = calculationHelper::getInstance()->_currencyDisplay; //echo $currency->_currency_id;
	?>
	<div class="vm-prices-block">
		<div class="related-product-price" id="productPrice<?php echo $related->virtuemart_product_id ?>">
		<?php
			echo $currency->createPriceDiv ('salesPrice', 'COM_VIRTUEMART_PRODUCT_SALESPRICE', $related->prices);
			if ($isSale) {
				echo '<div class="price-crossed" >'.$currency->createPriceDiv ('basePrice', 'COM_VIRTUEMART_PRODUCT_BASEPRICE', $related->prices).'</div>';	
			}				
		?>
		</div>
	</div>	
	<?php }
	if($customfield->wDescr){
		echo '<p class="product_s_desc">'.$related->product_s_desc.'</p>';
	}
	?>
</div>