<?php
	
	/**
	** base db wrapper class which extends the 
	** features of zend_Db_Table.
	** Call setTable and setPrimary before using any below 
	** member finctions.
	**/

	class Model_DbTable_DataBasic Extends Zend_Db_Table
	{
		protected $_name = '';
		protected $_primary = '';
		 
		# Set the table name
		function setTable($table)
		{
			$this->_name = $table;
			parent::_setupTableName();
		}
		# Set the primary key.
		function setPrimary($primary)
		{
			$this->_primary = $primary;
			parent::_setupPrimaryKey();
		}
		
		function toArray()
		{
			parent::toArray();
		}
		/**
		** $param  = Array ($
			'fields' => string
			'where' => string
			'order' => string
			'group' => string
			'limit' => string
			)
		**/
		function getData($param=Array())
		{
			$fields = '*';
			if(isset($param['fields']) && !empty($param['fields']))
			{
				$fields = $param['fields'];
			}
			$select = $this->select();
			$select->from($this, array($fields));
			if(isset($param['where']) && !empty($param['where']))
			{
				$select->where($param['where']);
			}
			if(isset($param['order']) && !empty($param['order']))
			{
				$select->order($param['order']);
			}
			if(isset($param['group']) && !empty($param['group']))
			{
				$select->group($param['group']);
			}
			if(isset($param['limit']) && !empty($param['limit']))
			{
				$select->limit($param['limit']);
			}
			//echo $select;
			$rows = $this->fetchAll($select);
			//return $rows->toArray();
			return $rows;
		}

		function getQuery($sql)
		{
			if(empty($sql)) return Array();
			
			$db = $this->getAdapter();
			
			$db->getProfiler()->setEnabled(true);

			$db->setFetchMode(Zend_Db::FETCH_ASSOC);
			$rows = $db->fetchAll($sql);
			
			$query = $db->getProfiler()->getLastQueryProfile()->getQuery();
			$params = $db->getProfiler()->getLastQueryProfile()->getQueryParams();
			$db->getProfiler()->setEnabled(false);
			
			$this->set_debug(count($rows), $query, 'SELECT', $params);
				
			return $rows;
		}

		/**
		** $data - Array (key => $value)
		** $where  - String
		**/
		function updateData($data, $where)
		{
		//	$db = $this->getAdapter();
			
		//	$db->getProfiler()->setEnabled(true);
			$rows_affected = $this->update($data, $where);
			
			//print $db->getProfiler()->getLastQueryProfile()->getQuery();
		//	print_r($db->getProfiler()->getLastQueryProfile()->getQueryParams());
		//	$db->getProfiler()->setEnabled(false);
			
			
			return $rows_affected;
		}
		/**
		** $data - Array (key => $value)
		**/
		function insertData($data)
		{
			return $inserted_id = $this->insert($data);
			
		}
		/**
		** $data - Array (key => $value)
		**/

		function deleteData($where)
		{
			return $rows_affected = $this->delete($where);
		}	
		
		function runQuery($sQuery, $queryType)
 		{
			$db = $this->getAdapter();
			$recordset = $db->query($sQuery);
			$result = $recordset->rowCount();
			return $result;
 		}

		function startTransaction()
		{
			 $db = $this->getAdapter();
			 $db->beginTransaction();
		}

		function transactionCommit()
		{
			 $db = $this->getAdapter();
			 $db->commit();
		}

		function transactionRollback()
		{
			 $db = $this->getAdapter();
			 $db->rollBack();
		}
 
			
	} # End of class