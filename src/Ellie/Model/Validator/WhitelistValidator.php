<?php
/**
 * Created by PhpStorm.
 * User: sophie
 * Date: 03/08/15
 * Time: 12:20
 */

namespace Ellie\Model\Validator;


class WhitelistValidator
{
    const NOT_CASE_SENSITIVE = 1;

    protected $whitelist;
    protected $options;

    public function __construct(array $whitelist, ...$options)
    {
        $this->whitelist = $whitelist;
        $this->options = $options;
    }

    protected function hasOption($option)
    {
        $result = array_search($option, $this->options);
        if ($result === false) {
            return false;
        }

        return true;
    }

    public function isValid($dataString)
    {
        $whitelist = $this->whitelist;

        if ($this->hasOption(self::NOT_CASE_SENSITIVE)) {
            $dataString = strtoupper($dataString);
            array_walk($whitelist, function (&$value) {
                $value = strtoupper($value);
            });
        }

        $result = array_search($dataString, $whitelist);
        if ($result === false) {
            return false;
        }

        return true;
    }
}
