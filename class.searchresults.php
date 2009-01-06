<?php

/* SearchResults Class v0.1.0
 * Holds search results.
 *
 * CHANGELOG
 * version 0.1.0, 04 Apr 2006
 *   NEW: Created class.
 */
 
class SearchResults extends DBA__Collection {
	private $__query = "";

	public function getQuery() {
		return $this->__query;
	}

	public function setQuery($value) {
		$this->__query = $value;
	}
}

?>
