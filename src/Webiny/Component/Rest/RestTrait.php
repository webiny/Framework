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
        if (!is_numeric($page) || $page < 1) {
            return $default;
        }

        return (int)$page;
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
        if (!is_numeric($perPage) || $perPage < 1 || $perPage > 1000) {
            return $default;
        }

        return (int)$perPage;
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
        }

        $sortDirection = substr($sort, 0, 1);
        if ($sortDirection == '+' || $sortDirection == '-') {
            return substr($sort, 1);
        }

        return $sort;
    }

    /**
     * Get the sort fields values
     *
     * @param string|bool $default Default value to return if sort parameter is not found.
     *
     * @return mixed|string
     */
    protected static function restGetSortFields($default = [])
    {
        $sort = Request::getInstance()->query('_sort', false);
        if (!$sort) {
            return $default;
        }

        $sorters = [];
        $fields = explode(',', $sort);
        foreach ($fields as $sort) {
            $sortField = $sort;
            $sortDirection = 1;

            $sortDirectionSign = substr($sort, 0, 1);
            if ($sortDirectionSign == '+' || $sortDirectionSign == '-') {
                $sortField = substr($sort, 1);
                $sortDirection = $sortDirectionSign == '+' ? 1 : -1;
            }

            $sorters[$sortField] = $sortDirection;
        }

        return $sorters;
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
        }

        $sortDirection = substr($sort, 0, 1);
        if ($sortDirection == '+') {
            return 1;
        }

        if ($sortDirection == '-') {
            return -1;
        }

        return $default;
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

    /**
     * Get the fields depth
     *
     * @param int Default value to return if fieldsDepth parameter is not found.
     *
     * @return int
     */
    protected static function restGetFieldsDepth($default = 1)
    {
        $depth = Request::getInstance()->query('_fieldsDepth', false);
        if (!$depth) {
            $fields = static::restGetFields(false);
            if (!$fields) {
                return $default;
            }

            // Determine the deepest key
            $depth = 1;
            foreach (explode(',', $fields) as $key) {
                $keyDepth = substr_count($key, '.');
                if ($depth < $keyDepth) {
                    $depth = $keyDepth;
                }
            }
            return $depth;
        }

        return $depth;

    }

    /**
     * Return a query filter.
     * Filters are all the parameters in the url query.
     *
     * @param string $name Filter name.
     * @param mixed  $default Default filter value, if filter is not defined.
     *
     * @return mixed
     */
    protected static function restGetFilter($name, $default = null)
    {
        return Request::getInstance()->query($name, $default);
    }

    /**
     * Return all query filters
     *
     * @return mixed
     */
    protected static function restGetFilters()
    {
        return Request::getInstance()->query();
    }
}