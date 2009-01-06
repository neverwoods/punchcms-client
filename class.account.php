<?php

/*
#########################################################################
#  Copyright (c) 2005-2006. Punch Software. All Rights Reserved.
#
#  Punch software [both binary and source (if released)] (hereafter,
#  Software) is intellectual property owned by Punch Software and
#  phixel.org and is copyright of Punch Software and phixel.org in all
#  countries in the world, and ownership remains with Punch Software and
#  phixel.org.
#
#  You (hereafter, Licensee) are not allowed to distribute the binary and
#  source code (if released) to third parties. Licensee is not allowed to
#  reverse engineer, disassemble or decompile code, or make any
#  modifications of the binary or source code, remove or alter any
#  trademark, logo, copyright or other proprietary notices, legends,
#  symbols, or labels in the Software.
#
#  Licensee is not allowed to sub-license the Software or any derivative
#  work based on or derived from the Software.
#
#  The Licensee acknowledges and agrees that the software is delivered
#  'as is' without warranty and without any support services (unless
#  agreed otherwise with Punch Software or phixel.org). Punch Software
#  and phixel.org make no warranties, either expressed or implied, as to
#  the software and its derivatives.
#
#  It is understood by Licensee that neither Punch Software nor
#  phixel.org shall be liable for any loss or damage that may arise,
#  including any indirect special or consequential loss or damage in
#  connection with or arising from the performance or use of the
#  software, including fitness for any particular purpose.
#
#  By using or copying this Software, Licensee agrees to abide by the
#  copyright law and all other applicable laws of The Netherlands
#  including, but not limited to, export control laws, and the terms of
#  this licence. Punch Software and/or phixel.org shall have the right to
#  terminate this licence immediately by written notice upon Licensee's
#  breach of, or non-compliance with, any of its terms. Licensee may be
#  held legally responsible for any copyright infringement that is caused
#  or encouraged by Licensee's failure to abide by the terms of this
#  licence.
#########################################################################
*/

/*
 * Account Class v0.1.1
 * Retrieves account data from the database.
 */

class Account extends DBA_Account {

	public static function getByUri($strUri) {
		$strSql = sprintf("SELECT * FROM punch_account WHERE uri = '%s'", $strUri);
		$objAccounts = self::select($strSql);

		if ($objAccounts->count() > 0) {
			return $objAccounts->current();
		}
	}

	public static function getByPunchId($strPunchId) {
		$strSql = sprintf("SELECT * FROM punch_account WHERE punchId = '%s'", $strPunchId);
		$objAccounts = self::select($strSql);

		if ($objAccounts->count() > 0) {
			return $objAccounts->current();
		}
	}

	public static function getById($intAccountId) {
		$strSql = sprintf("SELECT * FROM punch_account WHERE id = '%s'", $intAccountId);
		$objAccounts = self::select($strSql);

		if ($objAccounts->count() > 0) {
			return $objAccounts->current();
		}
	}

	public static function generateId() {
		$intLength = 8;

		$strChars = "abcdef0123456789";
		srand((double)microtime()*1000000);
		$strReturn = '';

		for ($i = 1; $i <= $intLength; $i++) {
			$intNum = rand() % (strlen($strChars) - 1);
			$strTmp = substr($strChars, $intNum, 1);
			$strReturn .= $strTmp;
		}

		return $strReturn;
	}

	public static function validate($strSection, $intId, $intCommand = CMD_LIST) {
		/**
		*  @desc: Validate an object in a specific LiveUser section against the account id.
		*  @param: sectionName - Name of the LiveUser section. Can be "auth_user", "perm_user", "group", "area", "application" and "right".
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

	public function hasProduct($intProductId) {
		/**
		*  @desc: Check if this account has a specific product.
		*  @param: productId - The id of the product in question.
		*  @return: Boolean: true on successful verification, otherwise false.
		*  @type: public
		*/

		$blnReturn = FALSE;

		$arrProducts = AccountProduct::getByAccountId($this->getId());
		foreach ($arrProducts as $arrProduct) {
			if ($arrProduct->getProductId() == $intProductId) {
				$blnReturn = TRUE;
				break;
			}
		}

		return $blnReturn;
	}
	
	public function addProduct($intId) {
		if ($this->id > 0) {
			$objAccountProduct = new AccountProduct();
			$objAccountProduct->setAccountId($this->id);
			$objAccountProduct->setProductId($intId);
			$objAccountProduct->save();
		}
	}
		
	public function clearProducts() {
		if ($this->id > 0) {
			$objProducts = AccountProduct::getByAccountId($this->id);

			foreach ($objProducts as $objProduct) {
				$objProduct->delete();
			}
		}
	}

}

?>