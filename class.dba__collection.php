<?php

/* General DBA Collection Class v0.3.1
 * Holds a collection of objects.
 *
 * CHANGELOG
 * version 0.3.1, 18 december 2009
 *   FIX: Fixed random method. Better index checks.
 * version 0.3.0, 6 december 2009
 *   FIX: Fixed merge method. Better collection checks.
 * version 0.2.9, 5 May 2009
 *   CHG: Changed paging to url based instead of request based (setCurrentPage).
 * version 0.2.8, 7 Jul 2008
 *   ADD: Added merge method.
 * version 0.2.7, 16 Jun 2008
 *   ADD: Added isFirst method.
 *   ADD: Added isLast method.
 * version 0.2.6, 18 Apr 2008
 *   FIX: Fixed setPageItems to seek to the right item.
 * version 0.2.5, 23 Jan 2008
 *   ADD: Added documentation to all methods.
 *   ADD: Added getPageByChild method.
 * version 0.2.4, 05 Nov 2007
 *   REM: Removed the orderByField method. Out of context.
 * version 0.2.3, 05 Oct 2007
 *   FIX: Fixed the inner workings of the reverse method.
 * version 0.2.2, 21 Aug 2007
 *   ADD: Added methods for paginating.
 * version 0.2.1, 02 Aug 2007
 *   ADD: Added the reverse method.
 * version 0.2.0, 16 May 2006
 *   NEW: Created class.
 */

class DBA__Collection implements Iterator {
	protected $collection = array();
	private $isSeek = FALSE;
	private $__pageItems = 0;
	private $__currentPage = 1;

	public function __construct($initArray = array()) {
	   if (is_array($initArray)) {
		   $this->collection = $initArray;
	   }
	}

	public function addObject($value, $blnAddToBeginning = FALSE) {
		/* Add an object to the collection.
		 *
		 * Method arguments are:
		 * - object to add.
		 */

		if ($blnAddToBeginning) {
			array_unshift($this->collection, $value);
		} else {
			array_push($this->collection, $value);
		}
	}

	public function seek($intPosition) {
    	//*** Advance the internal pointer to a specific index
        if (is_numeric($intPosition) && $intPosition < count($this->collection)) {
        	reset($this->collection);
			while($intPosition > key($this->collection)) {
				next($this->collection);
			}
        }

		$this->isSeek = TRUE;
	}

    public function random() {
    	//*** Pick a random child element.
    	$objReturn = null;
    	
    	$intIndex = rand(0, (count($this->collection) - 1));
    	if (isset($this->collection[$intIndex])) {
			$objReturn = $this->collection[$intIndex];
    	}
    	
    	return $objReturn;
    }

    public function randomize() {
    	//*** Randomize the collection.
		shuffle($this->collection);
    }

	public function orderBy($strSubject, $strOrder = "asc") {
    	//*** Order the collection on a given key [asc]ending or [desc]ending.

		for ($i = 0; $i < count($this->collection); $i++) {
			for ($j = 0; $j < count($this->collection) - $i - 1; $j++) {
				if ($strOrder == "asc") {
					if ($this->collection[$j + 1]->$strSubject < $this->collection[$j]->$strSubject) {
						$objTemp = $this->collection[$j];
						$this->collection[$j] = $this->collection[$j + 1];
						$this->collection[$j + 1] = $objTemp;
					}
				} else {
					if ($this->collection[$j + 1]->$strSubject > $this->collection[$j]->$strSubject) {
						$objTemp = $this->collection[$j];
						$this->collection[$j] = $this->collection[$j + 1];
						$this->collection[$j + 1] = $objTemp;
					}
				}
			}
		}
	}

	public function count() {
		//*** Get the item count.
		return count($this->collection);
	}

    public function current() {
		//*** Get the current item from the collection.
        return current($this->collection);
    }

    public function next() {
		//*** Place the pointer one item forward and return the item.
        return next($this->collection);
    }

    public function previous() {
		//*** Place the pointer one item back and return the item.
        return prev($this->collection);
    }

    public function key() {
		//*** Get the current position of the pointer.
        return key($this->collection);
    }

    public function isFirst() {
		//*** Check if the pointer is at the first record.
        return key($this->collection) == 0;
    }

    public function isLast() {
		//*** Check if the pointer is at the last record.
        return key($this->collection) == (count($this->collection) - 1);
    }

    public function merge($collection) {
		//*** Merge a collection with this collection.
		if (is_object($collection) && $collection->count() > 0) {
        	$this->collection = array_merge($this->collection, $collection->collection);
		}
    }

    public function valid() {
		//*** Test if the requested item is valid.
    	if ($this->__pageItems > 0) {
    		if ($this->key() + 1 > $this->pageEnd()) {
    			return FALSE;
    		} else {
    			return $this->current() !== FALSE;
    		}
    	} else {
    		return $this->current() !== FALSE;
    	}
    }

    public function rewind() {
		//*** Reset the internal pointer of the collection to the first item.
    	if ($this->__pageItems > 0) {
			$this->setCurrentPage();
    		$this->seek($this->pageStart() - 1);
    	} else {
			if (!$this->isSeek) {
				reset($this->collection);
			}
    	}
    }

    public function reverse() {
		//*** Reverse the order of the collection and return it.
		$this->collection = array_reverse($this->collection);
        return $this;
    }

    public function end() {
		//*** Set the internal pointer of the collection to the last item and return it.
        return end($this->collection);
    }

    public function inCollection($varValue) {
		//*** Test if an item is in the collection.
    	foreach ($this->collection as $object) {
    		if ($object == $varValue) {
				//*** Reset the internal pointer.
				self::rewind();
    			return TRUE;
    		}
    	}

		//*** Reset the internal pointer.
		self::rewind();

    	return FALSE;
    }

	public function setPageItems($intValue) {
		//*** Set the number of items per page.
		$this->__pageItems = $intValue;

		$this->setCurrentPage();
		$this->seek($this->pageStart() - 1);
	}

	public function getPageItems() {
		//*** Get the number of items per page.
		return $this->__pageItems;
	}

	public function setCurrentPage($intValue = NULL) {
		//*** Set the current page number.
		if (is_null($intValue)) {
			$intPage = 1;
			$strRewrite	= Request::get('rewrite');
			if (!empty($strRewrite) && mb_strpos($strRewrite, "__page") !== FALSE) {
				$strRewrite = rtrim($strRewrite, " \/");
				$arrParams = explode("/", $strRewrite);
				$intPage = array_pop($arrParams);
			} else {
				//*** Backwards compatibility.
				$intPage = Request::get("page", 1);
			}

			if ($intPage > $this->pageCount() || $intPage < 1) $intPage = 1;
			$this->__currentPage = $intPage;
		} else {
			$this->__currentPage = $intValue;
		}
	}

	public function getCurrentPage() {
		//*** Get the current page number.
		return $this->__currentPage;
	}

	public function pageCount() {
		//*** Get the number of pages for this collection.
		if ($this->__pageItems > 0) {
			$intReturn = ceil(count($this->collection) / $this->__pageItems);
		} else {
			$intReturn = 1;
		}

		return $intReturn;
	}

	public function pageStart() {
		//*** Get the number of the first item in the current page.
		return ($this->getCurrentPage() * $this->__pageItems) - ($this->__pageItems - 1);
	}

	public function pageEnd() {
		//*** Get the number of the last item in the current page.
		$intReturn = ($this->getCurrentPage() * $this->__pageItems);
		if ($intReturn > count($this->collection)) $intReturn = count($this->collection);

		return $intReturn;
	}

	public function nextPage() {
		//*** Get the page number of the next page.
		$intReturn = ($this->getCurrentPage() + 1 < $this->pageCount()) ? $this->getCurrentPage() + 1 : $this->pageCount();

		return $intReturn;
	}

	public function previousPage() {
		//*** Get the page number of the previous page.
		$intReturn = ($this->getCurrentPage() - 1 > 0) ? ($this->getCurrentPage() - 1) : 1;

		return $intReturn;
	}

	public function getPageByChild($objChild) {
		//*** Get the page number the child item is in.
		$intReturn = 1;

		$intId = (is_object($objChild)) ? $objChild->getId() : $objChild;
		$intCount = 1;
		foreach ($this->collection as $object) {
			if ($object->getId() == $intId) {
				$intReturn = ceil($intCount / $this->__pageItems);
			}

			$intCount++;
		}

		//*** Reset the internal pointer.
		self::rewind();

		return $intReturn;
	}

	public function seekByChild($objChild) {
    	//*** Advance the internal pointer to a specific index indicated by a child item and return the index.
    	$intReturn = 0;
		$intId = (is_object($objChild)) ? $objChild->getId() : $objChild;

		$intCount = 0;
        reset($this->collection);
		foreach ($this->collection as $object) {
			if ($object->getId() == $intId) {
				$intReturn = $intCount;
			}

			$intCount++;
		}

		$this->isSeek = TRUE;

		return $intReturn;
	}
}

?>