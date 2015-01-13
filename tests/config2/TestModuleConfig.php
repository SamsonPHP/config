<?php
/**
 * Created by PhpStorm.
 * User: egorov
 * Date: 05.01.2015
 * Time: 22:29
 */
namespace project2;

class TestModuleConfig extends \samsonphp\config\Entity
{
    public $parameterString = '77';
    public $parameterInt = 77;
    public $parameterArray = array('global2'=>77);
}
