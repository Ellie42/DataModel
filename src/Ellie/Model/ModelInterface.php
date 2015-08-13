<?php
/**
 * Created by PhpStorm.
 * User: sophie
 * Date: 11/06/15
 * Time: 10:58
 */

namespace Ellie\Model;


interface ModelInterface {

    /**
     * Must receive an array from mysql search and use it to set all properties of the model
     * @param array
     */
    public function setData(array $data);

    /**
     * Must return an array of all the intended public properties of the model
     * @return array
     */
    public function getData();
}
