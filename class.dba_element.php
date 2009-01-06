<?php

/***
 *
 * Element DBA Class.
 *
 */

class DBA_Element extends DBA__Object {
	protected $id = NULL;
	protected $accountid = 0;
	protected $name = "";
	protected $namecount = 0;
	protected $apiname = "";
	protected $description = "";
	protected $typeid = 0;
	protected $templateid = 0;
	protected $ispage = 0;
	protected $parentid = 0;
	protected $userid = 0;
	protected $groupid = 0;
	protected $active = 1;
	protected $username = "";

	//*** Constructor.
	public function DBA_Element() {
		self::$__object = "Element";
		self::$__table = "pcms_element";
	}

	//*** Static inherited functions.
	public static function selectByPK($varValue, $arrFields = array()) {
		self::$__object = "Element";
		self::$__table = "pcms_element";

		return parent::selectByPK($varValue, $arrFields);
	}

	public static function select($strSql = "") {
		self::$__object = "Element";
		self::$__table = "pcms_element";

		return parent::select($strSql);
	}

	public static function doDelete($varValue) {
		self::$__object = "Element";
		self::$__table = "pcms_element";

		return parent::doDelete($varValue);
	}

	public function save($blnSaveModifiedDate = TRUE) {
		self::$__object = "Element";
		self::$__table = "pcms_element";

		return parent::save($blnSaveModifiedDate);
	}

	public function delete() {
		self::$__object = "Element";
		self::$__table = "pcms_element";

		return parent::delete();
	}

	public function duplicate() {
		self::$__object = "Element";
		self::$__table = "pcms_element";

		return parent::duplicate();
	}
}

?>