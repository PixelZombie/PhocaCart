<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class PhocaCartRelated
{
	public static function storeRelatedItemsById($relatedString, $productId) {
	
		
		if ((int)$productId > 0) {
			$db =JFactory::getDBO();
			$query = ' DELETE '
					.' FROM #__phocacart_product_related'
					. ' WHERE product_a = '. (int)$productId;
			$db->setQuery($query);
			$db->execute();
			
			if (isset($relatedString) && $relatedString != '') {
				
				$relatedArray 	= explode(",", $relatedString);
				$values 		= array();
				$valuesString 	= '';
				
				foreach($relatedArray as $k => $v) {
					$values[] = ' ('.(int)$productId.', '.(int)$v.')';
				}
				
				if (!empty($values)) {
					$valuesString = implode($values, ',');
				
					$query = ' INSERT INTO #__phocacart_product_related (product_a, product_b)'
								.' VALUES '.(string)$valuesString;

					$db->setQuery($query);
					$db->execute();
				}
			}
		}
	}
	
	/*
	* Try to find the best menu link so we search for category which we are located
	* if we find the category, we use this, if not we use another if accessible, etc.
	*/
	
	public static function getRelatedItemsById($productId, $select = 0, $frontend = 0) {
	
		$db 		= JFactory::getDBO();
		$wheres		= array();
		$wheres[] 	= 't.product_a = '.(int) $productId;
		$catid		= 0;
		
		if ($frontend) {
			$user 		= JFactory::getUser();
			$userLevels	= implode (',', $user->getAuthorisedViewLevels());
		
			$wheres[] = " c.access IN (".$userLevels.")";
			$wheres[] = " a.access IN (".$userLevels.")";
			$wheres[] = " c.published = 1";
			$wheres[] = " a.published = 1";
			
			$catid	= PhocaCartCategoryMultiple::getCurrentCategoryId();

		}
		
		if ($select == 1) {
			$query = ' SELECT t.product_b';
		} else {
			$query = ' SELECT a.id as id, a.title as title, a.image as image, a.alias as alias,'
					.' c.id as catid, c.alias as catalias, c.title as cattitle';
		}
		if ((int)$catid > 0) {
			$query .= ', ';
			$query .= ' GROUP_CONCAT(c2.id) AS catid2, GROUP_CONCAT(c2.alias) AS catalias2, GROUP_CONCAT(c2.title) AS cattitle2';
		}
		
		if (!$frontend) {
			$query .= ', ';
			$query .= ' GROUP_CONCAT(c.title SEPARATOR " ") AS categories_title';
		}
			
		$query .= ' FROM #__phocacart_products AS a'
				.' LEFT JOIN #__phocacart_product_related AS t ON a.id = t.product_b'
			  //.' LEFT JOIN #__phocacart_categories AS c ON a.catid = c.id'
			    .' LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id = a.id'
				.' LEFT JOIN #__phocacart_categories AS c ON c.id = pc.category_id';
		if ((int)$catid > 0) {
			$query .= ' LEFT JOIN #__phocacart_categories AS c2 ON c2.id = pc.category_id and pc.category_id = '. (int)$catid;
		}
		
		$query .= ' WHERE ' . implode( ' AND ', $wheres )
				. ' GROUP BY a.id';
				
		$db->setQuery($query);
		
		if ($select == 1) {
			$related = $db->loadColumn();
		} else {
			$related = $db->loadObjectList();
		}

		return $related;
	}
}