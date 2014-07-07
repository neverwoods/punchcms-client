<?php

namespace PunchCMS;

/**
 *
 * Holds search results.
 * @author felix
 * @version 0.1.0
 *
 */
class SearchResults extends \PunchCMS\DBAL\Collection
{
	private $query = "";

	public function getQuery()
	{
		return $this->query;
	}

	public function setQuery($value)
	{
		$this->query = $value;
	}
}
