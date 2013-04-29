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
 * DriverException
 *
 * This exception is thrown by DBAL driver object
 *
 * @author Hendri Kurniawan <hendri@kurniawan.id.au>
 */
class DriverException extends Exception {
    const DRIVER_IMPLEMENTATION_ERROR           = 1;
    const CANNOT_BE_SERIALIZED                  = 2;
    const CANNOT_BE_UNSERIALIZED                = 3;
    const CANNOT_CONNECT                        = 4;
    const CANNOT_BE_CLONED                      = 5;
    const INVALID_SQL                           = 6;
    const INVALID_TRANSACTION_ID                = 7;
    const CANNOT_START_TRANSACTION              = 8;
    const TRANSACTION_WITH_ID_HAS_BEEN_STARTED  = 9;
    const TRANSACTION_WITH_ID_DOES_NOT_EXISTS   = 10;
    const CANNOT_COMMIT_TRANSACTION             = 11;
    const CANNOT_ROLLBACK_TRANSACTION           = 12;
}
