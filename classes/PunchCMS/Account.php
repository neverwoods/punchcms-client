<?php

namespace PunchCMS;

/**
 *
 * Account operations. Retrieves account data from the database.
 * @author felix & robin
 * @version 0.1.2
 *
 */
class Account extends \PunchCMS\DBAL\Account
{
    public static function getByUri($strUri)
    {
        $strSql = sprintf("SELECT * FROM punch_account WHERE uri = '%s'", $strUri);
        $objAccounts = self::select($strSql);

        if ($objAccounts->count() > 0) {
            return $objAccounts->current();
        }
    }

    public static function getByPunchId($strPunchId)
    {
        $strSql = sprintf("SELECT * FROM punch_account WHERE punchId = '%s'", $strPunchId);
        $objAccounts = self::select($strSql);

        if ($objAccounts->count() > 0) {
            return $objAccounts->current();
        }
    }

    public function delete($accountId = null)
    {
        global $objLiveAdmin;

        self::$object = "\\PunchCMS\\Account";
        self::$table = "punch_account";

        //*** Delete users.
        $objUsers = $objLiveAdmin->getUsers(array('container' => 'auth', 'filters' => array('account_id' => $this->id)));
        if (is_array($objUsers)) {
            foreach ($objUsers as $objUser) {
                $objLiveAdmin->removeUser($objUser["perm_user_id"]);
            }
        }

        //*** Delete groups.
        $objGroups = $objLiveAdmin->perm->getGroups(array('filters' => array('account_id' => $this->id)));
        if (is_array($objGroups)) {
            foreach ($objGroups as $objGroup) {
                $filters = array('group_id' => $objGroup['group_id']);
                $objLiveAdmin->perm->removeGroup($filters);
            }
        }

        //*** Delete applications, areas and rights.
        $objApps = $objLiveAdmin->perm->getApplications(array('filters' => array('account_id' => $this->id)));
        if (is_array($objApps)) {
            foreach ($objApps as $objApp) {
                $objAreas = $objLiveAdmin->perm->getAreas(array('filters' => array('application_id' => $objApp['application_id'], 'account_id' => $this->id)));
                if (is_array($objAreas)) {
                    foreach ($objAreas as $objArea) {
                        $objRights = $objLiveAdmin->perm->getRights(array('filters' => array('area_id' => $objArea['area_id'], 'account_id' => $this->id)));
                        if (is_array($objRights)) {
                            //*** Delete rights.
                            foreach ($objRights as $objRight) {
                                $filters = array('right_id' => $objRight['right_id']);
                                $objLiveAdmin->perm->removeRight($filters);
                            }
                        }

                        //*** Delete areas.
                        $filters = array('area_id' => $objArea['area_id']);
                        $objLiveAdmin->perm->removeArea($filters);
                    }
                }

                //*** Delete applications.
                $filters = array('application_id' => $objApp['application_id']);
                $objLiveAdmin->perm->removeApplication($filters);
            }
        }

        return parent::delete($accountId);
    }

    public static function getById($intAccountId)
    {
        $strSql = sprintf("SELECT * FROM punch_account WHERE id = '%s'", $intAccountId);
        $objAccounts = self::select($strSql);

        if ($objAccounts->count() > 0) {
            return $objAccounts->current();
        }
    }

    public static function generateId()
    {
        $intLength = 64;

        $strChars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        srand((double)microtime()*1000000);
        $strReturn = '';

        for ($i = 1; $i <= $intLength; $i++) {
            $intNum = rand() % (strlen($strChars) - 1);
            $strTmp = substr($strChars, $intNum, 1);
            $strReturn .= $strTmp;
        }

        return $strReturn;
    }

    public static function validate($strSection, $intId, $intCommand = CMD_LIST)
    {
        /**
        *  @desc: Validate an object in a specific LiveUser section against the account id.
        *  @param: sectionName - Name of the LiveUser section. Can be "auth_user",
        *  "perm_user", "group", "area", "application" and "right".
        *  @param: id - The id that needs verification.
        *  @param: command - The intended operation on the section. Can be CMD_LIST, CMD_ADD, CMD_EDIT, CMD_REMOVE.
        *  @return: True on successful verification, otherwise false.
        *  @type: public
        */

        global $objLiveAdmin,
                $_CONF;

        $blnReturn = false;

        switch ($strSection) {
            case "auth_user":
                $filters = array('container' => 'auth', 'filters' => array('auth_user_id' => $intId, 'account_id' => array($_CONF['app']['account']->getId())));
                $objUsers = $objLiveAdmin->getUsers($filters);
                if (is_array($objUsers)) {
                    foreach ($objUsers as $objUser) {
                        $blnReturn = true;
                        break;
                    }
                }

                break;
            case "perm_user":
                $filters = array('container' => 'perm', 'filters' => array('perm_user_id' => $intId));
                $objUsers = $objLiveAdmin->getUsers($filters);
                if (is_array($objUsers)) {
                    foreach ($objUsers as $objUser) {
                        if ($objUser['account_id'] == $_CONF['app']['account']->getId()) {
                            $blnReturn = true;
                            break;
                        }
                    }
                }

                break;
            case "group":
                switch ($intCommand) {
                    case CMD_LIST:
                        $filters = array('filters' => array('group_id' => $intId, 'account_id' => array('0', $_CONF['app']['account']->getId())));

                        break;
                    case CMD_ADD:
                    case CMD_EDIT:
                    case CMD_REMOVE:
                        $filters = array('filters' => array('group_id' => $intId, 'account_id' => array($_CONF['app']['account']->getId())));

                        break;
                }

                $objGroups = $objLiveAdmin->perm->getGroups($filters);
                if (is_array($objGroups)) {
                    foreach ($objGroups as $objGroup) {
                        $blnReturn = true;

                        break;
                    }
                }

                break;
            case "application":
                switch ($intCommand) {
                    case CMD_LIST:
                        $filters = array('filters' => array('application_id' => $intId, 'account_id' => array('0', $_CONF['app']['account']->getId())));

                        break;
                    case CMD_ADD:
                    case CMD_EDIT:
                    case CMD_REMOVE:
                        $filters = array('filters' => array('application_id' => $intId, 'account_id' => array($_CONF['app']['account']->getId())));

                        break;
                }

                $objApplications = $objLiveAdmin->perm->getApplications($filters);
                if (is_array($objApplications)) {
                    foreach ($objApplications as $objApplication) {
                        $blnReturn = true;

                        break;
                    }
                }

                break;
            case "area":
                switch ($intCommand) {
                    case CMD_LIST:
                        $filters = array('filters' => array('area_id' => $intId, 'account_id' => array('0', $_CONF['app']['account']->getId())));

                        break;
                    case CMD_ADD:
                    case CMD_EDIT:
                    case CMD_REMOVE:
                        $filters = array('filters' => array('area_id' => $intId, 'account_id' => array($_CONF['app']['account']->getId())));

                        break;
                }

                $objAreas = $objLiveAdmin->perm->getAreas($filters);
                if (is_array($objAreas)) {
                    foreach ($objAreas as $objArea) {
                        $blnReturn = true;

                        break;
                    }
                }

                break;
            case "right":
                switch ($intCommand) {
                    case CMD_LIST:
                        $filters = array('filters' => array('right_id' => $intId, 'account_id' => array('0', $_CONF['app']['account']->getId())));

                        break;
                    case CMD_ADD:
                    case CMD_EDIT:
                    case CMD_REMOVE:
                        $filters = array('filters' => array('right_id' => $intId, 'account_id' => array($_CONF['app']['account']->getId())));

                        break;
                }

                $objRights = $objLiveAdmin->perm->getRights($filters);
                if (is_array($objRights)) {
                    foreach ($objRights as $objRight) {
                        $blnReturn = true;

                        break;
                    }
                }

                break;
        }

        return $blnReturn;
    }

    public function hasProduct($intProductId)
    {
        /**
        *  @desc: Check if this account has a specific product.
        *  @param: productId - The id of the product in question.
        *  @return: Boolean: true on successful verification, otherwise false.
        *  @type: public
        */

        $blnReturn = false;

        // @FIXME Add AccountProduct class
        $arrProducts = AccountProduct::getByAccountId($this->getId());
        foreach ($arrProducts as $arrProduct) {
            if ($arrProduct->getProductId() == $intProductId) {
                $blnReturn = true;
                break;
            }
        }

        return $blnReturn;
    }

    public function addProduct($intId)
    {
        if ($this->id > 0) {
            // @FIXME Add AccountProduct class
            $objAccountProduct = new AccountProduct();
            $objAccountProduct->setAccountId($this->id);
            $objAccountProduct->setProductId($intId);
            $objAccountProduct->save();
        }
    }

    public function clearProducts()
    {
        if ($this->id > 0) {
            // @FIXME Add AccountProduct class
            $objProducts = AccountProduct::getByAccountId($this->id);

            foreach ($objProducts as $objProduct) {
                $objProduct->delete();
            }
        }
    }

    public function makeBackup($intMax = 5)
    {
        global $_PATHS;

        // @FIXME Add ImpEx class
        $strZipFile = ImpEx::export($this->id);
        copy($strZipFile, $_PATHS['backup'] . $this->punchId . "_" . strftime("%Y%m%d%H%M%S") . ".zip");

        //*** Remove old backups.
        $arrFiles = scandir($_PATHS['backup'], 1);
        $intCount = 1;
        foreach ($arrFiles as $strFileName) {
            if (substr($strFileName, 0, strlen($this->punchId) + 1) == $this->punchId . "_") {
                if ($intCount > $intMax) {
                    @unlink($_PATHS['backup'] . $strFileName);
                }
                $intCount++;
            }
        }

        @unlink($strZipFile);
    }

    public function restoreBackup($strFile = null)
    {
        global $_PATHS;

        $blnReturn = false;

        if (is_null($strFile)) {
            //*** Find the latest backup.
            $arrFiles = scandir($_PATHS['backup'], 1);
            foreach ($arrFiles as $strFileName) {
                if (substr($strFileName, 0, strlen($this->punchId) + 1) == $this->punchId . "_") {
                    $strFile = $strFileName;
                    break;
                }
            }
        }

        if (!empty($strFile) && is_file($strFile)) {
            // @FIXME Add ImpEx class
            ImpEx::import($strFile, true, true);
            $blnReturn = true;
        }

        return $blnReturn;
    }

    public function getBackups()
    {
        global $_PATHS;

        $arrReturn = array();

        $arrFiles = scandir($_PATHS['backup'], 1);
        foreach ($arrFiles as $strFileName) {
            if (substr($strFileName, 0, strlen($this->punchId) + 1) == $this->punchId . "_") {
                $arrFile = explode("_", $strFileName);
                $strYear = substr($arrFile[1], 0, 4);
                $strMonth = substr($arrFile[1], 4, 2);
                $strDay = substr($arrFile[1], 6, 2);
                $strHour = substr($arrFile[1], 8, 2);
                $strMinute = substr($arrFile[1], 10, 2);
                $strSecond = substr($arrFile[1], 12, 2);
                $strLabel = $strDay . "-" . $strMonth . "-" . $strYear . " " . $strHour . ":" . $strMinute . ":" . $strSecond;

                array_push($arrReturn, array("file" => $strFileName, "label" => $strLabel));
            }
        }

        return $arrReturn;
    }
}
