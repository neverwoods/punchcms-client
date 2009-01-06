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
		self::$__object = "SearchIndex";
		self::$__table = "pcms_search_index";
	}

	//*** Static inherited functions.
	public static function selectByPK($varValue, $arrFields = array()) {
		self::$__object = "SearchIndex";
		self::$__table = "pcms_search_index";

		return parent::selectByPK($varValue, $arrFields);
	}

	public static function select($strSql = "") {
		self::$__object = "SearchIndex";
		self::$__table = "pcms_search_index";

		return parent::select($strSql);
	}

	public static function doDelete($varValue) {
		self::$__object = "SearchIndex";
		self::$__table = "pcms_search_index";

		return parent::doDelete($varValue);
	}

	public function save($blnSaveModifiedDate = TRUE) {
		self::$__object = "SearchIndex";
		self::$__table = "pcms_search_index";

		return parent::save($blnSaveModifiedDate);
	}

	public function delete() {
		self::$__object = "SearchIndex";
		self::$__table = "pcms_search_index";

		return parent::delete();
	}

	public function duplicate() {
		self::$__object = "SearchIndex";
		self::$__table = "pcms_search_index";

		return parent::duplicate();
	}
}

?>