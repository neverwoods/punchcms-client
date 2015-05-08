<?php

use Bili\Request;
use PunchCMS\Client\Client;
use PunchCMS\Alias;
use PunchCMS\ContentLanguage;

/**************************************************************************
* PunchCMS Language include v0.2.5
* Handles language selection.
**************************************************************************/

$strLanguageAbbr     = Request::get("lang");
$strRewrite            = Request::get('rewrite');
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
