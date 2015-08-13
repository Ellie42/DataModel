<?php
/**
 * Created by PhpStorm.
 * User: sophie
 * Date: 23/06/15
 * Time: 14:24
 */

namespace Ellie\Model;

use Ellie\Model\Filter\StringTrimFilter;
use Ellie\Model\Filter\StripTagsFilter;
use Ellie\Model\Validator\MacAddressValidator;
use Ellie\Model\Validator\StringValidator;
use Ellie\Model\Validator\WhitelistValidator;
use Exception;

class TestFilter
{

}

class TestModel extends AbstractModel
{

    public function setData(array $data)
    {

    }

    public function getData()
    {

    }
}

class TestModel2 extends AbstractModel
{
    public function __construct($options = null, $data = null)
    {
        parent::__construct($options, $data);

        if ($this->allowedParams === null) {
            $this->allowedParams = [
                'user_id as id', 'email as lolmail'
            ];
        }
    }

    protected function alwaysTrue($field)
    {
        return true;
    }

    public function setData(array $data)
    {
        $this->setDataSafe($data);
    }

    public function getData()
    {
        return $this->getGenericData();
    }
}

class TestModel3 extends AbstractModel
{
    public function __construct()
    {
        $this->allowedParams = [
            '[config]'
        ];
    }

    public function setData(array $data)
    {
        $this->setDataSafe($data);
    }

    public function getData()
    {
        return $this->getGenericData();
    }
}

class AbstractModelTest extends \PHPUnit_Framework_TestCase
{
    protected $abstractModel;
    protected $reflectedAbstractModel;

    public function setup()
    {
        $this->abstractModel = new TestModel();
        $this->reflectedAbstractModel = new \ReflectionClass('Ellie\Model\TestModel');
    }

    /**
     * @group setDataLimitParams
     */
    public function test_setData_limitParams()
    {
        $inputData = [
            'email' => 'lolmail',
            'user_id' => 'haha',
            'password' => 'nope'
        ];
        $model = new TestModel2([
            'allowedParams' => [
                'email'
            ]
        ], $inputData);


        $model->setData($inputData);

        $outputData = [
            'email' => 'lolmail'
        ];

        $this->assertEquals($outputData, $model->getData());
    }

    /**
     * @group testSetDataGlobalFilter
     */
    public function test_setData_globalFilter()
    {
        $inputData = [
            'email' => 'lolm<script></script>ail',
            'user_id' => 'haha',
            'password' => 'nope'
        ];
        $model = new TestModel2();

        $filters = [
            '@all' => [new StripTagsFilter()]
        ];

        $model->setFilters($filters);

        $model->setData($inputData);

        $data = $model->getData();

        $this->assertEquals([
            'id' => 'haha',
            'lolmail' => 'lolmail'
        ], $data);
    }

    /**
     * @group testSetDataGlobalValidator
     * @expectedException Exception
     * @expectedExceptionMessage email did not pass Ellie\Model\Validator\StringValidator validation
     *
     */
    public function test_setData_globalValidator()
    {
        $inputData = [
            'email' => 23,
            'user_id' => 'haha',
            'password' => 'nope'
        ];
        $model = new TestModel2();

        $validators = [
            '@all' => [new StringValidator()]
        ];

        $model->setValidators($validators);

        $model->setData($inputData);
    }

    /**
     * @group testSetDataGlobalValidator
     *
     */
    public function test_setData_globalValidator_customValidator()
    {
        $inputData = [
            'email' => 23,
            'user_id' => 'haha',
            'password' => 'nope'
        ];
        $model = new TestModel2();

        $validators = [
            '@all' => ['alwaysTrue']
        ];

        $model->setValidators($validators);

        $model->setData($inputData);

        $this->assertEquals([
            'lolmail' => 23,
            'id' => 'haha'
        ], $model->getData());
    }

    /**
     * @group testSetDataGroupFilter
     */
    public function test_setData_groupFilter()
    {
        $inputData = [
            'email' => 'lolm<script></script>ail',
            'user_id' => 'haha',
            'password' => 'nope'
        ];
        $model = new TestModel2();

        $filters = [
            '@group' => [
                [
                    'fields' => ['user_id'],
                    'filters' => [new StripTagsFilter()]
                ],
                [
                    'fields' => ['email'],
                    'filters' => [new StripTagsFilter()]
                ]
            ],
            'user_id' => [new StripTagsFilter()]
        ];

        $model->setFilters($filters);

        $model->setData($inputData);

        $data = $model->getData();

        $this->assertEquals([
            'id' => 'haha',
            'lolmail' => 'lolmail'
        ], $data);

//        $this->markTestIncomplete("Finish it!");
    }

    /**
     * @group testSetDataGroupValidator
     * @expectedException Exception
     * @expectedExceptionMessage email did not pass Ellie\Model\Validator\StringValidator validation
     */
    public function test_setData_groupValidator()
    {
        $inputData = [
            'email' => 454,
            'user_id' => 'haha',
            'password' => 'nope'
        ];
        $model = new TestModel2();

        $validators = [
            '@group' => [
                [
                    'fields' => ['user_id'],
                    'validators' => [new Validator\StringValidator()]
                ],
                [
                    'fields' => ['email'],
                    'validators' => [new Validator\StringValidator()]
                ]
            ],
            'user_id' => [new Validator\StringValidator()]
        ];

        $model->setValidators($validators);

        $model->setData($inputData);

        $data = $model->getData();
    }

    /**
     * @group testGetDataWithAlias
     */
    public function test_getData_withAliases_noFilters()
    {
        $model = new TestModel2();

        $inputData = [
            'user_id' => 2,
            'email' => 'fdjhasidhweiodfhweoifhweo'
        ];

        $model->setData($inputData);

        $outputData = [
            'id' => 2,
            'lolmail' => 'fdjhasidhweiodfhweoifhweo'
        ];

        $this->assertEquals($outputData, $model->getData());
    }

    /**
     * @group testSetDataWithAlias
     */
    public function test_setData_withAliases_noFilters()
    {
        $model = new TestModel2();

        $inputData = [
            'id' => 2,
            'lolmail' => 'fdjhasidhweiodfhweoifhweo'
        ];

        $model->setData($inputData);

        $outputData = [
            'id' => 2,
            'lolmail' => 'fdjhasidhweiodfhweoifhweo'
        ];

        $this->assertEquals($outputData, $model->getData());
    }

    public function test_setFilters()
    {
        $filters = [
            'email' => [
                new StringTrimFilter(),
                new StripTagsFilter()
            ]
        ];

        $filtersVar = $this->reflectedAbstractModel->getProperty('filters');
        $filtersVar->setAccessible(true);

        $this->abstractModel->setFilters($filters);
        $setFilters = $filtersVar->getValue($this->abstractModel);

        $this->assertEquals($filters, $setFilters);
    }

    public function test_setFilter()
    {
        $filters = [
            'email' => [
                new StringTrimFilter(),
                new StripTagsFilter()
            ]
        ];

        $this->abstractModel->setFilters($filters);

        $filtersVar = $this->reflectedAbstractModel->getProperty('filters');
        $filtersVar->setAccessible(true);

        $newFilters = [
            new StringTrimFilter()
        ];

        $this->abstractModel->setFilter('email', $newFilters);

        $setFilters = $filtersVar->getValue($this->abstractModel);
        $this->assertEquals(['email' => $newFilters], $setFilters);
    }

    public function test_addFilters()
    {
        $filters = [
            'email' => [
                new StringTrimFilter(),
                new StripTagsFilter()
            ]
        ];

        $this->abstractModel->setFilters($filters);

        $filtersVar = $this->reflectedAbstractModel->getProperty('filters');
        $filtersVar->setAccessible(true);

        $newFilters = [
            new TestFilter()
        ];

        $this->abstractModel->addFilters('email', $newFilters);

        $setFilters = $filtersVar->getValue($this->abstractModel);

        $expectedResult['email'] = array_merge($filters['email'], $newFilters);
        $this->assertEquals($expectedResult, $setFilters);
    }

    public function test_setValidators()
    {
        $validators = [
            'email' => [
                new StringValidator(),
                new WhitelistValidator(['test'])
            ]
        ];

        $validatorsVar = $this->reflectedAbstractModel->getProperty('validators');
        $validatorsVar->setAccessible(true);

        $this->abstractModel->setValidators($validators);
        $setValidators = $validatorsVar->getValue($this->abstractModel);

        $this->assertEquals($validators, $setValidators);
    }

    public function test_setValidator()
    {
        $validators = [
            'email' => [
                new StringValidator(),
                new WhitelistValidator(['test'])
            ]
        ];

        $this->abstractModel->setValidators($validators);

        $validatorsVar = $this->reflectedAbstractModel->getProperty('validators');
        $validatorsVar->setAccessible(true);

        $newValidators = [
            new MacAddressValidator(),
            new WhitelistValidator(['test2'])
        ];

        $this->abstractModel->setValidator('email', $newValidators);

        $setValidators = $validatorsVar->getValue($this->abstractModel);

        $this->assertEquals(['email' => $newValidators], $setValidators);
    }

    public function test_addValidator()
    {
        $validators = [
            'email' => [
                new MacAddressValidator(),
                new StringValidator()
            ]
        ];

        $this->abstractModel->setValidators($validators);

        $validatorsVar = $this->reflectedAbstractModel->getProperty('validators');
        $validatorsVar->setAccessible(true);

        $newValidators = [
            new TestFilter()
        ];

        $this->abstractModel->addValidators('email', $newValidators);

        $setValidators = $validatorsVar->getValue($this->abstractModel);

        $expectedValidators['email'] = array_merge($validators['email'], $newValidators);

        $this->assertEquals($expectedValidators, $setValidators);
    }

    /**
     * @group testModelArrayAsValue
     */
    public function test_setData_arrayAsValue()
    {
        $data = [
            'config' => [
                'da', 'fef', '34'
            ]
        ];

        $model = new TestModel3();

        $model->setData($data);

        $returnData = $model->getData();

        $this->assertEquals($data, $returnData);
    }

    /**
     * @group testModelAddData
     */
    public function test_addData()
    {
        $inputData = [
            'user_id' => '14'
        ];

        $addData = [
            'email' => 'jdhakid@djasikdj.com'
        ];

        $model = new TestModel2();
        $model->setData($inputData);

        $model->setData($addData);

        $expectedData = [
            'id' => '14',
            'lolmail' => 'jdhakid@djasikdj.com'
        ];

        $this->assertEquals($expectedData, $model->getData());
    }
}
