<?php

/* ContentLanguage Class v0.1.0
 * Handles content language properties and methods.
 *
 * CHANGELOG
 * version 0.1.0, 04 Apr 2006
 *   NEW: Created class.
 */

class ContentLanguage extends DBA_ContentLanguage {

	public static function selectByPK($varValue, $arrFields = array()) {
		global $_CONF;
		parent::$__object = "ContentLanguage";
		parent::$__table = "pcms_language";

		return parent::selectByPK($varValue, $arrFields, $_CONF['app']['account']->getId());
	}

	public static function selectByAbbr($strAbbr) {
		global $_CONF;
		parent::$__object = "ContentLanguage";
		parent::$__table = "pcms_language";

		$objReturn = NULL;
		
		$strSql = sprintf("SELECT * FROM " . parent::$__table . " WHERE abbr = '%s' AND accountId = '%s' ORDER BY sort", quote_smart($strAbbr), $_CONF['app']['account']->getId());
		$objReturns = self::select($strSql);

		if ($objReturns->count() > 0) {
			$objReturn = $objReturns->current();
		}

		return $objReturn;
	}

	public static function selectActiveLanguages() {
		global $_CONF;
		parent::$__object = "ContentLanguage";
		parent::$__table = "pcms_language";

		$strSql = sprintf("SELECT * FROM " . parent::$__table . " WHERE accountId = '%s' and active = '1' ORDER BY sort", $_CONF['app']['account']->getId());

		return parent::select($strSql);
	}

	public static function select($strSql = "") {
		global $_CONF;
		parent::$__object = "ContentLanguage";
		parent::$__table = "pcms_language";

		if (empty($strSql)) {
			$strSql = sprintf("SELECT * FROM " . parent::$__table . " WHERE accountId = '%s' ORDER BY sort", $_CONF['app']['account']->getId());
		}

		return parent::select($strSql);
	}

	public static function getDefault() {
		global $_CONF;
		self::$__object = "ContentLanguage";
		self::$__table = "pcms_language";

		$objReturn = NULL;

		$strSql = sprintf("SELECT * FROM " . self::$__table . " WHERE `default` = '1' AND `accountId` = '%s'", $_CONF['app']['account']->getId());
		$objReturns = self::select($strSql);

		if ($objReturns->count() > 0) {
			$objReturn = $objReturns->current();
		}

		return $objReturn;
	}

	public static function setDefault($intId) {
		global $_CONF;
		self::$__object = "ContentLanguage";
		self::$__table = "pcms_language";

		$objReturn = NULL;

		$strSql = sprintf("SELECT * FROM " . self::$__table . " WHERE `default` = '1' AND `accountId` = '%s'", $_CONF['app']['account']->getId());
		$objLanguages = self::select($strSql);

		foreach ($objLanguages as $objLanguage) {
			$objLanguage->default = 0;
			$objLanguage->save();
		}
		
		$objLanguage = self::selectByPK($intId);
		$objLanguage->default = 1;
		$objLanguage->save();

		return $objReturn;
	}

	public static function hasLanguage($strLanguage) {
		global $_CONF;
		self::$__object = "ContentLanguage";
		self::$__table = "pcms_language";

		$intReturn = 0;

		$strSql = sprintf("SELECT * FROM " . self::$__table . " WHERE `name` = '%s' AND `accountId` = '%s'", quote_smart(strtolower($strLanguage)), $_CONF['app']['account']->getId());
		$objReturns = self::select($strSql);

		if ($objReturns->count() > 0) {
			$intReturn = $objReturns->current()->getId();
		}

		return $intReturn;
	}

	public function delete() {
		global $_CONF;
		self::$__object = "ContentLanguage";
		self::$__table = "pcms_language";
		
		//*** Remove all elements linked to this language.
		$objElements = Element::select();
		foreach ($objElements as $objElement) {
			$objFields = $objElement->getFields();
			foreach ($objFields as $objField) {
				$objValue = $objField->getValueObject($this->id);
				$objValue->delete();
			}
		}
		
		return parent::delete();
	}

	public static function sort($intLangId) {
		$lastSort = 0;
		$arrItemlist = request("itemlist");

		if (is_array($arrItemlist) && count($arrItemlist) > 0) {
			//*** Loop through the items and manipulate the sort order.
			foreach ($arrItemlist as $value) {
				$lastSort++;
				$objLanguage = ContentLanguage::selectByPK($value);
				$objLanguage->setSort($lastSort);
				$objLanguage->save(FALSE);
			}
		}
	}

}

?>