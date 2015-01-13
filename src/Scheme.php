<?php
namespace samsonphp\config;

use samson\core\Event;

/**
 * Generic SamsonPHP core configuration system
 * @author Vitaly Egorov <egorov@samsonos.com>
 * @copyright 2014 SamsonOS
 */
class Scheme
{
    /** Global/Default scheme marker */
    const BASE = 'global';

    /** Entity configuration file pattern */
    const ENTITY_PATTERN = '*Config.php';

    /** @var array Collection of file path -> class loaded */
    protected static $classes = array();

    /** @var string Current configuration environment */
    protected $environment;

    /** @var array Configuration folder path array */
    protected $path = array();

    /** @var array Collection of module identifier => configurator class */
    public $entities = array();

    /**
     * Create configuration instance.
     *
     * All module configurators must be stored within configuration base path,
     * by default this is stored in __SAMSON_CONFIG_PATH constant.
     *
     * Every environment configuration must be stored in sub-folder with the name of this
     * environment within base configuration folder.
     *
     * Configurators located at base root configuration folder considered as generic
     * module configurators.
     *
     * @param string $path    Base path to configuration root folder
     * @param string $environment Configuration environment name
     */
    public function __construct($path, $environment)
    {
        // Store current configuration environment
        $this->environment = $environment;

        // Check scheme folder existence
        if (file_exists($path)) {
            // Load scheme entities
            $this->load($path);
        }
    }

    /**
     * Load entity configuration classes for this scheme.
     * Function scans all required classes and matches them by
     * specified scheme path.
     */
    public function load($path = null)
    {
        // Build path to environment configuration folder
        $this->path[] = $path;

        // Iterate all loaded classes matching class name pattern
        foreach (preg_grep(Entity::CLASS_PATTERN, get_declared_classes()) as $class) {
            // If this is a entity configuration class ancestor
            if (in_array(__NAMESPACE__.'\Entity', class_parents($class))) {
                // Get class reflection object
                $reflector = new \ReflectionClass($class);

                // If path to class file is in scheme paths collection
                if (in_array(dirname($reflector->getFileName()), $this->path)) {
                    // Store module identifier - entity configuration object
                    $this->entities[$this->identifier($class)] = new $class();
                }
            }
        }
    }

    /**
     * Convert entity configuration or object class name to identifier
     * @param string $class Entity configuration class name
     * @return string Entity real class name
     */
    public function identifier($class)
    {
        // If namespace is present
        if (($classNamePos = strrpos($class, '\\')) !== false) {
            $class = substr($class, $classNamePos+1);
        }

        // Remove only last occurrence of pattern
        return preg_replace(Entity::CLASS_PATTERN, '', strtolower($class));
    }

    /**
     * Retrieve entity configuration by identifier.
     * If entity configuration not found null will be
     * returned.
     *
     * @param string $identifier Entity identifier
     * @param mixed $entity Return found entity configuration pointer
     * @return Entity|boolean Entity configuration pointer or bool
     */
    public function & entity($identifier, & $entity = null)
    {
        // Convert identifier of entity configuration name is passed
        $pointer = & $this->entities[$this->identifier($identifier)];

        // PHP bugs =) We cannot assign referenced value pointer to array element
        $entity = $pointer;

        // Prepare return value - pointer or bool
        $return = (func_num_args() == 2) ? isset($pointer) : $pointer;

        // Also PHP bugs - we cannot return reference from ternary operator
        return $return;
    }

    /**
     * Perform object configuration with specific entity
     * @param mixed $object Object instance pointer
     * @param Entity $entity Entity configuration pointer
     * @param mixed $params Collection of parameters
     * @return bool True if everything went fine, otherwise false
     */
    protected function handleConfigure(& $object, Entity & $entity, $params = null)
    {
        // If this class knows how to configure it self
        if (method_exists($object, 'configure')) {
            // Call custom configuration implementation
            return $object->configure($entity);
        } else { // Generic logic - implement entity configuration to object
            return $entity->configure($object, $params);
        }
    }

    /**
     * Configure object with configuration entity parameters.
     *
     * If now $identifier is passed - automatic identifier generation
     * will take place from object class name.
     *
     * If additional parameters key=>value collection is passed, they
     * will be used to configure object instead of entity configuration
     * class.
     *
     * @param mixed $object Object for configuration with entity
     * @param string $identifier Configuration entity name
     * @param array|null $params Collection of configuration parameters
     *
     * @return boolean True if we have successfully configured object
     */
    public function configure(& $object, $identifier = null, $params = null)
    {
        /** @var Entity $pointer Pointer to entity instance */
        $pointer = null;

        // If we have found this entity configuration, If no entity identifier is passed get it from object class
        if ($this->entity(isset($identifier) ? $identifier : get_class($object), $pointer)) {
            return $this->handleConfigure($object, $pointer, $params);
        } else { // Signal error
            Event::fire(
                'error',
                array(
                    $this,
                    'Cannot configure entity[' . $identifier . '] - Entity configuration does not exists'
                )
            );

            // We have failed
            return false;
        }
    }
}
