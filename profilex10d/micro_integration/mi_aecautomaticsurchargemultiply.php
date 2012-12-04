<?php
/**
 * @version $Id: mi_aecautomaticsurchargemultiply.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - Automatic Surcharge Multiply MI
 * @copyright 2011-2012 Copyright (C) David Deutsch
 * @author Calum Polwart, heavily based on Automatic Discount from David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this option is not allowed.' );

/**
 *  This section belongs in a language file but for portability its here.
 */



class mi_aecautomaticsurcharge
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_AECAUTOMATICSURCHARGE_MULTIPLY_NAME');
		$info['desc'] = JText::_('AEC_MI_AECAUTOMATICSURCHARGE_MULTIPLY_DESC');
		$info['type'] = array( 'aec.checkout', 'vendor.valanx' );

		return $info;
	}

	function Settings()
	{
		$settings = array();
		$settings['amount']		= array( 'inputC' );
		$settings['mode']		= array( 'list' );
		$settings['extra']		= array( 'inputD' );
		$settings['multiplier'] = array( 'inputC');

		$modes = array();
		$modes[] = JHTML::_('select.option', 'basic', JText::_('MI_MI_AECAUTOMATICSURCHARGE_MULTIPLY_SET_MODE_BASIC') );
		$modes[] = JHTML::_('select.option', 'percentage', JText::_('MI_MI_AECAUTOMATICSURCHARGE_MULTIPLY_SET_MODE_PERCENTAGE') );

		if ( isset( $this->settings['mode'] ) ) {
			$val = $this->settings['mode'];
		} else {
			$val = 'basic';
		}

		$settings['lists']['mode']			= JHTML::_('select.genericlist', $modes, 'mode', 'size="1"', 'value', 'text', $val );

		return $settings;
	}

	function invoice_item_cost( $request )
	{
		$request = $this->addCost( $request, $request->add );

		return true;
	}

	function addCost( $request, $item )
	{
		$total = $item['terms']->terms[0]->renderTotal();

		if ( $this->settings['mode'] == 'basic' ) {
			$extracost =  $this->settings['amount'] * $this->settings['multiplier'];
		} else {
			$extracost =  AECToolbox::correctAmount( ( $total * ( $this->settings['amount']/100 ) ) * $this->settings['multiplier'] );
		}

		$newtotal = AECToolbox::correctAmount( $total + $this->settings['amount'] );

		$item['terms']->terms[0]->addCost( $extracost, array( 'details' => $this->settings['extra'] ) );
		$item['cost'] = $item['terms']->renderTotal();

		return $request;
	}

}
?>
