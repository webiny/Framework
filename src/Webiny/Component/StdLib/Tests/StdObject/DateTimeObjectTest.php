<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\StdLib\Tests\StdObject;

use Webiny\Component\StdLib\StdObject\DateTimeObject\DateTimeObject;

class DateTimeObjectTest extends \PHPUnit_Framework_TestCase
{

    public function testConstruct()
    {
        $dt = new DateTimeObject();

        $this->assertSame(time(), $dt->getTimestamp());
    }

    public function testConstructor2()
    {
        $dt = new DateTimeObject('14.02.2013');
        $dt->setFormat('d.m.Y');

        $this->assertSame('14.02.2013', $dt->val());
    }

    public function testConstructor3()
    {
        $timestamp = time();
        $dt = new DateTimeObject($timestamp);

        $this->assertSame($timestamp, $dt->getTimestamp());
    }

    public function testCreateFromFormat()
    {
        $dt = DateTimeObject::createFromFormat('15-Feb-2009', 'j-M-Y');

        $this->assertSame('15-Feb-2009', $dt->val());
    }

    public function testDiff()
    {
        $start = new DateTimeObject('14.02.2013');
        $end = new DateTimeObject('03.03.2013');

        $diff = $start->diff($end);

        $this->assertSame(17, $diff->key('d'));
    }

    public function testGetOffset()
    {
        $ny = new DateTimeObject("now", 'America/New_York');

        if($ny->getOffset()!=-18000 && $ny->getOffset()!=-14400){ // based on daylight saving time
            $this->assertFalse(true);
        }
    }

    public function testGetTimezone()
    {
        $dt = new DateTimeObject("now", 'Europe/Zagreb');

        $this->assertSame('Europe/Zagreb', $dt->getTimezone());
    }

    public function testGetDate()
    {
        $dt = new DateTimeObject('14.02.2013');

        $this->assertSame("2013-02-14T00:00:00+00:00", $dt->getDate());
    }

    public function testGetYear()
    {
        $dt = new DateTimeObject('14.02.2013');

        $this->assertSame("13", $dt->getYear('y'));
    }

    /**
     * @expectedException \Webiny\Component\StdLib\StdObject\StdObjectException
     * @expectedExceptionMessage Invalid format m for getYear
     */
    public function testGetYear2()
    {
        $dt = new DateTimeObject('14.02.2013');

        $this->assertSame("13", $dt->getYear('m'));
    }

    public function testGetMonth()
    {
        $dt = new DateTimeObject('14.02.2013');

        $this->assertSame("02", $dt->getMonth());
    }

    public function testGetWeek()
    {
        $dt = new DateTimeObject('14.02.2013');

        $this->assertSame("07", $dt->getWeek());
    }

    public function testGetDay()
    {
        $dt = new DateTimeObject('14.02.2013');

        $this->assertSame("14", $dt->getDay());
    }

    public function testGetTime()
    {
        $dt = new DateTimeObject('14.02.2013 14:33:18');

        $this->assertSame("14:33:18", $dt->getTime());
    }

    public function testGetHours()
    {
        $dt = new DateTimeObject('14.02.2013 14:33:18');

        $this->assertSame("14", $dt->getHours());
    }

    public function testGetMinutes()
    {
        $dt = new DateTimeObject('14.02.2013 14:33:18');

        $this->assertSame("33", $dt->getMinutes());
    }

    public function testGetSeconds()
    {
        $dt = new DateTimeObject('14.02.2013 14:33:18');

        $this->assertSame("18", $dt->getSeconds());
    }

    public function testGetMeridiem()
    {
        $dt = new DateTimeObject('14.02.2013 14:33:18');

        $this->assertSame("pm", $dt->getMeridiem());
    }

    public function testAdd()
    {
        $dt = new DateTimeObject('14.02.2013');
        $dt->add("5 days");

        $this->assertSame('19.02.2013', $dt->format('d.m.Y'));
    }

    public function testAdd2()
    {
        $dt = new DateTimeObject('14.02.2013');
        $dt->add("P5D");

        $this->assertSame('19.02.2013', $dt->format('d.m.Y'));
    }

    public function testSetDate()
    {
        $dt = new DateTimeObject('14.02.2013');
        $dt->setDate('2014', '01', '15');

        $this->assertSame('15.01.2014', $dt->format('d.m.Y'));
    }

    public function testSetTime()
    {
        $dt = new DateTimeObject('14.02.2013 12:00:00');
        $dt->setDate('2014', '01', '15')->setTime('14', '25');

        $this->assertSame('14:25:00', $dt->format('H:i:s'));
    }

    public function testSub()
    {
        $dt = new DateTimeObject('14.02.2013');
        $dt->sub("5 days");

        $this->assertSame('09.02.2013', $dt->format('d.m.Y'));
    }

    public function testIsLeap()
    {
        $dt = new DateTimeObject('14.02.2013');

        $this->assertFalse($dt->isLeap());
    }

    public function testIsLeap2()
    {
        $dt = new DateTimeObject('01.01.2012');

        $this->assertTrue($dt->isLeap());
    }

    public function testIsFuture()
    {
        $dt = new DateTimeObject('14.02.2013');

        $this->assertFalse($dt->isFuture());
    }

    public function testIsFuture2()
    {
        $dt = new DateTimeObject('01.01.2020');

        $this->assertTrue($dt->isFuture());
    }

    public function testIsPast()
    {
        $dt = new DateTimeObject('14.02.2020');

        $this->assertFalse($dt->isPast());
    }

    public function testIsPast2()
    {
        $dt = new DateTimeObject('01.01.2002');

        $this->assertTrue($dt->isPast());
    }

    public function testLargerThan()
    {
        $dt = new DateTimeObject('14.02.2013');

        $this->assertTrue($dt->largerThan('13.02.2013'));
    }

    public function testLargerThan2()
    {
        $dt = new DateTimeObject('14.02.2013');

        $this->assertFalse($dt->largerThan('15.02.2013'));
    }

    public function testSmallerThan()
    {
        $dt = new DateTimeObject('14.02.2013');

        $this->assertTrue($dt->smallerThan('15.02.2013'));
    }

    public function testSmallerThan2()
    {
        $dt = new DateTimeObject('14.02.2013');

        $this->assertFalse($dt->smallerThan('13.02.2013'));
    }
}