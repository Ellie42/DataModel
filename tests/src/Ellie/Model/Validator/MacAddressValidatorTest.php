<?php
/**
 * Created by PhpStorm.
 * User: sophie
 * Date: 06/08/15
 * Time: 12:25
 */

namespace User\Model\Validator;


use Ellie\Model\Validator\MacAddressValidator;

class MacAddressValidatorTest extends \PHPUnit_Framework_TestCase
{
    protected $validator;

    public function setup()
    {
        $this->validator = new MacAddressValidator();
    }

    public function test_isValid()
    {
        $validMac = "3D:F2:C9:A6:B3:4F";

        $this->assertTrue($this->validator->isValid($validMac));
    }

    public function test_isValid_notValid()
    {
        $validMac = "3D:F2:";

        $this->assertFalse($this->validator->isValid($validMac));
    }
}
