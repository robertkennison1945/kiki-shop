<?php // no direct access
defined('_JEXEC') or die('Restricted access');
// Text Total
if ($data->totalProduct>1) $data->totalProductTxt = JText::sprintf('COM_VIRTUEMART_CART_X_PRODUCTS', $data->totalProduct);
else if ($data->totalProduct == 1) $data->totalProductTxt = JText::_('COM_VIRTUEMART_CART_ONE_PRODUCT');
else $data->totalProductTxt = JText::_('COM_VIRTUEMART_EMPTY_CART');

//dump ($cart,'mod cart');
// Ajax is displayed in vm_cart_products
// ALL THE DISPLAY IS Done by Ajax using "hiddencontainer" ?>

<!-- Virtuemart 2 Ajax Card -->
<div class="vmCartModule <?php echo $params->get('moduleclass_sfx'); ?>" id="vmCartModule">
	<?php if ($show_product_list) { ?>
		<div class="block-mini-cart">			                       		
			<div class="mini-cart mini-cart-body">
				<a class="mini-cart-title">
					<i class="fa fa-shopping-cart"></i>
					<!--<span class="item-cart">					
						<span class="total"><?php //echo $data->billTotal; ?></span>
					</span>	 -->
					<!--<span class="number"><?php //echo $data->totalProduct; ?></span>-->
					<span class="text-title"><?php echo JText::_('VM_LANG_MY_CART'); ?></span>
					<div class="total">
						<?php echo $data->billTotal; ?>						
					</div>
					<!--<span class="total_products">
						<?php //echo $data->totalProductTxt; ?>
					</span>-->					
				</a>
				<div id="hiddencontainer" class="hiddencontainer" style=" display: none; ">
					<div class="vmcontainer">
						<div class="product_row">
							<!-- Image line -->
							<!--<div class="product_image"></div>-->
							<!-- End - Image line -->
							<span class="quantity"></span>&nbsp;x&nbsp;<span class="product_name"></span>

							<?php if ($show_price and $currencyDisplay->_priceConfig['salesPrice'][0]) { ?>
								<div class="subtotal_with_tax" style="float: right;"></div>
							<?php } ?>
							<div class="customProductData"></div><br>
						</div>
					</div>
				</div>
				<div class="mini-cart-content">	
					<div class="vm_cart_products">
						<div class="vmcontainer">
							<?php if(empty($data->products)) { ?>
								<p class="empty"><?php echo JText::_('VM_LANG_CART_EMPTY')?></p>
							<?php } else { ?>
								<?php foreach ($data->products as $product){ ?>									
									<div class="product_row">

										<!-- Image line -->
										<!--<div class="product_image">
										<a href="<?php echo $product["link"];?>"><?php echo $product["image"]; ?></a>
										</div>-->
										<!-- End - Image line -->
							
										<span class="quantity"><?php echo  $product['quantity'] ?></span>&nbsp;x&nbsp;<span class="product_name"><?php echo  $product['product_name'] ?></span>
										<?php if ($show_price and $currencyDisplay->_priceConfig['salesPrice'][0]) { ?>
											<div class="subtotal_with_tax" style="float: right;"><?php echo $product['subtotal_with_tax'] ?></div>
										<?php } ?>
										<?php if ( !empty($product['customProductData']) ) { ?>
											<div class="customProductData"><?php echo $product['customProductData'] ?></div><br>
										<?php } ?>					
									</div>
								<?php } ?>								
							<?php } ?>
						</div>
					</div>		
					<div class="total">
						<?php echo $data->billTotal; ?>						
					</div>			
					<div class="show_cart">
						<?php //if ($data->totalProduct) ?>
						<?php echo  $data->cart_show; ?>
					</div>
					<div style="clear:both;"></div>
					<div class="payments_signin_button" ></div>
				</div>
			</div>			
		</div>					
	<?php } ?>
	<noscript>
		<?php echo vmText::_('MOD_VIRTUEMART_CART_AJAX_CART_PLZ_JAVASCRIPT') ?>
	</noscript>
</div>