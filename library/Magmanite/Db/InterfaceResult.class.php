<?php
namespace Magmanite\Db;


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
