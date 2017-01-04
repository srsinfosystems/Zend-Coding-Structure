<?php

class My_Helper_Authenticate extends Zend_Controller_Action_Helper_Abstract
{

    public function init()
    {
        /* Initialize action controller here */
		
    }
	

    public function auth()
    {
    	
    	if($this->validateContactPrivilege())
    	{
    		if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'AJAX')
				{
					echo "show_page*Error&#135;_&#135;".utf8_encode(tr('alert', 'denied_permission'));
					exit;
				}
				else
				{
					header('Location:'.APPLICATION_URL."home/error/");
					exit;
				}
    	}
    	
    	
	 		if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'AJAX')
			{
				$_thisRequest = $this->getRequest();
				$control = $_thisRequest->getControllerName();
				$action = $_thisRequest->getActionName();
				#  For admin menu
				if(isset($_SESSION['owner_type']) && $_SESSION['owner_type'] == 'Admin')
					return false;
	
				$ex_Action = Array('registration2submit', 'registration1submit', 'registrationcomplete', 'countrycode','einparse','registersubmit','zip', 'autosuggest', 'deletepartner', 'autologin','mailsubmit', 'resendverificationcode');
				if(in_array($action, $ex_Action))
				{
					return false;
				}
				if(Auth('AUTH') != 'Y')
				{
					echo "show_page*session_error&#135;_&#135;".APPLICATION_URL;
					exit;
				}
				else if (Auth('AUTHTYPE') == 'partner' || Auth('AUTHTYPE') == 'customer')
				{
					if (Auth('AUTHTYPE') != $_SESSION['type'])
					{
						echo "show_page*logout&#135;_&#135;".APPLICATION_URL."index/logout/";
						exit;
					}
				}
			
				if(AUTH('PARTNERID') == '')
				{			
					$excluded_controller = Array('help', 'admin');
					
					if(in_array($control, $excluded_controller))
					{
						return false;
					}
					
					# List of actions for which parter selection is not necessary
					$excluded_Action = Array('admindashboard', 'partnerlist', 'customercreditorlist', 'switchcustomercreditor', 'switchpartner', 'logout');
	
					if(in_array($action, $excluded_Action))
					{
						return false;
					}
	
					if(Auth('AUTHTYPE') == 'customer')
					{
						 $path = APPLICATION_URL."/home/customercreditorlist/";
					}
					else
					{
						$path = BASE_URL.AUTH('AUTHTYPE')."/home/partnerlist/";
					}
	
					echo "show_page*alert_redirect&#135;_&#135;".tr('alert','choose_partner')."&#135;_&#135;".$path;
					exit;
				}			
			}
			else
			{
				#$this->redirectHttps();
				# To validate url according to type of login person 
				$this->validateUrlControl();
				
			
				# For admin menu
				if(isset($_SESSION['owner_type']) && $_SESSION['owner_type'] == 'Admin')
					return false;
	
			
				$_thisRequest = $this->getRequest();
				$control = $_thisRequest->getControllerName('');
				$action = $_thisRequest->getActionName();
				$act_array = Array('index', 'partnerdashboard', 'viewinvoices');
				if(in_array($action, $act_array))
				{
					$_SESSION['redirect_session']['control'] = $control;
					$_SESSION['redirect_session']['action'] = $action;
					$_SESSION['redirect_session']['request'] = $_REQUEST;
				} 
	
				$excluded_control = Array('index', 'help', 'recoverpassword', 'confirm', 'error');
				if(in_array($control, $excluded_control))
				{
					return;
				}
				 
				$excluded_Action = Array('download', 'autosuggest', 'deletepartner');
				if(in_array($action, $excluded_Action))
				{
					return;
				}
			 
				if(Auth('AUTH') != 'Y')
				{
					header('Location:'.APPLICATION_URL);
					exit;
				}
				

				//else if (Auth('AUTHTYPE') == 'partner' || Auth('AUTHTYPE') == 'customer')
				//{
					if (Auth('AUTHTYPE') != $_SESSION['type'])
					{
					  // echo "show_page*logout&#135;_&#135;".APPLICATION_URL."index/logout/";
					  header('Location:'.APPLICATION_URL."index/logout/");
					   exit;
					}
				//}
				$excluded_controller = Array ('home','admin');
	
				$excluded_Action = Array('index', 'admindashboard', 'partnerlist', 'switchpartner', 'switchcustomercreditor', 'logout','customercreditorlist');
				if(in_array($control, $excluded_controller) && in_array($action, $excluded_Action))
				{
					return;
				}

				else if(AUTH('PARTNERID') == '')
				{
					
					/*
					$_thisRequest = $this->getRequest();
					$_thisRequest->setControllerName('home');
					$_thisRequest->setActionName('partnerlist');
					*/
					
					if(Auth('AUTHTYPE') == 'customer')
					{
						header('Location:'.APPLICATION_URL."home/customercreditorlist/");
						exit;
					}
					
					header('Location:'.APPLICATION_URL."home/partnerlist/");
					exit;
									
				}
			}
    }
	
	public function preDispatch()
    {
		if(!defined('_CRONJOB_') || _CRONJOB_ == false)
		{
			return $this->auth();
		}
    }
	
	public function direct()
	{
		if(!defined('_CRONJOB_') || _CRONJOB_ == false)
		{
			return $this->preDispatch();
		}
	}
	
	/**
	* @Date : 2/9/2010
	* To validate url 
	*
	*
	**/
	public function validateUrlControl()
	{
		$_thisRequest = $this->getRequest();
		$control = $_thisRequest->getControllerName();
		$action = $_thisRequest->getActionName();
 		$status = true;
		if(Auth('AUTHTYPE') == 'customer')
		{
			$status = $this->validateToCustomer($control, $action);
		}
		else if(Auth('AUTHTYPE') == 'admin')
		{
			$status = $this->validateToAdmin($control, $action);
			
		}
		else if(Auth('AUTHTYPE') == 'partner')
		{
			$status = $this->validateToPartner($control, $action);
		} 

		if(!$status)
		{
			header('Location:'.APPLICATION_URL."home/error/");
			exit;
		}
	}

	function validateToAdmin($control, $action)
	{
		$control_arr = Array('customeraccount', 'customerinvoice', 'customersubscription','contactaccount');
		if(in_array($control, $control_arr))
			return false;
		
		if($control != 'admin' && isset($_REQUEST['ad_inv']) && $_REQUEST['ad_inv'] == 'Y')
		{
 			return false;
		}

		return true;
	}

	function validateToPartner($control, $action)
	{
		$control_arr = Array('admin', 'customeraccount', 'customerinvoice','customersubscription','contactaccount');
		if(in_array($control, $control_arr))
			return false;
		
 		if(isset($_REQUEST['ad_inv']) && $_REQUEST['ad_inv'] == 'Y')
 			return false;
 
		return true;
	}

	function validateToCustomer($control, $action)
	{
		$control_arr = Array('home', 'customeraccount', 'customerinvoice','customersubscription','help', 'tracking','index','error','generateinvoice','contactaccount');
		if(!in_array($control, $control_arr))
			return false;
		
		if(isset($_REQUEST['ad_inv']) && $_REQUEST['ad_inv'] == 'Y')
 			return false;
 
		return true;
	}
	
	/**
	** Redirect the user on "https" if he is trying to access the site 
	** using http
	**/
	function redirectHttps()
	{
			$uri = "";
			if(isset($_REQUEST['type'])) $uri = $_REQUEST['type']."/";
			 			
			$securepage = 1;
			if ($_SERVER['HTTPS']=='on') {
			// we are on a secure page.
			if (!$securepage) {
			  // but we shouldn't be!
			  $url=BASE_URL.$uri;
			  header('location: '.$url);
			  exit;
			}
		  } else {
			// we aren't on a secure page.
			if ($securepage) {
			  // but we should be!
			  $url=BASE_URL_SECURE.$uri;
			  header('location: '.$url);
			  exit;
			}
		  }
	}
	
	function validateContactPrivilege()
	{
		 if(!isset($_SESSION['MEMBER']['USERID']))
				return false;
	
	 	 $this->objInvoice = new Model_DbTable_Invoice();
		 $this->objInvoice->loadInvoiceTable();		   
	 	 $this->objInvoice->setOwnerType();
		 $owner_type = getOwnerType();
		 if($owner_type == 'Creditor')
		 	return false;
		 	
		 $_thisRequest = $this->getRequest();
		 $control = $_thisRequest->getControllerName();
		 $action = $_thisRequest->getActionName();
		
		 $this->objPartner = new Model_DbTable_Partner();
		  
		 $contact_id = $_SESSION['MEMBER']['USERID'];
		 $cond = " action_name='".mysql_escape_string($control.'_'.$action)."' AND contact_id='$contact_id' AND owner_type='$owner_type' ";
		 $ActionPermission = $this->objPartner->getActionPermission($cond, " a.action_id  ");			
		 if(empty($ActionPermission) || is_null($ActionPermission))		
		 	return false;
		 
		 return true;
	}
	
}