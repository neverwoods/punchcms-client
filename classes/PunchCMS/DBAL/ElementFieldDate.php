<?php

namespace PunchCMS\DBAL;

/**
 * ElementFieldDate DBA Class.
 * @author Felix Langfeldt <felix@neverwoods.com>
 * @internal
 *
 */
class ElementFieldDate extends Object
{
    protected $id = null;
    protected $value = "0000-00-00 00:00:00";
    protected $fieldid = 0;
    protected $languageid = 0;
    protected $cascade = 0;

    // *** Constructor.
    public function __construct()
    {
        self::$object = "\\PunchCMS\\ElementFieldDate";
        self::$table = "pcms_element_field_date";
    }

    // *** Static inherited functions.
    public static function selectByPK($varValue, $arrFields = array(), $accountId = null)
    {
        self::$object = "\\PunchCMS\\ElementFieldDate";
        self::$table = "pcms_element_field_date";

        return parent::selectByPK($varValue, $arrFields, $accountId);
    }

    public static function select($strSql = "")
    {
        self::$object = "\\PunchCMS\\ElementFieldDate";
        self::$table = "pcms_element_field_date";

        return parent::select($strSql);
    }

    public static function doDelete($varValue)
    {
        self::$object = "\\PunchCMS\\ElementFieldDate";
        self::$table = "pcms_element_field_date";

        return parent::doDelete($varValue);
    }

    public function save($blnSaveModifiedDate = true)
    {
        self::$object = "\\PunchCMS\\ElementFieldDate";
        self::$table = "pcms_element_field_date";

        return parent::save($blnSaveModifiedDate);
    }

    public function delete($accountId = null)
    {
        self::$object = "\\PunchCMS\\ElementFieldDate";
        self::$table = "pcms_element_field_date";

        return parent::delete($accountId);
    }

    public function duplicate()
    {
        self::$object = "\\PunchCMS\\ElementFieldDate";
        self::$table = "pcms_element_field_date";

        return parent::duplicate();
    }
}
