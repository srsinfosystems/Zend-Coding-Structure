<?php
	class Zend_View_Helper_Currency extends Model_DbTable_DataBasic
	{
		function currency($currency_id)
		{
			if(isset($_SESSION['currency_data']))
			{
				$currency_data =  $_SESSION['currency_data'];
			}
			else 
			{
				$sql = "SELECT currency_id, symbol FROM currency WHERE  status='1' ";
				$currency_data = parent::getQuery($sql);
				$_SESSION['currency_data'] = $currency_data;
			}
			$currency = '';
			$cnt_cur = count($currency_data);
			for($i=0;$i<$cnt_cur;$i++)
			{
				if($currency_data[$i]['currency_id'] == $currency_id)
				{
					$currency = $currency_data[$i]['symbol'];
					break;
				}
			}
			return $currency;
		}
	}