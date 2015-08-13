<?php
/**
 * Created by PhpStorm.
 * User: sophie
 * Date: 11/06/15
 * Time: 11:46
 */

namespace Ellie\Model;


interface ModelCollectionInterface
{
    /**
     * @param array $models
     */
    public function __construct(array $models);
}
