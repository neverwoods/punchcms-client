<?php

namespace PunchCMS\DBAL;

/**
 * ElementFieldText DBA Class.
 * @author Felix Langfeldt <felix@neverwoods.com>
 * @internal
 *
 */

abstract class ElementFieldText extends Object
{
	protected $id = NULL;
	protected $value = "";
	protected $fieldid = 0;
	protected $languageid = 0;
	protected $cascade = 0;

	//*** Constructor.
	public function __construct() {
		self::$object = "\\PunchCMS\\DBAL\\ElementFieldText";
		self::$table = "pcms_element_field_text";
	}

	//*** Static inherited functions.
	public static function selectByPK($varValue, $arrFields = array(), $accountId = NULL) {
		self::$object = "\\PunchCMS\\DBAL\\ElementFieldText";
		self::$table = "pcms_element_field_text";

		return parent::selectByPK($varValue, $arrFields, $accountId);
	}

	public static function select($strSql = "") {
		self::$object = "\\PunchCMS\\DBAL\\ElementFieldText";
		self::$table = "pcms_element_field_text";

		return parent::select($strSql);
	}

	public static function doDelete($varValue) {
		self::$object = "\\PunchCMS\\DBAL\\ElementFieldText";
		self::$table = "pcms_element_field_text";

		return parent::doDelete($varValue);
	}

	public function save($blnSaveModifiedDate = TRUE) {
		self::$object = "\\PunchCMS\\DBAL\\ElementFieldText";
		self::$table = "pcms_element_field_text";

		return parent::save($blnSaveModifiedDate);
	}

	public function delete($accountId = NULL) {
		self::$object = "\\PunchCMS\\DBAL\\ElementFieldText";
		self::$table = "pcms_element_field_text";

		return parent::delete($accountId);
	}

	public function duplicate() {
		self::$object = "\\PunchCMS\\DBAL\\ElementFieldText";
		self::$table = "pcms_element_field_text";

		return parent::duplicate();
	}
}

?>