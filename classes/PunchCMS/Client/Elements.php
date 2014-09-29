<?php

namespace PunchCMS\Client;

use PunchCMS\DBAL\Collection;
use PunchCMS\Client\Element as ClientElement;
use PunchCMS\Client\Elements as ClientElements;
use PunchCMS\Element;
use PunchCMS\Template;

class Elements extends Collection
{
    public static function getElements($varName, $intParentId, $blnGetOne = false, $blnRecursive = false)
    {
        $objCms = Client::getInstance();

        if (!is_array($varName)) {
            if (empty($varName)) {
                $varName = array();
            } else {
                $varName = explode(",", $varName);
            }
        }

        $strSql = "SELECT pcms_element.* FROM pcms_element, pcms_element_schedule
                        WHERE pcms_element.parentId = '%s'
                        AND pcms_element.active = '1' ";
        $strSql .= (count($varName) > 1 || (count($varName) > 0 && !empty($varName[0]))) ? "AND pcms_element.apiName IN ('%s') " : "";
        $strSql .= "AND pcms_element.accountId = '%s'
                        AND pcms_element.id IN (SELECT elementId FROM pcms_element_language
                            WHERE languageId = '%s'
                            AND active = '1')
                        AND pcms_element.id = pcms_element_schedule.elementId
                        AND pcms_element_schedule.startDate <= '%s'
                        AND pcms_element_schedule.endDate >= '%s'
                        ORDER BY pcms_element.sort";
        if (count($varName) > 1 || (count($varName) > 0 && !empty($varName[0]))) {
            $strSql = sprintf($strSql, $intParentId, implode("','", $varName), Client::getAccount()->getId(), $objCms->getLanguage()->getId(), self::toMysql(), self::toMysql());
        } else {
            $strSql = sprintf($strSql, $intParentId, Client::getAccount()->getId(), $objCms->getLanguage()->getId(), self::toMysql(), self::toMysql());
        }
        $objElements = Element::select($strSql);

        if ($blnGetOne) {
            if ($objElements->count() > 0) {
                $objElement = new ClientElement($objElements->current());
                return $objElement;
            } else {
                $objReturn = null;
            }
        } else {
            $objReturn = new ClientElements();
        }

        foreach ($objElements as $objElement) {
            $objReturn->addObject(new ClientElement($objElement));

            if ($blnRecursive === true) {
                $objChilds = ClientElements::getElements($varName, $objElement->getId(), $blnGetOne, $blnRecursive);

                if ($blnGetOne) {
                    if (is_object($objChilds->getElement())) {
                        return $objChilds;
                    }
                } else {
                    foreach ($objChilds as $objChild) {
                        $objReturn->addObject($objChild);
                    }
                }
            }
        }

        //*** Find elements recursivly that are no direct child elements.
        if ($blnRecursive === true) {
            $strSql = "SELECT pcms_element.* FROM pcms_element, pcms_element_schedule
                    WHERE pcms_element.parentId = '%s'
                    AND pcms_element.active = '1'
                    AND pcms_element.apiName NOT IN ('%s')
                    AND pcms_element.accountId = '%s'
                    AND pcms_element.id = pcms_element_schedule.elementId
                    AND pcms_element_schedule.startDate <= '%s'
                    AND pcms_element_schedule.endDate >= '%s'
                    ORDER BY pcms_element.sort";
            $objElements = Element::select(sprintf($strSql, $intParentId, implode("','", $varName), Client::getAccount()->getId(), self::toMysql(), self::toMysql()));

            foreach ($objElements as $objElement) {
                $objChilds = ClientElements::getElements($varName, $objElement->getId(), $blnGetOne, $blnRecursive);

                if (is_object($objChilds)) {
                    if ($blnGetOne) {
                        return $objChilds;
                    } else {
                        foreach ($objChilds as $objChild) {
                            $objReturn->addObject($objChild);
                        }
                    }
                }
            }
        }

        return $objReturn;
    }

    public static function getElementsByTemplate(
        $varName,
        $intParentId,
        $blnGetOne = false,
        $blnRecursive = false,
        $blnRandom = false
    ) {
        $objCms = Client::getInstance();

        if (!is_array($varName)) {
            if (empty($varName)) {
                $varName = array();
            } else {
                $varName = explode(",", $varName);
            }
        }

        if ($blnRecursive === true) {
            $strSql = "SELECT pcms_element.* FROM pcms_element, pcms_element_schedule
                    WHERE pcms_element.parentId = '%s'
                    AND pcms_element.active = '1'
                    AND pcms_element.accountId = '%s'
                    AND pcms_element.id IN (SELECT elementId FROM pcms_element_language
                        WHERE languageId = '%s'
                        AND active = '1')
                    AND pcms_element.id = pcms_element_schedule.elementId
                    AND pcms_element_schedule.startDate <= '%s'
                    AND pcms_element_schedule.endDate >= '%s'
                    ORDER BY pcms_element.sort";

            $objElements = Element::select(
                sprintf(
                    $strSql,
                    $intParentId,
                    Client::getAccount()->getId(),
                    $objCms->getLanguage()->getId(),
                    self::toMysql(),
                    self::toMysql()
                )
            );

            $objReturn = new ClientElements();

            foreach ($objElements as $objElement) {
                $objTemplate = Template::selectByPK($objElement->getTemplateId());

                if (is_object($objTemplate) && in_array($objTemplate->getApiName(), $varName)) {
                    $objReturn->addObject(new ClientElement($objElement));
                }

                if ($blnGetOne && !$blnRandom && $objReturn->count() > 0) {
                    return $objReturn->current();
                }

                $objChilds = ClientElements::getElementsByTemplate($varName, $objElement->getId(), $blnGetOne, true);

                foreach ($objChilds as $objChild) {
                    $objReturn->addObject($objChild);
                }
            }

            if ($blnGetOne && $blnRandom) {
                return $objReturn->random();
            }

            if ($blnRandom) {
                $objReturn->randomize();
            }
        } else {
            $strSql = "SELECT pcms_element.* FROM pcms_element, pcms_template, pcms_element_schedule
                    WHERE pcms_element.parentId = '%s'
                    AND pcms_element.active = '1'
                    AND pcms_element.templateId = pcms_template.id
                    AND pcms_template.apiName IN ('%s')
                    AND pcms_element.accountId = '%s'
                    AND pcms_element.id IN (SELECT elementId FROM pcms_element_language
                        WHERE languageId = '%s'
                        AND active = '1')
                    AND pcms_element.id = pcms_element_schedule.elementId
                    AND pcms_element_schedule.startDate <= '%s'
                    AND pcms_element_schedule.endDate >= '%s'
                    ORDER BY pcms_element.sort";
            $objElements = Element::select(sprintf($strSql, $intParentId, implode("','", $varName), Client::getAccount()->getId(), $objCms->getLanguage()->getId(), self::toMysql(), self::toMysql()));

            if ($blnGetOne && !$blnRandom) {
                if ($objElements->count() > 0) {
                    $objReturn = new ClientElement($objElements->current());
                } else {
                    $objReturn = null;
                }
                return $objReturn;
            }

            $objReturn = new ClientElements();

            foreach ($objElements as $objElement) {
                $objReturn->addObject(new ClientElement($objElement));
            }

            if ($blnGetOne && $blnRandom) {
                return $objReturn->random();
            }

            if ($blnRandom) {
                $objReturn->randomize();
            }
        }


        return $objReturn;
    }

    public static function getElementsByTemplateO($varName, $intParentId, $strFieldName, $strOrder = "asc")
    {
        $objCms = Client::getInstance();

        if (!is_array($varName)) {
            if (empty($varName)) {
                $varName = array();
            } else {
                $varName = explode(",", $varName);
            }
        }

        //*** Find the type of the order field.
        $strType = "date";

        //*** Get the elements.
        $strSql = "SELECT pcms_element.* FROM pcms_element, pcms_template, pcms_element_schedule, pcms_element_field, pcms_element_field_date, pcms_template_field
                WHERE pcms_element.parentId = '%s'
                AND pcms_element.active = '1'
                AND pcms_element.templateId = pcms_template.id
                AND pcms_template.apiName IN ('%s')
                AND pcms_element.accountId = '%s'
                AND pcms_element.id IN (SELECT elementId FROM pcms_element_language
                    WHERE languageId = '%s'
                    AND active = '1')
                AND pcms_element.id = pcms_element_schedule.elementId
                AND pcms_element_schedule.startDate <= '%s'
                AND pcms_element_schedule.endDate >= '%s'
                AND pcms_element_field_%s.fieldId = pcms_element_field.id
                AND pcms_element_field.elementId = pcms_element.id
                AND pcms_element_field.templateFieldId = pcms_template_field.id
                AND pcms_template_field.apiName = '%s'
                ORDER BY pcms_element_field_date.value %s";
        $strSql = sprintf($strSql, $intParentId, implode("','", $varName), Client::getAccount()->getId(), $objCms->getLanguage()->getId(), self::toMysql(), self::toMysql(), $strType, $strFieldName, $strOrder);
        $objElements = Element::select($strSql);

        $objReturn = new ClientElements();

        foreach ($objElements as $objElement) {
            $objReturn->addObject(new ClientElement($objElement));
        }

        return $objReturn;
    }

    public static function getElementsByTemplateId($intId, $intParentId, $blnGetOne = false, $blnRecursive = false, $blnRandom = false)
    {
        $objCms = Client::getInstance();

        if ($blnRecursive === true) {
            $strSql = "SELECT pcms_element.* FROM pcms_element, pcms_element_schedule
                    WHERE pcms_element.parentId = '%s'
                    AND pcms_element.active = '1'
                    AND pcms_element.accountId = '%s'
                    AND pcms_element.id IN (SELECT elementId FROM pcms_element_language
                        WHERE languageId = '%s'
                        AND active = '1')
                    AND pcms_element.id = pcms_element_schedule.elementId
                    AND pcms_element_schedule.startDate <= '%s'
                    AND pcms_element_schedule.endDate >= '%s'
                    ORDER BY pcms_element.sort";
            $objElements = Element::select(sprintf($strSql, $intParentId, Client::getAccount()->getId(), $objCms->getLanguage()->getId(), self::toMysql(), self::toMysql()));

            $objReturn = new ClientElements();

            foreach ($objElements as $objElement) {
                $objTemplate = Template::selectByPK($objElement->getTemplateId());

                if ($objElement->getTemplateId() == $intId) {
                    $objReturn->addObject(new ClientElement($objElement));
                }

                if ($blnGetOne && !$blnRandom && $objReturn->count() > 0) {
                    return $objReturn->current();
                }

                $objChilds = ClientElements::getElementsByTemplateId($intId, $objElement->getId(), $blnGetOne, true, $blnRandom);

                foreach ($objChilds as $objChild) {
                    $objReturn->addObject($objChild);
                }
            }

            if ($blnGetOne && $blnRandom) {
                return $objReturn->random();
            }

            if ($blnRandom) {
                $objReturn->randomize();
            }
        } else {
            $strSql = "SELECT pcms_element.* FROM pcms_element, pcms_element_schedule
                    WHERE pcms_element.parentId = '%s'
                    AND pcms_element.active = '1'
                    AND pcms_element.templateId = '%s'
                    AND pcms_element.accountId = '%s'
                    AND pcms_element.id IN (SELECT elementId FROM pcms_element_language
                        WHERE languageId = '%s'
                        AND active = '1')
                    AND pcms_element.id = pcms_element_schedule.elementId
                    AND pcms_element_schedule.startDate <= '%s'
                    AND pcms_element_schedule.endDate >= '%s'
                    ORDER BY pcms_element.sort";
            $objElements = Element::select(sprintf($strSql, $intParentId, $intId, Client::getAccount()->getId(), $objCms->getLanguage()->getId(), self::toMysql(), self::toMysql()));

            if ($blnGetOne && !$blnRandom) {
                if ($objElements->count() > 0) {
                    $objReturn = new ClientElement($objElements->current());
                } else {
                    $objReturn = null;
                }
                return $objReturn;
            }

            $objReturn = new ClientElements();

            foreach ($objElements as $objElement) {
                $objReturn->addObject(new ClientElement($objElement));
            }

            if ($blnGetOne && $blnRandom) {
                return $objReturn->random();
            }

            if ($blnRandom) {
                $objReturn->randomize();
            }
        }

        return $objReturn;
    }

    public static function getFolders($strName = "", $intParentId = 0, $blnGetOne = false)
    {
        $objCms = Client::getInstance();

        $strSql = "SELECT pcms_element.* FROM pcms_element, pcms_element_schedule
                WHERE pcms_element.parentId = '%s'
                AND pcms_element.active = '1'
                AND pcms_element.typeId = '%s' ";
        $strSql .= (empty($strName)) ? "" : "AND pcms_element.apiName = '%s' ";
        $strSql .= "AND pcms_element.accountId = '%s'
                AND pcms_element.id IN (SELECT elementId FROM pcms_element_language
                    WHERE languageId = '%s'
                    AND active = '1')
                AND pcms_element.id = pcms_element_schedule.elementId
                AND pcms_element_schedule.startDate <= '%s'
                AND pcms_element_schedule.endDate >= '%s'
                ORDER BY pcms_element.sort";
        if (empty($strName)) {
            $objElements = Element::select(sprintf($strSql, $intParentId, ELM_TYPE_FOLDER, Client::getAccount()->getId(), $objCms->getLanguage()->getId(), self::toMysql(), self::toMysql()));
        } else {
            $objElements = Element::select(sprintf($strSql, $intParentId, ELM_TYPE_FOLDER, $strName, Client::getAccount()->getId(), $objCms->getLanguage()->getId(), self::toMysql(), self::toMysql()));
        }

        if ($blnGetOne) {
            if ($objElements->count() > 0) {
                $objElement = new ClientElement($objElements->current());
                return $objElement;
            } else {
                $objReturn = new ClientElement();
            }
        } else {
            $objReturn = new ClientElements();
        }

        foreach ($objElements as $objElement) {
            $objReturn->addObject(new ClientElement($objElement));
        }

        return $objReturn;
    }

    public static function toMysql()
    {
        $strReturn = "0000-00-00 00:00:00";
        $strFormat = "%Y-%m-%d %H:%M:%S";

        $strTStamp = strtotime("now");

        if ($strTStamp !== -1 || $strTStamp !== false) {
            $strReturn = strftime($strFormat, $strTStamp);
        }

        return $strReturn;
    }

    public function orderByField($strFieldName, $strOrder = "asc")
    {
        //*** Order the collection on a given field name [asc]ending or [desc]ending.

        for ($i = 0; $i < count($this->collection); $i++) {
            for ($j = 0; $j < count($this->collection) - $i - 1; $j++) {
                $objLeft = $this->collection[$j + 1]->getField($strFieldName);
                $objRight = $this->collection[$j]->getField($strFieldName);

                if (!is_object($objLeft)) {
                    $objLeft = $this->collection[$j + 1]->getVirtual();
                    if (is_object($objLeft)) {
                        $objLeft = $objLeft->getField($strFieldName);
                    }
                }

                if (!is_object($objRight)) {
                    $objRight = $this->collection[$j]->getVirtual();
                    if (is_object($objRight)) {
                        $objRight = $objRight->getField($strFieldName);
                    }
                }

                switch ($objLeft->typeid) {
                    case FIELD_TYPE_DATE:
                        $left = strtotime($objLeft->getValue());
                        $right = strtotime($objRight->getValue());
                        break;
                    default:
                        $left = $objLeft->getValue();
                        $right = $objRight->getValue();
                }

                if ($strOrder == "asc") {
                    if ($left < $right) {
                        $objTemp = $this->collection[$j];
                        $this->collection[$j] = $this->collection[$j + 1];
                        $this->collection[$j + 1] = $objTemp;
                    }
                } else {
                    if ($left > $right) {
                        $objTemp = $this->collection[$j];
                        $this->collection[$j] = $this->collection[$j + 1];
                        $this->collection[$j + 1] = $objTemp;
                    }
                }
            }
        }
    }

    public function normalize()
    {
        $arrHash = array();
        $tempCollection = array();

        foreach ($this->collection as $object) {
            if (!in_array($object->getId(), $arrHash)) {
                array_push($tempCollection, $object);
                array_push($arrHash, $object->getId());
            }
        }

        $this->collection = $tempCollection;
    }

    public function getArray($apiNames = false, $linkSelf = false, $recursive = false)
    {
        $aReturn = array();
        foreach ($this as $objElement) {
            $aReturn[] = $objElement->getArray($apiNames, $linkSelf, $recursive);
        }
        return $aReturn;
    }
}
