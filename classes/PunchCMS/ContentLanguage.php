<?php

namespace PunchCMS;

use Bili\Request;
use PunchCMS\Client\Client;

/**
 *
 * Handles content language properties and methods.
 * @author felix
 * @version 0.1.0
 *
 */
class ContentLanguage extends \PunchCMS\DBAL\ContentLanguage
{
    public function save($blnSaveModifiedDate = true)
    {
        parent::$object = "\\PunchCMS\\ContentLanguage";
        parent::$table = "pcms_language";

        $intId = $this->getId();

        $blnReturn = parent::save($blnSaveModifiedDate);
        if (class_exists("\\AuditLog")) {
            \AuditLog::addLog(
                AUDIT_TYPE_LANGUAGE,
                $this->getId(),
                $this->getName(),
                (empty($intId)) ? "create" : "edit",
                ($this->getActive()) ? "active" : "inactive"
            );
        }

        return $blnReturn;
    }

    public static function selectByPK($varValue, $arrFields = array(), $accountId = null)
    {
        global $_CONF;
        parent::$object = "\\PunchCMS\\ContentLanguage";
        parent::$table = "pcms_language";

        return parent::selectByPK($varValue, $arrFields, $_CONF['app']['account']->getId());
    }

    public static function selectByAbbr($strAbbr)
    {
        global $_CONF;
        parent::$object = "\\PunchCMS\\ContentLanguage";
        parent::$table = "pcms_language";

        $objReturn = null;

        $strSql = sprintf(
            "SELECT * FROM " . parent::$table . " WHERE abbr = %s AND accountId = '%s' ORDER BY sort",
            self::quote($strAbbr),
            $_CONF['app']['account']->getId()
        );
        $objReturns = self::select($strSql);

        if ($objReturns->count() > 0) {
            $objReturn = $objReturns->current();
        }

        return $objReturn;
    }

    public static function selectActiveLanguages()
    {
        global $_CONF;
        parent::$object = "\\PunchCMS\\ContentLanguage";
        parent::$table = "pcms_language";

        $strSql = sprintf(
            "SELECT * FROM " . parent::$table . " WHERE accountId = '%s' and active = '1' ORDER BY sort",
            $_CONF['app']['account']->getId()
        );

        return parent::select($strSql);
    }

    public static function select($strSql = "")
    {
        global $_CONF;
        parent::$object = "\\PunchCMS\\ContentLanguage";
        parent::$table = "pcms_language";

        if (empty($strSql)) {
            $strSql = sprintf(
                "SELECT * FROM " . parent::$table . " WHERE accountId = '%s' ORDER BY sort",
                $_CONF['app']['account']->getId()
            );
        }

        return parent::select($strSql);
    }

    public static function getDefault()
    {
        global $_CONF;
        self::$object = "\\PunchCMS\\ContentLanguage";
        self::$table = "pcms_language";

        $objReturn = null;

        $strSql = sprintf(
            "SELECT * FROM " . self::$table . " WHERE `default` = '1' AND `accountId` = '%s'",
            $_CONF['app']['account']->getId()
        );
        $objReturns = self::select($strSql);

        if ($objReturns->count() > 0) {
            $objReturn = $objReturns->current();
        }

        return $objReturn;
    }

    public static function setDefault($intId)
    {
        global $_CONF;
        self::$object = "\\PunchCMS\\ContentLanguage";
        self::$table = "pcms_language";

        //*** This could take a while.
        set_time_limit(60 * 60);

        $objReturn = null;

        //*** Adjust all fields who cascade from the default language.
        $strSql = sprintf(
            "SELECT * FROM " . self::$table . " WHERE `default` <> '1' AND `accountId` = '%s'",
            $_CONF['app']['account']->getId()
        );
        $objLanguages = self::select($strSql);

        $objDefaultLang = ContentLanguage::getDefault();
        if (is_object($objDefaultLang)) {
            $strSql = "SELECT pcms_element_field.* FROM
                    `pcms_element`,
                    `pcms_element_field`,
                    `pcms_element_field_bigtext`
                WHERE pcms_element_field.id = pcms_element_field_bigtext.fieldId
                AND pcms_element_field_bigtext.cascade = '1'
                AND pcms_element.id = pcms_element_field.elementId
                AND pcms_element.accountId = '%s'
                UNION
                SELECT pcms_element_field.* FROM
                    `pcms_element`,
                    `pcms_element_field`,
                    `pcms_element_field_date`
                WHERE pcms_element_field.id = pcms_element_field_date.fieldId
                AND pcms_element_field_date.cascade = '1'
                AND pcms_element.id = pcms_element_field.elementId
                AND pcms_element.accountId = '%s'
                UNION
                SELECT pcms_element_field.* FROM
                    `pcms_element`,
                    `pcms_element_field`,
                    `pcms_element_field_number`
                WHERE pcms_element_field.id = pcms_element_field_number.fieldId
                AND pcms_element_field_number.cascade = '1'
                AND pcms_element.id = pcms_element_field.elementId
                AND pcms_element.accountId = '%s'
                UNION
                SELECT pcms_element_field.* FROM
                    `pcms_element`,
                    `pcms_element_field`,
                    `pcms_element_field_text`
                WHERE pcms_element_field.id = pcms_element_field_text.fieldId
                AND pcms_element_field_text.cascade = '1'
                AND pcms_element.id = pcms_element_field.elementId
                AND pcms_element.accountId = '%s'";
            $strSql = sprintf(
                $strSql,
                $_CONF['app']['account']->getId(),
                $_CONF['app']['account']->getId(),
                $_CONF['app']['account']->getId(),
                $_CONF['app']['account']->getId()
            );
            $objFields = ElementField::select($strSql);
            foreach ($objFields as $objField) {
                $strDefaultValue = $objField->getRawValue($objDefaultLang->getId());
                foreach ($objLanguages as $objLanguage) {
                    $objValue = $objField->getValueObject($objLanguage->getId());
                    if (is_object($objValue)) {
                        $objValue->delete(false);
                    }

                    $objValue = $objField->getNewValueObject();
                    $objValue->setValue($strDefaultValue);
                    $objValue->setLanguageId($objLanguage->getId());
                    $objValue->setCascade(false);
                    $objField->setValueObject($objValue);
                }
            }

            //*** Set the new default language.
            $objDefaultLang->default = 0;
            $objDefaultLang->save();
        }

        $objLanguage = self::selectByPK($intId);
        $objLanguage->default = 1;
        $objLanguage->save();

        if (class_exists("\\AuditLog")) {
            \AuditLog::addLog(AUDIT_TYPE_LANGUAGE, $objLanguage->getId(), $objLanguage->getName(), "setdefault");
        }

        return $objReturn;
    }

    public static function hasLanguage($strLanguage)
    {
        global $_CONF;
        self::$object = "\\PunchCMS\\ContentLanguage";
        self::$table = "pcms_language";

        $intReturn = 0;

        $strSql = sprintf(
            "SELECT * FROM " . self::$table . " WHERE `name` = %s AND `accountId` = '%s'",
            self::quote(strtolower($strLanguage)),
            $_CONF['app']['account']->getId()
        );
        $objReturns = self::select($strSql);

        if ($objReturns->count() > 0) {
            $intReturn = $objReturns->current()->getId();
        }

        return $intReturn;
    }

    public function delete($accountId = null)
    {
        global $_CONF;
        self::$object = "\\PunchCMS\\ContentLanguage";
        self::$table = "pcms_language";

        //*** Remove all field values for this language.
        $objElements = Element::select();
        foreach ($objElements as $objElement) {
            $objFields = $objElement->getFields();
            foreach ($objFields as $objField) {
                $objValue = $objField->getValueObject($this->id);
                $objValue->delete();
            }
        }

        //*** Remove all elements linked to this language.
        ElementLanguage::deleteByLanguage($this->getId());

        if (class_exists("\\AuditLog")) {
            \AuditLog::addLog(AUDIT_TYPE_LANGUAGE, $this->getId(), $this->getName(), "delete");
        }

        return parent::delete($accountId);
    }

    public static function sort($intLangId)
    {
        $lastSort = 0;
        $arrItemlist = Request::get("itemlist");

        if (is_array($arrItemlist) && count($arrItemlist) > 0) {
            //*** Loop through the items and manipulate the sort order.
            foreach ($arrItemlist as $value) {
                $lastSort++;
                $objLanguage = ContentLanguage::selectByPK($value);
                $objLanguage->setSort($lastSort);
                $objLanguage->save(false);
            }
        }
    }

    public static function handleLanguage($strLanguageAbbr, $strRewrite)
    {
        $blnChanged         = false;
        $objCms             = Client::getInstance();

        if (!empty($strRewrite)) {
            $strRewrite = rtrim($strRewrite, " \/");
            $arrUrl = explode("/", $strRewrite);
            $intKey = array_search("language", $arrUrl);
            if ($intKey !== false && $intKey < count($arrUrl) - 1) {
                //*** Google friendly language URL.
                $strLanguageAbbr = $arrUrl[$intKey + 1];
                if (isset($_COOKIE["userlanguage_abbr"])
                        && $strLanguageAbbr == $_COOKIE["userlanguage_abbr"]) {
                    $strLanguageAbbr = "";
                }
            }
        }

        if (!empty($strLanguageAbbr)) {
            //*** A language change has been submitted.
            $objLanguage = ContentLanguage::selectByAbbr($strLanguageAbbr);

            if (is_object($objLanguage) && $objLanguage->getActive()) {
                //*** Write to session.
                $_SESSION["userlanguage"] = array();
                $_SESSION["userlanguage"]["id"] = $objLanguage->getId();
                $_SESSION["userlanguage"]["abbr"] = $objLanguage->getAbbr();
                $_SESSION["userlanguage"]["name"] = $objLanguage->getName();
                $_SESSION["userlanguage"]["default"] = $objLanguage->default;

                //*** Get base Url.
                $strBaseHost = $_SERVER["HTTP_HOST"];
                $arrBaseHost = explode(".", $strBaseHost);
                $strBaseHost = array_pop($arrBaseHost);
                $strBaseHost = array_pop($arrBaseHost) . "." . $strBaseHost;

                //*** Write to cookie.
                $intCookieLifetime = (isset($GLOBALS["_CONF"]["app"]["languageCookieLifetime"])) ? $GLOBALS["_CONF"]["app"]["languageCookieLifetime"] : time()+60*60*24*30;
                setcookie("userlanguage", $objLanguage->getId(), $intCookieLifetime, '/', "." . $strBaseHost);
                setcookie("userlanguage_abbr", $objLanguage->getAbbr(), $intCookieLifetime, '/', "." . $strBaseHost);
                setcookie("userlanguage_name", $objLanguage->getName(), $intCookieLifetime, '/', "." . $strBaseHost);
                setcookie("userlanguage_default", $objLanguage->default, $intCookieLifetime, '/', "." . $strBaseHost);

                //*** Set variables.
                $_CONF['app']['language'] = $objLanguage->getId();
                $_CONF['app']['languageAbbr'] = $objLanguage->getAbbr();
                $_CONF['app']['languageName'] = $objLanguage->getName();
                $_CONF['app']['languageDefault'] = $objLanguage->default;

                $blnChanged = true;
            }
        }

        //*** Try to retrieve a stored language id.
        if (!$blnChanged) {
            if (isset($_COOKIE["userlanguage_abbr"])) {
                //*** Test if the language still exists.
                $objTemp = ContentLanguage::selectByAbbr($_COOKIE["userlanguage_abbr"]);

                if (is_object($objTemp)) {
                    //*** Get language from cookie.
                    $_CONF['app']['language'] = $objTemp->getId();
                    $_CONF['app']['languageAbbr'] = $objTemp->getAbbr();
                    $_CONF['app']['languageName'] = $objTemp->getName();
                    $_CONF['app']['languageDefault'] = $objTemp->default;
                } else {
                    //*** Get default language.
                    $objLang = ContentLanguage::getDefault();
                    $_CONF['app']['language'] = $objLang->getId();
                    $_CONF['app']['languageAbbr'] = $objLang->getAbbr();
                    $_CONF['app']['languageName'] = $objLang->getName();
                    $_CONF['app']['languageDefault'] = $objLang->default;
                }
            } elseif (isset($_SESSION["userlanguage"]) && isset($_SESSION["userlanguage"]["abbr"])) {
                //*** Test if the language still exists.
                $objTemp = ContentLanguage::selectByAbbr($_SESSION["userlanguage"]["abbr"]);

                if (is_object($objTemp)) {
                    //*** Get language from session.
                    $_CONF['app']['language'] = $objTemp->getId();
                    $_CONF['app']['languageAbbr'] = $objTemp->getAbbr();
                    $_CONF['app']['languageName'] = $objTemp->getName();
                    $_CONF['app']['languageDefault'] = $objTemp->default;
                } else {
                    //*** Get default language.
                    $objLang = ContentLanguage::getDefault();
                    $_CONF['app']['language'] = $objLang->getId();
                    $_CONF['app']['languageAbbr'] = $objLang->getAbbr();
                    $_CONF['app']['languageName'] = $objLang->getName();
                    $_CONF['app']['languageDefault'] = $objLang->default;
                }
            } else {
                //*** Get default language.
                $objLang = ContentLanguage::getDefault();
                $_CONF['app']['language'] = $objLang->getId();
                $_CONF['app']['languageAbbr'] = $objLang->getAbbr();
                $_CONF['app']['languageName'] = $objLang->getName();
                $_CONF['app']['languageDefault'] = $objLang->default;
            }
        }

        $objTemp = new ContentLanguage();
        $objTemp->setId($_CONF['app']['language']);
        $objTemp->setAbbr($_CONF['app']['languageAbbr']);
        $objTemp->setName($_CONF['app']['languageName']);
        $objTemp->default = $_CONF['app']['languageDefault'];
        $objTemp->setActive(true);

        $objCms->setLanguage($objTemp);

        //*** Check if the current alias is forcing a language switch.
        if (!empty($strRewrite)) {
            $strRewrite = $objCms->cleanRewrite($strRewrite);

            //*** Get aliases for this URL.
            $objUrls = Alias::selectByAlias($strRewrite);
            if (!is_null($objUrls) && $objUrls->count() > 0) {
                //*** Check if the current language is in the list of aliases.
                $blnFoundLanguage = false;
                foreach ($objUrls as $objUrl) {
                    $intLanguage = $objUrl->getLanguageId();
                    if (($intLanguage == 0 || $intLanguage == $objCms->getLanguage()->getId()) && $objUrl->getActive()) {
                        $blnFoundLanguage = true;
                        break;
                    }
                }

                if (!$blnFoundLanguage) {
                    //*** Current language is not valid for this alias.
                    $objUrls->rewind();
                    $objUrl = $objUrls->current();
                    if ($objUrl->getActive()) {
                        $objLanguage = ContentLanguage::selectByPK($objUrl->getLanguageId());
                        if (is_object($objLanguage) && $objLanguage->getActive()) {
                            Request::redirect("/language/" . $objLanguage->getAbbr() . "/" . $objUrl->getAlias());
                        }
                    }
                }
            }
        }
    }
}
