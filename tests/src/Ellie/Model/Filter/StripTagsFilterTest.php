<?php
/**
 * Created by PhpStorm.
 * User: sophie
 * Date: 13/08/15
 * Time: 11:13
 */

namespace Ellie\Model\Filter;


class StripTagsFilterTest extends \PHPUnit_Framework_TestCase
{
    public function test_filter()
    {
        $filter = new StripTagsFilter();

        $string = "<html><?php?> kjadshnkas";

        $result = $filter->filter($string);

        $expectedResult = " kjadshnkas";

        $this->assertEquals($expectedResult, $result);
    }
}
