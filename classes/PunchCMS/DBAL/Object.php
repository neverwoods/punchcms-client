<?php

namespace PunchCMS\DBAL;

use PunchCMS\Client\Client;

/**
 * General DBA Object Class
 *
 * Holds the properties and methods of a DBA object.
 *
 * @author Felix Langfeldt <felix@neverwoods.com>
 * @internal
 */
class Object
{
    public static $object = "";
    public static $table = "";
    private static $debug = false;
    protected $sort = 0;
    protected $created = "0000-00-00 00:00:00";
    protected $modified = null;

    public function __get($property)
    {
        $property = strtolower($property);

        if (property_exists($this, $property)) {
            return $this->$property;
        } else {
            if (self::$debug === true) {
                echo "Property Error in " . get_class($this) . "::get({$property}) on line " . __LINE__ . ".\n";
            }
        }
    }

    public function __set($property, $value)
    {
        $property = strtolower($property);

        if (property_exists($this, $property)) {
            $this->$property = $value;
        } else {
            if (self::$debug === true) {
                echo "Property Error in " . get_class($this) . "::set({$property}) on line " . __LINE__ . ".\n";
            }
        }
    }

    public function __call($method, $values)
    {
        /* Handle Method calls to database fields. */

        if (substr($method, 0, 3) == "get") {
            $property = substr($method, 3);
            return $this->$property;
        }

        if (substr($method, 0, 3) == "set") {
            $property = substr($method, 3);
            $this->$property = $values[0];
            return;
        }

        if (self::$debug === true) {
            echo "Method Error in " . get_class($this) . "::{$method} on line " . __LINE__ . ".\n";
        }
    }

    public function save($blnSaveModifiedDate = true)
    {
        /* Save the current object to the database. */
        $DBAConn = Client::getConn();

        //*** Load all properties from the class;
        $objClass = new \ReflectionClass(self::$object);
        $objProperties = $objClass->getProperties();
        if ($blnSaveModifiedDate) {
            $this->modified = null;
        }

        if ($this->id > 0) {
            //*** Build the query for an UPDATE call.
            $strSql = "UPDATE " . self::$table . " SET ";

            for ($i = 0; $i < count($objProperties); $i++) {
                if ($objProperties[$i]->isProtected()) {
                    $strProperty = $objProperties[$i]->name;
                    $strSql .= "`" . $strProperty . "` = ";
                    $strSql .= (is_null($this->$strProperty)) ? "NULL, " : str_replace("%", "%%", self::quote($this->$strProperty)) . ", ";
                }
            }

            $strSql = substr($strSql, 0, strlen($strSql) - 2);
            $strSql .= " WHERE `id` = %s";

            $strSql = sprintf($strSql, self::quote($this->id));
        } else {
            //*** Set the global property "created".
            if (is_object($DBAConn)) {
                $this->created = strftime("%Y-%m-%d %H:%M:%S", strtotime("now"));
            }

            //*** Build the query for an INSERT call.
            $strSql = "INSERT INTO " . self::$table . " (";

            if (is_object($DBAConn)) {
                for ($i = 0; $i < count($objProperties); $i++) {
                    $driverName = $DBAConn->getAttribute(\PDO::ATTR_DRIVER_NAME);
                    if ($driverName == 'mssql' || $driverName == 'sqlsrv' || $driverName == 'dblib') {
                        if ($objProperties[$i]->isProtected() && $objProperties[$i]->name != 'id') {
                            $strSql .= $objProperties[$i]->name . ", ";
                        }
                    } else {
                        if ($objProperties[$i]->isProtected()) {
                            $strSql .= "`" . $objProperties[$i]->name . "`, ";
                        }
                    }
                }
            }

            $strSql = substr($strSql, 0, strlen($strSql) - 2);
            $strSql .= ") VALUES (";

            if (is_object($DBAConn)) {
                for ($i = 0; $i < count($objProperties); $i++) {
                    $driverName = $DBAConn->getAttribute(\PDO::ATTR_DRIVER_NAME);
                    if ($driverName == 'mssql' || $driverName == 'sqlsrv' || $driverName == 'dblib') {
                        if ($objProperties[$i]->isProtected() && $objProperties[$i]->name != 'id') {
                            $strProperty = $objProperties[$i]->name;
                            if ($strProperty !== "id") {
                                if ($this->$strProperty === "newid()") {
                                    $strSql .= "newid()";
                                } else {
                                    $strSql .= (is_null($this->$strProperty)) ? "getdate()" : self::quote($this->$strProperty);
                                }
                                $strSql .= ", ";
                            }
                        }
                    } else {
                        if ($objProperties[$i]->isProtected()) {
                            $strProperty = $objProperties[$i]->name;
                            $strSql .= (is_null($this->$strProperty)) ? "NULL" : self::quote($this->$strProperty) ;
                            $strSql .= ", ";
                        }
                    }
                }
            }

            $strSql = substr($strSql, 0, strlen($strSql) - 2);
            $strSql .= ")";
        }

        if (self::$debug === true) {
            echo self::$object . ".save() : " . $strSql . "<br />";
        }

        if (!is_object($DBAConn)) {
            $arrInfo = $DBAConn->errorInfo();
            throw new \Exception(
                "Connection Error in " . self::$object . "::save on line "
                . __LINE__ .
                ".<br /><b>Error Details</b>: " . $arrInfo[2]
            );
        }

        $objResult = $DBAConn->exec($strSql);

        if ($objResult === false) {
            $arrInfo = $DBAConn->errorInfo();
            throw new \Exception(
                "Database Error in " . self::$object . "::save on line "
                . __LINE__ .
                ".<br /><b>Error Details</b>: " . $arrInfo[2] . "<br />Trying to execute: " .
                $strSql
            );
        }

        $intReturn = $objResult;

        //*** Get the PK from the Database if we just inserted a new record.
        if (!$this->id > 0) {
            $this->id = $DBAConn->lastInsertId();
        }

        return $intReturn;
    }

    public function delete($accountId = null)
    {
        /* Delete the current object from the database. */
        $DBAConn = Client::getConn();

        if ($this->id > 0) {
            $strSql = sprintf("DELETE FROM " . self::$table . " WHERE id = %s", self::quote($this->id));
            if (!is_null($accountId)) {
                $strSql .= sprintf(" AND `accountId` = %s", self::quote($accountId));
            }

            if (self::$debug === true) {
                echo self::$object . ".delete() : " . $strSql . "<br />";
            }

            if (!is_object($DBAConn)) {
                $arrInfo = $DBAConn->errorInfo();
                throw new \Exception(
                    "Connection Error in " . self::$object . "::delete on line "
                    . __LINE__ .
                    ".<br /><b>Error Details</b>: " . $arrInfo[2]
                );
            }

            $objResult = $DBAConn->exec($strSql);

            if ($objResult === false) {
                $arrInfo = $DBAConn->errorInfo();
                throw new \Exception(
                    "Database Error in " . self::$object . "::delete on line "
                    . __LINE__ .
                    ".<br /><b>Error Details</b>: " . $arrInfo[2] . "<br />Trying to execute: " . $strSql
                );
            }

            $intReturn = $objResult;

            return $intReturn;
        }
    }

    public function duplicate()
    {
        /* Duplicate the current object in the database. */
        $DBAConn = Client::getConn();

        if ($this->id > 0) {
            $intId = $this->id;
            $objClass = new \ReflectionClass(self::$object);
            $objProperties = $objClass->getProperties();

            //*** Set the global property "created",
            $this->created = strftime("%Y-%m-%d %H:%M:%S", strtotime("now"));

            //*** Set the "id" and "modified" property to NULL.
            $this->id = null;
            $this->modified = null;

            //*** Build the query for an INSERT call.
            $strSql = "INSERT INTO " . self::$table . " (";

            for ($i = 0; $i < count($objProperties); $i++) {
                if ($objProperties[$i]->isProtected()) {
                    $strSql .= "`" . $objProperties[$i]->name . "`, ";
                }
            }

            $strSql = substr($strSql, 0, strlen($strSql) - 2);
            $strSql .= ") VALUES (";

            for ($i = 0; $i < count($objProperties); $i++) {
                if ($objProperties[$i]->isProtected()) {
                    $strProperty = $objProperties[$i]->name;
                    $strSql .= (is_null($this->$strProperty)) ? "NULL" : self::quote($this->$strProperty);
                    $strSql .= ", ";
                }
            }

            $strSql = substr($strSql, 0, strlen($strSql) - 2);
            $strSql .= ")";

            if (self::$debug === true) {
                echo self::$object . ".duplicate() : " . $strSql . "<br />";
            }

            if (!is_object($DBAConn)) {
                $arrInfo = $DBAConn->errorInfo();
                throw new \Exception(
                    "Connection Error in " . self::$object . "::duplicate on line "
                    . __LINE__ .
                    ".<br /><b>Error Details</b>: " . $arrInfo[2]
                );
            }

            $objResult = $DBAConn->query($strSql);

            if ($objResult === false) {
                $arrInfo = $DBAConn->errorInfo();
                throw new \Exception(
                    "Database Error in " . self::$object . "::duplicate on line "
                    . __LINE__ .
                    ".<br /><b>Error Details</b>: " . $arrInfo[2] . "<br />Trying to execute: " . $strSql
                );
            }

            //*** Get the PK from the Database if we just inserted a new record.
            if (!$this->id > 0) {
                $this->id = $DBAConn->lastInsertId();
            }

            //*** Get an instance of the duplicate object;
            $objMethod = $objClass->getMethod("selectByPK");
            $objReturn = $objMethod->invoke(null, $this->id);

            //*** Reset the "id" property.
            $this->id = $intId;

            return $objReturn;
        }

        return null;
    }

    public static function selectByPK($varValue, $arrFields = array(), $accountId = null)
    {
        /* Get one or multiple records from the database using the
         * primary key and convert them to objects.
         *
         * Method arguments are:
         * - single integer: Returns a single DBA object or NULL.
         * - array with multiple integers: Returns a DBA collection.
         */
        $DBAConn = Client::getConn();

        $varReturn = null;

        //*** Check if specific fields should be selected.
        if (is_array($arrFields) && count($arrFields) > 0) {
            $strSql = "SELECT `" . implode("`, `", $arrFields) . "` ";
        } else {
            $strSql = "SELECT * ";
        }

        if (is_array($varValue)) {
            //*** Select multiple records from the database.
            $strSql .= " FROM " . self::$table . " WHERE id IN ('" . implode("','", $varValue) . "')";
            if (isset($accountId)) {
                $strSql .= sprintf(" AND `accountId` = %s", self::quote($accountId));
            }
            $strSql .= " ORDER BY sort";
        } elseif ($varValue > -1) {
            //*** Select a single record from the database.
            $strSql .= sprintf(" FROM " . self::$table . " WHERE `id` = %s", self::quote($varValue));
            if (isset($accountId)) {
                $strSql .= sprintf(" AND `accountId` = %s", self::quote($accountId));
            }
        } else {
            unset($strSql);
        }

        if (self::$debug === true) {
            echo self::$object . ".selectByPk() : " . $strSql . "<br />";
        }

        if (isset($strSql)) {
            if (!is_object($DBAConn)) {
                $arrInfo = $DBAConn->errorInfo();
                throw new \Exception(
                    "Connection Error in " . self::$object . "::selectByPK on line " .
                    __LINE__ . ".<br /><b>Error Details</b>: " . $arrInfo[2]
                );
            }

            $objResult = $DBAConn->query($strSql);

            if ($objResult === false) {
                $arrInfo = $DBAConn->errorInfo();
                throw new \Exception(
                    "Database Error in " . self::$object . "::selectByPK on line " .
                    __LINE__ . ".<br /><b>Error Details</b>: " . $arrInfo[2] . "<br />Trying to execute: " . $strSql
                );
            }

            if (is_array($varValue)) {
                //*** Multiple records returned. Build Collection.
                $objCollection = new Collection();
                $objClass = new \ReflectionClass(self::$object);

                $objRows = $objResult->fetchAll(\PDO::FETCH_ASSOC);
                foreach ($objRows as $arrRow) {
                    $objRecord = $objClass->newInstance();

                    foreach ($arrRow as $column => $value) {
                        if (is_null($value)) {
                            $value = "";
                        }
                        if (is_callable(array($objRecord, $column))) {
                            $objRecord->$column = $value;
                        }
                    }

                    $objCollection->addObject($objRecord);
                }

                //*** Return a collection object.
                $varReturn = $objCollection;

            } elseif ($objResult->rowCount() > 0) {
                //*** Single record returned. Build object.
                $objClass = new \ReflectionClass(self::$object);

                $objRows = $objResult->fetchAll(\PDO::FETCH_ASSOC);
                foreach ($objRows as $arrRow) {
                    $objRecord = $objClass->newInstance();

                    foreach ($arrRow as $column => $value) {
                        if (is_null($value)) {
                            $value = "";
                        }
                        if (is_callable(array($objRecord, $column))) {
                            $objRecord->$column = $value;
                        }
                    }
                }

                //*** Return a single object.
                $varReturn = $objRecord;
            }
        }

        return $varReturn;
    }

    public static function select($strSql = "")
    {
        /* Selects DB records from the database using a SQL query. If the
         * query is empty all records will be selected.
         *
         * Method arguments are:
         * - SQL query: Returns a DBA collection or NULL.
         */
        $DBAConn = Client::getConn();

        $objReturn = null;

        if (empty($strSql)) {
            //*** Select all records.
            $strSql = "SELECT * FROM " . self::$table . " ORDER BY sort";
        }

        if (self::$debug === true) {
            echo self::$object . ".select() : " . $strSql . "<br />";
        }

        if (!is_object($DBAConn)) {
            $arrInfo = $DBAConn->errorInfo();
            throw new \Exception(
                "Connection Error in " . self::$object . "::select on line "
                . __LINE__ .
                ".<br /><b>Error Details</b>: " . $arrInfo[2]
            );
        }

        if (strtolower(substr($strSql, 0, 6)) == "select") {
            $objResult = $DBAConn->query($strSql);

            $strQueryType = (!empty(self::$object)) ? "pull" : "push";
        } else {
            $objResult = $DBAConn->exec($strSql);
            $strQueryType = "push";
        }

        if ($objResult === false) {
            $arrInfo = $DBAConn->errorInfo();
            throw new \Exception(
                "Database Error in " . self::$object . "::select on line "
                . __LINE__ .
                ".<br /><b>Error Details</b>: " . $arrInfo[2] . "<br />Trying to execute: " . $strSql
            );
        }

        switch ($strQueryType) {
            case "pull":
                //*** Multiple records returned. Build Collection.
                $objCollection = new Collection();
                $objClass = new \ReflectionClass(self::$object);

                if (is_object($objResult)) {
                    $objRows = $objResult->fetchAll(\PDO::FETCH_ASSOC);
                    foreach ($objRows as $arrRow) {
                        $objRecord = $objClass->newInstance();

                        foreach ($arrRow as $column => $value) {
                            if (is_null($value)) {
                                $value = "";
                            }

                            $objRecord->$column = $value;
                        }

                        $objCollection->addObject($objRecord);
                    }
                }

                //*** Return a collection object.
                $objReturn = $objCollection;
                break;
            case "push":
                //*** Just return the object.
                $objReturn = $objResult;
        }

        return $objReturn;
    }

    public static function doDelete($varValue)
    {
        /* Delete a record from the database.
         *
         * Method arguments are:
         * - single integer: Deletes a single record by PK.
         * - single object: Deletes a single record by PK.
         */

        if (is_int($varValue)) {
            //*** Input value is an integer.
            $objClass = new \ReflectionClass(self::$object);
            $objRecord = $objClass->newInstance();
            $objRecord->setId($varValue);
            $intReturn = $objRecord->delete();

        } elseif (is_object($varValue)) {
            //*** Input value is an object.
            $intReturn = $varValue->delete();
        }

        return $intReturn;
    }

    public static function quote($strValue)
    {
        /*
         * Quote a value according to the database rules.
         */
        $DBAConn = Client::getConn();

        //*** Stripslashes.
        if (get_magic_quotes_gpc()) {
            $strValue = (is_string($strValue)) ? stripslashes($strValue) : $strValue;
        }

        //*** Quote if not integer.
        $strValue = (empty($strValue) && !is_numeric($strValue)) ? "''" : self::escape($strValue);

        return $strValue;
    }

    public static function escape($strValue)
    {
        /*
         * Escape a value according to the database rules.
         */
        $DBAConn = Client::getConn();

        return $DBAConn->quote($strValue);
        return $strValue;
    }
}