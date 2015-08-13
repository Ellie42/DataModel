<?php
/**
 * Created by PhpStorm.
 * User: sophie
 * Date: 01/07/15
 * Time: 09:21
 */

namespace Ellie\Model;


abstract class AbstractCollection
{
    protected $models = [];
    protected $model;

    protected function setDataSafe($modelsArray)
    {
        foreach ($modelsArray as $model) {
            if (get_class($model) != $this->model) {
                throw new \Exception("Model is not an instance of $this->model");
            }

            $this->models[] = $model;
        }
    }
}
