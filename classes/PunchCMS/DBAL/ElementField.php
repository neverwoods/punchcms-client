<?php

namespace PunchCMS\DBAL;

/**
 * ElementField DBA Class.
 * @author Felix Langfeldt <felix@neverwoods.com>
 * @internal
 *
 */
abstract class ElementField extends Object
{
	protected $id = NULL;
	protected $elementid = 0;
	protected $templatefieldid = 0;
	protected $fieldtypeid = 0;
	protected $originalname = "";
	protected $username = "";

	//*** Constructor.
	public function __construct() {
		self::$object = "\\PunchCMS\\DBAL\\ElementField";
		self::$table = "pcms_element_field";
	}

	//*** Static inherited functions.
	public static function selectByPK($varValue, $arrFields = array(), $accountId = NULL) {
		self::$object = "\\PunchCMS\\DBAL\\ElementField";
		self::$table = "pcms_element_field";

		return parent::selectByPK($varValue, $arrFields, $accountId);
	}

	public static function select($strSql = "") {
		self::$object = "\\PunchCMS\\DBAL\\ElementField";
		self::$table = "pcms_element_field";

		return parent::select($strSql);
	}

	public static function doDelete($varValue) {
		self::$object = "\\PunchCMS\\DBAL\\ElementField";
		self::$table = "pcms_element_field";

		return parent::doDelete($varValue);
	}

	public function save($blnSaveModifiedDate = TRUE) {
		self::$object = "\\PunchCMS\\DBAL\\ElementField";
		self::$table = "pcms_element_field";

		return parent::save($blnSaveModifiedDate);
	}

	public function delete($accountId = NULL) {
		self::$object = "\\PunchCMS\\DBAL\\ElementField";
		self::$table = "pcms_element_field";

		return parent::delete($accountId);
	}

	public function duplicate() {
		self::$object = "\\PunchCMS\\DBAL\\ElementField";
		self::$table = "pcms_element_field";

		return parent::duplicate();
	}
}

?>