<?php
namespace tests;

use samsonphp\config\Manager;
use samsonphp\config\Scheme;

/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>
 * on 04.08.14 at 16:42
 */
class SchemeTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \samsonos\config\Manager */
    protected $manager;

    /** @var  Scheme */
    protected $globalScheme;

    /** Tests init */
    public function setUp()
    {
        $this->manager = new Manager();
        $this->manager->init(__DIR__ . '/config/');

        // Get default scheme
        $this->globalScheme = & $this->manager->schemes[Scheme::BASE];

        // Import object for testing
        require_once 'TestModule.php';
        // Import object for testing
        require_once 'TestConfigurableModule.php';
    }

    /** Test Init */
    public function testInit()
    {
        $this->assertArrayHasKey('global', $this->manager->schemes);
        $this->assertArrayHasKey('deploy', $this->manager->schemes);
        $this->assertArrayHasKey('dev', $this->manager->schemes);
    }

    /** Test implement*/
    public function testImplementGlobal()
    {
        // Create object for configuration
        $object = new TestModule();

        // Configure object
        $this->manager->configure($object, 'testmodule');

        $this->assertEquals('1', $object->parameterInt);
        $this->assertEquals('1', $object->parameterString);
        $this->assertArrayHasKey('global', $object->parameterArray);
    }

    /** Test implement*/
    public function testImplementDev()
    {
        // Create object for configuration
        $object = new TestModule();

        // Switch to deploy configuration scheme
        $this->manager->change('dev');

        // Configure object
        $this->manager->configure($object, 'testmodule');

        $this->assertEquals('2', $object->parameterInt);
        $this->assertEquals('2', $object->parameterString);
        $this->assertArrayHasKey('dev', $object->parameterArray);
    }

    /** Test implement*/
    public function testImplementInherit()
    {
        // Create object for configuration
        $object = new TestModule();

        // Switch to deploy configuration scheme
        $this->manager->change('inherit');

        // Configure object
        $this->manager->configure($object, 'testmodule');

        $this->assertEquals('3', $object->parameterInt);
        $this->assertEquals('2', $object->parameterString);
        $this->assertArrayHasKey('inherit', $object->parameterArray);
    }

    /** Test implement not found - use global */
    public function testNotFoundUseGlobal()
    {
        // Create object for configuration
        $object = new TestModule();

        // Switch to unreal configuration scheme
        $this->manager->change('notreal');

        // Switch to deploy configuration scheme
        $this->manager->change('deploy');

        // Configure object
        $this->manager->configure($object, 'testmodule');

        $this->assertEquals('1', $object->parameterInt);
        $this->assertEquals('1', $object->parameterString);
        $this->assertArrayHasKey('global', $object->parameterArray);
    }

    /** Test custom configure implementation */
    public function testConfigureImplementation()
    {
        $object = new TestConfigurableModule();

        // Switch to global configuration scheme
        $this->manager->change();

        // Configure object
        $this->manager->configure($object);

        $this->assertNotEquals('1', $object->parameterInt);
        $this->assertNotEquals('1', $object->parameterString);
        $this->assertArrayHasKey('configurable', $object->parameterArray);
    }

    public function testOtherBasePath()
    {
        // Create object for configuration
        $object = new TestModule();

        // Init manager on other project location/namespace
        $this->manager->init(__DIR__.'/config2/');

        // Configure object
        $this->manager->configure($object);

        $this->assertEquals('77', $object->parameterInt);
        $this->assertEquals('77', $object->parameterString);
        $this->assertArrayHasKey('global2', $object->parameterArray);
    }
}
