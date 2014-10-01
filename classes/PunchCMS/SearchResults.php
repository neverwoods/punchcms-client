<?php

namespace PunchCMS;

use PunchCMS\DBAL\Collection;

/**
 *
 * Holds search results.
 * @author felix
 * @version 0.1.0
 *
 */
class SearchResults extends Collection
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
