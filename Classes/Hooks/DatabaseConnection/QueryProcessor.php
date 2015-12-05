<?php

namespace KayStrobach\Developer\Hooks\DatabaseConnection;


use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Database\PostProcessQueryHookInterface;
use TYPO3\CMS\Core\Database\PreProcessQueryHookInterface;
use TYPO3\CMS\Core\SingletonInterface;

class QueryProcessor implements PostProcessQueryHookInterface, SingletonInterface
{
	protected $tablesToIgnore = array(
		'be_sessions',
		'be_users',
		'sys_log',
		'sys_lockedrecords',
		'sys_history'
	);


	/**
	 * Post-processor for the SELECTquery method.
	 *
	 * @param string $select_fields Fields to be selected
	 * @param string $from_table Table to select data from
	 * @param string $where_clause Where clause
	 * @param string $groupBy Group by statement
	 * @param string $orderBy Order by statement
	 * @param int $limit Database return limit
	 * @param \TYPO3\CMS\Core\Database\DatabaseConnection $parentObject
	 * @return void
	 */
	public function exec_SELECTquery_postProcessAction(&$select_fields, &$from_table, &$where_clause, &$groupBy, &$orderBy, &$limit, \TYPO3\CMS\Core\Database\DatabaseConnection $parentObject)
	{
		// do nothing ignore select queries
	}

	/**
	 * Post-processor for the exec_INSERTquery method.
	 *
	 * @param string $table Database table name
	 * @param array $fieldsValues Field values as key => value pairs
	 * @param string|array $noQuoteFields List/array of keys NOT to quote
	 * @param \TYPO3\CMS\Core\Database\DatabaseConnection $parentObject
	 * @return void
	 */
	public function exec_INSERTquery_postProcessAction(&$table, array &$fieldsValues, &$noQuoteFields, \TYPO3\CMS\Core\Database\DatabaseConnection $parentObject)
	{
		$this->processQuery($table);
	}

	/**
	 * Post-processor for the exec_INSERTmultipleRows method.
	 *
	 * @param string $table Database table name
	 * @param array $fields Field names
	 * @param array $rows Table rows
	 * @param string|array $noQuoteFields List/array of keys NOT to quote
	 * @param \TYPO3\CMS\Core\Database\DatabaseConnection $parentObject
	 * @return void
	 */
	public function exec_INSERTmultipleRows_postProcessAction(&$table, array &$fields, array &$rows, &$noQuoteFields, \TYPO3\CMS\Core\Database\DatabaseConnection $parentObject)
	{
		$this->processQuery($table);
	}

	/**
	 * Post-processor for the exec_UPDATEquery method.
	 *
	 * @param string $table Database table name
	 * @param string $where WHERE clause
	 * @param array $fieldsValues Field values as key => value pairs
	 * @param string|array $noQuoteFields List/array of keys NOT to quote
	 * @param \TYPO3\CMS\Core\Database\DatabaseConnection $parentObject
	 * @return void
	 */
	public function exec_UPDATEquery_postProcessAction(&$table, &$where, array &$fieldsValues, &$noQuoteFields, \TYPO3\CMS\Core\Database\DatabaseConnection $parentObject)
	{
		$this->processQuery($table);
	}

	/**
	 * Post-processor for the exec_DELETEquery method.
	 *
	 * @param string $table Database table name
	 * @param string $where WHERE clause
	 * @param \TYPO3\CMS\Core\Database\DatabaseConnection $parentObject
	 * @return void
	 */
	public function exec_DELETEquery_postProcessAction(&$table, &$where, \TYPO3\CMS\Core\Database\DatabaseConnection $parentObject)
	{
		$this->processQuery($table);
	}

	/**
	 * Post-processor for the exec_TRUNCATEquery method.
	 *
	 * @param string $table Database table name
	 * @param \TYPO3\CMS\Core\Database\DatabaseConnection $parentObject
	 * @return void
	 */
	public function exec_TRUNCATEquery_postProcessAction(&$table, \TYPO3\CMS\Core\Database\DatabaseConnection $parentObject)
	{
		$this->processQuery($table);
	}

	/**
	 * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
	 */
	protected function getBackendUser() {
		return $GLOBALS['BE_USER'];
	}

	/**
	 * @return \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected function getDatabaseConnection() {
		return $GLOBALS['TYPO3_DB'];
	}

	/**
	 * @param string $query
	 */
	protected function storeQueryInUserSession($query) {
		$queries = $this->getBackendUser()->getSessionData('developer_queries');
		$queries[] = $query;
		$this->getBackendUser()->setAndSaveSessionData('developer_queries', $queries);
	}

	/**
	 * @return mixed
	 */
	public function getQueriesInUserSession() {
		return $this->getBackendUser()->getSessionData('developer_queries');
	}

	public function resteQueriesInUserSession() {
		$this->getBackendUser()->setAndSaveSessionData('developer_queries', array());
	}

	/**
	 * enables query recording
	 */
	public function enableQueryRecording() {
		$this->getBackendUser()->setAndSaveSessionData('developer_record_queries', TRUE);
		$this->getDatabaseConnection()->store_lastBuiltQuery = TRUE;
	}

	/**
	 * disables query recording
	 */
	public function disableQueryRecording() {
		$this->getBackendUser()->setAndSaveSessionData('developer_record_queries', FALSE);
		$this->getDatabaseConnection()->store_lastBuiltQuery = FALSE;
	}

	/**
	 * @return boolean
	 */
	public function isQueryRecodingEnabled() {
		if(!is_object($this->getBackendUser())) {
			return FALSE;
		}
		return (bool) $this->getBackendUser()->getSessionData('developer_record_queries');
	}

	/**
	 * @param string $table
	 */
	protected function processQuery($table) {
		if(($this->isQueryRecodingEnabled())
			&& (!in_array($table, $this->tablesToIgnore))
			&& (substr($table, 0, 3) !== 'cf_')
		) {
			$this->storeQueryInUserSession($this->getDatabaseConnection()->debug_lastBuiltQuery);
		}
	}
}