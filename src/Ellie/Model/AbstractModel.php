<?php
/**
 * Created by PhpStorm.
 * User: sophie
 * Date: 23/06/15
 * Time: 09:06
 */

namespace Ellie\Model;


use Ellie\Traits\Common;

abstract class AbstractModel
{
    use Common;
    protected $data = [];

    protected $requiredFields = [];

    protected $allowedParams = null;
    protected $dataFormatConfig = [];
    protected $aliases = [];
    protected $names = [];

    protected $filters;
    protected $validators;
    protected $isInternal = false;

    protected $fieldOptions = [];

    const FIELD_NOT_VALID = -1;
    const REQUIRED_FIELD_MISSING = -2;

    public function __construct($options = null, $data = null)
    {
        if (isset($options['allowedParams'])) {
            $this->allowedParams = $options['allowedParams'];
        }

        if ($data !== null) {
            $this->setData($data);
        }
    }

    public function setInternal()
    {
        $this->isInternal = true;
    }

    public function setFilters(array $filters)
    {
        $this->filters = $filters;
    }

    public function setFilter($fieldName, array $filters)
    {
        $this->filters[$fieldName] = $filters;
    }

    public function addFilters($fieldName, array $filters)
    {
        if (!isset($this->filters[$fieldName])) {
            $this->filters[$fieldName] = [];
        }
        $this->filters[$fieldName] = array_merge($this->filters[$fieldName], $filters);
    }

    public function setValidators(array $validators)
    {
        $this->validators = $validators;
    }

    public function setValidator($fieldName, array $validators)
    {
        $this->validators[$fieldName] = $validators;
    }

    public function addValidators($fieldName, array $validators)
    {
        if (!isset($this->validators[$fieldName])) {
            $this->validators[$fieldName] = [];
        }
        $this->validators[$fieldName] = array_merge($this->validators[$fieldName], $validators);
    }

    public function get($name)
    {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }
        return null;
    }

    /**
     * Runs validators and filters on $dataArray using $this->filters and $this->validators
     * then sets $this->data using the keys in $allowedKeys array
     * if $allowedKeys is null then all validated and filtered data will be set
     * will also alias input data when the allowedParam field is "field as anotherFieldName"
     * dataArray input can also use the alias to set data
     * @param array $dataArray
     * @throws \Exception
     */
    protected function setDataSafe(array $dataArray)
    {
        if ($this->allowedParams === null) {
            return;
        }

        $this->parseConfigFieldOptions();

        $unFormattedArray = $this->unformatData($dataArray);

        $dataArray = $this->validateAndFilter($unFormattedArray);

        $allowedKeys = $this->allowedParams;

        if ($allowedKeys === null) {
            $this->data = array_merge($this->data, $dataArray);
        } else {
            foreach ($allowedKeys as $key) {
                $keyData = $this->getFieldAlias($key);

                $key = $keyData['name'];
                $keyAlias = $this->setDefault([$keyData, 'alias'], $keyData['name'], true);

                if (array_search($key, array_keys($dataArray)) !== false) {
                    $this->data[$keyAlias] = $dataArray[$key];
                } else if (array_search($keyAlias, array_keys($dataArray)) !== false) {
                    $this->data[$keyAlias] = $dataArray[$keyAlias];
                } else {
                    //Checks if key already exists to avoid overwriting old values
                    //so that you can call this function multiple times to merge new data
                    if (!isset($this->data[$keyAlias])) {
                        $this->data[$keyAlias] = null;
                    }
                }
            }
        }
    }

    protected function parseConfigFieldOptions()
    {
        foreach ($this->allowedParams as &$field) {
            if (substr($field, 0, 1) === '[' && substr($field, -1, 1) === ']') {
                preg_match('/\[(.*)\]/', $field, $match);

                $realFieldName = $match[1];

                $field = $realFieldName;
                $this->fieldOptions[$realFieldName][] = "array";
            }
        }
    }

    /**
     * This converts data from the format set in $this->dataFormatConfig back into it's flat unformatted form
     * It's mainly used to ease front end to back end data management, so long as you send the data back how you got it
     * it will work :)
     * @param array $data
     * @param null $config
     * @return array
     */
    public function unformatData(array $data, $config = null)
    {
        $config = $this->dataFormatConfig;
        $result = [];

        $flatConfig = [];

        array_walk_recursive($config, function ($a, $b) use (&$flatConfig) {
            $flatConfig[$b] = $a;
        });

        foreach ($data as $field => $value) {
            if (!isset($this->fieldOptions[$field])) {
                continue;
            }

            if (array_search('array', $this->fieldOptions[$field]) !== false) {
                $result[$field] = $value;
                unset($data[$field]);
            }
        }

        array_walk_recursive($data, function ($a, $b) use (&$result, $flatConfig) {
            $realFieldIndex = array_search($b, array_keys($flatConfig));
            if ($realFieldIndex !== false) {
                $realFieldName = preg_replace('/@/', '', array_values($flatConfig)[$realFieldIndex]);
                $result[$realFieldName] = $a;
            } else {
                $result[$b] = $a;
            }
        });

        return $result;
    }

    /**
     * Returns an alias set in an allowed key name in $this->allowedParams
     * an allowed key can have aliases in the style of mysql
     * $allowedKeys = ['user_id as id']
     * would allowed setDataSafe to use user_id but $this->data['id'] would contain the data
     * @param $fieldName
     * @return array
     */
    private function getFieldAlias($fieldName)
    {
        $splitFieldName = explode(' ', $fieldName);

        if (count($splitFieldName) == 1) {
            return ['name' => $fieldName];
        }

        if ($splitFieldName[1] == 'as') {
            $this->aliases[$splitFieldName[2]] = $splitFieldName[0];
            return ['name' => $splitFieldName[0], 'alias' => $splitFieldName[2]];
        }

        return ['name' => $fieldName];
    }

    /**
     * Returns all data set in the $data variable and ignores data with null value
     * @return array
     */
    protected function getGenericData($withAliases = true)
    {
        $data = [];

        $properties = $this->data;

        foreach ($properties as $variableName => $value) {
            if ($value !== null) {
                $name = $variableName;

                if ($withAliases === false) {
                    $name = $this->setDefault([$this->aliases, $variableName], $variableName, true);
                }

                $data[$name] = $value;
            }
        }

        return $data;
    }

    protected function getFormattedData(array $data)
    {
        $formattedData = $this->getFormattedDataRecursively($data);

        $formattedData = array_merge($formattedData, $data);

        return $formattedData;
    }

    /**
     * Creates the formatted array using placeholders and array structure defined by the user
     * @param array $data
     * @param null $config
     * @return array
     */
    private function getFormattedDataRecursively(array &$data, $config = null)
    {
        $config = $this->setDefault($config, $this->dataFormatConfig);
        $realData = [];
        foreach ($config as $fieldName => $placeholder) {
            if (is_array($placeholder) && count($placeholder) > 0) {
                //If array dive deeper
                $result = $this->getFormattedDataRecursively($data, $config[$fieldName]);
                if ($result !== []) {
                    $realData[$fieldName] = $result;
                }
            } else if (is_string($placeholder)) {
                //If string has @ in it then it is treated as a placeholder and will get $data[$placeholder]
                $realFieldName = preg_replace('/@/', '', $placeholder);
                if ($this->getArrayValue($data, $realFieldName) !== null) {
                    $realData[$fieldName] = $data[$realFieldName];
                    //Remove the formatted field from the main data
                    unset($data[$realFieldName]);
                } else if ($realFieldName === $placeholder) {
                    //If there was no @ then return plain text
                    $realData[$fieldName] = $placeholder;
                }
            }
        }
        return $realData;
    }

    /**
     * Set fields as required when going through validation in validateAndFilter()
     * @param ...$strings
     * @return $this
     */
    public function setRequired(...$strings)
    {
        foreach ($strings as $string) {
            $this->requiredFields[] = $string;
        }

        return $this;
    }

    /**
     * Performs all validation and filtering specified in $validators/Filters and checks for required fields
     * @param $data
     * @return array
     * @throws \Exception
     */
    protected function validateAndFilter($data)
    {
        foreach ($this->requiredFields as $requiredField) {
            if (array_search($requiredField, array_keys($data)) === false) {
                throw new \Exception("Required field $requiredField is missing", self::REQUIRED_FIELD_MISSING);
            }
        }

        if ($this->filters !== null) {
            $data = $this->filterData($data);
        }

        if ($this->validators !== null) {
            $validatorResults = $this->validateData($data, $this->validators);
        } else {
            $validatorResults = [];
        }


        foreach ($validatorResults as $fieldName => $field) {
            foreach ($field as $validatorName => $validatorResult) {
                if ($validatorResult == false) {
                    throw new \Exception("$fieldName did not pass $validatorName validation", self::FIELD_NOT_VALID);
                }
            }
        }

        return $data;
    }

    /**
     * Must receive an array of data to set all properties of the model
     * @param array
     */
    abstract public function setData(array $data);

    /**
     * Must return an array of all the intended public properties of the model
     * @return array
     */
    abstract public function getData();

    /**
     * Filters a string using any number of zend Filters
     * @param $string
     * @param ...$filters
     * @return mixed
     */
    protected function filter($string, ...$filters)
    {
        $validString = $string;
        foreach ($filters as $filter) {
            $validString = $filter->filter($validString);
        }

        return $validString;
    }

    /**
     * Filters an array of data using zend Filters
     * $dataArray must have key names to check against in the $filterConfig
     * $dataArray = ['email' => 'email@test.com'],
     * $filterConfig = ['email' => [new Zend\Filter, new Zend\Filter]]
     * @param array $dataArray
     * @return array
     * @internal param array $filtersConfig
     */
    protected function filterData(array $dataArray)
    {
        $filteredData = $dataArray;
        $this->addGroupFilters();

        foreach ($this->filters as $fieldName => $filters) {


            $this->runGlobalFilters($filteredData);

            if (!isset($dataArray[$fieldName])) {
                continue;
            }
            foreach ($filters as $filter) {
                $filteredData[$fieldName] = $filter->filter($filteredData[$fieldName]);
            }
        }

        return $filteredData;
    }

    /**
     * Validates a string using any number of zend validators
     * @param $string
     * @param ...$validators
     * @return array
     */
    protected function validate($string, ...$validators)
    {
        $resultArray = [];

        foreach ($validators as $validator) {
            $resultArray[get_class($validator)] = $validator->isValid($string);
        }

        return $resultArray;
    }

    /**
     * Validates an array of data using zend validators or custom functions
     * if validator is a string then it will call function in class
     * $dataArray must have key names to check against in the $validatorConfig
     * $dataArray = ['email' => 'email@test.com'],
     * $validatorConfig = ['email' => [new Zend\Validator, new Zend\Validator]]
     * @param array $dataArray
     * @param $validatorConfig
     * @return bool
     * @throws \Exception
     */
    protected function validateData(array $dataArray, array $validatorConfig)
    {
        $resultsArray = [];

        foreach ($validatorConfig as $fieldName => $validators) {
            $resultsArray = array_merge($resultsArray, $this->runGlobalValidators($dataArray));
            if (!isset($dataArray[$fieldName])) {
                continue;
            }
            foreach ($validators as $validator) {
                if (is_string($validator)) {
                    $result = call_user_func([$this, $validator], $dataArray[$fieldName]);
                    $validatorName = $validator;
                } else {
                    $result = $validator->isValid($dataArray[$fieldName]);
                    $validatorName = get_class($validator);
                }
                $resultsArray[$fieldName][$validatorName] = $result;
            }
        }

        return $resultsArray;
    }

    /**
     * Run filters in @group if field name is present
     * @internal param array $dataArray
     * @internal param $fieldName
     */
    protected function addGroupFilters()
    {
        if (!isset($this->filters['@group'])) {
            return;
        }
        foreach ($this->filters['@group'] as $group) {
            foreach ($group['fields'] as $fieldName) {
                $this->addFilters($fieldName, $group['filters']);
            }
        }
    }

    protected function getFieldGroups($fieldName, $type)
    {
        $array = $this->$$type;
        $groups = $array['@group'];

        $returnGroups = [];

        foreach ($groups as $group) {
            if (array_search($fieldName, $group['fields']) !== false) {
                $returnGroups = $group;
            }
        }

        return $returnGroups;
    }

    /**
     * Run all the filters set in @all
     * @param array $dataArray
     */
    protected function runGlobalFilters(array &$dataArray)
    {
        if (isset($this->filters['@all'])) {
            $globalFilters = $this->filters['@all'];
            foreach ($globalFilters as $globalFilter) {
                foreach ($dataArray as &$data) {
                    $data = $globalFilter->filter($data);
                }
            }
        }
    }

    /**
     * Check validity against all validators in @all
     * @param array $dataArray
     * @return array
     */
    protected function runGlobalValidators(array $dataArray)
    {
        $resultsArray = [];

        if (isset($this->validators['@all'])) {
            $globalValidators = $this->validators['@all'];
            foreach ($globalValidators as $globalValidator) {
                foreach ($dataArray as $name => $data) {
                    $result = $globalValidator->isValid($data);
                    $validatorName = get_class($globalValidator);
                    $resultsArray[$name][$validatorName] = $result;
                }
            }
        }

        return $resultsArray;
    }
}
