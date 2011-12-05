<?php

/**
 * 
 * Handles alias properties and methods.
 * @author felix
 * @version 0.1.1
 *
 */
class Alias extends DBA_Alias {
	
	public function save($blnSaveModifiedDate = TRUE) {
		parent::$__object = "Alias";
		parent::$__table = "pcms_alias";
		
		$intId = $this->getId();
		
		//*** Remove empty aliasses if this one is not empty.
		$this->clearByLanguage();
		
		$blnReturn = parent::save($blnSaveModifiedDate);
		if (class_exists("AuditLog")) AuditLog::addLog(AUDIT_TYPE_ALIAS, $this->getId(), $this->getAlias(), (empty($intId)) ? "create" : "edit", ($this->getActive()) ? "active" : "inactive");

		return $blnReturn;
	}

	public function delete() {
		parent::$__object = "Alias";
		parent::$__table = "pcms_alias";
		
		if (class_exists("AuditLog")) AuditLog::addLog(AUDIT_TYPE_ALIAS, $this->getId(), $this->getAlias(), "delete");
		return parent::delete();
	}
	
	private function clearByLanguage() {
		parent::$__object = "Alias";
		parent::$__table = "pcms_alias";
	
		if (!empty($this->alias) && !empty($this->languageId)) {
			$strSql = sprintf("DELETE FROM " . parent::$__table . " WHERE accountId = '%s' AND languageId = '%s' AND url = '%s' AND alias = '' ORDER BY sort", $_CONF['app']['account']->getId(), $this->getLanguageId(), $this->getUrl());
			
			return parent::select($strSql);
		}
	}

	public static function select($strSql = "") {
		global $_CONF;
		parent::$__object = "Alias";
		parent::$__table = "pcms_alias";

		if (empty($strSql)) {
			$strSql = sprintf("SELECT * FROM " . parent::$__table . " WHERE accountId = '%s' ORDER BY sort", $_CONF['app']['account']->getId());
		}

		return parent::select($strSql);
	}

	public static function selectSorted() {
		global $_CONF;
		parent::$__object = "Alias";
		parent::$__table = "pcms_alias";

		$strSql = sprintf("SELECT * FROM " . parent::$__table . " WHERE accountId = '%s' ORDER BY alias", $_CONF['app']['account']->getId());

		return parent::select($strSql);
	}

	public static function selectByUrl($strUrl, $intLanguageId = NULL) {
		global $_CONF;
		if (is_null($intLanguageId)) $intLanguageId = ContentLanguage::getDefault()->getId();
		parent::$__object = "Alias";
		parent::$__table = "pcms_alias";
		$objReturn = NULL;

		if (!empty($strUrl)) {
			$strSql = sprintf("SELECT * FROM " . parent::$__table . " WHERE accountId = '%s' AND url = %s AND languageId = %s ORDER BY sort", $_CONF['app']['account']->getId(), parent::quote($strUrl), parent::quote($intLanguageId));
		}

		$objReturn = parent::select($strSql);
		
		return $objReturn;
	}

	public static function selectByAlias($strAlias) {
		global $_CONF;
		parent::$__object = "Alias";
		parent::$__table = "pcms_alias";
		$objReturn = NULL;

		if (!empty($strAlias)) {
			$strSql = sprintf("SELECT * FROM " . parent::$__table . " WHERE accountId = '%s' AND alias = %s ORDER BY sort", $_CONF['app']['account']->getId(), parent::quote($strAlias));
			$objReturn = parent::select($strSql);
		}
		
		return $objReturn;
	}
	
	public static function getCascades($intElementId) {
		$arrReturn = Array();

		$objContentLangs = ContentLanguage::select();
		foreach ($objContentLangs as $objContentLanguage) {
			$objAliases = self::selectByUrl($intElementId, $objContentLanguage->getId());
			if (is_object($objAliases) && $objAliases->count() > 0) {
				$strValue = $objAliases->current()->getCascade();
				if ($strValue == 1) {
					array_push($arrReturn, $objContentLanguage->getId());
				}
			}
		}

		return $arrReturn;
	}

}

?>