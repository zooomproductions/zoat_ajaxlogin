<?php
class Tx_Ajaxlogin_Tca_FlexForm {

	/**
	 * Generates selectbox for field "country" in table "fe_users"
	 *
	 * @return string
	 */
	public static function country($tca) {
		$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			$tca['config']['foreign_table_uid_field'],
			$tca['config']['foreign_table'],
			'1=1 ' . $tca['config']['foreign_table_where']
		);
		// empty option
		$tca['items'] = array(
			array('', 0)
		);
		foreach($rows as $row) {
			$tca['items'][] = array(
				$row[$tca['config']['foreign_table_uid_field']],
				$row[$tca['config']['foreign_table_uid_field']]
			);
		}
		return $tca;
	}

}
?>