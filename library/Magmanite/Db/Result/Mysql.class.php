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

namespace Magmanite\Db\Result;

use Magmanite\Db\Exception\ResultException;



/**
 * MySql database result
 *
 * @author Hendri Kurniawan <hendri@kurniawan.id.au>
 */
class Mysql extends AbstractResult {
    /**
     * Mysqli result object
     * @var \mysqli_result
     */
    private $_result = null;



// -----------------------------------------------------------------------------
// Override AbstractResult functions
// -----------------------------------------------------------------------------
    /**
     * (non-PHPdoc)
     * @see \Magmanite\Db\Result\AbstractResult::getAll()
     */
    public function getAll() {
        if ($this->getStatus() !== true || is_null($this->_result)) {
            return null;
        }

        // Stop iterator from being used again.
        if ($this->isUnbuffered()) {
            if (0 !== $this->_iteratorCurrentPosition) {
                throw new \Magmanite\Db\Exception\ResultException(
                    'You cannot use un-buffered query more than once.',
                    \Magmanite\Db\Exception\ResultException::UNBUFFERED_QUERY_CANNOT_BE_REWIND);
            }

            $this->_iteratorCurrentPosition = 1;
        }

        return $this->_result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * (non-PHPdoc)
     * @see \Magmanite\Db\Result\AbstractResult::getCount()
     */
    public function getCount() {
        if (!is_null($this->_result)) {
            return $this->_result->num_rows;
        }

        return $this->getDriver()->getDbh()->affected_rows;
    }
// -----------------------------------------------------------------------------



// -----------------------------------------------------------------------------
// Implements for \Iterator interface
// -----------------------------------------------------------------------------
    /**
     * Current iterator record
     * @var array|NULL
     */
    private $_iteratorCurrentRecord = null;

    /**
     * Current iterator record position
     * @var integer
     */
    private $_iteratorCurrentRecordPos = -1;

    /**
     * Current iterator position
     * @var integer
     */
    private $_iteratorCurrentPosition = 0;



    /**
     * (non-PHPdoc)
     * @see \Iterator::current()
     */
    public function current () {
        return $this->_getRecordForIterator();
    }

    /**
     * (non-PHPdoc)
     * @see \Iterator::next()
     */
    public function next () {
        ++$this->_iteratorCurrentPosition;
    }

    /**
     * (non-PHPdoc)
     * @see \Iterator::key()
     */
    public function key () {
        return $this->_iteratorCurrentPosition;
    }

    /**
     * (non-PHPdoc)
     * @see \Iterator::valid()
     */
    public function valid () {
        return (!is_null($this->_getRecordForIterator()));
    }

    /**
     * (non-PHPdoc)
     * @see \Iterator::rewind()
     */
    public function rewind () {
        if (is_null($this->_result)) {
            return;
        }

        if (0 !== $this->_iteratorCurrentPosition) {
            if ($this->isUnbuffered()) {
                throw new \Magmanite\Db\Exception\ResultException(
                    'Un-buffered query cannot be re-wound back and can only be iterated once.',
                    \Magmanite\Db\Exception\ResultException::UNBUFFERED_QUERY_CANNOT_BE_REWIND);
            }

            $this->_result->data_seek(0);
            $this->_iteratorCurrentPosition = 0;
            $this->_iteratorCurrentRecord = false;
            $this->_iteratorCurrentRecordPos = -1;
        }
    }



    /**
     * Get record for current iterator position
     *
     * @return array|NULL Returns row if result still have rows, NULL otherwise
     */
    private function _getRecordForIterator() {
        if (is_null($this->_result)) {
            return null;
        }

        if ($this->_iteratorCurrentPosition != $this->_iteratorCurrentRecordPos) {
            $this->_iteratorCurrentRecord = $this->_result->fetch_assoc();
            $this->_iteratorCurrentRecordPos = $this->_iteratorCurrentPosition;
        }

        return $this->_iteratorCurrentRecord;
    }
// -----------------------------------------------------------------------------



// -----------------------------------------------------------------------------
// Implement abstract functions from AbstractResult
// -----------------------------------------------------------------------------
    /**
     * (non-PHPdoc)
     * @see \Magmanite\Db\Result\AbstractResult::_initResult()
     */
    protected function _initResult($result) {
        if (!($result instanceof \mysqli_result)) {
            throw new ResultException('Invalid result: Expecting \mysqli_result object.',
                ResultException::INVALID_RESULT);
        }

        $this->_result = $result;

        return $this->_result->num_rows;
    }

    /**
     * (non-PHPdoc)
     * @see \Magmanite\Db\Result\AbstractResult::_cleanupResult()
     */
    protected function _cleanupResult() {
        if (!is_null($this->_result)) {
            $this->_result->free();
            $this->_result = null;
        }
    }
// -----------------------------------------------------------------------------
}
