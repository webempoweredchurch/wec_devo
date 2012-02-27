<?php

class tx_wecdevo_procfunc {
	// for new records, set the section to default section
	function setSection($PA, $fobj) {
		if (strpos($PA['uid'],"NEW") === FALSE) {
			return;
		}
		// grab all sections available here...and find the default one
		$defaultSection = -1;
		$TSconfig = t3lib_BEfunc::getTCEFORM_TSconfig($PA['table'],$PA['row']);
		$rootPid = $TSconfig['_STORAGE_PID']?$TSconfig['_STORAGE_PID']:0;
		$table = 'tx_wecdevo_section';
		$where = 'tx_wecdevo_section.pid='.$rootPid;
		if ($PA['pid']) $where .= ' OR tx_wecdevo_section.pid='.$PA['pid'];
		$orderBy = 'tx_wecdevo_section.position';
		$query = 'SELECT * FROM ' . $table . ' WHERE ' . $where . ' ORDER BY ' . $orderBy;
		$res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
		// Loop over all records, adding them to the items array
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			if ($row['start_at'] == 2) {
				$defaultSection = $row['uid'];
				break;
			}
		}

		$selField = $PA['item'];
		// grab all option fields
		preg_match_all('/<option value="(.*)"(.*)>(.*)<\/option>/U',$PA['item'],$matches);
		// if selected == 0 or none selected, then set to default
		$doReset = -1;
		$numSelected = 0;
		for ($i = 0; $i < count($matches); $i++) {
			if (($matches[1][$i] == 0) && strlen($matches[2][$i])) {
				$doReset = $i;
			}
			else if (strlen($matches[2][$i])) {
				$numSelected = $i;
			}
		}
		if (!$numSelected || $doReset) {
			$PA['item']=str_replace('option value="'.$defaultSection.'"','option value="'.$defaultSection.'" selected="selected"', $PA['item']);
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wecdevo/pi1/class.tx_wecdevo_procfunc.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wecdevo/pi1/class.tx_wecdevo_procfunc.php']);
}

?>