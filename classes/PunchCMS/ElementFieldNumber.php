<?php

namespace PunchCMS;

/**
 *
 * Handles ElementFieldNumber properties and methods.
 * @author felix
 * @version 0.1.0
 *
 */
class ElementFieldNumber extends \PunchCMS\DBAL\ElementFieldNumber
{
    public static function getByFieldId($intFieldId, $intLanguageId = 0)
    {
        self::$object = "\\PunchCMS\\ElementFieldNumber";
        self::$table = "pcms_element_field_number";

        $objReturn = new ElementFieldNumber();

        if ($intFieldId > 0) {
            $strSql = sprintf(
                "SELECT * FROM " . self::$table . " WHERE fieldId = %s AND languageId = %s",
                self::quote($intFieldId),
                self::quote($intLanguageId)
            );
            $objElementValues = ElementFieldNumber::select($strSql);

            if (is_object($objElementValues) && $objElementValues->count() > 0) {
                $objReturn = $objElementValues->current();
            }
        }

        return $objReturn;
    }
}
