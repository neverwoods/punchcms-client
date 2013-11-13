<?php

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
 *
 * Holds the PunchCMS DOM classes.
 * @author felix
 * @version 0.2.88
 *
 */
class PCMS_Client {
	static $__connId 			= NULL;
	static $__account 			= NULL;
	static $__basePath 			= NULL;
	static $__instance 			= NULL;
	static $__dsn 				= "";
	static $__dbUser 			= "";
	static $__dbPassword		= "";
	static $__language			= NULL;
	static $__cacheConfig		= array();
	private $__aliases			= FALSE;
	private $__cacheFields		= FALSE;
	private $__cachedFields		= array();
	private $__defaultLanguage	= NULL;
	private $__languages		= NULL;

	private function __construct($strDSN = "", $strUsername = "", $strPassword = "") {
		/* Private constructor to insure singleton behaviour */

		if (!empty($strDSN)) {
			$this::$__dsn = $strDSN;
			$this::$__dbUser = $strUsername;
			$this::$__dbPassword = $strPassword;
		}

		$this->setDbConnection();
	}

	public static function singleton($strDSN, $strUsername, $strPassword, $strAccountId, $strBasePath) {
		/* Method to initially instanciate the class */

		PCMS_Client::$__instance = new PCMS_Client($strDSN, $strUsername, $strPassword);
		PCMS_Client::$__instance->setAccount($strAccountId);
		PCMS_Client::$__instance->setBasePath($strBasePath);

		//*** Caching.
		$cacheLiteConfig = array(
			'caching' => (PCMS_Client::$__instance->getSetting('caching_enable')) ? TRUE : FALSE,
			'cacheDir' => $strBasePath . PCMS_Client::$__instance->getSetting('caching_folder'),
			'lifeTime' => PCMS_Client::$__instance->getSetting('caching_timeout') * 60,
			'fileNameProtection' => FALSE
		);
		PCMS_Client::$__instance->setCacheConfig($cacheLiteConfig);

		return PCMS_Client::$__instance;
	}

	/**
	 * Return a singleton instance of the PCMS_Client
	 *
	 * @return PCMS_Client Singleton instance of PCMS_Client
	 */
	public static function getInstance() {
		/* Get the singleton instance for this class */

		if (is_null(PCMS_Client::$__instance)) {
			PCMS_Client::$__instance = new PCMS_Client();
		}

		return PCMS_Client::$__instance;
	}

	public function get($strName = "", $blnRecursive = FALSE) {
		$objReturn = new __Elements();

		if (!empty($strName)) {
			$objReturn = __Elements::getElements($strName, 0, TRUE, $blnRecursive);
		}

		return $objReturn;
	}

	public function getElements($strName = "", $blnGetOne = FALSE, $blnRecursive = FALSE) {
		$objReturn = new __Elements();

		if (!empty($strName)) {
			$objReturn = __Elements::getElements($strName, 0, $blnGetOne, $blnRecursive);
		}

		return $objReturn;
	}

	public function getFolders($strName = "", $blnGetOne = FALSE) {
		$objReturn = __Elements::getFolders($strName, 0, $blnGetOne);

		return $objReturn;
	}

	public function getPageElements($intLanguage = NULL) {
		$objReturn = new __Elements();

		if (is_null($intLanguage)) $intLanguage = $this->getLanguage()->getId();

		//*** Get individual page elements.
		$strSql = "SELECT pcms_element.*
					FROM pcms_element
					RIGHT JOIN pcms_element_language
					ON pcms_element.id = pcms_element_language.elementId
					INNER JOIN pcms_template ON pcms_element.templateId = pcms_template.id
					WHERE pcms_element_language.languageId = '%s'
					AND pcms_element.accountId = '%s'
					AND pcms_element.active = '1'
					AND pcms_template.isPage = '1'";
		$objElements = Element::select(sprintf($strSql, $intLanguage, $this->getAccount()->getId()));
		foreach ($objElements as $objElement) {
			$objCMSElement = new __Element($objElement);
			$objReturn->addObject($objCMSElement);
		}

		return $objReturn;
	}

	public function getElementById($intId) {
		$objReturn 	= NULL;
		$intId 		= (int)$intId; // Mandatory to prevent SQL injection

		if ($intId > 0) {
			$strSql = "SELECT pcms_element.* FROM pcms_element, pcms_element_schedule
					WHERE pcms_element.id = '%s'
					AND pcms_element.active = '1'
					AND pcms_element.accountId = '%s'
					AND pcms_element.id = pcms_element_schedule.elementId
					AND pcms_element_schedule.startDate <= '%s'
					AND pcms_element_schedule.endDate >= '%s'
					ORDER BY pcms_element.sort";
			$objElements = Element::select(sprintf($strSql, DBA__Object::escape($intId), PCMS_Client::getAccount()->getId(), __Elements::toMysql(), __Elements::toMysql()));
			if ($objElements->count() > 0) {
				$objReturn = new __Element($objElements->current());
			}
		}

		return $objReturn;
	}

	public function getFieldById($intId) {
		$objReturn = NULL;
		$intId = (int)$intId;

		if ($intId > 0) {
			$strSql = "SELECT pcms_element_field.* FROM pcms_element_field, pcms_element, pcms_element_schedule
					WHERE pcms_element_field.id = '%s'
					AND pcms_element.id = pcms_element_field.elementId
					AND pcms_element.active = '1'
					AND pcms_element.accountId = '%s'
					AND pcms_element.id = pcms_element_schedule.elementId
					AND pcms_element_schedule.startDate <= '%s'
					AND pcms_element_schedule.endDate >= '%s'
					ORDER BY pcms_element_field.sort";
			$objFields = ElementField::select(sprintf($strSql, DBA__Object::escape($intId), PCMS_Client::getAccount()->getId(), __Elements::toMysql(), __Elements::toMysql()));
			if ($objFields->count() > 0) {
				$objField = $objFields->current();
				$objReturn = new __ElementField($objField->getElementId(), TemplateField::selectByPk($objField->getTemplateFieldId()));
			}
		}

		return $objReturn;
	}

	public function getMediaById($intId) {
		$objReturn = NULL;
		$intId = (int)$intId;

		if ($intId > 0) {
			$objReturn = StorageItem::selectByPk(DBA__Object::escape($intId));
		}

		return $objReturn;
	}

	/**
	 * Get an element by template name.
	 *
	 * @param string $strName
	 * @param boolean $blnRecursive
	 * @param boolean $blnRandom
	 * @return __Element Instance of __Element
	 */
	public function getElementByTemplate($strName, $blnRecursive = FALSE, $blnRandom = FALSE) {
		$objReturn = new __Elements();

		if (!empty($strName)) {
			$objReturn = __Elements::getElementsByTemplate($strName, 0, TRUE, $blnRecursive, $blnRandom);
		}

		return $objReturn;
	}

	public function getElementsByTemplate($strName, $blnGetOne = FALSE, $blnRecursive = FALSE, $blnRandom = FALSE) {
		$objReturn = new __Elements();

		if (!empty($strName)) {
			$objReturn = __Elements::getElementsByTemplate($strName, 0, $blnGetOne, $blnRecursive, $blnRandom);
		}

		return $objReturn;
	}

	public function getElementByTemplateId($intId, $blnRecursive = FALSE, $blnRandom = FALSE) {
		$objReturn = new __Elements();

		if (!empty($intId)) {
			$objReturn = __Elements::getElementsByTemplateId($intId, 0, TRUE, $blnRecursive, $blnRandom);
		}

		return $objReturn;
	}

	public function getElementsByTemplateId($intId, $blnGetOne = FALSE, $blnRecursive = FALSE, $blnRandom = FALSE) {
		$objReturn = new __Elements();

		if (!empty($intId)) {
			$objReturn = __Elements::getElementsByTemplateId($intId, 0, $blnGetOne, $blnRecursive, $blnRandom);
		}

		return $objReturn;
	}

	public function getElementsFromParent($intId, $blnGetOne = FALSE, $blnRecursive = FALSE) {
		$objReturn = new __Elements();

		$objReturn = __Elements::getElements("", $intId, $blnGetOne, $blnRecursive);

		return $objReturn;
	}

	public function getAliasId() {
		$intReturn = 0;

		if ($this->usesAliases()) {
			$strRewrite	= Request::get('rewrite');

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
						} else if (strtolower(substr($strRewrite, 0, 15)) == "download/media/") {
							//*** Google friendly media URL.
							$arrMediaPath = explode("/", substr($strRewrite, 15));
							$blnInline = (count($arrMediaPath) > 1 && $arrMediaPath[1] == "inline") ? TRUE : FALSE;
							$strMediaId = $arrMediaPath[0];
							if (is_numeric($strMediaId)) {
								$this->downloadMediaItem($strMediaId, $blnInline);
								exit;
								break;
							}
						} else if (strtolower(substr($strRewrite, 0, 9)) == "download/") {
							//*** Google friendly element field URL.
							$arrMediaPath = explode("/", substr($strRewrite, 9));
							$blnInline = (count($arrMediaPath) > 1 && $arrMediaPath[1] == "inline") ? TRUE : FALSE;
							$arrField = explode("_", $arrMediaPath[0]);
							if (is_numeric($arrField[0])) {
								$intIndex = (count($arrField) > 1) ? $arrField[1] : 0;
								$this->downloadElementField($arrField[0], $intIndex, "", $blnInline);
								exit;
								break;
							}
						} else if (stristr($strRewrite, "/eid/") !== FALSE) {
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
				}
			}
		}

		return $intReturn;
	}

	public function cleanRewrite($strRewrite) {
		//*** Strip off any page parameters.
		if (mb_strpos($strRewrite, "__page") !== FALSE) {
			$strRewrite = substr($strRewrite, 0, mb_strpos($strRewrite, "__page") - 1);
		}

		//*** Strip of any language parameters.
		$arrUrl = explode("/", $strRewrite);
		$intKey = array_search("language", $arrUrl);
		if ($intKey !== FALSE) {
			if ($intKey < count($arrUrl) - 2) {
				array_shift($arrUrl);
				array_shift($arrUrl);
				$strRewrite = implode("/", $arrUrl);
			} else {
				$strRewrite = "";
			}
		}

		//*** Sanitize rewrite string.
		$strRewrite = addslashes($strRewrite);

		return $strRewrite;
	}

	public function getCurrentPage() {
		$intPage = 1;
		$strRewrite	= Request::get('rewrite');
		if (!empty($strRewrite) && mb_strpos($strRewrite, "__page") !== FALSE) {
			$strRewrite = rtrim($strRewrite, " \/");
			$arrParams = explode("/", $strRewrite);
			$intPage = array_pop($arrParams);

			if ($intPage < 1) $intPage = 1;
		}

		return $intPage;
	}

	public function getHeaderJS() {
		$strOutput = "";

		$strAnalytics = $this->renderAnalytics();
		if (!empty($strAnalytics)) {
			$strOutput = "<script type=\"text/javascript\">\n// <![CDATA[\n";
			$strOutput .= "$(function(){ $('a').each(function(){ var link = $(this).attr('href'); if (link.match(/^(\/download\/)/)) { $(this).bind('click', function() { pageTracker._trackPageview(link); }); }}) });";
			$strOutput .= "\n// ]]>\n</script>";
		}

		return $strOutput;
	}

	public function downloadElementField($fieldId, $intIndex = 0, $strSettingName = "", $blnInline = FALSE) {
		$blnError = FALSE;

		$objElementField = $this->getFieldById($fieldId);
		if (is_object($objElementField)) {
			if (!empty($strSettingName)) {
				$objImages = $objElementField->getValue(VALUE_IMAGES);
				if ($objImages->count() > $intIndex) $objImages->seek($intIndex);

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
				$blnError = TRUE;
			}
		} else {
			$blnError = TRUE;
		}

		if ($blnError === TRUE) {
			echo $this->getErrorHtml("Downloader", "Sorry, File not found", "<p>Unfortunatly we were unable to find the file you requested.</p>\n<p>Please inform the administrator of this website to prevent future problems.</p>");
			exit;
		}
	}

	public function downloadMediaItem($intId, $blnInline = FALSE) {
		$blnError = FALSE;

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
				$blnError = TRUE;
			}
		} else {
			$blnError = TRUE;
		}

		if ($blnError === TRUE) {
			echo $this->getErrorHtml("Media Downloader", "Sorry, File not found", "<p>Unfortunatly we were unable to find the file you requested.</p>\n<p>Please inform the administrator of this website to prevent future problems.</p>");
			exit;
		}
	}

	public function getErrorHtml($strTitle, $strHeader, $strBody) {
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

	public function find($strQuery, $arrFilters = array(), $blnExact = FALSE, $arrAllowedTypes = array()) {
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
	 * @param __Element $objForm
	 * @return string The form if invalid and otherwise the "thank you" message.
	 */
	public function buildForm($objForm) {
		$objValidForm = new PCMS_FormBuilder($objForm);
		return $objValidForm->buildForm();
	}

	public function useAliases($blnValue) {
		$this->__aliases = $blnValue;
	}

	public function usesAliases() {
		return $this->__aliases;
	}

	public function setCacheFields($blnValue) {
		$this->__cacheFields = $blnValue;
	}

	public function getCacheFields() {
		return $this->__cacheFields;
	}

	public function getLanguages() {
		return ContentLanguage::selectActiveLanguages();
	}

	public function getLanguageArray() {
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

	public function getLanguage() {
		return $this::$__language;
	}

	public function getDefaultLanguage() {
		if (is_null($this->__defaultLanguage)) {
			$this->__defaultLanguage = ContentLanguage::getDefault()->getId();
		}

		return $this->__defaultLanguage;
	}

	public function setLanguage($objValue) {
		$this::$__language = $objValue;
	}

	public function getCacheConfig() {
		return $this::$__cacheConfig;
	}

	public function setCacheConfig($arrValue) {
		$this::$__cacheConfig = $arrValue;
	}

	public function setBasePath($strValue) {
		$this::$__basePath = $strValue;
	}

	public function getBasePath() {
		return $this::$__basePath;
	}

	public function getDownloadPath() {
		if ($this->usesAliases()) {
			return "/download/";
		} else {
			return $this->getSetting("file_download");
		}
	}

	public function getFilePath() {
		return $this->getSetting("file_folder");
	}

	public function getCachedFields($intElementId) {
		$objReturn = NULL;

		if (array_key_exists($intElementId, $this->__cachedFields)) {
			$objReturn = $this->__cachedFields[$intElementId];
		}

		return $objReturn;
	}

	public function setCachedFields($intElementId, $objFields) {
		$this->__cachedFields[$intElementId] = $objFields;
	}

	/**
	 * Get the connection object for the CMS.
	 *
	 * @param string $blnReInit
	 * @return PDO
	 */
	public static function getConn($blnReInit = FALSE) {
		$objCms = PCMS_Client::getInstance();

		if ($blnReInit) {
			//*** Reset the connection. Could have been set by external scripts.
			$objCms->setDbConnection(TRUE);
		}

		return $objCms::$__connId;
	}

	public static function getFromCache($strMethod, $intElementId, $varArguments = NULL, $intUniqueId = NULL, $intLifetime = NULL) {
		/* Cache output of methods
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
		 */

		$strReturn = "";

		$objCms = PCMS_Client::getInstance();

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

		if (!is_null($intUniqueId)) $intElementId = $intUniqueId . "_" . $intElementId;
		$strId = (strlen($strArguments) > 0) ? $strPlainMethod . "_" . $intElementId . $strArguments . "_{$intLangId}" : $strPlainMethod . "_" . $intElementId . "_{$intLangId}";

		//*** Get configuration and override if apllicable.
		$arrConfig = $objCms->getCacheConfig();
		if (!is_null($intLifetime) && is_int($intLifetime)) {
			$arrConfig["lifeTime"] = $intLifetime;
		}

		$objCache = new Cache_Lite($arrConfig);
		if ($strReturn = $objCache->get($strId)) {
			//*** Cache hit, unserialize.
			$strUnserialized = @unserialize($strReturn);
			if ($strUnserialized !== FALSE) $strReturn = $strUnserialized;
		} else {
			if (is_callable($strMethod)) {
				$strReturn = call_user_func_array($strMethod, $arrArguments);
				$strCache = (is_object($strReturn) || is_array($strReturn)) ? serialize($strReturn) : $strReturn;
				if (!empty($strCache)) {
					$objCache->save($strCache, $strId);
				}
			}
		}

		return $strReturn;
	}

	public function getSetting($strValue) {
		return Setting::getValueByName($strValue, $this::$__account->getId());
	}

	public static function getAccount() {
		$objCms = PCMS_Client::getInstance();
		return $objCms::$__account;
	}

	public function setDbConnection($blnReInit = FALSE) {
		try {
			$objConnID = new PDO($this::$__dsn, $this::$__dbUser, $this::$__dbPassword, array(
			    PDO::ATTR_PERSISTENT => true
			));
		} catch (PDOException $e) {
			throw new Exception('Database connection failed: ' . $e->getMessage(), SQL_CONN_ERROR);
		}

		$this::$__connId = $objConnID;
	}

	public function renderAnalytics($analyticsKey = NULL) {
		$strOutput = "";

		if (is_null($analyticsKey)) {
			$objCms = PCMS_Client::getInstance();
			$objSettings = $objCms->getElementByTemplate("GlobalFields");
			if (is_object($objSettings)) $analyticsKey = $objSettings->getField("AnalyticsKey")->getValue();
		}

		if (!empty($analyticsKey)) {
			$strOutput .= "<script type=\"text/javascript\">\n";
			$strOutput .= "var _gaq = _gaq || [];\n";
			$strOutput .= "_gaq.push(['_setAccount', '{$analyticsKey}']);\n";
			$strOutput .= "_gaq.push(['_trackPageview', location.pathname + location.search + location.hash]);\n";
			$strOutput .= "(function() {\n";
			$strOutput .= "var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;\n";
			$strOutput .= "ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';\n";
			$strOutput .= "var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);\n";
			$strOutput .= "})();\n";
			$strOutput .= "</script>\n";
		}

		return $strOutput;
	}

	private function setAccount($strAccountId) {
		global $_CONF;

		$objAccount = Account::getByPunchId($strAccountId);

		$this::$__account 			= $objAccount;
		$_CONF['app']['account'] 	= $objAccount;
	}

	private function renderSitemap() {
		$objCms = PCMS_Client::getInstance();
		$objCms->setLanguage(ContentLanguage::getDefault());

		$strOutput = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
		$strOutput .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";

		//*** Get a collection of Languages.
		$objLanguages = $objCms->getLanguages();
		foreach ($objLanguages as $objLanguage) {
			//*** Alway show the RootUri of the language
			$strURL = ($objLanguage->default) ? Request::getRootURI() : Request::getRootURI() . "/language/" . $objLanguage->getAbbr();
			$strOutput .= "  <url>\n";
			$strOutput .= "    <loc>{$strURL}</loc>\n";
			$strOutput .= "  </url>\n";

			//*** Render individual page elements.
			$objElements = $objCms->getPageElements($objLanguage->getId());
			foreach ($objElements as $objElement) {
				$strURL = Request::getRootURI();
				$strURL .= (!$objLanguage->default) ? $objElement->getLink(TRUE, "", $objLanguage->getAbbr()) : $objElement->getLink(TRUE);

				$strOutput .= "  <url>\n";
				$strOutput .= "    <loc>" . $strURL . "</loc>\n";
				$strOutput .= "    <lastmod>" . Date::fromMysql("%Y-%m-%d", $objElement->getElement()->getModified()) . "</lastmod>\n";
				$strOutput .= "  </url>\n";
			}
		}

		$strOutput .= "</urlset>\n";

		return $strOutput;
	}

}

class __Elements extends DBA__Collection {

	public static function getElements($varName, $intParentId, $blnGetOne = FALSE, $blnRecursive = FALSE) {
		$objCms = PCMS_Client::getInstance();

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
			$strSql = sprintf($strSql, $intParentId, implode("','", $varName), PCMS_Client::getAccount()->getId(), $objCms->getLanguage()->getId(), self::toMysql(), self::toMysql());
		} else {
			$strSql = sprintf($strSql, $intParentId, PCMS_Client::getAccount()->getId(), $objCms->getLanguage()->getId(), self::toMysql(), self::toMysql());
		}
		$objElements = Element::select($strSql);

		if ($blnGetOne) {
			if ($objElements->count() > 0) {
				$objElement = new __Element($objElements->current());
				return $objElement;
			} else {
				$objReturn = NULL;
			}
		} else {
			$objReturn = new __Elements;
		}

		foreach ($objElements as $objElement) {
			$objReturn->addObject(new __Element($objElement));

			if ($blnRecursive === TRUE) {
				$objChilds = __Elements::getElements($varName, $objElement->getId(), $blnGetOne, $blnRecursive);

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
		if ($blnRecursive === TRUE) {
			$strSql = "SELECT pcms_element.* FROM pcms_element, pcms_element_schedule
					WHERE pcms_element.parentId = '%s'
					AND pcms_element.active = '1'
					AND pcms_element.apiName NOT IN ('%s')
					AND pcms_element.accountId = '%s'
					AND pcms_element.id = pcms_element_schedule.elementId
					AND pcms_element_schedule.startDate <= '%s'
					AND pcms_element_schedule.endDate >= '%s'
					ORDER BY pcms_element.sort";
			$objElements = Element::select(sprintf($strSql, $intParentId, implode("','", $varName), PCMS_Client::getAccount()->getId(), self::toMysql(), self::toMysql()));

			foreach ($objElements as $objElement) {
				$objChilds = __Elements::getElements($varName, $objElement->getId(), $blnGetOne, $blnRecursive);

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

	public static function getElementsByTemplate($varName, $intParentId, $blnGetOne = FALSE, $blnRecursive = FALSE, $blnRandom = FALSE) {
		$objCms = PCMS_Client::getInstance();

		if (!is_array($varName)) {
			if (empty($varName)) {
				$varName = array();
			} else {
				$varName = explode(",", $varName);
			}
		}

		if ($blnRecursive === TRUE) {
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
			$objElements = Element::select(sprintf($strSql, $intParentId, PCMS_Client::getAccount()->getId(), $objCms->getLanguage()->getId(), self::toMysql(), self::toMysql()));

			$objReturn = new __Elements;

			foreach ($objElements as $objElement) {
				$objTemplate = Template::selectByPK($objElement->getTemplateId());

				if (is_object($objTemplate) && in_array($objTemplate->getApiName(), $varName)) {
					$objReturn->addObject(new __Element($objElement));
				}

				if ($blnGetOne && !$blnRandom && $objReturn->count() > 0) {
					return $objReturn->current();
				}

				$objChilds = __Elements::getElementsByTemplate($varName, $objElement->getId(), $blnGetOne, TRUE);

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
			$objElements = Element::select(sprintf($strSql, $intParentId, implode("','", $varName), PCMS_Client::getAccount()->getId(), $objCms->getLanguage()->getId(), self::toMysql(), self::toMysql()));

			if ($blnGetOne && !$blnRandom) {
				if ($objElements->count() > 0) {
					$objReturn = new __Element($objElements->current());
				} else {
					$objReturn = NULL;
				}
				return $objReturn;
			}

			$objReturn = new __Elements;

			foreach ($objElements as $objElement) {
				$objReturn->addObject(new __Element($objElement));
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

	public static function getElementsByTemplateO($varName, $intParentId, $strFieldName, $strOrder = "asc") {
		$objCms = PCMS_Client::getInstance();

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
		$strSql = sprintf($strSql, $intParentId, implode("','", $varName), PCMS_Client::getAccount()->getId(), $objCms->getLanguage()->getId(), self::toMysql(), self::toMysql(), $strType, $strFieldName, $strOrder);
		$objElements = Element::select($strSql);

		$objReturn = new __Elements;

		foreach ($objElements as $objElement) {
			$objReturn->addObject(new __Element($objElement));
		}

		return $objReturn;
	}

	public static function getElementsByTemplateId($intId, $intParentId, $blnGetOne = FALSE, $blnRecursive = FALSE, $blnRandom = FALSE) {
		$objCms = PCMS_Client::getInstance();

		if ($blnRecursive === TRUE) {
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
			$objElements = Element::select(sprintf($strSql, $intParentId, PCMS_Client::getAccount()->getId(), $objCms->getLanguage()->getId(), self::toMysql(), self::toMysql()));

			$objReturn = new __Elements;

			foreach ($objElements as $objElement) {
				$objTemplate = Template::selectByPK($objElement->getTemplateId());

				if ($objElement->getTemplateId() == $intId) {
					$objReturn->addObject(new __Element($objElement));
				}

				if ($blnGetOne && !$blnRandom && $objReturn->count() > 0) {
					return $objReturn->current();
				}

				$objChilds = __Elements::getElementsByTemplateId($intId, $objElement->getId(), $blnGetOne, TRUE, $blnRandom);

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
			$objElements = Element::select(sprintf($strSql, $intParentId, $intId, PCMS_Client::getAccount()->getId(), $objCms->getLanguage()->getId(), self::toMysql(), self::toMysql()));

			if ($blnGetOne && !$blnRandom) {
				if ($objElements->count() > 0) {
					$objReturn = new __Element($objElements->current());
				} else {
					$objReturn = NULL;
				}
				return $objReturn;
			}

			$objReturn = new __Elements;

			foreach ($objElements as $objElement) {
				$objReturn->addObject(new __Element($objElement));
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

	public static function getFolders($strName = "", $intParentId = 0, $blnGetOne = FALSE) {
		$objCms = PCMS_Client::getInstance();

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
			$objElements = Element::select(sprintf($strSql, $intParentId, ELM_TYPE_FOLDER, PCMS_Client::getAccount()->getId(), $objCms->getLanguage()->getId(), self::toMysql(), self::toMysql()));
		} else {
			$objElements = Element::select(sprintf($strSql, $intParentId, ELM_TYPE_FOLDER, $strName, PCMS_Client::getAccount()->getId(), $objCms->getLanguage()->getId(), self::toMysql(), self::toMysql()));
		}

		if ($blnGetOne) {
			if ($objElements->count() > 0) {
				$objElement = new __Element($objElements->current());
				return $objElement;
			} else {
				$objReturn = new __Element();
			}
		} else {
			$objReturn = new __Elements;
		}

		foreach ($objElements as $objElement) {
			$objReturn->addObject(new __Element($objElement));
		}

		return $objReturn;
	}

	public static function toMysql() {
		$strReturn = "0000-00-00 00:00:00";
		$strFormat = "%Y-%m-%d %H:%M:%S";

		$strTStamp = strtotime("now");

		if ($strTStamp !== -1 || $strTStamp !== FALSE) {
			$strReturn = strftime($strFormat, $strTStamp);
		}

		return $strReturn;
	}

	public function orderByField($strFieldName, $strOrder = "asc") {
    	//*** Order the collection on a given field name [asc]ending or [desc]ending.

		for ($i = 0; $i < count($this->collection); $i++) {
			for ($j = 0; $j < count($this->collection) - $i - 1; $j++) {
				$objLeft = $this->collection[$j + 1]->getField($strFieldName);
				$objRight = $this->collection[$j]->getField($strFieldName);

				if (!is_object($objLeft)) {
					$objLeft = $this->collection[$j + 1]->getVirtual();
					if (is_object($objLeft)) $objLeft = $objLeft->getField($strFieldName);
				}

				if (!is_object($objRight)) {
					$objRight = $this->collection[$j]->getVirtual();
					if (is_object($objRight)) $objRight = $objRight->getField($strFieldName);
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

	public function normalize() {
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

    public function getArray($apiNames = false, $linkSelf = false, $recursive = false){
        $aReturn = array();
        foreach($this as $objElement){
            $aReturn[] = $objElement->getArray($apiNames, $linkSelf, $recursive);
        }
        return $aReturn;
    }

}

class __Element {
	private $objElementCollection;
	private $objFieldCollection;
	private $objElement;
	private $objMetadata;
	private $id;
	private $apiName;
	public $isPage;
	public $templateApiName;
	public $created;

	public function __construct($objElement = NULL) {
		if (is_object($objElement)) {
			$objCms = PCMS_Client::getInstance();

			$this->objElement = $objElement;

			$objTemplate = Template::selectByPK($objElement->getTemplateId());
			if (is_object($objTemplate)) {
				$this->isPage = $objTemplate->getIsPage();
				$this->templateApiName = $objTemplate->getApiName();
			} else {
				$this->isPage = $objElement->getIsPage();
			}

			if ($objCms->getCacheFields()) {
				$this->objFieldCollection = __ElementFields::getCachedFields($this->objElement->getId());
			}

			$this->created = $objElement->getCreated();
		}
	}

	public function getElement() {
		return $this->objElement;
	}

	public function getTemplateName() {
		return $this->templateApiName;
	}

	public function isPage() {
		return $this->isPage;
	}

	public function getName() {
		$strReturn = "";

		if (is_object($this->objElement)) {
			$strReturn = $this->objElement->getApiName();
		}

		return $strReturn;
	}

	public function getId() {
		$intReturn = NULL;

		if (is_object($this->objElement)) {
			$intReturn = $this->objElement->getId();
		}

		return $intReturn;
	}

	public function get($strName = "", $blnRecursive = FALSE) {
		$objReturn = __Elements::getElements($strName, $this->objElement->getId(), TRUE, $blnRecursive);

		return $objReturn;
	}

	public function getElements($strName = "", $blnGetOne = FALSE, $blnRecursive = FALSE) {
		$objReturn = __Elements::getElements($strName, $this->objElement->getId(), $blnGetOne, $blnRecursive);

		return $objReturn;
	}

	public function getElementByTemplate($strName, $blnRecursive = FALSE, $blnRandom = FALSE) {
		$objReturn = NULL;

		if (!empty($strName)) {
			$objReturn = __Elements::getElementsByTemplate($strName, $this->objElement->getId(), TRUE, $blnRecursive, $blnRandom);
		}

		return $objReturn;
	}

	public function getElementsByTemplate($strName, $blnGetOne = FALSE, $blnRecursive = FALSE, $blnRandom = FALSE) {
		$objReturn = new __Elements();

		if (!empty($strName)) {
			$objReturn = __Elements::getElementsByTemplate($strName, $this->objElement->getId(), $blnGetOne, $blnRecursive, $blnRandom);
		}

		return $objReturn;
	}

	public function getElementsByTemplateO($strName, $strFieldName, $strOrder = "asc") {
		$objReturn = new __Elements();

		if (!empty($strName)) {
			$objReturn = __Elements::getElementsByTemplateO($strName, $this->objElement->getId(), $strFieldName, $strOrder);
		}

		return $objReturn;
	}

	public function getElementByTemplateId($intId, $blnRecursive = FALSE, $blnRandom = FALSE) {
		$objReturn = NULL;

		if (!empty($intId)) {
			$objReturn = __Elements::getElementsByTemplateId($intId, $this->objElement->getId(), TRUE, $blnRecursive, $blnRandom);
		}

		return $objReturn;
	}

	public function getElementsByTemplateId($intId, $blnGetOne = FALSE, $blnRecursive = FALSE, $blnRandom = FALSE) {
		$objReturn = new __Elements();

		if (!empty($intId)) {
			$objReturn = __Elements::getElementsByTemplateId($intId, $this->objElement->getId(), $blnGetOne, $blnRecursive, $blnRandom);
		}

		return $objReturn;
	}

	public function getFolders($strName = "", $blnGetOne = FALSE) {
		$objReturn = __Elements::getFolders($strName, $this->objElement->getId(), $blnGetOne);

		return $objReturn;
	}

	public function getFields() {
		$objCms = PCMS_Client::getInstance();

		if (!is_object($this->objFieldCollection)) {
			if ($objCms->getCacheFields()) {
				$this->objFieldCollection = __ElementFields::getCachedFields($this->objElement->getId());
			} else {
				$this->objFieldCollection = __ElementFields::getFields($this->objElement->getId());
			}
		}

		return $this->objFieldCollection;
	}

	public function getArray($apiNames = false, $selfLink = false, $recursive = false) {
		$objCms = PCMS_Client::getInstance();
        $aReturn = array();

        $aReturn['template'] = $this->getTemplateName();
        $aReturn['eid'] = $this->getId();


        if($this->getTemplateName() == 'Form')
        {
            // get HTML form content
            $aReturn['html'] = $objCms->buildForm($this);
        }
        else if($recursive)
        {
            $objChildren = $this->getElements();
            foreach($objChildren as $objChild)
            {
                $aChild['template'] = $objChild->getTemplateName();
                $aChild['eid'] = $objChild->getId();
                $aChild = $objChild->getArray($apiNames, $selfLink, $recursive);
                $aReturn['children'][] = $aChild;
            }
        }

		$arrLanguages = $objCms->getLanguageArray();
		$intCurrentLanguage = $objCms->getLanguage()->getId();
		$intDefaultLanguage = $objCms->getDefaultLanguage();
		$blnCascade = FALSE;

		$objFields = $this->getFields();
        foreach($objFields as $objField){
            if(($apiNames === false || $apiNames === NULL) || (in_array($objField->getApiName(),$apiNames) || $apiNames == $objField->getApiName())){
                if ($objCms->getCacheFields() && count($arrLanguages) > 1) {
                    if ($objField->getLanguageId() == $intCurrentLanguage) {
                        if (!$objField->getCascade()) {
                            $aReturn[$objField->getApiName()] = $objField->getAutoValue();
                        } else {
                            $blnCascade = TRUE;
                        }
                    } else if ($objField->getLanguageId() == $intDefaultLanguage && $blnCascade) {
                        $aReturn[$objField->getApiName()] = $objField->getAutoValue();
                    }
                } else {
                    $aReturn[$objField->getApiName()] = $objField->getAutoValue();
                }

            }
        }

        if($selfLink){
            $aReturn['self'] = $this->getLink();
        }
        return $aReturn;
	}

	public function getMetadata() {
		$objCms = PCMS_Client::getInstance();

		$objReturn = NULL;

		if (is_object($this->objMetadata) && is_object($this->objMetadata->current()) && $this->objMetadata->count() > 0 && $this->objMetadata->current()->getLanguageId() == $objCms->getLanguage()->getId()) {
			$objReturn = $this->objMetadata;
		} else {
			if ($this->isPage() && is_object($this->objElement)) {
				$objReturn = $this->objElement->getMeta($objCms->getLanguage()->getId());
				if (is_object($objReturn)) $this->objMetadata = $objReturn;
			}
		}
		return $objReturn;
	}

	public function getPageTitle($strAlternative = "") {
		$strReturn = $strAlternative;

		$objMeta = $this->getMetadata();
		if (is_object($objMeta)) {
			$strValue = $objMeta->getValueByValue("name", "title");
			if (!empty($strValue)) $strReturn = $strValue;
		}

		return $strReturn;
	}

	public function getPageKeywords($strAlternative = "") {
		$strReturn = $strAlternative;

		$objMeta = $this->getMetadata();
		if (is_object($objMeta)) {
			$strValue = $objMeta->getValueByValue("name", "keywords");
			if (!empty($strValue)) $strReturn = $strValue;
		}

		return $strReturn;
	}

	public function getPageDescription($strAlternative = "") {
		$strReturn = $strAlternative;

		$objMeta = $this->getMetadata();
		if (is_object($objMeta)) {
			$strValue = $objMeta->getValueByValue("name", "description");
			if (!empty($strValue)) $strReturn = $strValue;
		}

		return $strReturn;
	}

	public function getPageByChild($objChild, $intPageItems, $blnChildType = TRUE) {
		$intReturn = 1;

		if (!is_object($objChild)) {
			$objCms = PCMS_Client::getInstance();
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

	public function getField($strName) {
		$objCms = PCMS_Client::getInstance();
		$objReturn = NULL;

		$arrLanguages = $objCms->getLanguageArray();
		$intCurrentLanguage = $objCms->getLanguage()->getId();
		$intDefaultLanguage = $objCms->getDefaultLanguage();
		$blnCascade = FALSE;

		$objFields = $this->getFields();
		foreach ($objFields as $objField) {
			if ($objCms->getCacheFields() && count($arrLanguages) > 1) {
				if ($objField->getApiName() == $strName) {
					if ($objField->getLanguageId() == $intCurrentLanguage) {
						if (!$objField->getCascade()) {
							$objReturn = $objField;
							break;
						} else {
							$blnCascade = TRUE;
						}
					} else if ($objField->getLanguageId() == $intDefaultLanguage && $blnCascade) {
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
			$objCms = PCMS_Client::getInstance();
			if ($objCms->getCacheFields()) {
				$objReturn = CachedFields::selectEmptyByElement($this->getId(), $strName);
			} else {
				$objReturn = new ElementField();
			}
		}

		return $objReturn;
	}

	public function getPageId() {
		$objCms = PCMS_Client::getInstance();

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

	public function getPageParent() {
		$objCms = PCMS_Client::getInstance();

		$objReturn = NULL;

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

	public function getParent() {
		$objCms = PCMS_Client::getInstance();

		$objReturn = NULL;

		if (is_object($this->objElement)) {
			$objReturn = $objCms->getElementById($this->objElement->getParentId());
		}

		return $objReturn;
	}

	public function findParentByName($strName, $blnSelfInclude = TRUE) {
		$objCms = PCMS_Client::getInstance();

		$objReturn = NULL;

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

	public function findParentByTemplateName($strName, $blnSelfInclude = TRUE) {
		$objCms = PCMS_Client::getInstance();

		$objReturn = NULL;

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

	public function hasParentId($intParentId) {
		$objCms = PCMS_Client::getInstance();

		$blnReturn = FALSE;

		if (is_object($this->objElement)) {
			if ($this->objElement->getParentId() == $intParentId || $this->objElement->getId() == $intParentId) {
				$blnReturn = TRUE;
			} else {
				if ($this->objElement->getParentId() > 0) {
					$objParent = $objCms->getElementById($this->objElement->getParentId());
					if (is_object($objParent)) $blnReturn = $objParent->hasParentId($intParentId);
				}
			}
		}

		return $blnReturn;
	}

	public function getLink($blnAbsolute = TRUE, $strAddQuery = "", $strLanguageAbbr = NULL) {
		$objCms = PCMS_Client::getInstance();
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

			if (!empty($strAddQuery)) $varReturn .= "?" . $strAddQuery;
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

			if (!empty($strAddQuery)) $varReturn .= "?" . $strAddQuery;

			$varReturn .= "#label_{$this->getId()}";
		}

		return $varReturn;
	}

	public function getPageLink($intPage, $blnAbsolute = TRUE, $strLanguageAbbr = NULL) {
		$strLink = $this->getLink($blnAbsolute, "", $strLanguageAbbr);
		$strReturn = $strLink . "/__page/" . $intPage;

		return $strReturn;
	}

	public function prepareNewElement() {
		if (is_object($this->objElement)) {
			$objCms = PCMS_Client::getInstance();

			return new __InsertElement($this);
		}
	}

	public function getVirtual($blnSubstitute = FALSE) {
		$objCms = PCMS_Client::getInstance();

		$objReturn = NULL;

		if (is_object($this->objElement)) {
			$objField = $this->getField("VirtualLink");
			if (is_object($objField)) {
				$objTemp = $objField->getElement();
				if (is_object($objTemp)) $objReturn = $objTemp->getVirtual(TRUE);
			}
		}

		if (!is_object($objReturn) && $blnSubstitute) $objReturn = $this;

		return $objReturn;
	}
}

class __InsertElement extends __Element {
	private $__template = NULL;
	private $__parent = NULL;
	private $__permissions = NULL;
	private $__defaultLanguage = NULL;
	private $__fields = array();
	private $active = FALSE;
	private $name = "";
	private $username = "";
	private $sort = 0;

	public function __construct($objParent) {
		$this->__parent = $objParent->getElement();

		$this->__permissions = new ElementPermission();
		if (is_object($this->__parent)) {
			$objPermissions = $this->__parent->getPermissions();
			$this->__permissions->setUserId($objPermissions->getUserId());
			$this->__permissions->setGroupId($objPermissions->getGroupId());
		}

		$this->__defaultLanguage = ContentLanguage::getDefault()->getId();
	}

	public function setTemplateName($strApiName) {
		$this->__template = Template::selectByName($strApiName);
	}

	public function addField($strApiName, $varValue, $intLanguageId = NULL, $blnCascade = FALSE) {
		if (is_null($intLanguageId)) $intLanguageId = $this->__defaultLanguage;

		$arrField = (array_key_exists($strApiName, $this->__fields)) ? $this->__fields[$strApiName] : array();

		if (is_string($intLanguageId)) {
			//*** Insert for all languages.
			$objCms = PCMS_Client::getInstance();

			$objLangs = $objCms->getLanguages();
			foreach ($objLangs as $objLang) {
				if (($blnCascade && !$objLang->default) || !$blnCascade) {
					$arrValue = array('value' => $varValue, 'cascade' => $blnCascade);
					$arrField[$objLang->getId()] = $arrValue;
				}
			}

			if ($blnCascade) {
				//*** Set the default language.
				$arrValue = array('value' => $varValue, 'cascade' => FALSE);
				$arrField[$this->__defaultLanguage] = $arrValue;
			}
		} else {
			$arrValue = array('value' => $varValue, 'cascade' => $blnCascade);
			$arrField[$intLanguageId] = $arrValue;
		}

		$this->__fields[$strApiName] = $arrField;
	}

	public function save() {
		if (is_object($this->__template)) {
			$objCms = PCMS_Client::getInstance();

			//*** Element.
			$objElement = new Element();
			$objElement->setParentId($this->__parent->getId());
			$objElement->setAccountId($objCms->getAccount()->getId());
			$objElement->setPermissions($this->__permissions);
			$objElement->setActive($this->active);
			$objElement->setName($this->name);
			$objElement->setUsername($this->username);
			$objElement->setSort($this->sort);
			$objElement->setTypeId(ELM_TYPE_ELEMENT);
			$objElement->setTemplateId($this->__template->getId());
			$objElement->save();

			//*** Activate default schedule.
			$objSchedule = new ElementSchedule();
			$objSchedule->setStartActive(0);
			$objSchedule->setStartDate(PCMS_DEFAULT_STARTDATE);
			$objSchedule->setEndActive(0);
			$objSchedule->setEndDate(PCMS_DEFAULT_ENDDATE);
			$objElement->setSchedule($objSchedule);

			foreach ($this->__fields as $apiName => $arrField) {
				$objTemplateField = $this->__template->getFieldByName($apiName);
				$objField = new ElementField();
				$objField->setElementId($objElement->getId());
				$objField->setTemplateFieldId($objTemplateField->getId());
				$objField->save();

				foreach ($arrField as $intLanguage => $arrValue) {
					$objValue = $objField->getNewValueObject();
					$objValue->setValue($arrValue['value']);
					$objValue->setLanguageId($intLanguage);
					$objValue->setCascade($arrValue['cascade']);
					$objField->setValueObject($objValue);

					//*** Activate the language.
					$objElement->setLanguageActive($intLanguage, TRUE);
				}
			}

			if (count($this->__fields) == 0) {
				//*** Set all languages active if there are no fields.
				$objLangs = $objCms->getLanguages();
				foreach ($objLangs as $objLang) {
					$objElement->setLanguageActive($objLang->getId(), TRUE);
				}
			}

			return new __Element($objElement);
		}
	}

	public function __get($property) {
		$property = strtolower($property);

		if (isset($this->$property) || is_null($this->$property)) {
			return $this->$property;
		} else {
			echo "Property Error in " . self::$object . "::get({$property}) on line " . __LINE__ . ".";
		}
	}

	public function __set($property, $value) {
		$property = strtolower($property);

		if (isset($this->$property) || is_null($this->$property)) {
			$this->$property = $value;
		} else {
			echo "Property Error in " . self::$object . "::set({$property}) on line " . __LINE__ . ".";
		}
	}

	public function __call($method, $values) {
		if (substr($method, 0, 3) == "get") {
			$property = substr($method, 3);
			return $this->$property;
		}

		if (substr($method, 0, 3) == "set") {
			$property = substr($method, 3);
			$this->$property = $values[0];
			return;
		}

		echo "Method Error in " . self::$object . "::{$method} on line " . __LINE__ . ".";
	}

}

class __ElementFields extends DBA__Collection {

	public function getFields($intElementId) {
		$strSql = "SELECT pcms_template_field.* FROM pcms_template_field, pcms_element
				WHERE pcms_element.id = '%s'
				AND pcms_element.templateId = pcms_template_field.templateId
				ORDER BY pcms_element.sort";
		$objTplFields = TemplateField::select(sprintf($strSql, $intElementId));

		$objReturn = new __ElementFields;

		foreach ($objTplFields as $objTplField) {
			$objReturn->addObject(new __ElementField($intElementId, $objTplField));
		}

		return $objReturn;
	}

	public static function getCachedFields($intElementId) {
		$objReturn = CachedFields::selectByElement($intElementId);

		return $objReturn;
	}

}

class __ElementField {
	private $objField;
	public $name;
	public $apiName;
	public $type;
	public $id;
	public $templateFieldId;

	public function __construct($intElementId, $objTplField = NULL) {
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

	public function getField() {
		return $this->objField;
	}

	public function getApiName() {
		return $this->apiName;
	}

	public function getTemplateFieldId() {
		return $this->templateFieldId;
	}

	public function getTypeId() {
		return $this->type;
	}

	public function getRange() {
		/* This method returns the possible values for list type fields. */
		$arrReturn = array();

		if ($this->templateFieldId > 0) {
			$objTemplateField = TemplateField::selectByPK($this->templateFieldId);

			if (is_object($objTemplateField)) {
				$strRange = $objTemplateField->getValueByName("tfv_multilist_value")->getValue();
				if (empty($strRange)) $strRange = $objTemplateField->getValueByName("tfv_list_value")->getValue();

				$arrValues = split("\n", $strRange);
				foreach ($arrValues as $value) {
					if (!empty($value)) {
						//*** Determine if we have a label.
						$arrValue = split(":", $value);
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

	public function getValue($varFilter = NULL, $varOptions = NULL) {
		$varReturn = "";
		$objCms = PCMS_Client::getInstance();

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
						self::filter_addAmpersand($varReturn);

						//*** Replace $ characters with &#36;.
						$varReturn = str_replace("$", "&#36;", $varReturn);

						//*** Replace BAD link targets with GOOD rels.
						self::filter_fixXtmlLinkTarget($varReturn);

						//*** Apply field type specific conversions
						if ($objCms->usesAliases()) self::filter_useAliases($this, $varReturn);
						switch ($this->type) {
							case FIELD_TYPE_SIMPLETEXT:
								$varReturn = nl2br($varReturn);
								break;
						}

						//*** Apply media specific conversions
						$blnDirect = (is_array($varOptions) && array_key_exists("directLink", $varOptions)) ? $varOptions["directLink"] : FALSE;
						self::filter_useMedia($this, $varReturn, $blnDirect);

						break;
					case VALUE_HILIGHT:
						//*** Enable URLs and email addresses.
						self::filter_text2html($varReturn);
						break;
					case VALUE_NOURL:
						//*** Remove URLs and email addresses.
						self::filter_removeUrl($varReturn);
						break;
					case VALUE_SRC:
						//*** Get the source of an image or file field.
						$objValue = (is_array($varReturn)) ? array_pop($varReturn) : NULL;
						$varReturn = (is_array($objValue)) ? $objCms->getFilePath() . $objValue['src'] : NULL;
						break;
					case VALUE_ORIGINAL:
						//*** Get the original name of an image or file field.
						$objValue = (is_array($varReturn)) ? array_pop($varReturn) : NULL;
						$varReturn = (is_array($objValue)) ? $objValue['original'] : NULL;
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
								if ($filter == VALUE_INLINE) $varReturn .= "/inline";
							} else {
								$strId = (!is_null($varOptions) && is_numeric($varOptions)) ? $this->id . "&amp;index=" . $varOptions : $this->id;
								$varReturn = $objCms->getDownloadPath() . $strId;
							}
						}
						break;
					case VALUE_XML:
						//*** Prepare output for XML.

						//*** Apply field type specific conversions
						if ($objCms->usesAliases()) self::filter_useAliases($this, $varReturn);

						//*** Apply media specific conversions
						$blnDirect = (is_array($varOptions) && array_key_exists("directLink", $varOptions)) ? $varOptions["directLink"] : FALSE;
						self::filter_useMedia($this, $varReturn, $blnDirect);

						//*** Replace & characters with &amp; and add slashes.
						self::filter_forXML($varReturn);
						break;
				}
			}
		}

		return $varReturn;
	}

	public function buildImageCollection() {
		$objCms = PCMS_Client::getInstance();
		$objReturn = new DBA__Collection();

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

	public function getSettings() {
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

	public function getSize($index = 0) {
		//*** Return the width and height of an image field as an array.
		$arrReturn = array('width' => 0, 'height' => 0);

		if ($this->type == FIELD_TYPE_IMAGE) {
			$objCms = PCMS_Client::getInstance();
			$arrFiles = $this->getValue();
			$arrFile = (is_array($arrFiles)) ? $arrFiles[$index] : NULL;
			$strFile = (is_array($arrFile)) ? $objCms->getBasePath() . $objCms->getFilePath() . $arrFile['src'] : NULL;
			if (is_file($strFile)) {
				$arrTemp = getimagesize($strFile);
				$arrReturn['width'] = $arrTemp[0];
				$arrReturn['height'] = $arrTemp[1];
			}
		}

		return $arrReturn;
	}

	public function getWidth($index = 0) {
		//*** Return the width of an image field as an integer.
		$arrSize = $this->getSize($index);

		return $arrSize['width'];
	}

	public function getHeight($index = 0) {
		//*** Return the height of an image field as an integer.
		$arrSize = $this->getSize($index);

		return $arrSize['height'];
	}

	public function getHtmlSize($index = 0) {
		//*** Return the width and height of an image field as an URL string.
		$arrSize = $this->getSize($index);

		return "width=\"{$arrSize['width']}\" height=\"{$arrSize['height']}\"";
	}

	public function getHtmlValue($varFilter = NULL) {
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

	public function getRawValue() {
		if (is_object($this->objField)) {
			$objCms = PCMS_Client::getInstance();
			return $this->objField->getRawValue($objCms->getLanguage()->getId());
		} else {
			return "";
		}
	}

	public function getOriginalName() {
		if (is_object($this->objField)) {
			$objCms = PCMS_Client::getInstance();
			$objValue = $this->objField->getValueObject($objCms->getLanguage()->getId());
			if (is_object($objValue)) {
				return $objValue->getOriginalName();
			}
		} else {
			return "";
		}
	}

	public function getShortValue($intCharLength = 200, $blnPreserveWord = TRUE, $strAppend = " ...", $blnHtml = TRUE) {
		//*** Get a short version of the value.
		if ($blnHtml) {
			$strInput = $this->getHtmlValue();
		} else {
			$strInput = $this->getValue();
		}
		$strReturn = $strInput;

		$strReturn = substr($strInput, 0, $intCharLength);

		if ($blnPreserveWord == TRUE && strlen($strReturn) < strlen($strInput)) {
			$intLastSpace = strrpos($strReturn, " ");
			$strReturn = substr($strReturn, 0, $intLastSpace);
		}

		if (strlen($strReturn) < strlen($strInput)) {
			$strReturn .= $strAppend;
		}

		return $strReturn;
	}

	public function getLink($blnAbsolute = TRUE, $strAddQuery = "", $strLanguageAbbr = NULL) {
		if ($this->type == FIELD_TYPE_LINK) {
			$objCms = PCMS_Client::getInstance();
            $value = $this->getValue();
            if (!empty($value)){
                // file
                if (is_array($value)){
                    return $objField->getValue(VALUE_SRC);
                } else {
                    if (preg_match('/^(http:\/\/|https:\/\/|mailto:)+/',$value)) {
                        return $value;
                    } else if(preg_match('/^(www)+/',$value)) {
                        return 'http://'. $value;
                    } else {
                        // deep link
                        $objElement = $objCms->getElementById($this->getValue());
                        if (is_object($objElement)) return $objElement->getLink($blnAbsolute, $strAddQuery, $strLanguageAbbr);
                    }
                }
            }
		}
	}

	public function getElement() {
		if ($this->type == FIELD_TYPE_LINK) {
			$objCms = PCMS_Client::getInstance();
			$objElement = $objCms->getElementById($this->getValue());

			if (is_object($objElement)) return $objElement;
		}
	}

	private static function filter_text2html(&$text) {
	    // match protocol://address/path/
	    $text = mb_ereg_replace("[a-zA-Z]+://([.]?[a-zA-Z0-9_/-])*", "<a href=\"\\0\" rel=\"external\">\\0</a>", $text);

	    // match www.something
	    $text = mb_ereg_replace("(^| |.)(www([.]?[a-zA-Z0-9_/-])*)", "\\1<a href=\"http://\\2\" rel=\"external\">\\2</a>", $text);

	    // match email
	    $text = mb_ereg_replace("[-a-z0-9!#$%&\'*+/=?^_`{|}~]+@([.]?[a-zA-Z0-9_/-])*", "<a href=\"mailto:\\0\" title=\"mailto:\\0\">\\0</a>", $text);
	}

	private static function filter_addAmpersand(&$text) {
		$text = preg_replace("/&(?!#?[xX]?(?:[0-9a-fA-F]+|\w{1,8});)/i", "&amp;", $text);
	}

	private static function filter_fixXtmlLinkTarget(&$text) {
		$text = str_ireplace("target=\"_blank\"", "rel=\"external\"", $text);
		$text = str_ireplace("target=\"_top\"", "rel=\"external\"", $text);
	}

	private static function filter_useAliases($objField, &$text) {
		$objCms = PCMS_Client::getInstance();

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

	private static function filter_useMedia($objField, &$text, $blnDirect) {
		$objCms = PCMS_Client::getInstance();

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

	private static function filter_removeUrl(&$text) {
	    // match protocol://address/path/
	    $text = mb_ereg_replace("[a-zA-Z]+://([.]?[a-zA-Z0-9_/-])*", "", $text);

	    // match www.something
	    $text = mb_ereg_replace("(^| |.)(www([.]?[a-zA-Z0-9_/-])*)", "", $text);

	    // match email
	    $text = mb_ereg_replace("[-a-z0-9!#$%&\'*+/=?^_`{|}~]+@([.]?[a-zA-Z0-9_/-])*", "", $text);
	}

	private static function filter_forXML(&$text) {
		//*** Convert HTML entities to the real characters.
		$text = html_entity_decode($text, ENT_COMPAT, "UTF-8");

		//*** Replace & characters with &amp;.
		self::filter_addAmpersand($text);

		//*** Replace 4 other characters with XML entities.
		$text = str_replace("<", "&lt;", $text);
		$text = str_replace(">", "&gt;", $text);
		$text = str_replace("\"", "&quot;", $text);
		$text = str_replace("'", "&apos;", $text);
	}

}

class CachedFields extends DBA__Collection {
	public static function selectByElement($intElementId) {
		$objCms = PCMS_Client::getInstance();

		$objReturn = $objCms->getCachedFields($intElementId);
		if (!is_object($objReturn)) {
			$arrStorages = array("bigtext", "text", "date", "number");

			$strSql = "";
			$intCount = 0;
			$strLanguages = implode("','", $objCms->getLanguageArray());
			foreach ($arrStorages as $storage) {
				$strSubSql = "SELECT pcms_element.id as elementId,
						pcms_template_field.id as templateFieldId,
						pcms_element_field.id as elementFieldId,
						pcms_template_field.typeId,
						pcms_template_field.apiName,
						pcms_template_field_type.element,
						pcms_element_field_%s.value,
						`pcms_element_field_%s`.`languageId`,
						`pcms_element_field_%s`.`cascade` as `cascade`
						FROM
						pcms_template_field,
						pcms_template_field_type,
						pcms_element,
						pcms_element_field,
						pcms_element_field_%s
						WHERE pcms_element.id = '%s'
						AND pcms_element.templateId = pcms_template_field.templateId
						AND pcms_template_field_type.id = pcms_template_field.typeId
						AND pcms_element_field.elementId = pcms_element.id
						AND pcms_element_field.templateFieldId = pcms_template_field.id
						AND pcms_element_field_%s.fieldId = pcms_element_field.id
						AND pcms_element_field_%s.languageId IN ('%s')";
				$strSql .= sprintf($strSubSql, $storage, $storage, $storage, $storage, $intElementId, $storage, $storage, $strLanguages, $storage);
				if ($intCount < count($arrStorages) - 1) {
					$strSql .= " UNION ";
				} else {
					$strSql .= " ORDER BY templateFieldId, `cascade` DESC";
				}

				$intCount++;
			}

			$objReturn = CachedField::select($strSql);
			$objCms->setCachedFields($intElementId, $objReturn);
		}

		return $objReturn;
	}

	public static function selectEmptyByElement($intElementId, $strApiName) {
		$objReturn = new CachedField();

		$strSql = "SELECT pcms_element.id,
			pcms_template_field.id as templateFieldId,
			pcms_template_field.typeId,
			pcms_template_field.apiName,
			pcms_template_field_type.element
			FROM
			pcms_template_field,
			pcms_template_field_type,
			pcms_element
			WHERE pcms_element.id = '%s'
			AND pcms_element.templateId = pcms_template_field.templateId
			AND pcms_template_field_type.id = pcms_template_field.typeId
			AND pcms_template_field.apiName = '%s'";
		$strSql = sprintf($strSql, $intElementId, $strApiName);
		$objTplFields = CachedField::select($strSql);

		if ($objTplFields->count() > 0) {
			$objReturn = $objTplFields->current();
		}

		return $objReturn;
	}
}

class CachedField extends DBA__Object {
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
	public function __construct() {
		self::$object = "CachedField";
		self::$table = "pcms_element_field";
	}

	public static function select($strSql = "") {
		self::$object = "CachedField";
		self::$table = "pcms_element_field";

		return parent::select($strSql);
	}

    public function getAutoValue()
    {
        $objCms = PCMS_Client::getInstance();

        $return = '';
        switch ($this->typeid) {
            case FIELD_TYPE_LARGETEXT:
                $return = $this->getHtmlValue();
                break;
            case FIELD_TYPE_IMAGE:
                $values = array();
                $arrSettings = $this->getSettings();
                $objImages = $this->getValue(VALUE_IMAGES);
                foreach($objImages as $objImage)
                {
                    $templates = array();

                    // search for templates
                    foreach ($arrSettings as $arrSetting) {
                        if($arrSetting['api'] !=''){
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
                foreach($arrFiles as $arrFile)
                {
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

	private function prepareValue() {
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
					$intElementId = Element::selectByPk($this->elementid)->getPageId();
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
						$this->value = TRUE;
					} else {
						$this->value = FALSE;
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

	public function getValue($varFilter = NULL, $varOptions = NULL) {
		$objCms = PCMS_Client::getInstance();

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
						self::filter_addAmpersand($varReturn);

						//*** Replace $ characters with &#36;.
						$varReturn = str_replace("$", "&#36;", $varReturn);

						//*** Replace BAD link targets with GOOD rels.
						self::filter_fixXtmlLinkTarget($varReturn);

						//*** Apply field type specific conversions
						if ($objCms->usesAliases()) self::filter_useAliases($this, $varReturn);

						//*** Apply media specific conversions
						$blnDirect = (is_array($varOptions) && array_key_exists("directLink", $varOptions)) ? $varOptions["directLink"] : FALSE;
						self::filter_useMedia($this, $varReturn, $blnDirect);

						break;
					case VALUE_HILIGHT:
						//*** Enable URLs and email addresses.
						self::filter_text2html($varReturn);
						break;
					case VALUE_NOURL:
						//*** Remove URLs and email addresses.
						self::filter_removeUrl($varReturn);
						break;
					case VALUE_SRC:
						//*** Get the source of an image or file field.
						$objValue = (is_array($varReturn)) ? array_pop($varReturn) : NULL;
						$varReturn = (is_array($objValue)) ? $objCms->getFilePath() . $objValue['src'] : NULL;
						break;
					case VALUE_ORIGINAL:
						//*** Get the original name of an image or file field.
						$objValue = (is_array($varReturn)) ? array_pop($varReturn) : NULL;
						$varReturn = (is_array($objValue)) ? $objValue['original'] : NULL;
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
								if ($filter == VALUE_INLINE) $varReturn .= "/inline";
							} else {
								$strId = (!is_null($varOptions) && is_numeric($varOptions)) ? $this->elementfieldid . "&amp;index=" . $varOptions : $this->elementfieldid;
								$varReturn = $objCms->getDownloadPath() . $strId;
							}
						}
						break;
					case VALUE_XML:
						//*** Prepare output for XML.

						//*** Apply field type specific conversions
						if ($objCms->usesAliases()) self::filter_useAliases($this, $varReturn);

						//*** Apply media specific conversions
						$blnDirect = (is_array($varOptions) && array_key_exists("directLink", $varOptions)) ? $varOptions["directLink"] : FALSE;
						self::filter_useMedia($this, $varReturn, $blnDirect);

						//*** Replace & characters with &amp; and add slashes.
						self::filter_forXML($varReturn);
						break;
				}
			}
		}

		return $varReturn;
	}

	public function buildImageCollection() {
		$objCms = PCMS_Client::getInstance();
		$objReturn = new DBA__Collection();

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

	public function getSettings() {
		$arrReturn = null;

		switch ($this->typeid) {
			case FIELD_TYPE_IMAGE:
				$objImage = new ImageField($this->templatefieldid);
				$arrReturn = $objImage->getSettings();

				break;
		}

		return $arrReturn;
	}

	public function getField() {
		$objReturn = NULL;

		$strSql = "SELECT * FROM pcms_element_field WHERE elementId = '%s' AND templateFieldId = '%s' ORDER BY sort";
		$objFields = ElementField::select(sprintf($strSql, $this->elementid, $this->templatefieldid));

		if (is_object($objFields) && $objFields->count() > 0) {
			$objReturn = $objFields->current();
		}

		return $objReturn;
	}

	public function getSize($index = 0) {
		//*** Return the width and height of an image field as an array.
		$arrReturn = array('width' => 0, 'height' => 0);

		if ($this->typeid == FIELD_TYPE_IMAGE) {
			$objCms = PCMS_Client::getInstance();
			$arrFiles = $this->getValue();
			$arrFile = (is_array($arrFiles) && count($arrFiles) >= $index + 1) ? $arrFiles[$index] : NULL;
			$strFile = (is_array($arrFile)) ? $objCms->getBasePath() . $objCms->getFilePath() . $arrFile['src'] : NULL;
			if (is_file($strFile)) {
				$arrTemp = getimagesize($strFile);
				$arrReturn['width'] = $arrTemp[0];
				$arrReturn['height'] = $arrTemp[1];
			}
		}

		return $arrReturn;
	}

	public function getWidth($index = 0) {
		//*** Return the width of an image field as an integer.
		$arrSize = $this->getSize($index);

		return $arrSize['width'];
	}

	public function getHeight($index = 0) {
		//*** Return the height of an image field as an integer.
		$arrSize = $this->getSize($index);

		return $arrSize['height'];
	}

	public function getHtmlSize($index = 0) {
		//*** Return the width and height of an image field as an URL string.
		$arrSize = $this->getSize($index);

		return "width=\"{$arrSize['width']}\" height=\"{$arrSize['height']}\"";
	}

	public function getHtmlValue($varFilter = NULL) {
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

	public function getRawValue() {
		$this->prepareValue();

		return $this->rawvalue;
	}

	public function getShortValue($intCharLength = 200, $blnPreserveWord = TRUE, $strAppend = " ...", $blnHtml = TRUE) {
		//*** Get a short version of the value.
		if ($blnHtml) {
			$strInput = $this->getHtmlValue();
		} else {
			$strInput = $this->getValue();
		}
		$strReturn = $strInput;

		$strReturn = substr($strInput, 0, $intCharLength);

		if ($blnPreserveWord == TRUE && strlen($strReturn) < strlen($strInput)) {
			$intLastSpace = strrpos($strReturn, " ");
			$strReturn = substr($strReturn, 0, $intLastSpace);
		}

		if (strlen($strReturn) < strlen($strInput)) {
			$strReturn .= $strAppend;
		}

		return $strReturn;
	}

    public function getLink($blnAbsolute = TRUE, $strAddQuery = "", $strLanguageAbbr = NULL) {
		if ($this->typeid == FIELD_TYPE_LINK) {
			$objCms = PCMS_Client::getInstance();
            $value = $this->getValue();
            if (!empty($value)){
                // file
                if (is_array($value)){
                    return $objField->getValue(VALUE_SRC);
                } else {
                    if (preg_match('/^(http:\/\/|https:\/\/|mailto:)+/',$value)) {
                        return $value;
                    } else if(preg_match('/^(www)+/',$value)) {
                        return 'http://'. $value;
                    } else {
                        // deep link
                        $objElement = $objCms->getElementById($this->getValue());
                        if (is_object($objElement)) return $objElement->getLink($blnAbsolute, $strAddQuery, $strLanguageAbbr);
                    }
                }
            }
		}
	}

	public function getElement() {
		if ($this->typeid == FIELD_TYPE_LINK) {
			$objCms = PCMS_Client::getInstance();
			$objElement = $objCms->getElementById($this->getValue());

			if (is_object($objElement)) return $objElement;
		}
	}

	private static function filter_text2html(&$text) {
	    // match protocol://address/path/
	    $text = mb_ereg_replace("[a-zA-Z]+://([.]?[a-zA-Z0-9_/-])*", "<a href=\"\\0\" rel=\"external\">\\0</a>", $text);

	    // match www.something
	    $text = mb_ereg_replace("(^| |.)(www([.]?[a-zA-Z0-9_/-])*)", "\\1<a href=\"http://\\2\" rel=\"external\">\\2</a>", $text);

	    // match email
	    $text = mb_ereg_replace("[-a-z0-9!#$%&\'*+/=?^_`{|}~]+@([.]?[a-zA-Z0-9_/-])*", "<a href=\"mailto:\\0\" title=\"mailto:\\0\">\\0</a>", $text);
	}

	private static function filter_addAmpersand(&$text) {
		$text = preg_replace("/&(?!#?[xX]?(?:[0-9a-fA-F]+|\w{1,8});)/i", "&amp;", $text);
	}

	private static function filter_fixXtmlLinkTarget(&$text) {
		$text = str_ireplace("target=\"_blank\"", "rel=\"external\"", $text);
		$text = str_ireplace("target=\"_top\"", "rel=\"external\"", $text);
	}

	private static function filter_useAliases($objField, &$text) {
		$objCms = PCMS_Client::getInstance();

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

	private static function filter_useMedia($objField, &$text, $blnDirect = FALSE) {
		$objCms = PCMS_Client::getInstance();

		switch ($objField->typeid) {
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

	private static function filter_removeUrl(&$text) {
	    // match protocol://address/path/
	    $text = mb_ereg_replace("[a-zA-Z]+://([.]?[a-zA-Z0-9_/-])*", "", $text);

	    // match www.something
	    $text = mb_ereg_replace("(^| |.)(www([.]?[a-zA-Z0-9_/-])*)", "", $text);

	    // match email
	    $text = mb_ereg_replace("[-a-z0-9!#$%&\'*+/=?^_`{|}~]+@([.]?[a-zA-Z0-9_/-])*", "", $text);
	}

	private static function filter_forXML(&$text) {
		//*** Convert HTML entities to the real characters.
		$text = html_entity_decode($text, ENT_COMPAT, "UTF-8");

		//*** Replace & characters with &amp;.
		self::filter_addAmpersand($text);

		//*** Replace 4 other characters with XML entities.
		$text = str_replace("<", "&lt;", $text);
		$text = str_replace(">", "&gt;", $text);
		$text = str_replace("\"", "&quot;", $text);
		$text = str_replace("'", "&apos;", $text);
	}
}

?>
