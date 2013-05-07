<?php
namespace Magmanite\Db\Driver;

use \Magmanite\Db\Exception\DriverException;



class Mysql extends \Magmanite\Db\Driver\AbstractDriver {
// -----------------------------------------------------------------------------
// Implements abstract methods from AbstractDriver class
// -----------------------------------------------------------------------------
    /**
     * (non-PHPdoc)
     * @see \Magmanite\Db\Driver\AbstractDriver::_connect()
     */
    protected function _connect(\Magmanite\Db\Dsn $dsn) {
        try {
            $res = @new \mysqli(
                $dsn->getDatabaseServerHost(),
                $dsn->getUsername(),
                $dsn->getPassword(),
                $dsn->getDatabaseName(),
                $dsn->getDatabaseServerPort());

            if ($res->connect_error) {
                throw new DriverException('Cannot connect to database: '
                    . $res->connect_error . ' (' . $res->connect_errno . ')');
            }

            return $res;
        } catch (\Exception $e) {
            if ($e instanceof DriverException) {
                throw $e;
            }

            throw new DriverException('Cannot connect to database: '
                . $e->getMessage(), DriverException::CANNOT_CONNECT);
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Magmanite\Db\Driver\AbstractDriver::_disconnect()
     */
    protected function _disconnect($dbh) {
        if (!($dbh instanceof \mysqli)) {
            throw new DriverException('Unknown DBH.', DriverException::INVALID_DATABASE_CONNECTION);
        }

        try {
            $dbh->close();
        } catch (\Exception $e) {
            throw new DriverException('Cannot close database connection: '
                . $e->getMessage(), DriverException::CANNOT_CONNECT);
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Magmanite\Db\Driver\AbstractDriver::_query()
     */
    protected function _query($dbh, $sql) {
        try {
            $result = $dbh->query($sql, MYSQLI_STORE_RESULT);

            if ($result === false) {
                return new \Magmanite\Db\Result\Mysql(null, false, false, $dbh->errno, $dbh->error);
            }

            return new \Magmanite\Db\Result\Mysql($this, $result);
        } catch (\Exception $e) {
            throw new DriverException(
                'Cannot query database: ' . $e->getMessage(),
                DriverException::CANNOT_QUERY_DATABASE,
                $e);
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Magmanite\Db\Driver\AbstractDriver::_queryUnbuffered()
     */
    protected function _queryUnbuffered($dbh, $sql) {
        try {
            $result = $dbh->query($sql, MYSQLI_USE_RESULT);

            if ($result === false) {
                return new \Magmanite\Db\Result\Mysql(null, false, true, $dbh->errno, $dbh->error);
            }

            return new \Magmanite\Db\Result\Mysql($this, $result, true);
        } catch (\Exception $e) {
            throw new DriverException(
                'Cannot query database: ' . $e->getMessage(),
                DriverException::CANNOT_QUERY_DATABASE,
                $e);
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Magmanite\Db\Driver\AbstractDriver::_escape()
     */
    protected function _escape($dbh, $value) {
        return $dbh->real_escape_string($value);
    }
// -----------------------------------------------------------------------------
}