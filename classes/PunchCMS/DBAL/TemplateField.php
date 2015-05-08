<?php

namespace PunchCMS\DBAL;

/**
 * TemplateField DBA Class.
 * @author Felix Langfeldt <felix@neverwoods.com>
 * @internal
 *
 */
class TemplateField extends Object
{
    protected $id = null;
    protected $templateid = 0;
    protected $formid = 0;
    protected $typeid = 0;
    protected $required = 0;
    protected $name = "";
    protected $apiname = "";
    protected $description = "";
    protected $username = "";

    //*** Constructor.
    public function __construct()
    {
        self::$object = "\\PunchCMS\\TemplateField";
        self::$table = "pcms_template_field";
    }

    // *** Static inherited functions.
    public static function selectByPK($varValue, $arrFields = array(), $accountId = null)
    {
        self::$object = "\\PunchCMS\\TemplateField";
        self::$table = "pcms_template_field";

        return parent::selectByPK($varValue, $arrFields, $accountId);
    }

    public static function select($strSql = "")
    {
        self::$object = "\\PunchCMS\\TemplateField";
        self::$table = "pcms_template_field";

        return parent::select($strSql);
    }

    public static function doDelete($varValue)
    {
        self::$object = "\\PunchCMS\\TemplateField";
        self::$table = "pcms_template_field";

        return parent::doDelete($varValue);
    }

    public function save($blnSaveModifiedDate = true)
    {
        self::$object = "\\PunchCMS\\TemplateField";
        self::$table = "pcms_template_field";

        return parent::save($blnSaveModifiedDate);
    }

    public function delete($accountId = null)
    {
        self::$object = "\\PunchCMS\\TemplateField";
        self::$table = "pcms_template_field";

        return parent::delete($accountId);
    }

    public function duplicate()
    {
        self::$object = "\\PunchCMS\\TemplateField";
        self::$table = "pcms_template_field";

        return parent::duplicate();
    }
}
