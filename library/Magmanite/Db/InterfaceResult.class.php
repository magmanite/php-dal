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
 * DBAL Result interface
 *
 * Provides an interface that a database result concrete class needs to implement.
 *
 * @author Hendri Kurniawan <hendri@kurniawan.id.au>
 */
interface InterfaceResult extends \Iterator {
    /**
     * Get driver class
     *
     * @return \Magmanite\Db\InterfaceDriver
     */
    function getDriver();

    /**
     * Get the number of record returned/affected by the query
     *
     * Please bear in mind that un-buffered query might not return
     * the correct number of records returned by query.
     *
     * @return integer Returns number of records returned/affected by the query
     *
     * @throws \Magmanite\Db\Exception\ResultException
     */
    function getCount();

    /**
     * Get query status
     *
     * @return boolean Returns TRUE if query was successful, FALSE otherwise
     *
     * @throws \Magmanite\Db\Exception\ResultException
     */
    function getStatus();

    /**
     * Get error code returned by failed query execution
     *
     * @return integer Returns error code
     */
    function getErrorCode();

    /**
     * Get error message returned by failed query execution
     *
     * @return string Returns error message
     */
    function getErrorMessage();

    /**
     * Get all records for the result
     *
     * @return array|NULL Returns an array of associative array if records available,
     *  NULL otherwise.
     *
     * @throws \Magmanite\Db\Exception\ResultException
     */
    function getAll();

    /**
     * Check if result is obtained from un-buffered query
     *
     * @return boolean Returns TRUE if this is a result from un-buffered query,
     *  FALSE otherwise.
     *
     * @throws \Magmanite\Db\Exception\ResultException
     */
    function isUnbuffered();
}
