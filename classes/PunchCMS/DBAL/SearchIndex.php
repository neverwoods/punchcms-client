<?php

namespace PunchCMS\DBAL;

/**
 * SearchIndex DBA Class.
 * @author Felix Langfeldt <felix@neverwoods.com>
 * @internal
 *
 */
class SearchIndex extends Object
{
    protected $id = null;
    protected $elementid = 0;
    protected $word = 0;
    protected $count = "";

    //*** Constructor.
    public function __construct()
    {
        self::$object = "\\PunchCMS\\SearchIndex";
        self::$table = "pcms_search_index";
    }

    // *** Static inherited functions.
    public static function selectByPK($varValue, $arrFields = array(), $accountId = null)
    {
        self::$object = "\\PunchCMS\\SearchIndex";
        self::$table = "pcms_search_index";

        return parent::selectByPK($varValue, $arrFields, $accountId);
    }

    public static function select($strSql = "")
    {
        self::$object = "\\PunchCMS\\SearchIndex";
        self::$table = "pcms_search_index";

        return parent::select($strSql);
    }

    public static function doDelete($varValue)
    {
        self::$object = "\\PunchCMS\\SearchIndex";
        self::$table = "pcms_search_index";

        return parent::doDelete($varValue);
    }

    public function save($blnSaveModifiedDate = true)
    {
        self::$object = "\\PunchCMS\\SearchIndex";
        self::$table = "pcms_search_index";

        return parent::save($blnSaveModifiedDate);
    }

    public function delete($accountId = null)
    {
        self::$object = "\\PunchCMS\\SearchIndex";
        self::$table = "pcms_search_index";

        return parent::delete($accountId);
    }

    public function duplicate()
    {
        self::$object = "\\PunchCMS\\SearchIndex";
        self::$table = "pcms_search_index";

        return parent::duplicate();
    }
}
