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



/**
 * DBAL Driver Interface
 *
 * Concrete implementation of DBAL driver needs to adhere to this interface.
 *
 * @author Hendri Kurniawan <hendri@kurniawan.id.au>
 */
interface InterfaceDriver {
    /**
     * Query database
     *
     * @param string $sql SQL query to be executed
     *
     * @return \Magmanite\Db\InterfaceResult Returns result object
     *
     * @throws \Magmanite\Db\Exception\DriverException
     */
    function query($sql);

    /**
     * Query database (Unbuffered)
     *
     * This is un-buffered query. It is diffe
     *
     * @param string $sql SQL query to be executed
     *
     * @return \Magmanite\Db\InterfaceResult Returns result object
     *
     * @throws \Magmanite\Db\Exception\DriverException
     */
    function queryUnbuffered($sql);

    /**
     * Escape string value
     *
     * @param string $value String value to be escaped
     *
     * @return string Returns escaped string value
     *
     * @throws \Magmanite\Db\Exception\DriverException
     */
    function escape($value);

    /**
     * Get database handler
     *
     * @return mixed Return database handler
     */
    function getDbh();

   /**
     * Start a transaction
     *
     * This method supports nested transaction. This means you can start multiple
     * transaction and then rollback or commit to the named transaction point
     * that you need.
     *
     * @param string $id Transaction ID
     *
     * @return \Magmanite\Db\InterfaceDriver Returns this instance
     *
     * @throws \Magmanite\Db\Exception\DriverException
     */
    function transactionStart($id);

    /**
     * Commit a transaction
     *
     * This method supports nested transaction. When committing parent transaction,
     * all child transaction that hasn't been rolledback will also get committed.
     *
     * @param string $id ID of the transaction to be committed.
     *
     * @return \Magmanite\Db\InterfaceDriver Returns this instance
     *
     * @throws \Magmanite\Db\Exception\DriverException
     */
    function transactionCommit($id);

    /**
     * Commit a transaction
     *
     * This method supports nested transaction. When rollingback parent transaction,
     * all child transaction will also be rolled back.
     *
     * @param string $id ID of the transaction to be rolled back.
     *
     * @return \Magmanite\Db\InterfaceDriver Returns this instance
     *
     * @throws \Magmanite\Db\Exception\DriverException
     */
    function transactionRollback($id);

    /**
     * List of transaction IDs currently started.
     *
     * @return array<string> Returns a list of transaction IDs
     */
    function transactionList();
}