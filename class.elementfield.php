<?php

/* ElementField Class v0.1.0
 * Handles ElementField properties and methods.
 *
 * CHANGELOG
 * version 0.1.0, 04 Apr 2006
 *   NEW: Created class.
 */

class ElementField extends DBA_ElementField {
	public $value = "";
	private $rawValue = NULL;
	private $languageId = 0;

	public function getValueObject($intLanguageId = 0) {
		$objValue = NULL;
		$this->languageId = $intLanguageId;

		if ($this->id > 0) {
			//*** Determine the field type using the template.
			$objTemplateField = TemplateField::selectByPK($this->templateFieldId);
			$objTemplateFieldType = TemplateFieldType::selectByPK($objTemplateField->getTypeId());
			$strElement = $objTemplateFieldType->getElement();
			$strClassName = "ElementField{$strElement}";

			$objMethod = new ReflectionMethod($strClassName, 'getByFieldId');
			$objValue = $objMethod->invoke(NULL, $this->id, $this->languageId);
		}

		return $objValue;
	}

	public function getNewValueObject() {
		$objReturn = NULL;

		if ($this->templateFieldId > 0) {
			//*** Determine the field type using the template.
			$objTemplateField = TemplateField::selectByPK($this->templateFieldId);
			$objTemplateFieldType = TemplateFieldType::selectByPK($objTemplateField->getTypeId());
			$strElement = $objTemplateFieldType->getElement();
			$strClassName = "ElementField{$strElement}";

			$objClass = new ReflectionClass($strClassName);
			$objReturn = $objClass->newInstance();
		}

		return $objReturn;
	}

	public function getValue($intLanguageId = 0) {
		$strReturn = "";
		if ($intLanguageId == 0) $intLanguageId = ContentLanguage::getDefault()->getId();

		$objValue = $this->getValueObject($intLanguageId);
		if (is_object($objValue)) {
			/* Perform any format conversions before saving the value to
			 * the database.
			 */

			$objTemplateField = TemplateField::selectByPK($this->templateFieldId);
			switch ($objTemplateField->getTypeId()) {
				case FIELD_TYPE_DATE:
					//*** Convert the date to the predefined format.
					$strReturn = Date::fromMysql($objTemplateField->getValueByName("tfv_field_format")->getValue(), $objValue->getValue());
					break;

				case FIELD_TYPE_LARGETEXT:
					//*** Correct internal anchors.
					$intElementId = Element::selectByPk($this->getElementId())->getPageId();
					$strReturn = str_replace("href=\"#", "href=\"?eid={$intElementId}#", $objValue->getValue());
					break;
					
				case FIELD_TYPE_FILE:
				case FIELD_TYPE_IMAGE:
					//*** Split the current filename from the raw value.
					$arrReturn = array();
					$arrFileTemp = explode("\n", $objValue->getValue());
					foreach ($arrFileTemp as $fileValue) {
						if (!empty($fileValue)) {
							$arrTemp = explode(":", $fileValue);
							$objTemp = array();
							$objTemp["original"] = $arrTemp[0];
							if (count($arrTemp) > 1) {
								$objTemp["src"] = $arrTemp[1];
							} else {
								$objTemp["src"] = $arrTemp[0];
							}		
							array_push($arrReturn, $objTemp);				
						}
					}
					$strReturn = $arrReturn;
					break;

				case FIELD_TYPE_BOOLEAN:
					//*** Make it a true boolean.
					if ($objValue->getValue() == "true") {
						$strReturn = TRUE;
					} else {
						$strReturn = FALSE;
					}
					break;

				default:
					$strReturn = $objValue->getValue();
					break;
			}

			$this->rawValue = $objValue->getValue();
		}

		return $strReturn;
	}

	public function getRawValue($intLanguageId = 0) {
		if ($intLanguageId == 0) $intLanguageId = ContentLanguage::getDefault()->getId();

		if (is_null($this->rawValue) || $this->languageId != $intLanguageId) {
			$this->getValue($intLanguageId);
		}

		return $this->rawValue;
	}

	public function setValue($varValue, $intLanguageId = 0, $blnCascade = FALSE) {
		if ($this->id > 0) {
			$objValue = $this->getNewValueObject();
			$objValue->setValue($varValue);
			$objValue->setLanguageId($intLanguageId);
			$objValue->setCascade($blnCascade);

			$this->setValueObject($objValue);
		}
	}

	public function setValueObject($objValue) {
		if ($this->id > 0) {
			/* Perform any format conversions before saving the value to
			 * the database.
			 */
			$strValue = $objValue->getValue();
			if (!empty($strValue)) {
				$objTemplateField = TemplateField::selectByPK($this->templateFieldId);
				switch ($objTemplateField->getTypeId()) {
					case FIELD_TYPE_DATE:
						$objValue->setValue(Date::toMysql($strValue));
						break;
				}
			}

			$objValue->setFieldId($this->id);
			$objValue->save();
		}
	}

	public function getCascades() {
		$arrReturn = Array();

		if ($this->id > 0) {
			$objContentLangs = ContentLanguage::select();
			foreach ($objContentLangs as $objContentLanguage) {
				$objValue = $this->getValueObject($objContentLanguage->getId());
				if ($objValue->getCascade() == 1) {
					array_push($arrReturn, $objContentLanguage->getId());
				}
			}
		}

		return $arrReturn;
	}

	public function duplicate() {
		if ($this->id > 0) {
			//*** Duplicate the field.
			$objNewField = parent::duplicate();

			//*** Duplicate the values for this field.
			$objContentLangs = ContentLanguage::select();
			foreach ($objContentLangs as $objContentLanguage) {
				$objValue = $this->getValueObject($objContentLanguage->getId());
				if (is_object($objValue)) {
					$objNewValue = $objValue->duplicate();
					$objNewValue->setFieldId($objNewField->getId());
					$objNewValue->save();
				}
			}

			return $objNewField;
		}

		return NULL;
	}
	
	public static function deleteByTemplateId($intTemplateFieldId) {
		$strSql = sprintf("SELECT * FROM pcms_element_field WHERE templateFieldId = '%s'", quote_smart($intTemplateFieldId));
		$objElementFields = ElementField::select($strSql);
		$objContentLangs = ContentLanguage::select();

		foreach ($objElementFields as $objElementField) {
			foreach ($objContentLangs as $objContentLanguage) {
				$objValue = $objElementField->getValueObject($objContentLanguage->getId());
				if (is_object($objValue)) {
					$objValue->delete();
				}
			}
			$objElementField->delete();
		}
	}
}

?>