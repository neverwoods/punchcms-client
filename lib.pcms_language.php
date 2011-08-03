<?php

/**************************************************************************
* PunchCMS Language include v0.2.4
* Handles language selection.
**************************************************************************/

$strLanguageAbbr 	= Request::get("lang");
$strRewrite			= Request::get('rewrite');
$blnChanged 		= FALSE;
$objCms 			= PCMS_Client::getInstance();

if (!empty($strRewrite)) {
	$strRewrite = rtrim($strRewrite, " \/");
	$arrUrl = explode("/", $strRewrite);
	$intKey = array_search("language", $arrUrl);
	if ($intKey !== FALSE && $intKey < count($arrUrl) - 1) {
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
		$_SESSION["userlanguage"]["default"] = $objLanguage->default;

		//*** Get base Url.
		$strBaseHost = $_SERVER["HTTP_HOST"];		
		$arrBaseHost = explode(".", $strBaseHost);
		$strBaseHost = array_pop($arrBaseHost);
		$strBaseHost = array_pop($arrBaseHost) . "." . $strBaseHost;
		
		//*** Write to cookie.
		setcookie("userlanguage", $objLanguage->getId(), time()+60*60*24*30, '/', "." . $strBaseHost);
		setcookie("userlanguage_abbr", $objLanguage->getAbbr(), time()+60*60*24*30, '/', "." . $strBaseHost);
		setcookie("userlanguage_default", $objLanguage->default, time()+60*60*24*30, '/', "." . $strBaseHost);
		
		//*** Set variables.		
		$_CONF['app']['language'] = $objLanguage->getId();
		$_CONF['app']['languageAbbr'] = $objLanguage->getAbbr();
		$_CONF['app']['languageDefault'] = $objLanguage->default;
		
		$blnChanged = TRUE;
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
			$_CONF['app']['languageDefault'] = $objTemp->default;
		} else {
			//*** Get default language.
			$objLang = ContentLanguage::getDefault();
			$_CONF['app']['language'] = $objLang->getId();
			$_CONF['app']['languageAbbr'] = $objLang->getAbbr();
			$_CONF['app']['languageDefault'] = $objLang->default;
		}
	} else if (isset($_SESSION["userlanguage"]) && isset($_SESSION["userlanguage"]["abbr"])) {
		//*** Test if the language still exists.
		$objTemp = ContentLanguage::selectByAbbr($_SESSION["userlanguage"]["abbr"]);
			
		if (is_object($objTemp)) {
			//*** Get language from session.
			$_CONF['app']['language'] = $objTemp->getId();
			$_CONF['app']['languageAbbr'] = $objTemp->getAbbr();
			$_CONF['app']['languageDefault'] = $objTemp->default;
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
}

$objTemp = new ContentLanguage();
$objTemp->setId($_CONF['app']['language']);
$objTemp->setAbbr($_CONF['app']['languageAbbr']);
$objTemp->default = $_CONF['app']['languageDefault'];
$objTemp->setActive(TRUE);

$objCms->setLanguage($objTemp);

//*** Check if the current alias is forcing a language switch.
if (!empty($strRewrite)) {
	$strRewrite = $objCms->cleanRewrite($strRewrite);

	$objUrl = Alias::selectByAlias($strRewrite);
	if (is_object($objUrl)) {
		$intLanguage = $objUrl->getLanguageId();
		if ($intLanguage > 0 && $intLanguage != $objCms->getLanguage()->getId() && $objUrl->getActive()) {
			//*** Different language. Do a language redirect.
			$objLanguage = ContentLanguage::selectByPK($intLanguage);
			if (is_object($objLanguage) && $objLanguage->getActive()) {
				Request::redirect("/language/" . $objLanguage->getAbbr() . "/" . $objUrl->getAlias());
			}
		}
	}
}

?>