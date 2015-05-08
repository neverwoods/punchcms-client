<?php

namespace PunchCMS;

use PunchCMS\DBAL\Collection;
use Bili\Request;

/**
 *
 * Handles Template properties and methods.
 * @author felix
 * @version 0.2.0
 *
 * CHANGELOG
 * version 0.2.0, 20 Nov 2007
 *   NEW: Added selectByName and getFieldByName methods.
 * version 0.1.0, 04 Apr 2006
 *   NEW: Created class.
 *
 */
class Template extends \PunchCMS\DBAL\Template
{
    private $objTemplateCollection;

    public static function selectByPK($varValue, $arrFields = array(), $accountId = null)
    {
        global $_CONF;
        parent::$object = "\\PunchCMS\\Template";
        parent::$table = "pcms_template";

        return parent::selectByPK($varValue, $arrFields, $_CONF['app']['account']->getId());
    }

    public static function selectByName($varValue)
    {
        global $_CONF;

        $strSql = sprintf("SELECT * FROM pcms_template WHERE apiName = '%s' AND accountId = '%s'", $varValue, $_CONF['app']['account']->getId());
        $objTemplates = Template::select($strSql);

        if ($objTemplates->count() > 0) {
            return $objTemplates->current();
        }
    }

    public function delete($accountId = null)
    {
        global $_CONF;
        parent::$object = "\\PunchCMS\\Template";
        parent::$table = "pcms_template";

        //*** Delete elements.
        $objElements = Element::getElementsByTemplateId($this->id);
        foreach ($objElements as $objElement) {
            $objElement->delete();
        }

        if (class_exists("\\AuditLog")) {
            \AuditLog::addLog(AUDIT_TYPE_TEMPLATE, $this->getId(), $this->getName(), "delete");
        }

        return parent::delete($_CONF['app']['account']->getId());
    }

    public function save($blnSaveModifiedDate = true)
    {
        parent::$object = "Template";
        parent::$table = "pcms_template";

        $intId = $this->getId();

        $blnReturn = parent::save($blnSaveModifiedDate);
        if (class_exists("\\AuditLog")) {
            \AuditLog::addLog(AUDIT_TYPE_TEMPLATE, $this->getId(), $this->getName(), (empty($intId)) ? "create" : "edit");
        }

        return $blnReturn;
    }

    public function duplicate($strNewName = "")
    {
        global $objLang,
                $_CONF,
                $objLiveUser;

        if ($this->id > 0) {
            //*** Cache the name of the current template.
            $strName = $this->name;

            if (!empty($strNewName)) {
                //*** Set the name of the duplicate template.
                $this->name = sprintf($strNewName, $strName);
            }

            //*** Duplicate the template.
            $objReturn = parent::duplicate();

            if (class_exists("\\AuditLog")) {
                \AuditLog::addLog(AUDIT_TYPE_TEMPLATE, $this->getId(), $strName, "duplicate", $objReturn->getId());
            }
            if (class_exists("\\AuditLog")) {
                \AuditLog::addLog(AUDIT_TYPE_TEMPLATE, $objReturn->getId(), $objReturn->getName(), "create");
            }

            //*** Reset the name of the current template.
            $this->name = $strName;

            //*** Duplicate the fields of the current template.
            $objFields = $this->getFields();
            foreach ($objFields as $objField) {
                $objNewField = $objField->duplicate();
                $objNewField->setTemplateId($objReturn->getId());
                $objNewField->setUsername($objLiveUser->getProperty("name"));
                $objNewField->save();
            }

            //*** Copy any child templates to the duplicate.
            $strSql = sprintf("SELECT * FROM pcms_template WHERE parentId = '%s' AND accountId = '%s'", $this->id, $_CONF['app']['account']->getId());
            $objTemplates = Template::select($strSql);

            foreach ($objTemplates as $objTemplate) {
                $objTemplate->copy($objReturn->getId());
            }

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

    public function getTemplates($blnRecursive = false)
    {
        if ($this->id > 0) {
            if (!is_object($this->objTemplateCollection)) {
                $this->objTemplateCollection = Templates::getFromParent($this->id, $blnRecursive);
            }
        } else {
            $this->objTemplateCollection = new Collection();
        }

        return $this->objTemplateCollection;
    }

    public function getSiblings($blnRecursive = false)
    {
        global $_CONF;

        $objReturn = null;

        $strSql = sprintf("SELECT * FROM pcms_template WHERE parentId = '%s' AND accountId = '%s'", $this->getParentId(), $_CONF['app']['account']->getId());
        $objReturn = Template::select($strSql);

        if ($blnRecursive && $this->getParentId() > 0) {
            $objParent = Template::selectByPk($this->getParentId());
            $objParents = $objParent->getSiblings($blnRecursive);

            foreach ($objParents as $objParent) {
                $objReturn->addObject($objParent);
            }
        }

        return $objReturn;
    }

    public function getFields()
    {
        $strSql = "SELECT * FROM pcms_template_field WHERE templateId = '{$this->id}' ORDER BY sort";
        $objReturn = TemplateField::select($strSql);

        return $objReturn;
    }

    public function getFieldByName($varValue)
    {
        $strSql = sprintf("SELECT * FROM pcms_template_field WHERE templateId = '%s' AND apiName = '%s' ORDER BY sort", $this->id, $varValue);
        $objFields = TemplateField::select($strSql);

        if ($objFields->count() > 0) {
            return $objFields->current();
        }
    }

    public static function setParent()
    {
        $intTemplateId = Request::get('eid', 0);
        $intParentId = Request::get('parentId', -1);

        $strReturn = "<fields>";
        $strReturn .= "<field name=\"templateId\">";

        if ($intTemplateId > 0 && $intParentId > -1) {
            $objTemplate = Template::selectByPK($intTemplateId);

            if (is_object($objTemplate)) {
                $objTemplate->setParentId($intParentId);
                $objTemplate->save();
            }

            $strReturn .= "<value>$intTemplateId</value>";
        } else {
            $strReturn .= "<value>-1</value>";
        }

        $strReturn .= "</field>";
        $strReturn .= "</fields>";

        return $strReturn;
    }
}
