<?php

namespace PunchCMS;

use PunchCMS\DBAL\Collection;

/**
 *
 * Handles TemplateField properties and methods.
 * @author felix
 * @version 0.1.0
 *
 */
class TemplateField extends \PunchCMS\DBAL\TemplateField
{
    private $objValueCollection;

    public function save($blnSaveModifiedDate = true)
    {
        self::$object = "\\PunchCMS\\TemplateField";
        self::$table = "pcms_template_field";

        $intId = $this->getId();

        $blnReturn = parent::save($blnSaveModifiedDate);
        if (class_exists("\\AuditLog")) {
            \AuditLog::addLog(AUDIT_TYPE_TEMPLATEFIELD, $this->getId(), $this->getName(), (empty($intId)) ? "create" : "edit");
        }

        return $blnReturn;
    }

    public function getValues()
    {
        if ($this->id > 0) {
            if (!is_object($this->objValueCollection)) {
                $strSql = "SELECT * FROM pcms_template_field_value WHERE fieldId = '" . $this->id . "'";
                $this->objValueCollection = TemplateFieldValue::select($strSql);
            }
        } else {
            $this->objValueCollection = new Collection();
        }

        return $this->objValueCollection;
    }

    public function getValueByName($strName)
    {
        $objReturn = null;

        if ($this->id > 0) {
            $objValues = $this->getValues();
            foreach ($objValues as $objValue) {
                if ($objValue->getName() == $strName) {
                    $objReturn = $objValue;
                    break;
                }
            }
        }

        return $objReturn;
    }

    public function clearValues()
    {
        if ($this->id > 0) {
            $objValues = $this->getValues();

            if (is_object($objValues)) {
                foreach ($objValues as $objValue) {
                    $objValue->delete();
                }
            }
        }
    }

    public function duplicate($strNewName = "")
    {
        global $objLang;

        if ($this->id > 0) {
            $strName = $this->name;

            if (!empty($strNewName)) {
                //*** Set the name of the duplicate element.
                $this->name = sprintf($objLang->get("copyOf", "label"), $strName);
            }

            $objReturn = parent::duplicate();

            if (class_exists("\\AuditLog")) {
                \AuditLog::addLog(AUDIT_TYPE_TEMPLATEFIELD, $this->getId(), $strName, "duplicate", $objReturn->getId() . ":" . $objReturn->getTemplateId());
            }

            if (class_exists("\\AuditLog")) {
                \AuditLog::addLog(AUDIT_TYPE_TEMPLATEFIELD, $objReturn->getId(), $objReturn->getName(), "create", $objReturn->getTemplateId());
            }

            $this->name = $strName;

            //*** Duplicate the values.
            $objValues = $this->getValues();
            foreach ($objValues as $objValue) {
                $objNewValue = $objValue->duplicate();
                $objNewValue->setFieldId($objReturn->getId());
                $objNewValue->save();
            }

            return $objReturn;
        }

        return null;
    }

    public static function selectByTypeId($intTemplateTypeId, $intTemplateId = null)
    {
        global $_CONF;

        if (is_null($intTemplateId)) {
            $strSql = "SELECT pcms_template_field.* FROM pcms_template_field, pcms_template
                WHERE pcms_template_field.typeId = %s
                AND pcms_template_field.templateId = pcms_template.id
                AND pcms_template.accountId = %s";
            $strSql = sprintf($strSql, self::quote($intTemplateTypeId), self::quote($_CONF['app']['account']->getId()));
        } else {
            $strSql = "SELECT pcms_template_field.* FROM pcms_template_field, pcms_template
                WHERE pcms_template_field.typeId = %s
                AND pcms_template_field.templateId = pcms_template.id
                AND pcms_template.id = %s
                AND pcms_template.accountId = %s";
            $strSql = sprintf($strSql, self::quote($intTemplateTypeId), self::quote($intTemplateId), self::quote($_CONF['app']['account']->getId()));
        }

        return self::select($strSql);
    }

    public function delete($accountId = null)
    {
        self::$object = "\\PunchCMS\\TemplateField";
        self::$table = "pcms_template_field";

        if (class_exists("\\AuditLog")) {
            \AuditLog::addLog(AUDIT_TYPE_TEMPLATEFIELD, $this->getId(), $this->getName(), "delete", $this->getTemplateId());
        }

        $objElementField = ElementField::deleteByTemplateId($this->id);
        return parent::delete($accountId);
    }
}
