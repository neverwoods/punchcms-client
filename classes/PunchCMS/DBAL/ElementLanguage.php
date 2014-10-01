<?php

namespace PunchCMS\DBAL;

/**
 * ElementLanguage DBA Class.
 * @author Felix Langfeldt <felix@neverwoods.com>
 * @internal
 *
 */
class ElementLanguage extends Object
{
    protected $id = null;
    protected $elementid = 0;
    protected $languageid = 0;
    protected $cascade = 0;
    protected $active = 1;

    //*** Constructor.
    public function __construct()
    {
        self::$object = "\\PunchCMS\\ElementLanguage";
        self::$table = "pcms_element_language";
    }

    // *** Static inherited functions.
    public static function selectByPK($varValue, $arrFields = array(), $accountId = null)
    {
        self::$object = "\\PunchCMS\\ElementLanguage";
        self::$table = "pcms_element_language";

        return parent::selectByPK($varValue, $arrFields, $accountId);
    }

    public static function select($strSql = "")
    {
        self::$object = "\\PunchCMS\\ElementLanguage";
        self::$table = "pcms_element_language";

        return parent::select($strSql);
    }

    public static function doDelete($varValue)
    {
        self::$object = "\\PunchCMS\\ElementLanguage";
        self::$table = "pcms_element_language";

        return parent::doDelete($varValue);
    }

    public function save($blnSaveModifiedDate = true)
    {
        self::$object = "\\PunchCMS\\ElementLanguage";
        self::$table = "pcms_element_language";

        return parent::save($blnSaveModifiedDate);
    }

    public function delete($accountId = null)
    {
        self::$object = "\\PunchCMS\\ElementLanguage";
        self::$table = "pcms_element_language";

        return parent::delete($accountId);
    }

    public function duplicate()
    {
        self::$object = "\\PunchCMS\\ElementLanguage";
        self::$table = "pcms_element_language";

        return parent::duplicate();
    }
}
