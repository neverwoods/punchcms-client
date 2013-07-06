<?php

/**
 *
 * ContentLanguage DBA Class.
 *
 */
class DBA_ContentLanguage extends DBA__Object
{
	protected $id = NULL;
	protected $accountid = 0;
	protected $name = "";
	protected $abbr = "";
	protected $active = 0;
	protected $default = 0;
	protected $username = "";

	//*** Constructor.
	public function DBA_ContentLanguage() {
		self::$object = "ContentLanguage";
		self::$table = "pcms_language";
	}

	//*** Static inherited functions.
	public static function selectByPK($varValue, $arrFields = array(), $accountId = NULL) {
		self::$object = "ContentLanguage";
		self::$table = "pcms_language";

		return parent::selectByPK($varValue, $arrFields, $accountId);
	}

	public static function select($strSql = "") {
		self::$object = "ContentLanguage";
		self::$table = "pcms_language";

		return parent::select($strSql);
	}

	public static function doDelete($varValue) {
		self::$object = "ContentLanguage";
		self::$table = "pcms_language";

		return parent::doDelete($varValue);
	}

	public function save($blnSaveModifiedDate = TRUE) {
		self::$object = "ContentLanguage";
		self::$table = "pcms_language";

		return parent::save($blnSaveModifiedDate);
	}

	public function delete($accountId = NULL) {
		self::$object = "ContentLanguage";
		self::$table = "pcms_language";

		return parent::delete($accountId);
	}

	public function duplicate() {
		self::$object = "ContentLanguage";
		self::$table = "pcms_language";

		return parent::duplicate();
	}
}

?>