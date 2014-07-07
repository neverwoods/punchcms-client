<?php

/**
 * 
 * Values of an image type field.
 * @author felix
 * @version 0.1.0
 *
 */
class ImageValue {
	private $__settings;
	private $__src;
	private $__path;
	private $__original;
	private $__alt;
	
	public function __construct($arrSettings) {
		$this->__settings = $arrSettings;
	}
	
	public function setPath($strValue) {
		$this->__path = $strValue;
	}
	
	public function getPath($strValue) {
		return $this->__path;
	}
	
	public function setSrc($strValue) {
		$this->__src = $strValue;
	}
	
	public function setOriginal($strValue) {
		$this->__original = $strValue;
	}
	
	public function setAlt($strValue) {
		$this->__alt = $strValue;
	}
	
	public function getSrc($strApiName = "") {
		$strReturn = "";
		
		if (empty($strApiName) || count($this->__settings) < 2) {
			$strReturn = $this->__path . $this->__src;
		} else {
			foreach ($this->__settings as $arrSetting) {
				if ($arrSetting['api'] == $strApiName) {
					$strReturn = $this->__path . FileIO::add2Base($this->__src, $arrSetting['key']);
					break;
				}
			} 
		}
		
		return $strReturn;
	}
	
	public function getSize($strApiName = "") {
		//*** Return the width and height of an image field as an array.
		$arrReturn = array('width' => 0, 'height' => 0);

		$objCms = PCMS_Client::getInstance();
		$strFile = $objCms->getBasePath() . $this->getSrc($strApiName);
		if (is_file($strFile)) {
			$arrTemp = getimagesize($strFile);
			$arrReturn['width'] = $arrTemp[0];
			$arrReturn['height'] = $arrTemp[1];
		}

		return $arrReturn;
	}
	
	public function getWidth($strApiName = "") {
		//*** Return the width of an image field as an integer.
		$arrSize = $this->getSize($strApiName);

		return $arrSize['width'];
	}

	public function getHeight($strApiName = "") {
		//*** Return the height of an image field as an integer.
		$arrSize = $this->getSize($strApiName);

		return $arrSize['height'];
	}

	public function getHtmlSize($strApiName = "") {
		//*** Return the width and height of an image field as an URL string.
		$arrSize = $this->getSize($strApiName);

		return "width=\"{$arrSize['width']}\" height=\"{$arrSize['height']}\"";
	}
	
	public function getOriginal() {
		return $this->__original;
	}
	
	public function getAlt() {
		return $this->__alt;
	}
	
}