<?php

namespace PunchCMS\DBAL;

/**
 * Account DBA Class.
 * @author Felix Langfeldt <felix@neverwoods.com>
 * @internal
 *
 */
abstract class Account extends Object
{
	protected $id = null;
	protected $punchid = 0;
	protected $name = "";
	protected $uri = "";
	protected $timezoneid = 0;
	protected $active = 0;

	//*** Constructor.
	public function __construct()
	{
		self::$object = "\\PunchCMS\\DBAL\\Account";
		self::$table = "punch_account";
	}

	//*** Static inherited functions.
	public static function selectByPK($varValue, $arrFields = array(), $accountId = NULL)
	{
		self::$object = "\\PunchCMS\\DBAL\\Account";
		self::$table = "punch_account";

		return parent::selectByPK($varValue, $arrFields, $accountId);
	}

	public static function select($strSql = "")
	{
		self::$object = "\\PunchCMS\\DBAL\\Account";
		self::$table = "punch_account";

		return parent::select($strSql);
	}

	public static function doDelete($varValue)
	{
		self::$object = "\\PunchCMS\\DBAL\\Account";
        self::$table = "punch_account";

		return parent::doDelete($varValue);
	}

	public function save($blnSaveModifiedDate = true)
	{
		self::$object = "\\PunchCMS\\DBAL\\Account";
        self::$table = "punch_account";

		return parent::save($blnSaveModifiedDate);
	}

	public function delete($accountId = NULL)
	{
		self::$object = "\\PunchCMS\\DBAL\\Account";
		self::$table = "punch_account";

		return parent::delete($accountId);
	}

	public function duplicate()
	{
		self::$object = "\\PunchCMS\\DBAL\\Account";
		self::$table = "punch_account";

		return parent::duplicate();
	}
}