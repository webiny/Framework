<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\StdLib\StdObject\DateTimeObject;

use Webiny\Component\StdLib\StdObject\ArrayObject\ArrayObject;
use Webiny\Component\StdLib\StdObject\StdObjectAbstract;
use Webiny\Component\StdLib\StdObject\StringObject\StringObject;

/**
 * Date standard object.
 * Class that enables you to work with dates and time much easier.
 *
 * @package         Webiny\Component\StdLib\StdObject\DateTimeObject
 */
class DateTimeObject extends StdObjectAbstract
{
    use ValidatorTrait, ManipulatorTrait;

    /**
     * @var \DateTime|null
     */
    protected $_value = null;

    /**
     * This is the default timezone. It's set to the servers timezone.
     * This object is static because we don't want to detect the default timezone each time.
     * @var null|\DateTimeZone
     */
    private static $_defaultTimezone = null;

    /**
     * Default date format
     * @var string
     */
    private static $_defaultFormat = 'Y-m-d H:i:s';

    /**
     * @var Timezone of entry date timestamp.
     */
    private $_timezone = null;

    /**
     * @var string Date format
     */
    private $_format = null;

    /**
     * A list of valid date formats grouped by their type.
     * @var ArrayObject
     */
    private static $_formatters = [
        'date'     => [
            'c',
            'r',
            'U'
        ],
        'year'     => [
            'Y',
            'y',
            'o'
        ],
        'month'    => [
            'F',
            'm',
            'M',
            'n',
            't'
        ],
        'week'     => ['W'],
        'day'      => [
            'd',
            'D',
            'j',
            'l',
            'N',
            'S',
            'w',
            'z'
        ],
        'time'     => ['H:i:s'],
        'hours'    => [
            'g',
            'G',
            'h',
            'H'
        ],
        'meridiem' => [
            'a',
            'A'
        ],
        'minutes'  => ['i'],
        'seconds'  => ['s']
    ];
    /**
     * @var null|ArrayObject
     */
    private $_dateTimeFormat = null;


    /**
     * Constructor.
     * Set standard object value.
     *
     * @param string|int  $time                     A date/time string. List of available formats is explained here
     *                                              http://www.php.net/manual/en/datetime.formats.php
     * @param null|string $timezone                 Timezone in which you want to set the date. Here is a list of valid
     *                                              timezones: http://php.net/manual/en/timezones.php
     *
     * @throws DateTimeObjectException
     */
    public function __construct($time = "now", $timezone = null)
    {
        try {
            // set the config
            $this->_parseDateTimeFormat();

            // if UNIX timestamp - convert to string value
            if (is_numeric($time) && $time <= PHP_INT_MAX && $time >= ~PHP_INT_MAX) {
                // Try converting timestamp to date string and back
                $date = date('Y-m-d H:i:s', $time);
                if ($date && strtotime($date)) {
                    $time = $date;
                }
            }

            // get date timezone
            $this->_entryTimezone = $this->_createTimezone($timezone);
            $this->val(new \DateTime($time, $this->_entryTimezone));

            // get UTC offset and correct the date to UTC by calculating the offset
            $this->_timestamp = $this->_getDateObject()->getTimestamp();
        } catch (\Exception $e) {
            throw new DateTimeObjectException($e->getMessage());
        }
    }

    /**
     * Set the date format.
     *
     * @param string $format Date format. These are the valid options: http://php.net/manual/en/function.date.php
     *
     * @return $this
     */
    public function setFormat($format)
    {
        $this->_format = $format;

        return $this;
    }

    /**
     * Set a new timezone for current date object.
     * NOTE: The current timestamp will be recalculated with the offset of current timezone and the new defined one.
     *
     * @param string|\DateTimeZone $timezone Timezone to which you wish to offset. You can either pass \DateTimeZone object
     *                                       or a valid timezone string. For timezone string formats
     *                                       visit: http://php.net/manual/en/timezones.php
     *
     * @return $this
     * @throws DateTimeObjectException
     */
    public function setTimezone($timezone)
    {
        try {
            if (!$this->isInstanceOf($timezone, 'DateTimeZone')) {
                $timezone = new \DateTimeZone($timezone);
            }

            $this->_getDateObject()->setTimezone($timezone);
        } catch (\Exception $e) {
            throw new DateTimeObjectException(DateTimeObjectException::MSG_INVALID_TIMEZONE, [$timezone]);
        }

        return $this;
    }

    /**
     * Create a DateTimeObject from the given $time and $format.
     *
     * @param string|int  $time   Timestamp.
     * @param null|string $format Format in which the current timestamp is defined.
     *
     * @return DateTimeObject
     * @throws DateTimeObjectException
     */
    public static function createFromFormat($time, $format = null)
    {
        if (self::isNull($format)) {
            $format = self::$_defaultFormat;
        }

        try {
            $date = \DateTime::createFromFormat($format, $time);
            if (!$date) {
                throw new DateTimeObjectException(DateTimeObjectException::MSG_UNABLE_TO_CREATE_FROM_FORMAT);
            }
        } catch (\Exception $e) {
            throw new DateTimeObjectException(DateTimeObjectException::MSG_UNABLE_TO_CREATE_FROM_FORMAT);
        }

        try {
            $dt = new DateTimeObject();
            $dt->setTimestamp($date->getTimestamp());
            $dt->setFormat($format);
        } catch (\Exception $e) {
            throw new DateTimeObjectException(DateTimeObjectException::MSG_UNABLE_TO_CREATE_FROM_FORMAT);
        }

        return $dt;
    }

    /**
     * @param int|string|\DateTime|DateTimeObject $time     Date to compare to.
     * @param bool                                $absolute Should the interval be forced to be positive?
     *
     * @throws DateTimeObjectException
     * @return ArrayObject Instance of ArrayObject containing time units (d,m,y,h,i,s) for keys and amounts for their values.
     */
    public function diff($time, $absolute = false)
    {
        try {
            if ($this->isInstanceOf($time, 'DateTime')) {
                $date = $time;
            } else {
                if ($this->isInstanceOf($time, $this)) {
                    $date = new \DateTime();
                    $date->setTimestamp($time->getTimestamp());
                } else {
                    $date = new \DateTime($time);
                }
            }
        } catch (\Exception $e) {
            throw new DateTimeObjectException(DateTimeObjectException::MSG_UNABLE_TO_PARSE, ['$time']);
        }

        try {
            $diff = $this->_getDateObject()->diff($date, $absolute);
        } catch (\Exception $e) {
            throw new DateTimeObjectException(DateTimeObjectException::MSG_UNABLE_TO_DIFF);
        }

        $result = get_object_vars($diff);

        return new ArrayObject($result);
    }

    /**
     * Return date in the given format.
     *
     * @param string $format A valid date format.
     *
     * @return string A string containing the date in the given $format.
     * @throws DateTimeObjectException
     */
    public function format($format)
    {
        try {
            return $this->_getDateObject()->format($format);
        } catch (\Exception $e) {
            throw new DateTimeObjectException(DateTimeObjectException::MSG_INVALID_DATE_FORMAT, [$format]);
        }
    }

    /**
     * Get the offset from current timezone to the UTC timezone in seconds.
     *
     * @return int The offset from current timezone to UTC in seconds.
     */
    public function getOffset()
    {
        return $this->_getDateObject()->getOffset();
    }

    /**
     * Get the name of current timezone.
     *
     * @return string The name of current timezone.
     */
    public function getTimezone()
    {
        return $this->_getDateObject()->getTimezone()->getName();
    }

    /**
     * Get date in full date format.
     *
     * @param null|string $format A valid date format.
     *
     * @return string Date in full date format like ISO 8691 or RFC 2822.
     */
    public function getDate($format = null)
    {
        return $this->_getDateElement('date', $format);
    }

    /**
     * Get year based on current date.
     *
     * @param null|string $format A valid year format.
     *
     * @return string Year based on current date.
     */
    public function getYear($format = null)
    {
        return $this->_getDateElement('year', $format);
    }

    /**
     * Get month based on current date.
     *
     * @param null|string $format A valid month format.
     *
     * @return string  Month based on current date.
     */
    public function getMonth($format = null)
    {
        return $this->_getDateElement('month', $format);
    }

    /**
     * Get week number based on current date.
     *
     * @return int Wek number based on current date.
     */
    public function getWeek()
    {
        return $this->_getDateElement('week');
    }

    /**
     * Get day based on current date.
     *
     * @param null|string $format A valid day format.
     *
     * @return string  Day based on current date.
     */
    public function getDay($format = null)
    {
        return $this->_getDateElement('day', $format);
    }

    /**
     * Return time based on current date.
     *
     * @param null|string $format A valid time format.
     *
     * @return string Time based on current date.
     */
    public function getTime($format = null)
    {
        return $this->_getDateElement('time', $format);
    }

    /**
     * Get hours based on current date.
     *
     * @param null|string $format A valid hour format.
     *
     * @return string Hours based on current date.
     */
    public function getHours($format = null)
    {
        return $this->_getDateElement('hours', $format);
    }

    /**
     * Get meridiem (am, pm) based on current date.
     *
     * @param null|string $format A valid meridiem format.
     *
     * @return string Meridiem (am, pm) based on current date.
     */
    public function getMeridiem($format = null)
    {
        return $this->_getDateElement('meridiem', $format);
    }

    /**
     * Get minutes based on current date.
     *
     * @return string Minutes based on current date
     */
    public function getMinutes()
    {
        return $this->_getDateElement('minutes');
    }

    /**
     * Get seconds based on current date.
     *
     * @return string Seconds based on current date.
     */
    public function getSeconds()
    {
        return $this->_getDateElement('seconds');
    }

    /**
     * Get UNIX timestamp based on current date.
     * @return int UNIX timestamp based on current date
     */
    public function getTimestamp()
    {
        return $this->_getDateObject()->getTimestamp();
    }

    /**
     * Calculates the time passed between current date and $form (default: now).
     * The output is formatted in plain words, like "4 hours ago".
     *
     * @param null $from Timestamp from where to calculate the offset. Default is now.
     *
     * @return string String describing the passed time. Example "4 hours ago".
     */
    public function getTimeAgo($from = null)
    {
        $periods = [
            'second',
            'minute',
            'hour',
            'day',
            'week',
            'month',
            'year',
            'decade'
        ];
        $lengths = [
            '60',
            '60',
            '24',
            '7',
            '4.35',
            '12',
            '10'
        ];

        $now = ($this->isNull($from)) ? time() : strtotime($from);
        $unix_date = $this->getTimestamp();

        // is it future date or past date
        if ($now > $unix_date) {
            $difference = $now - $unix_date;
            $tense = "ago";

        } else {
            $difference = $unix_date - $now;
            $tense = "from now";
        }

        for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths) - 1; $j++) {
            $difference /= $lengths[$j];
        }

        $difference = round($difference);

        if ($difference != 1) {
            $periods[$j] .= "s";
        }

        return $difference . ' ' . $periods[$j] . ' ' . $tense;
    }

    /**
     * Return, or update, current standard objects value.
     *
     * @param null $value If $value is set, value is updated and DateTimeObject is returned.
     *
     * @return mixed
     */
    public function val($value = null)
    {
        if (!$this->isNull($value)) {
            $this->_value = $value;

            return $this;
        }

        return $this->_getDateObject()->format($this->_format);
    }

    /**
     * To string implementation.
     *
     * @return mixed
     */
    public function __toString()
    {
        return $this->format($this->_format);
    }

    /**
     * Returns current \DateTime object.
     *
     * @return \DateTime|null
     */
    private function _getDateObject()
    {
        return $this->_value;
    }

    /**
     * Create a DateTimeZone object for the given $timeZone.
     * If $timezone is undefined, default timezone is returned. Default timezone is the servers timezone.
     *
     * @param string|null $timezone A valid time zone. For list of available timezones visit:
     *                              http://www.php.net/manual/en/timezones.php
     *
     * @return \DateTimeZone
     * @throws DateTimeObjectException
     */
    private function _createTimezone($timezone = null)
    {
        try {
            if ($this->isNull($timezone)) {
                if ($this->isNull(self::$_defaultTimezone)) {
                    try {
                        $defaultTimezone = date_default_timezone_get();

                        self::$_defaultTimezone = new \DateTimeZone($defaultTimezone);
                    } catch (\Exception $e) {
                        throw new DateTimeObjectException(DateTimeObjectException::MSG_DEFAULT_TIMEZONE);
                    }
                }

                return self::$_defaultTimezone;
            } else {
                return new \DateTimeZone($timezone);
            }
        } catch (\Exception $e) {
            throw new DateTimeObjectException(DateTimeObjectException::MSG_INVALID_TIMEZONE, [$timezone]);
        }
    }

    /**
     * This function parses the format provided by Config and sets the default formatting for getting date information
     * like day, month, year, etc..
     *
     * @throws DateTimeObjectException
     */
    private function _parseDateTimeFormat()
    {
        try {
            if ($this->isNull($this->_format)) {
                $this->_format = self::$_defaultFormat;
            }

            $str = new StringObject($this->_format);
            $chunks = $str->split();

            $this->_buildFormatterList();

            foreach ($chunks as $c) {
                foreach (self::$_formatters as $fk => $f) {
                    if ($f->inArray($c)) {
                        $this->_dateTimeFormat[$fk] = $c;
                    }
                }
            }
            $this->_dateTimeFormat = new ArrayObject($this->_dateTimeFormat);
        } catch (\Exception $e) {
            throw new DateTimeObjectException(DateTimeObjectException::MSG_INVALID_DATE_FORMAT, [$this->_format]);
        }
    }

    /**
     * Reformats self::$_formatters from array to ArrayObject.
     */
    private function _buildFormatterList()
    {
        if (!$this->isObject(self::$_formatters)) {
            $formatters = new ArrayObject([]);
            foreach (self::$_formatters as $fk => $fv) {
                $formatters->key($fk, new ArrayObject($fv));
            }
            self::$_formatters = $formatters;
        }
    }

    /**
     * Returns format for defined $dateElement.
     *
     * @param string $dateElement Possible values are: date, year, month, day, time, hour, minutes, seconds, meridiem.
     *
     * @return mixed
     */
    private function _getFormatFor($dateElement)
    {
        if ($this->_dateTimeFormat->keyExists($dateElement)) {
            return $this->_dateTimeFormat->key($dateElement);
        }

        return self::$_formatters->key($dateElement)->first();
    }

    /**
     * Checks if $format is a valid format for $dateElement.
     *
     * @param string $dateElement Possible values are: date, year, month, day, time, hour, minutes, seconds, meridiem.
     * @param string $format      For list of possible formats check: http://php.net/manual/en/function.date.php
     *
     * @return mixed
     * @throws DateTimeObjectException
     */
    private function _validateFormatFor($dateElement, $format)
    {
        if (!self::$_formatters->key($dateElement)->inArray($format)) {
            throw new DateTimeObjectException(DateTimeObjectException::MSG_INVALID_FORMAT_FOR_ELEMENT, [
                    $format,
                    "get" . ucfirst($dateElement)
                ]
            );
        }

        return $format;
    }

    /**
     * Returns defined $dateElement in defined $format.
     *
     * @param string      $dateElement Possible values are: date, year, month, day, time, hour, minutes, seconds, meridiem.
     * @param null|string $format      For list of possible formats check: http://php.net/manual/en/function.date.php
     *
     * @return string
     */
    private function _getDateElement($dateElement, $format = null)
    {
        $format = ($this->isNull($format)) ? $this->_getFormatFor($dateElement
        ) : $this->_validateFormatFor($dateElement, $format);

        return $this->_getDateObject()->format($format);
    }

}