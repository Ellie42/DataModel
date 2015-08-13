<?php
/**
 * Created by PhpStorm.
 * User: sophie
 * Date: 06/08/15
 * Time: 12:21
 */

namespace Ellie\Model\Validator;


class MacAddressValidator
{
    public function isValid($string)
    {
        $regex = "/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/";

        preg_match($regex, $string, $match);

        return !!$match;
    }
}
