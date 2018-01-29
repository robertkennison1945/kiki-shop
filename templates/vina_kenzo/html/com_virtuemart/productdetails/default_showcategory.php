<?php
/**
 *
 * Show the product details page
 *
 * @package	VirtueMart
 * @subpackage
 * @author Max Milbers, Valerie Isaksen

 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2012 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: default_showcategory.php 8811 2015-03-30 23:11:08Z Milbo $
 */

// Check to ensure this file is included in Joomla!
defined ( '_JEXEC' ) or die ( 'Restricted access' );

if ($this->category->haschildren) {
	$iCol = 1;
	$iCategory = 1;
	$categories_per_row = VmConfig::get('categories_per_row', 3);
	//$category_cellwidth = ' width' . floor(100 / $categories_per_row);
	//$verticalseparator = " vertical-separator";
	
	// Calculating Categories Per Row
	$category_cellwidth = 'width-percent floatleft width'.floor ( 100 / $categories_per_row );

	// Separator
	$verticalseparator = " vertical-separator";

	if($categories_per_row == 1 || $categories_per_row == 2 || $categories_per_row == 3 || $categories_per_row == 4 || $categories_per_row == 6 || $categories_per_row == 12) {
		$category_cellwidth = "col-md-".(12/$categories_per_row)." col-lg-".(12/$categories_per_row);	
	}
	?>

	<div class="category-view">
		<div class="category-fields-title-wrapper">
			<h3><span><?php echo JText::_('VM_LANG_RELATED_CATEGORIES_TITLE')?></span></h3>
		</div>
		<?php
		// Start the Output
		if (!empty($this->category->children)) {
			foreach ($this->category->children as $category) {

			// Show the horizontal seperator
			if ($iCol == 1 && $iCategory > $categories_per_row) {
			?>
				<div class="horizontal-separator"></div>
			<?php } ?>
			
			<?php // this is an indicator whether a row needs to be opened or not ?>
			<?php if ($iCol == 1) { ?>
				<div class="row">
			<?php } ?>
			
			<?php
				// Show the vertical seperator
				if ($iCategory == $categories_per_row or $iCategory % $categories_per_row == 0) {
				$show_vertical_separator = ' ';
				} else {
				$show_vertical_separator = $verticalseparator;
				}

				// Category Link
				$caturl = JRoute::_('index.php?option=com_virtuemart&view=category&virtuemart_category_id=' . $category->virtuemart_category_id, FALSE);

				// Show Category
				?>
				<div class="category <?php echo $category_cellwidth . $show_vertical_separator ?>">
					<div class="category-inner">						
						<a href="<?php echo $caturl ?>" title="<?php echo vmText::_($category->category_name) ?>">
							<h2 class="category-title"><?php echo vmText::_($category->category_name) ?></h2>							
							<?php echo $category->images[0]->displayMediaThumb("", false); ?>
						</a>						
					</div>
				</div>
				<?php
				$iCategory++;

				// Do we need to close the current row now?
				if ($iCol == $categories_per_row) {
				?>
					<div class="clear"></div>
				</div>
				<?php
				$iCol = 1;
			} else {
				$iCol++;
			}
			}
		}
		// Do we need a final closing row tag?
		if ($iCol != 1) {
		?>
		<div class="clear"></div>
	</div>
	<?php } ?>
</div>
<?php }