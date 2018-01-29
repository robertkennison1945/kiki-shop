<?php
/**
 * sublayout products
 *
 * @package	VirtueMart
 * @author Max Milbers
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2014 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL2, see LICENSE.php
 * @version $Id: cart.php 7682 2014-02-26 17:07:20Z Milbo $
 */

defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.modal'); 

$products_per_row = $viewData['products_per_row'];
$currency = $viewData['currency'];
$showRating = $viewData['showRating'];
$verticalseparator = " vertical-separator";
echo shopFunctionsF::renderVmSubLayout('askrecomjs');
$ItemidStr = '';
$Itemid = shopFunctionsF::getLastVisitedItemId();
if(!empty($Itemid)){
	$ItemidStr = '&Itemid='.$Itemid;
}

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

$productModel = VmModel::getModel('product');
foreach ($viewData['products'] as $type => $products ) {
	$productModel->addImages($products,2);
	$rowsHeight = shopFunctionsF::calculateProductRowsHeights($products,$currency,$products_per_row);
	if(!empty($type) and count($products)>0){
		$productTitle = vmText::_('COM_VIRTUEMART_'.strtoupper($type).'_PRODUCT'); ?>
		<div class="<?php echo $type ?>-view">
			<h4><?php echo $productTitle ?></h4>			
	<?php } ?>
	
	<?php // Calculating Products Per Row
	$cellwidth = 'width-percent floatleft width'.floor ( 100 / $products_per_row );
	$BrowseTotalProducts = count($products);
	$col = 1;
	$nb = 1;
	$row = 1;	
	
	if($products_per_row == 3) {
		$cellwidth = "col-xs-12 col-sm-4 col-md-4 col-lg-4";	
	}
	if($products_per_row == 2 || $products_per_row == 4) {
		$cellwidth = "col-xs-12 col-sm-6 col-md-".(12/$products_per_row)." col-lg-".(12/$products_per_row);	
	}
	if($products_per_row == 6 || $products_per_row == 12) {
		$cellwidth = "col-xs-12 col-sm-6 col-md-4 col-lg-".(12/$products_per_row);	
	} ?>
		
	<div class="list-product row">
		<?php foreach ( $products as $product ) {
			// Show the vertical seperator
			if ($nb == $products_per_row or $nb % $products_per_row == 0) {
				$show_vertical_separator = ' ';
			} else {
				$show_vertical_separator = $verticalseparator;
			}
			?>
			<?php 
				// Show Label Sale Or New								
				$isSaleLabel = (!empty($product->prices['discountAmount'])) ? 1 : 0;

				$pid = $product->virtuemart_product_id;
				$isNewLabel = in_array($pid, $newIds);
			?>
			<?php // Show Products ?>
			<?php if ($col == 1 ) { ?>
				<div class="row product-row">
			<?php } ?>
			
			<div class="product <?php echo $cellwidth . $show_vertical_separator ?>">
				<div class="product-inner">					
					<div class="item-i">
						<!-- Product Image -->							
						<div class="vm-product-media-container">
							<?php if($vm_product_labels) {?>
								<div class="product-status">
									<!-- Check Product Label -->									
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
							<?php }?>
							<!-- Image Block -->															
							<div class="image-block">										
								<?php
									$image = $product->images[0]->displayMediaThumb('class="browseProductImage"', false);
									if(!empty($product->images[1])){
										$image2 = $product->images[1]->displayMediaThumb('class="browseProductImage"', false);
										echo JHTML::_('link', $product->link.$ItemidStr,'<div class="pro-image first-image">'.$image.'</div><div class="pro-image second-image">'.$image2.'</div>',array('class'=>"double-image",'title'=>$product->product_name));
									} else {								
										echo JHTML::_('link', $product->link.$ItemidStr,'<div class="pro-image">'.$image.'</div>',array('class'=>"single-image",'title'=>$product->product_name));
									}
								?>																
							</div>
								
							<!-- View Details Button1 -->
							<?php $link = empty($product->link)? $product->canonical:$product->link; ?>
							<div class="vm-btn-quickview">
								<?php echo JHTML::link($link.$ItemidStr, '<span>'.vmText::_('COM_VIRTUEMART_PRODUCT_DETAILS').'</span>', array('title' => vmText::_ ( 'COM_VIRTUEMART_PRODUCT_DETAILS' ), 'class' => 'product-details')); ?>
							</div>							
						</div>
						
						<div class="text-block">							
							
							<!-- Product Title -->
							<h2 class="product-title"><?php echo JHtml::link ($product->link.$ItemidStr, $product->product_name); ?></h2>				
							
							<div class="vm-product-rating-container">
								<?php 
									echo shopFunctionsF::renderVmSubLayout('rating',array('showRating'=>$showRating, 'product'=>$product));	
									$ratingModel = VmModel::getModel('ratings');
									$reviews = $ratingModel->getReviewsByProduct($product->virtuemart_product_id);
									if(!empty($reviews)) {					
										$count_review = 0;
										foreach($reviews as $k=>$review) {
											$count_review ++;
										} ?>
										<span class="amount">
											<?php echo JHtml::link($product->link.$ItemidStr, $count_review.' '.JText::_('VM_LANG_REVIEWS'),'target = "_blank"'); ?>			
										</span>
									<?php } ?>
									<?php
									if ( VmConfig::get ('display_stock', 1)) { ?>
										<div class="product-stock">
										<span class="vmicon vm2-<?php echo $product->stock->stock_level ?>" title="<?php echo $product->stock->stock_tip ?>"></span>
										</div>
									<?php }
									echo shopFunctionsF::renderVmSubLayout('stockhandle',array('product'=>$product));
								?>
							</div>
							
							<!-- Product Price -->
							<?php echo shopFunctionsF::renderVmSubLayout('prices',array('product'=>$product,'currency'=>$currency)); ?>						
							
							<!-- Product Short Description -->
							<div class="product_s_desc vm-product-descr-container-<?php echo $rowsHeight[$row]['product_s_desc'] ?>">
								<?php if(!empty($rowsHeight[$row]['product_s_desc'])){ ?>						
									<?php // Product Short Description ?>
									<?php if (!empty($product->product_s_desc)) {
										echo shopFunctionsF::limitStringByWord ($product->product_s_desc, 220, ' ...') ?>
									<?php } ?>						
								<?php } ?>
							</div>
							
							<!-- Product Actions -->					
							<div class="actions">
								<?php // customfields - Add to cart ?>
								<?php echo shopFunctionsF::renderVmSubLayout('addtocart',array('product'=>$product,'rowHeights'=>$rowsHeight[$row])); ?>
								<div class="add-to-links">
									<?php // Wishlist ?>				
									<?php
										$detail_class = ' details-class';
										if(is_dir(JPATH_BASE."/components/com_wishlist/")) {
										$app = JFactory::getApplication();
										$detail_class ='';
									?>
										<div class="btn-wishlist">							
											<?php require(JPATH_BASE . "/templates/".$app->getTemplate()."/html/wishlist.php"); ?>													
										</div>						
									<?php } ?>

									<?php //View detail ?>
									<div class="vm-details-button<?php echo $detail_class;?>">
										<?php // Product Details Button			
										$link = empty($product->link)? $product->canonical:$product->link;
										echo JHtml::link($link.$ItemidStr,'<i class="fa fa-search"></i><span>'.vmText::_ ( 'COM_VIRTUEMART_PRODUCT_DETAILS' ).'</span>', array ('title' => vmText::_ ( 'COM_VIRTUEMART_PRODUCT_DETAILS' ), 'class' => 'product-details jutooltip' ) );				
										?>
									</div>
								</div>
							</div>
						</div>
					</div>					
				</div>
			</div>

			<?php
			$nb ++;
			// Do we need to close the current row now?
			if ($col == $products_per_row || $nb>$BrowseTotalProducts) { ?>			
				</div>
				<?php
				$col = 1;
				$row++;
			} else {
			  $col ++;
			}
		} ?>
		<?php if ($col != 1) { ?>			
			</div>
		<?php } ?>
	</div>
	<?php
	if(!empty($type)and count($products)>0){
		// Do we need a final closing row tag?
		//if ($col != 1) {
	?>
			<div class="clear"></div>
		</div>
    <?php   
    }
}
