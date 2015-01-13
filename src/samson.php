<?php
/**
 * SamsonPHP initialization class
 */

//[PHPCOMPRESSOR(remove,start)]
// Subscribe to core started event to load all possible module configurations
\samsonphp\event\Event::subscribe('core.configure', array(new \samsonos\config\Manager(), 'init'));
//[PHPCOMPRESSOR(remove,end)]
