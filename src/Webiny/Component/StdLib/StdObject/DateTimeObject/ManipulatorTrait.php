<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\StdLib\StdObject\DateTimeObject;

use Webiny\Component\StdLib\StdObject\StdObjectManipulatorTrait;
use Webiny\Component\StdLib\StdObject\StringObject\StringObject;

/**
 * DateTimeObject manipulator trait.
 *
 * @package         Webiny\Component\StdLib\StdObject\DateTimeObject
 */
trait ManipulatorTrait
{
    use StdObjectManipulatorTrait;

    /**
     * Adds an amount of days, months, years, hours, minutes and seconds to a DateTimeObject.
     *
     * @param string $amount You can specify the amount in ISO8601 format (example: 'P14D' = 14 days; 'P1DT12H' = 1 day 12 hours),
     *                       or as a date string (example: '1 day', '2 months', '3 year', '2 days + 10 minutes').
     *
     * @return $this
     * @throws DateTimeObjectException
     */
    public function add($amount)
    {
        try {
            $interval = $this->_parseDateInterval($amount);
            $this->_getDateObject()->add($interval);
        } catch (\Exception $e) {
            throw new DateTimeObjectException($e->getMessage());
        }


        return $this;
    }

    /**
     * Set the date on current object.
     *
     * @param int $year
     * @param int $month
     * @param int $day
     *
     * @throws DateTimeObjectException
     * @return $this
     */
    public function setDate($year, $month, $day)
    {
        try {
            $this->_getDateObject()->setDate($year, $month, $day);
        } catch (\Exception $e) {
            throw new DateTimeObjectException($e->getMessage());
        }

        return $this;
    }

    /**
     * Set the time on current object.
     *
     * @param int $hour
     * @param int $minute
     * @param int $second
     *
     * @throws DateTimeObjectException
     * @return $this
     */
    public function setTime($hour, $minute, $second = 0)
    {
        try {
            $this->_getDateObject()->setTime($hour, $minute, $second);
        } catch (\Exception $e) {
            throw new DateTimeObjectException($e->getMessage());
        }

        return $this;
    }

    /**
     * Set the timestamp on current object.
     *
     * @param int $timestamp UNIX timestamp.
     *
     * @throws DateTimeObjectException
     * @return $this
     */
    public function setTimestamp($timestamp)
    {
        try {
            $this->_getDateObject()->setTimestamp($timestamp);
        } catch (\Exception $e) {
            throw new DateTimeObjectException($e->getMessage());
        }

        return $this;
    }

    /**
     * Subtracts an amount of days, months, years, hours, minutes and seconds from current DateTimeObject.
     *
     * @param string $amount You can specify the amount in ISO8601 format (example: 'P14D' = 14 days; 'P1DT12H' = 1 day 12 hours),
     *                       or as a date string (example: '1 day', '2 months', '3 year', '2 days + 10 minutes').
     *
     * @return $this
     * @throws DateTimeObjectException
     */
    public function sub($amount)
    {
        try {
            $interval = $this->_parseDateInterval($amount);
            $this->_getDateObject()->sub($interval);
        } catch (\Exception $e) {
            throw new DateTimeObjectException($e->getMessage());
        }

        return $this;
    }


    /**
     * Offsets the date object from current timezone to defined $timezone.
     * This is an alias of DateTimeObject::setTimezone.
     *
     * @param string|\DateTimeZone $timezone Timezone to which you wish to offset. You can either pass \DateTimeZone object
     *                                       or a valid timezone string. For timezone string formats
     *                                       visit: http://php.net/manual/en/timezones.php
     *
     * @throws DateTimeObjectException
     * @return $this
     */
    public function offsetToTimezone($timezone)
    {
        try {
            $this->setTimezone($timezone);
        } catch (\Exception $e) {
            throw new DateTimeObjectException($e->getMessage());
        }

        return $this;
    }

    /**
     * @param $interval
     *
     * @return \DateInterval
     * @throws DateTimeObjectException
     */
    private function _parseDateInterval($interval)
    {
        try {
            if (!$this->isInstanceOf($interval, 'DateInterval')) {
                $interval = new StringObject($interval);
                if ($interval->startsWith('P')) {
                    $interval = new \DateInterval($interval);
                } else {
                    $interval = \DateInterval::createFromDateString($interval);
                }
            }
        } catch (Exception $e) {
            throw new DateTimeObjectException(DateTimeObjectException::MSG_INVALID_DATE_INTERVAL, [$interval]);
        }

        return $interval;
    }
}