<?php
/**
 * Simple PHP Database Abstraction Layer
 *
 * Copyright 2013 Magmanite Pty Ltd
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @link http://www.magmanite.com
 * @copyright Copyright (c) 2013 Magmanite (http://www.magmanite.com)
 * @license Apache 2.0
 */

namespace Magmanite\Db;

use \Magmanite\Db\Exception\DriverException;



/**
 * Abstract DBAL driver
 *
 * This class is the base class for all drivers.
 * All DBAL concrete class needs to extends this class.
 *
 * @author Hendri Kurniawan <hendri@kurniawan.id.au>
 */
abstract class AbstractDriver {
    /**
     * CONSTRUCTOR
     *
     * Upon object construction, object will immediately
     * connect to the database server.
     *
     * @param \Magmanite\Db\Dsn $dsn Parsed database source name
     *
     * @throws \Magmanite\Db\Excception\DriverException
     */
    public function __construct(\Magmanite\Db\Dsn $dsn) {
        $this->_setupDbh($dsn);
    }

    /**
     * DESTRUCTOR
     *
     * When destroyed, the object will attempt to disconnect from
     * database server.
     *
     * @throws \Magmanite\Db\Excception\DriverException
     */
    public function __destruct() {
        $this->_cleanupDbh();
    }

    /**
     * MAGIC: SLEEP
     *
     * Driver DBAL should not be serializable. Although it is not recommended,
     * each concrete implementation is allowed to override this behaviour.
     *
     * @throws \Magmanite\Db\Excception\DriverException
     */
    public function __sleep() {
        throw new DriverException('Cannot serialize DBAL driver object.', DriverException::CANNOT_BE_SERIALIZED);
    }

    /**
     * MAGIC: WAKEUP
     *
     * Driver DBAL should not be unserialized. Although it is not recommended,
     * each concrete implementation is allowed to override this behaviour.
     *
     * @throws \Magmanite\Db\Excception\DriverException
     */
    public function __wakeup() {
        throw new DriverException('Cannot unserialize DBAL driver object.', DriverException::CANNOT_BE_UNSERIALIZED);
    }

    /**
     * MAGIC: CLONE
     *
     * Driver DBAL should not be cloned. Although it is not recommended,
     * each concrete implementation is allowed to override this behaviour.
     *
     * @throws \Magmanite\Db\Excception\DriverException
     */
    public function __clone() {
        throw new DriverException('Cannot clone DBAL driver object.', DriverException::CANNOT_BE_CLONED);
    }



    /**
     * Query database
     *
     * @param string $sql SQL query to be executed
     *
     * @return \Magmanite\Db\InterfaceResult Returns result object
     *
     * @throws \Magmanite\Db\Excception\DriverException
     */
    public function query($sql) {
        // TODO Version 2 improvements: hooks

        if (!is_string($sql)) {
            throw new DriverException('SQL must be a string', DriverException::INVALID_SQL);
        }

        $result = $this->_query($sql);
        if (!($result instanceof \Magmanite\Db\InterfaceResult)) {
            throw new DriverException('Invalid result. Expecting InterfaceResult', DriverException::INVALID_RESULT);
        }

        return $result;
    }

    /**
     * Escape string value
     *
     * @param string $value String value to be escaped
     *
     * @return string Returns escaped string value
     *
     * @throws \Magmanite\Db\Excception\DriverException
     */
    public function escape($value) {
        // TODO Version 2 improvements: hooks
        return $this->_escape(strval($value));
    }



// -----------------------------------------------------------------------------
// Variables and methods used for database connection handler
// -----------------------------------------------------------------------------
    /**
     * Database connection handler
     *
     * @var mixed
     */
    private $_dbh = null;



    /**
     * Get database handler
     *
     * @return mixed Return database handler
     */
    public function getDbh() {
        // TODO Version 2 improvements: hooks
        return $this->_dbh;
    }

    /**
     * Setup Database Handler
     *
     * @param \Magmanite\Db\Dsn $dsn Data Source Name
     *
     * @throws DriverException
     */
    protected function _setupDbh(\Magmanite\Db\Dsn $dsn) {
        // TODO Version 2 improvements: hooks

        $dbh = $this->_connect($dsn);
        if (empty($dbh)) {
            throw new DriverException('Cannot connect to database server.', DriverException::CANNOT_CONNECT);
        }

        $this->_dbh = $dbh;
    }

    /**
     * Cleanup database handler
     *
     * @throws DriverException
     */
    protected function _cleanupDbh() {
        // TODO Version 2 improvements: hooks

        $this->_disconnect($this->_dbh);
        $this->_dbh = null;
    }
// -----------------------------------------------------------------------------



// -----------------------------------------------------------------------------
// Variables and methods used for transaction
// -----------------------------------------------------------------------------
    private $_transactionList = array();



    public function transactionStart($id) {
        $id = $this->_transactionProcessId($id);


        if (array_key_exists($id, $this->_transactionList)) {
            throw new DriverException(
                "Transaction with id '{$id}' has already been started",
                DriverException::TRANSACTION_WITH_ID_HAS_BEEN_STARTED);
        }


        if (count($this->_transactionList) === 0) {
            $result = $this->_query('START TRANSACTION');
            if (!$result->getStatus()) {
                throw new DriverException('Cannot start transaction: '
                    . $result->getErrorMessage() . ' (' . $result->getErrorCode() . ')',
                    DriverException::CANNOT_START_TRANSACTION);
            }
        }


        $result = $this->_query("SAVEPOINT {$id}");
        if (!$result->getStatus()) {
            throw new DriverException('Cannot create savepoint: '
                . $result->getErrorMessage() . ' (' . $result->getErrorCode() . ')',
                DriverException::CANNOT_START_TRANSACTION);
        }


        return $this;
    }

    public function transactionCommit($id) {
        $newTransactionList = $this->_transactionGetStackUntil($id);

        if (count($newTransactionList) === 0) {
            $result = $this->_query('COMMIT');
            if (!$result->getStatus()) {
                throw new DriverException('Cannot commit transaction: '
                    . $result->getErrorMessage() . ' (' . $result->getErrorCode() . ')',
                    DriverException::CANNOT_COMMIT_TRANSACTION);
            }
        }

        $this->_transactionList = $newTransactionList;

        return $this;
    }

    public function transactionRollback($id) {
        $newTransactionList = $this->_transactionGetStackUntil($id);


        // Rollback the whole transaction if there aren't any more
        // "transactions" in the stack.
        if (count($newTransactionList) === 0) {
            $result = $this->_query('ROLLBACK');
            if (!$result->getStatus()) {
                throw new DriverException('Cannot rollback transaction: '
                    . $result->getErrorMessage() . ' (' . $result->getErrorCode() . ')',
                    DriverException::CANNOT_ROLLBACK_TRANSACTION);
            }


        // If we still have "transactions" in the stack,
        // only rollback to specified save point.
        } else {
            $result = $this->_query('ROLLBACK TO SAVEPOINT ' . $id);
            if (!$result->getStatus()) {
                throw new DriverException('Cannot rollback transaction: '
                    . $result->getErrorMessage() . ' (' . $result->getErrorCode() . ')',
                    DriverException::CANNOT_ROLLBACK_TRANSACTION);
            }
        }


        $this->_transactionList = $newTransactionList;
        return $this;
    }

    public function transactionList() {
        return $this->_transactionList;
    }



    protected function _transactionProcessId($id) {
        if (!is_string($id)) {
            throw new DriverException('Transaction ID must be a string.',
                DriverException::INVALID_TRANSACTION_ID);
        }

        return preg_replace('[^A-Za-z0-9_]', '_', $id);
    }

    protected function _transactionGetStackUntil($id) {
        $index = array_search($id, $this->_transactionList);

        // If ID can't be found, throw exception
        if (false === $index) {
            throw new DriverException(
                "Transaction with id '{$id}' does not exists.",
                DriverException::TRANSACTION_WITH_ID_DOES_NOT_EXISTS);
        }

        // If ID is the first in the stack, simply return an empty string
        if (0 === $index) {
            return array();
        }

        return array_slice($this->_transactionList, 0, $index);
    }
// -----------------------------------------------------------------------------



// -----------------------------------------------------------------------------
// Methods to be implemented by children classes
// -----------------------------------------------------------------------------
    /**
     * Connect to the database
     *
     * @param \Magmanite\Db\Dsn $dsn Parsed data source name
     *
     * @return mixed Returns database connection object/resource
     *
     * @throws \Magmanite\Db\DriverException
     */
    abstract protected function _connect(\Magmanite\Db\Dsn $dsn);

    /**
     * Disconnect from database
     *
     * @param mixed $dbh Database connection object/resource to be disconnected
     *
     * @return boolean Returns TRUE if successful, FALSE otherwise
     *
     * @throws \Magmanite\Db\Excception\DriverException
     */
    abstract protected function _disconnect($dbh);

    /**
     * Query database
     *
     * @param string $sql SQL query to be executed
     *
     * @return \Magmanite\Db\InterfaceResult Returns result object
     *
     * @throws \Magmanite\Db\Excception\DriverException
     */
    abstract protected function _query($sql);

    /**
     * Escape string value
     *
     * @param string $value String value to be escaped
     *
     * @return string Returns escaped string value
     *
     * @throws \Magmanite\Db\Excception\DriverException
     */
    abstract protected function _escape($value);
// -----------------------------------------------------------------------------
}
