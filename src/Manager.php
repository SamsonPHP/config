<?php
/**
 * Created by PhpStorm.
 * User: egorov
 * Date: 06.01.2015
 * Time: 16:59
 */
namespace samsonos\config;

use samson\core\Event;

/**
 * Configuration scheme manager
 * @package samsonos\config
 */
class Manager
{
    /** @var Scheme[] Collection of available schemes */
    public $schemes = array();

    /** @var  Scheme Pointer to current active configuration scheme */
    protected $active;

    /**
     * Initialize all configuration logic
     */
    public function __construct()
    {
        // Subscribe active configuration scheme to core module configure event
        Event::subscribe('core.environment.change', array($this, 'change'));
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
        Event::subscribe('core.module.configure', array($this->active, 'configure'));

        if (!isset($this->active)) {
            // Signal error
        }
    }

    /**
     * Initialize all configuration logic
     * @param string $basePath Path to configuration base folder
     */
    public function init($basePath)
    {
        // Create global scheme instance if have not done it already
        if (!isset($this->schemes[Scheme::BASE])) {
            $this->create($basePath, Scheme::BASE);

            // Set current as active
            $this->change();
        }

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
        // If no environment identifier is passed - use it from path
        $environment = !isset($environment) ? basename($path) : $environment;

        // Pointer to a configuration scheme
        $pointer = & $this->schemes[$environment];

        // Check if have NOT already created configuration for this environment
        if (!isset($pointer)) {
            $pointer = new Scheme($path . '/', $environment);
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