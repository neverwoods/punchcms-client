<?php

namespace PunchCMS\DBAL;

/**
 * TemplateFieldType DBA Class.
 * @author Felix Langfeldt <felix@neverwoods.com>
 * @internal
 *
 */
class TemplateFieldType extends Object
{
    protected $id = null;
    protected $name = "";
    protected $input = "";
    protected $element = "";

    //*** Constructor.
    public function __construct()
    {
        self::$object = "\\PunchCMS\\TemplateFieldType";
        self::$table = "pcms_template_field_type";
    }

    // *** Static inherited functions.
    public static function selectByPK($varValue, $arrFields = array(), $accountId = null)
    {
        self::$object = "\\PunchCMS\\TemplateFieldType";
        self::$table = "pcms_template_field_type";

        return parent::selectByPK($varValue, $arrFields, $accountId);
    }

    public static function select($strSql = "")
    {
        self::$object = "\\PunchCMS\\TemplateFieldType";
        self::$table = "pcms_template_field_type";

        return parent::select($strSql);
    }

    public static function doDelete($varValue)
    {
        self::$object = "\\PunchCMS\\TemplateFieldType";
        self::$table = "pcms_template_field_type";

        return parent::doDelete($varValue);
    }

    public function save($blnSaveModifiedDate = true)
    {
        self::$object = "\\PunchCMS\\TemplateFieldType";
        self::$table = "pcms_template_field_type";

        return parent::save($blnSaveModifiedDate);
    }

    public function delete($accountId = null)
    {
        self::$object = "\\PunchCMS\\TemplateFieldType";
        self::$table = "pcms_template_field_type";

        return parent::delete($accountId);
    }

    public function duplicate()
    {
        self::$object = "\\PunchCMS\\TemplateFieldType";
        self::$table = "pcms_template_field_type";

        return parent::duplicate();
    }
}
