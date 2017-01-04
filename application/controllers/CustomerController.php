<?php

class CustomerController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
		$this->_helper->Authenticate();
		$this->assignCustomerTranslated();
		$this->_helper->viewRenderer->setNoRender(true);

		$this->objCnt    = new Model_DbTable_Contacts();
		$this->objBasic  = new Model_DbTable_DataBasic();
		$this->objValid  = new Model_DbTable_Validate();
		$this->objSub    = new Model_DbTable_Subscription();
		$this->objPartner= new Model_DbTable_Partner();
		$this->objProd = new Model_DbTable_Product();
		$this->objMailer = new Model_DbTable_Mailer();
		$this->objInvoice = new Model_DbTable_Invoice();
		$this->objInvoice->loadInvoiceTable();
		$this->objInvoice->setOwnerType();
		$this->objInvoice->setOwnerTypeSession();
		$this->view->date_format = $this->objBasic->getFormat();
    }
	
	function testwebserviceAction()
	{
 		 
 	}
	/**
	* @Date : 12/24/2009
	* List all customers
	*
	* Show list of all customer
	**/
    public function indexAction()
    {
		global $MAXRESULT;
		# Index page
		$create_bred = "N";
        if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']))
		{
			$this->_helper->layout()->setLayout($_SESSION['type'].'/index');
			createBreadcrumb($_REQUEST, 'customer_list', '1');
			$navigation = parseBreadCrumb();
			$this->view->breadCrumb = $navigation;
			$create_bred = "Y";
		}
		$this->view->create_bred = $create_bred;
		
 		$customer_id = '';
 		$MAXRESULT = 10; # Max records on a page
		$total_record = 0; 
		$show_searchbar = 'none';
		$start = $this->_getParam('start', 0);
		$end = $this->_getParam('end', $MAXRESULT);		
		$page = $this->_getParam('page', '1');	
		$slab_start = $this->_getParam('slab_start', '1');	

		$customer_name = $this->_getParam('customer_name', '');
		$customer_number = $this->_getParam('customer_number', '');
		$customer_ein = $this->_getParam('customer_ein', ''); 

		$contact_firstname = $this->_getParam('contact_firstname', '');
		$contact_lastname = $this->_getParam('contact_lastname', ''); 
		$contact_email = $this->_getParam('contact_email', ''); 
		$show_flag = $this->_getParam('show_flag', '0'); 
	
		$query_string  = "&customer_name=".urlencode($customer_name)."&customer_number=".urlencode($customer_number);
		$query_string .= "&contact_firstname=".urlencode($contact_firstname)."&contact_lastname=".urlencode($contact_lastname);
		$query_string .= "&show_flag=$show_flag&contact_email=$contact_email";
		$query_string .= "&customer_ein=$customer_ein&tab=tab_content";
		
		
		$creditor_id = isset($_SESSION['MEMBER']['PARTNERID'])?$_SESSION['MEMBER']['PARTNERID']:'';

		$fields = " c.customer_id, c.customer_number, c.customer_name, c.customer_zip, c.customer_city, c.customer_country, c.contact_firstname,c.contact_lastname, c.customer_type_id ";
		# Param fields
		$input = Array(
		 'creditor_id' => trim($creditor_id),	
		 'customer_name' => trim($customer_name),
		 'customer_number' => trim($customer_number),
		 'customer_ein' => trim($customer_ein),
		 'contact_firstname' => trim($contact_firstname),
		 'contact_lastname' => trim($contact_lastname),
		 'contact_email' => trim($contact_email),
		 'customer_id' => $customer_id,
		 'start' => $start,
		 'end' => $MAXRESULT,
		);
		
		$order_field = $this->_getParam('ofield', 'c.customer_name');
		$order_key = $this->_getParam('okey', 'ASC');
		if(empty($order_field))
			$order_field = 'c.customer_name';
		if(empty($order_key))
			$order_key = 'ASC';
		
		$query_string .= "&ofield=$order_field&okey=$order_key";
		$this->view->order_field = $order_field;
		$this->view->order_key = $order_key;
		
		# Order by 
		$order_by = " $order_field $order_key ";
		
		# Query to fetch customer list
		$customer_data = $this->objCnt->getFilterCustomers($input, $fields, $order_by);
 
		$this->view->customer_data = $customer_data;

		# Param fields for paging
		$input = Array(
		 'creditor_id' => trim($creditor_id),	
		 'customer_name' => trim($customer_name),
		 'customer_number' => trim($customer_number),
		 'customer_ein' => trim($customer_ein),
		 'contact_firstname' => trim($contact_firstname),
		 'contact_lastname' => trim($contact_lastname),
		 'contact_email' => trim($contact_email),
		 'customer_id' => $customer_id,
		);
		$fields = " COUNT(c.customer_number) as cust_cnt ";
		# Query to fetch count of customer
		$cust_cnt = $this->objCnt->getFilterCustomers($input, $fields);
		$total_record = $cust_cnt[0]['cust_cnt'];
 		$this->view->total_records = $total_record;
		
		$string = '';
		if($show_flag == '1')
			$string = $query_string;

		# create paging
		$query_string = pathType()."/customer/?".$query_string;
		
		$this->view->query_string = $query_string;
		# Ajax paging
		$paging = createAjaxPaging($query_string, $total_record, $start, $end, $page, $slab_start);
		$this->view->paging = $paging;

		
  		if($start != '0')
 			$string .= "&start=".$start."&end=".$end."&page=".$page."&slab_start=".$slab_start;
  		
		$_SESSION['last_query'] = $string;

		editBreadCrumbLink($string, '1', 'N');

		$trigger_newcust = $this->_getParam('nc', 'n');
		$this->view->trigger_newcust = $trigger_newcust;
		$this->view->start = $start;
		$this->view->customer_name= $customer_name;
		$this->view->customer_number= $customer_number;
		$this->view->customer_ein= $customer_ein;
		$this->view->contact_firstname= $contact_firstname;
		$this->view->contact_email= $contact_email;
		$this->view->contact_lastname= $contact_lastname;
		if($show_flag == '1')
			$show_searchbar = '';
		
		if(isset($_SESSION['show_trasc']))
		{
 			unset($_SESSION['show_trasc']);
		}

		$this->view->show_searchbar= $show_searchbar;
		$this->render();			
    }

	public function showcustomerAction()
	{
		
		$this->_helper->layout()->setLayout($_SESSION['type'].'/index');
		# navigation
		$bread_index = $this->_getParam('bread_index', '2');
		createBreadcrumb($_REQUEST, 'showcustomer', $bread_index);
		$navigation = parseBreadCrumb();
		$this->view->breadCrumb = $navigation;
		$this->view->backbuttonurl = getBackButtonUrl($bread_index);
	
		
		$this->view->tab_top_content = 'customer/show-customer.phtml';
		$creditor_id = isset($_SESSION['MEMBER']['PARTNERID'])?$_SESSION['MEMBER']['PARTNERID']:'';
		$customer_id = $this->_getParam('id', '');
		$customer_id = base64_decode($customer_id);

		$cond = " customer_id='".mysql_escape_string($customer_id)."' AND creditor_id='".$creditor_id."' AND status='1' ";
 		$customer_data = $this->objCnt->getCustomer(' * ', $cond);

		if((!is_numeric($customer_id) || empty($customer_id) || empty($customer_data) ) && !isset($_SERVER['HTTP_X_REQUESTED_WITH']) )
		{
			header('Location:'.APPLICATION_URL."customer/");
			exit;
		}
	 
		# Split ccode and phone/fax
		$cell_array = Array(
			'customer_cellphone' => $customer_data[0]['customer_cellphone'],
			'customer_phone' => $customer_data[0]['customer_phone'],
		);
 		$split_arr = $this->objBasic->splitCcodeCellphone( $customer_data[0]['country_code'] , $cell_array);
		$customer_data[0]['customer_cellphone'] = $split_arr['customer_cellphone'];
		$customer_data[0]['customer_phone'] = $split_arr['customer_phone'];
		$customer_data[0]['ccode'] = $split_arr['ccode'];
		$customer_data[0]['f_ccode'] = $split_arr['ccode'];
		$this->view->customer_data = $customer_data[0];
		
 		$cond = " customer_id='".mysql_escape_string($customer_id)."'";
		 
		#show date format for country basis//ritesh
		$birth_date_format = $this->objBasic->getCountryFormat($customer_data[0]['country_code']);
		$this->view->birth_date_format = $birth_date_format;
		#Date format
 		$date_format = $this->objBasic->getFormat();
		$this->view->date_format = $date_format;
		$this->view->default_date_format = returnSiteFormat($date_format);
		
		#show default value (massage) in text box
		$on_save_clear = '';
		$clear = Array('customer_number', 'date_of_birth', 'company_phone', 'cellphone', 'company_email', 'company_zip', 'company_city', 'address');
		$cnt_clear = count($clear);
		for($i=0;$i<$cnt_clear;$i++)
		{
			$on_save_clear .= $clear[$i];
			if($i != ($cnt_clear-1))
				$on_save_clear .= "::";
		}
		$this->view->on_save_clear = $on_save_clear;
		$this->view->clear = $clear;

		# Invoice method
		$this->view->invoice_method = $this->objCnt->getInvoiceMethod('*', '1');

		# for category_id dropdown
		$con = "creditor_id=".$creditor_id;
		$category_drop = $this->objPartner->getCategoryId(' * ', $con);
		
		# Fetch due date preference
		$date_pref= $this->objCnt->getDueDatePreference(' * ', '1');
		$custom = 'y';
		for($i=0;$i<count($date_pref);$i++)
		{
			if($date_pref[$i]['days'] == $customer_data[0]['due_date_preference'])
			{
				$custom = 'n';
				break;
			}
		}
		if(empty($customer_data[0]['due_date_preference']))
			$custom = 'n';

		$this->view->category_drop = $category_drop;
		$this->view->custom = $custom;
		$this->view->date_pref = $date_pref;
		$this->view->customer_id = $customer_id;
		# Get countries 
		$country_lang = $_SESSION['language'];
		$this->view->countries = $this->objCnt->getCountries('country_code, country_name_'.$country_lang.' as country_name', ' 1 ');	
		
		# Navigation start 
		$next_customer_id = '';
		$back_customer_id = '';
		# For next
		$customer_condition = " c.customer_id > '".$customer_id."'";
		$input = Array (
			'customer_condition' => $customer_condition,
 			'creditor_id'      => $creditor_id,
			'start'			  => '0',
			'end'			  => '1'
		 );
		
		$next = $this->objCnt->getFilterCustomers($input , ' c.customer_id',' c.customer_id ASC');
		if(!empty($next) && !empty($next[0]['customer_id']) )
		  $next_customer_id = $next[0]['customer_id'];
 
		# For Back
		$customer_condition = " c.customer_id < '".$customer_id."'";
		$input = Array (
			'creditor_id'      => $creditor_id,
			'customer_condition' => $customer_condition,
			'start'			  => '0',
			'end'			  => '1'
		 );

		$back =  $this->objCnt->getFilterCustomers($input , ' c.customer_id' , '  c.customer_id DESC' );
		if(!empty($back) && !empty($back[0]['customer_id']))
		  $back_customer_id = $back[0]['customer_id'];
	 
		$this->view->back_customer_id = $back_customer_id;
		$this->view->forward_customer_id = $next_customer_id;
		$alert_last_record = 'N';
		$alert_first_record = 'N';
		if(empty($next_customer_id))
			$alert_last_record = 'Y';
	
		if(empty($back_customer_id))
			$alert_first_record = 'Y';	
		
		$this->view->alert_last_record = $alert_last_record;
		$this->view->alert_first_record = $alert_first_record;

		$default_data = $this->objBasic->getAdminPreference(' default_credit_days ', ' 1 ');
		$this->view->default_data = $default_data[0];
		
		$from_cust = 'N';
		if(isset($_SESSION['show_trasc']) && $_SESSION['show_trasc'] == 'Y')
		{
			$from_cust = 'Y';
			unset($_SESSION['show_trasc']);
		}
		$this->view->from_cust = $from_cust;

		$edit_number = '';
		if(!$this->objCnt->isCustomerNumberEdit($customer_id))
		{
			$edit_number = ' readonly ';
		}
		$this->view->edit_number = $edit_number;
		
		$this->view->transaction_string = getTransactionString();

		$customer_balance =  $this->objPartner->get_customer_balance($customer_id,'');
		$overdue_balance =  $this->objPartner->get_overdue_balance($customer_id);
		$current_balance = ABS($customer_balance) - ABS($overdue_balance);
 		if($customer_balance < 0 ) $current_balance = "-".$current_balance;
		
 		$this->view->current_balance = $current_balance;
		$this->view->overdue_balance = $overdue_balance;
		$this->view->open_inv = $this->objPartner->getOpenInvoices($customer_id, $creditor_id);
		$this->assignBrregPrice();
		
 

		# set layout or not 
 		$this->view->view_tab = 'N';
		$page = $this->_getParam('p', 'inv');
 		# Tab dimension
		$tab_dim = Array (
			'width'		=> '110px',
			'back_width' => '96.1%',
			'show_breab_crum' => 'N',
			'name_heading' => tr('heading', 'invoices'),
		);  
		$default_cr_selected = 'class="selected-invoice-tab"';
		$default_vw_selected = '';
		$default_est_selected = '';
		$default_note_selected = '';
		$default_contact_selected = '';
		
		if($page == 'sub')
		{
			$default_contact_selected = '';
			$default_cr_selected = '';
			$default_est_selected = '';
			$default_vw_selected = 'class="selected-invoice-tab"';
		}
		if($page == 'contact')
		{
			$default_vw_selected = '';
			$default_cr_selected = '';
			$default_est_selected = '';
			$default_contact_selected = 'class="selected-invoice-tab"';
		}
		if($page == 'est')
		{
			$default_contact_selected = '';
			$default_cr_selected = '';
			$default_est_selected = 'class="selected-invoice-tab"';
			$default_vw_selected = '';
		}
		if($page == 'note')
		{
			$default_contact_selected = '';
			$default_cr_selected = '';
			$default_note_selected = 'class="selected-invoice-tab"';
		}
 		//echo $page;
	    # Tab param
		$tabs = Array(
			Array(
					'name'		     => 'list-customer-contacts', # Name of tab
					'controller'     => '/customer/list-customer-contacts/', # controller
					'query_string'   => '&tab=tab_content&id='.base64_encode($customer_id), # Query string
					'default_select' => $default_contact_selected, # default selected
			),
			Array(
				'name'		     => 'listcustomerinv', # Name of tab
				'controller'     => '/customer/listcustomerinv/', # controller
				'query_string'   => '&tab=tab_content&id='.base64_encode($customer_id), # Query string 
				'default_select' => $default_cr_selected, # default selected 
			),
			Array(
				'name'		   => 'list_subscription',
				'controller'   => '/customer/listsubscription/',
				'query_string' => '&tab=tab_content&id='.base64_encode($customer_id),
				'default_select' => $default_vw_selected, # default selected 
			),
			Array(
				'name'		   => 'list_estimate',
				'controller'   => '/customer/listestimate/',
				'query_string' => '&tab=tab_content&id='.base64_encode($customer_id),
				'default_select' => $default_est_selected, # default selected 
			),
			Array(
				'name'		   => 'list_notes',
				'controller'   => '/noteslist/addnotepage/',
				'query_string' => '&tab=tab_content&customer_id='.$customer_id,
				'default_select' => $default_note_selected, # default selected 
			),
		);
  		$this->view->tab_param = $tabs;
		$this->view->tab_dim = $tab_dim;

 		$this->render('show-customer'); 
		# END 		
		
		if($page == 'crd')
			$this->_forward('listcustomercredit');
		else if($page == 'sub')
			$this->_forward('listsubscription');
		else if($page == 'est')
			$this->_forward('listestimate');
		else if($page == 'note')
			$this->_forward('addnotepage', 'noteslist', null, array('customer_id' => $customer_id));
		else
			$this->_forward('listcustomerinv'); 
   	}
	 
} # End class