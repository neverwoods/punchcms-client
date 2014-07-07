<?php

namespace PunchCMS\DBAL;

/**
 * Template DBA Class.
 * @author Felix Langfeldt <felix@neverwoods.com>
 * @internal
 *
 */

abstract class Template extends Object
{
	protected $id = NULL;
	protected $accountid = 0;
	protected $name = "";
	protected $apiname = "";
	protected $description = "";
	protected $parentid = 0;
	protected $ispage = 0;
	protected $iscontainer = 0;
	protected $forcecreation = 0;
	protected $active = 0;
	protected $username = "";

	//*** Constructor.
	public function __construct() {
		self::$object = "\\PunchCMS\\DBAL\\Template";
		self::$table = "pcms_template";
	}

	//*** Static inherited functions.
	public static function selectByPK($varValue, $arrFields = array(), $accountId = NULL) {
		self::$object = "\\PunchCMS\\DBAL\\Template";
		self::$table = "pcms_template";

		return parent::selectByPK($varValue, $arrFields, $accountId);
	}

	public static function select($strSql = "") {
		self::$object = "\\PunchCMS\\DBAL\\Template";
		self::$table = "pcms_template";

		return parent::select($strSql);
	}

	public static function doDelete($varValue) {
		self::$object = "\\PunchCMS\\DBAL\\Template";
		self::$table = "pcms_template";

		return parent::doDelete($varValue);
	}

	public function save($blnSaveModifiedDate = TRUE) {
		self::$object = "\\PunchCMS\\DBAL\\Template";
		self::$table = "pcms_template";

		return parent::save($blnSaveModifiedDate);
	}

	public function delete($accountId = NULL) {
		self::$object = "\\PunchCMS\\DBAL\\Template";
		self::$table = "pcms_template";

		return parent::delete($accountId);
	}

	public function duplicate() {
		self::$object = "\\PunchCMS\\DBAL\\Template";
		self::$table = "pcms_template";

		return parent::duplicate();
	}
}
