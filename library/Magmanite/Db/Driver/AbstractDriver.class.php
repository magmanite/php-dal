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

namespace Magmanite\Db\Driver;

use \Magmanite\Db\Exception\DriverException;
use \Magmanite\Db\InterfaceDriver;



/**
 * Abstract DBAL driver.
 *
 * This is a base DBAL driver that you can choose to extends rather than
 * implementing InterfaceDriver from scratch.
 *
 * You will still need to implement a few methods marked as abstract
 * in this class.
 *
 * @author Hendri Kurniawan <hendri@kurniawan.id.au>
 */
abstract class AbstractDriver implements \Magmanite\Db\InterfaceDriver {
    /**
     * Database connection handler
     *
     * @var mixed
     */
    private $_dbh = null;

    /**
     * List of transactions
     * @var array<string>
     */
    private $_transactionList = array();



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
        $this->_dbhInit($dsn);
        $this->_transactionInit();
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
        $this->_transactionCleanup();
        $this->_dbhCleanup();
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



// -----------------------------------------------------------------------------
// Implements for InterfaceDriver
// -----------------------------------------------------------------------------
    /**
     * (non-PHPdoc)
     * @see \Magmanite\Db\InterfaceDriver::query()
     */
    public function query($sql) {
        // TODO Version 2 improvements: hooks

        if (!is_string($sql)) {
            throw new DriverException('SQL must be a string', DriverException::INVALID_SQL);
        }

        $result = $this->_query($this->getDbh(), $sql);
        if (!($result instanceof \Magmanite\Db\InterfaceResult)) {
            throw new DriverException('Invalid result. Expecting InterfaceResult', DriverException::INVALID_RESULT);
        }

        return $result;
    }

    /**
     * (non-PHPdoc)
     * @see \Magmanite\Db\InterfaceDriver::queryUnbuffered()
     */
    public function queryUnbuffered($sql) {
        // TODO Version 2 improvements: hooks

        if (!is_string($sql)) {
            throw new DriverException('SQL must be a string', DriverException::INVALID_SQL);
        }

        $result = $this->_queryUnbuffered($this->getDbh(), $sql);
        if (!($result instanceof \Magmanite\Db\InterfaceResult)) {
            throw new DriverException('Invalid result. Expecting InterfaceResult', DriverException::INVALID_RESULT);
        }

        return $result;
    }

    /**
     * (non-PHPdoc)
     * @see \Magmanite\Db\InterfaceDriver::escape()
     */
    public function escape($value) {
        // TODO Version 2 improvements: hooks
        return $this->_escape($this->getDbh(), strval($value));
    }

    /**
     * (non-PHPdoc)
     * @see \Magmanite\Db\InterfaceDriver::getDbh()
     */
    public function getDbh() {
        // TODO Version 2 improvements: hooks
        return $this->_dbh;
    }

    /**
     * (non-PHPdoc)
     * @see \Magmanite\Db\InterfaceDriver::transactionStart()
     */
    public function transactionStart($id) {
        if (!$this->_transactionValidateId($id)) {
            throw new DriverException('Transaction ID is not valid. Transaction ID must be an alpha-numeric string.',
                DriverException::INVALID_TRANSACTION_ID);
        }


        if (array_key_exists($id, $this->_transactionList)) {
            throw new DriverException(
                "Transaction with id '{$id}' has already been started",
                DriverException::TRANSACTION_WITH_ID_HAS_BEEN_STARTED);
        }


        if (count($this->_transactionList) === 0) {
            $result = $this->_query($this->getDbh(), 'START TRANSACTION');
            if (!$result->getStatus()) {
                throw new DriverException('Cannot start transaction: '
                    . $result->getErrorMessage() . ' (' . $result->getErrorCode() . ')',
                    DriverException::CANNOT_START_TRANSACTION);
            }
        }


        $result = $this->_query($this->getDbh(), "SAVEPOINT {$id}");
        if (!$result->getStatus()) {
            throw new DriverException('Cannot create savepoint: '
                . $result->getErrorMessage() . ' (' . $result->getErrorCode() . ')',
                DriverException::CANNOT_START_TRANSACTION);
        }


        // Add transaction ID to stack
        array_push($this->_transactionList, $id);


        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \Magmanite\Db\InterfaceDriver::transactionCommit()
     */
    public function transactionCommit($id) {
        $newTransactionList = $this->_transactionGetStackUntil($id);

        if (count($newTransactionList) === 0) {
            $result = $this->_query($this->getDbh(), 'COMMIT');
            if (!$result->getStatus()) {
                throw new DriverException('Cannot commit transaction: '
                    . $result->getErrorMessage() . ' (' . $result->getErrorCode() . ')',
                    DriverException::CANNOT_COMMIT_TRANSACTION);
            }
        }

        $this->_transactionList = $newTransactionList;

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \Magmanite\Db\InterfaceDriver::transactionRollback()
     */
    public function transactionRollback($id) {
        $newTransactionList = $this->_transactionGetStackUntil($id);


        // Rollback the whole transaction if there aren't any more
        // "transactions" in the stack.
        if (count($newTransactionList) === 0) {
            $result = $this->_query($this->getDbh(), 'ROLLBACK');
            if (!$result->getStatus()) {
                throw new DriverException('Cannot rollback transaction: '
                    . $result->getErrorMessage() . ' (' . $result->getErrorCode() . ')',
                    DriverException::CANNOT_ROLLBACK_TRANSACTION);
            }


        // If we still have "transactions" in the stack,
        // only rollback to specified save point.
        } else {
            $result = $this->_query($this->getDbh(), 'ROLLBACK TO SAVEPOINT ' . $id);
            if (!$result->getStatus()) {
                throw new DriverException('Cannot rollback transaction: '
                    . $result->getErrorMessage() . ' (' . $result->getErrorCode() . ')',
                    DriverException::CANNOT_ROLLBACK_TRANSACTION);
            }
        }


        $this->_transactionList = $newTransactionList;
        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \Magmanite\Db\InterfaceDriver::transactionList()
     */
    public function transactionList() {
        return $this->_transactionList;
    }
// -----------------------------------------------------------------------------



// -----------------------------------------------------------------------------
// Protected functions
// -----------------------------------------------------------------------------
    /**
     * Setup Database Handler
     *
     * @param \Magmanite\Db\Dsn $dsn Data Source Name
     *
     * @throws \Magmanite\Db\Excception\DriverException
     */
    protected function _dbhInit(\Magmanite\Db\Dsn $dsn) {
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
     * @throws \Magmanite\Db\Excception\DriverException
     */
    protected function _dbhCleanup() {
        // TODO Version 2 improvements: hooks

        $this->_disconnect($this->_dbh);
        $this->_dbh = null;
    }

    /**
     * Validate transaction ID
     *
     * At the moment, this method is validating if transaction ID is correct or not
     *
     * @param string $id Transaction ID to be processed
     *
     * @return boolean Returns TRUE if ID is correct FALSE otherwise
     *
     * @todo testing
     */
    protected function _transactionValidateId($id) {
        if (!is_string($id)) {
            return false;
        }

        if (preg_match('/[^A-Za-z0-9_]/', $id)) {
            return false;
        }

        return true;
    }

    /**
     * Get transaction stack until specified ID
     *
     * @param string $id Transaction ID to be searched
     *
     * @return array<string> Returns transaction array stack
     *
     * @throws \Magmanite\Db\Excception\DriverException
     */
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

    /**
     * Initialize transaction
     *
     * This method is called in constructor.
     * At the moment it doesn't do anything except initializing transaction stack array.
     */
    protected function _transactionInit() {
        $this->_transactionList = array();
    }

    /**
     * Transaction cleanup.
     *
     * This method is called in destructor.
     * At the moment it doesn't do anything except cleaning up transaction stack array.
     */
    protected function _transactionCleanup() {
        $this->_transactionList = null;
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
     * @param mixed $dbh Database connection to use
     * @param string $sql SQL query to be executed
     *
     * @return \Magmanite\Db\InterfaceResult Returns result object
     *
     * @throws \Magmanite\Db\Excception\DriverException
     */
    abstract protected function _query($dbh, $sql);

    /**
     * Query database (unbuffered)
     *
     * @param mixed $dbh Database connection to use
     * @param string $sql SQL query to be executed
     *
     * @return \Magmanite\Db\InterfaceResult Returns result object
     *
     * @throws \Magmanite\Db\Excception\DriverException
     */
    abstract protected function _queryUnbuffered($dbh, $sql);

    /**
     * Escape string value
     *
     * @param mixed $dbh Database connection to use
     * @param string $value String value to be escaped
     *
     * @return string Returns escaped string value
     *
     * @throws \Magmanite\Db\Excception\DriverException
     */
    abstract protected function _escape($dbh, $value);
// -----------------------------------------------------------------------------
}
