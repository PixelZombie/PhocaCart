<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
$app 		= JFactory::getApplication();
$d 			= $displayData;
$price		= new PhocacartPrice();
$msgSuffix	= '<span id="ph-msg-ns" class="ph-hidden"></span>';

$p['tax_calculation']					= $d['params']->get( 'tax_calculation', 0 );
$p['stock_checkout']					= $d['params']->get( 'stock_checkout', 0 );
$p['stock_checking']					= $d['params']->get( 'stock_checking', 0 );
$p['display_discount_product']			= $d['params']->get( 'display_discount_product', 1 );
$p['display_discount_price_product']	= $d['params']->get( 'display_discount_price_product', 1 );
$p['zero_shipping_price_calculation']	= $d['params']->get( 'zero_shipping_price_calculation', 0 );
$p['zero_payment_price_calculation']	= $d['params']->get( 'zero_payment_price_calculation', 0 );
$p['display_reward_points_receive_info']= $d['params']->get( 'display_reward_points_receive_info', 0 );
//$p['min_quantity_calculation']	= $d['params']->get( 'min_quantity_calculation', 0 ); set in product xml - product options, not in global

// POS
$task 			= $d['pos'] == true ? 'pos.update' : 'checkout.update';
$inputNumber	= $d['pos'] == true ? 'number' : 'text';
$displayTax		= true;// Specific settings for POS - to make smaller widht of cart

// A) MINIMUM QUANTITY FOR GROUPS - MAIN PRODUCT
if (!empty($d['fullitemsgroup'][0])) {
	foreach($d['fullitemsgroup'][0] as $k => $v) {
		
		if (isset($v['minqtyvalid']) && $v['minqtyvalid'] == 0) {
			echo '<div class="alert alert-error">'.JText::_('COM_PHOCACART_MINIMUM_ORDER_QUANTITY_FOR_PRODUCT'). ' '.$v['title']. ' '.JText::_('COM_PHOCACART_IS').': '.$v['minqty']. $msgSuffix .'</div>';
		
		}
		
		if (isset($v['minmultipleqtyvalid']) && $v['minmultipleqtyvalid'] == 0) {
			echo '<div class="alert alert-error">'.JText::_('COM_PHOCACART_MINIMUM_MULTIPLE_ORDER_QUANTITY_FOR_PRODUCT'). ' '.$v['title']. ' '.JText::_('COM_PHOCACART_IS').': '.$v['minmultipleqty']. $msgSuffix .'</div>';
		
		}
	}
}

if (!empty($d['fullitems'][1])) {
	
	$r		= 'row';
	$cA 	= 'col-sm-12 col-md-12 col-xs-12';// whole row
	$cI 	= 'col-sm-2 col-md-2 col-xs-2';// image
	$cQ		= 'col-sm-2 col-md-2 col-xs-2';// quantity
	$cN 	= 'col-sm-2 col-md-2 col-xs-2';// netto
	$cT 	= 'col-sm-2 col-md-2 col-xs-2';// tax
	$cB 	= 'col-sm-2 col-md-2 col-xs-2';// brutto
	$cV		= ' ph-vertical-align';
	$cVRow	= ' ph-vertical-align-row';
	$cAT	= 'col-sm-10 col-md-10 col-xs-10';// attributes
	
	// Total summarization
	$cTotE = 'col-sm-6 col-md-6 col-xs-6'; // empty space
	$cTotT = 'col-sm-4 col-md-4 col-xs-4'; // title
	$cTotB = 'col-sm-2 col-md-2 col-xs-2'; // price
	if ((int)$p['tax_calculation'] > 0) {
		$cP 	= 'col-sm-2 col-md-2 col-xs-2';// - 4 (Tax, Netto)
	} else {
		$cP 	= 'col-sm-6 col-md-6 col-xs-6';// + 4 (Tax, Netto)
	}
	
	if ($d['pos']) {
		
		// HIDE TAX for POS
		$displayTax = false;
		
		$cI 	= 'col-sm-0 col-md-0 col-xs-0';// image (display: none in css)
		//$cQ		= 'col-sm-3 col-md-3 col-xs-3';// quantity
		if ((int)$p['tax_calculation'] > 0 && $displayTax) {
			$cP 	= 'col-sm-2 col-md-2 col-xs-2';// - 4 (Tax, Netto)
		} else {
			$cP 	= 'col-sm-6 col-md-6 col-xs-6';// + 4 (Tax, Netto)
		}
		$cQ		= 'col-sm-3 col-md-3 col-xs-3 ph-pd-zero';// quantity
		$cN 	= 'col-sm-3 col-md-3 col-xs-3';// netto
		$cT 	= 'col-sm-3 col-md-3 col-xs-3';// tax
		$cB 	= 'col-sm-3 col-md-3 col-xs-3';// brutto
		
		$cAT	= 'col-sm-12 col-md-12 col-xs-12';// attributes
		
		// Total summarization
		$cTotE = 'col-sm-0 col-md-0 col-xs-0'; // empty space
		$cTotT = 'col-sm-8 col-md-8 col-xs-8'; // title
		$cTotB = 'col-sm-4 col-md-4 col-xs-4'; // price
		
		$cV		= '';
		$cVRow	= '';
		
		
		
	}
	
	
	echo '<div class="ph-checkout-cart-box">';
	
	
	// HEADER
	echo '<div class="'.$r.'">';
	echo '<div class="'.$cI.' ph-checkout-cart-image">'.JText::_('COM_PHOCACART_IMAGE').'</div>';
	echo '<div class="'.$cP.' ph-checkout-cart-product">'.JText::_('COM_PHOCACART_PRODUCT').'</div>';
	
	if ((int)$p['tax_calculation'] > 0 && $displayTax) {
		echo '<div class="'.$cN.' ph-checkout-cart-netto">'.JText::_('COM_PHOCACART_PRICE_EXCL_TAX').'</div>';
	}
	
	echo '<div class="'.$cQ.' ph-checkout-cart-quantity">'.JText::_('COM_PHOCACART_QUANTITY').'</div>';
	
	if ((int)$p['tax_calculation'] > 0 && $displayTax) {
		echo '<div class="'.$cT.' ph-checkout-cart-tax">'.JText::_('COM_PHOCACART_TAX').'</div>';
	}

	echo '<div class="'.$cB.' ph-checkout-cart-brutto">'.JText::_('COM_PHOCACART_PRICE').'</div>';
	echo '</div>'. "\n"; // end row
	

	// ROW
	echo '<div class="'.$r.'">';
	echo '<div class="'.$cA.'"><div class="ph-hr"></div></div>';
	echo '</div>'. "\n"; // end row
	
	foreach($d['fullitems'][1] as $k => $v) {
		
		$link 				= PhocacartRoute::getItemRoute((int)$v['id'], (int)$v['catid'], $v['alias']);

		// Design only
		$lineThroughClass	= '';
		if ($p['display_discount_product'] == 1 && ($d['fullitems'][2][$k]['discountproduct'] || $d['fullitems'][3][$k]['discountcart'] || $d['couponvalid'])) {
			$lineThroughClass	= ' ph-line-through';
		}

		
		if (isset($v['image']) && $v['image'] != '') {

			if (empty($v['attributes'])){ $v['attributes'] = array();}
			$image = PhocacartImage::getImageDisplay($v['image'], '', $d['pathitem'], '', '', '', 'small', '', $v['attributes'], 2);

			if (isset($image['image']->rel)) {
				$imageOutput = '<img src="'.JURI::base(true).'/'.$image['image']->rel.'" alt="'.strip_tags($v['title']).'" />';
			}
		} else {
			$imageOutput = '<div class="ph-no-image"><span class="glyphicon glyphicon-ban-circle"</span></div>';
		}

		echo '<div class="'.$r.$cV.'">';
		echo '<div class="'.$cI.$cVRow.' ph-checkout-cart-image ph-row-image">'.$imageOutput.'</div>';
		echo '<div class="'.$cP.$cVRow.' ph-checkout-cart-title"><a href="'.$link.'">'.$v['title'].'</a>';
		echo '</div>';
		
		
		if ((int)$p['tax_calculation'] > 0 && $displayTax) {
			echo '<div class="'.$cN.$cVRow.$lineThroughClass.' ph-checkout-cart-netto">'.$price->getPriceFormat($v['netto']).'</div>';
		}
		
		echo '<div class="'.$cQ.$cVRow.' ph-checkout-cart-quantity">';
	
		echo '<form action="'.$d['linkcheckout'].'" class="form-inline phItemCartUpdateBoxForm" method="post">';
		echo '<div class="form-group">';
		echo '<input type="hidden" name="id" value="'.(int)$v['id'].'">';
		echo '<input type="hidden" name="catid" value="'.(int)$v['catid'].'">';
		echo '<input type="hidden" name="idkey" value="'.$v['idkey'].'">';
		echo '<input type="hidden" name="ticketid" value="'.(int)$d['ticketid'].'">';
		echo '<input type="hidden" name="unitid" value="'.(int)$d['unitid'].'">';
		echo '<input type="hidden" name="sectionid" value="'.(int)$d['sectionid'].'">';
		echo '<input type="'.$inputNumber.'" class="form-control ph-input-quantity ph-input-sm" name="quantity" value="'.$v['quantity'].'">';
		echo '<input type="hidden" name="task" value="'.$task.'">';
		echo '<input type="hidden" name="tmpl" value="component" />';
		echo '<input type="hidden" name="option" value="com_phocacart" />';
		echo '<input type="hidden" name="return" value="'.$d['actionbase64'].'" />';
		//UPDATE
		echo ' <button class="btn btn-success btn-xs ph-btn" type="submit" name="action" value="update"><span title="'.JText::_('COM_PHOCACART_UPDATE_QUANTITY_IN_CART').'" class="glyphicon glyphicon-refresh"></span></button>';
		//DELETE
		echo ' <button class="btn btn-danger btn-xs ph-btn" type="submit" name="action" value="delete"><span title="'.JText::_('COM_PHOCACART_UPDATE_QUANTITY_IN_CART').'" class="glyphicon glyphicon-trash"></span></button>';
		echo JHtml::_('form.token');
		echo '</div>';
		echo '</form>';
		

		echo '</div>';// end quantity
		
		if ((int)$p['tax_calculation'] > 0 && $displayTax) {
			echo '<div class="'.$cT.$cVRow.$lineThroughClass.' ph-checkout-cart-tax">'.$price->getPriceFormat($v['tax'] * $v['quantity']).'</div>';
		}
		
		echo '<div class="'.$cB.$cVRow.$lineThroughClass.' ph-checkout-cart-brutto">'.$price->getPriceFormat($v['final']).'</div>';
		echo '</div>'. "\n"; // end row
		
		
		// ATTRIBUTES
	
		if (!empty($v['attributes'])) {

			echo '<div class="'.$r.'">';
			echo '<div class="'.$cI.'"></div>';
			echo '<div class="'.$cAT.'">';
			echo '<ul class="ph-checkout-attribute-box">';
			foreach($v['attributes'] as $k2 => $v2) {
				if (!empty($v2)) {
					foreach($v2 as $k3 => $v3) {
						echo '<li class="ph-checkout-attribute-item"><span class="ph-small ph-cart-small-attribute">'.$v3['atitle'] . ' '.$v3['otitle'].'</span>';
						if (isset($v3['ovalue']) && urldecode($v3['ovalue']) != '') {
							echo ': <span class="ph-small ph-cart-small-attribute">'.htmlspecialchars(urldecode($v3['ovalue']), ENT_QUOTES, 'UTF-8').'</span>';
						}
						echo '</li>';
					}
				}
			}
			echo '</ul>';
			echo '</div>';
			echo '</div>'. "\n"; // end row
		}
		
		
		// DISCOUNT price for each product
	
		if ($p['display_discount_product'] == 1) {
			
			
			
			
			// REWARD DISCOUNT
			if($d['fullitems'][5][$k]['rewardproduct'] && $p['display_discount_price_product'] > 0) {
				
				$discountTitle = JText::_('COM_PHOCACART_REWARD_POINTS_PRICE');
				if (isset($d['fullitems'][5][$k]['rewardproducttitle']) && $d['fullitems'][5][$k]['rewardproducttitle'] != '') {
					$discountTitle = $d['fullitems'][5][$k]['rewardproducttitle'];
				}
				
				$rewardNetto 	= $price->getPriceFormat($d['fullitems'][5][$k]['netto']);
				$rewardTax 		= $price->getPriceFormat($d['fullitems'][5][$k]['tax'] * $v['quantity']);
				$rewardFinal 	= $price->getPriceFormat($d['fullitems'][5][$k]['final']);
				
				
				if ($p['display_discount_price_product'] == 2 && isset($d['fullitems'][5][$k]['finaldiscount'])) {
					$rewardNetto 	= $price->getPriceFormat($d['fullitems'][5][$k]['nettodiscount'], 1);
					$rewardTax 		= $price->getPriceFormat($d['fullitems'][5][$k]['taxdiscount'] * $v['quantity'], 1);
					$rewardFinal 	= $price->getPriceFormat($d['fullitems'][5][$k]['finaldiscount'], 1);
				}
				
				echo '<div class="'.$r.$cV.' ph-checkout-discount-row">';
				echo '<div class="'.$cI.$cVRow.'"></div>';
				echo '<div class="'.$cP.$cVRow.' ph-checkout-cart-title">'.$discountTitle.' '.$d['fullitems'][5][$k]['rewardproducttxtsuffix'].'</div>';
				if ((int)$p['tax_calculation'] > 0 && $displayTax) {
					echo '<div class="'.$cN.$cVRow.' ph-checkout-cart-netto">'.$rewardNetto.'</div>';
				}
				echo '<div class="'.$cQ.$cVRow.' ph-checkout-cart-quantity"></div>';
				if ((int)$p['tax_calculation'] > 0 && $displayTax) {
					echo '<div class="'.$cT.$cVRow.' ph-checkout-cart-tax">'.$rewardTax.'</div>';
				}
				echo '<div class="'.$cB.$cVRow.' ph-checkout-cart-brutto">'.$rewardNetto.'</div>';
				echo '</div>'. "\n"; // end row
			}
			
			// PRODUCT DISCOUNT
			
			if($d['fullitems'][2][$k]['discountproduct'] && $p['display_discount_price_product'] > 0) {
				
				$discountTitle = JText::_('COM_PHOCACART_PRODUCT_DISCOUNT_PRICE');
				if (isset($d['fullitems'][2][$k]['discountproducttitle']) && $d['fullitems'][2][$k]['discountproducttitle'] != '') {
					$discountTitle = $d['fullitems'][2][$k]['discountproducttitle'];
				}
				
				$productNetto 	= $price->getPriceFormat($d['fullitems'][2][$k]['netto']);
				$productTax 	= $price->getPriceFormat($d['fullitems'][2][$k]['tax'] * $v['quantity']);
				$productFinal 	= $price->getPriceFormat($d['fullitems'][2][$k]['final']);
				
				if ($p['display_discount_price_product'] == 2 && isset($d['fullitems'][2][$k]['finaldiscount'])) {
					$productNetto 	= $price->getPriceFormat($d['fullitems'][2][$k]['nettodiscount'], 1);
					$productTax 	= $price->getPriceFormat($d['fullitems'][2][$k]['taxdiscount'] * $v['quantity'], 1);
					$productFinal 	= $price->getPriceFormat($d['fullitems'][2][$k]['finaldiscount'], 1);
				}
				
				echo '<div class="'.$r.$cV.' ph-checkout-discount-row">';
				echo '<div class="'.$cI.$cVRow.'"></div>';
				echo '<div class="'.$cP.$cVRow.' ph-checkout-cart-title">'.$discountTitle.'</div>';
				if ((int)$p['tax_calculation'] > 0 && $displayTax) {
					echo '<div class="'.$cN.$cVRow.' ph-checkout-cart-netto">'.$productNetto.'</div>';
				}
				echo '<div class="'.$cQ.$cVRow.' ph-checkout-cart-quantity"></div>';
				if ((int)$p['tax_calculation'] > 0 && $displayTax) {
					echo '<div class="'.$cT.$cVRow.' ph-checkout-cart-tax">'.$productTax.'</div>';
				}
				echo '<div class="'.$cB.$cVRow.' ph-checkout-cart-brutto">'.$productFinal.'</div>';
				echo '</div>'. "\n"; // end row
			}
			
			// CART DISCOUNT
			if($d['fullitems'][3][$k]['discountcart'] && $p['display_discount_price_product'] > 0) {
				
				$discountTitle = JText::_('COM_PHOCACART_CART_DISCOUNT_PRICE');
				if (isset($d['fullitems'][3][$k]['discountcarttitle']) && $d['fullitems'][3][$k]['discountcarttitle'] != '') {
					$discountTitle = $d['fullitems'][3][$k]['discountcarttitle'];
				}
				
				$cartNetto 	= $price->getPriceFormat($d['fullitems'][3][$k]['netto']);
				$cartTax 	= $price->getPriceFormat($d['fullitems'][3][$k]['tax'] * $v['quantity']);
				$cartFinal 	= $price->getPriceFormat($d['fullitems'][3][$k]['final']);
				
				if ($p['display_discount_price_product'] == 2 && isset($d['fullitems'][3][$k]['finaldiscount'])) {
					$cartNetto 	= $price->getPriceFormat($d['fullitems'][3][$k]['nettodiscount'], 1);
					$cartTax 	= $price->getPriceFormat($d['fullitems'][3][$k]['taxdiscount'] * $v['quantity'], 1);
					$cartFinal 	= $price->getPriceFormat($d['fullitems'][3][$k]['finaldiscount'], 1);
				}
				
				echo '<div class="'.$r.$cV.' ph-checkout-discount-row">';
				echo '<div class="'.$cI.$cVRow.'"></div>';
				echo '<div class="'.$cP.$cVRow.' ph-checkout-cart-title">'.$discountTitle.'</div>';
				if ((int)$p['tax_calculation'] > 0 && $displayTax) {
					echo '<div class="'.$cN.$cVRow.' ph-checkout-cart-netto">'.$cartNetto.'</div>';
				}
				echo '<div class="'.$cQ.$cVRow.' ph-checkout-cart-quantity"></div>';
				if ((int)$p['tax_calculation'] > 0 && $displayTax) {
					echo '<div class="'.$cT.$cVRow.' ph-checkout-cart-tax">'.$cartTax.'</div>';
				}
				echo '<div class="'.$cB.$cVRow.' ph-checkout-cart-brutto">'.$cartFinal.'</div>';
				echo '</div>'. "\n"; // end row
			}
			
			// CART COUPON
			if($d['couponvalid'] && $d['fullitems'][4][$k]['couponcart'] && $p['display_discount_price_product'] > 0) {

				$couponTitle = JText::_('COM_PHOCACART_COUPON');
				if (isset($d['coupontitle']) && $d['coupontitle'] != '') {
					$couponTitle = $d['coupontitle'];
				}
				
				$couponNetto 	= $price->getPriceFormat($d['fullitems'][4][$k]['netto']);
				$couponTax 		= $price->getPriceFormat($d['fullitems'][4][$k]['tax'] * $v['quantity']);
				$couponFinal 	= $price->getPriceFormat($d['fullitems'][4][$k]['final']);
				
				if ($p['display_discount_price_product'] == 2 && isset($d['fullitems'][4][$k]['finaldiscount'])) {
					$couponNetto 	= $price->getPriceFormat($d['fullitems'][4][$k]['nettodiscount'], 1);
					$couponTax 		= $price->getPriceFormat($d['fullitems'][4][$k]['taxdiscount'] * $v['quantity'], 1);
					$couponFinal 	= $price->getPriceFormat($d['fullitems'][4][$k]['finaldiscount'], 1);
				}
				
				echo '<div class="'.$r.$cV.' ph-checkout-discount-row">';
				echo '<div class="'.$cI.$cVRow.'"></div>';
				echo '<div class="'.$cP.$cVRow.' ph-checkout-cart-title">'.$couponTitle.'</div>';
				if ((int)$p['tax_calculation'] > 0 && $displayTax) {
					echo '<div class="'.$cN.$cVRow.' ph-checkout-cart-netto">'.$couponNetto.'</div>';
				}
				echo '<div class="'.$cQ.$cVRow.' ph-checkout-cart-quantity"></div>';
				if ((int)$p['tax_calculation'] > 0 && $displayTax) {
					echo '<div class="'.$cT.$cVRow.' ph-checkout-cart-tax">'.$couponTax.'</div>';
				}
				echo '<div class="'.$cB.$cVRow.' ph-checkout-cart-brutto">'.$couponFinal.'</div>';
				echo '</div>'. "\n"; // end row
			}
		}
		
		
		

		// STOCK VALID
		if ($v['stockvalid'] == 0 && $p['stock_checkout'] == 1 && $p['stock_checking'] == 1) {

			echo '<div class="'.$r.'">';
			echo '<div class="'.$cA.'">';
			echo '<div class="alert alert-error ph-alert-small">'.JText::_('COM_PHOCACART_PRODUCT_NOT_AVAILABLE_IN_QUANTITY_OR_NOT_IN_STOCK').'</div>';

			echo '</div>';
			echo '</div>'. "\n"; // end row
		}
		
		// B) MINIMUM QUANTITY - PRODUCT VARIATIONS - EACH PRODUCT VARIATION
		// see cart/calculation class - it is explained why a) method is not used
		if ($v['minqtyvalid'] == 0 && ($v['minqtycalculation'] == 1 || $v['minqtycalculation'] == 2)) {
			echo '<div class="'.$r.'">';
			echo '<div class="'.$cA.'">';
			echo '<div class="alert alert-error ph-alert-small">'.JText::_('COM_PHOCACART_MINIMUM_ORDER_QUANTITY_FOR_THIS_PRODUCT_IS').': '.$v['minqty'].'</div>';
			echo '</div>';
			echo '</div>'. "\n"; // end row
		}
		
		if ($v['minmultipleqtyvalid'] == 0 && ($v['minqtycalculation'] == 1 || $v['minqtycalculation'] == 2)) {
			echo '<div class="'.$r.'">';
			echo '<div class="'.$cA.'">';
			echo '<div class="alert alert-error ph-alert-small">'.JText::_('COM_PHOCACART_MINIMUM_MULTIPLE_ORDER_QUANTITY_FOR_PRODUCT').': '.$v['minmultipleqty'].'</div>';
			echo '</div>';
			echo '</div>'. "\n"; // end row
		}
	}
	

	// ROW
	echo '<div class="'.$r.'">';
	echo '<div class="'.$cA.'"><div class="ph-hr"></div></div>';
	echo '</div>'. "\n"; // end row

	
	
	
	// SUBTOTAL NETTO
	if ($d['total'][1]['netto'] !== 0) {

		echo '<div class="'.$r.' ph-cart-subtotal-box">';
		echo '<div class="'.$cTotE.'"></div>';
		echo '<div class="'.$cTotT.' ph-cart-subtotal-netto-txt">'.JText::_('COM_PHOCACART_SUBTOTAL').'</div>';
		echo '<div class="'.$cTotB.' ph-right ph-cart-subtotal-netto">'.$price->getPriceFormat($d['total'][1]['netto']).'</div>';
		echo '</div>';// end row
	}
	
	// REWARD DISCOUNT
	if ($d['total'][5]['dnetto']) {
		echo '<div class="'.$r.' ph-cart-reward-discount-box">';
		echo '<div class="'.$cTotE.'"></div>';
		echo '<div class="'.$cTotT.' ph-cart-reward-discount-txt">'.JText::_('COM_PHOCACART_REWARD_POINTS').$d['total'][5]['rewardproducttxtsuffix'].'</div>';
		echo '<div class="'.$cTotB.' ph-right ph-cart-reward-discount">'.$price->getPriceFormat($d['total'][5]['dnetto'], 1).'</div>';
		echo '</div>';// end row
	}
	
	// PRODUCT DISCOUNT
	if ($d['total'][2]['dnetto']) {
		echo '<div class="'.$r.' ph-cart-product-discount-box">';
		echo '<div class="'.$cTotE.'"></div>';
		echo '<div class="'.$cTotT.' ph-cart-product-discount-txt">'.JText::_('COM_PHOCACART_PRODUCT_DISCOUNT').'</div>';
		echo '<div class="'.$cTotB.' ph-right ph-cart-product-discount">'.$price->getPriceFormat($d['total'][2]['dnetto'], 1).'</div>';
		echo '</div>';// end row
	}
	
	// CART DISCOUNT
	if ($d['total'][3]['dnetto']) {
		echo '<div class="'.$r.' ph-cart-discount-box">';
		echo '<div class="'.$cTotE.'"></div>';
		echo '<div class="'.$cTotT.' ph-cart-cart-discount-txt">'.JText::_('COM_PHOCACART_CART_DISCOUNT').$d['total'][3]['discountcarttxtsuffix'].'</div>';
		echo '<div class="'.$cTotB.' ph-right ph-cart-cart-discount">'.$price->getPriceFormat($d['total'][3]['dnetto'], 1).'</div>';
		echo '</div>';// end row
	}
	
	// COUPON
	
	if ($d['total'][4]['dnetto'] && $d['couponvalid']) {
		$couponTitle = JText::_('COM_PHOCACART_COUPON');
		if (isset($d['coupontitle']) && $d['coupontitle'] != '') {
			$couponTitle = $d['coupontitle'];
		}
		echo '<div class="'.$r.' ph-cart-coupon-box">';
		echo '<div class="'.$cTotE.'"></div>';
		echo '<div class="'.$cTotT.' ph-cart-coupon-txt">'.$couponTitle.$d['total'][4]['couponcarttxtsuffix'].'</div>';
		echo '<div class="'.$cTotB.' ph-checkout-total-coupon ph-right ph-cart-coupon">'.$price->getPriceFormat($d['total'][4]['dnetto'], 1).'</div>';
		echo '</div>';// end row
	}
	
	

	// TAX
	if (!empty($d['total'][0]['tax'])) {
		foreach($d['total'][0]['tax'] as $k3 => $v3) {
			if($v3['tax'] !== 0 && $v3['tax'] != 0 && $p['tax_calculation'] != 0) {
				echo '<div class="'.$r.' ph-cart-tax-box">';
				echo '<div class="'.$cTotE.'"></div>';
				echo '<div class="'.$cTotT.' ph-cart-tax-txt">'.$v3['title'].'</div>';
				echo '<div class="'.$cTotB.' ph-checkout-total-amount ph-right ph-cart-tax">'.$price->getPriceFormat($v3['tax']).'</div>';
				echo '</div>';// end row
			}
		}
	}
	

	

	// SHIPPING
	// Add Shipping costs if there are some

	if (!empty($d['shippingcosts'])) {
		$sC = $d['shippingcosts'];
		
		if ($p['zero_shipping_price_calculation'] == -1 && $sC['zero'] == 1) {
			// Hide completely
		} else 	if ($p['zero_shipping_price_calculation'] == 0 && $sC['zero'] == 1) {
			// Display blank price field
			echo '<div class="'.$r.' ph-cart-shipping-box">';
			echo '<div class="'.$cTotE.'"></div>';
			echo '<div class="'.$cTotT.' ph-cart-shipping-txt">'.$sC['title'].'</div>';
			echo '<div class="'.$cTotB.' ph-checkout-total-amount ph-right ph-cart-shipping"></div>';
			echo '</div>';// end row
		} else if ($p['zero_shipping_price_calculation'] == 2 && $sC['zero'] == 1) {
			// Display free text
			echo '<div class="'.$r.' ph-cart-shipping-box">';
			echo '<div class="'.$cTotE.'"></div>';
			echo '<div class="'.$cTotT.' ph-cart-shipping-txt">'.$sC['title'].'</div>';
			echo '<div class="'.$cTotB.' ph-checkout-total-amount ph-right ph-cart-shipping">'.JText::_('COM_PHOCACART_FREE').'</div>';
			echo '</div>';// end row
		} else {
		
	
			if (isset($sC['nettoformat']) && $sC['nettoformat'] != '' && isset($sC['nettotxt'])/* && $sC['nettotxt'] != '' can be empty */) {
				echo '<div class="'.$r.' ph-cart-shipping-box">';
				echo '<div class="'.$cTotE.'"></div>';
				echo '<div class="'.$cTotT.' ph-cart-shipping-netto-txt">'.$sC['title']. PhocacartUtils::addSeparator($sC['nettotxt']).'</div>';
				echo '<div class="'.$cTotB.' ph-checkout-total-amount ph-right ph-cart-shipping-netto">'.$sC['nettoformat'].'</div>';
				echo '</div>';// end row
			}
			
			if (isset($sC['taxformat']) && $sC['taxformat'] != '' && isset($sC['taxtxt'])/* && $sC['taxtxt'] != '' can be empty */) {
				echo '<div class="'.$r.' ph-cart-shipping-box">';
				echo '<div class="'.$cTotE.'"></div>';
				echo '<div class="'.$cTotT.' ph-cart-shipping-tax-txt">'.$sC['title']. PhocacartUtils::addSeparator($sC['taxtxt']).'</div>';
				echo '<div class="'.$cTotB.' ph-checkout-total-amount ph-right ph-cart-shipping-tax">'.$sC['taxformat'].'</div>';
				echo '</div>';// end row
			}
			
			if ((isset($sC['bruttoformat']) && $sC['bruttoformat'] != '' && isset($sC['bruttotxt']) /* && $sC['bruttotxt'] != '' - can be empty */) || $sC['freeshipping'] == 1) {

				echo '<div class="'.$r.' ph-cart-shipping-box">';
				echo '<div class="'.$cTotE.'"></div>';
				echo '<div class="'.$cTotT.' ph-cart-shipping-brutto-txt">'.$sC['title']. PhocacartUtils::addSeparator($sC['bruttotxt']).'</div>';
				echo '<div class="'.$cTotB.' ph-checkout-total-amount ph-right ph-cart-shipping-brutto">'.$sC['bruttoformat'].'</div>';
				echo '</div>';// end row
			}
		}
	}
	
	// PAYMENT
	// Add Payment costs if there are some
	if (!empty($d['paymentcosts'])) {
		$pC = $d['paymentcosts'];
		
		
		if ($p['zero_payment_price_calculation'] == -1 && $pC['zero'] == 1) {
			// Hide completely
		} else 	if ($p['zero_payment_price_calculation'] == 0 && $pC['zero'] == 1) {
			// Display blank price field
			echo '<div class="'.$r.' ph-cart-payment-box">';
			echo '<div class="'.$cTotE.'"></div>';
			echo '<div class="'.$cTotT.' ph-cart-payment-txt">'.$pC['title'].'</div>';
			echo '<div class="'.$cTotB.' ph-checkout-total-amount ph-right ph-cart-payment"></div>';
			echo '</div>';// end row
		} else if ($p['zero_payment_price_calculation'] == 2 && $pC['zero'] == 1) {
			// Display free text
			echo '<div class="'.$r.' ph-cart-payment-box">';
			echo '<div class="'.$cTotE.'"></div>';
			echo '<div class="'.$cTotT.' ph-cart-payment-txt">'.$pC['title'].'</div>';
			echo '<div class="'.$cTotB.' ph-checkout-total-amount ph-right ph-cart-payment">'.JText::_('COM_PHOCACART_FREE').'</div>';
			echo '</div>';// end row
		} else {
		
		
			if (isset($pC['nettoformat']) && $pC['nettoformat'] != '' && isset($pC['nettotxt'])/* && $pC['nettotxt'] != '' can be empty */) {
				
				
				$pC['nettotxt'] = $pC['nettotxt'] != '' ? ' - ' : '';
				echo '<div class="'.$r.' ph-cart-payment-box">';
				echo '<div class="'.$cTotE.'"></div>';
				echo '<div class="'.$cTotT.' ph-cart-payment-netto-txt">'.$pC['title']. PhocacartUtils::addSeparator($pC['nettotxt']).'</div>';
				echo '<div class="'.$cTotB.' ph-checkout-total-amount ph-right ph-cart-payment-netto">'.$pC['nettoformat'].'</div>';
				echo '</div>';// end row
			}
			
			if (isset($pC['taxformat']) && $pC['taxformat'] != '' && isset($pC['taxtxt'])/* && $pC['taxtxt'] != '' can be empty */) {
				echo '<div class="'.$r.' ph-cart-payment-box">';
				echo '<div class="'.$cTotE.'"></div>';
				echo '<div class="'.$cTotT.' ph-cart-payment-tax-txt">'.$pC['title']. PhocacartUtils::addSeparator($pC['taxtxt']).'</div>';
				echo '<div class="'.$cTotB.' ph-checkout-total-amount ph-right ph-cart-payment-tax">'.$pC['taxformat'].'</div>';
				echo '</div>';// end row
			}
			
			if ((isset($pC['bruttoformat']) && $pC['bruttoformat'] != '' && isset($pC['bruttotxt'])/* && $pC['bruttotxt'] != '' can be empty */) || $pC['freepayment'] == 1) {

				echo '<div class="'.$r.' ph-cart-payment-box">';
				echo '<div class="'.$cTotE.'"></div>';
				echo '<div class="'.$cTotT.' ph-cart-payment-brutto-txt">'.$pC['title']. PhocacartUtils::addSeparator($pC['bruttotxt']).'</div>';
				echo '<div class="'.$cTotB.' ph-checkout-total-amount ph-right ph-cart-payment-brutto">'.$pC['bruttoformat'].'</div>';
				echo '</div>';// end row
			}
		}
	}
	
	
	// ROUNDING | ROUNDING CURRENCY
	if ($d['total'][0]['rounding_currency'] != 0) {
		
		echo '<div class="'.$r.' ph-cart-currency-box">';
		echo '<div class="'.$cTotE.'"></div>';
		echo '<div class="'.$cTotT.' ph-cart-rounding-currency-txt">'.JText::_('COM_PHOCACART_ROUNDING_CURRENCY').'</div>';
		echo '<div class="'.$cTotB.' ph-right ph-cart-rounding-currency">'.$price->getPriceFormat($d['total'][0]['rounding_currency'], 0, 1).'</div>';
		echo '</div>';// end row
	} else if ($d['total'][0]['rounding'] != 0) {
		
		echo '<div class="'.$r.' ph-cart-currency-box">';
		echo '<div class="'.$cTotE.'"></div>';
		echo '<div class="'.$cTotT.'  ph-cart-rounding-txt">'.JText::_('COM_PHOCACART_ROUNDING').'</div>';
		echo '<div class="'.$cTotB.' ph-right ph-cart-rounding">'.$price->getPriceFormat($d['total'][0]['rounding']).'</div>';
		echo '</div>';// end row
	}

	// BRUTTO (Because of rounding currency we need to display brutto in currency which is set)
	if ($d['total'][0]['brutto_currency'] !== 0) {
		echo '<div class="'.$r.' ph-cart-currency-box">';
		echo '<div class="'.$cTotE.'"></div>';
		echo '<div class="'.$cTotT.' ph-cart-brutto-currency-txt">'.JText::_('COM_PHOCACART_TOTAL').'</div>';
		echo '<div class="'.$cTotB.' ph-checkout-total-amount ph-cart-total ph-right ph-cart-brutto-currency">'.$price->getPriceFormat($d['total'][0]['brutto_currency'], 0, 1).'</div>';
		echo '</div>';// end row
	} else if ($d['total'][0]['brutto'] !== 0) {
		echo '<div class="'.$r.' ph-cart-total-box">';
		echo '<div class="'.$cTotE.'"></div>';
		echo '<div class="'.$cTotT.' ph-cart-total-txt">'.JText::_('COM_PHOCACART_TOTAL').'</div>';
		echo '<div class="'.$cTotB.' ph-checkout-total-amount ph-cart-total ph-right">'.$price->getPriceFormat($d['total'][0]['brutto']).'</div>';
		echo '</div>';// end row
	}
	
	// Possible points received
	
	if ($p['display_reward_points_receive_info'] == 1 && isset($d['total'][0]['points_received']) && $d['total'][0]['points_received'] > 0) {
		
		echo '<div class="ph-ceckout-points-received">'.JText::_('COM_PHOCACART_POINTS_RECEIVED_FOR_THIS_PURCHASE').': ' .$d['total'][0]['points_received'].'</div>';
	}
	

	echo '</div>'. "\n"; // end checkout box
} else {
	
	if ($d['pos']) {
		echo '<div class="ph-cart-icon"><span class="glyphicon glyphicon-shopping-cart"></span></div>';
	}
	echo '<div class="ph-cart-empty">'.JText::_('COM_PHOCACART_SHOPPING_CART_IS_EMPTY').'</div>';
}
	

?>