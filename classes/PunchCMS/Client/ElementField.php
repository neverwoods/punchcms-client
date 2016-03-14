<?php

namespace PunchCMS\Client;

use PunchCMS\DBAL\Collection;
use PunchCMS\ContentLanguage;
use PunchCMS\TemplateField;
use PunchCMS\ImageField;
use PunchCMS\ImageValue;

class ElementField extends \PunchCMS\DBAL\ElementField
{
    private $objField;
    public $name;
    public $apiName;
    public $type;
    public $id;
    public $templateFieldId;

    public function __construct($intElementId, $objTplField = null)
    {
        if (is_object($objTplField)) {
            $this->name = $objTplField->getName();
            $this->apiName = $objTplField->getApiName();
            $this->type = $objTplField->getTypeId();
        }

        $strSql = "SELECT * FROM pcms_element_field WHERE elementId = '%s' AND templateFieldId = '%s' ORDER BY sort";
        $objFields = ElementField::select(sprintf($strSql, $intElementId, $objTplField->getId()));

        if (is_object($objFields) && $objFields->count() > 0) {
            $this->objField = $objFields->current();
            $this->templateFieldId = $this->objField->getTemplateFieldId();
            $this->id = $this->objField->getId();
        }
    }

    public function getField()
    {
        return $this->objField;
    }

    public function getApiName()
    {
        return $this->apiName;
    }

    public function getTemplateFieldId()
    {
        return $this->templateFieldId;
    }

    public function getTypeId()
    {
        return $this->type;
    }

    public function getRange()
    {
        /* This method returns the possible values for list type fields. */
        $arrReturn = array();

        if ($this->templateFieldId > 0) {
            $objTemplateField = TemplateField::selectByPK($this->templateFieldId);

            if (is_object($objTemplateField)) {
                $strRange = $objTemplateField->getValueByName("tfv_multilist_value")->getValue();
                if (empty($strRange)) {
                    $strRange = $objTemplateField->getValueByName("tfv_list_value")->getValue();
                }

                $arrValues = explode("\n", $strRange);
                foreach ($arrValues as $value) {
                    if (!empty($value)) {
                        //*** Determine if we have a label.
                        $arrValue = explode(":", $value);
                        if (count($arrValue) > 1) {
                            $optionLabel = trim($arrValue[0]);
                            $optionValue = trim($arrValue[1]);
                        } else {
                            $optionLabel = trim($value);
                            $optionValue = trim($value);
                        }

                        $arrTemp = array("label" => $optionLabel, "value" => $optionValue);
                        array_push($arrReturn, $arrTemp);
                    }
                }
            }
        }

        return $arrReturn;
    }

    public function getValue($varFilter = null, $varOptions = null)
    {
        $varReturn = "";
        $objCms = Client::getInstance();

        if (is_object($this->objField)) {
            $objValue = $this->objField->getValueObject($objCms->getLanguage()->getId());
            if (is_object($objValue) && $objValue->getCascade()) {
                $varReturn = $this->objField->getValue(ContentLanguage::getDefault()->getId());
            } else {
                $varReturn = $this->objField->getValue($objCms->getLanguage()->getId());
            }
        } else {
            switch ($this->type) {
                case FIELD_TYPE_IMAGE:
                case FIELD_TYPE_FILE:
                    $varReturn = array();
                    break;
            }
        }

        //*** Filters.
        if (!is_null($varFilter)) {
            if (!is_array($varFilter)) {
                $varFilter = array($varFilter);
            }

            foreach ($varFilter as $filter) {
                switch ($filter) {
                    case VALUE_HTML:
                        //*** Replace & characters with &amp;.
                        self::filterAddAmpersand($varReturn);

                        //*** Replace $ characters with &#36;.
                        $varReturn = str_replace("$", "&#36;", $varReturn);

                        //*** Replace BAD link targets with GOOD rels.
                        self::filterFixXtmlLinkTarget($varReturn);

                        //*** Apply field type specific conversions
                        if ($objCms->usesAliases()) {
                            self::filterUseAliases($this, $varReturn);
                        }
                        switch ($this->type) {
                            case FIELD_TYPE_SIMPLETEXT:
                                $varReturn = nl2br($varReturn);
                                break;
                        }

                        //*** Apply media specific conversions
                        $blnDirect = (is_array($varOptions) && array_key_exists("directLink", $varOptions)) ? $varOptions["directLink"] : false;
                        self::filterUseMedia($this, $varReturn, $blnDirect);

                        break;
                    case VALUE_HILIGHT:
                        //*** Enable URLs and email addresses.
                        self::filterTextToHtml($varReturn);
                        break;
                    case VALUE_NOURL:
                        //*** Remove URLs and email addresses.
                        self::filterRemoveUrl($varReturn);
                        break;
                    case VALUE_SRC:
                        //*** Get the source of an image or file field.
                        $objValue = (is_array($varReturn)) ? array_pop($varReturn) : null;
                        $varReturn = (is_array($objValue)) ? $objCms->getFilePath() . $objValue['src'] : null;
                        break;
                    case VALUE_ORIGINAL:
                        //*** Get the original name of an image or file field.
                        $objValue = (is_array($varReturn)) ? array_pop($varReturn) : null;
                        $varReturn = (is_array($objValue)) ? $objValue['original'] : null;
                        break;
                    case VALUE_IMAGES:
                        //*** Get the collection of images objects.
                        $varReturn = $this->buildImageCollection();
                        break;
                    case VALUE_DOWNLOAD:
                    case VALUE_INLINE:
                        //*** Get the download path for an image or file field.
                        if (count($varReturn) == 0) {
                            $varReturn = "";
                        } else {
                            if ($objCms->usesAliases()) {
                                $strId = (!is_null($varOptions) && is_numeric($varOptions)) ? $this->id . "_" . $varOptions : $this->id;
                                $varReturn = $objCms->getDownloadPath() . $strId;
                                if ($filter == VALUE_INLINE) {
                                    $varReturn .= "/inline";
                                }
                            } else {
                                $strId = (!is_null($varOptions) && is_numeric($varOptions)) ? $this->id . "&amp;index=" . $varOptions : $this->id;
                                $varReturn = $objCms->getDownloadPath() . $strId;
                            }
                        }
                        break;
                    case VALUE_XML:
                        //*** Prepare output for XML.

                        //*** Apply field type specific conversions
                        if ($objCms->usesAliases()) {
                            self::filterUseAliases($this, $varReturn);
                        }

                        //*** Apply media specific conversions
                        $blnDirect = (is_array($varOptions) && array_key_exists("directLink", $varOptions)) ? $varOptions["directLink"] : false;
                        self::filterUseMedia($this, $varReturn, $blnDirect);

                        //*** Replace & characters with &amp; and add slashes.
                        self::filterForXml($varReturn);
                        break;
                }
            }
        }

        return $varReturn;
    }

    public function buildImageCollection()
    {
        $objCms = Client::getInstance();
        $objReturn = new Collection();

        $arrImages = $this->getValue();
        if (is_object($arrImages) || is_array($arrImages)) {
            foreach ($arrImages as $arrImage) {
                $objImageValue = new ImageValue($this->getSettings());
                $objImageValue->setPath($objCms->getFilePath());
                $objImageValue->setSrc($arrImage['src']);
                $objImageValue->setOriginal($arrImage['original']);
                $objImageValue->setAlt($arrImage['alt']);

                $objReturn->addObject($objImageValue);
            }
        }

        return $objReturn;
    }

    public function getSettings()
    {
        $arrReturn = null;

        switch ($this->type) {
            case FIELD_TYPE_IMAGE:
                if (!empty($this->templateFieldId)) {
                    $objImage = new ImageField($this->templateFieldId);
                    $arrReturn = $objImage->getSettings();
                }

                break;
        }

        return $arrReturn;
    }

    public function getSize($index = 0)
    {
        //*** Return the width and height of an image field as an array.
        $arrReturn = array('width' => 0, 'height' => 0);

        if ($this->type == FIELD_TYPE_IMAGE) {
            $objCms = Client::getInstance();
            $arrFiles = $this->getValue();
            $arrFile = (is_array($arrFiles)) ? $arrFiles[$index] : null;
            $strFile = (is_array($arrFile)) ? $objCms->getBasePath() . $objCms->getFilePath() . $arrFile['src'] : null;
            if (is_file($strFile)) {
                $arrTemp = getimagesize($strFile);
                $arrReturn['width'] = $arrTemp[0];
                $arrReturn['height'] = $arrTemp[1];
            }
        }

        return $arrReturn;
    }

    public function getWidth($index = 0)
    {
        //*** Return the width of an image field as an integer.
        $arrSize = $this->getSize($index);

        return $arrSize['width'];
    }

    public function getHeight($index = 0)
    {
        //*** Return the height of an image field as an integer.
        $arrSize = $this->getSize($index);

        return $arrSize['height'];
    }

    public function getHtmlSize($index = 0)
    {
        //*** Return the width and height of an image field as an URL string.
        $arrSize = $this->getSize($index);

        return "width=\"{$arrSize['width']}\" height=\"{$arrSize['height']}\"";
    }

    public function getHtmlValue($varFilter = null)
    {
        if (!is_array($varFilter)) {
            if (empty($varFilter)) {
                $varFilter = array();
            } else {
                $varFilter = array($varFilter);
            }
        }

        array_push($varFilter, VALUE_HTML);
        $varFilter = array_unique($varFilter);

        $varReturn = $this->getValue($varFilter);

        return $varReturn;
    }

    public function getRawValue()
    {
        if (is_object($this->objField)) {
            $objCms = Client::getInstance();
            return $this->objField->getRawValue($objCms->getLanguage()->getId());
        } else {
            return "";
        }
    }

    public function getOriginalName()
    {
        if (is_object($this->objField)) {
            $objCms = Client::getInstance();
            $objValue = $this->objField->getValueObject($objCms->getLanguage()->getId());
            if (is_object($objValue)) {
                return $objValue->getOriginalName();
            }
        } else {
            return "";
        }
    }

    public function getShortValue($intCharLength = 200, $blnPreserveWord = true, $strAppend = " ...", $blnHtml = true)
    {
        //*** Get a short version of the value.
        if ($blnHtml) {
            $strInput = $this->getHtmlValue();
        } else {
            $strInput = $this->getValue();
        }
        $strReturn = $strInput;

        $strReturn = substr($strInput, 0, $intCharLength);

        if ($blnPreserveWord == true && strlen($strReturn) < strlen($strInput)) {
            $intLastSpace = strrpos($strReturn, " ");
            $strReturn = substr($strReturn, 0, $intLastSpace);
        }

        if (strlen($strReturn) < strlen($strInput)) {
            $strReturn .= $strAppend;
        }

        return $strReturn;
    }

    public function getLink($blnAbsolute = true, $strAddQuery = "", $strLanguageAbbr = null)
    {
        if ($this->type == FIELD_TYPE_LINK) {
            $objCms = Client::getInstance();
            $value = $this->getValue();
            if (!empty($value)) {
                // file
                if (is_array($value)) {
                    //return $objField->getValue(VALUE_SRC);
                } else {
                    if (preg_match('/^(http:\/\/|https:\/\/|mailto:)+/', $value)) {
                        return $value;
                    } elseif (preg_match('/^(www)+/', $value)) {
                        return 'http://'. $value;
                    } else {
                        // deep link
                        $objElement = $objCms->getElementById($this->getValue());
                        if (is_object($objElement)) {
                            return $objElement->getLink($blnAbsolute, $strAddQuery, $strLanguageAbbr);
                        }
                    }
                }
            }
        }
    }

    public function getElement()
    {
        if ($this->type == FIELD_TYPE_LINK) {
            $objCms = Client::getInstance();
            $objElement = $objCms->getElementById($this->getValue());

            if (is_object($objElement)) {
                return $objElement;
            }
        }
    }

    private static function filterTextToHtml(&$text)
    {
        // match protocol://address/path/
        $text = mb_ereg_replace("[a-zA-Z]+://([.]?[a-zA-Z0-9_/-])*", "<a href=\"\\0\" rel=\"external\">\\0</a>", $text);

        // match www.something
        $text = mb_ereg_replace("(^| |.)(www([.]?[a-zA-Z0-9_/-])*)", "\\1<a href=\"http://\\2\" rel=\"external\">\\2</a>", $text);

        // match email
        $text = mb_ereg_replace("[-a-z0-9!#$%&\'*+/=?^_`{|}~]+@([.]?[a-zA-Z0-9_/-])*", "<a href=\"mailto:\\0\" title=\"mailto:\\0\">\\0</a>", $text);
    }

    private static function filterAddAmpersand(&$text)
    {
        $text = preg_replace("/&(?!#?[xX]?(?:[0-9a-fA-F]+|\w{1,8});)/i", "&amp;", $text);
    }

    private static function filterFixXtmlLinkTarget(&$text)
    {
        $text = str_ireplace("target=\"_blank\"", "rel=\"external\"", $text);
        $text = str_ireplace("target=\"_top\"", "rel=\"external\"", $text);
    }

    private static function filterUseAliases($objField, &$text)
    {
        $objCms = Client::getInstance();

        switch ($objField->type) {
            case FIELD_TYPE_LARGETEXT:
                //*** Replace "href='?eid=" with "href='/?eid=" or "href='alias" if useAliases is on.
                $strPattern = "/(\?eid=)([0-9]+)/ie";
                $arrMatches = array();
                if (preg_match_all($strPattern, $text, $arrMatches) > 0) {
                    for ($intCount = 0; $intCount < count($arrMatches[0]); $intCount++) {
                        $strMatch = $arrMatches[0][$intCount];
                        $objElement = $objCms->getElementById($arrMatches[2][$intCount]);
                        if (is_object($objElement)) {
                            $text = str_ireplace("href=\"{$strMatch}", "href=\"" . $objElement->getLink(), $text);
                        }
                    }
                }

                break;
        }
    }

    private static function filterUseMedia($objField, &$text, $blnDirect)
    {
        $objCms = Client::getInstance();

        switch ($objField->type) {
            case FIELD_TYPE_LARGETEXT:
                //*** Replace "href='?mid=" with "href='/download.php?mid=" or "href='/download/media/id" if useAliases is on.
                $strPattern = "/(\?mid=)([0-9]+)/ie";
                $arrMatches = array();
                if (preg_match_all($strPattern, $text, $arrMatches) > 0) {
                    for ($intCount = 0; $intCount < count($arrMatches[0]); $intCount++) {
                        $strMatch = $arrMatches[0][$intCount];
                        if ($blnDirect) {
                            $objMediaItem = $objCms->getMediaById($arrMatches[2][$intCount]);
                            if (is_object($objMediaItem)) {
                                $strLink = $objCms->getFilePath() . $objMediaItem->getData()->getLocalName();
                                $text = str_ireplace("href=\"{$strMatch}", "href=\"" . $strLink, $text);
                            }
                        } else {
                            $strLink = ($objCms->usesAliases()) ? "/download/media/" : "/download.php?mid=";
                            $text = str_ireplace("href=\"{$strMatch}", "href=\"" . $strLink . $arrMatches[2][$intCount], $text);
                        }
                    }
                }

                break;
        }
    }

    private static function filterRemoveUrl(&$text)
    {
        // match protocol://address/path/
        $text = mb_ereg_replace("[a-zA-Z]+://([.]?[a-zA-Z0-9_/-])*", "", $text);

        // match www.something
        $text = mb_ereg_replace("(^| |.)(www([.]?[a-zA-Z0-9_/-])*)", "", $text);

        // match email
        $text = mb_ereg_replace("[-a-z0-9!#$%&\'*+/=?^_`{|}~]+@([.]?[a-zA-Z0-9_/-])*", "", $text);
    }

    private static function filterForXml(&$text)
    {
        //*** Convert HTML entities to the real characters.
        $text = html_entity_decode($text, ENT_COMPAT, "UTF-8");

        //*** Replace & characters with &amp;.
        self::filterAddAmpersand($text);

        //*** Replace 4 other characters with XML entities.
        $text = str_replace("<", "&lt;", $text);
        $text = str_replace(">", "&gt;", $text);
        $text = str_replace("\"", "&quot;", $text);
        $text = str_replace("'", "&apos;", $text);
    }
}
