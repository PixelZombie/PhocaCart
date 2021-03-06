<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class PhocaCartControllerCheckout extends JControllerForm
{
	/*
	 * Add product to cart 
	 */
	public function add() {
		
		JSession::checkToken() or jexit( 'Invalid Token' );
		$app				= JFactory::getApplication();
		$item				= array();
		$item['id']			= $this->input->get( 'id', 0, 'int' );
		$item['catid']		= $this->input->get( 'catid', 0, 'int' );
		$item['quantity']	= $this->input->get( 'quantity', 0, 'int'  );
		$item['return']		= $this->input->get( 'return', '', 'string'  );
		$item['attribute']	= $this->input->get( 'attribute', array(), 'array'  );
		
	
		$cart	= new PhocacartCart();
		
		$added	= $cart->addItems((int)$item['id'], (int)$item['catid'], (int)$item['quantity'], $item['attribute']);
		
		if ($added) {
			$app->enqueueMessage(JText::_('COM_PHOCACART_PRODUCT_ADDED_TO_SHOPPING_CART'), 'message');
		} else {
			$app->enqueueMessage(JText::_('COM_PHOCACART_PRODUCT_NOT_ADDED_TO_SHOPPING_CART'), 'error');
		}
		//$app->redirect(JRoute::_('index.php?option=com_phocacart&view=checkout'));
		$app->redirect(base64_decode($item['return']));
	}
	
	/*
	 * Change currency
	 */
	public function currency() {
		JSession::checkToken() or jexit( 'Invalid Token' );
		$app				= JFactory::getApplication();
		$item				= array();
		$item['id']			= $this->input->get( 'id', 0, 'int' );
		$item['return']		= $this->input->get( 'return', '', 'string'  );
		
		//$currency = new PhocacartCurrency();
		//$currency->setCurrentCurrency((int)$item['id']);
		
		PhocacartCurrency::setCurrentCurrency((int)$item['id']);
		
		$app->redirect(base64_decode($item['return']));
	}
	
	/*
	 * Save billing and shipping address
	 */
	 
	 public function saveaddress() {
		
		JSession::checkToken() or jexit( 'Invalid Token' );
		$app					= JFactory::getApplication();
		$item					= array();
		$item['return']			= $this->input->get( 'return', '', 'string'  );
		$item['jform']			= $this->input->get( 'jform', array(), 'array'  );
		$item['phcheckoutbsas']	= $this->input->get( 'phcheckoutbsas', false, 'string'  );
		$guest					= PhocacartUserGuestuser::getGuestUser();	
		$error 					= 0;
		$msgSuffix				= '<span id="ph-msg-ns" class="ph-hidden"></span>';
		if(!empty($item['jform'])) {
			
			// Form Data
			$billing	= array();
			$shipping	= array();
			
			$bas 		= PhocacartUser::convertAddressTwo($item['jform']);
			$billing 	= $bas[0];
			$shipping	= $bas[1];
			
			
			
			// Form Items
			$fI = new PhocacartFormItems();
			$items = $fI->getFormItems(1,1,0);
			if(!empty($items)) {
				foreach($items as $k => $v) {
					if ($v->required == 1) {
						if (isset($billing[$v->title]) && $billing[$v->title] == '') {
							$msg = JText::_('COM_PHOCACART_FILL_OUT_THIS_FIELD') . ': '.JText::_($v->label) 
							. ' <small>('.JText::_('COM_PHOCACART_BILLING_ADDRESS').')</small>';
							$app->enqueueMessage($msg, 'error');
							$error = 1;
						}
						
						// Don't check the shipping as it is not required
						if ($item['phcheckoutbsas']) {
							$billing['ba_sa'] = 1;
							$shipping['ba_sa'] = 1;
							// CHECKBOX IS ON
						} else {
							// CHECKBOX IS OFF
							$billing['ba_sa'] = 0;
							$shipping['ba_sa'] = 0;
							if (isset($shipping[$v->title]) && $shipping[$v->title] == '') {
								$msg = JText::_('COM_PHOCACART_FILL_OUT_THIS_FIELD') . ': '.JText::_($v->label)
								. ' <small>('.JText::_('COM_PHOCACART_SHIPPING_ADDRESS').')</small>';
								$app->enqueueMessage($msg, 'error');
								$error = 1;
							}
						}
					}
				
				}
			} else {
				$app->enqueueMessage(JText::_('COM_PHOCACART_ERROR_NO_FORM_LOADED') . $msgSuffix, 'error');
				$error = 1;
			}
		} else {
			$app->enqueueMessage(JText::_('COM_PHOCACART_NO_DATA_STORED'). $msgSuffix, 'error');// Not used: COM_PHOCACART_ERROR_NO_DATA_STORED
			$error = 1;																// as in fact this can be possible
		}																			// that admin does not require any data
		if ($error == 1) {
			$app->redirect(base64_decode($item['return']));
		}
		
		if ($guest) {
			$model 	= $this->getModel('checkout');
			if ($item['phcheckoutbsas']) {
				$item['jform']['ba_sa'] = 1;
				foreach($item['jform'] as $k => $v) {
					$pos = strpos($k, '_phs');
					if ($pos === false) {
						
					} else {
						unset($item['jform'][$k]);
					}
				}
			}
			if(!$model->saveAddressGuest($item['jform'])) {
				$msg = JText::_('COM_PHOCACART_ERROR_DATA_NOT_STORED');
				$app->enqueueMessage($msg. $msgSuffix, 'error');
				$error = 1;
			}
		
		} else {
			
			if (!empty($billing)) {
				$model 	= $this->getModel('checkout');
				if(!$model->saveAddress($billing)) {
					$msg = JText::_('COM_PHOCACART_ERROR_DATA_NOT_STORED');
					$app->enqueueMessage($msg. $msgSuffix, 'error');
					$error = 1;
				} else {
					//$msg = JText::_('COM_PHOCACART_SUCCESS_DATA_STORED');
					//$app->enqueueMessage($msg, 'message');
					// Waiting for shipping
				}
				//$app->redirect(base64_decode($item['return']));
			}
			
			if (!empty($shipping)) {
				$model 	= $this->getModel('checkout');
				if(!$model->saveAddress($shipping, 1)) {
					$msg = JText::_('COM_PHOCACART_ERROR_DATA_NOT_STORED');
					$app->enqueueMessage($msg. $msgSuffix, 'error');
					$error = 1;
				} else {
					//$msg = JText::_('COM_PHOCACART_SUCCESS_DATA_STORED');
					//$app->enqueueMessage($msg, 'message');
					// Waiting for shipping
				}
				//$app->redirect(base64_decode($item['return']));
			}
		}
		
		
		// Remove shipping because shipping methods can change while chaning address
		PhocacartShipping::removeShipping(0);
		PhocacartPayment::removePayment(0);
		$msg = JText::_('COM_PHOCACART_SUCCESS_DATA_STORED');
		if ($error != 1) {
			$app->enqueueMessage($msg, 'message');
		}
		
		$app->redirect(base64_decode($item['return']));
	 }
	 
	 
	 /*
	 * Save shipping method
	 */
	 
	 public function saveshipping() {
		
		JSession::checkToken() or jexit( 'Invalid Token' );
		$app					= JFactory::getApplication();
		$item					= array();
		$item['return']			= $this->input->get( 'return', '', 'string'  );
		$item['phshippingopt']	= $this->input->get( 'phshippingopt', array(), 'array'  );
		$guest					= PhocacartUserGuestuser::getGuestUser();
		$msgSuffix				= '<span id="ph-msg-ns" class="ph-hidden"></span>';
		
		if(!empty($item['phshippingopt']) && isset($item['phshippingopt'][0]) && (int)$item['phshippingopt'][0] > 0) {
			
			$model 	= $this->getModel('checkout');
			
			if ($guest) {
				if(!$model->saveShippingGuest((int)$item['phshippingopt'][0])) {
					$msg = JText::_('COM_PHOCACART_ERROR_DATA_NOT_STORED');
					$app->enqueueMessage($msg.$msgSuffix, 'error');
				} else {
					$msg = JText::_('COM_PHOCACART_SUCCESS_DATA_STORED');
					$app->enqueueMessage($msg, 'message');
					PhocacartPayment::removePayment($guest);
				}
			
			} else {
				if(!$model->saveShipping((int)$item['phshippingopt'][0])) {
					$msg = JText::_('COM_PHOCACART_ERROR_DATA_NOT_STORED');
					$app->enqueueMessage($msg.$msgSuffix, 'error');
				} else {
					$msg = JText::_('COM_PHOCACART_SUCCESS_DATA_STORED');
					$app->enqueueMessage($msg, 'message');
					PhocacartPayment::removePayment($guest);
				}
			}
			
		} else {
			$msg = JText::_('COM_PHOCACART_NO_SHIPPING_METHOD_SELECTED');
			$app->enqueueMessage($msg.$msgSuffix, 'error');
		}
		$app->redirect(base64_decode($item['return']));
	}
	
	/*
	 * Save payment method and coupons
	 */
	 
	 public function savepayment() {
		
		JSession::checkToken() or jexit( 'Invalid Token' );
		$app						= JFactory::getApplication();
		$item						= array();
		$item['return']				= $this->input->get( 'return', '', 'string'  );
		$item['phpaymentopt']		= $this->input->get( 'phpaymentopt', array(), 'array'  );
		$item['phcoupon']			= $this->input->get( 'phcoupon', '', 'string'  );
		$item['phreward']			= $this->input->get( 'phreward', '', 'int'  );
		$guest						= PhocacartUserGuestuser::getGuestUser();
		$params 					= $app->getParams();
		$msgSuffix					= '<span id="ph-msg-ns" class="ph-hidden"></span>';
	
		$this->t['enable_coupons']			= $params->get( 'enable_coupons', 1 );
		$this->t['enable_rewards']			= $params->get( 'enable_rewards', 1 );
	
	
		if(!empty($item['phpaymentopt']) && isset($item['phpaymentopt'][0]) && (int)$item['phpaymentopt'][0] > 0) {
			
			// Coupon
			$couponId = 0;
			if (isset($item['phcoupon']) && $item['phcoupon'] != '' && $this->t['enable_coupons']) {
				
				$coupon = new PhocacartCoupon();
				$coupon->setCoupon(0, $item['phcoupon']);
				$couponTrue = $coupon->checkCoupon(1);// Basic Check - Coupon True does not mean it is valid
				$couponId 	= 0;
				if ($couponTrue) {
					$couponData = $coupon->getCoupon();
					if (isset($couponData['id']) && $couponData['id'] > 0) {
						$couponId = $couponData['id'];
					}
				}
				
				if(!$couponId) {
					$msg = JText::_('COM_PHOCACART_COUPON_INVALID_EXPIRED_REACHED_USAGE_LIMIT');
					$app->enqueueMessage($msg.$msgSuffix, 'error');
				} else {
					$msg = JText::_('COM_PHOCACART_COUPON_ADDED');
					$app->enqueueMessage($msg, 'message');
				}
			}
			
			$rewards 			= array();
			$rewards['used'] 	= 0;
			
			if (isset($item['phreward']) && $item['phreward'] != '' && $this->t['enable_rewards']) {
				
				$reward 			= new PhocacartReward();
				$rewards['used']	= $reward->checkReward((int)$item['phreward'], 1);
				
				
				if($rewards['used'] === false) {
					$msg = JText::_('COM_PHOCACART_REWARD_POINTS_NOT_ADDED');
					$app->enqueueMessage($msg.$msgSuffix, 'error');
				} else {
					$msg = JText::_('COM_PHOCACART_REWARD_POINTS_ADDED');
					$app->enqueueMessage($msg, 'message');
				}
				
				
			}
			
			$model 	= $this->getModel('checkout');
			
			if ($guest) {
				if(!$model->savePaymentAndCouponGuest((int)$item['phpaymentopt'][0], $couponId)) {
					$msg = JText::_('COM_PHOCACART_ERROR_DATA_NOT_STORED');
					$app->enqueueMessage($msg.$msgSuffix, 'error');
				} else {
					$msg = JText::_('COM_PHOCACART_SUCCESS_DATA_STORED');
					$app->enqueueMessage($msg, 'message');
				}
			} else {
				if(!$model->savePaymentAndCouponAndReward((int)$item['phpaymentopt'][0], $couponId, $rewards['used'])) {
					$msg = JText::_('COM_PHOCACART_ERROR_DATA_NOT_STORED');
					$app->enqueueMessage($msg.$msgSuffix, 'error');
				} else {
					$msg = JText::_('COM_PHOCACART_SUCCESS_DATA_STORED');
					$app->enqueueMessage($msg, 'message');
				}	
			}
			
			
		} else {
			$msg = JText::_('COM_PHOCACART_NO_PAYMENT_METHOD_SELECTED');
			$app->enqueueMessage($msg.$msgSuffix, 'error');
		}
		$app->redirect(base64_decode($item['return']));
	}
	
	/*
	 * Update or delete from cart
	 */
	public function update() {
		
		JSession::checkToken() or jexit( 'Invalid Token' );
		$app				= JFactory::getApplication();
		$item				= array();
		$item['id']			= $this->input->get( 'id', 0, 'int' );
		$item['idkey']		= $this->input->get( 'idkey', '', 'string' );
		$item['quantity']	= $this->input->get( 'quantity', 0, 'int'  );
		$item['return']		= $this->input->get( 'return', '', 'string'  );
		$item['action']		= $this->input->get( 'action', '', 'string'  );
		$msgSuffix			= '<span id="ph-msg-ns" class="ph-hidden"></span>';
		
		if ((int)$item['idkey'] != '' && $item['action'] != '') {
		
			$cart	= new PhocacartCart();
			if ($item['action'] == 'delete') {
				$updated	= $cart->updateItemsFromCheckout($item['idkey'], 0);
				if ($updated) {
					$app->enqueueMessage(JText::_('COM_PHOCACART_PRODUCT_REMOVED_FROM_SHOPPING_CART') . $msgSuffix, 'message');
				} else {
					$app->enqueueMessage(JText::_('COM_PHOCACART_ERROR_PRODUCT_NOT_REMOVED_FROM_SHOPPING_CART') . $msgSuffix, 'error');
				}
			} else {// update
				$updated	= $cart->updateItemsFromCheckout($item['idkey'], (int)$item['quantity']);
				if ($updated) {
					$app->enqueueMessage(JText::_('COM_PHOCACART_PRODUCT_QUANTITY_UPDATED') .$msgSuffix , 'message');
				} else {
					$app->enqueueMessage(JText::_('COM_PHOCACART_ERROR_PRODUCT_QUANTITY_NOT_UPDATED'). $msgSuffix, 'error');
				}
			}
		}
		
		//$app->redirect(JRoute::_('index.php?option=com_phocacart&view=checkout'));
		$app->redirect(base64_decode($item['return']));
	}
	/*
	 public function saveshipping() {
		
		JSession::checkToken() or jexit( 'Invalid Token' );
		$app					= JFactory::getApplication();
		$item					= array();
		$item['return']			= $this->input->get( 'return', '', 'string'  );
		$item['phshippingopt']	= $this->input->get( 'phshippingopt', array(), 'array'  );

		
		if(!empty($item['phshippingopt']) && isset($item['phshippingopt'][0]) && (int)$item['phshippingopt'][0] > 0) {
			
			$model 	= $this->getModel('checkout');
			if(!$model->saveShipping((int)$item['phshippingopt'][0])) {
				$msg = JText::_('COM_PHOCACART_ERROR_DATA_NOT_STORED');
				$app->enqueueMessage($msg, 'error');
			} else {
				$msg = JText::_('COM_PHOCACART_SUCCESS_DATA_STORED');
				$app->enqueueMessage($msg, 'message');
			}
			
		} else {
			$app->enqueueMessage(JText::_('COM_PHOCACART_NO_SHIPPING_METHOD_SELECTED'), 'error');
		}
		$app->redirect(base64_decode($item['return']));
	}
	*/
	/*
	 * Make an order
	 */
	 
	 public function order() {
		
		JSession::checkToken() or jexit( 'Invalid Token' );
		$pC 								= PhocacartUtils::getComponentParameters();
		$display_checkout_privacy_checkbox	= $pC->get( 'display_checkout_privacy_checkbox', 0 );
		$display_checkout_toc_checkbox		= $pC->get( 'display_checkout_toc_checkbox', 2);
		
		$app						= JFactory::getApplication();
		$item						= array();
		$item['return']				= $this->input->get( 'return', '', 'string'  );
		$item['phcheckouttac']		= $this->input->get( 'phcheckouttac', false, 'string'  );
		$item['privacy']			= $this->input->get( 'privacy', false, 'string'  );
		$item['phcomment']			= $this->input->get( 'phcomment', '', 'string'  );
		$msgSuffix					= '<span id="ph-msg-ns" class="ph-hidden"></span>';
		
		$item['privacy'] 			= $item['privacy'] ? 1 : 0;
		$item['phcheckouttac']	 	= $item['phcheckouttac'] ? 1 : 0;

		
		if ($display_checkout_privacy_checkbox == 2 && $item['privacy'] == 0) {
			$msg = JText::_('COM_PHOCACART_ERROR_YOU_NEED_TO_AGREE_TO_PRIVACY_TERMS_AND_CONDITIONS');
			$app->enqueueMessage($msg.$msgSuffix, 'error');
			$app->redirect(base64_decode($item['return']));
			return false;
			
		}
		
		if ($display_checkout_toc_checkbox == 2 && $item['phcheckouttac'] == 0) {
			$msg = JText::_('COM_PHOCACART_ERROR_YOU_NEED_TO_AGREE_TO_TERMS_AND_CONDITIONS');
			$app->enqueueMessage($msg.$msgSuffix, 'error');
			$app->redirect(base64_decode($item['return']));
			return false;
		}
	

		
	
		$order = new PhocacartOrder();		
		$orderMade = $order->saveOrderMain($item);
		
		if(!$orderMade) {
			$msg = '';
			if (!PhocacartUtils::issetMessage()){
				$msg = JText::_('COM_PHOCACART_ORDER_ERROR_PROCESSING');
			}
			$app->enqueueMessage($msg.$msgSuffix, 'error');
			$app->redirect(base64_decode($item['return']));
			return true;
		} else {
			
			$cart = new PhocacartCart();
			$cart->emptyCart();
			PhocacartUserGuestuser::cancelGuestUser();
			
			$action 	= $order->getActionAfterOrder();// Which action should be done
			$message	= $order->getMessageAfterOrder();// Custom message by payment plugin Payment/Download, Payment/No Download ...
			
			$session 	= JFactory::getSession();
			if ($action == 4 || $action == 3) {
				// Ordered OK, but now we proceed to payment
				$session->set('infoaction', $action, 'phocaCart');
				$session->set('infomessage', $message, 'phocaCart');
				$app->redirect(JRoute::_(PhocacartRoute::getPaymentRoute(), false));
				return true;
				// This message should stay 
				// when order - the message is created
				// when payment - the message stays unchanged
				// after payment - it will be redirected to info view and there the message will be displayed and then deleted
			
			} else {
				// Ordered OK, but the payment method does not have any instruction to proceed to payment (e.g. cash on delivery)
				//$msg = JText::_('COM_PHOCACART_ORDER_SUCCESSFULLY_PROCESSED');
				// We produce not message but we redirect to specific view with message and additional instructions
				//$app->enqueueMessage($msg, 'message');
				
				$session->set('infoaction', $action, 'phocaCart');
				$session->set('infomessage', $message, 'phocaCart');
				$app->redirect(JRoute::_(PhocacartRoute::getInfoRoute(), false));
				return true;
			}
		}
		
		
	}
	
	public function setguest() {
		
		JSession::checkToken() or jexit( 'Invalid Token' );
		$app				= JFactory::getApplication();
		$item				= array();
		$item['id']			= $this->input->get( 'id', 0, 'int' );
		$item['return']		= $this->input->get( 'return', '', 'string'  );
		$msgSuffix			= '<span id="ph-msg-ns" class="ph-hidden"></span>';
		
		
		$guest	= new PhocacartUserGuestuser();
		$set	= $guest->setGuestUser((int)$item['id']);
		if ((int)$item['id'] == 1) {
			if ($set) {
				$app->enqueueMessage(JText::_('COM_PHOCACART_YOU_PROCEEDING_GUEST_CHECKOUT').$msgSuffix, 'message');
			} else {
				$app->enqueueMessage(JText::_('COM_PHOCACART_ERROR_DURING_PROCEEDING_GUESTBOOK_CHECKOUT').$msgSuffix, 'error');
			}
		} else {
			if ($set) {
				$app->enqueueMessage(JText::_('COM_PHOCACART_GUEST_CHECKOUT_CANCELED').$msgSuffix, 'message');
			} else {
				$app->enqueueMessage(JText::_('COM_PHOCACART_ERROR_DURING_CANCELING_GUESTBOOK_CHECKOUT').$msgSuffix, 'error');
			}
		}
		//$app->redirect(JRoute::_('index.php?option=com_phocacart&view=checkout'));
		$app->redirect(base64_decode($item['return']));
	}
	/*
	public function compareadd() {
		
		JSession::checkToken() or jexit( 'Invalid Token' );
		$app				= JFactory::getApplication();
		$item				= array();
		$item['id']			= $this->input->get( 'id', 0, 'int' );
		$item['return']		= $this->input->get( 'return', '', 'string'  );
		
		$compare	= new PhocacartCompare();
		$added	= $compare->addItem((int)$item['id']);
		if ($added) {
			$app->enqueueMessage(JText::_('COM_PHOCACART_PRODUCT_ADDED_TO_COMPARISON_LIST'), 'message');
		} else {
			$app->enqueueMessage(JText::_('COM_PHOCACART_PRODUCT_NOT_ADDED_TO_COMPARISON_LIST'), 'error');
		}
		//$app->redirect(JRoute::_('index.php?option=com_phocacart&view=checkout'));
		$app->redirect(base64_decode($item['return']));
	}
	
		public function compareremove() {
		
		JSession::checkToken() or jexit( 'Invalid Token' );
		$app				= JFactory::getApplication();
		$item				= array();
		$item['id']			= $this->input->get( 'id', 0, 'int' );
		$item['return']		= $this->input->get( 'return', '', 'string'  );
		
		$compare	= new PhocacartCompare();
		$added	= $compare->removeItem((int)$item['id']);
		if ($added) {
			$app->enqueueMessage(JText::_('COM_PHOCACART_PRODUCT_REMOVED_FROM_COMPARISON_LIST'), 'message');
		} else {
			$app->enqueueMessage(JText::_('COM_PHOCACART_PRODUCT_NOT_REMOVED_FROM_COMPARISON_LIST'), 'error');
		}
		//$app->redirect(JRoute::_('index.php?option=com_phocacart&view=checkout'));
		$app->redirect(base64_decode($item['return']));
	}*/
	
}
?>