<?php

/**
 * 
 * Holds a record from the setting template table.
 * @author felix
 * @version 0.1.0
 *
 */
class SettingTemplate extends DBA_SettingTemplate {

	public static function selectByName($strName) {
		global $_CONF;

		self::$__object = "SettingTemplate";
		self::$__table = "pcms_setting_tpl";

		$strSql = sprintf("SELECT * FROM " . self::$__table . " WHERE name = '%s' ORDER BY section, sort", quote_smart($strName));
		$objSettings = self::select($strSql);

		if ($objSettings->count() > 0) {
			return $objSettings->current();
		}
	}

	public static function getValueByName($strName) {
		$strValue = "";
		$objSetting = self::selectByName($strName);

		if (is_object($objSetting)) {
			$strValue = $objSetting->getValue();
		}

		return $strValue;
	}

}

?>