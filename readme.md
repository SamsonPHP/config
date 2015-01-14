# SamsonPHP Configuration system

[![Latest Stable Version](https://poser.pugx.org/samsonphp/config/v/stable.svg)](https://packagist.org/packages/samsonphp/config) 
[![Build Status](https://travis-ci.org/SamsonPHP/config.svg?branch=1.0.11)](https://travis-ci.org/SamsonPHP/config)
[![Code Coverage](https://scrutinizer-ci.com/g/samsonphp/config/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/samsonphp/config/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/samsonphp/config/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/samsonphp/config/?branch=master)
[![Stories in Ready](https://badge.waffle.io/samsonphp/config.png?label=ready&title=Ready)](https://waffle.io/samsonphp/config)
[![Total Downloads](https://poser.pugx.org/samsonphp/config/downloads.svg)](https://packagist.org/packages/samsonphp/config)

OOP based configuration system. This approach uses all abilities of PHP OOP for creating configurations based on classes.  

## Configuration scheme
Your project can have any amount of possible configurations, which is usually used for different environments, such as *development, test, deploy, production* stages,
for this purposes we have created ```Scheme```, each of them corresponds to specific environment. In practice you should have _base configuration folder_,
by default it is located at ```app/config``` folder. 

### Global configuration 
In root of your ```app/config``` folder you should create your default [entity configuration classes](#entity-configuration). If no current configuration environment would be specified, this
entity configuration classes would be used automatically. 
> IMPORTANT! If you have specified some configuration environment(for example ```production```) but you do not have entity configuration class for some module/object in it - Global
configuration entity will be used for it instead if present.

### Creating configuration environment
To create new configuration environment you should create new folder in your _base configuration folder_(by default it located at ```app/config```), for example we want to create ```production``` environment, new folder path would be: ```app/config/production/```. And all entity configuration classes there would correspond to your ```production``` configuration scheme, which will be created automatically.

## Entity configuration
To configure your project modules\objects  - you should use classes, for correct finding this classes among others, we force you to extend our base entity configuration class ```Entity``` :

```php
namespace project;

class entityIDConfig extends samsonphp\config\Entity
{    
}
```

Your entity configuration class name should meet next logic: ```[EntityIdentifier]Config```:
* ```EntityIdentifier``` - configured module/object identifier  
* All entity configuration class names must end with ```Config```

Your entity configuration class namespace should meet next logic:
* Every class has to have any namespace defined(by default your project name)
* Global entity configuration classes(located at [base configuration folder](#configuration-scheme)), should
have name space defined in previous list item.
* Other environments entity configuration classes (located at [other inner folders](#creating-configuration-environment)),
should have namespace: ```[ParentNamespace]\[EnvironmentName]```, if we extend our previous entity configuration example, but now for ```production``` environment:

```php
namespace project\production;

class entityIDConfig extends samsonphp\config\Entity
{
    public $parameter = 'value';
}
```

> IMPORTANT! As we use PSR-* standard - Class name must match file name

### Possible configuration parameters
The main beauty of OOP configuration approach is that you can specify any possible  by PHP values as predefined class field values, the main
limitation is that they have to be ```public``` accessible:

```php
namespace project;

class entityIDConfig extends samsonphp\config\Entity
{    
    public $stringParameter = 'I am a string';
    public $integerParameter = 1123;
    public $arrayParameter = array('1', 123);
}
```

### Prohibited operations  
Unfortunately you cannot predefine calculations and string concatenations(and perform other operations) in class field values, but you can 
always perform it in ```__construct``` method. When [configuration scheme](#configuration-scheme) is created, for every environment all 
[entity configuration](#entity-configuration) instances are being created - so their ```__construct``` method is performed, where you
can make any possible operation.

```php
namespace project;

class entityIDConfig extends samsonphp\config\Entity
{    
    public $stringParameter = 'I am a string';
    public $integerParameter = 1123;
    public $arrayParameter = array('1', 123);
    public $complicatedParameter;
    
    public function __construct()
    {
        // Fill your parameter with what you want
        $this->complicatedParameter = $this->stringParameter . dirname(__DIR__); 
    }
}
```

### Extending existing configurations
Also extending parent entity configuration is available, if want to add just some little changes to some entity configuration we can just extend it:
```php
// Mention - class name is the same, only namespace is different
namespace project\other;

// We are extending global configuration from previous example
class entityIDConfig extends project\entityIDConfig
{
  // We overload only this parameter, as we want all to be the same as parent
  public $integerParameter = 2222;
}
```

## Configuration manager
All project modules/objects configuration is processed via configuration manager ```samsonphp\config\Manager```, you must create instance of it, you must specify
only one parameter ```Path to base configuration folder```, for initialization of manager you should use ```init()``` method, this methods are specially separated
to give ability to load entity configuration classes as you wish.  
```php 
$manager = new \samsonphp\config\Manager('.../path/to/base/folder');
$manager->init();
```

### Loading entity configuration classes
We have created special static method for loading entity configuration ```samsonphp\config\Manager::import($path)```, this method is used internally when configuration scheme is being created or loaded, for this tasks you should use ```init(path)```:
```php 
$manager = new \samsonphp\config\Manager();
$manager->init('.../path/to/base/folder');
```

### Switching configuration
If you want to change current active manager configuration use should use ```change($identifier)``` method:
* ```$identifier``` - Environment identifier(Configuration scheme identifier)
* 
### Load schemes from different locations
If you have separate base configuration folders with different configurations, for example one project inside another, and inner project want to take some configurations from parent object, you can load configuration from other location:
```php 
$manager = new \samsonphp\config\Manager();
$manager->init('.../path/to/base/folder');
$manager->init('.../path/to/OTHER/folder');
```
> IMPORTANT! Entity configuration for same environments in different locations are ovveritten.

# Event system
This module has only one external dependency - [Event system](https://github.com/samsonos/php_event) and all interoperabily with outside world must me done using this approach. 
Currently module does not firing any events, but has 2 subscriptions:
* ```core.environment.change``` - When environment is changing outside
* ```core.module.configure``` - When some object is being configured


