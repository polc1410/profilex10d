<?php
/**
 * @version $Id: mi_acymailbulk.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations
 * @copyright Copyright (C) 2011 David Deutsch
 * @author Based on original work by David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
defined('_JEXEC') OR defined( '_VALID_MOS' ) OR die( 'Direct Access to this location is not allowed.' );

class mi_acymailbulk extends MI
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_NAME_ACYMAIL');
		$info['desc'] = JText::_('AEC_MI_DESC_ACYMAIL');
		$info['type'] = array( 'sharing.newsletter', 'vendor.acyba' );

		return $info;
	}

	function Settings()
	{
		$settings = array();

		
		return ;
	}

	function action( $request )
	{
		$lists = array (
		'GenNews' => 1,
		'JuniorSailing' => 3,
		'JuniorWindsurfing' => 4,
		'FlyingFifteen' => 5,
		'GP14' => 6,
		'Handicap' => 7,	
		'Laser' => 8,
		'Mirror' => 9,
		'Optimist' => 10,
		'Performance' => 11,
		'RS-200' => 12,
		'Tera' =>13,
		'Topaz' => 14,
		'Topper' => 15,
		'Windsurf' => 16
		);
		
		for ($r=1; $r <= 25; $r++) {
		
			$key = 'profile_email'.$r;
			$subList = "";
			
			foreach ($lists as $list) {
				if(json_decode($request->metaUser->jProfile[$list.$r])==1){
					$subList .= $lists[$list].",";
				}
			}
			rtrim($subList,',');

			if ($request->metaUser->jProfile[$key]) {
				$url = JURI::root( false ). "index.php";
				$fields = array (
					'option' => 'com_acymailing',
					'ctrl' => 'sub',
					'task' => 'optin',
					'hiddenlists' => $subList,
					'user[email]' => json_decode($request->metaUser->jProfile[$key]),
					'user[name]' => json_decode($request->metaUser->jProfile['profile_firstname'.$r])." ".json_decode($request->metaUser->jProfile['profile_lastname'.$r])
					);
					
				foreach($fields as $key=>$value) { 
					$fields_string .= $key.'='.$value.'&'; 
				}
				rtrim($fields_string,'&');
				
				$ch = curl_init();
	
				curl_setopt($ch, CURLOPT_URL,$url);
				curl_setopt($ch, CURLOPT_POST, count($fields));
				curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
				
				$server_output = curl_exec ($ch);
				
				curl_close ($ch);
				
				
			}
			
		}
	
	}
	


	


}
?>
