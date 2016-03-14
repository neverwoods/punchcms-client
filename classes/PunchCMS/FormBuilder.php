<?php

namespace PunchCMS;

use ValidFormBuilder\ValidForm;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;
use ValidFormBuilder\Comparison;
use ValidFormBuilder\MultiField;

/**
 * Holds the PunchCMS Valid Form classes.
 *
 * Depends on ValidForm Builder 3 and htmlMimeMail5.
 *
 * @author Felix Langfeldt <felix@neverwoods.com>
 * @author Robin van Baalen <robin@neverwoods.com>
 *
 * @version 2.0
 */
class FormBuilder
{
    protected $formElement = false;
    protected $maxLengthAlert = "";
    protected $minLengthAlert = "";
    protected $requiredAlert = "";

    /**
     * This is a PunchCMS -> ValidForm element lookup array
     * @var array
     */
    protected $lookup = array();

    /**
     * @var \ValidFormBuider\ValidForm
     */
    public $validForm    = false;

    public function __construct($objForm, $strAction = null)
    {
        $this->formElement = $objForm;
        $strName = $objForm->getName();
        $strName = (empty($strName)) ? $objForm->getId() : strtolower($strName);
        $this->validForm = new ValidForm("validform_" . $strName, $this->formElement->getField("RequiredBody")->getHtmlValue(), $strAction);
    }

    /**
     * Get the internal ValidForm object.
     *
     * @throws Exception
     * @return ValidForm Instance of ValidForm
     */
    public function getValidForm()
    {
        $varReturn = null;
        if (is_object($this->validForm)) {
            $varReturn = $this->validForm;
        } else {
            throw new \Exception("ValidForm is not yet initiated. Could not load ValidForm from PCMS_FormBuilder.", E_ERROR);
        }

        return $varReturn;
    }

    public function buildForm($blnSend = true, $blnClientSide = true)
    {
        $objCms = \PunchCMS\Client\Client::getInstance();

        $strReturn = "";

        $this->maxLengthAlert = $this->formElement->getField("AlertMaxLength")->getHtmlValue();
        $this->minLengthAlert = $this->formElement->getField("AlertMinLength")->getHtmlValue();
        $this->requiredAlert = $this->formElement->getField("AlertRequired")->getHtmlValue();

        $this->validForm->setRequiredStyle($this->formElement->getField("RequiredIndicator")->getHtmlValue());
        $this->validForm->setMainAlert($this->formElement->getField("AlertMain")->getHtmlValue());

        //*** Form starts here.
        $objFieldsets = $this->formElement->getElementsByTemplate(array("Fieldset", "Paragraph"));
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

        //*** Add conditions
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

        $this->validForm->setSubmitLabel($this->formElement->getField("SendLabel")->getHtmlValue());

        if ($this->validForm->isSubmitted() && $this->validForm->isValid()) {
            if ($blnSend) {
                $objRecipientEmails = $this->formElement->getElementsByTemplate("RecipientEmail");
                foreach ($objRecipientEmails as $objRecipientEmail) {
                    $strHtmlBody = "<html><head><title></title></head><body>";
                    $strHtmlBody .= sprintf($objRecipientEmail->getField("Body")->getHtmlValue(), $this->validForm->valuesAsHtml(true));
                    $strHtmlBody .= "</body></html>";

                    $varEmailId = $objRecipientEmail->getField("SenderEmail")->getValue();
                    $objEmailElement = $objCms->getElementById($varEmailId);
                    $strFrom = "webserver";
                    if (is_object($objEmailElement)) {
                        $varEmailId = $objEmailElement->getElement()->getApiName();
                        if (empty($varEmailId)) {
                            $varEmailId = $objEmailElement->getId();
                        }
                        $strFrom = $this->validForm->getValidField("formfield_" . strtolower($varEmailId))->getValue();
                    }

                    $strErrors = $this->sendMail(
                        $objRecipientEmail->getField("Subject")->getHtmlValue(),
                        $strHtmlBody,
                        $strFrom,
                        explode(",", $objRecipientEmail->getField("RecipientEmail")->getHtmlValue())
                    );

                    if (!empty($strErrors)) {
                        throw new \Exception($strErrors, E_ERROR);
                    }
                }

                $objSenderEmails = $this->formElement->getElementsByTemplate("SenderEmail");
                foreach ($objSenderEmails as $objSenderEmail) {
                    $strHtmlBody = "<html><head><title></title></head><body>";
                    $strHtmlBody .= sprintf($objSenderEmail->getField("Body")->getHtmlValue(), $this->validForm->valuesAsHtml(true));
                    $strHtmlBody .= "</body></html>";

                    $varEmailId = $objSenderEmail->getField("RecipientEmail")->getValue();
                    $objEmailElement = $objCms->getElementById($varEmailId);
                    if (is_object($objEmailElement)) {
                        $varEmailId = $objEmailElement->getElement()->getApiName();
                        if (empty($varEmailId)) {
                            $varEmailId = $objEmailElement->getId();
                        }
                    }

                    $strErrors = $this->sendMail(
                        $objSenderEmail->getField("Subject")->getHtmlValue(),
                        $strHtmlBody,
                        $objSenderEmail->getField("SenderEmail")->getHtmlValue(),
                        array($this->validForm->getValidField("formfield_" . strtolower($varEmailId))->getValue())
                    );

                    if (!empty($strErrors)) {
                        throw new \Exception($strErrors, E_ERROR);
                    }
                }

                $strReturn = $this->formElement->getField("ThanksBody")->getHtmlValue();
            } else {
                $strReturn = $this->formElement->getField("ThanksBody")->getHtmlValue();
            }
        } else {
            $strReturn = $this->validForm->toHtml($blnClientSide);
        }

        return $strReturn;
    }

    public function sendMail($strSubject, $strHtmlBody, $strSender, $arrRecipients)
    {
        $strReturn = "";

        //*** Build the e-mail.
        $strTextBody = str_replace("<br /> ", "<br />", $strHtmlBody);
        $strTextBody = str_replace("<br />", "\n", $strTextBody);
        $strTextBody = str_replace("&nbsp;", "", $strTextBody);
        $strTextBody = strip_tags($strTextBody);
        $strTextBody = html_entity_decode($strTextBody, ENT_COMPAT, "UTF-8");

        //*** Send the email.
        $objMessage = new Message();
        $objMessage->setFrom($strSender)
            ->setSubject($strSubject)
            ->setBody($strTextBody)
            ->setHtmlBody($strHtmlBody);

        foreach ($arrRecipients as $strRecipient) {
            $objMessage->addTo($strRecipient);
        }

        $objMailer = new SendmailMailer();
        $objMailer->send($objMessage);

        return $strReturn;
    }

    public function addConditions(&$objSubject)
    {
        $objConditions= $objSubject->getElementsByTemplate("Condition");

        foreach ($objConditions as $objCondition) {
            try {
                $strProperty     = $objCondition->getField("Property")->getHtmlValue();
                $strValue         = $objCondition->getField("Value")->getHtmlValue();
                $blnValue        = ($strValue == "true") ? true : false;

                $strConstValue = $objCondition->getField("Type")->getHtmlValue();
                if (defined("\\ValidFormBuilder\\ValidForm::" . $strConstValue)) {
                    $constType = constant("\\ValidFormBuilder\\ValidForm::" . $strConstValue);
                } else {
                    throw new \Exception(
                        "Tried to get undefined constant '{$strConstValue}'. From element {$objCondition->getId()}",
                        E_ERROR
                    );
                }

                $arrComparisons = array();
                $objCmsComparisons = $objCondition->getElementsByTemplate("Comparison");
                foreach ($objCmsComparisons as $objCmsComparison) {
                    $objComparisonSubject = $objCmsComparison
                       ->getField("Subject")
                       ->getElement();

                    if (is_object($objComparisonSubject)) {
                        $objComparisonSubjectElement = $this
                            ->getFormElementById(
                                $objComparisonSubject
                                   ->getId()
                            );

                        //*** Get the comparison's value
                        $varValue = $objCmsComparison->getField("Value")->getHtmlValue();
                        $objValue = $objCmsComparison->getField("Value")->getElement();
                        if (is_object($objValue) && $objValue->getTemplateName() == "ListOption") {
                            // If the comparison's value is a deeplink to a list option, overwrite $varValue
                            $varValue = $objValue->getField("Value")->getHtmlValue();
                        }

                        array_push(
                            $arrComparisons,
                            new Comparison(
                                $objComparisonSubjectElement,
                                constant(
                                    "\\ValidFormBuilder\\ValidForm::" .
                                    $objCmsComparison
                                        ->getField("Comparison")
                                        ->getHtmlValue()
                                ),
                                $varValue
                            )
                        );
                    } else {
                        throw new \Exception("Failed to load comparison: {$objCmsComparison->getId()}", E_ERROR);
                    }
                }

                $objFormSubject = $this->getFormElementById($objSubject->getId());
                if (is_object($objFormSubject)) {
                    $objFormSubject->addCondition($strProperty, $blnValue, $arrComparisons, $constType);
                }

            } catch (\Exception $e) {
                throw new \Exception("Failed to add condition to field {$objSubject->getId()}. Error: " . $e->getMessage(), 1);
            }
        }
    }

    protected function register($objCmsElement, $objFormElement)
    {
        $this->lookup[$objCmsElement->getId()] = &$objFormElement;
    }

    protected function getFormElementById($intId = null)
    {
        $varReturn = false;

        if (!is_null($intId)) {
            $varReturn = (isset($this->lookup[$intId])) ? $this->lookup[$intId] : $varReturn;
        }

        return $varReturn;
    }

    protected function renderParagraph(&$objParent, $objElement)
    {
        $objReturn = $objParent->addParagraph($objElement->getField("Body")->getHtmlValue(), $objElement->getField("Title")->getHtmlValue());

        $this->register($objElement, $objReturn);
        return $objReturn;
    }

    protected function renderFieldset(&$objParent, $objElement)
    {
        $objReturn = $objParent->addFieldset($objElement->getField("Title")->getHtmlValue(), $objElement->getField("TipTitle")->getHtmlValue(), $objElement->getField("TipBody")->getHtmlValue());

        $this->register($objElement, $objReturn);
        return $objReturn;
    }

    protected function renderArea(&$objParent, $objElement)
    {
        $blnDynamic = ($objElement->getField("DynamicLabel")->getHtmlValue() != "") ? true : false;

        // Default area field meta
        $arrFieldMeta = array(
            "dynamic" => $blnDynamic,
            "dynamicLabel" => $objElement->getField("DynamicLabel")->getHtmlValue()
        );

        // Add short label if not empty.
        $strSummaryLabel = $objElement->getField("SummaryLabel")->getHtmlValue();
        if (!empty($strSummaryLabel)) {
            $arrFieldMeta["summaryLabel"] = $strSummaryLabel;
        }

        $objReturn = $objParent->addArea(
            $objElement->getField("Label")->getHtmlValue(),
            $objElement->getField("Active")->getValue(),
            $this->generateId($objElement),
            $objElement->getField("Selected")->getValue(),
            $arrFieldMeta
        );

        // Store the PunchCMS ElementID in this field to have a reference for later use.
        $objReturn->setData("eid", $objElement->getId());

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

        $this->register($objElement, $objReturn);
        return $objReturn;
    }

    protected function renderMultiField(&$objParent, $objElement)
    {
        $blnDynamic = ($objElement->getField("DynamicLabel")->getHtmlValue() != "") ? true : false;

        // Default area field meta
        $arrFieldMeta = array(
            "dynamic" => $blnDynamic,
            "dynamicLabel" => $objElement->getField("DynamicLabel")->getHtmlValue()
        );

        // Add short label if not empty.
        $strSummaryLabel = $objElement->getField("SummaryLabel")->getHtmlValue();
        if (!empty($strSummaryLabel)) {
            $arrFieldMeta["summaryLabel"] = $strSummaryLabel;
        }

        $objReturn = $objParent->addMultiField(
            $objElement->getField("Label")->getHtmlValue(),
            $arrFieldMeta
        );

        // Store the PunchCMS ElementID in this field to have a reference for later use.
        $objReturn->setData("eid", $objElement->getId());

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

            $this->addConditions($objField);
        }

        $this->register($objElement, $objReturn);

        return $objReturn;
    }

    protected function renderField(&$objParent, $objElement, $blnJustRender = false)
    {
        $blnDynamic = ($objElement->getField("DynamicLabel")->getHtmlValue() != "") ? true : false;

        // Default validation rules
        $validationRules = array(
            "maxLength" => $objElement->getField("MaxLength")->getValue(),
            "minLength" => $objElement->getField("MinLength")->getValue(),
            "required" => $objElement->getField("Required")->getValue()
        );

        // Default field meta
        $arrFieldMeta = array(
            "class" => $objElement->getField("Class")->getHtmlValue(),
            "fieldclass" => $objElement->getField("FieldClass")->getHtmlValue(),
            "fieldstyle" => $objElement->getField("Style")->getHtmlValue(),
            "tip" => $objElement->getField("Tip")->getHtmlValue(),
            "default" => $objElement->getField("DefaultValue")->getHtmlValue(),
            "hint" => $objElement->getField("HintValue")->getHtmlValue(),
            "dynamic" => $blnDynamic,
            "dynamicLabel" => $objElement->getField("DynamicLabel")->getHtmlValue()
        );

        $strData = $objElement->getField("Data")->getHtmlValue();
        if (!empty($strData)) {
            $arrData = explode("<br />", $strData);
            foreach ($arrData as $strDataLine) {
                $value = explode(":", $strDataLine);
                $arrFieldMeta["fielddata-" . trim($value[0])] = trim($value[1]);
            }
        }

        // Get the boolean readonly value and convert it to a string. This renders
        // XHTML valid code like 'required="required"' instead of 'required="true"' (invalid)
        $blnReadOnly = $objElement->getField("ReadOnly")->getValue();
        if ($blnReadOnly) {
            $arrFieldMeta["fieldreadonly"] = "readonly";
        }

        $arrCustomTypes = array(ValidForm::VFORM_CUSTOM, ValidForm::VFORM_CUSTOM_TEXT);
        $intType = $objElement->getField("Type")->getValue();
        if (!empty($intType)) {
            if (in_array(constant("\\ValidFormBuilder\\ValidForm::" . $intType), $arrCustomTypes)) {
                $validationRules["validation"] = $objElement->getField("Validation")->getValue();
            }
        } else {
            throw new \Exception("Field type is empty in element " . $objElement->getId(), E_ERROR);
        }

        if ($objParent instanceof MultiField) {
            // Add field without the label.
            $objReturn = $objParent->addField(
                $this->generateId($objElement),
                constant("\\ValidFormBuilder\\ValidForm::" . $objElement->getField("Type")->getValue()),
                $validationRules,
                array(
                    "maxLength" => $this->maxLengthAlert,
                    "minLength" => $this->minLengthAlert,
                    "required" => $this->requiredAlert,
                    "type" => $objElement->getField("TypeAlert")->getHtmlValue()
                ),
                $arrFieldMeta,
                $blnJustRender
            );

            // Store the PunchCMS ElementID in this field to have a reference for later use.
            $objReturn->setData("eid", $objElement->getId());
        } else {
            // Set short label if set.
            $strSummaryLabel = $objElement->getField("SummaryLabel")->getHtmlValue();
            if (!empty($strSummaryLabel)) {
                $arrFieldMeta["summaryLabel"] = $strSummaryLabel;
            }

            $objReturn = $objParent->addField(
                $this->generateId($objElement),
                $objElement->getField("Label")->getHtmlValue(),
                constant("\\ValidFormBuilder\\ValidForm::" . $objElement->getField("Type")->getValue()),
                $validationRules,
                array(
                    "maxLength" => $this->maxLengthAlert,
                    "minLength" => $this->minLengthAlert,
                    "required" => $this->requiredAlert,
                    "type" => $objElement->getField("TypeAlert")->getHtmlValue()
                ),
                $arrFieldMeta,
                $blnJustRender
            );

            // Store the PunchCMS ElementID in this field to have a reference for later use.
            $objReturn->setData("eid", $objElement->getId());

        }

        $this->register($objElement, $objReturn);
        return $objReturn;
    }

    protected function renderListField(&$objParent, $objElement)
    {
        // Pre loop options for auto generation of options.
        $blnAutoOptions = false;
        $objOptions = $objElement->getElementsByTemplate("ListOption");
        foreach ($objOptions as $objOption) {
            switch ($objOption->getName()) {
                case "Start":
                    $intStart = $objOption->getField("Value")->getHtmlValue();
                    $blnAutoOptions = true;
                    break;
                case "End":
                    $intEnd = $objOption->getField("Value")->getHtmlValue();
                    $blnAutoOptions = true;
                    break;
                default:
                    break 2;
            }
        }

        $blnDynamic = ($objElement->getField("DynamicLabel")->getHtmlValue() != "") ? true : false;

        $arrMeta = array(
            "fieldclass" => $objElement->getField("FieldClass")->getHtmlValue(),
            "fieldstyle" => $objElement->getField("Style")->getHtmlValue(),
            "tip" => $objElement->getField("Tip")->getHtmlValue(),
            "hint" => $objElement->getField("HintValue")->getHtmlValue(),
            "dynamic" => $blnDynamic,
            "dynamicLabel" => $objElement->getField("DynamicLabel")->getHtmlValue()
        );

        switch (constant("\\ValidFormBuilder\\ValidForm::" . $objElement->getField("Type")->getValue())) {
            case ValidForm::VFORM_CHECK_LIST:
            case ValidForm::VFORM_RADIO_LIST:
                // In list fields, we want to add the class directly to the
                // generated element instead of the parent element
                $arrMeta["fieldclass"] = $objElement->getField("Class")->getHtmlValue();
                break;
            default:
                //*** In all other cases, just do 'class' instead of fieldclass
                $arrMeta["class"] = $objElement->getField("Class")->getHtmlValue();
        }

        $strMultiple = $objElement->getField("Multiple")->getValue();
        if (!empty($strMultiple)) {
            $arrMeta["multiple"] = $strMultiple;
        }

        if ($blnAutoOptions && isset($intStart) && isset($intEnd)) {
            $arrMeta["start"] = $intStart;
            $arrMeta["end"] = $intEnd;
        }

        if ($objParent instanceof MultiField) {
            // Add field without the label.
            $objReturn = $objParent->addField(
                $this->generateId($objElement),
                constant("\\ValidFormBuilder\\ValidForm::" . $objElement->getField("Type")->getValue()),
                array(
                    "maxLength" => $objElement->getField("MaxLength")->getValue(),
                    "minLength" => $objElement->getField("MinLength")->getValue(),
                    "required" => $objElement->getField("Required")->getValue()
                ),
                array(
                    "maxLength" => $this->maxLengthAlert,
                    "minLength" => $this->minLengthAlert,
                    "required" => $this->requiredAlert,
                    "type" => $objElement->getField("TypeAlert")->getHtmlValue()
                ),
                $arrMeta
            );

            // Store the PunchCMS ElementID in this field to have a reference for later use.
            $objReturn->setData("eid", $objElement->getId());

        } else {
            // Add field with the label.
            $strSummaryLabel = $objElement->getField("SummaryLabel")->getHtmlValue();
            if (!empty($strSummaryLabel)) {
                $arrMeta["summaryLabel"] = $strSummaryLabel;
            }

            if (defined("\\ValidFormBuilder\\ValidForm::" . $objElement->getField("Type")->getValue())) {
                $varConst = constant("\\ValidFormBuilder\\ValidForm::" . $objElement->getField("Type")->getValue());
            } else {
                throw new \Exception("Element with EID {$objElement->getId()} has no Field Type set.", E_ERROR);
            }

            $objReturn = $objParent->addField(
                $this->generateId($objElement),
                $objElement->getField("Label")->getHtmlValue(),
                $varConst,
                array(
                    "maxLength" => $objElement->getField("MaxLength")->getValue(),
                    "minLength" => $objElement->getField("MinLength")->getValue(),
                    "required" => $objElement->getField("Required")->getValue()
                ),
                array(
                    "maxLength" => $this->maxLengthAlert,
                    "minLength" => $this->minLengthAlert,
                    "required" => $this->requiredAlert,
                    "type" => $objElement->getField("TypeAlert")->getHtmlValue()
                ),
                $arrMeta
            );

            // Store the PunchCMS ElementID in this field to have a reference for later use.
            $objReturn->setData("eid", $objElement->getId());
        }

        $objOptions = $objElement->getElementsByTemplate(array("ListOption"));
        if (!$blnAutoOptions || ($blnAutoOptions && $objOptions->count() > 2)) {
            foreach ($objOptions as $objOption) {
                if ($objOption->getName() != "Start" && $objOption->getName() != "End") {
                    $objOptionField = $objReturn->addField($objOption->getField("Label")->getHtmlValue(), $objOption->getField("Value")->getHtmlValue(), $objOption->getField("Selected")->getValue());

                    $objTipField = $objOption->getField("Tip");
                    if (is_object($objTipField)) {
                        $strTip = $objTipField->getHtmlValue();
                        if (strlen($strTip) > 0) {
                            $objOptionField->setFieldMeta("data-tip", $strTip);
                        }
                    }
                }
            }
        }

        $this->register($objElement, $objReturn);
        return $objReturn;
    }

    protected function generateId($objElement)
    {
        $strApiName = $objElement->getElement()->getApiName();
        $strReturn = (empty($strApiName)) ? "formfield_" . $objElement->getId() : "formfield_" . strtolower($strApiName);

        return $strReturn;
    }
}
