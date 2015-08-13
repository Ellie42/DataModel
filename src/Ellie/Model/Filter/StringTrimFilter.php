<?php
/**
 * Created by PhpStorm.
 * User: sophie
 * Date: 13/08/15
 * Time: 10:28
 */

namespace Ellie\Model\Filter;


class StringTrimFilter
{
    public function filter($string)
    {
        return trim($string);
    }
}
