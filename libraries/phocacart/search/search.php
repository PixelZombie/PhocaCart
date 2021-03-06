<?php
/**
 * @package   Phoca Cart
 * @author    Jan Pavelka - https://www.phoca.cz
 * @copyright Copyright (C) Jan Pavelka https://www.phoca.cz
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 and later
 * @cms       Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die();

class PhocacartSearch
{
	public function __construct() {}
	
	
	public function renderSearch($options = array()) {
		
		$o						= array();
		$app					= JFactory::getApplication();
		$data['id'] 			= 'phSearch';
		$data['param'] 			= 'search';
		$data['getparams']		= htmlspecialchars($app->input->get('search', '', 'string'));
		$data['title']			= JText::_('COM_PHOCACART_SEARCH');
		$category				= PhocacartRoute::getIdForItemsRoute();
		//$data['getparams'][]	= $category['idalias'];
		$data['activefilter']	= PhocacartRoute::isFilterActive();
		//$data['searchoptions']	= $searchOptions;
		if (!empty($options)) {
			foreach($options as $k => $v) {
				$data[$k] = $v;
			}
		}
		//$app		= JFactory::getApplication();
		$layout 	= new JLayoutFile('form_search', null, array('component' => 'com_phocacart'));
		$o[] = $layout->render($data);
		$o2 = implode("\n", $o);
		return $o2;
	}
	
	/* Static part */
	
	public static function getSqlParts($type, $search, $param) {
	
		$in 	= '';
		$where 	= '';
		$left	= '';
		$db		= JFactory::getDBO();
	
		switch($type) {
			case 'int':
				
				$w 		= $param;
				//$w		= str_replace('%2C', ',', $w);
				$a 		= explode(',', $w);
				
				$inA 	= array();
				if (!empty($a)) {
					foreach($a as $k => $v) {
						$inA[] = (int)$v;
					}
				}
			
				$in = implode(',', $inA);
			break;
			
			case 'string':
				$in = $param;
			break;
			
			case 'array':
				$w	= $param;
				$inA 	= array();
				
				if (!empty($w)) {
					foreach ($w as $k => $v) {
						$s		= '';
						//$v		= str_replace('%2C', ',', $v);
						$a 		= explode(',', $v);
						if ($k != '' && $v != '' && !empty($a)) {
							if ($search == 'a') {
								// Attributes
								$inA[] = '(at2.alias = '.$db->quote($k). ' AND v2.alias IN ('. '\'' . implode($a, '\',\''). '\'' .'))';
							} else if ($search == 's') {
								// Specifications
								$inA[] = '(s2.alias = '.$db->quote($k). ' AND s2.alias_value IN ('. '\'' . implode($a, '\',\''). '\'' .'))';
							}
							
						}
					}
				}
				$in = $inA;
				
	
			
			break;
			
			default:
			break;
		}
		

		
		if ($in != '') {
			switch($search) {
				case 'tag':
					$where 	= ' tr.tag_id IN ('.$in.')';
					$left 	= ' LEFT JOIN #__phocacart_tags_related AS tr ON a.id = tr.item_id';
				break;
				
				case 'manufacturer':
					$where 	= ' m.id IN ('.$in.')';
					$left 	= ' LEFT JOIN #__phocacart_manufacturers AS m ON m.id = a.manufacturer_id';
				break;
				
				case 'price_from':
				case 'price_to':
					$currency	= PhocacartCurrency::getCurrency();
					$price		= PhocacartPrice::convertPriceCurrentToDefaultCurrency($in, $currency->exchange_rate );
					
					if ($search == 'price_from') {
						$where 	= ' a.price >= '.$db->quote($price);
					} else {
						$where 	= ' a.price <= '.$db->quote($price);
					};
				break;
				
				case 'id': // Category
					$where 	= ' c.id IN ('.$in.')';
					$left 	= '';//' LEFT JOIN #__phocacart_categories AS c ON c.id = a.catid';// Category always included
				break;
				
				case 'c': // Category
					$where 	= ' c.id IN ('.$in.')';
					$left 	= '';//' LEFT JOIN #__phocacart_categories AS c ON c.id = a.catid';// Category always included
				break;
				
				case 'a': // Attributes
					
					$where  = '';
					if (!empty($in)) {
						$c = count($in);
						$where 	= ' a.id IN (SELECT at2.product_id FROM #__phocacart_attributes AS at2'
						.' LEFT JOIN  #__phocacart_attribute_values AS v2 ON v2.attribute_id = at2.id'
						.' WHERE ' . implode( ' OR ', $in )
						.' GROUP BY at2.product_id'
						//.' HAVING COUNT(distinct at2.alias) >= '.(int)$c.')';// problematic on some servers
						.' HAVING COUNT(at2.alias) >= '.(int)$c
						.')';
					}
					$left 	= '';
				break;
				
				case 's': // Specifications
					
					$where  = '';
					
					if (!empty($in)) {
						$c = count($in);
						$where 	= ' a.id IN (SELECT s2.product_id FROM #__phocacart_specifications AS s2'
						.' WHERE ' . implode( ' OR ', $in )
						.' GROUP BY s2.product_id'
						//.' HAVING COUNT(distinct s2.alias) >= '.(int)$c.')';// problematic on some servers
						.' HAVING COUNT(s2.alias) >= '.(int)$c
						.')';
					}
					
					$left 	= '';
				break;
				
				case 'search': // Search
					
					$phrase = 'any';// exact, any - different methods can be implemented in future
					$where	= '';
					switch ($phrase) {
						case 'exact':
							$text		= $db->quote('%'.$db->escape($in, true).'%', false);
							$wheres	= array();
							$wheres[]	= 'a.title LIKE '.$text;
							$wheres[]	= 'a.alias LIKE '.$text;
							$wheres[]	= 'a.metakey LIKE '.$text;
							$wheres[]	= 'a.metadesc LIKE '.$text;
							$wheres[]	= 'a.description LIKE '.$text;
							$wheres[]	= 'a.sku LIKE '.$text;
							$where		= '(' . implode(') OR (', $wheres) . ')';
							$left 		= '';
						break;

						case 'all':
						case 'any':
						default:
						
							$words	= explode(' ', $in);
							$wheres = array();
							foreach ($words as $word) {
								
								if (!$word = trim($word)) {
									continue;
								}
								
								$word		= $db->quote('%'.$db->escape($word, true).'%', false);
								$wheres	= array();
								$wheres[]	= 'a.title LIKE '.$word;
								$wheres[]	= 'a.alias LIKE '.$word;
								$wheres[]	= 'a.metakey LIKE '.$word;
								$wheres[]	= 'a.metadesc LIKE '.$word;
								$wheres[]	= 'a.description LIKE '.$word;
								$wheres[]	= 'a.sku LIKE '.$word;
								$wheres[]	= implode(' OR ', $wheres);
							}
							$where	= '(' . implode(($phrase == 'all' ? ') AND (' : ') OR ('), $wheres) . ')';
							$left 	= '';
						break;
					}
				break;
				
				default:
				break;
			}
		}
		
		$a			= array();
		$a['where'] = $where;
		$a['left']	= $left;
		
	
		return $a;
		
	}
	
}