<?php

namespace PunchCMS\Client;

use PunchCMS\Template;
use PunchCMS\ContentLanguage;

class Element
{
    private $objElementCollection;
    private $objFieldCollection;
    private $objElement;
    private $objMetadata;
    private $id;
    private $apiName;
    public $isPage;
    public $templateApiName;
    public $created;

    public function __construct($objElement = null)
    {
        if (is_object($objElement)) {
            $objCms = Client::getInstance();

            $this->objElement = $objElement;

            $objTemplate = Template::selectByPK($objElement->getTemplateId());
            if (is_object($objTemplate)) {
                $this->isPage = $objTemplate->getIsPage();
                $this->templateApiName = $objTemplate->getApiName();
            } else {
                $this->isPage = $objElement->getIsPage();
            }

            if ($objCms->getCacheFields()) {
                $this->objFieldCollection = ElementFields::getCachedFields($this->objElement->getId());
            }

            $this->created = $objElement->getCreated();
        }
    }

    public function getElement()
    {
        return $this->objElement;
    }

    public function getTemplateName()
    {
        return $this->templateApiName;
    }

    public function isPage()
    {
        return $this->isPage;
    }

    public function getName()
    {
        $strReturn = "";

        if (is_object($this->objElement)) {
            $strReturn = $this->objElement->getApiName();
        }

        return $strReturn;
    }

    public function getId()
    {
        $intReturn = null;

        if (is_object($this->objElement)) {
            $intReturn = $this->objElement->getId();
        }

        return $intReturn;
    }

    public function get($strName = "", $blnRecursive = false)
    {
        $objReturn = Elements::getElements($strName, $this->objElement->getId(), true, $blnRecursive);

        return $objReturn;
    }

    public function getElements($strName = "", $blnGetOne = false, $blnRecursive = false)
    {
        $objReturn = Elements::getElements($strName, $this->objElement->getId(), $blnGetOne, $blnRecursive);

        return $objReturn;
    }

    public function getElementByTemplate($strName, $blnRecursive = false, $blnRandom = false)
    {
        $objReturn = null;

        if (!empty($strName)) {
            $objReturn = Elements::getElementsByTemplate($strName, $this->objElement->getId(), true, $blnRecursive, $blnRandom);
        }

        return $objReturn;
    }

    public function getElementsByTemplate($strName, $blnGetOne = false, $blnRecursive = false, $blnRandom = false)
    {
        $objReturn = new Elements();

        if (!empty($strName)) {
            $objReturn = Elements::getElementsByTemplate($strName, $this->objElement->getId(), $blnGetOne, $blnRecursive, $blnRandom);
        }

        return $objReturn;
    }

    public function getElementsByTemplateO($strName, $strFieldName, $strOrder = "asc")
    {
        $objReturn = new Elements();

        if (!empty($strName)) {
            $objReturn = Elements::getElementsByTemplateO($strName, $this->objElement->getId(), $strFieldName, $strOrder);
        }

        return $objReturn;
    }

    public function getElementByTemplateId($intId, $blnRecursive = false, $blnRandom = false)
    {
        $objReturn = null;

        if (!empty($intId)) {
            $objReturn = Elements::getElementsByTemplateId($intId, $this->objElement->getId(), true, $blnRecursive, $blnRandom);
        }

        return $objReturn;
    }

    public function getElementsByTemplateId($intId, $blnGetOne = false, $blnRecursive = false, $blnRandom = false)
    {
        $objReturn = new Elements();

        if (!empty($intId)) {
            $objReturn = Elements::getElementsByTemplateId($intId, $this->objElement->getId(), $blnGetOne, $blnRecursive, $blnRandom);
        }

        return $objReturn;
    }

    public function getFolders($strName = "", $blnGetOne = false)
    {
        $objReturn = Elements::getFolders($strName, $this->objElement->getId(), $blnGetOne);

        return $objReturn;
    }

    public function getFields()
    {
        $objCms = Client::getInstance();

        if (!is_object($this->objFieldCollection)) {
            if ($objCms->getCacheFields()) {
                $this->objFieldCollection = ElementFields::getCachedFields($this->objElement->getId());
            } else {
                $this->objFieldCollection = ElementFields::getFields($this->objElement->getId());
            }
        }

        return $this->objFieldCollection;
    }

    public function getArray($apiNames = false, $selfLink = false, $recursive = false)
    {
        $objCms = Client::getInstance();
        $aReturn = array();

        $aReturn['template'] = $this->getTemplateName();
        $aReturn['eid'] = $this->getId();

        if ($this->getTemplateName() == 'Form') {
            // get HTML form content
            $aReturn['html'] = $objCms->buildForm($this);
        } elseif ($recursive) {
            $objChildren = $this->getElements();
            foreach ($objChildren as $objChild) {
                $aChild['template'] = $objChild->getTemplateName();
                $aChild['eid'] = $objChild->getId();
                $aChild = $objChild->getArray($apiNames, $selfLink, $recursive);
                $aReturn['children'][] = $aChild;
            }
        }

        $arrLanguages = $objCms->getLanguageArray();
        $intCurrentLanguage = $objCms->getLanguage()->getId();
        $intDefaultLanguage = $objCms->getDefaultLanguage();
        $blnCascade = false;

        $objFields = $this->getFields();
        foreach ($objFields as $objField) {
            if (($apiNames === false || $apiNames === null) || (in_array($objField->getApiName(), $apiNames) || $apiNames == $objField->getApiName())) {
                if ($objCms->getCacheFields() && count($arrLanguages) > 1) {
                    if ($objField->getLanguageId() == $intCurrentLanguage) {
                        if (!$objField->getCascade()) {
                            $aReturn[$objField->getApiName()] = $objField->getAutoValue();
                        } else {
                            $blnCascade = true;
                        }
                    } elseif ($objField->getLanguageId() == $intDefaultLanguage && $blnCascade) {
                        $aReturn[$objField->getApiName()] = $objField->getAutoValue();
                    }
                } else {
                    $aReturn[$objField->getApiName()] = $objField->getAutoValue();
                }

            }
        }

        if ($selfLink) {
            $aReturn['self'] = $this->getLink();
        }
        return $aReturn;
    }

    public function getMetadata()
    {
        $objCms = Client::getInstance();

        $objReturn = null;

        if (is_object($this->objMetadata) && is_object($this->objMetadata->current()) && $this->objMetadata->count() > 0 && $this->objMetadata->current()->getLanguageId() == $objCms->getLanguage()->getId()) {
            $objReturn = $this->objMetadata;
        } else {
            if ($this->isPage() && is_object($this->objElement)) {
                $objReturn = $this->objElement->getMeta($objCms->getLanguage()->getId());
                if (is_object($objReturn)) {
                    $this->objMetadata = $objReturn;
                }
            }
        }
        return $objReturn;
    }

    public function getPageTitle($strAlternative = "")
    {
        $strReturn = $strAlternative;

        $objMeta = $this->getMetadata();
        if (is_object($objMeta)) {
            $strValue = $objMeta->getValueByValue("name", "title");
            if (!empty($strValue)) {
                $strReturn = $strValue;
            }
        }

        return $strReturn;
    }

    public function getPageKeywords($strAlternative = "")
    {
        $strReturn = $strAlternative;

        $objMeta = $this->getMetadata();
        if (is_object($objMeta)) {
            $strValue = $objMeta->getValueByValue("name", "keywords");
            if (!empty($strValue)) {
                $strReturn = $strValue;
            }
        }

        return $strReturn;
    }

    public function getPageDescription($strAlternative = "")
    {
        $strReturn = $strAlternative;

        $objMeta = $this->getMetadata();
        if (is_object($objMeta)) {
            $strValue = $objMeta->getValueByValue("name", "description");
            if (!empty($strValue)) {
                $strReturn = $strValue;
            }
        }

        return $strReturn;
    }

    public function getPageByChild($objChild, $intPageItems, $blnChildType = true)
    {
        $intReturn = 1;

        if (!is_object($objChild)) {
            $objCms = Client::getInstance();
            $objChild = $objCms->getElementById($objChild);
        }

        if (is_object($objChild)) {
            if ($blnChildType) {
                $objElements = $this->getElementsByTemplate($objChild->getTemplateName());
            } else {
                $objElements = $this->getElements();
            }

            $objElements->setPageItems($intPageItems);
            $intReturn = $objElements->getPageByChild($objChild);
        }

        return $intReturn;
    }

    public function getField($strName)
    {
        $objCms = Client::getInstance();
        $objReturn = null;

        $arrLanguages = $objCms->getLanguageArray();
        $intCurrentLanguage = $objCms->getLanguage()->getId();
        $intDefaultLanguage = $objCms->getDefaultLanguage();
        $blnCascade = false;

        $objFields = $this->getFields();
        foreach ($objFields as $objField) {
            if ($objCms->getCacheFields() && count($arrLanguages) > 1) {
                if ($objField->getApiName() == $strName) {
                    if ($objField->getLanguageId() == $intCurrentLanguage) {
                        if (!$objField->getCascade()) {
                            $objReturn = $objField;
                            break;
                        } else {
                            $blnCascade = true;
                        }
                    } elseif ($objField->getLanguageId() == $intDefaultLanguage && $blnCascade) {
                        $objReturn = $objField;
                        break;
                    }
                }
            } else {
                if ($objField->apiName == $strName) {
                    $objReturn = $objField;
                    break;
                }
            }
        }

        if (!is_object($objReturn)) {
            $objCms = Client::getInstance();
            if ($objCms->getCacheFields()) {
                $objReturn = CachedFields::selectEmptyByElement($this->getId(), $strName);
            } else {
                $objReturn = new ElementField();
            }
        }

        return $objReturn;
    }

    public function getPageId()
    {
        $objCms = Client::getInstance();

        $intReturn = 0;

        if ($this->isPage == 1) {
            $intReturn = $this->objElement->getId();
        } elseif (is_object($this->objElement) && $this->objElement->getParentId() > 0) {
            $objParent = $objCms->getElementById($this->objElement->getParentId());

            if ($objParent->isPage == 1) {
                $intReturn = $objParent->getElement()->getId();
            } else {
                $intReturn = $objParent->getPageId();
            }
        }

        return $intReturn;
    }

    public function getPageParent()
    {
        $objCms = Client::getInstance();

        $objReturn = null;

        $objParent = $this->getParent();
        if (is_object($objParent)) {
            if ($objParent->isPage == 1) {
                $objReturn = $objParent;
            } else {
                $objReturn = $objParent->getPageParent();
            }
        }

        return $objReturn;
    }

    public function getParent()
    {
        $objCms = Client::getInstance();

        $objReturn = null;

        if (is_object($this->objElement)) {
            $objReturn = $objCms->getElementById($this->objElement->getParentId());
        }

        return $objReturn;
    }

    public function findParentByName($strName, $blnSelfInclude = true)
    {
        $objCms = Client::getInstance();

        $objReturn = null;

        if (is_object($this->objElement)) {
            if ($this->objElement->getApiName() != $strName || !$blnSelfInclude) {
                $objParent = $objCms->getElementById($this->objElement->getParentId());

                if (is_object($objParent)) {
                    $objReturn = $objParent->findParentByName($strName);
                }
            } else {
                $objReturn = $this;
            }
        }

        return $objReturn;
    }

    public function findParentByTemplateName($strName, $blnSelfInclude = true)
    {
        $objCms = Client::getInstance();

        $objReturn = null;

        if (is_object($this->objElement)) {
            if ($this->templateApiName != $strName || !$blnSelfInclude) {
                $objParent = $objCms->getElementById($this->objElement->getParentId());

                if (is_object($objParent)) {
                    $objReturn = $objParent->findParentByTemplateName($strName);
                }
            } else {
                $objReturn = $this;
            }
        }

        return $objReturn;
    }

    public function hasParentId($intParentId)
    {
        $objCms = Client::getInstance();

        $blnReturn = false;

        if (is_object($this->objElement)) {
            if ($this->objElement->getParentId() == $intParentId || $this->objElement->getId() == $intParentId) {
                $blnReturn = true;
            } else {
                if ($this->objElement->getParentId() > 0) {
                    $objParent = $objCms->getElementById($this->objElement->getParentId());
                    if (is_object($objParent)) {
                        $blnReturn = $objParent->hasParentId($intParentId);
                    }
                }
            }
        }

        return $blnReturn;
    }

    public function getLink($blnAbsolute = true, $strAddQuery = "", $strLanguageAbbr = null)
    {
        $objCms = Client::getInstance();
        $intLanguageId = null;
        if (is_null($strLanguageAbbr)) {
            $objLang = $objCms->getLanguage();
            $strLangAbbr = $objLang->getAbbr();
            $intLanguageId = $objLang->getId();
        } else {
            $objLang = ContentLanguage::selectByAbbr($strLanguageAbbr);
            if (is_object($objLang)) {
                $strLangAbbr = $objLang->getAbbr();
                $intLanguageId = $objLang->getId();
            } else {
                $strLangAbbr = "";
            }
        }

        if ($this->isPage) {
            $varReturn = $this->getId();
            if (!is_null($varReturn)) {
                $varReturn = ($blnAbsolute) ? "/" : "";
                $varReturn .= (!$objLang->default || !is_null($strLanguageAbbr)) ? "language/{$strLangAbbr}/" : "";
                $varReturn .= "eid/{$this->getId()}";
            }

            if ($objCms->usesAliases() && is_object($this->objElement)) {
                $strAlias = $this->objElement->getAlias($intLanguageId);
                if (!empty($strAlias)) {
                    $varReturn = ($blnAbsolute) ? "/" : "";
                    $varReturn .= (!$objLang->default || !is_null($strLanguageAbbr)) ? "language/{$strLangAbbr}/" : "";
                    $varReturn .= $strAlias;
                }
            }

            if (!empty($strAddQuery)) {
                $varReturn .= "?" . $strAddQuery;
            }
        } else {
            ///*** Find the closest element that represents a complete page.
            $intPageId = $this->getPageId();
            $objPageParent = $objCms->getElementById($intPageId);

            if (!is_null($intPageId) && is_object($objPageParent)) {
                $varReturn = ($blnAbsolute) ? "/" : "";
                $varReturn .= (!$objLang->default || !is_null($strLanguageAbbr)) ? "language/{$strLangAbbr}/" : "";
                $varReturn .= "eid/{$objPageParent->getId()}";
            }

            if ($objCms->usesAliases() && is_object($objPageParent->objElement)) {
                $strAlias = $objPageParent->objElement->getAlias($intLanguageId);
                if (!empty($strAlias)) {
                    $varReturn = ($blnAbsolute) ? "/" : "";
                    $varReturn .= (!$objLang->default || !is_null($strLanguageAbbr)) ? "language/{$strLangAbbr}/" : "";
                    $varReturn .= $strAlias;
                }
            }

            if (!empty($strAddQuery)) {
                $varReturn .= "?" . $strAddQuery;
            }

            $varReturn .= "#label_{$this->getId()}";
        }

        return $varReturn;
    }

    public function getPageLink($intPage, $blnAbsolute = true, $strLanguageAbbr = null)
    {
        $strLink = $this->getLink($blnAbsolute, "", $strLanguageAbbr);
        $strReturn = $strLink . "/__page/" . $intPage;

        return $strReturn;
    }

    public function prepareNewElement()
    {
        if (is_object($this->objElement)) {
            $objCms = Client::getInstance();

            return new InsertElement($this);
        }
    }

    public function getVirtual($blnSubstitute = false)
    {
        $objCms = Client::getInstance();

        $objReturn = null;

        if (is_object($this->objElement)) {
            $objField = $this->getField("VirtualLink");
            if (is_object($objField)) {
                $objTemp = $objField->getElement();
                if (is_object($objTemp)) {
                    $objReturn = $objTemp->getVirtual(true);
                }
            }
        }

        if (!is_object($objReturn) && $blnSubstitute) {
            $objReturn = $this;
        }

        return $objReturn;
    }
}
