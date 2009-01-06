<?php

/* ElementPermission Class v0.1.0
 * Handles ElementPermission properties and methods.
 *
 * CHANGELOG
 * version 0.1.0, 09 Aug 2006
 *   NEW: Created class.
 */

class ElementPermission extends DBA_ElementPermission {
	public static function getByElement($intElementId) {
		global $_CONF;
	
		$strSql = "SELECT pcms_element_permission.* 
					FROM pcms_element_permission, pcms_element 
					WHERE pcms_element_permission.elementId = '%s' 
					AND pcms_element.accountId = '%s' 
					AND pcms_element_permission.elementId = pcms_element.id";
		$strSql = sprintf($strSql, quote_smart($intElementId), quote_smart($_CONF['app']['account']->getId()));
		$objPermissions = self::select($strSql);
		
		$objReturn = new ElementPermission();
		$objReturn->setUserId(array());
		$objReturn->setGroupId(array());
		
		foreach ($objPermissions as $objPermission) {
			$objTemp = $objReturn->getUserId();
			$intTemp = $objPermission->getUserId();
			if (!in_array($objPermission->getUserId(), $objTemp) && !empty($intTemp)) {
				array_push($objTemp, $objPermission->getUserId());
				$objReturn->setUserId($objTemp);
			}
			
			$objTemp = $objReturn->getGroupId();
			$intTemp = $objPermission->getGroupId();
			if (!in_array($objPermission->getGroupId(), $objTemp) && !empty($intTemp)) {
				array_push($objTemp, $objPermission->getGroupId());
				$objReturn->setGroupId($objTemp);
			}
		}
		
		return $objReturn;
	}

	public function save($blnSaveModifiedDate = TRUE) {
		self::$__object = "ElementPermission";
		self::$__table = "pcms_element_permission";
		
		$blnReturn = TRUE;
		
		//*** Save the user permissions.
		if (is_array($this->getUserId()) && count($this->getUserId()) > 0) {
			foreach ($this->getUserId() as $userId) {
				if ($userId > 0) {
					$objTemp = new DBA_ElementPermission();
					$objTemp->setElementId($this->getElementId());
					$objTemp->setUserId($userId);
					$objTemp->save($blnSaveModifiedDate);
				}
			}
		} else if (is_integer($this->getUserId()) && $this->getUserId() > 0) {
			$objTemp = new DBA_ElementPermission();
			$objTemp->setElementId($this->getElementId());
			$objTemp->setUserId($this->getUserId());
			$objTemp->save($blnSaveModifiedDate);
		}

		//*** Save the group permissions.
		if (is_array($this->getGroupId()) && count($this->getGroupId()) > 0) {
			foreach ($this->getGroupId() as $groupId) {
				if ($groupId > 0) {
					$objTemp = new DBA_ElementPermission();
					$objTemp->setElementId($this->getElementId());
					$objTemp->setGroupId($groupId);
					$objTemp->save($blnSaveModifiedDate);
				}
			}
		} else if (is_integer($this->getGroupId()) && $this->getGroupId() > 0) {
			$objTemp = new DBA_ElementPermission();
			$objTemp->setElementId($this->getElementId());
			$objTemp->setGroupId($this->getGroupId());
			$objTemp->save($blnSaveModifiedDate);
		}
		
		return $blnReturn;
	}

	public function delete() {
		global $_CONF;
		
		self::$__object = "ElementPermission";
		self::$__table = "pcms_element_permission";
		
		if ($this->elementId > 0) {
			$strSql = "SELECT pcms_element_permission.* 
						FROM pcms_element_permission, pcms_element 
						WHERE pcms_element_permission.elementId = '%s' 
						AND pcms_element.accountId = '%s' 
						AND pcms_element_permission.elementId = pcms_element.id";
			$strSql = sprintf($strSql, quote_smart($this->elementId), quote_smart($_CONF['app']['account']->getId()));
			$objPermissions = self::select($strSql);
			
			foreach ($objPermissions as $objPermission) {
				$objPermission->delete();
			}
		}		
	}
}

?>
