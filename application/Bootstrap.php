<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	protected function _init()
	{
		
	}
	protected function _initAutoload()
	{
		
		$options = Array(
			'layoutPath' => APPLICATION_PATH."/layout",
			'layout' => 'main',
			);
		
		Zend_Layout::startMvc($options);
		
		
	 
		
		# Set global variables
		/**
		** Zend_Registry::set('test', 'arun');
		** Zend_Registry::get('test');
		**/
		
		
		$moduleLoader = new Zend_Application_Module_Autoloader(array(
		'namespace' => '',
		'basePath' => APPLICATION_PATH));
		
		# Create session for default country
		$default_country = 'NO';
		$change = '0'; # Just hide the country from the signup altogether.
		if (array_key_exists('country', $_GET) && array_key_exists('change', $_GET))
		{
			$_SESSION['default_country'] = $_GET['country'];
			$_SESSION['change'] = $_GET['change'];
		}

		if (empty($_SESSION['language']))
		{
			$_SESSION['default_country'] = $default_country;
			$_SESSION['change'] = $change;
		}
 

		# Load the language file
		$lang = "nb";
		# set session for language
		if (array_key_exists('lang', $_GET))
		{
			$_SESSION['language'] = $_GET['lang'];
		}
		if (empty($_SESSION['language']))
		{
			$_SESSION['language'] = $lang; # $DEFAULT_LANG;
		}
		$language_file = $_SESSION['language'].".php";
		if(!file_exists(APPLICATION_PATH."/language/".$language_file))
		{
			die("Language file is missing");
		}

		$language = isset($_SESSION['language'])?$_SESSION['language']:'nb';
		$translate = new Zend_Translate('array', APPLICATION_PATH.'/language/'.$language_file, $language);

		$registry = Zend_Registry::getInstance();
		$registry->set('Zend_Translate', $translate);
		
		Zend_Loader::loadFile('common_function.php', APPLICATION_PATH."/functions", $once=true);
		Zend_Loader::loadFile('My_Helper_Authenticate.php', APPLICATION_PATH."/controllers/helpers", $once=true);
		
		
		Zend_Controller_Action_HelperBroker::addHelper(
			new My_Helper_Authenticate()
		);
		
		return $moduleLoader;
	}
	 
	protected function _initConfig()
	{
		Zend_Registry::set('config', $this->getOptions());
	}	
}