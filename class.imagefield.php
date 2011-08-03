<?php

/**
 * 
 * Holds all properties of an Image field.
 * @author felix
 * @version 0.1.0
 *
 */
class ImageField {
	private $__templateFieldId;
	private $__templateField;
	
	public function __construct($intTemplateFieldId) {
		$this->__templateFieldId = $intTemplateFieldId;
		$this->__templateField = TemplateField::selectByPK($intTemplateFieldId);
	}
	
	public function getSettings() {
		$arrReturn = array();
		
		$objValue = $this->__templateField->getValueByName("tfv_image_settings_count");
		if (is_object($objValue) && $objValue->getValue() > 0) {
			$intMaxSettings = $objValue->getValue();
			for ($intCount = 0; $intCount <= $intMaxSettings; $intCount++) {
				$arrReturn[$intCount] = array();
				
				$objValue = $this->__templateField->getValueByName("tfv_field_width_" . $intCount);
				$arrReturn[$intCount]['width'] = (is_object($objValue)) ? $objValue->getValue() : "";
				$objValue = $this->__templateField->getValueByName("tfv_field_height_" . $intCount);
				$arrReturn[$intCount]['height'] = (is_object($objValue)) ? $objValue->getValue() : "";
				$objValue = $this->__templateField->getValueByName("tfv_image_scale_" . $intCount);
				$arrReturn[$intCount]['scale'] = (is_object($objValue)) ? $objValue->getValue() : "";
				$objValue = $this->__templateField->getValueByName("tfv_image_quality_" . $intCount);
				$arrReturn[$intCount]['quality'] = (is_object($objValue)) ? $objValue->getValue() : 75;
				$objValue = $this->__templateField->getValueByName("tfv_image_grayscale_" . $intCount);
				$arrReturn[$intCount]['grayscale'] = (is_object($objValue) && $objValue->getValue() == "on") ? TRUE : FALSE;
				$objValue = $this->__templateField->getValueByName("tfv_image_setting_name_" . $intCount);
				$arrReturn[$intCount]['api'] = (is_object($objValue)) ? $objValue->getValue() : "";
				
				$strKey = $arrReturn[$intCount]['width'] . $arrReturn[$intCount]['height'] . $arrReturn[$intCount]['quality'];
				$arrReturn[$intCount]['key'] = ($arrReturn[$intCount]['grayscale']) ? "__" . $strKey . "1" : "__" . $strKey . "0";
			}			
		} else {
			$intCount = 0;
			$arrReturn[$intCount] = array();
			
			$objValue = $this->__templateField->getValueByName("tfv_field_width");
			if (is_object($objValue)) {
				$arrReturn[$intCount]['width'] = $objValue->getValue();
			} else {
				$objValue = $this->__templateField->getValueByName("tfv_field_width_0");
				$arrReturn[$intCount]['width'] = (is_object($objValue)) ? $objValue->getValue() : "";
			}
			$objValue = $this->__templateField->getValueByName("tfv_field_height");
			if (is_object($objValue)) {
				$arrReturn[$intCount]['height'] = $objValue->getValue();
			} else {
				$objValue = $this->__templateField->getValueByName("tfv_field_height_0");
				$arrReturn[$intCount]['height'] = (is_object($objValue)) ? $objValue->getValue() : "";
			}												
			$objValue = $this->__templateField->getValueByName("tfv_image_scale");
			if (is_object($objValue)) {
				$arrReturn[$intCount]['scale'] = $objValue->getValue();
			} else {
				$objValue = $this->__templateField->getValueByName("tfv_image_scale_0");
				$arrReturn[$intCount]['scale'] = (is_object($objValue)) ? $objValue->getValue() : "";
			}
			$objValue = $this->__templateField->getValueByName("tfv_image_quality");
			if (is_object($objValue)) {
				$arrReturn[$intCount]['quality'] = $objValue->getValue();
			} else {
				$objValue = $this->__templateField->getValueByName("tfv_image_quality_0");
				$arrReturn[$intCount]['quality'] = (is_object($objValue)) ? $objValue->getValue() : 75;
			}
			$objValue = $this->__templateField->getValueByName("tfv_image_grayscale");
			if (is_object($objValue)) {
				$arrReturn[$intCount]['grayscale'] = ($objValue->getValue() == "on") ? TRUE : FALSE;
			} else {
				$objValue = $this->__templateField->getValueByName("tfv_image_grayscale_0");
				$arrReturn[$intCount]['grayscale'] = (is_object($objValue) && $objValue->getValue() == "on") ? TRUE : FALSE;
			}
			$arrReturn[$intCount]['api'] = "";
			$arrReturn[$intCount]['key'] = "";
		}
		
		return $arrReturn;
	}
	
	public static function filename2LocalName($filename) {
		$objUpload = new SingleUpload();
		$objUpload->setRename(TRUE);
		$objUpload->setOriginalName($filename);
		return $objUpload->getFileName($filename);
	}
	
}