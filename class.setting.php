<?php

class Setting extends DBA_Setting {

	public function save($blnSaveModifiedDate = TRUE) {
		self::$__object = "Setting";
		self::$__table = "pcms_setting";
				
		$blnReturn = parent::save($blnSaveModifiedDate);
		
		$objSettingTemplate = SettingTemplate::selectByPk($this->getSettingId());
		AuditLog::addLog(LOG_SETTING, $this->getId(), $objSettingTemplate->getName(), "edit", $this->getValue());

		return $blnReturn;
	}

	public static function selectByName($strName, $intAccountId = 0) {
		global $_CONF;

		self::$__object = "Setting";
		self::$__table = "pcms_setting";

		if ($intAccountId == 0) {
			$intAccountId = $_CONF['app']['account']->getId();
		}

		$objSetting = SettingTemplate::selectByName($strName);
		if (is_object($objSetting)) {
			$strSql = sprintf("SELECT * FROM pcms_setting WHERE accountId = '%s' AND settingId = '%s'", $intAccountId, quote_smart($objSetting->getId()));
			$objSettings = self::select($strSql);

			if ($objSettings->count() > 0) {
				return $objSettings->current();
			}
		}
	}

	public static function getValueByName($strName, $intAccountId = 0) {
		global $_CONF;

		if ($intAccountId == 0) {
			$intAccountId = $_CONF['app']['account']->getId();
		}

		$strValue = "";
		$objSetting = self::selectByName($strName, $intAccountId);

		if (is_object($objSetting)) {
			$strValue = $objSetting->getValue();
		} else {
			//*** Get default value from setting template.
			$objSetting = SettingTemplate::selectByName($strName);
			if (is_object($objSetting)) {
				$strValue = $objSetting->getValue();
			}
		}

		return trim($strValue);
	}

	public static function clearFields() {
		global $_CONF;

		self::$__object = "Setting";
		self::$__table = "pcms_setting";

		$strSql = sprintf("DELETE FROM " . self::$__table . " WHERE accountId = '%s'", $_CONF['app']['account']->getId());
		self::select($strSql);
	}

	public function getName() {
		$strReturn = "";

		$objSetting = SettingTemplate::selectByPK($this->getSettingId());

		if (is_object($objSetting)) {
			$strReturn = $objSetting->getName();
		}

		return $strReturn;
	}

}

?>