<?php

namespace PunchCMS;

/**
 *
 * Handles ElementMeta properties and methods.
 * @author felix
 * @version 0.1.0
 *
 */
class ElementMeta extends \PunchCMS\DBAL\ElementMeta
{
    private $languageId = 0;

    public static function selectByElement($intElementId, $intLanguageId = null)
    {
        if (is_null($intLanguageId)) {
            $intLanguageId = ContentLanguage::getDefault()->getId();
        }

        $strSql = "SELECT * FROM pcms_element_meta WHERE elementId = '%s' AND languageId = '%s'";
        $objReturn = parent::select(sprintf($strSql, $intElementId, $intLanguageId));

        return $objReturn;
    }

    public static function deleteByElement($intElementId)
    {
        $strSql = "DELETE FROM pcms_element_meta WHERE elementId = '%s'";
        parent::select(sprintf($strSql, $intElementId));
    }

    public static function getCascades($intElementId, $strField)
    {
        $arrReturn = array();

        $objContentLangs = ContentLanguage::select();
        foreach ($objContentLangs as $objContentLanguage) {
            $objMeta = self::selectByElement($intElementId, $objContentLanguage->getId());
            if (is_object($objMeta)) {
                $strValue = $objMeta->getValueByValue("name", $strField, "cascade");
                if ($strValue == 1) {
                    array_push($arrReturn, $objContentLanguage->getId());
                }
            }
        }

        return $arrReturn;
    }
}
