<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
jimport( 'joomla.application.component.view');
jimport( 'joomla.filesystem.folder' );  
jimport( 'joomla.filesystem.file' );

class PhocaCartViewPos extends JViewLegacy
{
	protected $category;
	protected $subcategories;
	protected $items;
	protected $t;
	protected $p;
	protected $cart;
	
	function display($tpl = null) {		
		
		$app						= JFactory::getApplication();
		$document					= JFactory::getDocument();
		$this->p 					= $app->getParams();
		$uri 						= JFactory::getURI();
		$model						= $this->getModel();
		$this->state				= $this->get('State');
		$this->t['action']			= $uri->toString();
		$this->t['actionbase64']	= base64_encode($this->t['action']);
		
		
		// INPUTS
		$this->t['id']				= $app->input->get( 'id', 0, 'int' );
		//$this->t['categoryid']		= $app->input->get( 'id', 0, 'int' );// optional
		$this->t['limitstart']		= $app->input->get( 'limitstart', 0, 'int' );
		$this->t['search']			= $app->input->get( 'search', '', 'string' );
		$this->t['sku']				= $app->input->get( 'sku', '', 'string' );//sku, ean, isbn, jan, ...
		$this->t['card']			= $app->input->get( 'card', '', 'string' );// loyalty customer card
		$this->t['page']			= $app->input->get( 'page', 'main.content.products', 'string' );
		$this->t['category']		= $app->input->get('category', '', 'string');// list of active categories
		
		$this->t['linkcheckout']	= JRoute::_(PhocacartRoute::getCheckoutRoute(0));
		$this->t['limitstarturl'] 	= $this->t['limitstart'] > 0 ? '&start='.$this->t['limitstart'] : '';
		
		$this->t['currency_array']	= PhocacartCurrency::getCurrenciesArray();
		$this->t['price'] 			= new PhocacartPrice();
		$this->t['categoryarray']	= explode(',', $this->t['category']);
		$this->t['ajax'] 			= 0;
		$this->t['shippingedit'] 	= 0;
		$this->t['paymentedit'] 	= 0;
		
		$preferredSku = PhocacartPos::getPreferredSku();
		$this->t['skutype']		= $preferredSku['name'];
		$this->t['skutypetxt']	= $preferredSku['title'];
		
		$this->t['user'] 	= array();
		$this->t['vendor']	= array();
		$this->t['ticket']	= array();
		$this->t['unit']	= array();
		$this->t['section']	= array();
		$dUser 				= PhocacartUser::defineUser($this->t['user'], $this->t['vendor'], $this->t['ticket'], $this->t['unit'], $this->t['section']);
		
		// 1) CHECK - VENDOR LOGGED IN
		if (!isset($this->t['vendor']->id) || (isset($this->t['vendor']->id) && (int)$this->t['vendor']->id < 1 )) {
			//$this->t['infotext'] = JText::_('COM_PHOCACART_PLEASE_LOGIN_ACCESS_POS');
			//$this->t['infotype'] = 'alert-error alert-danger';
			//parent::display('info');
			
			$returnUrl  						= 'index.php?option=com_users&view=login&return='.$this->t['actionbase64'];
			$app->redirect(JRoute::_($returnUrl, false), JText::_('COM_PHOCACART_PLEASE_LOGIN_ACCESS_POS'));
			return;
			
		}
		
		// PARAMS
		$this->t['display_new']				= $this->p->get( 'display_new', 0 );
		$this->t['cart_metakey'] 			= $this->p->get( 'cart_metakey', '' );
		$this->t['cart_metadesc'] 			= $this->p->get( 'cart_metadesc', '' );
		$this->t['image_width_cat']			= $this->p->get( 'image_width_cat', '' );
		$this->t['image_height_cat']		= $this->p->get( 'image_height_cat', '' );
		$this->t['columns_pos']				= $this->p->get( 'columns_pos', 6 );
		$this->t['display_addtocart_icon']	= $this->p->get( 'display_addtocart_icon', 0 );
		$this->t['category_addtocart']		= $this->p->get( 'category_addtocart', 1 );
		$this->t['dynamic_change_image']	= $this->p->get( 'dynamic_change_image', 0);
		$this->t['dynamic_change_price']	= $this->p->get( 'dynamic_change_price', 0 );
		$this->t['dynamic_change_stock']	= $this->p->get( 'dynamic_change_stock', 0 );
		$this->t['hide_price']				= 0;//$this->p->get( 'hide_price', 0 );
		$this->t['hide_addtocart']			= 0;//$this->p->get( 'hide_addtocart', 0 );
		$this->t['hide_add_to_cart_stock']	= $this->p->get( 'hide_add_to_cart_stock', 0 );
		$this->t['display_star_rating']		= $this->p->get( 'display_star_rating', 0 );
		$this->t['add_cart_method']			= $this->p->get( 'add_cart_method', 0 );
		$this->t['pos_hide_attributes']		= $this->p->get( 'pos_hide_attributes', 1 );
		$this->t['pos_display_stock_status']= $this->p->get( 'pos_display_stock_status', 0 );
		$this->t['pos_payment_force']		= $this->p->get( 'pos_payment_force', 0 );
		$this->t['pos_shipping_force']		= $this->p->get( 'pos_shipping_force', 0 );
		$this->t['pos_input_autocomplete']	= $this->p->get( 'pos_input_autocomplete', 0 );
		$this->t['pos_sku_input_type']		= $this->p->get( 'pos_sku_input_type', 'text' );
		$this->t['pos_input_type']			= $this->p->get( 'pos_input_type', 'text' );
		$this->t['display_shipping_desc']	= $this->p->get( 'display_shipping_desc', 0 );
		$this->t['display_payment_desc']	= $this->p->get( 'display_payment_desc', 0 );
		$this->t['zero_shipping_price']		= $this->p->get( 'zero_shipping_price', 1 );
		$this->t['zero_payment_price']		= $this->p->get( 'zero_payment_price', 1 );
		$this->t['enable_coupons']			= $this->p->get( 'enable_coupons', 1 );
		$this->t['enable_rewards']			= $this->p->get( 'enable_rewards', 1 );
		
		$this->t['display_view_product_button']				= $this->p->get( 'display_view_product_button', 1 );
		$this->t['product_name_link']						= $this->p->get( 'product_name_link', 0 );
		$this->t['switch_image_category_items']				= $this->p->get( 'switch_image_category_items', 0 );
		$this->t['pos_loyalty_card_number_input_type']		= $this->p->get( 'pos_loyalty_card_number_input_type', 'text' );
		
		
		$this->t['pos_input_autocomplete_output'] = '';
		if ($this->t['pos_input_autocomplete'] == 0) {
			$this->t['pos_input_autocomplete_output'] = ' autocomplete="off" ';
		}
		
		
		// CATEGORIES 
		$this->t['categories'] = PhocacartCategoryMultiple::getAllCategories(1, array(0,2));
		
		// LAYOUT
		PhocacartPos::renderPosPage();// render the page (boxes)
		
		// MEDIA
		$media = new PhocacartRenderMedia();
		$media->loadBootstrap();
		$media->loadChosen();
		$this->t['class-row-flex'] 	= $media->loadEqualHeights();
		$this->t['class_thumbnail'] = 'ph-pos-thumbnail';
		
		PhocacartRenderJs::renderAjaxAddToCart();
		PhocacartRenderJs::renderAjaxUpdateCart();
		PhocacartRenderJs::renderSubmitPaginationTopForm($this->t['action'], '#phPosContentBox');

		PhocacartRenderJs::managePos($this->t['action']);
		PhocacartRenderJs::printPos(JRoute::_( 'index.php?option=com_phocacart&view=order&tmpl=component&format=raw'));
		PhocacartRenderJs::searchPosByType('#phPosSearch');
		PhocacartRenderJs::searchPosByCategory();
		
		// Tendered
		$currency = PhocacartCurrency::getCurrency();
		PhocacartRenderJs::getPriceFormatJavascript($currency->price_decimals, $currency->price_dec_symbol, $currency->price_thousands_sep, $currency->price_currency_symbol, $currency->price_prefix, $currency->price_suffix, $currency->price_format);
		
		// UI
		PhocacartRenderJS::renderJsUi();
		
		if ($this->t['pos_hide_attributes'] == 0) {
			$media->loadPhocaAttributeRequired(1); // Some of the attribute can be required and can be a image checkbox
		}
		
		if ($this->t['dynamic_change_price'] == 1) {
			// items == category -> this is why items has class: ph-category-price-box (to have the same styling)
			PhocacartRenderJs::renderAjaxChangeProductPriceByOptions(0, 'Pos', 'ph-category-price-box');// We need to load it here
		}
		if ($this->t['dynamic_change_stock'] == 1) {
			PhocacartRenderJs::renderAjaxChangeProductStockByOptions(0, 'Pos', 'ph-item-stock-box');
		}
		
		
		
		// 2) CHECK TICKET
		if ((int)$this->t['ticket']->id < 1) {
			$this->t['infotext'] = JText::_('COM_PHOCACART_TICKET_DOES_NOT_EXIST');
			$this->t['infotype'] = 'alert-error alert-danger';
			parent::display('info');
			return true;
			
		}
		
		// 3) CHECK - SECTION EXISTS (if the asked not found, set the first existing)
		if (isset($this->t['section']->id)) {
			// Set in PhocacartUser::defineUser() -> PhocacartTicket::getTicket()
		} else {
			$this->t['section']->id = 0;
		}
		
		// 4) CHECK - UNIT EXISTS (if the asked not found, set the first existing but by the section
		if (isset($this->t['unit']->id)) {
			// Set in PhocacartUser::defineUser() -> PhocacartTicket::getTicket()
		} else {
			$this->t['unit']->id = 0;
		}
		
		$this->t['linkpos']				= JRoute::_(PhocacartRoute::getPosRoute($this->t['ticket']->id, $this->t['unit']->id, $this->t['section']->id));
			
		
		// 5) CHECK - USER
		$this->t['userexists'] 			= false;
		$this->t['anonymoususerexists'] = false;
		if (isset($this->t['user']->id) && (int)$this->t['user']->id && isset($this->t['user']->name)) {
			$this->t['userexists'] = true;
		} else {
			// Try to find anonymous user (only loyalty card number added - which is not stored in our database
			// such can be used for different features without having it stored in our database
			$this->t['loyalty_card_number'] = PhocacartPos::getCardByVendorAndTicket($this->t['vendor']->id, $this->t['ticket']->id, $this->t['unit']->id, $this->t['section']->id, 0);
			if ($this->t['loyalty_card_number'] != '') {
				$this->t['anonymoususerexists'] = true;
			}
		}
		
		$this->t['shippingmethodexists'] 	= false;
		$this->t['paymentmethodexists'] 	= false;
		
		// CART
		$this->cart	= new PhocacartCartRendercheckout();
		$this->cart->setType(array(0,2));
		$this->cart->setFullItems();
		$this->t['shippingid'] 	= $this->cart->getShippingId();
		
		if (isset($this->t['shippingid']) && (int)$this->t['shippingid'] > 0 && $this->t['shippingedit'] == 0) {
			$this->cart->addShippingCosts($this->t['shippingid']);
			$this->t['shippingmethodexists'] = true;
		}
		$this->t['paymentid'] 	= $this->cart->getPaymentId();
		if (isset($this->t['paymentid']) && (int)$this->t['paymentid'] > 0 && $this->t['paymentedit'] == 0) {
			$this->cart->addPaymentCosts($this->t['paymentid']);
			$this->t['paymentmethodexists'] = true;
		}
		
		$this->cart->roundTotalAmount();
		$this->t['total']		= $this->cart->getTotal();
		
		//$this->t['paymentexists'] 	= false;
		//$this->t['plugin-pdf']		= PhocacartUtilsExtension::getExtensionInfo('phocacart', 'plugin', 'phocapdf');
		//$this->t['component-pdf']		= PhocacartUtilsExtension::getExtensionInfo('com_phocapdf');
		

		$this->items						= $model->getItemList($this->t['user']->id, $this->t['vendor']->id, $this->t['ticket']->id, $this->t['unit']->id, $this->t['section']->id);
		$this->t['pagination']				= $model->getPagination();
		$this->t['ordering']				= $model->getOrdering();

		$this->_prepareDocument();
		$this->t['pathcat'] = PhocacartPath::getPath('categoryimage');
		$this->t['pathitem'] = PhocacartPath::getPath('productimage');
		
		
		switch ($this->t['page']) {
			
			case 'section':
				
				// Prepare units (in fact we asked for tickets because of ticket information
				// and we need to sort them to units
				$sortedItems = array();
				if (!empty($this->items)) {
					
					foreach($this->items as $k => $v) {
						$id = $v->id;
						$sortedItems[$id]['id'] 				= $v->id;
						$sortedItems[$id]['user_id'] 			= $v->user_id;
						$sortedItems[$id]['vendor_id'] 			= $v->vendor_id;
						//$sortedItems[$id]['ticket_id'] 			= $v->ticket_id;
						$sortedItems[$id]['unit_id'] 			= $v->unit_id;
						$sortedItems[$id]['section_id'] 		= $v->section_id;	
						$sortedItems[$id]['title'] 				= $v->title;
						$sortedItems[$id]['tickets'][$k]['cart']= $v->cart;
						$sortedItems[$id]['tickets'][$k]['id'] 	= $v->ticket_id;
					}
				}
				$this->items = $sortedItems;
				
				// Change the url bar (only to not confuse when the ticketid will be changed to existing from not existing)
				PhocacartRenderJs::changeUrlParameter( array(
				"sectionid" => (int)$this->t['section']->id));
				parent::display('section');
			break;
			
			default:
				
				// Scroll cart to bottom
				PhocacartRenderJs::renderJsScrollToPos();
				// Change the url bar (only to not confuse when the ticketid will be changed to existing from not existing)
				PhocacartRenderJs::changeUrlParameter( array(
				"ticketid" => (int)$this->t['ticket']->id,
				"unitid" => (int)$this->t['unit']->id,
				"sectionid" => (int)$this->t['section']->id));
				
				parent::display($tpl);
			break;
		}
	}
	

	protected function _prepareDocument() {
		$category = false;
		if (isset($this->category[0]) && is_object($this->category[0])) {
			$category = $this->category[0];
		}
		PhocacartRenderFront::prepareDocument($this->document, $this->p, $category);
	}
}
?>