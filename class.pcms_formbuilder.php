<?php

/**************************************************************************
* PunchCMS Form Builder class v0.1.2
* Holds the Valid FormBuilder Wrapper methods.
**************************************************************************/

class PCMS_FormBuilder {

	public static function buildForm($objForm) {
		$strReturn = "";
	
		$strMaxLength = $objForm->getField("AlertMaxLength")->getHtmlValue();
		$strMinLength = $objForm->getField("AlertMinLength")->getHtmlValue();
		$strRequired = $objForm->getField("AlertRequired")->getHtmlValue();

		$objValidForm = new ValidForm("validform_" . $objForm->getId(), $objForm->getField("RequiredBody")->getHtmlValue());
		$objValidForm->setRequiredStyle($objForm->getField("RequiredIndicator")->getHtmlValue());
		$objValidForm->setMainAlert($objForm->getField("AlertMain")->getHtmlValue());

		//*** Form starts here.
		$objFieldsets = $objForm->getElementsByTemplate(array("Fieldset", "Paragraph"));
		foreach ($objFieldsets as $objFieldset) {
			switch ($objFieldset->getTemplateName()) {
				case "Paragraph":
					$objValidForm->addParagraph($objFieldset->getField("Body")->getHtmlValue(), $objFieldset->getField("Title")->getHtmlValue());
					break;
				case "Fieldset":
					$objValidForm->addFieldset($objFieldset->getField("Title")->getHtmlValue(), $objFieldset->getField("TipTitle")->getHtmlValue(), $objFieldset->getField("TipBody")->getHtmlValue());

					$objFields = $objFieldset->getElementsByTemplate(array("Field", "Area", "ListField"));
					foreach ($objFields as $objField) {
						$strId = "formfield_" . $objField->getId();

						switch ($objField->getTemplateName()) {
							case "Field":
								$objValidForm->addField($strId, 
									$objField->getField("Label")->getHtmlValue(), 
									constant($objField->getField("Type")->getValue()), 
									array(
										"maxLength" => $objField->getField("MaxLength")->getValue(), 
										"minLength" => $objField->getField("MinLength")->getValue(), 
										"required" => $objField->getField("Required")->getValue()
									), 
									array(
										"maxLength" => $strMaxLength, 
										"required" => $strRequired, 
										"type" => $objField->getField("TypeAlert")->getHtmlValue()
									), 
									array(
										"style" => $objField->getField("Style")->getHtmlValue()
									)
								);
								break;
							case "ListField":
								$objList = $objValidForm->addField($strId, 
									$objField->getField("Label")->getHtmlValue(), 
									constant($objField->getField("Type")->getValue()), 
									array(
										"maxLength" => $objField->getField("MaxLength")->getValue(), 
										"minLength" => $objField->getField("MinLength")->getValue(), 
										"required" => $objField->getField("Required")->getValue()
									), 
									array(
										"maxLength" => $strMaxLength, 
										"required" => $strRequired, 
										"type" => $objField->getField("TypeAlert")->getHtmlValue()
									), 
									array(
										"style" => $objField->getField("Style")->getHtmlValue()
									)
								);

								$objOptions = $objField->getElementsByTemplate("ListOption");
								foreach ($objOptions as $objOption) {
									$objList->addField($objOption->getField("Label")->getHtmlValue(), $objOption->getField("Value")->getHtmlValue(), $objOption->getField("Selected")->getValue());
								}
								break;										
							case "Area":
								$objArea = $objValidForm->addArea($objField->getField("Label")->getHtmlValue(), $objField->getField("Active")->getValue(), $strId, $objField->getField("Selected")->getValue());

								$objAreaFields = $objField->getElementsByTemplate(array("Field", "ListField"));
								foreach ($objAreaFields as $objAreaField) {
									$strId = "formfield_" . $objAreaField->getId();

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
													"required" => $strRequired, 
													"type" => $objAreaField->getField("TypeAlert")->getHtmlValue()
												), 
												array(
													"style" => $objAreaField->getField("Style")->getHtmlValue()
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
													"required" => $strRequired, 
													"type" => $objAreaField->getField("TypeAlert")->getHtmlValue()
												), 
												array(
													"style" => $objAreaField->getField("Style")->getHtmlValue()
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

		$objValidForm->setSubmitLabel($objForm->getField("SendLabel")->getHtmlValue());

		if ($objValidForm->isSubmitted() && $objValidForm->isValid()) {
			$objRecipientEmails = $objForm->getElementsByTemplate("RecipientEmail");	
			foreach ($objRecipientEmails as $objRecipientEmail) {
				$strHtmlBody = "<html><head><title></title></head><body>";
				$strHtmlBody .= sprintf($objRecipientEmail->getField("Body")->getHtmlValue(), $objValidForm->valuesAsHtml(TRUE));
				$strHtmlBody .= "</body></html>";

				//*** Build the e-mail.
				$strTextBody = str_replace("<br />","\n",$strHtmlBody);
				$strTextBody = str_replace("&nbsp;","",$strTextBody);
				$strTextBody = strip_tags($strTextBody);

				//*** Send the email.
				$objMail = new htmlMimeMail5();
				$objMail->setTextCharset("utf-8");
				$objMail->setHTMLCharset("utf-8");
				$objMail->setFrom($objValidForm->getValidField("formfield_" . $objRecipientEmail->getField("SenderEmail")->getValue())->getValue());
				$objMail->setSubject($objRecipientEmail->getField("Subject")->getHtmlValue());
				$objMail->setText($strTextBody);
				$objMail->setHTML($strHtmlBody);
				if (!$objMail->send(array($objRecipientEmail->getField("RecipientEmail")->getHtmlValue()))) {
					echo $objMail->getErrors();
				}
			}

			$objSenderEmails = $objForm->getElementsByTemplate("SenderEmail");	
			foreach ($objSenderEmails as $objSenderEmail) {
				$strHtmlBody = "<html><head><title></title></head><body>";
				$strHtmlBody .= sprintf($objSenderEmail->getField("Body")->getHtmlValue(), $objValidForm->valuesAsHtml(TRUE));
				$strHtmlBody .= "</body></html>";

				//*** Build the e-mail.
				$strTextBody = str_replace("<br />","\n",$strHtmlBody);
				$strTextBody = str_replace("&nbsp;","",$strTextBody);
				$strTextBody = strip_tags($strTextBody);

				//*** Send the email.
				$objMail = new htmlMimeMail5();
				$objMail->setTextCharset("utf-8");
				$objMail->setHTMLCharset("utf-8");
				$objMail->setFrom($objSenderEmail->getField("SenderEmail")->getHtmlValue());
				$objMail->setSubject($objSenderEmail->getField("Subject")->getHtmlValue());
				$objMail->setText($strTextBody);
				$objMail->setHTML($strHtmlBody);
				if (!$objMail->send(array($objValidForm->getValidField("formfield_" . $objSenderEmail->getField("RecipientEmail")->getValue())->getValue()))) {
					echo $objMail->getErrors();
				}
			}

			$strReturn = $objForm->getField("ThanksBody")->getHtmlValue();
		} else {
			$strReturn = $objValidForm->toHtml();
		}

		return $strReturn;
	}
	
}

?>