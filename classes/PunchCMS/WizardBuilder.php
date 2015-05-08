<?php

namespace PunchCMS;

use ValidFormBuilder\ValidWizard;
use PunchCMS\Client\Client;

/**
 * Holds the PunchCMS Valid Form classes.
 *
 * Depends on ValidForm Builder library including the ValidWizard 3 extension and htmlMimeMail5.
 *
 * @author Robin van Baalen <robin@neverwoods.com>
 * @version 2.0
 */
class WizardBuilder extends FormBuilder
{
    public function __construct($objForm, $strAction = null)
    {
        $this->formElement = $objForm;
        $strName = $objForm->getName();
        $strName = (empty($strName)) ? $objForm->getId() : strtolower($strName);
        $this->validForm = new ValidWizard("validwizard_" . $strName, $this->formElement->getField("RequiredBody")->getHtmlValue(), $strAction);

        $blnShowOverview = !!$this->formElement->getField("ShowOverview")->getHtmlValue();
        if ($blnShowOverview) {
            $this->validForm->addConfirmPage();
        }
    }

    /**
     * Get the internal ValidWizard object.
     *
     * @throws Exception
     * @return ValidWizard Instance of ValidWizard
     */
    public function getValidWizard()
    {
        $varReturn = null;
        if (is_object($this->validForm)) {
            $varReturn = $this->validForm;
        } else {
            throw new \Exception("ValidForm is not yet initiated. Could not load ValidForm from PCMS_FormBuilder.", E_ERROR);
        }

        return $varReturn;
    }

    public function buildForm($blnHandle = true, $blnClientSide = true)
    {
        $objCms = Client::getInstance();

        $strReturn = "";

        $this->maxLengthAlert = $this->formElement->getField("AlertMaxLength")->getHtmlValue();
        $this->minLengthAlert = $this->formElement->getField("AlertMinLength")->getHtmlValue();
        $this->requiredAlert = $this->formElement->getField("AlertRequired")->getHtmlValue();

        $this->validForm->setRequiredStyle($this->formElement->getField("RequiredIndicator")->getHtmlValue());
        $this->validForm->setMainAlert($this->formElement->getField("AlertMain")->getHtmlValue());

        //*** Form starts here.
        $objPages = $this->formElement->getElementsByTemplate(array("Page", "Paragraph"));
        foreach ($objPages as $objPage) {
            if (get_class($objPage) == "Hidden") {
                continue;
            }

            $objParent = $this->renderPage($this->validForm, $objPage);

            $objFieldsets = $objPage->getElementsByTemplate(array("Fieldset", "Paragraph"));
            foreach ($objFieldsets as $objFieldset) {
                switch ($objFieldset->getTemplateName()) {
                    case "Paragraph":
                        $this->renderParagraph($this->validForm, $objFieldset);
                        break;
                    case "Fieldset":
                        $this->renderFieldset($this->validForm, $objFieldset);

                        $objFields = $objFieldset->getElementsByTemplate(array("Field", "Area", "ListField", "MultiField"));
                        foreach ($objFields as $objField) {
                            switch ($objField->getTemplateName()) {
                                case "Field":
                                    $this->renderField($this->validForm, $objField);
                                    break;

                                case "ListField":
                                    $this->renderListField($this->validForm, $objField);
                                    break;

                                case "Area":
                                    $this->renderArea($this->validForm, $objField);
                                    break;

                                case "MultiField":
                                    $this->renderMultiField($this->validForm, $objField);
                                    break;

                            }
                        }
                }
            }
        }

        //*** Add conditions
        foreach ($objPages as $objPage) {
            if (get_class($objPage) == "Hidden") {
                continue;
            }

            $objFieldsets = $objPage->getElementsByTemplate(array("Fieldset", "Paragraph"));
            foreach ($objFieldsets as $objFieldset) {
                $this->addConditions($objFieldset);

                $objFields = $objFieldset->getElementsByTemplate(array("Field", "Area", "ListField", "MultiField"));
                foreach ($objFields as $objField) {
                    $this->addConditions($objField);

                    $objFields = $objField->getElementsByTemplate(array("Field", "ListField", "MultiField"));
                    foreach ($objFields as $objField) {
                        $this->addConditions($objField);
                    }
                }
            }
        }

        $this->validForm->setSubmitLabel($this->formElement->getField("SendLabel")->getHtmlValue());

        if ($blnHandle) {
            if ($this->validForm->isSubmitted() && $this->validForm->isValid()) {
                $strReturn = $this->formElement->getField("ThanksBody")->getHtmlValue();
            } else {
                $strReturn = $this->validForm->toHtml($blnClientSide);
            }
        }

        return $strReturn;
    }

    private function renderPage(&$objParent, $objElement)
    {
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

    private function generatePageId($objElement)
    {
        $strApiName = $objElement->getElement()->getApiName();
        return (empty($strApiName)) ? "page_" . $objElement->getId() : "page_" . strtolower($strApiName);
    }
}
