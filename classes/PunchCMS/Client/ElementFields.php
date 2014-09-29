<?php

namespace PunchCMS\Client;

use PunchCMS\DBAL\Collection;
use PunchCMS\TemplateField;

class ElementFields extends Collection
{
    public function getFields($intElementId)
    {
        $strSql = "SELECT pcms_template_field.* FROM pcms_template_field, pcms_element
                WHERE pcms_element.id = '%s'
                AND pcms_element.templateId = pcms_template_field.templateId
                ORDER BY pcms_element.sort";
        $objTplFields = TemplateField::select(sprintf($strSql, $intElementId));

        $objReturn = new ElementFields();

        foreach ($objTplFields as $objTplField) {
            $objReturn->addObject(new ElementField($intElementId, $objTplField));
        }

        return $objReturn;
    }

    public static function getCachedFields($intElementId)
    {
        $objReturn = CachedFields::selectByElement($intElementId);

        return $objReturn;
    }
}
