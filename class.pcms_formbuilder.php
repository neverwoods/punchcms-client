<?php

/**
 * 
 * Holds the PunchCMS Valid Form classes.
 * Depends on ValidForm Builder and htmlMimeMail5.
 * @author felix
 * @version 0.1.7.7
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
				$this->sendRecipientMail();	
				$this->sendSenderMail();
	
				$strReturn = $this->__formElement->getField("ThanksBody")->getHtmlValue();
			} else {
				$strReturn = $this->__formElement->getField("ThanksBody")->getHtmlValue();
			}
		} else {
			$strReturn = $this->__validForm->toHtml($blnClientSide);
		}

		return $strReturn;
	}
	
	public function sendRecipientMail($objRecipientEmail = NULL, $blnInlcudeFormValues = TRUE) {
		$objCms = PCMS_Client::getInstance();
		$arrToSend = array();
		
		if (is_null($objRecipientEmail)) {
			$objRecipientEmails = $this->__formElement->getElementsByTemplate("RecipientEmail");	
			foreach ($objRecipientEmails as $objRecipientEmail) {
				$arrToSend[] = $objRecipientEmail;
			}
		} else {
			$arrToSend[] = $objRecipientEmail;
		}
		
		foreach ($arrToSend as $objRecipientEmail) {
			$strHtmlBody = "<html><head><title></title></head><body>";
			if ($blnInlcudeFormValues) {
				$strHtmlBody .= sprintf($objRecipientEmail->getField("Body")->getHtmlValue(), $this->__validForm->valuesAsHtml(TRUE));
			} else {
				$strHtmlBody .= $objRecipientEmail->getField("Body")->getHtmlValue();
			}
			$strHtmlBody .= "</body></html>";
	
			$varEmailId = $objRecipientEmail->getField("SenderEmail")->getValue();
			$objEmailElement = $objCms->getElementById($varEmailId);
			$strFrom = "webserver";
			if (is_object($objEmailElement)) {
				$varEmailId = $objEmailElement->getElement()->getApiName();
				if (empty($varEmailId)) $varEmailId = $objEmailElement->getId();
				$strFrom = $this->__validForm->getValidField("formfield_" . strtolower($varEmailId))->getValue();
			}
			
			$strErrors = $this->sendMail($objRecipientEmail->getField("Subject")->getHtmlValue(), 
				$strHtmlBody, 
				$strFrom, 
				explode(",", $objRecipientEmail->getField("RecipientEmail")->getHtmlValue())
			);
			if (!empty($strErrors)) echo $strErrors;
		}
	}
	
	public function sendSenderMail($objSenderEmail = NULL, $blnInlcudeFormValues = TRUE) {
		$objCms = PCMS_Client::getInstance();
		$arrToSend = array();
		
		if (is_null($objSenderEmail)) {
			$objSenderEmails = $this->__formElement->getElementsByTemplate("SenderEmail");	
			foreach ($objSenderEmails as $objSenderEmail) {
				$arrToSend[] = $objSenderEmail;
			}
		} else {
			$arrToSend[] = $objSenderEmail;
		}
		
		foreach ($arrToSend as $objRecipientEmail) {
			$strHtmlBody = "<html><head><title></title></head><body>";
			if ($blnInlcudeFormValues) {
				$strHtmlBody .= sprintf($objSenderEmail->getField("Body")->getHtmlValue(), $this->__validForm->valuesAsHtml(TRUE));
			} else {
				$strHtmlBody .= $objSenderEmail->getField("Body")->getHtmlValue();
			}
			$strHtmlBody .= "</body></html>";

			$varEmailId = $objSenderEmail->getField("RecipientEmail")->getValue();
			$objEmailElement = $objCms->getElementById($varEmailId);
			if (is_object($objEmailElement)) {
				$varEmailId = $objEmailElement->getElement()->getApiName();
				if (empty($varEmailId)) $varEmailId = $objEmailElement->getId();
			}
			
			$strErrors = $this->sendMail($objSenderEmail->getField("Subject")->getHtmlValue(), 
				$strHtmlBody, 
				$objSenderEmail->getField("SenderEmail")->getHtmlValue(), 
				array($this->__validForm->getValidField("formfield_" . strtolower($varEmailId))->getValue())
			);
			if (!empty($strErrors)) echo $strErrors;
		}
	}
	
	public function sendMail($strSubject, $strHtmlBody, $strSender, $arrRecipients) {
		$strReturn = "";
		
		//*** Build the e-mail.
		$strTextBody = str_replace("<br /> ", "<br />", $strHtmlBody);
		$strTextBody = str_replace("<br />", "\n", $strTextBody);
		$strTextBody = str_replace("&nbsp;", "", $strTextBody);
		$strTextBody = strip_tags($strTextBody);
		$strTextBody = html_entity_decode($strTextBody, ENT_COMPAT, "UTF-8");

		//*** Send the email.
		$objMail = new htmlMimeMail5();
		$objMail->setHTMLEncoding(new Base64Encoding());
		$objMail->setTextCharset("utf-8");
		$objMail->setHTMLCharset("utf-8");
		$objMail->setHeadCharset("utf-8");
		$objMail->setFrom($strSender);
		$objMail->setSubject($strSubject);
		$objMail->setText($strTextBody);
		$objMail->setHTML($strHtmlBody);
		if (!$objMail->send($arrRecipients)) {
			$strReturn = $objMail->errors;
		}
		
		return $strReturn;
	}
	
	private function renderParagraph(&$objParent, $objElement) {
		$objReturn = $objParent->addParagraph($objElement->getField("Body")->getHtmlValue(), $objElement->getField("Title")->getHtmlValue());
		
		return $objReturn;
	}
	
	private function renderFieldset(&$objParent, $objElement) {
		$objReturn = $objParent->addFieldset($objElement->getField("Title")->getHtmlValue(), $objElement->getField("TipTitle")->getHtmlValue(), $objElement->getField("TipBody")->getHtmlValue());
		
		return $objReturn;
	}
	
	private function renderArea(&$objParent, $objElement) {
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
	
	private function renderMultiField(&$objParent, $objElement) {
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
	
	private function renderField(&$objParent, $objElement) {
		$blnDynamic = ($objElement->getField("DynamicLabel")->getHtmlValue() != "") ? true : false;
		
		$arrValidationRules = array(
			"maxLength" => $objElement->getField("MaxLength")->getValue(), 
			"minLength" => $objElement->getField("MinLength")->getValue(), 
			"required" => $objElement->getField("Required")->getValue()
		);
		
		$arrValidationErrors = array(
			"maxLength" => $this->__maxLengthAlert, 
			"minLength" => $this->__minLengthAlert, 
			"required" => $this->__requiredAlert, 
			"type" => $objElement->getField("TypeAlert")->getHtmlValue()
		);
		
		$arrMeta = array(
			"class" => $objElement->getField("Class")->getHtmlValue(),
			"style" => $objElement->getField("Style")->getHtmlValue(),
			"tip" => $objElement->getField("Tip")->getHtmlValue(),
			"default" => $objElement->getField("DefaultValue")->getHtmlValue(),
			"hint" => $objElement->getField("HintValue")->getHtmlValue(),
			"dynamic" => $blnDynamic,
			"dynamicLabel" => $objElement->getField("DynamicLabel")->getHtmlValue()
		);
					
		switch (get_class($objParent)) {
			case "VF_MultiField":
				$objReturn = $objParent->addField(
					$this->generateId($objElement), 
					constant($objElement->getField("Type")->getValue()), 
					$arrValidationRules,
					$arrValidationErrors,
					$arrMeta
				);
				
				break;
				
			default:
				$objReturn = $objParent->addField(
					$this->generateId($objElement), 
					$objElement->getField("Label")->getHtmlValue(), 
					constant($objElement->getField("Type")->getValue()), 
					$arrValidationRules,
					$arrValidationErrors,
					$arrMeta
				);
		}
		
		return $objReturn;
	}
	
	private function renderListField(&$objParent, $objElement) {
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

		$arrValidationRules = array(
			"maxLength" => $objElement->getField("MaxLength")->getValue(), 
			"minLength" => $objElement->getField("MinLength")->getValue(), 
			"required" => $objElement->getField("Required")->getValue()
		);
		
		$arrValidationErrors = array(
			"maxLength" => $this->__maxLengthAlert, 
			"minLength" => $this->__minLengthAlert, 
			"required" => $this->__requiredAlert, 
			"type" => $objElement->getField("TypeAlert")->getHtmlValue()
		);
		
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
					
		switch (get_class($objParent)) {
			case "VF_MultiField":
				$objReturn = $objParent->addField(
					$this->generateId($objElement), 
					constant($objElement->getField("Type")->getValue()), 
					$arrValidationRules, 
					$arrValidationErrors,
					$arrMeta
				);
				
				break;
				
			default:
				$objReturn = $objParent->addField(
					$this->generateId($objElement), 
					$objElement->getField("Label")->getHtmlValue(), 
					constant($objElement->getField("Type")->getValue()), 
					$arrValidationRules, 
					$arrValidationErrors,
					$arrMeta
				);
		}
		
		if (!$blnAutoOptions) {
			$objOptions = $objElement->getElementsByTemplate("ListOption");
			foreach ($objOptions as $objOption) {
				$objReturn->addField($objOption->getField("Label")->getHtmlValue(), $objOption->getField("Value")->getHtmlValue(), $objOption->getField("Selected")->getValue());
			}
		}
		
		return $objReturn;
	}
	
	private function generateId($objElement) {
		$strApiName = $objElement->getElement()->getApiName();
		$strReturn = (empty($strApiName)) ? "formfield_" . $objElement->getId() : "formfield_" . strtolower($strApiName);
		
		return $strReturn;
	}
	
}

?>