<?php

namespace Ellie\Traits;

trait Common
{
//    private $keyNames;

    /**
     * Returns the value of an array index or sub index or returning null if it does not exist
     * @param $array
     * @param ...$keyNames
     * @return null
     */
    function getArrayValue($array, ...$keyNames)
    {
//        $this->keyNames = $keyNames;
        $value = $this->getArrayValueRecursively($array, 0, $keyNames);

        return $value;
    }

    /**
     * Searches for the subindex value
     * @param $array
     * @param int $offset
     * @param null $keyNames
     * @return null
     */
    private function getArrayValueRecursively($array, $offset = 0, $keyNames = null)
    {
        if (isset($keyNames[$offset])) {
            $keyName = $keyNames[$offset];
        } else {
            return $array;
        }

        if (isset($array[$keyName])) {
            $nextLevel = $this->getArrayValueRecursively($array[$keyName], $offset + 1, $keyNames);
            return $nextLevel;
        }

        return null;
    }

    function doesMethodExist($class, $method)
    {
        $reflectedClass = new \ReflectionClass($class);

        $method = $reflectedClass->hasMethod($method);

        return $method;
    }

    /**
     * Used to create a simple setter/getter function
     * when value is null it will get the value else it will set the vartoset
     * @param $value
     * @param $varToSet
     * @return mixed
     */
    function setGet($value, &$varToSet)
    {
        if ($value === null)
            return $varToSet;
        else
            $varToSet = $value;
    }


    /**
     * @param $data
     * @param $listToSearch
     * @param bool $searchInValues
     * @return array
     */
    protected function getAllowedArrayKeys($data, $listToSearch, $searchInValues = false)
    {
        $insertData = [];

        foreach ($data as $field => $value) {
            if ($searchInValues === false) {
                $listSearch = array_keys($listToSearch);
            } else {
                $listSearch = array_values($listToSearch);
            }
            $arrayKey = array_search($field, $listSearch);
            if ($arrayKey !== false) {
                $insertData[$field] = $data[$field];
            }
        }

        return $insertData;
    }

    /**
     * Input param can be any variable, if you have to check an array index use the following format
     * [
     *  arrayContainingIndex,
     *  ...indexesToCheck
     * ]
     * @param $inputParam
     * @param $default
     * makes the function treat arrays and class names differently
     * will check specific index(sub-index) in arrays and will instantiate classes
     * @param bool $evaluateType
     * @return mixed
     */
    function setDefault($inputParam, $default, $evaluateType = false)
    {
        /**
         * If the inputParam is null return $default
         */
        if ($inputParam === null) {
            /**
             * If evaluate type is true then class name strings will be instantiated
             */
            if (is_string($default) && $evaluateType === true && class_exists($default)) {
                $class = new $default();
                return $class;
            }
            return $default;
            /**
             * If evaluate type is true then it will search for the value of the specified index using
             * $this->getArrayValue($array,...$params)
             */
        } elseif (is_array($inputParam) && $evaluateType === true) {
            $result = call_user_func_array([$this, 'getArrayValue'], $inputParam);
            if ($result === null) {
                return $default;
            }
            return $result;
        }
        return $inputParam;
    }
}
