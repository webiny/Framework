<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Http\Response;

use Webiny\Component\Http\Response;
use Webiny\Component\StdLib\StdLibTrait;
use Webiny\Component\StdLib\StdObject\ArrayObject\ArrayObject;
use Webiny\Component\StdLib\StdObject\StdObjectWrapper;
use Webiny\Component\StdLib\ValidatorTrait;

/**
 * JsonResponse extends the main Response object and configures it for JSON response.
 *
 * @package         Webiny\Component\Http\Response
 */
class JsonResponse extends Response
{
    use StdLibTrait;

    /**
     * Base constructor.
     *
     * @param string|array|ArrayObject $content     Json content.
     * @param array                    $headers     Headers to attach to the response.
     * @param bool                     $prettyPrint Should we use JSON_PRETTY_PRINT to nicely format the output.
     */
    public function __construct($content, $headers = [], $prettyPrint = false)
    {
        if (StdObjectWrapper::isArrayObject($content)) {
            $content = $this->jsonEncode($content->val(), (($prettyPrint) ? JSON_PRETTY_PRINT : 0));
        } else {
            if ($this->isArray($content) || $this->isObject($content)) {
                $content = $this->jsonEncode($content, (($prettyPrint) ? JSON_PRETTY_PRINT : 0));
            }
        }

        parent::__construct($content, 200, $headers);
        $this->setContentType('application/json');
    }

    /**
     * Creates a JsonResponse instance and sends the output to the browser.
     * This is just a short hand method for creating a new JsonResponse instance and then calling the "send" method.
     *
     * @param string|array|ArrayObject $content Json content.
     * @param array                    $headers Headers to attach to the response.
     */
    public static function sendJson($content, $headers = [])
    {
        $response = new self($content, $headers);
        $response->send();
    }
}