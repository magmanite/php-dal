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
 * Data Source Name PHPUnit test class
 *
 * @author Hendri Kurniawan <hendri@kurniawan.id.au>
 */
class DsnTest extends \PHPUnit_Framework_TestCase {
    public function testConstructor() {
        $databaseType = 'oracle';
        $username = 'someUser';
        $password = '%h3ll01@mher3&';
        $host = 'somedbserver1';
        $port = mt_rand(1, 9999);
        $dbName = 'testdb1';
        $options = array(
            'hello' => 'world',
            'one' => "1",
            'isTrue' => 'true?',
            'extras' => array('e1', 'e2', 'e3'),
        );

        $dsnString = $databaseType . '://';
        $dsnString .= urlencode($username);
        $dsnString .= ':' . urlencode($password);
        $dsnString .= "@{$host}:{$port}";
        $dsnString .= '/' . urlencode($dbName);
        $dsnString .= '?' . http_build_query($options);

        $dsn = new Dsn($dsnString);
        $this->assertEquals($databaseType, $dsn->getDatabaseType());
        $this->assertEquals($username, $dsn->getUsername());
        $this->assertEquals($password, $dsn->getPassword());
        $this->assertEquals($host, $dsn->getDatabaseServerHost());
        $this->assertEquals($port, $dsn->getDatabaseServerPort());
        $this->assertEquals(serialize($options), serialize($dsn->getOptions()));

        $this->assertEquals($dsnString, strval($dsn));
    }

    public function testSerializeUnserialize() {
        $databaseType = 'oracle';
        $username = 'someUser';
        $password = '%h3ll01@mher3&';
        $host = 'somedbserver1';
        $port = mt_rand(1, 9999);
        $dbName = 'testdb1';
        $options = array(
            'hello' => 'world',
            'one' => "1",
            'isTrue' => 'true?',
            'extras' => array('e1', 'e2', 'e3'),
        );

        $dsnString = $databaseType . '://';
        $dsnString .= urlencode($username);
        $dsnString .= ':' . urlencode($password);
        $dsnString .= "@{$host}:{$port}";
        $dsnString .= '/' . urlencode($dbName);
        $dsnString .= '?' . http_build_query($options);


        $dsn1 = new Dsn($dsnString);
        $serializeDsn = serialize($dsn1);
        $dsn2 = unserialize($serializeDsn);

        $this->assertEquals($dsn1, $dsn2);
    }
}
