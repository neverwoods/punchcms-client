<?php

/***
 *
 * SearchIndex DBA Class.
 *
 */

class DBA_SearchIndex extends DBA__Object {
	protected $id = NULL;
	protected $elementid = 0;
	protected $word = 0;
	protected $count = "";

	//*** Constructor.
	public function DBA_SearchIndex() {
		self::$object = "SearchIndex";
		self::$table = "pcms_search_index";
	}

	//*** Static inherited functions.
	public static function selectByPK($varValue, $arrFields = array(), $accountId = NULL) {
		self::$object = "SearchIndex";
		self::$table = "pcms_search_index";

		return parent::selectByPK($varValue, $arrFields, $accountId);
	}

	public static function select($strSql = "") {
		self::$object = "SearchIndex";
		self::$table = "pcms_search_index";

		return parent::select($strSql);
	}

	public static function doDelete($varValue) {
		self::$object = "SearchIndex";
		self::$table = "pcms_search_index";

		return parent::doDelete($varValue);
	}

	public function save($blnSaveModifiedDate = TRUE) {
		self::$object = "SearchIndex";
		self::$table = "pcms_search_index";

		return parent::save($blnSaveModifiedDate);
	}

	public function delete($accountId = NULL) {
		self::$object = "SearchIndex";
		self::$table = "pcms_search_index";

		return parent::delete($accountId);
	}

	public function duplicate() {
		self::$object = "SearchIndex";
		self::$table = "pcms_search_index";

		return parent::duplicate();
	}
}

?>