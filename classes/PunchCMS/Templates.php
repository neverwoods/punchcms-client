<?php

namespace PunchCMS;

use PunchCMS\DBAL\Collection;
use Bili\Request;

/**
 * Collection class for the Template objects.
 * @author Felix Langfeldt <felix@neverwoods.com>
 *
 */
class Templates extends \PunchCMS\DBAL\Collection
{
    public static function getFromParent($lngParentId, $blnRecursive = false, $intAccountId = 0)
    {
        global $_CONF;
        $objReturn = null;

        if ($intAccountId == 0) {
            $intAccountId = $_CONF['app']['account']->getId();
        }

        $strSql = sprintf("SELECT * FROM pcms_template WHERE parentId = '%s' AND accountId = '%s' ORDER BY sort, name", $lngParentId, $intAccountId);
        $objTemplates = Template::select($strSql);

        if ($objTemplates) {
            $objReturn = new Collection();
            foreach ($objTemplates as $objTemplate) {
                $objReturn->addObject($objTemplate);
            }
        }

        if ($blnRecursive === true && is_object($objReturn)) {
            foreach ($objReturn as $objTemplate) {
                $objTemplate->getTemplates(true);
            }
        }

        return $objReturn;
    }

    public static function sortChildren($intElementId)
    {
        $lastSort = 0;
        $arrItemlist = Request::get("itemlist");
        $lastPosition = Request::get("pos", 0);

        if (is_array($arrItemlist) && count($arrItemlist) > 0) {
            $objTemplate = Template::selectByPK($intElementId);

            //*** Find last sort position.
            if ($lastPosition > 0) {
                $objFields = $objTemplate->getFields();
                $objFields->seek($lastPosition);
                $lastSort = $objFields->current()->getSort();
            }

            //*** Loop through the items and manipulate the sort order.
            foreach ($arrItemlist as $value) {
                $lastSort++;
                $objField = TemplateField::selectByPK($value);
                $objField->setSort($lastSort);
                $objField->save(false);
            }
        }
    }
}
