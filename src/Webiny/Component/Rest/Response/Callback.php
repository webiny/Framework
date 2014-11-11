<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest\Response;

use Webiny\Component\Http\HttpTrait;
use Webiny\Component\Rest\RestErrorException;
use Webiny\Component\Rest\RestException;

/**
 * Response calls the api method and parses the output.
 * Based on the output it builds the response object.
 *
 * @package         Webiny\Component\Rest\Response
 */
class Callback
{
    use HttpTrait;

    /**
     * @var RequestBag
     */
    private $_requestBag;


    /**
     * Base constructor.
     *
     * @param RequestBag $requestBag Current api request.
     */
    public function __construct(RequestBag $requestBag)
    {
        $this->_requestBag = $requestBag;
    }

    /**
     * Processes the callback and returns an instance of CallbackResult.
     *
     * @return CallbackResult
     * @throws \Webiny\Component\Rest\RestException
     */
    public function getCallbackResult()
    {
        $class = $this->_requestBag->getClassData()['class'];
        $this->_requestBag->setClassInstance(new $class);

        // create CallbackResult instance
        $cr = new CallbackResult();

        $env = 'production';
        if ($this->_requestBag->getApiConfig()->get('Environment', 'production') == 'development') {
            $cr->setEnvToDevelopment();
            $env = 'development';
        }

        // attach some metadata
        $cr->attachDebugHeader('Class', $class);
        $cr->attachDebugHeader('ClassVersion', $this->_requestBag->getClassData()['version']);
        $cr->attachDebugHeader('Method', strtoupper($this->httpRequest()->getRequestMethod()));
        $cr->attachDebugHeader('CompileCacheFile', $this->_requestBag->getCompileCacheFile());


        if (!$this->_requestBag->getMethodData()) {
            // if no method matched the request
            $cr->setHeaderResponse(404);
            $cr->setErrorResponse('No service matched the request.');

            return $cr;
        }

        // check rate limit
        try {
            $rateControl = RateControl::isWithinRateLimits($this->_requestBag, $cr);
            if (!$rateControl) {
                $cr->setHeaderResponse(429);
                $cr->setErrorResponse('Rate control limit reached.');

                return $cr;
            }
        } catch (\Exception $e) {
            throw new RestException('Rate control verification failed. ' . $e->getMessage());
        }

        // verify access role
        try {
            $hasAccess = Security::hasAccess($this->_requestBag);
            if (!$hasAccess) {
                $cr->setHeaderResponse(403);
                $cr->setErrorResponse('You don\'t have the required access level.');
                $cr->attachDebugHeader('RequestedRole', $this->_requestBag->getMethodData()['role']);

                return $cr;
            }
        } catch (\Exception $e) {
            throw new RestException('Access role verification failed. ' . $e->getMessage());
        }

        // verify cache
        try {
            $cachedResult = Cache::getFromCache($this->_requestBag);
        } catch (\Exception $e) {
            throw new RestException('Reading result from cache failed. ' . $e->getMessage());
        }

        // finalize output
        if ($cachedResult) {
            $cr->setData($cachedResult);
            $cr->attachDebugHeader('Cache', 'HIT');
        } else {
            try {
                $result = call_user_func_array([
                                                   $this->_requestBag->getClassInstance(),
                                                   $this->_requestBag->getMethodData()['method']
                                               ], $this->_requestBag->getMethodParameters()
                );

                // check if method has custom headers set
                $cr->setHeaderResponse($this->_requestBag->getMethodData()['header']['status']['success']);
                $cr->attachDebugHeader('Cache', 'MISS');

                // add result to output
                $cr->setData($result);

                // check if we need to attach the cache headers
                if ($this->_requestBag->getMethodData()['header']['cache']['expires'] > 0) {
                    $cr->setExpiresIn($this->_requestBag->getMethodData()['header']['cache']['expires']);
                }

                // cache the result
                Cache::saveResult($this->_requestBag, $result);

            } catch (RestErrorException $re) {
                // check if method has custom headers set
                $cr->setHeaderResponse($this->_requestBag->getMethodData()['header']['status']['error'],
                                       $this->_requestBag->getMethodData()['header']['status']['errorMessage']
                );

                $cr->setErrorResponse($re->getErrorMessage(), $re->getErrorDescription(), $re->getErrorCode());
                $errors = $re->getErrors();
                foreach ($errors as $e) {
                    $cr->addErrorMessage($e);
                }

                if ($env == 'development') {
                    $cr->addDebugMessage([
                                             'file'   => $re->getFile(),
                                             'line'   => $re->getLine(),
                                             'traces' => explode('#', $re->getTraceAsString())
                                         ]
                    );
                }
            } catch (\Exception $e) {
                // check if method has custom headers set
                $cr->setHeaderResponse($this->_requestBag->getMethodData()['header']['status']['error'],
                                       $this->_requestBag->getMethodData()['header']['status']['errorMessage']
                );

                $cr->setErrorResponse('There has been an error processing the request.');
                if ($env == 'development') {
                    $cr->addErrorMessage(['message' => $e->getMessage()]);
                    $cr->addDebugMessage([
                                             'file'   => $e->getFile(),
                                             'line'   => $e->getLine(),
                                             'traces' => explode('#', $e->getTraceAsString())
                                         ]
                    );
                }
            }
        }

        return $cr;
    }
}
