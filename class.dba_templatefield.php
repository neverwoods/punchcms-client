<?php

/***
 *
 * TemplateField DBA Class.
 *
 */

class DBA_TemplateField extends DBA__Object {
	protected $id = NULL;
	protected $templateid = 0;
	protected $formid = 0;
	protected $typeid = 0;
	protected $required = 0;
	protected $name = "";
	protected $apiname = "";
	protected $description = "";
	protected $username = "";

	//*** Constructor.
	public function DBA_TemplateField() {
		self::$__object = "TemplateField";
		self::$__table = "pcms_template_field";
	}

	//*** Static inherited functions.
	public static function selectByPK($varValue, $arrFields = array()) {
		self::$__object = "TemplateField";
		self::$__table = "pcms_template_field";

		return parent::selectByPK($varValue, $arrFields);
	}

	public static function select($strSql = "") {
		self::$__object = "TemplateField";
		self::$__table = "pcms_template_field";

		return parent::select($strSql);
	}

	public static function doDelete($varValue) {
		self::$__object = "TemplateField";
		self::$__table = "pcms_template_field";

		return parent::doDelete($varValue);
	}

	public function save($blnSaveModifiedDate = TRUE) {
		self::$__object = "TemplateField";
		self::$__table = "pcms_template_field";

		return parent::save($blnSaveModifiedDate);
	}

	public function delete() {
		self::$__object = "TemplateField";
		self::$__table = "pcms_template_field";

		return parent::delete();
	}

	public function duplicate() {
		self::$__object = "TemplateField";
		self::$__table = "pcms_template_field";

		return parent::duplicate();
	}
}

?>