<?php
/**
 * Created by PhpStorm.
 * User: egorov
 * Date: 06.01.2015
 * Time: 16:59
 */
namespace samsonphp\config;

use samson\core\Event;

/**
 * Configuration scheme manager
 * @package samsonos\config
 */
class Manager
{
    /** @var array Collection of file path -> class loaded */
    protected static $classes = array();

    /** @var array Collection of files in current init */
    protected static $files = array();

    /**
     * Load all entity configuration classes in specific location.
     *
     * All module files must match Entity::FILE_PATTERN to be
     * loaded.
     *
     * @param string $path Path for importing classes
     */
    public static function import($path)
    {
        // Read all files in path
        self::$files = glob($path .'/'. Entity::FILE_PATTERN);

        // Fill array of entity files with keys of file names without extension
        foreach (self::$files as $file) {
            $file = realpath($file);
            // If we have not already loaded this class before with other schemes
            if (!isset(self::$classes[$file])) {
                // Store loaded classes
                $classes = get_declared_classes();

                // Load entity configuration file
                require_once($file);

                // Get loaded class - store class to static collection
                $classes = array_diff(get_declared_classes(), $classes);
                self::$classes[$file] = end($classes);
            }
        }
    }

    /** @var Scheme[] Collection of available schemes */
    public $schemes = array();

    /** @var  Scheme Pointer to current active configuration scheme */
    protected $active;

    /**
     * Initialize all configuration logic
     */
    public function __construct()
    {
        // Add class auto loader
        spl_autoload_register(array($this, 'autoload'));

        // Subscribe active configuration scheme to core module configure event
        Event::subscribe('core.environment.change', array($this, 'change'));
    }

    /**
     * Configuration class autoloader.
     * It helps resolve single configuration entity configuration dependencies
     * @param string $className Class name for loading
     */
    public function autoload($className)
    {
        // If namespace is present
        $class = substr($className, strrpos($className, '\\')+1);

        // Try to find class file by class name without namespace
        $matches = preg_grep('/'.$class.'/i', self::$files);
        if (sizeof($matches)) {
            require_once(end($matches));
        }
    }

    /**
     * Switch active environment
     * @param string $environment Configuration environment identifier
     */
    public function change($environment = Scheme::BASE)
    {
        // Switch to configuration environment
        $this->active = & $this->schemes[$environment];

        // Subscribe active configuration scheme to core module configure event
        Event::subscribe('core.module.configure', array($this, 'configure'));

        // If we have successfully changed configuration scheme
        if (!isset($this->active)) {
            // Signal error
            Event::fire(
                'error',
                array(
                    $this,
                    'Cannot change configuration scheme to ['.$environment.'] - Configuration scheme does not exists'
                )
            );

            // Set global scheme as active
            $this->active = & $this->schemes[Scheme::BASE];
        }
    }

    /**
     * Initialize all configuration logic
     * @param string $basePath Path to configuration base folder
     */
    public function init($basePath)
    {
        // Create global scheme instance
        $this->create($basePath, Scheme::BASE);

        // Switch to global environment
        $this->change();

        // Read all directories in base configuration path
        foreach (glob($basePath . '*', GLOB_ONLYDIR) as $path) {
            // Create new configuration scheme
            $this->create($path);
        }
    }

    /**
     * Create configuration scheme
     * @param string $path Path to configuration scheme folder
     * @param string $environment Configuration scheme environment identifier
     */
    public function create($path, $environment = null)
    {
        // Import all valid entity configuration classes from path
        self::import($path);

        // If no environment identifier is passed - use it from path
        $environment = !isset($environment) ? basename($path) : $environment;

        // Pointer to a configuration scheme
        $pointer = & $this->schemes[$environment];

        // Check if have NOT already created configuration for this environment
        if (!isset($pointer)) {
            $pointer = new Scheme(realpath($path . '/'), $environment);
        } else { // Load data to existing configuration scheme
            $pointer->load(realpath($path . '/'));
        }
    }

    /**
     * Configure object with current configuration scheme entity parameters.
     *
     * If now $identifier is passed - automatic identifier generation
     * will take place from object class name.
     *
     * If additional parameters key=>value collection is passed, they
     * will be used to configure object instead of entity configuration
     * class.
     *
     * If current configuration scheme has no entity configuration for
     * passed object - global configuration scheme will be used.
     *
     * @param mixed $object Object for configuration with entity
     * @param string $identifier Configuration entity name
     * @param array|null $params Collection of configuration parameters
     */
    public function configure(& $object, $identifier = null, $params = null)
    {
        // Try to configure using current scheme
        if (!$this->active->configure($object, $identifier, $params)) {
            // Get pointer to global scheme
            $base = & $this->schemes[Scheme::BASE];
            if (isset($base) && $base !== $this->active) {
                // Call global scheme for configuration
                $base->configure($object, $identifier, $params);
            }
        }
    }
}
