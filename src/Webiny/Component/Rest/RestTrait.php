<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest;

use Webiny\Component\Http\Request;

/**
 * The RestTrait is a helper for working with the rest component.
 *
 * @package         Webiny\Component\Rest
 */
trait RestTrait
{

    /**
     * Get the page number.
     *
     * @param int $default Default value to return if page parameter is not found or if it's not valid.
     *
     * @return int
     */
    protected static function restGetPage($default = 1)
    {
        $page = Request::getInstance()->query('_page', $default);
        if (!is_int($page) || $page < 1) {
            return $default;
        }

        return $page;
    }

    /**
     * Get the perPage value.
     *
     * @param int $default Default value to return if perPage parameter is not found or if it's not valid.
     *
     * @return int
     */
    protected static function restGetPerPage($default = 10)
    {
        $perPage = Request::getInstance()->query('_perPage', $default);
        if (!is_int($perPage) || $perPage < 1 || $perPage > 1000) {
            return $default;
        }

        return $perPage;
    }

    /**
     * Get the sort value.
     *
     * @param string|bool $default Default value to return if sort parameter is not found.
     *
     * @return mixed|string
     */
    protected static function restGetSortField($default = false)
    {
        $sort = Request::getInstance()->query('_sort', false);
        if (!$sort) {
            return $default;
        } else {
            $sortDirection = substr($sort, 0, 1);
            if ($sortDirection == '+' || $sortDirection == '-') {
                return substr($sort, 1);
            }

            return $sort;
        }
    }

    /**
     * Get the sort direction.
     * The result output is optimized for mongodb, meaning we return '1' for ascending and '-1' for descending.
     *
     * @param int $default Default value to return if sort parameter is not found or if it's not valid.
     *
     * @return int
     */
    protected static function restGetSortDirection($default = 1)
    {
        $sort = Request::getInstance()->query('_sort', false);
        if (!$sort) {
            return $default;
        } else {
            $sortDirection = substr($sort, 0, 1);
            if ($sortDirection == '+') {
                return 1;
            } else {
                if ($sortDirection == '-') {
                    return -1;
                }
            }

            return $default;
        }
    }

    /**
     * Get the field list.
     *
     * @param string $default Default value to return if sort parameter is not found.
     *
     * @return string
     */
    protected static function restGetFields($default = '')
    {
        return Request::getInstance()->query('_fields', $default);
    }
}