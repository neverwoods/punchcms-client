<?php

/**
 * 
 * Holds the PunchCMS Valid Form classes.
 * Depends on ValidForm Builder and htmlMimeMail5.
 * @author felix
 * @version 0.1.7.6
 *
 */
class PCMS_FormBuilder {
	private $__formElement	= FALSE;
	private $__maxLengthAlert = "";
	private $__minLengthAlert = "";
	private $__requiredAlert = "";
	public $__validForm	= FALSE;

	public function __construct($objForm, $strAction = null) {
		$this->__formElement = $objForm;
		$strName = $objForm->getName();
		$strName = (empty($strName)) ? $objForm->getId() : strtolower($strName);
		$this->__validForm = new ValidForm("validform_" . $strName, $this->__formElement->getField("RequiredBody")->getHtmlValue(), $strAction);
	}

	public function getValidForm() {
		$varReturn = null;
		if (is_object($this->__validForm)) {
			$varReturn = $this->__validForm;
		} else {
			throw new Exception("ValidForm is not yet initiated. Could not load ValidForm from PCMS_FormBuilder.", E_ERROR);
		}

		return $varReturn;
	}

	public function buildForm($blnSend = TRUE, $blnClientSide = TRUE) {
		$objCms = PCMS_Client::getInstance();
	
		$strReturn = "";
	
		$this->__maxLengthAlert = $this->__formElement->getField("AlertMaxLength")->getHtmlValue();
		$this->__minLengthAlert = $this->__formElement->getField("AlertMinLength")->getHtmlValue();
		$this->__requiredAlert = $this->__formElement->getField("AlertRequired")->getHtmlValue();

		$this->__validForm->setRequiredStyle($this->__formElement->getField("RequiredIndicator")->getHtmlValue());
		$this->__validForm->setMainAlert($this->__formElement->getField("AlertMain")->getHtmlValue());

		//*** Form starts here.
		$objFieldsets = $this->__formElement->getElementsByTemplate(array("Fieldset", "Paragraph"));
		foreach ($objFieldsets as $objFieldset) {
			switch ($objFieldset->getTemplateName()) {
				case "Paragraph":
					$this->renderParagraph($this->__validForm, $objFieldset);
					break;
				case "Fieldset":
					$this->renderFieldset($this->__validForm, $objFieldset);

					$objFields = $objFieldset->getElementsByTemplate(array("Field", "Area", "ListField", "MultiField"));
					foreach ($objFields as $objField) {
						switch ($objField->getTemplateName()) {
							case "Field":
								$this->renderField($this->__validForm, $objField);
								break;
								
							case "ListField":
								$this->renderListField($this->__validForm, $objField);
								break;			
	
							case "Area":
								$this->renderArea($this->__validForm, $objField);
								break;
								
							case "MultiField":
								$this->renderMultiField($this->__validForm, $objField);
								break;
								
						}
					}
			}
		}

		$this->__validForm->setSubmitLabel($this->__formElement->getField("SendLabel")->getHtmlValue());

		if ($this->__validForm->isSubmitted() && $this->__validForm->isValid()) {
			if ($blnSend) {
				$objRecipientEmails = $this->__formElement->getElementsByTemplate("RecipientEmail");	
				foreach ($objRecipientEmails as $objRecipientEmail) {
					$strHtmlBody = "<html><head><title></title></head><body>";
					$strHtmlBody .= sprintf($objRecipientEmail->getField("Body")->getHtmlValue(), $this->__validForm->valuesAsHtml(TRUE));
					$strHtmlBody .= "</body></html>";
	
					//*** Build the e-mail.
					$strTextBody = str_replace("<br /> ", "<br />", $strHtmlBody);
					$strTextBody = str_replace("<br />", "\n", $strTextBody);
					$strTextBody = str_replace("&nbsp;","",$strTextBody);
					$strTextBody = strip_tags($strTextBody);
					$strTextBody = html_entity_decode($strTextBody, ENT_COMPAT, "UTF-8");
	
					$varEmailId = $objRecipientEmail->getField("SenderEmail")->getValue();
					$objEmailElement = $objCms->getElementById($varEmailId);
					$strFrom = "";
					if (is_object($objEmailElement)) {
						$varEmailId = $objEmailElement->getElement()->getApiName();
						if (empty($varEmailId)) $varEmailId = $objEmailElement->getId();
						$strFrom = $this->__validForm->getValidField("formfield_" . strtolower($varEmailId))->getValue();
					}
					
					//*** Send the email.
					$objMail = new htmlMimeMail5();
					$objMail->setHTMLEncoding(new Base64Encoding());
					$objMail->setTextCharset("utf-8");
					$objMail->setHTMLCharset("utf-8");
					$objMail->setHeadCharset("utf-8");
					$objMail->setFrom($strFrom);
					$objMail->setSubject($objRecipientEmail->getField("Subject")->getHtmlValue());
					$objMail->setText($strTextBody);
					$objMail->setHTML($strHtmlBody);
					if (!$objMail->send(explode(",", $objRecipientEmail->getField("RecipientEmail")->getHtmlValue()))) {
						echo $objMail->errors;
					}
				}
	
				$objSenderEmails = $this->__formElement->getElementsByTemplate("SenderEmail");	
				foreach ($objSenderEmails as $objSenderEmail) {
					$strHtmlBody = "<html><head><title></title></head><body>";
					$strHtmlBody .= sprintf($objSenderEmail->getField("Body")->getHtmlValue(), $this->__validForm->valuesAsHtml(TRUE));
					$strHtmlBody .= "</body></html>";
	
					//*** Build the e-mail.
					$strTextBody = str_replace("<br /> ", "<br />", $strHtmlBody);
					$strTextBody = str_replace("<br />", "\n", $strTextBody);
					$strTextBody = str_replace("&nbsp;", "", $strTextBody);
					$strTextBody = strip_tags($strTextBody);
					$strTextBody = html_entity_decode($strTextBody, ENT_COMPAT, "UTF-8");
	
					$varEmailId = $objSenderEmail->getField("RecipientEmail")->getValue();
					$objEmailElement = $objCms->getElementById($varEmailId);
					if (is_object($objEmailElement)) {
						$varEmailId = $objEmailElement->getElement()->getApiName();
						if (empty($varEmailId)) $varEmailId = $objEmailElement->getId();
					}
	
					//*** Send the email.
					$objMail = new htmlMimeMail5();
					$objMail->setHTMLEncoding(new Base64Encoding());
					$objMail->setTextCharset("utf-8");
					$objMail->setHTMLCharset("utf-8");
					$objMail->setHeadCharset("utf-8");
					$objMail->setFrom($objSenderEmail->getField("SenderEmail")->getHtmlValue());
					$objMail->setSubject($objSenderEmail->getField("Subject")->getHtmlValue());
					$objMail->setText($strTextBody);
					$objMail->setHTML($strHtmlBody);
					if (!$objMail->send(array($this->__validForm->getValidField("formfield_" . strtolower($varEmailId))->getValue()))) {
						echo $objMail->errors;
					}
				}
	
				$strReturn = $this->__formElement->getField("ThanksBody")->getHtmlValue();
			} else {
				$strReturn = $this->__formElement->getField("ThanksBody")->getHtmlValue();
			}
		} else {
			$strReturn = $this->__validForm->toHtml($blnClientSide);
		}

		return $strReturn;
	}
	
	protected function renderParagraph(&$objParent, $objElement) {
		$objReturn = $objParent->addParagraph($objElement->getField("Body")->getHtmlValue(), $objElement->getField("Title")->getHtmlValue());
		
		return $objReturn;
	}
	
	protected function renderFieldset(&$objParent, $objElement) {
		$objReturn = $objParent->addFieldset($objElement->getField("Title")->getHtmlValue(), $objElement->getField("TipTitle")->getHtmlValue(), $objElement->getField("TipBody")->getHtmlValue());
		
		return $objReturn;
	}
	
	protected function renderArea(&$objParent, $objElement) {
		$blnDynamic = ($objElement->getField("DynamicLabel")->getHtmlValue() != "") ? true : false;
		
		$objReturn = $objParent->addArea(
			$objElement->getField("Label")->getHtmlValue(), 
			$objElement->getField("Active")->getValue(), 
			$this->generateId($objElement), 
			$objElement->getField("Selected")->getValue(),
			array(
				"dynamic" => $blnDynamic,
				"dynamicLabel" => $objElement->getField("DynamicLabel")->getHtmlValue()
			)
		);
		
		$objFields = $objElement->getElementsByTemplate(array("Field", "ListField", "MultiField"));
		foreach ($objFields as $objField) {									
			switch ($objField->getTemplateName()) {
				case "Field":
					$this->renderField($objReturn, $objField);
					break;
					
				case "ListField":
					$this->renderListField($objReturn, $objField);
					break;
					
				case "MultiField":
					$this->renderMultiField($objReturn, $objField);
					break;
			}
		}
		
		return $objReturn;
	}
	
	protected function renderMultiField(&$objParent, $objElement) {
		$blnDynamic = ($objElement->getField("DynamicLabel")->getHtmlValue() != "") ? true : false;
		
		$objReturn = $objParent->addMultiField(
			$objElement->getField("Label")->getHtmlValue(),
			array(
				"dynamic" => $blnDynamic,
				"dynamicLabel" => $objElement->getField("DynamicLabel")->getHtmlValue()
			)
		);
		
		$objFields = $objElement->getElementsByTemplate(array("Field", "ListField"));
		foreach ($objFields as $objField) {									
			switch ($objField->getTemplateName()) {
				case "Field":
					$this->renderField($objReturn, $objField);
					break;
					
				case "ListField":
					$this->renderListField($objReturn, $objField);
					break;
			}
		}
		
		return $objReturn;
	}
	
	protected function renderField(&$objParent, $objElement, $blnJustRender = false) {
		$blnDynamic = ($objElement->getField("DynamicLabel")->getHtmlValue() != "") ? true : false;

		$validationRules = array(
			"maxLength" => $objElement->getField("MaxLength")->getValue(), 
			"minLength" => $objElement->getField("MinLength")->getValue(), 
			"required" => $objElement->getField("Required")->getValue()
		);
		
		$arrCustomTypes = array(VFORM_CUSTOM, VFORM_CUSTOM_TEXT);
		$intType = $objElement->getField("Type")->getValue();
		if (!empty($intType)) {
			if (in_array(constant($intType), $arrCustomTypes)) {
				$validationRules["validation"] = $objElement->getField("Validation")->getValue();
			}
		} else {
			throw new Exception("Field type is empty in element " . $objElement->getId(), E_ERROR);
		}

		if (get_class($objParent) == "VF_MultiField") {
			// Add field without the label.
			$objReturn = $objParent->addField(
				$this->generateId($objElement),
				constant($objElement->getField("Type")->getValue()), 
				$validationRules,
				array(
					"maxLength" => $this->__maxLengthAlert, 
					"minLength" => $this->__minLengthAlert, 
					"required" => $this->__requiredAlert, 
					"type" => $objElement->getField("TypeAlert")->getHtmlValue()
				), 
				array(
					"class" => $objElement->getField("Class")->getHtmlValue(),
					"style" => $objElement->getField("Style")->getHtmlValue(),
					"tip" => $objElement->getField("Tip")->getHtmlValue(),
					"default" => $objElement->getField("DefaultValue")->getHtmlValue(),
					"hint" => $objElement->getField("HintValue")->getHtmlValue(),
					"dynamic" => $blnDynamic,
					"dynamicLabel" => $objElement->getField("DynamicLabel")->getHtmlValue()
				),
				$blnJustRender
			);
		} else {
			// Add field with label.
			$objReturn = $objParent->addField(
				$this->generateId($objElement), 
				$objElement->getField("Label")->getHtmlValue(), 
				constant($objElement->getField("Type")->getValue()), 
				$validationRules,
				array(
					"maxLength" => $this->__maxLengthAlert, 
					"minLength" => $this->__minLengthAlert, 
					"required" => $this->__requiredAlert, 
					"type" => $objElement->getField("TypeAlert")->getHtmlValue()
				), 
				array(
					"class" => $objElement->getField("Class")->getHtmlValue(),
					"style" => $objElement->getField("Style")->getHtmlValue(),
					"tip" => $objElement->getField("Tip")->getHtmlValue(),
					"default" => $objElement->getField("DefaultValue")->getHtmlValue(),
					"hint" => $objElement->getField("HintValue")->getHtmlValue(),
					"dynamic" => $blnDynamic,
					"dynamicLabel" => $objElement->getField("DynamicLabel")->getHtmlValue()
				),
				$blnJustRender
			);
		}	
		
		return $objReturn;
	}
	
	protected function renderListField(&$objParent, $objElement) {
		// Pre loop options for auto generation of options.
		$blnAutoOptions = FALSE;
		$objOptions = $objElement->getElementsByTemplate("ListOption");
		foreach ($objOptions as $objOption) {
			switch ($objOption->getName()) {
				case "Start":
					$intStart = $objOption->getField("Value")->getHtmlValue();
					$blnAutoOptions = TRUE;
					break;
				case "End":
					$intEnd = $objOption->getField("Value")->getHtmlValue();
					$blnAutoOptions = TRUE;
					break;
				default:
					break 2;
			}
		}
		
		$blnDynamic = ($objElement->getField("DynamicLabel")->getHtmlValue() != "") ? true : false;

		$arrMeta = array(
			"class" => $objElement->getField("Class")->getHtmlValue(),
			"style" => $objElement->getField("Style")->getHtmlValue(),
			"tip" => $objElement->getField("Tip")->getHtmlValue(),
			"hint" => $objElement->getField("HintValue")->getHtmlValue(),
			"dynamic" => $blnDynamic,
			"dynamicLabel" => $objElement->getField("DynamicLabel")->getHtmlValue()
		);
		if ($blnAutoOptions && isset($intStart) && isset($intEnd)) {
			$arrMeta["start"] = $intStart;
			$arrMeta["end"] = $intEnd;
		}
                                            
		$objReturn = $objParent->addField(
			$this->generateId($objElement), 
			$objElement->getField("Label")->getHtmlValue(), 
			constant($objElement->getField("Type")->getValue()), 
			array(
				"maxLength" => $objElement->getField("MaxLength")->getValue(), 
				"minLength" => $objElement->getField("MinLength")->getValue(), 
				"required" => $objElement->getField("Required")->getValue()
			), 
			array(
				"maxLength" => $this->__maxLengthAlert, 
				"minLength" => $this->__minLengthAlert, 
				"required" => $this->__requiredAlert, 
				"type" => $objElement->getField("TypeAlert")->getHtmlValue()
			), 
			$arrMeta
		);
		
		if (!$blnAutoOptions) {
			$objOptions = $objElement->getElementsByTemplate(array("ListOption", "TargetField"));
			foreach ($objOptions as $objOption) {
				switch ($objOption->getTemplateName()) {
					case "ListOption":
						$objReturn->addField($objOption->getField("Label")->getHtmlValue(), $objOption->getField("Value")->getHtmlValue(), $objOption->getField("Selected")->getValue());
						break;
					case "TargetField":
						$objReturn->addFieldObject($this->renderField($this->__validForm, $objOption, true));
						// $this->renderField($objParent, $objOption, true);
						break;
				}
			}
		}
		
		return $objReturn;
	}
	
	protected function generateId($objElement) {
		$strApiName = $objElement->getElement()->getApiName();
		$strReturn = (empty($strApiName)) ? "formfield_" . $objElement->getId() : "formfield_" . strtolower($strApiName);
		
		return $strReturn;
	}
	
}

?>