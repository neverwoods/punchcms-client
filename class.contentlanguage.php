<?php

/**
 * 
 * Handles content language properties and methods.
 * @author felix
 * @version 0.1.0
 *
 */
class ContentLanguage extends DBA_ContentLanguage {
	
	public function save($blnSaveModifiedDate = TRUE) {
		parent::$__object = "ContentLanguage";
		parent::$__table = "pcms_language";
		
		$intId = $this->getId();
		
		$blnReturn = parent::save($blnSaveModifiedDate);
		if (class_exists("AuditLog")) AuditLog::addLog(AUDIT_TYPE_LANGUAGE, $this->getId(), $this->getName(), (empty($intId)) ? "create" : "edit", ($this->getActive()) ? "active" : "inactive");

		return $blnReturn;
	}

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
		
		//*** This could take a while.
		set_time_limit(60 * 60);

		$objReturn = NULL;

		//*** Adjust all fields who cascade from the default language.
		$strSql = sprintf("SELECT * FROM " . self::$__table . " WHERE `default` <> '1' AND `accountId` = '%s'", $_CONF['app']['account']->getId());
		$objLanguages = self::select($strSql);
		
		$objDefaultLang = ContentLanguage::getDefault();
		if (is_object($objDefaultLang)) {
			$strSql = "SELECT pcms_element_field.* FROM 
					`pcms_element`,
					`pcms_element_field`,
					`pcms_element_field_bigtext`
				WHERE pcms_element_field.id = pcms_element_field_bigtext.fieldId
				AND pcms_element_field_bigtext.cascade = '1'
				AND pcms_element.id = pcms_element_field.elementId
				AND pcms_element.accountId = '%s'
				UNION 
				SELECT pcms_element_field.* FROM 
					`pcms_element`,
					`pcms_element_field`,
					`pcms_element_field_date`
				WHERE pcms_element_field.id = pcms_element_field_date.fieldId
				AND pcms_element_field_date.cascade = '1'
				AND pcms_element.id = pcms_element_field.elementId
				AND pcms_element.accountId = '%s'
				UNION 
				SELECT pcms_element_field.* FROM 
					`pcms_element`,
					`pcms_element_field`,
					`pcms_element_field_number`
				WHERE pcms_element_field.id = pcms_element_field_number.fieldId
				AND pcms_element_field_number.cascade = '1'
				AND pcms_element.id = pcms_element_field.elementId
				AND pcms_element.accountId = '%s'
				UNION 
				SELECT pcms_element_field.* FROM 
					`pcms_element`,
					`pcms_element_field`,
					`pcms_element_field_text`
				WHERE pcms_element_field.id = pcms_element_field_text.fieldId
				AND pcms_element_field_text.cascade = '1'
				AND pcms_element.id = pcms_element_field.elementId
				AND pcms_element.accountId = '%s'";
			$strSql = sprintf($strSql, $_CONF['app']['account']->getId(), $_CONF['app']['account']->getId(), $_CONF['app']['account']->getId(), $_CONF['app']['account']->getId());
			$objFields = ElementField::select($strSql);
			foreach ($objFields as $objField) {
				$strDefaultValue = $objField->getRawValue($objDefaultLang->getId());
				foreach ($objLanguages as $objLanguage) {
					$objValue = $objField->getValueObject($objLanguage->getId());
					if (is_object($objValue)) {
						$objValue->delete(FALSE);
					}
					
					$objValue = $objField->getNewValueObject();
					$objValue->setValue($strDefaultValue);
					$objValue->setLanguageId($objLanguage->getId());
					$objValue->setCascade(FALSE);
					$objField->setValueObject($objValue);
				}
			}
			
			//*** Set the new default language.
			$objDefaultLang->default = 0;
			$objDefaultLang->save();	
		}
		
		$objLanguage = self::selectByPK($intId);
		$objLanguage->default = 1;		
		$objLanguage->save();
		
		if (class_exists("AuditLog")) AuditLog::addLog(AUDIT_TYPE_LANGUAGE, $objLanguage->getId(), $objLanguage->getName(), "setdefault");

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
		
		//*** Remove all field values for this language.
		$objElements = Element::select();
		foreach ($objElements as $objElement) {
			$objFields = $objElement->getFields();
			foreach ($objFields as $objField) {
				$objValue = $objField->getValueObject($this->id);
				$objValue->delete();
			}
		}
		
		//*** Remove all elements linked to this language.
		ElementLanguage::deleteByLanguage($this->getId());
		
		if (class_exists("AuditLog")) AuditLog::addLog(AUDIT_TYPE_LANGUAGE, $this->getId(), $this->getName(), "delete");
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