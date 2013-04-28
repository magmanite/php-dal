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

namespace Magmanite\Db\Exception;



/**
 * Data Source Name Exception
 *
 * This exception is thrown by Dsn class
 *
 * @author Hendri Kurniawan <hendri@kurniawan.id.au>
 */
class DsnException extends Exception {
    const DNS_CANNOT_BE_EMPTY                = 1;
    const DNS_MUST_BE_A_STRING               = 2;
    const DNS_INVALID                        = 3;
}