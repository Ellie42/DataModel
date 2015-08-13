<?php
namespace Ellie\Model\Filter;

/**
 * Created by PhpStorm.
 * User: sophie
 * Date: 13/08/15
 * Time: 10:27
 */
class StripTagsFilter
{
    public function filter($string)
    {
        return strip_tags($string);
    }
}
