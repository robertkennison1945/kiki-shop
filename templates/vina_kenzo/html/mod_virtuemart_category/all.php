<?php // no direct access
defined('_JEXEC') or die('Restricted access');
//JHTML::stylesheet ( 'menucss.css', 'modules/mod_virtuemart_category/css/', false );

//Include Helix3 plugin
$helix3_path = JPATH_PLUGINS.'/system/helix3/core/helix3.php';
if (file_exists($helix3_path)) {
    require_once($helix3_path);
    $helix3 = helix3::getInstance();
} else {
    die('Please install and activate helix plugin');
}

$cat_max_items = $helix3->getParam('vm_category_maxItems', 3);
?>

<ul class="menu<?php echo $class_sfx ?>" >
``<?php foreach ($categories as $category) {
		if($key < $cat_max_items) {
			$active_menu = 'class="VmClose"';
			$caturl = JRoute::_('index.php?option=com_virtuemart&view=category&virtuemart_category_id='.$category->virtuemart_category_id);
			$cattext = $category->category_name;
			
			//var_dump($category);
			$category_description = $category->category_description;	
			if (in_array( $category->virtuemart_category_id, $parentCategories)) $active_menu = 'class="VmOpen"';

	?>

<li <?php echo $active_menu ?>>
	<div>
		<?php echo JHTML::link($caturl, $cattext); ?>
	</div>
<?php if ($category->childs ) {


?>
<ul class="menu<?php echo $class_sfx; ?>">
<?php
	foreach ($category->childs as $child) {

		$caturl = JRoute::_('index.php?option=com_virtuemart&view=category&virtuemart_category_id='.$child->virtuemart_category_id);
		$cattext = vmText::_($child->category_name);
		?>
<li>
	<div ><?php echo JHTML::link($caturl, $cattext); ?></div>
</li>
<?php } ?>
</ul>
<?php 	} ?>
</li>
<?php
	} ?>
</ul>
