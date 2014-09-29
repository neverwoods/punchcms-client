<?php

namespace PunchCMS;

/**
 *
 * Handles alias properties and methods.
 * @author felix
 * @version 0.1.1
 *
 */
class Alias extends \PunchCMS\DBAL\Alias
{
    public function save($blnSaveModifiedDate = true)
    {
        parent::$object = "\\PunchCMS\\Alias";
        parent::$table = "pcms_alias";

        $intId = $this->getId();

        //*** Remove empty aliasses if this one is not empty.
        $this->clearByLanguage();

        $blnReturn = parent::save($blnSaveModifiedDate);
        if (class_exists("\\AuditLog")) {
            \AuditLog::addLog(AUDIT_TYPE_ALIAS, $this->getId(), $this->getAlias(), (empty($intId)) ? "create" : "edit", ($this->getActive()) ? "active" : "inactive");
        }

        return $blnReturn;
    }

    public function delete($accountId = null)
    {
        parent::$object = "\\PunchCMS\\Alias";
        parent::$table = "pcms_alias";

        if (class_exists("\\AuditLog")) {
            \AuditLog::addLog(AUDIT_TYPE_ALIAS, $this->getId(), $this->getAlias(), "delete");
        }

        return parent::delete($accountId);
    }

    private function clearByLanguage()
    {
        parent::$object = "\\PunchCMS\\Alias";
        parent::$table = "pcms_alias";

        if (!empty($this->alias) && !empty($this->languageId)) {
            $strSql = sprintf("DELETE FROM " . parent::$table . " WHERE accountId = '%s' AND languageId = '%s' AND url = %s AND alias = '' ORDER BY sort", $_CONF['app']['account']->getId(), $this->getLanguageId(), parent::quote($this->getUrl()));

            return parent::select($strSql);
        }
    }

    public static function select($strSql = "")
    {
        global $_CONF;
        parent::$object = "\\PunchCMS\\Alias";
        parent::$table = "pcms_alias";

        if (empty($strSql)) {
            $strSql = sprintf("SELECT * FROM " . parent::$table . " WHERE accountId = '%s' ORDER BY sort", $_CONF['app']['account']->getId());
        }

        return parent::select($strSql);
    }

    public static function selectSorted()
    {
        global $_CONF;
        parent::$object = "\\PunchCMS\\Alias";
        parent::$table = "pcms_alias";

        $strSql = sprintf("SELECT * FROM " . parent::$table . " WHERE accountId = '%s' ORDER BY alias", $_CONF['app']['account']->getId());

        return parent::select($strSql);
    }

    public static function selectByUrl($strUrl, $intLanguageId = null)
    {
        global $_CONF;

        if (is_null($intLanguageId)) {
            $intLanguageId = ContentLanguage::getDefault()->getId();
        }

        parent::$object = "\\PunchCMS\\Alias";
        parent::$table = "pcms_alias";
        $objReturn = null;

        if (!empty($strUrl)) {
            $strSql = sprintf("SELECT * FROM " . parent::$table . " WHERE accountId = '%s' AND url = %s AND languageId = %s ORDER BY sort", $_CONF['app']['account']->getId(), parent::quote($strUrl), parent::quote($intLanguageId));
        }

        $objReturn = parent::select($strSql);

        return $objReturn;
    }

    public static function selectByAlias($strAlias)
    {
        global $_CONF;

        parent::$object = "\\PunchCMS\\Alias";
        parent::$table = "pcms_alias";
        $objReturn = null;

        if (!empty($strAlias)) {
            $strSql = sprintf("SELECT * FROM " . parent::$table . " WHERE accountId = '%s' AND alias = %s ORDER BY sort", $_CONF['app']['account']->getId(), parent::quote($strAlias));
            $objReturn = parent::select($strSql);
        }

        return $objReturn;
    }

    public static function getCascades($intElementId)
    {
        $arrReturn = array();

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
