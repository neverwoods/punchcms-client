<?php

namespace PunchCMS;

use PunchCMS\DBAL\Collection;
use Bili\Request;
use Bili\FTP;

/**
 *
 * Handles Element properties and methods.
 * @author Felix Langfeldt <felix@neverwoods.com>
 * @version 0.1.2
 *
 * CHANGELOG
 * version 0.1.2, 14 Jul 2009
 *   ADD: Added clearZeroCache method.
 * version 0.1.1, 23 Apr 2008
 *   FIX: getFields fixed.
 * version 0.1.0, 04 Apr 2006
 *   NEW: Created class.
 *
 */
class Element extends \PunchCMS\DBAL\Element
{
    private $objElementCollection;
    private $objPermissions;

    public function getPermissions()
    {
        if (!is_object($this->objPermissions)) {
            $this->objPermissions = ElementPermission::getByElement($this->id);
        }

        return $this->objPermissions;
    }

    public function setPermissions($objPermissions, $blnSave = false)
    {
        $this->objPermissions = $objPermissions;

        if ($blnSave) {
            $this->clearPermissions();
            $this->objPermissions->setElementId($this->id);
            $this->objPermissions->save();
        }
    }

    public static function selectByPK($varValue, $arrFields = array(), $accountId = null)
    {
        global $_CONF;
        parent::$object = "\\PunchCMS\\Element";
        parent::$table = "pcms_element";

        return parent::selectByPK($varValue, $arrFields, $_CONF['app']['account']->getId());
    }

    public static function select($strSql = "")
    {
        global $_CONF;
        parent::$object = "\\PunchCMS\\Element";
        parent::$table = "pcms_element";

        if (empty($strSql)) {
            $strSql = sprintf("SELECT * FROM " . parent::$table . " WHERE accountId = '%s' ORDER BY sort", $_CONF['app']['account']->getId());
        }

        return parent::select($strSql);
    }

    public function delete($accountId = null)
    {
        global $_CONF;
        parent::$object = "\\PunchCMS\\Element";
        parent::$table = "pcms_element";

        //*** Delete fields.
        $this->clearFields(true);

        //*** Delete permissions.
        $this->clearPermissions();

        //*** Delete aliases.
        $this->clearAliases();

        //*** Delete schedules.
        $this->clearSchedule();

        //*** Delete languages.
        $this->clearLanguages();

        //*** Delete feed.
        $this->clearFeed();

        //*** Remove locked elements.
        $objParent = Element::selectByPK($this->getParentId());
        if (is_object($objParent)
            && $objParent->getTypeId() != ELM_TYPE_DYNAMIC
            && $this->getTypeId() != ELM_TYPE_LOCKED
        ) {
            $objOldElements = $objParent->getElements(false, ELM_TYPE_LOCKED, $_CONF['app']['account']->getId());
            foreach ($objOldElements as $objOldElement) {
                $objOldElement->delete();
            }
        }

        //*** Delete child elements.
        $objElements = $this->getElements();
        foreach ($objElements as $objElement) {
            $objElement->delete();
        }

        if (class_exists("\\AuditLog")) {
            \AuditLog::addLog(AUDIT_TYPE_ELEMENT, $this->getId(), $this->getName(), "delete");
        }

        return parent::delete($_CONF['app']['account']->getId());
    }

    public function save($blnSaveModifiedDate = true, $blnCreateForced = true)
    {
        parent::$object = "\\PunchCMS\\Element";
        parent::$table = "pcms_element";

        $intId = $this->getId();

        $blnReturn = parent::save($blnSaveModifiedDate);
        if (class_exists("\\AuditLog")) {
            \AuditLog::addLog(
                AUDIT_TYPE_ELEMENT,
                $this->getId(),
                $this->getName(),
                (empty($intId)) ? "create" : "edit",
                ($this->getActive()) ? "active" : "inactive"
            );
        }

        //*** Save permissions.
        if (is_object($this->objPermissions)) {
            $this->clearPermissions();
            $this->objPermissions->setElementId($this->id);
            $this->objPermissions->save();
        }

        //*** Create forced children.
        if (empty($intId) && $blnCreateForced) {
            $this->createForcedElements();
        }

        return $blnReturn;
    }

    public function getElements($blnRecursive = false, $intElementType = ELM_TYPE_ALL, $intAccountId = 0)
    {
        if ($this->id > 0) {
            if (!is_object($this->objElementCollection)) {
                $this->objElementCollection = Elements::getFromParent($this->id, $blnRecursive, $intElementType, $intAccountId);
            }
        } else {
            $this->objElementCollection = new Collection();
        }

        return $this->objElementCollection;
    }

    public function getFields($blnRecursive = false)
    {
        $objReturn = new Collection();

        if ($this->id > 0) {
            $strSql = "SELECT * FROM pcms_element_field WHERE elementId = '" . $this->id . "'";
            $objFields = ElementField::select($strSql);

            if ($blnRecursive === true) {
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

    public function clearFields($blnRemovePhysical = false)
    {
        if ($this->id > 0) {
            $objFields = $this->getFields();

            if (is_object($objFields)) {
                foreach ($objFields as $objField) {
                    $objContentLangs = ContentLanguage::select();
                    foreach ($objContentLangs as $objContentLanguage) {
                        $objValue = $objField->getValueObject($objContentLanguage->getId());
                        if (is_object($objValue)) {
                            $objValue->delete($blnRemovePhysical);
                        }
                    }
                    $objField->delete();
                }
            }
        }
    }

    public function clearPermissions()
    {
        if ($this->id > 0) {
            $objPermissions = $this->getPermissions();

            if (is_object($objPermissions)) {
                $objPermissions->delete();
            }
        }
    }

    public function clearLanguages()
    {
        if ($this->id > 0) {
            ElementLanguage::deleteByElement($this->id);
        }
    }

    public function getAlias($intLanguageId = null)
    {
        $strReturn = "";

        if ($this->id > 0) {
            $objAliases = Alias::selectByUrl($this->getId(), $intLanguageId);
            if ($objAliases->count() > 0) {
                $strReturn = $objAliases->current()->getAlias();
            } elseif ($intLanguageId > 0) {
                $objAliases = Alias::selectByUrl($this->getId(), 0);
                if ($objAliases->count() > 0) {
                    $strReturn = $objAliases->current()->getAlias();
                }
            }
        }

        return $strReturn;
    }

    public function setAlias($objAlias)
    {
        global $_CONF;

        if ($this->id > 0) {
            $objAlias->setAccountId($_CONF['app']['account']->getId());
            $objAlias->setActive(1);
            $objAlias->setUrl($this->id);
            $objAlias->save();
        }
    }

    public function duplicate($strNewName = "")
    {
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

            if (class_exists("\\AuditLog")) {
                \AuditLog::addLog(AUDIT_TYPE_ELEMENT, $this->getId(), $strName, "duplicate", $objReturn->getId());
            }
            if (class_exists("\\AuditLog")) {
                \AuditLog::addLog(AUDIT_TYPE_ELEMENT, $objReturn->getId(), $objReturn->getName(), "create");
            }

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
            $strSql = sprintf(
                "SELECT * FROM pcms_element WHERE parentId = '%s' AND accountId = '%s'",
                $this->id,
                $_CONF['app']['account']->getId()
            );
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
                $objReturn->setLanguageActive($value, true);
            }

            //*** Save schedule information.
            $objTemp = $this->getSchedule();
            $objTemp->id = 0;
            $objReturn->setSchedule($objTemp);

            return $objReturn;
        }

        return null;
    }

    public function copy($intParentId)
    {
        global $objLiveUser;

        $objDuplicate = $this->duplicate();
        $objDuplicate->setParentId($intParentId);
        $objDuplicate->setUsername($objLiveUser->getProperty("name"));
        $objDuplicate->save();

        return $objDuplicate;
    }

    public function getValueByTemplateField($intFieldId, $intLanguageId = 0, $blnRaw = false)
    {
        $strReturn = null;

        if ($this->id > 0) {
            if ($intLanguageId == 0) {
                $intLanguageId = ContentLanguage::getDefault()->getId();
            }

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

    public function getFieldByTemplateField($intFieldId)
    {
        $objReturn = null;

        if ($this->id > 0) {
            $strSql = sprintf(
                "SELECT * FROM pcms_element_field WHERE elementId = %s AND templateFieldId = %s",
                self::quote($this->id),
                self::quote($intFieldId)
            );
            $objElementFields = ElementField::select($strSql);

            if (is_object($objElementFields) && $objElementFields->count() > 0) {
                $objReturn = $objElementFields->current();
            }
        }

        return $objReturn;
    }

    public function getFeedValueByTemplateField($intFieldId, $intLanguageId = 0)
    {
        $strReturn = null;

        if ($this->id > 0) {
            if ($intLanguageId == 0) {
                $intLanguageId = ContentLanguage::getDefault()->getId();
            }

            $objField = $this->getFeedFieldByTemplateField($intFieldId, $intLanguageId);
            if (is_object($objField)) {
                $strReturn = str_replace("/", "----", $objField->getFeedPath());
            }
        }

        return $strReturn;
    }

    public function getFeedFieldByTemplateField($intFieldId, $intLanguageId = 0)
    {
        $objReturn = null;

        if ($this->id > 0) {
            if ($intLanguageId == 0) {
                $intLanguageId = ContentLanguage::getDefault()->getId();
            }

            // @FIXME Add ElementFieldFeed class
            $objReturn = ElementFieldFeed::selectByTemplateField($this->getId(), $intFieldId, $intLanguageId);
        }

        return $objReturn;
    }

    public function getField($strName)
    {
        $objReturn = null;

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

    public function getSubTemplates()
    {
        global $_CONF;
        $objReturn = null;

        if ($this->typeId == ELM_TYPE_FOLDER) {
            $objParent = Element::selectByPK($this->parentId);

            if (is_object($objParent)) {
                $objReturn = $objParent->getSubTemplates();
            } else {
                $strSql = sprintf(
                    "SELECT * FROM pcms_template WHERE parentId = '0' AND accountId = '%s'",
                    $_CONF['app']['account']->getId()
                );
                $objReturn = Template::select($strSql);
            }
        } else {
            $objTemplate = Template::selectByPK($this->templateId);

            if ($objTemplate->getIsContainer()) {
                $objReturn = $objTemplate->getSiblings(true);

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

    public function getElementsByTemplateName($strName, $blnGetOne = false)
    {
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

    public static function getElementsByTemplateId($intId, $blnGetOne = false)
    {
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

    public static function setParent()
    {
        $intElementId = Request::get('eid', 0);
        $intParentId = Request::get('parentId', -1);

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

    public static function recursivePath($intElementId)
    {
        $strReturn = "Webroot";

        $objElement = self::selectByPK($intElementId);
        if (is_object($objElement)) {
            $strReturn = "";
            if ($objElement->getParentId() > 0) {
                $strReturn .= self::recursivePath($objElement->getParentId()) . " -> ";
            }
            $strReturn .= str_replace("&", "&amp;", $objElement->getName());
        }

        return $strReturn;
    }

    public function isPage()
    {
        $blnReturn = false;

        $objTemplate = Template::selectByPK($this->getTemplateId());
        if (is_object($objTemplate)) {
            $blnReturn = $objTemplate->getIsPage();
        } else {
            $blnReturn = $this->getIsPage();
        }

        return $blnReturn;
    }

    public function getPageId()
    {
        $intReturn = 0;

        if ($this->isPage()) {
            $intReturn = $this->getId();
        } elseif ($this->getParentId() > 0) {
            $objParent = Element::selectByPk($this->getParentId());
            $intReturn = $objParent->getPageId();
        }

        return $intReturn;
    }

    public function setLanguageActive($intLanguageId, $blnActive)
    {
        if ($this->id > 0) {
            $objElementLanguage = new ElementLanguage();

            $objElementLanguage->setElementId($this->id);
            $objElementLanguage->setLanguageId($intLanguageId);
            $objElementLanguage->setActive(($blnActive) ? 1 : 0);
            $objElementLanguage->save();
        }
    }

    public function getLanguageActives()
    {
        $arrReturn = array();

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

    public function getSchedule()
    {
        $objReturn = ElementSchedule::selectByElement($this->id);

        if ($objReturn->count() == 0) {
            $objReturn = new ElementSchedule();
        } elseif ($objReturn->count() >= 1) {
            $objReturn = $objReturn->current();
        }

        return $objReturn;
    }

    public function setSchedule($objSchedule)
    {
        if ($this->id > 0) {
            $this->clearSchedule();

            $objSchedule->setElementId($this->id);
            $objSchedule->save();
        }
    }

    public function clearSchedule()
    {
        if ($this->id > 0) {
            $objSchedules = ElementSchedule::selectByElement($this->id);

            foreach ($objSchedules as $objSchedule) {
                $objSchedule->delete();
            }
        }
    }

    public function getFeed()
    {
        // @FIXME Add ElementFeed class
        $objReturn = ElementFeed::selectByElement($this->id);

        if ($objReturn->count() == 0) {
            $objReturn = new ElementFeed();
        } elseif ($objReturn->count() >= 1) {
            $objReturn = $objReturn->current();
        }

        return $objReturn;
    }

    public function setFeed($objFeed)
    {
        if ($this->id > 0) {
            $this->clearFeed();

            $objFeed->setElementId($this->id);
            $objFeed->save();
        }
    }

    public function clearFeed()
    {
        global $_CONF;

        if ($this->id > 0) {
            // @FIXME Add ElementFeed class
            $objFeeds = ElementFeed::selectByElement($this->id);

            foreach ($objFeeds as $objFeed) {
                $objFeed->delete();
            }
        }
    }

    public function clearAliases()
    {
        if ($this->id > 0) {
            $objContentLangs = ContentLanguage::select();
            foreach ($objContentLangs as $objContentLanguage) {
                $objAliases = Alias::selectByUrl($this->id, $objContentLanguage->getId());

                if (is_object($objAliases)) {
                    foreach ($objAliases as $objAlias) {
                        $objAlias->delete();
                    }
                }
            }
        }
    }

    public function clearCache($objFtp = null)
    {
        if (Setting::getValueByName('caching_enable')) {
            if (!is_object($objFtp)) {
                $objFtp = new FTP(Setting::getValueByName('ftp_server'));
                $objFtp->login(Setting::getValueByName('ftp_username'), Setting::getValueByName('ftp_password'));
                $objFtp->pasv(true);
            }
            $objFtp->delete(Setting::getValueByName('caching_ftp_folder') . "/*_{$this->id}_*");

            $objParent = Element::selectByPk($this->getParentId());
            if (is_object($objParent)) {
                $objParent->clearCache($objFtp);
            }
        }
    }

    public function clearZeroCache($objFtp = null)
    {
        if (Setting::getValueByName('caching_enable')) {
            if (!is_object($objFtp)) {
                $objFtp = new FTP(Setting::getValueByName('ftp_server'));
                $objFtp->login(Setting::getValueByName('ftp_username'), Setting::getValueByName('ftp_password'));
                $objFtp->pasv(true);
            }
            $objFtp->delete(Setting::getValueByName('caching_ftp_folder') . "/*_0_*");
        }
    }

    public function getMeta($intLanguageId = null)
    {
        $objReturn = ElementMeta::selectByElement($this->getId(), $intLanguageId);
        return $objReturn;
    }

    public function setMeta($objMeta)
    {
        if ($this->id > 0) {
            $objMeta->setElementId($this->id);
            $objMeta->save();
        }
    }

    public function clearMeta()
    {
        if ($this->id > 0) {
            ElementMeta::deleteByElement($this->id);
        }
    }

    private function createForcedElements()
    {
        global $_CONF;

        $objTemplates = $this->getSubTemplates();

        foreach ($objTemplates as $objTemplate) {
            if ($objTemplate->getForceCreation()) {
                //*** Create a child element based on this template.
                $objPermissions = new ElementPermission();
                $objPermissions->setUserId($this->getPermissions()->getUserId());
                $objPermissions->setGroupId($this->getPermissions()->getGroupId());

                $objElement = new Element();
                $objElement->setParentId($this->getId());
                $objElement->setAccountId($_CONF['app']['account']->getId());
                $objElement->setPermissions($objPermissions);

                $objElement->setActive($this->getActive());
                $objElement->setIsPage(0);
                $objElement->setName($objTemplate->getName());
                $objElement->setUsername($this->getUsername());

                $objElement->setTypeId(ELM_TYPE_ELEMENT);
                $objElement->setTemplateId($objTemplate->getId());

                $objElement->save();

                $objSchedule = new ElementSchedule();
                $objSchedule->setStartActive(0);
                $objSchedule->setStartDate(APP_DEFAULT_STARTDATE);
                $objSchedule->setEndActive(0);
                $objSchedule->setEndDate(APP_DEFAULT_ENDDATE);
                $objElement->setSchedule($objSchedule);

                $objElement->setLanguageActive(ContentLanguage::getDefault()->getId(), true);
            }
        }
    }
}
