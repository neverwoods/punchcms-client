<?php

namespace PunchCMS\Client;

use Bili\Request;
use PunchCMS\DBAL\Object;
use PunchCMS\Setting;
use PunchCMS\StorageItem;
use PunchCMS\TemplateField;
use PunchCMS\Alias;
use PunchCMS\SearchResults;
use PunchCMS\Search;
use PunchCMS\FormBuilder;
use PunchCMS\ContentLanguage;
use PunchCMS\Account;
use Bili\Date;

//*** Global constantes.
define("ELM_TYPE_FOLDER", 1);
define("ELM_TYPE_ELEMENT", 2);
define("ELM_TYPE_CONTAINER", 3);
define("ELM_TYPE_DYNAMIC", 4);
define("ELM_TYPE_LOCKED", 5);
define("ELM_TYPE_ALL", "'1','2','3','4','5'");

define('FIELD_TYPE_DATE', 1);
define('FIELD_TYPE_SMALLTEXT', 2);
define('FIELD_TYPE_LARGETEXT', 3);
define('FIELD_TYPE_FILE', 4);
define('FIELD_TYPE_NUMBER', 5);
define('FIELD_TYPE_SELECT_LIST_MULTI', 6);
define('FIELD_TYPE_IMAGE', 7);
define('FIELD_TYPE_USER', 8);
define('FIELD_TYPE_LINK', 9);
define('FIELD_TYPE_BOOLEAN', 10);
define('FIELD_TYPE_SELECT_LIST_SINGLE', 11);
define('FIELD_TYPE_CHECK_LIST_MULTI', 12);
define('FIELD_TYPE_CHECK_LIST_SINGLE', 13);
define('FIELD_TYPE_SIMPLETEXT', 14);

define('VALUE_HTML', 1);
define('VALUE_HILIGHT', 2);
define('VALUE_NOURL', 3);
define('VALUE_SRC', 4);
define('VALUE_ORIGINAL', 5);
define('VALUE_XML', 6);
define('VALUE_DOWNLOAD', 7);
define('VALUE_IMAGES', 8);
define('VALUE_INLINE', 9);

define("PCMS_DEFAULT_STARTDATE", "0000-00-00 00:00:00");
define("APP_DEFAULT_STARTDATE", "0000-00-00 00:00:00");
define("PCMS_DEFAULT_ENDDATE", "2100-01-01 01:00:00");
define("APP_DEFAULT_ENDDATE", "2100-01-01 01:00:00");

/**
 * Holds the PunchCMS client logic
 *
 * @author Felix Langfeldt <felix@neverwoods.com>
 */
class Client
{
    protected static $__connId = null;
    protected static $__account = null;
    protected static $__basePath = null;
    protected static $__instance = null;
    protected static $__dsn = "";
    protected static $__dbUser = "";
    protected static $__dbPassword = "";
    protected static $__language = null;
    protected static $__cacheConfig = array();

    private $__aliases = false;
    private $__cacheFields = false;
    private $__cachedFields = array();
    private $__defaultLanguage = null;
    private $__languages = null;
    private $__sitemapBlacklist = [];

    private function __construct($strDSN = "", $strUsername = "", $strPassword = "")
    {
        /* Private constructor to insure singleton behaviour */

        if (!empty($strDSN)) {
            self::$__dsn = $strDSN;
            self::$__dbUser = $strUsername;
            self::$__dbPassword = $strPassword;
        }

        $this->setDbConnection();
    }

    public static function singleton($strDSN, $strUsername, $strPassword, $strAccountId, $strBasePath)
    {
        /* Method to initially instanciate the class */

        self::$__instance = new Client($strDSN, $strUsername, $strPassword);
        self::$__instance->setAccount($strAccountId);
        self::$__instance->setBasePath($strBasePath);

        //*** Caching.
        $cacheLiteConfig = array(
            'caching' => (self::$__instance->getSetting('caching_enable')) ? true : false,
            'cacheDir' => $strBasePath . self::$__instance->getSetting('caching_folder'),
            'lifeTime' => self::$__instance->getSetting('caching_timeout') * 60,
            'fileNameProtection' => false
        );
        self::$__instance->setCacheConfig($cacheLiteConfig);

        return self::$__instance;
    }

    /**
     * Return a singleton instance of the Client
     *
     * @return Client Singleton instance of Client
     */
    public static function getInstance()
    {
        /* Get the singleton instance for this class */

        if (is_null(self::$__instance)) {
            self::$__instance = new Client();
        }

        return self::$__instance;
    }

    public function get($strName = "", $blnRecursive = false)
    {
        $objReturn = new Elements();

        if (!empty($strName)) {
            $objReturn = Elements::getElements($strName, 0, true, $blnRecursive);
        }

        return $objReturn;
    }

    public function getElements($strName = "", $blnGetOne = false, $blnRecursive = false)
    {
        $objReturn = new Elements();

        if (!empty($strName)) {
            $objReturn = Elements::getElements($strName, 0, $blnGetOne, $blnRecursive);
        }

        return $objReturn;
    }

    public function getFolders($strName = "", $blnGetOne = false)
    {
        $objReturn = Elements::getFolders($strName, 0, $blnGetOne);

        return $objReturn;
    }

    public function getPageElements($intLanguage = null)
    {
        $objReturn = new Elements();

        if (is_null($intLanguage)) {
            $intLanguage = $this->getLanguage()->getId();
        }

        //*** Get individual page elements.
        $strSql = "SELECT pcms_element.*
                    FROM pcms_element
                    RIGHT JOIN pcms_element_language
                    ON pcms_element.id = pcms_element_language.elementId
                    INNER JOIN pcms_template ON pcms_element.templateId = pcms_template.id
                    WHERE pcms_element_language.languageId = '%s'
                    AND pcms_element_language.active = '1'
                    AND pcms_element.accountId = '%s'
                    AND pcms_element.active = '1'
                    AND pcms_template.isPage = '1'";
        $objElements = \PunchCMS\Element::select(sprintf($strSql, $intLanguage, $this->getAccount()->getId()));
        foreach ($objElements as $objElement) {
            $objCMSElement = new Element($objElement);
            $objReturn->addObject($objCMSElement);
        }

        return $objReturn;
    }

    public function getElementById($intId, $intLanguage = null)
    {
        $objReturn     = null;
        $intId         = (int)$intId; // Mandatory to prevent SQL injection

        if ($intId > 0) {
            if (is_null($intLanguage)) {
                $intLanguage = $this->getLanguage()->getId();
            }

            $strSql = "SELECT pcms_element.* FROM pcms_element
                    RIGHT JOIN pcms_element_language
                    ON pcms_element.id = pcms_element_language.elementId
                    INNER JOIN pcms_element_schedule
                    ON pcms_element.id = pcms_element_schedule.elementId
                    WHERE pcms_element.id = %s
                    AND pcms_element.active = '1'
                    AND pcms_element.accountId = '%s'
                    AND pcms_element_language.languageId = '%s'
                    AND pcms_element_language.active = '1'
                    AND pcms_element.id = pcms_element_schedule.elementId
                    AND pcms_element_schedule.startDate <= '%s'
                    AND pcms_element_schedule.endDate >= '%s'
                    ORDER BY pcms_element.sort";
            $objElements = \PunchCMS\Element::select(
                sprintf(
                    $strSql,
                    Object::escape($intId),
                    self::getAccount()->getId(),
                    (int)$intLanguage,
                    Elements::toMysql(),
                    Elements::toMysql()
                )
            );

            if ($objElements->count() > 0) {
                $objReturn = new Element($objElements->current());
            }
        }

        return $objReturn;
    }

    public function getFieldById($intId)
    {
        $objReturn = null;
        $intId = (int)$intId;

        if ($intId > 0) {
            $strSql = "SELECT pcms_element_field.* FROM pcms_element_field, pcms_element, pcms_element_schedule
                    WHERE pcms_element_field.id = %s
                    AND pcms_element.id = pcms_element_field.elementId
                    AND pcms_element.active = '1'
                    AND pcms_element.accountId = '%s'
                    AND pcms_element.id = pcms_element_schedule.elementId
                    AND pcms_element_schedule.startDate <= '%s'
                    AND pcms_element_schedule.endDate >= '%s'
                    ORDER BY pcms_element_field.sort";
            $objFields = \PunchCMS\ElementField::select(sprintf($strSql, Object::escape($intId), self::getAccount()->getId(), Elements::toMysql(), Elements::toMysql()));
            if ($objFields->count() > 0) {
                $objField = $objFields->current();
                $objReturn = new ElementField($objField->getElementId(), TemplateField::selectByPk($objField->getTemplateFieldId()));
            }
        }

        return $objReturn;
    }

    public function getMediaById($intId)
    {
        $objReturn = null;
        $intId = (int)$intId;

        if ($intId > 0) {
            $objReturn = StorageItem::selectByPk($intId);
        }

        return $objReturn;
    }

    /**
     * Get an element by template name.
     *
     * @param string $strName
     * @param boolean $blnRecursive
     * @param boolean $blnRandom
     * @return Element Instance of Element
     */
    public function getElementByTemplate($strName, $blnRecursive = false, $blnRandom = false)
    {
        $objReturn = new Elements();

        if (!empty($strName)) {
            $objReturn = Elements::getElementsByTemplate($strName, 0, true, $blnRecursive, $blnRandom);
        }

        return $objReturn;
    }

    public function getElementsByTemplate($strName, $blnGetOne = false, $blnRecursive = false, $blnRandom = false)
    {
        $objReturn = new Elements();

        if (!empty($strName)) {
            $objReturn = Elements::getElementsByTemplate($strName, 0, $blnGetOne, $blnRecursive, $blnRandom);
        }

        return $objReturn;
    }

    public function getElementByTemplateId($intId, $blnRecursive = false, $blnRandom = false)
    {
        $objReturn = new Elements();

        if (!empty($intId)) {
            $objReturn = Elements::getElementsByTemplateId($intId, 0, true, $blnRecursive, $blnRandom);
        }

        return $objReturn;
    }

    public function getElementsByTemplateId($intId, $blnGetOne = false, $blnRecursive = false, $blnRandom = false)
    {
        $objReturn = new Elements();

        if (!empty($intId)) {
            $objReturn = Elements::getElementsByTemplateId($intId, 0, $blnGetOne, $blnRecursive, $blnRandom);
        }

        return $objReturn;
    }

    public function getElementsFromParent($intId, $blnGetOne = false, $blnRecursive = false)
    {
        $objReturn = new Elements();

        $objReturn = Elements::getElements("", $intId, $blnGetOne, $blnRecursive);

        return $objReturn;
    }

    public function setSitemapBlacklist(array $arrList)
    {
        $this->__sitemapBlacklist = $arrList;
    }

    public function getAliasId()
    {
        $intReturn = 0;

        if ($this->usesAliases()) {
            $strRewrite    = Request::get('rewrite');

            if (!empty($strRewrite)) {
                $strRewrite = rtrim($strRewrite, " \/");

                switch ($strRewrite) {
                    case "sitemap.xml":
                        //*** Automatic Sitemap generation.
                        header("Content-type: text/xml");
                        echo $this->renderSitemap();
                        exit;
                        break;
                    default:
                        if (strtolower(substr($strRewrite, 0, 4)) == "eid/") {
                            //*** Clean the rewrite string.
                            $strRewrite = $this->cleanRewrite($strRewrite);

                            //*** Google friendly eid URL.
                            $strUrl = substr($strRewrite, 4);
                            if (is_numeric($strUrl)) {
                                $intReturn = $strUrl;
                            }
                        } elseif (strtolower(substr($strRewrite, 0, 15)) == "download/media/") {
                            //*** Google friendly media URL.
                            $arrMediaPath = explode("/", substr($strRewrite, 15));
                            $blnInline = (count($arrMediaPath) > 1 && $arrMediaPath[1] == "inline") ? true : false;
                            $strMediaId = $arrMediaPath[0];
                            if (is_numeric($strMediaId)) {
                                $this->downloadMediaItem($strMediaId, $blnInline);
                                exit;
                                break;
                            }
                        } elseif (strtolower(substr($strRewrite, 0, 9)) == "download/") {
                            //*** Google friendly element field URL.
                            $arrMediaPath = explode("/", substr($strRewrite, 9));
                            $blnInline = (count($arrMediaPath) > 1 && $arrMediaPath[1] == "inline") ? true : false;
                            $arrField = explode("_", $arrMediaPath[0]);
                            if (is_numeric($arrField[0])) {
                                $intIndex = (count($arrField) > 1) ? $arrField[1] : 0;
                                $this->downloadElementField($arrField[0], $intIndex, "", $blnInline);
                                exit;
                                break;
                            }
                        } elseif (stristr($strRewrite, "/eid/") !== false) {
                            //*** Clean the rewrite string.
                            $strRewrite = $this->cleanRewrite($strRewrite);

                            //*** Google friendly eid URL after language definition.
                            $strUrl = substr(stristr($strRewrite, "eid/"), 4);
                            if (is_numeric($strUrl)) {
                                $intReturn = $strUrl;
                            }
                        } else {
                            //*** Clean the rewrite string.
                            $strRewrite = $this->cleanRewrite($strRewrite);

                            //*** Get the alias.
                            if (!empty($strRewrite)) {
                                $objUrls = Alias::selectByAlias($strRewrite);
                                if (!is_null($objUrls) && $objUrls->count() > 0) {
                                    $strUrl = $objUrls->current()->getUrl();
                                    if (is_numeric($strUrl)) {
                                        $intReturn = $strUrl;
                                    } else {
                                        Request::redirect($strUrl);
                                    }
                                }
                            }
                        }

                        /**
                         * If empty we set the return to -1. This indicates that a request to a specific page has
                         * been made, but could not be satisfied. This could lead to a 404 message.
                         */
                        if (empty($intReturn)) {
                            $intReturn = -1;
                        }
                }
            }
        }

        return $intReturn;
    }

    public function cleanRewrite($strRewrite)
    {
        //*** Strip off any page parameters.
        if (mb_strpos($strRewrite, "__page") !== false) {
            $strRewrite = substr($strRewrite, 0, mb_strpos($strRewrite, "__page") - 1);
        }

        //*** Strip of any language parameters.
        $arrUrl = explode("/", $strRewrite);
        $intKey = array_search("language", $arrUrl);
        if ($intKey !== false) {
            if ($intKey < count($arrUrl) - 2) {
                array_shift($arrUrl);
                array_shift($arrUrl);
                $strRewrite = implode("/", $arrUrl);
            } else {
                $strRewrite = "";
            }
        }

        //*** Sanitize rewrite string.
        //$strRewrite = mysql_real_escape_string($strRewrite);

        return $strRewrite;
    }

    public function getCurrentPage()
    {
        $intPage = 1;
        $strRewrite    = Request::get('rewrite');
        if (!empty($strRewrite) && mb_strpos($strRewrite, "__page") !== false) {
            $strRewrite = rtrim($strRewrite, " \/");
            $arrParams = explode("/", $strRewrite);
            $intPage = array_pop($arrParams);

            if ($intPage < 1) {
                $intPage = 1;
            }
        }

        return $intPage;
    }

    public function getHeaderJS()
    {
        $strOutput = "";

        $strAnalytics = $this->renderAnalytics();
        if (!empty($strAnalytics)) {
            $strOutput = "<script type=\"text/javascript\">\n// <![CDATA[\n";
            $strOutput .= "$(function(){ $('a').each(function(){ var link = $(this).attr('href'); if (link.match(/^(\/download\/)/)) { $(this).bind('click', function() { pageTracker._trackPageview(link); }); }}) });";
            $strOutput .= "\n// ]]>\n</script>";
        }

        return $strOutput;
    }

    public function downloadElementField($fieldId, $intIndex = 0, $strSettingName = "", $blnInline = false)
    {
        $blnError = false;

        $objElementField = $this->getFieldById($fieldId);
        if (is_object($objElementField)) {
            if (!empty($strSettingName)) {
                $objImages = $objElementField->getValue(VALUE_IMAGES);
                if ($objImages->count() > $intIndex) {
                    $objImages->seek($intIndex);
                }

                $objImage = $objImages->current();
                $strTarget = $this->getBasePath() . $objImage->getSrc($strSettingName);
                $strOriginal = $objImage->getOriginal();
            } else {
                $arrFiles = $objElementField->getValue();
                $arrValue = (count($arrFiles) > $intIndex) ? $arrFiles[$intIndex] : $arrFiles[0];
                $strTarget = $this->getBasePath() . $this->getFilePath() . $arrValue['src'];
                $strOriginal = $arrValue['original'];
            }

            if (!empty($strOriginal) && file_exists($strTarget) && $fh = fopen($strTarget, "rb")) {
                $mimeType = "application/octet-stream";
                if (function_exists("mime_content_type")) {
                    $strRes = mime_content_type($strTarget);
                    if (is_string($strRes) && !empty($strRes)) {
                        $mimeType = $strRes;
                    }
                }

                $strDisposition = ($blnInline) ? "inline" : "attachment";

                header("HTTP/1.1 200 OK");
                header("Pragma: public");
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                header("Cache-Control: private", false);
                header("Content-type: " . $mimeType);
                header("Content-Disposition: {$strDisposition}; filename=\"" . $strOriginal . "\"");
                header("Content-Transfer-Encoding: binary");
                header("Content-Length: " . filesize($strTarget));

                fpassthru($fh);
                fclose($fh);
                exit();
            } else {
                $blnError = true;
            }
        } else {
            $blnError = true;
        }

        if ($blnError === true) {
            header("HTTP/1.1 404 Not found");

            echo $this->getErrorHtml("Downloader", "Sorry, File not found", "<p>Unfortunatly we were unable to find the file you requested.</p>\n<p>Please inform the administrator of this website to prevent future problems.</p>");
            exit;
        }
    }

    public function downloadMediaItem($intId, $blnInline = false)
    {
        $blnError = false;

        $objMediaItem = $this->getMediaById($intId);
        if (is_object($objMediaItem)) {
            $strTarget = $this->getBasePath() . $this->getFilePath() . $objMediaItem->getData()->getLocalName();
            $strOriginalName = $objMediaItem->getData()->getOriginalName();

            if (!empty($strOriginalName) && file_exists($strTarget) && $fh = fopen($strTarget, "rb")) {
                $mimeType = "application/octet-stream";
                if (function_exists("mime_content_type")) {
                    $strRes = mime_content_type($strTarget);
                    if (is_string($strRes) && !empty($strRes)) {
                        $mimeType = $strRes;
                    }
                }

                $strDisposition = ($blnInline) ? "inline" : "attachment";

                header("HTTP/1.1 200 OK");
                header("Pragma: public");
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                header("Cache-Control: private", false);
                header("Content-type: " . $mimeType);
                header("Content-Disposition: {$strDisposition}; filename=\"" . $strOriginalName . "\"");
                header("Content-Transfer-Encoding: binary");
                header("Content-Length: " . filesize($strTarget));

                fpassthru($fh);
                fclose($fh);
                exit();
            } else {
                $blnError = true;
            }
        } else {
            $blnError = true;
        }

        if ($blnError === true) {
            header("HTTP/1.1 404 Not found");

            echo $this->getErrorHtml("Media Downloader", "Sorry, File not found", "<p>Unfortunatly we were unable to find the file you requested.</p>\n<p>Please inform the administrator of this website to prevent future problems.</p>");
            exit;
        }
    }

    public function getErrorHtml($strTitle, $strHeader, $strBody)
    {
        $strReturn = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"\n";
        $strReturn .= " \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n";
        $strReturn .= "<html xmlns=\"http://www.w3.org/1999/xhtml\">\n";
        $strReturn .= "<head>\n";
        $strReturn .= "<title>{$strTitle}</title>\n";
        $strReturn .= "<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\" />\n";
        $strReturn .= "</head>\n";
        $strReturn .= "<body>\n";
        $strReturn .= "<h2>{$strHeader}</h2>\n";
        $strReturn .= "{$strBody}\n";
        $strReturn .= "</body>\n";
        $strReturn .= "</html>\n";

        return $strReturn;
    }

    public function find($strQuery, $arrFilters = array(), $blnExact = false, $arrAllowedTypes = array())
    {
        /* Search for elements containing keywords
         *
         * Filters may look like this:
         * array("element.Shops", "template.HomePage > element.Misc > templates.Banner", "element:1399")
         *
         * Allowed types may look like this:
         * array("element.Shops", "template.HomePage", "element:1399")
         *
         */

        $objReturn = new SearchResults();

        if (!empty($strQuery)) {
            $objSearch = new Search();
            $objReturn = $objSearch->find($strQuery, $blnExact);

            //*** Filters.
            if (count($arrFilters) > 0) {
                //*** Parse filters.
                $arrElementIds = array();
                foreach ($arrFilters as $strFilter) {
                    $arrRecursive = explode(">", $strFilter);
                    $objElement = $this;
                    foreach ($arrRecursive as $strRecursive) {
                        $arrFilter = explode(".", trim($strRecursive));
                        if (!empty($arrFilter[1])) {
                            switch ($arrFilter[0]) {
                                case "element":
                                    if (is_numeric($arrFilter[1])) {
                                        $objElement = $this->getElementById($arrFilter[1]);
                                    } else {
                                        $objElement = $objElement->get($arrFilter[1]);
                                    }
                                    break;
                                case "elements":
                                    if (is_numeric($arrFilter[1])) {
                                        $objElement = $this->getElementById($arrFilter[1])->getElements();
                                    } else {
                                        $objElement = $objElement->getElements($arrFilter[1]);
                                    }
                                    break;
                                case "template":
                                    if (is_numeric($arrFilter[1])) {
                                        $objElement = $objElement->getElementByTemplateId($arrFilter[1]);
                                    } else {
                                        $objElement = $objElement->getElementByTemplate($arrFilter[1]);
                                    }
                                    break;
                                case "templates":
                                    if (is_numeric($arrFilter[1])) {
                                        $objElement = $objElement->getElementsByTemplateId($arrFilter[1]);
                                    } else {
                                        $objElement = $objElement->getElementsByTemplate($arrFilter[1]);
                                    }
                                    break;
                            }
                        }
                    }

                    if (method_exists($objElement, "count")) {
                        $objElements = $objElement;
                        foreach ($objElements as $objElement) {
                            array_push($arrElementIds, $objElement->getElement()->getId());
                        }
                    } else {
                        array_push($arrElementIds, $objElement->getElement()->getId());
                    }
                }

                //*** Apply filters.
                $objResults = new SearchResults();
                $objResults->setQuery($objReturn->getQuery());
                foreach ($objReturn as $objResult) {
                    foreach ($arrElementIds as $intElementId) {
                        $objElement = $this->getElementById($objResult->id);
                        if (is_object($objElement) && $objElement->hasParentId($intElementId)) {
                            if (count($arrAllowedTypes) > 0) {
                                foreach ($arrAllowedTypes as $allowedType) {
                                    $arrFilter = explode(".", trim($allowedType));
                                    if (!empty($arrFilter[1])) {
                                        switch ($arrFilter[0]) {
                                            case "element":
                                                if (is_numeric($arrFilter[1])) {
                                                    if ($objElement->getId() == $arrFilter[1]) {
                                                        $objResults->addObject($objResult);
                                                    }
                                                } else {
                                                    if ($objElement->getName() == $arrFilter[1]) {
                                                        $objResults->addObject($objResult);
                                                    }
                                                }
                                                break;
                                            case "template":
                                                if ($objElement->getTemplateName() == $arrFilter[1]) {
                                                    $objResults->addObject($objResult);
                                                }
                                                break;
                                        }
                                    }
                                }
                            } else {
                                $objResults->addObject($objResult);
                            }
                        }
                    }
                }

                $objReturn = $objResults;
            }
        }
        return $objReturn;
    }

    /**
     * Build ValidForm Builder form from PunchCMS form element
     * @param Element $objForm
     * @return string The form if invalid and otherwise the "thank you" message.
     */
    public function buildForm($objForm)
    {
        $objValidForm = new FormBuilder($objForm);
        return $objValidForm->buildForm();
    }

    public function useAliases($blnValue)
    {
        $this->__aliases = $blnValue;
    }

    public function usesAliases()
    {
        return $this->__aliases;
    }

    public function setCacheFields($blnValue)
    {
        $this->__cacheFields = $blnValue;
    }

    public function getCacheFields()
    {
        return $this->__cacheFields;
    }

    public function getLanguages()
    {
        return ContentLanguage::selectActiveLanguages();
    }

    public function getLanguageArray()
    {
        if (!is_array($this->__languages)) {
            $arrLanguages = array();
            $objLanguages = $this->getLanguages();

            foreach ($objLanguages as $objLanguage) {
                array_push($arrLanguages, $objLanguage->getId());
            }

            $this->__languages = $arrLanguages;
        }

        return $this->__languages;
    }

    public function getLanguage()
    {
        return self::$__language;
    }

    public function getDefaultLanguage()
    {
        if (is_null($this->__defaultLanguage)) {
            $this->__defaultLanguage = ContentLanguage::getDefault()->getId();
        }

        return $this->__defaultLanguage;
    }

    public function setLanguage($objValue)
    {
        self::$__language = $objValue;
    }

    public function getCacheConfig()
    {
        return self::$__cacheConfig;
    }

    public function setCacheConfig($arrValue)
    {
        self::$__cacheConfig = $arrValue;
    }

    public function setBasePath($strValue)
    {
        self::$__basePath = $strValue;
    }

    public function getBasePath()
    {
        return self::$__basePath;
    }

    public function getDownloadPath()
    {
        if ($this->usesAliases()) {
            return "/download/";
        } else {
            return $this->getSetting("file_download");
        }
    }

    public function getFilePath()
    {
        return $this->getSetting("file_folder");
    }

    public function getCachedFields($intElementId)
    {
        $objReturn = null;

        if (array_key_exists($intElementId, $this->__cachedFields)) {
            $objReturn = $this->__cachedFields[$intElementId];
        }

        return $objReturn;
    }

    public function setCachedFields($intElementId, $objFields)
    {
        $this->__cachedFields[$intElementId] = $objFields;
    }

    /**
     * Get the connection object for the CMS.
     *
     * @param string $blnReInit
     * @return \PDO
     */
    public static function getConn($blnReInit = false)
    {
        $objCms = self::getInstance();

        if ($blnReInit) {
            //*** Reset the connection. Could have been set by external scripts.
            $objCms->setDbConnection(true);
        }

        return self::$__connId;
    }

    /**
     * Cache output of methods
     *
     * $strMethod can be the name of the requested method or a static class call.
     * In that case $strMethod should be an array like so array("Class name", "Method name").
     *
     * $intElementId is the identifier for the cache request. If the element changes
     * in the CMS the cache file will be deleted according to the element id.
     *
     * $varArguments is either a single argument or array of arguments that need to
     * be passed to the requested method.
     *
     * $intUniqueId is an optional way to split the "delete" id and the "cache" id.
     * This is particually handy for menu's. In that case every page needs a unique cache,
     * but as soon as one item changes all caches need to be removed.
     *
     * If caching is turned of in the CMS this method will work fully transparent and
     * return the output of the method without caching it.
     *
     * @param string $strMethod
     * @param integer $intElementId
     * @param mixed|null $varArguments
     * @param integer $intUniqueId
     * @param integer $intLifetime
     *
     * @return string
     */
    public static function getFromCache(
        $strMethod,
        $intElementId,
        $varArguments = null,
        $intUniqueId = null,
        $intLifetime = null
    ) {
        $strReturn = "";

        $objCms = self::getInstance();

        $intLangId = $objCms->getLanguage()->getId();
        if (is_array($strMethod)) {
            if (is_object($strMethod[0]) && get_class($strMethod[0])) {
                $strPlainMethod = get_class($strMethod[0]) . "." . $strMethod[1];
            } else {
                $strPlainMethod = implode(".", $strMethod);
            }
        } else {
            $strPlainMethod = $strMethod;
        }
        $arrArguments = (is_array($varArguments)) ? $varArguments : array($varArguments);
        $strArguments = "";
        foreach ($arrArguments as $value) {
            if (!is_object($value) && !is_array($value)) {
                $strArguments .= "_" . $value;
            } else {
                $strArguments .= "_" . md5(serialize($value));
            }
        }

        //*** Create OS save filename.
        $strArguments = (strlen($strArguments) > 0) ? "_" . md5($strArguments) : "";

        if (!is_null($intUniqueId)) {
            $intElementId = $intUniqueId . "_" . $intElementId;
        }
        $strId = (strlen($strArguments) > 0) ? $strPlainMethod . "_" . $intElementId . $strArguments . "_{$intLangId}" : $strPlainMethod . "_" . $intElementId . "_{$intLangId}";

        //*** Get configuration and override if apllicable.
        $arrConfig = $objCms->getCacheConfig();
        if (!is_null($intLifetime) && is_int($intLifetime)) {
            $arrConfig["lifeTime"] = $intLifetime;
        }

        // @FIXME Add CacheLite
        $objCache = new \Cache_Lite($arrConfig);
        if ($strReturn = $objCache->get($strId)) {
            //*** Cache hit, unserialize.
            $strUnserialized = @unserialize($strReturn);
            if ($strUnserialized !== false) {
                $strReturn = $strUnserialized;
            }
        } else {
            if (is_callable($strMethod, true)) {
                $strReturn = call_user_func_array($strMethod, $arrArguments);
                $strCache = (is_object($strReturn) || is_array($strReturn)) ? serialize($strReturn) : $strReturn;
                if (!empty($strCache)) {
                    $objCache->save($strCache, $strId);
                }
            }
        }

        return $strReturn;
    }

    public function getSetting($strValue)
    {
        return Setting::getValueByName($strValue, self::$__account->getId());
    }

    public static function getAccount()
    {
        return self::$__account;
    }

    public function setDbConnection($blnReInit = false)
    {
        try {
            $objConnID = new \PDO(self::$__dsn, self::$__dbUser, self::$__dbPassword, array(
                \PDO::ATTR_PERSISTENT => true
            ));
        } catch (\PDOException $e) {
            throw new \Exception(
                'Database connection failed. ' .
                PHP_EOL .
                'DSN: ' .
                self::$__dsn .
                PHP_EOL .
                'Message: ' .
                $e->getMessage(),
                SQL_CONN_ERROR
            );
        }

        self::$__connId = $objConnID;
    }

    public function renderAnalytics($analyticsKey = null, $strDomainName = null)
    {
        $strOutput = "";

        if (is_null($analyticsKey)) {
            $objCms = self::getInstance();
            $objSettings = $objCms->getElementByTemplate("GlobalFields");
            if (is_object($objSettings)) {
                $analyticsKey = $objSettings->getField("AnalyticsKey")->getValue();

                if (is_null($strDomainName)) {
                    $objElement = $objSettings->getField("AnalyticsDomain");
                    if (is_object($objElement)) {
                        $strDomainName = $objElement->getValue();
                    }
                }
            }
        }

        if (!empty($analyticsKey)) {
            if (empty($strDomainName)) {
                $arrHostname = explode(".", parse_url(Request::getRootURI(), PHP_URL_HOST));
                $intCount = count($arrHostname);
                if ($intCount > 1) {
                    $strDomainName = $arrHostname[$intCount - 2] . "." . $arrHostname[$intCount - 1];
                }
            }

            $strOutput .= "<script>\n";
            $strOutput .= "(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){\n";
            $strOutput .= "(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),\n";
            $strOutput .= "m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)\n";
            $strOutput .= "})(window,document,'script','//www.google-analytics.com/analytics.js','ga');\n";
            $strOutput .= "ga('create', '{$analyticsKey}', '" . $strDomainName . "');\n";
            $strOutput .= "ga('send', 'pageview');\n";
            $strOutput .= "</script>\n";
        }

        return $strOutput;
    }

    private function setAccount($strAccountId)
    {
        global $_CONF;

        $objAccount = Account::getByPunchId($strAccountId);

        self::$__account             = $objAccount;
        $_CONF['app']['account']     = $objAccount;
    }

    private function renderSitemap()
    {
        $objCms = self::getInstance();
        $objCms->setLanguage(ContentLanguage::getDefault());

        $strOutput = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $strOutput .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\" xmlns:xhtml=\"http://www.w3.org/1999/xhtml\">\n";

        //*** Get a collection of Languages.
        $arrEidLanguages = [];
        $objLanguages = $objCms->getLanguages();
        foreach ($objLanguages as $objLanguage) {
            //*** Hompages.
            $strURL = ($objLanguage->default) ? Request::getRootURI() : Request::getRootURI() . "/language/" . $objLanguage->getAbbr();
            $arrEidLanguages[0][$objLanguage->getAbbr()]["url"] = $strURL;

            //*** Render individual page elements.
            $objElements = $objCms->getPageElements($objLanguage->getId());
            foreach ($objElements as $objElement) {
                if (!in_array($objElement->getName(), $this->__sitemapBlacklist)) {
                	$strURL = Request::getRootURI();
	                $strURL .= (!$objLanguage->default) ? $objElement->getLink(true, "", $objLanguage->getAbbr()) : $objElement->getLink(true);

                    $arrEidLanguages[$objElement->getId()][$objLanguage->getAbbr()]["url"] = $strURL;
                    $arrEidLanguages[$objElement->getId()][$objLanguage->getAbbr()]["mod"] = Date::fromMysql("%Y-%m-%d", $objElement->getElement()->getModified());
                }
            }
        }

        foreach ($arrEidLanguages as $arrElements) {
            foreach ($objLanguages as $objLanguage) {
                $strMainLink = "";
                $strSubLinks = "";

                $strAbbr = $objLanguage->getAbbr();
                if (isset($arrElements[$strAbbr])) {
                    $strMainLink = "<loc>" . $arrElements[$strAbbr]["url"] . "</loc>\n";

                    if (isset($arrElements[$strAbbr]["mod"])) {
                        $strMainLink .= "<lastmod>" . $arrElements[$strAbbr]["mod"] . "</lastmod>\n";
                    }
                }

                foreach ($arrElements as $key => $arrElement) {
                    $strSubLinks .= "<xhtml:link rel=\"alternate\" hreflang=\"" . $key . "\" href=\"" . $arrElement["url"] . "\"/>\n";
                }

                if (!empty($strMainLink)) {
                    $strOutput .= "<url>\n";
                    $strOutput .= $strMainLink;
                    $strOutput .= $strSubLinks;
                    $strOutput .= "</url>\n";
                }
            }
        }

        $strOutput .= "</urlset>";

        return $strOutput;
    }
}
