<?php

namespace PunchCMS\Client;

use PunchCMS\DBAL\Collection;

class CachedFields extends Collection
{
    public static function selectByElement($intElementId)
    {
        $objCms = Client::getInstance();

        $objReturn = $objCms->getCachedFields($intElementId);
        if (!is_object($objReturn)) {
            $arrStorages = array("bigtext", "text", "date", "number");

            $strSql = "";
            $intCount = 0;
            $strLanguages = implode("','", $objCms->getLanguageArray());
            foreach ($arrStorages as $storage) {
                $strSubSql = "SELECT pcms_element.id as elementId,
                        pcms_template_field.id as templateFieldId,
                        pcms_element_field.id as elementFieldId,
                        pcms_template_field.typeId,
                        pcms_template_field.apiName,
                        pcms_template_field_type.element,
                        pcms_element_field_%s.value,
                        `pcms_element_field_%s`.`languageId`,
                        `pcms_element_field_%s`.`cascade` as `cascade`
                        FROM
                        pcms_template_field,
                        pcms_template_field_type,
                        pcms_element,
                        pcms_element_field,
                        pcms_element_field_%s
                        WHERE pcms_element.id = '%s'
                        AND pcms_element.templateId = pcms_template_field.templateId
                        AND pcms_template_field_type.id = pcms_template_field.typeId
                        AND pcms_element_field.elementId = pcms_element.id
                        AND pcms_element_field.templateFieldId = pcms_template_field.id
                        AND pcms_element_field_%s.fieldId = pcms_element_field.id
                        AND pcms_element_field_%s.languageId IN ('%s')";
                $strSql .= sprintf($strSubSql, $storage, $storage, $storage, $storage, $intElementId, $storage, $storage, $strLanguages, $storage);
                if ($intCount < count($arrStorages) - 1) {
                    $strSql .= " UNION ";
                } else {
                    $strSql .= " ORDER BY templateFieldId, `cascade` DESC";
                }

                $intCount++;
            }

            $objReturn = CachedField::select($strSql);
            $objCms->setCachedFields($intElementId, $objReturn);
        }

        return $objReturn;
    }

    public static function selectEmptyByElement($intElementId, $strApiName)
    {
        $objReturn = new CachedField();

        $strSql = "SELECT pcms_element.id,
            pcms_template_field.id as templateFieldId,
            pcms_template_field.typeId,
            pcms_template_field.apiName,
            pcms_template_field_type.element
            FROM
            pcms_template_field,
            pcms_template_field_type,
            pcms_element
            WHERE pcms_element.id = '%s'
            AND pcms_element.templateId = pcms_template_field.templateId
            AND pcms_template_field_type.id = pcms_template_field.typeId
            AND pcms_template_field.apiName = '%s'";
        $strSql = sprintf($strSql, $intElementId, $strApiName);
        $objTplFields = CachedField::select($strSql);

        if ($objTplFields->count() > 0) {
            $objReturn = $objTplFields->current();
        }

        return $objReturn;
    }
}
