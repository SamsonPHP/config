<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>
 * on 15.09.14 at 11:01
 */
namespace samsonphp\config;

/**
 * Generic object configuration class
 * @author Vitaly Egorov <egorov@samsonos.com>
 * @copyright 2014 SamsonOS
 */
class Entity
{
    /** Entity configuration file pattern */
    const FILE_PATTERN = '*Config.php';

    /** Entity configuration class name pattern */
    const CLASS_PATTERN = '/Config$/i';

    /**
     * Configure object with configuration entity parameters.
     * If additional parameters key=>value collection is passed, they
     * will be used to configure object.
     *
     * @param mixed $object Object for configuration with entity
     * @param array|null $params Collection of configuration parameters
     *
     * @return boolean True if everything is fine, otherwise false
     */
    public function configure(& $object, $params = null)
    {
        // Use entity params if external is not passed, iterate all children class variables
        foreach (isset($params) ? $params : get_object_vars($this) as $var => $value) {
            // If module has configured property defined
            if (property_exists($object, $var)) {
                // Set module variable value
                $object->$var = $value;
            }
        }

        return true;
    }
}
