<?php

namespace PunchCMS\DBAL;

/**
 * ElementField DBA Class.
 *
 * @author Felix Langfeldt <felix@neverwoods.com>
 * @internal
 *
 */
class ElementField extends Object
{
    protected $id = null;
    protected $elementid = 0;
    protected $templatefieldid = 0;
    protected $fieldtypeid = 0;
    protected $originalname = "";
    protected $username = "";

    // *** Constructor.
    public function __construct()
    {
        self::$object = "\\PunchCMS\\ElementField";
        self::$table = "pcms_element_field";
    }

    // *** Static inherited functions.
    public static function selectByPK($varValue, $arrFields = array(), $accountId = null)
    {
        self::$object = "\\PunchCMS\\ElementField";
        self::$table = "pcms_element_field";

        return parent::selectByPK($varValue, $arrFields, $accountId);
    }

    public static function select($strSql = "")
    {
        self::$object = "\\PunchCMS\\ElementField";
        self::$table = "pcms_element_field";

        return parent::select($strSql);
    }

    public static function doDelete($varValue)
    {
        self::$object = "\\PunchCMS\\ElementField";
        self::$table = "pcms_element_field";

        return parent::doDelete($varValue);
    }

    public function save($blnSaveModifiedDate = true)
    {
        self::$object = "\\PunchCMS\\ElementField";
        self::$table = "pcms_element_field";

        return parent::save($blnSaveModifiedDate);
    }

    public function delete($accountId = null)
    {
        self::$object = "\\PunchCMS\\ElementField";
        self::$table = "pcms_element_field";

        return parent::delete($accountId);
    }

    public function duplicate()
    {
        self::$object = "\\PunchCMS\\ElementField";
        self::$table = "pcms_element_field";

        return parent::duplicate();
    }
}
