<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved. and (C) Calum Polwart 2012
 * @license		GNU General Public License version 3; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.utilities.date');

/**
 * An enhanced user profile plugin to work with AEC and allow paged registration
 *
 * @package		Joomla.Plugin
 * @subpackage	User.profile
 * @version		2.5.6
 */



class plgUserProfile10 extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @access      protected
	 * @param       object  $subject The object to observe
	 * @param       array   $config  An array that holds the plugin configuration
	 * @since       1.5
	 */
	 
	
	
	public function __construct(& $subject, $config)
	{

		parent::__construct($subject, $config);
		include (JPATH_SITE.'/plugins/user/profile10/configuration.php') ;
		$this->loadLanguage();
		JFormHelper::addFieldPath(dirname(__FILE__) . '/fields');
		
	

	}





	/**
	 * @param	string	$context	The context for the data
	 * @param	int		$data		The user id
	 * @param	object
	 *
	 * @return	boolean
	 * @since	1.6
	 */
	function onContentPrepareData($context, $data)
	{
		// Check we are manipulating a valid form.
		if (!in_array($context, array('com_users.profile', 'com_users.user', 'com_users.registration', 'com_admin.profile')))
		{
			return true;
		}

		
		// Check this is not the contact form.
		if (in_array($context, array('com_contact', 'com_users.user'))) 
		{
			return true;
		}




		if (is_object($data))
		{
			$userId = isset($data->id) ? $data->id : 0;

			if (!isset($data->profile) and $userId > 0)
			{
				// Load the profile data from the database.
				$db = JFactory::getDbo();
				$db->setQuery(
					'SELECT profile_key, profile_value FROM #__user_profiles' .
					' WHERE user_id = '.(int) $userId." AND profile_key LIKE 'profile.%'" .
					' ORDER BY ordering'
				);
				$results = $db->loadRowList();

				// Check for a database error.
				if ($db->getErrorNum())
				{
					$this->_subject->setError($db->getErrorMsg());
					return false;
				}

				foreach ($results as $v)
				{
					$k = str_replace('profile.', '', $v[0]);
					$data->profile[$k] = json_decode($v[1], true);
					if ($data->profile[$k] === null)
					{
						$data->profile[$k] = $v[1];
					}
				}

				
			}

			if (!JHtml::isRegistered('users.url'))
			{
				JHtml::register('users.url', array(__CLASS__, 'url'));
			}
			if (!JHtml::isRegistered('users.calendar'))
			{
				JHtml::register('users.calendar', array(__CLASS__, 'calendar'));
			}
			if (!JHtml::isRegistered('users.tos'))
			{
				JHtml::register('users.tos', array(__CLASS__, 'tos'));
			}
		}

		return true;
	}

	public static function url($value)
	{
		if (empty($value))
		{
			return JHtml::_('users.value', $value);
		}
		else
		{
			$value = htmlspecialchars($value);
			if (substr ($value, 0, 4) == "http")
			{
				return '<a href="'.$value.'">'.$value.'</a>';
			}
			else
			{
				return '<a href="http://'.$value.'">'.$value.'</a>';
			}
		}
	}

	public static function calendar($value)
	{
		if (empty($value))
		{
			return JHtml::_('users.value', $value);
		}
		else
		{
			return JHtml::_('date', $value, null, null);
		}
	}

	public static function tos($value)
	{
		if ($value)
		{
			return JText::_('JYES');
		}
		else
		{
			return JText::_('JNO');
		}
	}

	/**
	 * @param	JForm	$form	The form to be altered.
	 * @param	array	$data	The associated data for the form.
	 *
	 * @return	boolean
	 * @since	1.6
	 */
	function onContentPrepareForm($form, $data)
	{

		if (!($form instanceof JForm))
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');
			return false;
		}

		// Check we are manipulating a valid form.
		$name = $form->getName();
		if (!in_array($name, array('com_admin.profile', 'com_users.user', 'com_users.profile', 'com_users.registration')))
		{
			return true;
		}

		$looped_fields = array();
		for($loop = 1; $loop <= LOOP_COUNTER; $loop++ ) {
			$fieldlist = array(
				'firstname'.$loop, 
				'lastname'.$loop, 
				'email'.$loop, 
				'dob'.$loop,  
				'mobile'.$loop, 
				'membertype'.$loop,
				'RYA1_'.$loop,
				'RYA2_'.$loop,
				'ADI_'.$loop,
				'DI_'.$loop,
				'SDI_'.$loop,
				'AWI_'.$loop,
				'WI_'.$loop,
				'SWI_'.$loop,
				'PB2_'.$loop,
				'safetyboat_'.$loop,
				'PBInst_'.$loop,
				'coach_'.$loop,
				'firstaid_'.$loop,
				'firstaid_instructor_'.$loop,
				'GenNews_'.$loop,
				'JuniorNews_'.$loop,
				'Team15_'.$loop,
				'F15_'.$loop,
				'GP14_'.$loop,
				'Handicap_'.$loop,
				'Laser_'.$loop,
				'Mirror_'.$loop,
				'Optimist_'.$loop,
				'Performance_'.$loop,
				'RS200_'.$loop,
				'Tera_'.$loop,
				'Topaz_'.$loop,
				'Topper_'.$loop,
				'Windsurf_'.$loop		
				) ;		
			$looped_fields = array_merge($looped_fields , $fieldlist);
			}

		$fields = array_merge(array(
			'address1',
			'address2',
			'city',
			'county',
			'postcode',
			'country'),
			$looped_fields
			);		
			
		$document =& JFactory::getDocument();
		$document->addScript("http://code.jquery.com/jquery-1.8.3.js");
		$document->addScript(JURI::root( )."media/profile10/scripts/formToWizard.js");
		$document->addScript(JURI::root( )."media/profile10/scripts/jquery.validationEngine.js");
		$document->addScript(JURI::root( )."media/profile10/scripts/jquery.validationEngine-en.js");

		$document->addScript("http://code.jquery.com/ui/1.9.2/jquery-ui.js");


		$document->addScript(JURI::root( )."media/profile10/scripts/ModalPopups.js");
		$document->addStyleSheet(JURI::root( )."media/profile10/style/formProfile.css");
		$document->addStyleSheet(JURI::root( )."media/profile10/style/validationEngine.jquery.css");
		$document->addStyleSheet("http://code.jquery.com/ui/1.9.2/themes/base/jquery-ui.css");

	 	include(JPATH_SITE."/plugins/user/profile10/form.html");
		
	
		
		$form->removeField("name");
		$form->removeField("username");
				$form->removeField("password1");
						$form->removeField("password2");
								$form->removeField("email1");
								$form->removeField("email2");
		$form->removeGroup("default");


						
								
								
		return true;
	}


	function onUserAfterSave($data, $isNew, $result, $error)
	{
		$userId	= JArrayHelper::getValue($data, 'id', 0, 'int');

		if ($userId && $result && isset($data['profile']) && (count($data['profile'])))
		{
			try
			{
				//Sanitize the date
				for($loop = 1; $loop <= LOOP_COUNTER; $loop++ ) {
				if (!empty($data['profile']['dob'.$loop]))
					{
						$date = new JDate($data['profile']['dob'.$loop]);
						$data['profile']['dob'.$loop] = $date->format('Y-m-d');
					}
				}
				$db = JFactory::getDbo();
				$db->setQuery(
					'DELETE FROM #__user_profiles WHERE user_id = '.$userId .
					" AND profile_key LIKE 'profile.%'"
				);

				if (!$db->query())
				{
					throw new Exception($db->getErrorMsg());
				}

				$tuples = array();
				$order	= 1;

				foreach ($data['profile'] as $k => $v)
				{
					$tuples[] = '('.$userId.', '.$db->quote('profile.'.$k).', '.$db->quote(json_encode($v)).', '.$order++.')';
				}

				$db->setQuery('INSERT INTO #__user_profiles VALUES '.implode(', ', $tuples));

				if (!$db->query())
				{
					throw new Exception($db->getErrorMsg());
				}

			}
			catch (JException $e)
			{
				$this->_subject->setError($e->getMessage());
				return false;
			}
		}

		return true;
	}

	/**
	 * Remove all user profile information for the given user ID
	 *
	 * Method is called after user data is deleted from the database
	 *
	 * @param	array		$user		Holds the user data
	 * @param	boolean		$success	True if user was succesfully stored in the database
	 * @param	string		$msg		Message
	 */
	function onUserAfterDelete($user, $success, $msg)
	{
		if (!$success)
		{
			return false;
		}

		$userId	= JArrayHelper::getValue($user, 'id', 0, 'int');

		if ($userId)
		{
			try
			{
				$db = JFactory::getDbo();
				$db->setQuery(
					'DELETE FROM #__user_profiles WHERE user_id = '.$userId .
					" AND profile_key LIKE 'profile.%'"
				);

				if (!$db->query())
				{
					throw new Exception($db->getErrorMsg());
				}
			}
			catch (JException $e)
			{
				$this->_subject->setError($e->getMessage());
				return false;
			}
		}

		return true;
	}
}

