<?php

namespace PunchCMS\DBAL;

/**
 * ElementFieldText DBA Class.
 * @author Felix Langfeldt <felix@neverwoods.com>
 * @internal
 *
 */
class ElementFieldText extends Object
{
    protected $id = null;
    protected $value = "";
    protected $fieldid = 0;
    protected $languageid = 0;
    protected $cascade = 0;

    //*** Constructor.
    public function __construct()
    {
        self::$object = "\\PunchCMS\\ElementFieldText";
        self::$table = "pcms_element_field_text";
    }

    // *** Static inherited functions.
    public static function selectByPK($varValue, $arrFields = array(), $accountId = null)
    {
        self::$object = "\\PunchCMS\\ElementFieldText";
        self::$table = "pcms_element_field_text";

        return parent::selectByPK($varValue, $arrFields, $accountId);
    }

    public static function select($strSql = "")
    {
        self::$object = "\\PunchCMS\\ElementFieldText";
        self::$table = "pcms_element_field_text";

        return parent::select($strSql);
    }

    public static function doDelete($varValue)
    {
        self::$object = "\\PunchCMS\\ElementFieldText";
        self::$table = "pcms_element_field_text";

        return parent::doDelete($varValue);
    }

    public function save($blnSaveModifiedDate = true)
    {
        self::$object = "\\PunchCMS\\ElementFieldText";
        self::$table = "pcms_element_field_text";

        return parent::save($blnSaveModifiedDate);
    }

    public function delete($accountId = null)
    {
        self::$object = "\\PunchCMS\\ElementFieldText";
        self::$table = "pcms_element_field_text";

        return parent::delete($accountId);
    }

    public function duplicate()
    {
        self::$object = "\\PunchCMS\\ElementFieldText";
        self::$table = "pcms_element_field_text";

        return parent::duplicate();
    }
}
