<?php
/**
 * Created by PhpStorm.
 * User: egorov
 * Date: 05.01.2015
 * Time: 22:29
 */
namespace project\deploy;

class OtherModuleConfig extends \samsonphp\config\Entity
{
    public $parameterString = '2';
    public $parameterInt = 2;
    public $parameterArray = array('dev' => 2, '2');
}
