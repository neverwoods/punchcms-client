<?php
/**
 * Holds the PunchCMS Valid Form classes.
 * Depends on ValidForm Builder library including the ValidWizard extension and htmlMimeMail5.
 *
 * @author Robin van Baalen <robin@neverwoods.com>
 * @version 1.0
 *
 */
class PCMS_WizardBuilder extends PCMS_FormBuilder {

	public function __construct($objForm, $strAction = null) {
		$this->__formElement = $objForm;
		$strName = $objForm->getName();
		$strName = (empty($strName)) ? $objForm->getId() : strtolower($strName);
		$this->__validForm = new ValidWizard("validwizard_" . $strName, $this->__formElement->getField("RequiredBody")->getHtmlValue(), $strAction);

		$blnShowOverview = !!$this->__formElement->getField("ShowOverview")->getHtmlValue();
		if ($blnShowOverview) {
			$this->__validForm->addConfirmPage();
		}
	}

	/**
	 * Get the internal ValidWizard object.
	 *
	 * @throws Exception
	 * @return ValidWizard Instance of ValidWizard
	 */
	public function getValidWizard() {
		$varReturn = null;
		if (is_object($this->__validForm)) {
			$varReturn = $this->__validForm;
		} else {
			throw new Exception("ValidForm is not yet initiated. Could not load ValidForm from PCMS_FormBuilder.", E_ERROR);
		}

		return $varReturn;
	}

	public function buildForm($blnHandle = TRUE, $blnClientSide = TRUE) {
		$objCms = PCMS_Client::getInstance();

		$strReturn = "";

		$this->__maxLengthAlert = $this->__formElement->getField("AlertMaxLength")->getHtmlValue();
		$this->__minLengthAlert = $this->__formElement->getField("AlertMinLength")->getHtmlValue();
		$this->__requiredAlert = $this->__formElement->getField("AlertRequired")->getHtmlValue();

		$this->__validForm->setRequiredStyle($this->__formElement->getField("RequiredIndicator")->getHtmlValue());
		$this->__validForm->setMainAlert($this->__formElement->getField("AlertMain")->getHtmlValue());

		//*** Form starts here.
		$objPages = $this->__formElement->getElementsByTemplate(array("Page", "Paragraph"));
		foreach ($objPages as $objPage) {
			if (get_class($objPage) == "VF_Hidden") continue;

			$objParent = $this->renderPage($this->__validForm, $objPage);

			$objFieldsets = $objPage->getElementsByTemplate(array("Fieldset", "Paragraph"));
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
		}

		//*** Add conditions
		foreach ($objPages as $objPage) {
			if (get_class($objPage) == "VF_Hidden") continue;

			$objFieldsets = $objPage->getElementsByTemplate(array("Fieldset", "Paragraph"));
			foreach ($objFieldsets as $objFieldset) {
				$this->addConditions($objFieldset);

				$objFields = $objFieldset->getElementsByTemplate(array("Field", "Area", "ListField", "MultiField"));
				foreach ($objFields as $objField) {
					$this->addConditions($objField);
				}
			}
		}

		$this->__validForm->setSubmitLabel($this->__formElement->getField("SendLabel")->getHtmlValue());

		if ($blnHandle) {
			if ($this->__validForm->isSubmitted() && $this->__validForm->isValid()) {
				$strReturn = $this->__formElement->getField("ThanksBody")->getHtmlValue();
			} else {
				$strReturn = $this->__validForm->toHtml($blnClientSide);
			}
		}

		return $strReturn;
	}

	private function renderPage(&$objParent, $objElement) {
		$arrFieldMeta = array();

		// Add short label if not empty.
		$strSummaryLabel = $objElement->getField("SummaryLabel")->getHtmlValue();
		if (!empty($strSummaryLabel)) {
			$arrFieldMeta["summaryLabel"] = $strSummaryLabel;
		}

		$objReturn = $objParent->addPage($this->generatePageId($objElement), $objElement->getField("Title")->getHtmlValue(), $arrFieldMeta);

		// Store the PunchCMS ElementID in this field to have a reference for later use.
		$objReturn->setData("eid", $objElement->getId());

		$this->register($objElement, $objReturn);
		return $objReturn;
	}

	private function generatePageId($objElement) {
		$strApiName = $objElement->getElement()->getApiName();
		return (empty($strApiName)) ? "page_" . $objElement->getId() : "page_" . strtolower($strApiName);
	}

}

?>