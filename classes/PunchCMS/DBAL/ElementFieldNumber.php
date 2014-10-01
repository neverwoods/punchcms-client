<?php

namespace PunchCMS\DBAL;

/**
 * ElementFieldNumber DBA Class.
 * @author Felix Langfeldt <felix@neverwoods.com>
 * @internal
 *
 */
class ElementFieldNumber extends Object
{
    protected $id = null;
    protected $value = 0;
    protected $fieldid = 0;
    protected $languageid = 0;
    protected $cascade = 0;

    //*** Constructor.
    public function __construct()
    {
        self::$object = "\\PunchCMS\\ElementFieldNumber";
        self::$table = "pcms_element_field_number";
    }

    // *** Static inherited functions.
    public static function selectByPK($varValue, $arrFields = array(), $accountId = null)
    {
        self::$object = "\\PunchCMS\\ElementFieldNumber";
        self::$table = "pcms_element_field_number";

        return parent::selectByPK($varValue, $arrFields, $accountId);
    }

    public static function select($strSql = "")
    {
        self::$object = "\\PunchCMS\\ElementFieldNumber";
        self::$table = "pcms_element_field_number";

        return parent::select($strSql);
    }

    public static function doDelete($varValue)
    {
        self::$object = "\\PunchCMS\\ElementFieldNumber";
        self::$table = "pcms_element_field_number";

        return parent::doDelete($varValue);
    }

    public function save($blnSaveModifiedDate = true)
    {
        self::$object = "\\PunchCMS\\ElementFieldNumber";
        self::$table = "pcms_element_field_number";

        return parent::save($blnSaveModifiedDate);
    }

    public function delete($accountId = null)
    {
        self::$object = "\\PunchCMS\\ElementFieldNumber";
        self::$table = "pcms_element_field_number";

        return parent::delete($accountId);
    }

    public function duplicate()
    {
        self::$object = "\\PunchCMS\\ElementFieldNumber";
        self::$table = "pcms_element_field_number";

        return parent::duplicate();
    }
}
