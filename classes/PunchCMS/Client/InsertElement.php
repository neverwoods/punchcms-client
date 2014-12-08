<?php

namespace PunchCMS\Client;

use PunchCMS\ElementPermission;
use PunchCMS\ContentLanguage;
use PunchCMS\Template;
use PunchCMS\ElementSchedule;

class InsertElement extends Element
{
    private $template = null;
    private $parent = null;
    private $permissions = null;
    private $defaultLanguage = null;
    private $fields = array();
    private $active = false;
    private $name = "";
    private $username = "";
    private $sort = 0;

    public function __construct($objParent)
    {
        $this->parent = $objParent->getElement();

        $this->permissions = new ElementPermission();
        if (is_object($this->parent)) {
            $objPermissions = $this->parent->getPermissions();
            $this->permissions->setUserId($objPermissions->getUserId());
            $this->permissions->setGroupId($objPermissions->getGroupId());
        }

        $this->defaultLanguage = ContentLanguage::getDefault()->getId();
    }

    public function setTemplateName($strApiName)
    {
        $this->template = Template::selectByName($strApiName);
    }

    public function addField($strApiName, $varValue, $intLanguageId = null, $blnCascade = false)
    {
        if (is_null($intLanguageId)) {
            $intLanguageId = $this->defaultLanguage;
        }

        $arrField = (array_key_exists($strApiName, $this->fields)) ? $this->fields[$strApiName] : array();

        if (is_string($intLanguageId)) {
            //*** Insert for all languages.
            $objCms = Client::getInstance();

            $objLangs = $objCms->getLanguages();
            foreach ($objLangs as $objLang) {
                if (($blnCascade && !$objLang->default) || !$blnCascade) {
                    $arrValue = array('value' => $varValue, 'cascade' => $blnCascade);
                    $arrField[$objLang->getId()] = $arrValue;
                }
            }

            if ($blnCascade) {
                //*** Set the default language.
                $arrValue = array('value' => $varValue, 'cascade' => false);
                $arrField[$this->defaultLanguage] = $arrValue;
            }
        } else {
            $arrValue = array('value' => $varValue, 'cascade' => $blnCascade);
            $arrField[$intLanguageId] = $arrValue;
        }

        $this->fields[$strApiName] = $arrField;
    }

    public function save()
    {
        if (is_object($this->template)) {
            $objCms = Client::getInstance();

            //*** Element.
            $objElement = new \PunchCMS\Element();
            $objElement->setParentId($this->parent->getId());
            $objElement->setAccountId($objCms->getAccount()->getId());
            $objElement->setPermissions($this->permissions);
            $objElement->setActive($this->active);
            $objElement->setName($this->name);
            $objElement->setUsername($this->username);
            $objElement->setSort($this->sort);
            $objElement->setTypeId(ELM_TYPE_ELEMENT);
            $objElement->setTemplateId($this->template->getId());
            $objElement->save();

            //*** Activate default schedule.
            $objSchedule = new ElementSchedule();
            $objSchedule->setStartActive(0);
            $objSchedule->setStartDate(PCMS_DEFAULT_STARTDATE);
            $objSchedule->setEndActive(0);
            $objSchedule->setEndDate(PCMS_DEFAULT_ENDDATE);
            $objElement->setSchedule($objSchedule);

            foreach ($this->fields as $apiName => $arrField) {
                $objTemplateField = $this->template->getFieldByName($apiName);
                $objField = new \PunchCMS\ElementField();
                $objField->setElementId($objElement->getId());
                $objField->setTemplateFieldId($objTemplateField->getId());
                $objField->save();

                foreach ($arrField as $intLanguage => $arrValue) {
                    $objValue = $objField->getNewValueObject();
                    $objValue->setValue($arrValue['value']);
                    $objValue->setLanguageId($intLanguage);
                    $objValue->setCascade($arrValue['cascade']);
                    $objField->setValueObject($objValue);

                    //*** Activate the language.
                    $objElement->setLanguageActive($intLanguage, true);
                }
            }

            if (count($this->fields) == 0) {
                //*** Set all languages active if there are no fields.
                $objLangs = $objCms->getLanguages();
                foreach ($objLangs as $objLang) {
                    $objElement->setLanguageActive($objLang->getId(), true);
                }
            }

            return new Element($objElement);
        }
    }

    public function __get($property)
    {
        $property = strtolower($property);

        if (isset($this->$property) || is_null($this->$property)) {
            return $this->$property;
        } else {
            echo "Property Error in " . self::$object . "::get({$property}) on line " . __LINE__ . ".";
        }
    }

    public function __set($property, $value)
    {
        $property = strtolower($property);

        if (isset($this->$property) || is_null($this->$property)) {
            $this->$property = $value;
        } else {
            echo "Property Error in " . self::$object . "::set({$property}) on line " . __LINE__ . ".";
        }
    }

    public function __call($method, $values)
    {
        if (substr($method, 0, 3) == "get") {
            $property = substr($method, 3);
            return $this->$property;
        }

        if (substr($method, 0, 3) == "set") {
            $property = substr($method, 3);
            $this->$property = $values[0];
            return;
        }

        echo "Method Error in " . self::$object . "::{$method} on line " . __LINE__ . ".";
    }
}
