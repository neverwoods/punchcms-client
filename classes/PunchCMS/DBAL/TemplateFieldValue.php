<?php

namespace PunchCMS\DBAL;

/**
 * TemplateFieldValue DBA Class.
 * @author Felix Langfeldt <felix@neverwoods.com>
 * @internal
 *
 */
class TemplateFieldValue extends Object
{
    protected $id = null;
    protected $fieldid = 0;
    protected $name = "";
    protected $value = "";

    //*** Constructor.
    public function __construct()
    {
        self::$object = "\\PunchCMS\\TemplateFieldValue";
        self::$table = "pcms_template_field_value";
    }

    // *** Static inherited functions.
    public static function selectByPK($varValue, $arrFields = array(), $accountId = null)
    {
        self::$object = "\\PunchCMS\\TemplateFieldValue";
        self::$table = "pcms_template_field_value";

        return parent::selectByPK($varValue, $arrFields, $accountId);
    }

    public static function select($strSql = "")
    {
        self::$object = "\\PunchCMS\\TemplateFieldValue";
        self::$table = "pcms_template_field_value";

        return parent::select($strSql);
    }

    public static function doDelete($varValue)
    {
        self::$object = "\\PunchCMS\\TemplateFieldValue";
        self::$table = "pcms_template_field_value";

        return parent::doDelete($varValue);
    }

    public function save($blnSaveModifiedDate = true)
    {
        self::$object = "\\PunchCMS\\TemplateFieldValue";
        self::$table = "pcms_template_field_value";

        return parent::save($blnSaveModifiedDate);
    }

    public function delete($accountId = null)
    {
        self::$object = "\\PunchCMS\\TemplateFieldValue";
        self::$table = "pcms_template_field_value";

        return parent::delete($accountId);
    }

    public function duplicate()
    {
        self::$object = "\\PunchCMS\\TemplateFieldValue";
        self::$table = "pcms_template_field_value";

        return parent::duplicate();
    }
}
