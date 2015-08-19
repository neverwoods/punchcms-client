<?php

namespace PunchCMS;

use \Bili\Date;

/**
 *
 * Handles ElementField properties and methods.
 * @author felix
 * @version 0.3.0
 *
 */
class ElementField extends \PunchCMS\DBAL\ElementField
{
    public $value = "";
    public $typeId = null;
    private $rawValue = null;
    private $languageId = 0;

    private static $templateFieldTypeMap = array(
        "bigtext" => "BigText",
        "date" => "Date",
        "number" => "Number",
        "text" => "Text"
    );

    public function getValueObject($intLanguageId = 0)
    {
        $objValue = null;
        $this->languageId = $intLanguageId;

        if ($this->id > 0) {
            //*** Determine the field type using the template.
            $objTemplateField = TemplateField::selectByPK($this->templateFieldId);
            $objTemplateFieldType = TemplateFieldType::selectByPK($objTemplateField->getTypeId());
            $strElement = $objTemplateFieldType->getElement();
            $strClassName = "\\PunchCMS\\ElementField" . self::$templateFieldTypeMap[$strElement];

            $objMethod = new \ReflectionMethod($strClassName, 'getByFieldId');
            $objValue = $objMethod->invoke(null, $this->id, $this->languageId);
        }

        return $objValue;
    }

    public function getNewValueObject()
    {
        $objReturn = null;

        if ($this->templateFieldId > 0) {
            //*** Determine the field type using the template.
            $objTemplateField = TemplateField::selectByPK($this->templateFieldId);
            $objTemplateFieldType = TemplateFieldType::selectByPK($objTemplateField->getTypeId());
            $strElement = $objTemplateFieldType->getElement();
            $strClassName = "\\PunchCMS\\ElementField" . self::$templateFieldTypeMap[$strElement];

            $objClass = new \ReflectionClass($strClassName);
            $objReturn = $objClass->newInstance();
        }

        return $objReturn;
    }

    public function getValue($intLanguageId = 0)
    {
        $strReturn = "";
        if ($intLanguageId == 0) {
            $intLanguageId = ContentLanguage::getDefault()->getId();
        }

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
                            $objTemp["src"] = (count($arrTemp) > 1) ? $arrTemp[1] : $arrTemp[0];
                            $objTemp["media_id"] = (count($arrTemp) > 2) ? $arrTemp[2] : 0;
                            $objTemp["alt"] = (count($arrTemp) > 3) ? $arrTemp[3] : "";
                            array_push($arrReturn, $objTemp);
                        }
                    }
                    $strReturn = $arrReturn;
                    break;

                case FIELD_TYPE_BOOLEAN:
                    //*** Make it a true boolean.
                    if ($objValue->getValue() == "true") {
                        $strReturn = true;
                    } else {
                        $strReturn = false;
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

    public function getRawValue($intLanguageId = 0)
    {
        if ($intLanguageId == 0) {
            $intLanguageId = ContentLanguage::getDefault()->getId();
        }

        if (is_null($this->rawValue) || $this->languageId != $intLanguageId) {
            $this->getValue($intLanguageId);
        }

        return $this->rawValue;
    }

    public function setValue($varValue, $intLanguageId = 0, $blnCascade = false)
    {
        if ($this->id > 0) {
            $objValue = $this->getNewValueObject();
            $objValue->setValue($varValue);
            $objValue->setLanguageId($intLanguageId);
            $objValue->setCascade($blnCascade);

            $this->setValueObject($objValue);
        }
    }

    public function setValueObject($objValue)
    {
        if ($this->id > 0) {
            /* Perform any format conversions before saving the value to
             * the database.
             */
            $strValue = $objValue->getValue();
            if (!empty($strValue) || is_numeric($strValue)) {
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

    public function getCascades()
    {
        $arrReturn = array();

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

    public function duplicate()
    {
        if ($this->id > 0) {
            //*** Duplicate the field.
            $objNewField = parent::duplicate();

            //*** Duplicate the values for this field.
            $objContentLangs = ContentLanguage::select();
            foreach ($objContentLangs as $objContentLanguage) {
                $objValue = $this->getValueObject($objContentLanguage->getId());
                if (is_object($objValue)) {
                    $objNewValue = $objValue->duplicate();
                    if (is_object($objNewValue)) {
                        $objNewValue->setFieldId($objNewField->getId());
                        $objNewValue->save();
                    }
                }
            }

            return $objNewField;
        }

        return null;
    }

    public function getTypeId()
    {
        $intReturn = null;

        $objTemplateField = TemplateField::selectByPK($this->templateFieldId);
        if (is_object($objTemplateField)) {
            $intReturn = $objTemplateField->getTypeId();
        }

        return $intReturn;
    }

    public static function deleteByTemplateId($intTemplateFieldId)
    {
        $strSql = sprintf("SELECT * FROM pcms_element_field WHERE templateFieldId = %s", self::quote($intTemplateFieldId));
        $objElementFields = ElementField::select($strSql);
        $objContentLangs = ContentLanguage::select();

        foreach ($objElementFields as $objElementField) {
            foreach ($objContentLangs as $objContentLanguage) {
                $objValue = $objElementField->getValueObject($objContentLanguage->getId());
                if (is_object($objValue)) {
                    $objValue->delete(true);
                }
            }
            $objElementField->delete();
        }
    }

    public static function fileHasDuplicates($strFileValue, $intOffset = 0)
    {
        global $_CONF;

        $blnReturn = false;

        $strSql = "SELECT pcms_element_field_bigtext.id
            FROM pcms_element_field_bigtext, pcms_element_field, pcms_element
            WHERE pcms_element_field_bigtext.value LIKE %s
            AND pcms_element_field_bigtext.fieldId = pcms_element_field.id
            AND pcms_element_field.elementId = pcms_element.id
            AND pcms_element.accountId = %s";
        $strSql = sprintf($strSql, self::quote('%'. $strFileValue .'\n%%'), self::quote($_CONF['app']['account']->getId()));

        $objElementFields = ElementField::select($strSql);

        if ($objElementFields->count() > $intOffset) {
            $blnReturn = true;
        }

        return $blnReturn;
    }
}
