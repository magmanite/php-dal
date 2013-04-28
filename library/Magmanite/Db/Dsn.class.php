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

use Magmanite\Db\Exception\DsnException;



/**
 * Data Source Name
 *
 * This class has the responsibility of parsing DSN string
 * into separate paths that Db factory and Db driver can understand.
 *
 * @author Hendri Kurniawan <hendri@kurniawan.id.au>
 */
class Dsn implements \Serializable {
    /**
     * DSN string
     * @var string|NULL
     */
    private $_dsn = null;

    /**
     * Database type
     * @var string|NULL
     */
    private $_databaseType = null;

    /**
     * Database server host
     * @var string|NULL
     */
    private $_databaseServerHost = null;

    /**
     * Database server port
     * @var integer|NULL
     */
    private $_databaseServerPort = null;

    /**
     * Username to login to database server
     * @var string|NULL
     */
    private $_databaseUsername = null;

    /**
     * Password to login to database server
     * @var string|NULL
     */
    private $_databasePassword = null;

    /**
     * Database name
     * @var string|NULL
     */
    private $_databaseName = null;

    /**
     * Additional options
     * @var array
     */
    private $_databaseOptions = array();



    /**
     * CONSTRUCTOR
     *
     * @param string $dsn Data Source Name string
     *
     * @throws \Magmanite\Db\Exception\DsnException
     */
    public function __construct($dsn) {
        $this->_initWithDsn($dsn);
    }

    /**
     * MAGIC: To Sring
     *
     * Returns DSN string representing this DSN object
     *
     * @return string Returns DSN string
     */
    public function __toString() {
        return $this->_dsn;
    }



    /**
     * Get database type
     *
     * @return string Returns database type
     */
    public function getDatabaseType() {
        return $this->_databaseType;
    }

    /**
     *  Get database server host
     *
     * @return string Returns server host
     */
    public function getDatabaseServerHost() {
        return $this->_databaseServerHost;
    }

    /**
     * Get database server port number
     *
     * @return integer|NULL Returns server port number if available, NULL otherwise
     */
    public function getDatabaseServerPort() {
        return $this->_databaseServerPort;
    }

    /**
     * Get login username for database server
     *
     * @return string|NULL Returns username if available, NULL otherwise
     */
    public function getUsername() {
        return $this->_databaseUsername;
    }

    /**
     * Get login password for database server
     *
     * @return string|NULL Returns password if available, NULL otherwise
     */
    public function getPassword() {
        return $this->_databasePassword;
    }

    /**
     * Get database name
     *
     * @return string|NULL Returns database name if available, NULL otherwise
     */
    public function getDatabaseName() {
        return $this->_databaseName;
    }

    /**
     * Get other options
     *
     * @return array Returns other options
     */
    public function getOptions() {
        return $this->_databaseOptions;
    }



    /**
     * Update this object with DSN string
     *
     * @param string $dsn DSN string
     *
     * @return \Magmanite\Db\Dsn Returns this instance
     *
     * @throws DsnException
     */
    private function _initWithDsn($dsn) {
        if (!is_string($dsn)) {
            throw new DsnException('DSN must be a string.', DsnException::DNS_MUST_BE_A_STRING);
        }

        $dsn = trim($dsn);
        if ('' === $dsn) {
            throw new DsnException('DSN cannot be empty.', DsnException::DNS_CANNOT_BE_EMPTY);
        }


        // Parse and assign to class variable.
        // Some DSN elements are mandatiory, some optional.
        $parsedDsn = parse_url($dsn);
        if (false === $parsedDsn) {
            throw new DsnException('Cannot parse DSN: ' . $dsn, DsnException::DNS_INVALID);
        }

        if (!array_key_exists('scheme', $parsedDsn)) {
            throw new DsnException("DSN not valid: database type is required", DsnException::DNS_INVALID);
        }
        $this->_databaseType = preg_replace('/[^A-Za-z0-9_]/', '', urldecode($parsedDsn['scheme']));

        if (!array_key_exists('host', $parsedDsn)) {
            throw new DsnException("DSN not valid: host is required", DsnException::DNS_INVALID);
        }
        $this->_databaseServerHost = urldecode($parsedDsn['host']);

        if (!array_key_exists('path', $parsedDsn)) {
            throw new DsnException("DSN not valid: database name is required", DsnException::DNS_INVALID);
        } else {
            $dbName = trim($parsedDsn['path']);
            if ('' !== $dbName) {
                $this->_databaseName = preg_replace('/[^A-Za-z0-9_]/', '', urldecode($dbName));
            }
        }

        if (array_key_exists('port', $parsedDsn) && is_numeric($parsedDsn['port'])) {
            $port = intval($parsedDsn['port']);

            if (0 !== $port) {
                $this->_databaseServerPort = $port;
            }
        }

        if (array_key_exists('user', $parsedDsn)) {
            $username = trim(urldecode($parsedDsn['user']));
            if ('' !== $username) {
                $this->_databaseUsername = $username;
            }
        }

        if (array_key_exists('pass', $parsedDsn)) {
            $password = trim(urldecode($parsedDsn['pass']));
            if ('' !== $password) {
                $this->_databasePassword = $password;
            }
        }

        if (array_key_exists('query', $parsedDsn)) {
            $parsedQuery = null;
            parse_str($parsedDsn['query'], $parsedQuery);
            if (is_array($parsedQuery)) {
                $this->_databaseOptions = $parsedQuery;
            }
        }

        $this->_dsn = $dsn;

        return $this;
    }



// -----------------------------------------------------------------------------
// Implements abstract methods from Serializable class
// -----------------------------------------------------------------------------
	public function serialize () {
	    return $this->_dsn;
	}

	public function unserialize ($serialized) {
	    $this->_initWithDsn($serialized);
	}
// -----------------------------------------------------------------------------
}
