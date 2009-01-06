<?php

/**************************************************************************
* PunchCMS Language include v0.1.7
* Handles language selection.
**************************************************************************/

$strLanguageAbbr 	= Request::get("lang");
$strRewrite			= Request::get('rewrite');

if (!empty($strRewrite)) {
	$strRewrite = rtrim($strRewrite, " \/");
	$arrUrl = explode("/", $strRewrite);
	$intKey = array_search("language", $arrUrl);
	if ($intKey !== FALSE && $intKey < count($arrUrl) - 1) {
		//*** Google friendly language URL.
		$strLanguageAbbr = $arrUrl[$intKey + 1];
		if (array_key_exists("userlanguage", $_SESSION) 
				&& array_key_exists("abbr", $_SESSION["userlanguage"]) 
				&& $strLanguageAbbr == $_SESSION["userlanguage"]["abbr"]) {
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
		$_SESSION["userlanguage"]["default"] = $objLanguage->default;

		//*** Write to cookie.
		setcookie("userlanguage", $objLanguage->getId(), time()+60*60*24*30, '/');
		setcookie("userlanguage_abbr", $objLanguage->getAbbr(), time()+60*60*24*30, '/');
		setcookie("userlanguage_default", $objLanguage->default, time()+60*60*24*30, '/');
	}
}

//*** Try to retrieve a stored language id.
if (!empty($_SESSION["userlanguage"])) {
	//*** Test if the language still exists.
	$objTemp = ContentLanguage::selectByPk($_SESSION["userlanguage"]["id"]);
		
	if (is_object($objTemp)) {
		//*** Get language from session.
		$_CONF['app']['language'] = $_SESSION["userlanguage"]["id"];
		$_CONF['app']['languageAbbr'] = $_SESSION["userlanguage"]["abbr"];
		$_CONF['app']['languageDefault'] = $_SESSION["userlanguage"]["default"];
	} else {
		//*** Get default language.
		$objLang = ContentLanguage::getDefault();
		$_CONF['app']['language'] = $objLang->getId();
		$_CONF['app']['languageAbbr'] = $objLang->getAbbr();
		$_CONF['app']['languageDefault'] = $objLang->default;
	}
} else if (!empty($_COOKIE["userlanguage"])) {
	//*** Test if the language still exists.
	$objTemp = ContentLanguage::selectByPk($_COOKIE["userlanguage"]);
	
	if (is_object($objTemp)) {
		//*** Get language from cookie.
		$_CONF['app']['language'] = $_COOKIE["userlanguage"];
		$_CONF['app']['languageAbbr'] = $_COOKIE["userlanguage_abbr"];
		$_CONF['app']['languageDefault'] = $_COOKIE["userlanguage_default"];
	} else {
		//*** Get default language.
		$objLang = ContentLanguage::getDefault();
		$_CONF['app']['language'] = $objLang->getId();
		$_CONF['app']['languageAbbr'] = $objLang->getAbbr();
		$_CONF['app']['languageDefault'] = $objLang->default;
	}
} else {
	//*** Get default language.
	$objLang = ContentLanguage::getDefault();
	$_CONF['app']['language'] = $objLang->getId();
	$_CONF['app']['languageAbbr'] = $objLang->getAbbr();
	$_CONF['app']['languageDefault'] = $objLang->default;
}

$objTemp = new ContentLanguage();
$objTemp->setId($_CONF['app']['language']);
$objTemp->setAbbr($_CONF['app']['languageAbbr']);
$objTemp->default = $_CONF['app']['languageDefault'];
$objTemp->setActive(TRUE);

$objCms = PCMS_Client::getInstance();
$objCms->setLanguage($objTemp);

?>