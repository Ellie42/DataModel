# DataModel [![Build Status](https://travis-ci.org/Ellie42/DataModel.svg)](https://travis-ci.org/Ellie42/DataModel)
This data model is used as a container for arbitrary data to assure that all data passed through this model is completely safe and structured correctly.


## Usage
To use the model system you should first create a class that extends `Ellie\Model\AbstractModel`
and implement the `setData()` and `getData()` functions.

#### Set and Get Example 
Setting and getting data can be done however you want but the functions `setDataSafe()` and `getGenericData()` have been created for this purpose
``` 
public function setData(array $data){
    //You can modify the data being set in here before saving it in the model
    
    //After any modifications you should call the setDataSafe() function with the data
    //this will run all validators and filters on only the allowed fields
    $this->setDataSafe($data);
}

public function getData(){
    //You can do anything here but the function getGenericData() has been provided.
    //This will create an array containing only the fields with non-null values
    //getGenericData(true) will return the array formatted.
    return $this->getGenericData(true);
}
```

#### Configuration
You can setup the configuration for allowed parameters and validators/filters in the construct.
##### Example Configuration
```
public function __construct(){
    // $allowedParams should be an array with field names to allow.
    // when using setDataSafe() any fields in the data not allowed will be ignored.
    $this->allowedParams = ["name","email","password"];
    
    // $validators should contain the field names you want to be validated
    $this->validators = [
        "name" => [new StringValidator()],
        "email" => [new EmailValidator()],
        
        // If the index is @all then the validators inside will be run on every 
        // field BEFORE the field specific validators
        "@all" => [new LengthValidator(5,10)],
        
        // You can also use @group to run validators on only these fields
        "@group" => [
            "fields" => ["password","name"],
            "validators" => [new LengthValidator(6,10)]
        ]
    ];
    
    // $filters contains an array exactly like the $validators array
    $this->filters = [];
    
    // You can set any params as required and when calling setDataSafe() 
    // if the required field isn't provided then it will throw an exception
    $this->setRequired("email","password",...);
}
````
##### AllowedParams
If the parameter is allowed to be an array then you should surround the field name in []
###### Ex
````
    $this->allowedParams = [
        "name","password","[emails]"
    ];
````
##### Validators
A validator is any class that contains the function `isValid($field)` this function should return a bool.

##### Filters
A filter is any class that contains the function `filter($field)` and should return a filtered version of the field.

---
You can also use a function name string as a validator or filter.
###### Ex
    $validators = [
        "name" => ['customValidationFunction']
    ];
If the function `customValidationFunction()` exists within the model then it will run it.

## Formatting
##### Allowed Params Aliases
The allowed params array also supports basic aliasing.
In the `$allowedParams` array you can put `"$fieldName as name"` and it will be set in the `$data` array with the alias.

Aliases will only be used if getting formatted data.

##### Data format
Using `$dataFormatConfig` you can force the data to be returned with a defined structure.
In this config you can set custom index names that will be used in place of the internal `$data` index.
You can use any of the values in `$data` by putting `"@fieldName"` as the value.

Any fields not defined in this configuration will be added to the first level of the array.

###### Ex
````
        $this->dataFormatConfig = [
            "id" => "@user_id",
            "details" => [
                "email" => "@email_address"
            ]
        ];
````

When attempting to `setDataSafe()` you should make sure the input data is formatted exactly the same as the format configuration otherwise the aliases set in the configuration will not work.
Setting the data using the internal index names will still work if you do not nest them.

````
    //If the data config is 
    $this->dataFormatConfig = [
        "id" => "@user_id",
        "details" => [
            "email" => "@email_address"
        ]
    ];
````
You can return the data as either
````
$data = [
    "id" => "1",
    "details" => [
        "email" => "email@1.com"
    ]
];
````
or as 
````
$data = [
    "user_id" => "1",
    "email_address" => "email@1.com"
]
````

# Disclaimer

I probably missed a few things here but there is most of it :)
