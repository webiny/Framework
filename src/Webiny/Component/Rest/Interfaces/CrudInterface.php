<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest\Interfaces;

/**
 * Implementing this interface you will get the basic CRUD methods and behavior described below:
 *
 * ---------------------------------------------------------------------------------------------------------------
 * |  Request type   |        Url         |       Mapping               |  Description                           |
 * ---------------------------------------------------------------------------------------------------------------
 * |      GET        |    foo-class/      |  FooClass::crudList         | Retrieve all records in a collection.  |
 * |      POST       |    foo-class/      |  FooClass::crudCreate()     | Create new record.                     |
 * |      DELETE     |    foo-class/{id}  |  FooClass::crudDelete($id)  | Delete a record with the given id.     |
 * |      GET        |    foo-class/{id}  |  FooClass::crudGet($id)     | Retrieve a single record.              |
 * |      PUT        |    foo-class/{id}  |  FooClass::crudReplace($id) | Replace a single record.               |
 * |      PATCH      |    foo-class/{id}  |  FooClass::crudUpdate($id)  | Update a single record.                |
 * ---------------------------------------------------------------------------------------------------------------
 *
 * @package    Webiny\Component\Rest\Interfaces
 */

interface CrudInterface
{

    /**
     * Retrieve all records in a collection.
     *
     * @rest.default
     * @rest.method get
     *
     * @return array|mixed A list of retrieved records.
     * As an array with properties [records, totalCount, perPage, page].
     */
    public function crudList();

    /**
     * Create new record.
     *
     * @rest.default
     * @rest.method post
     *
     * @return array|mixed Array containing the newly created record.
     */
    public function crudCreate();

    /**
     * Delete a record with the given id.
     *
     * @rest.default
     * @rest.method delete
     *
     * @param string $id Id of the record to be deleted.
     *
     * @return bool True if delete was successful or false.
     */
    public function crudDelete($id);

    /**
     * Retrieve a single record.
     *
     * @rest.default
     * @rest.method get
     *
     * @param string $id Id of the record that should be retrieved.
     *
     * @return array|mixed The requested record.
     */
    public function crudGet($id);

    /**
     * Replace a single record.
     *
     * Note that the difference between crudUpdate and crudReplace is that in crudReplace, all current record attributes
     * should be removed and the new attributes should be added, while in crudUpdate the attributes would only be added
     * or deleted. In crudUpdate, if the record doesn't exist, it can be created.
     *
     * @see http://tools.ietf.org/html/rfc5789
     *
     * @rest.default
     * @rest.method put
     *
     * @param string $id Id of the record that should be replaced.
     *
     * @return array|mixed The replaced record.
     */
    public function crudReplace($id);

    /**
     * Update a single record.
     *
     * Note that the difference between crudUpdate and crudReplace is that in crudReplace, all current record attributes
     * should be removed and the new attributes should be added, while in crudUpdate the attributes would only be added
     * or deleted. In crudUpdate, if the record doesn't exist, it can be created.
     *
     * @see http://tools.ietf.org/html/rfc5789
     *
     * @rest.default
     * @rest.method patch
     *
     * @param string $id Id of the record that should be replaced.
     *
     * @return array|mixed The updated, or created, record.
     */
    public function crudUpdate($id);
}