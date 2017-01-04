<?php

	/**
	* BreadCrumb View Helper
	*@author Joey Adams
	*
	*/
	class Zend_View_Helper_BreadCrumb 
	{

		public function breadCrumb() 
		{
			$module = Zend_Controller_Front::getInstance()->getRequest()->getModuleName();
			$l_m = strtolower($module);

			$controller = Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
			$l_c = strtolower($controller);

			$action = Zend_Controller_Front::getInstance()->getRequest()->getActionName();
			$l_a = strtolower($action);

			// HomePage = No Breadcrumb
			if($l_m == 'default' && $l_c == 'index' && $l_a == 'index'){
				return;
			}
			if($l_a == 'partnerdashboard')
			{
				return;
			}

			// Get our url and create a home crumb
			$fc = Zend_Controller_Front::getInstance();
			$url = $fc->getBaseUrl();
			$path = "";
			if(isset($_SESSION['type']) && $_SESSION['type'] == 'admin')
				$path = $_SESSION['type'];		
			if(isset($_SESSION['type']) && $_SESSION['type'] == 'partner')
				$path = $_SESSION['type'];		
			if(isset($_SESSION['type']) && $_SESSION['type'] == 'customer')
				$path = $_SESSION['location']."/".$_SESSION['type'];		
			$url = BASE_URL.$path;
			$homeLink = "<a href='{$url}/' style='text-decoration:none;' >".strtoupper(tr('breadcrumb', 'home'))."</a>";

			// Start crumbs
			$crumbs = '<div class="clear"></div><div class="breadcrumb"><ul><li>'.$homeLink . "</li>";

			// If our module is default
			if($l_m == 'default') 
			{
				if($l_a == 'index')
				{

					$crumbs .= "&nbsp;&nbsp;<li class='last' >".strtoupper(tr('breadcrumb', $controller))."</li>";
				} 
				else 
				{

					$crumbs .= "<li><a href='{$url}/{$controller}/' style='text-decoration:none;' >".strtoupper(tr('breadcrumb', $controller))."</a> </li>&nbsp;&nbsp; <li class='last'  >".strtoupper(tr('breadcrumb', $action))."</li>";
				}
			}
			else 
			{
				// Non Default Module
				if($l_c == 'index' && $l_a == 'index') {
				$crumbs .= strtoupper(tr('breadcrumb', $module));//$module;
				} else {
				$crumbs .= "<li><a href='{$url}/{$module}/' style='text-decoration:none;' >".strtoupper(tr('breadcrumb', $module))."</a> </li> ";
				if($l_a == 'index') {
					
				$crumbs .=  strtoupper(tr('breadcrumb', $controller));//$controller;
				} else {
					
				$crumbs .= "<li><a href='{$url}/{$module}/{$controller}/' style='text-decoration:none;' >".strtoupper(tr('breadcrumb', $controller))."</a> </li>&nbsp;&nbsp;<li class='last'  >".strtoupper(tr('breadcrumb', $action))."</li>";
				}
				}

			}
			$crumbs .= '</ul></div><div class="clear"></div>';
			return $crumbs;
		}
	}