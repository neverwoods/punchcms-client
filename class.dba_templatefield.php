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
		self::$object = "TemplateField";
		self::$table = "pcms_template_field";
	}

	//*** Static inherited functions.
	public static function selectByPK($varValue, $arrFields = array(), $accountId = NULL) {
		self::$object = "TemplateField";
		self::$table = "pcms_template_field";

		return parent::selectByPK($varValue, $arrFields, $accountId);
	}

	public static function select($strSql = "") {
		self::$object = "TemplateField";
		self::$table = "pcms_template_field";

		return parent::select($strSql);
	}

	public static function doDelete($varValue) {
		self::$object = "TemplateField";
		self::$table = "pcms_template_field";

		return parent::doDelete($varValue);
	}

	public function save($blnSaveModifiedDate = TRUE) {
		self::$object = "TemplateField";
		self::$table = "pcms_template_field";

		return parent::save($blnSaveModifiedDate);
	}

	public function delete($accountId = NULL) {
		self::$object = "TemplateField";
		self::$table = "pcms_template_field";

		return parent::delete($accountId);
	}

	public function duplicate() {
		self::$object = "TemplateField";
		self::$table = "pcms_template_field";

		return parent::duplicate();
	}
}

?>