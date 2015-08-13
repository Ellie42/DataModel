<?php
/**
 * Created by PhpStorm.
 * User: sophie
 * Date: 10/08/15
 * Time: 11:02
 */

namespace Ellie\Model\Validator;


class StringValidator
{
    public function isValid($string)
    {
        if (is_string($string)) {
            return true;
        }


        return false;
    }
}
