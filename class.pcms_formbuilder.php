<?php

/**************************************************************************
* PunchCMS FormBuilder class v0.1.7.4
* Holds the PunchCMS Valid Form classes.
**************************************************************************/

class PCMS_FormBuilder {
	private $__formElement	= FALSE;
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
	
		$strMaxLength = $this->__formElement->getField("AlertMaxLength")->getHtmlValue();
		$strMinLength = $this->__formElement->getField("AlertMinLength")->getHtmlValue();
		$strRequired = $this->__formElement->getField("AlertRequired")->getHtmlValue();

		$this->__validForm->setRequiredStyle($this->__formElement->getField("RequiredIndicator")->getHtmlValue());
		$this->__validForm->setMainAlert($this->__formElement->getField("AlertMain")->getHtmlValue());

		//*** Form starts here.
		$objFieldsets = $this->__formElement->getElementsByTemplate(array("Fieldset", "Paragraph"));
		foreach ($objFieldsets as $objFieldset) {
			switch ($objFieldset->getTemplateName()) {
				case "Paragraph":
					$this->__validForm->addParagraph($objFieldset->getField("Body")->getHtmlValue(), $objFieldset->getField("Title")->getHtmlValue());
					break;
				case "Fieldset":
					$this->__validForm->addFieldset($objFieldset->getField("Title")->getHtmlValue(), $objFieldset->getField("TipTitle")->getHtmlValue(), $objFieldset->getField("TipBody")->getHtmlValue());

					$objFields = $objFieldset->getElementsByTemplate(array("Field", "Area", "ListField"));
					foreach ($objFields as $objField) {
						$strApiName = $objField->getElement()->getApiName();
						$strId = (empty($strApiName)) ? "formfield_" . $objField->getId() : "formfield_" . strtolower($strApiName);

						switch ($objField->getTemplateName()) {
							case "Field":
								$this->__validForm->addField($strId, 
									$objField->getField("Label")->getHtmlValue(), 
									constant($objField->getField("Type")->getValue()), 
									array(
										"maxLength" => $objField->getField("MaxLength")->getValue(), 
										"minLength" => $objField->getField("MinLength")->getValue(), 
										"required" => $objField->getField("Required")->getValue()
									), 
									array(
										"maxLength" => $strMaxLength, 
										"minLength" => $strMinLength, 
										"required" => $strRequired, 
										"type" => $objField->getField("TypeAlert")->getHtmlValue()
									), 
									array(
										"style" => $objField->getField("Style")->getHtmlValue(),
										"tip" => $objField->getField("Tip")->getHtmlValue()
									)
								);
								break;
							case "ListField":
								$objList = $this->__validForm->addField($strId, 
									$objField->getField("Label")->getHtmlValue(), 
									constant($objField->getField("Type")->getValue()), 
									array(
										"maxLength" => $objField->getField("MaxLength")->getValue(), 
										"minLength" => $objField->getField("MinLength")->getValue(), 
										"required" => $objField->getField("Required")->getValue()
									), 
									array(
										"maxLength" => $strMaxLength, 
										"minLength" => $strMinLength, 
										"required" => $strRequired, 
										"type" => $objField->getField("TypeAlert")->getHtmlValue()
									), 
									array(
										"style" => $objField->getField("Style")->getHtmlValue(),
										"tip" => $objField->getField("Tip")->getHtmlValue()
									)
								);

								$objOptions = $objField->getElementsByTemplate("ListOption");
								foreach ($objOptions as $objOption) {
									$objList->addField($objOption->getField("Label")->getHtmlValue(), $objOption->getField("Value")->getHtmlValue(), $objOption->getField("Selected")->getValue());
								}
								break;										
							case "Area":
								$objArea = $this->__validForm->addArea($objField->getField("Label")->getHtmlValue(), $objField->getField("Active")->getValue(), $strId, $objField->getField("Selected")->getValue());

								$objAreaFields = $objField->getElementsByTemplate(array("Field", "ListField"));
								foreach ($objAreaFields as $objAreaField) {
									$strApiName = $objAreaField->getElement()->getApiName();
									$strId = (empty($strApiName)) ? "formfield_" . $objAreaField->getId() : "formfield_" . strtolower($strApiName);
									
									switch ($objAreaField->getTemplateName()) {
										case "Field":
											$objArea->addField($strId, 
												$objAreaField->getField("Label")->getHtmlValue(), 
												constant($objAreaField->getField("Type")->getValue()), 
												array(
													"maxLength" => $objAreaField->getField("MaxLength")->getValue(), 
													"minLength" => $objAreaField->getField("MinLength")->getValue(), 
													"required" => $objAreaField->getField("Required")->getValue()
												), 
												array(
													"maxLength" => $strMaxLength, 
													"minLength" => $strMinLength, 
													"required" => $strRequired, 
													"type" => $objAreaField->getField("TypeAlert")->getHtmlValue()
												), 
												array(
													"style" => $objAreaField->getField("Style")->getHtmlValue(),
													"tip" => $objField->getField("Tip")->getHtmlValue()
												)
											);
											break;
										case "ListField":
											$objList = $objArea->addField($strId, 
												$objAreaField->getField("Label")->getHtmlValue(), 
												constant($objAreaField->getField("Type")->getValue()), 
												array(
													"maxLength" => $objAreaField->getField("MaxLength")->getValue(), 
													"minLength" => $objAreaField->getField("MinLength")->getValue(), 
													"required" => $objAreaField->getField("Required")->getValue()
												), 
												array(
													"maxLength" => $strMaxLength, 
													"minLength" => $strMinLength, 
													"required" => $strRequired, 
													"type" => $objAreaField->getField("TypeAlert")->getHtmlValue()
												), 
												array(
													"style" => $objAreaField->getField("Style")->getHtmlValue(),
													"tip" => $objField->getField("Tip")->getHtmlValue()
												)
											);

											$objOptions = $objAreaField->getElementsByTemplate("ListOption");
											foreach ($objOptions as $objOption) {
												$objList->addField($objOption->getField("Label")->getHtmlValue(), $objOption->getField("Value")->getHtmlValue(), $objOption->getField("Selected")->getValue());
											}
											break;
									}
								}
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
	
}

?>