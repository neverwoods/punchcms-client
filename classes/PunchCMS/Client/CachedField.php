<?php

namespace PunchCMS\Client;

use PunchCMS\DBAL\Object;
use PunchCMS\DBAL\Collection;
use PunchCMS\TemplateField;
use PunchCMS\ImageValue;
use PunchCMS\ImageField;
use Bili\Date;
use Bili\FileIO;

class CachedField extends Object
{
    protected $elementid = 0;
    protected $templatefieldid = 0;
    protected $elementfieldid = 0;
    protected $typeid = 0;
    protected $apiname = "";
    protected $element = "";
    protected $value = "";
    protected $languageid = 0;
    protected $cascade = 0;
    protected $rawvalue = "";

    //*** Constructor.
    public function __construct()
    {
        self::$object = "\\PunchCMS\\Client\\CachedField";
        self::$table = "pcms_element_field";
    }

    public static function select($strSql = "")
    {
        self::$object = "\\PunchCMS\\Client\\CachedField";
        self::$table = "pcms_element_field";

        return parent::select($strSql);
    }

    public function getAutoValue()
    {
        $objCms = Client::getInstance();

        $return = '';
        switch ($this->typeid) {
            case FIELD_TYPE_LARGETEXT:
                $return = $this->getHtmlValue();
                break;
            case FIELD_TYPE_IMAGE:
                $values = array();
                $arrSettings = $this->getSettings();
                $objImages = $this->getValue(VALUE_IMAGES);
                foreach ($objImages as $objImage) {
                    $templates = array();

                    // search for templates
                    foreach ($arrSettings as $arrSetting) {
                        if ($arrSetting['api'] !='') {
                            $templates[$arrSetting['api']] = $objCms->getFilePath() . FileIO::add2Base($objImage->getSrc(), $arrSetting['key']);
                        }
                    }

                    $values[] = array(
                        'original' => $objImage->getOriginal(),
                        'src' => $objImage->getSrc(),
                        'height' => $objImage->getHeight(),
                        'width' => $objImage->getWidth(),
                        'templates' => $templates,
                    );
                }
                $return = $values;
                break;
            case FIELD_TYPE_FILE:
                $values = array();
                $arrFiles = $this->getValue();
                foreach ($arrFiles as $arrFile) {
                    $values[] = array(
                        'original' => $arrFile['original'],
                        'src' => $objCms->getFilePath() . $arrFile['src'],
                        'download' => $objCms->getDownloadpath() . $this->elementfieldid
                    );
                }
                $return = $values;
                break;
            case FIELD_TYPE_LINK:
                $return = $this->getLink();
                break;
            default:
                $return = $this->getValue();
                break;
        }
        return $return;
    }

    private function prepareValue()
    {
        if (empty($this->rawvalue) && !is_array($this->rawvalue) && !empty($this->value)) {
            $this->rawvalue = $this->value;

            switch ($this->typeid) {
                case FIELD_TYPE_DATE:
                    //*** Convert the date to the predefined format.
                    $objTemplateField = TemplateField::selectByPK($this->templatefieldid);
                    $this->value = Date::fromMysql($objTemplateField->getValueByName("tfv_field_format")->getValue(), $this->value);
                    break;

                case FIELD_TYPE_LARGETEXT:
                    //*** Correct internal anchors.
                    $intElementId = \PunchCMS\Element::selectByPk($this->elementid)->getPageId();
                    $this->value = str_replace("href=\"#", "href=\"?eid={$intElementId}#", $this->value);
                    break;

                case FIELD_TYPE_FILE:
                case FIELD_TYPE_IMAGE:
                    //*** Split the current filename from the raw value.
                    $arrReturn = array();
                    $arrFileTemp = explode("\n", $this->value);
                    foreach ($arrFileTemp as $fileValue) {
                        if (!empty($fileValue)) {
                            $arrTemp = explode(":", $fileValue);
                            $objTemp = array();
                            $objTemp["original"] = $arrTemp[0];
                            $objTemp["src"] = (count($arrTemp) > 1) ? $arrTemp[1] : $arrTemp[0];
                            $objTemp["media_id"] = (count($arrTemp) > 2) ? $arrTemp[2] : 0;
                            $objTemp["alt"] = (count($arrTemp) > 3) ? $arrTemp[3] : "";
                            array_push($arrReturn, $objTemp);
                        }
                    }
                    $this->value = $arrReturn;
                    break;

                case FIELD_TYPE_BOOLEAN:
                    //*** Make it a true boolean.
                    if ($this->value == "true") {
                        $this->value = true;
                    } else {
                        $this->value = false;
                    }
                    break;

                case FIELD_TYPE_SIMPLETEXT:
                    $this->value = nl2br($this->value);
                    break;
            }
        }

        if (empty($this->value)) {
            switch ($this->typeid) {
                case FIELD_TYPE_FILE:
                case FIELD_TYPE_IMAGE:
                    $this->value = array();
                    break;
            }
        }
    }

    public function getValue($varFilter = null, $varOptions = null)
    {
        $objCms = Client::getInstance();

        $this->prepareValue();
        $varReturn = $this->value;

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

                        //*** Apply media specific conversions
                        $blnDirect = (is_array($varOptions) && array_key_exists("directLink", $varOptions)) ? $varOptions["directLink"] : false;
                        self::filterUseMedia($this, $varReturn, $blnDirect);

                        break;
                    case VALUE_HILIGHT:
                        //*** Enable URLs and email addresses.
                        self::filterText2html($varReturn);
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
                                $strId = (!is_null($varOptions) && is_numeric($varOptions)) ? $this->elementfieldid . "_" . $varOptions : $this->elementfieldid;
                                $varReturn = $objCms->getDownloadPath() . $strId;
                                if ($filter == VALUE_INLINE) {
                                    $varReturn .= "/inline";
                                }
                            } else {
                                $strId = (!is_null($varOptions) && is_numeric($varOptions)) ? $this->elementfieldid . "&amp;index=" . $varOptions : $this->elementfieldid;
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
                        self::filterForXML($varReturn);
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

        switch ($this->typeid) {
            case FIELD_TYPE_IMAGE:
                $objImage = new ImageField($this->templatefieldid);
                $arrReturn = $objImage->getSettings();

                break;
        }

        return $arrReturn;
    }

    public function getField()
    {
        $objReturn = null;

        $strSql = "SELECT * FROM pcms_element_field WHERE elementId = '%s' AND templateFieldId = '%s' ORDER BY sort";
        $objFields = \PunchCMS\ElementField::select(sprintf($strSql, $this->elementid, $this->templatefieldid));

        if (is_object($objFields) && $objFields->count() > 0) {
            $objReturn = $objFields->current();
        }

        return $objReturn;
    }

    public function getSize($index = 0)
    {
        //*** Return the width and height of an image field as an array.
        $arrReturn = array('width' => 0, 'height' => 0);

        if ($this->typeid == FIELD_TYPE_IMAGE) {
            $objCms = Client::getInstance();
            $arrFiles = $this->getValue();
            $arrFile = (is_array($arrFiles) && count($arrFiles) >= $index + 1) ? $arrFiles[$index] : null;
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
        $this->prepareValue();

        return $this->rawvalue;
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
        if ($this->typeid == FIELD_TYPE_LINK) {
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
        if ($this->typeid == FIELD_TYPE_LINK) {
            $objCms = Client::getInstance();
            $objElement = $objCms->getElementById($this->getValue());

            if (is_object($objElement)) {
                return $objElement;
            }
        }
    }

    private static function filterText2html(&$text)
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

        switch ($objField->typeid) {
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

    private static function filterUseMedia($objField, &$text, $blnDirect = false)
    {
        $objCms = Client::getInstance();

        switch ($objField->typeid) {
            case FIELD_TYPE_LARGETEXT:
                // Replace "href='?mid=" with "href='/download.php?mid="
                // or "href='/download/media/id" if useAliases is on.
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

    private static function filterForXML(&$text)
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
