<?php
namespace Magmanite\Db\Result;

use Magmanite\Db\Exception\ResultException;



abstract class AbstractResult implements \Magmanite\Db\InterfaceResult {
    /**
     * Database connection being used
     * @var \Magmanite\Db\InterfaceDriver
     */
    private $_dbDriver = null;

    /**
     * Flag on whether or not query was successful
     * @var boolean
     */
    private $_status = false;

    /**
     * Error code
     * @var integer|NULL
     */
    private $_errorCode = null;

    /**
     * Error message
     * @var string|NULL
     */
    private $_errorMessage = null;

    /**
     * Un-buffered flag
     * @var boolean
     */
    private $_isUnbuffered = false;



    /**
     * CONSTRUCTOR
     *
     * @param \Magmanite\Db\InterfaceDriver $driver Database driver
     * @param mixed|boolean $result Query result (OPTIONAL, default TRUE)
     * @param boolean $isUnbuffered Set this to TRUE if the query is an un-buffered query (OPTIONAL, default FALSE)
     * @param string|NULL $errorCode Error code (OPTIONAL, default NULL)
     * @param string|NULL $errorMessage Error message (OPTIONAL, default NULL)
     *
     * @throws \Magmanite\Db\Exception\ResultException
     */
    public function __construct(\Magmanite\Db\InterfaceDriver $driver = null, $result = true, $isUnbuffered = false, $errorCode = null, $errorMessage = null) {
        $this->_dbDriver = $driver;
        $this->_status = ($result !== false || is_null($result));
        $this->_errorCode = $errorCode;
        $this->_errorMessage = $errorMessage;
        $this->_isUnbuffered = $isUnbuffered;

        if ($this->_status && !is_bool($result)) {
            $this->_initResult($result);
        }
    }

    /**
     * DESTRUCTOR
     */
    public function __destruct() {
        $this->_cleanupResult();
        $this->_db = null;
        $this->_errorCode = null;
        $this->_errorMessage = null;
    }

    /**
     * MAGIC: Sleep
     * @throws \Magmanite\Db\Exception\ResultException
     */
    public function __sleep() {
        throw new ResultException('This class cannot be serialized', ResultException::CANNOT_BE_SERIALIZED);
    }

    /**
     * MAGIC: Wakeup
     * @throws \Magmanite\Db\Exception\ResultException
     */
    public function __wakeup() {
        throw new ResultException('This class cannot be un-serialized', ResultException::CANNOT_BE_UNSERIALIZED);
    }



// -----------------------------------------------------------------------------
// Implements for InterfaceResult
// -----------------------------------------------------------------------------
    /**
     * (non-PHPdoc)
     * @see \Magmanite\Db\interfaceResult::getDriver()
     */
    public function getDriver() {
        return $this->_dbDriver;
    }

    /**
     * (non-PHPdoc)
     * @see \Magmanite\Db\interfaceResult::getCount()
     */
    public function getCount() {
        return $this->_getCount($this->getDriver());
    }

    /**
     * (non-PHPdoc)
     * @see \Magmanite\Db\interfaceResult::getStatus()
     */
    public function getStatus() {
        return $this->_status;
    }

    /**
     * (non-PHPdoc)
     * @see \Magmanite\Db\interfaceResult::getErrorCode()
     */
    public function getErrorCode() {
        return $this->_errorCode;
    }

    /**
     * (non-PHPdoc)
     * @see \Magmanite\Db\interfaceResult::getErrorMessage()
     */
    public function getErrorMessage() {
        return $this->_errorMessage;
    }

    /**
     * (non-PHPdoc)
     * @see \Magmanite\Db\interfaceResult::getErrorMessage()
     */
    public function getAll() {
        if ($this->getStatus() !== true) {
            return null;
        }

        $rowList = array();
        foreach ($this as $row) {
            $rowList[] = $row;
        }
        return $rowList;
    }

    /**
     * Check if result is obtained from un-buffered query
     *
     * @return boolean Returns TRUE if this is a result from un-buffered query,
     *  FALSE otherwise.
     */
    public function isUnbuffered() {
        return $this->_isUnbuffered;
    }
// -----------------------------------------------------------------------------



// -----------------------------------------------------------------------------
// To be implemented by concrete child classes
// -----------------------------------------------------------------------------
    /**
     * Initialize result
     *
     * @param mixed $result Result
     *
     * @returns integer Returns the number of records returned/affected.
     *
     * @throws \Magmanite\Db\Exception\ResultException
     */
    abstract protected function _initResult($result);

    /**
     * Cleanup result
     *
     * @throws \Magmanite\Db\Exception\ResultException
     */
    abstract protected function _cleanupResult();
// -----------------------------------------------------------------------------
}
