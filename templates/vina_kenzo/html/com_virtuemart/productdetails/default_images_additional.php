<?php
/**
 *
 * Show the product details page
 *
 * @package	VirtueMart
 * @subpackage
 * @author Max Milbers, Valerie Isaksen

 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: default_images.php 7784 2014-03-25 00:18:44Z Milbo $
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
?>
<div class="additional-images">
	<!--<div class="additional-images-inner">-->
		<div id="additional_images_gallery" class="owl-carousel style1">
		<?php
			$start_image = VmConfig::get('add_img_main', 1) ? 0 : 1;
			for ($i = $start_image; $i < count($this->product->images); $i++) {
				$image = $this->product->images[$i];
				?>
				<div class="item">
					<div class="item-inner">
						<?php						
						if(VmConfig::get('add_img_main', 1)) {
							$title_file = '';							
							$title_file = ($image->file_meta == '') ? ' title="'. $image->file_name .'"' : ' title="'. $image->file_meta .'"';
							echo $image->displayMediaThumb('class="product-image img-'. $i .'" style="cursor: pointer"',false,$image->file_description);
							echo '<a href="'. $image->file_url .'"  class="product-image image-'. $i .'" style="display:none;"'.$title_file.' data-rel="vm-additional-images"></a>';
						} else {
							echo $image->displayMediaThumb("",true,"data-rel='vm-additional-images'",true,$image->file_description);
						}
						?>						
					</div>
				</div>
			<?php } ?>
		</div>
		
		<!-- Javascript Block -->
		<script type="text/javascript">
		jQuery(document).ready(function($) {
			$("#additional_images_gallery").owlCarousel({
				items : 		4,
				itemsDesktop:	[1170,4],
				itemsDesktopSmall: [980,4],
				itemsTablet: [800,3],
				itemsTabletSmall: [650,3],
				itemsMobile: [479,2],
				
				navigation: 	true,
				pagination: 	false,
				
				mouseDrag: false,
				touchDrag: false,
				navigationText : ['<i class="fa fa-angle-left" aria-hidden="true"></i>','<i class="fa fa-angle-right" aria-hidden="true"></i>'],
			});
		}); 
		</script>
	<!--</div>-->
</div>

