<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest\Response;

use Webiny\Component\Cache\CacheTrait;
use Webiny\Component\Http\HttpTrait;
use Webiny\Component\Rest\RestException;
use Webiny\Component\StdLib\StdLibTrait;

/**
 * This class add a protection layer over your API in a way that it limits, per IP, how many
 * requests can it make, in one minute.
 * The interval is automatically reset after each minute.
 * RateControl requires that you have a Cache defined on your Rest configuration.
 *
 * @package         Webiny\Component\Rest\Response
 */
class RateControl
{
    use HttpTrait, CacheTrait, StdLibTrait;

    /**
     * Checks if user is within rate limits.
     *
     * @param RequestBag     $requestBag
     * @param CallbackResult $cr
     *
     * @return bool
     * @throws \Webiny\Component\Rest\RestException
     */
    public static function isWithinRateLimits(RequestBag $requestBag, CallbackResult $cr)
    {
        // do we have rate control in place?
        if (!($rateControl = $requestBag->getApiConfig()->get('RateControl', false))) {
            return true; // if rate control is not set, user is within his limits
        }

        // check if we should ignore rate control for this particular method
        if (isset($requestBag->getMethodData()['rateControl']['ignore'])) {
            return true;
        }

        // verify that we have a Cache service set
        if (!($cache = $requestBag->getApiConfig()->get('Cache', false))) {
            throw new RestException('Rest Rate Control requires that you have a Cache service defined
            under the Rest configuration.'
            );
        }

        // set the limit in response header
        $cr->attachDebugHeader('RateControl-Limit', $rateControl->Limit, true);

        // get current usage
        $cacheKey = md5('Webiny.Rest.RateLimit.' . self::httpRequest()->getClientIp());
        $cacheData = self::cache($cache)->read($cacheKey);
        if (!$cacheData) {
            $cacheData = [
                'usage'   => 0,
                'penalty' => 0,
                'ttl'     => time() + (60 * $rateControl->Interval)
            ];
        } else {
            $cacheData = self::unserialize($cacheData);

            // validate the ttl
            if (time() > $cacheData['ttl']) {
                $cacheData = [
                    'usage'   => 0,
                    'penalty' => 0,
                    'ttl'     => time() + (60 * $rateControl->Interval)
                ];
            }
        }

        // check if user is already in penalty
        if ($cacheData['penalty'] > time()) {
            // when in penalty the reset value, equals the penalty value
            $cr->attachDebugHeader('RateControl-Reset', ($cacheData['penalty'] - time()), true);

            // and the remaining equals 0
            $cr->attachDebugHeader('RateControl-Remaining', 0, true);

            return false;
        }

        // check if rate is reached
        if ($cacheData['usage'] >= $rateControl->Limit) {
            // set penalty for reaching the limit
            $cr->attachDebugHeader('RateControl-Reset', (($rateControl->Penalty * 60) + time()), true);

            // and the remaining 0
            $cr->attachDebugHeader('RateControl-Remaining', 0, true);

            return false;
        }

        // if limit not reached, increment the usage and save the data
        $cacheData['usage']++;
        $cr->attachDebugHeader('RateControl-Remaining', ($rateControl->Limit - $cacheData['usage']), true);
        $cr->attachDebugHeader('RateControl-Reset', $cacheData['ttl'], true);

        $cacheTtl = ($rateControl->Interval > $rateControl->Penalty) ? $rateControl->Interval : $rateControl->Penalty;

        self::cache($cache)->save($cacheKey, self::serialize($cacheData), ($cacheTtl * 60));

        return true;
    }


}