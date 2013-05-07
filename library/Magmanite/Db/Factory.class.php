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

use \Magmanite\Db\Exception\FactoryException;



/**
 * Database Abstraction Layer Factory class.
 *
 * @author Hendri Kurniawan <hendri@kurniawan.id.au>
 */
abstract class Factory {
    /**
     * Driver class namespace
     */
    const DRIVER_CLASS_NAMESPACE = '\\Magmanite\\Db\\Driver\\';



    /**
     * PRIVATE CONSTRUCTOR
     */
    final private function __construct() { }




    /**
     * Get a new concrete instance of Database Abstraction Layer
     *
     * @param \Magmanite\Db\Dsn $dsn Database Source Name
     *
     * @return \Magmanite\Db\InterfaceDriver Returns a concrete DAL instance
     *
     * @throws \Magmanite\Db\Exception\FactoryException
     */
    static public function getInstance(Dsn $dsn) {
        $className = self::DRIVER_CLASS_NAMESPACE . ucfirst($dsn->getDatabaseType());
        if (!class_exists($className)) {
            throw new FactoryException('DBAL Driver class not found: ' . $className, FactoryException::DRIVER_NOT_FOUND);
        }
        return new $className($dsn);
    }
}
