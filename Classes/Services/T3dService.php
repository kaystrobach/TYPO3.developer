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

class T3dService {
	/**
	 * @var \TYPO3\CMS\Impexp\ImportExport
	 */
	protected $export;

	public function export($filename) {
		$this->export = GeneralUtility::makeInstance(\TYPO3\CMS\Impexp\ImportExport::class);
		$this->export->init(0, 'export');
		$this->export->setCharset('utf-8');
		$this->export->maxFileSize = 10000 * 1024;
		$this->export->excludeMap = array();
		$this->export->softrefCfg = array();
		$this->export->extensionDependencies = array();
		$this->export->showStaticRelations = array();
		$this->export->includeExtFileResources = FALSE;
		$this->export->setSaveFilesOutsideExportFile(TRUE);
		$this->export->setHeaderBasics();

		$this->export->setMetaData(
			'Exported with ext:developer',
			date('Y-m-d H:i:s'),
			'',
			'username',
			'realName',
			'email'
		);

		$idH = array();
		$idH[0]['uid'] = 0;

		$flatList = $this->export->setPageTree($idH);
		foreach ($flatList as $k => $value) {
			$this->export->export_addRecord('pages', BackendUtility::getRecord('pages', $k));
			$this->addRecordsForPid($k, '_ALL', 999999999999999);
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

		$this->export->export_addFilesFromSysFilesRecords();

		$out = $this->export->compileMemoryToFileContent();
		$fExt = ($this->export->doOutputCompress() ? '-z' : '') . '.t3d';

		$mimeType = 'application/octet-stream';
		Header('Content-Type: application/octet-stream');
		Header('Content-Length: ' . strlen($out));
		Header('Content-Disposition: attachment; filename=' . date('Y-m-d-h-i-s').'.t3d');
		echo $out;
		die;
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
		$db = $GLOBALS['TYPO3_DB'];
		foreach ($GLOBALS['TCA'] as $table => $value) {
			if ($table != 'pages' && (in_array($table, $tables) || in_array('_ALL', $tables))) {
				if ($this->getBackendUser()->check('tables_select', $table) && !$GLOBALS['TCA'][$table]['ctrl']['is_static']) {
					$res = $this->exec_listQueryPid($table, $k, MathUtility::forceIntegerInRange($maxNumber, 1));
					while ($subTrow = $db->sql_fetch_assoc($res)) {
						$this->export->export_addRecord($table, $subTrow);
					}
					$db->sql_free_result($res);
				}
			}
		}
	}
}