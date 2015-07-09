<?php
/**
 * Created by PhpStorm.
 * User: kay
 * Date: 06.02.15
 * Time: 11:40
 */

namespace KayStrobach\Developer\Services;


use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Dbal\Database\DatabaseConnection;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;

class T3dService {
	/**
	 * @var \TYPO3\CMS\Impexp\ImportExport
	 */
	protected $export;

	public function export($saveAt = 'php://stdout') {
		$this->export = GeneralUtility::makeInstance('TYPO3\CMS\Impexp\ImportExport');
		$this->export->init(0, 'export');
		$this->export->setCharset('utf-8');
		$this->export->maxFileSize = 10000 * 1024;
		$this->export->excludeMap = array();
		$this->export->softrefCfg = array();
		$this->export->extensionDependencies = array();
		$this->export->showStaticRelations = array();
		$this->export->includeExtFileResources = FALSE;
		if(method_exists($this->export, 'setSaveFilesOutsideExportFile')) {
			$this->export->setSaveFilesOutsideExportFile(TRUE);
		}
		$this->export->setHeaderBasics();

		$this->export->relStaticTables = array('_ALL');
		$this->export->relOnlyTables = array('_ALL');
		;

		$this->export->setMetaData(
			'Exported with ext:developer',
			date('Y-m-d H:i:s'),
			'',
			'username',
			'realName',
			'email'
		);

		$sPage = array(
			'uid' => 0,
			'title' => 'ROOT'
		);

		$pid = 0;
		$tree = GeneralUtility::makeInstance('TYPO3\CMS\Backend\Tree\View\PageTreeView');
		$tree->init('');
		$HTML = '';
		$tree->tree[] = array('row' => $sPage, 'HTML' => $HTML);
		$tree->buffer_idH = array();
		$tree->getTree($pid, 9999999999, '');

		$idH = array();
		$idH[$pid]['uid'] = $pid;
		if (count($tree->buffer_idH)) {
			$idH[$pid]['subrow'] = $tree->buffer_idH;
		}
		$tree = NULL;
		unset($tree);

		$flatList = $this->export->setPageTree($idH);
		foreach ($flatList as $k => $value) {
			$this->export->export_addRecord('pages', BackendUtility::getRecord('pages', $k));
			$this->addRecordsForPid($k, array('_ALL'), 9999999999);
		}

		// After adding ALL records we set relations:
		for ($a = 0; $a < 10; $a++) {
			$addR = $this->export->export_addDBRelations($a);
			if (!count($addR)) {
				break;
			}
		}

		// Finally files are added:
		// MUST be after the DBrelations are set so that files from ALL added records are included!
		$this->export->export_addFilesFromRelations();

		#$this->export->export_addFilesFromSysFilesRecords();

		$out = $this->export->compileMemoryToFileContent();
		$fExt = ($this->export->doOutputCompress() ? '-z' : '') . '.t3d';

		if($saveAt === 'php://stdout') {
			Header('Content-Type: application/octet-stream');
			Header('Content-Length: ' . strlen($out));
			Header('Content-Disposition: attachment; filename=' . date('Y-m-d-h-i-s').'.t3d');
			echo $out;
			die();
		}
		file_put_contents($saveAt, $out);
		$out = NULL;
		unset($out);
	}

	public function import($filename) {

	}

	/**
	 * Adds records to the export object for a specific page id.
	 *
	 * @param int $k Page id for which to select records to add
	 * @param array $tables Array of table names to select from
	 * @param int $maxNumber Max amount of records to select
	 * @return void
	 */
	public function addRecordsForPid($k, $tables, $maxNumber) {
		if (!is_array($tables)) {
			return;
		}
		$db = $this->getDatabaseConnection();
		foreach ($GLOBALS['TCA'] as $table => $value) {
			if ($table != 'pages' && (in_array($table, $tables) || in_array('_ALL', $tables))) {
				if ($this->getBeUser()->check('tables_select', $table) && !$GLOBALS['TCA'][$table]['ctrl']['is_static']) {
					$res = $this->exec_listQueryPid($table, $k, MathUtility::forceIntegerInRange($maxNumber, 1));
					while ($subTrow = $db->sql_fetch_assoc($res)) {
						$this->export->export_addRecord($table, $subTrow);
					}
					$db->sql_free_result($res);
				}
			}
		}
	}

	/**
	 * Selects records from table / pid
	 *
	 * @param string $table Table to select from
	 * @param int $pid Page ID to select from
	 * @param int $limit Max number of records to select
	 * @return \mysqli_result|object Database resource
	 */
	public function exec_listQueryPid($table, $pid, $limit) {
		$db = $this->getDatabaseConnection();
		$orderBy = $GLOBALS['TCA'][$table]['ctrl']['sortby']
			? 'ORDER BY ' . $GLOBALS['TCA'][$table]['ctrl']['sortby']
			: $GLOBALS['TCA'][$table]['ctrl']['default_sortby'];
		$res = $db->exec_SELECTquery(
			'*',
			$table,
			'pid=' . (int)$pid . BackendUtility::deleteClause($table) . BackendUtility::versioningPlaceholderClause($table),
			'',
			$db->stripOrderBy($orderBy),
			$limit
		);
		return $res;
	}

	/**
	 * @return DatabaseConnection
	 */
	protected function getDatabaseConnection() {
		return $GLOBALS['TYPO3_DB'];
	}

	/**
	 * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
	 */
	public function getBeUser() {
		return $GLOBALS['BE_USER'];
	}
}