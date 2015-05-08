<?php

namespace PunchCMS\DBAL;

/**
 * ElementSchedule DBA Class.
 * @author Felix Langfeldt <felix@neverwoods.com>
 * @internal
 *
 */

class ElementSchedule extends Object
{
    protected $id = null;
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
    public function __construct()
    {
        self::$object = "\\PunchCMS\\ElementSchedule";
        self::$table = "pcms_element_schedule";
    }

    // *** Static inherited functions.
    public static function selectByPK($varValue, $arrFields = array(), $accountId = null)
    {
        self::$object = "\\PunchCMS\\ElementSchedule";
        self::$table = "pcms_element_schedule";

        return parent::selectByPK($varValue, $arrFields, $accountId);
    }

    public static function select($strSql = "")
    {
        self::$object = "\\PunchCMS\\ElementSchedule";
        self::$table = "pcms_element_schedule";

        return parent::select($strSql);
    }

    public static function doDelete($varValue)
    {
        self::$object = "\\PunchCMS\\ElementSchedule";
        self::$table = "pcms_element_schedule";

        return parent::doDelete($varValue);
    }

    public function save($blnSaveModifiedDate = true)
    {
        self::$object = "\\PunchCMS\\ElementSchedule";
        self::$table = "pcms_element_schedule";

        return parent::save($blnSaveModifiedDate);
    }

    public function delete($accountId = null)
    {
        self::$object = "\\PunchCMS\\ElementSchedule";
        self::$table = "pcms_element_schedule";

        return parent::delete($accountId);
    }

    public function duplicate()
    {
        self::$object = "\\PunchCMS\\ElementSchedule";
        self::$table = "pcms_element_schedule";

        return parent::duplicate();
    }
}
