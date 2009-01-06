<?php

/* StorageItem Class v0.1.0
 * Handles StorageItem properties and methods.
 *
 * CHANGELOG
 * version 0.1.0, 04 Apr 2006
 *   NEW: Created class.
 */

class StorageItem extends DBA_StorageItem {
	private $data;

	public static function selectByPK($varValue, $arrFields = array()) {
		global $_CONF;
		parent::$__object = "StorageItem";
		parent::$__table = "pcms_storage_item";

		return parent::selectByPK($varValue, $arrFields, $_CONF['app']['account']->getId());
	}

	public static function select($strSql = "") {
		global $_CONF;
		parent::$__object = "StorageItem";
		parent::$__table = "pcms_storage_item";

		if (empty($strSql)) {
			$strSql = sprintf("SELECT * FROM " . parent::$__table . " WHERE accountId = '%s' ORDER BY sort", $_CONF['app']['account']->getId());
		}

		return parent::select($strSql);
	}

	public function delete() {
		global $_CONF;
		parent::$__object = "StorageItem";
		parent::$__table = "pcms_storage_item";
		
		AuditLog::addLog(AUDIT_TYPE_STORAGE, $this->getId(), $this->getName(), "delete");

		//*** Remove child items.
		$objElements = $this->getItems();
		foreach ($objElements as $objElement) {
			$objElement->delete();
		}
		
		if ($this->getTypeId() == STORAGE_TYPE_FILE) {
			$strValue =  $this->getData()->getLocalName();
			if (!empty($strValue)) {
				//*** Remove physical.
				$strServer = Setting::getValueByName('ftp_server');
				$strUsername = Setting::getValueByName('ftp_username');
				$strPassword = Setting::getValueByName('ftp_password');
				$strRemoteFolder = Setting::getValueByName('ftp_remote_folder');

				//*** Remove deleted files.
				$objFtp = new FTP($strServer);
				$objFtp->login($strUsername, $strPassword);
				$objFtp->pasv(TRUE);
				$strFile = $strRemoteFolder . $strValue;
				$objFtp->delete($strFile);
			}
		}

		return parent::delete($_CONF['app']['account']->getId());
	}
	
	public function getData() {
		if (!is_object($this->data)) {
			$objElements = StorageData::selectByItemId($this->getId());
			if ($objElements->count() > 0) {
				$this->data = $objElements->current();
			} else {
				$this->data = new StorageData();
			}
		}
		
		return $this->data;
	}

	public function save($blnSaveModifiedDate = TRUE) {
		parent::$__object = "StorageItem";
		parent::$__table = "pcms_storage_item";
		
		$intId = $this->getId();

		$blnReturn = parent::save($blnSaveModifiedDate);
		AuditLog::addLog(AUDIT_TYPE_STORAGE, $this->getId(), $this->getName(), (empty($intId)) ? "create" : "edit");

		return $blnReturn;
	}

	public function duplicate($strNewName = "") {
		global $objLang,
				$_CONF;

		if ($this->id > 0) {
			//*** Cache the name of the current element.
			$strName = $this->name;

			if (!empty($strNewName)) {
				//*** Set the name of the duplicate element.
				$this->name = sprintf($strNewName, $strName);
			}

			//*** Duplicate the element.
			$objReturn = parent::duplicate();
			
			AuditLog::addLog(AUDIT_TYPE_STORAGE, $this->getId(), $strName, "duplicate", $objReturn->getId());
			AuditLog::addLog(AUDIT_TYPE_STORAGE, $objReturn->getId(), $objReturn->getName(), "create");

			//*** Reset the name of the current element.
			$this->name = $strName;

			//*** Copy any child elements to the duplicate.
			$strSql = sprintf("SELECT * FROM pcms_storage_item WHERE parentId = '%s' AND accountId = '%s'", $this->id, $_CONF['app']['account']->getId());
			$objElements = StorageItem::select($strSql);

			foreach ($objElements as $objElement) {
				$objElement->copy($objReturn->getId());
			}

			return $objReturn;
		}

		return NULL;
	}

	public function copy($intParentId) {
		global $objLiveUser;

		$objDuplicate = $this->duplicate();
		$objDuplicate->setParentId($intParentId);
		$objDuplicate->setUsername($objLiveUser->getProperty("name"));
		$objDuplicate->save();

		return $objDuplicate;
	}
	
	public static function setParent() {
		$intElementId = request('eid', 0);
		$intParentId = request('parentId', -1);

		$strReturn = "<fields>";
		$strReturn .= "<field name=\"itemId\">";

		if ($intElementId > 0 && $intParentId > -1) {
			$objElement = StorageItem::selectByPK($intElementId);

			if (is_object($objElement)) {
				$objElement->setParentId($intParentId);
				$objElement->save();
			}

			$strReturn .= "<value>$intElementId</value>";
		} else {
			$strReturn .= "<value>-1</value>";
		}

		$strReturn .= "</field>";
		$strReturn .= "</fields>";

		return $strReturn;
	}

	public function getItems($intTypeId = STORAGE_TYPE_ALL) {
		global $_CONF;
		parent::$__object = "StorageItem";
		parent::$__table = "pcms_storage_item";
		
		$strSql = sprintf("SELECT * FROM " . parent::$__table . " WHERE parentId = '%s' AND typeId IN (%s) AND accountId = '%s' ORDER BY sort", $this->getId(), $intTypeId, $_CONF['app']['account']->getId());
		
		return parent::select($strSql);
	}

	public function getFolders() {
		return $this->getItems(STORAGE_TYPE_FOLDER);
	}

	public function getFiles() {
		return $this->getItems(STORAGE_TYPE_FILE);
	}
	
	public function getLink() {
	
	}
}

?>
