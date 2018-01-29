<?php
/**
 *
 * Show the product details page
 *
 * @package	VirtueMart
 * @subpackage
 * @author Max Milbers, Eugen Stranz, Max Galt
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2014 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: default.php 8842 2015-05-04 20:34:47Z Milbo $
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

//Include Helix3 plugin
$helix3_path = JPATH_PLUGINS.'/system/helix3/core/helix3.php';

if (file_exists($helix3_path)) {
    require_once($helix3_path);
    $helix3 = helix3::getInstance();
} else {
    die('Please install and activate helix plugin');
}

/* Parameter */

$vm_social_product = $helix3->getParam('vm_social_product', 1);
$vm_social_product_code = $helix3->getParam('vm_social_product_code');

/* Let's see if we found the product */
if (empty($this->product)) {
	echo vmText::_('COM_VIRTUEMART_PRODUCT_NOT_FOUND');
	echo '<br /><br />  ' . $this->continue_link_html;
	return;
}

echo shopFunctionsF::renderVmSubLayout('askrecomjs',array('product'=>$this->product));

if(vRequest::getInt('print',false)){ ?>
	<body onload="javascript:print();">
<?php } ?>

<div class="productdetails-view productdetails">

    <?php // Product Navigation ?>
    <?php if (VmConfig::get('product_navigation', 1)) { ?>
        <div class="product-neighbours">
			<?php
			if (!empty($this->product->neighbours ['previous'][0])) {
				$prev_link = JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $this->product->neighbours ['previous'][0] ['virtuemart_product_id'] . '&virtuemart_category_id=' . $this->product->virtuemart_category_id, FALSE);
				echo JHtml::_('link', $prev_link, '<i class="fa fa-long-arrow-left"></i>'.$this->product->neighbours ['previous'][0]['product_name'], array('rel'=>'prev', 'class' => 'previous-page','data-dynamic-update' => '1'));
			}
			if (!empty($this->product->neighbours ['next'][0])) {
				$next_link = JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $this->product->neighbours ['next'][0] ['virtuemart_product_id'] . '&virtuemart_category_id=' . $this->product->virtuemart_category_id, FALSE);
				echo JHtml::_('link', $next_link, $this->product->neighbours ['next'][0] ['product_name'] . '<i class="fa fa-long-arrow-right"></i>', array('rel'=>'next','class' => 'next-page','data-dynamic-update' => '1'));
			}
			?>
			<div class="clear"></div>
        </div>
    <?php } // Product Navigation END ?>

	<?php // Back To Category Button
	if ($this->product->virtuemart_category_id) {
		$catURL =  JRoute::_('index.php?option=com_virtuemart&view=category&virtuemart_category_id='.$this->product->virtuemart_category_id, FALSE);
		$categoryName = vmText::_($this->product->category_name) ;
	} else {
		$catURL =  JRoute::_('index.php?option=com_virtuemart');
		$categoryName = vmText::_('COM_VIRTUEMART_SHOP_HOME') ;
	}
	?>	

	<?php echo shopFunctionsF::renderVmSubLayout('customfields',array('product'=>$this->product,'position'=>'ontop')); ?>

    <div class="vm-product-container row">
		<div class="vm-product-media-container col-xs-12 col-md-5">
			<?php
			echo $this->loadTemplate('images'); 
			$count_images = count ($this->product->images);
			if ($count_images > 1) {
				echo $this->loadTemplate('images_additional');
			}
			?>
			<!-- Social Button -->	
			<?php if($vm_social_product) : ?>
			<div class="link-share">
				<?php if ($vm_social_product_code):
							echo $vm_social_product_code;
						else:?>
				<!-- AddThis Button BEGIN -->
				<div class="addthis_toolbox addthis_default_style ">
					<a class="addthis_button_facebook_like at300b" fb:like:layout="button_count"></a>
					<a class="addthis_button_tweet at300b"></a>
					<a class="addthis_button_google_plusone at300b" g:plusone:size="medium"></a>
					<a class="addthis_counter addthis_pill_style addthis_nonzero" href="#"></a>
				</div>	
				<script type="text/javascript" src="//s7.addthis.com/js/250/addthis_widget.js"></script>
				<!-- AddThis Button END --> 
				<?php endif;?>
			</div>
			<?php endif; ?>
		</div>

		<div class="vm-product-details-container col-xs-12 col-md-7">
			<div class="vm-product-details-inner spacer-buy-area">
				<?php // Product Title   ?>
					<h1><?php echo $this->product->product_name ?></h1>
				<?php // Product Title END   ?>
				
				<div class="back-to-category">
					<a href="<?php echo $catURL ?>" class="product-details" title="<?php echo $categoryName ?>"><?php echo vmText::sprintf('COM_VIRTUEMART_CATEGORY_BACK_TO',$categoryName) ?></a>
				</div>
				<?php
				// Product Edit Link
					echo $this->edit_link;
				// Product Edit Link END
				?>
				<?php // afterDisplayTitle Event
				echo $this->product->event->afterDisplayTitle ?>
				
				<?php // Show Rating ?>
				<div class="vm-product-rating-container">
					<?php echo shopFunctionsF::renderVmSubLayout('rating',array('showRating'=>$this->showRating,'product'=>$this->product)); ?>
					
					<?php if(VmConfig::get('showReviewFor', 'none') != 'none') { ?>
						<span class="separator">|</span>
						<span class="add_review"><a href="javascript:void(0)" class="to_review"><?php echo JText::_('VM_LANG_ADD_YOUR_REVIEW'); ?></a></span>
					<?php } ?>
				</div>	   
				   
				<?php // Stock ?>				
              	<p class="in-stock">
					<?php echo "<span>".vmText::_('COM_VIRTUEMART_AVAILABILITY')."</span>"; ?>: 
					<?php if($this->product->product_in_stock > 0) {
						echo vmText::_('COM_VIRTUEMART_PRODUCT_FORM_IN_STOCK');
					}
					else {
						echo vmText::_('COM_VIRTUEMART_STOCK_LEVEL_OUT');
					}?>	
				</p>				
				
				<?php
				// Manufacturer of the Product
				if (VmConfig::get('show_manufacturers', 1) && !empty($this->product->virtuemart_manufacturer_id)) {
					echo $this->loadTemplate('manufacturer');
				}
				?>
				
				<?php // Product Short Description ?>
				<?php if (!empty($this->product->product_s_desc)) { ?>
					<div class="product-short-description">
						<?php
						/** @todo Test if content plugins modify the product description */
						echo nl2br($this->product->product_s_desc);
						?>
					</div>	
				<?php } // Product Short Description END ?>
				
				<?php
				/*if (is_array($this->productDisplayShipments)) {
					foreach ($this->productDisplayShipments as $productDisplayShipment) {
					echo $productDisplayShipment . '<br />';
					}
				}
				if (is_array($this->productDisplayPayments)) {
					foreach ($this->productDisplayPayments as $productDisplayPayment) {
					echo $productDisplayPayment . '<br />';
					}
				}*/
				
				//In case you are not happy using everywhere the same price display fromat, just create your own layout
				//in override /html/fields and use as first parameter the name of your file
				echo shopFunctionsF::renderVmSubLayout('prices',array('product'=>$this->product,'currency'=>$this->currency));
				?>
				
				<?php
				// TODO in Multi-Vendor not needed at the moment and just would lead to confusion
				/* $link = JRoute::_('index2.php?option=com_virtuemart&view=virtuemart&task=vendorinfo&virtuemart_vendor_id='.$this->product->virtuemart_vendor_id);
				  $text = vmText::_('COM_VIRTUEMART_VENDOR_FORM_INFO_LBL');
				  echo '<span class="bold">'. vmText::_('COM_VIRTUEMART_PRODUCT_DETAILS_VENDOR_LBL'). '</span>'; ?><a class="modal" href="<?php echo $link ?>"><?php echo $text ?></a><br />
				 */
				?>				
				
				<?php
				// PDF - Print - Email Icon
				if (VmConfig::get('show_printicon') || VmConfig::get('pdf_icon')) {
				?>
					<div class="icons">
						<?php
						$link = 'index.php?tmpl=component&option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $this->product->virtuemart_product_id;						
					
						//echo $this->linkIcon($link . '&format=pdf', 'COM_VIRTUEMART_PDF', 'pdf_button', 'pdf_icon', false);
						
						//echo $this->linkIcon($link . '&print=1', 'COM_VIRTUEMART_PRINT', 'printButton', 'show_printicon',false,true,false,'class="printModal icon-print"');
						
						//echo $this->linkIcon($MailLink, 'COM_VIRTUEMART_EMAIL', 'emailButton', 'show_emailfriend', false,true,false,'class="recommened-to-friend icon-envelope"');
						?>
						<?php  if (VmConfig::get('pdf_icon')) { ?>
						<a class="icon-file" title="PDF" href="<?php echo $link . '&format=pdf' ?>"><span><?php echo vmText::_('COM_VIRTUEMART_PDF'); ?></span></a>
						<?php } ?>
						<?php  if (VmConfig::get('show_printicon')) { ?>
						<a class="print printModal icon-print" href="<?php echo $link . '&amp;print=1' ?>" data-original-title="<?php echo JText::_('VM_LANG_PRINT_TITLE'); ?>">
							<span><?php echo vmText::_('COM_VIRTUEMART_PRINT'); ?></span>
						</a>
						<?php } ?>						
					</div>
				<?php } // PDF - Print - Email Icon END ?>
				
				<div class="clear"></div>
				<?php
				echo shopFunctionsF::renderVmSubLayout('addtocart',array('product'=>$this->product));
				echo shopFunctionsF::renderVmSubLayout('customfields',array('product'=>$this->product,'position'=>'normal'));
				echo shopFunctionsF::renderVmSubLayout('stockhandle',array('product'=>$this->product));				
				?>				
				<?php if(is_dir(JPATH_BASE . "/components/com_wishlist/") || is_dir(JPATH_BASE . "/components/com_virtuemartproductcompare/") || VmConfig::get('show_emailfriend') ) {?>
					<div class="add-to-box">
						<!-- Add Compare Button -->
						<?php if(is_dir(JPATH_BASE . "/components/com_virtuemartproductcompare/")) : ?>
							<div class="btn-compare jutooltip" title="<?php echo JText::_('ADD_TO_COMPARE');?>">
								<span class="vm-btn-compare"></span>							
							</div>					
						<?php endif; ?>
						
						<!-- Add Wishlist Button -->
						<?php if(is_dir(JPATH_BASE . "/components/com_wishlist/")) : 
							$app = JFactory::getApplication();	
						?>												
							<div class="btn-wishlist">									
								<?php require(JPATH_BASE . "/templates/".$app->getTemplate()."/html/wishlist.php"); ?>									
							</div>							
						<?php endif; ?>
						
						<?php  if (VmConfig::get('show_emailfriend')) { ?>
							<?php $MailLink = 'index.php?option=com_virtuemart&view=productdetails&task=recommend&virtuemart_product_id=' . $this->product->virtuemart_product_id . '&virtuemart_category_id=' . $this->product->virtuemart_category_id . '&tmpl=component'; ?>
							<a class="email-friend recommened-to-friend icon-envelope jutooltip" href="<?php echo $MailLink; ?>" data-original-title="<?php echo JText::_('VM_LANG_EMAIL_TITLE'); ?>" title="<?php echo JText::_('VM_LANG_EMAIL_TITLE'); ?>">
								<span><?php echo vmText::_('COM_VIRTUEMART_EMAIL'); ?></span>
							</a>
						<?php } ?>
					</div>
					<div class="clear"></div>
				<?php } ?>
				
				<?php
				// Ask a question about this product
				if (VmConfig::get('ask_question', 0) == 1) {
					$askquestion_url = JRoute::_('index.php?option=com_virtuemart&view=productdetails&task=askquestion&virtuemart_product_id=' . $this->product->virtuemart_product_id . '&virtuemart_category_id=' . $this->product->virtuemart_category_id . '&tmpl=component', FALSE);
				?>							
					<a class="ask-a-question" href="<?php echo $askquestion_url ?>" rel="nofollow" ><i class="fa fa-paper-plane-o"></i><?php echo vmText::_('COM_VIRTUEMART_PRODUCT_ENQUIRY_LBL') ?></a>						
				<?php } ?>			
			</div>
		</div>		
    </div>
	
	<div class="clear"></div>
	<!-- End Social Button -->
	
	<!-- Tabs Full Description + Review + comment -->
	<div id="tab-block" role="tabpanel">
		<!-- Nav tabs -->
		<ul id="tabs-detail-product" class="nav nav-tabs" role="tablist">
			<li role="presentation" class="tab_des active"><a href="#vina-description" aria-controls="vina-description" role="tab" data-toggle="tab"><?php echo JText::_('VM_LANG_FULL_DESCRIPTION'); ?></a></li>
			<li role="presentation" class="tab_review"><a href="#vina-reviews" aria-controls="vina-reviews" role="tab" data-toggle="tab"><?php echo JText::_('VM_LANG_OVERVIEWS'); ?></a></li>			
		</ul>

		<!-- Tab panes -->
		<div id="vinaTabContent" class="tab-content">
			<div role="tabpanel" class="tab-pane active" id="vina-description">
				<?php echo $this->product->product_desc; ?>
			</div>
			<div role="tabpanel" class="tab-pane" id="vina-reviews">
				<?php echo $this->loadTemplate('reviews'); ?>
			</div>			
		</div>
	</div>
	<?php

	// event onContentBeforeDisplay
	echo $this->product->event->beforeDisplayContent; 
	?>	

	<?php

	// Product Packaging
	$product_packaging = '';
	if ($this->product->product_box) {
	?>
		<div class="product-box">
			<?php echo vmText::_('COM_VIRTUEMART_PRODUCT_UNITS_IN_BOX') .$this->product->product_box; ?>
		</div>
	<?php } // Product Packaging END ?>

	<?php 
	echo shopFunctionsF::renderVmSubLayout('customfields',array('product'=>$this->product,'position'=>'onbot'));
	echo shopFunctionsF::renderVmSubLayout('customfields_related',array('product'=>$this->product,'position'=>'related_products','class'=> 'product-related-products','customTitle' => true ));
	echo shopFunctionsF::renderVmSubLayout('customfields',array('product'=>$this->product,'position'=>'related_categories','class'=> 'product-related-categories'));
	?>

	<?php // onContentAfterDisplay event
	echo $this->product->event->afterDisplayContent;
	//echo $this->loadTemplate('reviews');

	// Show child categories
	if (VmConfig::get('showCategory', 1)) {
		echo $this->loadTemplate('showcategory');
	}

	$j = 'jQuery(document).ready(function($) {
		Virtuemart.product(jQuery("form.product"));

		$("form.js-recalculate").each(function(){
			if ($(this).find(".product-fields").length && !$(this).find(".no-vm-bind").length) {
				var id= $(this).find(\'input[name="virtuemart_product_id[]"]\').val();
				Virtuemart.setproducttype($(this),id);

			}
		});
	});';
	//vmJsApi::addJScript('recalcReady',$j);

	/** GALT
		 * Notice for Template Developers!
		 * Templates must set a Virtuemart.container variable as it takes part in
		 * dynamic content update.
		 * This variable points to a topmost element that holds other content.
		 */
	$j = "Virtuemart.container = jQuery('.productdetails-view');
		  Virtuemart.containerSelector = '.productdetails-view';";

	vmJsApi::addJScript('ajaxContent',$j);
	
	echo vmJsApi::writeJS();
	?>
	
</div>
<?php
	$app= JFactory::getApplication();	
	$doc = JFactory::getDocument();
	
	$template = $app->getTemplate();
	$current_template_path = $this->baseurl."/templates/".$template ;
	if(VmConfig::get('showReviewFor', 'none') != 'none') {					
		$doc->addScriptDeclaration("
			jQuery(function($) {							
				$('.to_review, .count_review').click(function() {
					$('html, body').animate({
						scrollTop: ($('#tab-block').offset().top - 120)
					},500);									
					$('#tabs-detail-product li').removeClass('active');
					$('#tabs-detail-product li.tab_review').addClass('active');
					$('#vinaTabContent >div').removeClass('active');
					$('#vinaTabContent #vina-reviews').addClass('active');
				});
			})
		");
	}	
?>