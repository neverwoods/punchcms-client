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
		self::$__object = "TemplateFieldValue";
		self::$__table = "pcms_template_field_value";
	}

	//*** Static inherited functions.
	public static function selectByPK($varValue, $arrFields = array(), $accountId = NULL) {
		self::$__object = "TemplateFieldValue";
		self::$__table = "pcms_template_field_value";

		return parent::selectByPK($varValue, $arrFields, $accountId);
	}

	public static function select($strSql = "") {
		self::$__object = "TemplateFieldValue";
		self::$__table = "pcms_template_field_value";

		return parent::select($strSql);
	}

	public static function doDelete($varValue) {
		self::$__object = "TemplateFieldValue";
		self::$__table = "pcms_template_field_value";

		return parent::doDelete($varValue);
	}

	public function save($blnSaveModifiedDate = TRUE) {
		self::$__object = "TemplateFieldValue";
		self::$__table = "pcms_template_field_value";

		return parent::save($blnSaveModifiedDate);
	}

	public function delete($accountId = NULL) {
		self::$__object = "TemplateFieldValue";
		self::$__table = "pcms_template_field_value";

		return parent::delete($accountId);
	}

	public function duplicate() {
		self::$__object = "TemplateFieldValue";
		self::$__table = "pcms_template_field_value";

		return parent::duplicate();
	}
}

?>