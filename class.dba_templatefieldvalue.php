<?php

/***
 *
 * TemplateFieldValue DBA Class.
 *
 */

class DBA_TemplateFieldValue extends DBA__Object {
	protected $id = NULL;
	protected $fieldid = 0;
	protected $name = "";
	protected $value = "";

	//*** Constructor.
	public function DBA_TemplateFieldValue() {
		self::$object = "TemplateFieldValue";
		self::$table = "pcms_template_field_value";
	}

	//*** Static inherited functions.
	public static function selectByPK($varValue, $arrFields = array(), $accountId = NULL) {
		self::$object = "TemplateFieldValue";
		self::$table = "pcms_template_field_value";

		return parent::selectByPK($varValue, $arrFields, $accountId);
	}

	public static function select($strSql = "") {
		self::$object = "TemplateFieldValue";
		self::$table = "pcms_template_field_value";

		return parent::select($strSql);
	}

	public static function doDelete($varValue) {
		self::$object = "TemplateFieldValue";
		self::$table = "pcms_template_field_value";

		return parent::doDelete($varValue);
	}

	public function save($blnSaveModifiedDate = TRUE) {
		self::$object = "TemplateFieldValue";
		self::$table = "pcms_template_field_value";

		return parent::save($blnSaveModifiedDate);
	}

	public function delete($accountId = NULL) {
		self::$object = "TemplateFieldValue";
		self::$table = "pcms_template_field_value";

		return parent::delete($accountId);
	}

	public function duplicate() {
		self::$object = "TemplateFieldValue";
		self::$table = "pcms_template_field_value";

		return parent::duplicate();
	}
}

?>