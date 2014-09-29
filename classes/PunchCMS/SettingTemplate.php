<?php

namespace PunchCMS;

/**
 *
 * Holds a record from the setting template table.
 * @author felix
 * @version 0.1.0
 *
 */
class SettingTemplate extends \PunchCMS\DBAL\SettingTemplate
{
    public static function selectByName($strName)
    {
        global $_CONF;

        self::$object = "\\PunchCMS\\SettingTemplate";
        self::$table = "pcms_setting_tpl";

        $strSql = sprintf("SELECT * FROM " . self::$table . " WHERE name = %s ORDER BY section, sort", self::quote($strName));
        $objSettings = self::select($strSql);

        if ($objSettings->count() > 0) {
            return $objSettings->current();
        }
    }

    public static function getValueByName($strName)
    {
        $strValue = "";
        $objSetting = self::selectByName($strName);

        if (is_object($objSetting)) {
            $strValue = $objSetting->getValue();
        }

        return $strValue;
    }
}
