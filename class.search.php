<?php

/* Search Class v0.2.1
 * Searches for strings in the elements.
 *
 * CHANGELOG
 * version 0.2.1, 30 May 2008
 *   CHG: Refined the wildcard seach.
 * version 0.2.0, 18 Apr 2008
 *   CHG: Added wildcard seach to the find method.
 * version 0.1.0, 04 Apr 2006
 *   NEW: Created class.
 */

class Search {
	const SEARCH_WEIGHT = 1;

	//*** Public Methods.
	public function updateIndex($intId = 0) {
		if ($intId > 0) {
			$objElement = Element::selectByPk($intId);
			$objElements = new DBA__Collection();
			$objElements->addObject($objElement);
		} else {
			$objElements = Element::select();
		}

		foreach ($objElements as $objElement) {
			//*** Delete current index.
			$this->deleteSearchIndex($objElement->getId());

			//*** Get index words from the elements text field.
			$strSql = sprintf("SELECT pcms_element_field_text.value as value
					FROM pcms_element_field_text, pcms_element_field
					WHERE pcms_element_field_text.fieldId = pcms_element_field.id
					AND pcms_element_field.elementId = '%s'", quote_smart($objElement->getId()));

			$objElementFields = ElementFieldText::select($strSql);

			foreach ($objElementFields as $objElementField) {
				$this->insertSearchWord($objElementField->getValue(), $objElement->getId());
			}

			//*** Get index words from the elements bigtext field.
			$strSql = sprintf("SELECT pcms_element_field_bigtext.value as value
					FROM pcms_element_field_bigtext, pcms_element_field
					WHERE pcms_element_field_bigtext.fieldId = pcms_element_field.id
					AND pcms_element_field.elementId = '%s'", quote_smart($objElement->getId()));

			$objElementFields = ElementFieldBigText::select($strSql);

			foreach ($objElementFields as $objElementField) {
				$this->insertSearchWord($objElementField->getValue(), $objElement->getId());
			}
		}
	}

	public function find($strQuery, $blnExact = false) {
		global $_CONF;

		//*** Set query property.
		$objReturn		= new SearchResults();
		$objReturn->setQuery($strQuery);
		$strQuery		= str_replace("*", "%", $strQuery);

		//*** Convert query to stem.
		$arrWords		= array_values($this->stemPhrase($strQuery));
		$intWordCount	= count($arrWords);

		//*** Query does not validate.
		if (!$arrWords) {
			return $objReturn;
		}

		//*** Set query property.
		$objReturn->setQuery($strQuery);

		$strSql = sprintf("SELECT DISTINCT pcms_search_index.elementId, COUNT(pcms_search_index.id) as word,
					SUM(pcms_search_index.count) as count FROM pcms_search_index, pcms_element WHERE
					pcms_search_index.elementId = pcms_element.id AND
					pcms_element.accountId = '%s' AND ", quote_smart($_CONF['app']['account']->getId()));
		$strSql .= '(' . implode(' OR ', array_fill(0, $intWordCount, '?')) . ')
					GROUP BY pcms_search_index.elementId';

		//*** AND query?
		if ($blnExact) {
			$strSql .= ' HAVING word = ' . $intWordCount;
		}

		$strSql .= ' ORDER BY word DESC, count DESC';

		//*** Inject the search words into the query.
		$arrSql = explode('?', $strSql);
		$strTempSql = "";
		for ($i = 0; $i < $intWordCount; $i++) {
			$equal = (stripos($arrWords[$i], "%") !== FALSE) ? "LIKE" : "=";
			$strTempSql .= $arrSql[$i] . "word {$equal} '" . $arrWords[$i] . "'";
		}
		$strTempSql .= $arrSql[$i];

		//*** Query the database.
		$objSearchIndexes = SearchIndex::select($strTempSql);

		foreach ($objSearchIndexes as $objSearchIndex) {
			$objElement = Element::selectByPk($objSearchIndex->getElementId());

			if (!isset($intMaxWeight)) $intMaxWeight = $objSearchIndex->getCount();
			if (!isset($intMaxCount)) $intMaxCount = $objSearchIndex->getWord();
			$intRatio = round((100 / ($intMaxWeight * $intMaxCount)) * $objSearchIndex->getCount() * $objSearchIndex->getWord());

			$objSearchResult = new SearchResult();
			$objSearchResult->id = $objSearchIndex->getElementId();
			$objSearchResult->name = $objElement->getName();
			$objSearchResult->value = $objElement->getDescription();
			$objSearchResult->ratio = $intRatio;

			$objReturn->addObject($objSearchResult);
		}

		return $objReturn;
	}

	public function clearIndex() {
		global $_CONF;

		$strSql = sprintf("DELETE FROM pcms_search_index WHERE elementId IN	(SELECT id FROM pcms_element WHERE accountId = '%s')", quote_smart($_CONF['app']['account']->getId()));
		SearchIndex::select($strSql);
	}

	//*** Private Methods.
	private function insertSearchWord($strText, $intId) {
		foreach ($this->getWords($strText, self::SEARCH_WEIGHT) as $strWord => $intWeight) {
			$objSearchIndex = new SearchIndex();
			$objSearchIndex->setElementId($intId);
			$objSearchIndex->setWord($strWord);
			$objSearchIndex->setCount($intWeight);
			$objSearchIndex->save();
		}
	}

	private function deleteSearchIndex($intId) {
		$strSql = sprintf("SELECT * FROM pcms_search_index WHERE elementId = '%s'", quote_smart($intId));
		$objSearchIndexes = SearchIndex::select($strSql);

		foreach ($objSearchIndexes as $objSearchIndex) {
			$objSearchIndex->delete();
		}
	}

	private function removeStopWordsFromArray($arrWords) {
	  $arrStopWords = array(
		'aan', 'af', 'al', 'als', 'bij', 'dan', 'dat', 'die', 'dit',
		'een', 'en', 'er', 'had', 'heb', 'hem', 'het', 'hij', 'hoe',
		'hun', 'ik', 'in', 'is', 'je', 'kan', 'me', 'men', 'met', 'mij',
		'nog', 'nu', 'of', 'ons', 'ook', 'te', 'tot', 'uit', 'van',
		'was', 'wat', 'we', 'wel', 'wij', 'zal', 'ze', 'zei', 'zij',
		'zo', 'zou',
	  );

	  return array_diff($arrWords, $arrStopWords);
	}

	private function stemPhrase($strPhrase) {
		if ($strPhrase == "%") {
			//*** Wildcard only search.
			return array($strPhrase);
		} else {
			//*** Split into words.
			$arrWords = str_word_count(str_replace('-', ' ', strtolower($strPhrase)), 1, "%");

			//*** Ignore stop words.
			$arrWords = $this->removeStopWordsFromArray($arrWords);

			//*** Stem words.
			$arrStemmedWords = array();

			foreach ($arrWords as $strWord) {
				//*** Ignore 1 and 2 letter words.
				if (strlen($strWord) <= 2) {
					continue;
				}
		  	
				//*** Don't stem wildcards.
				if (stripos($strWord, "%") !== FALSE) {
					$arrStemmedWords[] = $strWord;
					continue;
				}

				$arrStemmedWords[] = PorterStemmer::stem($strWord, true);
			}

			return $arrStemmedWords;
		}
	}

	private function getWords($strPhrase, $intWeight) {
	  	$strRaw = str_repeat(' ' . strip_tags($strPhrase), $intWeight);

		//*** Stemming.
		$arrStemmedWords = $this->stemPhrase($strRaw);

		//*** Unique words with weight.
		$arrWords = array_count_values($arrStemmedWords);

		return $arrWords;
	}
}

?>
