<?php
/**
 * Created by PhpStorm.
 * User: egorov
 * Date: 05.01.2015
 * Time: 22:29
 */
namespace project;

class TestConfigurableModuleConfig extends \samsonphp\config\Entity
{
    public $parameterString = '1';
    public $parameterInt = 1;
    public $parameterArray = array('global'=>1, '1');
}
