<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class PhocacartRenderMedia
{
	public $jquery			= 0;
	protected $document		= false;
	

	public function __construct() {
		JHTML::stylesheet('media/com_phocacart/css/main.css' );
		JHtml::_('jquery.framework', false);
		$this->document	= JFactory::getDocument();
	}
	
	public function loadProductHover($load = 0) {
		if ($load == 1) {
			JHTML::stylesheet('media/com_phocacart/css/main-product-hover.css' );
		}
	}
	
	public function loadPhocaMoveImage($load = 0) {
		if ($load == 1) {
			JHTML::stylesheet('media/com_phocacart/css/main-product-image-move.css' );
		}
	}
	
	public function loadBootstrap($load = 0) {
		if ($load == 1) {

			JHTML::stylesheet('media/com_phocacart/bootstrap/css/bootstrap.min.css' );
			$this->document->addScript(JURI::root(true).'/media/com_phocacart/bootstrap/js/bootstrap.min.js');
		}
	}
	
	public function loadChosen($load = 0) {
		if ($load == 1) {
			$this->document->addScript(JURI::root(true).'/media/com_phocacart/bootstrap/js/bootstrap.min.js');
			$this->document->addScript(JURI::root(true).'/media/com_phocacart/js/chosen/chosen.jquery.min.js');
			$this->document->addScript(JURI::root(true).'/media/com_phocacart/js/chosen/chosen.required.js');
			$js = "\n". 'jQuery(document).ready(function(){'."\n";
			$js .= '   jQuery(".chosen-select").chosen({disable_search_threshold: 10});'."\n"; // Set chosen, created hidden will be required
			// When select box is required, display the error message (when value not selected)
			// But on mobiles, this hide standard select boxes
			// we need to have condition, if really chosen is applied:
			// https://github.com/harvesthq/chosen/issues/1582
			//$js .= '   jQuery(".chosen-select").attr(\'style\',\'display:visible; position:absolute; clip:rect(0,0,0,0)\');'."\n";
			$js .= '});'."\n";
			$this->document->addScriptDeclaration($js);
			JHTML::stylesheet( 'media/com_phocacart/js/chosen/chosen.css' );
			JHTML::stylesheet( 'media/com_phocacart/js/chosen/chosen-bootstrap.css' );
		}
	}
	
	
	
	public function loadEqualHeights($load = 0) {
		if ($load == 1) {	

			//$app			= JFactory::getApplication();
			//$paramsC 		= $app->isAdmin() ? JComponentHelper::getParams('com_phocacart') : $app->getParams();
			//$equal_height_method	= $paramsC->get( 'equal_height_method', 1 );
			$equal_height_method 	= 0;// FLEXBOX USED
			
			if ($equal_height_method == 1) {
				$this->document->addScript(JURI::root(true).'/media/com_phocacart/js/jquery.equal.heights.js');
				//$this->document->addScript(JURI::root(true).'/media/com_phocacart/js/jquery.equalminheights.min.js');
				$this->document->addScriptDeclaration(			
				'jQuery(window).load(function(){
					jQuery(\'.ph-thumbnail-c\').equalHeights();
				});');
			} else if ($equal_height_method == 2) {
				$this->document->addScript(JURI::root(true).'/media/com_phocacart/js/jquery.matchheight.min.js');
				$this->document->addScriptDeclaration(
				'jQuery(window).load(function(){
					jQuery(\'.ph-thumbnail-c\').matchHeight();
				});');
			} else if ($equal_height_method == 3) {
				
				$this->document->addScript(JURI::root(true).'/media/com_phocacart/js/jquery.matchheight.min.js');
				$this->document->addScriptDeclaration(
				'jQuery(window).load(function(){
					jQuery(\'.ph-thumbnail-c.grid\').matchHeight({
					   byRow: false,
					   property: \'height\',
					   target: null,
					   remove: false
				    });
				});');
			}
			// not ph-thumbnail but only ph-thumbnail-c (in component area so module will not influence it)
		}
	}
	
	
	public function loadPhocaSwapImage($load = 0) {
		if ($load == 1) {
			$this->document->addScript(JURI::root(true).'/media/com_phocacart/js/phoca/jquery.phocaswapimage.js');
		}
	}

	
	public function loadPhocaAttribute($load = 0) {
		if ($load == 1) {
			$this->document->addScript(JURI::root(true).'/media/com_phocacart/js/phoca/jquery.phocaattribute.js');
		}
	}
	
	public function loadRating() {
		JHTML::stylesheet( 'media/com_phocacart/js/barrating/css/rating.css' );
		$this->document->addScript(JURI::root(true).'/media/com_phocacart/js/barrating/jquery.barrating.js');
		$js = "\n". 'jQuery(document).ready(function(){'."\n";
		$js .= 	'   jQuery(\'#phitemrating\').barrating({ showSelectedRating:false });'."\n";		
		$js .= '});'."\n";
		$this->document->addScriptDeclaration($js);
	}
}
?>