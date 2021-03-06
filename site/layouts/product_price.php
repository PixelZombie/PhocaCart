<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
$d 			= $displayData;
$classPS	= '';// class price suffix
if (isset($d['discount']) && $d['discount']) {
	$classPS	= 'ph-line-through';
}

?>
<div id="phItemPriceBox<?php echo $d['typeview'] . (int)$d['product_id']; ?>">
	<div class="<?php echo $d['class']; ?>">
	<?php if (isset($d['priceitemsorig']['bruttoformat']) && $d['priceitemsorig']['bruttoformat']) { ?>
		<div class="ph-price-txt"><?php echo JText::_('COM_PHOCACART_ORIGINAL_PRICE') ?></div>
		<div class="ph-price-original"><?php echo $d['priceitemsorig']['bruttoformat'] ?></div>
	<?php } ?>

	<?php /*
	if ($d['priceitemsorig']['brutto'] > $d['priceitems']['brutto']) {
		if (isset($d['priceitemsorig']['bruttoformat']) && $d['priceitemsorig']['bruttoformat']) { ?>
			<div class="ph-price-txt"><?php echo JText::_('COM_PHOCACART_ORIGINAL_PRICE') ?></div>
			<div class="ph-price-original"><?php echo $d['priceitemsorig']['bruttoformat'] ?></div>
	<?php }
	} */ ?>

	<?php if ($d['priceitems']['netto'] && $d['priceitems']['taxcalc'] > 0) { ?>
		<div class="ph-price-txt <?php echo $classPS; ?>-txt"><?php echo $d['priceitems']['nettotxt'] ?></div>
		<div class="ph-price-netto <?php echo $classPS; ?>"><?php echo $d['priceitems']['nettoformat'] ?></div>
	<?php } ?>

	<?php if ($d['priceitems']['tax'] && $d['priceitems']['taxcalc'] > 0) { ?>
		<div class="ph-tax-txt <?php echo $classPS; ?>-txt"><?php echo $d['priceitems']['taxtxt'] ?></div>
		<div class="ph-tax <?php echo $classPS; ?>"><?php echo $d['priceitems']['taxformat'] ?></div>
	<?php } ?>

	<?php if ($d['priceitems']['brutto']) { ?>
		<div class="ph-price-txt <?php echo $classPS; ?>-txt"><?php echo $d['priceitems']['bruttotxt'] ?></div>
		<div class="ph-price-brutto <?php echo $classPS; ?>"><?php echo $d['priceitems']['bruttoformat'] ?></div>
	<?php } ?>
	
	
	
	
	<?php
	// PRODUCT DISCOUNT
	if (isset ($d['discount']) && $d['discount']) { ?>
		<?php if ($d['priceitemsdiscount']['netto'] && $d['priceitemsdiscount']['taxcalc'] > 0) { ?>
			<div class="ph-price-txt ph-price-discount"><?php echo $d['priceitemsdiscount']['nettotxt'] ?></div>
			<div class="ph-price-netto ph-price-discount"><?php echo $d['priceitemsdiscount']['nettoformat'] ?></div>
		<?php } ?>
	
		<?php if ($d['priceitemsdiscount']['tax'] && $d['priceitemsdiscount']['taxcalc'] > 0) { ?>
			<div class="ph-tax-txt ph-price-discount"><?php echo $d['priceitemsdiscount']['taxtxt'] ?></div>
			<div class="ph-tax ph-price-discount"><?php echo $d['priceitemsdiscount']['taxformat'] ?></div>
		<?php } ?>
	
		<?php if ($d['priceitemsdiscount']['brutto']) { ?>
			<div class="ph-price-txt ph-price-discount"><?php echo $d['priceitemsdiscount']['bruttotxt'] ?></div>
			<div class="ph-price-brutto ph-price-discount ph-price-discount-product"><?php echo $d['priceitemsdiscount']['bruttoformat'] ?></div>
		<?php } ?>
	<?php }?>
	
	<?php
	// CART DISCOUNT DISPLAYED IN PRODUCT VIEWS (under specific conditions only)
	if (isset ($d['discountcart']) && $d['discountcart']) { ?>
		<?php if ($d['priceitemsdiscountcart']['netto'] && $d['priceitemsdiscountcart']['taxcalc'] > 0) { ?>
			<div class="ph-price-txt ph-price-discount"><?php echo $d['priceitemsdiscountcart']['nettotxt'] ?></div>
			<div class="ph-price-netto ph-price-discount"><?php echo $d['priceitemsdiscountcart']['nettoformat'] ?></div>
		<?php } ?>
	
		<?php if ($d['priceitemsdiscountcart']['tax'] && $d['priceitemsdiscountcart']['taxcalc'] > 0) { ?>
			<div class="ph-tax-txt ph-price-discount"><?php echo $d['priceitemsdiscountcart']['taxtxt'] ?></div>
			<div class="ph-tax ph-price-discount"><?php echo $d['priceitemsdiscountcart']['taxformat'] ?></div>
		<?php } ?>
	
		<?php if ($d['priceitemsdiscountcart']['brutto']) { ?>
			<div class="ph-price-txt ph-price-discount"><?php echo $d['priceitemsdiscountcart']['bruttotxt'] ?></div>
			<div class="ph-price-brutto ph-price-discount ph-price-discount-cart"><?php echo $d['priceitemsdiscountcart']['bruttoformat'] ?></div>
		<?php } ?>
	<?php }?>
	
	
	

	<?php if ($d['priceitems']['baseformat']) { ?>
		<div class="ph-price-txt"><?php echo JText::_('COM_PHOCACART_UNIT_PRICE') ?></div>
		<div class="ph-price-base"><?php echo $d['priceitems']['baseformat'] ?></div>
	<?php } ?>

		<div class="ph-cb"></div>
	</div>
</div>
<div class="ph-cb"></div>