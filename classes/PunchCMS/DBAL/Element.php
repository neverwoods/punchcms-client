<?php

namespace PunchCMS\DBAL;

/**
 *
 * Element DBA Class.
 * @author Felix Langfeldt <felix@neverwoods.com>
 * @internal
 *
 */
abstract class Element extends Object
{
	protected $id = NULL;
	protected $accountid = 0;
	protected $name = "";
	protected $namecount = 0;
	protected $apiname = "";
	protected $description = "";
	protected $typeid = 0;
	protected $templateid = 0;
	protected $ispage = 0;
	protected $parentid = 0;
	protected $userid = 0;
	protected $groupid = 0;
	protected $active = 1;
	protected $username = "";

	//*** Constructor.
	public function __construct() {
		self::$object = "\\PunchCMS\\DBAL\\Element";
		self::$table = "pcms_element";
	}

	//*** Static inherited functions.
	public static function selectByPK($varValue, $arrFields = array(), $accountId = NULL) {
		self::$object = "\\PunchCMS\\DBAL\\Element";
		self::$table = "pcms_element";

		return parent::selectByPK($varValue, $arrFields, $accountId);
	}

	public static function select($strSql = "") {
		self::$object = "\\PunchCMS\\DBAL\\Element";
		self::$table = "pcms_element";

		return parent::select($strSql);
	}

	public static function doDelete($varValue) {
		self::$object = "\\PunchCMS\\DBAL\\Element";
		self::$table = "pcms_element";

		return parent::doDelete($varValue);
	}

	public function save($blnSaveModifiedDate = TRUE) {
		self::$object = "\\PunchCMS\\DBAL\\Element";
		self::$table = "pcms_element";

		return parent::save($blnSaveModifiedDate);
	}

	public function delete($accountId = NULL) {
		self::$object = "\\PunchCMS\\DBAL\\Element";
		self::$table = "pcms_element";

		return parent::delete($accountId);
	}

	public function duplicate() {
		self::$object = "\\PunchCMS\\DBAL\\Element";
		self::$table = "pcms_element";

		return parent::duplicate();
	}
}

?>