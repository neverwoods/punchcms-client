<?php

namespace PunchCMS;

use Bili\FTP;
use Bili\FileIO;

/**
 *
 * Handles ElementFieldBigText properties and methods.
 * @author felix
 * @version 0.1.0
 *
 */
class ElementFieldBigText extends \PunchCMS\DBAL\ElementFieldBigText
{
    public static function getByFieldId($intFieldId, $intLanguageId = 0)
    {
        self::$object = "\\PunchCMS\\ElementFieldBigText";
        self::$table = "pcms_element_field_bigtext";

        $objReturn = new ElementFieldBigText();

        if ($intFieldId > 0) {
            $strSql = sprintf(
                "SELECT * FROM " . self::$table . " WHERE fieldId = %s AND languageId = %s",
                self::quote($intFieldId),
                self::quote($intLanguageId)
            );
            $objElementValues = ElementFieldBigText::select($strSql);

            if (is_object($objElementValues) && $objElementValues->count() > 0) {
                $objReturn = $objElementValues->current();
            }
        }

        return $objReturn;
    }

    public function delete($blnRemovePhysical = false)
    {
        self::$object = "\\PunchCMS\\ElementFieldBigText";
        self::$table = "pcms_element_field_bigtext";

        if ($blnRemovePhysical) {
            //*** Get TemplateField.
            $objElementField = ElementField::selectByPk($this->fieldId);
            if (is_object($objElementField)) {
                $objTemplateField = TemplateField::selectByPk($objElementField->getTemplateFieldId());

                switch ($objTemplateField->getTypeId()) {
                    case FIELD_TYPE_FILE:
                    case FIELD_TYPE_IMAGE:
                        //*** Get remote settings.
                        $strServer = Setting::getValueByName('ftp_server');
                        $strUsername = Setting::getValueByName('ftp_username');
                        $strPassword = Setting::getValueByName('ftp_password');
                        $strRemoteFolder = Setting::getValueByName('ftp_remote_folder');

                        //*** Remove deleted files.
                        $objFtp = new FTP($strServer);
                        $objFtp->login($strUsername, $strPassword);
                        $objFtp->pasv(true);
                        $arrValues = explode("\n", $this->value);
                        foreach ($arrValues as $value) {
                            if (!empty($value)) {
                                //*** Find file name.
                                $arrFile = explode(":", $value);
                                if (count($arrFile) > 1) {
                                    //*** Check if the file is used by other elements.
                                    if (!ElementField::fileHasDuplicates($value, 1)) {
                                        //*** Remove files.
                                        $strFile = $strRemoteFolder . $arrFile[1];
                                        $objFtp->delete($strFile);

                                        if ($objTemplateField->getTypeId() == FIELD_TYPE_IMAGE) {
                                            //*** Remove template settings files.
                                            $objImageField = new ImageField($objElementField->getTemplateFieldId());
                                            $arrSettings = $objImageField->getSettings();
                                            foreach ($arrSettings as $key => $arrSetting) {
                                                if (!empty($arrSetting['width']) ||    !empty($arrSetting['height'])) {
                                                    //*** Remove file.
                                                    $strFile = $strRemoteFolder . FileIO::add2Base($arrFile[1], $arrSetting['key']);
                                                    $objFtp->delete($strFile);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        break;
                }
            }
        }

        return parent::delete();
    }
}
