<?php
 
/**
 * 
 * Holds search results.
 * @author felix
 * @version 0.1.0
 *
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
