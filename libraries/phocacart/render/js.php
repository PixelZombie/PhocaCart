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
final class PhocacartRenderJs
{
	private function __construct(){}	
	
	// =======
	// AJAX
	// =======
	
	public static function renderAjaxDoRequest($text) {
	
		$s 	= array();	 
		$s[] = 'function phDoRequest(url, manager, value) {';
		$s[] = 'var phAjaxTop = \'<div id="ph-ajaxtop-message"><div class="ph-loader-top"></div> \' + \''. htmlspecialchars($text).'\' + \'</div>\';';
		$s[] = '   jQuery("#ph-ajaxtop").html(phAjaxTop);';
		$s[] = '   jQuery("#ph-ajaxtop").show();';
		$s[] = '   var dataPost = {};';
		$s[] = '   dataPost[\'filename\'] = encodeURIComponent(value);';	
		$s[] = '   dataPost[\'manager\'] = manager;';
		$s[] = '   phRequestActive = jQuery.ajax({';
		$s[] = '      url: url,';
		$s[] = '      type:\'POST\',';
		$s[] = '      data:dataPost,';
		$s[] = '      dataType:\'JSON\',';
		$s[] = '      success:function(data){';
		$s[] = '         if ( data.status == 1 ){';
		$s[] = '            jQuery("#ph-ajaxtop-message").html(data.message);';
		$s[] = '            phRequestActive = null;';
		$s[] = '            setTimeout(function(){';
		$s[] = '		        jQuery("#ph-ajaxtop").hide(600);';
		$s[] = '		        jQuery(".ph-result-txt").remove();';
		$s[] = '	           }, 2500);';
		$s[] = '         } else {';
		$s[] = '	           jQuery("#ph-ajaxtop-message").html(data.error);';
		$s[] = '            phRequestActive = null;';
		$s[] = '	           setTimeout(function(){';
		$s[] = '		        jQuery("#ph-ajaxtop").hide(600);';
		$s[] = '		        jQuery(".ph-result-txt").remove();';
		$s[] = '	           }, 3500);';
		$s[] = '         }';
		$s[] = '      }';
		$s[] = '   });';
		$s[] = '}';
	
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	
	public static function renderAjaxDoRequestAfterChange($url, $manager = 'product', $value = 'imageCreateThumbs') {
		//$s[] = '       phDoRequest(\''.$url.'\', \''.$manager.'\', jQuery(this).val());';
		$s 	= array();
		$s[] = 'jQuery(document).ready(function() {';
		$s[] = '   jQuery( \'.'.$value.'\' ).on("change", function() {';
		$s[] = '       phDoRequest(\''.$url.'\', \''.$manager.'\', jQuery(this).val());';
		$s[] = '   })';
		$s[] = '})';
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	
	
	public static function renderAjaxDoRequestAfterPaste($url, $manager = 'product') {
		$s 	= array();
		$s[] = 'function phAddValue(id, title, titleModal) {';
		$s[] = '   document.getElementById(id).value = title;';
		//$s[] = '   SqueezeBox.close();';// close
		$s[] = '   jQuery(\'.modal\').modal(\'hide\');';
		$s[] = '   phDoRequest(\''.$url.'\', \''.$manager.'\', title );';
		$s[] = '}';

		//jQuery('.modal').on('hidden', function () {
		//  // Do something after close
		//});
		
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	
	public static function renderAjaxAddToCart() {
		
		$app					= JFactory::getApplication();
		$paramsC 				= PhocacartUtils::getComponentParameters();
		$add_cart_method		= $paramsC->get( 'add_cart_method', 0 );
		
		
		// We need to refresh checkout site when AJAX used for removing or adding products to cart
		$app 		= JFactory::getApplication();
		
		$task 		= 'checkout.add';
		$class		= '.phItemCartBox';
		if (PhocacartUtils::isView('checkout')) {
			$cView = 1;
		} else {
			$cView = 0;
		}
		
		
		// POS
		if (PhocacartUtils::isView('pos')) {
			$task 				= 'pos.add';
			$add_cart_method	= 1;// POS has always 1 (ajax and no popup)
			$cView 				= 0;
			$class		= '.phPosCartBox';
		}
		
		
		if ($add_cart_method == 0) {
			return false;
		}
		
		if ($add_cart_method == 2) {
			JHtml::stylesheet( 'media/com_phocacart/bootstrap/css/bs_modal_transition.css');
		}
		
		if ($add_cart_method > 0) {
			$urlAjax = JURI::base(true).'/index.php?option=com_phocacart&task='.$task.'&format=json&'. JSession::getFormToken().'=1&checkoutview='.(int)$cView;
			
			
			// ::ACTION - Ajax the form
			$s[] = 'function phDoSubmitFormAddToCart(sFormData) {';
			$s[] = '   var phUrl 	= "'. $urlAjax.'";';
			$s[] = '   var phCheckoutView = '.(int)$cView.'';
			$s[] = '   ';   
			$s[] = '   phRequest = jQuery.ajax({';
			$s[] = '      type: "POST",';
			$s[] = '      url: phUrl,';
			$s[] = '      async: "false",';
			$s[] = '      cache: "false",';
			$s[] = '      data: sFormData,';
			$s[] = '      dataType:"JSON",';
			$s[] = '      success: function(data){';
			$s[] = '         if (data.status == 1){';
			$s[] = '            jQuery("'.$class.'").html(data.item);';
			$s[] = '            jQuery("'.$class.'Count").html(data.count);';
			$s[] = '            jQuery("'.$class.'Total").html(data.total); ';
			
			
			// POS update message box (clean) and input box (when product added or changed - shipping and payment method must be cleaned)
			if (PhocacartUtils::isView('pos')) {
				$s[] = '    		var phUrlPos 	= phAddSuffixToUrl(window.location.href, \'format=raw\');';
				$s[] = '			var phDataInput = phPosCurrentData("main.input");';
				$s[] = '			phDoSubmitFormUpdateInputBox(phDataInput, phUrlPos);';// refresh input box
				$s[] = '			jQuery(".ph-pos-message-box").html(data.message);';// clear message box
				$s[] = '			phPosManagePage();';
			}
			
			if ($add_cart_method == 2) {
				$s[] = ' 			jQuery("body").append(jQuery("#phContainer"));';												
				$s[] = '            jQuery("#phContainer").html(data.popup);';
				$s[] = '            jQuery("#phAddToCartPopup").modal();';
			}
			if ($add_cart_method == 1) {
				// If no popup is displayed we can relaod the page when we are in comparison page
				// If popup, this will be done when clicking continue or comparison list
				$s[] = '            if (phCheckoutView == 1) {';
				$s[] = '               setTimeout(function() {location.reload();}, 0001);';
				$s[] = '            }';
			}
			$s[] = '         } else if (data.status == 0){';
			
			if ($add_cart_method != 2) {
				$s[] = '            jQuery(".phItemCartBox").html(data.error);';
			}
			if ($add_cart_method == 2) {
				$s[] = ' 			jQuery("body").append(jQuery("#phContainer"));';												
				$s[] = '            jQuery("#phContainer").html(data.popup);';
				$s[] = '            jQuery("#phAddToCartPopup").modal();';
			}
			if ($add_cart_method == 1) {
				// If no popup is displayed we can relaod the page when we are in comparison page
				// If popup, this will be done when clicking continue or comparison list
				$s[] = '            if (phCheckoutView == 1) {';
				$s[] = '               setTimeout(function() {location.reload();}, 0001);';
				$s[] = '            }';
			}
			

			// POS update message box (clean) and input box (when product added or changed - shipping and payment method must be cleaned)
			if (PhocacartUtils::isView('pos')) {
				//$s[] = '    		var phUrlPos 	= phAddSuffixToUrl(window.location.href, \'format=raw\');';
				//$s[] = '			var phDataInput = phPosCurrentData("main.input");';
				//$s[] = '			phDoSubmitFormUpdateInputBox(phDataInput, phUrlPos);';// refresh input box
				$s[] = '			jQuery(".ph-pos-message-box").html(data.error);';// clear message box
				$s[] = '			phPosManagePage();';
			}
			
			
			
			
			$s[] = '         } else {';
			//$s[] = '					// Don\'t change the price box';
			$s[] = '         }';
			$s[] = '      },';
		//	$s[] = '      error: function(data){}';
			$s[] = '   })';
			$s[] = '   return false;';	
			$s[] = '}';
			
			$s[] = ' ';
			
			// :: EVENT (CLICK) Category/Items View (icon/button - ajax/standard)
		/*	$s[] = 'function phEventClickFormAddToCart(phFormId) {';
			$s[] = '   var phForm = \'#\' + phFormId;';
			//$s[] = '   var sForm 	= jQuery(this).closest("form");';// Find in which form the right button was clicked
			$s[] = '   var sFormData = jQuery(phForm).serialize();';
			$s[] = '   phDoSubmitFormAddToCart(sFormData);';
			$s[] = '}';*/
			
			// Set it onclick as it is used in even not ajax submitting
		/*	$s[] = 'function phEventClickFormAddToCart(phFormId) {';
			$s[] = '   var phForm = \'#\' + phFormId;';
			$s[] = '   jQuery(\'phFormId\').find(\':submit\').click();"';
			$s[] = '   return false;';
			$s[] = '}';*/
			
			$s[] = ' ';
			
			
			
			// :: EVENT (SUBMIT) Item View
			$s[] = 'jQuery(document).ready(function(){';
			//$s[] = '	jQuery(".phItemCartBoxForm").on(\'submit\', function (e) {';// Not working when form is added by ajax
			$s[] = '	jQuery(document).on("submit", "form.phItemCartBoxForm", function (e) {';// Works with forms added by ajax
			$s[] = '		e.preventDefault();';
			$s[] = '	    var sFormData = jQuery(this).serialize();';
			$s[] = '	    phDoSubmitFormAddToCart(sFormData);';
			$s[] = '    })';
			$s[] = '})';
			
			JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
		}
	}
	
	public static function renderAjaxUpdateCart() {
		
		$app					= JFactory::getApplication();
		$paramsC 				= PhocacartUtils::getComponentParameters();
		
		// We need to refresh checkout site when AJAX used for removing or adding products to cart
		$app 		= JFactory::getApplication();
	
		$task 		= 'checkout.update';
		$class		= '.phCheckoutCartBox';
		if (PhocacartUtils::isView('checkout')) {
			$cView = 1;
		} else {
			$cView = 0;
		}
		
		// POS
		if (PhocacartUtils::isView('pos')) {
			$task 				= 'pos.update';
			$add_cart_method	= 1;// POS has always 1 (ajax and no popup)
			$cView 				= 0;
			$class		= '.phPosCartBox';
		}
		

		$urlAjax = JURI::base(true).'/index.php?option=com_phocacart&task='.$task.'&format=json&'. JSession::getFormToken().'=1&checkoutview='.(int)$cView;
		
		
		// ::ACTION - Ajax the form
		$s[] = 'function phDoSubmitFormUpdateCart(sFormData) {';
		$s[] = '   var phUrl 	= "'. $urlAjax.'";';
		$s[] = '   var phCheckoutView = '.(int)$cView.'';
		//$s[] = '   alert(sFormData);';   
		$s[] = '   phRequest = jQuery.ajax({';
		$s[] = '      type: "POST",';
		$s[] = '      url: phUrl,';
		$s[] = '      async: "false",';
		$s[] = '      cache: "false",';
		$s[] = '      data: sFormData,';
		$s[] = '      dataType:"JSON",';
		$s[] = '      success: function(data){';
		$s[] = '         if (data.status == 1){';
		$s[] = '            jQuery("'.$class.'").html(data.item);';
		$s[] = '            jQuery("'.$class.'Count").html(data.count);';
		$s[] = '            jQuery("'.$class.'Total").html(data.total); ';

		
		// POS update message box (clean) and input box (when product added or changed - shipping and payment method must be cleaned)
		if (PhocacartUtils::isView('pos')) {
			$s[] = '    		var phUrlPos 	= phAddSuffixToUrl(window.location.href, \'format=raw\');';
			$s[] = '			var phDataInput = phPosCurrentData("main.input");';
			$s[] = '			phDoSubmitFormUpdateInputBox(phDataInput, phUrlPos);';// refresh input box
			$s[] = '			jQuery(".ph-pos-message-box").html(data.message);';// clear message box
			$s[] = '			phPosManagePage();';
		}
		
			// If no popup is displayed we can relaod the page when we are in comparison page
			// If popup, this will be done when clicking continue or comparison list
		/*	$s[] = '            if (phCheckoutView == 1) {';
			$s[] = '               setTimeout(function() {location.reload();}, 0001);';
			$s[] = '            }';*/
		
		$s[] = '         } else if (data.status == 0){';
		

		
			// If no popup is displayed we can relaod the page when we are in comparison page
			// If popup, this will be done when clicking continue or comparison list
			/*$s[] = '            if (phCheckoutView == 1) {';
			$s[] = '               setTimeout(function() {location.reload();}, 0001);';
			$s[] = '            }';*/
		
		
		
		$s[] = '         } else {';
		//$s[] = '					// Don\'t change the price box';
		$s[] = '         }';
		$s[] = '      },';
	//	$s[] = '      error: function(data){}';
		$s[] = '   })';
		$s[] = '   return false;';	
		$s[] = '}';
		
		$s[] = ' ';
		
		
		
		// ::EVENT (CLICK) Change Layout Type Clicking on Grid, Gridlist, List
		$s[] = 'jQuery(document).ready(function(){';
		$s[] = '	jQuery(document).on("click", "form.phItemCartUpdateBoxForm button", function (e) {';
		$s[] = '		e.preventDefault();';
		$s[] = '	    var sForm 	= jQuery(this).closest("form");';// Find in which form the right button was clicked
		$s[] = '		var phAction= jQuery(this).val()';	
		$s[] = '	    var sFormData = sForm.serialize() + "&action=" + phAction;';
		$s[] = '	    phDoSubmitFormUpdateCart(sFormData);';	
		$s[] = '	})';
		$s[] = '})';
		
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	
	}
	
	
	public static function renderAjaxAddToCompare() {
		
		$app			= JFactory::getApplication();
		$paramsC 		= PhocacartUtils::getComponentParameters();
		$add_compare_method	= $paramsC->get( 'add_compare_method', 0 );
		
		// We need to refresh comparison site when AJAX used for removing or adding products to comparison list
		$app 		= JFactory::getApplication();
		$view 		= $app->input->get('view', '');
		$option 	= $app->input->get('option', '');
		if ($option == 'com_phocacart' && $view == 'comparison') {
			$cView = 1;
		} else {
			$cView = 0;
		}
		
		if ($add_compare_method == 0) {
			return false;
		}
		if ($add_compare_method == 2) {
			JHtml::stylesheet( 'media/com_phocacart/bootstrap/css/bs_modal_transition.css');
		}
		
		if ($add_compare_method > 0) {	
			$urlAjax = JURI::base(true).'/index.php?option=com_phocacart&task=comparison.add&format=json&'. JSession::getFormToken().'=1&comparisonview='.(int)$cView;
			//$s[] = 'jQuery(document).ready(function(){';
			//$s[] = '	jQuery(".phItemCompareBoxForm").on(\'submit\', function (e) {';
			$s[] = '	function phItemCompareBoxFormAjax(phItemId) {';
			$s[] = '		var phUrl 	= "'. $urlAjax.'";';
			$s[] = '		var phItem = \'#\' + phItemId;';
			$s[] = '		var phComparisonView = '.(int)$cView.'';
			$s[] = '		var phData = jQuery(phItem).serialize();';
			$s[] = ' ';		
			$s[] = '		phRequest = jQuery.ajax({';
			$s[] = '			type: "POST",';
			$s[] = '			url: phUrl,';
			$s[] = '			async: "false",';
			$s[] = '			cache: "false",';
			$s[] = '			data: phData,';
			$s[] = '			dataType:"JSON",';
			$s[] = '			success: function(data){';
			$s[] = '				if (data.status == 1){';
			$s[] = '					jQuery(".phItemCompareBox").html(data.item);';
			$s[] = '					jQuery(".phItemCompareBoxCount").html(data.count);';
			if ($add_compare_method == 2) {
				$s[] = ' 					jQuery("body").append(jQuery("#phContainer"));';												  
				$s[] = ' 					jQuery("#phContainer").html(data.popup);';
				$s[] = ' 					jQuery("#phAddToComparePopup").modal();';
			}
			if ($add_compare_method == 1) {
				// If no popup is displayed we can relaod the page when we are in comparison page
				// If popup, this will be done when clicking continue or comparison list
				$s[] = '						if (phComparisonView == 1) {';
				$s[] = self::renderOverlay();
				$s[] = '							setTimeout(function() {location.reload();}, 0001);';
				$s[] = '			   			}';
			}
			$s[] = '			   } else {';
			//$s[] = '					// Don\'t change the price box';
			$s[] = '			   }';
			$s[] = '			}';
			$s[] = '		})';
			//$s[] = '		e.preventDefault();';
			//$s[] = '       return false;';	
			$s[] = '	}';
			//$s[] = '})';
			JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
		}
	}
	
	public static function renderAjaxRemoveFromCompare() {
		
		$app			= JFactory::getApplication();
		$paramsC 		= PhocacartUtils::getComponentParameters();
		$add_compare_method	= $paramsC->get( 'add_compare_method', 0 );
		
		// We need to refresh comparison site when AJAX used for removing or adding products to comparison list
		$app 		= JFactory::getApplication();
		$view 		= $app->input->get('view', '');
		$option 	= $app->input->get('option', '');
		if ($option == 'com_phocacart' && $view == 'comparison') {
			$cView = 1;
		} else {
			$cView = 0;
		}
		
		
		if ($add_compare_method == 0) {
			return false;
		}
		if ($add_compare_method == 2) {
			JHtml::stylesheet( 'media/com_phocacart/bootstrap/css/bs_modal_transition.css');
		}
		
		if ($add_compare_method > 0) {	
			$urlAjax = JURI::base(true).'/index.php?option=com_phocacart&task=comparison.remove&format=json&'. JSession::getFormToken().'=1&comparisonview='.(int)$cView;
			//$s[] = 'jQuery(document).ready(function(){';
			//$s[] = '	jQuery(".phItemCompareBoxForm").on(\'submit\', function (e) {';
			$s[] = '	function phItemRemoveCompareFormAjax(phItemId) {';
			$s[] = '		var phUrl 	= "'. $urlAjax.'";';
			$s[] = '		var phItem = \'#\' + phItemId;';
			$s[] = '		var phComparisonView = '.(int)$cView.'';
			$s[] = '		var phData = jQuery(phItem).serialize();';
			$s[] = ' ';		
			$s[] = '		phRequest = jQuery.ajax({';
			$s[] = '			type: "POST",';
			$s[] = '			url: phUrl,';
			$s[] = '			async: "false",';
			$s[] = '			cache: "false",';
			$s[] = '			data: phData,';
			$s[] = '			dataType:"JSON",';
			$s[] = '			success: function(data){';
			$s[] = '				if (data.status == 1){';
			$s[] = '					jQuery(".phItemCompareBox").html(data.item);';
			$s[] = '					jQuery(".phItemCompareBoxCount").html(data.count);';
			if ($add_compare_method == 2) {
				// Display the popup
				$s[] = ' 					jQuery("#phContainerModuleCompare").html(data.popup);';
				$s[] = ' 					jQuery("#phRemoveFromComparePopup").modal();';
			}
			if ($add_compare_method == 1) {
				// If no popup is displayed we can relaod the page when we are in comparison page
				// If popup, this will be done when clicking continue or comparison list
				$s[] = '						if (phComparisonView == 1) {';
				$s[] = self::renderOverlay();
				$s[] = '							setTimeout(function() {location.reload();}, 0001);';
				$s[] = '			   			}';
			}	
			$s[] = '			   } else {';
			//$s[] = '					// Don\'t change the price box';
			$s[] = '			   }';
			$s[] = '			}';
			$s[] = '		})';
			//$s[] = '		e.preventDefault();';
			//$s[] = '       return false;';	
			$s[] = '	}';
			//$s[] = '})';
			JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
		}
	}
	
	

	public static function renderAjaxAddToWishList() {
		
		$app			= JFactory::getApplication();
		$paramsC 		= PhocacartUtils::getComponentParameters();
		$add_wishlist_method	= $paramsC->get( 'add_wishlist_method', 0 );
		
		// We need to refresh wishlist site when AJAX used for removing or adding products to wishlist list
		$app 		= JFactory::getApplication();
		$view 		= $app->input->get('view', '');
		$option 	= $app->input->get('option', '');
		if ($option == 'com_phocacart' && $view == 'wishlist') {
			$wView = 1;
		} else {
			$wView = 0;
		}
		
		if ($add_wishlist_method == 0) {
			return false;
		}
		if ($add_wishlist_method == 2) {
			JHtml::stylesheet( 'media/com_phocacart/bootstrap/css/bs_modal_transition.css');
		}
		
		if ($add_wishlist_method > 0) {	
			$urlAjax = JURI::base(true).'/index.php?option=com_phocacart&task=wishlist.add&format=json&'. JSession::getFormToken().'=1&wishlistview='.(int)$wView;
			//$s[] = 'jQuery(document).ready(function(){';
			//$s[] = '	jQuery(".phItemWishListBoxForm").on(\'submit\', function (e) {';
			$s[] = '	function phItemWishListBoxFormAjax(phItemId) {';
			$s[] = '		var phUrl 	= "'. $urlAjax.'";';
			$s[] = '		var phItem = \'#\' + phItemId;';
			$s[] = '		var phWishListView = '.(int)$wView.'';
			$s[] = '		var phData = jQuery(phItem).serialize();';
			$s[] = ' ';		
			$s[] = '		phRequest = jQuery.ajax({';
			$s[] = '			type: "POST",';
			$s[] = '			url: phUrl,';
			$s[] = '			async: "false",';
			$s[] = '			cache: "false",';
			$s[] = '			data: phData,';
			$s[] = '			dataType:"JSON",';
			$s[] = '			success: function(data){';
			$s[] = '				if (data.status == 1){';
			$s[] = '					jQuery(".phItemWishListBox").html(data.item);';
			$s[] = '					jQuery(".phItemWishListBoxCount").html(data.count);';
			if ($add_wishlist_method == 2) {
				$s[] = ' 					jQuery("body").append(jQuery("#phContainer"));';												  
				$s[] = ' 					jQuery("#phContainer").html(data.popup);';
				$s[] = ' 					jQuery("#phAddToWishListPopup").modal();';
			}
			if ($add_wishlist_method == 1) {
				// If no popup is displayed we can relaod the page when we are in wishlist page
				// If popup, this will be done when clicking continue or wishlist list
				$s[] = '						if (phWishListView == 1) {';
				$s[] = self::renderOverlay();
				$s[] = '							setTimeout(function() {location.reload();}, 0001);';
				$s[] = '			   			}';
			}
			$s[] = '			   } else {';
			//$s[] = '					// Don\'t change the price box';
			$s[] = '			   }';
			$s[] = '			}';
			$s[] = '		})';
			//$s[] = '		e.preventDefault();';
			//$s[] = '       return false;';	
			$s[] = '	}';
			//$s[] = '})';
			JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
		}
	}
	
	public static function renderAjaxRemoveFromWishList() {
		
		$app			= JFactory::getApplication();
		$paramsC 		= PhocacartUtils::getComponentParameters();
		$add_wishlist_method	= $paramsC->get( 'add_wishlist_method', 0 );
		
		// We need to refresh wishlist site when AJAX used for removing or adding products to wishlist list
		$app 		= JFactory::getApplication();
		$view 		= $app->input->get('view', '');
		$option 	= $app->input->get('option', '');
		if ($option == 'com_phocacart' && $view == 'wishlist') {
			$wView = 1;
		} else {
			$wView = 0;
		}
		
		
		if ($add_wishlist_method == 0) {
			return false;
		}
		if ($add_wishlist_method == 2) {
			JHtml::stylesheet( 'media/com_phocacart/bootstrap/css/bs_modal_transition.css');
		}
		
		if ($add_wishlist_method > 0) {	
		
			$urlAjax = JURI::base(true).'/index.php?option=com_phocacart&task=wishlist.remove&format=json&'. JSession::getFormToken().'=1&wishlistview='.(int)$wView;
			//$s[] = 'jQuery(document).ready(function(){';
			//$s[] = '	jQuery(".phItemWishListBoxForm").on(\'submit\', function (e) {';
			$s[] = '	function phItemRemoveWishListFormAjax(phItemId) {';
			$s[] = '		var phUrl 	= "'. $urlAjax.'";';
			$s[] = '		var phItem = \'#\' + phItemId;';
			$s[] = '		var phWishListView = '.(int)$wView.'';
			$s[] = '		var phData = jQuery(phItem).serialize();';
			$s[] = ' ';		
			$s[] = '		phRequest = jQuery.ajax({';
			$s[] = '			type: "POST",';
			$s[] = '			url: phUrl,';
			$s[] = '			async: "false",';
			$s[] = '			cache: "false",';
			$s[] = '			data: phData,';
			$s[] = '			dataType:"JSON",';
			$s[] = '			success: function(data){';
			$s[] = '				if (data.status == 1){';
			$s[] = '					jQuery(".phItemWishListBox").html(data.item);';
			$s[] = '					jQuery(".phItemWishListBoxCount").html(data.count);';
			if ($add_wishlist_method == 2) {
				// Display the popup
				$s[] = ' 					jQuery("#phContainerModuleWishList").html(data.popup);';
				$s[] = ' 					jQuery("#phRemoveFromWishListPopup").modal();';
			}
			if ($add_wishlist_method == 1) {
				// If no popup is displayed we can relaod the page when we are in wishlist page
				// If popup, this will be done when clicking continue or wishlist list
				$s[] = '						if (phWishListView == 1) {';
				$s[] = self::renderOverlay();
				$s[] = '							setTimeout(function() {location.reload();}, 0001);';
				$s[] = '			   			}';
			}	
			$s[] = '			   } else {';
			//$s[] = '					// Don\'t change the price box';
			$s[] = '			   }';
			$s[] = '			}';
			$s[] = '		})';
			//$s[] = '		e.preventDefault();';
			//$s[] = '       return false;';	
			$s[] = '	}';
			//$s[] = '})';
			JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
		}
	}
	
	public static function renderAjaxQuickViewBox() {
		
		
		$app					= JFactory::getApplication();
		$paramsC 				= PhocacartUtils::getComponentParameters();
		$dynamic_change_price 	= $paramsC->get( 'dynamic_change_price', 1 );
		$load_chosen 			= $paramsC->get( 'load_chosen', 1 );
		$media 					= new PhocacartRenderMedia();
		self::renderPhocaAttribute();// needed because of phChangeAttributeType()
		
		
		// We need to refresh comparison site when AJAX used for removing or adding products to comparison list
		$app 		= JFactory::getApplication();
		$view 		= $app->input->get('view', '');
		$option 	= $app->input->get('option', '');
		/*if ($option == 'com_phocacart' && $view == 'comparison') {
			$cView = 1;
		} else {
			$cView = 0;
		}*/
		
		
		JHtml::stylesheet( 'media/com_phocacart/bootstrap/css/bs_modal_transition.css');
	
		
	
		$urlAjax = JURI::base(true).'/index.php?option=com_phocacart&view=item&format=json&tmpl=component&'. JSession::getFormToken().'=1';
		//$s[] = 'jQuery(document).ready(function(){';
		//$s[] = '	jQuery(".phItemCompareBoxForm").on(\'submit\', function (e) {';
		$s[] = '	function phItemQuickViewBoxFormAjax(phItemId) {';
		$s[] = '		var phUrl 	= "'. $urlAjax.'";';
		$s[] = '		var phItem = \'#\' + phItemId;';
		//$s[] = '		var phComparisonView = '.(int)$cView.'';
		$s[] = '		var phData = jQuery(phItem).serialize();';
		$s[] = ' ';		
		$s[] = '		phRequest = jQuery.ajax({';
		$s[] = '			type: "POST",';
		$s[] = '			url: phUrl,';
		$s[] = '			async: "false",';
		$s[] = '			cache: "false",';
		$s[] = '			data: phData,';
		$s[] = '			dataType:"JSON",';
		$s[] = '			success: function(data){';
		$s[] = '				if (data.status == 1){';
		//$s[] = '					jQuery("#phItemCompareBox").html(data.item);';
		
		
		
		//$s[] = ' 					jQuery("#phQuickViewPopupBody").html(data.popup);'; added in ajax
		/////$s[] = ' 				jQuery("#phContainer").html(data.popup); ';
		$s[] = ' 					jQuery(".phjItemQuick.phjProductAttribute").remove(); ';// Clear attributes from dom when ajax reload
		$s[] = ' 					jQuery("body").append(jQuery("#phContainer"));';
		$s[] = ' 					jQuery("#phContainer").html(data.popup); ';
		
		/////$s[] = ' 				jQuery("#phQuickViewPopup").modal();';
		$s[] = ' 					jQuery("body").append(jQuery("#phQuickViewPopup"));';
		$s[] = ' 					jQuery("#phQuickViewPopup").modal();';
		if ($load_chosen == 1) {
			
			// TO DO 
			// Chosen cannot be dynamically recreated in case
			// we want to add support for mobiles and have support for html required forms (browser checks for html required forms)
			// Now choosen is disables on mobile devices so when we reload choosen for standard devices
			// we lost the select boxes on mobiles
			//$s[] = '	  				jQuery(\'select\').chosen({disable_search_threshold : 10,allow_single_deselect : true});';//Reload Chosen
			// This seems to work
			//$s[] = '	  jQuery(\'select\').chosen({disable_search_threshold : 10,allow_single_deselect : true});';
			$s[] = '	  jQuery(\'select\').chosen(\'destroy\').chosen({disable_search_threshold : 10,allow_single_deselect : true});';
		}
		
		if ($dynamic_change_price == 1) {
			//$s[] = '					phAjaxChangePrice();';
		}
		
		$s[] = 'phChangeAttributeType(\'ItemQuick\');';// Recreate the select attribute (color, image) after AJAX
		
		$s[] = '			'. $media->loadTouchSpin('quantity');// Touch spin for input
			
		$s[] = '			   } else {';
		//$s[] = '					// Don\'t change the price box';
		$s[] = '			   }';
		$s[] = '			}';
		$s[] = '		})';
		//$s[] = '		e.preventDefault();';
		//$s[] = '       return false;';	
		$s[] = '	}';
		//$s[] = '})';
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	
	
	/*
	 * Change Price
	 * select box (standard, image, color)
	 * check box
	 */

	public static function renderAjaxChangeProductPriceByOptions($id = 0, $typeView = '', $class = '') {
		
		$app			= JFactory::getApplication();
		$paramsC 		= PhocacartUtils::getComponentParameters();
		$dynamic_change_price = $paramsC->get( 'dynamic_change_price', 0 );
		
		$app			= JFactory::getApplication();
		$option			= $app->input->get( 'option', '', 'string' );
		$view			= $app->input->get( 'view', '', 'string' );
		
		
		if ($dynamic_change_price == 0) {
			return false;
		}
		//if ($id == 0) {
		//	$idJs = 'var phId = phProductId;'. "\n";
		//	$idJs .= 'var phIdItem = "#phItemPriceBox'.$typeView.'" + phProductId;';
	/*	} else {
			$idJs = 'var phId = '.(int)$id.';'. "\n";
			$idJs .= 'var phIdItem = "#phItemPriceBox'.(int)$id.'";';
		}*/

		$urlAjax = JURI::base(true).'/index.php?option=com_phocacart&task=checkout.changepricebox&format=json&'. JSession::getFormToken().'=1';
		
		
		
		$s[] = '	function phAjaxChangePrice'.$typeView.'(phProductId, phDataA1, phDataA2){';
		$s[] = '		var phUrl 		= "'. $urlAjax.'";';
		$s[] = '		var phId 		= phProductId;'. "\n";
		$s[] = '		var phIdItem 	= "#phItemPriceBox'.$typeView.'" + phProductId;';
		$s[] = '		var phClass 	= "'.$class.'";';
		$s[] = '		var phTypeView 	= "'.$typeView.'";';
		
		$s[] = '		var phData 	= \'id=\'+phId+\'&\'+phDataA1+\'&\'+phDataA2+\'&\'+\'class=\'+phClass+\'&\'+\'typeview=\'+phTypeView;';
		$s[] = '		jQuery.ajax({';
		$s[] = '			type: "POST",';
		$s[] = '			url: phUrl,';
		$s[] = '			async: "false",';
		$s[] = '			cache: "false",';
		$s[] = '			data: phData,';
		$s[] = '			dataType:"JSON",';
		$s[] = '			success: function(data){';
		$s[] = '				if (data.status == 1){';
		$s[] = '					jQuery(phIdItem).html(data.item);';
		$s[] = '			   } else {';
		//$s[] = '					// Don\'t change the price box, don't render any error message
		$s[] = '			   }';
		$s[] = '			}';
		$s[] = '		})';
		$s[] = '	}';
		$s[] = ' ';
		
		$s[] = 'jQuery(document).ready(function(){';
		
		
		// Select Box
		$s[] = '	jQuery(document).on(\'change\', \'select.phj'.$typeView.'.phjProductAttribute\', function(){';	
		//$s[] = '		jQuery(this).off("change");';
		$s[] = '		var phProductId = jQuery(this).data(\'product-id\');';
		$s[] = '		var phProductGroup = \'.phjAddToCartV'.$typeView.'P\' + phProductId;';
		// All Selects
		$s[] = '		var phDataA1 = jQuery(phProductGroup).find(\'select\').serialize();';
		// All Checkboxes
		$s[] = '		var phDataA2 = jQuery(phProductGroup).find(\':checkbox\').serialize();';
		
		$s[] = '		phAjaxChangePrice'.$typeView.'(phProductId, phDataA1, phDataA2);';
	
		$s[] = '	})';
		
		// Checkbox
		// Unfortunately, we cannot run event:
		// 1. CHANGE - because of Bootstrap toogle button, this will run 3x ajax (checkbox is hidden and changes when clicking on button)
		// 2. CLICK directly on checkbox as if Bootstrap toogle button is use, clicked will be icon not the checkbox
		// So we run click on div box over the checkbox which works and don't run ajax 3x
		//$s[] = '	jQuery(document).on(\'change\', \'.ph-checkbox-attribute.phj'.$typeView.'.phjProductAttribute :checkbox\', function(){';
		//$s[] = '	jQuery(document).on(\'click\', \'#phItemPriceBoxForm .ph-checkbox-attribute.ph-item-input-set-attributes\', function(){';		
		$s[] = '	jQuery(document).on(\'click\', \'.ph-checkbox-attribute.phj'.$typeView.'.phjProductAttribute\', function(e){';
		
		// Prevent from twice running
		$s[] = '        if (e.target.tagName.toUpperCase() === "LABEL") { return;}';
		
		$s[] = '		var phProductId = jQuery(this).data(\'product-id\');';
		$s[] = '		var phProductGroup = \'.phjAddToCartV'.$typeView.'P\' + phProductId;';
		// All Selects
		$s[] = '		var phDataA1 = jQuery(phProductGroup).find(\'select\').serialize();';
		// All Checkboxes
		$s[] = '		var phDataA2 = jQuery(phProductGroup).find(\':checkbox\').serialize();';
		
		$s[] = '		phAjaxChangePrice'.$typeView.'(phProductId, phDataA1, phDataA2);';
		$s[] = '	})';
		
		// Change the price on time view when site is initialized
		// Because some parameters can be selected as default
		// Automatically start only in item view, not in category or another view
		/*if ($option == 'com_phocacart' && $view == 'item') {
			//$s[] = '		var phProductId = jQuery(\'.phjItemAttribute\').data(\'product-id\')';
			$s[] = '		var phDataA1 = jQuery("select.phjItemAttribute").serialize();';
			$s[] = '		var phDataA2 = jQuery(".ph-checkbox-attribute.phjItemAttribute :checkbox").serialize();';
			$s[] = '		var phpDataA = phDataA1 +\'&\'+ phDataA2;';
			$s[] = '		phAjaxChangePrice'.$typeView.'('.(int)$id.');';
		}*/
		
		
		$s[] = '})';
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	
	
	/*
	 * Change Stock
	 * select box (standard, image, color)
	 * check box
	 */

	public static function renderAjaxChangeProductStockByOptions($id = 0, $typeView = '', $class = '') {
		
		$app					= JFactory::getApplication();
		$paramsC 				= PhocacartUtils::getComponentParameters();
		$dynamic_change_stock 	= $paramsC->get( 'dynamic_change_stock', 0 );
		$hide_add_to_cart_stock = $paramsC->get( 'hide_add_to_cart_stock', 0 );
		
		$app					= JFactory::getApplication();
		$option					= $app->input->get( 'option', '', 'string' );
		$view					= $app->input->get( 'view', '', 'string' );
		
		
		if ($dynamic_change_stock == 0) {
			return false;
		}

		$urlAjax = JURI::base(true).'/index.php?option=com_phocacart&task=checkout.changestockbox&format=json&'. JSession::getFormToken().'=1';
		
		$s[] = '	function phAjaxChangeStock'.$typeView.'(phProductId, phDataA1, phDataA2){';
		$s[] = '		var phUrl 					= "'. $urlAjax.'";';
		$s[] = '		var phId 					= phProductId;'. "\n";
		$s[] = '		var phIdItem 				= "#phItemStockBox'.$typeView.'" + phProductId;';
		$s[] = '		var phProductAddToCart 		= ".phProductAddToCart'.$typeView.'" + phProductId;';// display or hide add to cart button
		$s[] = '		var phProductAddToCartIcon 	= ".phProductAddToCartIcon'.$typeView.'" + phProductId;';// display or hide add to cart icon
		$s[] = '		var phClass 				= "'.$class.'";';
		$s[] = '		var phTypeView 				= "'.$typeView.'";';
		
		$s[] = '		var phData 	= \'id=\'+phId+\'&\'+phDataA1+\'&\'+phDataA2+\'&\'+\'class=\'+phClass+\'&\'+\'typeview=\'+phTypeView;';
		$s[] = '		jQuery.ajax({';
		$s[] = '			type: "POST",';
		$s[] = '			url: phUrl,';
		$s[] = '			async: "false",';
		$s[] = '			cache: "false",';
		$s[] = '			data: phData,';
		$s[] = '			dataType:"JSON",';
		$s[] = '			success: function(data){';
		$s[] = '				if (data.status == 1){';
		
		if ($hide_add_to_cart_stock == 1) {
			$s[] = '					if (data.stock < 1) {';
			//$s[] = '						jQuery(phProductAddToCart).hide();';
			$s[] = '						jQuery(phProductAddToCart).css(\'visibility\', \'hidden\');';
			$s[] = '						jQuery(phProductAddToCartIcon).css(\'display\', \'none\');';
			
			$s[] = '					} else {';
			//$s[] = '						jQuery(phProductAddToCart).show();';
			$s[] = '						jQuery(phProductAddToCart).css(\'visibility\', \'visible\');';
			$s[] = '						jQuery(phProductAddToCartIcon).css(\'display\', \'block\');';
			$s[] = '					}';
		}
		
		$s[] = '					jQuery(phIdItem).html(data.item);';
		$s[] = '			   } else {';
		//$s[] = '					// Don\'t change the price box, don't render any error message
		$s[] = '			   }';
		$s[] = '			}';
		$s[] = '		})';
		$s[] = '	}';
		$s[] = ' ';
		
		$s[] = 'jQuery(document).ready(function(){';
		
		
		// Select Box
		$s[] = '	jQuery(document).on(\'change\', \'select.phj'.$typeView.'.phjProductAttribute\', function(){';	
		//$s[] = '		jQuery(this).off("change");';
		$s[] = '		var phProductId = jQuery(this).data(\'product-id\');';
		$s[] = '		var phProductGroup = \'.phjAddToCartV'.$typeView.'P\' + phProductId;';
		// All Selects
		$s[] = '		var phDataA1 = jQuery(phProductGroup).find(\'select\').serialize();';
		// All Checkboxes
		$s[] = '		var phDataA2 = jQuery(phProductGroup).find(\':checkbox\').serialize();';
		
		$s[] = '		phAjaxChangeStock'.$typeView.'(phProductId, phDataA1, phDataA2);';
		$s[] = '	})';
			
		$s[] = '	jQuery(document).on(\'click\', \'.ph-checkbox-attribute.phj'.$typeView.'.phjProductAttribute\', function(e){';
		
		// Prevent from twice running
		$s[] = '        if (e.target.tagName.toUpperCase() === "LABEL") { return;}';
		
		$s[] = '		var phProductId = jQuery(this).data(\'product-id\');';
		$s[] = '		var phProductGroup = \'.phjAddToCartV'.$typeView.'P\' + phProductId;';
		// All Selects
		$s[] = '		var phDataA1 = jQuery(phProductGroup).find(\'select\').serialize();';
		// All Checkboxes
		$s[] = '		var phDataA2 = jQuery(phProductGroup).find(\':checkbox\').serialize();';
		
		$s[] = '		phAjaxChangeStock'.$typeView.'(phProductId, phDataA1, phDataA2);';
		$s[] = '	})';
		
		$s[] = '})';
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}

	
	/*
	 * Javascript for Pagination TOP
	 * - change Layout Type: Grid, List, Gridlist
	 * - change pagination
	 * - change ordering
	 * with help of AJAX
	 * 
	 * This function is used to reload POS main box by ajax
	 */
	
	public static function renderSubmitPaginationTopForm($urlAjax, $outputDiv) {
		
		$app						= JFactory::getApplication();
		$paramsC 					= PhocacartUtils::getComponentParameters();
		$equal_height				= $paramsC ->get( 'equal_height', 0 );// reload equal height
		$load_chosen				= $paramsC ->get( 'load_chosen', 1 );// reload choosen
		$ajax_pagination_category	= $paramsC ->get( 'ajax_pagination_category', 0 );
		
		// loading.gif
		$overlay1 = PhocacartRenderJs::renderLoaderDivOverlay($outputDiv);
		$overlay2 = PhocacartRenderJs::renderLoaderFullOverlay();
		
	
		
		self::renderPhocaAttribute();// needed because of phChangeAttributeType()
		
		// ::ACTION Ajax for top pagination: pagination/ordering/layouttype
		$s[] = 'function phDoSubmitFormPaginationTop(sFormData, phUrlJs) {';
		//$s[] = '    	e.preventDefault();';

		$s[] = $overlay1['start'];
		
		//if (PhocacartUtils::isView('pos')) {
		//	$s[] = '    var phUrl 	= phAddSuffixToUrl(window.location.href, \'format=raw\');';
		//} else {
			$s[] = '		var phUrl 	= "'. $urlAjax.'";';
		//}
		$s[] = '		phUrl 		= typeof phUrlJs !== "undefined" ? phUrlJs : phUrl;';
		$s[] = '		phRequest = jQuery.ajax({';
		$s[] = '			type: "POST",';
		$s[] = '			url: phUrl,';
		//$s[] = '			async: false,';
		$s[] = '			async: true,';
		$s[] = '			cache: "false",';
		$s[] = '			data: sFormData,';
		$s[] = '			dataType:"HTML",';
		$s[] = '			success: function(data){';
		$s[] = '				jQuery("'.$outputDiv.'").html(data);';
		
		if (PhocacartUtils::isView('pos')) {
			$s[] = '			phPosManagePage()';
		}
		
		if ($load_chosen) {
			//$s[] = '	  jQuery(\'select\').chosen({disable_search_threshold : 10,allow_single_deselect : true});';
			$s[] = '	  jQuery(\'select\').chosen(\'destroy\').chosen({disable_search_threshold : 10,allow_single_deselect : true});';
		
		}
		
		if ($equal_height) {
			//$s[] = '	   jQuery(\'.ph-thumbnail-c.grid\').matchHeight();';// FLEXBOX USED
		}
		
		$s[] = 'phChangeAttributeType();';// Recreate the select attribute (color, image) after AJAX
		$s[] = $overlay1['end'];
		$s[] = '			}';
		$s[] = '		})';
		//$s[] = '		e.preventDefault();';

		$s[] = '       return false;';	
		$s[] = '}';
		
		$s[] = ' ';
		
		// ::EVENT (CLICK) Change Layout Type Clicking on Grid, Gridlist, List
		$s[] = 'jQuery(document).ready(function(){';
		$s[] = '	jQuery(".phItemSwitchLayoutType").on(\'click\', function (e) {';
		$s[] = '	    var phDataL = jQuery(this).data("layouttype");';// Get the right button (list, grid, gridlist)
		$s[] = '	    var sForm 	= jQuery(this).closest("form");';// Find in which form the right button was clicked
		$s[] = '	    var sFormData = sForm.serialize() + "&layouttype=" + phDataL;';
		$s[] = '	    jQuery(".phItemSwitchLayoutType").removeClass("active");';
		$s[] = '	    jQuery(".phItemSwitchLayoutType." + phDataL).addClass("active");';
		$s[] = '        var phUrl = window.location.href;';
		$s[] = '		phDoSubmitFormPaginationTop(sFormData, phUrl);';	
		$s[] = '	})';
		$s[] = '})';
		
		
		// ::EVENT (CLICK) Pagination - Clicking on Start Prev 1 2 3 Next End
		if ($ajax_pagination_category == 1 || PhocacartUtils::isView('pos')) {
			$s[] = 'jQuery(document).ready(function(){';
			$s[] = '	jQuery(document).on(\'click\', ".phPaginationBox .pagination li a", function (e) {';
			$s[] = '		var phUrl = jQuery(this).attr("href");';
			$s[] = '	    var sForm 	= jQuery(this).closest("form");';// Find in which form the right button was clicked
			$s[] = '	    var sFormData = sForm.serialize();';
			$s[] = '		phDoSubmitFormPaginationTop(sFormData, phUrl);';
			
			// Don't set format for url bar (e.g. pagination uses ajax with raw - such cannot be set in url bar)
			// we use ajax and pagination for different views inside one view (customers, products, orders) so we cannot set this parameter in url, because of ajax
			if (PhocacartUtils::isView('pos')) {
				$s[] = '		phUrl = phRemoveUrlParameter("format", phUrl);';
				$s[] = '		phUrl = phRemoveUrlParameter("start", phUrl);';
			}
			
			$s[] = '		window.history.pushState("", "", phUrl);';// change url bar
			$s[] = '		e.preventDefault();';
			$s[] = '	})';
			$s[] = '})';
		}
		
		// ::EVENT (CHANGE) Automatically reload of the pagination/ordering form Clicking on Ordering and Display Num
		$s[] = 'function phEventChangeFormPagination(sForm, sItem) {';
		$s[] = '   var phA = 1;';// Full Overlay Yes
		
		
		// If pagination changes on top (ordering or display num then the bottom pagination is reloaded by ajax
		// But if bottom pagination changes, the top pagination is not reloaded
		// so we need to copy the bottom values from ordering and display num selectbox
		// and set it to top
		// top id: itemorderingtop, limittop
		// bottom id: itemordering, limit
		$s[] = '   var phSelectBoxVal  	= jQuery(sItem).val();';
		$s[] = '   var phSelectBoxId 	= "#" + jQuery(sItem).attr("id") + "top";';
		$s[] = '   jQuery(phSelectBoxId).val(phSelectBoxVal);';
		
		
		$s[] = '   var formName = jQuery(sForm).attr("name");';
		
		if ($ajax_pagination_category == 1 || PhocacartUtils::isView('pos')) {
			// Everything is AJAX - pagination top even pagination bottom
			$s[] = '   var phUrl = window.location.href;';
			$s[] = '   phDoSubmitFormPaginationTop(jQuery(sForm).serialize(), phUrl);';
		} else {
			// Only top pagination is ajax, bottom pagination is not ajax start prev 1 2 3 next end
			$s[] = '   if (formName == "phitemstopboxform") {';// AJAX - Top pagination always ajax
			$s[] = '       var phUrl = window.location.href;';
			$s[] = '       phDoSubmitFormPaginationTop(jQuery(sForm).serialize(), phUrl);';
			$s[] = '   } else {';
			$s[] = '	   sForm.submit();'; // STANDARD
			$s[] = $overlay2;
			$s[] = '   }';
		}

		$s[] = '}';
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	
	
	
	
	/* =============
	 * POS
	 * =============
	 */
	
	
	/*
	 * Main Pos function
	 * ajax to render all pages in content area
	 * url ... current url
	 * outputDiv ... input box div (cart div is reloaded, main div is redirected)
	 */
	public static function managePos($url) {
		
		$app						= JFactory::getApplication();
		$paramsC 					= PhocacartUtils::getComponentParameters();
		$pos_focus_input_fields		= $paramsC ->get( 'pos_focus_input_fields', 0 );
		
		
		JFactory::getDocument()->addScript(JURI::root(true).'/media/com_phocacart/js/base64.js');
		
		$s 	= array();
		
		
		// ------------
		// FUNCTIONS
		// ------------
		
		/*
		 * Update input box after change
		 */
		$s[] = ' function phDoSubmitFormUpdateInputBox(sFormData, phUrlAjax) {';
		$s[] = '	phRequest = jQuery.ajax({';
		$s[] = '		type: "POST",';
		$s[] = '		url: phUrlAjax,';
		//$s[] = '		async: false,';
		$s[] = '		async: true,';
		$s[] = '		cache: "false",';
		$s[] = '		data: sFormData,';
		$s[] = '		dataType:"HTML",';
		$s[] = '		success: function(data){';
		$s[] = '			jQuery("#phPosInputBox").html(data);';
		$s[] = '		}';
		$s[] = '	})';
		$s[] = '    return false;';	
		$s[] = ' }';
		
		$s[] = ' ';
		
		/*
		 * Update categories box after change (users can have different access rights for different catgories, so when selecting user, categories must be changed)
		 */
		$s[] = ' function phDoSubmitFormUpdateCategoriesBox(sFormData, phUrlAjax) {';
		
		// Change categories only when customer changed
		$s[] = '	var page 	= jQuery("#phPosPaginationBox input[name=page]").val();';
		$s[] = '	if (page != "main.content.customers") {';
		$s[] = '		return false;';
		$s[] = '	}';
		
		$s[] = '	phRequest = jQuery.ajax({';
		$s[] = '		type: "POST",';
		$s[] = '		url: phUrlAjax,';
		//$s[] = '		async: false,';
		$s[] = '		async: true,';
		$s[] = '		cache: "false",';
		$s[] = '		data: sFormData,';
		$s[] = '		dataType:"HTML",';
		$s[] = '		success: function(data){';
		$s[] = '			jQuery("#phPosCategoriesBox").html(data);';
		$s[] = '		}';
		$s[] = '	})';
		$s[] = '    return false;';	
		$s[] = ' }';
		
		$s[] = ' ';
		
		/*
		 * Main content box can be variable: products/customers/payment/shippment
		 * Get info about current ticket id and page (page: products, customers, payment, shipping)
		 * 
		 */
		$s[] = ' function phPosCurrentData(forcepage, format, id) {';
		$s[] = '	if (typeof forcepage !== "undefined") {';
		$s[] = '		var page 	= forcepage;';
		$s[] = '	} else {';
		$s[] = '		var page 	= jQuery("#phPosPaginationBox input[name=page]").val();';
		$s[] = '	}';
		
		$s[] = '	if (typeof format !== "undefined") {';
		$s[] = '		var formatSuffix = format;';
		$s[] = '	} else {';
		$s[] = '		var formatSuffix = "raw";';
		$s[] = '	}';
		
		$s[] = '	if (typeof id !== "undefined") {';
		$s[] = '		var idSuffix 	= "&id="+id;';
		$s[] = '	} else {';
		$s[] = '		var idSuffix 	= "";';
		$s[] = '	}';
		
		$s[] = '	var ticketid	= jQuery("#phPosPaginationBox input[name=ticketid]").val();';
		$s[] = '	var unitid		= jQuery("#phPosPaginationBox input[name=unitid]").val();';
		$s[] = '	var sectionid	= jQuery("#phPosPaginationBox input[name=sectionid]").val();';
		$s[] = '	var phData		= "format=" + formatSuffix + "&tmpl=component&page=" + page + idSuffix +"&ticketid=" + ticketid + "&unitid=" + unitid + "&sectionid=" + sectionid + "&'. JSession::getFormToken().'=1";';
		$s[] = '	return phData;';	
		$s[] = ' }';
		
		$s[] = ' ';
		
		/*
		 * When chaning main page, clear all filters (e.g. going from product list to customer list)
		 * Category - remove url parameters in url bar, then empty all checkboxes
		 * Search - remove url parameters in url bar, then empty search input field
		 */
		$s[] = ' function phPosClearFilter() {';
		$s[] = '	phUpdateUrlParameter("category", "");';
		$s[] = '	jQuery("input.phPosCategoryCheckbox:checkbox:checked").prop("checked", false);';
		$s[] = '	jQuery("label.phCheckBoxCategory").removeClass("active");';
		$s[] = '	phUpdateUrlParameter("search", "");';	
		$s[] = '	jQuery("#phPosSearch").val("");';
		$s[] = ' }';
		
		$s[] = ' ';
		
		/*
		 * Focus on form input if asked (sku, loyalty card, coupon, tendered amount)
		 */
		$s[] = ' function phPosManagePageFocus(page) {';
		
		if ($pos_focus_input_fields	== 1) {
			$s[] = '	if (page == "main.content.products") {';
			$s[] = '		var hasFocusSearch = jQuery("#phPosSearch").is(":focus");';
			$s[] = '		if (!hasFocusSearch) {';
			$s[] = '			jQuery("#phPosSku").focus();';
			$s[] = '		}';
			$s[] = '	} else if (page == "main.content.customers") {';
			$s[] = '		var hasFocusSearch = jQuery("#phPosSearch").is(":focus");';
			$s[] = '		if (!hasFocusSearch) {';
			$s[] = '			jQuery("#phPosCard").focus();';
			$s[] = '		}';
			$s[] = '	} else if (page == "main.content.paymentmethods") {';
			$s[] = '		var hasFocusSearch = jQuery("#phPosSearch").is(":focus");';
			$s[] = '		if (!hasFocusSearch) {';
			$s[] = '			jQuery("#phcoupon").focus();';
			$s[] = '		}';
			$s[] = '	} else if (page == "main.content.payment") {';
			$s[] = '			jQuery("#phAmountTendered").focus();';
			$s[] = '	}';
		} else {
			$s[] = '	return true;';
		}
		$s[] = ' }';
		
		
		/*
		 * Manage view after ajax request (hide or display different parts on site)
		 * 1) Hide categories for another views than products
		 */
		$s[] = ' function phPosManagePage() {';
		$s[] = '	var page 	= jQuery("#phPosPaginationBox input[name=page]").val();';
		
		// we use ajax and start parameter can be used for more items (products, customers, orders) so we cannot leave it in url
		// because if there are 100 products and 10 customers - switching to customers per ajax will leave e.g. 50 which is will display zero results
		// START IS SET ONLY WHEN CLICKING ON PAGINATION LINKS (see: renderSubmitPaginationTopFor, it is removed directly by click
		//$s[] = 'phUpdateUrlParameter("start", "");';
		
		//$s[] = '	alert(page);';
		$s[] = '	if (page == "main.content.products") {'; // PRODUCTS
		$s[] = '		jQuery(".ph-pos-checkbox-box").show();';
		$s[] = '		jQuery(".ph-pos-sku-product-box").show();';
		$s[] = '		jQuery(".ph-pos-card-user-box").hide();';
		$s[] = '		jQuery(".ph-pos-search-box").show();';
		$s[] = '		jQuery(".ph-pos-date-order-box").hide();';
		$s[] = '		phPosManagePageFocus(page);';// Focus on start
		$s[] = '	} else if (page == "main.content.customers") {'; // CUSTMERS
		$s[] = '		jQuery(".ph-pos-checkbox-box").hide();';//categories
		$s[] = '		jQuery(".ph-pos-search-box").show();';
		$s[] = '		jQuery(".ph-pos-card-user-box").show();';
		$s[] = '		jQuery(".ph-pos-sku-product-box").hide();';
		$s[] = '		jQuery(".ph-pos-date-order-box").hide();';
		$s[] = '		phPosManagePageFocus(page);';// Focus on start
		$s[] = '	} else if (page == "main.content.order") {';// ORDER
		$s[] = '		jQuery(".ph-pos-checkbox-box").hide();';//categories
		$s[] = '		jQuery(".ph-pos-search-box").hide();';
		$s[] = '		jQuery(".ph-pos-card-user-box").hide();';
		$s[] = '		jQuery(".ph-pos-sku-product-box").hide();';
		$s[] = '		jQuery(".ph-pos-date-order-box").hide();';
		$s[] = '	} else if (page == "main.content.orders") {'; // ORDERS
		$s[] = '		jQuery(".ph-pos-checkbox-box").hide();';//categories
		$s[] = '		jQuery(".ph-pos-search-box").hide();';
		$s[] = '		jQuery(".ph-pos-card-user-box").hide();';
		$s[] = '		jQuery(".ph-pos-sku-product-box").hide();';
		$s[] = '		jQuery(".ph-pos-date-order-box").show();';
		$s[] = '	} else if (page == "main.content.paymentmethods") {'; // PAYMENT METHODS
		$s[] = '		jQuery(".ph-pos-checkbox-box").hide();';//categories
		$s[] = '		jQuery(".ph-pos-search-box").hide();';
		$s[] = '		jQuery(".ph-pos-card-user-box").hide();';
		$s[] = '		jQuery(".ph-pos-sku-product-box").hide();';
		$s[] = '		jQuery(".ph-pos-date-order-box").hide();';
		$s[] = '		phPosManagePageFocus(page);';// Focus on start
		$s[] = '	} else if (page == "main.content.payment") {'; // PAYMENT
		$s[] = '		jQuery(".ph-pos-checkbox-box").hide();';//categories
		$s[] = '		jQuery(".ph-pos-search-box").hide();';
		$s[] = '		jQuery(".ph-pos-card-user-box").hide();';
		$s[] = '		jQuery(".ph-pos-sku-product-box").hide();';
		$s[] = '		jQuery(".ph-pos-date-order-box").hide();';
		$s[] = '		phPosManagePageFocus(page);';// Focus on start
		$s[] = '	} else {';
		$s[] = '		jQuery(".ph-pos-checkbox-box").hide();';//categories
		$s[] = '		jQuery(".ph-pos-search-box").hide();';
		$s[] = '		jQuery(".ph-pos-card-user-box").hide();';
		$s[] = '		jQuery(".ph-pos-sku-product-box").hide();';
		$s[] = '		jQuery(".ph-pos-date-order-box").hide();';
		$s[] = '	}';
		$s[] = ' }';
		
		// Declare it on start (event associated to phPosManagePage function
		$s[] = ' jQuery(document).ready(function(){';
		$s[] = '	phPosManagePage();';
		$s[] = ' })';
		
		$s[] = ' ';

		/*
		 * When adding new parameter to url bar, check if ? is there to set ? or &
		 */
		$s[] = ' function phAddSuffixToUrl(action, suffix) {';
		$s[] = '	return action + (action.indexOf(\'?\') != -1 ? \'&\' : \'?\') + suffix;';	
		$s[] = ' }';
		
		$s[] = ' ';
		
		/*
		 * Edit something in main view and then reload cart, main page, input page
		 * 
		 */
		$s[] = ' function phAjaxEditPos(sFormData, phUrlAjax, forcepageSuccess, forcepageError) {';
		$s[] = '    var phUrl 		= phAddSuffixToUrl(window.location.href, \'format=raw\');';
		$s[] = '	var phDataInput = phPosCurrentData("main.input");';
		$s[] = '	var phDataCats  = phPosCurrentData("main.categories");';
		$s[] = '	var phDataCart 	= phPosCurrentData("main.cart", "json");';
		$s[] = '	phRequest = jQuery.ajax({';
		$s[] = '		type: "POST",';
		$s[] = '		url: phUrlAjax,';
		//$s[] = '		async: false,';
		$s[] = '		async: true,';
		$s[] = '		cache: "false",';
		$s[] = '		data: sFormData,';
		$s[] = '		dataType:"JSON",';
		$s[] = '		success: function(data){';
		$s[] = '			if (data.status == 1){';
		//$s[] = '				jQuery("'.$outputDiv.'").html(data.item);';
		$s[] = '				if (data.id !== "undefined") {';
		$s[] = '					var id 	= data.id;';
		$s[] = '				} else {';
		$s[] = '					var id	= "";';
		$s[] = '				}';
		$s[] = '				var phDataMain 	= phPosCurrentData(forcepageSuccess, "raw", id);';
		$s[] = '				phDoSubmitFormUpdateCategoriesBox(phDataCats, phUrl);';// refresh categories box (when chaning users, users can have different access to categories)
		$s[] = '				phDoSubmitFormPaginationTop(phDataMain, phUrl);';// reload main box to default (list of products)
		$s[] = '				phDoSubmitFormUpdateInputBox(phDataInput, phUrl);';// refresh input box
		$s[] = '   				phDoSubmitFormUpdateCart(phDataCart);';// reload updated cart
		$s[] = '				jQuery(".ph-pos-message-box").html(data.message);';
		$s[] = '			} else if (data.status == 0){';
		$s[] = '				var phDataMain 	= phPosCurrentData(forcepageError);';
		$s[] = '				phDoSubmitFormPaginationTop(phDataMain, phUrl);';// reload main box to default (list of products)
		$s[] = '				phDoSubmitFormUpdateInputBox(phDataInput, phUrl);';// refresh input box
		$s[] = '   				phDoSubmitFormUpdateCart(phDataCart);';// reload updated cart
		$s[] = '				jQuery(".ph-pos-message-box").html(data.error);';
		$s[] = '			}';
		$s[] = '		}';
		$s[] = '	})';
		$s[] = '	return false;';	
		$s[] = '}';
		
		$s[] = ' ';
		
		
		
		// ------------
		// EVENTS
		// ------------
		$s[] = 'jQuery(document).ready(function(){';
		
		/*
		 * Clear form input after submit - for example, if vendor add products per
		 * bar scanner, after scanning the field must be empty for new product scan
		 * PRODUCTS, LOYALTY CARD
		 */
	    $s[] = ' jQuery(document).on("submit","#phPosSkuProductForm",function(){';
	   // $s[] = '	jQuery("#phPosSku").val("");';
	   // $s[] = '	e.preventDefault();';
	   // $s[] = '	this.submit();';
	    $s[] = '	setTimeout(function(){';
	    $s[] = '	   jQuery("#phPosSku").val("");';
	    $s[] = '	}, 100);';
	    $s[] = ' });';
	    
	    $s[] = ' jQuery(document).on("submit","#phPosCardUserForm",function(){';
	   // $s[] = '	jQuery("#phPosSku").val("");';
	   // $s[] = '	e.preventDefault();';
	   // $s[] = '	this.submit();';
	    $s[] = '	setTimeout(function(){';
	    $s[] = '	   jQuery("#phPosCard").val("");';
	    $s[] = '	}, 100);';
	    $s[] = ' });';
		
		
		/*
		 * Test if Bootstrap JS is loaded more than once
		 * This is important because of toggle buttons in select/checkboxes
		 * Toggle can be switched more than one time because of loaded instances of Bootstrap JS
		 */
		$s[] = ' var phScriptsLoaded = document.getElementsByTagName("script");';
		$s[] = ' var bMinJs = "bootstrap.min.js";';
		$s[] = ' var bJs = "bootstrap.js";';
		$s[] = ' var bJsCount = 0;';
		
		$s[] = ' jQuery.each(phScriptsLoaded, function (k, v) {';
		
		$s[] = '	var s = v.src;';
		$s[] = '	var n = s.indexOf("?")';
		$s[] = '	s = s.substring(0, n != -1 ? n : s.length);';
		$s[] = '	var filename = s.split(\'\\\\\').pop().split(\'/\').pop();';
		$s[] = '	if (filename == bMinJs || filename == bJs) {';
		$s[] = '		bJsCount++;';
		$s[] = '	}';
		$s[] = ' })';
		 
		$s[] = ' if (bJsCount > 1){';	
		$s[] = '	jQuery("#phPosWarningMsgBox").text("'.JText::_('COM_PHOCACART_WARNING_BOOTSTRAP_JS_LOADED_MORE_THAN_ONCE').'");';	
		$s[] = '	jQuery("#phPosWarningMsgBox").show();';
		$s[] = ' }';	
		
		$s[] = ' ';
		
		/*
		 * Load main content by links - e.g. in input box we call list of customers, payment methods or shipping methods
		 */
		
		$s[] = ' jQuery(document).on("click", ".loadMainContent", function (e) {';
		$s[] = '	phPosClearFilter();';
		$s[] = '	var phUrl 		= phAddSuffixToUrl(window.location.href, \'format=raw\');';
		$s[] = '	var sForm 		= jQuery(this).closest("form");';// Find in which form the right button was clicked
		$s[] = '	var sFormData 	= sForm.serialize();';
		$s[] = '	phDoSubmitFormPaginationTop(sFormData, phUrl);';
		$s[] = '	jQuery(".ph-pos-message-box").html("");';// clean message box
		$s[] = '		e.preventDefault();';
		$s[] = ' })';
		
		$s[] = ' ';
		
		/*
		 * Edit something in content area (e.g. customer list is loaded in main content and we change it)
		 */
		$s[] = ' jQuery(document).on("click", ".editMainContent", function (e) {';
		$s[] = '	phPosClearFilter();';
		$s[] = '	var phUrl 				= phAddSuffixToUrl(window.location.href, \'format=json\');';
		$s[] = '	var sForm				= jQuery(this).closest("form");';// Find in which form the right button was clicked
		$s[] = '	var sFormData			= sForm.serialize();';
		$s[] = '	var phRedirectSuccess 	= sForm.find(\'input[name="redirectsuccess"]\').val();';
		$s[] = '	var phRedirectError 	= sForm.find(\'input[name="redirecterror"]\').val();';
		$s[] = '	phAjaxEditPos(sFormData, phUrl, phRedirectSuccess, phRedirectError);';
		$s[] = '	jQuery(".ph-pos-message-box").html("");';// clean message box
		$s[] = '	e.preventDefault();';
		$s[] = ' })';
		
		$s[] = ' ';
		
		// Unfortunately we have form without buttons so we need to run the form without click too
		// to not submit more forms at once we will use ID :-(
		$s[] = ' jQuery(document).on("submit", "#phPosCardUserForm", function (e) {';
		$s[] = '	phPosClearFilter();';
		$s[] = '    var phUrl 		= phAddSuffixToUrl(window.location.href, \'format=json\');';
		$s[] = '	var sForm		= jQuery("#phPosCardUserForm");';
		$s[] = '	var sFormData	= sForm.serialize();';
		$s[] = '	phAjaxEditPos(sFormData, phUrl, "main.content.products", "main.content.products");';
		$s[] = '	jQuery(".ph-pos-message-box").html("");';// clean message box
		$s[] = '	e.preventDefault();';
		$s[] = ' })';
		
		$s[] = ' ';
	
		$s[] = ' jQuery(document).on("submit", "#phPosDateOrdersForm", function (e) {';
		$s[] = '	phPosClearFilter();';
		$s[] = '    var phUrl 		= phAddSuffixToUrl(window.location.href, \'format=raw\');';
		$s[] = '	var sForm		= jQuery("#phPosDateOrdersForm");';
		$s[] = '	var sFormData	= sForm.serialize();';
		//$s[] = '	var phDataMain 	= phPosCurrentData();';
		$s[] = '	phDoSubmitFormPaginationTop(sFormData, phUrl);';// reload main box to default (list of products)
		$s[] = '	jQuery(".ph-pos-message-box").html("");';// clean message box
		$s[] = '	e.preventDefault();';
		$s[] = ' })';

		$s[] = ' ';
		
		/*
		 * Display warning when closing a ticket
		 */
		$s[] = ' phPosCloseTicketFormConfirmed = false;';
		$s[] = ' jQuery(document).on("submit", "#phPosCloseTicketForm", function (e) {';
		$s[] = '	var txt = jQuery(this).data("txt");';
		$s[] = '	if(!phPosCloseTicketFormConfirmed) {';
		//$s[] = '		var phData = jQuery(this).serialize();'	;
		$s[] = '		phConfirm("#phPosCloseTicketForm", "", txt);';
		$s[] = '		e.preventDefault();';
		$s[] = '		return false;';
		$s[] = '	} else {';
		$s[] = '		phPosCloseTicketFormConfirmed = false;';// set back the variable
		$s[] = '		return true;';
		$s[] = '	}';
		$s[] = ' })';
		

		$s[] = '})';// end document ready

		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	
	
	
	/*
	 * Search by key type - typing of charcters into the search field
	 * 
	 * Must be loaded:
	 * renderSubmitPaginationTopForm()
	 * changeUrlParameter()
	 * editPos()
	 */
	public static function searchPosByType($id) {
		
		$s 	= array();
		$s[] = ' function phFindMember(typeValue) {';
		$s[] = '	var phData 	= "search=" + typeValue + "&" + phPosCurrentData();';
		$s[] = '	phUpdateUrlParameter("search", typeValue);';
		$s[] = '    var phUrl 	= phAddSuffixToUrl(window.location.href, \'format=raw\');';//get the url after update
		$s[] = '   	phDoSubmitFormPaginationTop(phData, phUrl);';
		$s[] = '	jQuery(".ph-pos-message-box").html("");';// clear message box
		$s[] = ' }';
		$s[] = ' ';
		$s[] = ' jQuery(document).ready(function() {';
		$s[] = '	var phThread = null;';
	    $s[] = '	jQuery("'.$id.'").keyup(function() {';
	    $s[] = '		clearTimeout(phThread);';
	    $s[] = '		var $this = jQuery(this); phThread = setTimeout(function(){phFindMember($this.val())}, 800);';
	    $s[] = '	});';
		$s[] = ' })';
		
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	
	
	
	
	/*
	 * Get all checkboxes of categories which are active and add them to url bar and filter the categories
	 * 
	 * Must be loaded:
	 * renderSubmitPaginationTopForm()
	 * changeUrlParameter()
	 * editPos()
	 * 
	 * Test checkbox
	 * components\com_phocacart\views\pos\tmpl\default_main_categories.php
	 * data-toggle="buttons" - changes the standard checkbox to graphical checkbox
	 * 
	 */
	public static function searchPosByCategory() {
		
		$app						= JFactory::getApplication();
		$paramsC 					= PhocacartUtils::getComponentParameters();
		$pos_filter_category		= $paramsC ->get( 'pos_filter_category', 1 );// reload equal height
		
		$s 	= array();
		$s[] = 'jQuery(document).ready(function(){';
		$s[] = '	jQuery("#ph-pc-pos-site .phPosCategoryCheckbox").off().on("change", function() {';
		
		
		
		if ($pos_filter_category == 2) {
			
			// Multiple categories can be displayed - can be active
			$s[] = '		var phA = [];';
			$s[] = '		jQuery("input.phPosCategoryCheckbox:checkbox:checked").each(function () {';
			$s[] = '	    	phA.push(jQuery(this).val());';
			$s[] = '		})';
			$s[] = '		var cValue = phA.join(",");';
		} else {
			// Only one category can be displayed
			// Deselect all checkboxed except the one selected - can be active
			$s[] = '		var cValue = jQuery(this).val();';
			$s[] = '		jQuery("input.phPosCategoryCheckbox:checkbox:checked").each(function () {';
			$s[] = '			if (cValue != jQuery(this).val() ) {';
			$s[] = '				jQuery(this).prop("checked", false);';
			$s[] = '				jQuery("label.phCheckBoxCategory").removeClass("active");';
			$s[] = '			}';
			$s[] = '		})';
			
			// Current checkbox was deselected
			$s[] = '		if (jQuery(this).prop("checked") == false) {';
			$s[] = '			cValue = "";';
			$s[] = '		}; ';
		}
		
		
		
		$s[] = '		var phData 	= "category=" + cValue + "&" + phPosCurrentData();';
		$s[] = '		phUpdateUrlParameter("category", cValue);';// update URL bar
		//$s[] = '		var phUrl = phUpdateUrlParameter("category", cValue, phUrl);';// Update phUrl - it is a form url which is taken by joomla to create pagination links
		$s[] = '    	var phUrl 	= phAddSuffixToUrl(window.location.href, \'format=raw\');';// get the link after update of url bar
		$s[] = '   		phDoSubmitFormPaginationTop(phData, phUrl);';
		$s[] = '			jQuery(".ph-pos-message-box").html("");';// clear message box
		$s[] = '	});';
		$s[] = '})';
		
		/*$s[] = 'jQuery(document).ready(function(){';
		$s[] = '	jQuery(document).on("click", ".mainBoxCategory", function (e) {';
		$s[] = '        var phUrl 		= phAddSuffixToUrl(window.location.href, \'format=raw\');';
		$s[] = '	    var sForm 		= jQuery(this).closest("form");';// Find in which form the right button was clicked
		$s[] = '	    var sFormData 	= sForm.serialize()';
		$s[] = '		phDoSubmitFormPaginationTop(sFormData, phUrl);';
		$s[] = '		e.preventDefault();';
		$s[] = '	})';
		$s[] = '})';*/
		
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	
	
	
	public static function changeUrlParameter($params) {
		
		$s 	= array();
		$s[] = ' function phRemoveUrlParameter(param, url) {';
		$s[] = '	var rtn = url.split("?")[0],';
        $s[] = '	param,';
        $s[] = '	params_arr = [],';
        $s[] = '	queryString = (url.indexOf("?") !== -1) ? url.split("?")[1] : "";';
        $s[] = '	if (queryString !== "") {';
        $s[] = '		params_arr = queryString.split("&");';
        $s[] = '		for (var i = params_arr.length - 1; i >= 0; i -= 1) {';
        $s[] = '    		paramV = params_arr[i].split("=")[0];';
        $s[] = '			if (paramV === param) {';
        $s[] = '        		params_arr.splice(i, 1);';
        $s[] = '    		}';
        $s[] = '		}';
        $s[] = '			rtn = rtn + "?" + params_arr.join("&");';
        $s[] = '		}';
        $s[] = '	return rtn;';
	    $s[] = ' }';

		$s[] = ' ';
		
		$s[] = ' function phUpdateUrlParameter(param, value, urlChange) {';
		$s[] = '	if (typeof urlChange !== "undefined") {';
		$s[] = '		var url =  urlChange;';
		$s[] = '		var urlA =  url.split("#");';
	    $s[] = '		var hash =  ""';
	    $s[] = '		if(urlA.length > 1) { hash = urlA[1];}';
	    $s[] = ' 	} else {';
	    $s[] = '		var url = window.location.href;';
	    $s[] = '		var hash = location.hash;';
	    $s[] = ' 	}';
		
	    $s[] = '	url = url.replace(hash, \'\');';
	    $s[] = '	if (url.indexOf(param + "=") >= 0) {';
	    $s[] = '    	var prefix = url.substring(0, url.indexOf(param));';
	    $s[] = '		var suffix = url.substring(url.indexOf(param));';
	    $s[] = '    	suffix = suffix.substring(suffix.indexOf("=") + 1);';
	    $s[] = '    	suffix = (suffix.indexOf("&") >= 0) ? suffix.substring(suffix.indexOf("&")) : "";';
	    $s[] = '    	url = prefix + param + "=" + value + suffix;';
	    $s[] = ' 	} else {';
	    $s[] = '    	if (url.indexOf("?") < 0) {';
	    $s[] = '       		url += "?" + param + "=" + value;';
	    $s[] = '    	} else {';
	    $s[] = '       		url += "&" + param + "=" + value;';
	    $s[] = '		}';
	    $s[] = ' 	}';
	    $s[] = '	url = url.replace(/[^=&]+=(&|$)/g,"").replace(/&$/,"");';// remove all parameters with empty values
	    
	    $s[] = '	if (typeof urlChange !== "undefined") {';
	    $s[] = '		return (url + hash);';
	    $s[] = ' 	} else {';
	    $s[] = '		window.history.pushState(null, null, url + hash);';
	    $s[] = '	}';
	    $s[] = ' }';
		
	    if (!empty($params)) {
	    	
			$s[] = ' jQuery(document).ready(function(){';
			foreach($params as $k => $v) {
				$s[] = '	phUpdateUrlParameter("'.$k.'", '.(int)$v.');';
			}
			$s[] = '})';	
	    }
	   
	    JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	
	
	
	/*
	 * Print to POS printer
	 */
	public static function printPos($url) {
		
		$app				= JFactory::getApplication();
		$paramsC 			= PhocacartUtils::getComponentParameters();
		$pos_server_print	= $paramsC->get( 'pos_server_print', 0 );


		$s 	 = array();
		$s[] = ' jQuery(document).ready(function(){';
		
		$s[] = '	jQuery("#phPosContentBox").on("click", ".phOrderPrintBtn", function (e) {';
		$s[] = '	var phUrlAjax 		= "'.$url.'"';
		$s[] = '	var phOrder	 		= jQuery(this).data("order");';
		$s[] = '	var phType	 		= jQuery(this).data("type");';
		$s[] = '	var phOrderCurrent	= jQuery("#phPosOrderPrintBox").attr("data-order");';// data("order"); not working
		$s[] = '	var phTypeCurrent	= jQuery("#phPosOrderPrintBox").attr("data-type");';// data("type"); not working
		
		// PC PRINT
		$s[] = '	if (phType == "-1") {';// -1 type is print (1 order, 2 invoice, 3 delivery note, 4 receipt)
		
		if ($pos_server_print == 2 || $pos_server_print == 3) {
			// - 1 AND 4 PC PRINT FOR ALL DOCUMENTS EXCEPT 4 (Receipt) - Receipt will be printend by SERVER PRINT
			$s[] = '		if (phTypeCurrent == "4") {';
			$s[] = '			var phUrlAjaxPrint = phAddSuffixToUrl(phUrlAjax, "id=" + phOrder + "&type=" + phTypeCurrent + "&pos=1&printserver=1");';
			$s[] = '			phRequestPrint = jQuery.ajax({';
			$s[] = '				type: "GET",';
			$s[] = '				url: phUrlAjaxPrint,';
			$s[] = '				async: true,';
			$s[] = '				cache: "false",';
			$s[] = '				dataType:"HTML",';
			$s[] = '				success: function(data){';
			$s[] = '					jQuery(".ph-pos-message-box").html(\'<div>\' + data + \'</div>\');';
			//$s[] = '					jQuery("#phPosOrderPrintBox").attr("class", phClass);';// Add class to box of document - to differentiate documents loaded by ajax
			//$s[] = '					jQuery("#phPosOrderPrintBox").attr("data-type", phType);';// Add data type to box of document - so it can be read by print function
			//$s[] = '					jQuery("#phPosOrderPrintBox").html(data);';// Add the document itself to the site
			$s[] = '				}';
			$s[] = '			})';
			$s[] = '			e.preventDefault();';
			$s[] = '			return false;';
			// -1 PC PRINT
			$s[] = '		} else {';
			$s[] = '			window.print(); return false;';// print with javascript for all documents except receipt (receipt is ready for server POS printers)
			$s[] = '		}';
			
		} else {
			$s[] = '			window.print(); return false;';// print with javascript for all document (including receipt)
		}
		$s[] = '	}';
		
		// DISPLAYING THE DOCUMENT
		$s[] = '	var phClass 	= "phType" + phType;';
		$s[] = '	var phUrlAjax 	= phAddSuffixToUrl(phUrlAjax, "id=" + phOrder + "&type=" + phType + "&pos=1");';
		$s[] = '	phRequest = jQuery.ajax({';
		$s[] = '		type: "GET",';
		$s[] = '		url: phUrlAjax,';
		$s[] = '		async: true,';
		$s[] = '		cache: "false",';
		$s[] = '		dataType:"HTML",';
		$s[] = '		success: function(data){';
		$s[] = '			jQuery("#phPosOrderPrintBox").attr("class", phClass);';// Add class to box of document - to differentiate documents loaded by ajax
		$s[] = '			jQuery("#phPosOrderPrintBox").attr("data-type", phType);';// Add data type to box of document - so it can be read by print function
		$s[] = '			jQuery("#phPosOrderPrintBox").html(data);';// Add the document itself to the site
		$s[] = '		}';
		$s[] = '	})';
		
		
		$s[] = '	e.preventDefault();';
		$s[] = ' })';
		
		$s[] = ' ';

		$s[] = '})';// end document ready

		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}

	
	
	
	// ========
	// JQUERY UI
	// ========
	public static function renderJsUi() {
	
		$s 	= array();
		$s[] = ' function phSetCloseButton() {';
		$s[] = '	jQuery(".ui-dialog-titlebar-close").html(\'<span class="ph-close-button ui-button-icon-primary ui-icon ui-icon-closethick"></span>\');';
		$s[] = ' }';
		$s[] = ' ';
		$s[] = ' function phConfirm(submitForm, dataPost, txt) {';
		$s[] = '	jQuery("#phDialogConfirm" ).html( txt );';
		$s[] = '	jQuery("#phDialogConfirm").dialog({';
		$s[] = '        autoOpen: false,';
		$s[] = '		modal: true,';
		$s[] = '		buttons: {';
		$s[] = '           "'. JText::_('COM_PHOCACART_OK').'": function() {';
		$s[] = '				jQuery(this).dialog("close");';
        $s[] = '				phPosCloseTicketFormConfirmed = true;';
		$s[] = '           		if (submitForm != "") {';
		$s[] = '           			jQuery(submitForm).submit();';
		$s[] = '           		} else if (typeof dataPost !== "undefined" && dataPost != "") {';
		$s[] = '           			//phDoRequest(dataPost);';
		$s[] = '				}';
		$s[] = '				return true;';
		
		$s[] = '           },';
		$s[] = '           "'.  JText::_('COM_PHOCACART_CANCEL').'": function() {';
		$s[] = '				jQuery(this).dialog("close");';
		$s[] = '				return false;';
		$s[] = '           }';
		$s[] = '       }';
		$s[] = '	})';
		$s[] = '	jQuery( "#phDialogConfirm" ).dialog( "open" );';
		$s[] = '	phSetCloseButton();/* Correct class */';
		$s[] = '	jQuery("button").addClass("btn btn-default");';
		$s[] = ' }';

		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	
	
	
	
	
	/*
	 * JS equivalent to PhocacartPrice::getPriceFormat();
	 */
	
	public static function getPriceFormatJavascript($price_decimals, $price_dec_symbol, $price_thousands_sep, $price_currency_symbol, $price_prefix, $price_suffix, $price_format) {
		

		JFactory::getDocument()->addScript(JURI::root(true).'/media/com_phocacart/js/number_format.js');
	
		$s 	= array();
		$s[] = ' function phGetPriceFormat($price) {';
		$s[] = '	var $negative = 0;';
		$s[] = ' 	if ($price < 0) {';
		$s[] = ' 		$negative = 1;';
		$s[] = '	}';

		$s[] = '	if ($negative == 1 ) {';
		$s[] = ' 		$price = Math.abs($price);';
		$s[] = ' 	}';

		$s[] = ' 	$price = number_format($price, "'.$price_decimals.'", "'.$price_dec_symbol.'", "'.$price_thousands_sep.'");';
		
		switch($price_format) {
			case 1:
				$s[] = '	$price = $price + "'.$price_currency_symbol.'";';
			break;
			
			case 2:
				$s[] = '	$price = "'.$price_currency_symbol.'" + $price;';
			break;
			
			case 3:
				$s[] = '	$price = "'.$price_currency_symbol.'" + " " + $price;';
			break;
			
			case 0:
			default:
				$s[] = '	$price = $price + " " + "'.$price_currency_symbol.'";';
			break;
		}
		
		$s[] = '	if ($negative == 1) {';
		$s[] = '		return "- " + "'.$price_prefix.'" + $price + "'.$price_suffix.'";';
		$s[] = '	} else {';
		$s[] = '		return "'.$price_prefix.'" + $price + "'.$price_suffix.'";';
		$s[] = '	}';
		$s[] = ' }';
		
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	
	
	public static function renderFilterRange($min, $max, $priceFrom, $priceTo) {
		
		
		$s 	= array();
		$s[] = ' jQuery(document).ready(function(){';
		$s[] = '   jQuery( "#phPriceFilterRange" ).slider({';
		$s[] = '      range: true,';
		$s[] = '      min: '.$min.',';
		$s[] = '      max: '.$max.',';
		$s[] = '  	  values: ['.$priceFrom.', '.$priceTo.'],';
		$s[] = '      slide: function( event, ui ) {';
		$s[] = '         jQuery("#phPriceFromTopricefrom").val(ui.values[0]);';
		$s[] = '	     jQuery("#phPriceFromTopriceto").val(ui.values[1]);';
		$s[] = '         jQuery("#phPriceFilterPrice").html("'.JText::_('COM_PHOCACART_PRICE').': " + phGetPriceFormat(ui.values[0]) + " - " + phGetPriceFormat(ui.values[1]));';
		$s[] = '      }';
		$s[] = '   });';
		$s[] = ' ';
		$s[] = '   jQuery("#phPriceFilterPrice").html("'.JText::_('COM_PHOCACART_PRICE').': " + phGetPriceFormat('.$priceFrom.') + " - " + phGetPriceFormat('.$priceTo.'));';
		$s[] = ' ';
		
		$s[] = '	jQuery("#phPriceFromTopricefrom").on("change", function (e) {';
		$s[] = '		var from = jQuery("#phPriceFromTopricefrom").val();';
		$s[] = '		var to = jQuery("#phPriceFromTopriceto").val();';
		$s[] = '		if (to == \'\') { to = '.$max.';}';
		$s[] = '		if (from == \'\') { from = '.$min.';}';
		$s[] = '		if (to < from) {to = from;}';
		$s[] = '		jQuery( "#phPriceFilterRange" ).slider({values: [from,to]});';
		$s[] = '	})';
		
		$s[] = '	jQuery("#phPriceFromTopriceto").on("change", function (e) {';
		$s[] = '		var from = jQuery("#phPriceFromTopricefrom").val();';
		$s[] = '		var to = jQuery("#phPriceFromTopriceto").val();';
		$s[] = '		if (to == \'\') { to = '.$max.';}';
		$s[] = '		if (from == \'\') { from = '.$min.';}';
		$s[] = '		if (to < from) {to = from;}';
		$s[] = '		jQuery( "#phPriceFilterRange" ).slider({values: [from,to]});';
		$s[] = '	})';
		
		$s[] = ' });';
		
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
		
	}
	
	
	
	
	
	// ========
	// HELPERS
	// ========
	
		public static function renderBillingAndShippingSame() {
		
		$app			= JFactory::getApplication();
		$paramsC 		= PhocacartUtils::getComponentParameters();
		$load_chosen 		= $paramsC->get( 'load_chosen', 1 );
		

		// BILLING AND SHIPPING THE SAME
		// If checkbox will be enabled (Shipping and Billing address is the same) - remove the required protection of input fields
		$s 	= array();

		$s[] = 'jQuery(document).ready(function(){';

		//$s[] = '   phBgInputCh  = jQuery("#phShippingAddress .chosen-single").css("background");';
		//$s[] = '   phBgInputI	= jQuery(".phShippingFormFields").css("background");';
		$s[] = '   phDisableRequirement();';
	  
		$s[] = '   jQuery("#phCheckoutBillingSameAsShipping").on(\'click\', function() {';
		$s[] = '      phDisableRequirement();';
		$s[] = '   })';
	  
		$s[] = '   function phDisableRequirement() {';
		
		//$s[] = '   var phBgInputCh  = jQuery("#phShippingAddress .chosen-single").css("background");';
		//$s[] = '   var phBgInputI	= jQuery(".phShippingFormFields").css("background");';
		
		$s[] = '		var selectC = jQuery("#jform_country_phs");';
		$s[] = '		var selectR = jQuery("#jform_region_phs");';
	  
		$s[] = '      var checked = jQuery(\'#phCheckoutBillingSameAsShipping\').prop(\'checked\');';

		$s[] = '      if (checked) {';
		//jQuery(".phShippingFormFieldsRequired").prop("disabled", true);//.trigger("chosen:updated");// Not working - using readonly instead
		//jQuery(".phShippingFormFields").prop("readonly", true);// Not working for Select box
		
		$s[] = '		jQuery(".phShippingFormFields").prop("readonly", true);';
		$s[] = '		selectC.attr("disabled", "disabled");';
		$s[] = '		selectR.attr("disabled", "disabled");';
		
		
		$s[] = '	     jQuery(".phShippingFormFieldsRequired").removeAttr(\'aria-required\');';
		$s[] = '	     jQuery(".phShippingFormFieldsRequired").removeAttr(\'required\');';	
		//$s[] = '	     jQuery("#phShippingAddress .chosen-single").css(\'background\', \'#f0f0f0\');';
		//$s[] = '	     jQuery(".phShippingFormFields").css(\'background\', \'#f0f0f0\');';	
		if ($load_chosen == 1) {
			$s[] = '	     jQuery(".phShippingFormFieldsRequired").trigger("chosen:updated");';
			$s[] = '	     jQuery(".phShippingFormFields").trigger("chosen:updated");';
		}
		$s[] = '      } else {';
		  
		$s[] = '	     jQuery(".phShippingFormFieldsRequired").prop(\'aria-required\', \'true\');';
		$s[] = '	     jQuery(".phShippingFormFieldsRequired").prop(\'required\', \'true\');';
		//jQuery(".phShippingFormFields").removeAttr(\'readonly\'); 
		//$s[] = '	     jQuery("#phShippingAddress .chosen-single").css(\'background\', phBgInputCh);'; 
		//$s[] = '	     jQuery(".phShippingFormFields").css(\'background\', phBgInputI);';
		
		$s[] = '	    jQuery(".phShippingFormFields").removeAttr(\'readonly\');';
		$s[] = '		selectC.removeAttr("disabled");';
		$s[] = '		selectR.removeAttr("disabled");';
		if ($load_chosen == 1) {
			$s[] = '	     jQuery(".phShippingFormFieldsRequired").trigger("chosen:updated");';
			$s[] = '	     jQuery(".phShippingFormFields").trigger("chosen:updated");';
		}
		$s[] = '      }';
		$s[] = '   }';
		$s[] = '});';
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	
	public static function renderAjaxTopHtml($text = '') {
		$o = '<div id="ph-ajaxtop">';
		if ($text != '') {
			$o .= '<div id="ph-ajaxtop-message"><div class="ph-loader-top"></div> '. htmlspecialchars($text) . '</div>';
		}
		$o .= '</div>';
		return $o;
	}
	
	// Singleton - do not load items more times from database
	/*public static function renderLoaderFullOverlay() {
		
		if( self::$fullOverlay == '' ) {
			
			$s 	= array();
			$s[] = 'var phOverlay = jQuery(\'<div id="phOverlay"><div id="phLoaderFull"> </div></div>\');';
			$s[] = 'phOverlay.appendTo(document.body);';
			$s[] = 'jQuery("#phOverlay").fadeIn().css("display","block");';
			self::$fullOverlay = implode("\n", $s);
		}		
		return self::$fullOverlay;
	}*/
	
	// loading.gif - whole page
	// Singleton - check if loaded - xxx No Singleton, it must be inside each javascript function
	public static function renderLoaderFullOverlay() {
		//static $fullOverlay = 0;
		//if( $fullOverlay == 0) {
			$s 	= array();
			$s[] = 'if (phA == 2) {';
			$s[] = '';// 2 means false
			$s[] = '} else {';
			$s[] = '   var phOverlay = jQuery(\'<div id="phOverlay"><div id="phLoaderFull"> </div></div>\');';
			$s[] = '   phOverlay.appendTo(document.body);';
			$s[] = '   jQuery("#phOverlay").fadeIn().css("display","block");';
			$s[] = '}';
			$fullOverlay = 1;
			return implode("\n", $s);
		//} else {
		//	return '';
		//}		
		
		/*
		var phOverlay = jQuery('<div id="phOverlay"><div id="phLoaderFull"> </div></div>');
		phOverlay.appendTo(document.body);
		var $loading = jQuery('#phOverlay').hide();
		jQuery(document)
		  .ajaxStart(function () {
			$loading.show();
		  })
		  .ajaxStop(function () {
			$loading.hide();
		  });
		*/
	}
	
	public static function renderLoaderDivOverlay($outputDiv) {
		
		$overlay['start'] = '';
		$overlay['end'] = '';
		
		$s[] = '   var phOverlay = jQuery(\'<div id="phOverlayDiv"><div id="phLoaderFull"> </div></div>\');';
		$s[] = '   phOverlay.appendTo("'.$outputDiv.'");';
		$s[] = '   jQuery("#phOverlayDiv").fadeIn().css("display","block");';
		
		$overlay['start'] = implode("\n", $s);
		
		$s2[] = '   jQuery("#phOverlay").fadeIn().css("display","none");';
	
		$overlay['end'] = implode("\n", $s2);
		
		return $overlay;
	}
	
	public static function renderOverlay(){
		
		$s	 = array();
		$s[] = '		var phOverlay = jQuery(\'<div id="phOverlay"><div id="phLoaderFull"> </div></div>\');';
		$s[] = '		phOverlay.appendTo(document.body);';
		$s[] = '		jQuery("#phOverlay").fadeIn().css("display","block");';
		return implode("\n", $s);
	}
	
	public static function renderMagnific() {
		
		$document	= JFactory::getDocument();
		$document->addScript(JURI::base(true).'/media/com_phocacart/js/magnific/jquery.magnific-popup.min.js');
		$document->addStyleSheet(JURI::base(true).'/media/com_phocacart/js/magnific/magnific-popup.css');
		$s = array();
		$s[] = 'jQuery(document).ready(function() {';
		$s[] = '	jQuery(\'#phImageBox\').magnificPopup({';
		$s[] = '		tLoading: \''.JText::_('COM_PHOCACART_LOADING').'\',';
		$s[] = '		tClose: \''.JText::_('COM_PHOCACART_CLOSE').'\',';
		$s[] = '		delegate: \'a.magnific\',';
		$s[] = '		type: \'image\',';
		$s[] = '		mainClass: \'mfp-img-mobile\',';
		$s[] = '		zoom: {';
		$s[] = '			enabled: true,';
		$s[] = '			duration: 300,';
		$s[] = '			easing: \'ease-in-out\'';
		$s[] = '		},';
		$s[] = '		gallery: {';
		$s[] = '			enabled: true,';
		$s[] = '			navigateByImgClick: true,';
		$s[] = '			tPrev: \''.JText::_('COM_PHOCACART_PREVIOUS').'\',';
		$s[] = '			tNext: \''.JText::_('COM_PHOCACART_NEXT').'\',';
		$s[] = '			tCounter: \''.JText::_('COM_PHOCACART_MAGNIFIC_CURR_OF_TOTAL').'\'';
		$s[] = '		},';
		$s[] = '		image: {';
		$s[] = '			titleSrc: function(item) {';
		$s[] = '				return item.el.attr(\'title\');';
		$s[] = '			},';
		$s[] = '			tError: \''.JText::_('COM_PHOCACART_IMAGE_NOT_LOADED').'\'';
		$s[] = '		}';
		$s[] = '	});';
		$s[] = '});';
		
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
				
	}
	
	
	public static function renderPrettyPhoto() {
		$document	= JFactory::getDocument();
		JHtml::stylesheet( 'media/com_phocacart/js/prettyphoto/css/prettyPhoto.css' );
		$document->addScript(JURI::root(true).'/media/com_phocacart/js/prettyphoto/js/jquery.prettyPhoto.js');
		
		$s[] = 'jQuery(document).ready(function(){';
		$s[] = '	jQuery("a[rel^=\'prettyPhoto\']").prettyPhoto({';
		$s[] = '  social_tools: 0';		
		$s[] = '  });';
		$s[] = '})';

		$document->addScriptDeclaration(implode("\n", $s));
	}
	


	
	public static function renderPhocaAttribute() {
		$document	= JFactory::getDocument();
		$document->addScript(JURI::root(true).'/media/com_phocacart/js/phoca/jquery.phocaattribute.js');
	}
	
	public static function renderOverlayOnSubmit($id) {
		
		$document	= JFactory::getDocument();
		
		$s[] = 'jQuery(document).ready(function(){';
		$s[] = '   jQuery(\'#'.$id.'\').on(\'submit\', function(){';
		$s[] = self::renderOverlay();
		$s[] = '   })';
		$s[] = '})';

		$document->addScriptDeclaration(implode("\n", $s));
	}
	
	/* OBSOLETE
	 * Swap large images by attributes
	 */
/*	public static function renderPhSwapImageInitialize($formId, $dynamicChangeImage = 0, $ajax = 0, $imgClass = 'ph-item-image-full-box') {
		/*
		if ($dynamicChangeImage == 1) {
			$s = array();
			$s[] = 'jQuery(document).ready(function() {';
			$s[] = '	var phSIO1'.(int)$formId.' = new phSwapImage;';
		//	$s[] = '	phSIO1'.(int)$formId.'.Init(\'.ph-item-image-full-box\', \'#phItemPriceBoxForm\', \'.ph-item-input-set-attributes\', 0);';
		$s[] = '	phSIO1'.(int)$formId.'.Init(\'.'.$imgClass.'\', \'#'.$formId.'\', \'.ph-item-input-set-attributes\', 0);';
			$s[] = '	phSIO1'.(int)$formId.'.Display();';
			$s[] = '});';
			if ($ajax == 1) {
				return '<script type="text/javascript">'.implode("\n", $s).'</script>';
			} else {
				JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
			}
			
		}*//*
	}*/
	
	/* OBSOLETE
	 * Type Color Select Box and Image Select Box - displaying images or colors instead of select box
	 */
	/* 
	public static function renderPhAttributeSelectBoxInitialize($id, $type, $typeView) {
	
		
		return;

		$s = array();
		$s[] = 'jQuery(document).ready(function() {';
		$s[] = '	var phAOV'.$typeView.'I'.(int)$id.' = new phAttribute;';
		$s[] = '	phAOV'.$typeView.'I'.(int)$id.'.Init('.(int)$id.', '.(int)$type.', \''.$typeView.'\');';
		$s[] = '	phAOV'.$typeView.'I'.(int)$id.'.Display();';
		$s[] = '});';
		
		if ($typeView == 'ItemQuick') {
			return '<script type="text/javascript">'.implode("\n", $s).'</script>';
		} else {
			JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
		}
	
	}
	*/
	
	/*
	 * Checkbox color and image - set by bootstrap button - active class
	 */ 
	 
	 
	/*
	* This jQuery function replaces HTML 5 for checking required checkboxes
	* If there is a required group of checkboxes: 
	* components\com_phocacart\views\item\tmpl\default.php reqC (cca line 277)
	* it checks for at least one checked checkbox
	* 1. it loops for every required checkbox
	* 2. then asks for groups or required checkbox
	* 3. then the group id selects all checkboxes in the group and make them not required if some of the checkbox was selected
	*/
	
	/*
	OBSOLETE
	public static function renderCheckBoxRequired() {
		
		
	/*	$s[] = 'jQuery(document).ready(function(){';
		$s[] = '   jQuery(\'.phjPriceBoxForm button[type="submit"]\').on(\'click\', function() {';
		$s[] = '      jQuery(this).closest("form").find(\' .checkbox-group.required input:checkbox\').each(function() {';// 1
		$s[] = '	  var phAttributeGroup 		= jQuery(this).closest(".checkbox-group").attr(\'id\');';// 2
		$s[] = '      var phAttributeGroupItems	= jQuery(\'#\' + phAttributeGroup + \' input:checkbox\');';// 3
		$s[] = '         phAttributeGroupItems.prop(\'required\', true);';
		$s[] = '      	 if(phAttributeGroupItems.is(":checked")){';
		$s[] = '      		phAttributeGroupItems.prop(\'required\', false);';
		$s[] = '      	 }';
		$s[] = '      })';
		
		//var phCheckBoxGroup = jQuery(".checkbox-group-'.(int)$id.' input:checkbox");';
		//$s[] = '      phCheckBoxGroup.prop(\'required\', true);';
		//$s[] = '      if(phCheckBoxGroup.is(":checked")){';
		//$s[] = '         phCheckBoxGroup.prop(\'required\', false);';
		//$s[] = '      }';
		$s[] = '   });';
		$s[] = '})';
	*/	/*
		//if ($ajax == 1) {
		//	return '<script type="text/javascript">'.implode("\n", $s).'</script>';
		//} else {
		
		//	JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
		//}
	} */
	
	/*
	 * Two features in one function
	 * 1) return required parts 
	 *    If the attribute is required, return different required parts (attribute - html5, class - jquery, span - heading)
	 * 2) Initialize JQuery Check for required fields working with HTML 5
	 *    Checkboxes cannot be checked by HTML 5, so we need help of jQuery which manage for example:
	 *    There are 3 checkboxes - one selected, two not (It is OK but not for HTML5)
	 */
	public static function renderRequiredParts($id, $required) {
		
		// If the attribute is required
		$req['attribute'] 	= '';// Attribute - required field HTML 5
		$req['span']		= '';// Span - displayed * next to title
		$req['class']		= '';// Class - Checkboxes cannot be checked per HTML 5,
								 //jquery used PhocacartRenderJs::renderCheckBoxRequired()
		
		if($required) {
			$req['attribute'] 	= ' required="" aria-required="true"';
			$req['span'] 		= ' <span class="ph-req">*</span>';
			$req['class'] 		= ' checkbox-group-'.(int)$id.' checkbox-group required';
		}
		return $req;	
	}
	
	
	public static function renderJsScrollTo($scrollTo = '', $animation = 0) {
		
		
		
		$s[] = 'jQuery(function() {';
		$s[] = '   if (jQuery("#ph-msg-ns").length > 0){';
		$s[] = '      jQuery(document).scrollTop( jQuery("#system-message").offset().top );';
		//$s[] = '      jQuery(\'html,body\').animate({scrollTop: jQuery("#system-message").offset().top}, 1500 );';
		
		if ($scrollTo != '') {
			$s[] = '   } else {';
			if ($animation == 1) {
				$s[] = '	  jQuery(\'html,body\').animate({scrollTop: jQuery("#'.$scrollTo.'").offset().top}, 1500 );';
			} else {
				$s[] = '      jQuery(document).scrollTop( jQuery("#'.$scrollTo.'").offset().top );';
			}
		}
		$s[] = '   }';
		$s[] = '});';
			
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	
	/* POS Scroll cart */
	
	public static function renderJsScrollToPos() {
	
		$s[] = 'function phScrollPosCart(phPosCart) {';
		$s[] = '	if (jQuery("#ph-msg-ns").length > 0){';
		$s[] = '		phPosCart.animate({scrollTop: 0}, 1500 );';
		$s[] = '	} else {';
		$s[] = '		var phPosCartHeight = phPosCart[0].scrollHeight;';
		$s[] = '		phPosCart.animate({scrollTop: phPosCartHeight}, 1500 );';
		$s[] = '	}';
		$s[] = '}';
		
		$s[] = 'jQuery(document).ready(function() {';
		$s[] = '	var phPosCart 		= jQuery(\'#phPosCart\');';
		$s[] = '	phScrollPosCart(phPosCart);';//  On start
		$s[] = '	phPosCart.on("DOMSubtreeModified", function(){';// On modified
		$s[] = '		if (phPosCart.text() != \'\') {';// this event runs twice - first when jquery empty the object, second when it fills it again
		$s[] = '			phScrollPosCart(phPosCart);';// run only on second when it fills the object
		$s[] = '		}';
		$s[] = '	});';
		$s[] = '});';
			
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	
	public static function renderJsAddTrackingCode($idSource, $classDestination) {
		$s 	= array();
		$s[] = 'jQuery(document).ready(function() {';
		$s[] = '   var destGlobal 	= jQuery( \'.'.$classDestination.'\').text();';
		$s[] = '   var sourceGlobal	= jQuery(\'#'.$idSource.'\').val();';
		$s[] = '   var textGlobal 	= destGlobal + sourceGlobal';
		$s[] = '   jQuery( \'.'.$classDestination.'\').html(textGlobal);';
		
		$s[] = '   jQuery(\'#'.$idSource.'\').on("input", function() {';
		$s[] = '       var source	= jQuery(this).val();';
		$s[] = '       var text = destGlobal + source';
		$s[] = '       jQuery( \'.'.$classDestination.'\').html(text);';
		$s[] = '   })';
		$s[] = '})';
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));

		
	}
	
	public static function renderDetectVirtualKeyboard() {
		$s 	= array();
		$s[] = 'jQuery(document).ready(function(){';
		$s[] = '  var phDefaultSize = jQuery(window).width() + jQuery(window).height()';
		$s[] = '  jQuery(window).resize(function(){';
		$s[] = '    if(jQuery(window).width() + jQuery(window).height() != phDefaultSize){';
		$s[] = '       ';
		$s[] = '      jQuery(".ph-pos-wrap-main").css("position","fixed");';  
		$s[] = '    } else {';
		$s[] = '       ';
		$s[] = '      jQuery(".ph-pos-wrap-main").css("position","relative");';
		$s[] = '    }';
		$s[] = '  });';
		$s[] = '});';
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));

		
	}
	
	
	
	
	
	
	public final function __clone() {
		throw new Exception('Function Error: Cannot clone instance of Singleton pattern', 500);
		return false;
	}
}
?>