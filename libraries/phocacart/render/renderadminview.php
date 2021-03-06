<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die( 'Restricted access' );

class PhocaCartRenderAdminView
{
	public function __construct(){}
	
	public function startForm($option, $view, $itemId, $id = 'adminForm', $name = 'adminForm', $class = '') {
		return '<div id="'.$view.'"><form action="'.JRoute::_('index.php?option='.$option . '&layout=edit&id='.(int) $itemId).'" method="post" name="'.$name.'" id="'.$id.'" class="form-validate '.$class.'" role="form">'."\n"
		.'<div class="row-fluid">'."\n";
	}
	
	public function endForm() {
		return '</div>'."\n".'</form>'."\n".'</div>'."\n";
	}
	
	public function formInputs() {
	
		return '<input type="hidden" name="task" value="" />'. "\n"
		. JHtml::_('form.token'). "\n";
	}
	
	public function navigation($tabs) {
		$o = '<ul class="nav nav-tabs">';
		$i = 0;
		foreach($tabs as $k => $v) {
			$cA = '';
			if ($i == 0) {
				$cA = 'class="active"';
			}
			$o .= '<li '.$cA.'><a href="#'.$k.'" data-toggle="tab">'. $v.'</a></li>'."\n";
			$i++;
		}
		$o .= '</ul>';
		return $o;
	}
	
	public function group($form, $formArray, $clear = 0) {
		$o = '';
		if (!empty($formArray)) {
			if ($clear == 1) {
				foreach ($formArray as $value) {
					$o .= '<div>'. $form->getLabel($value) . '</div>'."\n"
					. '<div class="clearfix"></div>'. "\n"
					. '<div>' . $form->getInput($value). '</div>'."\n";
				} 
			} else {
				foreach ($formArray as $value) {
					$o .= '<div class="control-group">'."\n"
					. '<div class="control-label">'. $form->getLabel($value) . '</div>'."\n"
					. '<div class="controls">' . $form->getInput($value). '</div>'."\n"
					. '</div>' . "\n";
				}
			}
		}
		return $o;
	}
	
	
	
	
	public function item($form, $item, $suffix = '', $realSuffix = 0) {
		$value = $o = '';
		if ($suffix != '') {
			if ($realSuffix) {
				$value = $form->getInput($item) .' '. $suffix;
			} else {
				$value = $suffix;
			}
		} else {
			$value = $form->getInput($item);
		}
		$o .= '<div class="control-group">'."\n";
		$o .= '<div class="control-label">'. $form->getLabel($item) . '</div>'."\n"
		. '<div class="controls">' . $value.'</div>'."\n"
		. '</div>' . "\n";
		return $o;
	}
	
	public function itemLabel($item, $label) {
		$o = '';
		$o .= '<div class="control-group">'."\n";
		$o .= '<div class="control-label">'. $label . '</div>'."\n"
		. '<div class="controls">' . $item.'</div>'."\n"
		. '</div>' . "\n";
		return $o;
	}
	
	public function itemText($item, $label) {
		$o = '';
		$o .= '<div class="control-group ph-control-group-text">'."\n";
		$o .= '<div class="control-label">'. $label . '</div>'."\n"
		. '<div class="controls">' . $item.'</div>'."\n"
		. '</div>' . "\n";
		return $o;
	}
	
	public function itemCalc($id, $name, $value, $form = 'pform', $size = 1) {
	
		switch ($size){
			case 3: $class = 'input-xxlarge';
			break;
			case 2: $class = 'input-xlarge';
			break;
			case 0: $class = 'input-mini';
			break;
			default: $class= 'input-small';
			break;
		}
		$o = '';
		$o .= '<input type="text" name="'.$form.'['.(int)$id.']['.htmlspecialchars($name).']" id="'.$form.'_'.(int)$id.'_'.htmlspecialchars($name).'" value="'.htmlspecialchars($value).'" class="'.htmlspecialchars($class).'" />';
		
		return $o;
	}
	
	public function itemCalcCheckbox($id, $name, $value, $form = 'pform' ) {
	                        
		$checked = '';
		if ($value == 1) {
			$checked = 'checked="checked"';
		}
		$o = '';
		$o .= '<input type="checkbox" name="'.$form.'['.(int)$id.']['.htmlspecialchars($name).']" id="'.$form.'_'.(int)$id.'_'.htmlspecialchars($name).'"  '.$checked.' />';
		
		return $o;
	}
	
	/*
	* Common function for Image, Attribute, Option
	*/
	public function addRowButton($text, $type = 'image') {
	

		$o = '<div id="phrowbox'.$type.'"></div>';
		$o .= '<div style="clear:both;"></div>';
		$o .= '<div class="ph-add-row"><a class="btn btn-success btn-mini" href="#" onclick="phAddRow'.ucfirst($type).'(); return false;"><i class="icon-plus"></i> '.$text.'</a></div>';
		return $o;
	}
	
	/*
	public function additionalImagesRow($id, $url, $value = '', $js = 0) {
		
		// Will be displayed inside Javascript
		$o = '<div class="ph-row-image'.$id.' ph-row-image" id="phrowimage'.$id.'" >'
		.'<div class="ph-add-item">'
		
		.'<div class="input-append">'
		.'<input class="imageCreateThumbs" id="jform_image'.$id.'" name="pformimg['.$id.'][image]" value="'.htmlspecialchars($value).'" class="inputbox" size="40" type="text">'
		.'<a class="modal_jform_image btn" title="'.JText::_('COM_PHOCACART_FORM_SELECT_IMAGE').'" href="'.$url.$id.'"';

		if ($js == 1) {
			$o .= ' rel="{handler: \\\'iframe\\\', size: {x: 780, y: 560}}">';
		} else {
			$o .= ' rel="{handler: \'iframe\', size: {x: 780, y: 560}}">';
		}
		
		$o .= JText::_('COM_PHOCACART_FORM_SELECT_IMAGE').'</a>'
		.'</div>'
		
		.'<input type="hidden" name="pformimg['.$id.'][imageid]" id="jform_imageid'.$id.'" value="'.$id.'" />'
		.'</div>'
		
		.'<div class="ph-remove-row"><a class="btn btn-danger btn-mini" href="#" onclick="phRemoveRowImage('.$id.'); return false;"><i class="icon-minus"></i> '.JText::_('COM_PHOCACART_REMOVE_IMAGE').'</a></div>'
		.'<div class="ph-cb"></div>'
		. '</div>';
		
		return $o;
	}*/
	
	
	public function additionalImagesRow($id, $url, $value = '', $js = 0, $w = 700, $h = 400) {
		
	
		$idA			= 'phFileImageNameModalAT'; //phFileImageNameModal - standard image, phFileImageNameModalAT - additional images
		$textButton		= 'COM_PHOCACART_FORM_SELECT_IMAGE';
	
		// Will be displayed inside Javascript
		$o = '<div class="ph-row-image'.$id.' ph-row-image" id="phrowimage'.$id.'" >'
		.'<div class="ph-add-item">';
		
		$o .='<span class="input-append">'
		.'<input class="imageCreateThumbs inputbox" id="jform_image'.$id.'" name="pformimg['.$id.'][image]" value="'.htmlspecialchars($value).'" size="40" type="text">';
		//$o .= '<a class="modal_jform_image btn" title="'.JText::_('COM_PHOCACART_FORM_SELECT_IMAGE').'" href="'.$url.$id.'"';

		//$o .= '<a href="#'.$idA.'" onclick="setPhRowImageId('.$id.')" role="button" class="btn btn-primary phbtnaddimages" data-toggle="modal" title="' . JText::_($textButton) . '">'
		
	
		
			$o .= ' <a href="#'.$idA.'" role="button" class="btn btn-primary '.$idA.'ModalButton" data-toggle="modal" title="' . JText::_($textButton) . '" data-src="'.$url.$id.'" data-height="'.$h.'" data-width="'.$w.'">'
			. '<span class="icon-list icon-white"></span> '
			. JText::_($textButton) . '</a></span>';

		
		// Javascript rendered by modal windows $this->modalWindowDynamic() but in edit file to produce html code on right place

		
		$o .= '<input type="hidden" name="pformimg['.$id.'][imageid]" id="jform_imageid'.$id.'" value="'.$id.'" />'
		.'</div>'
		
		.'<div class="ph-remove-row"><a class="btn btn-danger" href="#" onclick="phRemoveRowImage('.$id.'); return false;"><i class="icon-minus"></i> '.JText::_('COM_PHOCACART_REMOVE_IMAGE').'</a></div>'
		.'<div class="ph-cb"></div>'
		. '</div>';
		
		return $o;
	}
	
	public function additionalAttributesRow($id, $title, $alias, $required, $type, $js = 0) {
		
		$requiredArray	= PhocaCartAttribute::getRequiredArray();
		$typeArray		= PhocaCartAttribute::getTypeArray();
		$o				= '';
		
		// Will be displayed inside Javascript
		$o .= '<div id="phAttributeBox'.$id.'" class="ph-attribute-box" >';
		
		if ($id == 0) {
			// Add Header
			$o .= '<div class="ph-row">'."\n"
			. '<div class="span2">'. JText::_('COM_PHOCACART_TITLE') . '</div>'
			. '<div class="span2">'. JText::_('COM_PHOCACART_ALIAS') . '</div>'
			. '<div class="span1">'. JText::_('COM_PHOCACART_REQUIRED') . '</div>'
			. '<div class="span2">'. JText::_('COM_PHOCACART_TYPE') . '</div>'
			. '<div class="span5">&nbsp;</div>'
			.'</div><div class="ph-cb"></div>'."\n";
		}
		

		$o .= '<div class="ph-row-attribute'.$id.' ph-row-attribute" id="phrowattribute'.$id.'" >'

		.'<div class="span2">'
		.'<input id="jform_attrtitle'.$id.'" name="pformattr['.$id.'][title]" value="'.htmlspecialchars($title).'" class="inputbox input-small" size="40" type="text">'
		.'</div>'
		
		.'<div class="span2">'
		.'<input id="jform_attralias'.$id.'" name="pformattr['.$id.'][alias]" value="'.htmlspecialchars($alias).'" class="inputbox input-small" size="20" type="text">'
		.'</div>'
		
		.'<div class="span1">'
		. JHtml::_('select.genericlist', $requiredArray, 'pformattr['.$id.'][required]', 'class="input-mini"', 'value', 'text', htmlspecialchars($required), 'jform_attrrequired'.$id)
		.'</div>'
		
		.'<div class="span2">'
		. JHtml::_('select.genericlist', $typeArray, 'pformattr['.$id.'][type]', 'class="input"', 'value', 'text', htmlspecialchars($type), 'jform_attrtype'.$id)
		.'<input type="hidden" name="pformattr['.$id.'][attrid]" id="jform_attrid'.$id.'" value="'.$id.'" />'
		.'</div>'
	
		.'<div class="span5"></div>'
		.'<div class="ph-float-icon"><a class="btn btn-transparent" href="#" onclick="phRemoveRowAttribute('.$id.'); return false;" title="'.JText::_('COM_PHOCACART_REMOVE_ATTRIBUTE').'"><i class="icon-cancel ph-icon-remove"></i>'.''.'</a></div>'
		.'<div class="ph-cb ph-pad-b"></div>'
		
		. '</div>';
		
		if ($js == 1) { 
			$o .= $this->addNewOptionButton($id, $js);
		}
		
		return $o;
	}

	/*
	 * 1 CALL IT BY JAVASCRIPT - we can add button and we can close the additionalAttributesRow box (JS -> BUTTON -> CLOSE)
     * 2 CALL IT BY PHP - we cannot add button and we cannot close the additionalAttributesRow box
	 *                    because we need to list options loaded by database, after they are loaded
	 *                    we call this function specially to add button and to close (inside javascript is it not called specially
	 *                    but by additionalAttributesRow function)
	 *                    (PHP -> OPTIONS -> BUTTON(ADDED SPECIAL) -> CLOSE (ADDED SPECIAL))
	 *                    BE AWARE js must be checked 2x - 1) it decides from where the code is loaded, 2) it changeds the output
	 */
	public function addNewOptionButton($id, $js) {
		
		$o = '';
		if ($js == 1) { 
			$id = '\' + phRowOptionAttributeId +  \'';// if no javascript, get real id, if javascript, get js variable
		}
		$o .= '<div id="phrowboxoptionjs'.$id.'"></div>';
		$o .= '<div style="clear:both;"></div>';
		$o .= '<div class="ph-add-row"><a class="btn btn-primary btn-mini" href="#" onclick="phAddRowOption('.$id.'); return false;"><i class="icon-plus"></i> '.JText::_('COM_PHOCACART_ADD_OPTION').'</a></div>';

		$o .= '</div>';// !!! END OF additionalAttributesRow BOX
		
		return $o;
	}
	
	public function additionalOptionsRow($id, $attrId, $title, $alias, $operator, $amount, $stock, $operatorWeight, $weight, $image, $image_small, $color, $url, $url2, $w = 700, $h = 400) {
		
	

		$operatorArray 	= PhocaCartAttribute::getOperatorArray();
		$o				= '';

		// Will be displayed inside Javascript
		$o .= '<div class="ph-option-box row-fluid" id="phOptionBox'.$attrId.$id.'">';
		$o .= '<div class="ph-row-option'.$attrId.$id.' ph-row-option-attrid'.$attrId.'" id="phrowoption'.$attrId.$id.'" >'
	
		.'<div class="span2">'
		.'<input id="jform_optiontitle'.$attrId.$id.'" name="pformattr['.$attrId.'][option]['.$id.'][title]" value="'.htmlspecialchars($title).'" class="inputbox input-small" size="40" type="text">'
		.'</div>'
		
		.'<div class="span2">'
		.'<input id="jform_optionalias'.$attrId.$id.'" name="pformattr['.$attrId.'][option]['.$id.'][alias]" value="'.htmlspecialchars($alias).'" class="inputbox input-small" size="30" type="text">'
		.'</div>'
		
		// Amount - Value
		.'<div class="span1">'
		. JHtml::_('select.genericlist', $operatorArray, 'pformattr['.$attrId.'][option]['.$id.'][operator]', 'class="input-mini"', 'value', 'text', htmlspecialchars($operator), 'jform_optionoperator'.$attrId. $id)
		.'</div>'
		
		.'<div class="span1">'
		.'<input id="jform_optionamount'.$attrId.$id.'" name="pformattr['.$attrId.'][option]['.$id.'][amount]" value="'.htmlspecialchars($amount).'" class="inputbox input-mini" size="30" type="text">'
		.'</div>'
		
		// Stock
		.'<div class="span1">'
		.'<input id="jform_optionstock'.$attrId.$id.'" name="pformattr['.$attrId.'][option]['.$id.'][stock]" value="'.htmlspecialchars($stock).'" class="inputbox input-mini" size="30" type="text">'
		
		.'<input type="hidden" name="pformattr['.$attrId.'][option]['.$id.'][id]" id="jform_optionid'.$attrId.$id.'" value="'.$id.'" />'
		.'</div>'
		
		
		// Weight
		.'<div class="span1">'
		. JHtml::_('select.genericlist', $operatorArray, 'pformattr['.$attrId.'][option]['.$id.'][operator_weight]', 'class="input-mini"', 'value', 'text', htmlspecialchars($operatorWeight), 'jform_optionoperatorweight'.$attrId. $id)
		.'</div>'
		
		.'<div class="span1">'
		.'<input id="jform_optionweight'.$attrId.$id.'" name="pformattr['.$attrId.'][option]['.$id.'][weight]" value="'.htmlspecialchars($weight).'" class="inputbox input-mini" size="40" type="text">'
		.'</div>';	
		
		
		// Images
		// -----
		$o .= '<div class="span2">';
		
		/*if (is_numeric($attrId) && is_numeric($id)) {
			JHtml::_('behavior.modal', 'a.modal_jform_optionimage'.$attrId.$id);
		} else {
			// Don't render anything for items which will be added by javascript
			// it is set in javascript addnewrow function
			// administrator\components\com_phocacart\libraries\phocacart\render\renderjs.php line cca 171
		}*/
		
		// IMAGE LARGE
		
		$group 			= PhocaCartSettings::getManagerGroup('productimage');
		$managerOutput	= '&amp;manager=productimage';
		$textButton		= 'COM_PHOCACART_FORM_SELECT_'.strtoupper($group['t']);
		$textButton2	= 'COM_PHOCACART_LARGE';
		//$link 			= 'index.php?option=com_phocacart&amp;view=phocacartmanager'.$group['c'].$managerOutput.'&amp;field=jform_optionimage'.$attrId.$id;
		$attr			= '';
		$idA			= 'phFileImageNameModalO';
		
		$html	= array();
		$html[] = '<span class="input-append">';
		$html[] = '<input class="imageCreateThumbs ph-w40" type="text" id="jform_optionimage'.$attrId.$id.'" name="pformattr['.$attrId.'][option]['.$id.'][image]" value="'. htmlspecialchars($image).'"' .' '.$attr.' />';
		
		/*$html[] = '<a class="modal_jform_optionimage'.$attrId.$id.' btn" title="'.JText::_($textButton).'"'
				.' href="'.$link.'"'
				.' rel="{handler: &quot;iframe&quot;, size: {x: 780, y: 560}}">'
				. JText::_($textButton).'</a>';
				
				
		$html[] = '<a href="#'.$idA.'" onclick="setPhRowImgOptionId('.$attrId.','.$id.')" role="button" class="btn btn-primary phbtnaddimagesoptions" data-toggle="modal" title="' . JText::_($textButton) . '">'
			. '<span class="icon-list icon-white"></span> '
			. JText::_($textButton) . '</a>';*/
			
		$html[] = ' <a href="#'.$idA.'" role="button" class="btn btn-primary '.$idA.'ModalButton" data-toggle="modal" title="' . JText::_($textButton) . '" data-src="'.$url . $attrId. $id.'" data-height="'.$h.'" data-width="'.$w.'">'
			. '<span class="icon-list icon-white"></span>'
			. JText::_($textButton2). '</a></span>';
			
		$html[] = '</span>'. "\n";
		
		$o .= implode("\n", $html);
		
		$o .= '<div class="ph-br-small"></div>';
		
		// IMAGE SMALL
		
		$attr			= '';
		$idA			= 'phFileImageNameModalO';
		$textButton2	= 'COM_PHOCACART_SMALL';
		
		$html	= array();
		$html[] = '<span class="input-append">';
		$html[] = '<input class="imageCreateThumbs ph-w40" type="text" id="jform_optionimage_small'.$attrId.$id.'" name="pformattr['.$attrId.'][option]['.$id.'][image_small]" value="'. htmlspecialchars($image_small).'"' .' '.$attr.' />';
		$html[] = ' <a href="#'.$idA.'" role="button" class="btn btn-primary '.$idA.'ModalButton" data-toggle="modal" title="' . JText::_($textButton) . '" data-src="'.$url2 . $attrId. $id.'" data-height="'.$h.'" data-width="'.$w.'">'
			. '<span class="icon-list icon-white"></span>'
			. JText::_($textButton2). '</a></span>';
			
		$html[] = '</span>'. "\n";
		
		$o .= implode("\n", $html);
		
		
		$o .= '</div>';
		
		
		// Color
		// -----
		$o .= '<div class="span1">';
		
		$format 		= 'hex';
		$keywords 		= '';
		$validate 		= ' data-validate="hex"';
		$class			= '';
		$control		= '';
		$readonly		= '';
		$autocomplete 	= true;
		$lang 			= JFactory::getLanguage();
		$position		= '';
		$disabled		= '';
		$required		= '';
		$onchange		= '';
		$autofocus		= 'autofocus';
		
		if (in_array($format, array('rgb', 'rgba')) && $validate != 'color') {
			$alpha = ($format == 'rgba') ? true : false;
			$placeholder = $alpha ? 'rgba(0, 0, 0, 0.5)' : 'rgb(0, 0, 0)';
		} else {
			$placeholder = '#rrggbb';
		}
		
		$inputclass   = ($keywords && ! in_array($format, array('rgb', 'rgba'))) ? ' keywords' : ' ' . $format;
		$class        = ' class="' . trim('minicolors ' . $class) . ($validate == 'color' ? '' : $inputclass) . '"';
		$control      = $control ? ' data-control="' . $control . '"' : '';
		$format       = $format ? ' data-format="' . $format . '"' : '';
		$keywords     = $keywords ? ' data-keywords="' . $keywords . '"' : '';
		$readonly     = $readonly ? ' readonly' : '';
		$hint         = ' placeholder="' . $placeholder . '"';
		$autocomplete = ! $autocomplete ? ' autocomplete="off"' : '';
		$direction    = $lang->isRTL() ? ' dir="ltr" style="text-align:right"' : '';
		
		JHtml::_('jquery.framework');
		JHtml::_('script', 'system/html5fallback.js', false, true);
		JHtml::_('behavior.colorpicker');
		
		/*$jQ = "jQuery('INPUT[type=minicolors]').on('change', function() {
					var hex = jQuery(this).val(),
					opacity = jQuery(this).attr('data-opacity');
					jQuery('BODY').css('backgroundColor', hex);

				});";
		JFactory::getDocument()->addScriptDeclaration($jQ);*/
		
		$html 	= array();
		$html[] =  '<input type="text" id="jform_optioncolor'.$attrId.$id.'" name="pformattr['.$attrId.'][option]['.$id.'][color]"'
				. ' value="'. htmlspecialchars($color, ENT_COMPAT, 'UTF-8') . '"' 
				. $hint . $class . $position . $control
				. $readonly . $disabled . $required . $onchange . $autocomplete . $autofocus
				. $format . $keywords . $direction . $validate . '/>';
		
		
		$o .= implode("\n", $html);
		$o .= '</div>';
		

		
		//$o .= '<div class="span1"></div>';
		
		
		$o .= '<div class="ph-float-icon"><a class="btn btn-transparent" href="#" onclick="phRemoveRowOption('.$id.','.$attrId.'); return false;" title="'.JText::_('COM_PHOCACART_REMOVE_OPTION').'"><i class="icon-cancel ph-icon-remove"></i>'.''.'</a></div>'
		.'<div class="ph-cb"></div>'
		
		
		. '</div>'
		.'</div>';
		
		return $o;
	}
	
	public function headerOption($id = 0) {
		
		$o = '';

		// we have two phrowboxoptions - phrowboxoption - loaded with php/mysql, phrowboxoptionjs - added by javascript
		$o .= '<div id="phrowboxoption'.$id.'">';
		
		$o .= '<h4>'.JText::_('COM_PHOCACART_OPTIONS').'</h4>';
		$o .= '<div class="ph-row">'."\n"
		. '<div class="span2">'. JText::_('COM_PHOCACART_TITLE') . '</div>'
		. '<div class="span2">'. JText::_('COM_PHOCACART_ALIAS') . '</div>'
		
		. '<div class="span1">&nbsp;</div>'
		. '<div class="span1">'. JText::_('COM_PHOCACART_VALUE') . '</div>'
		
		. '<div class="span1">'. JText::_('COM_PHOCACART_IN_STOCK') . '</div>'
		
		. '<div class="span1">&nbsp;</div>'
		. '<div class="span1">'. JText::_('COM_PHOCACART_WEIGHT') . '</div>'
		
		. '<div class="span2">'. JText::_('COM_PHOCACART_IMAGES') . '</div>'
		. '<div class="span1">'. JText::_('COM_PHOCACART_COLOR') . '</div>'
		//. '<div class="span1">&nbsp;</div>'
		.'</div><div class="ph-cb"></div>'."\n";
		
		$o .= '</div>';
		return $o;
	}
	
	
	public function additionalSpecificationsRow($id, $title, $alias, $value, $alias_value, $group, $js = 0) {
		
		$groupArray	= PhocaCartSpecification::getGroupArray();
		$o				= '';
		
		// Will be displayed inside Javascript
		$o .= '<div class="ph-specification-box" id="phSpecificationBox'.$id.'">';
		
		if ($id == 0) {
			// Add Header
			/*$o .= '<div class="ph-row">'."\n"
			. '<div class="span2">'. JText::_('COM_PHOCACART_TITLE') . '</div>'
			. '<div class="span2">'. JText::_('COM_PHOCACART_ALIAS') . '</div>'
			. '<div class="span1">'. JText::_('COM_PHOCACART_REQUIRED') . '</div>'
			. '<div class="span2">'. JText::_('COM_PHOCACART_TYPE') . '</div>'
			. '<div class="span5">&nbsp;</div>'
			.'</div><div class="ph-cb"></div>'."\n";*/
			$o .= $this->headerSpecification();
		}
	
		$o .= '<div class="ph-row-specification'.$id.' ph-row-specification" id="phrowspecification'.$id.'" >'

		.'<div class="span3">'
		.'<input id="jform_spectitle'.$id.'" name="pformspec['.$id.'][title]" value="'.htmlspecialchars($title).'" class="inputbox" size="40" type="text">'
		.'</div>'
		
		.'<div class="span3">'
		.'<textarea id="jform_specvalue'.$id.'" name="pformspec['.$id.'][value]" class="inputbox" rows="3" cols="10" type="textarea">'.htmlspecialchars($value).'</textarea>'
		.'</div>'
		
		.'<div class="span2">'
		. JHtml::_('select.genericlist', $groupArray, 'pformspec['.$id.'][group_id]', 'class="input"', 'value', 'text', (int)$group, 'jform_specgroup'.$id)
		.'</div>'
		
	
		.'<div class="span4"></div>'
		.'<div class="ph-float-icon"><a class="btn btn-transparent" href="#" onclick="phRemoveRowSpecification('.$id.'); return false;" title="'.JText::_('COM_PHOCACART_REMOVE_PARAMETER').'"><i class="icon-cancel ph-icon-remove"></i>'.''.'</a></div>'
		.'<div class="ph-cb ph-pad-b"></div>'
		
		
		
		
		// ALIASES
		.'<div class="ph-row-specification">'
		
		.'<div class="span3">'
		. JText::_('COM_PHOCACART_ALIAS_PARAMETER') . '<br /><input id="jform_specalias'.$id.'" name="pformspec['.$id.'][alias]" value="'.htmlspecialchars($alias).'" class="inputbox" size="40" type="text">'
		.'</div>'
		
		.'<div class="span3">'
		. JText::_('COM_PHOCACART_ALIAS_VALUE') . '<br /><input id="jform_specalias_value'.$id.'" name="pformspec['.$id.'][alias_value]" value="'.htmlspecialchars($alias_value).'" class="inputbox" size="40" type="text">'
		.'</div>'
		
		.'<div class="span2"> </div>'
		
		.'<div class="span4"> </div>'
		.'<div class="ph-cb ph-pad-b"></div>'
		
		.'</div>'
		
		. '</div>'
		. '</div>';
		
		
		return $o;
	}
	
	public function headerSpecification() {
		//$o = '<div class="ph-row" id="phrowboxspecificationheader">'."\n"
		$o = '<div class="ph-row">'."\n"
		. '<div class="span3">'. JText::_('COM_PHOCACART_PARAMETER') . '</div>'
		. '<div class="span3">'. JText::_('COM_PHOCACART_VALUE') . '</div>'
		. '<div class="span2">'. JText::_('COM_PHOCACART_GROUP') . '</div>'
		. '<div class="span4">&nbsp;</div>'
		.'</div><div class="ph-cb"></div>'."\n";
		return $o;
	}
	
	
	public function modalWindow($id, $link, $textButton) {
		
		// Add javascript for additional images
		// Specific case for additional images
		// In case we have more than one "select image form input" and the additional form inputs are made by javascript
		// we need to differentiate between them - the field id for each form input
		// phRowImage is a variable set when clicking select button for additional images
		//$link 			= 'index.php?option=com_phocacart&amp;view=phocacartmanager'.$group['c'].$managerOutput.'&amp;field='.$this->id . '\'+ (phRowImage) +\'';
		$html	= array();
		$html[] = JHtml::_(
			'bootstrap.renderModal',
			$id,
			array(
				'url'    => $link,
				'title'  => JText::_($textButton),
				'width'  => '700px',
				'height' => '400px',
				'modalWidth' => '80',
				'bodyHeight' => '70',
				'footer' => '<button type="button" class="btn" data-dismiss="modal" aria-hidden="true">'
					. JText::_('COM_PHOCACART_CLOSE') . '</button>'
			)
		);
		return implode("\n", $html);
	}
	
	
	public function modalWindowDynamic($id, $textButton, $w = 700, $h = 400, $reload = false) {
		
		
		$s 	= array();
		$s[] = 'jQuery(document).ready(function() {';
		$s[] = '   jQuery(document.body).on(\'click\', \'a.'.$id.'ModalButton\' ,function(e) {';
		$s[] = '      var src = jQuery(this).attr(\'data-src\');';
		//$s[] = '      var height = jQuery(this).attr(\'data-height\') || '.$h.';';
		//$s[] = '      var width = jQuery(this).attr(\'data-width\') || '.$w.';';
		$s[] = '      jQuery("#'.$id.' iframe").attr({\'src\':src, \'height\': \'100%\', \'width\': \'auto\', \'max-height\': \'100%\'});';
		$s[] = '   });';
		
		if ($reload) {
			$s[] = '	jQuery("#'.$id.'").on("hidden", function () {';
			$s[] = '	   var phOverlay = jQuery(\'<div id="phOverlay"><div id="phLoaderFull"> </div></div>\');';
			$s[] = '	   phOverlay.appendTo(document.body);';
			$s[] = '	   jQuery("#phOverlay").fadeIn().css("display","block");';
			$s[] = '		setTimeout(function(){';
			$s[] = '			window.parent.location.reload();';
			$s[] = '		},10);';
			$s[] = '	});';
		}
		$s[] = '})';
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));

		$html	= array();
		$html[] = JHtml::_(
			'bootstrap.renderModal',
			$id,
			array(
				//'url'    => $link,
				'title'  => JText::_($textButton),
				'width'  => $w.'px',
				'height' => $h.'px',
				'modalWidth' => '80',
				'bodyHeight' => '70',
				'footer' => '<button type="button" class="btn" data-dismiss="modal" aria-hidden="true">'
					. JText::_('COM_PHOCACART_CLOSE') . '</button>'
			), 
			'<iframe frameborder="0"></iframe>'
		);
		return implode("\n", $html);
		
		/* Row
		$o .= ' <a href="#'.$idA.'" role="button" class="btn btn-primary '.$idA.'ModalButton" data-toggle="modal" title="' . JText::_($textButton) . '" data-src="'.$url.$id.'" data-height="'.$h.'" data-width="'.$w.'">'
			. '<span class="icon-list icon-white"></span> '
			. JText::_($textButton) . '</a></span>';
		*/
	}
	
}
?>