<?php

namespace PunchCMS\DBAL;

/**
 * TemplateFieldValue DBA Class.
 * @author Felix Langfeldt <felix@neverwoods.com>
 * @internal
 *
 */
class TemplateFieldValue extends Object
{
	protected $id = NULL;
	protected $fieldid = 0;
	protected $name = "";
	protected $value = "";

	//*** Constructor.
	public function __construct() {
		self::$object = "\\PunchCMS\\DBAL\\TemplateFieldValue";
		self::$table = "pcms_template_field_value";
	}

	//*** Static inherited functions.
	public static function selectByPK($varValue, $arrFields = array(), $accountId = NULL) {
		self::$object = "\\PunchCMS\\DBAL\\TemplateFieldValue";
		self::$table = "pcms_template_field_value";

		return parent::selectByPK($varValue, $arrFields, $accountId);
	}

	public static function select($strSql = "") {
		self::$object = "\\PunchCMS\\DBAL\\TemplateFieldValue";
		self::$table = "pcms_template_field_value";

		return parent::select($strSql);
	}

	public static function doDelete($varValue) {
		self::$object = "\\PunchCMS\\DBAL\\TemplateFieldValue";
		self::$table = "pcms_template_field_value";

		return parent::doDelete($varValue);
	}

	public function save($blnSaveModifiedDate = TRUE) {
		self::$object = "\\PunchCMS\\DBAL\\TemplateFieldValue";
		self::$table = "pcms_template_field_value";

		return parent::save($blnSaveModifiedDate);
	}

	public function delete($accountId = NULL) {
		self::$object = "\\PunchCMS\\DBAL\\TemplateFieldValue";
		self::$table = "pcms_template_field_value";

		return parent::delete($accountId);
	}

	public function duplicate() {
		self::$object = "\\PunchCMS\\DBAL\\TemplateFieldValue";
		self::$table = "pcms_template_field_value";

		return parent::duplicate();
	}
}
