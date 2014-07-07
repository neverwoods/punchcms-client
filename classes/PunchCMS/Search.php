<?php

namespace PunchCMS;

use PunchCMS\DBAL\Collection;

/**
 *
 * Searches for strings in the elements.
 * @author felix
 * @version 0.2.1
 *
 * CHANGELOG
 * version 0.2.1, 30 May 2008
 *   CHG: Refined the wildcard seach.
 * version 0.2.0, 18 Apr 2008
 *   CHG: Added wildcard seach to the find method.
 * version 0.1.0, 04 Apr 2006
 *   NEW: Created class.
 *
 */
class Search extends \PunchCMS\DBAL\Object
{
    const SEARCH_WEIGHT = 1;
    const WORD_COUNT_MASK = "/\p{L}[\p{L}\p{Mn}\p{Pd}'\x{2019}]*|\%/u";

    //*** Public Methods.
    public function updateIndex($intId = 0)
    {
        if ($intId > 0) {
            $objElement = Element::selectByPk($intId);
            $objElements = new Collection();
            $objElements->addObject($objElement);
        } else {
            $objElements = Element::select();
        }

        foreach ($objElements as $objElement) {
            $searchIndexes = array();
            $now = date('Y-m-d H:i:s');

            //*** Delete current index.
            $this->deleteSearchIndex($objElement->getId());

            //*** Get index words from the elements text field.
            $strSql = sprintf(
                "SELECT pcms_element_field_text.value as value " .
                "FROM pcms_element_field_text, pcms_element_field " .
                "WHERE pcms_element_field_text.fieldId = pcms_element_field.id " .
                "AND pcms_element_field.elementId = %s",
                self::quote($objElement->getId())
            );

            $objElementFields = ElementFieldText::select($strSql);

            foreach ($objElementFields as $objElementField) {
                foreach ($this->getWords($objElementField->getValue(), self::SEARCH_WEIGHT) as $strWord => $intWeight) {
                    $searchIndexes[] = sprintf(
                        "('%s', '%s', '%s', '%s', '%s', '%s')",
                        self::quote($objElement->getId()),
                        self::quote($strWord),
                        self::quote($intWeight),
                        0,
                        $now,
                        $now
                    );
                }
            }

            //*** Get index words from the elements bigtext field.
            $strSql = sprintf(
                "SELECT pcms_element_field_bigtext.value as value
                FROM pcms_element_field_bigtext, pcms_element_field
                WHERE pcms_element_field_bigtext.fieldId = pcms_element_field.id
                AND pcms_element_field.elementId = %s",
                self::quote($objElement->getId())
            );

            $objElementFields = ElementFieldBigText::select($strSql);

            foreach ($objElementFields as $objElementField) {
                foreach ($this->getWords($objElementField->getValue(), self::SEARCH_WEIGHT) as $strWord => $intWeight) {
                    $searchIndexes[] = sprintf(
                        "('%s', '%s', '%s', '%s', '%s', '%s')",
                        self::quote($objElement->getId()),
                        self::quote($strWord),
                        self::quote($intWeight),
                        0,
                        $now,
                        $now
                    );
                }
            }

            if (count($searchIndexes) > 0) {
                $strSql = 'INSERT INTO pcms_search_index (elementId, word, count, sort, created, modified) VALUES '. implode(',', $searchIndexes);
                SearchIndex::select($strSql);
            }
        }
    }

    public function find($strQuery, $blnExact = false)
    {
        global $_CONF;

        //*** Set query property.
        $objReturn        = new SearchResults();
        $objReturn->setQuery($strQuery);
        $strQuery        = str_replace("*", "%", $strQuery);

        //*** Convert query to stem.
        $arrWords        = array_values($this->stemPhrase($strQuery));
        $intWordCount    = count($arrWords);

        //*** Query does not validate.
        if (!$arrWords) {
            return $objReturn;
        }

        //*** Set query property.
        $objReturn->setQuery($strQuery);

        $strSql = sprintf(
            "SELECT DISTINCT pcms_search_index.elementId, COUNT(pcms_search_index.id) as word,
            SUM(pcms_search_index.count) as count FROM pcms_search_index, pcms_element WHERE
            pcms_search_index.elementId = pcms_element.id AND
            pcms_element.accountId = %s AND ",
            self::quote($_CONF['app']['account']->getId())
        );
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
            $equal = (stripos($arrWords[$i], "%") !== false) ? "LIKE" : "=";
            $strTempSql .= $arrSql[$i] . "word {$equal} '" . addslashes($arrWords[$i]) . "'";
        }
        $strTempSql .= $arrSql[$i];

        //*** Query the database.
        $objSearchIndexes = SearchIndex::select($strTempSql);

        foreach ($objSearchIndexes as $objSearchIndex) {
            $objElement = Element::selectByPk($objSearchIndex->getElementId());

            if (!isset($intMaxWeight)) {
                $intMaxWeight = $objSearchIndex->getCount();
            }
            if (!isset($intMaxCount)) {
                $intMaxCount = $objSearchIndex->getWord();
            }
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

    public function clearIndex()
    {
        global $_CONF;

        $strSql = sprintf(
            "DELETE FROM pcms_search_index WHERE elementId IN    (SELECT id FROM pcms_element WHERE accountId = %s)",
            self::quote($_CONF['app']['account']->getId())
        );
        SearchIndex::select($strSql);
    }

    //*** Private Methods.
    private function insertSearchWord($strText, $intId)
    {
        foreach ($this->getWords($strText, self::SEARCH_WEIGHT) as $strWord => $intWeight) {
            $objSearchIndex = new SearchIndex();
            $objSearchIndex->setElementId($intId);
            $objSearchIndex->setWord($strWord);
            $objSearchIndex->setCount($intWeight);
            $objSearchIndex->save();
        }
    }

    private function deleteSearchIndex($intId)
    {
        $strSql = sprintf("DELETE FROM pcms_search_index WHERE elementId = '%s'", self::quote($intId));
        SearchIndex::select($strSql);
    }

    private function removeStopWordsFromArray($arrWords)
    {
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

    private function stemPhrase($strPhrase)
    {
        if ($strPhrase == "%") {
            //*** Wildcard only search.
            return array($strPhrase);
        } else {
            //*** Split into words.
            $arrWords = str_word_count(str_replace('-', ' ', mb_strtolower($strPhrase)), 1, "%");

            //*** Ignore stop words.
            $arrWords = $this->removeStopWordsFromArray($arrWords);

            //*** Stem words.
            $arrStemmedWords = array();

            foreach ($arrWords as $strWord) {
                //*** Ignore 1 and 2 letter words.
                if (mb_strlen($strWord) <= 2) {
                    continue;
                }

                //*** Don't stem wildcards.
                if (stripos($strWord, "%") !== false) {
                    $arrStemmedWords[] = $strWord;
                    continue;
                }

                $arrStemmedWords[] = \Porter::Stem($strWord, true);
            }

            return $arrStemmedWords;
        }
    }

    private function getWords($strPhrase, $intWeight)
    {
        $strRaw = str_replace("><", "> <", $strPhrase);
        $strRaw = str_repeat(' ' . strip_tags($strPhrase), $intWeight);

        //*** Stemming.
        $arrStemmedWords = $this->stemPhrase($strRaw);

        //*** Unique words with weight.
        $arrWords = array_count_values($arrStemmedWords);

        return $arrWords;
    }
}