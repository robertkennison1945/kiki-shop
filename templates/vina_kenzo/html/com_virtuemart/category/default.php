<?php
/**
 *
 * Show the products in a category
 *
 * @package    VirtueMart
 * @subpackage
 * @author RolandD
 * @author Max Milbers
 * @todo add pagination
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: default.php 8811 2015-03-30 23:11:08Z Milbo $
 */

defined ('_JEXEC') or die('Restricted access');

$app 	  = JFactory::getApplication();
$template = $app->getTemplate();

//Include Helix3 plugin
$helix3_path = JPATH_PLUGINS.'/system/helix3/core/helix3.php';

if (file_exists($helix3_path)) {
    require_once($helix3_path);
    $helix3 = helix3::getInstance();
} else {
    die('Please install and activate helix plugin');
}

$show_vmcategory_image = $helix3->getParam('show_vmcategory_image', 1);
$vm_view_mode = $helix3->getParam('vm_view_mode', 1);

?>
<script type="text/javascript" src="<?php echo JURI::base() . 'templates/' . $template . '/js/jquery.cookie.js'; ?>"></script>

<script type="text/javascript">
jQuery(document).ready(function ($) {
	if ($.cookie('listing') == 'list') {
		$('.view-mode a').parents('.listing-view').addClass('vm_list_view');
		$('.view-mode a.mode-grid').removeClass('active');
		$('.view-mode a.mode-list').addClass('active');
	}
});
</script>

<div class="category-view">
	<?php
	$js = "
		jQuery(document).ready(function () {
			jQuery('.orderlistcontainer').hover(
				function() { jQuery(this).find('.orderlist').stop().show()},
				function() { jQuery(this).find('.orderlist').stop().hide()}
			)
		});
	";
	vmJsApi::addJScript('vm.hover',$js);
	?>

	<?php if($this->category->category_name) { ?>
		<?php // Category Name ?>
		<!--<div class="vm-page-title vm-category-title">
			<h1><?php //echo $this->category->category_name; ?></h1>
		</div>-->
		<?php // Category Image
		if( $show_vmcategory_image && !empty($this->category->images[0]->file_url_thumb) && isset($this->category->images[0]->file_url_thumb) ) { ?>
			<div class="cat_image">
				<?php echo $this->category->images[0]->displayMediaThumb("",false); ?>
			</div>
		<?php } ?>
	<?php } ?>		

	<?php
	// Category Description
	if (empty($this->keyword) and !empty($this->category)) { ?>
		<div class="category_description">
			<?php echo $this->category->category_description; ?>
		</div>
	<?php } ?>
	
	<?php // Show child categories
	if (VmConfig::get ('showCategory', 1) and empty($this->keyword)) {
		if (!empty($this->category->haschildren)) {
			echo ShopFunctionsF::renderVmSubLayout('categories',array('categories'=>$this->category->children));
		}
	} ?>

	<?php if($this->showproducts){ ?>
		<div id="vm-products-category" class="browse-view listing-view <?php echo  ($this->productsLayout == 'products_horizon') ? 'vm_list_view' : 'vm_grid_view'; ?>">
			<?php
			if (!empty($this->keyword)) {
				//id taken in the view.html.php could be modified
				$category_id  = vRequest::getInt ('virtuemart_category_id', 0); ?>
				<h3><?php echo $this->keyword; ?></h3>
				
				<form action="<?php echo JRoute::_ ('index.php?option=com_virtuemart&view=category&limitstart=0', FALSE); ?>" method="get">
					<!--BEGIN Search Box -->
					<div class="virtuemart_search">
						<?php echo $this->searchcustom ?>
						<br/>
						<?php echo $this->searchCustomValues ?>
						<input name="keyword" class="inputbox" type="text" size="20" value="<?php echo $this->keyword ?>"/>
						<input type="submit" value="<?php echo vmText::_ ('COM_VIRTUEMART_SEARCH') ?>" class="button" onclick="this.form.keyword.focus();"/>
					</div>
					<input type="hidden" name="search" value="true"/>
					<input type="hidden" name="view" value="category"/>
					<input type="hidden" name="option" value="com_virtuemart"/>
					<input type="hidden" name="virtuemart_category_id" value="<?php echo $category_id; ?>"/>

				</form>
				<!-- End Search Box -->
			<?php  } ?>

			<?php // Show orderby & displaynumber ?>
			<div class="orderby-displaynumber">	
				<?php if($vm_view_mode) {?>
					<div class="view-mode pull-left">
						<a href="javascript:viewMode('grid');" class="mode-grid <?php echo  ($this->productsLayout == 'products') ? 'active' : ''; ?>" title="Grid"><i class="fa fa-th-large"></i></a>
						<a href="javascript:viewMode('list');" class="mode-list	<?php echo  ($this->productsLayout == 'products_horizon') ? 'active' : ''; ?>" title="List"><i class="fa fa-th-list"></i></a>
					</div>
				<?php }?>
				<div class="orderby-displaynumber-inner">
					<div class="floatleft vm-order-list">
						<?php echo $this->orderByList['orderby']; ?>
						<?php echo $this->orderByList['manufacturer']; ?>
					</div>
					<!--<div class="vm-pagination vm-pagination-top">
						<?php echo $this->vmPagination->getPagesLinks (); ?>
						<span class="vm-page-counter"><?php echo $this->vmPagination->getPagesCounter (); ?></span>
					</div> -->
					<div class="floatright display-number">
						<?php echo $this->vmPagination->getResultsCounter ();?>
						<?php echo $this->vmPagination->getLimitBox ($this->category->limit_list_step); ?>
					</div>
				</div>
			</div> <!-- end of orderby-displaynumber -->

			<div class="clear"></div>

			<!--<h1 class="title-category"><?php echo vmText::_($this->category->category_name); ?></h1>-->

			<?php
			if (!empty($this->products)) {
				$products = array();
				$products[0] = $this->products;
				echo shopFunctionsF::renderVmSubLayout($this->productsLayout,array('products'=>$products,'currency'=>$this->currency,'products_per_row'=>$this->perRow,'showRating'=>$this->showRating));
			?>
				<?php if ($this->vmPagination->getPagesCounter ()) { ?>
					<div class="vm-pagination vm-pagination-bottom">
						<span class="vm-page-counter"><?php echo $this->vmPagination->getPagesCounter (); ?></span>
						<?php echo $this->vmPagination->getPagesLinks (); ?>					
					</div>					
				<?php } ?>					
			<?php
			} elseif (!empty($this->keyword)) {
				echo vmText::_ ('COM_VIRTUEMART_NO_RESULT') . ($this->keyword ? ' : (' . $this->keyword . ')' : '');
			}
			?>
		</div>
	<?php } ?>
</div>

<?php
	$j = "Virtuemart.container = jQuery('.category-view');
	Virtuemart.containerSelector = '.category-view';";
	vmJsApi::addJScript('ajaxContent',$j);
?>
<!-- end browse-view -->