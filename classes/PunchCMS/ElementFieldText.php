<?php

namespace PunchCMS;

/**
 *
 * Handles ElementFieldText properties and methods.
 * @author felix
 * @version 0.1.0
 *
 */
class ElementFieldText extends \PunchCMS\DBAL\ElementFieldText
{
    public static function getByFieldId($intFieldId, $intLanguageId = 0)
    {
        self::$object = "\\PunchCMS\\ElementFieldText";
        self::$table = "pcms_element_field_text";

        $objReturn = new ElementFieldText();

        if ($intFieldId > 0) {
            $strSql = sprintf(
                "SELECT * FROM " . self::$table . " WHERE fieldId = %s AND languageId = %s",
                self::quote($intFieldId),
                self::quote($intLanguageId)
            );
            $objElementValues = ElementFieldText::select($strSql);

            if (is_object($objElementValues) && $objElementValues->count() > 0) {
                $objReturn = $objElementValues->current();
            }
        }

        return $objReturn;
    }
}
