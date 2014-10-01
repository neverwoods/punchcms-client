<?php

namespace PunchCMS;

use PunchCMS\DBAL\Collection;
use Bili\Request;

/**
 *
 * Collection class for the Element objects.
 * @author felix
 * @version 0.1.0
 *
 */
class Elements extends Collection
{
    public static function getParentHTML()
    {
        global $_CONF;
        $strReturn = "";

        $intId = Request::get("eid", 0);

        if ($intId > 0) {
            $objElement = Element::selectByPK($intId);
            if ($objElement) {
                $strReturn .= self::getChildrenHTML($objElement->getParentId(), true, $intId);
            } else {
                $strReturn .= self::getChildrenHTML(0, true, $intId);
            }
        }

        return $strReturn;
    }

    public static function getChildrenHTML($intParentId = null, $blnRecursive = null, $intChildId = null)
    {
        global $_CONF;
        $strReturn = "";

        $intParentId = (is_null($intParentId)) ? Request::get("parentId", 0) : $intParentId;
        $blnRecursive = (is_null($blnRecursive)) ? Request::get("recursive", 0) : $blnRecursive;
        $intAccountId = $_CONF['app']['account']->getId();

        if ($blnRecursive && $intParentId > 0) {
            $objElement = Element::selectByPK($intParentId);
            if ($objElement) {
                $strReturn .= self::getChildrenHTML($objElement->getParentId(), $blnRecursive, $intParentId);
            }
        }

        $strSql = sprintf("SELECT * FROM pcms_element WHERE parentId = '%s' AND typeId IN (%s) AND accountId = '%s' ORDER BY sort", $intParentId, ELM_TYPE_ALL, $intAccountId);
        $objElements = Element::select($strSql);

        $strReturn .= "<field id=\"{$intParentId}\"><![CDATA[";
        foreach ($objElements as $objElement) {
            $strSelected = ($intChildId == $objElement->getId()) ? " selected=\"selected\"" : "";
            $strReturn .= "<option value=\"{$objElement->getId()}\"{$strSelected}>" . str_replace("&", "&amp;", $objElement->getName()) . "</option>\n";
        }
        $strReturn .= "]]></field>";

        return $strReturn;
    }

    public static function getFromParent($lngParentId, $blnRecursive = false, $intElementType = ELM_TYPE_ALL, $intAccountId = 0)
    {
        global $_CONF;
        $objReturn = null;

        if ($intAccountId == 0) {
            $intAccountId = $_CONF['app']['account']->getId();
        }

        $strSql = sprintf("SELECT * FROM pcms_element WHERE parentId = '%s' AND typeId IN (%s) AND accountId = '%s' ORDER BY sort", $lngParentId, $intElementType, $intAccountId);
        $objElements = Element::select($strSql);

        if ($objElements) {
            $objReturn = new Collection();
            foreach ($objElements as $objElement) {
                $objReturn->addObject($objElement);
            }
        }

        if ($blnRecursive === true && is_object($objReturn)) {
            foreach ($objReturn as $objElement) {
                $objElement->getElements(true, $intElementType, $intAccountId);
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
            //*** Find last sort position.
            if ($lastPosition > 0) {
                $objElements = Elements::getFromParent($intElementId);
                $objElements->seek($lastPosition);
                $lastSort = $objElements->current()->getSort();
            }

            //*** Loop through the items and manipulate the sort order.
            foreach ($arrItemlist as $value) {
                $lastSort++;
                $objElement = Element::selectByPK($value);
                $objElement->setSort($lastSort);
                $objElement->save(false);
            }
        }
    }
}
