<?php

/***
 *
 * ElementSchedule DBA Class.
 *
 */

class DBA_ElementSchedule extends DBA__Object {
	protected $id = NULL;
	protected $elementid = 0;
	protected $startdate = "0000-00-00 00:00:00";
	protected $enddate = "0000-00-00 00:00:00";
	protected $startactive = 0;
	protected $endactive = 0;
	protected $monday = 0;
	protected $tuesday = 0;
	protected $wednesday = 0;
	protected $thursday = 0;
	protected $friday = 0;
	protected $saturday = 0;
	protected $sunday = 0;
	protected $starttime = "0000-00-00 00:00:00";
	protected $endtime = "0000-00-00 00:00:00";

	//*** Constructor.
	public function DBA_ElementSchedule() {
		self::$__object = "ElementSchedule";
		self::$__table = "pcms_element_schedule";
	}

	//*** Static inherited functions.
	public static function selectByPK($varValue, $arrFields = array(), $accountId = NULL) {
		self::$__object = "ElementSchedule";
		self::$__table = "pcms_element_schedule";

		return parent::selectByPK($varValue, $arrFields, $accountId);
	}

	public static function select($strSql = "") {
		self::$__object = "ElementSchedule";
		self::$__table = "pcms_element_schedule";

		return parent::select($strSql);
	}

	public static function doDelete($varValue) {
		self::$__object = "ElementSchedule";
		self::$__table = "pcms_element_schedule";

		return parent::doDelete($varValue);
	}

	public function save($blnSaveModifiedDate = TRUE) {
		self::$__object = "ElementSchedule";
		self::$__table = "pcms_element_schedule";

		return parent::save($blnSaveModifiedDate);
	}

	public function delete($accountId = NULL) {
		self::$__object = "ElementSchedule";
		self::$__table = "pcms_element_schedule";

		return parent::delete($accountId);
	}

	public function duplicate() {
		self::$__object = "ElementSchedule";
		self::$__table = "pcms_element_schedule";

		return parent::duplicate();
	}
}

?>