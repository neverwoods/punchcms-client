<?php

/***
 *
 * Template DBA Class.
 *
 */

class DBA_Template extends DBA__Object {
	protected $id = NULL;
	protected $accountid = 0;
	protected $name = "";
	protected $apiname = "";
	protected $description = "";
	protected $parentid = 0;
	protected $ispage = 0;
	protected $iscontainer = 0;
	protected $forcecreation = 0;
	protected $active = 0;
	protected $username = "";

	//*** Constructor.
	public function DBA_Template() {
		self::$__object = "Template";
		self::$__table = "pcms_template";
	}

	//*** Static inherited functions.
	public static function selectByPK($varValue, $arrFields = array()) {
		self::$__object = "Template";
		self::$__table = "pcms_template";

		return parent::selectByPK($varValue, $arrFields);
	}

	public static function select($strSql = "") {
		self::$__object = "Template";
		self::$__table = "pcms_template";

		return parent::select($strSql);
	}

	public static function doDelete($varValue) {
		self::$__object = "Template";
		self::$__table = "pcms_template";

		return parent::doDelete($varValue);
	}

	public function save($blnSaveModifiedDate = TRUE) {
		self::$__object = "Template";
		self::$__table = "pcms_template";

		return parent::save($blnSaveModifiedDate);
	}

	public function delete() {
		self::$__object = "Template";
		self::$__table = "pcms_template";

		return parent::delete();
	}

	public function duplicate() {
		self::$__object = "Template";
		self::$__table = "pcms_template";

		return parent::duplicate();
	}
}

?>