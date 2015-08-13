<?php
/**
 * Created by PhpStorm.
 * User: sophie
 * Date: 13/08/15
 * Time: 10:31
 */

namespace Ellie\Model\Filter;


class StringTrimFilterTest extends \PHPUnit_Framework_TestCase
{
    protected $filter;

    public function setup()
    {
        $this->filter = new StringTrimFilter();
    }

    public function test_filter()
    {
        $string = "  djkahdik  kjhndaskhnd  jkhndaskjd     ";

        $result = $this->filter->filter($string);

        $expectedString = "djkahdik  kjhndaskhnd  jkhndaskjd";

        $this->assertEquals($expectedString, $result);
    }
}
