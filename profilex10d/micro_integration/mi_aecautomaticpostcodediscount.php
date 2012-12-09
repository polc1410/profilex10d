<?php
/**
* @version $Id: mi_aecautomaticpostcodediscount.php
* @package AEC - Account Control Expiration - Membership Manager
* @subpackage Micro Integrations - Automatic Post Code Discount MI
* @copyright 2011-2012 Copyright (C) David Deutsch
* @author Calum Polwart Largely based on work from David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
* @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
*/

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this option is not allowed.' );

class mi_aecautomaticpostcodediscount
{
function Info()
{
$info = array();
$info['name'] = JText::_('AEC_MI_AECAUTOMATICPOSTCODEDISCOUNT_NAME');
$info['desc'] = JText::_('AEC_MI_AECAUTOMATICPOSTCODEDISCOUNT_DESC');
$info['type'] = array( 'aec.checkout', 'vendor.valanx' );

return $info;
}

function Settings()
{
$settings = array();
$settings['amount'] = array( 'inputC' );
$settings['mode'] = array( 'list' );
$settings['extra'] = array( 'inputD' );
$settings['postcode_field'] = array( 'inputD' );
$rewriteswitches	= array( 'cms', 'user', 'expiration', 'subscription', 'plan', 'invoice' );
$settings	= AECToolbox::rewriteEngineInfo( $rewriteswitches, $settings );
$settings['match'] = array( 'list' );
$settings['criteria'] = array( 'inputE' );

$modes = array();
$modes[] = JHTML::_('select.option', 'basic', JText::_('MI_MI_AECAUTOMATICDISCOUNT_SET_MODE_BASIC') );
$modes[] = JHTML::_('select.option', 'percentage', JText::_('MI_MI_AECAUTOMATICDISCOUNT_SET_MODE_PERCENTAGE') );

$match = array();
$match[] = JHTML::_('select.option', 'contains', JText::_('MI_MI_AECAUTOMATICDISCOUNT_SET_MODE_CONTAINS') );
$match[] = JHTML::_('select.option', 'notcontain', JText::_('MI_MI_AECAUTOMATICDISCOUNT_SET_MODE_NOTCONTAIN') );

if ( isset( $this->settings['mode'] ) ) {
$val = $this->settings['mode'];
} else {
$val = 'basic';
}

if ( isset( $this->settings['match'] ) ) {
$val = $this->settings['match'];
} else {
$val = 'contains';
}

$settings['lists']['mode'] = JHTML::_('select.genericlist', $modes, 'mode', 'size="1"', 'value', 'text', $val );

$settings['lists']['match'] = JHTML::_('select.genericlist', $match, 'match', 'size="1"', 'value', 'text', $val );

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

$postcode = strtoupper(json_decode(AECToolbox::rewriteEngineRQ( $this->settings['postcode_field'], $request )));
$postcodeStart= trim(substr($postcode, 0, strpos($postcode, " ")));

if ($postcode !=""){
$postcodes = explode(",",$this->settings['criteria']);

if ( $this->settings['match'] == 'contains' ) {

//Check if match contains postcode
if (in_array($postcodeStart, $postcodes)) {


if ( $this->settings['mode'] == 'basic' ) {
$extracost = - $this->settings['amount'];
} else {
$extracost = - AECToolbox::correctAmount( $total * ( $this->settings['amount']/100 ) );
}
} else {

return $request;
}
} else {

//Check if match does not contain postcode
if (in_array($postcodeStart, $postcodes)) {

return $request;
} else {

if ( $this->settings['mode'] == 'basic' ) {
$extracost = - $this->settings['amount'];
} else {
$extracost = - AECToolbox::correctAmount( $total * ( $this->settings['amount']/100 ) );
}
}
}
$newtotal = AECToolbox::correctAmount( $total - $this->settings['amount'] );

$item['terms']->terms[0]->addCost( $extracost, array( 'details' => $this->settings['extra'] ) );
$item['cost'] = $item['terms']->renderTotal();
}


return $request;
}

}
?>