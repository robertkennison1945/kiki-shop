<?php
/**
 /**
 * The helper class which contains the functionality for fetching and creating the filter's options
 * @package	customfilters
 * @author 	Sakis Terz
 * @copyright	Copyright (C) 2012-2016 breakdesigns.net . All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
jimport('joomla.filter.filteroutput');

/**
 * Class responsible for generating the options of each filter
 * @author sakis
 */
class ModCfilteringOptions
{
	private $langPlugin=false;
	private $product_ids;
	public $selected_flt;
	private $shopperGroups=array();
	public $input;
	public $fltSuffix=array(
	'q'=>'keyword_flt',
	'virtuemart_category_id'=>'category_flt',
	'virtuemart_manufacturer_id'=>'manuf_flt',
	'price'=>'price_flt',
	'custom_f'=>'custom_flt');

	private $moduleparams;
	protected static $_publishedCustomFilters=array();

	public static $parent_categories=array();

	/**
	 * Constructor
	 *
	 * @param object $params - the module params
	 * @param object $module - the module object
	 */
	function __construct($params,$module)
	{
		$app = JFactory::getApplication() ;
		$this->moduleparams=$params;
		$this->componentparams  = cftools::getComponentparams();
		$this->menuparams=cftools::getMenuparams();
		$this->customFltActive=cftools::getCustomFilters($this->moduleparams);
		$jinput=$app->input;
		$this->input=$jinput;
		$this->selected_flt=CfInput::getInputs();
		$this->shopperGroups=cftools::getUserShopperGroups();
		$this->vmVersion=VmConfig::getInstalledVersion();
		$option=$jinput->get('option','','cmd');

		//in cf pages get the returned product ids
		if($option=='com_customfilters'){
			$this->product_ids=$app->getUserState("com_customfilters.product_ids");
		}else $this->product_ids=array();

		$dependency_dir=$params->get('dependency_direction','all');
		if(count($this->selected_flt)>0 && $dependency_dir=='t-b')$this->selected_fl_per_flt=CfInput::getInputsPerFilter($module);
	}

	/**
	 * Proxy function to get the options of specific filters
	 *
	 * @param 	string $var_name
	 * @param 	string $custom_field_type - used only for custom fields
	 *
	 * @return	array - the options
	 */
	public function getOptions($var_name,$custom_filter=null)
	{
		$options=array();
		if(strpos($var_name, 'custom_f_')!==false)$var_type='custom_f';
		else $var_type=$var_name;

		switch($var_type){
			case 'virtuemart_category_id':
				$options=$this->getCategories();
				break;
			case 'virtuemart_manufacturer_id':
				$options=$this->getManufacturers();
				break;
		}
		return $options;
	}

	/**
	 *
	 * Proxy function to build the queries of the various filters
	 *
	 * @param	object	a db query object
	 * @param 	string 	$var_name
	 * @param 	string 	$custom_field_type - used only for custom fields
	 *
	 * @return	object 	a db query object
	 * @since	1.5.0
	 */
	public function buildQuery($query,$var_name,$customFilter,$part=false)
	{
		$options=array();
		if(!empty($customFilter))$var_type='custom_f';
		else $var_type=$var_name;

		switch($var_type){
			case 'virtuemart_category_id':
				$query=$this->buildCategoriesQuery($query,$part);
				break;
			case 'virtuemart_manufacturer_id':
				$query=$this->buildManufQuery($query,$part);
				break;
			default:
				$query=$query;
				break;
		}
		return $query;
	}

	/**
	 * Set the languagefilter plugin state
	 * @param boolean $isEnabled
	 */
	public function setLanguageSwitch($isEnabled=false){
		$this->langPlugin=$isEnabled;
	}

	/**
	 * Get the active options of a current filter using dependencies from the selections in other filters
	 *
	 * @param	string	$field The var's name
	 * @param	object	$customfilter Used only for custom filters.The current custom filter
	 * @param	boolean	$joinFieldData indicates if there will be join with other queries, built by the buildQuery functions.
	 * Join is not necessary when the display type is "disabled" as only the active options are used
	 *
	 * @return 	mixed	array when there are results - true if there are no other filters selected. So all are active
	 * @author	Sakis Terz
	 * @since	1.0
	 */
	 public function getActiveOptions($field, $customfilter=null, $joinFieldData=false)
	 {
		$vmCompatibility=VmCompatibility::getInstance();
		$selected_flt=array();

		/* ids generated in the component and should be included in the query
		 * Storing them to the component we avoid duplicate work and the sql query becomes lighter*/
		$where_productIds=$this->input->get('where_productIds',array(),'array');

		//each range filter and search stores the found product ids in this assoc array
		$found_product_ids_per_filter=$this->input->get('found_product_ids',array(),'array');

		//all the product ids found from searches and range filters
		$returned_products=$this->componentparams->get('returned_products','parent');
		$filtered_products=$this->componentparams->get('filtered_products','parent');

		//if the filters are generated from child is the same as returning child. The only difference is the counter but this is handled in the subquery functions
		if($filtered_products=='child')$returned_products=$filtered_products;

		//if the dependency works from top to bottom, get the selected filters as stored in the "selected_fl_per_flt"
		if(isset($this->selected_fl_per_flt)){
			if(isset($this->selected_fl_per_flt[$field]))$selected_flt=$this->selected_fl_per_flt[$field];
			else $selected_flt=array();
		}

		//if the category is not reseting the keywords, then it should get keywords in account
		elseif($field=='virtuemart_category_id' && $this->moduleparams->get('category_flt_onchange_reset','filters')=='filters'){

			//reset that for the categories we do not want them to take other filters like price range into account
			$where_productIds=array();
			!empty($this->selected_flt['q'])?$selected_flt['q']=$this->selected_flt['q']:'';
		}
		else $selected_flt=$this->selected_flt;

		$api=JFactory::getApplication();
		$db=JFactory::getDbo();
		$query=$db->getQuery(true);
		$query=$this->buildQuery($query,$field,$customfilter,true);

		$is_customfield=strpos($field, 'custom_f_');
		$activeOptions=array();

		//the keys used in some arrays
		if($is_customfield!==false)$field_key='custom_f';
		else $field_key=$field;

		$innerJoins=array();
		$leftJoins=array();
		$where=array();
		$where_str='';
		$i=0;

		//the table names which are retreived by the $field var which is used as key
		$table_db_flds['virtuemart_category_id']='#__virtuemart_product_categories';
		$table_db_flds['virtuemart_manufacturer_id']='#__virtuemart_product_manufacturers';
		$table_db_flds['price']='#__virtuemart_product_prices';
		$table_db_flds[$vmCompatibility->getColumnName('virtuemart_custom_id')]='#__virtuemart_product_customfields';

		//if the field is a cucstomfield use that as the table name
		if($is_customfield!==false){
			$table_db_flds[$field]='cfp';
		}

		//iterate through the selected variables and join the relevant tables
		foreach($selected_flt as $key=>$ar_value){
			$wherecf=array();

			/*
			 * the query should run only if there are options selected in the filters
			 * other than the one we get as field param in that function
			 */
			if(count($ar_value)>0){
				if($key!=$field){

					/*
					 * if the key of the selected filters array is a customfield there are other rules
					 * This because the custom filers are stored in various arrays. Use a generated by the module name
					 * and also because they are stored as varchars in the db and we cannot use where in
					 */
					if(strpos($key, 'custom_f_')!==false){

						//get the filter id
						preg_match('/[0-9]+/', $key,$mathces);
						$custFltObj=$this->customFltActive[(int)$mathces[0]];

						//check if its range
						if($custFltObj->disp_type!=5 && $custFltObj->disp_type!=6 && $custFltObj->disp_type!=8){

							//not plugin
							if($custFltObj->field_type!='E'){
								$table_db_flds[$key]='#__virtuemart_product_customfields';
								$sel_field=$vmCompatibility->getColumnName('custom_value');

								foreach($ar_value as $av){
									$wherecf[]="{$key}.{$sel_field} =". $db->quote($av);
								}

								if(count($wherecf)>0)$where[]="((".implode(' OR ',$wherecf).") AND {$key}.".$vmCompatibility->getColumnName('virtuemart_custom_id')."=".(int)$mathces[0].")";
								$innerJoins[]="$table_db_flds[$key] AS $key ON {$key}.virtuemart_product_id=p.virtuemart_product_id";

							}else{

								//if the plugin has not declared the necessary params go to the next selected var
								if(empty($custFltObj->pluginparams))continue;

								//get vars from plugins
								$curSelectionTable=$custFltObj->pluginparams->product_customvalues_table;
								$sel_field=$custFltObj->pluginparams->filter_by_field;
								$filter_data_type=$custFltObj->pluginparams->filter_data_type;
								$wherecf=array();

								//if its string we need to escape and quote each value
								if($filter_data_type=='string'){
									foreach($ar_value as $av){
										$wherecf[]="{$key}.{$sel_field} =". $db->quote($av);
									}

									if(count($wherecf)>0){
										if($custFltObj->pluginparams->product_customvalues_table==$custFltObj->pluginparams->customvalues_table)$where[]='(('.implode(' OR ',$wherecf).") AND {$key}.$vmCompatibility->getColumnName('virtuemart_custom_id')=".(int)$mathces[0].")";
										else $where[]='('.implode(' OR ',$wherecf).")";
									}
								}else{

									//if they are in different tables we can use where in which is faster also we should sanitize the vars
									if($filter_data_type=='int' || $filter_data_type=='boolean' || $filter_data_type=='bool')JArrayHelper::toInteger($ar_value);
									elseif($filter_data_type=='float'){//sanitize the float numbers
										foreach($ar_value as &$av){
											$av=(float)$av;
										}
									}
									// if none of the above continue
									else continue;
									if(!empty($ar_value))$where[]="{$key}.{$sel_field} IN(".implode(',',$ar_value).")";
								}

								$innerJoins[]="$curSelectionTable AS $key ON {$key}.virtuemart_product_id=p.virtuemart_product_id";
							}
						}
					}

					//keyword
					elseif($key=='q'){

						//if the $where_productIds is not empty, then this var contains also the products from searches and will be added later
						if(!empty($where_productIds))continue;
						$product_ids_from_search=$found_product_ids_per_filter['search'];
						if(is_array($product_ids_from_search) && !empty($product_ids_from_search))$where[]="p.virtuemart_product_id IN(".implode(',', $product_ids_from_search).")";
						//empty set of products
						else return;
					}

					//other filters than customfilters but not product_price or keyword (i.e. categories, manufacturers)
					elseif($key!='price'){
						$sel_field=$key;
						$where[]="{$table_db_flds[$key]}.{$sel_field} IN (".implode(' ,',$ar_value).")";

						/*
						 * lookup for filters into the parent or the child products
						 * This is designated by the use of the $returned_products component setting
						 * Or by the custom_flt_lookup_in module setting for the custom filters
						 */
						if($returned_products=='child'){
							$innerJoins[]="$table_db_flds[$key] ON p.product_parent_id={$table_db_flds[$key]}.virtuemart_product_id";
						}elseif($returned_products=='parent')$innerJoins[]="$table_db_flds[$key] ON p.virtuemart_product_id={$table_db_flds[$key]}.virtuemart_product_id";
					}

				}
			}
		}

		//product ids that come from the component (range filters and seaeches)
		if(!empty($where_productIds)){

			/* If we are searching for price ranges, we should not take into account the products returned by the current price search, only by the other searches*/
			if($field=='price' && !empty($found_product_ids_per_filter['price'])){
				if(!isset($found_product_ids_per_filter['search']))$found_product_ids_per_filter['search']=array();
				if(!isset($found_product_ids_per_filter['ranges']))$found_product_ids_per_filter['ranges']=array();
				$where_productIds=array_merge($found_product_ids_per_filter['search'],$found_product_ids_per_filter['ranges']);
			}
			if(!empty($where_productIds))$where[]="p.virtuemart_product_id IN(".implode(',', $where_productIds).")";
		}

		if($where){

			//generate some db vars
			if($is_customfield!==false)
			{
				preg_match('/[0-9]+/', $field,$mathcess);
				if($customfilter->field_type!='E'){//not plugin
					$from_alias='cfp';
					$from='#__virtuemart_product_customfields AS '.$from_alias;
					$sqlfield='custom_value';
					$where[]='cfp.'.$vmCompatibility->getColumnName('virtuemart_custom_id','virtuemart_product_customfields').'='.(int)$mathcess[0];
				}

				//is plugin and has params
				elseif(isset($customfilter->pluginparams)){
					$from_alias='cfp';
					$from=$customfilter->pluginparams->customvalues_table.' AS '.$from_alias='cf';
					$sqlfield=$customfilter->pluginparams->filter_by_field;
					$where[]='cf.virtuemart_custom_id='.(int)$mathcess[0];
				}
			}else {
				$sqlfield=$field;
				$from=$table_db_flds[$field];
				$from_alias=$from;
			}

			$query->where(implode(' AND ',$where));
			if(count($innerJoins)>0)$query->innerJoin(implode(' INNER JOIN ', $innerJoins));
			if(count($leftJoins)>0)$query->leftJoin(implode(' LEFT JOIN ', $leftJoins));
		}

		$db->setQuery($query);
		$activeOpt=$db->loadObjectList();

		/*
		 * If $joinFieldData is true all the data are included in the $activeOpt
		 * so we have to handle them accordingly e.g. Create category levels or encode cf values
		*/
		if (! empty($activeOpt)) {
            if ($joinFieldData) {
                if ($field == 'virtuemart_category_id' && ! empty($activeOpt)) {

                } else
                    if ($is_customfield !== false && ! empty($activeOpt)) {
                        $sort_by = 'name';
                        if (($customfilter->is_list && ! empty($customfilter->custom_value)) || $customfilter->field_type == 'E')
                            $sort_by = 'default_values';
                        $activeOpt = $this->encodeOptions($activeOpt);
                        if ($sort_by == 'name')
                            $this->sort_by($sort_by, $activeOpt);
                    }
            } else {

                // convert to hexademical if custom filters
                if ($is_customfield !== false) {
                    $activeOptions = array();
                    if (is_array($activeOpt)) {
                        $activeOpt = $this->encodeOptions($activeOpt);
                    }
                }
            }
            if (! empty($activeOpt)) {
                $activeOptions = cftools::arrayFromValList($activeOpt);
            }
        }
		return $activeOptions;
	}

    /**
     * Get the categories
     *
     * @param
     *            Array Contains the ids of the categories we want to display
     * @param
     *            String
     *
     * @author Sakis Terz
     * @since 1.0
     */
    public function getCategories()
    {
        $caching = false;
        $cahche_id = '';
        $results = array();
        $subtree_parent_category_id=false;
        $displayCounterSetting = $this->moduleparams->get('category_flt_display_counter_results', 1);
        $on_category_reset_others = $this->moduleparams->get('category_flt_onchange_reset', 'filters');
        $selected_categories=!empty($this->selected_flt['virtuemart_category_id'])?$this->selected_flt['virtuemart_category_id']:array();

		/*
		 *if we should return only the subcats
		 *this should happen only when imposed by the corresponding setting in the module
		 *and the categories list is integral, unaffected by other selections
		 */
		if(
		    $this->moduleparams->get('category_flt_only_subcats', false) &&
		    (
		        ($this->moduleparams->get('category_flt_onchange_reset', 'filters')=='filters' && empty($this->selected_flt['q']))
		        || $this->moduleparams->get('category_flt_onchange_reset', 'filters')=='filters_keywords')
		    ) {
		        $subtree_parent_category_id=$this->getParentCategoryId($selected_categories);
		    }

        //the display order of the categories. If we display only the subcategories we do not need tree ordering.
        $cat_disp_order=!empty($subtree_parent_category_id) && $this->moduleparams->get('categories_disp_order')=='tree'?'ordering':$this->moduleparams->get('categories_disp_order');

        // if the category tree is always the same and has no countering then get a cached version
        if (($on_category_reset_others == 'filters_keywords' || ($on_category_reset_others == 'filters')) && empty($subtree_parent_category_id)) {
            $caching = true;
            $disp_vm_cat = $this->moduleparams->get('category_flt_disp_vm_cat', '');
            $excluded_vm_cat = $this->moduleparams->get('category_flt_exclude_vm_cat', '');
            $display_empty_opt = $this->moduleparams->get('category_flt_disable_empty_filters', '1');
            $q = ! empty($this->selected_flt['q']) ? $this->selected_flt['q'] : '';
            $cahche_id = serialize('dbObj' . $cat_disp_order . $disp_vm_cat . $excluded_vm_cat . $display_empty_opt . $displayCounterSetting . $q);

            //6 minutes if we have counter - 60 if we do not have
            $cacheTime=$displayCounterSetting?6:60;

            $cache = JFactory::getCache('mod_cf_filtering.categories', '');
            $cache->setCaching(true);
            $cache->setLifeTime($cacheTime);
            $results = $cache->get($cahche_id);
        }

        // runs when the cache is inactive or empty
        if (empty($results)) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query = $this->buildCategoriesQuery($query, $part=false, $subtree_parent_category_id);
            $db->setQuery($query);
            $dbresults = $db->loadObjectList();

            //we need the creation of levels only in trees
            if($cat_disp_order=='tree'){
                $elaborated_list = $this->createCatLevels($dbresults);
                $results=$elaborated_list;
            }
            else {
                //in case of subtree, set the parent at the top as a clear option, but not when checkboxes
                if(!empty($subtree_parent_category_id)) {
                   $handle='ontop';

                   //remove the parent category from top when checkboxes. Add the clear option
                   if($this->moduleparams->get('category_flt_disp_type','1')=='3')$handle='remove';
                   $dbresults=$this->_handleParentCategory($dbresults, $subtree_parent_category_id, $handle);
                }
                else $dbresults=cftools::arrayFromValList($dbresults);

                $results['options']=$dbresults;
                $results['maxLevel']=0;
            }
            if ($caching)
                $cache->store($results, $cahche_id);
        }
        return $results;
    }

	/**
	 * Build the query for the Categories
	 *
	 * @since	1.5.0
	 * @param	$query object The db query Object
	 * @param   $part  boolean
	 *
	 * @return 	object The db query Object
	 * @author	Sakis Terz
	 */
	public function buildCategoriesQuery($query, $part=false, $subtree=false)
	{
		$where=array();
		$where_str='';
		$innerJoin=array();
		$innerJoin_str='';
		$join_products=false;
		$subtree_parent_category_id=false;
		$join_product_lang=version_compare($this->vmVersion, '2.9','<');
		$selected_categories=!empty($this->selected_flt['virtuemart_category_id'])?$this->selected_flt['virtuemart_category_id']:array();

		/*
		 *if we should return only the subcats
		 */
		if($subtree) {
		  $subtree_parent_category_id=$this->getParentCategoryId($selected_categories);
		}

		//the display order of the categories. If we display only the subcategories we do not need tree ordering.
		$cat_disp_order=!empty($subtree_parent_category_id) && $this->moduleparams->get('categories_disp_order')=='tree'?'ordering':$this->moduleparams->get('categories_disp_order');

		//included categories
		$disp_vm_cat=$this->moduleparams->get('category_flt_disp_vm_cat','');

		//excluded categories
		$excluded_vm_cat=$this->moduleparams->get('category_flt_exclude_vm_cat','');

		$display_empty_opt=$this->moduleparams->get('category_flt_disable_empty_filters','1');

		//convert to array to sanitize data
		if(!empty($disp_vm_cat)){
			$cat_ids_ar=explode(',',$disp_vm_cat);
			JArrayHelper::toInteger($cat_ids_ar);
		}else $cat_ids_ar=array();

		//convert to array to sanitize data
		if(!empty($excluded_vm_cat)){
			$excluded_ids_ar=explode(',',$excluded_vm_cat);
			if(is_array($excluded_ids_ar))JArrayHelper::toInteger($excluded_ids_ar);
		}else $excluded_ids_ar=array();

		//counter
		$suffix=$this->fltSuffix['virtuemart_category_id'];
		$displayCounterSetting=$this->moduleparams->get($suffix.'_display_counter_results',1);

		/*
		 count results only when
		 the $displayCounterSetting is active and it is a part query (getActiveOptions)
		 or when all options are active (display type setting)
		 or the $displayCounterSetting is active and there is no selection
		 or when the $displayCounterSetting is active and the only selection is a category

		 //we don't want in any case to run the count both in the getActiveOptions and here
		 */
		if($displayCounterSetting){
			if($this->langPlugin && $join_product_lang)$table='plang';
			else $table='#__virtuemart_product_categories';

			//if return child products
			if($this->componentparams->get('returned_products','parent')=='child')$query->select("SUM(CASE WHEN p.product_parent_id>0 THEN 1 ELSE 0 END) AS counter");
			else {

				//return parents and generate filters from parents
				if($this->componentparams->get('filtered_products','parent')=='parent')$query->select("SUM(CASE WHEN p.product_parent_id=0 THEN 1 ELSE 0 END) AS counter");

				//return parents and generate filters from child
				else $query->select("COUNT(DISTINCT p.product_parent_id) AS counter");
			}
		}

		//if not part join the category products and the products_lang in case of multi-language site
		if($displayCounterSetting || $part){
			$parents_sql='';

			//get the parents if exist.Parents should be always displayed, otherwise the tree will be incomprehensive
            if($cat_disp_order=='tree'){
    			if(!isset($db))$db=JFactory::getDbo();
    			$myQuery=$db->getQuery(true);
    			$myQuery->select('DISTINCT cx.category_parent_id');
    			$myQuery->from('#__virtuemart_category_categories AS cx');
    			$myQuery->innerJoin('#__virtuemart_categories AS c ON cx.category_parent_id=c.virtuemart_category_id');
    			$myQuery->where('cx.category_parent_id>0 AND c.published=1');
    			$db->setQuery($myQuery);
    			$parents=$db->loadColumn();
    			if(!empty($parents)){
    				$parents=implode(',',$parents);
    				$parents_sql=" OR vc.virtuemart_category_id IN($parents)";
    			}
             }

			//join with the language tables
			$query->leftJoin("#__virtuemart_product_categories ON langt.virtuemart_category_id=#__virtuemart_product_categories.virtuemart_category_id");
			if($this->langPlugin && $join_product_lang){
				$query->leftJoin("`#__virtuemart_products_".JLANGPRFX."` AS plang ON #__virtuemart_product_categories.virtuemart_product_id = plang.`virtuemart_product_id`");
			}

			//join the products table to check for unpublished
			if($this->componentparams->get('returned_products','parent')=='child' || $this->componentparams->get('filtered_products','parent')=='child')$query->leftJoin("`#__virtuemart_products` AS p ON #__virtuemart_product_categories.virtuemart_product_id = p.`product_parent_id`");
			else $query->leftJoin("`#__virtuemart_products` AS p ON #__virtuemart_product_categories.virtuemart_product_id = p.`virtuemart_product_id`");

			//stock control
			if(!VmConfig::get('use_as_catalog',0)) {
				if (VmConfig::get('stockhandle','none')=='disableit_children') {
					$where[] = '((p.published=1 AND (p.`product_in_stock` - p.`product_ordered` >0 OR children.`product_in_stock` - children.`product_ordered` >0))'.$parents_sql.')';
					$query->leftJoin('`#__virtuemart_products` AS children ON p.`virtuemart_product_id` = children.`product_parent_id`');
				} elseif (VmConfig::get('stockhandle','none')=='disableit') {
					$where[] = '((p.published=1 AND(p.`product_in_stock` - p.`product_ordered` >0))'.$parents_sql.')';
				}else $where[]="(p.published=1 $parents_sql)";
			}else $where[]="(p.published=1 $parents_sql)";

			//use of shopper groups
			if(count($this->shopperGroups)>0 && $this->componentparams->get('products_multiple_shoppers',0)){

				$query->leftJoin("(SELECT #__virtuemart_product_categories.virtuemart_product_id,
					CASE WHEN (s.`virtuemart_shoppergroup_id` IN(".implode(',', $this->shopperGroups).") OR  (s.`virtuemart_shoppergroup_id`) IS NULL) THEN 1 ELSE 0 END AS `virtuemart_shoppergroup_id`
					FROM `#__virtuemart_product_shoppergroups` AS s
					RIGHT JOIN #__virtuemart_product_categories ON #__virtuemart_product_categories.virtuemart_product_id =s.virtuemart_product_id
					WHERE
					(s.`virtuemart_shoppergroup_id` IN(".implode(',', $this->shopperGroups).") OR  (s.`virtuemart_shoppergroup_id`) IS NULL)
					GROUP BY #__virtuemart_product_categories.virtuemart_product_id
					) AS sp
					ON  #__virtuemart_product_categories.virtuemart_product_id=sp.virtuemart_product_id");

				$where[]="(sp.virtuemart_shoppergroup_id=1 $parents_sql)";
			}
			$query->group('cx.category_child_id');
		}

		$innerJoin[]="#__virtuemart_category_categories AS cx ON cx.category_child_id=langt.virtuemart_category_id ";

		//where
		$cat_ids=implode(',',$cat_ids_ar);
		if (!empty($cat_ids)){
			$where[]="langt.virtuemart_category_id IN(".$cat_ids.")";
		}

		$excluded_cat_ids=implode(',',$excluded_ids_ar);
		if (!empty($excluded_cat_ids)){
			$where[]="langt.virtuemart_category_id NOT IN(".$excluded_cat_ids.")";
		}

		if (!empty($subtree_parent_category_id)){
		    $where[]="(cx.category_child_id=".(int)$subtree_parent_category_id." OR cx.category_parent_id=".(int)$subtree_parent_category_id.')';
		}


		//define the categories order by and some other vars
		switch ($cat_disp_order){
			case 'ordering':
				$orderBy='vc.ordering, name';
				$fields='';
				break;
			case 'names':
				$orderBy='name';
				$fields='';
				break;
			case 'tree':
				$orderBy='cx.category_parent_id,vc.ordering';
				$fields=',cx.category_parent_id ,cx.category_child_id';
				break;
		}

		//published only
		$innerJoin[]="#__virtuemart_categories AS vc ON vc.virtuemart_category_id=langt.virtuemart_category_id";
		$where[]='vc.published=1';

		if(count($innerJoin)>0)$query->innerJoin(implode(" INNER JOIN ",$innerJoin));
		if(count($where)>0)$query->where(implode(" AND ",$where));

		//format the final query
		$query->select("langt.category_name AS name, langt.virtuemart_category_id AS id $fields");
		$query->from( "#__virtuemart_categories_".JLANGPRFX." AS langt");
		$query->order($orderBy);
		return $query;
	}

	/**
	 * Detects and returns the parent category of the current subtree based on the selections
	 * The parent category is not necessarily the actual parent of the cuurent category but the parent of the categories that should be displayed (subtree)
	 *
	 * @param array $categories
	 * @return boolean|mixed
	 */
	public function getParentCategoryId($categories)
	{
        if(empty($categories) || !is_array($categories))return false;
        $category_id=reset($categories);
        $key=md5($category_id);

        if(empty(self::$parent_categories[$key])){
            $category=$this->_getCategory($category_id);

            $hasSubCategories=$this->_hasSubCategories($category_id);
            if(!empty($hasSubCategories))$parent_category_id=$category_id;
            else $parent_category_id=$category->category_parent_id;
            self::$parent_categories[$key]=$parent_category_id;
        }
        return self::$parent_categories[$key];
	}

	/**
	 * Get a category by it's id
	 *
	 * @param  int $id
	 * @return mixed
	 * @since   2.3.1
	 */
	private function _getCategory($id)
	{
        $db=JFactory::getDbo();
        $q=$db->getQuery(true);
        $q->select('*')->from('#__virtuemart_category_categories')->where('category_child_id='.(int)$id);
        $db->setQuery($q);
        $result=$db->loadObject();
        return $result;
	}

	/**
	 * Detect if a category is parent / has sub categories
	 * @param int $id
	 *
	 * @return mixed
	 * @since   2.3.1
	 */
	private function _hasSubCategories($id)
	{
	    $db=JFactory::getDbo();
	    $q=$db->getQuery(true);
	    $q->select('category_child_id')->from('#__virtuemart_category_categories')->where('category_parent_id='.(int)$id)->setLimit(1);
	    $db->setQuery($q);
	    $result=$db->loadResult();
	    return $result;
	}

	/**
	 * Gets an array and puts an element at the start of it
	 *
	 * @param array $categories
	 * @param int $parent_id - the id of the parent category
	 *
	 * @return array
	 * @since   2.3.1
	 */
	private function _handleParentCategory($categories, $parent_id, $handle='ontop')
	{
	    $newArray=array();
        foreach($categories as $category) {
            $category->name=htmlspecialchars($category->name, ENT_COMPAT, 'UTF-8');

            if($category->id==$parent_id){

                //put parent it on top
                if($handle=='ontop'){
                    $category->name=JText::sprintf('MOD_CF_ANY_HEADER', $category->name);
                    $category->isparent=true;
                    $category->counter=0;
                    $newArray[0]=$category; //set this at top
                }
                //ignore parent
            }

            else $newArray[$category->id]=$category;
        }
        return $newArray;
	}

	/**
	 * Set categories to levels and order them appropriately
	 *
	 * @param 	array $categArray
	 *
	 * @return	array	The categories
	 * @since	1.0
	 */
	public function createCatLevels($categArray)
	{
		if(empty($categArray)) return;
		$maxLevel=0;
		$disp_vm_cat=$this->moduleparams->get('category_flt_disp_vm_cat','');
		$category_flt_disp_type=$this->moduleparams->get('category_flt_disp_type','1');

		//convert to array to sanitize data
		if(!empty($disp_vm_cat)){
			$cat_ids_ar=explode(',',trim($disp_vm_cat));
			JArrayHelper::toInteger($cat_ids_ar);
		}

		//excluded categories
		$excluded_vm_cat=$this->moduleparams->get('category_flt_exclude_vm_cat','');

		//convert to array to sanitize data
		if(!empty($excluded_vm_cat)){
			$excluded_ids_ar=explode(',',$excluded_vm_cat);
			if(is_array($excluded_ids_ar))JArrayHelper::toInteger($excluded_ids_ar);
		}else $excluded_ids_ar=array();

		$cat_disp_order=$this->moduleparams->get('categories_disp_order');

		//create the tree
		if(empty($cat_ids_ar) && $cat_disp_order=='tree'){

			$results=$this->orderVMcats($categArray,$excluded_ids_ar);
			if(empty($results))return;
			$levels=$this->getVmCatLevels($results);

			//add the spaces
			foreach($results as $key=>&$cat){
				$cat->level=$levels[$key];
				$cat->name=$cat->name;
				if($levels[$key]>$maxLevel)$maxLevel=$levels[$key];
				//add the blanks only when drop-down lists
				if($category_flt_disp_type==1){
					for($i=0; $i<$levels[$key]; $i++){
						$cat->name='&nbsp;&nbsp;'.$cat->name;	//add the blanks
					}
				}
			}
		}

		//when no tree
		else {
			//the returned array should be assoc with key the cat id
			foreach($categArray as $cat){
				$cat->name=$cat->name;
				$results[$cat->id]=$cat;
			}
		}
		$finalArray['options']=$results;
		$finalArray['maxLevel']=$maxLevel;
		return $finalArray;
	}

	/**
	 *creates indentation according to the categories hierarhy
	 *
	 * @param 	array $categoryArr
	 * @return  array
	 */
	public function getVmCatLevels($results)
	{
		if(!$results)return;
		$blank=0;
		$blanks=array();
		$blanks[0]=0;

		foreach($results as $res){
			if(!empty($blanks[$res->category_parent_id]))$blanks[$res->category_child_id]=$blanks[$res->category_parent_id];
			else $blanks[$res->category_child_id]=0;
			$blanks[$res->category_child_id]+=1;
		}

		//set the levels - removing them by 1 (1st should be zero)
		foreach($blanks as &$bl){
			$bl-=1;
		}
		return $blanks;
	}

	/**
	 * Order the categories to create the tree
	 *
	 * @param 	array $categoryArr
	 * @param 	array $excluded_categ
	 *
	 * @return	mixed	array on success, boolean on failure
	 */
	public function orderVMcats(&$categoryArr,$excluded_categ)
	{
		// Copy the Array into an Array with auto_incrementing Indexes

		$categCount=count($categoryArr);
		if($categCount>0){
			for($i=0; $i<$categCount; $i++){
				$resultsKey[$categoryArr[$i]->category_child_id]=$categoryArr[$i];
			}
			$key = array_keys($resultsKey); // Array of category table primary keys
			$nrows = $size = sizeOf($key); // Category count

			// Order the Category Array and build a Tree of it
			$id_list = array();
			$row_list = array();
			$depth_list = array();

			$children = array();
			$parent_ids = array();
			$parent_ids_hash = array();

			//Build an array of category references
			$category_temp = array();
			for ($i=0; $i<$size; $i++)
			{
				$category_tmp[$i] = &$resultsKey[$key[$i]];
				$parent_ids[$i] = $category_tmp[$i]->category_parent_id;

				if($category_tmp[$i]->category_parent_id == 0 || in_array($category_tmp[$i]->category_parent_id, $excluded_categ))
				{
					array_push($id_list,$category_tmp[$i]->category_child_id);
					array_push($row_list,$i);
					array_push($depth_list,0);
				}

				$parent_id = $parent_ids[$i];

				if (isset($parent_ids_hash[$parent_id]))
				{
					$parent_ids_hash[$parent_id][$i] = $parent_id;

				}
				else
				{
					$parent_ids_hash[$parent_id] = array($i => $parent_id);

				}
			}
			$loop_count = 0;
			$watch = array(); // Hash to store children
			while(count($id_list) < $nrows) {
				if( $loop_count > $nrows )break;
				$id_temp = array();
				$row_temp = array();

				for($i = 0 ; $i < count($id_list) ; $i++) {
					$id = $id_list[$i];
					$row = $row_list[$i];
					if (isset($parent_ids_hash[$id]) && $id>0){$resultsKey[$id]->isparent=true;}
					array_push($id_temp,$id);
					array_push($row_temp,$row);

					if (isset($parent_ids_hash[$id]))
					{	$children = $parent_ids_hash[$id];
					foreach($children as $key => $value) {
						if( !isset($watch[$id][$category_tmp[$key]->category_child_id])) {
							$watch[$id][$category_tmp[$key]->category_child_id] = 1;
							$category_tmp[$key]->isparent=false;
							array_push($id_temp,$category_tmp[$key]->category_child_id);
							array_push($row_temp,$key);
						}
					}
					}
				}
				$id_list = $id_temp;
				$row_list = $row_temp;
				$loop_count++;
			}
			$orderedArray=array();
			for($i=0; $i<count($resultsKey); $i++){
				if(isset($id_list[$i]) && isset($resultsKey[$id_list[$i]])){
					$parent_id=$resultsKey[$id_list[$i]]->category_parent_id;

					if($parent_id==0){
						$resultsKey[$id_list[$i]]->cat_tree=$parent_id;
					}
					else {
						if(isset($resultsKey[$parent_id]->cat_tree))$parent_tree=$resultsKey[$parent_id]->cat_tree;
						else $parent_tree='0';

						$parent_tree.='-'.$parent_id;
						$resultsKey[$id_list[$i]]->cat_tree=$parent_tree;
					}
					$orderedArray[$id_list[$i]]=$resultsKey[$id_list[$i]];
				}
			}
			return $orderedArray;
		}return;
	}

	/**
	 * Gets the options for the manufacturers
	 *
	 * @return 	array	A list of objects with the available options
	 * @since	1.0
	 * @author	Sakis Terz
	 */
	public function getManufacturers()
	{
		$db=JFactory::getDbo();
		$query=$db->getQuery(true);
		$query=$this->buildManufQuery($query);
		$db->setQuery($query);
		//print_r((string)$query);
		$manufs=cftools::arrayFromValList($db->loadObjectList());
		return $manufs;
	}

	/**
	 * Build the query for the manufacturers
	 *
	 * @param	$query object The db query Object
	 * @param	$part Boolean	Indicates if this is a query part or the whole query
	 *
	 * @return 	object The db query Object
	 * @author	Sakis Terz
	 * @since	1.5.0
	 */
	public function buildManufQuery($query, $part=false)
	{
		$suffix=$this->fltSuffix['virtuemart_manufacturer_id'];
		$displayCounterSetting=$this->moduleparams->get($suffix.'_display_counter_results',1);
		$display_type=$this->moduleparams->get($suffix.'_disp_type',1);
		$query->innerJoin("#__virtuemart_manufacturers AS vm ON vm.virtuemart_manufacturer_id=langt.virtuemart_manufacturer_id");
		//use the media only if image link
		if($display_type==7)$query->leftJoin("#__virtuemart_manufacturer_medias AS manuf_med ON vm.virtuemart_manufacturer_id=manuf_med.virtuemart_manufacturer_id");
		$display_empty_opt=$this->moduleparams->get('manuf_flt_disable_empty_filters','1');
		$join_product_lang=version_compare($this->vmVersion, '2.9','<');

		/*
		 count results only when
		 the $displayCounterSetting is active
		 */
		if($displayCounterSetting){
			if($this->langPlugin && $join_product_lang)$table='plang';
			else $table='#__virtuemart_product_manufacturers';

			//if return child products
			if($this->componentparams->get('returned_products','parent')=='child')$query->select("SUM(CASE WHEN p.product_parent_id>0 THEN 1 ELSE 0 END) AS counter");
			else {

				//return parents and generate filters from parents
				if($this->componentparams->get('filtered_products','parent')=='parent')$query->select("SUM(CASE WHEN p.product_parent_id=0 THEN 1 ELSE 0 END) AS counter");

				//return parents and generate filters from child
				else $query->select("COUNT(DISTINCT p.product_parent_id) AS counter");
			}

			$query->group('langt.virtuemart_manufacturer_id');
		}


		if($displayCounterSetting || $part){
			$query->leftJoin("#__virtuemart_product_manufacturers ON langt.virtuemart_manufacturer_id=#__virtuemart_product_manufacturers.virtuemart_manufacturer_id");
			if($this->langPlugin && $join_product_lang){
				$query->leftJoin("`#__virtuemart_products_".JLANGPRFX."` AS plang ON #__virtuemart_product_manufacturers.virtuemart_product_id = plang.`virtuemart_product_id`");
			}

			//join the products table to check for unpublished
			if($this->componentparams->get('returned_products','parent')=='child' || $this->componentparams->get('filtered_products','parent')=='child')$query->innerJoin("`#__virtuemart_products` AS p ON #__virtuemart_product_manufacturers.virtuemart_product_id = p.`product_parent_id`");
			else $query->innerJoin("`#__virtuemart_products` AS p ON #__virtuemart_product_manufacturers.virtuemart_product_id = p.`virtuemart_product_id`");

			//stock control
			if(!VmConfig::get('use_as_catalog',0)) {
				if (VmConfig::get('stockhandle','none')=='disableit_children') {
					$query->where('(p.`product_in_stock` - p.`product_ordered` >0 OR children.`product_in_stock` - children.`product_ordered` >0)');
					$query->leftJoin('`#__virtuemart_products` AS children ON p.`virtuemart_product_id` = children.`product_parent_id`');
				} elseif (VmConfig::get('stockhandle','none')=='disableit') {
					$query->where('(p.`product_in_stock` - p.`product_ordered` >0)');
				}
			}
			//use of shopper groups
			if(count($this->shopperGroups)>0 && $this->componentparams->get('products_multiple_shoppers',0)){
				$query->innerJoin("(SELECT #__virtuemart_product_manufacturers.virtuemart_product_id,s.`virtuemart_shoppergroup_id` FROM `#__virtuemart_product_shoppergroups` AS s
					RIGHT JOIN #__virtuemart_product_manufacturers ON #__virtuemart_product_manufacturers.virtuemart_product_id =s.virtuemart_product_id WHERE
					(s.`virtuemart_shoppergroup_id` IN(".implode(',', $this->shopperGroups).") OR (s.`virtuemart_shoppergroup_id`) IS NULL) GROUP BY #__virtuemart_product_manufacturers.virtuemart_product_id) AS sp
					ON  #__virtuemart_product_manufacturers.virtuemart_product_id=sp.virtuemart_product_id");
			}
			$query->where(" p.published=1");
		}

		$query->select("langt.virtuemart_manufacturer_id AS id, langt.mf_name AS name");
		if($display_type==7)$query->select("manuf_med.virtuemart_media_id AS media_id");
		$query->from("#__virtuemart_manufacturers_".JLANGPRFX." AS langt");
		$query->where("vm.published=1");
		$query->order("name ASC");
		return $query;
	}

	/**
	 * Sort the options in ascending order
	 * Options may translated in other languages, so they need to be translated
	 * @param	Array	An object array with the options
	 * @return	Array
	 * @since	1.1
	 * @author	Sakis Terz
	 */
	public function sort_by($field='name', &$arr, $sorting=SORT_ASC, $case_insensitive=false)
	{
		if(is_array($arr) && (count($arr)>0) ){
			if($case_insensitive==true) $strcmp_fn = "strnatcasecmp";
			else $strcmp_fn = "strnatcmp";

			if($sorting==SORT_ASC){
				$fn = create_function('$a,$b', '
                    if(is_object($a) && is_object($b)){
                        return '.$strcmp_fn.'($a->'.$field.', $b->'.$field.');
                    }elseif(is_array($a) && is_array($b)){
                        return '.$strcmp_fn.'($a["'.$field.'"], $b["'.$field.'"]);
                    }else return 0;
                ');
			}else{
				$fn = create_function('$a,$b', '
                    if(is_object($a) && is_object($b)){
                        return '.$strcmp_fn.'($b->'.$field.', $a->'.$field.');
                    }elseif(is_array($a) && is_array($b)){
                        return '.$strcmp_fn.'($b["'.$field.'"], $a["'.$field.'"]);
                    }else return 0;
                ');
			}
			usort($arr, $fn);
			//sort correctly - usort removes the array keys
			$arr=cftools::arrayFromValList($arr);
			return true;
		}else{
			return false;
		}
	}
}
