<?php

namespace PunchCMS\DBAL;

use Bili\Request;

/**
 * General DBAL Collection Class
 *
 * Holds a Collection of objects
 *
 * @author Felix Langfeldt <felix@neverwoods.com>
 * @internal
 */
class Collection implements \Iterator
{
    protected $collection = array();
    private $isSeek = false;
    private $pageItems = 0;
    private $currentPage = 1;

    /**
     * Constructor method
     *
     * @param array $initArray
     */
    public function __construct($initArray = array())
    {
        if (is_array($initArray)) {
             $this->collection = $initArray;
        }
    }

    /**
     * Add object to the collection
     *
     * @param object The object
     * @param boolean Add object to beginning of array or not
     */
    public function addObject($value, $blnAddToBeginning = false)
    {
        if ($blnAddToBeginning) {
            array_unshift($this->collection, $value);
        } else {
            array_push($this->collection, $value);
        }
    }

    /**
     * Advance internal pointer to a specific index
     *
     * @param integer $intPosition
     */
    public function seek($intPosition)
    {
        if (is_numeric($intPosition) && $intPosition < count($this->collection)) {
            reset($this->collection);
            while ($intPosition > key($this->collection)) {
                next($this->collection);
            }
        }

        $this->isSeek = true;
    }

    /**
     * Pick a random child element
     */
    public function random()
    {
        $objReturn = null;

        $intIndex = rand(0, (count($this->collection) - 1));
        if (isset($this->collection[$intIndex])) {
            $objReturn = $this->collection[$intIndex];
        }

        return $objReturn;
    }

    /**
     * Randomize the collection
     */
    public function randomize()
    {
        shuffle($this->collection);
    }

    /**
     * Get an element of the collection selected by property value.
     */
    public function getByPropertyValue($strSearchProperty, $strSearchValue)
    {
        $objReturn = null;

        foreach ($this->collection as $objElement) {
            $strProperty = "get{$strSearchProperty}";
            if (is_callable(array($objElement, $strProperty))) {
                if ($objElement->$strProperty() == $strSearchValue) {
                    $objReturn = $objElement;
                    break;
                }
            }
        }

        return $objReturn;
    }

    /**
     * Get the value of a property of a specific element, selected by property value.
     */
    public function getValueByValue($strSearchProperty, $strSearchValue, $strResultProperty = "value")
    {
        $strReturn = "";

        $objElement = $this->getByPropertyValue($strSearchProperty, $strSearchValue);
        if (is_object($objElement)) {
            $strProperty = "get{$strResultProperty}";
            if (is_callable(array($objElement, $strProperty))) {
                $strReturn = $objElement->$strProperty();
            }
        }

        return $strReturn;
    }

    /**
     * Order the collection on a given key [asc]ending or [desc]ending
     *
     * @param string $strSubject
     * @param string $strOrder
     */
    public function orderBy($strSubject, $strOrder = "asc")
    {
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

    /**
     * Get the item count.
     */
    public function count()
    {
        return count($this->collection);
    }

    /**
     * Get the current item from the collection.
     */
    public function current()
    {
        return current($this->collection);
    }

    /**
     * Place the pointer one item forward and return the item.
     */
    public function next()
    {
        return next($this->collection);
    }

    /**
     * Place the pointer one item back and return the item.
     */
    public function previous()
    {
        return prev($this->collection);
    }

    /**
     * Get the current position of the pointer.
     */
    public function key()
    {
        return key($this->collection);
    }

    /**
     * Check if the pointer is at the first record.
     */
    public function isFirst()
    {
        return key($this->collection) == 0;
    }

    /**
     * Check if the pointer is at the last record.
     */
    public function isLast()
    {
        return key($this->collection) == (count($this->collection) - 1);
    }

    /**
     * Merge a collection with this collection.
     */
    public function merge($collection)
    {
        if (is_object($collection) && $collection->count() > 0) {
            $this->collection = array_merge($this->collection, $collection->collection);
        }
    }

    /**
     * Test if the requested item is valid.
     */
    public function valid()
    {
        if ($this->pageItems > 0) {
            if ($this->key() + 1 > $this->pageEnd()) {
                return false;
            } else {
                return $this->current() !== false;
            }
        } else {
            return $this->current() !== false;
        }
    }

    /**
     * Reset the internal pointer of the collection to the first item.
     */
    public function rewind()
    {
        if ($this->pageItems > 0) {
            $this->setCurrentPage();
            $this->seek($this->pageStart() - 1);
        } else {
            if (!$this->isSeek) {
                reset($this->collection);
            }
        }

        return $this;
    }

    /**
     * Reverse the order of the collection and return it.
     */
    public function reverse()
    {
        $this->collection = array_reverse($this->collection);
        return $this;
    }

    /**
     * Set the internal pointer of the collection to the last item and return it.
     */
    public function end()
    {
        return end($this->collection);
    }

    /**
     * Check if an object is in the collection
     *
     * @param variable $varValue
     */
    public function inCollection($varValue)
    {
        $blnReturn = false;
        foreach ($this->collection as $object) {
            if ($object == $varValue) {
                $blnReturn = true;
                break;
            }
        }

        //*** Reset the internal pointer.
        self::rewind();

        return $blnReturn;
    }

    /**
     * Set the number of items per page.
     *
     * @param integer $intValue
     */
    public function setPageItems($intValue)
    {
        $this->pageItems = $intValue;

        $this->setCurrentPage();
        $this->seek($this->pageStart() - 1);
    }

    /**
     * Get the number of items per page.
     */
    public function getPageItems()
    {
        return $this->pageItems;
    }

    /**
     * Set the current page.
     *
     * @param integer $intValue
     */
    public function setCurrentPage($intValue = null)
    {
        if (is_null($intValue)) {
            $intPage = 1;
            $strRewrite    = Request::get('rewrite');
            if (!empty($strRewrite) && mb_strpos($strRewrite, "__page") !== false) {
                $strRewrite = rtrim($strRewrite, " \/");
                $arrParams = explode("/", $strRewrite);
                $intPage = array_pop($arrParams);
            } else {
                //*** Backwards compatibility.
                $intPage = Request::get("page", 1);
            }

            if ($intPage > $this->pageCount() || $intPage < 1) {
                $intPage = 1;
            }

            $this->currentPage = $intPage;
        } else {
            $this->currentPage = $intValue;
        }
    }

    /**
     * Get the current page number.
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * Get the number of pages for this collection.
     */
    public function pageCount()
    {
        if ($this->pageItems > 0) {
            $intReturn = ceil(count($this->collection) / $this->pageItems);
        } else {
            $intReturn = 1;
        }

        return $intReturn;
    }

    /**
     * Get the number of the first item in the current page.
     */
    public function pageStart()
    {
        return ($this->getCurrentPage() * $this->pageItems) - ($this->pageItems - 1);
    }

    /**
     * Get the number of the last item in the current page.
     */
    public function pageEnd()
    {
        $intReturn = ($this->getCurrentPage() * $this->pageItems);
        if ($intReturn > count($this->collection)) {
            $intReturn = count($this->collection);
        }

        return $intReturn;
    }

    /**
     * Get the page number of the next page.
     */
    public function nextPage()
    {
        $intReturn = ($this->getCurrentPage() + 1 < $this->pageCount()) ? $this->getCurrentPage() + 1 : $this->pageCount();

        return $intReturn;
    }

    /**
     * Get the page number of the previous page.
     */
    public function previousPage()
    {
        $intReturn = ($this->getCurrentPage() - 1 > 0) ? ($this->getCurrentPage() - 1) : 1;

        return $intReturn;
    }

    /**
     * Get the page number the child item is in.
     *
     * @param object $objChild
     */
    public function getPageByChild($objChild)
    {
        $intReturn = 1;

        $intId = (is_object($objChild)) ? $objChild->getId() : $objChild;
        $intCount = 1;
        foreach ($this->collection as $object) {
            if ($object->getId() == $intId) {
                $intReturn = ceil($intCount / $this->pageItems);
            }

            $intCount++;
        }

        //*** Reset the internal pointer.
        self::rewind();

        return $intReturn;
    }

    /**
     * Advance the internal pointer to a specific index indicated by a child item and return the index.
     *
     * @param object $objChild
     */
    public function seekByChild($objChild)
    {
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

        $this->isSeek = true;

        return $intReturn;
    }
}
