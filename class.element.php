<?php

/* Element Class v0.1.0
 * Handles Element properties and methods.
 *
 * CHANGELOG
 * version 0.1.0, 04 Apr 2006
 *   NEW: Created class.
 */

class Element extends DBA_Element {
	private $objElementCollection;
	private $objPermissions;

	public function getPermissions() {
		if (!is_object($this->objPermissions)) {
			$this->objPermissions = ElementPermission::getByElement($this->id);
		}

		return $this->objPermissions;
	}

	public function setPermissions($objPermissions) {
		$this->objPermissions = $objPermissions;
	}

	public static function selectByPK($varValue, $arrFields = array()) {
		global $_CONF;
		parent::$__object = "Element";
		parent::$__table = "pcms_element";

		return parent::selectByPK($varValue, $arrFields, $_CONF['app']['account']->getId());
	}

	public static function select($strSql = "") {
		global $_CONF;
		parent::$__object = "Element";
		parent::$__table = "pcms_element";

		if (empty($strSql)) {
			$strSql = sprintf("SELECT * FROM " . parent::$__table . " WHERE accountId = '%s' ORDER BY sort", $_CONF['app']['account']->getId());
		}

		return parent::select($strSql);
	}

	public function delete() {
		global $_CONF;
		parent::$__object = "Element";
		parent::$__table = "pcms_element";

		//*** Delete fields.
		$this->clearFields();

		return parent::delete($_CONF['app']['account']->getId());
	}

	public function getElements($blnRecursive = FALSE, $intElementType = ELM_TYPE_ALL, $intAccountId = 0) {
		if ($this->id > 0) {
			if (!is_object($this->objElementCollection)) {
				$this->objElementCollection = Elements::getFromParent($this->id, $blnRecursive, $intElementType, $intAccountId);
			}
		} else {
			$this->objElementCollection = new DBA__Collection();
		}

		return $this->objElementCollection;
	}

	public function getFields($blnRecursive = FALSE) {
		$objReturn = new DBA__Collection();

		if ($this->id > 0) {
			$strSql = "SELECT * FROM pcms_element_field WHERE elementId = '" . $this->id . "'";
			$objFields = ElementField::select($strSql);

			if ($blnRecursive === TRUE) {
				foreach ($objFields as $objField) {
					$objTemplateField = TemplateField::selectByPK($objField->templateFieldId);
					$objField->fieldtypeid = $objTemplateField->getTypeId();
					$objField->value = array();

					$objContentLangs = ContentLanguage::select();
					foreach ($objContentLangs as $objContentLanguage) {
						$objField->value[$objContentLanguage->getId()] = $objField->getValueObject($objContentLanguage->getId());
					}
					$objReturn->addObject($objField);
				}
			} else {
				$objReturn = $objFields;
			}
		}

		return $objReturn;
	}

	public function clearFields() {
		if ($this->id > 0) {
			$objFields = $this->getFields();

			if (is_object($objFields)) {
				foreach ($objFields as $objField) {
					$objContentLangs = ContentLanguage::select();
					foreach ($objContentLangs as $objContentLanguage) {
						$objValue = $objField->getValueObject($objContentLanguage->getId());
						if (is_object($objValue)) {
							$objValue->delete();
						}
					}
					$objField->delete();
				}
			}
		}
	}

	public function clearPermissions() {
		if ($this->id > 0) {
			$objPermissions = $this->getPermissions();

			if (is_object($objPermissions)) {
				foreach ($objPermissions as $objPermission) {
					$objPermission->delete();
				}
			}
		}
	}

	public function clearLanguages() {
		if ($this->id > 0) {
			ElementLanguage::deleteByElement($this->id);
		}
	}
	
	public function getAlias() {
		$strReturn = "";
		
		if ($this->id > 0) {
			$objAlias = Alias::selectByUrl($this->id);
			if (is_object($objAlias)) {
				$strReturn = $objAlias->getAlias();
			}
		}
		
		return $strReturn;
	}
	
	public function setAlias($strValue) {
		global $_CONF;
		
		if ($this->id > 0) {
			$objAlias = Alias::selectByUrl($this->id);
			if (empty($strValue) && is_object($objAlias)) {
				$objAlias->delete();
			} else if (!empty($strValue)) {
				if (!is_object($objAlias)) {
					$objAlias = new Alias();
					$objAlias->setAccountId($_CONF['app']['account']->getId());
					$objAlias->setActive(1);
				}
				$objAlias->setUrl($this->id);
				$objAlias->setAlias($strValue);
				$objAlias->save();
			}
		}
	}

	public function duplicate($strNewName = "") {
		global $objLang,
				$_CONF,
				$objLiveUser;

		if ($this->id > 0) {
			//*** Cache the name of the current element.
			$strName = $this->name;

			if (!empty($strNewName)) {
				//*** Set the name of the duplicate element.
				$this->name = sprintf($strNewName, $strName);
			}

			//*** Duplicate the element.
			$objReturn = parent::duplicate();

			//*** Reset the name of the current element.
			$this->name = $strName;

			//*** Duplicate the fields of the current element.
			$objFields = $this->getFields();
			foreach ($objFields as $objField) {
				$objNewField = $objField->duplicate();
				$objNewField->setElementId($objReturn->getId());
				$objNewField->setUsername($objLiveUser->getProperty("name"));
				$objNewField->save();

				$objNewField->setValue($objField->getValue());
			}

			//*** Copy any child elements to the duplicate.
			$strSql = sprintf("SELECT * FROM pcms_element WHERE parentId = '%s' AND accountId = '%s'", $this->id, $_CONF['app']['account']->getId());
			$objElements = Element::select($strSql);

			foreach ($objElements as $objElement) {
				$objElement->copy($objReturn->getId());
			}

			//*** Save permissions.
			$objTemp = $this->getPermissions();
			$objTemp->setElementId($objReturn->getId());
			$objTemp->save();

			//*** Save language properties.
			$objTemps = $this->getLanguageActives();
			foreach ($objTemps as $key => $value) {
				$objReturn->setLanguageActive($value, TRUE);
			}
			
			//*** Save schedule information.
			$objTemp = $this->getSchedule();
			$objTemp->id = 0;
			$objReturn->setSchedule($objTemp);

			return $objReturn;
		}

		return NULL;
	}

	public function copy($intParentId) {
		global $objLiveUser;

		$objDuplicate = $this->duplicate();
		$objDuplicate->setParentId($intParentId);
		$objDuplicate->setUsername($objLiveUser->getProperty("name"));
		$objDuplicate->save();

		return $objDuplicate;
	}

	public function getValueByTemplateField($intFieldId, $intLanguageId = 0, $blnRaw = FALSE) {
		$strReturn = NULL;
		
		if ($this->id > 0) {
			if ($intLanguageId == 0) $intLanguageId = ContentLanguage::getDefault()->getId();

			$objField = $this->getFieldByTemplateField($intFieldId);
			if (is_object($objField)) {
				if ($blnRaw) {
					$strReturn = $objField->getRawValue($intLanguageId);
				} else {
					$strReturn = $objField->getValue($intLanguageId);
				}
			}
		}

		return $strReturn;
	}

	public function getFieldByTemplateField($intFieldId) {
		$objReturn = NULL;

		if ($this->id > 0) {
			$strSql = sprintf("SELECT * FROM pcms_element_field WHERE elementId = '%s' AND templateFieldId = '%s'",
						quote_smart($this->id), quote_smart($intFieldId));
			$objElementFields = ElementField::select($strSql);

			if (is_object($objElementFields) && $objElementFields->count() > 0) {
				$objReturn = $objElementFields->current();
			}
		}

		return $objReturn;
	}

	public function getField($strName) {
		$objReturn = NULL;

		$objFields = $this->getFields();
		foreach ($objFields as $objField) {
			$objTplField = TemplateField::selectByPK($objField->getTemplateFieldId());
			if (is_object($objTplField) && $objTplField->getApiName() == $strName) {
				$objReturn = $objField;
				break;
			}
		}

		return $objReturn;
	}

	public function getSubTemplates() {
		global $_CONF;
		$objReturn = NULL;

		if ($this->typeId == ELM_TYPE_FOLDER) {
			$objParent = Element::selectByPK($this->parentId);

			if (is_object($objParent)) {
				$objReturn = $objParent->getSubTemplates();
			} else {
				$strSql = sprintf("SELECT * FROM pcms_template WHERE parentId = '0' AND accountId = '%s'", $_CONF['app']['account']->getId());
				$objReturn = Template::select($strSql);
			}
		} else {
			$objTemplate = Template::selectByPK($this->templateId);

			if ($objTemplate->getIsContainer()) {				
				$objReturn = $objTemplate->getSiblings(TRUE);
				
				//*** Add child templates.
				$objChildTpls = $objTemplate->getTemplates();

				foreach ($objChildTpls as $objChildTpl) {
					$objReturn->addObject($objChildTpl);
				}
			} else {
				$objReturn = $objTemplate->getTemplates();
			}
		}

		return $objReturn;
	}

	public function getElementsByTemplateName($strName, $blnGetOne = FALSE) {
		global $_CONF;

		$strSql = "SELECT pcms_element.* FROM pcms_element, pcms_template
				WHERE pcms_element.parentId = '%s'
				AND pcms_element.active = '1'
				AND pcms_element.accountId = '%s'
				AND pcms_element.templateId = pcms_template.id
				AND pcms_template.apiName = '%s'
				ORDER BY pcms_element.sort";
		$objElements = Element::select(sprintf($strSql, $this->id, $_CONF['app']['account']->getId(), $strName));

		if ($blnGetOne && $objElements->count(0) > 0) {
			return $objElements->current();
		}

		return $objElements;
	}

	public static function getElementsByTemplateId($intId, $blnGetOne = FALSE) {
		global $_CONF;

		$strSql = "SELECT pcms_element.* FROM pcms_element, pcms_template
				WHERE pcms_element.accountId = '%s'
				AND pcms_element.templateId = pcms_template.id
				AND pcms_template.id = '%s'
				ORDER BY pcms_element.sort";
		$objElements = Element::select(sprintf($strSql, $_CONF['app']['account']->getId(), $intId));

		if ($blnGetOne && $objElements->count() > 0) {
			return $objElements->current();
		}

		return $objElements;
	}

	public static function setParent() {
		$intElementId = request('eid', 0);
		$intParentId = request('parentId', -1);

		$strReturn = "<fields>";
		$strReturn .= "<field name=\"elementId\">";

		if ($intElementId > 0 && $intParentId > -1) {
			$objElement = Element::selectByPK($intElementId);

			if (is_object($objElement)) {
				$objElement->setParentId($intParentId);
				$objElement->save();
			}

			$strReturn .= "<value>$intElementId</value>";
		} else {
			$strReturn .= "<value>-1</value>";
		}

		$strReturn .= "</field>";
		$strReturn .= "</fields>";

		return $strReturn;
	}

	public static function recursivePath($intElementId) {
		$strReturn = "Webroot";

		$objElement = self::selectByPK($intElementId);
		if (is_object($objElement)) {
			$strReturn = "";
			if ($objElement->getParentId() > 0) {
				$strReturn .= self::recursivePath($objElement->getParentId()) . " -> ";
			}
			$strReturn .= $objElement->getName();
		}

		return $strReturn;
	}

	public function getPageId() {
		$intReturn = 0;
		$blnIsPage = 0;

		$objTemplate = Template::selectByPK($this->getTemplateId());
		if (is_object($objTemplate)) {
			$blnIsPage = $objTemplate->getIsPage();
		} else {
			$blnIsPage = $this->getIsPage();
		}

		if ($blnIsPage == 1) {
			$intReturn = $this->getId();
		} elseif ($this->getParentId() > 0) {
			$objParent = Element::selectByPk($this->getParentId());
			$intReturn = $objParent->getPageId();
		}

		return $intReturn;
	}

	public function setLanguageActive($intLanguageId, $blnActive) {
		if ($this->id > 0) {
			$objElementLanguage = new ElementLanguage();

			$objElementLanguage->setElementId($this->id);
			$objElementLanguage->setLanguageId($intLanguageId);
			$objElementLanguage->setActive($blnActive);
			$objElementLanguage->save();
		}
	}

	public function getLanguageActives() {
		$arrReturn = Array();

		if ($this->id > 0) {
			$objElementLanguages = ElementLanguage::selectByElement($this->id);

			if ($objElementLanguages->count() > 0) {
				foreach ($objElementLanguages as $objElementLanguage) {
					if ($objElementLanguage->getActive()) {
						array_push($arrReturn, $objElementLanguage->getLanguageId());
					}
				}
			} else {
				array_push($arrReturn, ContentLanguage::getDefault()->getId());
			}
		}

		return $arrReturn;
	}
	
	public function getSchedule() {
		$objReturn = ElementSchedule::selectByElement($this->id);		
		
		if ($objReturn->count() == 0) {
			$objReturn = new ElementSchedule();
		} else if ($objReturn->count() >= 1) {
			$objReturn = $objReturn->current();
		}
		
		return $objReturn;
	}
	
	public function setSchedule($objSchedule) {
		if ($this->id > 0) {
			$this->clearSchedule();
			
			$objSchedule->setElementId($this->id);
			$objSchedule->save();
		}
	}
		
	public function clearSchedule() {
		if ($this->id > 0) {
			$objSchedules = ElementSchedule::selectByElement($this->id);

			foreach ($objSchedules as $objSchedule) {
				$objSchedule->delete();
			}
		}
	}

}

?>