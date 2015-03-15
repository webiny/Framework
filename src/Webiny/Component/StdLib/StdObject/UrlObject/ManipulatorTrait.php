<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\StdLib\StdObject\UrlObject;

use Webiny\Component\StdLib\StdObject\ArrayObject\ArrayObject;
use Webiny\Component\StdLib\StdObject\StdObjectManipulatorTrait;
use Webiny\Component\StdLib\StdObject\StdObjectWrapper;
use Webiny\Component\StdLib\StdObject\StringObject\StringObject;

/**
 * UrlObject manipulator trait.
 *
 * @package         Webiny\Component\StdLib\StdObject\UrlObject
 */
trait ManipulatorTrait
{
    use StdObjectManipulatorTrait;

    /**
     * Set url scheme.
     *
     * @param StringObject|string $scheme - Scheme must end with '://'. Example 'http://'.
     *
     * @throws UrlObjectException
     * @return $this
     */
    public function setScheme($scheme)
    {
        // validate scheme
        try {
            $scheme = new StringObject($scheme);
        } catch (\Exception $e) {
            throw new UrlObjectException($e->getMessage());
        }

        if (!$scheme->endsWith('://')) {
            $scheme->val($scheme->val() . '://');
        }

        // set the scheme
        $this->scheme = $scheme->trimRight('://')->val();
        $this->rebuildUrl();

        return $this;
    }

    /**
     * Set url host.
     *
     * @param StringObject|string $host Url host.
     *
     * @throws UrlObjectException
     * @return $this
     */
    public function setHost($host)
    {
        try {
            $host = new StringObject($host);
        } catch (\Exception $e) {
            throw new UrlObjectException($e->getMessage());
        }

        $this->host = $host->stripTrailingSlash()->trim()->val();

        $this->rebuildUrl();

        return $this;
    }

    /**
     * Set url port.
     *
     * @param StringObject|string $port Url port.
     *
     * @return $this
     */
    public function setPort($port)
    {
        $this->port = StdObjectWrapper::toString($port);
        $this->rebuildUrl();

        return $this;
    }

    /**
     * Set url path.
     *
     * @param StringObject|string $path Url path.
     *
     * @throws UrlObjectException
     * @return $this
     */
    public function setPath($path)
    {
        try {
            $path = new StringObject($path);
        } catch (\Exception $e) {
            throw new UrlObjectException($e->getMessage());
        }

        if ($path != '') {
            $path->trimLeft('/');
            $this->path = '/' . $path->val();
        } else {
            $this->path = $path->val();
        }

        $this->rebuildUrl();

        return $this;
    }

    /**
     * Set url query param.
     *
     * @param StringObject|ArrayObject|string|array $query  Query params.
     * @param bool                                  $append Do you want to append or overwrite current query param.
     *                                                      In case when you are appending values, the values from $query,
     *                                                      that already exist in the current query, will be overwritten
     *                                                      by the ones inside the $query.
     *
     * @throws UrlObjectException
     * @return $this
     */
    public function setQuery($query, $append = false)
    {
        if ($this->isStdObject($query)) {
            $query = $query->val();
        }

        if ($append && $this->query != '') {

            if ($this->isString($this->query)) {
                $currentQuery = new StringObject($this->query);
                $currentQuery = $currentQuery->parseString();
            } else {
                $currentQuery = new ArrayObject($this->query);
            }

            if ($this->isStdObject($query)) {
                if (StdObjectWrapper::isArrayObject($append)) {
                    $query = $query->val();
                } else {
                    if (StdObjectWrapper::isStringObject($query)) {
                        $query = $query->parseString()->val();
                    } else {
                        throw new UrlObjectException(UrlObjectException::MSG_INVALID_ARG, [
                                '$query',
                                'StringObject|ArrayObject|string|array'
                            ]
                        );
                    }
                }
            } else {
                if ($this->isString($query)) {
                    $query = new StringObject($query);
                    $query = $query->parseString()->val();
                }
            }

            $currentQuery->merge($query);
            $query = $currentQuery->val();
        }

        $this->query = $query;
        $this->rebuildUrl();

        return $this;
    }

}
