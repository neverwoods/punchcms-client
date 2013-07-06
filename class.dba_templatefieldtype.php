<?php

/***
 *
 * TemplateFieldType DBA Class.
 *
 */

class DBA_TemplateFieldType extends DBA__Object {
	protected $id = NULL;
	protected $name = "";
	protected $input = "";
	protected $element = "";

	//*** Constructor.
	public function DBA_TemplateFieldType() {
		self::$object = "TemplateFieldType";
		self::$table = "pcms_template_field_type";
	}

	//*** Static inherited functions.
	public static function selectByPK($varValue, $arrFields = array(), $accountId = NULL) {
		self::$object = "TemplateFieldType";
		self::$table = "pcms_template_field_type";

		return parent::selectByPK($varValue, $arrFields, $accountId);
	}

	public static function select($strSql = "") {
		self::$object = "TemplateFieldType";
		self::$table = "pcms_template_field_type";

		return parent::select($strSql);
	}

	public static function doDelete($varValue) {
		self::$object = "TemplateFieldType";
		self::$table = "pcms_template_field_type";

		return parent::doDelete($varValue);
	}

	public function save($blnSaveModifiedDate = TRUE) {
		self::$object = "TemplateFieldType";
		self::$table = "pcms_template_field_type";

		return parent::save($blnSaveModifiedDate);
	}

	public function delete($accountId = NULL) {
		self::$object = "TemplateFieldType";
		self::$table = "pcms_template_field_type";

		return parent::delete($accountId);
	}

	public function duplicate() {
		self::$object = "TemplateFieldType";
		self::$table = "pcms_template_field_type";

		return parent::duplicate();
	}
}

?>