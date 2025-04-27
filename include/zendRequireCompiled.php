<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Loader;

use Traversable;

if (false) {
    return;
}

/**
 * Defines an interface for classes that may register with the spl_autoload
 * registry
 */
interface SplAutoloader
{
    /**
     * Constructor
     *
     * Allow configuration of the autoloader via the constructor.
     *
     * @param  null|array|Traversable $options
     */
    public function __construct($options = null);

    /**
     * Configure the autoloader
     *
     * In most cases, $options should be either an associative array or
     * Traversable object.
     *
     * @param  array|Traversable $options
     * @return SplAutoloader
     */
    public function setOptions($options);

    /**
     * Autoload a class
     *
     * @param   $class
     * @return  mixed
     *          False [if unable to load $class]
     *          get_class($class) [if $class is successfully loaded]
     */
    public function autoload($class);

    /**
     * Register the autoloader with spl_autoload registry
     *
     * Typically, the body of this will simply be:
     * <code>
     * spl_autoload_register(array($this, 'autoload'));
     * </code>
     *
     * @return void
     */
    public function register();
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Loader;

/**
 * Short name locator interface
 */
interface ShortNameLocator
{
    /**
     * Whether or not a Helper by a specific name
     *
     * @param  string $name
     * @return bool
     */
    public function isLoaded($name);

    /**
     * Return full class name for a named helper
     *
     * @param  string $name
     * @return string
     */
    public function getClassName($name);

    /**
     * Load a helper via the name provided
     *
     * @param  string $name
     * @return string
     */
    public function load($name);
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Loader;

use IteratorAggregate;
use Traversable;

/**
 * Plugin class locator interface
 */
interface PluginClassLocator extends ShortNameLocator, IteratorAggregate
{
    /**
     * Register a class to a given short name
     *
     * @param  string $shortName
     * @param  string $className
     * @return PluginClassLocator
     */
    public function registerPlugin($shortName, $className);

    /**
     * Unregister a short name lookup
     *
     * @param  mixed $shortName
     * @return void
     */
    public function unregisterPlugin($shortName);

    /**
     * Get a list of all registered plugins
     *
     * @return array|Traversable
     */
    public function getRegisteredPlugins();
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Loader;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

/**
 * Plugin class locator interface
 */
class PluginClassLoader implements PluginClassLocator
{
    /**
     * List of plugin name => class name pairs
     * @var array
     */
    protected $plugins = array();

    /**
     * Static map allow global seeding of plugin loader
     * @var array
     */
    protected static $staticMap = array();

    /**
     * Constructor
     *
     * @param  null|array|Traversable $map If provided, seeds the loader with a map
     */
    public function __construct($map = null)
    {
        // Merge in static overrides
        if (!empty(static::$staticMap)) {
            $this->registerPlugins(static::$staticMap);
        }

        // Merge in constructor arguments
        if ($map !== null) {
            $this->registerPlugins($map);
        }
    }

    /**
     * Add a static map of plugins
     *
     * A null value will clear the static map.
     *
     * @param  null|array|Traversable $map
     * @throws Exception\InvalidArgumentException
     * @return void
     */
    public static function addStaticMap($map)
    {
        if (null === $map) {
            static::$staticMap = array();
            return;
        }

        if (!is_array($map) && !$map instanceof Traversable) {
            throw new Exception\InvalidArgumentException('Expects an array or Traversable object');
        }
        foreach ($map as $key => $value) {
            static::$staticMap[$key] = $value;
        }
    }

    /**
     * Register a class to a given short name
     *
     * @param  string $shortName
     * @param  string $className
     * @return PluginClassLoader
     */
    public function registerPlugin($shortName, $className)
    {
        $this->plugins[strtolower($shortName)] = $className;
        return $this;
    }

    /**
     * Register many plugins at once
     *
     * If $map is a string, assumes that the map is the class name of a
     * Traversable object (likely a ShortNameLocator); it will then instantiate
     * this class and use it to register plugins.
     *
     * If $map is an array or Traversable object, it will iterate it to
     * register plugin names/classes.
     *
     * For all other arguments, or if the string $map is not a class or not a
     * Traversable class, an exception will be raised.
     *
     * @param  string|array|Traversable $map
     * @return PluginClassLoader
     * @throws Exception\InvalidArgumentException
     */
    public function registerPlugins($map)
    {
        if (is_string($map)) {
            if (!class_exists($map)) {
                throw new Exception\InvalidArgumentException('Map class provided is invalid');
            }
            $map = new $map;
        }
        if (is_array($map)) {
            $map = new ArrayIterator($map);
        }
        if (!$map instanceof Traversable) {
            throw new Exception\InvalidArgumentException('Map provided is invalid; must be traversable');
        }

        // iterator_apply doesn't work as expected with IteratorAggregate
        if ($map instanceof IteratorAggregate) {
            $map = $map->getIterator();
        }

        foreach ($map as $name => $class) {
            if (is_int($name) || is_numeric($name)) {
                if (!is_object($class) && class_exists($class)) {
                    $class = new $class();
                }

                if ($class instanceof Traversable) {
                    $this->registerPlugins($class);
                    continue;
                }
            }

            $this->registerPlugin($name, $class);
        }

        return $this;
    }

    /**
     * Unregister a short name lookup
     *
     * @param  mixed $shortName
     * @return PluginClassLoader
     */
    public function unregisterPlugin($shortName)
    {
        $lookup = strtolower($shortName);
        if (array_key_exists($lookup, $this->plugins)) {
            unset($this->plugins[$lookup]);
        }
        return $this;
    }

    /**
     * Get a list of all registered plugins
     *
     * @return array|Traversable
     */
    public function getRegisteredPlugins()
    {
        return $this->plugins;
    }

    /**
     * Whether or not a plugin by a specific name has been registered
     *
     * @param  string $name
     * @return bool
     */
    public function isLoaded($name)
    {
        $lookup = strtolower($name);
        return isset($this->plugins[$lookup]);
    }

    /**
     * Return full class name for a named helper
     *
     * @param  string $name
     * @return string|false
     */
    public function getClassName($name)
    {
        return $this->load($name);
    }

    /**
     * Load a helper via the name provided
     *
     * @param  string $name
     * @return string|false
     */
    public function load($name)
    {
        if (!$this->isLoaded($name)) {
            return false;
        }
        return $this->plugins[strtolower($name)];
    }

    /**
     * Defined by IteratorAggregate
     *
     * Returns an instance of ArrayIterator, containing a map of
     * all plugins
     *
     * @return ArrayIterator
     */
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        return new ArrayIterator($this->plugins);
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Config;

use ArrayAccess;
use Countable;
use Iterator;

/**
 * Provides a property based interface to an array.
 * The data are read-only unless $allowModifications is set to true
 * on construction.
 *
 * Implements Countable, Iterator and ArrayAccess
 * to facilitate easy access to the data.
 */
class Config implements Countable, Iterator, ArrayAccess
{
    /**
     * Whether modifications to configuration data are allowed.
     *
     * @var bool
     */
    protected $allowModifications;

    /**
     * Data within the configuration.
     *
     * @var array
     */
    protected $data = array();

    /**
     * Used when unsetting values during iteration to ensure we do not skip
     * the next element.
     *
     * @var bool
     */
    protected $skipNextIteration;

    /**
     * Constructor.
     *
     * Data is read-only unless $allowModifications is set to true
     * on construction.
     *
     * @param  array   $array
     * @param  bool $allowModifications
     */
    public function __construct(array $array, $allowModifications = false)
    {
        $this->allowModifications = (bool) $allowModifications;

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $this->data[$key] = new static($value, $this->allowModifications);
            } else {
                $this->data[$key] = $value;
            }
        }
    }

    /**
     * Retrieve a value and return $default if there is no element set.
     *
     * @param  string $name
     * @param  mixed  $default
     * @return mixed
     */
    public function get($name, $default = null)
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }

        return $default;
    }

    /**
     * Magic function so that $obj->value will work.
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * Set a value in the config.
     *
     * Only allow setting of a property if $allowModifications  was set to true
     * on construction. Otherwise, throw an exception.
     *
     * @param  string $name
     * @param  mixed  $value
     * @return void
     * @throws Exception\RuntimeException
     */
    public function __set($name, $value)
    {
        if ($this->allowModifications) {
            if (is_array($value)) {
                $value = new static($value, true);
            }

            if (null === $name) {
                $this->data[] = $value;
            } else {
                $this->data[$name] = $value;
            }
        } else {
            throw new Exception\RuntimeException('Config is read only');
        }
    }

    /**
     * Deep clone of this instance to ensure that nested Zend\Configs are also
     * cloned.
     *
     * @return void
     */
    public function __clone()
    {
        $array = array();

        foreach ($this->data as $key => $value) {
            if ($value instanceof self) {
                $array[$key] = clone $value;
            } else {
                $array[$key] = $value;
            }
        }

        $this->data = $array;
    }

    /**
     * Return an associative array of the stored data.
     *
     * @return array
     */
    public function toArray()
    {
        $array = array();
        $data  = $this->data;

        /** @var self $value */
        foreach ($data as $key => $value) {
            if ($value instanceof self) {
                $array[$key] = $value->toArray();
            } else {
                $array[$key] = $value;
            }
        }

        return $array;
    }

    /**
     * isset() overloading
     *
     * @param  string $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    /**
     * unset() overloading
     *
     * @param  string $name
     * @return void
     * @throws Exception\InvalidArgumentException
     */
    public function __unset($name)
    {
        if (!$this->allowModifications) {
            throw new Exception\InvalidArgumentException('Config is read only');
        } elseif (isset($this->data[$name])) {
            unset($this->data[$name]);
            $this->skipNextIteration = true;
        }
    }

    /**
     * count(): defined by Countable interface.
     *
     * @see    Countable::count()
     * @return int
     */
    #[\ReturnTypeWillChange]
    public function count()
    {
        return count($this->data);
    }

    /**
     * current(): defined by Iterator interface.
     *
     * @see    Iterator::current()
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        $this->skipNextIteration = false;
        return current($this->data);
    }

    /**
     * key(): defined by Iterator interface.
     *
     * @see    Iterator::key()
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        return key($this->data);
    }

    /**
     * next(): defined by Iterator interface.
     *
     * @see    Iterator::next()
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function next()
    {
        if ($this->skipNextIteration) {
            $this->skipNextIteration = false;
            return;
        }

        next($this->data);
    }

    /**
     * rewind(): defined by Iterator interface.
     *
     * @see    Iterator::rewind()
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function rewind()
    {
        $this->skipNextIteration = false;
        reset($this->data);
    }

    /**
     * valid(): defined by Iterator interface.
     *
     * @see    Iterator::valid()
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function valid()
    {
        return ($this->key() !== null);
    }

    /**
     * offsetExists(): defined by ArrayAccess interface.
     *
     * @see    ArrayAccess::offsetExists()
     * @param  mixed $offset
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return $this->__isset($offset);
    }

    /**
     * offsetGet(): defined by ArrayAccess interface.
     *
     * @see    ArrayAccess::offsetGet()
     * @param  mixed $offset
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    /**
     * offsetSet(): defined by ArrayAccess interface.
     *
     * @see    ArrayAccess::offsetSet()
     * @param  mixed $offset
     * @param  mixed $value
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        $this->__set($offset, $value);
    }

    /**
     * offsetUnset(): defined by ArrayAccess interface.
     *
     * @see    ArrayAccess::offsetUnset()
     * @param  mixed $offset
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        $this->__unset($offset);
    }

    /**
     * Merge another Config with this one.
     *
     * For duplicate keys, the following will be performed:
     * - Nested Configs will be recursively merged.
     * - Items in $merge with INTEGER keys will be appended.
     * - Items in $merge with STRING keys will overwrite current values.
     *
     * @param  Config $merge
     * @return Config
     */
    public function merge(Config $merge)
    {
        /** @var Config $value */
        foreach ($merge as $key => $value) {
            if (array_key_exists($key, $this->data)) {
                if (is_int($key)) {
                    $this->data[] = $value;
                } elseif ($value instanceof self && $this->data[$key] instanceof self) {
                    $this->data[$key]->merge($value);
                } else {
                    if ($value instanceof self) {
                        $this->data[$key] = new static($value->toArray(), $this->allowModifications);
                    } else {
                        $this->data[$key] = $value;
                    }
                }
            } else {
                if ($value instanceof self) {
                    $this->data[$key] = new static($value->toArray(), $this->allowModifications);
                } else {
                    $this->data[$key] = $value;
                }
            }
        }

        return $this;
    }

    /**
     * Prevent any more modifications being made to this instance.
     *
     * Useful after merge() has been used to merge multiple Config objects
     * into one object which should then not be modified again.
     *
     * @return void
     */
    public function setReadOnly()
    {
        $this->allowModifications = false;

        /** @var Config $value */
        foreach ($this->data as $value) {
            if ($value instanceof self) {
                $value->setReadOnly();
            }
        }
    }

    /**
     * Returns whether this Config object is read only or not.
     *
     * @return bool
     */
    public function isReadOnly()
    {
        return !$this->allowModifications;
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Adapter;

/**
 *
 * @property Driver\DriverInterface $driver
 * @property Platform\PlatformInterface $platform
 */
interface AdapterInterface
{
    /**
     * @return Driver\DriverInterface
     */
    public function getDriver();

    /**
     * @return Platform\PlatformInterface
     */
    public function getPlatform();
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Adapter\Profiler;

interface ProfilerAwareInterface
{
    /**
     * @param  ProfilerInterface $profiler
     */
    public function setProfiler(ProfilerInterface $profiler);
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Adapter;

use Zend\Db\ResultSet;

/**
 * @property Driver\DriverInterface $driver
 * @property Platform\PlatformInterface $platform
 */
class Adapter implements AdapterInterface, Profiler\ProfilerAwareInterface
{
    /**
     * Query Mode Constants
     */
    const QUERY_MODE_EXECUTE = 'execute';
    const QUERY_MODE_PREPARE = 'prepare';

    /**
     * Prepare Type Constants
     */
    const PREPARE_TYPE_POSITIONAL = 'positional';
    const PREPARE_TYPE_NAMED = 'named';

    const FUNCTION_FORMAT_PARAMETER_NAME = 'formatParameterName';
    const FUNCTION_QUOTE_IDENTIFIER = 'quoteIdentifier';
    const FUNCTION_QUOTE_VALUE = 'quoteValue';

    const VALUE_QUOTE_SEPARATOR = 'quoteSeparator';

    /**
     * @var Driver\DriverInterface
     */
    protected $driver = null;

    /**
     * @var Platform\PlatformInterface
     */
    protected $platform = null;

    /**
     * @var Profiler\ProfilerInterface
     */
    protected $profiler = null;

    /**
     * @var ResultSet\ResultSetInterface
     */
    protected $queryResultSetPrototype = null;

    /**
     * @var Driver\StatementInterface
     */
    protected $lastPreparedStatement = null;

    /**
     * @param Driver\DriverInterface|array $driver
     * @param Platform\PlatformInterface $platform
     * @param ResultSet\ResultSetInterface $queryResultPrototype
     * @param Profiler\ProfilerInterface $profiler
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($driver, ?Platform\PlatformInterface $platform = null, ?ResultSet\ResultSetInterface $queryResultPrototype = null, ?Profiler\ProfilerInterface $profiler = null)
    {
        // first argument can be an array of parameters
        $parameters = array();

        if (is_array($driver)) {
            $parameters = $driver;
            if ($profiler === null && isset($parameters['profiler'])) {
                $profiler = $this->createProfiler($parameters);
            }
            $driver = $this->createDriver($parameters);
        } elseif (!$driver instanceof Driver\DriverInterface) {
            throw new Exception\InvalidArgumentException(
                'The supplied or instantiated driver object does not implement Zend\Db\Adapter\Driver\DriverInterface'
            );
        }

        $driver->checkEnvironment();
        $this->driver = $driver;

        if ($platform === null) {
            $platform = $this->createPlatform($parameters);
        }

        $this->platform = $platform;
        $this->queryResultSetPrototype = ($queryResultPrototype) ?: new ResultSet\ResultSet();

        if ($profiler) {
            $this->setProfiler($profiler);
        }
    }

    /**
     * @param Profiler\ProfilerInterface $profiler
     * @return Adapter
     */
    public function setProfiler(Profiler\ProfilerInterface $profiler)
    {
        $this->profiler = $profiler;
        if ($this->driver instanceof Profiler\ProfilerAwareInterface) {
            $this->driver->setProfiler($profiler);
        }
        return $this;
    }

    /**
     * @return null|Profiler\ProfilerInterface
     */
    public function getProfiler()
    {
        return $this->profiler;
    }

    /**
     * getDriver()
     *
     * @throws Exception\RuntimeException
     * @return Driver\DriverInterface
     */
    public function getDriver()
    {
        if ($this->driver === null) {
            throw new Exception\RuntimeException('Driver has not been set or configured for this adapter.');
        }
        return $this->driver;
    }

    /**
     * @return Platform\PlatformInterface
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * @return ResultSet\ResultSetInterface
     */
    public function getQueryResultSetPrototype()
    {
        return $this->queryResultSetPrototype;
    }

    public function getCurrentSchema()
    {
        return $this->driver->getConnection()->getCurrentSchema();
    }

    /**
     * query() is a convenience function
     *
     * @param string $sql
     * @param string|array|ParameterContainer $parametersOrQueryMode
     * @param \Zend\Db\ResultSet\ResultSetInterface $resultPrototype
     * @throws Exception\InvalidArgumentException
     * @return Driver\StatementInterface|ResultSet\ResultSet
     */
    public function query($sql, $parametersOrQueryMode = self::QUERY_MODE_PREPARE, ?ResultSet\ResultSetInterface $resultPrototype = null)
    {
        if (is_string($parametersOrQueryMode) && in_array($parametersOrQueryMode, array(self::QUERY_MODE_PREPARE, self::QUERY_MODE_EXECUTE))) {
            $mode = $parametersOrQueryMode;
            $parameters = null;
        } elseif (is_array($parametersOrQueryMode) || $parametersOrQueryMode instanceof ParameterContainer) {
            $mode = self::QUERY_MODE_PREPARE;
            $parameters = $parametersOrQueryMode;
        } else {
            throw new Exception\InvalidArgumentException('Parameter 2 to this method must be a flag, an array, or ParameterContainer');
        }

        if ($mode == self::QUERY_MODE_PREPARE) {
            $this->lastPreparedStatement = null;
            $this->lastPreparedStatement = $this->driver->createStatement($sql);
            $this->lastPreparedStatement->prepare();
            if (is_array($parameters) || $parameters instanceof ParameterContainer) {
                $this->lastPreparedStatement->setParameterContainer((is_array($parameters)) ? new ParameterContainer($parameters) : $parameters);
                $result = $this->lastPreparedStatement->execute();
            } else {
                return $this->lastPreparedStatement;
            }
        } else {
            $result = $this->driver->getConnection()->execute($sql);
        }

        if ($result instanceof Driver\ResultInterface && $result->isQueryResult()) {
            $resultSet = clone ($resultPrototype ?: $this->queryResultSetPrototype);
            $resultSet->initialize($result);
            return $resultSet;
        }

        return $result;
    }

    /**
     * Create statement
     *
     * @param  string $initialSql
     * @param  ParameterContainer $initialParameters
     * @return Driver\StatementInterface
     */
    public function createStatement($initialSql = null, $initialParameters = null)
    {
        $statement = $this->driver->createStatement($initialSql);
        if ($initialParameters === null || !$initialParameters instanceof ParameterContainer && is_array($initialParameters)) {
            $initialParameters = new ParameterContainer((is_array($initialParameters) ? $initialParameters : array()));
        }
        $statement->setParameterContainer($initialParameters);
        return $statement;
    }

    public function getHelpers(/* $functions */)
    {
        $functions = array();
        $platform = $this->platform;
        foreach (func_get_args() as $arg) {
            switch ($arg) {
                case self::FUNCTION_QUOTE_IDENTIFIER:
                    $functions[] = function ($value) use ($platform) { return $platform->quoteIdentifier($value); };
                    break;
                case self::FUNCTION_QUOTE_VALUE:
                    $functions[] = function ($value) use ($platform) { return $platform->quoteValue($value); };
                    break;

            }
        }
    }

    /**
     * @param $name
     * @throws Exception\InvalidArgumentException
     * @return Driver\DriverInterface|Platform\PlatformInterface
     */
    public function __get($name)
    {
        switch (strtolower($name)) {
            case 'driver':
                return $this->driver;
            case 'platform':
                return $this->platform;
            default:
                throw new Exception\InvalidArgumentException('Invalid magic property on adapter');
        }
    }

    /**
     * @param array $parameters
     * @return Driver\DriverInterface
     * @throws \InvalidArgumentException
     * @throws Exception\InvalidArgumentException
     */
    protected function createDriver($parameters)
    {
        if (!isset($parameters['driver'])) {
            throw new Exception\InvalidArgumentException(__FUNCTION__ . ' expects a "driver" key to be present inside the parameters');
        }

        if ($parameters['driver'] instanceof Driver\DriverInterface) {
            return $parameters['driver'];
        }

        if (!is_string($parameters['driver'])) {
            throw new Exception\InvalidArgumentException(__FUNCTION__ . ' expects a "driver" to be a string or instance of DriverInterface');
        }

        $options = array();
        if (isset($parameters['options'])) {
            $options = (array) $parameters['options'];
            unset($parameters['options']);
        }

        $driverName = strtolower($parameters['driver']);
        switch ($driverName) {
            case 'mysqli':
                $driver = new Driver\Mysqli\Mysqli($parameters, null, null, $options);
                break;
            case 'sqlsrv':
                $driver = new Driver\Sqlsrv\Sqlsrv($parameters);
                break;
            case 'oci8':
                $driver = new Driver\Oci8\Oci8($parameters);
                break;
            case 'pgsql':
                $driver = new Driver\Pgsql\Pgsql($parameters);
                break;
            case 'ibmdb2':
                $driver = new Driver\IbmDb2\IbmDb2($parameters);
                break;
            case 'pdo':
            default:
                if ($driverName == 'pdo' || strpos($driverName, 'pdo') === 0) {
                    $driver = new Driver\Pdo\Pdo($parameters);
                }
        }

        if (!isset($driver) || !$driver instanceof Driver\DriverInterface) {
            throw new Exception\InvalidArgumentException('DriverInterface expected', null, null);
        }

        return $driver;
    }

    /**
     * @param Driver\DriverInterface $driver
     * @return Platform\PlatformInterface
     */
    protected function createPlatform($parameters)
    {
        if (isset($parameters['platform'])) {
            $platformName = $parameters['platform'];
        } elseif ($this->driver instanceof Driver\DriverInterface) {
            $platformName = $this->driver->getDatabasePlatformName(Driver\DriverInterface::NAME_FORMAT_CAMELCASE);
        } else {
            throw new Exception\InvalidArgumentException('A platform could not be determined from the provided configuration');
        }

        // currently only supported by the IbmDb2 & Oracle concrete implementations
        $options = (isset($parameters['platform_options'])) ? $parameters['platform_options'] : array();

        switch ($platformName) {
            case 'Mysql':
                // mysqli or pdo_mysql driver
                $driver = ($this->driver instanceof Driver\Mysqli\Mysqli || $this->driver instanceof Driver\Pdo\Pdo) ? $this->driver : null;
                return new Platform\Mysql($driver);
            case 'SqlServer':
                // PDO is only supported driver for quoting values in this platform
                return new Platform\SqlServer(($this->driver instanceof Driver\Pdo\Pdo) ? $this->driver : null);
            case 'Oracle':
                // oracle does not accept a driver as an option, no driver specific quoting available
                return new Platform\Oracle($options);
            case 'Sqlite':
                // PDO is only supported driver for quoting values in this platform
                return new Platform\Sqlite(($this->driver instanceof Driver\Pdo\Pdo) ? $this->driver : null);
            case 'Postgresql':
                // pgsql or pdo postgres driver
                $driver = ($this->driver instanceof Driver\Pgsql\Pgsql || $this->driver instanceof Driver\Pdo\Pdo) ? $this->driver : null;
                return new Platform\Postgresql($driver);
            case 'IbmDb2':
                // ibm_db2 driver escaping does not need an action connection
                return new Platform\IbmDb2($options);
            default:
                return new Platform\Sql92();
        }
    }

    protected function createProfiler($parameters)
    {
        if ($parameters['profiler'] instanceof Profiler\ProfilerInterface) {
            $profiler = $parameters['profiler'];
        } elseif (is_bool($parameters['profiler'])) {
            $profiler = ($parameters['profiler'] == true) ? new Profiler\Profiler : null;
        } else {
            throw new Exception\InvalidArgumentException(
                '"profiler" parameter must be an instance of ProfilerInterface or a boolean'
            );
        }
        return $profiler;
    }

    /**
     * @param array $parameters
     * @return Driver\DriverInterface
     * @throws \InvalidArgumentException
     * @throws Exception\InvalidArgumentException
     * @deprecated
     */
    protected function createDriverFromParameters(array $parameters)
    {
        return $this->createDriver($parameters);
    }

    /**
     * @param Driver\DriverInterface $driver
     * @return Platform\PlatformInterface
     * @deprecated
     */
    protected function createPlatformFromDriver(Driver\DriverInterface $driver)
    {
        return $this->createPlatform($driver);
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Adapter\Driver;

interface ConnectionInterface
{
    /**
     * Get current schema
     *
     * @return string
     */
    public function getCurrentSchema();

    /**
     * Get resource
     *
     * @return mixed
     */
    public function getResource();

    /**
     * Connect
     *
     * @return ConnectionInterface
     */
    public function connect();

    /**
     * Is connected
     *
     * @return bool
     */
    public function isConnected();

    /**
     * Disconnect
     *
     * @return ConnectionInterface
     */
    public function disconnect();

    /**
     * Begin transaction
     *
     * @return ConnectionInterface
     */
    public function beginTransaction();

    /**
     * Commit
     *
     * @return ConnectionInterface
     */
    public function commit();

    /**
     * Rollback
     *
     * @return ConnectionInterface
     */
    public function rollback();

    /**
     * Execute
     *
     * @param  string $sql
     * @return ResultInterface
     */
    public function execute($sql);

    /**
     * Get last generated id
     *
     * @param  null $name Ignored
     * @return int
     */
    public function getLastGeneratedValue($name = null);
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Adapter\Driver;

use Zend\Db\Adapter\Profiler\ProfilerAwareInterface;
use Zend\Db\Adapter\Profiler\ProfilerInterface;

abstract class AbstractConnection implements ConnectionInterface, ProfilerAwareInterface
{
    /**
     * @var array
     */
    protected $connectionParameters = array();

    /**
     * @var string|null
     */
    protected $driverName;

    /**
     * @var boolean
     */
    protected $inTransaction = false;

    /**
     * Nested transactions count.
     *
     * @var integer
     */
    protected $nestedTransactionsCount = 0;

    /**
     * @var ProfilerInterface|null
     */
    protected $profiler;

    /**
     * @var resource|null
     */
    protected $resource;

    /**
     * {@inheritDoc}
     */
    public function disconnect()
    {
        if ($this->isConnected()) {
            $this->resource = null;
        }

        return $this;
    }

    /**
     * Get connection parameters
     *
     * @return array
     */
    public function getConnectionParameters()
    {
        return $this->connectionParameters;
    }

    /**
     * Get driver name
     *
     * @return null|string
     */
    public function getDriverName()
    {
        return $this->driverName;
    }

    /**
     * @return null|ProfilerInterface
     */
    public function getProfiler()
    {
        return $this->profiler;
    }

    /**
     * {@inheritDoc}
     *
     * @return resource
     */
    public function getResource()
    {
        if (!$this->isConnected()) {
            $this->connect();
        }

        return $this->resource;
    }

    /**
     * Checks whether the connection is in transaction state.
     *
     * @return boolean
     */
    public function inTransaction()
    {
        return $this->inTransaction;
    }

    /**
     * @param  array $connectionParameters
     * @return self
     */
    public function setConnectionParameters(array $connectionParameters)
    {
        $this->connectionParameters = $connectionParameters;

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @return self
     */
    public function setProfiler(ProfilerInterface $profiler)
    {
        $this->profiler = $profiler;

        return $this;
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Adapter\Driver;

interface DriverInterface
{
    const PARAMETERIZATION_POSITIONAL = 'positional';
    const PARAMETERIZATION_NAMED = 'named';
    const NAME_FORMAT_CAMELCASE = 'camelCase';
    const NAME_FORMAT_NATURAL = 'natural';

    /**
     * Get database platform name
     *
     * @param string $nameFormat
     * @return string
     */
    public function getDatabasePlatformName($nameFormat = self::NAME_FORMAT_CAMELCASE);

    /**
     * Check environment
     *
     * @return bool
     */
    public function checkEnvironment();

    /**
     * Get connection
     *
     * @return ConnectionInterface
     */
    public function getConnection();

    /**
     * Create statement
     *
     * @param string|resource $sqlOrResource
     * @return StatementInterface
     */
    public function createStatement($sqlOrResource = null);

    /**
     * Create result
     *
     * @param resource $resource
     * @return ResultInterface
     */
    public function createResult($resource);

    /**
     * Get prepare type
     *
     * @return array
     */
    public function getPrepareType();

    /**
     * Format parameter name
     *
     * @param string $name
     * @param mixed  $type
     * @return string
     */
    public function formatParameterName($name, $type = null);

    /**
     * Get last generated value
     *
     * @return mixed
     */
    public function getLastGeneratedValue();
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Adapter\Driver\Mysqli;

use mysqli_stmt;
use Zend\Db\Adapter\Driver\DriverInterface;
use Zend\Db\Adapter\Exception;
use Zend\Db\Adapter\Profiler;

class Mysqli implements DriverInterface, Profiler\ProfilerAwareInterface
{
    /**
     * @var Connection
     */
    protected $connection = null;

    /**
     * @var Statement
     */
    protected $statementPrototype = null;

    /**
     * @var Result
     */
    protected $resultPrototype = null;

    /**
     * @var Profiler\ProfilerInterface
     */
    protected $profiler = null;

    /**
     * @var array
     */
    protected $options = array(
        'buffer_results' => false
    );

    /**
     * Constructor
     *
     * @param array|Connection|\mysqli $connection
     * @param null|Statement $statementPrototype
     * @param null|Result $resultPrototype
     * @param array $options
     */
    public function __construct($connection, ?Statement $statementPrototype = null, ?Result $resultPrototype = null, array $options = array())
    {
        if (!$connection instanceof Connection) {
            $connection = new Connection($connection);
        }

        $options = array_intersect_key(array_merge($this->options, $options), $this->options);

        $this->registerConnection($connection);
        $this->registerStatementPrototype(($statementPrototype) ?: new Statement($options['buffer_results']));
        $this->registerResultPrototype(($resultPrototype) ?: new Result());
    }

    /**
     * @param Profiler\ProfilerInterface $profiler
     * @return Mysqli
     */
    public function setProfiler(Profiler\ProfilerInterface $profiler)
    {
        $this->profiler = $profiler;
        if ($this->connection instanceof Profiler\ProfilerAwareInterface) {
            $this->connection->setProfiler($profiler);
        }
        if ($this->statementPrototype instanceof Profiler\ProfilerAwareInterface) {
            $this->statementPrototype->setProfiler($profiler);
        }
        return $this;
    }

    /**
     * @return null|Profiler\ProfilerInterface
     */
    public function getProfiler()
    {
        return $this->profiler;
    }

    /**
     * Register connection
     *
     * @param  Connection $connection
     * @return Mysqli
     */
    public function registerConnection(Connection $connection)
    {
        $this->connection = $connection;
        $this->connection->setDriver($this); // needs access to driver to createStatement()
        return $this;
    }

    /**
     * Register statement prototype
     *
     * @param Statement $statementPrototype
     */
    public function registerStatementPrototype(Statement $statementPrototype)
    {
        $this->statementPrototype = $statementPrototype;
        $this->statementPrototype->setDriver($this); // needs access to driver to createResult()
    }

    /**
     * Get statement prototype
     *
     * @return null|Statement
     */
    public function getStatementPrototype()
    {
        return $this->statementPrototype;
    }

    /**
     * Register result prototype
     *
     * @param Result $resultPrototype
     */
    public function registerResultPrototype(Result $resultPrototype)
    {
        $this->resultPrototype = $resultPrototype;
    }

    /**
     * @return null|Result
     */
    public function getResultPrototype()
    {
        return $this->resultPrototype;
    }

    /**
     * Get database platform name
     *
     * @param  string $nameFormat
     * @return string
     */
    public function getDatabasePlatformName($nameFormat = self::NAME_FORMAT_CAMELCASE)
    {
        if ($nameFormat == self::NAME_FORMAT_CAMELCASE) {
            return 'Mysql';
        }

        return 'MySQL';
    }

    /**
     * Check environment
     *
     * @throws Exception\RuntimeException
     * @return void
     */
    public function checkEnvironment()
    {
        if (!extension_loaded('mysqli')) {
            throw new Exception\RuntimeException('The Mysqli extension is required for this adapter but the extension is not loaded');
        }
    }

    /**
     * Get connection
     *
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Create statement
     *
     * @param string $sqlOrResource
     * @return Statement
     */
    public function createStatement($sqlOrResource = null)
    {
        /**
         * @todo Resource tracking
        if (is_resource($sqlOrResource) && !in_array($sqlOrResource, $this->resources, true)) {
            $this->resources[] = $sqlOrResource;
        }
        */

        $statement = clone $this->statementPrototype;
        if ($sqlOrResource instanceof mysqli_stmt) {
            $statement->setResource($sqlOrResource);
        } else {
            if (is_string($sqlOrResource)) {
                $statement->setSql($sqlOrResource);
            }
            if (!$this->connection->isConnected()) {
                $this->connection->connect();
            }
            $statement->initialize($this->connection->getResource());
        }
        return $statement;
    }

    /**
     * Create result
     *
     * @param resource $resource
     * @param null|bool $isBuffered
     * @return Result
     */
    public function createResult($resource, $isBuffered = null)
    {
        $result = clone $this->resultPrototype;
        $result->initialize($resource, $this->connection->getLastGeneratedValue(), $isBuffered);
        return $result;
    }

    /**
     * Get prepare type
     *
     * @return array
     */
    public function getPrepareType()
    {
        return self::PARAMETERIZATION_POSITIONAL;
    }

    /**
     * Format parameter name
     *
     * @param string $name
     * @param mixed  $type
     * @return string
     */
    public function formatParameterName($name, $type = null)
    {
        return '?';
    }

    /**
     * Get last generated value
     *
     * @return mixed
     */
    public function getLastGeneratedValue()
    {
        return $this->getConnection()->getLastGeneratedValue();
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Adapter\Driver\Mysqli;

use Zend\Db\Adapter\Driver\AbstractConnection;
use Zend\Db\Adapter\Exception;

class Connection extends AbstractConnection
{
    /**
     * @var Mysqli
     */
    protected $driver = null;

    /**
     * @var \mysqli
     */
    protected $resource = null;

    /**
     * Constructor
     *
     * @param  array|mysqli|null                                   $connectionInfo
     * @throws \Zend\Db\Adapter\Exception\InvalidArgumentException
     */
    public function __construct($connectionInfo = null)
    {
        if (is_array($connectionInfo)) {
            $this->setConnectionParameters($connectionInfo);
        } elseif ($connectionInfo instanceof \mysqli) {
            $this->setResource($connectionInfo);
        } elseif (null !== $connectionInfo) {
            throw new Exception\InvalidArgumentException('$connection must be an array of parameters, a mysqli object or null');
        }
    }

    /**
     * @param  Mysqli $driver
     * @return self
     */
    public function setDriver(Mysqli $driver)
    {
        $this->driver = $driver;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrentSchema()
    {
        if (!$this->isConnected()) {
            $this->connect();
        }

        /** @var $result \mysqli_result */
        $result = $this->resource->query('SELECT DATABASE()');
        $r = $result->fetch_row();

        return $r[0];
    }

    /**
     * Set resource
     *
     * @param  \mysqli $resource
     * @return self
     */
    public function setResource(\mysqli $resource)
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function connect()
    {
        if ($this->resource instanceof \mysqli) {
            return $this;
        }

        // localize
        $p = $this->connectionParameters;

        // given a list of key names, test for existence in $p
        $findParameterValue = function (array $names) use ($p) {
            foreach ($names as $name) {
                if (isset($p[$name])) {
                    return $p[$name];
                }
            }

            return;
        };

        $hostname = $findParameterValue(array('hostname', 'host'));
        $username = $findParameterValue(array('username', 'user'));
        $password = $findParameterValue(array('password', 'passwd', 'pw'));
        $database = $findParameterValue(array('database', 'dbname', 'db', 'schema'));
        $port     = (isset($p['port'])) ? (int) $p['port'] : null;
        $socket   = (isset($p['socket'])) ? $p['socket'] : null;

        $this->resource = new \mysqli();
        $this->resource->init();

        if (!empty($p['driver_options'])) {
            foreach ($p['driver_options'] as $option => $value) {
                if (is_string($option)) {
                    $option = strtoupper($option);
                    if (!defined($option)) {
                        continue;
                    }
                    $option = constant($option);
                }
                $this->resource->options($option, $value);
            }
        }

        $this->resource->real_connect($hostname, $username, $password, $database, $port, $socket);

        if ($this->resource->connect_error) {
            throw new Exception\RuntimeException(
                'Connection error',
                null,
                new Exception\ErrorException($this->resource->connect_error, $this->resource->connect_errno)
            );
        }

        if (!empty($p['charset'])) {
            $this->resource->set_charset($p['charset']);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function isConnected()
    {
        return ($this->resource instanceof \mysqli);
    }

    /**
     * {@inheritDoc}
     */
    public function disconnect()
    {
        if ($this->resource instanceof \mysqli) {
            $this->resource->close();
        }
        $this->resource = null;
    }

    /**
     * {@inheritDoc}
     */
    public function beginTransaction()
    {
        if (!$this->isConnected()) {
            $this->connect();
        }

        $this->resource->autocommit(false);
        $this->inTransaction = true;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function commit()
    {
        if (!$this->isConnected()) {
            $this->connect();
        }

        $this->resource->commit();
        $this->inTransaction = false;
        $this->resource->autocommit(true);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function rollback()
    {
        if (!$this->isConnected()) {
            throw new Exception\RuntimeException('Must be connected before you can rollback.');
        }

        if (!$this->inTransaction) {
            throw new Exception\RuntimeException('Must call beginTransaction() before you can rollback.');
        }

        $this->resource->rollback();
        $this->resource->autocommit(true);
        $this->inTransaction = false;

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception\InvalidQueryException
     */
    public function execute($sql)
    {
        if (!$this->isConnected()) {
            $this->connect();
        }

        if ($this->profiler) {
            $this->profiler->profilerStart($sql);
        }

        $resultResource = $this->resource->query($sql);

        if ($this->profiler) {
            $this->profiler->profilerFinish($sql);
        }

        // if the returnValue is something other than a mysqli_result, bypass wrapping it
        if ($resultResource === false) {
            throw new Exception\InvalidQueryException($this->resource->error);
        }

        $resultPrototype = $this->driver->createResult(($resultResource === true) ? $this->resource : $resultResource);

        return $resultPrototype;
    }

    /**
     * {@inheritDoc}
     */
    public function getLastGeneratedValue($name = null)
    {
        return $this->resource->insert_id;
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Adapter;

interface StatementContainerInterface
{
    /**
     * Set sql
     *
     * @param $sql
     * @return mixed
     */
    public function setSql($sql);

    /**
     * Get sql
     *
     * @return mixed
     */
    public function getSql();

    /**
     * Set parameter container
     *
     * @param ParameterContainer $parameterContainer
     * @return mixed
     */
    public function setParameterContainer(ParameterContainer $parameterContainer);

    /**
     * Get parameter container
     *
     * @return mixed
     */
    public function getParameterContainer();
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Adapter\Driver;

use Zend\Db\Adapter\ParameterContainer;
use Zend\Db\Adapter\StatementContainerInterface;

interface StatementInterface extends StatementContainerInterface
{
    /**
     * Get resource
     *
     * @return resource
     */
    public function getResource();

    /**
     * Prepare sql
     *
     * @param string $sql
     */
    public function prepare($sql = null);

    /**
     * Check if is prepared
     *
     * @return bool
     */
    public function isPrepared();

    /**
     * Execute
     *
     * @param null|array|ParameterContainer $parameters
     * @return ResultInterface
     */
    public function execute($parameters = null);
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Adapter\Driver\Mysqli;

use Zend\Db\Adapter\Driver\StatementInterface;
use Zend\Db\Adapter\Exception;
use Zend\Db\Adapter\ParameterContainer;
use Zend\Db\Adapter\Profiler;

class Statement implements StatementInterface, Profiler\ProfilerAwareInterface
{
    /**
     * @var \mysqli
     */
    protected $mysqli = null;

    /**
     * @var Mysqli
     */
    protected $driver = null;

    /**
     * @var Profiler\ProfilerInterface
     */
    protected $profiler = null;

    /**
     * @var string
     */
    protected $sql = '';

    /**
     * Parameter container
     *
     * @var ParameterContainer
     */
    protected $parameterContainer = null;

    /**
     * @var \mysqli_stmt
     */
    protected $resource = null;

    /**
     * Is prepared
     *
     * @var bool
     */
    protected $isPrepared = false;

    /**
     * @var bool
     */
    protected $bufferResults = false;

    /**
     * @param  bool $bufferResults
     */
    public function __construct($bufferResults = false)
    {
        $this->bufferResults = (bool) $bufferResults;
    }

    /**
     * Set driver
     *
     * @param  Mysqli $driver
     * @return Statement
     */
    public function setDriver(Mysqli $driver)
    {
        $this->driver = $driver;
        return $this;
    }

    /**
     * @param Profiler\ProfilerInterface $profiler
     * @return Statement
     */
    public function setProfiler(Profiler\ProfilerInterface $profiler)
    {
        $this->profiler = $profiler;
        return $this;
    }

    /**
     * @return null|Profiler\ProfilerInterface
     */
    public function getProfiler()
    {
        return $this->profiler;
    }

    /**
     * Initialize
     *
     * @param  \mysqli $mysqli
     * @return Statement
     */
    public function initialize(\mysqli $mysqli)
    {
        $this->mysqli = $mysqli;
        return $this;
    }

    /**
     * Set sql
     *
     * @param  string $sql
     * @return Statement
     */
    public function setSql($sql)
    {
        $this->sql = $sql;
        return $this;
    }

    /**
     * Set Parameter container
     *
     * @param ParameterContainer $parameterContainer
     * @return Statement
     */
    public function setParameterContainer(ParameterContainer $parameterContainer)
    {
        $this->parameterContainer = $parameterContainer;
        return $this;
    }

    /**
     * Get resource
     *
     * @return mixed
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Set resource
     *
     * @param  \mysqli_stmt $mysqliStatement
     * @return Statement
     */
    public function setResource(\mysqli_stmt $mysqliStatement)
    {
        $this->resource = $mysqliStatement;
        $this->isPrepared = true;
        return $this;
    }

    /**
     * Get sql
     *
     * @return string
     */
    public function getSql()
    {
        return $this->sql;
    }

    /**
     * Get parameter count
     *
     * @return ParameterContainer
     */
    public function getParameterContainer()
    {
        return $this->parameterContainer;
    }

    /**
     * Is prepared
     *
     * @return bool
     */
    public function isPrepared()
    {
        return $this->isPrepared;
    }

    /**
     * Prepare
     *
     * @param string $sql
     * @throws Exception\InvalidQueryException
     * @throws Exception\RuntimeException
     * @return Statement
     */
    public function prepare($sql = null)
    {
        if ($this->isPrepared) {
            throw new Exception\RuntimeException('This statement has already been prepared');
        }

        $sql = ($sql) ?: $this->sql;

        $this->resource = $this->mysqli->prepare($sql);
        if (!$this->resource instanceof \mysqli_stmt) {
            throw new Exception\InvalidQueryException(
                'Statement couldn\'t be produced with sql: ' . $sql,
                null,
                new Exception\ErrorException($this->mysqli->error, $this->mysqli->errno)
            );
        }

        $this->isPrepared = true;
        return $this;
    }

    /**
     * Execute
     *
     * @param null|array|ParameterContainer $parameters
     * @throws Exception\RuntimeException
     * @return mixed
     */
    public function execute($parameters = null)
    {
        if (!$this->isPrepared) {
            $this->prepare();
        }

        /** START Standard ParameterContainer Merging Block */
        if (!$this->parameterContainer instanceof ParameterContainer) {
            if ($parameters instanceof ParameterContainer) {
                $this->parameterContainer = $parameters;
                $parameters = null;
            } else {
                $this->parameterContainer = new ParameterContainer();
            }
        }

        if (is_array($parameters)) {
            $this->parameterContainer->setFromArray($parameters);
        }

        if ($this->parameterContainer->count() > 0) {
            $this->bindParametersFromContainer();
        }
        /** END Standard ParameterContainer Merging Block */

        if ($this->profiler) {
            $this->profiler->profilerStart($this);
        }

        $return = $this->resource->execute();

        if ($this->profiler) {
            $this->profiler->profilerFinish();
        }

        if ($return === false) {
            throw new Exception\RuntimeException($this->resource->error);
        }

        if ($this->bufferResults === true) {
            $this->resource->store_result();
            $this->isPrepared = false;
            $buffered = true;
        } else {
            $buffered = false;
        }

        $result = $this->driver->createResult($this->resource, $buffered);
        return $result;
    }

    /**
     * Bind parameters from container
     *
     * @return void
     */
    protected function bindParametersFromContainer()
    {
        $parameters = $this->parameterContainer->getNamedArray();
        $type = '';
        $args = array();

        foreach ($parameters as $name => &$value) {
            if ($this->parameterContainer->offsetHasErrata($name)) {
                switch ($this->parameterContainer->offsetGetErrata($name)) {
                    case ParameterContainer::TYPE_DOUBLE:
                        $type .= 'd';
                        break;
                    case ParameterContainer::TYPE_NULL:
                        $value = null; // as per @see http://www.php.net/manual/en/mysqli-stmt.bind-param.php#96148
                    case ParameterContainer::TYPE_INTEGER:
                        $type .= 'i';
                        break;
                    case ParameterContainer::TYPE_STRING:
                    default:
                        $type .= 's';
                        break;
                }
            } else {
                $type .= 's';
            }
            $args[] = &$value;
        }

        if ($args) {
            array_unshift($args, $type);
            call_user_func_array(array($this->resource, 'bind_param'), $args);
        }
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Adapter\Driver;

use Countable;
use Iterator;

interface ResultInterface extends
    Countable,
    Iterator
{
    /**
     * Force buffering
     *
     * @return void
     */
    public function buffer();

    /**
     * Check if is buffered
     *
     * @return bool|null
     */
    public function isBuffered();

    /**
     * Is query result?
     *
     * @return bool
     */
    public function isQueryResult();

    /**
     * Get affected rows
     *
     * @return int
     */
    public function getAffectedRows();

    /**
     * Get generated value
     *
     * @return mixed|null
     */
    public function getGeneratedValue();

    /**
     * Get the resource
     *
     * @return mixed
     */
    public function getResource();

    /**
     * Get field count
     *
     * @return int
     */
    public function getFieldCount();
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Adapter\Driver\Mysqli;

use Iterator;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\Adapter\Exception;

class Result implements
    Iterator,
    ResultInterface
{
    /**
     * @var \mysqli|\mysqli_result|\mysqli_stmt
     */
    protected $resource = null;

    /**
     * @var bool
     */
    protected $isBuffered = null;

    /**
     * Cursor position
     * @var int
     */
    protected $position = 0;

    /**
     * Number of known rows
     * @var int
     */
    protected $numberOfRows = -1;

    /**
     * Is the current() operation already complete for this pointer position?
     * @var bool
     */
    protected $currentComplete = false;

    /**
     * @var bool
     */
    protected $nextComplete = false;

    /**
     * @var bool
     */
    protected $currentData = false;

    /**
     *
     * @var array
     */
    protected $statementBindValues = array('keys' => null, 'values' => array());

    /**
     * @var mixed
     */
    protected $generatedValue = null;

    /**
     * Initialize
     *
     * @param mixed $resource
     * @param mixed $generatedValue
     * @param bool|null $isBuffered
     * @throws Exception\InvalidArgumentException
     * @return Result
     */
    public function initialize($resource, $generatedValue, $isBuffered = null)
    {
        if (!$resource instanceof \mysqli && !$resource instanceof \mysqli_result && !$resource instanceof \mysqli_stmt) {
            throw new Exception\InvalidArgumentException('Invalid resource provided.');
        }

        if ($isBuffered !== null) {
            $this->isBuffered = $isBuffered;
        } else {
            if ($resource instanceof \mysqli || $resource instanceof \mysqli_result
                || $resource instanceof \mysqli_stmt && $resource->num_rows != 0) {
                $this->isBuffered = true;
            }
        }

        $this->resource = $resource;
        $this->generatedValue = $generatedValue;
        return $this;
    }

    /**
     * Force buffering
     *
     * @throws Exception\RuntimeException
     */
    public function buffer()
    {
        if ($this->resource instanceof \mysqli_stmt && $this->isBuffered !== true) {
            if ($this->position > 0) {
                throw new Exception\RuntimeException('Cannot buffer a result set that has started iteration.');
            }
            $this->resource->store_result();
            $this->isBuffered = true;
        }
    }

    /**
     * Check if is buffered
     *
     * @return bool|null
     */
    public function isBuffered()
    {
        return $this->isBuffered;
    }

    /**
     * Return the resource
     *
     * @return mixed
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Is query result?
     *
     * @return bool
     */
    public function isQueryResult()
    {
        return ($this->resource->field_count > 0);
    }

    /**
     * Get affected rows
     *
     * @return int
     */
    public function getAffectedRows()
    {
        if ($this->resource instanceof \mysqli || $this->resource instanceof \mysqli_stmt) {
            return $this->resource->affected_rows;
        }

        return $this->resource->num_rows;
    }

    /**
     * Current
     *
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        if ($this->currentComplete) {
            return $this->currentData;
        }

        if ($this->resource instanceof \mysqli_stmt) {
            $this->loadDataFromMysqliStatement();
            return $this->currentData;
        } else {
            $this->loadFromMysqliResult();
            return $this->currentData;
        }
    }

    /**
     * Mysqli's binding and returning of statement values
     *
     * Mysqli requires you to bind variables to the extension in order to
     * get data out.  These values have to be references:
     * @see http://php.net/manual/en/mysqli-stmt.bind-result.php
     *
     * @throws Exception\RuntimeException
     * @return bool
     */
    protected function loadDataFromMysqliStatement()
    {
        // build the default reference based bind structure, if it does not already exist
        if ($this->statementBindValues['keys'] === null) {
            $this->statementBindValues['keys'] = array();
            $resultResource = $this->resource->result_metadata();
            foreach ($resultResource->fetch_fields() as $col) {
                $this->statementBindValues['keys'][] = $col->name;
            }
            $this->statementBindValues['values'] = array_fill(0, count($this->statementBindValues['keys']), null);
            $refs = array();
            foreach ($this->statementBindValues['values'] as $i => &$f) {
                $refs[$i] = &$f;
            }
            call_user_func_array(array($this->resource, 'bind_result'), $this->statementBindValues['values']);
        }

        if (($r = $this->resource->fetch()) === null) {
            if (!$this->isBuffered) {
                $this->resource->close();
            }
            return false;
        } elseif ($r === false) {
            throw new Exception\RuntimeException($this->resource->error);
        }
        // dereference
        if (!is_array($this->currentData)) {
            $this->currentData=array();
        }
        for ($i = 0, $count = count($this->statementBindValues['keys']); $i < $count; $i++) {
            $this->currentData[$this->statementBindValues['keys'][$i]] = $this->statementBindValues['values'][$i];
        }
        $this->currentComplete = true;
        $this->nextComplete = true;
        $this->position++;
        return true;
    }

    /**
     * Load from mysqli result
     *
     * @return bool
     */
    protected function loadFromMysqliResult()
    {
        $this->currentData = null;

        if (($data = $this->resource->fetch_assoc()) === null) {
            return false;
        }

        $this->position++;
        $this->currentData = $data;
        $this->currentComplete = true;
        $this->nextComplete = true;
        $this->position++;
        return true;
    }

    /**
     * Next
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function next()
    {
        $this->currentComplete = false;

        if ($this->nextComplete == false) {
            $this->position++;
        }

        $this->nextComplete = false;
    }

    /**
     * Key
     *
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->position;
    }

    /**
     * Rewind
     *
     * @throws Exception\RuntimeException
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function rewind()
    {
        if ($this->position !== 0) {
            if ($this->isBuffered === false) {
                throw new Exception\RuntimeException('Unbuffered results cannot be rewound for multiple iterations');
            }
        }
        $this->resource->data_seek(0); // works for both mysqli_result & mysqli_stmt
        $this->currentComplete = false;
        $this->position = 0;
    }

    /**
     * Valid
     *
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function valid()
    {
        if ($this->currentComplete) {
            return true;
        }

        if ($this->resource instanceof \mysqli_stmt) {
            return $this->loadDataFromMysqliStatement();
        }

        return $this->loadFromMysqliResult();
    }

    /**
     * Count
     *
     * @throws Exception\RuntimeException
     * @return int
     */
    #[\ReturnTypeWillChange]
    public function count()
    {
        if ($this->isBuffered === false) {
            throw new Exception\RuntimeException('Row count is not available in unbuffered result sets.');
        }
        return $this->resource->num_rows;
    }

    /**
     * Get field count
     *
     * @return int
     */
    public function getFieldCount()
    {
        return $this->resource->field_count;
    }

    /**
     * Get generated value
     *
     * @return mixed|null
     */
    public function getGeneratedValue()
    {
        return $this->generatedValue;
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Adapter\Platform;

interface PlatformInterface
{
    /**
     * Get name
     *
     * @return string
     */
    public function getName();

    /**
     * Get quote identifier symbol
     *
     * @return string
     */
    public function getQuoteIdentifierSymbol();

    /**
     * Quote identifier
     *
     * @param  string $identifier
     * @return string
     */
    public function quoteIdentifier($identifier);

    /**
     * Quote identifier chain
     *
     * @param string|string[] $identifierChain
     * @return string
     */
    public function quoteIdentifierChain($identifierChain);

    /**
     * Get quote value symbol
     *
     * @return string
     */
    public function getQuoteValueSymbol();

    /**
     * Quote value
     *
     * Will throw a notice when used in a workflow that can be considered "unsafe"
     *
     * @param  string $value
     * @return string
     */
    public function quoteValue($value);

    /**
     * Quote Trusted Value
     *
     * The ability to quote values without notices
     *
     * @param $value
     * @return mixed
     */
    public function quoteTrustedValue($value);

    /**
     * Quote value list
     *
     * @param string|string[] $valueList
     * @return string
     */
    public function quoteValueList($valueList);

    /**
     * Get identifier separator
     *
     * @return string
     */
    public function getIdentifierSeparator();

    /**
     * Quote identifier in fragment
     *
     * @param  string $identifier
     * @param  array $additionalSafeWords
     * @return string
     */
    public function quoteIdentifierInFragment($identifier, array $additionalSafeWords = array());
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Adapter\Platform;

abstract class AbstractPlatform implements PlatformInterface
{
    /**
     * @var string[]
     */
    protected $quoteIdentifier = array('"', '"');

    /**
     * @var string
     */
    protected $quoteIdentifierTo = '\'';

    /**
     * @var bool
     */
    protected $quoteIdentifiers = true;

    /**
     * {@inheritDoc}
     */
    public function quoteIdentifierInFragment($identifier, array $safeWords = array())
    {
        if (! $this->quoteIdentifiers) {
            return $identifier;
        }

        $safeWordsInt = array('*' => true, ' ' => true, '.' => true, 'as' => true);

        foreach ($safeWords as $sWord) {
            $safeWordsInt[strtolower($sWord)] = true;
        }

        $parts = preg_split(
            '/([^0-9,a-z,A-Z$_:])/i',
            $identifier??'',
            -1,
            PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
        );

        $identifier = '';

        foreach ($parts as $part) {
            $identifier .= isset($safeWordsInt[strtolower($part)])
                ? $part
                : $this->quoteIdentifier[0]
                . str_replace($this->quoteIdentifier[0], $this->quoteIdentifierTo, $part)
                . $this->quoteIdentifier[1];
        }

        return $identifier;
    }

    /**
     * {@inheritDoc}
     */
    public function quoteIdentifier($identifier)
    {
        if (! $this->quoteIdentifiers) {
            return $identifier;
        }

        return $this->quoteIdentifier[0]
            . str_replace($this->quoteIdentifier[0], $this->quoteIdentifierTo, $identifier)
            . $this->quoteIdentifier[1];
    }

    /**
     * {@inheritDoc}
     */
    public function quoteIdentifierChain($identifierChain)
    {
        return '"' . implode('"."', (array) str_replace('"', '\\"', $identifierChain)) . '"';
    }

    /**
     * {@inheritDoc}
     */
    public function getQuoteIdentifierSymbol()
    {
        return $this->quoteIdentifier[0];
    }

    /**
     * {@inheritDoc}
     */
    public function getQuoteValueSymbol()
    {
        return '\'';
    }

    /**
     * {@inheritDoc}
     */
    public function quoteValue($value)
    {
        trigger_error(
            'Attempting to quote a value in ' . get_class($this) .
            ' without extension/driver support can introduce security vulnerabilities in a production environment'
        );
        return '\'' . addcslashes((string) $value, "\x00\n\r\\'\"\x1a") . '\'';
    }

    /**
     * {@inheritDoc}
     */
    public function quoteTrustedValue($value)
    {
        return '\'' . addcslashes((string) $value, "\x00\n\r\\'\"\x1a") . '\'';
    }

    /**
     * {@inheritDoc}
     */
    public function quoteValueList($valueList)
    {
        return implode(', ', array_map(array($this, 'quoteValue'), (array) $valueList));
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentifierSeparator()
    {
        return '.';
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Adapter\Platform;

use Zend\Db\Adapter\Driver\DriverInterface;
use Zend\Db\Adapter\Driver\Mysqli;
use Zend\Db\Adapter\Driver\Pdo;
use Zend\Db\Adapter\Exception;

class Mysql extends AbstractPlatform
{
    /**
     * {@inheritDoc}
     */
    protected $quoteIdentifier = array('`', '`');

    /**
     * {@inheritDoc}
     */
    protected $quoteIdentifierTo = '``';

    /**
     * @var \mysqli|\PDO
     */
    protected $resource = null;

    /**
     * @param null|\Zend\Db\Adapter\Driver\Mysqli\Mysqli|\Zend\Db\Adapter\Driver\Pdo\Pdo|\mysqli|\PDO $driver
     */
    public function __construct($driver = null)
    {
        if ($driver) {
            $this->setDriver($driver);
        }
    }

    /**
     * @param \Zend\Db\Adapter\Driver\Mysqli\Mysqli|\Zend\Db\Adapter\Driver\Pdo\Pdo|\mysqli|\PDO $driver
     * @throws \Zend\Db\Adapter\Exception\InvalidArgumentException
     *
     * @return self
     */
    public function setDriver($driver)
    {
        // handle Zend\Db drivers
        if ($driver instanceof Mysqli\Mysqli
            || ($driver instanceof Pdo\Pdo && $driver->getDatabasePlatformName() == 'Mysql')
            || ($driver instanceof \mysqli)
            || ($driver instanceof \PDO && $driver->getAttribute(\PDO::ATTR_DRIVER_NAME) == 'mysql')
        ) {
            $this->resource = $driver;
            return $this;
        }

        throw new Exception\InvalidArgumentException('$driver must be a Mysqli or Mysql PDO Zend\Db\Adapter\Driver, Mysqli instance or MySQL PDO instance');
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'MySQL';
    }

    /**
     * {@inheritDoc}
     */
    public function quoteIdentifierChain($identifierChain)
    {
        return '`' . implode('`.`', (array) str_replace('`', '``', $identifierChain)) . '`';
    }

    /**
     * {@inheritDoc}
     */
    public function quoteValue($value)
    {
        if ($this->resource instanceof DriverInterface) {
            $this->resource = $this->resource->getConnection()->getResource();
        }
        if ($this->resource instanceof \mysqli) {
            return '\'' . $this->resource->real_escape_string($value) . '\'';
        }
        if ($this->resource instanceof \PDO) {
            return $this->resource->quote($value);
        }
        return parent::quoteValue($value);
    }

    /**
     * {@inheritDoc}
     */
    public function quoteTrustedValue($value)
    {
        if ($this->resource instanceof DriverInterface) {
            $this->resource = $this->resource->getConnection()->getResource();
        }
        if ($this->resource instanceof \mysqli) {
            return '\'' . $this->resource->real_escape_string($value) . '\'';
        }
        if ($this->resource instanceof \PDO) {
            return $this->resource->quote($value);
        }
        return parent::quoteTrustedValue($value);
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\ResultSet;

use Countable;
use Traversable;

interface ResultSetInterface extends Traversable, Countable
{
    /**
     * Can be anything traversable|array
     * @abstract
     * @param $dataSource
     * @return mixed
     */
    public function initialize($dataSource);

    /**
     * Field terminology is more correct as information coming back
     * from the database might be a column, and/or the result of an
     * operation or intersection of some data
     * @abstract
     * @return mixed
     */
    public function getFieldCount();
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\ResultSet;

use ArrayIterator;
use Countable;
use Iterator;
use IteratorAggregate;
use Zend\Db\Adapter\Driver\ResultInterface;

abstract class AbstractResultSet implements Iterator, ResultSetInterface
{
    /**
     * if -1, datasource is already buffered
     * if -2, implicitly disabling buffering in ResultSet
     * if false, explicitly disabled
     * if null, default state - nothing, but can buffer until iteration started
     * if array, already buffering
     * @var mixed
     */
    protected $buffer = null;

    /**
     * @var null|int
     */
    protected $count = null;

    /**
     * @var Iterator|IteratorAggregate|ResultInterface
     */
    protected $dataSource = null;

    /**
     * @var int
     */
    protected $fieldCount = null;

    /**
     * @var int
     */
    protected $position = 0;

    /**
     * Set the data source for the result set
     *
     * @param  array|Iterator|IteratorAggregate|ResultInterface $dataSource
     * @return ResultSet
     * @throws Exception\InvalidArgumentException
     */
    public function initialize($dataSource)
    {
        // reset buffering
        if (is_array($this->buffer)) {
            $this->buffer = array();
        }

        if ($dataSource instanceof ResultInterface) {
            $this->count = $dataSource->count();
            $this->fieldCount = $dataSource->getFieldCount();
            $this->dataSource = $dataSource;
            if ($dataSource->isBuffered()) {
                $this->buffer = -1;
            }
            if (is_array($this->buffer)) {
                $this->dataSource->rewind();
            }
            return $this;
        }

        if (is_array($dataSource)) {
            // its safe to get numbers from an array
            $first = current($dataSource);
            reset($dataSource);
            $this->count = count($dataSource);
            $this->fieldCount = count($first);
            $this->dataSource = new ArrayIterator($dataSource);
            $this->buffer = -1; // array's are a natural buffer
        } elseif ($dataSource instanceof IteratorAggregate) {
            $this->dataSource = $dataSource->getIterator();
        } elseif ($dataSource instanceof Iterator) {
            $this->dataSource = $dataSource;
        } else {
            throw new Exception\InvalidArgumentException('DataSource provided is not an array, nor does it implement Iterator or IteratorAggregate');
        }

        if ($this->count === null && $this->dataSource instanceof Countable) {
            $this->count = $this->dataSource->count();
        }

        return $this;
    }

    public function buffer()
    {
        if ($this->buffer === -2) {
            throw new Exception\RuntimeException('Buffering must be enabled before iteration is started');
        } elseif ($this->buffer === null) {
            $this->buffer = array();
            if ($this->dataSource instanceof ResultInterface) {
                $this->dataSource->rewind();
            }
        }
        return $this;
    }

    public function isBuffered()
    {
        if ($this->buffer === -1 || is_array($this->buffer)) {
            return true;
        }
        return false;
    }

    /**
     * Get the data source used to create the result set
     *
     * @return null|Iterator
     */
    public function getDataSource()
    {
        return $this->dataSource;
    }

    /**
     * Retrieve count of fields in individual rows of the result set
     *
     * @return int
     */
    public function getFieldCount()
    {
        if (null !== $this->fieldCount) {
            return $this->fieldCount;
        }

        $dataSource = $this->getDataSource();
        if (null === $dataSource) {
            return 0;
        }

        $dataSource->rewind();
        if (!$dataSource->valid()) {
            $this->fieldCount = 0;
            return 0;
        }

        $row = $dataSource->current();
        if (is_object($row) && $row instanceof Countable) {
            $this->fieldCount = $row->count();
            return $this->fieldCount;
        }

        $row = (array) $row;
        $this->fieldCount = count($row);
        return $this->fieldCount;
    }

    /**
     * Iterator: move pointer to next item
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function next()
    {
        if ($this->buffer === null) {
            $this->buffer = -2; // implicitly disable buffering from here on
        }
        if (!is_array($this->buffer) || $this->position == $this->dataSource->key()) {
            $this->dataSource->next();
        }
        $this->position++;
    }

    /**
     * Iterator: retrieve current key
     *
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->position;
    }

    /**
     * Iterator: get current item
     *
     * @return array
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        if ($this->buffer === null) {
            $this->buffer = -2; // implicitly disable buffering from here on
        } elseif (is_array($this->buffer) && isset($this->buffer[$this->position])) {
            return $this->buffer[$this->position];
        }
        $data = $this->dataSource->current();
        if (is_array($this->buffer)) {
            $this->buffer[$this->position] = $data;
        }
        return $data;
    }

    /**
     * Iterator: is pointer valid?
     *
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function valid()
    {
        if (is_array($this->buffer) && isset($this->buffer[$this->position])) {
            return true;
        }
        if ($this->dataSource instanceof Iterator) {
            return $this->dataSource->valid();
        } else {
            $key = key($this->dataSource);
            return ($key !== null);
        }
    }

    /**
     * Iterator: rewind
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function rewind()
    {
        if (!is_array($this->buffer)) {
            if ($this->dataSource instanceof Iterator) {
                $this->dataSource->rewind();
            } else {
                reset($this->dataSource);
            }
        }
        $this->position = 0;
    }

    /**
     * Countable: return count of rows
     *
     * @return int
     */
    #[\ReturnTypeWillChange]
    public function count()
    {
        if ($this->count !== null) {
            return $this->count;
        }
        $this->count = count($this->dataSource);
        return $this->count;
    }

    /**
     * Cast result set to array of arrays
     *
     * @return array
     * @throws Exception\RuntimeException if any row is not castable to an array
     */
    public function toArray()
    {
        $return = array();
        foreach ($this as $row) {
            if (is_array($row)) {
                $return[] = $row;
            } elseif (method_exists($row, 'toArray')) {
                $return[] = $row->toArray();
            } elseif (method_exists($row, 'getArrayCopy')) {
                $return[] = $row->getArrayCopy();
            } else {
                throw new Exception\RuntimeException(
                    'Rows as part of this DataSource, with type ' . gettype($row) . ' cannot be cast to an array'
                );
            }
        }
        return $return;
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\ResultSet;

use ArrayObject;

class ResultSet extends AbstractResultSet
{
    const TYPE_ARRAYOBJECT = 'arrayobject';
    const TYPE_ARRAY  = 'array';

    /**
     * Allowed return types
     *
     * @var array
     */
    protected $allowedReturnTypes = array(
        self::TYPE_ARRAYOBJECT,
        self::TYPE_ARRAY,
    );

    /**
     * @var ArrayObject
     */
    protected $arrayObjectPrototype = null;

    /**
     * Return type to use when returning an object from the set
     *
     * @var ResultSet::TYPE_ARRAYOBJECT|ResultSet::TYPE_ARRAY
     */
    protected $returnType = self::TYPE_ARRAYOBJECT;

    /**
     * Constructor
     *
     * @param string           $returnType
     * @param null|ArrayObject $arrayObjectPrototype
     */
    public function __construct($returnType = self::TYPE_ARRAYOBJECT, $arrayObjectPrototype = null)
    {
        $this->returnType = (in_array($returnType, array(self::TYPE_ARRAY, self::TYPE_ARRAYOBJECT))) ? $returnType : self::TYPE_ARRAYOBJECT;
        if ($this->returnType === self::TYPE_ARRAYOBJECT) {
            $this->setArrayObjectPrototype(($arrayObjectPrototype) ?: new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS));
        }
    }

    /**
     * Set the row object prototype
     *
     * @param  ArrayObject $arrayObjectPrototype
     * @throws Exception\InvalidArgumentException
     * @return ResultSet
     */
    public function setArrayObjectPrototype($arrayObjectPrototype)
    {
        if (!is_object($arrayObjectPrototype)
            || (!$arrayObjectPrototype instanceof ArrayObject && !method_exists($arrayObjectPrototype, 'exchangeArray'))

        ) {
            throw new Exception\InvalidArgumentException('Object must be of type ArrayObject, or at least implement exchangeArray');
        }
        $this->arrayObjectPrototype = $arrayObjectPrototype;
        return $this;
    }

    /**
     * Get the row object prototype
     *
     * @return ArrayObject
     */
    public function getArrayObjectPrototype()
    {
        return $this->arrayObjectPrototype;
    }

    /**
     * Get the return type to use when returning objects from the set
     *
     * @return string
     */
    public function getReturnType()
    {
        return $this->returnType;
    }

    /**
     * @return array|\ArrayObject|null
     */
    public function current()
    {
        $data = parent::current();

        if ($this->returnType === self::TYPE_ARRAYOBJECT && is_array($data)) {
            /** @var $ao ArrayObject */
            $ao = clone $this->arrayObjectPrototype;
            if ($ao instanceof ArrayObject || method_exists($ao, 'exchangeArray')) {
                $ao->exchangeArray($data);
            }
            return $ao;
        }

        return $data;
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache;

use Traversable;
use Zend\EventManager\EventsCapableInterface;
use Zend\Stdlib\ArrayUtils;

abstract class StorageFactory
{
    /**
     * Plugin manager for loading adapters
     *
     * @var null|Storage\AdapterPluginManager
     */
    protected static $adapters = null;

    /**
     * Plugin manager for loading plugins
     *
     * @var null|Storage\PluginManager
     */
    protected static $plugins = null;

    /**
     * The storage factory
     * This can instantiate storage adapters and plugins.
     *
     * @param array|Traversable $cfg
     * @return Storage\StorageInterface
     * @throws Exception\InvalidArgumentException
     */
    public static function factory($cfg)
    {
        if ($cfg instanceof Traversable) {
            $cfg = ArrayUtils::iteratorToArray($cfg);
        }

        if (!is_array($cfg)) {
            throw new Exception\InvalidArgumentException(
                'The factory needs an associative array '
                . 'or a Traversable object as an argument'
            );
        }

        // instantiate the adapter
        if (!isset($cfg['adapter'])) {
            throw new Exception\InvalidArgumentException('Missing "adapter"');
        }
        $adapterName    = $cfg['adapter'];
        $adapterOptions = [];
        if (is_array($cfg['adapter'])) {
            if (!isset($cfg['adapter']['name'])) {
                throw new Exception\InvalidArgumentException('Missing "adapter.name"');
            }

            $adapterName    = $cfg['adapter']['name'];
            $adapterOptions = isset($cfg['adapter']['options']) ? $cfg['adapter']['options'] : [];
        }
        if (isset($cfg['options'])) {
            $adapterOptions = array_merge($adapterOptions, $cfg['options']);
        }

        $adapter = static::adapterFactory((string) $adapterName, $adapterOptions);

        // add plugins
        if (isset($cfg['plugins'])) {
            if (!$adapter instanceof EventsCapableInterface) {
                throw new Exception\RuntimeException(sprintf(
                    "The adapter '%s' doesn't implement '%s' and therefore can't handle plugins",
                    get_class($adapter),
                    'Zend\EventManager\EventsCapableInterface'
                ));
            }

            if (!is_array($cfg['plugins'])) {
                throw new Exception\InvalidArgumentException(
                    'Plugins needs to be an array'
                );
            }

            foreach ($cfg['plugins'] as $k => $v) {
                $pluginPrio = 1; // default priority

                if (is_string($k)) {
                    if (!is_array($v)) {
                        throw new Exception\InvalidArgumentException(
                            "'plugins.{$k}' needs to be an array"
                        );
                    }
                    $pluginName    = $k;
                    $pluginOptions = $v;
                } elseif (is_array($v)) {
                    if (!isset($v['name'])) {
                        throw new Exception\InvalidArgumentException("Invalid plugins[{$k}] or missing plugins[{$k}].name");
                    }
                    $pluginName = (string) $v['name'];

                    if (isset($v['options'])) {
                        $pluginOptions = $v['options'];
                    } else {
                        $pluginOptions = [];
                    }

                    if (isset($v['priority'])) {
                        $pluginPrio = $v['priority'];
                    }
                } else {
                    $pluginName    = $v;
                    $pluginOptions = [];
                }

                $plugin = static::pluginFactory($pluginName, $pluginOptions);
                $adapter->addPlugin($plugin, $pluginPrio);
            }
        }

        return $adapter;
    }

    /**
     * Instantiate a storage adapter
     *
     * @param  string|Storage\StorageInterface                  $adapterName
     * @param  array|Traversable|Storage\Adapter\AdapterOptions $options
     * @return Storage\StorageInterface
     * @throws Exception\RuntimeException
     */
    public static function adapterFactory($adapterName, $options = [])
    {
        if ($adapterName instanceof Storage\StorageInterface) {
            // $adapterName is already an adapter object
            $adapter = $adapterName;
        } else {
            $adapter = static::getAdapterPluginManager()->get($adapterName);
        }

        if ($options) {
            $adapter->setOptions($options);
        }

        return $adapter;
    }

    /**
     * Get the adapter plugin manager
     *
     * @return Storage\AdapterPluginManager
     */
    public static function getAdapterPluginManager()
    {
        if (static::$adapters === null) {
            static::$adapters = new Storage\AdapterPluginManager();
        }
        return static::$adapters;
    }

    /**
     * Change the adapter plugin manager
     *
     * @param  Storage\AdapterPluginManager $adapters
     * @return void
     */
    public static function setAdapterPluginManager(Storage\AdapterPluginManager $adapters)
    {
        static::$adapters = $adapters;
    }

    /**
     * Resets the internal adapter plugin manager
     *
     * @return void
     */
    public static function resetAdapterPluginManager()
    {
        static::$adapters = null;
    }

    /**
     * Instantiate a storage plugin
     *
     * @param string|Storage\Plugin\PluginInterface     $pluginName
     * @param array|Traversable|Storage\Plugin\PluginOptions $options
     * @return Storage\Plugin\PluginInterface
     * @throws Exception\RuntimeException
     */
    public static function pluginFactory($pluginName, $options = [])
    {
        if ($pluginName instanceof Storage\Plugin\PluginInterface) {
            // $pluginName is already a plugin object
            $plugin = $pluginName;
        } else {
            $plugin = static::getPluginManager()->get($pluginName);
        }

        if (!$options instanceof Storage\Plugin\PluginOptions) {
            $options = new Storage\Plugin\PluginOptions($options);
        }

        if ($options) {
            $plugin->setOptions($options);
        }

        return $plugin;
    }

    /**
     * Get the plugin manager
     *
     * @return Storage\PluginManager
     */
    public static function getPluginManager()
    {
        if (static::$plugins === null) {
            static::$plugins = new Storage\PluginManager();
        }
        return static::$plugins;
    }

    /**
     * Change the plugin manager
     *
     * @param  Storage\PluginManager $plugins
     * @return void
     */
    public static function setPluginManager(Storage\PluginManager $plugins)
    {
        static::$plugins = $plugins;
    }

    /**
     * Resets the internal plugin manager
     *
     * @return void
     */
    public static function resetPluginManager()
    {
        static::$plugins = null;
    }
}

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace Interop\Container;

use Interop\Container\Exception\ContainerException;
use Interop\Container\Exception\NotFoundException;

/**
 * Describes the interface of a container that exposes methods to read its entries.
 */
interface ContainerInterface
{
    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws NotFoundException  No entry was found for this identifier.
     * @throws ContainerException Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public function get($id);

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return boolean
     */
    public function has($id);
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\ServiceManager;

/**
 * Service locator interface
 */
interface ServiceLocatorInterface
{
    /**
     * Retrieve a registered instance
     *
     * @param  string  $name
     * @throws Exception\ServiceNotFoundException
     * @return object|array
     */
    public function get($name);

    /**
     * Check for a registered instance
     *
     * @param  string|array  $name
     * @return bool
     */
    public function has($name);
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\ServiceManager;

interface ServiceLocatorAwareInterface
{
    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator);

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator();
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\ServiceManager;

use Interop\Container\ContainerInterface;

class ServiceManager implements ServiceLocatorInterface, ContainerInterface
{
    /**@#+
     * Constants
     */
    const SCOPE_PARENT = 'parent';
    const SCOPE_CHILD = 'child';
    /**@#-*/

    /**
     * Lookup for canonicalized names.
     *
     * @var array
     */
    protected $canonicalNames = [];

    /**
     * @var bool
     */
    protected $allowOverride = false;

    /**
     * @var array
     */
    protected $invokableClasses = [];

    /**
     * @var string|callable|\Closure|FactoryInterface[]
     */
    protected $factories = [];

    /**
     * @var AbstractFactoryInterface[]
     */
    protected $abstractFactories = [];

    /**
     * @var array[]
     */
    protected $delegators = [];

    /**
     * @var array
     */
    protected $pendingAbstractFactoryRequests = [];

    /**
     * @var integer
     */
    protected $nestedContextCounter = -1;

    /**
     * @var array
     */
    protected $nestedContext = [];

    /**
     * @var array
     */
    protected $shared = [];

    /**
     * Registered services and cached values
     *
     * @var array
     */
    protected $instances = [];

    /**
     * @var array
     */
    protected $aliases = [];

    /**
     * @var array
     */
    protected $initializers = [];

    /**
     * @var ServiceManager[]
     */
    protected $peeringServiceManagers = [];

    /**
     * Whether or not to share by default
     *
     * @var bool
     */
    protected $shareByDefault = true;

    /**
     * @var bool
     */
    protected $retrieveFromPeeringManagerFirst = false;

    /**
     * @var bool Track whether not to throw exceptions during create()
     */
    protected $throwExceptionInCreate = true;

    /**
     * @var array map of characters to be replaced through strtr
     */
    protected $canonicalNamesReplacements = ['-' => '', '_' => '', ' ' => '', '\\' => '', '/' => ''];

    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceManagerCaller;

    /**
     * Constructor
     *
     * @param ConfigInterface $config
     */
    public function __construct(?ConfigInterface $config = null)
    {
        if ($config) {
            $config->configureServiceManager($this);
        }
    }

    /**
     * Set allow override
     *
     * @param $allowOverride
     * @return ServiceManager
     */
    public function setAllowOverride($allowOverride)
    {
        $this->allowOverride = (bool) $allowOverride;
        return $this;
    }

    /**
     * Get allow override
     *
     * @return bool
     */
    public function getAllowOverride()
    {
        return $this->allowOverride;
    }

    /**
     * Set flag indicating whether services are shared by default
     *
     * @param  bool $shareByDefault
     * @return ServiceManager
     * @throws Exception\RuntimeException if allowOverride is false
     */
    public function setShareByDefault($shareByDefault)
    {
        if ($this->allowOverride === false) {
            throw new Exception\RuntimeException(sprintf(
                '%s: cannot alter default shared service setting; container is marked immutable (allow_override is false)',
                get_class($this) . '::' . __FUNCTION__
            ));
        }
        $this->shareByDefault = (bool) $shareByDefault;
        return $this;
    }

    /**
     * Are services shared by default?
     *
     * @return bool
     */
    public function shareByDefault()
    {
        return $this->shareByDefault;
    }

    /**
     * Set throw exceptions in create
     *
     * @param  bool $throwExceptionInCreate
     * @return ServiceManager
     */
    public function setThrowExceptionInCreate($throwExceptionInCreate)
    {
        $this->throwExceptionInCreate = $throwExceptionInCreate;
        return $this;
    }

    /**
     * Get throw exceptions in create
     *
     * @return bool
     */
    public function getThrowExceptionInCreate()
    {
        return $this->throwExceptionInCreate;
    }

    /**
     * Set flag indicating whether to pull from peering manager before attempting creation
     *
     * @param  bool $retrieveFromPeeringManagerFirst
     * @return ServiceManager
     */
    public function setRetrieveFromPeeringManagerFirst($retrieveFromPeeringManagerFirst = true)
    {
        $this->retrieveFromPeeringManagerFirst = (bool) $retrieveFromPeeringManagerFirst;
        return $this;
    }

    /**
     * Should we retrieve from the peering manager prior to attempting to create a service?
     *
     * @return bool
     */
    public function retrieveFromPeeringManagerFirst()
    {
        return $this->retrieveFromPeeringManagerFirst;
    }

    /**
     * Set invokable class
     *
     * @param  string  $name
     * @param  string  $invokableClass
     * @param  bool $shared
     * @return ServiceManager
     * @throws Exception\InvalidServiceNameException
     */
    public function setInvokableClass($name, $invokableClass, $shared = null)
    {
        $cName = $this->canonicalizeName($name);

        if ($this->has([$cName, $name], false)) {
            if ($this->allowOverride === false) {
                throw new Exception\InvalidServiceNameException(sprintf(
                    'A service by the name or alias "%s" already exists and cannot be overridden; please use an alternate name',
                    $name
                ));
            }
            $this->unregisterService($cName);
        }

        if ($shared === null) {
            $shared = $this->shareByDefault;
        }

        $this->invokableClasses[$cName] = $invokableClass;
        $this->shared[$cName]           = (bool) $shared;

        return $this;
    }

    /**
     * Set factory
     *
     * @param  string                           $name
     * @param  string|FactoryInterface|callable $factory
     * @param  bool                             $shared
     * @return ServiceManager
     * @throws Exception\InvalidArgumentException
     * @throws Exception\InvalidServiceNameException
     */
    public function setFactory($name, $factory, $shared = null)
    {
        $cName = $this->canonicalizeName($name);

        if (!($factory instanceof FactoryInterface || is_string($factory) || is_callable($factory))) {
            throw new Exception\InvalidArgumentException(
                'Provided abstract factory must be the class name of an abstract factory or an instance of an AbstractFactoryInterface.'
            );
        }

        if ($this->has([$cName, $name], false)) {
            if ($this->allowOverride === false) {
                throw new Exception\InvalidServiceNameException(sprintf(
                    'A service by the name or alias "%s" already exists and cannot be overridden, please use an alternate name',
                    $name
                ));
            }
            $this->unregisterService($cName);
        }

        if ($shared === null) {
            $shared = $this->shareByDefault;
        }

        $this->factories[$cName] = $factory;
        $this->shared[$cName]    = (bool) $shared;

        return $this;
    }

    /**
     * Add abstract factory
     *
     * @param  AbstractFactoryInterface|string $factory
     * @param  bool                            $topOfStack
     * @return ServiceManager
     * @throws Exception\InvalidArgumentException if the abstract factory is invalid
     */
    public function addAbstractFactory($factory, $topOfStack = true)
    {
        if (!$factory instanceof AbstractFactoryInterface && is_string($factory)) {
            $factory = new $factory();
        }

        if (!$factory instanceof AbstractFactoryInterface) {
            throw new Exception\InvalidArgumentException(
                'Provided abstract factory must be the class name of an abstract'
                . ' factory or an instance of an AbstractFactoryInterface.'
            );
        }

        if ($topOfStack) {
            array_unshift($this->abstractFactories, $factory);
        } else {
            array_push($this->abstractFactories, $factory);
        }
        return $this;
    }

    /**
     * Sets the given service name as to be handled by a delegator factory
     *
     * @param  string $serviceName          name of the service being the delegate
     * @param  string $delegatorFactoryName name of the service being the delegator factory
     *
     * @return ServiceManager
     */
    public function addDelegator($serviceName, $delegatorFactoryName)
    {
        $cName = $this->canonicalizeName($serviceName);

        if (!isset($this->delegators[$cName])) {
            $this->delegators[$cName] = [];
        }

        $this->delegators[$cName][] = $delegatorFactoryName;

        return $this;
    }

    /**
     * Add initializer
     *
     * @param  callable|InitializerInterface $initializer
     * @param  bool                          $topOfStack
     * @return ServiceManager
     * @throws Exception\InvalidArgumentException
     */
    public function addInitializer($initializer, $topOfStack = true)
    {
        if (!($initializer instanceof InitializerInterface || is_callable($initializer))) {
            if (is_string($initializer)) {
                $initializer = new $initializer;
            }

            if (!($initializer instanceof InitializerInterface || is_callable($initializer))) {
                throw new Exception\InvalidArgumentException('$initializer should be callable.');
            }
        }

        if ($topOfStack) {
            array_unshift($this->initializers, $initializer);
        } else {
            array_push($this->initializers, $initializer);
        }
        return $this;
    }

    /**
     * Register a service with the locator
     *
     * @param  string  $name
     * @param  mixed   $service
     * @return ServiceManager
     * @throws Exception\InvalidServiceNameException
     */
    public function setService($name, $service)
    {
        $cName = $this->canonicalizeName($name);

        if ($this->has($cName, false)) {
            if ($this->allowOverride === false) {
                throw new Exception\InvalidServiceNameException(sprintf(
                    '%s: A service by the name "%s" or alias already exists and cannot be overridden, please use an alternate name.',
                    get_class($this) . '::' . __FUNCTION__,
                    $name
                ));
            }
            $this->unregisterService($cName);
        }

        $this->instances[$cName] = $service;

        return $this;
    }

    /**
     * @param  string $name
     * @param  bool   $isShared
     * @return ServiceManager
     * @throws Exception\ServiceNotFoundException
     */
    public function setShared($name, $isShared)
    {
        $cName = $this->canonicalizeName($name);

        if (
            !isset($this->invokableClasses[$cName])
            && !isset($this->factories[$cName])
            && !$this->canCreateFromAbstractFactory($cName, $name)
        ) {
            throw new Exception\ServiceNotFoundException(sprintf(
                '%s: A service by the name "%s" was not found and could not be marked as shared',
                get_class($this) . '::' . __FUNCTION__,
                $name
            ));
        }

        $this->shared[$cName] = (bool) $isShared;
        return $this;
    }

    /**
     * @param  string $name
     * @return bool
     * @throws Exception\ServiceNotFoundException
     */
    public function isShared($name)
    {
        $cName = $this->canonicalizeName($name);

        if (!$this->has($name)) {
            throw new Exception\ServiceNotFoundException(sprintf(
                '%s: A service by the name "%s" was not found',
                get_class($this) . '::' . __FUNCTION__,
                $name
            ));
        }

        if (!isset($this->shared[$cName])) {
            return $this->shareByDefault();
        }

        return $this->shared[$cName];
    }

    /**
     * Resolve the alias for the given canonical name
     *
     * @param  string $cName The canonical name to resolve
     * @return string The resolved canonical name
     */
    protected function resolveAlias($cName)
    {
        $stack = [];

        while ($this->hasAlias($cName)) {
            if (isset($stack[$cName])) {
                throw new Exception\CircularReferenceException(sprintf(
                    'Circular alias reference: %s -> %s',
                    implode(' -> ', $stack),
                    $cName
                ));
            }

            $stack[$cName] = $cName;
            $cName = $this->aliases[$cName];
        }

        return $cName;
    }

    /**
     * Retrieve a registered instance
     *
     * @param  string  $name
     * @param  bool    $usePeeringServiceManagers
     * @throws Exception\ServiceNotFoundException
     * @return object|array
     */
    public function get($name, $usePeeringServiceManagers = true)
    {
        // inlined code from ServiceManager::canonicalizeName for performance
        if (isset($this->canonicalNames[$name])) {
            $cName = $this->canonicalNames[$name];
        } else {
            $cName = $this->canonicalizeName($name);
        }

        $isAlias = false;

        if ($this->hasAlias($cName)) {
            $isAlias = true;
            $cName = $this->resolveAlias($cName);
        }

        $instance = null;

        if ($usePeeringServiceManagers && $this->retrieveFromPeeringManagerFirst) {
            $instance = $this->retrieveFromPeeringManager($name);

            if (null !== $instance) {
                return $instance;
            }
        }

        if (isset($this->instances[$cName])) {
            return $this->instances[$cName];
        }

        if (!$instance) {
            $this->checkNestedContextStart($cName);
            if (
                isset($this->invokableClasses[$cName])
                || isset($this->factories[$cName])
                || isset($this->aliases[$cName])
                || $this->canCreateFromAbstractFactory($cName, $name)
            ) {
                $instance = $this->create([$cName, $name]);
            } elseif ($isAlias && $this->canCreateFromAbstractFactory($name, $cName)) {
                /*
                 * case of an alias leading to an abstract factory :
                 * 'my-alias' => 'my-abstract-defined-service'
                 *     $name = 'my-alias'
                 *     $cName = 'my-abstract-defined-service'
                 */
                $instance = $this->create([$name, $cName]);
            } elseif ($usePeeringServiceManagers && !$this->retrieveFromPeeringManagerFirst) {
                $instance = $this->retrieveFromPeeringManager($name);
            }
            $this->checkNestedContextStop();
        }

        // Still no instance? raise an exception
        if ($instance === null) {
            $this->checkNestedContextStop(true);
            if ($isAlias) {
                throw new Exception\ServiceNotFoundException(sprintf(
                    'An alias "%s" was requested but no service could be found.',
                    $name
                ));
            }

            throw new Exception\ServiceNotFoundException(sprintf(
                '%s was unable to fetch or create an instance for %s',
                get_class($this) . '::' . __FUNCTION__,
                $name
            ));
        }

        if (
            ($this->shareByDefault && !isset($this->shared[$cName]))
            || (isset($this->shared[$cName]) && $this->shared[$cName] === true)
        ) {
            $this->instances[$cName] = $instance;
        }

        return $instance;
    }

    /**
     * Create an instance of the requested service
     *
     * @param  string|array $name
     *
     * @return bool|object
     */
    public function create($name)
    {
        if (is_array($name)) {
            list($cName, $rName) = $name;
        } else {
            $rName = $name;

            // inlined code from ServiceManager::canonicalizeName for performance
            if (isset($this->canonicalNames[$rName])) {
                $cName = $this->canonicalNames[$name];
            } else {
                $cName = $this->canonicalizeName($name);
            }
        }

        if (isset($this->delegators[$cName])) {
            return $this->createDelegatorFromFactory($cName, $rName);
        }

        return $this->doCreate($rName, $cName);
    }

    /**
     * Creates a callback that uses a delegator to create a service
     *
     * @param DelegatorFactoryInterface|callable $delegatorFactory the delegator factory
     * @param string                             $rName            requested service name
     * @param string                             $cName            canonical service name
     * @param callable                           $creationCallback callback for instantiating the real service
     *
     * @return callable
     */
    private function createDelegatorCallback($delegatorFactory, $rName, $cName, $creationCallback)
    {
        return function () use ($delegatorFactory, $rName, $cName, $creationCallback) {
            return $delegatorFactory instanceof DelegatorFactoryInterface
                ? $delegatorFactory->createDelegatorWithName($this, $cName, $rName, $creationCallback)
                : $delegatorFactory($this, $cName, $rName, $creationCallback);
        };
    }

    /**
     * Actually creates the service
     *
     * @param string $rName real service name
     * @param string $cName canonicalized service name
     *
     * @return bool|mixed|null|object
     * @throws Exception\ServiceNotFoundException
     *
     */
    protected function doCreate($rName, $cName)
    {
        $instance = null;

        if (isset($this->factories[$cName])) {
            $instance = $this->createFromFactory($cName, $rName);
        }

        if ($instance === null && isset($this->invokableClasses[$cName])) {
            $instance = $this->createFromInvokable($cName, $rName);
        }
        $this->checkNestedContextStart($cName);
        if ($instance === null && $this->canCreateFromAbstractFactory($cName, $rName)) {
            $instance = $this->createFromAbstractFactory($cName, $rName);
        }
        $this->checkNestedContextStop();

        if ($instance === null && $this->throwExceptionInCreate) {
            $this->checkNestedContextStop(true);
            throw new Exception\ServiceNotFoundException(sprintf(
                'No valid instance was found for %s%s',
                $cName,
                ($rName ? '(alias: ' . $rName . ')' : '')
            ));
        }

        // Do not call initializers if we do not have an instance
        if ($instance === null) {
            return $instance;
        }

        foreach ($this->initializers as $initializer) {
            if ($initializer instanceof InitializerInterface) {
                $initializer->initialize($instance, $this);
            } else {
                call_user_func($initializer, $instance, $this);
            }
        }

        return $instance;
    }

    /**
     * Determine if we can create an instance.
     * Proxies to has()
     *
     * @param  string|array $name
     * @param  bool         $checkAbstractFactories
     * @return bool
     * @deprecated this method is being deprecated as of zendframework 2.3, and may be removed in future major versions
     */
    public function canCreate($name, $checkAbstractFactories = true)
    {
        trigger_error(sprintf('%s is deprecated; please use %s::has', __METHOD__, __CLASS__), E_USER_DEPRECATED);
        return $this->has($name, $checkAbstractFactories, false);
    }

    /**
     * Determine if an instance exists.
     *
     * @param  string|array  $name  An array argument accepts exactly two values.
     *                              Example: array('canonicalName', 'requestName')
     * @param  bool          $checkAbstractFactories
     * @param  bool          $usePeeringServiceManagers
     * @return bool
     */
    public function has($name, $checkAbstractFactories = true, $usePeeringServiceManagers = true)
    {
        if (is_string($name)) {
            $rName = $name;

            // inlined code from ServiceManager::canonicalizeName for performance
            if (isset($this->canonicalNames[$rName])) {
                $cName = $this->canonicalNames[$rName];
            } else {
                $cName = $this->canonicalizeName($name);
            }
        } elseif (is_array($name) && count($name) >= 2) {
            list($cName, $rName) = $name;
        } else {
            return false;
        }

        if (isset($this->invokableClasses[$cName])
            || isset($this->factories[$cName])
            || isset($this->aliases[$cName])
            || isset($this->instances[$cName])
            || ($checkAbstractFactories && $this->canCreateFromAbstractFactory($cName, $rName))
        ) {
            return true;
        }

        if ($usePeeringServiceManagers) {
            $caller = $this->serviceManagerCaller;
            foreach ($this->peeringServiceManagers as $peeringServiceManager) {
                // ignore peering service manager if they are the same instance
                if ($caller === $peeringServiceManager) {
                    continue;
                }

                $peeringServiceManager->serviceManagerCaller = $this;

                if ($peeringServiceManager->has($name)) {
                    $peeringServiceManager->serviceManagerCaller = null;
                    return true;
                }

                $peeringServiceManager->serviceManagerCaller = null;
            }
        }

        return false;
    }

    /**
     * Determine if we can create an instance from an abstract factory.
     *
     * @param  string $cName
     * @param  string $rName
     * @return bool
     */
    public function canCreateFromAbstractFactory($cName, $rName)
    {
        if (array_key_exists($cName, $this->nestedContext)) {
            $context = $this->nestedContext[$cName];
            if ($context === false) {
                return false;
            } elseif (is_object($context)) {
                return !isset($this->pendingAbstractFactoryRequests[get_class($context).$cName]);
            }
        }
        $this->checkNestedContextStart($cName);
        // check abstract factories
        $result = false;
        $this->nestedContext[$cName] = false;
        foreach ($this->abstractFactories as $abstractFactory) {
            $pendingKey = get_class($abstractFactory).$cName;
            if (isset($this->pendingAbstractFactoryRequests[$pendingKey])) {
                $result = false;
                break;
            }

            if ($abstractFactory->canCreateServiceWithName($this, $cName, $rName)) {
                $this->nestedContext[$cName] = $abstractFactory;
                $result = true;
                break;
            }
        }
        $this->checkNestedContextStop();
        return $result;
    }

    /**
     * Ensure the alias definition will not result in a circular reference
     *
     * @param  string $alias
     * @param  string $nameOrAlias
     * @throws Exception\CircularReferenceException
     * @return self
     */
    protected function checkForCircularAliasReference($alias, $nameOrAlias)
    {
        $aliases = $this->aliases;
        $aliases[$alias] = $nameOrAlias;
        $stack = [];

        while (isset($aliases[$alias])) {
            if (isset($stack[$alias])) {
                throw new Exception\CircularReferenceException(sprintf(
                    'The alias definition "%s" : "%s" results in a circular reference: "%s" -> "%s"',
                    $alias,
                    $nameOrAlias,
                    implode('" -> "', $stack),
                    $alias
                ));
            }

            $stack[$alias] = $alias;
            $alias = $aliases[$alias];
        }

        return $this;
    }

    /**
     * @param  string $alias
     * @param  string $nameOrAlias
     * @return ServiceManager
     * @throws Exception\ServiceNotFoundException
     * @throws Exception\InvalidServiceNameException
     */
    public function setAlias($alias, $nameOrAlias)
    {
        if (!is_string($alias) || !is_string($nameOrAlias)) {
            throw new Exception\InvalidServiceNameException('Service or alias names must be strings.');
        }

        $cAlias = $this->canonicalizeName($alias);
        $nameOrAlias = $this->canonicalizeName($nameOrAlias);

        if ($alias == '' || $nameOrAlias == '') {
            throw new Exception\InvalidServiceNameException('Invalid service name alias');
        }

        if ($this->allowOverride === false && $this->has([$cAlias, $alias], false)) {
            throw new Exception\InvalidServiceNameException(sprintf(
                'An alias by the name "%s" or "%s" already exists',
                $cAlias,
                $alias
            ));
        }

        if ($this->hasAlias($alias)) {
            $this->checkForCircularAliasReference($cAlias, $nameOrAlias);
        }

        $this->aliases[$cAlias] = $nameOrAlias;
        return $this;
    }

    /**
     * Determine if we have an alias
     *
     * @param  string $alias
     * @return bool
     */
    public function hasAlias($alias)
    {
        return isset($this->aliases[$this->canonicalizeName($alias)]);
    }

    /**
     * Create scoped service manager
     *
     * @param  string $peering
     * @return ServiceManager
     */
    public function createScopedServiceManager($peering = self::SCOPE_PARENT)
    {
        $scopedServiceManager = new ServiceManager();
        if ($peering == self::SCOPE_PARENT) {
            $scopedServiceManager->peeringServiceManagers[] = $this;
        }
        if ($peering == self::SCOPE_CHILD) {
            $this->peeringServiceManagers[] = $scopedServiceManager;
        }
        return $scopedServiceManager;
    }

    /**
     * Add a peering relationship
     *
     * @param  ServiceManager $manager
     * @param  string         $peering
     * @return ServiceManager
     */
    public function addPeeringServiceManager(ServiceManager $manager, $peering = self::SCOPE_PARENT)
    {
        if ($peering == self::SCOPE_PARENT) {
            $this->peeringServiceManagers[] = $manager;
        }
        if ($peering == self::SCOPE_CHILD) {
            $manager->peeringServiceManagers[] = $this;
        }
        return $this;
    }

    /**
     * Canonicalize name
     *
     * @param  string $name
     * @return string
     */
    protected function canonicalizeName($name)
    {
        if (isset($this->canonicalNames[$name])) {
            return $this->canonicalNames[$name];
        }

        // this is just for performance instead of using str_replace
        return $this->canonicalNames[$name] = strtolower(strtr($name, $this->canonicalNamesReplacements));
    }

    /**
     * Create service via callback
     *
     * @param  callable $callable
     * @param  string   $cName
     * @param  string   $rName
     * @throws Exception\ServiceNotCreatedException
     * @throws Exception\ServiceNotFoundException
     * @throws Exception\CircularDependencyFoundException
     * @return object
     */
    protected function createServiceViaCallback($callable, $cName, $rName)
    {
        static $circularDependencyResolver = [];
        $depKey = spl_object_hash($this) . '-' . $cName;

        if (isset($circularDependencyResolver[$depKey])) {
            $circularDependencyResolver = [];
            throw new Exception\CircularDependencyFoundException('Circular dependency for LazyServiceLoader was found for instance ' . $rName);
        }

        try {
            $circularDependencyResolver[$depKey] = true;
            $instance = call_user_func($callable, $this, $cName, $rName);
            unset($circularDependencyResolver[$depKey]);
        } catch (Exception\ServiceNotFoundException $e) {
            unset($circularDependencyResolver[$depKey]);
            throw $e;
        } catch (\Exception $e) {
            unset($circularDependencyResolver[$depKey]);
            throw new Exception\ServiceNotCreatedException(
                sprintf('An exception was raised while creating "%s"; no instance returned', $rName),
                $e->getCode(),
                $e
            );
        }
        if ($instance === null) {
            throw new Exception\ServiceNotCreatedException('The factory was called but did not return an instance.');
        }

        return $instance;
    }

    /**
     * Retrieve a keyed list of all registered services. Handy for debugging!
     *
     * @return array
     */
    public function getRegisteredServices()
    {
        return [
            'invokableClasses' => array_keys($this->invokableClasses),
            'factories' => array_keys($this->factories),
            'aliases' => array_keys($this->aliases),
            'instances' => array_keys($this->instances),
        ];
    }

    /**
     * Retrieve a keyed list of all canonical names. Handy for debugging!
     *
     * @return array
     */
    public function getCanonicalNames()
    {
        return $this->canonicalNames;
    }

    /**
     * Allows to override the canonical names lookup map with predefined
     * values.
     *
     * @param array $canonicalNames
     * @return ServiceManager
     */
    public function setCanonicalNames($canonicalNames)
    {
        $this->canonicalNames = $canonicalNames;

        return $this;
    }

    /**
     * Attempt to retrieve an instance via a peering manager
     *
     * @param  string $name
     * @return mixed
     */
    protected function retrieveFromPeeringManager($name)
    {
        if (null !== ($service = $this->loopPeeringServiceManagers($name))) {
            return $service;
        }

        $name = $this->canonicalizeName($name);

        if ($this->hasAlias($name)) {
            do {
                $name = $this->aliases[$name];
            } while ($this->hasAlias($name));
        }

        if (null !== ($service = $this->loopPeeringServiceManagers($name))) {
            return $service;
        }

        return;
    }

    /**
     * Loop over peering service managers.
     *
     * @param string $name
     * @return mixed
     */
    protected function loopPeeringServiceManagers($name)
    {
        $caller = $this->serviceManagerCaller;

        foreach ($this->peeringServiceManagers as $peeringServiceManager) {
            // ignore peering service manager if they are the same instance
            if ($caller === $peeringServiceManager) {
                continue;
            }

            // pass this instance to peering service manager
            $peeringServiceManager->serviceManagerCaller = $this;

            if ($peeringServiceManager->has($name)) {
                $this->shared[$name] = $peeringServiceManager->isShared($name);
                $instance = $peeringServiceManager->get($name);
                $peeringServiceManager->serviceManagerCaller = null;
                return $instance;
            }

            $peeringServiceManager->serviceManagerCaller = null;
        }

        return;
    }

    /**
     * Attempt to create an instance via an invokable class
     *
     * @param  string $canonicalName
     * @param  string $requestedName
     * @return null|\stdClass
     * @throws Exception\ServiceNotFoundException If resolved class does not exist
     */
    protected function createFromInvokable($canonicalName, $requestedName)
    {
        $invokable = $this->invokableClasses[$canonicalName];
        if (!class_exists($invokable)) {
            throw new Exception\ServiceNotFoundException(sprintf(
                '%s: failed retrieving "%s%s" via invokable class "%s"; class does not exist',
                get_class($this) . '::' . __FUNCTION__,
                $canonicalName,
                ($requestedName ? '(alias: ' . $requestedName . ')' : ''),
                $invokable
            ));
        }
        $instance = new $invokable;
        return $instance;
    }

    /**
     * Attempt to create an instance via a factory
     *
     * @param  string $canonicalName
     * @param  string $requestedName
     * @return mixed
     * @throws Exception\ServiceNotCreatedException If factory is not callable
     */
    protected function createFromFactory($canonicalName, $requestedName)
    {
        $factory = $this->factories[$canonicalName];
        if (is_string($factory) && class_exists($factory, true)) {
            $factory = new $factory;
            $this->factories[$canonicalName] = $factory;
        }
        if ($factory instanceof FactoryInterface) {
            $instance = $this->createServiceViaCallback([$factory, 'createService'], $canonicalName, $requestedName);
        } elseif (is_callable($factory)) {
            $instance = $this->createServiceViaCallback($factory, $canonicalName, $requestedName);
        } else {
            throw new Exception\ServiceNotCreatedException(sprintf(
                'While attempting to create %s%s an invalid factory was registered for this instance type.',
                $canonicalName,
                ($requestedName ? '(alias: ' . $requestedName . ')' : '')
            ));
        }
        return $instance;
    }

    /**
     * Attempt to create an instance via an abstract factory
     *
     * @param  string $canonicalName
     * @param  string $requestedName
     * @return object|null
     * @throws Exception\ServiceNotCreatedException If abstract factory is not callable
     */
    protected function createFromAbstractFactory($canonicalName, $requestedName)
    {
        if (isset($this->nestedContext[$canonicalName])) {
            $abstractFactory = $this->nestedContext[$canonicalName];
            $pendingKey = get_class($abstractFactory).$canonicalName;
            try {
                $this->pendingAbstractFactoryRequests[$pendingKey] = true;
                $instance = $this->createServiceViaCallback(
                    [$abstractFactory, 'createServiceWithName'],
                    $canonicalName,
                    $requestedName
                );
                unset($this->pendingAbstractFactoryRequests[$pendingKey]);
                return $instance;
            } catch (\Exception $e) {
                unset($this->pendingAbstractFactoryRequests[$pendingKey]);
                $this->checkNestedContextStop(true);
                throw new Exception\ServiceNotCreatedException(
                    sprintf(
                        'An abstract factory could not create an instance of %s%s.',
                        $canonicalName,
                        ($requestedName ? '(alias: ' . $requestedName . ')' : '')
                    ),
                    $e->getCode(),
                    $e
                );
            }
        }
        return;
    }

    /**
     *
     * @param string $cName
     * @return self
     */
    protected function checkNestedContextStart($cName)
    {
        if ($this->nestedContextCounter === -1 || !isset($this->nestedContext[$cName])) {
            $this->nestedContext[$cName] = null;
        }
        $this->nestedContextCounter++;
        return $this;
    }

    /**
     *
     * @param bool $force
     * @return self
     */
    protected function checkNestedContextStop($force = false)
    {
        if ($force) {
            $this->nestedContextCounter = -1;
            $this->nestedContext = [];
            return $this;
        }

        $this->nestedContextCounter--;
        if ($this->nestedContextCounter === -1) {
            $this->nestedContext = [];
        }
        return $this;
    }

    /**
     * @param $canonicalName
     * @param $requestedName
     * @return mixed
     * @throws Exception\ServiceNotCreatedException
     */
    protected function createDelegatorFromFactory($canonicalName, $requestedName)
    {
        $delegatorsCount    = count($this->delegators[$canonicalName]);
        $creationCallback   = function () use ($requestedName, $canonicalName) {
            return $this->doCreate($requestedName, $canonicalName);
        };

        for ($i = 0; $i < $delegatorsCount; $i += 1) {
            $delegatorFactory = $this->delegators[$canonicalName][$i];

            if (is_string($delegatorFactory)) {
                $delegatorFactory = !$this->has($delegatorFactory) && class_exists($delegatorFactory, true) ?
                    new $delegatorFactory
                    : $this->get($delegatorFactory);
                $this->delegators[$canonicalName][$i] = $delegatorFactory;
            }

            if (!$delegatorFactory instanceof DelegatorFactoryInterface && !is_callable($delegatorFactory)) {
                throw new Exception\ServiceNotCreatedException(sprintf(
                    'While attempting to create %s%s an invalid factory was registered for this instance type.',
                    $canonicalName,
                    ($requestedName ? '(alias: ' . $requestedName . ')' : '')
                ));
            }

            $creationCallback = $this->createDelegatorCallback(
                $delegatorFactory,
                $requestedName,
                $canonicalName,
                $creationCallback
            );
        }

        return $creationCallback($this, $canonicalName, $requestedName, $creationCallback);
    }

    /**
     * Checks if the object has this class as one of its parents
     *
     * @see https://bugs.php.net/bug.php?id=53727
     * @see https://github.com/zendframework/zf2/pull/1807
     *
     * @deprecated since zf 2.3 requires PHP >= 5.3.23
     *
     * @param string $className
     * @param string $type
     * @return bool
     *
     * @deprecated this method is being deprecated as of zendframework 2.2, and may be removed in future major versions
     */
    protected static function isSubclassOf($className, $type)
    {
        return is_subclass_of($className, $type);
    }

    /**
     * Unregister a service
     *
     * Called when $allowOverride is true and we detect that a service being
     * added to the instance already exists. This will remove the duplicate
     * entry, and also any shared flags previously registered.
     *
     * @param  string $canonical
     * @return void
     */
    protected function unregisterService($canonical)
    {
        $types = ['invokableClasses', 'factories', 'aliases'];
        foreach ($types as $type) {
            if (isset($this->{$type}[$canonical])) {
                unset($this->{$type}[$canonical]);
                break;
            }
        }

        if (isset($this->instances[$canonical])) {
            unset($this->instances[$canonical]);
        }

        if (isset($this->shared[$canonical])) {
            unset($this->shared[$canonical]);
        }
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\ServiceManager;

use Exception as BaseException;

/**
 * ServiceManager implementation for managing plugins
 *
 * Automatically registers an initializer which should be used to verify that
 * a plugin instance is of a valid type. Additionally, allows plugins to accept
 * an array of options for the constructor, which can be used to configure
 * the plugin when retrieved. Finally, enables the allowOverride property by
 * default to allow registering factories, aliases, and invokables to take
 * the place of those provided by the implementing class.
 */
abstract class AbstractPluginManager extends ServiceManager implements ServiceLocatorAwareInterface
{
    /**
     * Allow overriding by default
     *
     * @var bool
     */
    protected $allowOverride = true;

    /**
     * Whether or not to auto-add a class as an invokable class if it exists
     *
     * @var bool
     */
    protected $autoAddInvokableClass = true;

    /**
     * Options to use when creating an instance
     *
     * @var mixed
     */
    protected $creationOptions = null;

    /**
     * The main service locator
     *
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * Constructor
     *
     * Add a default initializer to ensure the plugin is valid after instance
     * creation.
     *
     * @param null|ConfigInterface $configuration
     */
    public function __construct(?ConfigInterface $configuration = null)
    {
        parent::__construct($configuration);
        $this->addInitializer(function ($instance) {
            if ($instance instanceof ServiceLocatorAwareInterface) {
                $instance->setServiceLocator($this);
            }
        });
    }

    /**
     * Validate the plugin
     *
     * Checks that the filter loaded is either a valid callback or an instance
     * of FilterInterface.
     *
     * @param  mixed                      $plugin
     * @return void
     * @throws Exception\RuntimeException if invalid
     */
    abstract public function validatePlugin($plugin);

    /**
     * Retrieve a service from the manager by name
     *
     * Allows passing an array of options to use when creating the instance.
     * createFromInvokable() will use these and pass them to the instance
     * constructor if not null and a non-empty array.
     *
     * @param  string $name
     * @param  array  $options
     * @param  bool   $usePeeringServiceManagers
     *
     * @return object
     *
     * @throws Exception\ServiceNotFoundException
     * @throws Exception\ServiceNotCreatedException
     * @throws Exception\RuntimeException
     */
    public function get($name, $options = [], $usePeeringServiceManagers = true)
    {
        $isAutoInvokable = false;

        // Allow specifying a class name directly; registers as an invokable class
        if (!$this->has($name) && $this->autoAddInvokableClass && class_exists($name)) {
            $isAutoInvokable = true;

            $this->setInvokableClass($name, $name);
        }

        $this->creationOptions = $options;

        try {
            $instance = parent::get($name, $usePeeringServiceManagers);
        } catch (Exception\ServiceNotFoundException $exception) {
            $this->tryThrowingServiceLocatorUsageException($name, $isAutoInvokable, $exception);
        } catch (Exception\ServiceNotCreatedException $exception) {
            $this->tryThrowingServiceLocatorUsageException($name, $isAutoInvokable, $exception);
        }

        $this->creationOptions = null;

        try {
            $this->validatePlugin($instance);
        } catch (Exception\RuntimeException $exception) {
            $this->tryThrowingServiceLocatorUsageException($name, $isAutoInvokable, $exception);
        }

        return $instance;
    }

    /**
     * Register a service with the locator.
     *
     * Validates that the service object via validatePlugin() prior to
     * attempting to register it.
     *
     * @param  string                                $name
     * @param  mixed                                 $service
     * @param  bool                                  $shared
     * @return AbstractPluginManager
     * @throws Exception\InvalidServiceNameException
     */
    public function setService($name, $service, $shared = true)
    {
        if ($service) {
            $this->validatePlugin($service);
        }
        parent::setService($name, $service, $shared);

        return $this;
    }

    /**
     * Set the main service locator so factories can have access to it to pull deps
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return AbstractPluginManager
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;

        return $this;
    }

    /**
     * Get the main plugin manager. Useful for fetching dependencies from within factories.
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * Attempt to create an instance via an invokable class
     *
     * Overrides parent implementation by passing $creationOptions to the
     * constructor, if non-null.
     *
     * @param  string                               $canonicalName
     * @param  string                               $requestedName
     * @return null|\stdClass
     * @throws Exception\ServiceNotCreatedException If resolved class does not exist
     */
    protected function createFromInvokable($canonicalName, $requestedName)
    {
        $invokable = $this->invokableClasses[$canonicalName];

        if (!class_exists($invokable)) {
            throw new Exception\ServiceNotFoundException(sprintf(
                '%s: failed retrieving "%s%s" via invokable class "%s"; class does not exist',
                get_class($this) . '::' . __FUNCTION__,
                $canonicalName,
                ($requestedName ? '(alias: ' . $requestedName . ')' : ''),
                $invokable
            ));
        }

        if (null === $this->creationOptions
            || (is_array($this->creationOptions) && empty($this->creationOptions))
        ) {
            $instance = new $invokable();
        } else {
            $instance = new $invokable($this->creationOptions);
        }

        return $instance;
    }

    /**
     * Attempt to create an instance via a factory class
     *
     * Overrides parent implementation by passing $creationOptions to the
     * constructor, if non-null.
     *
     * @param  string                               $canonicalName
     * @param  string                               $requestedName
     * @return mixed
     * @throws Exception\ServiceNotCreatedException If factory is not callable
     */
    protected function createFromFactory($canonicalName, $requestedName)
    {
        $factory            = $this->factories[$canonicalName];
        $hasCreationOptions = !(null === $this->creationOptions || (is_array($this->creationOptions) && empty($this->creationOptions)));

        if (is_string($factory) && class_exists($factory, true)) {
            if (!$hasCreationOptions) {
                $factory = new $factory();
            } else {
                $factory = new $factory($this->creationOptions);
            }

            $this->factories[$canonicalName] = $factory;
        }

        if ($factory instanceof FactoryInterface) {
            $instance = $this->createServiceViaCallback([$factory, 'createService'], $canonicalName, $requestedName);
        } elseif (is_callable($factory)) {
            $instance = $this->createServiceViaCallback($factory, $canonicalName, $requestedName);
        } else {
            throw new Exception\ServiceNotCreatedException(sprintf(
                'While attempting to create %s%s an invalid factory was registered for this instance type.',
                $canonicalName,
                ($requestedName ? '(alias: ' . $requestedName . ')' : '')
            ));
        }

        return $instance;
    }

    /**
     * Create service via callback
     *
     * @param  callable                                   $callable
     * @param  string                                     $cName
     * @param  string                                     $rName
     * @throws Exception\ServiceNotCreatedException
     * @throws Exception\ServiceNotFoundException
     * @throws Exception\CircularDependencyFoundException
     * @return object
     */
    protected function createServiceViaCallback($callable, $cName, $rName)
    {
        if (is_object($callable)) {
            $factory = $callable;
        } elseif (is_array($callable)) {
            // reset both rewinds and returns the value of the first array element
            $factory = reset($callable);
        }

        if (isset($factory)
            && ($factory instanceof MutableCreationOptionsInterface)
            && is_array($this->creationOptions)
            && !empty($this->creationOptions)
        ) {
            $factory->setCreationOptions($this->creationOptions);
        }

        return parent::createServiceViaCallback($callable, $cName, $rName);
    }

    /**
     * @param string        $serviceName
     * @param bool          $isAutoInvokable
     * @param BaseException $exception
     *
     * @throws BaseException
     * @throws Exception\ServiceLocatorUsageException
     */
    private function tryThrowingServiceLocatorUsageException(
        $serviceName,
        $isAutoInvokable,
        BaseException $exception
    ) {
        if ($isAutoInvokable) {
            $this->unregisterService($this->canonicalizeName($serviceName));
        }

        $serviceLocator = $this->getServiceLocator();

        if ($serviceLocator && $serviceLocator->has($serviceName)) {
            throw Exception\ServiceLocatorUsageException::fromInvalidPluginManagerRequestedServiceName(
                $this,
                $serviceLocator,
                $serviceName,
                $exception
            );
        }

        throw $exception;
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Storage;

use Zend\Cache\Exception;
use Zend\ServiceManager\AbstractPluginManager;

/**
 * Plugin manager implementation for cache storage adapters
 *
 * Enforces that adapters retrieved are instances of
 * StorageInterface. Additionally, it registers a number of default
 * adapters available.
 */
class AdapterPluginManager extends AbstractPluginManager
{
    /**
     * Default set of adapters
     *
     * @var array
     */
    protected $invokableClasses = [
        'apc'            => 'Zend\Cache\Storage\Adapter\Apc',
        'blackhole'      => 'Zend\Cache\Storage\Adapter\BlackHole',
        'dba'            => 'Zend\Cache\Storage\Adapter\Dba',
        'filesystem'     => 'Zend\Cache\Storage\Adapter\Filesystem',
        'memcache'       => 'Zend\Cache\Storage\Adapter\Memcache',
        'memcached'      => 'Zend\Cache\Storage\Adapter\Memcached',
        'memory'         => 'Zend\Cache\Storage\Adapter\Memory',
        'mongodb'        => 'Zend\Cache\Storage\Adapter\MongoDb',
        'redis'          => 'Zend\Cache\Storage\Adapter\Redis',
        'session'        => 'Zend\Cache\Storage\Adapter\Session',
        'xcache'         => 'Zend\Cache\Storage\Adapter\XCache',
        'wincache'       => 'Zend\Cache\Storage\Adapter\WinCache',
        'zendserverdisk' => 'Zend\Cache\Storage\Adapter\ZendServerDisk',
        'zendservershm'  => 'Zend\Cache\Storage\Adapter\ZendServerShm',
    ];

    /**
     * Do not share by default
     *
     * @var array
     */
    protected $shareByDefault = false;

    /**
     * Validate the plugin
     *
     * Checks that the adapter loaded is an instance of StorageInterface.
     *
     * @param  mixed $plugin
     * @return void
     * @throws Exception\RuntimeException if invalid
     */
    public function validatePlugin($plugin)
    {
        if ($plugin instanceof StorageInterface) {
            // we're okay
            return;
        }

        throw new Exception\RuntimeException(sprintf(
            'Plugin of type %s is invalid; must implement %s\StorageInterface',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
            __NAMESPACE__
        ));
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Storage;

use Traversable;

interface StorageInterface
{
    /**
     * Set options.
     *
     * @param array|Traversable|Adapter\AdapterOptions $options
     * @return StorageInterface Fluent interface
     */
    public function setOptions($options);

    /**
     * Get options
     *
     * @return Adapter\AdapterOptions
     */
    public function getOptions();

    /* reading */

    /**
     * Get an item.
     *
     * @param  string  $key
     * @param  bool $success
     * @param  mixed   $casToken
     * @return mixed Data on success, null on failure
     * @throws \Zend\Cache\Exception\ExceptionInterface
     */
    public function getItem($key, & $success = null, & $casToken = null);

    /**
     * Get multiple items.
     *
     * @param  array $keys
     * @return array Associative array of keys and values
     * @throws \Zend\Cache\Exception\ExceptionInterface
     */
    public function getItems(array $keys);

    /**
     * Test if an item exists.
     *
     * @param  string $key
     * @return bool
     * @throws \Zend\Cache\Exception\ExceptionInterface
     */
    public function hasItem($key);

    /**
     * Test multiple items.
     *
     * @param  array $keys
     * @return array Array of found keys
     * @throws \Zend\Cache\Exception\ExceptionInterface
     */
    public function hasItems(array $keys);

    /**
     * Get metadata of an item.
     *
     * @param  string $key
     * @return array|bool Metadata on success, false on failure
     * @throws \Zend\Cache\Exception\ExceptionInterface
     */
    public function getMetadata($key);

    /**
     * Get multiple metadata
     *
     * @param  array $keys
     * @return array Associative array of keys and metadata
     * @throws \Zend\Cache\Exception\ExceptionInterface
     */
    public function getMetadatas(array $keys);

    /* writing */

    /**
     * Store an item.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return bool
     * @throws \Zend\Cache\Exception\ExceptionInterface
     */
    public function setItem($key, $value);

    /**
     * Store multiple items.
     *
     * @param  array $keyValuePairs
     * @return array Array of not stored keys
     * @throws \Zend\Cache\Exception\ExceptionInterface
     */
    public function setItems(array $keyValuePairs);

    /**
     * Add an item.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return bool
     * @throws \Zend\Cache\Exception\ExceptionInterface
     */
    public function addItem($key, $value);

    /**
     * Add multiple items.
     *
     * @param  array $keyValuePairs
     * @return array Array of not stored keys
     * @throws \Zend\Cache\Exception\ExceptionInterface
     */
    public function addItems(array $keyValuePairs);

    /**
     * Replace an existing item.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return bool
     * @throws \Zend\Cache\Exception\ExceptionInterface
     */
    public function replaceItem($key, $value);

    /**
     * Replace multiple existing items.
     *
     * @param  array $keyValuePairs
     * @return array Array of not stored keys
     * @throws \Zend\Cache\Exception\ExceptionInterface
     */
    public function replaceItems(array $keyValuePairs);

    /**
     * Set an item only if token matches
     *
     * It uses the token received from getItem() to check if the item has
     * changed before overwriting it.
     *
     * @param  mixed  $token
     * @param  string $key
     * @param  mixed  $value
     * @return bool
     * @throws \Zend\Cache\Exception\ExceptionInterface
     * @see    getItem()
     * @see    setItem()
     */
    public function checkAndSetItem($token, $key, $value);

    /**
     * Reset lifetime of an item
     *
     * @param  string $key
     * @return bool
     * @throws \Zend\Cache\Exception\ExceptionInterface
     */
    public function touchItem($key);

    /**
     * Reset lifetime of multiple items.
     *
     * @param  array $keys
     * @return array Array of not updated keys
     * @throws \Zend\Cache\Exception\ExceptionInterface
     */
    public function touchItems(array $keys);

    /**
     * Remove an item.
     *
     * @param  string $key
     * @return bool
     * @throws \Zend\Cache\Exception\ExceptionInterface
     */
    public function removeItem($key);

    /**
     * Remove multiple items.
     *
     * @param  array $keys
     * @return array Array of not removed keys
     * @throws \Zend\Cache\Exception\ExceptionInterface
     */
    public function removeItems(array $keys);

    /**
     * Increment an item.
     *
     * @param  string $key
     * @param  int    $value
     * @return int|bool The new value on success, false on failure
     * @throws \Zend\Cache\Exception\ExceptionInterface
     */
    public function incrementItem($key, $value);

    /**
     * Increment multiple items.
     *
     * @param  array $keyValuePairs
     * @return array Associative array of keys and new values
     * @throws \Zend\Cache\Exception\ExceptionInterface
     */
    public function incrementItems(array $keyValuePairs);

    /**
     * Decrement an item.
     *
     * @param  string $key
     * @param  int    $value
     * @return int|bool The new value on success, false on failure
     * @throws \Zend\Cache\Exception\ExceptionInterface
     */
    public function decrementItem($key, $value);

    /**
     * Decrement multiple items.
     *
     * @param  array $keyValuePairs
     * @return array Associative array of keys and new values
     * @throws \Zend\Cache\Exception\ExceptionInterface
     */
    public function decrementItems(array $keyValuePairs);

    /* status */

    /**
     * Capabilities of this storage
     *
     * @return Capabilities
     */
    public function getCapabilities();
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\EventManager;

/**
 * Interface providing events that can be attached, detached and triggered.
 */
interface EventsCapableInterface
{
    /**
     * Retrieve the event manager
     *
     * Lazy-loads an EventManager instance if none registered.
     *
     * @return EventManagerInterface
     */
    public function getEventManager();
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Storage\Adapter;

use ArrayObject;
use SplObjectStorage;
use stdClass;
use Traversable;
use Zend\Cache\Exception;
use Zend\Cache\Storage\Capabilities;
use Zend\Cache\Storage\Event;
use Zend\Cache\Storage\ExceptionEvent;
use Zend\Cache\Storage\Plugin;
use Zend\Cache\Storage\PostEvent;
use Zend\Cache\Storage\StorageInterface;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventsCapableInterface;

abstract class AbstractAdapter implements StorageInterface, EventsCapableInterface
{
    /**
     * The used EventManager if any
     *
     * @var null|EventManagerInterface
     */
    protected $events = null;

    /**
     * Event handles of this adapter
     * @var array
     */
    protected $eventHandles = [];

    /**
     * The plugin registry
     *
     * @var SplObjectStorage Registered plugins
     */
    protected $pluginRegistry;

    /**
     * Capabilities of this adapter
     *
     * @var null|Capabilities
     */
    protected $capabilities = null;

    /**
     * Marker to change capabilities
     *
     * @var null|object
     */
    protected $capabilityMarker;

    /**
     * options
     *
     * @var mixed
     */
    protected $options;

    /**
     * Constructor
     *
     * @param  null|array|Traversable|AdapterOptions $options
     * @throws Exception\ExceptionInterface
     */
    public function __construct($options = null)
    {
        if ($options) {
            $this->setOptions($options);
        }
    }

    /**
     * Destructor
     *
     * detach all registered plugins to free
     * event handles of event manager
     *
     * @return void
     */
    public function __destruct()
    {
        foreach ($this->getPluginRegistry() as $plugin) {
            $this->removePlugin($plugin);
        }

        if ($this->eventHandles) {
            $events = $this->getEventManager();
            foreach ($this->eventHandles as $handle) {
                $events->detach($handle);
            }
        }
    }

    /* configuration */

    /**
     * Set options.
     *
     * @param  array|Traversable|AdapterOptions $options
     * @return AbstractAdapter
     * @see    getOptions()
     */
    public function setOptions($options)
    {
        if ($this->options !== $options) {
            if (!$options instanceof AdapterOptions) {
                $options = new AdapterOptions($options);
            }

            if ($this->options) {
                $this->options->setAdapter(null);
            }
            $options->setAdapter($this);
            $this->options = $options;

            $event = new Event('option', $this, new ArrayObject($options->toArray()));
            $this->getEventManager()->trigger($event);
        }
        return $this;
    }

    /**
     * Get options.
     *
     * @return AdapterOptions
     * @see setOptions()
     */
    public function getOptions()
    {
        if (!$this->options) {
            $this->setOptions(new AdapterOptions());
        }
        return $this->options;
    }

    /**
     * Enable/Disable caching.
     *
     * Alias of setWritable and setReadable.
     *
     * @see    setWritable()
     * @see    setReadable()
     * @param  bool $flag
     * @return AbstractAdapter
     */
    public function setCaching($flag)
    {
        $flag    = (bool) $flag;
        $options = $this->getOptions();
        $options->setWritable($flag);
        $options->setReadable($flag);
        return $this;
    }

    /**
     * Get caching enabled.
     *
     * Alias of getWritable and getReadable.
     *
     * @see    getWritable()
     * @see    getReadable()
     * @return bool
     */
    public function getCaching()
    {
        $options = $this->getOptions();
        return ($options->getWritable() && $options->getReadable());
    }

    /* Event/Plugin handling */

    /**
     * Get the event manager
     *
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
        if ($this->events === null) {
            $this->events = new EventManager([__CLASS__, get_class($this)]);
        }
        return $this->events;
    }

    /**
     * Trigger a pre event and return the event response collection
     *
     * @param  string $eventName
     * @param  ArrayObject $args
     * @return \Zend\EventManager\ResponseCollection All handler return values
     */
    protected function triggerPre($eventName, ArrayObject $args)
    {
        return $this->getEventManager()->trigger(new Event($eventName . '.pre', $this, $args));
    }

    /**
     * Triggers the PostEvent and return the result value.
     *
     * @param  string      $eventName
     * @param  ArrayObject $args
     * @param  mixed       $result
     * @return mixed
     */
    protected function triggerPost($eventName, ArrayObject $args, & $result)
    {
        $postEvent = new PostEvent($eventName . '.post', $this, $args, $result);
        $eventRs   = $this->getEventManager()->trigger($postEvent);

        return $eventRs->stopped()
            ? $eventRs->last()
            : $postEvent->getResult();
    }

    /**
     * Trigger an exception event
     *
     * If the ExceptionEvent has the flag "throwException" enabled throw the
     * exception after trigger else return the result.
     *
     * @param  string      $eventName
     * @param  ArrayObject $args
     * @param  mixed       $result
     * @param  \Exception  $exception
     * @throws Exception\ExceptionInterface
     * @return mixed
     */
    protected function triggerException($eventName, ArrayObject $args, & $result, \Exception $exception)
    {
        $exceptionEvent = new ExceptionEvent($eventName . '.exception', $this, $args, $result, $exception);
        $eventRs        = $this->getEventManager()->trigger($exceptionEvent);

        if ($exceptionEvent->getThrowException()) {
            throw $exceptionEvent->getException();
        }

        return $eventRs->stopped()
            ? $eventRs->last()
            : $exceptionEvent->getResult();
    }

    /**
     * Check if a plugin is registered
     *
     * @param  Plugin\PluginInterface $plugin
     * @return bool
     */
    public function hasPlugin(Plugin\PluginInterface $plugin)
    {
        $registry = $this->getPluginRegistry();
        return $registry->contains($plugin);
    }

    /**
     * Register a plugin
     *
     * @param  Plugin\PluginInterface $plugin
     * @param  int                    $priority
     * @return AbstractAdapter Fluent interface
     * @throws Exception\LogicException
     */
    public function addPlugin(Plugin\PluginInterface $plugin, $priority = 1)
    {
        $registry = $this->getPluginRegistry();
        if ($registry->contains($plugin)) {
            throw new Exception\LogicException(sprintf(
                'Plugin of type "%s" already registered',
                get_class($plugin)
            ));
        }

        $plugin->attach($this->getEventManager(), $priority);
        $registry->attach($plugin);

        return $this;
    }

    /**
     * Unregister an already registered plugin
     *
     * @param  Plugin\PluginInterface $plugin
     * @return AbstractAdapter Fluent interface
     * @throws Exception\LogicException
     */
    public function removePlugin(Plugin\PluginInterface $plugin)
    {
        $registry = $this->getPluginRegistry();
        if ($registry->contains($plugin)) {
            $plugin->detach($this->getEventManager());
            $registry->detach($plugin);
        }
        return $this;
    }

    /**
     * Return registry of plugins
     *
     * @return SplObjectStorage
     */
    public function getPluginRegistry()
    {
        if (!$this->pluginRegistry instanceof SplObjectStorage) {
            $this->pluginRegistry = new SplObjectStorage();
        }
        return $this->pluginRegistry;
    }

    /* reading */

    /**
     * Get an item.
     *
     * @param  string  $key
     * @param  bool $success
     * @param  mixed   $casToken
     * @return mixed Data on success, null on failure
     * @throws Exception\ExceptionInterface
     *
     * @triggers getItem.pre(PreEvent)
     * @triggers getItem.post(PostEvent)
     * @triggers getItem.exception(ExceptionEvent)
     */
    public function getItem($key, & $success = null, & $casToken = null)
    {
        if (!$this->getOptions()->getReadable()) {
            $success = false;
            return;
        }

        $this->normalizeKey($key);

        $argn = func_num_args();
        $args = [
            'key' => & $key,
        ];
        if ($argn > 1) {
            $args['success'] = & $success;
        }
        if ($argn > 2) {
            $args['casToken'] = & $casToken;
        }
        $args = new ArrayObject($args);

        try {
            $eventRs = $this->triggerPre(__FUNCTION__, $args);

            if ($eventRs->stopped()) {
                $result = $eventRs->last();
            } elseif ($args->offsetExists('success') && $args->offsetExists('casToken')) {
                $result = $this->internalGetItem($args['key'], $args['success'], $args['casToken']);
            } elseif ($args->offsetExists('success')) {
                $result = $this->internalGetItem($args['key'], $args['success']);
            } else {
                $result = $this->internalGetItem($args['key']);
            }

            return $this->triggerPost(__FUNCTION__, $args, $result);
        } catch (\Exception $e) {
            $result = null;
            $success = false;
            return $this->triggerException(__FUNCTION__, $args, $result, $e);
        }
    }

    /**
     * Internal method to get an item.
     *
     * @param  string  $normalizedKey
     * @param  bool $success
     * @param  mixed   $casToken
     * @return mixed Data on success, null on failure
     * @throws Exception\ExceptionInterface
     */
    abstract protected function internalGetItem(& $normalizedKey, & $success = null, & $casToken = null);

    /**
     * Get multiple items.
     *
     * @param  array $keys
     * @return array Associative array of keys and values
     * @throws Exception\ExceptionInterface
     *
     * @triggers getItems.pre(PreEvent)
     * @triggers getItems.post(PostEvent)
     * @triggers getItems.exception(ExceptionEvent)
     */
    public function getItems(array $keys)
    {
        if (!$this->getOptions()->getReadable()) {
            return [];
        }

        $this->normalizeKeys($keys);
        $args = new ArrayObject([
            'keys' => & $keys,
        ]);

        try {
            $eventRs = $this->triggerPre(__FUNCTION__, $args);

            $result = $eventRs->stopped()
                ? $eventRs->last()
                : $this->internalGetItems($args['keys']);

            return $this->triggerPost(__FUNCTION__, $args, $result);
        } catch (\Exception $e) {
            $result = [];
            return $this->triggerException(__FUNCTION__, $args, $result, $e);
        }
    }

    /**
     * Internal method to get multiple items.
     *
     * @param  array $normalizedKeys
     * @return array Associative array of keys and values
     * @throws Exception\ExceptionInterface
     */
    protected function internalGetItems(array & $normalizedKeys)
    {
        $success = null;
        $result  = [];
        foreach ($normalizedKeys as $normalizedKey) {
            $value = $this->internalGetItem($normalizedKey, $success);
            if ($success) {
                $result[$normalizedKey] = $value;
            }
        }

        return $result;
    }

    /**
     * Test if an item exists.
     *
     * @param  string $key
     * @return bool
     * @throws Exception\ExceptionInterface
     *
     * @triggers hasItem.pre(PreEvent)
     * @triggers hasItem.post(PostEvent)
     * @triggers hasItem.exception(ExceptionEvent)
     */
    public function hasItem($key)
    {
        if (!$this->getOptions()->getReadable()) {
            return false;
        }

        $this->normalizeKey($key);
        $args = new ArrayObject([
            'key' => & $key,
        ]);

        try {
            $eventRs = $this->triggerPre(__FUNCTION__, $args);

            $result = $eventRs->stopped()
                ? $eventRs->last()
                : $this->internalHasItem($args['key']);

            return $this->triggerPost(__FUNCTION__, $args, $result);
        } catch (\Exception $e) {
            $result = false;
            return $this->triggerException(__FUNCTION__, $args, $result, $e);
        }
    }

    /**
     * Internal method to test if an item exists.
     *
     * @param  string $normalizedKey
     * @return bool
     * @throws Exception\ExceptionInterface
     */
    protected function internalHasItem(& $normalizedKey)
    {
        $success = null;
        $this->internalGetItem($normalizedKey, $success);
        return $success;
    }

    /**
     * Test multiple items.
     *
     * @param  array $keys
     * @return array Array of found keys
     * @throws Exception\ExceptionInterface
     *
     * @triggers hasItems.pre(PreEvent)
     * @triggers hasItems.post(PostEvent)
     * @triggers hasItems.exception(ExceptionEvent)
     */
    public function hasItems(array $keys)
    {
        if (!$this->getOptions()->getReadable()) {
            return [];
        }

        $this->normalizeKeys($keys);
        $args = new ArrayObject([
            'keys' => & $keys,
        ]);

        try {
            $eventRs = $this->triggerPre(__FUNCTION__, $args);

            $result = $eventRs->stopped()
                ? $eventRs->last()
                : $this->internalHasItems($args['keys']);

            return $this->triggerPost(__FUNCTION__, $args, $result);
        } catch (\Exception $e) {
            $result = [];
            return $this->triggerException(__FUNCTION__, $args, $result, $e);
        }
    }

    /**
     * Internal method to test multiple items.
     *
     * @param  array $normalizedKeys
     * @return array Array of found keys
     * @throws Exception\ExceptionInterface
     */
    protected function internalHasItems(array & $normalizedKeys)
    {
        $result = [];
        foreach ($normalizedKeys as $normalizedKey) {
            if ($this->internalHasItem($normalizedKey)) {
                $result[] = $normalizedKey;
            }
        }
        return $result;
    }

    /**
     * Get metadata of an item.
     *
     * @param  string $key
     * @return array|bool Metadata on success, false on failure
     * @throws Exception\ExceptionInterface
     *
     * @triggers getMetadata.pre(PreEvent)
     * @triggers getMetadata.post(PostEvent)
     * @triggers getMetadata.exception(ExceptionEvent)
     */
    public function getMetadata($key)
    {
        if (!$this->getOptions()->getReadable()) {
            return false;
        }

        $this->normalizeKey($key);
        $args = new ArrayObject([
            'key' => & $key,
        ]);

        try {
            $eventRs = $this->triggerPre(__FUNCTION__, $args);

            $result = $eventRs->stopped()
                ? $eventRs->last()
                : $this->internalGetMetadata($args['key']);

            return $this->triggerPost(__FUNCTION__, $args, $result);
        } catch (\Exception $e) {
            $result = false;
            return $this->triggerException(__FUNCTION__, $args, $result, $e);
        }
    }

    /**
     * Internal method to get metadata of an item.
     *
     * @param  string $normalizedKey
     * @return array|bool Metadata on success, false on failure
     * @throws Exception\ExceptionInterface
     */
    protected function internalGetMetadata(& $normalizedKey)
    {
        if (!$this->internalHasItem($normalizedKey)) {
            return false;
        }

        return [];
    }

    /**
     * Get multiple metadata
     *
     * @param  array $keys
     * @return array Associative array of keys and metadata
     * @throws Exception\ExceptionInterface
     *
     * @triggers getMetadatas.pre(PreEvent)
     * @triggers getMetadatas.post(PostEvent)
     * @triggers getMetadatas.exception(ExceptionEvent)
     */
    public function getMetadatas(array $keys)
    {
        if (!$this->getOptions()->getReadable()) {
            return [];
        }

        $this->normalizeKeys($keys);
        $args = new ArrayObject([
            'keys' => & $keys,
        ]);

        try {
            $eventRs = $this->triggerPre(__FUNCTION__, $args);

            $result = $eventRs->stopped()
                ? $eventRs->last()
                : $this->internalGetMetadatas($args['keys']);

            return $this->triggerPost(__FUNCTION__, $args, $result);
        } catch (\Exception $e) {
            $result = [];
            return $this->triggerException(__FUNCTION__, $args, $result, $e);
        }
    }

    /**
     * Internal method to get multiple metadata
     *
     * @param  array $normalizedKeys
     * @return array Associative array of keys and metadata
     * @throws Exception\ExceptionInterface
     */
    protected function internalGetMetadatas(array & $normalizedKeys)
    {
        $result = [];
        foreach ($normalizedKeys as $normalizedKey) {
            $metadata = $this->internalGetMetadata($normalizedKey);
            if ($metadata !== false) {
                $result[$normalizedKey] = $metadata;
            }
        }
        return $result;
    }

    /* writing */

    /**
     * Store an item.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return bool
     * @throws Exception\ExceptionInterface
     *
     * @triggers setItem.pre(PreEvent)
     * @triggers setItem.post(PostEvent)
     * @triggers setItem.exception(ExceptionEvent)
     */
    public function setItem($key, $value)
    {
        if (!$this->getOptions()->getWritable()) {
            return false;
        }

        $this->normalizeKey($key);
        $args = new ArrayObject([
            'key'   => & $key,
            'value' => & $value,
        ]);

        try {
            $eventRs = $this->triggerPre(__FUNCTION__, $args);

            $result = $eventRs->stopped()
                ? $eventRs->last()
                : $this->internalSetItem($args['key'], $args['value']);

            return $this->triggerPost(__FUNCTION__, $args, $result);
        } catch (\Exception $e) {
            $result = false;
            return $this->triggerException(__FUNCTION__, $args, $result, $e);
        }
    }

    /**
     * Internal method to store an item.
     *
     * @param  string $normalizedKey
     * @param  mixed  $value
     * @return bool
     * @throws Exception\ExceptionInterface
     */
    abstract protected function internalSetItem(& $normalizedKey, & $value);

    /**
     * Store multiple items.
     *
     * @param  array $keyValuePairs
     * @return array Array of not stored keys
     * @throws Exception\ExceptionInterface
     *
     * @triggers setItems.pre(PreEvent)
     * @triggers setItems.post(PostEvent)
     * @triggers setItems.exception(ExceptionEvent)
     */
    public function setItems(array $keyValuePairs)
    {
        if (!$this->getOptions()->getWritable()) {
            return array_keys($keyValuePairs);
        }

        $this->normalizeKeyValuePairs($keyValuePairs);
        $args = new ArrayObject([
            'keyValuePairs' => & $keyValuePairs,
        ]);

        try {
            $eventRs = $this->triggerPre(__FUNCTION__, $args);

            $result = $eventRs->stopped()
                ? $eventRs->last()
                : $this->internalSetItems($args['keyValuePairs']);

            return $this->triggerPost(__FUNCTION__, $args, $result);
        } catch (\Exception $e) {
            $result = array_keys($keyValuePairs);
            return $this->triggerException(__FUNCTION__, $args, $result, $e);
        }
    }

    /**
     * Internal method to store multiple items.
     *
     * @param  array $normalizedKeyValuePairs
     * @return array Array of not stored keys
     * @throws Exception\ExceptionInterface
     */
    protected function internalSetItems(array & $normalizedKeyValuePairs)
    {
        $failedKeys = [];
        foreach ($normalizedKeyValuePairs as $normalizedKey => $value) {
            if (!$this->internalSetItem($normalizedKey, $value)) {
                $failedKeys[] = $normalizedKey;
            }
        }
        return $failedKeys;
    }

    /**
     * Add an item.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return bool
     * @throws Exception\ExceptionInterface
     *
     * @triggers addItem.pre(PreEvent)
     * @triggers addItem.post(PostEvent)
     * @triggers addItem.exception(ExceptionEvent)
     */
    public function addItem($key, $value)
    {
        if (!$this->getOptions()->getWritable()) {
            return false;
        }

        $this->normalizeKey($key);
        $args = new ArrayObject([
            'key'   => & $key,
            'value' => & $value,
        ]);

        try {
            $eventRs = $this->triggerPre(__FUNCTION__, $args);

            $result = $eventRs->stopped()
                ? $eventRs->last()
                : $this->internalAddItem($args['key'], $args['value']);

            return $this->triggerPost(__FUNCTION__, $args, $result);
        } catch (\Exception $e) {
            $result = false;
            return $this->triggerException(__FUNCTION__, $args, $result, $e);
        }
    }

    /**
     * Internal method to add an item.
     *
     * @param  string $normalizedKey
     * @param  mixed  $value
     * @return bool
     * @throws Exception\ExceptionInterface
     */
    protected function internalAddItem(& $normalizedKey, & $value)
    {
        if ($this->internalHasItem($normalizedKey)) {
            return false;
        }
        return $this->internalSetItem($normalizedKey, $value);
    }

    /**
     * Add multiple items.
     *
     * @param  array $keyValuePairs
     * @return array Array of not stored keys
     * @throws Exception\ExceptionInterface
     *
     * @triggers addItems.pre(PreEvent)
     * @triggers addItems.post(PostEvent)
     * @triggers addItems.exception(ExceptionEvent)
     */
    public function addItems(array $keyValuePairs)
    {
        if (!$this->getOptions()->getWritable()) {
            return array_keys($keyValuePairs);
        }

        $this->normalizeKeyValuePairs($keyValuePairs);
        $args = new ArrayObject([
            'keyValuePairs' => & $keyValuePairs,
        ]);

        try {
            $eventRs = $this->triggerPre(__FUNCTION__, $args);

            $result = $eventRs->stopped()
                ? $eventRs->last()
                : $this->internalAddItems($args['keyValuePairs']);

            return $this->triggerPost(__FUNCTION__, $args, $result);
        } catch (\Exception $e) {
            $result = array_keys($keyValuePairs);
            return $this->triggerException(__FUNCTION__, $args, $result, $e);
        }
    }

    /**
     * Internal method to add multiple items.
     *
     * @param  array $normalizedKeyValuePairs
     * @return array Array of not stored keys
     * @throws Exception\ExceptionInterface
     */
    protected function internalAddItems(array & $normalizedKeyValuePairs)
    {
        $result = [];
        foreach ($normalizedKeyValuePairs as $normalizedKey => $value) {
            if (!$this->internalAddItem($normalizedKey, $value)) {
                $result[] = $normalizedKey;
            }
        }
        return $result;
    }

    /**
     * Replace an existing item.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return bool
     * @throws Exception\ExceptionInterface
     *
     * @triggers replaceItem.pre(PreEvent)
     * @triggers replaceItem.post(PostEvent)
     * @triggers replaceItem.exception(ExceptionEvent)
     */
    public function replaceItem($key, $value)
    {
        if (!$this->getOptions()->getWritable()) {
            return false;
        }

        $this->normalizeKey($key);
        $args = new ArrayObject([
            'key'   => & $key,
            'value' => & $value,
        ]);

        try {
            $eventRs = $this->triggerPre(__FUNCTION__, $args);

            $result = $eventRs->stopped()
                ? $eventRs->last()
                : $this->internalReplaceItem($args['key'], $args['value']);

            return $this->triggerPost(__FUNCTION__, $args, $result);
        } catch (\Exception $e) {
            $result = false;
            return $this->triggerException(__FUNCTION__, $args, $result, $e);
        }
    }

    /**
     * Internal method to replace an existing item.
     *
     * @param  string $normalizedKey
     * @param  mixed  $value
     * @return bool
     * @throws Exception\ExceptionInterface
     */
    protected function internalReplaceItem(& $normalizedKey, & $value)
    {
        if (!$this->internalhasItem($normalizedKey)) {
            return false;
        }

        return $this->internalSetItem($normalizedKey, $value);
    }

    /**
     * Replace multiple existing items.
     *
     * @param  array $keyValuePairs
     * @return array Array of not stored keys
     * @throws Exception\ExceptionInterface
     *
     * @triggers replaceItems.pre(PreEvent)
     * @triggers replaceItems.post(PostEvent)
     * @triggers replaceItems.exception(ExceptionEvent)
     */
    public function replaceItems(array $keyValuePairs)
    {
        if (!$this->getOptions()->getWritable()) {
            return array_keys($keyValuePairs);
        }

        $this->normalizeKeyValuePairs($keyValuePairs);
        $args = new ArrayObject([
            'keyValuePairs' => & $keyValuePairs,
        ]);

        try {
            $eventRs = $this->triggerPre(__FUNCTION__, $args);

            $result = $eventRs->stopped()
                ? $eventRs->last()
                : $this->internalReplaceItems($args['keyValuePairs']);

            return $this->triggerPost(__FUNCTION__, $args, $result);
        } catch (\Exception $e) {
            $result = array_keys($keyValuePairs);
            return $this->triggerException(__FUNCTION__, $args, $result, $e);
        }
    }

    /**
     * Internal method to replace multiple existing items.
     *
     * @param  array $normalizedKeyValuePairs
     * @return array Array of not stored keys
     * @throws Exception\ExceptionInterface
     */
    protected function internalReplaceItems(array & $normalizedKeyValuePairs)
    {
        $result = [];
        foreach ($normalizedKeyValuePairs as $normalizedKey => $value) {
            if (!$this->internalReplaceItem($normalizedKey, $value)) {
                $result[] = $normalizedKey;
            }
        }
        return $result;
    }

    /**
     * Set an item only if token matches
     *
     * It uses the token received from getItem() to check if the item has
     * changed before overwriting it.
     *
     * @param  mixed  $token
     * @param  string $key
     * @param  mixed  $value
     * @return bool
     * @throws Exception\ExceptionInterface
     * @see    getItem()
     * @see    setItem()
     */
    public function checkAndSetItem($token, $key, $value)
    {
        if (!$this->getOptions()->getWritable()) {
            return false;
        }

        $this->normalizeKey($key);
        $args = new ArrayObject([
            'token' => & $token,
            'key'   => & $key,
            'value' => & $value,
        ]);

        try {
            $eventRs = $this->triggerPre(__FUNCTION__, $args);

            $result = $eventRs->stopped()
                ? $eventRs->last()
                : $this->internalCheckAndSetItem($args['token'], $args['key'], $args['value']);

            return $this->triggerPost(__FUNCTION__, $args, $result);
        } catch (\Exception $e) {
            $result = false;
            return $this->triggerException(__FUNCTION__, $args, $result, $e);
        }
    }

    /**
     * Internal method to set an item only if token matches
     *
     * @param  mixed  $token
     * @param  string $normalizedKey
     * @param  mixed  $value
     * @return bool
     * @throws Exception\ExceptionInterface
     * @see    getItem()
     * @see    setItem()
     */
    protected function internalCheckAndSetItem(& $token, & $normalizedKey, & $value)
    {
        $oldValue = $this->internalGetItem($normalizedKey);
        if ($oldValue !== $token) {
            return false;
        }

        return $this->internalSetItem($normalizedKey, $value);
    }

    /**
     * Reset lifetime of an item
     *
     * @param  string $key
     * @return bool
     * @throws Exception\ExceptionInterface
     *
     * @triggers touchItem.pre(PreEvent)
     * @triggers touchItem.post(PostEvent)
     * @triggers touchItem.exception(ExceptionEvent)
     */
    public function touchItem($key)
    {
        if (!$this->getOptions()->getWritable()) {
            return false;
        }

        $this->normalizeKey($key);
        $args = new ArrayObject([
            'key' => & $key,
        ]);

        try {
            $eventRs = $this->triggerPre(__FUNCTION__, $args);

            $result = $eventRs->stopped()
                ? $eventRs->last()
                : $this->internalTouchItem($args['key']);

            return $this->triggerPost(__FUNCTION__, $args, $result);
        } catch (\Exception $e) {
            $result = false;
            return $this->triggerException(__FUNCTION__, $args, $result, $e);
        }
    }

    /**
     * Internal method to reset lifetime of an item
     *
     * @param  string $normalizedKey
     * @return bool
     * @throws Exception\ExceptionInterface
     */
    protected function internalTouchItem(& $normalizedKey)
    {
        $success = null;
        $value   = $this->internalGetItem($normalizedKey, $success);
        if (!$success) {
            return false;
        }

        return $this->internalReplaceItem($normalizedKey, $value);
    }

    /**
     * Reset lifetime of multiple items.
     *
     * @param  array $keys
     * @return array Array of not updated keys
     * @throws Exception\ExceptionInterface
     *
     * @triggers touchItems.pre(PreEvent)
     * @triggers touchItems.post(PostEvent)
     * @triggers touchItems.exception(ExceptionEvent)
     */
    public function touchItems(array $keys)
    {
        if (!$this->getOptions()->getWritable()) {
            return $keys;
        }

        $this->normalizeKeys($keys);
        $args = new ArrayObject([
            'keys' => & $keys,
        ]);

        try {
            $eventRs = $this->triggerPre(__FUNCTION__, $args);

            $result = $eventRs->stopped()
                ? $eventRs->last()
                : $this->internalTouchItems($args['keys']);

            return $this->triggerPost(__FUNCTION__, $args, $result);
        } catch (\Exception $e) {
            return $this->triggerException(__FUNCTION__, $args, $keys, $e);
        }
    }

    /**
     * Internal method to reset lifetime of multiple items.
     *
     * @param  array $normalizedKeys
     * @return array Array of not updated keys
     * @throws Exception\ExceptionInterface
     */
    protected function internalTouchItems(array & $normalizedKeys)
    {
        $result = [];
        foreach ($normalizedKeys as $normalizedKey) {
            if (!$this->internalTouchItem($normalizedKey)) {
                $result[] = $normalizedKey;
            }
        }
        return $result;
    }

    /**
     * Remove an item.
     *
     * @param  string $key
     * @return bool
     * @throws Exception\ExceptionInterface
     *
     * @triggers removeItem.pre(PreEvent)
     * @triggers removeItem.post(PostEvent)
     * @triggers removeItem.exception(ExceptionEvent)
     */
    public function removeItem($key)
    {
        if (!$this->getOptions()->getWritable()) {
            return false;
        }

        $this->normalizeKey($key);
        $args = new ArrayObject([
            'key' => & $key,
        ]);

        try {
            $eventRs = $this->triggerPre(__FUNCTION__, $args);

            $result = $eventRs->stopped()
                ? $eventRs->last()
                : $this->internalRemoveItem($args['key']);

            return $this->triggerPost(__FUNCTION__, $args, $result);
        } catch (\Exception $e) {
            $result = false;
            return $this->triggerException(__FUNCTION__, $args, $result, $e);
        }
    }

    /**
     * Internal method to remove an item.
     *
     * @param  string $normalizedKey
     * @return bool
     * @throws Exception\ExceptionInterface
     */
    abstract protected function internalRemoveItem(& $normalizedKey);

    /**
     * Remove multiple items.
     *
     * @param  array $keys
     * @return array Array of not removed keys
     * @throws Exception\ExceptionInterface
     *
     * @triggers removeItems.pre(PreEvent)
     * @triggers removeItems.post(PostEvent)
     * @triggers removeItems.exception(ExceptionEvent)
     */
    public function removeItems(array $keys)
    {
        if (!$this->getOptions()->getWritable()) {
            return $keys;
        }

        $this->normalizeKeys($keys);
        $args = new ArrayObject([
            'keys' => & $keys,
        ]);

        try {
            $eventRs = $this->triggerPre(__FUNCTION__, $args);

            $result = $eventRs->stopped()
                ? $eventRs->last()
                : $this->internalRemoveItems($args['keys']);

            return $this->triggerPost(__FUNCTION__, $args, $result);
        } catch (\Exception $e) {
            return $this->triggerException(__FUNCTION__, $args, $keys, $e);
        }
    }

    /**
     * Internal method to remove multiple items.
     *
     * @param  array $normalizedKeys
     * @return array Array of not removed keys
     * @throws Exception\ExceptionInterface
     */
    protected function internalRemoveItems(array & $normalizedKeys)
    {
        $result = [];
        foreach ($normalizedKeys as $normalizedKey) {
            if (!$this->internalRemoveItem($normalizedKey)) {
                $result[] = $normalizedKey;
            }
        }
        return $result;
    }

    /**
     * Increment an item.
     *
     * @param  string $key
     * @param  int    $value
     * @return int|bool The new value on success, false on failure
     * @throws Exception\ExceptionInterface
     *
     * @triggers incrementItem.pre(PreEvent)
     * @triggers incrementItem.post(PostEvent)
     * @triggers incrementItem.exception(ExceptionEvent)
     */
    public function incrementItem($key, $value)
    {
        if (!$this->getOptions()->getWritable()) {
            return false;
        }

        $this->normalizeKey($key);
        $args = new ArrayObject([
            'key'   => & $key,
            'value' => & $value,
        ]);

        try {
            $eventRs = $this->triggerPre(__FUNCTION__, $args);

            $result = $eventRs->stopped()
                ? $eventRs->last()
                : $this->internalIncrementItem($args['key'], $args['value']);

            return $this->triggerPost(__FUNCTION__, $args, $result);
        } catch (\Exception $e) {
            $result = false;
            return $this->triggerException(__FUNCTION__, $args, $result, $e);
        }
    }

    /**
     * Internal method to increment an item.
     *
     * @param  string $normalizedKey
     * @param  int    $value
     * @return int|bool The new value on success, false on failure
     * @throws Exception\ExceptionInterface
     */
    protected function internalIncrementItem(& $normalizedKey, & $value)
    {
        $success  = null;
        $value    = (int) $value;
        $get      = (int) $this->internalGetItem($normalizedKey, $success);
        $newValue = $get + $value;

        if ($success) {
            $this->internalReplaceItem($normalizedKey, $newValue);
        } else {
            $this->internalAddItem($normalizedKey, $newValue);
        }

        return $newValue;
    }

    /**
     * Increment multiple items.
     *
     * @param  array $keyValuePairs
     * @return array Associative array of keys and new values
     * @throws Exception\ExceptionInterface
     *
     * @triggers incrementItems.pre(PreEvent)
     * @triggers incrementItems.post(PostEvent)
     * @triggers incrementItems.exception(ExceptionEvent)
     */
    public function incrementItems(array $keyValuePairs)
    {
        if (!$this->getOptions()->getWritable()) {
            return [];
        }

        $this->normalizeKeyValuePairs($keyValuePairs);
        $args = new ArrayObject([
            'keyValuePairs' => & $keyValuePairs,
        ]);

        try {
            $eventRs = $this->triggerPre(__FUNCTION__, $args);

            $result = $eventRs->stopped()
                ? $eventRs->last()
                : $this->internalIncrementItems($args['keyValuePairs']);

            return $this->triggerPost(__FUNCTION__, $args, $result);
        } catch (\Exception $e) {
            $result = [];
            return $this->triggerException(__FUNCTION__, $args, $result, $e);
        }
    }

    /**
     * Internal method to increment multiple items.
     *
     * @param  array $normalizedKeyValuePairs
     * @return array Associative array of keys and new values
     * @throws Exception\ExceptionInterface
     */
    protected function internalIncrementItems(array & $normalizedKeyValuePairs)
    {
        $result = [];
        foreach ($normalizedKeyValuePairs as $normalizedKey => $value) {
            $newValue = $this->internalIncrementItem($normalizedKey, $value);
            if ($newValue !== false) {
                $result[$normalizedKey] = $newValue;
            }
        }
        return $result;
    }

    /**
     * Decrement an item.
     *
     * @param  string $key
     * @param  int    $value
     * @return int|bool The new value on success, false on failure
     * @throws Exception\ExceptionInterface
     *
     * @triggers decrementItem.pre(PreEvent)
     * @triggers decrementItem.post(PostEvent)
     * @triggers decrementItem.exception(ExceptionEvent)
     */
    public function decrementItem($key, $value)
    {
        if (!$this->getOptions()->getWritable()) {
            return false;
        }

        $this->normalizeKey($key);
        $args = new ArrayObject([
            'key'   => & $key,
            'value' => & $value,
        ]);

        try {
            $eventRs = $this->triggerPre(__FUNCTION__, $args);

            $result = $eventRs->stopped()
                ? $eventRs->last()
                : $this->internalDecrementItem($args['key'], $args['value']);

            return $this->triggerPost(__FUNCTION__, $args, $result);
        } catch (\Exception $e) {
            $result = false;
            return $this->triggerException(__FUNCTION__, $args, $result, $e);
        }
    }

    /**
     * Internal method to decrement an item.
     *
     * @param  string $normalizedKey
     * @param  int    $value
     * @return int|bool The new value on success, false on failure
     * @throws Exception\ExceptionInterface
     */
    protected function internalDecrementItem(& $normalizedKey, & $value)
    {
        $success  = null;
        $value    = (int) $value;
        $get      = (int) $this->internalGetItem($normalizedKey, $success);
        $newValue = $get - $value;

        if ($success) {
            $this->internalReplaceItem($normalizedKey, $newValue);
        } else {
            $this->internalAddItem($normalizedKey, $newValue);
        }

        return $newValue;
    }

    /**
     * Decrement multiple items.
     *
     * @param  array $keyValuePairs
     * @return array Associative array of keys and new values
     * @throws Exception\ExceptionInterface
     *
     * @triggers incrementItems.pre(PreEvent)
     * @triggers incrementItems.post(PostEvent)
     * @triggers incrementItems.exception(ExceptionEvent)
     */
    public function decrementItems(array $keyValuePairs)
    {
        if (!$this->getOptions()->getWritable()) {
            return [];
        }

        $this->normalizeKeyValuePairs($keyValuePairs);
        $args = new ArrayObject([
            'keyValuePairs' => & $keyValuePairs,
        ]);

        try {
            $eventRs = $this->triggerPre(__FUNCTION__, $args);

            $result = $eventRs->stopped()
                ? $eventRs->last()
                : $this->internalDecrementItems($args['keyValuePairs']);

            return $this->triggerPost(__FUNCTION__, $args, $result);
        } catch (\Exception $e) {
            $result = [];
            return $this->triggerException(__FUNCTION__, $args, $result, $e);
        }
    }

    /**
     * Internal method to decrement multiple items.
     *
     * @param  array $normalizedKeyValuePairs
     * @return array Associative array of keys and new values
     * @throws Exception\ExceptionInterface
     */
    protected function internalDecrementItems(array & $normalizedKeyValuePairs)
    {
        $result = [];
        foreach ($normalizedKeyValuePairs as $normalizedKey => $value) {
            $newValue = $this->decrementItem($normalizedKey, $value);
            if ($newValue !== false) {
                $result[$normalizedKey] = $newValue;
            }
        }
        return $result;
    }

    /* status */

    /**
     * Get capabilities of this adapter
     *
     * @return Capabilities
     * @triggers getCapabilities.pre(PreEvent)
     * @triggers getCapabilities.post(PostEvent)
     * @triggers getCapabilities.exception(ExceptionEvent)
     */
    public function getCapabilities()
    {
        $args = new ArrayObject();

        try {
            $eventRs = $this->triggerPre(__FUNCTION__, $args);

            $result = $eventRs->stopped()
                ? $eventRs->last()
                : $this->internalGetCapabilities();

            return $this->triggerPost(__FUNCTION__, $args, $result);
        } catch (\Exception $e) {
            $result = false;
            return $this->triggerException(__FUNCTION__, $args, $result, $e);
        }
    }

    /**
     * Internal method to get capabilities of this adapter
     *
     * @return Capabilities
     */
    protected function internalGetCapabilities()
    {
        if ($this->capabilities === null) {
            $this->capabilityMarker = new stdClass();
            $this->capabilities     = new Capabilities($this, $this->capabilityMarker);
        }
        return $this->capabilities;
    }

    /* internal */

    /**
     * Validates and normalizes a key
     *
     * @param  string $key
     * @return void
     * @throws Exception\InvalidArgumentException On an invalid key
     */
    protected function normalizeKey(& $key)
    {
        $key = (string) $key;

        if ($key === '') {
            throw new Exception\InvalidArgumentException(
                "An empty key isn't allowed"
            );
        } elseif (($p = $this->getOptions()->getKeyPattern()) && !preg_match($p, $key)) {
            throw new Exception\InvalidArgumentException(
                "The key '{$key}' doesn't match against pattern '{$p}'"
            );
        }
    }

    /**
     * Validates and normalizes multiple keys
     *
     * @param  array $keys
     * @return void
     * @throws Exception\InvalidArgumentException On an invalid key
     */
    protected function normalizeKeys(array & $keys)
    {
        if (!$keys) {
            throw new Exception\InvalidArgumentException(
                "An empty list of keys isn't allowed"
            );
        }

        array_walk($keys, [$this, 'normalizeKey']);
        $keys = array_values(array_unique($keys));
    }

    /**
     * Validates and normalizes an array of key-value pairs
     *
     * @param  array $keyValuePairs
     * @return void
     * @throws Exception\InvalidArgumentException On an invalid key
     */
    protected function normalizeKeyValuePairs(array & $keyValuePairs)
    {
        $normalizedKeyValuePairs = [];
        foreach ($keyValuePairs as $key => $value) {
            $this->normalizeKey($key);
            $normalizedKeyValuePairs[$key] = $value;
        }
        $keyValuePairs = $normalizedKeyValuePairs;
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Storage;

interface AvailableSpaceCapableInterface
{
    /**
     * Get available space in bytes
     *
     * @return int|float
     */
    public function getAvailableSpace();
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Storage;

interface ClearByNamespaceInterface
{
    /**
     * Remove items of given namespace
     *
     * @param string $namespace
     * @return bool
     */
    public function clearByNamespace($namespace);
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Storage;

interface ClearByPrefixInterface
{
    /**
     * Remove items matching given prefix
     *
     * @param string $prefix
     * @return bool
     */
    public function clearByPrefix($prefix);
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Storage;

interface ClearExpiredInterface
{
    /**
     * Remove expired items
     *
     * @return bool
     */
    public function clearExpired();
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Storage;

interface FlushableInterface
{
    /**
     * Flush the whole storage
     *
     * @return bool
     */
    public function flush();
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Storage;

use IteratorAggregate;

/**
 *
 * @method IteratorInterface getIterator() Get the storage iterator
 */
interface IterableInterface extends IteratorAggregate
{
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Storage;

interface OptimizableInterface
{
    /**
     * Optimize the storage
     *
     * @return bool
     */
    public function optimize();
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Storage;

interface TaggableInterface
{
    /**
     * Set tags to an item by given key.
     * An empty array will remove all tags.
     *
     * @param string   $key
     * @param string[] $tags
     * @return bool
     */
    public function setTags($key, array $tags);

    /**
     * Get tags of an item by given key
     *
     * @param string $key
     * @return string[]|FALSE
     */
    public function getTags($key);

    /**
     * Remove items matching given tags.
     *
     * If $disjunction only one of the given tags must match
     * else all given tags must match.
     *
     * @param string[] $tags
     * @param  bool  $disjunction
     * @return bool
     */
    public function clearByTags(array $tags, $disjunction = false);
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Storage;

interface TotalSpaceCapableInterface
{
    /**
     * Get total space in bytes
     *
     * @return int|float
     */
    public function getTotalSpace();
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Storage\Adapter;

use Exception as BaseException;
use GlobIterator;
use stdClass;
use Zend\Cache\Exception;
use Zend\Cache\Storage;
use Zend\Cache\Storage\AvailableSpaceCapableInterface;
use Zend\Cache\Storage\Capabilities;
use Zend\Cache\Storage\ClearByNamespaceInterface;
use Zend\Cache\Storage\ClearByPrefixInterface;
use Zend\Cache\Storage\ClearExpiredInterface;
use Zend\Cache\Storage\FlushableInterface;
use Zend\Cache\Storage\IterableInterface;
use Zend\Cache\Storage\OptimizableInterface;
use Zend\Cache\Storage\TaggableInterface;
use Zend\Cache\Storage\TotalSpaceCapableInterface;
use Zend\Stdlib\ErrorHandler;
use ArrayObject;

class Filesystem extends AbstractAdapter implements
    AvailableSpaceCapableInterface,
    ClearByNamespaceInterface,
    ClearByPrefixInterface,
    ClearExpiredInterface,
    FlushableInterface,
    IterableInterface,
    OptimizableInterface,
    TaggableInterface,
    TotalSpaceCapableInterface
{
    /**
     * Buffered total space in bytes
     *
     * @var null|int|float
     */
    protected $totalSpace;

    /**
     * An identity for the last filespec
     * (cache directory + namespace prefix + key + directory level)
     *
     * @var string
     */
    protected $lastFileSpecId = '';

    /**
     * The last used filespec
     *
     * @var string
     */
    protected $lastFileSpec = '';

    /**
     * Set options.
     *
     * @param  array|\Traversable|FilesystemOptions $options
     * @return Filesystem
     * @see    getOptions()
     */
    public function setOptions($options)
    {
        if (!$options instanceof FilesystemOptions) {
            $options = new FilesystemOptions($options);
        }

        return parent::setOptions($options);
    }

    /**
     * Get options.
     *
     * @return FilesystemOptions
     * @see setOptions()
     */
    public function getOptions()
    {
        if (!$this->options) {
            $this->setOptions(new FilesystemOptions());
        }
        return $this->options;
    }

    /* FlushableInterface */

    /**
     * Flush the whole storage
     *
     * @throws Exception\RuntimeException
     * @return bool
     */
    public function flush()
    {
        $flags = GlobIterator::SKIP_DOTS | GlobIterator::CURRENT_AS_PATHNAME;
        $dir   = $this->getOptions()->getCacheDir();
        $clearFolder = null;
        $clearFolder = function ($dir) use (& $clearFolder, $flags) {
            $it = new GlobIterator($dir . DIRECTORY_SEPARATOR . '*', $flags);
            foreach ($it as $pathname) {
                if ($it->isDir()) {
                    $clearFolder($pathname);
                    rmdir($pathname);
                } else {
                    unlink($pathname);
                }
            }
        };

        ErrorHandler::start();
        $clearFolder($dir);
        $error = ErrorHandler::stop();
        if ($error) {
            throw new Exception\RuntimeException("Flushing directory '{$dir}' failed", 0, $error);
        }

        return true;
    }

    /* ClearExpiredInterface */

    /**
     * Remove expired items
     *
     * @return bool
     *
     * @triggers clearExpired.exception(ExceptionEvent)
     */
    public function clearExpired()
    {
        $options   = $this->getOptions();
        $namespace = $options->getNamespace();
        $prefix    = ($namespace === '') ? '' : $namespace . $options->getNamespaceSeparator();

        $flags = GlobIterator::SKIP_DOTS | GlobIterator::CURRENT_AS_FILEINFO;
        $path  = $options->getCacheDir()
            . str_repeat(DIRECTORY_SEPARATOR . $prefix . '*', $options->getDirLevel())
            . DIRECTORY_SEPARATOR . $prefix . '*.dat';
        $glob = new GlobIterator($path, $flags);
        $time = time();
        $ttl  = $options->getTtl();

        ErrorHandler::start();
        foreach ($glob as $entry) {
            $mtime = $entry->getMTime();
            if ($time >= $mtime + $ttl) {
                $pathname = $entry->getPathname();
                unlink($pathname);

                $tagPathname = substr($pathname, 0, -4) . '.tag';
                if (file_exists($tagPathname)) {
                    unlink($tagPathname);
                }
            }
        }
        $error = ErrorHandler::stop();
        if ($error) {
            $result = false;
            return $this->triggerException(
                __FUNCTION__,
                new ArrayObject(),
                $result,
                new Exception\RuntimeException('Failed to clear expired items', 0, $error)
            );
        }

        return true;
    }

    /* ClearByNamespaceInterface */

    /**
     * Remove items by given namespace
     *
     * @param string $namespace
     * @throws Exception\RuntimeException
     * @return bool
     */
    public function clearByNamespace($namespace)
    {
        $namespace = (string) $namespace;
        if ($namespace === '') {
            throw new Exception\InvalidArgumentException('No namespace given');
        }

        $options = $this->getOptions();
        $prefix  = $namespace . $options->getNamespaceSeparator();

        $flags = GlobIterator::SKIP_DOTS | GlobIterator::CURRENT_AS_PATHNAME;
        $path = $options->getCacheDir()
            . str_repeat(DIRECTORY_SEPARATOR . $prefix . '*', $options->getDirLevel())
            . DIRECTORY_SEPARATOR . $prefix . '*.*';
        $glob = new GlobIterator($path, $flags);

        ErrorHandler::start();
        foreach ($glob as $pathname) {
            unlink($pathname);
        }
        $error = ErrorHandler::stop();
        if ($error) {
            throw new Exception\RuntimeException("Failed to remove files of '{$path}'", 0, $error);
        }

        return true;
    }

    /* ClearByPrefixInterface */

    /**
     * Remove items matching given prefix
     *
     * @param string $prefix
     * @throws Exception\RuntimeException
     * @return bool
     */
    public function clearByPrefix($prefix)
    {
        $prefix = (string) $prefix;
        if ($prefix === '') {
            throw new Exception\InvalidArgumentException('No prefix given');
        }

        $options   = $this->getOptions();
        $namespace = $options->getNamespace();
        $nsPrefix  = ($namespace === '') ? '' : $namespace . $options->getNamespaceSeparator();

        $flags = GlobIterator::SKIP_DOTS | GlobIterator::CURRENT_AS_PATHNAME;
        $path = $options->getCacheDir()
            . str_repeat(DIRECTORY_SEPARATOR . $nsPrefix . '*', $options->getDirLevel())
            . DIRECTORY_SEPARATOR . $nsPrefix . $prefix . '*.*';
        $glob = new GlobIterator($path, $flags);

        ErrorHandler::start();
        foreach ($glob as $pathname) {
            unlink($pathname);
        }
        $error = ErrorHandler::stop();
        if ($error) {
            throw new Exception\RuntimeException("Failed to remove files of '{$path}'", 0, $error);
        }

        return true;
    }

    /* TaggableInterface  */

    /**
     * Set tags to an item by given key.
     * An empty array will remove all tags.
     *
     * @param string   $key
     * @param string[] $tags
     * @return bool
     */
    public function setTags($key, array $tags)
    {
        $this->normalizeKey($key);
        if (!$this->internalHasItem($key)) {
            return false;
        }

        $filespec = $this->getFileSpec($key);

        if (!$tags) {
            $this->unlink($filespec . '.tag');
            return true;
        }

        $this->putFileContent($filespec . '.tag', implode("\n", $tags));
        return true;
    }

    /**
     * Get tags of an item by given key
     *
     * @param string $key
     * @return string[]|FALSE
     */
    public function getTags($key)
    {
        $this->normalizeKey($key);
        if (!$this->internalHasItem($key)) {
            return false;
        }

        $filespec = $this->getFileSpec($key);
        $tags     = [];
        if (file_exists($filespec . '.tag')) {
            $tags = explode("\n", $this->getFileContent($filespec . '.tag'));
        }

        return $tags;
    }

    /**
     * Remove items matching given tags.
     *
     * If $disjunction only one of the given tags must match
     * else all given tags must match.
     *
     * @param string[] $tags
     * @param  bool  $disjunction
     * @return bool
     */
    public function clearByTags(array $tags, $disjunction = false)
    {
        if (!$tags) {
            return true;
        }

        $tagCount  = count($tags);
        $options   = $this->getOptions();
        $namespace = $options->getNamespace();
        $prefix    = ($namespace === '') ? '' : $namespace . $options->getNamespaceSeparator();

        $flags = GlobIterator::SKIP_DOTS | GlobIterator::CURRENT_AS_PATHNAME;
        $path  = $options->getCacheDir()
            . str_repeat(DIRECTORY_SEPARATOR . $prefix . '*', $options->getDirLevel())
            . DIRECTORY_SEPARATOR . $prefix . '*.tag';
        $glob = new GlobIterator($path, $flags);

        foreach ($glob as $pathname) {
            $diff = array_diff($tags, explode("\n", $this->getFileContent($pathname)));

            $rem  = false;
            if ($disjunction && count($diff) < $tagCount) {
                $rem = true;
            } elseif (!$disjunction && !$diff) {
                $rem = true;
            }

            if ($rem) {
                unlink($pathname);

                $datPathname = substr($pathname, 0, -4) . '.dat';
                if (file_exists($datPathname)) {
                    unlink($datPathname);
                }
            }
        }

        return true;
    }

    /* IterableInterface */

    /**
     * Get the storage iterator
     *
     * @return FilesystemIterator
     */
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        $options   = $this->getOptions();
        $namespace = $options->getNamespace();
        $prefix    = ($namespace === '') ? '' : $namespace . $options->getNamespaceSeparator();
        $path      = $options->getCacheDir()
            . str_repeat(DIRECTORY_SEPARATOR . $prefix . '*', $options->getDirLevel())
            . DIRECTORY_SEPARATOR . $prefix . '*.dat';
        return new FilesystemIterator($this, $path, $prefix);
    }

    /* OptimizableInterface */

    /**
     * Optimize the storage
     *
     * @return bool
     * @return Exception\RuntimeException
     */
    public function optimize()
    {
        $options = $this->getOptions();
        if ($options->getDirLevel()) {
            $namespace = $options->getNamespace();
            $prefix    = ($namespace === '') ? '' : $namespace . $options->getNamespaceSeparator();

            // removes only empty directories
            $this->rmDir($options->getCacheDir(), $prefix);
        }
        return true;
    }

    /* TotalSpaceCapableInterface */

    /**
     * Get total space in bytes
     *
     * @throws Exception\RuntimeException
     * @return int|float
     */
    public function getTotalSpace()
    {
        if ($this->totalSpace === null) {
            $path = $this->getOptions()->getCacheDir();

            ErrorHandler::start();
            $total = disk_total_space($path);
            $error = ErrorHandler::stop();
            if ($total === false) {
                throw new Exception\RuntimeException("Can't detect total space of '{$path}'", 0, $error);
            }
            $this->totalSpace = $total;

            // clean total space buffer on change cache_dir
            $events     = $this->getEventManager();
            $handle     = null;
            $totalSpace = & $this->totalSpace;
            $callback   = function ($event) use (& $events, & $handle, & $totalSpace) {
                $params = $event->getParams();
                if (isset($params['cache_dir'])) {
                    $totalSpace = null;
                    $events->detach($handle);
                }
            };
            $events->attach('option', $callback);
        }

        return $this->totalSpace;
    }

    /* AvailableSpaceCapableInterface */

    /**
     * Get available space in bytes
     *
     * @throws Exception\RuntimeException
     * @return float
     */
    public function getAvailableSpace()
    {
        $path = $this->getOptions()->getCacheDir();

        ErrorHandler::start();
        $avail = disk_free_space($path);
        $error = ErrorHandler::stop();
        if ($avail === false) {
            throw new Exception\RuntimeException("Can't detect free space of '{$path}'", 0, $error);
        }

        return $avail;
    }

    /* reading */

    /**
     * Get an item.
     *
     * @param  string  $key
     * @param  bool $success
     * @param  mixed   $casToken
     * @return mixed Data on success, null on failure
     * @throws Exception\ExceptionInterface
     *
     * @triggers getItem.pre(PreEvent)
     * @triggers getItem.post(PostEvent)
     * @triggers getItem.exception(ExceptionEvent)
     */
    public function getItem($key, & $success = null, & $casToken = null)
    {
        $options = $this->getOptions();
        if ($options->getReadable() && $options->getClearStatCache()) {
            clearstatcache();
        }

        $argn = func_num_args();
        if ($argn > 2) {
            return parent::getItem($key, $success, $casToken);
        } elseif ($argn > 1) {
            return parent::getItem($key, $success);
        }

        return parent::getItem($key);
    }

    /**
     * Get multiple items.
     *
     * @param  array $keys
     * @return array Associative array of keys and values
     * @throws Exception\ExceptionInterface
     *
     * @triggers getItems.pre(PreEvent)
     * @triggers getItems.post(PostEvent)
     * @triggers getItems.exception(ExceptionEvent)
     */
    public function getItems(array $keys)
    {
        $options = $this->getOptions();
        if ($options->getReadable() && $options->getClearStatCache()) {
            clearstatcache();
        }

        return parent::getItems($keys);
    }

    /**
     * Internal method to get an item.
     *
     * @param  string  $normalizedKey
     * @param  bool $success
     * @param  mixed   $casToken
     * @return null|mixed Data on success, null on failure
     * @throws Exception\ExceptionInterface
     * @throws BaseException
     */
    protected function internalGetItem(& $normalizedKey, & $success = null, & $casToken = null)
    {
        if (!$this->internalHasItem($normalizedKey)) {
            $success = false;
            return;
        }

        try {
            $filespec = $this->getFileSpec($normalizedKey);
            $data     = $this->getFileContent($filespec . '.dat');

            // use filemtime + filesize as CAS token
            if (func_num_args() > 2) {
                $casToken = filemtime($filespec . '.dat') . filesize($filespec . '.dat');
            }
            $success  = true;
            return $data;
        } catch (BaseException $e) {
            $success = false;
            throw $e;
        }
    }

    /**
     * Internal method to get multiple items.
     *
     * @param  array $normalizedKeys
     * @return array Associative array of keys and values
     * @throws Exception\ExceptionInterface
     */
    protected function internalGetItems(array & $normalizedKeys)
    {
        $keys    = $normalizedKeys; // Don't change argument passed by reference
        $result  = [];
        while ($keys) {
            // LOCK_NB if more than one items have to read
            $nonBlocking = count($keys) > 1;
            $wouldblock  = null;

            // read items
            foreach ($keys as $i => $key) {
                if (!$this->internalHasItem($key)) {
                    unset($keys[$i]);
                    continue;
                }

                $filespec = $this->getFileSpec($key);
                $data     = $this->getFileContent($filespec . '.dat', $nonBlocking, $wouldblock);
                if ($nonBlocking && $wouldblock) {
                    continue;
                } else {
                    unset($keys[$i]);
                }

                $result[$key] = $data;
            }

            // TODO: Don't check ttl after first iteration
            // $options['ttl'] = 0;
        }

        return $result;
    }

    /**
     * Test if an item exists.
     *
     * @param  string $key
     * @return bool
     * @throws Exception\ExceptionInterface
     *
     * @triggers hasItem.pre(PreEvent)
     * @triggers hasItem.post(PostEvent)
     * @triggers hasItem.exception(ExceptionEvent)
     */
    public function hasItem($key)
    {
        $options = $this->getOptions();
        if ($options->getReadable() && $options->getClearStatCache()) {
            clearstatcache();
        }

        return parent::hasItem($key);
    }

    /**
     * Test multiple items.
     *
     * @param  array $keys
     * @return array Array of found keys
     * @throws Exception\ExceptionInterface
     *
     * @triggers hasItems.pre(PreEvent)
     * @triggers hasItems.post(PostEvent)
     * @triggers hasItems.exception(ExceptionEvent)
     */
    public function hasItems(array $keys)
    {
        $options = $this->getOptions();
        if ($options->getReadable() && $options->getClearStatCache()) {
            clearstatcache();
        }

        return parent::hasItems($keys);
    }

    /**
     * Internal method to test if an item exists.
     *
     * @param  string $normalizedKey
     * @return bool
     * @throws Exception\ExceptionInterface
     */
    protected function internalHasItem(& $normalizedKey)
    {
        $file = $this->getFileSpec($normalizedKey) . '.dat';
        if (!file_exists($file)) {
            return false;
        }

        $ttl = $this->getOptions()->getTtl();
        if ($ttl) {
            ErrorHandler::start();
            $mtime = filemtime($file);
            $error = ErrorHandler::stop();
            if (!$mtime) {
                throw new Exception\RuntimeException("Error getting mtime of file '{$file}'", 0, $error);
            }

            if (time() >= ($mtime + $ttl)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get metadata
     *
     * @param string $key
     * @return array|bool Metadata on success, false on failure
     */
    public function getMetadata($key)
    {
        $options = $this->getOptions();
        if ($options->getReadable() && $options->getClearStatCache()) {
            clearstatcache();
        }

        return parent::getMetadata($key);
    }

    /**
     * Get metadatas
     *
     * @param array $keys
     * @param array $options
     * @return array Associative array of keys and metadata
     */
    public function getMetadatas(array $keys, array $options = [])
    {
        $options = $this->getOptions();
        if ($options->getReadable() && $options->getClearStatCache()) {
            clearstatcache();
        }

        return parent::getMetadatas($keys);
    }

    /**
     * Get info by key
     *
     * @param string $normalizedKey
     * @return array|bool Metadata on success, false on failure
     */
    protected function internalGetMetadata(& $normalizedKey)
    {
        if (!$this->internalHasItem($normalizedKey)) {
            return false;
        }

        $options  = $this->getOptions();
        $filespec = $this->getFileSpec($normalizedKey);
        $file     = $filespec . '.dat';

        $metadata = [
            'filespec' => $filespec,
            'mtime'    => filemtime($file)
        ];

        if (!$options->getNoCtime()) {
            $metadata['ctime'] = filectime($file);
        }

        if (!$options->getNoAtime()) {
            $metadata['atime'] = fileatime($file);
        }

        return $metadata;
    }

    /**
     * Internal method to get multiple metadata
     *
     * @param  array $normalizedKeys
     * @return array Associative array of keys and metadata
     * @throws Exception\ExceptionInterface
     */
    protected function internalGetMetadatas(array & $normalizedKeys)
    {
        $options = $this->getOptions();
        $result  = [];

        foreach ($normalizedKeys as $normalizedKey) {
            $filespec = $this->getFileSpec($normalizedKey);
            $file     = $filespec . '.dat';

            $metadata = [
                'filespec' => $filespec,
                'mtime'    => filemtime($file),
            ];

            if (!$options->getNoCtime()) {
                $metadata['ctime'] = filectime($file);
            }

            if (!$options->getNoAtime()) {
                $metadata['atime'] = fileatime($file);
            }

            $result[$normalizedKey] = $metadata;
        }

        return $result;
    }

    /* writing */

    /**
     * Store an item.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return bool
     * @throws Exception\ExceptionInterface
     *
     * @triggers setItem.pre(PreEvent)
     * @triggers setItem.post(PostEvent)
     * @triggers setItem.exception(ExceptionEvent)
     */
    public function setItem($key, $value)
    {
        $options = $this->getOptions();
        if ($options->getWritable() && $options->getClearStatCache()) {
            clearstatcache();
        }
        return parent::setItem($key, $value);
    }

    /**
     * Store multiple items.
     *
     * @param  array $keyValuePairs
     * @return array Array of not stored keys
     * @throws Exception\ExceptionInterface
     *
     * @triggers setItems.pre(PreEvent)
     * @triggers setItems.post(PostEvent)
     * @triggers setItems.exception(ExceptionEvent)
     */
    public function setItems(array $keyValuePairs)
    {
        $options = $this->getOptions();
        if ($options->getWritable() && $options->getClearStatCache()) {
            clearstatcache();
        }

        return parent::setItems($keyValuePairs);
    }

    /**
     * Add an item.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return bool
     * @throws Exception\ExceptionInterface
     *
     * @triggers addItem.pre(PreEvent)
     * @triggers addItem.post(PostEvent)
     * @triggers addItem.exception(ExceptionEvent)
     */
    public function addItem($key, $value)
    {
        $options = $this->getOptions();
        if ($options->getWritable() && $options->getClearStatCache()) {
            clearstatcache();
        }

        return parent::addItem($key, $value);
    }

    /**
     * Add multiple items.
     *
     * @param  array $keyValuePairs
     * @return bool
     * @throws Exception\ExceptionInterface
     *
     * @triggers addItems.pre(PreEvent)
     * @triggers addItems.post(PostEvent)
     * @triggers addItems.exception(ExceptionEvent)
     */
    public function addItems(array $keyValuePairs)
    {
        $options = $this->getOptions();
        if ($options->getWritable() && $options->getClearStatCache()) {
            clearstatcache();
        }

        return parent::addItems($keyValuePairs);
    }

    /**
     * Replace an existing item.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return bool
     * @throws Exception\ExceptionInterface
     *
     * @triggers replaceItem.pre(PreEvent)
     * @triggers replaceItem.post(PostEvent)
     * @triggers replaceItem.exception(ExceptionEvent)
     */
    public function replaceItem($key, $value)
    {
        $options = $this->getOptions();
        if ($options->getWritable() && $options->getClearStatCache()) {
            clearstatcache();
        }

        return parent::replaceItem($key, $value);
    }

    /**
     * Replace multiple existing items.
     *
     * @param  array $keyValuePairs
     * @return bool
     * @throws Exception\ExceptionInterface
     *
     * @triggers replaceItems.pre(PreEvent)
     * @triggers replaceItems.post(PostEvent)
     * @triggers replaceItems.exception(ExceptionEvent)
     */
    public function replaceItems(array $keyValuePairs)
    {
        $options = $this->getOptions();
        if ($options->getWritable() && $options->getClearStatCache()) {
            clearstatcache();
        }

        return parent::replaceItems($keyValuePairs);
    }

    /**
     * Internal method to store an item.
     *
     * @param  string $normalizedKey
     * @param  mixed  $value
     * @return bool
     * @throws Exception\ExceptionInterface
     */
    protected function internalSetItem(& $normalizedKey, & $value)
    {
        $filespec = $this->getFileSpec($normalizedKey);
        $this->prepareDirectoryStructure($filespec);

        // write data in non-blocking mode
        $wouldblock = null;
        $this->putFileContent($filespec . '.dat', $value, true, $wouldblock);

        // delete related tag file (if present)
        $this->unlink($filespec . '.tag');

        // Retry writing data in blocking mode if it was blocked before
        if ($wouldblock) {
            $this->putFileContent($filespec . '.dat', $value);
        }

        return true;
    }

    /**
     * Internal method to store multiple items.
     *
     * @param  array $normalizedKeyValuePairs
     * @return array Array of not stored keys
     * @throws Exception\ExceptionInterface
     */
    protected function internalSetItems(array & $normalizedKeyValuePairs)
    {
        // create an associated array of files and contents to write
        $contents = [];
        foreach ($normalizedKeyValuePairs as $key => & $value) {
            $filespec = $this->getFileSpec($key);
            $this->prepareDirectoryStructure($filespec);

            // *.dat file
            $contents[$filespec . '.dat'] = & $value;

            // *.tag file
            $this->unlink($filespec . '.tag');
        }

        // write to disk
        while ($contents) {
            $nonBlocking = count($contents) > 1;
            $wouldblock  = null;

            foreach ($contents as $file => & $content) {
                $this->putFileContent($file, $content, $nonBlocking, $wouldblock);
                if (!$nonBlocking || !$wouldblock) {
                    unset($contents[$file]);
                }
            }
        }

        // return OK
        return [];
    }

    /**
     * Set an item only if token matches
     *
     * It uses the token received from getItem() to check if the item has
     * changed before overwriting it.
     *
     * @param  mixed  $token
     * @param  string $key
     * @param  mixed  $value
     * @return bool
     * @throws Exception\ExceptionInterface
     * @see    getItem()
     * @see    setItem()
     */
    public function checkAndSetItem($token, $key, $value)
    {
        $options = $this->getOptions();
        if ($options->getWritable() && $options->getClearStatCache()) {
            clearstatcache();
        }

        return parent::checkAndSetItem($token, $key, $value);
    }

    /**
     * Internal method to set an item only if token matches
     *
     * @param  mixed  $token
     * @param  string $normalizedKey
     * @param  mixed  $value
     * @return bool
     * @throws Exception\ExceptionInterface
     * @see    getItem()
     * @see    setItem()
     */
    protected function internalCheckAndSetItem(& $token, & $normalizedKey, & $value)
    {
        if (!$this->internalHasItem($normalizedKey)) {
            return false;
        }

        // use filemtime + filesize as CAS token
        $file  = $this->getFileSpec($normalizedKey) . '.dat';
        $check = filemtime($file) . filesize($file);
        if ($token !== $check) {
            return false;
        }

        return $this->internalSetItem($normalizedKey, $value);
    }

    /**
     * Reset lifetime of an item
     *
     * @param  string $key
     * @return bool
     * @throws Exception\ExceptionInterface
     *
     * @triggers touchItem.pre(PreEvent)
     * @triggers touchItem.post(PostEvent)
     * @triggers touchItem.exception(ExceptionEvent)
     */
    public function touchItem($key)
    {
        $options = $this->getOptions();
        if ($options->getWritable() && $options->getClearStatCache()) {
            clearstatcache();
        }

        return parent::touchItem($key);
    }

    /**
     * Reset lifetime of multiple items.
     *
     * @param  array $keys
     * @return array Array of not updated keys
     * @throws Exception\ExceptionInterface
     *
     * @triggers touchItems.pre(PreEvent)
     * @triggers touchItems.post(PostEvent)
     * @triggers touchItems.exception(ExceptionEvent)
     */
    public function touchItems(array $keys)
    {
        $options = $this->getOptions();
        if ($options->getWritable() && $options->getClearStatCache()) {
            clearstatcache();
        }

        return parent::touchItems($keys);
    }

    /**
     * Internal method to reset lifetime of an item
     *
     * @param  string $normalizedKey
     * @return bool
     * @throws Exception\ExceptionInterface
     */
    protected function internalTouchItem(& $normalizedKey)
    {
        if (!$this->internalHasItem($normalizedKey)) {
            return false;
        }

        $filespec = $this->getFileSpec($normalizedKey);

        ErrorHandler::start();
        $touch = touch($filespec . '.dat');
        $error = ErrorHandler::stop();
        if (!$touch) {
            throw new Exception\RuntimeException("Error touching file '{$filespec}.dat'", 0, $error);
        }

        return true;
    }

    /**
     * Remove an item.
     *
     * @param  string $key
     * @return bool
     * @throws Exception\ExceptionInterface
     *
     * @triggers removeItem.pre(PreEvent)
     * @triggers removeItem.post(PostEvent)
     * @triggers removeItem.exception(ExceptionEvent)
     */
    public function removeItem($key)
    {
        $options = $this->getOptions();
        if ($options->getWritable() && $options->getClearStatCache()) {
            clearstatcache();
        }

        return parent::removeItem($key);
    }

    /**
     * Remove multiple items.
     *
     * @param  array $keys
     * @return array Array of not removed keys
     * @throws Exception\ExceptionInterface
     *
     * @triggers removeItems.pre(PreEvent)
     * @triggers removeItems.post(PostEvent)
     * @triggers removeItems.exception(ExceptionEvent)
     */
    public function removeItems(array $keys)
    {
        $options = $this->getOptions();
        if ($options->getWritable() && $options->getClearStatCache()) {
            clearstatcache();
        }

        return parent::removeItems($keys);
    }

    /**
     * Internal method to remove an item.
     *
     * @param  string $normalizedKey
     * @return bool
     * @throws Exception\ExceptionInterface
     */
    protected function internalRemoveItem(& $normalizedKey)
    {
        $filespec = $this->getFileSpec($normalizedKey);
        if (!file_exists($filespec . '.dat')) {
            return false;
        } else {
            $this->unlink($filespec . '.dat');
            $this->unlink($filespec . '.tag');
        }
        return true;
    }

    /* status */

    /**
     * Internal method to get capabilities of this adapter
     *
     * @return Capabilities
     */
    protected function internalGetCapabilities()
    {
        if ($this->capabilities === null) {
            $marker  = new stdClass();
            $options = $this->getOptions();

            // detect metadata
            $metadata = ['mtime', 'filespec'];
            if (!$options->getNoAtime()) {
                $metadata[] = 'atime';
            }
            if (!$options->getNoCtime()) {
                $metadata[] = 'ctime';
            }

            $capabilities = new Capabilities(
                $this,
                $marker,
                [
                    'supportedDatatypes' => [
                        'NULL'     => 'string',
                        'boolean'  => 'string',
                        'integer'  => 'string',
                        'double'   => 'string',
                        'string'   => true,
                        'array'    => false,
                        'object'   => false,
                        'resource' => false,
                    ],
                    'supportedMetadata'  => $metadata,
                    'minTtl'             => 1,
                    'maxTtl'             => 0,
                    'staticTtl'          => false,
                    'ttlPrecision'       => 1,
                    'expiredRead'        => true,
                    'maxKeyLength'       => 251, // 255 - strlen(.dat | .tag)
                    'namespaceIsPrefix'  => true,
                    'namespaceSeparator' => $options->getNamespaceSeparator(),
                ]
            );

            // update capabilities on change options
            $this->getEventManager()->attach('option', function ($event) use ($capabilities, $marker) {
                $params = $event->getParams();

                if (isset($params['namespace_separator'])) {
                    $capabilities->setNamespaceSeparator($marker, $params['namespace_separator']);
                }

                if (isset($params['no_atime']) || isset($params['no_ctime'])) {
                    $metadata = $capabilities->getSupportedMetadata();

                    if (isset($params['no_atime']) && !$params['no_atime']) {
                        $metadata[] = 'atime';
                    } elseif (isset($params['no_atime']) && ($index = array_search('atime', $metadata)) !== false) {
                        unset($metadata[$index]);
                    }

                    if (isset($params['no_ctime']) && !$params['no_ctime']) {
                        $metadata[] = 'ctime';
                    } elseif (isset($params['no_ctime']) && ($index = array_search('ctime', $metadata)) !== false) {
                        unset($metadata[$index]);
                    }

                    $capabilities->setSupportedMetadata($marker, $metadata);
                }
            });

            $this->capabilityMarker = $marker;
            $this->capabilities     = $capabilities;
        }

        return $this->capabilities;
    }

    /* internal */

    /**
     * Removes directories recursive by namespace
     *
     * @param  string $dir    Directory to delete
     * @param  string $prefix Namespace + Separator
     * @return bool
     */
    protected function rmDir($dir, $prefix)
    {
        $glob = glob(
            $dir . DIRECTORY_SEPARATOR . $prefix  . '*',
            GLOB_ONLYDIR | GLOB_NOESCAPE | GLOB_NOSORT
        );
        if (!$glob) {
            // On some systems glob returns false even on empty result
            return true;
        }

        $ret = true;
        foreach ($glob as $subdir) {
            // skip removing current directory if removing of sub-directory failed
            if ($this->rmDir($subdir, $prefix)) {
                // ignore not empty directories
                ErrorHandler::start();
                $ret = rmdir($subdir) && $ret;
                ErrorHandler::stop();
            } else {
                $ret = false;
            }
        }

        return $ret;
    }

    /**
     * Get file spec of the given key and namespace
     *
     * @param  string $normalizedKey
     * @return string
     */
    protected function getFileSpec($normalizedKey)
    {
        $options   = $this->getOptions();
        $namespace = $options->getNamespace();
        $prefix    = ($namespace === '') ? '' : $namespace . $options->getNamespaceSeparator();
        $path      = $options->getCacheDir() . DIRECTORY_SEPARATOR;
        $level     = $options->getDirLevel();

        $fileSpecId = $path . $prefix . $normalizedKey . '/' . $level;
        if ($this->lastFileSpecId !== $fileSpecId) {
            if ($level > 0) {
                // create up to 256 directories per directory level
                $hash = md5($normalizedKey);
                for ($i = 0, $max = ($level * 2); $i < $max; $i+= 2) {
                    $path .= $prefix . $hash[$i] . $hash[$i+1] . DIRECTORY_SEPARATOR;
                }
            }

            $this->lastFileSpecId = $fileSpecId;
            $this->lastFileSpec   = $path . $prefix . $normalizedKey;
        }

        return $this->lastFileSpec;
    }

    /**
     * Read info file
     *
     * @param  string  $file
     * @param  bool $nonBlocking Don't block script if file is locked
     * @param  bool $wouldblock  The optional argument is set to TRUE if the lock would block
     * @return array|bool The info array or false if file wasn't found
     * @throws Exception\RuntimeException
     */
    protected function readInfoFile($file, $nonBlocking = false, & $wouldblock = null)
    {
        if (!file_exists($file)) {
            return false;
        }

        $content = $this->getFileContent($file, $nonBlocking, $wouldblock);
        if ($nonBlocking && $wouldblock) {
            return false;
        }

        ErrorHandler::start();
        $ifo = unserialize($content);
        $err = ErrorHandler::stop();
        if (!is_array($ifo)) {
            throw new Exception\RuntimeException("Corrupted info file '{$file}'", 0, $err);
        }

        return $ifo;
    }

    /**
     * Read a complete file
     *
     * @param  string  $file        File complete path
     * @param  bool $nonBlocking Don't block script if file is locked
     * @param  bool $wouldblock  The optional argument is set to TRUE if the lock would block
     * @return string
     * @throws Exception\RuntimeException
     */
    protected function getFileContent($file, $nonBlocking = false, & $wouldblock = null)
    {
        $locking    = $this->getOptions()->getFileLocking();
        $wouldblock = null;

        ErrorHandler::start();

        // if file locking enabled -> file_get_contents can't be used
        if ($locking) {
            $fp = fopen($file, 'rb');
            if ($fp === false) {
                $err = ErrorHandler::stop();
                throw new Exception\RuntimeException("Error opening file '{$file}'", 0, $err);
            }

            if ($nonBlocking) {
                $lock = flock($fp, LOCK_SH | LOCK_NB, $wouldblock);
                if ($wouldblock) {
                    fclose($fp);
                    ErrorHandler::stop();
                    return;
                }
            } else {
                $lock = flock($fp, LOCK_SH);
            }

            if (!$lock) {
                fclose($fp);
                $err = ErrorHandler::stop();
                throw new Exception\RuntimeException("Error locking file '{$file}'", 0, $err);
            }

            $res = stream_get_contents($fp);
            if ($res === false) {
                flock($fp, LOCK_UN);
                fclose($fp);
                $err = ErrorHandler::stop();
                throw new Exception\RuntimeException('Error getting stream contents', 0, $err);
            }

            flock($fp, LOCK_UN);
            fclose($fp);

        // if file locking disabled -> file_get_contents can be used
        } else {
            $res = file_get_contents($file, false);
            if ($res === false) {
                $err = ErrorHandler::stop();
                throw new Exception\RuntimeException("Error getting file contents for file '{$file}'", 0, $err);
            }
        }

        ErrorHandler::stop();
        return $res;
    }

    /**
     * Prepares a directory structure for the given file(spec)
     * using the configured directory level.
     *
     * @param string $file
     * @return void
     * @throws Exception\RuntimeException
     */
    protected function prepareDirectoryStructure($file)
    {
        $options = $this->getOptions();
        $level   = $options->getDirLevel();

        // Directory structure is required only if directory level > 0
        if (!$level) {
            return;
        }

        // Directory structure already exists
        $pathname = dirname($file);
        if (file_exists($pathname)) {
            return;
        }

        $perm     = $options->getDirPermission();
        $umask    = $options->getUmask();
        if ($umask !== false && $perm !== false) {
            $perm = $perm & ~$umask;
        }

        ErrorHandler::start();

        if ($perm === false || $level == 1) {
            // build-in mkdir function is enough

            $umask = ($umask !== false) ? umask($umask) : false;
            $res   = mkdir($pathname, ($perm !== false) ? $perm : 0777, true);

            if ($umask !== false) {
                umask($umask);
            }

            if (!$res) {
                $err = ErrorHandler::stop();

                // Issue 6435:
                // mkdir could fail because of a race condition it was already created by another process
                // after the first file_exists above
                if (file_exists($pathname)) {
                    return;
                }

                $oct = ($perm === false) ? '777' : decoct($perm);
                throw new Exception\RuntimeException("mkdir('{$pathname}', 0{$oct}, true) failed", 0, $err);
            }

            if ($perm !== false && !chmod($pathname, $perm)) {
                $oct = decoct($perm);
                $err = ErrorHandler::stop();
                throw new Exception\RuntimeException("chmod('{$pathname}', 0{$oct}) failed", 0, $err);
            }
        } else {
            // build-in mkdir function sets permission together with current umask
            // which doesn't work well on multo threaded webservers
            // -> create directories one by one and set permissions

            // find existing path and missing path parts
            $parts = [];
            $path  = $pathname;
            while (!file_exists($path)) {
                array_unshift($parts, basename($path));
                $nextPath = dirname($path);
                if ($nextPath === $path) {
                    break;
                }
                $path = $nextPath;
            }

            // make all missing path parts
            foreach ($parts as $part) {
                $path.= DIRECTORY_SEPARATOR . $part;

                // create a single directory, set and reset umask immediately
                $umask = ($umask !== false) ? umask($umask) : false;
                $res   = mkdir($path, ($perm === false) ? 0777 : $perm, false);
                if ($umask !== false) {
                    umask($umask);
                }

                if (!$res) {
                    // Issue 6435:
                    // mkdir could fail because of a race condition it was already created by another process
                    // after the first file_exists above ... go to the next path part.
                    if (file_exists($path)) {
                        continue;
                    }

                    $oct = ($perm === false) ? '777' : decoct($perm);
                    ErrorHandler::stop();
                    throw new Exception\RuntimeException(
                        "mkdir('{$path}', 0{$oct}, false) failed"
                    );
                }

                if ($perm !== false && !chmod($path, $perm)) {
                    $oct = decoct($perm);
                    ErrorHandler::stop();
                    throw new Exception\RuntimeException(
                        "chmod('{$path}', 0{$oct}) failed"
                    );
                }
            }
        }

        ErrorHandler::stop();
    }

    /**
     * Write content to a file
     *
     * @param  string  $file        File complete path
     * @param  string  $data        Data to write
     * @param  bool $nonBlocking Don't block script if file is locked
     * @param  bool $wouldblock  The optional argument is set to TRUE if the lock would block
     * @return void
     * @throws Exception\RuntimeException
     */
    protected function putFileContent($file, $data, $nonBlocking = false, & $wouldblock = null)
    {
        if (! is_string($data)) {
            // Ensure we have a string
            $data = (string) $data;
        }

        $options     = $this->getOptions();
        $locking     = $options->getFileLocking();
        $nonBlocking = $locking && $nonBlocking;
        $wouldblock  = null;

        $umask = $options->getUmask();
        $perm  = $options->getFilePermission();
        if ($umask !== false && $perm !== false) {
            $perm = $perm & ~$umask;
        }

        ErrorHandler::start();

        // if locking and non blocking is enabled -> file_put_contents can't used
        if ($locking && $nonBlocking) {
            $umask = ($umask !== false) ? umask($umask) : false;

            $fp = fopen($file, 'cb');

            if ($umask) {
                umask($umask);
            }

            if (!$fp) {
                $err = ErrorHandler::stop();
                throw new Exception\RuntimeException("Error opening file '{$file}'", 0, $err);
            }

            if ($perm !== false && !chmod($file, $perm)) {
                fclose($fp);
                $oct = decoct($perm);
                $err = ErrorHandler::stop();
                throw new Exception\RuntimeException("chmod('{$file}', 0{$oct}) failed", 0, $err);
            }

            if (!flock($fp, LOCK_EX | LOCK_NB, $wouldblock)) {
                fclose($fp);
                $err = ErrorHandler::stop();
                if ($wouldblock) {
                    return;
                } else {
                    throw new Exception\RuntimeException("Error locking file '{$file}'", 0, $err);
                }
            }

            if (fwrite($fp, $data) === false) {
                flock($fp, LOCK_UN);
                fclose($fp);
                $err = ErrorHandler::stop();
                throw new Exception\RuntimeException("Error writing file '{$file}'", 0, $err);
            }

            if (!ftruncate($fp, strlen($data))) {
                flock($fp, LOCK_UN);
                fclose($fp);
                $err = ErrorHandler::stop();
                throw new Exception\RuntimeException("Error truncating file '{$file}'", 0, $err);
            }

            flock($fp, LOCK_UN);
            fclose($fp);

        // else -> file_put_contents can be used
        } else {
            $flags = 0;
            if ($locking) {
                $flags = $flags | LOCK_EX;
            }

            $umask = ($umask !== false) ? umask($umask) : false;

            $rs = file_put_contents($file, $data, $flags);

            if ($umask) {
                umask($umask);
            }

            if ($rs === false) {
                $err = ErrorHandler::stop();
                throw new Exception\RuntimeException("Error writing file '{$file}'", 0, $err);
            }

            if ($perm !== false && !chmod($file, $perm)) {
                $oct = decoct($perm);
                $err = ErrorHandler::stop();
                throw new Exception\RuntimeException("chmod('{$file}', 0{$oct}) failed", 0, $err);
            }
        }

        ErrorHandler::stop();
    }

    /**
     * Unlink a file
     *
     * @param string $file
     * @return void
     * @throws Exception\RuntimeException
     */
    protected function unlink($file)
    {
        ErrorHandler::start();
        $res = unlink($file);
        $err = ErrorHandler::stop();

        // only throw exception if file still exists after deleting
        if (!$res && file_exists($file)) {
            throw new Exception\RuntimeException(
                "Error unlinking file '{$file}'; file still exists",
                0,
                $err
            );
        }
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Stdlib;

interface ParameterObjectInterface
{
    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set($key, $value);

    /**
     * @param string $key
     * @return mixed
     */
    public function __get($key);

    /**
     * @param string $key
     * @return bool
     */
    public function __isset($key);

    /**
     * @param string $key
     * @return void
     */
    public function __unset($key);
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Stdlib;

use Traversable;

abstract class AbstractOptions implements ParameterObjectInterface
{
    /**
     * We use the __ prefix to avoid collisions with properties in
     * user-implementations.
     *
     * @var bool
     */
    protected $__strictMode__ = true;

    /**
     * Constructor
     *
     * @param  array|Traversable|null $options
     */
    public function __construct($options = null)
    {
        if (null !== $options) {
            $this->setFromArray($options);
        }
    }

    /**
     * Set one or more configuration properties
     *
     * @param  array|Traversable|AbstractOptions $options
     * @throws Exception\InvalidArgumentException
     * @return AbstractOptions Provides fluent interface
     */
    public function setFromArray($options)
    {
        if ($options instanceof self) {
            $options = $options->toArray();
        }

        if (!is_array($options) && !$options instanceof Traversable) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    'Parameter provided to %s must be an %s, %s or %s',
                    __METHOD__,
                    'array',
                    'Traversable',
                    'Zend\Stdlib\AbstractOptions'
                )
            );
        }

        foreach ($options as $key => $value) {
            $this->__set($key, $value);
        }

        return $this;
    }

    /**
     * Cast to array
     *
     * @return array
     */
    public function toArray()
    {
        $array = [];
        $transform = function ($letters) {
            $letter = array_shift($letters);
            return '_' . strtolower($letter);
        };
        foreach ($this as $key => $value) {
            if ($key === '__strictMode__') {
                continue;
            }
            $normalizedKey = preg_replace_callback('/([A-Z])/', $transform, $key);
            $array[$normalizedKey] = $value;
        }
        return $array;
    }

    /**
     * Set a configuration property
     *
     * @see ParameterObject::__set()
     * @param string $key
     * @param mixed $value
     * @throws Exception\BadMethodCallException
     * @return void
     */
    public function __set($key, $value)
    {
        $setter = 'set' . str_replace('_', '', $key);

        if (is_callable([$this, $setter])) {
            $this->{$setter}($value);

            return;
        }

        if ($this->__strictMode__) {
            throw new Exception\BadMethodCallException(sprintf(
                'The option "%s" does not have a callable "%s" ("%s") setter method which must be defined',
                $key,
                'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key))),
                $setter
            ));
        }
    }

    /**
     * Get a configuration property
     *
     * @see ParameterObject::__get()
     * @param string $key
     * @throws Exception\BadMethodCallException
     * @return mixed
     */
    public function __get($key)
    {
        $getter = 'get' . str_replace('_', '', $key);

        if (is_callable([$this, $getter])) {
            return $this->{$getter}();
        }

        throw new Exception\BadMethodCallException(sprintf(
            'The option "%s" does not have a callable "%s" getter method which must be defined',
            $key,
            'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key)))
        ));
    }

    /**
     * Test if a configuration property is null
     * @see ParameterObject::__isset()
     * @param string $key
     * @return bool
     */
    public function __isset($key)
    {
        $getter = 'get' . str_replace('_', '', $key);

        return method_exists($this, $getter) && null !== $this->__get($key);
    }

    /**
     * Set a configuration property to NULL
     *
     * @see ParameterObject::__unset()
     * @param string $key
     * @throws Exception\InvalidArgumentException
     * @return void
     */
    public function __unset($key)
    {
        try {
            $this->__set($key, null);
        } catch (Exception\BadMethodCallException $e) {
            throw new Exception\InvalidArgumentException(
                'The class property $' . $key . ' cannot be unset as'
                . ' NULL is an invalid value for it',
                0,
                $e
            );
        }
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Storage\Adapter;

use ArrayObject;
use Zend\Cache\Exception;
use Zend\Cache\Storage\Event;
use Zend\Cache\Storage\StorageInterface;
use Zend\EventManager\EventsCapableInterface;
use Zend\Stdlib\AbstractOptions;
use Zend\Stdlib\ErrorHandler;

/**
 * Unless otherwise marked, all options in this class affect all adapters.
 */
class AdapterOptions extends AbstractOptions
{
    /**
     * The adapter using these options
     *
     * @var null|StorageInterface
     */
    protected $adapter;

    /**
     * Validate key against pattern
     *
     * @var string
     */
    protected $keyPattern = '';

    /**
     * Namespace option
     *
     * @var string
     */
    protected $namespace = 'zfcache';

    /**
     * Readable option
     *
     * @var bool
     */
    protected $readable = true;

    /**
     * TTL option
     *
     * @var int|float 0 means infinite or maximum of adapter
     */
    protected $ttl = 0;

    /**
     * Writable option
     *
     * @var bool
     */
    protected $writable = true;

    /**
     * Adapter using this instance
     *
     * @param  StorageInterface|null $adapter
     * @return AdapterOptions
     */
    public function setAdapter(?StorageInterface $adapter = null)
    {
        $this->adapter = $adapter;
        return $this;
    }

    /**
     * Set key pattern
     *
     * @param  null|string $keyPattern
     * @throws Exception\InvalidArgumentException
     * @return AdapterOptions
     */
    public function setKeyPattern($keyPattern)
    {
        $keyPattern = (string) $keyPattern;
        if ($this->keyPattern !== $keyPattern) {
            // validate pattern
            if ($keyPattern !== '') {
                ErrorHandler::start(E_WARNING);
                $result = preg_match($keyPattern, '');
                $error = ErrorHandler::stop();
                if ($result === false) {
                    throw new Exception\InvalidArgumentException(sprintf(
                        'Invalid pattern "%s"%s',
                        $keyPattern,
                        ($error ? ': ' . $error->getMessage() : '')
                    ), 0, $error);
                }
            }

            $this->triggerOptionEvent('key_pattern', $keyPattern);
            $this->keyPattern = $keyPattern;
        }

        return $this;
    }

    /**
     * Get key pattern
     *
     * @return string
     */
    public function getKeyPattern()
    {
        return $this->keyPattern;
    }

    /**
     * Set namespace.
     *
     * @param  string $namespace
     * @return AdapterOptions
     */
    public function setNamespace($namespace)
    {
        $namespace = (string) $namespace;
        if ($this->namespace !== $namespace) {
            $this->triggerOptionEvent('namespace', $namespace);
            $this->namespace = $namespace;
        }

        return $this;
    }

    /**
     * Get namespace
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Enable/Disable reading data from cache.
     *
     * @param  bool $readable
     * @return AbstractAdapter
     */
    public function setReadable($readable)
    {
        $readable = (bool) $readable;
        if ($this->readable !== $readable) {
            $this->triggerOptionEvent('readable', $readable);
            $this->readable = $readable;
        }
        return $this;
    }

    /**
     * If reading data from cache enabled.
     *
     * @return bool
     */
    public function getReadable()
    {
        return $this->readable;
    }

    /**
     * Set time to live.
     *
     * @param  int|float $ttl
     * @return AdapterOptions
     */
    public function setTtl($ttl)
    {
        $this->normalizeTtl($ttl);
        if ($this->ttl !== $ttl) {
            $this->triggerOptionEvent('ttl', $ttl);
            $this->ttl = $ttl;
        }
        return $this;
    }

    /**
     * Get time to live.
     *
     * @return float
     */
    public function getTtl()
    {
        return $this->ttl;
    }

    /**
     * Enable/Disable writing data to cache.
     *
     * @param  bool $writable
     * @return AdapterOptions
     */
    public function setWritable($writable)
    {
        $writable = (bool) $writable;
        if ($this->writable !== $writable) {
            $this->triggerOptionEvent('writable', $writable);
            $this->writable = $writable;
        }
        return $this;
    }

    /**
     * If writing data to cache enabled.
     *
     * @return bool
     */
    public function getWritable()
    {
        return $this->writable;
    }

    /**
     * Triggers an option event if this options instance has a connection to
     * an adapter implements EventsCapableInterface.
     *
     * @param string $optionName
     * @param mixed  $optionValue
     * @return void
     */
    protected function triggerOptionEvent($optionName, $optionValue)
    {
        if ($this->adapter instanceof EventsCapableInterface) {
            $event = new Event('option', $this->adapter, new ArrayObject([$optionName => $optionValue]));
            $this->adapter->getEventManager()->trigger($event);
        }
    }

    /**
     * Validates and normalize a TTL.
     *
     * @param  int|float $ttl
     * @throws Exception\InvalidArgumentException
     * @return void
     */
    protected function normalizeTtl(&$ttl)
    {
        if (!is_int($ttl)) {
            $ttl = (float) $ttl;

            // convert to int if possible
            if ($ttl === (float) (int) $ttl) {
                $ttl = (int) $ttl;
            }
        }

        if ($ttl < 0) {
            throw new Exception\InvalidArgumentException("TTL can't be negative");
        }
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Storage\Adapter;

use Traversable;
use Zend\Cache\Exception;

/**
 * These are options specific to the Filesystem adapter
 */
class FilesystemOptions extends AdapterOptions
{
    /**
     * Directory to store cache files
     *
     * @var null|string The cache directory
     *                  or NULL for the systems temporary directory
     */
    protected $cacheDir = null;

    /**
     * Call clearstatcache enabled?
     *
     * @var bool
     */
    protected $clearStatCache = true;

    /**
     * How much sub-directaries should be created?
     *
     * @var int
     */
    protected $dirLevel = 1;

    /**
     * Permission creating new directories
     *
     * @var false|int
     */
    protected $dirPermission = 0700;

    /**
     * Lock files on writing
     *
     * @var bool
     */
    protected $fileLocking = true;

    /**
     * Permission creating new files
     *
     * @var false|int
     */
    protected $filePermission = 0600;

    /**
     * Overwrite default key pattern
     *
     * Defined in AdapterOptions
     *
     * @var string
     */
    protected $keyPattern = '/^[a-z0-9_\+\-]*$/Di';

    /**
     * Namespace separator
     *
     * @var string
     */
    protected $namespaceSeparator = '-';

    /**
     * Don't get 'fileatime' as 'atime' on metadata
     *
     * @var bool
     */
    protected $noAtime = true;

    /**
     * Don't get 'filectime' as 'ctime' on metadata
     *
     * @var bool
     */
    protected $noCtime = true;

    /**
     * Umask to create files and directories
     *
     * @var false|int
     */
    protected $umask = false;

    /**
     * Constructor
     *
     * @param  array|Traversable|null $options
     * @return FilesystemOptions
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($options = null)
    {
        // disable file/directory permissions by default on windows systems
        if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
            $this->filePermission = false;
            $this->dirPermission = false;
        }

        parent::__construct($options);
    }

    /**
     * Set cache dir
     *
     * @param  string $cacheDir
     * @return FilesystemOptions
     * @throws Exception\InvalidArgumentException
     */
    public function setCacheDir($cacheDir)
    {
        if ($cacheDir !== null) {
            if (!is_dir($cacheDir)) {
                throw new Exception\InvalidArgumentException(
                    "Cache directory '{$cacheDir}' not found or not a directory"
                );
            } elseif (!is_writable($cacheDir)) {
                throw new Exception\InvalidArgumentException(
                    "Cache directory '{$cacheDir}' not writable"
                );
            } elseif (!is_readable($cacheDir)) {
                throw new Exception\InvalidArgumentException(
                    "Cache directory '{$cacheDir}' not readable"
                );
            }

            $cacheDir = rtrim(realpath($cacheDir), DIRECTORY_SEPARATOR);
        } else {
            $cacheDir = sys_get_temp_dir();
        }

        $this->triggerOptionEvent('cache_dir', $cacheDir);
        $this->cacheDir = $cacheDir;
        return $this;
    }

    /**
     * Get cache dir
     *
     * @return null|string
     */
    public function getCacheDir()
    {
        if ($this->cacheDir === null) {
            $this->setCacheDir(null);
        }

        return $this->cacheDir;
    }

    /**
     * Set clear stat cache
     *
     * @param  bool $clearStatCache
     * @return FilesystemOptions
     */
    public function setClearStatCache($clearStatCache)
    {
        $clearStatCache = (bool) $clearStatCache;
        $this->triggerOptionEvent('clear_stat_cache', $clearStatCache);
        $this->clearStatCache = $clearStatCache;
        return $this;
    }

    /**
     * Get clear stat cache
     *
     * @return bool
     */
    public function getClearStatCache()
    {
        return $this->clearStatCache;
    }

    /**
     * Set dir level
     *
     * @param  int $dirLevel
     * @return FilesystemOptions
     * @throws Exception\InvalidArgumentException
     */
    public function setDirLevel($dirLevel)
    {
        $dirLevel = (int) $dirLevel;
        if ($dirLevel < 0 || $dirLevel > 16) {
            throw new Exception\InvalidArgumentException(
                "Directory level '{$dirLevel}' must be between 0 and 16"
            );
        }
        $this->triggerOptionEvent('dir_level', $dirLevel);
        $this->dirLevel = $dirLevel;
        return $this;
    }

    /**
     * Get dir level
     *
     * @return int
     */
    public function getDirLevel()
    {
        return $this->dirLevel;
    }

    /**
     * Set permission to create directories on unix systems
     *
     * @param false|string|int $dirPermission FALSE to disable explicit permission or an octal number
     * @return FilesystemOptions
     * @see setUmask
     * @see setFilePermission
     * @link http://php.net/manual/function.chmod.php
     */
    public function setDirPermission($dirPermission)
    {
        if ($dirPermission !== false) {
            if (is_string($dirPermission)) {
                $dirPermission = octdec($dirPermission);
            } else {
                $dirPermission = (int) $dirPermission;
            }

            // validate
            if (($dirPermission & 0700) != 0700) {
                throw new Exception\InvalidArgumentException(
                    'Invalid directory permission: need permission to execute, read and write by owner'
                );
            }
        }

        if ($this->dirPermission !== $dirPermission) {
            $this->triggerOptionEvent('dir_permission', $dirPermission);
            $this->dirPermission = $dirPermission;
        }

        return $this;
    }

    /**
     * Get permission to create directories on unix systems
     *
     * @return false|int
     */
    public function getDirPermission()
    {
        return $this->dirPermission;
    }

    /**
     * Set file locking
     *
     * @param  bool $fileLocking
     * @return FilesystemOptions
     */
    public function setFileLocking($fileLocking)
    {
        $fileLocking = (bool) $fileLocking;
        $this->triggerOptionEvent('file_locking', $fileLocking);
        $this->fileLocking = $fileLocking;
        return $this;
    }

    /**
     * Get file locking
     *
     * @return bool
     */
    public function getFileLocking()
    {
        return $this->fileLocking;
    }

    /**
     * Set permission to create files on unix systems
     *
     * @param false|string|int $filePermission FALSE to disable explicit permission or an octal number
     * @return FilesystemOptions
     * @see setUmask
     * @see setDirPermission
     * @link http://php.net/manual/function.chmod.php
     */
    public function setFilePermission($filePermission)
    {
        if ($filePermission !== false) {
            if (is_string($filePermission)) {
                $filePermission = octdec($filePermission);
            } else {
                $filePermission = (int) $filePermission;
            }

            // validate
            if (($filePermission & 0600) != 0600) {
                throw new Exception\InvalidArgumentException(
                    'Invalid file permission: need permission to read and write by owner'
                );
            } elseif ($filePermission & 0111) {
                throw new Exception\InvalidArgumentException(
                    "Invalid file permission: Cache files shoudn't be executable"
                );
            }
        }

        if ($this->filePermission !== $filePermission) {
            $this->triggerOptionEvent('file_permission', $filePermission);
            $this->filePermission = $filePermission;
        }

        return $this;
    }

    /**
     * Get permission to create files on unix systems
     *
     * @return false|int
     */
    public function getFilePermission()
    {
        return $this->filePermission;
    }

    /**
     * Set namespace separator
     *
     * @param  string $namespaceSeparator
     * @return FilesystemOptions
     */
    public function setNamespaceSeparator($namespaceSeparator)
    {
        $namespaceSeparator = (string) $namespaceSeparator;
        $this->triggerOptionEvent('namespace_separator', $namespaceSeparator);
        $this->namespaceSeparator = $namespaceSeparator;
        return $this;
    }

    /**
     * Get namespace separator
     *
     * @return string
     */
    public function getNamespaceSeparator()
    {
        return $this->namespaceSeparator;
    }

    /**
     * Set no atime
     *
     * @param  bool $noAtime
     * @return FilesystemOptions
     */
    public function setNoAtime($noAtime)
    {
        $noAtime = (bool) $noAtime;
        $this->triggerOptionEvent('no_atime', $noAtime);
        $this->noAtime = $noAtime;
        return $this;
    }

    /**
     * Get no atime
     *
     * @return bool
     */
    public function getNoAtime()
    {
        return $this->noAtime;
    }

    /**
     * Set no ctime
     *
     * @param  bool $noCtime
     * @return FilesystemOptions
     */
    public function setNoCtime($noCtime)
    {
        $noCtime = (bool) $noCtime;
        $this->triggerOptionEvent('no_ctime', $noCtime);
        $this->noCtime = $noCtime;
        return $this;
    }

    /**
     * Get no ctime
     *
     * @return bool
     */
    public function getNoCtime()
    {
        return $this->noCtime;
    }

    /**
     * Set the umask to create files and directories on unix systems
     *
     * Note: On multithreaded webservers it's better to explicit set file and dir permission.
     *
     * @param false|string|int $umask FALSE to disable umask or an octal number
     * @return FilesystemOptions
     * @see setFilePermission
     * @see setDirPermission
     * @link http://php.net/manual/function.umask.php
     * @link http://en.wikipedia.org/wiki/Umask
     */
    public function setUmask($umask)
    {
        if ($umask !== false) {
            if (is_string($umask)) {
                $umask = octdec($umask);
            } else {
                $umask = (int) $umask;
            }

            // validate
            if ($umask & 0700) {
                throw new Exception\InvalidArgumentException(
                    'Invalid umask: need permission to execute, read and write by owner'
                );
            }

            // normalize
            $umask = $umask & 0777;
        }

        if ($this->umask !== $umask) {
            $this->triggerOptionEvent('umask', $umask);
            $this->umask = $umask;
        }

        return $this;
    }

    /**
     * Get the umask to create files and directories on unix systems
     *
     * @return false|int
     */
    public function getUmask()
    {
        return $this->umask;
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\EventManager;

/**
 * Interface to automate setter injection for a SharedEventManagerInterface instance
 */
interface SharedEventManagerAwareInterface
{
    /**
     * Inject a SharedEventManager instance
     *
     * @param  SharedEventManagerInterface $sharedEventManager
     * @return SharedEventManagerAwareInterface
     */
    public function setSharedManager(SharedEventManagerInterface $sharedEventManager);

    /**
     * Get shared collections container
     *
     * @return SharedEventManagerInterface
     */
    public function getSharedManager();

    /**
     * Remove any shared collections
     *
     * @return void
     */
    public function unsetSharedManager();
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\EventManager;

use Traversable;
use Zend\Stdlib\CallbackHandler;

/**
 * Interface for messengers
 */
interface EventManagerInterface extends SharedEventManagerAwareInterface
{
    /**
     * Trigger an event
     *
     * Should allow handling the following scenarios:
     * - Passing Event object only
     * - Passing event name and Event object only
     * - Passing event name, target, and Event object
     * - Passing event name, target, and array|ArrayAccess of arguments
     * - Passing event name, target, array|ArrayAccess of arguments, and callback
     *
     * @param  string|EventInterface $event
     * @param  object|string $target
     * @param  array|object $argv
     * @param  null|callable $callback
     * @return ResponseCollection
     */
    public function trigger($event, $target = null, $argv = [], $callback = null);

    /**
     * Trigger an event until the given callback returns a boolean true
     *
     * Should allow handling the following scenarios:
     * - Passing Event object and callback only
     * - Passing event name, Event object, and callback only
     * - Passing event name, target, Event object, and callback
     * - Passing event name, target, array|ArrayAccess of arguments, and callback
     *
     * @param  string|EventInterface $event
     * @param  object|string $target
     * @param  array|object $argv
     * @param  callable $callback
     * @return ResponseCollection
     * @deprecated Please use trigger()
     */
    public function triggerUntil($event, $target, $argv = null, $callback = null);

    /**
     * Attach a listener to an event
     *
     * @param  string $event
     * @param  callable $callback
     * @param  int $priority Priority at which to register listener
     * @return CallbackHandler
     */
    public function attach($event, $callback = null, $priority = 1);

    /**
     * Detach an event listener
     *
     * @param  CallbackHandler|ListenerAggregateInterface $listener
     * @return bool
     */
    public function detach($listener);

    /**
     * Get a list of events for which this collection has listeners
     *
     * @return array
     */
    public function getEvents();

    /**
     * Retrieve a list of listeners registered to a given event
     *
     * @param  string $event
     * @return array|object
     */
    public function getListeners($event);

    /**
     * Clear all listeners for a given event
     *
     * @param  string $event
     * @return void
     */
    public function clearListeners($event);

    /**
     * Set the event class to utilize
     *
     * @param  string $class
     * @return EventManagerInterface
     */
    public function setEventClass($class);

    /**
     * Get the identifier(s) for this EventManager
     *
     * @return array
     */
    public function getIdentifiers();

    /**
     * Set the identifiers (overrides any currently set identifiers)
     *
     * @param string|int|array|Traversable $identifiers
     * @return EventManagerInterface
     */
    public function setIdentifiers($identifiers);

    /**
     * Add some identifier(s) (appends to any currently set identifiers)
     *
     * @param string|int|array|Traversable $identifiers
     * @return EventManagerInterface
     */
    public function addIdentifiers($identifiers);

    /**
     * Attach a listener aggregate
     *
     * @param  ListenerAggregateInterface $aggregate
     * @param  int $priority If provided, a suggested priority for the aggregate to use
     * @return mixed return value of {@link ListenerAggregateInterface::attach()}
     */
    public function attachAggregate(ListenerAggregateInterface $aggregate, $priority = 1);

    /**
     * Detach a listener aggregate
     *
     * @param  ListenerAggregateInterface $aggregate
     * @return mixed return value of {@link ListenerAggregateInterface::detach()}
     */
    public function detachAggregate(ListenerAggregateInterface $aggregate);
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\EventManager;

use ArrayAccess;

/**
 * Representation of an event
 */
interface EventInterface
{
    /**
     * Get event name
     *
     * @return string
     */
    public function getName();

    /**
     * Get target/context from which event was triggered
     *
     * @return null|string|object
     */
    public function getTarget();

    /**
     * Get parameters passed to the event
     *
     * @return array|ArrayAccess
     */
    public function getParams();

    /**
     * Get a single parameter by name
     *
     * @param  string $name
     * @param  mixed $default Default value to return if parameter does not exist
     * @return mixed
     */
    public function getParam($name, $default = null);

    /**
     * Set the event name
     *
     * @param  string $name
     * @return void
     */
    public function setName($name);

    /**
     * Set the event target/context
     *
     * @param  null|string|object $target
     * @return void
     */
    public function setTarget($target);

    /**
     * Set event parameters
     *
     * @param  string $params
     * @return void
     */
    public function setParams($params);

    /**
     * Set a single parameter by key
     *
     * @param  string $name
     * @param  mixed $value
     * @return void
     */
    public function setParam($name, $value);

    /**
     * Indicate whether or not the parent EventManagerInterface should stop propagating events
     *
     * @param  bool $flag
     * @return void
     */
    public function stopPropagation($flag = true);

    /**
     * Has this event indicated event propagation should stop?
     *
     * @return bool
     */
    public function propagationIsStopped();
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\EventManager;

use ArrayAccess;

/**
 * Representation of an event
 *
 * Encapsulates the target context and parameters passed, and provides some
 * behavior for interacting with the event manager.
 */
class Event implements EventInterface
{
    /**
     * @var string Event name
     */
    protected $name;

    /**
     * @var string|object The event target
     */
    protected $target;

    /**
     * @var array|ArrayAccess|object The event parameters
     */
    protected $params = [];

    /**
     * @var bool Whether or not to stop propagation
     */
    protected $stopPropagation = false;

    /**
     * Constructor
     *
     * Accept a target and its parameters.
     *
     * @param  string $name Event name
     * @param  string|object $target
     * @param  array|ArrayAccess $params
     */
    public function __construct($name = null, $target = null, $params = null)
    {
        if (null !== $name) {
            $this->setName($name);
        }

        if (null !== $target) {
            $this->setTarget($target);
        }

        if (null !== $params) {
            $this->setParams($params);
        }
    }

    /**
     * Get event name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the event target
     *
     * This may be either an object, or the name of a static method.
     *
     * @return string|object
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Set parameters
     *
     * Overwrites parameters
     *
     * @param  array|ArrayAccess|object $params
     * @return Event
     * @throws Exception\InvalidArgumentException
     */
    public function setParams($params)
    {
        if (!is_array($params) && !is_object($params)) {
            throw new Exception\InvalidArgumentException(
                sprintf('Event parameters must be an array or object; received "%s"', gettype($params))
            );
        }

        $this->params = $params;
        return $this;
    }

    /**
     * Get all parameters
     *
     * @return array|object|ArrayAccess
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Get an individual parameter
     *
     * If the parameter does not exist, the $default value will be returned.
     *
     * @param  string|int $name
     * @param  mixed $default
     * @return mixed
     */
    public function getParam($name, $default = null)
    {
        // Check in params that are arrays or implement array access
        if (is_array($this->params) || $this->params instanceof ArrayAccess) {
            if (!isset($this->params[$name])) {
                return $default;
            }

            return $this->params[$name];
        }

        // Check in normal objects
        if (!isset($this->params->{$name})) {
            return $default;
        }
        return $this->params->{$name};
    }

    /**
     * Set the event name
     *
     * @param  string $name
     * @return Event
     */
    public function setName($name)
    {
        $this->name = (string) $name;
        return $this;
    }

    /**
     * Set the event target/context
     *
     * @param  null|string|object $target
     * @return Event
     */
    public function setTarget($target)
    {
        $this->target = $target;
        return $this;
    }

    /**
     * Set an individual parameter to a value
     *
     * @param  string|int $name
     * @param  mixed $value
     * @return Event
     */
    public function setParam($name, $value)
    {
        if (is_array($this->params) || $this->params instanceof ArrayAccess) {
            // Arrays or objects implementing array access
            $this->params[$name] = $value;
        } else {
            // Objects
            $this->params->{$name} = $value;
        }
        return $this;
    }

    /**
     * Stop further event propagation
     *
     * @param  bool $flag
     * @return void
     */
    public function stopPropagation($flag = true)
    {
        $this->stopPropagation = (bool) $flag;
    }

    /**
     * Is propagation stopped?
     *
     * @return bool
     */
    public function propagationIsStopped()
    {
        return $this->stopPropagation;
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Storage;

use ArrayObject;
use Zend\EventManager\Event as BaseEvent;

class Event extends BaseEvent
{
    /**
     * Constructor
     *
     * Accept a storage adapter and its parameters.
     *
     * @param  string           $name Event name
     * @param  StorageInterface $storage
     * @param  ArrayObject      $params
     */
    public function __construct($name, StorageInterface $storage, ArrayObject $params)
    {
        parent::__construct($name, $storage, $params);
    }

    /**
     * Set the event target/context
     *
     * @param  StorageInterface $target
     * @return Event
     * @see    Zend\EventManager\Event::setTarget()
     */
    public function setTarget($target)
    {
        return $this->setStorage($target);
    }

    /**
     * Alias of setTarget
     *
     * @param  StorageInterface $storage
     * @return Event
     * @see    Zend\EventManager\Event::setTarget()
     */
    public function setStorage(StorageInterface $storage)
    {
        $this->target = $storage;
        return $this;
    }

    /**
     * Alias of getTarget
     *
     * @return StorageInterface
     */
    public function getStorage()
    {
        return $this->getTarget();
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\EventManager;

use ArrayAccess;
use ArrayObject;
use Traversable;
use Zend\Stdlib\CallbackHandler;
use Zend\Stdlib\PriorityQueue;

/**
 * Event manager: notification system
 *
 * Use the EventManager when you want to create a per-instance notification
 * system for your objects.
 */
class EventManager implements EventManagerInterface
{
    /**
     * Subscribed events and their listeners
     * @var array Array of PriorityQueue objects
     */
    protected $events = [];

    /**
     * @var string Class representing the event being emitted
     */
    protected $eventClass = 'Zend\EventManager\Event';

    /**
     * Identifiers, used to pull shared signals from SharedEventManagerInterface instance
     * @var array
     */
    protected $identifiers = [];

    /**
     * Shared event manager
     * @var false|null|SharedEventManagerInterface
     */
    protected $sharedManager = null;

    /**
     * Constructor
     *
     * Allows optionally specifying identifier(s) to use to pull signals from a
     * SharedEventManagerInterface.
     *
     * @param  null|string|int|array|Traversable $identifiers
     */
    public function __construct($identifiers = null)
    {
        $this->setIdentifiers($identifiers);
    }

    /**
     * Set the event class to utilize
     *
     * @param  string $class
     * @return EventManager
     */
    public function setEventClass($class)
    {
        $this->eventClass = $class;
        return $this;
    }

    /**
     * Set shared event manager
     *
     * @param SharedEventManagerInterface $sharedEventManager
     * @return EventManager
     */
    public function setSharedManager(SharedEventManagerInterface $sharedEventManager)
    {
        $this->sharedManager = $sharedEventManager;
        StaticEventManager::setInstance($sharedEventManager);
        return $this;
    }

    /**
     * Remove any shared event manager currently attached
     *
     * @return void
     */
    public function unsetSharedManager()
    {
        $this->sharedManager = false;
    }

    /**
     * Get shared event manager
     *
     * If one is not defined, but we have a static instance in
     * StaticEventManager, that one will be used and set in this instance.
     *
     * If none is available in the StaticEventManager, a boolean false is
     * returned.
     *
     * @return false|SharedEventManagerInterface
     */
    public function getSharedManager()
    {
        // "false" means "I do not want a shared manager; don't try and fetch one"
        if (false === $this->sharedManager
            || $this->sharedManager instanceof SharedEventManagerInterface
        ) {
            return $this->sharedManager;
        }

        if (!StaticEventManager::hasInstance()) {
            return false;
        }

        $this->sharedManager = StaticEventManager::getInstance();
        return $this->sharedManager;
    }

    /**
     * Get the identifier(s) for this EventManager
     *
     * @return array
     */
    public function getIdentifiers()
    {
        return $this->identifiers;
    }

    /**
     * Set the identifiers (overrides any currently set identifiers)
     *
     * @param string|int|array|Traversable $identifiers
     * @return EventManager Provides a fluent interface
     */
    public function setIdentifiers($identifiers)
    {
        if (is_array($identifiers) || $identifiers instanceof Traversable) {
            $this->identifiers = array_unique((array) $identifiers);
        } elseif ($identifiers !== null) {
            $this->identifiers = [$identifiers];
        }
        return $this;
    }

    /**
     * Add some identifier(s) (appends to any currently set identifiers)
     *
     * @param string|int|array|Traversable $identifiers
     * @return EventManager Provides a fluent interface
     */
    public function addIdentifiers($identifiers)
    {
        if (is_array($identifiers) || $identifiers instanceof Traversable) {
            $this->identifiers = array_unique(array_merge($this->identifiers, (array) $identifiers));
        } elseif ($identifiers !== null) {
            $this->identifiers = array_unique(array_merge($this->identifiers, [$identifiers]));
        }
        return $this;
    }

    /**
     * Trigger all listeners for a given event
     *
     * @param  string|EventInterface $event
     * @param  string|object     $target   Object calling emit, or symbol describing target (such as static method name)
     * @param  array|ArrayAccess $argv     Array of arguments; typically, should be associative
     * @param  null|callable     $callback Trigger listeners until return value of this callback evaluate to true
     * @return ResponseCollection All listener return values
     * @throws Exception\InvalidCallbackException
     */
    public function trigger($event, $target = null, $argv = [], $callback = null)
    {
        if ($event instanceof EventInterface) {
            $e        = $event;
            $event    = $e->getName();
            $callback = $target;
        } elseif ($target instanceof EventInterface) {
            $e = $target;
            $e->setName($event);
            $callback = $argv;
        } elseif ($argv instanceof EventInterface) {
            $e = $argv;
            $e->setName($event);
            $e->setTarget($target);
        } else {
            $e = new $this->eventClass();
            $e->setName($event);
            $e->setTarget($target);
            $e->setParams($argv);
        }

        if ($callback && !is_callable($callback)) {
            throw new Exception\InvalidCallbackException('Invalid callback provided');
        }

        // Initial value of stop propagation flag should be false
        $e->stopPropagation(false);

        return $this->triggerListeners($event, $e, $callback);
    }

    /**
     * Trigger listeners until return value of one causes a callback to
     * evaluate to true
     *
     * Triggers listeners until the provided callback evaluates the return
     * value of one as true, or until all listeners have been executed.
     *
     * @param  string|EventInterface $event
     * @param  string|object $target Object calling emit, or symbol describing target (such as static method name)
     * @param  array|ArrayAccess $argv Array of arguments; typically, should be associative
     * @param  callable $callback
     * @return ResponseCollection
     * @deprecated Please use trigger()
     * @throws Exception\InvalidCallbackException if invalid callable provided
     */
    public function triggerUntil($event, $target, $argv = null, $callback = null)
    {
        trigger_error(
            'This method is deprecated and will be removed in the future. Please use trigger() instead.',
            E_USER_DEPRECATED
        );
        return $this->trigger($event, $target, $argv, $callback);
    }

    /**
     * Attach a listener to an event
     *
     * The first argument is the event, and the next argument describes a
     * callback that will respond to that event. A CallbackHandler instance
     * describing the event listener combination will be returned.
     *
     * The last argument indicates a priority at which the event should be
     * executed. By default, this value is 1; however, you may set it for any
     * integer value. Higher values have higher priority (i.e., execute first).
     *
     * You can specify "*" for the event name. In such cases, the listener will
     * be triggered for every event.
     *
     * @param  string|array|ListenerAggregateInterface $event An event or array of event names. If a ListenerAggregateInterface, proxies to {@link attachAggregate()}.
     * @param  callable|int $callback If string $event provided, expects PHP callback; for a ListenerAggregateInterface $event, this will be the priority
     * @param  int $priority If provided, the priority at which to register the callable
     * @return CallbackHandler|mixed CallbackHandler if attaching callable (to allow later unsubscribe); mixed if attaching aggregate
     * @throws Exception\InvalidArgumentException
     */
    public function attach($event, $callback = null, $priority = 1)
    {
        // Proxy ListenerAggregateInterface arguments to attachAggregate()
        if ($event instanceof ListenerAggregateInterface) {
            return $this->attachAggregate($event, $callback);
        }

        // Null callback is invalid
        if (null === $callback) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s: expects a callback; none provided',
                __METHOD__
            ));
        }

        // Array of events should be registered individually, and return an array of all listeners
        if (is_array($event)) {
            $listeners = [];
            foreach ($event as $name) {
                $listeners[] = $this->attach($name, $callback, $priority);
            }
            return $listeners;
        }

        // If we don't have a priority queue for the event yet, create one
        if (empty($this->events[$event])) {
            $this->events[$event] = new PriorityQueue();
        }

        // Create a callback handler, setting the event and priority in its metadata
        $listener = new CallbackHandler($callback, ['event' => $event, 'priority' => $priority]);

        // Inject the callback handler into the queue
        $this->events[$event]->insert($listener, $priority);
        return $listener;
    }

    /**
     * Attach a listener aggregate
     *
     * Listener aggregates accept an EventManagerInterface instance, and call attach()
     * one or more times, typically to attach to multiple events using local
     * methods.
     *
     * @param  ListenerAggregateInterface $aggregate
     * @param  int $priority If provided, a suggested priority for the aggregate to use
     * @return mixed return value of {@link ListenerAggregateInterface::attach()}
     */
    public function attachAggregate(ListenerAggregateInterface $aggregate, $priority = 1)
    {
        return $aggregate->attach($this, $priority);
    }

    /**
     * Unsubscribe a listener from an event
     *
     * @param  CallbackHandler|ListenerAggregateInterface $listener
     * @return bool Returns true if event and listener found, and unsubscribed; returns false if either event or listener not found
     * @throws Exception\InvalidArgumentException if invalid listener provided
     */
    public function detach($listener)
    {
        if ($listener instanceof ListenerAggregateInterface) {
            return $this->detachAggregate($listener);
        }

        if (!$listener instanceof CallbackHandler) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s: expected a ListenerAggregateInterface or CallbackHandler; received "%s"',
                __METHOD__,
                (is_object($listener) ? get_class($listener) : gettype($listener))
            ));
        }

        $event = $listener->getMetadatum('event');
        if (!$event || empty($this->events[$event])) {
            return false;
        }
        $return = $this->events[$event]->remove($listener);
        if (!$return) {
            return false;
        }
        if (!count($this->events[$event])) {
            unset($this->events[$event]);
        }
        return true;
    }

    /**
     * Detach a listener aggregate
     *
     * Listener aggregates accept an EventManagerInterface instance, and call detach()
     * of all previously attached listeners.
     *
     * @param  ListenerAggregateInterface $aggregate
     * @return mixed return value of {@link ListenerAggregateInterface::detach()}
     */
    public function detachAggregate(ListenerAggregateInterface $aggregate)
    {
        return $aggregate->detach($this);
    }

    /**
     * Retrieve all registered events
     *
     * @return array
     */
    public function getEvents()
    {
        return array_keys($this->events);
    }

    /**
     * Retrieve all listeners for a given event
     *
     * @param  string $event
     * @return PriorityQueue
     */
    public function getListeners($event)
    {
        if (!array_key_exists($event, $this->events)) {
            return new PriorityQueue();
        }
        return $this->events[$event];
    }

    /**
     * Clear all listeners for a given event
     *
     * @param  string $event
     * @return void
     */
    public function clearListeners($event)
    {
        if (!empty($this->events[$event])) {
            unset($this->events[$event]);
        }
    }

    /**
     * Prepare arguments
     *
     * Use this method if you want to be able to modify arguments from within a
     * listener. It returns an ArrayObject of the arguments, which may then be
     * passed to trigger().
     *
     * @param  array $args
     * @return ArrayObject
     */
    public function prepareArgs(array $args)
    {
        return new ArrayObject($args);
    }

    /**
     * Trigger listeners
     *
     * Actual functionality for triggering listeners, to which trigger() delegate.
     *
     * @param  string           $event Event name
     * @param  EventInterface $e
     * @param  null|callable    $callback
     * @return ResponseCollection
     */
    protected function triggerListeners($event, EventInterface $e, $callback = null)
    {
        $responses = new ResponseCollection;
        $listeners = $this->getListeners($event);

        // Add shared/wildcard listeners to the list of listeners,
        // but don't modify the listeners object
        $sharedListeners         = $this->getSharedListeners($event);
        $sharedWildcardListeners = $this->getSharedListeners('*');
        $wildcardListeners       = $this->getListeners('*');
        if (count($sharedListeners) || count($sharedWildcardListeners) || count($wildcardListeners)) {
            $listeners = clone $listeners;

            // Shared listeners on this specific event
            $this->insertListeners($listeners, $sharedListeners);

            // Shared wildcard listeners
            $this->insertListeners($listeners, $sharedWildcardListeners);

            // Add wildcard listeners
            $this->insertListeners($listeners, $wildcardListeners);
        }

        foreach ($listeners as $listener) {
            $listenerCallback = $listener->getCallback();

            // Trigger the listener's callback, and push its result onto the
            // response collection
            $responses->push(call_user_func($listenerCallback, $e));

            // If the event was asked to stop propagating, do so
            if ($e->propagationIsStopped()) {
                $responses->setStopped(true);
                break;
            }

            // If the result causes our validation callback to return true,
            // stop propagation
            if ($callback && call_user_func($callback, $responses->last())) {
                $responses->setStopped(true);
                break;
            }
        }

        return $responses;
    }

    /**
     * Get list of all listeners attached to the shared event manager for
     * identifiers registered by this instance
     *
     * @param  string $event
     * @return array
     */
    protected function getSharedListeners($event)
    {
        if (!$sharedManager = $this->getSharedManager()) {
            return [];
        }

        $identifiers     = $this->getIdentifiers();
        //Add wildcard id to the search, if not already added
        if (!in_array('*', $identifiers)) {
            $identifiers[] = '*';
        }
        $sharedListeners = [];

        foreach ($identifiers as $id) {
            if (!$listeners = $sharedManager->getListeners($id, $event)) {
                continue;
            }

            if (!is_array($listeners) && !($listeners instanceof Traversable)) {
                continue;
            }

            foreach ($listeners as $listener) {
                if (!$listener instanceof CallbackHandler) {
                    continue;
                }
                $sharedListeners[] = $listener;
            }
        }

        return $sharedListeners;
    }

    /**
     * Add listeners to the master queue of listeners
     *
     * Used to inject shared listeners and wildcard listeners.
     *
     * @param  PriorityQueue $masterListeners
     * @param  array|Traversable $listeners
     * @return void
     */
    protected function insertListeners($masterListeners, $listeners)
    {
        foreach ($listeners as $listener) {
            $priority = $listener->getMetadatum('priority');
            if (null === $priority) {
                $priority = 1;
            } elseif (is_array($priority)) {
                // If we have an array, likely using PriorityQueue. Grab first
                // element of the array, as that's the actual priority.
                $priority = array_shift($priority);
            }
            $masterListeners->insert($listener, $priority);
        }
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\EventManager;

use SplStack;

/**
 * Collection of signal handler return values
 */
class ResponseCollection extends SplStack
{
    protected $stopped = false;

    /**
     * Did the last response provided trigger a short circuit of the stack?
     *
     * @return bool
     */
    public function stopped()
    {
        return $this->stopped;
    }

    /**
     * Mark the collection as stopped (or its opposite)
     *
     * @param  bool $flag
     * @return ResponseCollection
     */
    public function setStopped($flag)
    {
        $this->stopped = (bool) $flag;
        return $this;
    }

    /**
     * Convenient access to the first handler return value.
     *
     * @return mixed The first handler return value
     */
    public function first()
    {
        return parent::bottom();
    }

    /**
     * Convenient access to the last handler return value.
     *
     * If the collection is empty, returns null. Otherwise, returns value
     * returned by last handler.
     *
     * @return mixed The last handler return value
     */
    public function last()
    {
        if (count($this) === 0) {
            return;
        }
        return parent::top();
    }

    /**
     * Check if any of the responses match the given value.
     *
     * @param  mixed $value The value to look for among responses
     * @return bool
     */
    public function contains($value)
    {
        foreach ($this as $response) {
            if ($response === $value) {
                return true;
            }
        }
        return false;
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Stdlib;

use Countable;
use IteratorAggregate;
use Serializable;

/**
 * Re-usable, serializable priority queue implementation
 *
 * SplPriorityQueue acts as a heap; on iteration, each item is removed from the
 * queue. If you wish to re-use such a queue, you need to clone it first. This
 * makes for some interesting issues if you wish to delete items from the queue,
 * or, as already stated, iterate over it multiple times.
 *
 * This class aggregates items for the queue itself, but also composes an
 * "inner" iterator in the form of an SplPriorityQueue object for performing
 * the actual iteration.
 */
class PriorityQueue implements Countable, IteratorAggregate, Serializable
{
    public function __serialize() {$this->serialize();}
    public function __unserialize($data) {$this->unserialize($data);}
    const EXTR_DATA     = 0x00000001;
    const EXTR_PRIORITY = 0x00000002;
    const EXTR_BOTH     = 0x00000003;

    /**
     * Inner queue class to use for iteration
     * @var string
     */
    protected $queueClass = 'Zend\Stdlib\SplPriorityQueue';

    /**
     * Actual items aggregated in the priority queue. Each item is an array
     * with keys "data" and "priority".
     * @var array
     */
    protected $items      = [];

    /**
     * Inner queue object
     * @var SplPriorityQueue
     */
    protected $queue;

    /**
     * Insert an item into the queue
     *
     * Priority defaults to 1 (low priority) if none provided.
     *
     * @param  mixed $data
     * @param  int $priority
     * @return PriorityQueue
     */
    public function insert($data, $priority = 1)
    {
        $priority = (int) $priority;
        $this->items[] = [
            'data'     => $data,
            'priority' => $priority,
        ];
        $this->getQueue()->insert($data, $priority);
        return $this;
    }

    /**
     * Remove an item from the queue
     *
     * This is different than {@link extract()}; its purpose is to dequeue an
     * item.
     *
     * This operation is potentially expensive, as it requires
     * re-initialization and re-population of the inner queue.
     *
     * Note: this removes the first item matching the provided item found. If
     * the same item has been added multiple times, it will not remove other
     * instances.
     *
     * @param  mixed $datum
     * @return bool False if the item was not found, true otherwise.
     */
    public function remove($datum)
    {
        $found = false;
        foreach ($this->items as $key => $item) {
            if ($item['data'] === $datum) {
                $found = true;
                break;
            }
        }
        if ($found) {
            unset($this->items[$key]);
            $this->queue = null;

            if (!$this->isEmpty()) {
                $queue = $this->getQueue();
                foreach ($this->items as $item) {
                    $queue->insert($item['data'], $item['priority']);
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Is the queue empty?
     *
     * @return bool
     */
    public function isEmpty()
    {
        return (0 === $this->count());
    }

    /**
     * How many items are in the queue?
     *
     * @return int
     */
    #[\ReturnTypeWillChange]
    public function count()
    {
        return count($this->items);
    }

    /**
     * Peek at the top node in the queue, based on priority.
     *
     * @return mixed
     */
    public function top()
    {
        return $this->getIterator()->top();
    }

    /**
     * Extract a node from the inner queue and sift up
     *
     * @return mixed
     */
    public function extract()
    {
        return $this->getQueue()->extract();
    }

    /**
     * Retrieve the inner iterator
     *
     * SplPriorityQueue acts as a heap, which typically implies that as items
     * are iterated, they are also removed. This does not work for situations
     * where the queue may be iterated multiple times. As such, this class
     * aggregates the values, and also injects an SplPriorityQueue. This method
     * retrieves the inner queue object, and clones it for purposes of
     * iteration.
     *
     * @return SplPriorityQueue
     */
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        $queue = $this->getQueue();
        return clone $queue;
    }

    /**
     * Serialize the data structure
     *
     * @return string
     */
    public function serialize()
    {
        return serialize($this->items);
    }

    /**
     * Unserialize a string into a PriorityQueue object
     *
     * Serialization format is compatible with {@link Zend\Stdlib\SplPriorityQueue}
     *
     * @param  string $data
     * @return void
     */
    public function unserialize($data)
    {
        foreach (unserialize($data) as $item) {
            $this->insert($item['data'], $item['priority']);
        }
    }

    /**
     * Serialize to an array
     *
     * By default, returns only the item data, and in the order registered (not
     * sorted). You may provide one of the EXTR_* flags as an argument, allowing
     * the ability to return priorities or both data and priority.
     *
     * @param  int $flag
     * @return array
     */
    public function toArray($flag = self::EXTR_DATA)
    {
        switch ($flag) {
            case self::EXTR_BOTH:
                return $this->items;
            case self::EXTR_PRIORITY:
                return array_map(function ($item) {
                    return $item['priority'];
                }, $this->items);
            case self::EXTR_DATA:
            default:
                return array_map(function ($item) {
                    return $item['data'];
                }, $this->items);
        }
    }

    /**
     * Specify the internal queue class
     *
     * Please see {@link getIterator()} for details on the necessity of an
     * internal queue class. The class provided should extend SplPriorityQueue.
     *
     * @param  string $class
     * @return PriorityQueue
     */
    public function setInternalQueueClass($class)
    {
        $this->queueClass = (string) $class;
        return $this;
    }

    /**
     * Does the queue contain the given datum?
     *
     * @param  mixed $datum
     * @return bool
     */
    public function contains($datum)
    {
        foreach ($this->items as $item) {
            if ($item['data'] === $datum) {
                return true;
            }
        }
        return false;
    }

    /**
     * Does the queue have an item with the given priority?
     *
     * @param  int $priority
     * @return bool
     */
    public function hasPriority($priority)
    {
        foreach ($this->items as $item) {
            if ($item['priority'] === $priority) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get the inner priority queue instance
     *
     * @throws Exception\DomainException
     * @return SplPriorityQueue
     */
    protected function getQueue()
    {
        if (null === $this->queue) {
            $this->queue = new $this->queueClass();
            if (!$this->queue instanceof \SplPriorityQueue) {
                throw new Exception\DomainException(sprintf(
                    'PriorityQueue expects an internal queue of type SplPriorityQueue; received "%s"',
                    get_class($this->queue)
                ));
            }
        }
        return $this->queue;
    }

    /**
     * Add support for deep cloning
     *
     * @return void
     */
    public function __clone()
    {
        if (null !== $this->queue) {
            $this->queue = clone $this->queue;
        }
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\EventManager;

/**
 * Interface for allowing attachment of shared aggregate listeners.
 */
interface SharedEventAggregateAwareInterface
{
    /**
     * Attach a listener aggregate
     *
     * @param  SharedListenerAggregateInterface $aggregate
     * @param  int $priority If provided, a suggested priority for the aggregate to use
     * @return mixed return value of {@link SharedListenerAggregateInterface::attachShared()}
     */
    public function attachAggregate(SharedListenerAggregateInterface $aggregate, $priority = 1);

    /**
     * Detach a listener aggregate
     *
     * @param  SharedListenerAggregateInterface $aggregate
     * @return mixed return value of {@link SharedListenerAggregateInterface::detachShared()}
    */
    public function detachAggregate(SharedListenerAggregateInterface $aggregate);
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\EventManager;

use Zend\Stdlib\CallbackHandler;
use Zend\Stdlib\PriorityQueue;

/**
 * Interface for shared event listener collections
 */
interface SharedEventManagerInterface
{
    /**
     * Retrieve all listeners for a given identifier and event
     *
     * @param  string|int $id
     * @param  string|int $event
     * @return false|PriorityQueue
     */
    public function getListeners($id, $event);

    /**
     * Attach a listener to an event
     *
     * @param  string|array $id Identifier(s) for event emitting component(s)
     * @param  string $event
     * @param  callable $callback PHP Callback
     * @param  int $priority Priority at which listener should execute
     * @return CallbackHandler|array Either CallbackHandler or array of CallbackHandlers
     */
    public function attach($id, $event, $callback, $priority = 1);

    /**
     * Detach a listener from an event offered by a given resource
     *
     * @param  string|int $id
     * @param  CallbackHandler $listener
     * @return bool Returns true if event and listener found, and unsubscribed; returns false if either event or listener not found
     */
    public function detach($id, CallbackHandler $listener);

    /**
     * Retrieve all registered events for a given resource
     *
     * @param  string|int $id
     * @return array
     */
    public function getEvents($id);

    /**
     * Clear all listeners for a given identifier, optionally for a specific event
     *
     * @param  string|int $id
     * @param  null|string $event
     * @return bool
     */
    public function clearListeners($id, $event = null);
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\EventManager;

use Zend\Stdlib\CallbackHandler;
use Zend\Stdlib\PriorityQueue;

/**
 * Shared/contextual EventManager
 *
 * Allows attaching to EMs composed by other classes without having an instance first.
 * The assumption is that the SharedEventManager will be injected into EventManager
 * instances, and then queried for additional listeners when triggering an event.
 */
class SharedEventManager implements
    SharedEventAggregateAwareInterface,
    SharedEventManagerInterface
{
    /**
     * Identifiers with event connections
     * @var array
     */
    protected $identifiers = [];

    /**
     * Attach a listener to an event
     *
     * Allows attaching a callback to an event offered by one or more
     * identifying components. As an example, the following connects to the
     * "getAll" event of both an AbstractResource and EntityResource:
     *
     * <code>
     * $sharedEventManager = new SharedEventManager();
     * $sharedEventManager->attach(
     *     array('My\Resource\AbstractResource', 'My\Resource\EntityResource'),
     *     'getAll',
     *     function ($e) use ($cache) {
     *         if (!$id = $e->getParam('id', false)) {
     *             return;
     *         }
     *         if (!$data = $cache->load(get_class($resource) . '::getOne::' . $id )) {
     *             return;
     *         }
     *         return $data;
     *     }
     * );
     * </code>
     *
     * @param  string|array $id Identifier(s) for event emitting component(s)
     * @param  string $event
     * @param  callable $callback PHP Callback
     * @param  int $priority Priority at which listener should execute
     * @return CallbackHandler|array Either CallbackHandler or array of CallbackHandlers
     */
    public function attach($id, $event, $callback, $priority = 1)
    {
        $ids = (array) $id;
        $listeners = [];
        foreach ($ids as $id) {
            if (!array_key_exists($id, $this->identifiers)) {
                $this->identifiers[$id] = new EventManager($id);
            }
            $listeners[] = $this->identifiers[$id]->attach($event, $callback, $priority);
        }
        if (count($listeners) > 1) {
            return $listeners;
        }
        return $listeners[0];
    }

    /**
     * Attach a listener aggregate
     *
     * Listener aggregates accept an EventManagerInterface instance, and call attachShared()
     * one or more times, typically to attach to multiple events using local
     * methods.
     *
     * @param  SharedListenerAggregateInterface $aggregate
     * @param  int $priority If provided, a suggested priority for the aggregate to use
     * @return mixed return value of {@link ListenerAggregateInterface::attachShared()}
     */
    public function attachAggregate(SharedListenerAggregateInterface $aggregate, $priority = 1)
    {
        return $aggregate->attachShared($this, $priority);
    }

    /**
     * Detach a listener from an event offered by a given resource
     *
     * @param  string|int $id
     * @param  CallbackHandler $listener
     * @return bool Returns true if event and listener found, and unsubscribed; returns false if either event or listener not found
     */
    public function detach($id, CallbackHandler $listener)
    {
        if (!array_key_exists($id, $this->identifiers)) {
            return false;
        }
        return $this->identifiers[$id]->detach($listener);
    }

    /**
     * Detach a listener aggregate
     *
     * Listener aggregates accept a SharedEventManagerInterface instance, and call detachShared()
     * of all previously attached listeners.
     *
     * @param  SharedListenerAggregateInterface $aggregate
     * @return mixed return value of {@link SharedListenerAggregateInterface::detachShared()}
     */
    public function detachAggregate(SharedListenerAggregateInterface $aggregate)
    {
        return $aggregate->detachShared($this);
    }

    /**
     * Retrieve all registered events for a given resource
     *
     * @param  string|int $id
     * @return array
     */
    public function getEvents($id)
    {
        if (!array_key_exists($id, $this->identifiers)) {
            //Check if there are any id wildcards listeners
            if ('*' != $id && array_key_exists('*', $this->identifiers)) {
                return $this->identifiers['*']->getEvents();
            }
            return false;
        }
        return $this->identifiers[$id]->getEvents();
    }

    /**
     * Retrieve all listeners for a given identifier and event
     *
     * @param  string|int $id
     * @param  string|int $event
     * @return false|PriorityQueue
     */
    public function getListeners($id, $event)
    {
        if (!array_key_exists($id, $this->identifiers)) {
            return false;
        }
        return $this->identifiers[$id]->getListeners($event);
    }

    /**
     * Clear all listeners for a given identifier, optionally for a specific event
     *
     * @param  string|int $id
     * @param  null|string $event
     * @return bool
     */
    public function clearListeners($id, $event = null)
    {
        if (!array_key_exists($id, $this->identifiers)) {
            return false;
        }

        if (null === $event) {
            unset($this->identifiers[$id]);
            return true;
        }

        return $this->identifiers[$id]->clearListeners($event);
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\EventManager;

/**
 * Static version of EventManager
 */
class StaticEventManager extends SharedEventManager
{
    /**
     * @var SharedEventManagerInterface
     */
    protected static $instance;

    /**
     * Singleton
     */
    protected function __construct()
    {
    }

    /**
     * Singleton
     *
     * @return void
     */
    private function __clone()
    {
    }

    /**
     * Retrieve instance
     *
     * @return StaticEventManager
     */
    public static function getInstance()
    {
        if (null === static::$instance) {
            static::setInstance(new static());
        }
        return static::$instance;
    }

    /**
     * Set the singleton to a specific SharedEventManagerInterface instance
     *
     * @param SharedEventManagerInterface $instance
     * @return void
     */
    public static function setInstance(SharedEventManagerInterface $instance)
    {
        static::$instance = $instance;
    }

    /**
     * Is a singleton instance defined?
     *
     * @return bool
     */
    public static function hasInstance()
    {
        return (static::$instance instanceof SharedEventManagerInterface);
    }

    /**
     * Reset the singleton instance
     *
     * @return void
     */
    public static function resetInstance()
    {
        static::$instance = null;
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Stdlib;

use Serializable;

/**
 * Serializable version of SplPriorityQueue
 *
 * Also, provides predictable heap order for datums added with the same priority
 * (i.e., they will be emitted in the same order they are enqueued).
 */
class SplPriorityQueue extends \SplPriorityQueue implements Serializable
{
    public function __serialize() {$this->serialize();}
    public function __unserialize($data) {$this->unserialize($data);}
    /**
     * @var int Seed used to ensure queue order for items of the same priority
     */
    protected $serial = PHP_INT_MAX;

    /**
     * Insert a value with a given priority
     *
     * Utilizes {@var $serial} to ensure that values of equal priority are
     * emitted in the same order in which they are inserted.
     *
     * @param  mixed $datum
     * @param  mixed $priority
     * @return void
     */
     #[\ReturnTypeWillChange]
    public function insert($datum, $priority)
    {
        if (!is_array($priority)) {
            $priority = [$priority, $this->serial--];
        }
        parent::insert($datum, $priority);
    }

    /**
     * Serialize to an array
     *
     * Array will be priority => data pairs
     *
     * @return array
     */
    public function toArray()
    {
        $array = [];
        foreach (clone $this as $item) {
            $array[] = $item;
        }
        return $array;
    }

    /**
     * Serialize
     *
     * @return string
     */
    public function serialize()
    {
        $clone = clone $this;
        $clone->setExtractFlags(self::EXTR_BOTH);

        $data = [];
        foreach ($clone as $item) {
            $data[] = $item;
        }

        return serialize($data);
    }

    /**
     * Deserialize
     *
     * @param  string $data
     * @return void
     */
    public function unserialize($data)
    {
        foreach (unserialize($data) as $item) {
            $this->insert($item['data'], $item['priority']);
        }
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Storage;

use Zend\Cache\Exception;
use Zend\ServiceManager\AbstractPluginManager;

/**
 * Plugin manager implementation for cache plugins
 *
 * Enforces that plugins retrieved are instances of
 * Plugin\PluginInterface. Additionally, it registers a number of default
 * plugins available.
 */
class PluginManager extends AbstractPluginManager
{
    /**
     * Default set of plugins
     *
     * @var array
     */
    protected $invokableClasses = [
        'clearexpiredbyfactor' => 'Zend\Cache\Storage\Plugin\ClearExpiredByFactor',
        'exceptionhandler'     => 'Zend\Cache\Storage\Plugin\ExceptionHandler',
        'ignoreuserabort'      => 'Zend\Cache\Storage\Plugin\IgnoreUserAbort',
        'optimizebyfactor'     => 'Zend\Cache\Storage\Plugin\OptimizeByFactor',
        'serializer'           => 'Zend\Cache\Storage\Plugin\Serializer',
    ];

    /**
     * Do not share by default
     *
     * @var array
     */
    protected $shareByDefault = false;

    /**
     * Validate the plugin
     *
     * Checks that the plugin loaded is an instance of Plugin\PluginInterface.
     *
     * @param  mixed $plugin
     * @return void
     * @throws Exception\RuntimeException if invalid
     */
    public function validatePlugin($plugin)
    {
        if ($plugin instanceof Plugin\PluginInterface) {
            // we're okay
            return;
        }

        throw new Exception\RuntimeException(sprintf(
            'Plugin of type %s is invalid; must implement %s\Plugin\PluginInterface',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
            __NAMESPACE__
        ));
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\EventManager;

/**
 * Interface for self-registering event listeners.
 *
 * Classes implementing this interface may be registered by name or instance
 * with an EventManager, without an event name. The {@link attach()} method will
 * then be called with the current EventManager instance, allowing the class to
 * wire up one or more listeners.
 */
interface ListenerAggregateInterface
{
    /**
     * Attach one or more listeners
     *
     * Implementors may add an optional $priority argument; the EventManager
     * implementation will pass this to the aggregate.
     *
     * @param EventManagerInterface $events
     *
     * @return void
     */
    public function attach(EventManagerInterface $events);

    /**
     * Detach all previously attached listeners
     *
     * @param EventManagerInterface $events
     *
     * @return void
     */
    public function detach(EventManagerInterface $events);
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\EventManager;

/**
 * Abstract aggregate listener
 */
abstract class AbstractListenerAggregate implements ListenerAggregateInterface
{
    /**
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = [];

    /**
     * {@inheritDoc}
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $callback) {
            if ($events->detach($callback)) {
                unset($this->listeners[$index]);
            }
        }
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Storage\Plugin;

use Zend\EventManager\ListenerAggregateInterface;

interface PluginInterface extends ListenerAggregateInterface
{
    /**
     * Set options
     *
     * @param  PluginOptions $options
     * @return PluginInterface
     */
    public function setOptions(PluginOptions $options);

    /**
     * Get options
     *
     * @return PluginOptions
     */
    public function getOptions();
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Storage\Plugin;

use Zend\EventManager\AbstractListenerAggregate;

abstract class AbstractPlugin extends AbstractListenerAggregate implements PluginInterface
{
    /**
     * @var PluginOptions
     */
    protected $options;

    /**
     * Set pattern options
     *
     * @param  PluginOptions $options
     * @return AbstractPlugin
     */
    public function setOptions(PluginOptions $options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Get all pattern options
     *
     * @return PluginOptions
     */
    public function getOptions()
    {
        if (null === $this->options) {
            $this->setOptions(new PluginOptions());
        }
        return $this->options;
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Storage\Plugin;

use stdClass;
use Zend\Cache\Storage\Capabilities;
use Zend\Cache\Storage\Event;
use Zend\Cache\Storage\PostEvent;
use Zend\EventManager\EventManagerInterface;

class Serializer extends AbstractPlugin
{
    /**
     * @var array
     */
    protected $capabilities = [];

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        // The higher the priority the sooner the plugin will be called on pre events
        // but the later it will be called on post events.
        $prePriority  = $priority;
        $postPriority = -$priority;

        // read
        $this->listeners[] = $events->attach('getItem.post', [$this, 'onReadItemPost'], $postPriority);
        $this->listeners[] = $events->attach('getItems.post', [$this, 'onReadItemsPost'], $postPriority);

        // write
        $this->listeners[] = $events->attach('setItem.pre', [$this, 'onWriteItemPre'], $prePriority);
        $this->listeners[] = $events->attach('setItems.pre', [$this, 'onWriteItemsPre'], $prePriority);

        $this->listeners[] = $events->attach('addItem.pre', [$this, 'onWriteItemPre'], $prePriority);
        $this->listeners[] = $events->attach('addItems.pre', [$this, 'onWriteItemsPre'], $prePriority);

        $this->listeners[] = $events->attach('replaceItem.pre', [$this, 'onWriteItemPre'], $prePriority);
        $this->listeners[] = $events->attach('replaceItems.pre', [$this, 'onWriteItemsPre'], $prePriority);

        $this->listeners[] = $events->attach('checkAndSetItem.pre', [$this, 'onWriteItemPre'], $prePriority);

        // increment / decrement item(s)
        $this->listeners[] = $events->attach('incrementItem.pre', [$this, 'onIncrementItemPre'], $prePriority);
        $this->listeners[] = $events->attach('incrementItems.pre', [$this, 'onIncrementItemsPre'], $prePriority);

        $this->listeners[] = $events->attach('decrementItem.pre', [$this, 'onDecrementItemPre'], $prePriority);
        $this->listeners[] = $events->attach('decrementItems.pre', [$this, 'onDecrementItemsPre'], $prePriority);

        // overwrite capabilities
        $this->listeners[] = $events->attach('getCapabilities.post', [$this, 'onGetCapabilitiesPost'], $postPriority);
    }

    /**
     * On read item post
     *
     * @param  PostEvent $event
     * @return void
     */
    public function onReadItemPost(PostEvent $event)
    {
        $serializer = $this->getOptions()->getSerializer();
        $result     = $event->getResult();
        $result     = $serializer->unserialize($result);
        $event->setResult($result);
    }

    /**
     * On read items post
     *
     * @param  PostEvent $event
     * @return void
     */
    public function onReadItemsPost(PostEvent $event)
    {
        $serializer = $this->getOptions()->getSerializer();
        $result     = $event->getResult();
        foreach ($result as &$value) {
            $value = $serializer->unserialize($value);
        }
        $event->setResult($result);
    }

    /**
     * On write item pre
     *
     * @param  Event $event
     * @return void
     */
    public function onWriteItemPre(Event $event)
    {
        $serializer = $this->getOptions()->getSerializer();
        $params     = $event->getParams();
        $params['value'] = $serializer->serialize($params['value']);
    }

    /**
     * On write items pre
     *
     * @param  Event $event
     * @return void
     */
    public function onWriteItemsPre(Event $event)
    {
        $serializer = $this->getOptions()->getSerializer();
        $params     = $event->getParams();
        foreach ($params['keyValuePairs'] as &$value) {
            $value = $serializer->serialize($value);
        }
    }

    /**
     * On increment item pre
     *
     * @param  Event $event
     * @return mixed
     */
    public function onIncrementItemPre(Event $event)
    {
        $storage  = $event->getTarget();
        $params   = $event->getParams();
        $casToken = null;
        $success  = null;
        $oldValue = $storage->getItem($params['key'], $success, $casToken);
        $newValue = $oldValue + $params['value'];

        if ($success) {
            $storage->checkAndSetItem($casToken, $params['key'], $oldValue + $params['value']);
            $result = $newValue;
        } else {
            $result = false;
        }

        $event->stopPropagation(true);
        return $result;
    }

    /**
     * On increment items pre
     *
     * @param  Event $event
     * @return mixed
     */
    public function onIncrementItemsPre(Event $event)
    {
        $storage       = $event->getTarget();
        $params        = $event->getParams();
        $keyValuePairs = $storage->getItems(array_keys($params['keyValuePairs']));
        foreach ($params['keyValuePairs'] as $key => & $value) {
            if (isset($keyValuePairs[$key])) {
                $keyValuePairs[$key]+= $value;
            } else {
                $keyValuePairs[$key] = $value;
            }
        }

        $failedKeys = $storage->setItems($keyValuePairs);
        foreach ($failedKeys as $failedKey) {
            unset($keyValuePairs[$failedKey]);
        }

        $event->stopPropagation(true);
        return $keyValuePairs;
    }

    /**
     * On decrement item pre
     *
     * @param  Event $event
     * @return mixed
     */
    public function onDecrementItemPre(Event $event)
    {
        $storage  = $event->getTarget();
        $params   = $event->getParams();
        $success  = null;
        $casToken = null;
        $oldValue = $storage->getItem($params['key'], $success, $casToken);
        $newValue = $oldValue - $params['value'];

        if ($success) {
            $storage->checkAndSetItem($casToken, $params['key'], $oldValue + $params['value']);
            $result = $newValue;
        } else {
            $result = false;
        }

        $event->stopPropagation(true);
        return $result;
    }

    /**
     * On decrement items pre
     *
     * @param  Event $event
     * @return mixed
     */
    public function onDecrementItemsPre(Event $event)
    {
        $storage       = $event->getTarget();
        $params        = $event->getParams();
        $keyValuePairs = $storage->getItems(array_keys($params['keyValuePairs']));
        foreach ($params['keyValuePairs'] as $key => &$value) {
            if (isset($keyValuePairs[$key])) {
                $keyValuePairs[$key]-= $value;
            } else {
                $keyValuePairs[$key] = -$value;
            }
        }

        $failedKeys = $storage->setItems($keyValuePairs);
        foreach ($failedKeys as $failedKey) {
            unset($keyValuePairs[$failedKey]);
        }

        $event->stopPropagation(true);
        return $keyValuePairs;
    }

    /**
     * On get capabilities
     *
     * @param  PostEvent $event
     * @return void
     */
    public function onGetCapabilitiesPost(PostEvent $event)
    {
        $baseCapabilities = $event->getResult();
        $index = spl_object_hash($baseCapabilities);

        if (!isset($this->capabilities[$index])) {
            $this->capabilities[$index] = new Capabilities(
                $baseCapabilities->getAdapter(),
                new stdClass(), // marker
                ['supportedDatatypes' => [
                    'NULL'     => true,
                    'boolean'  => true,
                    'integer'  => true,
                    'double'   => true,
                    'string'   => true,
                    'array'    => true,
                    'object'   => 'object',
                    'resource' => false,
                ]],
                $baseCapabilities
            );
        }

        $event->setResult($this->capabilities[$index]);
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Storage\Plugin;

use Zend\Cache\Exception;
use Zend\Serializer\Adapter\AdapterInterface as SerializerAdapter;
use Zend\Serializer\Serializer as SerializerFactory;
use Zend\Stdlib\AbstractOptions;

class PluginOptions extends AbstractOptions
{
    /**
     * Used by:
     * - ClearByFactor
     * @var int
     */
    protected $clearingFactor = 0;

    /**
     * Used by:
     * - ExceptionHandler
     * @var null|callable
     */
    protected $exceptionCallback;

    /**
     * Used by:
     * - IgnoreUserAbort
     * @var bool
     */
    protected $exitOnAbort = true;

    /**
     * Used by:
     * - OptimizeByFactor
     * @var int
     */
    protected $optimizingFactor = 0;

    /**
     * Used by:
     * - Serializer
     * @var string|SerializerAdapter
     */
    protected $serializer;

    /**
     * Used by:
     * - Serializer
     * @var array
     */
    protected $serializerOptions = [];

    /**
     * Used by:
     * - ExceptionHandler
     * @var bool
     */
    protected $throwExceptions = true;

    /**
     * Set automatic clearing factor
     *
     * Used by:
     * - ClearExpiredByFactor
     *
     * @param  int $clearingFactor
     * @return PluginOptions
     */
    public function setClearingFactor($clearingFactor)
    {
        $this->clearingFactor = $this->normalizeFactor($clearingFactor);
        return $this;
    }

    /**
     * Get automatic clearing factor
     *
     * Used by:
     * - ClearExpiredByFactor
     *
     * @return int
     */
    public function getClearingFactor()
    {
        return $this->clearingFactor;
    }

    /**
     * Set callback to call on intercepted exception
     *
     * Used by:
     * - ExceptionHandler
     *
     * @param  null|callable $exceptionCallback
     * @throws Exception\InvalidArgumentException
     * @return PluginOptions
     */
    public function setExceptionCallback($exceptionCallback)
    {
        if ($exceptionCallback !== null && !is_callable($exceptionCallback, true)) {
            throw new Exception\InvalidArgumentException('Not a valid callback');
        }
        $this->exceptionCallback = $exceptionCallback;
        return $this;
    }

    /**
     * Get callback to call on intercepted exception
     *
     * Used by:
     * - ExceptionHandler
     *
     * @return null|callable
     */
    public function getExceptionCallback()
    {
        return $this->exceptionCallback;
    }

    /**
     * Exit if connection aborted and ignore_user_abort is disabled.
     *
     * @param  bool $exitOnAbort
     * @return PluginOptions
     */
    public function setExitOnAbort($exitOnAbort)
    {
        $this->exitOnAbort = (bool) $exitOnAbort;
        return $this;
    }

    /**
     * Exit if connection aborted and ignore_user_abort is disabled.
     *
     * @return bool
     */
    public function getExitOnAbort()
    {
        return $this->exitOnAbort;
    }

    /**
     * Set automatic optimizing factor
     *
     * Used by:
     * - OptimizeByFactor
     *
     * @param  int $optimizingFactor
     * @return PluginOptions
     */
    public function setOptimizingFactor($optimizingFactor)
    {
        $this->optimizingFactor = $this->normalizeFactor($optimizingFactor);
        return $this;
    }

    /**
     * Set automatic optimizing factor
     *
     * Used by:
     * - OptimizeByFactor
     *
     * @return int
     */
    public function getOptimizingFactor()
    {
        return $this->optimizingFactor;
    }

    /**
     * Set serializer
     *
     * Used by:
     * - Serializer
     *
     * @param  string|SerializerAdapter $serializer
     * @throws Exception\InvalidArgumentException
     * @return self
     */
    public function setSerializer($serializer)
    {
        if (!is_string($serializer) && !$serializer instanceof SerializerAdapter) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects either a string serializer name or Zend\Serializer\Adapter\AdapterInterface instance; '
                . 'received "%s"',
                __METHOD__,
                (is_object($serializer) ? get_class($serializer) : gettype($serializer))
            ));
        }
        $this->serializer = $serializer;
        return $this;
    }

    /**
     * Get serializer
     *
     * Used by:
     * - Serializer
     *
     * @return SerializerAdapter
     */
    public function getSerializer()
    {
        if (!$this->serializer instanceof SerializerAdapter) {
            // use default serializer
            if (!$this->serializer) {
                $this->setSerializer(SerializerFactory::getDefaultAdapter());
            // instantiate by class name + serializer_options
            } else {
                $options = $this->getSerializerOptions();
                $this->setSerializer(SerializerFactory::factory($this->serializer, $options));
            }
        }
        return $this->serializer;
    }

    /**
     * Set configuration options for instantiating a serializer adapter
     *
     * Used by:
     * - Serializer
     *
     * @param  mixed $serializerOptions
     * @return PluginOptions
     */
    public function setSerializerOptions($serializerOptions)
    {
        $this->serializerOptions = $serializerOptions;
        return $this;
    }

    /**
     * Get configuration options for instantiating a serializer adapter
     *
     * Used by:
     * - Serializer
     *
     * @return array
     */
    public function getSerializerOptions()
    {
        return $this->serializerOptions;
    }

    /**
     * Set flag indicating we should re-throw exceptions
     *
     * Used by:
     * - ExceptionHandler
     *
     * @param  bool $throwExceptions
     * @return PluginOptions
     */
    public function setThrowExceptions($throwExceptions)
    {
        $this->throwExceptions = (bool) $throwExceptions;
        return $this;
    }

    /**
     * Should we re-throw exceptions?
     *
     * Used by:
     * - ExceptionHandler
     *
     * @return bool
     */
    public function getThrowExceptions()
    {
        return $this->throwExceptions;
    }

    /**
     * Normalize a factor
     *
     * Cast to int and ensure we have a value greater than zero.
     *
     * @param  int $factor
     * @return int
     * @throws Exception\InvalidArgumentException
     */
    protected function normalizeFactor($factor)
    {
        $factor = (int) $factor;
        if ($factor < 0) {
            throw new Exception\InvalidArgumentException(
                "Invalid factor '{$factor}': must be greater or equal 0"
            );
        }
        return $factor;
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Stdlib;

use ReflectionClass;

/**
 * CallbackHandler
 *
 * A handler for an event, event, filterchain, etc. Abstracts PHP callbacks,
 * primarily to allow for lazy-loading and ensuring availability of default
 * arguments (currying).
 */
class CallbackHandler
{
    /**
     * @var string|array|callable PHP callback to invoke
     */
    protected $callback;

    /**
     * Callback metadata, if any
     * @var array
     */
    protected $metadata;

    /**
     * Constructor
     *
     * @param  string|array|object|callable $callback PHP callback
     * @param  array                        $metadata  Callback metadata
     */
    public function __construct($callback, array $metadata = [])
    {
        $this->metadata  = $metadata;
        $this->registerCallback($callback);
    }

    /**
     * Registers the callback provided in the constructor
     *
     * @param  callable $callback
     * @throws Exception\InvalidCallbackException
     * @return void
     */
    protected function registerCallback($callback)
    {
        if (!is_callable($callback)) {
            throw new Exception\InvalidCallbackException('Invalid callback provided; not callable');
        }

        $this->callback = $callback;
    }

    /**
     * Retrieve registered callback
     *
     * @return callable
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * Invoke handler
     *
     * @param  array $args Arguments to pass to callback
     * @return mixed
     */
    public function call(array $args = [])
    {
        $callback = $this->getCallback();
        $argCount = count($args);

        if (is_string($callback)) {
            $result = $this->validateStringCallbackFor54($callback);

            if ($result !== true && $argCount <= 3) {
                $callback       = $result;
                // Minor performance tweak, if the callback gets called more
                // than once
                $this->callback = $result;
            }
        }

        // Minor performance tweak; use call_user_func() until > 3 arguments
        // reached
        switch ($argCount) {
            case 0:
                return $callback();
            case 1:
                return $callback(array_shift($args));
            case 2:
                $arg1 = array_shift($args);
                $arg2 = array_shift($args);
                return $callback($arg1, $arg2);
            case 3:
                $arg1 = array_shift($args);
                $arg2 = array_shift($args);
                $arg3 = array_shift($args);
                return $callback($arg1, $arg2, $arg3);
            default:
                return call_user_func_array($callback, $args);
        }
    }

    /**
     * Invoke as functor
     *
     * @return mixed
     */
    public function __invoke()
    {
        return $this->call(func_get_args());
    }

    /**
     * Get all callback metadata
     *
     * @return array
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * Retrieve a single metadatum
     *
     * @param  string $name
     * @return mixed
     */
    public function getMetadatum($name)
    {
        if (array_key_exists($name, $this->metadata)) {
            return $this->metadata[$name];
        }
        return;
    }

    /**
     * Validate a static method call
     *
     *
     * @param  string $callback
     * @return true|array
     * @throws Exception\InvalidCallbackException if invalid
     */
    protected function validateStringCallbackFor54($callback)
    {
        if (!strstr($callback, '::')) {
            return true;
        }

        list($class, $method) = explode('::', $callback, 2);

        if (!class_exists($class)) {
            throw new Exception\InvalidCallbackException(sprintf(
                'Static method call "%s" refers to a class that does not exist',
                $callback
            ));
        }

        $r = new ReflectionClass($class);
        if (!$r->hasMethod($method)) {
            throw new Exception\InvalidCallbackException(sprintf(
                'Static method call "%s" refers to a method that does not exist',
                $callback
            ));
        }
        $m = $r->getMethod($method);
        if (!$m->isStatic()) {
            throw new Exception\InvalidCallbackException(sprintf(
                'Static method call "%s" refers to a method that is not static',
                $callback
            ));
        }

        // returning a non boolean value may not be nice for a validate method,
        // but that allows the usage of a static string callback without using
        // the call_user_func function.
        return [$class, $method];
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Session;

use Zend\EventManager\EventManagerInterface;
use Zend\Session\Config\ConfigInterface as Config;
use Zend\Session\SaveHandler\SaveHandlerInterface as SaveHandler;
use Zend\Session\Storage\StorageInterface as Storage;

/**
 * Session manager interface
 */
interface ManagerInterface
{
    public function setConfig(Config $config);
    public function getConfig();

    public function setStorage(Storage $storage);
    public function getStorage();

    public function setSaveHandler(SaveHandler $saveHandler);
    public function getSaveHandler();

    public function sessionExists();
    public function start();
    public function destroy();
    public function writeClose();

    public function setName($name);
    public function getName();

    public function setId($id);
    public function getId();
    public function regenerateId();

    public function rememberMe($ttl = null);
    public function forgetMe();
    public function expireSessionCookie();

    public function setValidatorChain(EventManagerInterface $chain);
    public function getValidatorChain();
    public function isValid();
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Session;

use Zend\Session\Config\ConfigInterface as Config;
use Zend\Session\ManagerInterface as Manager;
use Zend\Session\SaveHandler\SaveHandlerInterface as SaveHandler;
use Zend\Session\Storage\StorageInterface as Storage;

/**
 * Base ManagerInterface implementation
 *
 * Defines common constructor logic and getters for Storage and Configuration
 */
abstract class AbstractManager implements Manager
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * Default configuration class to use when no configuration provided
     * @var string
     */
    protected $defaultConfigClass = 'Zend\Session\Config\SessionConfig';

    /**
     * @var Storage
     */
    protected $storage;

    /**
     * Default storage class to use when no storage provided
     * @var string
     */
    protected $defaultStorageClass = 'Zend\Session\Storage\SessionArrayStorage';

    /**
     * @var SaveHandler
     */
    protected $saveHandler;

    /**
     * @var array
     */
    protected $validators;

    /**
     * Constructor
     *
     * @param  Config|null      $config
     * @param  Storage|null     $storage
     * @param  SaveHandler|null $saveHandler
     * @param  array            $validators
     * @throws Exception\RuntimeException
     */
    public function __construct(
        ?Config $config = null,
        ?Storage $storage = null,
        ?SaveHandler $saveHandler = null,
        array $validators = array()
    ) {
        // init config
        if ($config === null) {
            if (!class_exists($this->defaultConfigClass)) {
                throw new Exception\RuntimeException(sprintf(
                    'Unable to locate config class "%s"; class does not exist',
                    $this->defaultConfigClass
                ));
            }

            $config = new $this->defaultConfigClass();

            if (!$config instanceof Config) {
                throw new Exception\RuntimeException(sprintf(
                    'Default config class %s is invalid; must implement %s\Config\ConfigInterface',
                    $this->defaultConfigClass,
                    __NAMESPACE__
                ));
            }
        }

        $this->config = $config;

        // init storage
        if ($storage === null) {
            if (!class_exists($this->defaultStorageClass)) {
                throw new Exception\RuntimeException(sprintf(
                    'Unable to locate storage class "%s"; class does not exist',
                    $this->defaultStorageClass
                ));
            }

            $storage = new $this->defaultStorageClass();

            if (!$storage instanceof Storage) {
                throw new Exception\RuntimeException(sprintf(
                    'Default storage class %s is invalid; must implement %s\Storage\StorageInterface',
                    $this->defaultConfigClass,
                    __NAMESPACE__
                ));
            }
        }

        $this->storage = $storage;

        // save handler
        if ($saveHandler !== null) {
            $this->saveHandler = $saveHandler;
        }

        $this->validators = $validators;
    }

    /**
     * Set configuration object
     *
     * @param  Config $config
     * @return AbstractManager
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Retrieve configuration object
     *
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Set session storage object
     *
     * @param  Storage $storage
     * @return AbstractManager
     */
    public function setStorage(Storage $storage)
    {
        $this->storage = $storage;
        return $this;
    }

    /**
     * Retrieve storage object
     *
     * @return Storage
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * Set session save handler object
     *
     * @param  SaveHandler $saveHandler
     * @return AbstractManager
     */
    public function setSaveHandler(SaveHandler $saveHandler)
    {
        $this->saveHandler = $saveHandler;
        return $this;
    }

    /**
     * Get SaveHandler Object
     *
     * @return SaveHandler
     */
    public function getSaveHandler()
    {
        return $this->saveHandler;
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Session;

use Zend\EventManager\EventManagerInterface;
use Zend\Stdlib\ArrayUtils;

/**
 * Session ManagerInterface implementation utilizing ext/session
 */
class SessionManager extends AbstractManager
{
    /**
     * Default options when a call to {@link destroy()} is made
     * - send_expire_cookie: whether or not to send a cookie expiring the current session cookie
     * - clear_storage: whether or not to empty the storage object of any stored values
     * @var array
     */
    protected $defaultDestroyOptions = array(
        'send_expire_cookie' => true,
        'clear_storage'      => false,
    );

    /**
     * @var string value returned by session_name()
     */
    protected $name;

    /**
     * @var EventManagerInterface Validation chain to determine if session is valid
     */
    protected $validatorChain;

    /**
     * Constructor
     *
     * @param  Config\ConfigInterface|null           $config
     * @param  Storage\StorageInterface|null         $storage
     * @param  SaveHandler\SaveHandlerInterface|null $saveHandler
     * @param  array                                 $validators
     * @throws Exception\RuntimeException
     */
    public function __construct(
        ?Config\ConfigInterface $config = null,
        ?Storage\StorageInterface $storage = null,
        ?SaveHandler\SaveHandlerInterface $saveHandler = null,
        array $validators = array()
    ) {
        parent::__construct($config, $storage, $saveHandler, $validators);
        register_shutdown_function(array($this, 'writeClose'));
    }

    /**
     * Does a session exist and is it currently active?
     *
     * @return bool
     */
    public function sessionExists()
    {
        $sid = false;
        if ($sid !== false && $this->getId()) {
            return true;
        }
        if (headers_sent()) {
            return true;
        }
        return false;
    }

    /**
     * Start session
     *
     * if No session currently exists, attempt to start it. Calls
     * {@link isValid()} once session_start() is called, and raises an
     * exception if validation fails.
     *
     * @param bool $preserveStorage        If set to true, current session storage will not be overwritten by the
     *                                     contents of $_SESSION.
     * @return void
     * @throws Exception\RuntimeException
     */
    public function start($preserveStorage = false)
    {
        if ($this->sessionExists()) {
            return;
        }

        $saveHandler = $this->getSaveHandler();
        if ($saveHandler instanceof SaveHandler\SaveHandlerInterface) {
            // register the session handler with ext/session
            $this->registerSaveHandler($saveHandler);
        }

        $oldSessionData = array();
        if (isset($_SESSION)) {
            $oldSessionData = $_SESSION;
        }

        @session_start();

        if ($oldSessionData instanceof \Traversable
            || (! empty($oldSessionData) && is_array($oldSessionData))
        ) {
            $_SESSION = ArrayUtils::merge($oldSessionData, $_SESSION, true);
        }

        $storage = $this->getStorage();

        // Since session is starting, we need to potentially repopulate our
        // session storage
        if ($storage instanceof Storage\SessionStorage && $_SESSION !== $storage) {
            if (!$preserveStorage) {
                $storage->fromArray($_SESSION);
            }
            $_SESSION = $storage;
        } elseif ($storage instanceof Storage\StorageInitializationInterface) {
            $storage->init($_SESSION);
        }

        $this->initializeValidatorChain();

        if (!$this->isValid()) {
            throw new Exception\RuntimeException('Session validation failed');
        }
    }

    /**
     * Create validators, insert reference value and add them to the validator chain
     */
    protected function initializeValidatorChain()
    {
        $validatorChain  = $this->getValidatorChain();
        $validatorValues = $this->getStorage()->getMetadata('_VALID');

        foreach ($this->validators as $validator) {
            // Ignore validators which are already present in Storage
            if (is_array($validatorValues) && array_key_exists($validator, $validatorValues)) {
                continue;
            }

            $validator = new $validator(null);
            $validatorChain->attach('session.validate', array($validator, 'isValid'));
        }
    }

    /**
     * Destroy/end a session
     *
     * @param  array $options See {@link $defaultDestroyOptions}
     * @return void
     */
    public function destroy(?array $options = null)
    {
        if (!$this->sessionExists()) {
            return;
        }

        if (null === $options) {
            $options = $this->defaultDestroyOptions;
        } else {
            $options = array_merge($this->defaultDestroyOptions, $options);
        }

        session_destroy();
        if ($options['send_expire_cookie']) {
            $this->expireSessionCookie();
        }

        if ($options['clear_storage']) {
            $this->getStorage()->clear();
        }
    }

    /**
     * Write session to save handler and close
     *
     * Once done, the Storage object will be marked as isImmutable.
     *
     * @return void
     */
    public function writeClose()
    {
        // The assumption is that we're using PHP's ext/session.
        // session_write_close() will actually overwrite $_SESSION with an
        // empty array on completion -- which leads to a mismatch between what
        // is in the storage object and $_SESSION. To get around this, we
        // temporarily reset $_SESSION to an array, and then re-link it to
        // the storage object.
        //
        // Additionally, while you _can_ write to $_SESSION following a
        // session_write_close() operation, no changes made to it will be
        // flushed to the session handler. As such, we now mark the storage
        // object isImmutable.
        $storage  = $this->getStorage();
        if (!$storage->isImmutable()) {
            $_SESSION = $storage->toArray(true);
            session_write_close();
            $storage->fromArray($_SESSION);
            $storage->markImmutable();
        }
    }

    /**
     * Attempt to set the session name
     *
     * If the session has already been started, or if the name provided fails
     * validation, an exception will be raised.
     *
     * @param  string $name
     * @return SessionManager
     * @throws Exception\InvalidArgumentException
     */
    public function setName($name)
    {
        if ($this->sessionExists()) {
            throw new Exception\InvalidArgumentException(
                'Cannot set session name after a session has already started'
            );
        }

        if (!preg_match('/^[a-zA-Z0-9]+$/', $name)) {
            throw new Exception\InvalidArgumentException(
                'Name provided contains invalid characters; must be alphanumeric only'
            );
        }

        $this->name = $name;
        session_name($name);
        return $this;
    }

    /**
     * Get session name
     *
     * Proxies to {@link session_name()}.
     *
     * @return string
     */
    public function getName()
    {
        if (null === $this->name) {
            // If we're grabbing via session_name(), we don't need our
            // validation routine; additionally, calling setName() after
            // session_start() can lead to issues, and often we just need the name
            // in order to do things such as setting cookies.
            $this->name = session_name();
        }
        return $this->name;
    }

    /**
     * Set session ID
     *
     * Can safely be called in the middle of a session.
     *
     * @param  string $id
     * @return SessionManager
     */
    public function setId($id)
    {
        if ($this->sessionExists()) {
            throw new Exception\RuntimeException('Session has already been started, to change the session ID call regenerateId()');
        }
        session_id($id);
        return $this;
    }

    /**
     * Get session ID
     *
     * Proxies to {@link session_id()}
     *
     * @return string
     */
    public function getId()
    {
        return session_id();
    }

    /**
     * Regenerate id
     *
     * Regenerate the session ID, using session save handler's
     * native ID generation Can safely be called in the middle of a session.
     *
     * @param  bool $deleteOldSession
     * @return SessionManager
     */
    public function regenerateId($deleteOldSession = true)
    {
        session_regenerate_id((bool) $deleteOldSession);
        return $this;
    }

    /**
     * Set the TTL (in seconds) for the session cookie expiry
     *
     * Can safely be called in the middle of a session.
     *
     * @param  null|int $ttl
     * @return SessionManager
     */
    public function rememberMe($ttl = null)
    {
        if (null === $ttl) {
            $ttl = $this->getConfig()->getRememberMeSeconds();
        }
        $this->setSessionCookieLifetime($ttl);
        return $this;
    }

    /**
     * Set a 0s TTL for the session cookie
     *
     * Can safely be called in the middle of a session.
     *
     * @return SessionManager
     */
    public function forgetMe()
    {
        $this->setSessionCookieLifetime(0);
        return $this;
    }

    /**
     * Set the validator chain to use when validating a session
     *
     * In most cases, you should use an instance of {@link ValidatorChain}.
     *
     * @param  EventManagerInterface $chain
     * @return SessionManager
     */
    public function setValidatorChain(EventManagerInterface $chain)
    {
        $this->validatorChain = $chain;
        return $this;
    }

    /**
     * Get the validator chain to use when validating a session
     *
     * By default, uses an instance of {@link ValidatorChain}.
     *
     * @return EventManagerInterface
     */
    public function getValidatorChain()
    {
        if (null === $this->validatorChain) {
            $this->setValidatorChain(new ValidatorChain($this->getStorage()));
        }
        return $this->validatorChain;
    }

    /**
     * Is this session valid?
     *
     * Notifies the Validator Chain until either all validators have returned
     * true or one has failed.
     *
     * @return bool
     */
    public function isValid()
    {
        $validator = $this->getValidatorChain();
        $responses = $validator->trigger('session.validate', $this, array($this), function ($test) {
            return false === $test;
        });
        if ($responses->stopped()) {
            // If execution was halted, validation failed
            return false;
        }
        // Otherwise, we're good to go
        return true;
    }

    /**
     * Expire the session cookie
     *
     * Sends a session cookie with no value, and with an expiry in the past.
     *
     * @return void
     */
    public function expireSessionCookie()
    {
        $config = $this->getConfig();
        if (!$config->getUseCookies()) {
            return;
        }
        setcookie(
            $this->getName(), // session name
            '', // value
            $_SERVER['REQUEST_TIME'] - 42000, // TTL for cookie
            $config->getCookiePath(),
            $config->getCookieDomain(),
            $config->getCookieSecure(),
            $config->getCookieHttpOnly()
        );
    }

    /**
     * Set the session cookie lifetime
     *
     * If a session already exists, destroys it (without sending an expiration
     * cookie), regenerates the session ID, and restarts the session.
     *
     * @param  int $ttl
     * @return void
     */
    protected function setSessionCookieLifetime($ttl)
    {
        $config = $this->getConfig();
        if (!$config->getUseCookies()) {
            return;
        }

        // Set new cookie TTL
        $config->setCookieLifetime($ttl);

        if ($this->sessionExists()) {
            // There is a running session so we'll regenerate id to send a new cookie
            $this->regenerateId();
        }
    }

    /**
     * Register Save Handler with ext/session
     *
     * Since ext/session is coupled to this particular session manager
     * register the save handler with ext/session.
     *
     * @param SaveHandler\SaveHandlerInterface $saveHandler
     * @return bool
     */
    protected function registerSaveHandler(SaveHandler\SaveHandlerInterface $saveHandler)
    {
        return session_set_save_handler(
            array($saveHandler, 'open'),
            array($saveHandler, 'close'),
            array($saveHandler, 'read'),
            array($saveHandler, 'write'),
            array($saveHandler, 'destroy'),
            array($saveHandler, 'gc')
        );
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Session\Config;

/**
 * Standard session configuration
 */
interface ConfigInterface
{
    public function setOptions($options);
    public function getOptions();

    public function setOption($option, $value);
    public function getOption($option);
    public function hasOption($option);

    public function toArray();

    public function setName($name);
    public function getName();

    public function setSavePath($savePath);
    public function getSavePath();

    public function setCookieLifetime($cookieLifetime);
    public function getCookieLifetime();

    public function setCookiePath($cookiePath);
    public function getCookiePath();

    public function setCookieDomain($cookieDomain);
    public function getCookieDomain();

    public function setCookieSecure($cookieSecure);
    public function getCookieSecure();

    public function setCookieHttpOnly($cookieHttpOnly);
    public function getCookieHttpOnly();

    public function setUseCookies($useCookies);
    public function getUseCookies();

    public function setRememberMeSeconds($rememberMeSeconds);
    public function getRememberMeSeconds();
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Session\Config;

use Traversable;
use Zend\Session\Exception;
use Zend\Validator\Hostname as HostnameValidator;

/**
 * Standard session configuration
 */
class StandardConfig implements ConfigInterface
{
    /**
     * session.name
     *
     * @var string
     */
    protected $name;

    /**
     * session.save_path
     *
     * @var string
     */
    protected $savePath;

    /**
     * session.cookie_lifetime
     *
     * @var int
     */
    protected $cookieLifetime;

    /**
     * session.cookie_path
     *
     * @var string
     */
    protected $cookiePath;

    /**
     * session.cookie_domain
     *
     * @var string
     */
    protected $cookieDomain;

    /**
     * session.cookie_secure
     *
     * @var bool
     */
    protected $cookieSecure;

    /**
     * session.cookie_httponly
     *
     * @var bool
     */
    protected $cookieHttpOnly;

    /**
     * remember_me_seconds
     *
     * @var int
     */
    protected $rememberMeSeconds;

    /**
     * session.use_cookies
     *
     * @var bool
     */
    protected $useCookies;

    /**
     * All options
     *
     * @var array
     */
    protected $options = array();

    /**
     * Set many options at once
     *
     * If a setter method exists for the key, that method will be called;
     * otherwise, a standard option will be set with the value provided via
     * {@link setOption()}.
     *
     * @param  array|Traversable $options
     * @return StandardConfig
     * @throws Exception\InvalidArgumentException
     */
    public function setOptions($options)
    {
        if (!is_array($options) && !$options instanceof Traversable) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Parameter provided to %s must be an array or Traversable',
                __METHOD__
            ));
        }

        foreach ($options as $key => $value) {
            $setter = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
            if (method_exists($this, $setter)) {
                $this->{$setter}($value);
            } else {
                $this->setOption($key, $value);
            }
        }
        return $this;
    }

    /**
     * Get all options set
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set an individual option
     *
     * Keys are normalized to lowercase. After setting internally, calls
     * {@link setStorageOption()} to allow further processing.
     *
     *
     * @param  string $option
     * @param  mixed $value
     * @return StandardConfig
     */
    public function setOption($option, $value)
    {
        $option                 = strtolower($option);
        $this->options[$option] = $value;
        $this->setStorageOption($option, $value);
        return $this;
    }

    /**
     * Get an individual option
     *
     * Keys are normalized to lowercase. If the option is not found, attempts
     * to retrieve it via {@link getStorageOption()}; if a value is returned
     * from that method, it will be set as the internal value and returned.
     *
     * Returns null for unfound options
     *
     * @param  string $option
     * @return mixed
     */
    public function getOption($option)
    {
        $option = strtolower($option);
        if (array_key_exists($option, $this->options)) {
            return $this->options[$option];
        }

        $value = $this->getStorageOption($option);
        if (null !== $value) {
            $this->setOption($option, $value);
            return $value;
        }

        return;
    }

    /**
     * Check to see if an internal option has been set for the key provided.
     *
     * @param  string $option
     * @return bool
     */
    public function hasOption($option)
    {
        $option = strtolower($option);
        return array_key_exists($option, $this->options);
    }

    /**
     * Set storage option in backend configuration store
     *
     * Does nothing in this implementation; others might use it to set things
     * such as INI settings.
     *
     * @param  string $storageName
     * @param  mixed $storageValue
     * @return StandardConfig
     */
    public function setStorageOption($storageName, $storageValue)
    {
        return $this;
    }

    /**
     * Retrieve a storage option from a backend configuration store
     *
     * Used to retrieve default values from a backend configuration store.
     *
     * @param  string $storageOption
     * @return mixed
     */
    public function getStorageOption($storageOption)
    {
        return;
    }

    /**
     * Set session.save_path
     *
     * @param  string $savePath
     * @return StandardConfig
     * @throws Exception\InvalidArgumentException on invalid path
     */
    public function setSavePath($savePath)
    {
        if (!is_dir($savePath)) {
            throw new Exception\InvalidArgumentException('Invalid save_path provided; not a directory');
        }
        if (!is_writable($savePath)) {
            throw new Exception\InvalidArgumentException('Invalid save_path provided; not writable');
        }

        $this->savePath = $savePath;
        $this->setStorageOption('save_path', $savePath);
        return $this;
    }

    /**
     * Set session.save_path
     *
     * @return string|null
     */
    public function getSavePath()
    {
        if (null === $this->savePath) {
            $this->savePath = $this->getStorageOption('save_path');
        }
        return $this->savePath;
    }

    /**
     * Set session.name
     *
     * @param  string $name
     * @return StandardConfig
     * @throws Exception\InvalidArgumentException
     */
    public function setName($name)
    {
        $this->name = (string) $name;
        if (empty($this->name)) {
            throw new Exception\InvalidArgumentException('Invalid session name; cannot be empty');
        }
        $this->setStorageOption('name', $this->name);
        return $this;
    }

    /**
     * Get session.name
     *
     * @return null|string
     */
    public function getName()
    {
        if (null === $this->name) {
            $this->name = $this->getStorageOption('name');
        }
        return $this->name;
    }

    /**
     * Set session.gc_probability
     *
     * @param  int $gcProbability
     * @return StandardConfig
     * @throws Exception\InvalidArgumentException
     */
    public function setGcProbability($gcProbability)
    {
        if (!is_numeric($gcProbability)) {
            throw new Exception\InvalidArgumentException('Invalid gc_probability; must be numeric');
        }
        $gcProbability = (int) $gcProbability;
        if (0 > $gcProbability || 100 < $gcProbability) {
            throw new Exception\InvalidArgumentException('Invalid gc_probability; must be a percentage');
        }
        $this->setOption('gc_probability', $gcProbability);
        $this->setStorageOption('gc_probability', $gcProbability);
        return $this;
    }

    /**
     * Get session.gc_probability
     *
     * @return int
     */
    public function getGcProbability()
    {
        if (!isset($this->options['gc_probability'])) {
            $this->options['gc_probability'] = $this->getStorageOption('gc_probability');
        }

        return $this->options['gc_probability'];
    }

    /**
     * Set session.gc_divisor
     *
     * @param  int $gcDivisor
     * @return StandardConfig
     * @throws Exception\InvalidArgumentException
     */
    public function setGcDivisor($gcDivisor)
    {
        if (!is_numeric($gcDivisor)) {
            throw new Exception\InvalidArgumentException('Invalid gc_divisor; must be numeric');
        }
        $gcDivisor = (int) $gcDivisor;
        if (1 > $gcDivisor) {
            throw new Exception\InvalidArgumentException('Invalid gc_divisor; must be a positive integer');
        }
        $this->setOption('gc_divisor', $gcDivisor);
        $this->setStorageOption('gc_divisor', $gcDivisor);
        return $this;
    }

    /**
     * Get session.gc_divisor
     *
     * @return int
     */
    public function getGcDivisor()
    {
        if (!isset($this->options['gc_divisor'])) {
            $this->options['gc_divisor'] = $this->getStorageOption('gc_divisor');
        }

        return $this->options['gc_divisor'];
    }

    /**
     * Set gc_maxlifetime
     *
     * @param  int $gcMaxlifetime
     * @return StandardConfig
     * @throws Exception\InvalidArgumentException
     */
    public function setGcMaxlifetime($gcMaxlifetime)
    {
        if (!is_numeric($gcMaxlifetime)) {
            throw new Exception\InvalidArgumentException('Invalid gc_maxlifetime; must be numeric');
        }

        $gcMaxlifetime = (int) $gcMaxlifetime;
        if (1 > $gcMaxlifetime) {
            throw new Exception\InvalidArgumentException('Invalid gc_maxlifetime; must be a positive integer');
        }

        $this->setOption('gc_maxlifetime', $gcMaxlifetime);
        $this->setStorageOption('gc_maxlifetime', $gcMaxlifetime);
        return $this;
    }

    /**
     * Get session.gc_maxlifetime
     *
     * @return int
     */
    public function getGcMaxlifetime()
    {
        if (!isset($this->options['gc_maxlifetime'])) {
            $this->options['gc_maxlifetime'] = $this->getStorageOption('gc_maxlifetime');
        }

        return $this->options['gc_maxlifetime'];
    }

    /**
     * Set session.cookie_lifetime
     *
     * @param  int $cookieLifetime
     * @return StandardConfig
     * @throws Exception\InvalidArgumentException
     */
    public function setCookieLifetime($cookieLifetime)
    {
        if (!is_numeric($cookieLifetime)) {
            throw new Exception\InvalidArgumentException('Invalid cookie_lifetime; must be numeric');
        }
        if (0 > $cookieLifetime) {
            throw new Exception\InvalidArgumentException(
                'Invalid cookie_lifetime; must be a positive integer or zero'
            );
        }

        $this->cookieLifetime = (int) $cookieLifetime;
        $this->setStorageOption('cookie_lifetime', $this->cookieLifetime);
        return $this;
    }

    /**
     * Get session.cookie_lifetime
     *
     * @return int
     */
    public function getCookieLifetime()
    {
        if (null === $this->cookieLifetime) {
            $this->cookieLifetime = $this->getStorageOption('cookie_lifetime');
        }
        return $this->cookieLifetime;
    }

    /**
     * Set session.cookie_path
     *
     * @param  string $cookiePath
     * @return StandardConfig
     * @throws Exception\InvalidArgumentException
     */
    public function setCookiePath($cookiePath)
    {
        $cookiePath = (string) $cookiePath;

        $test = parse_url($cookiePath, PHP_URL_PATH);
        if ($test != $cookiePath || '/' != $test[0]) {
            throw new Exception\InvalidArgumentException('Invalid cookie path');
        }

        $this->cookiePath = $cookiePath;
        $this->setStorageOption('cookie_path', $cookiePath);
        return $this;
    }

    /**
     * Get session.cookie_path
     *
     * @return string
     */
    public function getCookiePath()
    {
        if (null === $this->cookiePath) {
            $this->cookiePath = $this->getStorageOption('cookie_path');
        }
        return $this->cookiePath;
    }

    /**
     * Set session.cookie_domain
     *
     * @param  string $cookieDomain
     * @return StandardConfig
     * @throws Exception\InvalidArgumentException
     */
    public function setCookieDomain($cookieDomain)
    {
        if (!is_string($cookieDomain)) {
            throw new Exception\InvalidArgumentException('Invalid cookie domain: must be a string');
        }

        $validator = new HostnameValidator(HostnameValidator::ALLOW_ALL);

        if (!empty($cookieDomain) && !$validator->isValid($cookieDomain)) {
            throw new Exception\InvalidArgumentException(
                'Invalid cookie domain: ' . implode('; ', $validator->getMessages())
            );
        }

        $this->cookieDomain = $cookieDomain;
        $this->setStorageOption('cookie_domain', $cookieDomain);
        return $this;
    }

    /**
     * Get session.cookie_domain
     *
     * @return string
     */
    public function getCookieDomain()
    {
        if (null === $this->cookieDomain) {
            $this->cookieDomain = $this->getStorageOption('cookie_domain');
        }
        return $this->cookieDomain;
    }

    /**
     * Set session.cookie_secure
     *
     * @param  bool $cookieSecure
     * @return StandardConfig
     */
    public function setCookieSecure($cookieSecure)
    {
        $this->cookieSecure = (bool) $cookieSecure;
        $this->setStorageOption('cookie_secure', $this->cookieSecure);
        return $this;
    }

    /**
     * Get session.cookie_secure
     *
     * @return bool
     */
    public function getCookieSecure()
    {
        if (null === $this->cookieSecure) {
            $this->cookieSecure = $this->getStorageOption('cookie_secure');
        }
        return $this->cookieSecure;
    }

    /**
     * Set session.cookie_httponly
     *
     * case sensitive method lookups in setOptions means this method has an
     * unusual casing
     *
     * @param  bool $cookieHttpOnly
     * @return StandardConfig
     */
    public function setCookieHttpOnly($cookieHttpOnly)
    {
        $this->cookieHttpOnly = (bool) $cookieHttpOnly;
        $this->setStorageOption('cookie_httponly', $this->cookieHttpOnly);
        return $this;
    }

    /**
     * Get session.cookie_httponly
     *
     * @return bool
     */
    public function getCookieHttpOnly()
    {
        if (null === $this->cookieHttpOnly) {
            $this->cookieHttpOnly = $this->getStorageOption('cookie_httponly');
        }
        return $this->cookieHttpOnly;
    }

    /**
     * Set session.use_cookies
     *
     * @param  bool $useCookies
     * @return StandardConfig
     */
    public function setUseCookies($useCookies)
    {
        $this->useCookies = (bool) $useCookies;
        $this->setStorageOption('use_cookies', $this->useCookies);
        return $this;
    }

    /**
     * Get session.use_cookies
     *
     * @return bool
     */
    public function getUseCookies()
    {
        if (null === $this->useCookies) {
            $this->useCookies = $this->getStorageOption('use_cookies');
        }
        return $this->useCookies;
    }

    /**
     * Set session.entropy_file
     *
     * @param  string $entropyFile
     * @return StandardConfig
     * @throws Exception\InvalidArgumentException
     */
    public function setEntropyFile($entropyFile)
    {
        if (!is_readable($entropyFile)) {
            throw new Exception\InvalidArgumentException(sprintf(
                "Invalid entropy_file provided: '%s'; doesn't exist or not readable",
                $entropyFile
            ));
        }

        $this->setOption('entropy_file', $entropyFile);
        $this->setStorageOption('entropy_file', $entropyFile);
        return $this;
    }

    /**
     * Get session.entropy_file
     *
     * @return string
     */
    public function getEntropyFile()
    {
        if (!isset($this->options['entropy_file'])) {
            $this->options['entropy_file'] = $this->getStorageOption('entropy_file');
        }

        return $this->options['entropy_file'];
    }

    /**
     * set session.entropy_length
     *
     * @param  int $entropyLength
     * @return StandardConfig
     * @throws Exception\InvalidArgumentException
     */
    public function setEntropyLength($entropyLength)
    {
        if (!is_numeric($entropyLength)) {
            throw new Exception\InvalidArgumentException('Invalid entropy_length; must be numeric');
        }
        if (0 > $entropyLength) {
            throw new Exception\InvalidArgumentException('Invalid entropy_length; must be a positive integer or zero');
        }

        $this->setOption('entropy_length', $entropyLength);
        $this->setStorageOption('entropy_length', $entropyLength);
        return $this;
    }

    /**
     * Get session.entropy_length
     *
     * @return string
     */
    public function getEntropyLength()
    {
        if (!isset($this->options['entropy_length'])) {
            $this->options['entropy_length'] = $this->getStorageOption('entropy_length');
        }

        return $this->options['entropy_length'];
    }

    /**
     * Set session.cache_expire
     *
     * @param  int $cacheExpire
     * @return StandardConfig
     * @throws Exception\InvalidArgumentException
     */
    public function setCacheExpire($cacheExpire)
    {
        if (!is_numeric($cacheExpire)) {
            throw new Exception\InvalidArgumentException('Invalid cache_expire; must be numeric');
        }

        $cacheExpire = (int) $cacheExpire;
        if (1 > $cacheExpire) {
            throw new Exception\InvalidArgumentException('Invalid cache_expire; must be a positive integer');
        }

        $this->setOption('cache_expire', $cacheExpire);
        $this->setStorageOption('cache_expire', $cacheExpire);
        return $this;
    }

    /**
     * Get session.cache_expire
     *
     * @return string
     */
    public function getCacheExpire()
    {
        if (!isset($this->options['cache_expire'])) {
            $this->options['cache_expire'] = $this->getStorageOption('cache_expire');
        }

        return $this->options['cache_expire'];
    }

    /**
     * Set session.hash_bits_per_character
     *
     * @param  int $hashBitsPerCharacter
     * @return StandardConfig
     * @throws Exception\InvalidArgumentException
     */
    public function setHashBitsPerCharacter($hashBitsPerCharacter)
    {
        if (!is_numeric($hashBitsPerCharacter)) {
            throw new Exception\InvalidArgumentException('Invalid hash bits per character provided');
        }
        $hashBitsPerCharacter = (int) $hashBitsPerCharacter;
        $this->setOption('hash_bits_per_character', $hashBitsPerCharacter);
        $this->setStorageOption('hash_bits_per_character', $hashBitsPerCharacter);
        return $this;
    }

    /**
     * Get session.hash_bits_per_character
     *
     * @return string
     */
    public function getHashBitsPerCharacter()
    {
        if (!isset($this->options['hash_bits_per_character'])) {
            $this->options['hash_bits_per_character'] = $this->getStorageOption('hash_bits_per_character');
        }

        return $this->options['hash_bits_per_character'];
    }

    /**
     * Set remember_me_seconds
     *
     * @param  int $rememberMeSeconds
     * @return StandardConfig
     * @throws Exception\InvalidArgumentException
     */
    public function setRememberMeSeconds($rememberMeSeconds)
    {
        if (!is_numeric($rememberMeSeconds)) {
            throw new Exception\InvalidArgumentException('Invalid remember_me_seconds; must be numeric');
        }

        $rememberMeSeconds = (int) $rememberMeSeconds;
        if (1 > $rememberMeSeconds) {
            throw new Exception\InvalidArgumentException('Invalid remember_me_seconds; must be a positive integer');
        }

        $this->rememberMeSeconds = $rememberMeSeconds;
        $this->setStorageOption('remember_me_seconds', $rememberMeSeconds);
        return $this;
    }

    /**
     * Get remember_me_seconds
     *
     * @return int
     */
    public function getRememberMeSeconds()
    {
        if (null === $this->rememberMeSeconds) {
            $this->rememberMeSeconds = $this->getStorageOption('remember_me_seconds');
        }
        return $this->rememberMeSeconds;
    }

    /**
     * Cast configuration to an array
     *
     * @return array
     */
    public function toArray()
    {
        $extraOpts = array(
            'cookie_domain'       => $this->getCookieDomain(),
            'cookie_httponly'     => $this->getCookieHttpOnly(),
            'cookie_lifetime'     => $this->getCookieLifetime(),
            'cookie_path'         => $this->getCookiePath(),
            'cookie_secure'       => $this->getCookieSecure(),
            'name'                => $this->getName(),
            'remember_me_seconds' => $this->getRememberMeSeconds(),
            'save_path'           => $this->getSavePath(),
            'use_cookies'         => $this->getUseCookies(),
        );
        return array_merge($this->options, $extraOpts);
    }

    /**
     * Intercept get*() and set*() methods
     *
     * Intercepts getters and setters and passes them to getOption() and setOption(),
     * respectively.
     *
     * @param  string $method
     * @param  array $args
     * @return mixed
     * @throws Exception\BadMethodCallException on non-getter/setter method
     */
    public function __call($method, $args)
    {
        $prefix = substr($method, 0, 3);
        $option = substr($method, 3);
        $key    = strtolower(preg_replace('#(?<=[a-z])([A-Z])#', '_\1', $option));

        if ($prefix === 'set') {
            $value  = array_shift($args);
            return $this->setOption($key, $value);
        } elseif ($prefix === 'get') {
            return $this->getOption($key);
        } else {
            throw new Exception\BadMethodCallException(sprintf(
                'Method "%s" does not exist in %s',
                $method,
                get_class($this)
            ));
        }
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Session\Config;

use Zend\Session\Exception;

/**
 * Session configuration proxying to session INI options
 */
class SessionConfig extends StandardConfig
{
    /**
     * Used with {@link handleError()}; stores PHP error code
     * @var int
     */
    protected $phpErrorCode    = false;

    /**
     * Used with {@link handleError()}; stores PHP error message
     * @var string
     */
    protected $phpErrorMessage = false;

    /**
     * @var int Default number of seconds to make session sticky, when rememberMe() is called
     */
    protected $rememberMeSeconds = 1209600; // 2 weeks

    /**
     * @var string session.serialize_handler
     */
    protected $serializeHandler;

    /**
     * @var array Valid cache limiters (per session.cache_limiter)
     */
    protected $validCacheLimiters = array(
        '',
        'nocache',
        'public',
        'private',
        'private_no_expire',
    );

    /**
     * @var array Valid hash bits per character (per session.hash_bits_per_character)
     */
    protected $validHashBitsPerCharacters = array(
        4,
        5,
        6,
    );

    /**
     * @var array Valid hash functions (per session.hash_function)
     */
    protected $validHashFunctions;

    /**
     * Set storage option in backend configuration store
     *
     * @param  string $storageName
     * @param  mixed $storageValue
     * @return SessionConfig
     * @throws Exception\InvalidArgumentException
     */
    public function setStorageOption($storageName, $storageValue)
    {
        switch ($storageName) {
            case 'remember_me_seconds':
                // do nothing; not an INI option
                return;
            case 'url_rewriter_tags':
                $key = 'url_rewriter.tags';
                break;
            default:
                $key = 'session.' . $storageName;
                break;
        }

        $result = ini_set($key, (string) $storageValue);
        if (false === $result) {
            throw new Exception\InvalidArgumentException(
                "'{$key}' is not a valid sessions-related ini setting."
            );
        }
        return $this;
    }

    /**
     * Retrieve a storage option from a backend configuration store
     *
     * Used to retrieve default values from a backend configuration store.
     *
     * @param  string $storageOption
     * @return mixed
     */
    public function getStorageOption($storageOption)
    {
        switch ($storageOption) {
            case 'remember_me_seconds':
                // No remote storage option; just return the current value
                return $this->rememberMeSeconds;
            case 'url_rewriter_tags':
                return ini_get('url_rewriter.tags');
            // The following all need a transformation on the retrieved value;
            // however they use the same key naming scheme
            case 'use_cookies':
            case 'use_only_cookies':
            case 'use_trans_sid':
            case 'cookie_httponly':
                return (bool) ini_get('session.' . $storageOption);
            default:
                return ini_get('session.' . $storageOption);
        }
    }

    /**
     * Set session.save_handler
     *
     * @param  string $phpSaveHandler
     * @return SessionConfig
     * @throws Exception\InvalidArgumentException
     */
    public function setPhpSaveHandler($phpSaveHandler)
    {
        $phpSaveHandler = (string) $phpSaveHandler;
        set_error_handler(array($this, 'handleError'));
        ini_set('session.save_handler', $phpSaveHandler);
        restore_error_handler();
        if ($this->phpErrorCode >= E_WARNING) {
            throw new Exception\InvalidArgumentException(
                'Invalid save handler specified: ' . $this->phpErrorMessage
            );
        }

        $this->setOption('save_handler', $phpSaveHandler);
        return $this;
    }

    /**
     * Set session.save_path
     *
     * @param  string $savePath
     * @return SessionConfig
     * @throws Exception\InvalidArgumentException on invalid path
     */
    public function setSavePath($savePath)
    {
        if ($this->getOption('save_handler') == 'files') {
            parent::setSavePath($savePath);
        }
        $this->savePath = $savePath;
        $this->setOption('save_path', $savePath);
        return $this;
    }

    /**
     * Set session.serialize_handler
     *
     * @param  string $serializeHandler
     * @return SessionConfig
     * @throws Exception\InvalidArgumentException
     */
    public function setSerializeHandler($serializeHandler)
    {
        $serializeHandler = (string) $serializeHandler;

        set_error_handler(array($this, 'handleError'));
        ini_set('session.serialize_handler', $serializeHandler);
        restore_error_handler();
        if ($this->phpErrorCode >= E_WARNING) {
            throw new Exception\InvalidArgumentException('Invalid serialize handler specified');
        }

        $this->serializeHandler = (string) $serializeHandler;
        return $this;
    }

    // session.cache_limiter

    /**
     * Set cache limiter
     *
     * @param $cacheLimiter
     * @return SessionConfig
     * @throws Exception\InvalidArgumentException
     */
    public function setCacheLimiter($cacheLimiter)
    {
        $cacheLimiter = (string) $cacheLimiter;
        if (!in_array($cacheLimiter, $this->validCacheLimiters)) {
            throw new Exception\InvalidArgumentException('Invalid cache limiter provided');
        }
        $this->setOption('cache_limiter', $cacheLimiter);
        ini_set('session.cache_limiter', $cacheLimiter);
        return $this;
    }

    /**
     * Set session.hash_function
     *
     * @param  string|int $hashFunction
     * @return SessionConfig
     * @throws Exception\InvalidArgumentException
     */
    public function setHashFunction($hashFunction)
    {
        $hashFunction = (string) $hashFunction;
        $validHashFunctions = $this->getHashFunctions();
        if (!in_array($hashFunction, $validHashFunctions, true)) {
            throw new Exception\InvalidArgumentException('Invalid hash function provided');
        }

        $this->setOption('hash_function', $hashFunction);
        ini_set('session.hash_function', $hashFunction);
        return $this;
    }

    /**
     * Set session.hash_bits_per_character
     *
     * @param  int $hashBitsPerCharacter
     * @return SessionConfig
     * @throws Exception\InvalidArgumentException
     */
    public function setHashBitsPerCharacter($hashBitsPerCharacter)
    {
        if (!is_numeric($hashBitsPerCharacter)
            || !in_array($hashBitsPerCharacter, $this->validHashBitsPerCharacters)
        ) {
            throw new Exception\InvalidArgumentException('Invalid hash bits per character provided');
        }

        $hashBitsPerCharacter = (int) $hashBitsPerCharacter;
        $this->setOption('hash_bits_per_character', $hashBitsPerCharacter);
        ini_set('session.hash_bits_per_character', $hashBitsPerCharacter);
        return $this;
    }

    /**
     * Retrieve list of valid hash functions
     *
     * @return array
     */
    protected function getHashFunctions()
    {
        if (empty($this->validHashFunctions)) {
            /**
             * @link http://php.net/manual/en/session.configuration.php#ini.session.hash-function
             * "0" and "1" refer to MD5-128 and SHA1-160, respectively, and are
             * valid in addition to whatever is reported by hash_algos()
             */
            $this->validHashFunctions = array('0', '1') + hash_algos();
        }
        return $this->validHashFunctions;
    }

    /**
     * Handle PHP errors
     *
     * @param  int $code
     * @param  string $message
     * @return void
     */
    protected function handleError($code, $message)
    {
        $this->phpErrorCode    = $code;
        $this->phpErrorMessage = $message;
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Session\Storage;

use ArrayAccess;
use Countable;
use Serializable;
use Traversable;

/**
 * Session storage interface
 *
 * Defines the minimum requirements for handling userland, in-script session
 * storage (e.g., the $_SESSION superglobal array).
 */
interface StorageInterface extends Traversable, ArrayAccess, Serializable, Countable
{
    public function getRequestAccessTime();

    public function lock($key = null);
    public function isLocked($key = null);
    public function unlock($key = null);

    public function markImmutable();
    public function isImmutable();

    public function setMetadata($key, $value, $overwriteArray = false);
    public function getMetadata($key = null);

    public function clear($key = null);

    public function fromArray(array $array);
    public function toArray($metaData = false);
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Session\Storage;

/**
 * Session storage interface
 *
 * Defines the minimum requirements for handling userland, in-script session
 * storage (e.g., the $_SESSION superglobal array).
 */
interface StorageInitializationInterface
{
    /**
     * Initialize Session Storage
     *
     * @param  array $input
     * @return void
     */
    public function init($input = null);
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Session\Storage;

use ArrayIterator;
use IteratorAggregate;
use Zend\Session\Exception;

/**
 * Session storage in $_SESSION
 *
 * Replaces the $_SESSION superglobal with an ArrayObject that allows for
 * property access, metadata storage, locking, and immutability.
 */
abstract class AbstractSessionArrayStorage implements
    IteratorAggregate,
    StorageInterface,
    StorageInitializationInterface
{
    /**
     * Constructor
     *
     * @param array|null $input
     */
    public function __construct($input = null)
    {
        // this is here for B.C.
        $this->init($input);
    }

    /**
     * Initialize Storage
     *
     * @param  array $input
     * @return void
     */
    public function init($input = null)
    {
        if ((null === $input) && isset($_SESSION)) {
            $input = $_SESSION;
            if (is_object($input) && !$_SESSION instanceof \ArrayObject) {
                $input = (array) $input;
            }
        } elseif (null === $input) {
            $input = array();
        }
        $_SESSION = $input;
        $this->setRequestAccessTime(microtime(true));
    }

    /**
     * Get Offset
     *
     * @param  mixed $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->offsetGet($key);
    }

    /**
     * Set Offset
     *
     * @param  mixed $key
     * @param  mixed $value
     * @return void
     */
    public function __set($key, $value)
    {
        return $this->offsetSet($key, $value);
    }

    /**
     * Isset Offset
     *
     * @param  mixed   $key
     * @return bool
     */
    public function __isset($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Unset Offset
     *
     * @param  mixed $key
     * @return void
     */
    public function __unset($key)
    {
        return $this->offsetUnset($key);
    }

    /**
     * Destructor
     *
     * @return void
     */
    public function __destruct()
    {
        return ;
    }

    /**
     * Offset Exists
     *
     * @param  mixed   $key
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($key)
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Offset Get
     *
     * @param  mixed $key
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($key)
    {
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }

        return;
    }

    /**
     * Offset Set
     *
     * @param  mixed $key
     * @param  mixed $value
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Offset Unset
     *
     * @param  mixed $key
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($key)
    {
        unset($_SESSION[$key]);
    }

    /**
     * Count
     *
     * @return int
     */
    #[\ReturnTypeWillChange]
    public function count()
    {
        return count($_SESSION);
    }

    /**
     * Seralize
     *
     * @return string
     */
    public function serialize()
    {
        return serialize($_SESSION);
    }

    /**
     * Unserialize
     *
     * @param  string $session
     * @return mixed
     */
    public function unserialize($session)
    {
        return unserialize($session);
    }

    /**
     * Get Iterator
     *
     * @return ArrayIterator
     */
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        return new ArrayIterator($_SESSION);
    }

    /**
     * Load session object from an existing array
     *
     * Ensures $_SESSION is set to an instance of the object when complete.
     *
     * @param  array          $array
     * @return SessionStorage
     */
    public function fromArray(array $array)
    {
        $ts = $this->getRequestAccessTime();
        $_SESSION = $array;
        $this->setRequestAccessTime($ts);

        return $this;
    }

    /**
     * Mark object as isImmutable
     *
     * @return SessionStorage
     */
    public function markImmutable()
    {
        $_SESSION['_IMMUTABLE'] = true;

        return $this;
    }

    /**
     * Determine if this object is isImmutable
     *
     * @return bool
     */
    public function isImmutable()
    {
        return (isset($_SESSION['_IMMUTABLE']) && $_SESSION['_IMMUTABLE']);
    }

    /**
     * Lock this storage instance, or a key within it
     *
     * @param  null|int|string $key
     * @return ArrayStorage
     */
    public function lock($key = null)
    {
        if (null === $key) {
            $this->setMetadata('_READONLY', true);

            return $this;
        }
        if (isset($_SESSION[$key])) {
            $this->setMetadata('_LOCKS', array($key => true));
        }

        return $this;
    }

    /**
     * Is the object or key marked as locked?
     *
     * @param  null|int|string $key
     * @return bool
     */
    public function isLocked($key = null)
    {
        if ($this->isImmutable()) {
            // isImmutable trumps all
            return true;
        }

        if (null === $key) {
            // testing for global lock
            return $this->getMetadata('_READONLY');
        }

        $locks    = $this->getMetadata('_LOCKS');
        $readOnly = $this->getMetadata('_READONLY');

        if ($readOnly && !$locks) {
            // global lock in play; all keys are locked
            return true;
        }
        if ($readOnly && $locks) {
            return array_key_exists($key, $locks);
        }

        // test for individual locks
        if (!$locks) {
            return false;
        }

        return array_key_exists($key, $locks);
    }

    /**
     * Unlock an object or key marked as locked
     *
     * @param  null|int|string $key
     * @return ArrayStorage
     */
    public function unlock($key = null)
    {
        if (null === $key) {
            // Unlock everything
            $this->setMetadata('_READONLY', false);
            $this->setMetadata('_LOCKS', false);

            return $this;
        }

        $locks = $this->getMetadata('_LOCKS');
        if (!$locks) {
            if (!$this->getMetadata('_READONLY')) {
                return $this;
            }
            $array = $this->toArray();
            $keys  = array_keys($array);
            $locks = array_flip($keys);
            unset($array, $keys);
        }

        if (array_key_exists($key, $locks)) {
            unset($locks[$key]);
            $this->setMetadata('_LOCKS', $locks, true);
        }

        return $this;
    }

    /**
     * Set storage metadata
     *
     * Metadata is used to store information about the data being stored in the
     * object. Some example use cases include:
     * - Setting expiry data
     * - Maintaining access counts
     * - localizing session storage
     * - etc.
     *
     * @param  string                     $key
     * @param  mixed                      $value
     * @param  bool                       $overwriteArray Whether to overwrite or merge array values; by default, merges
     * @return ArrayStorage
     * @throws Exception\RuntimeException
     */
    public function setMetadata($key, $value, $overwriteArray = false)
    {
        if ($this->isImmutable()) {
            throw new Exception\RuntimeException(
                sprintf('Cannot set key "%s" as storage is marked isImmutable', $key)
            );
        }

        if (!isset($_SESSION['__ZF'])) {
            $_SESSION['__ZF'] = array();
        }
        if (isset($_SESSION['__ZF'][$key]) && is_array($value)) {
            if ($overwriteArray) {
                $_SESSION['__ZF'][$key] = $value;
            } else {
                $_SESSION['__ZF'][$key] = array_replace_recursive($_SESSION['__ZF'][$key], $value);
            }
        } else {
            if ((null === $value) && isset($_SESSION['__ZF'][$key])) {
                $array = $_SESSION['__ZF'];
                unset($array[$key]);
                $_SESSION['__ZF'] = $array;
                unset($array);
            } elseif (null !== $value) {
                $_SESSION['__ZF'][$key] = $value;
            }
        }

        return $this;
    }

    /**
     * Retrieve metadata for the storage object or a specific metadata key
     *
     * Returns false if no metadata stored, or no metadata exists for the given
     * key.
     *
     * @param  null|int|string $key
     * @return mixed
     */
    public function getMetadata($key = null)
    {
        if (!isset($_SESSION['__ZF'])) {
            return false;
        }

        if (null === $key) {
            return $_SESSION['__ZF'];
        }

        if (!array_key_exists($key, $_SESSION['__ZF'])) {
            return false;
        }

        return $_SESSION['__ZF'][$key];
    }

    /**
     * Clear the storage object or a subkey of the object
     *
     * @param  null|int|string            $key
     * @return ArrayStorage
     * @throws Exception\RuntimeException
     */
    public function clear($key = null)
    {
        if ($this->isImmutable()) {
            throw new Exception\RuntimeException('Cannot clear storage as it is marked immutable');
        }
        if (null === $key) {
            $this->fromArray(array());

            return $this;
        }

        if (!isset($_SESSION[$key])) {
            return $this;
        }

        // Clear key data
        unset($_SESSION[$key]);

        // Clear key metadata
        $this->setMetadata($key, null)
             ->unlock($key);

        return $this;
    }

    /**
     * Retrieve the request access time
     *
     * @return float
     */
    public function getRequestAccessTime()
    {
        return $this->getMetadata('_REQUEST_ACCESS_TIME');
    }

    /**
     * Set the request access time
     *
     * @param  float        $time
     * @return ArrayStorage
     */
    protected function setRequestAccessTime($time)
    {
        $this->setMetadata('_REQUEST_ACCESS_TIME', $time);

        return $this;
    }

    /**
     * Cast the object to an array
     *
     * @param  bool $metaData Whether to include metadata
     * @return array
     */
    public function toArray($metaData = false)
    {
        if (isset($_SESSION)) {
            $values = $_SESSION;
        } else {
            $values = array();
        }

        if ($metaData) {
            return $values;
        }

        if (isset($values['__ZF'])) {
            unset($values['__ZF']);
        }

        return $values;
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Session\Storage;

/**
 * Session storage in $_SESSION
 */
class SessionArrayStorage extends AbstractSessionArrayStorage
{
    public function __serialize() {$this->serialize();}
    public function __unserialize($data) {$this->unserialize($data);}
    /**
     * Get Offset
     *
     * @param  mixed $key
     * @return mixed
     */
    public function &__get($key)
    {
        return $_SESSION[$key];
    }

    /**
     * Offset Get
     *
     * @param  mixed $key
     * @return mixed
     */
    public function &offsetGet($key)
    {
        return $_SESSION[$key];
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Stdlib;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use Serializable;

/**
 * Custom framework ArrayObject implementation
 *
 * Extends version-specific "abstract" implementation.
 */
class ArrayObject implements IteratorAggregate, ArrayAccess, Serializable, Countable
{
    public function __serialize() {return (array)$this->serialize();}
    public function __unserialize($data) {$this->unserialize($data);}
    /**
     * Properties of the object have their normal functionality
     * when accessed as list (var_dump, foreach, etc.).
     */
    const STD_PROP_LIST = 1;

    /**
     * Entries can be accessed as properties (read and write).
     */
    const ARRAY_AS_PROPS = 2;

    /**
     * @var array
     */
    protected $storage;

    /**
     * @var int
     */
    protected $flag;

    /**
     * @var string
     */
    protected $iteratorClass;

    /**
     * @var array
     */
    protected $protectedProperties;

    /**
     * Constructor
     *
     * @param array  $input
     * @param int    $flags
     * @param string $iteratorClass
     */
    public function __construct($input = [], $flags = self::STD_PROP_LIST, $iteratorClass = 'ArrayIterator')
    {
        $this->setFlags($flags);
        $this->storage = $input;
        $this->setIteratorClass($iteratorClass);
        $this->protectedProperties = array_keys(get_object_vars($this));
    }

    /**
     * Returns whether the requested key exists
     *
     * @param  mixed $key
     * @return bool
     */
    public function __isset($key)
    {
        if ($this->flag == self::ARRAY_AS_PROPS) {
            return $this->offsetExists($key);
        }
        if (in_array($key, $this->protectedProperties)) {
            throw new Exception\InvalidArgumentException('$key is a protected property, use a different key');
        }

        return isset($this->$key);
    }

    /**
     * Sets the value at the specified key to value
     *
     * @param  mixed $key
     * @param  mixed $value
     * @return void
     */
    public function __set($key, $value)
    {
        if ($this->flag == self::ARRAY_AS_PROPS) {
            return $this->offsetSet($key, $value);
        }
        if (in_array($key, $this->protectedProperties)) {
            throw new Exception\InvalidArgumentException('$key is a protected property, use a different key');
        }
        $this->$key = $value;
    }

    /**
     * Unsets the value at the specified key
     *
     * @param  mixed $key
     * @return void
     */
    public function __unset($key)
    {
        if ($this->flag == self::ARRAY_AS_PROPS) {
            return $this->offsetUnset($key);
        }
        if (in_array($key, $this->protectedProperties)) {
            throw new Exception\InvalidArgumentException('$key is a protected property, use a different key');
        }
        unset($this->$key);
    }

    /**
     * Returns the value at the specified key by reference
     *
     * @param  mixed $key
     * @return mixed
     */
    public function &__get($key)
    {
        $ret = null;
        if ($this->flag == self::ARRAY_AS_PROPS) {
            $ret =& $this->offsetGet($key);

            return $ret;
        }
        if (in_array($key, $this->protectedProperties)) {
            throw new Exception\InvalidArgumentException('$key is a protected property, use a different key');
        }

        return $this->$key;
    }

    /**
     * Appends the value
     *
     * @param  mixed $value
     * @return void
     */
    public function append($value)
    {
        $this->storage[] = $value;
    }

    /**
     * Sort the entries by value
     *
     * @return void
     */
    public function asort()
    {
        asort($this->storage);
    }

    /**
     * Get the number of public properties in the ArrayObject
     *
     * @return int
     */
    #[\ReturnTypeWillChange]
    public function count()
    {
        return count($this->storage);
    }

    /**
     * Exchange the array for another one.
     *
     * @param  array|ArrayObject $data
     * @return array
     */
    public function exchangeArray($data)
    {
        if (!is_array($data) && !is_object($data)) {
            throw new Exception\InvalidArgumentException('Passed variable is not an array or object, using empty array instead');
        }

        if (is_object($data) && ($data instanceof self || $data instanceof \ArrayObject)) {
            $data = $data->getArrayCopy();
        }
        if (!is_array($data)) {
            $data = (array) $data;
        }

        $storage = $this->storage;

        $this->storage = $data;

        return $storage;
    }

    /**
     * Creates a copy of the ArrayObject.
     *
     * @return array
     */
    public function getArrayCopy()
    {
        return $this->storage;
    }

    /**
     * Gets the behavior flags.
     *
     * @return int
     */
    public function getFlags()
    {
        return $this->flag;
    }

    /**
     * Create a new iterator from an ArrayObject instance
     *
     * @return \Iterator
     */
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        $class = $this->iteratorClass;

        return new $class($this->storage);
    }

    /**
     * Gets the iterator classname for the ArrayObject.
     *
     * @return string
     */
    public function getIteratorClass()
    {
        return $this->iteratorClass;
    }

    /**
     * Sort the entries by key
     *
     * @return void
     */
    public function ksort()
    {
        ksort($this->storage);
    }

    /**
     * Sort an array using a case insensitive "natural order" algorithm
     *
     * @return void
     */
    public function natcasesort()
    {
        natcasesort($this->storage);
    }

    /**
     * Sort entries using a "natural order" algorithm
     *
     * @return void
     */
    public function natsort()
    {
        natsort($this->storage);
    }

    /**
     * Returns whether the requested key exists
     *
     * @param  mixed $key
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($key)
    {
        return isset($this->storage[$key]);
    }

    /**
     * Returns the value at the specified key
     *
     * @param  mixed $key
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function &offsetGet($key)
    {
        $ret = null;
        if (!$this->offsetExists($key)) {
            return $ret;
        }
        $ret =& $this->storage[$key];

        return $ret;
    }

    /**
     * Sets the value at the specified key to value
     *
     * @param  mixed $key
     * @param  mixed $value
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($key, $value)
    {
        $this->storage[$key] = $value;
    }

    /**
     * Unsets the value at the specified key
     *
     * @param  mixed $key
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($key)
    {
        if ($this->offsetExists($key)) {
            unset($this->storage[$key]);
        }
    }

    /**
     * Serialize an ArrayObject
     *
     * @return string
     */
    public function serialize()
    {
        return serialize(get_object_vars($this));
    }

    /**
     * Sets the behavior flags
     *
     * @param  int  $flags
     * @return void
     */
    public function setFlags($flags)
    {
        $this->flag = $flags;
    }

    /**
     * Sets the iterator classname for the ArrayObject
     *
     * @param  string $class
     * @return void
     */
    public function setIteratorClass($class)
    {
        if (class_exists($class)) {
            $this->iteratorClass = $class;

            return ;
        }

        if (strpos($class, '\\') === 0) {
            $class = '\\' . $class;
            if (class_exists($class)) {
                $this->iteratorClass = $class;

                return ;
            }
        }

        throw new Exception\InvalidArgumentException('The iterator class does not exist');
    }

    /**
     * Sort the entries with a user-defined comparison function and maintain key association
     *
     * @param  callable $function
     * @return void
     */
    public function uasort($function)
    {
        if (is_callable($function)) {
            uasort($this->storage, $function);
        }
    }

    /**
     * Sort the entries by keys using a user-defined comparison function
     *
     * @param  callable $function
     * @return void
     */
    public function uksort($function)
    {
        if (is_callable($function)) {
            uksort($this->storage, $function);
        }
    }

    /**
     * Unserialize an ArrayObject
     *
     * @param  string $data
     * @return void
     */
    public function unserialize($data)
    {
        if (is_array($data)) {
            $data = reset($data);
        }
        $ar                        = unserialize($data);
        $this->protectedProperties = array_keys(get_object_vars($this));

        $this->setFlags($ar['flag']);
        $this->exchangeArray($ar['storage']);
        $this->setIteratorClass($ar['iteratorClass']);

        foreach ($ar as $k => $v) {
            switch ($k) {
                case 'flag':
                    $this->setFlags($v);
                    break;
                case 'storage':
                    $this->exchangeArray($v);
                    break;
                case 'iteratorClass':
                    $this->setIteratorClass($v);
                    break;
                case 'protectedProperties':
                    break;
                default:
                    $this->__set($k, $v);
            }
        }
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Stdlib;

use Traversable;
use Zend\Stdlib\ArrayUtils\MergeRemoveKey;
use Zend\Stdlib\ArrayUtils\MergeReplaceKeyInterface;

/**
 * Utility class for testing and manipulation of PHP arrays.
 *
 * Declared abstract, as we have no need for instantiation.
 */
abstract class ArrayUtils
{
    /**
     * Compatibility Flag for ArrayUtils::filter
     */
    const ARRAY_FILTER_USE_BOTH = 1;

    /**
     * Compatibility Flag for ArrayUtils::filter
     */
    const ARRAY_FILTER_USE_KEY  = 2;

    /**
     * Test whether an array contains one or more string keys
     *
     * @param  mixed $value
     * @param  bool  $allowEmpty    Should an empty array() return true
     * @return bool
     */
    public static function hasStringKeys($value, $allowEmpty = false)
    {
        if (!is_array($value)) {
            return false;
        }

        if (!$value) {
            return $allowEmpty;
        }

        return count(array_filter(array_keys($value), 'is_string')) > 0;
    }

    /**
     * Test whether an array contains one or more integer keys
     *
     * @param  mixed $value
     * @param  bool  $allowEmpty    Should an empty array() return true
     * @return bool
     */
    public static function hasIntegerKeys($value, $allowEmpty = false)
    {
        if (!is_array($value)) {
            return false;
        }

        if (!$value) {
            return $allowEmpty;
        }

        return count(array_filter(array_keys($value), 'is_int')) > 0;
    }

    /**
     * Test whether an array contains one or more numeric keys.
     *
     * A numeric key can be one of the following:
     * - an integer 1,
     * - a string with a number '20'
     * - a string with negative number: '-1000'
     * - a float: 2.2120, -78.150999
     * - a string with float:  '4000.99999', '-10.10'
     *
     * @param  mixed $value
     * @param  bool  $allowEmpty    Should an empty array() return true
     * @return bool
     */
    public static function hasNumericKeys($value, $allowEmpty = false)
    {
        if (!is_array($value)) {
            return false;
        }

        if (!$value) {
            return $allowEmpty;
        }

        return count(array_filter(array_keys($value), 'is_numeric')) > 0;
    }

    /**
     * Test whether an array is a list
     *
     * A list is a collection of values assigned to continuous integer keys
     * starting at 0 and ending at count() - 1.
     *
     * For example:
     * <code>
     * $list = array('a', 'b', 'c', 'd');
     * $list = array(
     *     0 => 'foo',
     *     1 => 'bar',
     *     2 => array('foo' => 'baz'),
     * );
     * </code>
     *
     * @param  mixed $value
     * @param  bool  $allowEmpty    Is an empty list a valid list?
     * @return bool
     */
    public static function isList($value, $allowEmpty = false)
    {
        if (!is_array($value)) {
            return false;
        }

        if (!$value) {
            return $allowEmpty;
        }

        return (array_values($value) === $value);
    }

    /**
     * Test whether an array is a hash table.
     *
     * An array is a hash table if:
     *
     * 1. Contains one or more non-integer keys, or
     * 2. Integer keys are non-continuous or misaligned (not starting with 0)
     *
     * For example:
     * <code>
     * $hash = array(
     *     'foo' => 15,
     *     'bar' => false,
     * );
     * $hash = array(
     *     1995  => 'Birth of PHP',
     *     2009  => 'PHP 5.3.0',
     *     2012  => 'PHP 5.4.0',
     * );
     * $hash = array(
     *     'formElement,
     *     'options' => array( 'debug' => true ),
     * );
     * </code>
     *
     * @param  mixed $value
     * @param  bool  $allowEmpty    Is an empty array() a valid hash table?
     * @return bool
     */
    public static function isHashTable($value, $allowEmpty = false)
    {
        if (!is_array($value)) {
            return false;
        }

        if (!$value) {
            return $allowEmpty;
        }

        return (array_values($value) !== $value);
    }

    /**
     * Checks if a value exists in an array.
     *
     * Due to "foo" == 0 === TRUE with in_array when strict = false, an option
     * has been added to prevent this. When $strict = 0/false, the most secure
     * non-strict check is implemented. if $strict = -1, the default in_array
     * non-strict behaviour is used.
     *
     * @param mixed $needle
     * @param array $haystack
     * @param int|bool $strict
     * @return bool
     */
    public static function inArray($needle, array $haystack, $strict = false)
    {
        if (!$strict) {
            if (is_int($needle) || is_float($needle)) {
                $needle = (string) $needle;
            }
            if (is_string($needle)) {
                foreach ($haystack as &$h) {
                    if (is_int($h) || is_float($h)) {
                        $h = (string) $h;
                    }
                }
            }
        }
        return in_array($needle, $haystack, $strict);
    }

    /**
     * Convert an iterator to an array.
     *
     * Converts an iterator to an array. The $recursive flag, on by default,
     * hints whether or not you want to do so recursively.
     *
     * @param  array|Traversable  $iterator     The array or Traversable object to convert
     * @param  bool               $recursive    Recursively check all nested structures
     * @throws Exception\InvalidArgumentException if $iterator is not an array or a Traversable object
     * @return array
     */
    public static function iteratorToArray($iterator, $recursive = true)
    {
        if (!is_array($iterator) && !$iterator instanceof Traversable) {
            throw new Exception\InvalidArgumentException(__METHOD__ . ' expects an array or Traversable object');
        }

        if (!$recursive) {
            if (is_array($iterator)) {
                return $iterator;
            }

            return iterator_to_array($iterator);
        }

        if (method_exists($iterator, 'toArray')) {
            return $iterator->toArray();
        }

        $array = [];
        foreach ($iterator as $key => $value) {
            if (is_scalar($value)) {
                $array[$key] = $value;
                continue;
            }

            if ($value instanceof Traversable) {
                $array[$key] = static::iteratorToArray($value, $recursive);
                continue;
            }

            if (is_array($value)) {
                $array[$key] = static::iteratorToArray($value, $recursive);
                continue;
            }

            $array[$key] = $value;
        }

        return $array;
    }

    /**
     * Merge two arrays together.
     *
     * If an integer key exists in both arrays and preserveNumericKeys is false, the value
     * from the second array will be appended to the first array. If both values are arrays, they
     * are merged together, else the value of the second array overwrites the one of the first array.
     *
     * @param  array $a
     * @param  array $b
     * @param  bool  $preserveNumericKeys
     * @return array
     */
    public static function merge(array $a, array $b, $preserveNumericKeys = false)
    {
        foreach ($b as $key => $value) {
            if ($value instanceof MergeReplaceKeyInterface) {
                $a[$key] = $value->getData();
            } elseif (isset($a[$key]) || array_key_exists($key, $a)) {
                if ($value instanceof MergeRemoveKey) {
                    unset($a[$key]);
                } elseif (!$preserveNumericKeys && is_int($key)) {
                    $a[] = $value;
                } elseif (is_array($value) && is_array($a[$key])) {
                    $a[$key] = static::merge($a[$key], $value, $preserveNumericKeys);
                } else {
                    $a[$key] = $value;
                }
            } else {
                if (!$value instanceof MergeRemoveKey) {
                    $a[$key] = $value;
                }
            }
        }

        return $a;
    }

    /**
     * Compatibility Method for array_filter on <5.6 systems
     *
     * @param array $data
     * @param callable $callback
     * @param null|int $flag
     * @return array
     */
    public static function filter(array $data, $callback, $flag = null)
    {
        if (! is_callable($callback)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Second parameter of %s must be callable',
                __METHOD__
            ));
        }

        if (version_compare(PHP_VERSION, '5.6.0') >= 0) {
            return array_filter($data, $callback, $flag);
        }

        $output = [];
        foreach ($data as $key => $value) {
            $params = [$value];

            if ($flag === static::ARRAY_FILTER_USE_BOTH) {
                $params[] = $key;
            }

            if ($flag === static::ARRAY_FILTER_USE_KEY) {
                $params = [$key];
            }

            $response = call_user_func_array($callback, $params);
            if ($response) {
                $output[$key] = $value;
            }
        }

        return $output;
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Stdlib;

use Countable;
use Iterator;

class PriorityList implements Iterator, Countable
{
    const EXTR_DATA     = 0x00000001;
    const EXTR_PRIORITY = 0x00000002;
    const EXTR_BOTH     = 0x00000003;
    /**
     * Internal list of all items.
     *
     * @var array[]
     */
    protected $items = [];

    /**
     * Serial assigned to items to preserve LIFO.
     *
     * @var int
     */
    protected $serial = 0;

    /**
     * Serial order mode
     * @var integer
     */
    protected $isLIFO = 1;

    /**
     * Internal counter to avoid usage of count().
     *
     * @var int
     */
    protected $count = 0;

    /**
     * Whether the list was already sorted.
     *
     * @var bool
     */
    protected $sorted = false;

    /**
     * Insert a new item.
     *
     * @param  string  $name
     * @param  mixed   $value
     * @param  int     $priority
     *
     * @return void
     */
    public function insert($name, $value, $priority = 0)
    {
        if (!isset($this->items[$name])) {
            $this->count++;
        }

        $this->sorted = false;

        $this->items[$name] = [
            'data'     => $value,
            'priority' => (int) $priority,
            'serial'   => $this->serial++,
        ];
    }

    /**
     * @param string $name
     * @param int    $priority
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function setPriority($name, $priority)
    {
        if (!isset($this->items[$name])) {
            throw new \Exception("item $name not found");
        }

        $this->items[$name]['priority'] = (int) $priority;
        $this->sorted                   = false;

        return $this;
    }

    /**
     * Remove a item.
     *
     * @param  string $name
     * @return void
     */
    public function remove($name)
    {
        if (isset($this->items[$name])) {
            $this->count--;
        }

        unset($this->items[$name]);
    }

    /**
     * Remove all items.
     *
     * @return void
     */
    public function clear()
    {
        $this->items  = [];
        $this->serial = 0;
        $this->count  = 0;
        $this->sorted = false;
    }

    /**
     * Get a item.
     *
     * @param  string $name
     * @return mixed
     */
    public function get($name)
    {
        if (!isset($this->items[$name])) {
            return;
        }

        return $this->items[$name]['data'];
    }

    /**
     * Sort all items.
     *
     * @return void
     */
    protected function sort()
    {
        if (!$this->sorted) {
            uasort($this->items, [$this, 'compare']);
            $this->sorted = true;
        }
    }

    /**
     * Compare the priority of two items.
     *
     * @param  array $item1,
     * @param  array $item2
     * @return int
     */
    protected function compare(array $item1, array $item2)
    {
        return ($item1['priority'] === $item2['priority'])
            ? ($item1['serial']   > $item2['serial']   ? -1 : 1) * $this->isLIFO
            : ($item1['priority'] > $item2['priority'] ? -1 : 1);
    }

    /**
     * Get/Set serial order mode
     *
     * @param bool|null $flag
     *
     * @return bool
     */
    public function isLIFO($flag = null)
    {
        if ($flag !== null) {
            $isLifo = $flag === true ? 1 : -1;

            if ($isLifo !== $this->isLIFO) {
                $this->isLIFO = $isLifo;
                $this->sorted = false;
            }
        }

        return 1 === $this->isLIFO;
    }

    /**
     * {@inheritDoc}
     */
    #[\ReturnTypeWillChange]
    public function rewind()
    {
        $this->sort();
        reset($this->items);
    }

    /**
     * {@inheritDoc}
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        $this->sorted || $this->sort();
        $node = current($this->items);

        return $node ? $node['data'] : false;
    }

    /**
     * {@inheritDoc}
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        $this->sorted || $this->sort();
        return key($this->items);
    }

    /**
     * {@inheritDoc}
     */
    #[\ReturnTypeWillChange]
    public function next()
    {
        $node = next($this->items);

        return $node ? $node['data'] : false;
    }

    /**
     * {@inheritDoc}
     */
    #[\ReturnTypeWillChange]
    public function valid()
    {
        return current($this->items) !== false;
    }

    /**
     * @return self
     */
    public function getIterator()
    {
        return clone $this;
    }

    /**
     * {@inheritDoc}
     */
    #[\ReturnTypeWillChange]
    public function count()
    {
        return $this->count;
    }

    /**
     * Return list as array
     *
     * @param int $flag
     *
     * @return array
     */
    public function toArray($flag = self::EXTR_DATA)
    {
        $this->sort();

        if ($flag == self::EXTR_BOTH) {
            return $this->items;
        }

        return array_map(
            function ($item) use ($flag) {
                return ($flag == PriorityList::EXTR_PRIORITY) ? $item['priority'] : $item['data'];
            },
            $this->items
        );
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Session;

use ArrayIterator;
use Iterator;
use Traversable;
use Zend\Session\ManagerInterface as Manager;
use Zend\Session\Storage\StorageInterface as Storage;
use Zend\Stdlib\ArrayObject;

/**
 * Session storage container
 *
 * Allows for interacting with session storage in isolated containers, which
 * may have their own expiries, or even expiries per key in the container.
 * Additionally, expiries may be absolute TTLs or measured in "hops", which
 * are based on how many times the key or container were accessed.
 */
abstract class AbstractContainer extends ArrayObject
{
    /**
     * Container name
     *
     * @var string
     */
    protected $name;

    /**
     * @var Manager
     */
    protected $manager;

    /**
     * Default manager class to use if no manager has been provided
     *
     * @var string
     */
    protected static $managerDefaultClass = 'Zend\\Session\\SessionManager';

    /**
     * Default manager to use when instantiating a container without providing a ManagerInterface
     *
     * @var Manager
     */
    protected static $defaultManager;

    /**
     * Constructor
     *
     * Provide a name ('Default' if none provided) and a ManagerInterface instance.
     *
     * @param  null|string                        $name
     * @param  Manager                            $manager
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($name = 'Default', ?Manager $manager = null)
    {
        if (!preg_match('/^[a-z0-9][a-z0-9_\\\\]+$/i', $name)) {
            throw new Exception\InvalidArgumentException(
                'Name passed to container is invalid; must consist of alphanumerics, backslashes and underscores only'
            );
        }
        $this->name = $name;
        $this->setManager($manager);

        // Create namespace
        parent::__construct(array(), ArrayObject::ARRAY_AS_PROPS);

        // Start session
        $this->getManager()->start();
    }

    /**
     * Set the default ManagerInterface instance to use when none provided to constructor
     *
     * @param  Manager $manager
     * @return void
     */
    public static function setDefaultManager(?Manager $manager = null)
    {
        static::$defaultManager = $manager;
    }

    /**
     * Get the default ManagerInterface instance
     *
     * If none provided, instantiates one of type {@link $managerDefaultClass}
     *
     * @return Manager
     * @throws Exception\InvalidArgumentException if invalid manager default class provided
     */
    public static function getDefaultManager()
    {
        if (null === static::$defaultManager) {
            $manager = new static::$managerDefaultClass();
            if (!$manager instanceof Manager) {
                throw new Exception\InvalidArgumentException(
                    'Invalid default manager type provided; must implement ManagerInterface'
                );
            }
            static::$defaultManager = $manager;
        }

        return static::$defaultManager;
    }

    /**
     * Get container name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set session manager
     *
     * @param  null|Manager                       $manager
     * @return Container
     * @throws Exception\InvalidArgumentException
     */
    protected function setManager(?Manager $manager = null)
    {
        if (null === $manager) {
            $manager = static::getDefaultManager();
            if (!$manager instanceof Manager) {
                throw new Exception\InvalidArgumentException(
                    'Manager provided is invalid; must implement ManagerInterface'
                );
            }
        }
        $this->manager = $manager;

        return $this;
    }

    /**
     * Get manager instance
     *
     * @return Manager
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * Get session storage object
     *
     * Proxies to ManagerInterface::getStorage()
     *
     * @return Storage
     */
    protected function getStorage()
    {
        return $this->getManager()->getStorage();
    }

    /**
     * Create a new container object on which to act
     *
     * @return ArrayObject
     */
    protected function createContainer()
    {
        return new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Verify container namespace
     *
     * Checks to see if a container exists within the Storage object already.
     * If not, one is created; if so, checks to see if it's an ArrayObject.
     * If not, it raises an exception; otherwise, it returns the Storage
     * object.
     *
     * @param  bool                       $createContainer Whether or not to create the container for the namespace
     * @return Storage|null               Returns null only if $createContainer is false
     * @throws Exception\RuntimeException
     */
    protected function verifyNamespace($createContainer = true)
    {
        $storage = $this->getStorage();
        $name    = $this->getName();
        if (!isset($storage[$name])) {
            if (!$createContainer) {
                return;
            }
            $storage[$name] = $this->createContainer();
        }
        if (!is_array($storage[$name]) && !$storage[$name] instanceof Traversable) {
            throw new Exception\RuntimeException('Container cannot write to storage due to type mismatch');
        }

        return $storage;
    }

    /**
     * Determine whether a given key needs to be expired
     *
     * Returns true if the key has expired, false otherwise.
     *
     * @param  null|string $key
     * @return bool
     */
    protected function expireKeys($key = null)
    {
        $storage = $this->verifyNamespace();
        $name    = $this->getName();

        // Return early if key not found
        if ((null !== $key) && !isset($storage[$name][$key])) {
            return true;
        }

        if ($this->expireByExpiryTime($storage, $name, $key)) {
            return true;
        }

        if ($this->expireByHops($storage, $name, $key)) {
            return true;
        }

        return false;
    }

    /**
     * Expire a key by expiry time
     *
     * Checks to see if the entire container has expired based on TTL setting,
     * or the individual key.
     *
     * @param  Storage $storage
     * @param  string  $name    Container name
     * @param  string  $key     Key in container to check
     * @return bool
     */
    protected function expireByExpiryTime(Storage $storage, $name, $key)
    {
        $metadata = $storage->getMetadata($name);

        // Global container expiry
        if (is_array($metadata)
            && isset($metadata['EXPIRE'])
            && ($_SERVER['REQUEST_TIME'] > $metadata['EXPIRE'])
        ) {
            unset($metadata['EXPIRE']);
            $storage->setMetadata($name, $metadata, true);
            $storage[$name] = $this->createContainer();

            return true;
        }

        // Expire individual key
        if ((null !== $key)
            && is_array($metadata)
            && isset($metadata['EXPIRE_KEYS'])
            && isset($metadata['EXPIRE_KEYS'][$key])
            && ($_SERVER['REQUEST_TIME'] > $metadata['EXPIRE_KEYS'][$key])
        ) {
            unset($metadata['EXPIRE_KEYS'][$key]);
            $storage->setMetadata($name, $metadata, true);
            unset($storage[$name][$key]);

            return true;
        }

        // Find any keys that have expired
        if ((null === $key)
            && is_array($metadata)
            && isset($metadata['EXPIRE_KEYS'])
        ) {
            foreach (array_keys($metadata['EXPIRE_KEYS']) as $key) {
                if ($_SERVER['REQUEST_TIME'] > $metadata['EXPIRE_KEYS'][$key]) {
                    unset($metadata['EXPIRE_KEYS'][$key]);
                    if (isset($storage[$name][$key])) {
                        unset($storage[$name][$key]);
                    }
                }
            }
            $storage->setMetadata($name, $metadata, true);

            return true;
        }

        return false;
    }

    /**
     * Expire key by session hops
     *
     * Determines whether the container or an individual key within it has
     * expired based on session hops
     *
     * @param  Storage $storage
     * @param  string  $name
     * @param  string  $key
     * @return bool
     */
    protected function expireByHops(Storage $storage, $name, $key)
    {
        $ts       = $storage->getRequestAccessTime();
        $metadata = $storage->getMetadata($name);

        // Global container expiry
        if (is_array($metadata)
            && isset($metadata['EXPIRE_HOPS'])
            && ($ts > $metadata['EXPIRE_HOPS']['ts'])
        ) {
            $metadata['EXPIRE_HOPS']['hops']--;
            if (-1 === $metadata['EXPIRE_HOPS']['hops']) {
                unset($metadata['EXPIRE_HOPS']);
                $storage->setMetadata($name, $metadata, true);
                $storage[$name] = $this->createContainer();

                return true;
            }
            $metadata['EXPIRE_HOPS']['ts'] = $ts;
            $storage->setMetadata($name, $metadata, true);

            return false;
        }

        // Single key expiry
        if ((null !== $key)
            && is_array($metadata)
            && isset($metadata['EXPIRE_HOPS_KEYS'])
            && isset($metadata['EXPIRE_HOPS_KEYS'][$key])
            && ($ts > $metadata['EXPIRE_HOPS_KEYS'][$key]['ts'])
        ) {
            $metadata['EXPIRE_HOPS_KEYS'][$key]['hops']--;
            if (-1 === $metadata['EXPIRE_HOPS_KEYS'][$key]['hops']) {
                unset($metadata['EXPIRE_HOPS_KEYS'][$key]);
                $storage->setMetadata($name, $metadata, true);
                unset($storage[$name][$key]);

                return true;
            }
            $metadata['EXPIRE_HOPS_KEYS'][$key]['ts'] = $ts;
            $storage->setMetadata($name, $metadata, true);

            return false;
        }

        // Find all expired keys
        if ((null === $key)
            && is_array($metadata)
            && isset($metadata['EXPIRE_HOPS_KEYS'])
        ) {
            foreach (array_keys($metadata['EXPIRE_HOPS_KEYS']) as $key) {
                if ($ts > $metadata['EXPIRE_HOPS_KEYS'][$key]['ts']) {
                    $metadata['EXPIRE_HOPS_KEYS'][$key]['hops']--;
                    if (-1 === $metadata['EXPIRE_HOPS_KEYS'][$key]['hops']) {
                        unset($metadata['EXPIRE_HOPS_KEYS'][$key]);
                        $storage->setMetadata($name, $metadata, true);
                        unset($storage[$name][$key]);
                        continue;
                    }
                    $metadata['EXPIRE_HOPS_KEYS'][$key]['ts'] = $ts;
                }
            }
            $storage->setMetadata($name, $metadata, true);

            return false;
        }

        return false;
    }

    /**
     * Store a value within the container
     *
     * @param  string $key
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->expireKeys($key);
        $storage = $this->verifyNamespace();
        $name    = $this->getName();
        $storage[$name][$key] = $value;
    }

    /**
     * Determine if the key exists
     *
     * @param  string $key
     * @return bool
     */
    public function offsetExists($key)
    {
        // If no container exists, we can't inspect it
        if (null === ($storage = $this->verifyNamespace(false))) {
            return false;
        }
        $name = $this->getName();

        // Return early if the key isn't set
        if (!isset($storage[$name][$key])) {
            return false;
        }

        $expired = $this->expireKeys($key);

        return !$expired;
    }

    /**
     * Retrieve a specific key in the container
     *
     * @param  string $key
     * @return mixed
     */
    public function &offsetGet($key)
    {
        if (!$this->offsetExists($key)) {
            return;
        }
        $storage = $this->getStorage();
        $name = $this->getName();

        return $storage[$name][$key];
    }

    /**
     * Unset a single key in the container
     *
     * @param  string $key
     * @return void
     */
    public function offsetUnset($key)
    {
        if (!$this->offsetExists($key)) {
            return;
        }
        $storage = $this->getStorage();
        $name    = $this->getName();
        unset($storage[$name][$key]);
    }

    /**
     * Exchange the current array with another array or object.
     *
     * @param  array|object $input
     * @return array        Returns the old array
     * @see ArrayObject::exchangeArray()
     */
    public function exchangeArray($input)
    {
        // handle arrayobject, iterators and the like:
        if (is_object($input) && ($input instanceof ArrayObject || $input instanceof \ArrayObject)) {
            $input = $input->getArrayCopy();
        }
        if (!is_array($input)) {
            $input = (array) $input;
        }

        $storage = $this->verifyNamespace();
        $name    = $this->getName();

        $old = $storage[$name];
        $storage[$name] = $input;
        if ($old instanceof ArrayObject) {
            return $old->getArrayCopy();
        }

        return $old;
    }

    /**
     * Iterate over session container
     *
     * @return Iterator
     */
    public function getIterator()
    {
        $this->expireKeys();
        $storage   = $this->getStorage();
        $container = $storage[$this->getName()];

        if ($container instanceof Traversable) {
            return $container;
        }

        return new ArrayIterator($container);
    }

    /**
     * Set expiration TTL
     *
     * Set the TTL for the entire container, a single key, or a set of keys.
     *
     * @param  int                                $ttl  TTL in seconds
     * @param  string|array|null                  $vars
     * @return Container
     * @throws Exception\InvalidArgumentException
     */
    public function setExpirationSeconds($ttl, $vars = null)
    {
        $storage = $this->getStorage();
        $ts      = $_SERVER['REQUEST_TIME'] + $ttl;
        if (is_scalar($vars) && null !== $vars) {
            $vars = (array) $vars;
        }

        if (null === $vars) {
            $this->expireKeys(); // first we need to expire global key, since it can already be expired
            $data = array('EXPIRE' => $ts);
        } elseif (is_array($vars)) {
            // Cannot pass "$this" to a lambda
            $container = $this;

            // Filter out any items not in our container
            $expires   = array_filter($vars, function ($value) use ($container) {
                return $container->offsetExists($value);
            });

            // Map item keys => timestamp
            $expires   = array_flip($expires);
            $expires   = array_map(function () use ($ts) {
                return $ts;
            }, $expires);

            // Create metadata array to merge in
            $data = array('EXPIRE_KEYS' => $expires);
        } else {
            throw new Exception\InvalidArgumentException(
                'Unknown data provided as second argument to ' . __METHOD__
            );
        }

        $storage->setMetadata(
            $this->getName(),
            $data
        );

        return $this;
    }

    /**
     * Set expiration hops for the container, a single key, or set of keys
     *
     * @param  int                                $hops
     * @param  null|string|array                  $vars
     * @throws Exception\InvalidArgumentException
     * @return Container
     */
    public function setExpirationHops($hops, $vars = null)
    {
        $storage = $this->getStorage();
        $ts      = $storage->getRequestAccessTime();

        if (is_scalar($vars) && (null !== $vars)) {
            $vars = (array) $vars;
        }

        if (null === $vars) {
            $this->expireKeys(); // first we need to expire global key, since it can already be expired
            $data = array('EXPIRE_HOPS' => array('hops' => $hops, 'ts' => $ts));
        } elseif (is_array($vars)) {
            // Cannot pass "$this" to a lambda
            $container = $this;

            // FilterInterface out any items not in our container
            $expires   = array_filter($vars, function ($value) use ($container) {
                return $container->offsetExists($value);
            });

            // Map item keys => timestamp
            $expires   = array_flip($expires);
            $expires   = array_map(function () use ($hops, $ts) {
                return array('hops' => $hops, 'ts' => $ts);
            }, $expires);

            // Create metadata array to merge in
            $data = array('EXPIRE_HOPS_KEYS' => $expires);
        } else {
            throw new Exception\InvalidArgumentException(
                'Unknown data provided as second argument to ' . __METHOD__
            );
        }

        $storage->setMetadata(
            $this->getName(),
            $data
        );

        return $this;
    }

    /**
     * Creates a copy of the specific container name
     *
     * @return array
     */
    public function getArrayCopy()
    {
        $storage   = $this->verifyNamespace();
        $container = $storage[$this->getName()];

        return $container instanceof ArrayObject ? $container->getArrayCopy() : $container;
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Session;

/**
 * Session storage container
 *
 * Allows for interacting with session storage in isolated containers, which
 * may have their own expiries, or even expiries per key in the container.
 * Additionally, expiries may be absolute TTLs or measured in "hops", which
 * are based on how many times the key or container were accessed.
 */
class Container extends AbstractContainer
{
    public function __serialize() {$this->serialize();}
    public function __unserialize($data) {$this->unserialize($data);}
    /**
     * Retrieve a specific key in the container
     *
     * @param  string $key
     * @return mixed
     */
    public function &offsetGet($key)
    {
        $ret = null;
        if (!$this->offsetExists($key)) {
            return $ret;
        }
        $storage = $this->getStorage();
        $name    = $this->getName();
        $ret =& $storage[$name][$key];

        return $ret;
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Authentication;

/**
 * Provides an API for authentication and identity management
 */
interface AuthenticationServiceInterface
{
    /**
     * Authenticates and provides an authentication result
     *
     * @return Result
     */
    public function authenticate();

    /**
     * Returns true if and only if an identity is available
     *
     * @return bool
     */
    public function hasIdentity();

    /**
     * Returns the authenticated identity or null if no identity is available
     *
     * @return mixed|null
     */
    public function getIdentity();

    /**
     * Clears the identity
     *
     * @return void
     */
    public function clearIdentity();
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Authentication;

class AuthenticationService implements AuthenticationServiceInterface
{
    /**
     * Persistent storage handler
     *
     * @var Storage\StorageInterface
     */
    protected $storage = null;

    /**
     * Authentication adapter
     *
     * @var Adapter\AdapterInterface
     */
    protected $adapter = null;

    /**
     * Constructor
     *
     * @param  Storage\StorageInterface $storage
     * @param  Adapter\AdapterInterface $adapter
     */
    public function __construct(?Storage\StorageInterface $storage = null, ?Adapter\AdapterInterface $adapter = null)
    {
        if (null !== $storage) {
            $this->setStorage($storage);
        }
        if (null !== $adapter) {
            $this->setAdapter($adapter);
        }
    }

    /**
     * Returns the authentication adapter
     *
     * The adapter does not have a default if the storage adapter has not been set.
     *
     * @return Adapter\AdapterInterface|null
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Sets the authentication adapter
     *
     * @param  Adapter\AdapterInterface $adapter
     * @return AuthenticationService Provides a fluent interface
     */
    public function setAdapter(Adapter\AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }

    /**
     * Returns the persistent storage handler
     *
     * Session storage is used by default unless a different storage adapter has been set.
     *
     * @return Storage\StorageInterface
     */
    public function getStorage()
    {
        if (null === $this->storage) {
            $this->setStorage(new Storage\Session());
        }

        return $this->storage;
    }

    /**
     * Sets the persistent storage handler
     *
     * @param  Storage\StorageInterface $storage
     * @return AuthenticationService Provides a fluent interface
     */
    public function setStorage(Storage\StorageInterface $storage)
    {
        $this->storage = $storage;
        return $this;
    }

    /**
     * Authenticates against the supplied adapter
     *
     * @param  Adapter\AdapterInterface $adapter
     * @return Result
     * @throws Exception\RuntimeException
     */
    public function authenticate(?Adapter\AdapterInterface $adapter = null)
    {
        if (!$adapter) {
            if (!$adapter = $this->getAdapter()) {
                throw new Exception\RuntimeException('An adapter must be set or passed prior to calling authenticate()');
            }
        }
        $result = $adapter->authenticate();

        /**
         * ZF-7546 - prevent multiple successive calls from storing inconsistent results
         * Ensure storage has clean state
         */
        if ($this->hasIdentity()) {
            $this->clearIdentity();
        }

        if ($result->isValid()) {
            $this->getStorage()->write($result->getIdentity());
        }

        return $result;
    }

    /**
     * Returns true if and only if an identity is available from storage
     *
     * @return bool
     */
    public function hasIdentity()
    {
        return !$this->getStorage()->isEmpty();
    }

    /**
     * Returns the identity from storage or null if no identity is available
     *
     * @return mixed|null
     */
    public function getIdentity()
    {
        $storage = $this->getStorage();

        if ($storage->isEmpty()) {
            return;
        }

        return $storage->read();
    }

    /**
     * Clears the identity from persistent storage
     *
     * @return void
     */
    public function clearIdentity()
    {
        $this->getStorage()->clear();
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Authentication\Storage;

interface StorageInterface
{
    /**
     * Returns true if and only if storage is empty
     *
     * @throws \Zend\Authentication\Exception\ExceptionInterface If it is impossible to determine whether storage is empty
     * @return bool
     */
    public function isEmpty();

    /**
     * Returns the contents of storage
     *
     * Behavior is undefined when storage is empty.
     *
     * @throws \Zend\Authentication\Exception\ExceptionInterface If reading contents from storage is impossible
     * @return mixed
     */
    public function read();

    /**
     * Writes $contents to storage
     *
     * @param  mixed $contents
     * @throws \Zend\Authentication\Exception\ExceptionInterface If writing $contents to storage is impossible
     * @return void
     */
    public function write($contents);

    /**
     * Clears contents from storage
     *
     * @throws \Zend\Authentication\Exception\ExceptionInterface If clearing contents from storage is impossible
     * @return void
     */
    public function clear();
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Authentication\Storage;

use Zend\Session\Container as SessionContainer;
use Zend\Session\ManagerInterface as SessionManager;

class Session implements StorageInterface
{
    /**
     * Default session namespace
     */
    const NAMESPACE_DEFAULT = 'Zend_Auth';

    /**
     * Default session object member name
     */
    const MEMBER_DEFAULT = 'storage';

    /**
     * Object to proxy $_SESSION storage
     *
     * @var SessionContainer
     */
    protected $session;

    /**
     * Session namespace
     *
     * @var mixed
     */
    protected $namespace = self::NAMESPACE_DEFAULT;

    /**
     * Session object member
     *
     * @var mixed
     */
    protected $member = self::MEMBER_DEFAULT;

    /**
     * Sets session storage options and initializes session namespace object
     *
     * @param  mixed $namespace
     * @param  mixed $member
     * @param  SessionManager $manager
     */
    public function __construct($namespace = null, $member = null, ?SessionManager $manager = null)
    {
        if ($namespace !== null) {
            $this->namespace = $namespace;
        }
        if ($member !== null) {
            $this->member = $member;
        }
        $this->session   = new SessionContainer($this->namespace, $manager);
    }

    /**
     * Returns the session namespace
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Returns the name of the session object member
     *
     * @return string
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * Defined by Zend\Authentication\Storage\StorageInterface
     *
     * @return bool
     */
    public function isEmpty()
    {
        return !isset($this->session->{$this->member});
    }

    /**
     * Defined by Zend\Authentication\Storage\StorageInterface
     *
     * @return mixed
     */
    public function read()
    {
        return $this->session->{$this->member};
    }

    /**
     * Defined by Zend\Authentication\Storage\StorageInterface
     *
     * @param  mixed $contents
     * @return void
     */
    public function write($contents)
    {
        $this->session->{$this->member} = $contents;
    }

    /**
     * Defined by Zend\Authentication\Storage\StorageInterface
     *
     * @return void
     */
    public function clear()
    {
        unset($this->session->{$this->member});
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Authentication;

class Result
{
    /**
     * General Failure
     */
    const FAILURE                        =  0;

    /**
     * Failure due to identity not being found.
     */
    const FAILURE_IDENTITY_NOT_FOUND     = -1;

    /**
     * Failure due to identity being ambiguous.
     */
    const FAILURE_IDENTITY_AMBIGUOUS     = -2;

    /**
     * Failure due to invalid credential being supplied.
     */
    const FAILURE_CREDENTIAL_INVALID     = -3;

    /**
     * Failure due to uncategorized reasons.
     */
    const FAILURE_UNCATEGORIZED          = -4;

    /**
     * Authentication success.
     */
    const SUCCESS                        =  1;

    /**
     * Authentication result code
     *
     * @var int
     */
    protected $code;

    /**
     * The identity used in the authentication attempt
     *
     * @var mixed
     */
    protected $identity;

    /**
     * An array of string reasons why the authentication attempt was unsuccessful
     *
     * If authentication was successful, this should be an empty array.
     *
     * @var array
     */
    protected $messages;

    /**
     * Sets the result code, identity, and failure messages
     *
     * @param  int     $code
     * @param  mixed   $identity
     * @param  array   $messages
     */
    public function __construct($code, $identity, array $messages = [])
    {
        $this->code     = (int) $code;
        $this->identity = $identity;
        $this->messages = $messages;
    }

    /**
     * Returns whether the result represents a successful authentication attempt
     *
     * @return bool
     */
    public function isValid()
    {
        return ($this->code > 0);
    }

    /**
     * getCode() - Get the result code for this authentication attempt
     *
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Returns the identity used in the authentication attempt
     *
     * @return mixed
     */
    public function getIdentity()
    {
        return $this->identity;
    }

    /**
     * Returns an array of string reasons why the authentication attempt was unsuccessful
     *
     * If authentication was successful, this method returns an empty array.
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Zend\Session;

use Zend\EventManager\EventManager;
use Zend\Session\Storage\StorageInterface as Storage;
use Zend\Session\Validator\ValidatorInterface as Validator;

/**
 * Validator chain for validating sessions
 */
class ValidatorChain extends EventManager
{
    /**
     * @var Storage
     */
    protected $storage;

    /**
     * Construct the validation chain
     *
     * Retrieves validators from session storage and attaches them.
     *
     * @param Storage $storage
     */
    public function __construct(Storage $storage)
    {
        $this->storage = $storage;

        $validators = $storage->getMetadata('_VALID');
        if ($validators) {
            foreach ($validators as $validator => $data) {
                $this->attach('session.validate', array(new $validator($data), 'isValid'));
            }
        }
    }

    /**
     * Attach a listener to the session validator chain
     *
     * @param  string $event
     * @param  callable $callback
     * @param  int $priority
     * @return \Zend\Stdlib\CallbackHandler
     */
    public function attach($event, $callback = null, $priority = 1)
    {
        $context = null;
        if ($callback instanceof Validator) {
            $context = $callback;
        } elseif (is_array($callback)) {
            $test = array_shift($callback);
            if ($test instanceof Validator) {
                $context = $test;
            }
            array_unshift($callback, $test);
        }
        if ($context instanceof Validator) {
            $data = $context->getData();
            $name = $context->getName();
            $this->getStorage()->setMetadata('_VALID', array($name => $data));
        }

        $listener = parent::attach($event, $callback, $priority);
        return $listener;
    }

    /**
     * Retrieve session storage object
     *
     * @return Storage
     */
    public function getStorage()
    {
        return $this->storage;
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Exception;

interface ExceptionInterface
{
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Exception;

class ErrorException extends \Exception implements ExceptionInterface
{
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Exception;

class RuntimeException extends \RuntimeException implements ExceptionInterface
{
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Exception;

class UnexpectedValueException extends \UnexpectedValueException implements ExceptionInterface
{
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Exception;

class InvalidArgumentException extends \InvalidArgumentException implements ExceptionInterface
{
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Adapter\Exception;

use Zend\Db\Exception;

interface ExceptionInterface extends Exception\ExceptionInterface
{
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Adapter\Exception;

use Zend\Db\Exception;

class ErrorException extends Exception\ErrorException implements ExceptionInterface
{
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Adapter\Exception;

use Zend\Db\Exception;

class RuntimeException extends Exception\RuntimeException implements ExceptionInterface
{
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Adapter\Exception;

use Zend\Db\Exception;

class UnexpectedValueException extends Exception\UnexpectedValueException implements ExceptionInterface
{
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Adapter\Exception;

class InvalidQueryException extends UnexpectedValueException implements ExceptionInterface
{
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Adapter;

use ArrayAccess;
use Countable;
use Iterator;

class ParameterContainer implements Iterator, ArrayAccess, Countable
{
    const TYPE_AUTO    = 'auto';
    const TYPE_NULL    = 'null';
    const TYPE_DOUBLE  = 'double';
    const TYPE_INTEGER = 'integer';
    const TYPE_BINARY  = 'binary';
    const TYPE_STRING  = 'string';
    const TYPE_LOB     = 'lob';

    /**
     * Data
     *
     * @var array
     */
    protected $data = array();

    /**
     * @var array
     */
    protected $positions = array();

    /**
     * Errata
     *
     * @var array
     */
    protected $errata = array();

    /**
     * Max length
     *
     * @var array
     */
    protected $maxLength = array();

    /**
     * Constructor
     *
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        if ($data) {
            $this->setFromArray($data);
        }
    }

    /**
     * Offset exists
     *
     * @param  string $name
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($name)
    {
        return (isset($this->data[$name]));
    }

    /**
     * Offset get
     *
     * @param  string $name
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($name)
    {
        return (isset($this->data[$name])) ? $this->data[$name] : null;
    }

    /**
     * @param $name
     * @param $from
     */
    public function offsetSetReference($name, $from)
    {
        $this->data[$name] =& $this->data[$from];
    }

    /**
     * Offset set
     *
     * @param string|int $name
     * @param mixed $value
     * @param mixed $errata
     * @param mixed $maxLength
     * @throws Exception\InvalidArgumentException
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($name, $value, $errata = null, $maxLength = null)
    {
        $position = false;

        // if integer, get name for this position
        if (is_int($name)) {
            if (isset($this->positions[$name])) {
                $position = $name;
                $name = $this->positions[$name];
            } else {
                $name = (string) $name;
            }
        } elseif (is_string($name)) {
            // is a string:
            $position = array_key_exists($name, $this->data);
        } elseif ($name === null) {
            $name = (string) count($this->data);
        } else {
            throw new Exception\InvalidArgumentException('Keys must be string, integer or null');
        }

        if ($position === false) {
            $this->positions[] = $name;
        }

        $this->data[$name] = $value;

        if ($errata) {
            $this->offsetSetErrata($name, $errata);
        }

        if ($maxLength) {
            $this->offsetSetMaxLength($name, $maxLength);
        }
    }

    /**
     * Offset unset
     *
     * @param  string $name
     * @return ParameterContainer
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($name)
    {
        if (is_int($name) && isset($this->positions[$name])) {
            $name = $this->positions[$name];
        }
        unset($this->data[$name]);
        return $this;
    }

    /**
     * Set from array
     *
     * @param  array $data
     * @return ParameterContainer
     */
    public function setFromArray(Array $data)
    {
        foreach ($data as $n => $v) {
            $this->offsetSet($n, $v);
        }
        return $this;
    }

    /**
     * Offset set max length
     *
     * @param string|int $name
     * @param mixed $maxLength
     */
    public function offsetSetMaxLength($name, $maxLength)
    {
        if (is_int($name)) {
            $name = $this->positions[$name];
        }
        $this->maxLength[$name] = $maxLength;
    }

    /**
     * Offset get max length
     *
     * @param  string|int $name
     * @throws Exception\InvalidArgumentException
     * @return mixed
     */
    public function offsetGetMaxLength($name)
    {
        if (is_int($name)) {
            $name = $this->positions[$name];
        }
        if (!array_key_exists($name, $this->data)) {
            throw new Exception\InvalidArgumentException('Data does not exist for this name/position');
        }
        return $this->maxLength[$name];
    }

    /**
     * Offset has max length
     *
     * @param  string|int $name
     * @return bool
     */
    public function offsetHasMaxLength($name)
    {
        if (is_int($name)) {
            $name = $this->positions[$name];
        }
        return (isset($this->maxLength[$name]));
    }

    /**
     * Offset unset max length
     *
     * @param string|int $name
     * @throws Exception\InvalidArgumentException
     */
    public function offsetUnsetMaxLength($name)
    {
        if (is_int($name)) {
            $name = $this->positions[$name];
        }
        if (!array_key_exists($name, $this->maxLength)) {
            throw new Exception\InvalidArgumentException('Data does not exist for this name/position');
        }
        $this->maxLength[$name] = null;
    }

    /**
     * Get max length iterator
     *
     * @return \ArrayIterator
     */
    public function getMaxLengthIterator()
    {
        return new \ArrayIterator($this->maxLength);
    }

    /**
     * Offset set errata
     *
     * @param string|int $name
     * @param mixed $errata
     */
    public function offsetSetErrata($name, $errata)
    {
        if (is_int($name)) {
            $name = $this->positions[$name];
        }
        $this->errata[$name] = $errata;
    }

    /**
     * Offset get errata
     *
     * @param  string|int $name
     * @throws Exception\InvalidArgumentException
     * @return mixed
     */
    public function offsetGetErrata($name)
    {
        if (is_int($name)) {
            $name = $this->positions[$name];
        }
        if (!array_key_exists($name, $this->data)) {
            throw new Exception\InvalidArgumentException('Data does not exist for this name/position');
        }
        return $this->errata[$name];
    }

    /**
     * Offset has errata
     *
     * @param  string|int $name
     * @return bool
     */
    public function offsetHasErrata($name)
    {
        if (is_int($name)) {
            $name = $this->positions[$name];
        }
        return (isset($this->errata[$name]));
    }

    /**
     * Offset unset errata
     *
     * @param string|int $name
     * @throws Exception\InvalidArgumentException
     */
    public function offsetUnsetErrata($name)
    {
        if (is_int($name)) {
            $name = $this->positions[$name];
        }
        if (!array_key_exists($name, $this->errata)) {
            throw new Exception\InvalidArgumentException('Data does not exist for this name/position');
        }
        $this->errata[$name] = null;
    }

    /**
     * Get errata iterator
     *
     * @return \ArrayIterator
     */
    public function getErrataIterator()
    {
        return new \ArrayIterator($this->errata);
    }

    /**
     * getNamedArray
     *
     * @return array
     */
    public function getNamedArray()
    {
        return $this->data;
    }

    /**
     * getNamedArray
     *
     * @return array
     */
    public function getPositionalArray()
    {
        return array_values($this->data);
    }

    /**
     * count
     *
     * @return int
     */
    #[\ReturnTypeWillChange]
    public function count()
    {
        return count($this->data);
    }

    /**
     * Current
     *
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return current($this->data);
    }

    /**
     * Next
     *
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function next()
    {
        return next($this->data);
    }

    /**
     * Key
     *
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        return key($this->data);
    }

    /**
     * Valid
     *
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function valid()
    {
        return (current($this->data) !== false);
    }

    /**
     * Rewind
     */
    #[\ReturnTypeWillChange]
    public function rewind()
    {
        reset($this->data);
    }

    /**
     * @param array|ParameterContainer $parameters
     * @throws Exception\InvalidArgumentException
     * @return ParameterContainer
     */
    public function merge($parameters)
    {
        if (!is_array($parameters) && !$parameters instanceof ParameterContainer) {
            throw new Exception\InvalidArgumentException('$parameters must be an array or an instance of ParameterContainer');
        }

        if (count($parameters) == 0) {
            return $this;
        }

        if ($parameters instanceof ParameterContainer) {
            $parameters = $parameters->getNamedArray();
        }

        foreach ($parameters as $key => $value) {
            if (is_int($key)) {
                $key = null;
            }
            $this->offsetSet($key, $value);
        }
        return $this;
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Adapter;

class StatementContainer implements StatementContainerInterface
{
    /**
     * @var string
     */
    protected $sql = '';

    /**
     * @var ParameterContainer
     */
    protected $parameterContainer = null;

    /**
     * @param string|null $sql
     * @param ParameterContainer|null $parameterContainer
     */
    public function __construct($sql = null, ?ParameterContainer $parameterContainer = null)
    {
        if ($sql) {
            $this->setSql($sql);
        }
        $this->parameterContainer = ($parameterContainer) ?: new ParameterContainer;
    }

    /**
     * @param $sql
     * @return StatementContainer
     */
    public function setSql($sql)
    {
        $this->sql = $sql;
        return $this;
    }

    /**
     * @return string
     */
    public function getSql()
    {
        return $this->sql;
    }

    /**
     * @param ParameterContainer $parameterContainer
     * @return StatementContainer
     */
    public function setParameterContainer(ParameterContainer $parameterContainer)
    {
        $this->parameterContainer = $parameterContainer;
        return $this;
    }

    /**
     * @return null|ParameterContainer
     */
    public function getParameterContainer()
    {
        return $this->parameterContainer;
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\TableGateway;

interface TableGatewayInterface
{
    public function getTable();
    public function select($where = null);
    public function insert($set);
    public function update($set, $where = null);
    public function delete($where);
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\TableGateway;

use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\ResultSet\ResultSetInterface;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\TableIdentifier;
use Zend\Db\Sql\Update;
use Zend\Db\Sql\Where;
use Zend\Db\TableGateway\Feature\EventFeature;

/**
 *
 * @property AdapterInterface $adapter
 * @property int $lastInsertValue
 * @property string $table
 */
abstract class AbstractTableGateway implements TableGatewayInterface
{
    /**
     * @var bool
     */
    protected $isInitialized = false;

    /**
     * @var AdapterInterface
     */
    protected $adapter = null;

    /**
     * @var string|array|TableIdentifier
     */
    protected $table = null;

    /**
     * @var array
     */
    protected $columns = array();

    /**
     * @var Feature\FeatureSet
     */
    protected $featureSet = null;

    /**
     * @var ResultSetInterface
     */
    protected $resultSetPrototype = null;

    /**
     * @var Sql
     */
    protected $sql = null;

    /**
     *
     * @var int
     */
    protected $lastInsertValue = null;

    /**
     * @return bool
     */
    public function isInitialized()
    {
        return $this->isInitialized;
    }

    /**
     * Initialize
     *
     * @throws Exception\RuntimeException
     * @return null
     */
    public function initialize()
    {
        if ($this->isInitialized) {
            return;
        }

        if (!$this->featureSet instanceof Feature\FeatureSet) {
            $this->featureSet = new Feature\FeatureSet;
        }

        $this->featureSet->setTableGateway($this);
        $this->featureSet->apply(EventFeature::EVENT_PRE_INITIALIZE, array());

        if (!$this->adapter instanceof AdapterInterface) {
            throw new Exception\RuntimeException('This table does not have an Adapter setup');
        }

        if (!is_string($this->table) && !$this->table instanceof TableIdentifier && !is_array($this->table)) {
            throw new Exception\RuntimeException('This table object does not have a valid table set.');
        }

        if (!$this->resultSetPrototype instanceof ResultSetInterface) {
            $this->resultSetPrototype = new ResultSet;
        }

        if (!$this->sql instanceof Sql) {
            $this->sql = new Sql($this->adapter, $this->table);
        }

        $this->featureSet->apply(EventFeature::EVENT_POST_INITIALIZE, array());

        $this->isInitialized = true;
    }

    /**
     * Get table name
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Get adapter
     *
     * @return AdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @return Feature\FeatureSet
     */
    public function getFeatureSet()
    {
        return $this->featureSet;
    }

    /**
     * Get select result prototype
     *
     * @return ResultSet
     */
    public function getResultSetPrototype()
    {
        return $this->resultSetPrototype;
    }

    /**
     * @return Sql
     */
    public function getSql()
    {
        return $this->sql;
    }

    /**
     * Select
     *
     * @param Where|\Closure|string|array $where
     * @return ResultSet
     */
    public function select($where = null)
    {
        if (!$this->isInitialized) {
            $this->initialize();
        }

        $select = $this->sql->select();

        if ($where instanceof \Closure) {
            $where($select);
        } elseif ($where !== null) {
            $select->where($where);
        }

        return $this->selectWith($select);
    }

    /**
     * @param Select $select
     * @return null|ResultSetInterface
     * @throws \RuntimeException
     */
    public function selectWith(Select $select)
    {
        if (!$this->isInitialized) {
            $this->initialize();
        }
        return $this->executeSelect($select);
    }

    /**
     * @param Select $select
     * @return ResultSet
     * @throws Exception\RuntimeException
     */
    protected function executeSelect(Select $select)
    {
        $selectState = $select->getRawState();
        if ($selectState['table'] != $this->table
            && (is_array($selectState['table'])
                && end($selectState['table']) != $this->table)
        ) {
            throw new Exception\RuntimeException(
                'The table name of the provided select object must match that of the table'
            );
        }

        if ($selectState['columns'] == array(Select::SQL_STAR)
            && $this->columns !== array()) {
            $select->columns($this->columns);
        }

        // apply preSelect features
        $this->featureSet->apply(EventFeature::EVENT_PRE_SELECT, array($select));

        // prepare and execute
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        // build result set
        $resultSet = clone $this->resultSetPrototype;
        $resultSet->initialize($result);

        // apply postSelect features
        $this->featureSet->apply(EventFeature::EVENT_POST_SELECT, array($statement, $result, $resultSet));

        return $resultSet;
    }

    /**
     * Insert
     *
     * @param  array $set
     * @return int
     */
    public function insert($set)
    {
        if (!$this->isInitialized) {
            $this->initialize();
        }
        $insert = $this->sql->insert();
        $insert->values($set);
        return $this->executeInsert($insert);
    }

    /**
     * @param Insert $insert
     * @return mixed
     */
    public function insertWith(Insert $insert)
    {
        if (!$this->isInitialized) {
            $this->initialize();
        }
        return $this->executeInsert($insert);
    }

    /**
     * @todo add $columns support
     *
     * @param Insert $insert
     * @return mixed
     * @throws Exception\RuntimeException
     */
    protected function executeInsert(Insert $insert)
    {
        $insertState = $insert->getRawState();
        if ($insertState['table'] != $this->table) {
            throw new Exception\RuntimeException(
                'The table name of the provided Insert object must match that of the table'
            );
        }

        // apply preInsert features
        $this->featureSet->apply(EventFeature::EVENT_PRE_INSERT, array($insert));

        // Most RDBMS solutions do not allow using table aliases in INSERTs
        // See https://github.com/zendframework/zf2/issues/7311
        $unaliasedTable = false;
        if (is_array($insertState['table'])) {
            $tableData      = array_values($insertState['table']);
            $unaliasedTable = array_shift($tableData);
            $insert->into($unaliasedTable);
        }

        $statement = $this->sql->prepareStatementForSqlObject($insert);
        $result = $statement->execute();
        $this->lastInsertValue = $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();

        // apply postInsert features
        $this->featureSet->apply(EventFeature::EVENT_POST_INSERT, array($statement, $result));

        // Reset original table information in Insert instance, if necessary
        if ($unaliasedTable) {
            $insert->into($insertState['table']);
        }

        $return = $result->getAffectedRows();
        return $return;
    }

    /**
     * Update
     *
     * @param  array $set
     * @param  string|array|\Closure $where
     * @return int
     */
    public function update($set, $where = null)
    {
        if (!$this->isInitialized) {
            $this->initialize();
        }
        $sql = $this->sql;
        $update = $sql->update();
        $update->set($set);
        if ($where !== null) {
            $update->where($where);
        }
        return $this->executeUpdate($update);
    }

    /**
     * @param \Zend\Db\Sql\Update $update
     * @return mixed
     */
    public function updateWith(Update $update)
    {
        if (!$this->isInitialized) {
            $this->initialize();
        }
        return $this->executeUpdate($update);
    }

    /**
     * @todo add $columns support
     *
     * @param Update $update
     * @return mixed
     * @throws Exception\RuntimeException
     */
    protected function executeUpdate(Update $update)
    {
        $updateState = $update->getRawState();
        if ($updateState['table'] != $this->table) {
            throw new Exception\RuntimeException(
                'The table name of the provided Update object must match that of the table'
            );
        }

        // apply preUpdate features
        $this->featureSet->apply(EventFeature::EVENT_PRE_UPDATE, array($update));

        $statement = $this->sql->prepareStatementForSqlObject($update);
        $result = $statement->execute();

        // apply postUpdate features
        $this->featureSet->apply(EventFeature::EVENT_POST_UPDATE, array($statement, $result));

        return $result->getAffectedRows();
    }

    /**
     * Delete
     *
     * @param  Where|\Closure|string|array $where
     * @return int
     */
    public function delete($where)
    {
        if (!$this->isInitialized) {
            $this->initialize();
        }
        $delete = $this->sql->delete();
        if ($where instanceof \Closure) {
            $where($delete);
        } else {
            $delete->where($where);
        }
        return $this->executeDelete($delete);
    }

    /**
     * @param Delete $delete
     * @return mixed
     */
    public function deleteWith(Delete $delete)
    {
        $this->initialize();
        return $this->executeDelete($delete);
    }

    /**
     * @todo add $columns support
     *
     * @param Delete $delete
     * @return mixed
     * @throws Exception\RuntimeException
     */
    protected function executeDelete(Delete $delete)
    {
        $deleteState = $delete->getRawState();
        if ($deleteState['table'] != $this->table) {
            throw new Exception\RuntimeException(
                'The table name of the provided Update object must match that of the table'
            );
        }

        // pre delete update
        $this->featureSet->apply(EventFeature::EVENT_PRE_DELETE, array($delete));

        $statement = $this->sql->prepareStatementForSqlObject($delete);
        $result = $statement->execute();

        // apply postDelete features
        $this->featureSet->apply(EventFeature::EVENT_POST_DELETE, array($statement, $result));

        return $result->getAffectedRows();
    }

    /**
     * Get last insert value
     *
     * @return int
     */
    public function getLastInsertValue()
    {
        return $this->lastInsertValue;
    }

    /**
     * __get
     *
     * @param  string $property
     * @throws Exception\InvalidArgumentException
     * @return mixed
     */
    public function __get($property)
    {
        switch (strtolower($property)) {
            case 'lastinsertvalue':
                return $this->lastInsertValue;
            case 'adapter':
                return $this->adapter;
            case 'table':
                return $this->table;
        }
        if ($this->featureSet->canCallMagicGet($property)) {
            return $this->featureSet->callMagicGet($property);
        }
        throw new Exception\InvalidArgumentException('Invalid magic property access in ' . __CLASS__ . '::__get()');
    }

    /**
     * @param string $property
     * @param mixed $value
     * @return mixed
     * @throws Exception\InvalidArgumentException
     */
    public function __set($property, $value)
    {
        if ($this->featureSet->canCallMagicSet($property)) {
            return $this->featureSet->callMagicSet($property, $value);
        }
        throw new Exception\InvalidArgumentException('Invalid magic property access in ' . __CLASS__ . '::__set()');
    }

    /**
     * @param $method
     * @param $arguments
     * @return mixed
     * @throws Exception\InvalidArgumentException
     */
    public function __call($method, $arguments)
    {
        if ($this->featureSet->canCallMagicCall($method)) {
            return $this->featureSet->callMagicCall($method, $arguments);
        }
        throw new Exception\InvalidArgumentException(sprintf(
            'Invalid method (%s) called, caught by %s::__call()',
            $method,
            __CLASS__
        ));
    }

    /**
     * __clone
     */
    public function __clone()
    {
        $this->resultSetPrototype = (isset($this->resultSetPrototype)) ? clone $this->resultSetPrototype : null;
        $this->sql = clone $this->sql;
        if (is_object($this->table)) {
            $this->table = clone $this->table;
        } elseif (
            is_array($this->table)
            && count($this->table) == 1
            && is_object(reset($this->table))
        ) {
            foreach ($this->table as $alias => &$tableObject) {
                $tableObject = clone $tableObject;
            }
        }
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\TableGateway;

use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\ResultSet\ResultSetInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\TableIdentifier;

class TableGateway extends AbstractTableGateway
{
    /**
     * Constructor
     *
     * @param string|TableIdentifier|array                                              $table
     * @param AdapterInterface                                                          $adapter
     * @param Feature\AbstractFeature|Feature\FeatureSet|Feature\AbstractFeature[]|null $features
     * @param ResultSetInterface|null                                                   $resultSetPrototype
     * @param Sql|null                                                                  $sql
     *
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($table, AdapterInterface $adapter, $features = null, ?ResultSetInterface $resultSetPrototype = null, ?Sql $sql = null)
    {
        // table
        if (!(is_string($table) || $table instanceof TableIdentifier || is_array($table))) {
            throw new Exception\InvalidArgumentException('Table name must be a string or an instance of Zend\Db\Sql\TableIdentifier');
        }
        $this->table = $table;

        // adapter
        $this->adapter = $adapter;

        // process features
        if ($features !== null) {
            if ($features instanceof Feature\AbstractFeature) {
                $features = array($features);
            }
            if (is_array($features)) {
                $this->featureSet = new Feature\FeatureSet($features);
            } elseif ($features instanceof Feature\FeatureSet) {
                $this->featureSet = $features;
            } else {
                throw new Exception\InvalidArgumentException(
                    'TableGateway expects $feature to be an instance of an AbstractFeature or a FeatureSet, or an array of AbstractFeatures'
                );
            }
        } else {
            $this->featureSet = new Feature\FeatureSet();
        }

        // result prototype
        $this->resultSetPrototype = ($resultSetPrototype) ?: new ResultSet;

        // Sql object (factory for select, insert, update, delete)
        $this->sql = ($sql) ?: new Sql($this->adapter, $this->table);

        // check sql object bound to same table
        if ($this->sql->getTable() != $this->table) {
            throw new Exception\InvalidArgumentException('The table inside the provided Sql object must match the table of this TableGateway');
        }

        $this->initialize();
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\TableGateway\Feature;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\TableGateway\TableGatewayInterface;

class FeatureSet
{
    const APPLY_HALT = 'halt';

    protected $tableGateway = null;

    /**
     * @var AbstractFeature[]
     */
    protected $features = array();

    /**
     * @var array
     */
    protected $magicSpecifications = array();

    public function __construct(array $features = array())
    {
        if ($features) {
            $this->addFeatures($features);
        }
    }

    public function setTableGateway(AbstractTableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        foreach ($this->features as $feature) {
            $feature->setTableGateway($this->tableGateway);
        }
        return $this;
    }

    public function getFeatureByClassName($featureClassName)
    {
        $feature = false;
        foreach ($this->features as $potentialFeature) {
            if ($potentialFeature instanceof $featureClassName) {
                $feature = $potentialFeature;
                break;
            }
        }
        return $feature;
    }

    public function addFeatures(array $features)
    {
        foreach ($features as $feature) {
            $this->addFeature($feature);
        }
        return $this;
    }

    public function addFeature(AbstractFeature $feature)
    {
        if ($this->tableGateway instanceof TableGatewayInterface) {
            $feature->setTableGateway($this->tableGateway);
        }
        $this->features[] = $feature;
        return $this;
    }

    public function apply($method, $args)
    {
        foreach ($this->features as $feature) {
            if (method_exists($feature, $method)) {
                $return = call_user_func_array(array($feature, $method), $args);
                if ($return === self::APPLY_HALT) {
                    break;
                }
            }
        }
    }

    /**
     * @param string $property
     * @return bool
     */
    public function canCallMagicGet($property)
    {
        return false;
    }

    /**
     * @param string $property
     * @return mixed
     */
    public function callMagicGet($property)
    {
        $return = null;
        return $return;
    }

    /**
     * @param string $property
     * @return bool
     */
    public function canCallMagicSet($property)
    {
        return false;
    }

    /**
     * @param $property
     * @param $value
     * @return mixed
     */
    public function callMagicSet($property, $value)
    {
        $return = null;
        return $return;
    }

    /**
     * @param string $method
     * @return bool
     */
    public function canCallMagicCall($method)
    {
        return false;
    }

    /**
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function callMagicCall($method, $arguments)
    {
        $return = null;
        return $return;
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\TableGateway\Feature;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\TableGateway\Exception;

abstract class AbstractFeature extends AbstractTableGateway
{
    /**
     * @var AbstractTableGateway
     */
    protected $tableGateway = null;

    protected $sharedData = array();

    public function getName()
    {
        return get_class($this);
    }

    public function setTableGateway(AbstractTableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function initialize()
    {
        throw new Exception\RuntimeException('This method is not intended to be called on this object.');
    }

    public function getMagicMethodSpecifications()
    {
        return array();
    }


    /*
    public function preInitialize();
    public function postInitialize();
    public function preSelect(Select $select);
    public function postSelect(StatementInterface $statement, ResultInterface $result, ResultSetInterface $resultSet);
    public function preInsert(Insert $insert);
    public function postInsert(StatementInterface $statement, ResultInterface $result);
    public function preUpdate(Update $update);
    public function postUpdate(StatementInterface $statement, ResultInterface $result);
    public function preDelete(Delete $delete);
    public function postDelete(StatementInterface $statement, ResultInterface $result);
    */
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\TableGateway\Feature;

use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\Adapter\Driver\StatementInterface;
use Zend\Db\ResultSet\ResultSetInterface;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Update;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventsCapableInterface;

class EventFeature extends AbstractFeature implements EventsCapableInterface
{
    const EVENT_PRE_INITIALIZE  = 'preInitialize';
    const EVENT_POST_INITIALIZE = 'postInitialize';

    const EVENT_PRE_SELECT      = 'preSelect';
    const EVENT_POST_SELECT     = 'postSelect';

    const EVENT_PRE_INSERT      = 'preInsert';
    const EVENT_POST_INSERT     = 'postInsert';

    const EVENT_PRE_DELETE      = 'preDelete';
    const EVENT_POST_DELETE     = 'postDelete';

    const EVENT_PRE_UPDATE      = 'preUpdate';
    const EVENT_POST_UPDATE     = 'postUpdate';

    /**
     * @var EventManagerInterface
     */
    protected $eventManager = null;

    /**
     * @var null
     */
    protected $event = null;

    /**
     * @param EventManagerInterface $eventManager
     * @param EventFeature\TableGatewayEvent $tableGatewayEvent
     */
    public function __construct(
        ?EventManagerInterface $eventManager = null,
        ?EventFeature\TableGatewayEvent $tableGatewayEvent = null
    ) {
        $this->eventManager = ($eventManager instanceof EventManagerInterface)
                            ? $eventManager
                            : new EventManager;

        $this->eventManager->addIdentifiers(array(
            'Zend\Db\TableGateway\TableGateway',
        ));

        $this->event = ($tableGatewayEvent) ?: new EventFeature\TableGatewayEvent();
    }

    /**
     * Retrieve composed event manager instance
     *
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }

    /**
     * Retrieve composed event instance
     *
     * @return EventFeature\TableGatewayEvent
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Initialize feature and trigger "preInitialize" event
     *
     * Ensures that the composed TableGateway has identifiers based on the
     * class name, and that the event target is set to the TableGateway
     * instance. It then triggers the "preInitialize" event.
     *
     * @return void
     */
    public function preInitialize()
    {
        if (get_class($this->tableGateway) != 'Zend\Db\TableGateway\TableGateway') {
            $this->eventManager->addIdentifiers(get_class($this->tableGateway));
        }

        $this->event->setTarget($this->tableGateway);
        $this->event->setName(static::EVENT_PRE_INITIALIZE);
        $this->eventManager->trigger($this->event);
    }

    /**
     * Trigger the "postInitialize" event
     *
     * @return void
     */
    public function postInitialize()
    {
        $this->event->setName(static::EVENT_POST_INITIALIZE);
        $this->eventManager->trigger($this->event);
    }

    /**
     * Trigger the "preSelect" event
     *
     * Triggers the "preSelect" event mapping the following parameters:
     * - $select as "select"
     *
     * @param  Select $select
     * @return void
     */
    public function preSelect(Select $select)
    {
        $this->event->setName(static::EVENT_PRE_SELECT);
        $this->event->setParams(array('select' => $select));
        $this->eventManager->trigger($this->event);
    }

    /**
     * Trigger the "postSelect" event
     *
     * Triggers the "postSelect" event mapping the following parameters:
     * - $statement as "statement"
     * - $result as "result"
     * - $resultSet as "result_set"
     *
     * @param  StatementInterface $statement
     * @param  ResultInterface $result
     * @param  ResultSetInterface $resultSet
     * @return void
     */
    public function postSelect(StatementInterface $statement, ResultInterface $result, ResultSetInterface $resultSet)
    {
        $this->event->setName(static::EVENT_POST_SELECT);
        $this->event->setParams(array(
            'statement' => $statement,
            'result' => $result,
            'result_set' => $resultSet
        ));
        $this->eventManager->trigger($this->event);
    }

    /**
     * Trigger the "preInsert" event
     *
     * Triggers the "preInsert" event mapping the following parameters:
     * - $insert as "insert"
     *
     * @param  Insert $insert
     * @return void
     */
    public function preInsert(Insert $insert)
    {
        $this->event->setName(static::EVENT_PRE_INSERT);
        $this->event->setParams(array('insert' => $insert));
        $this->eventManager->trigger($this->event);
    }

    /**
     * Trigger the "postInsert" event
     *
     * Triggers the "postInsert" event mapping the following parameters:
     * - $statement as "statement"
     * - $result as "result"
     *
     * @param  StatementInterface $statement
     * @param  ResultInterface $result
     * @return void
     */
    public function postInsert(StatementInterface $statement, ResultInterface $result)
    {
        $this->event->setName(static::EVENT_POST_INSERT);
        $this->event->setParams(array(
            'statement' => $statement,
            'result' => $result,
        ));
        $this->eventManager->trigger($this->event);
    }

    /**
     * Trigger the "preUpdate" event
     *
     * Triggers the "preUpdate" event mapping the following parameters:
     * - $update as "update"
     *
     * @param  Update $update
     * @return void
     */
    public function preUpdate(Update $update)
    {
        $this->event->setName(static::EVENT_PRE_UPDATE);
        $this->event->setParams(array('update' => $update));
        $this->eventManager->trigger($this->event);
    }

    /**
     * Trigger the "postUpdate" event
     *
     * Triggers the "postUpdate" event mapping the following parameters:
     * - $statement as "statement"
     * - $result as "result"
     *
     * @param  StatementInterface $statement
     * @param  ResultInterface $result
     * @return void
     */
    public function postUpdate(StatementInterface $statement, ResultInterface $result)
    {
        $this->event->setName(static::EVENT_POST_UPDATE);
        $this->event->setParams(array(
            'statement' => $statement,
            'result' => $result,
        ));
        $this->eventManager->trigger($this->event);
    }

    /**
     * Trigger the "preDelete" event
     *
     * Triggers the "preDelete" event mapping the following parameters:
     * - $delete as "delete"
     *
     * @param  Delete $delete
     * @return void
     */
    public function preDelete(Delete $delete)
    {
        $this->event->setName(static::EVENT_PRE_DELETE);
        $this->event->setParams(array('delete' => $delete));
        $this->eventManager->trigger($this->event);
    }

    /**
     * Trigger the "postDelete" event
     *
     * Triggers the "postDelete" event mapping the following parameters:
     * - $statement as "statement"
     * - $result as "result"
     *
     * @param  StatementInterface $statement
     * @param  ResultInterface $result
     * @return void
     */
    public function postDelete(StatementInterface $statement, ResultInterface $result)
    {
        $this->event->setName(static::EVENT_POST_DELETE);
        $this->event->setParams(array(
            'statement' => $statement,
            'result' => $result,
        ));
        $this->eventManager->trigger($this->event);
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql;

use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Adapter\Driver\StatementInterface;
use Zend\Db\Adapter\Platform\PlatformInterface;

class Sql
{
    /** @var AdapterInterface */
    protected $adapter = null;

    /** @var string|array|TableIdentifier */
    protected $table = null;

    /** @var Platform\Platform */
    protected $sqlPlatform = null;

    /**
     * @param AdapterInterface                  $adapter
     * @param null|string|array|TableIdentifier $table
     * @param null|Platform\AbstractPlatform    $sqlPlatform @deprecated since version 3.0
     */
    public function __construct(?AdapterInterface $adapter, $table = null, ?Platform\AbstractPlatform $sqlPlatform = null)
    {
        $this->adapter = $adapter;
        if ($table) {
            $this->setTable($table);
        }
        $this->sqlPlatform = $sqlPlatform ?: new Platform\Platform($adapter);
    }

    /**
     * @return null|\Zend\Db\Adapter\AdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    public function hasTable()
    {
        return ($this->table !== null);
    }

    public function setTable($table)
    {
        if (is_string($table) || is_array($table) || $table instanceof TableIdentifier) {
            $this->table = $table;
        } else {
            throw new Exception\InvalidArgumentException('Table must be a string, array or instance of TableIdentifier.');
        }
        return $this;
    }

    public function getTable()
    {
        return $this->table;
    }

    public function getSqlPlatform()
    {
        return $this->sqlPlatform;
    }

    public function select($table = null)
    {
        if ($this->table !== null && $table !== null) {
            throw new Exception\InvalidArgumentException(sprintf(
                'This Sql object is intended to work with only the table "%s" provided at construction time.',
                $this->table
            ));
        }
        return new Select(($table) ?: $this->table);
    }

    public function insert($table = null)
    {
        if ($this->table !== null && $table !== null) {
            throw new Exception\InvalidArgumentException(sprintf(
                'This Sql object is intended to work with only the table "%s" provided at construction time.',
                $this->table
            ));
        }
        return new Insert(($table) ?: $this->table);
    }

    public function update($table = null)
    {
        if ($this->table !== null && $table !== null) {
            throw new Exception\InvalidArgumentException(sprintf(
                'This Sql object is intended to work with only the table "%s" provided at construction time.',
                $this->table
            ));
        }
        return new Update(($table) ?: $this->table);
    }

    public function delete($table = null)
    {
        if ($this->table !== null && $table !== null) {
            throw new Exception\InvalidArgumentException(sprintf(
                'This Sql object is intended to work with only the table "%s" provided at construction time.',
                $this->table
            ));
        }
        return new Delete(($table) ?: $this->table);
    }

    /**
     * @param PreparableSqlInterface $sqlObject
     * @param StatementInterface     $statement
     * @param AdapterInterface       $adapter
     *
     * @return StatementInterface
     */
    public function prepareStatementForSqlObject(?PreparableSqlInterface $sqlObject, ?StatementInterface $statement = null, ?AdapterInterface $adapter = null)
    {
        $adapter   = $adapter ?: $this->adapter;
        $statement = $statement ?: $adapter->getDriver()->createStatement();

        return $this->sqlPlatform->setSubject($sqlObject)->prepareStatement($adapter, $statement);
    }

    /**
     * Get sql string using platform or sql object
     *
     * @param SqlInterface           $sqlObject
     * @param PlatformInterface|null $platform
     *
     * @return string
     *
     * @deprecated Deprecated in 2.4. Use buildSqlString() instead
     */
    public function getSqlStringForSqlObject(?SqlInterface $sqlObject, ?PlatformInterface $platform = null)
    {
        $platform = ($platform) ?: $this->adapter->getPlatform();
        return $this->sqlPlatform->setSubject($sqlObject)->getSqlString($platform);
    }

    /**
     * @param SqlInterface     $sqlObject
     * @param AdapterInterface $adapter
     *
     * @return string
     *
     * @throws Exception\InvalidArgumentException
     */
    public function buildSqlString(?SqlInterface $sqlObject, ?AdapterInterface $adapter = null)
    {
        return $this
            ->sqlPlatform
            ->setSubject($sqlObject)
            ->getSqlString($adapter ? $adapter->getPlatform() : $this->adapter->getPlatform());
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql;

use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Adapter\StatementContainerInterface;

interface PreparableSqlInterface
{
    /**
     * @param AdapterInterface            $adapter
     * @param StatementContainerInterface $statementContainer
     *
     * @return void
     */
    public function prepareStatement(AdapterInterface $adapter, StatementContainerInterface $statementContainer);
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Platform;

interface PlatformDecoratorInterface
{
    /**
     * @param $subject
     *
     * @return self
     */
    public function setSubject($subject);
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql;

use Zend\Db\Adapter\Platform\PlatformInterface;

interface SqlInterface
{
    /**
     * Get SQL string for statement
     *
     * @param null|PlatformInterface $adapterPlatform
     *
     * @return string
     */
    public function getSqlString(?PlatformInterface $adapterPlatform = null);
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Platform;

use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\Adapter\StatementContainerInterface;
use Zend\Db\Sql\Exception;
use Zend\Db\Sql\PreparableSqlInterface;
use Zend\Db\Sql\SqlInterface;

class AbstractPlatform implements PlatformDecoratorInterface, PreparableSqlInterface, SqlInterface
{
    /**
     * @var object|null
     */
    protected $subject;

    /**
     * @var PlatformDecoratorInterface[]
     */
    protected $decorators = array();

    /**
     * {@inheritDoc}
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @param string                     $type
     * @param PlatformDecoratorInterface $decorator
     *
     * @return void
     */
    public function setTypeDecorator($type, PlatformDecoratorInterface $decorator)
    {
        $this->decorators[$type] = $decorator;
    }

    /**
     * @param PreparableSqlInterface|SqlInterface $subject
     * @return PlatformDecoratorInterface|PreparableSqlInterface|SqlInterface
     */
    public function getTypeDecorator($subject)
    {
        foreach ($this->decorators as $type => $decorator) {
            if ($subject instanceof $type) {
                $decorator->setSubject($subject);

                return $decorator;
            }
        }

        return $subject;
    }

    /**
     * @return array|PlatformDecoratorInterface[]
     */
    public function getDecorators()
    {
        return $this->decorators;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception\RuntimeException
     */
    public function prepareStatement(AdapterInterface $adapter, StatementContainerInterface $statementContainer)
    {
        if (! $this->subject instanceof PreparableSqlInterface) {
            throw new Exception\RuntimeException('The subject does not appear to implement Zend\Db\Sql\PreparableSqlInterface, thus calling prepareStatement() has no effect');
        }

        $this->getTypeDecorator($this->subject)->prepareStatement($adapter, $statementContainer);

        return $statementContainer;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception\RuntimeException
     */
    public function getSqlString(?PlatformInterface $adapterPlatform = null)
    {
        if (! $this->subject instanceof SqlInterface) {
            throw new Exception\RuntimeException('The subject does not appear to implement Zend\Db\Sql\SqlInterface, thus calling prepareStatement() has no effect');
        }

        return $this->getTypeDecorator($this->subject)->getSqlString($adapterPlatform);
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Platform\SqlServer;

use Zend\Db\Sql\Platform\AbstractPlatform;

class SqlServer extends AbstractPlatform
{
    public function __construct(?SelectDecorator $selectDecorator = null)
    {
        $this->setTypeDecorator('Zend\Db\Sql\Select', ($selectDecorator) ?: new SelectDecorator());
        $this->setTypeDecorator('Zend\Db\Sql\Ddl\CreateTable', new Ddl\CreateTableDecorator());
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Platform;

use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\Adapter\StatementContainerInterface;
use Zend\Db\Sql\Exception;
use Zend\Db\Sql\PreparableSqlInterface;
use Zend\Db\Sql\SqlInterface;

class Platform extends AbstractPlatform
{
    /**
     * @var AdapterInterface
     */
    protected $adapter = null;

    /**
     * @var PlatformInterface|null
     */
    protected $defaultPlatform;

    public function __construct(AdapterInterface $adapter)
    {
        $this->defaultPlatform = $adapter->getPlatform();

        $mySqlPlatform     = new Mysql\Mysql();
        $sqlServerPlatform = new SqlServer\SqlServer();
        $oraclePlatform    = new Oracle\Oracle();
        $ibmDb2Platform    = new IbmDb2\IbmDb2();

        $this->decorators['mysql']     = $mySqlPlatform->getDecorators();
        $this->decorators['sqlserver'] = $sqlServerPlatform->getDecorators();
        $this->decorators['oracle']    = $oraclePlatform->getDecorators();
        $this->decorators['ibmdb2']    = $ibmDb2Platform->getDecorators();
    }

    /**
     * @param string                             $type
     * @param PlatformDecoratorInterface         $decorator
     * @param AdapterInterface|PlatformInterface $adapterOrPlatform
     */
    public function setTypeDecorator($type, PlatformDecoratorInterface $decorator, $adapterOrPlatform = null)
    {
        $platformName = $this->resolvePlatformName($adapterOrPlatform);
        $this->decorators[$platformName][$type] = $decorator;
    }

    /**
     * @param PreparableSqlInterface|SqlInterface     $subject
     * @param AdapterInterface|PlatformInterface|null $adapterOrPlatform
     * @return PlatformDecoratorInterface|PreparableSqlInterface|SqlInterface
     */
    public function getTypeDecorator($subject, $adapterOrPlatform = null)
    {
        $platformName = $this->resolvePlatformName($adapterOrPlatform);

        if (isset($this->decorators[$platformName])) {
            foreach ($this->decorators[$platformName] as $type => $decorator) {
                if ($subject instanceof $type && is_a($decorator, $type, true)) {
                    $decorator->setSubject($subject);
                    return $decorator;
                }
            }
        }

        return $subject;
    }

    /**
     * @return array|PlatformDecoratorInterface[]
     */
    public function getDecorators()
    {
        $platformName = $this->resolvePlatformName($this->getDefaultPlatform());
        return $this->decorators[$platformName];
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception\RuntimeException
     */
    public function prepareStatement(AdapterInterface $adapter, StatementContainerInterface $statementContainer)
    {
        if (! $this->subject instanceof PreparableSqlInterface) {
            throw new Exception\RuntimeException('The subject does not appear to implement Zend\Db\Sql\PreparableSqlInterface, thus calling prepareStatement() has no effect');
        }

        $this->getTypeDecorator($this->subject, $adapter)->prepareStatement($adapter, $statementContainer);

        return $statementContainer;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception\RuntimeException
     */
    public function getSqlString(?PlatformInterface $adapterPlatform = null)
    {
        if (! $this->subject instanceof SqlInterface) {
            throw new Exception\RuntimeException('The subject does not appear to implement Zend\Db\Sql\SqlInterface, thus calling prepareStatement() has no effect');
        }

        $adapterPlatform = $this->resolvePlatform($adapterPlatform);

        return $this->getTypeDecorator($this->subject, $adapterPlatform)->getSqlString($adapterPlatform);
    }

    protected function resolvePlatformName($adapterOrPlatform)
    {
        $platformName = $this->resolvePlatform($adapterOrPlatform)->getName();
        return str_replace(array(' ', '_'), '', strtolower($platformName));
    }
    /**
     * @param null|PlatformInterface|AdapterInterface $adapterOrPlatform
     *
     * @return PlatformInterface
     *
     * @throws Exception\InvalidArgumentException
     */
    protected function resolvePlatform($adapterOrPlatform)
    {
        if (! $adapterOrPlatform) {
            return $this->getDefaultPlatform();
        }

        if ($adapterOrPlatform instanceof AdapterInterface) {
            return $adapterOrPlatform->getPlatform();
        }

        if ($adapterOrPlatform instanceof PlatformInterface) {
            return $adapterOrPlatform;
        }

        throw new Exception\InvalidArgumentException(sprintf(
            '$adapterOrPlatform should be null, %s, or %s',
            'Zend\Db\Adapter\AdapterInterface',
            'Zend\Db\Adapter\Platform\PlatformInterface'
        ));
    }

    /**
     * @return PlatformInterface
     *
     * @throws Exception\RuntimeException
     */
    protected function getDefaultPlatform()
    {
        if (! $this->defaultPlatform) {
            throw new Exception\RuntimeException('$this->defaultPlatform was not set');
        }

        return $this->defaultPlatform;
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Platform\IbmDb2;

use Zend\Db\Sql\Platform\AbstractPlatform;

class IbmDb2 extends AbstractPlatform
{
    /**
     * @param SelectDecorator $selectDecorator
     */
    public function __construct(?SelectDecorator $selectDecorator = null)
    {
        $this->setTypeDecorator('Zend\Db\Sql\Select', ($selectDecorator) ?: new SelectDecorator());
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Platform\Oracle;

use Zend\Db\Sql\Platform\AbstractPlatform;

class Oracle extends AbstractPlatform
{
    public function __construct(?SelectDecorator $selectDecorator = null)
    {
        $this->setTypeDecorator('Zend\Db\Sql\Select', ($selectDecorator) ?: new SelectDecorator());
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Platform\Mysql;

use Zend\Db\Sql\Platform\AbstractPlatform;

class Mysql extends AbstractPlatform
{
    public function __construct()
    {
        $this->setTypeDecorator('Zend\Db\Sql\Select', new SelectDecorator());
        $this->setTypeDecorator('Zend\Db\Sql\Ddl\CreateTable', new Ddl\CreateTableDecorator());
        $this->setTypeDecorator('Zend\Db\Sql\Ddl\AlterTable', new Ddl\AlterTableDecorator());
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql;

use Zend\Db\Adapter\Driver\DriverInterface;
use Zend\Db\Adapter\ParameterContainer;
use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\Sql\Platform\PlatformDecoratorInterface;
use Zend\Db\Adapter\Platform\Sql92 as DefaultAdapterPlatform;

abstract class AbstractSql implements SqlInterface
{
    /**
     * Specifications for Sql String generation
     *
     * @var string[]|array[]
     */
    protected $specifications = array();

    /**
     * @var string
     */
    protected $processInfo = array('paramPrefix' => '', 'subselectCount' => 0);

    /**
     * @var array
     */
    protected $instanceParameterIndex = array();

    /**
     * {@inheritDoc}
     */
    public function getSqlString(?PlatformInterface $adapterPlatform = null)
    {
        $adapterPlatform = ($adapterPlatform) ?: new DefaultAdapterPlatform;
        return $this->buildSqlString($adapterPlatform);
    }

    /**
     * @param PlatformInterface $platform
     * @param null|DriverInterface $driver
     * @param null|ParameterContainer $parameterContainer
     * @return string
     */
    protected function buildSqlString(
        ?PlatformInterface $platform,
        ?DriverInterface $driver = null,
        ?ParameterContainer $parameterContainer = null
    ) {
        $this->localizeVariables();

        $sqls       = array();
        $parameters = array();

        foreach ($this->specifications as $name => $specification) {
            $parameters[$name] = $this->{'process' . $name}(
                $platform,
                $driver,
                $parameterContainer,
                $sqls,
                $parameters
            );

            if ($specification && is_array($parameters[$name])) {
                $sqls[$name] = $this->createSqlFromSpecificationAndParameters($specification, $parameters[$name]);

                continue;
            }

            if (is_string($parameters[$name])) {
                $sqls[$name] = $parameters[$name];
            }
        }
        return rtrim(implode(' ', $sqls), "\n ,");
    }

    /**
     * @staticvar int $runtimeExpressionPrefix
     * @param ExpressionInterface $expression
     * @param PlatformInterface $platform
     * @param null|DriverInterface $driver
     * @param null|ParameterContainer $parameterContainer
     * @param null|string $namedParameterPrefix
     * @return string
     * @throws Exception\RuntimeException
     */
    protected function processExpression(
        ?ExpressionInterface $expression,
        ?PlatformInterface $platform,
        ?DriverInterface $driver = null,
        ?ParameterContainer $parameterContainer = null,
        $namedParameterPrefix = null
    ) {
        $namedParameterPrefix = ! $namedParameterPrefix
            ? $namedParameterPrefix
            : $this->processInfo['paramPrefix'] . $namedParameterPrefix;
        // static counter for the number of times this method was invoked across the PHP runtime
        static $runtimeExpressionPrefix = 0;

        if ($parameterContainer && ((!is_string($namedParameterPrefix) || $namedParameterPrefix == ''))) {
            $namedParameterPrefix = sprintf('expr%04dParam', ++$runtimeExpressionPrefix);
        } else {
            $namedParameterPrefix = preg_replace('/\s/', '__', $namedParameterPrefix);
        }

        $sql = '';

        // initialize variables
        $parts = $expression->getExpressionData();

        if (! isset($this->instanceParameterIndex[$namedParameterPrefix])) {
            $this->instanceParameterIndex[$namedParameterPrefix] = 1;
        }

        $expressionParamIndex = &$this->instanceParameterIndex[$namedParameterPrefix];

        foreach ($parts as $part) {
            // #7407: use $expression->getExpression() to get the unescaped
            // version of the expression
            if (is_string($part) && $expression instanceof Expression) {
                $sql .= $expression->getExpression();
                continue;
            }

            // If it is a string, simply tack it onto the return sql
            // "specification" string
            if (is_string($part)) {
                $sql .= $part;
                continue;
            }

            if (! is_array($part)) {
                throw new Exception\RuntimeException(
                    'Elements returned from getExpressionData() array must be a string or array.'
                );
            }

            // Process values and types (the middle and last position of the
            // expression data)
            $values = $part[1];
            $types = isset($part[2]) ? $part[2] : array();
            foreach ($values as $vIndex => $value) {
                if (!isset($types[$vIndex])) {
                    continue;
                }
                $type = $types[$vIndex];
                if ($value instanceof Select) {
                    // process sub-select
                    $values[$vIndex] = '('
                        . $this->processSubSelect($value, $platform, $driver, $parameterContainer)
                        . ')';
                } elseif ($value instanceof ExpressionInterface) {
                    // recursive call to satisfy nested expressions
                    $values[$vIndex] = $this->processExpression(
                        $value,
                        $platform,
                        $driver,
                        $parameterContainer,
                        $namedParameterPrefix . $vIndex . 'subpart'
                    );
                } elseif ($type == ExpressionInterface::TYPE_IDENTIFIER) {
                    $values[$vIndex] = $platform->quoteIdentifierInFragment($value);
                } elseif ($type == ExpressionInterface::TYPE_VALUE) {
                    // if prepareType is set, it means that this particular value must be
                    // passed back to the statement in a way it can be used as a placeholder value
                    if ($parameterContainer) {
                        $name = $namedParameterPrefix . $expressionParamIndex++;
                        $parameterContainer->offsetSet($name, $value);
                        $values[$vIndex] = $driver->formatParameterName($name);
                        continue;
                    }

                    // if not a preparable statement, simply quote the value and move on
                    $values[$vIndex] = $platform->quoteValue($value);
                } elseif ($type == ExpressionInterface::TYPE_LITERAL) {
                    $values[$vIndex] = $value;
                }
            }

            // After looping the values, interpolate them into the sql string
            // (they might be placeholder names, or values)
            $sql .= vsprintf($part[0], $values);
        }

        return $sql;
    }

    /**
     * @param string|array $specifications
     * @param string|array $parameters
     *
     * @return string
     *
     * @throws Exception\RuntimeException
     */
    protected function createSqlFromSpecificationAndParameters($specifications, $parameters)
    {
        if (is_string($specifications)) {
            return vsprintf($specifications, $parameters);
        }

        $parametersCount = count($parameters);

        foreach ($specifications as $specificationString => $paramSpecs) {
            if ($parametersCount == count($paramSpecs)) {
                break;
            }

            unset($specificationString, $paramSpecs);
        }

        if (!isset($specificationString)) {
            throw new Exception\RuntimeException(
                'A number of parameters was found that is not supported by this specification'
            );
        }

        $topParameters = array();
        foreach ($parameters as $position => $paramsForPosition) {
            if (isset($paramSpecs[$position]['combinedby'])) {
                $multiParamValues = array();
                foreach ($paramsForPosition as $multiParamsForPosition) {
                    $ppCount = count($multiParamsForPosition);
                    if (!isset($paramSpecs[$position][$ppCount])) {
                        throw new Exception\RuntimeException(sprintf(
                            'A number of parameters (%d) was found that is not supported by this specification',
                            $ppCount
                        ));
                    }
                    $multiParamValues[] = vsprintf($paramSpecs[$position][$ppCount], $multiParamsForPosition);
                }
                $topParameters[] = implode($paramSpecs[$position]['combinedby'], $multiParamValues);
            } elseif ($paramSpecs[$position] !== null) {
                $ppCount = count($paramsForPosition);
                if (!isset($paramSpecs[$position][$ppCount])) {
                    throw new Exception\RuntimeException(sprintf(
                        'A number of parameters (%d) was found that is not supported by this specification',
                        $ppCount
                    ));
                }
                $topParameters[] = vsprintf($paramSpecs[$position][$ppCount], $paramsForPosition);
            } else {
                $topParameters[] = $paramsForPosition;
            }
        }
        return vsprintf($specificationString, $topParameters);
    }

    /**
     * @param Select $subselect
     * @param PlatformInterface $platform
     * @param null|DriverInterface $driver
     * @param null|ParameterContainer $parameterContainer
     * @return string
     */
    protected function processSubSelect(
        ?Select $subselect,
        ?PlatformInterface $platform,
        ?DriverInterface $driver = null,
        ?ParameterContainer $parameterContainer = null
    ) {
        if ($this instanceof PlatformDecoratorInterface) {
            $decorator = clone $this;
            $decorator->setSubject($subselect);
        } else {
            $decorator = $subselect;
        }

        if ($parameterContainer) {
            // Track subselect prefix and count for parameters
            $processInfoContext = ($decorator instanceof PlatformDecoratorInterface) ? $subselect : $decorator;
            $this->processInfo['subselectCount']++;
            $processInfoContext->processInfo['subselectCount'] = $this->processInfo['subselectCount'];
            $processInfoContext->processInfo['paramPrefix'] = 'subselect'
                . $processInfoContext->processInfo['subselectCount'];

            $sql = $decorator->buildSqlString($platform, $driver, $parameterContainer);

            // copy count
            $this->processInfo['subselectCount'] = $decorator->processInfo['subselectCount'];
            return $sql;
        }

        return $decorator->buildSqlString($platform, $driver, $parameterContainer);
    }

    /**
     * @param null|array|ExpressionInterface|Select $column
     * @param PlatformInterface $platform
     * @param null|DriverInterface $driver
     * @param null|string $namedParameterPrefix
     * @param null|ParameterContainer $parameterContainer
     * @return string
     */
    protected function resolveColumnValue(
        $column,
        ?PlatformInterface $platform,
        ?DriverInterface $driver = null,
        ?ParameterContainer $parameterContainer = null,
        $namedParameterPrefix = null
    ) {
        $namedParameterPrefix = ! $namedParameterPrefix
            ? $namedParameterPrefix
            : $this->processInfo['paramPrefix'] . $namedParameterPrefix;
        $isIdentifier = false;
        $fromTable = '';
        if (is_array($column)) {
            if (isset($column['isIdentifier'])) {
                $isIdentifier = (bool) $column['isIdentifier'];
            }
            if (isset($column['fromTable']) && $column['fromTable'] !== null) {
                $fromTable = $column['fromTable'];
            }
            $column = $column['column'];
        }

        if ($column instanceof ExpressionInterface) {
            return $this->processExpression($column, $platform, $driver, $parameterContainer, $namedParameterPrefix);
        }
        if ($column instanceof Select) {
            return '(' . $this->processSubSelect($column, $platform, $driver, $parameterContainer) . ')';
        }
        if ($column === null) {
            return 'NULL';
        }
        return $isIdentifier
                ? $fromTable . $platform->quoteIdentifierInFragment($column)
                : $platform->quoteValue($column);
    }

    /**
     * @param string|TableIdentifier|Select $table
     * @param PlatformInterface $platform
     * @param DriverInterface $driver
     * @param ParameterContainer $parameterContainer
     * @return string
     */
    protected function resolveTable(
        $table,
        ?PlatformInterface $platform,
        ?DriverInterface $driver = null,
        ?ParameterContainer $parameterContainer = null
    ) {
        $schema = null;
        if ($table instanceof TableIdentifier) {
            list($table, $schema) = $table->getTableAndSchema();
        }

        if ($table instanceof Select) {
            $table = '(' . $this->processSubselect($table, $platform, $driver, $parameterContainer) . ')';
        } elseif ($table) {
            $table = $platform->quoteIdentifier($table);
        }

        if ($schema && $table) {
            $table = $platform->quoteIdentifier($schema) . $platform->getIdentifierSeparator() . $table;
        }
        return $table;
    }

    /**
     * Copy variables from the subject into the local properties
     */
    protected function localizeVariables()
    {
        if (! $this instanceof PlatformDecoratorInterface) {
            return;
        }

        foreach (get_object_vars($this->subject) as $name => $value) {
            $this->{$name} = $value;
        }
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql;

use Zend\Db\Adapter\StatementContainerInterface;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Adapter\ParameterContainer;

abstract class AbstractPreparableSql extends AbstractSql implements PreparableSqlInterface
{
    /**
     * {@inheritDoc}
     *
     * @return StatementContainerInterface
     */
    public function prepareStatement(AdapterInterface $adapter, StatementContainerInterface $statementContainer)
    {
        $parameterContainer = $statementContainer->getParameterContainer();

        if (! $parameterContainer instanceof ParameterContainer) {
            $parameterContainer = new ParameterContainer();

            $statementContainer->setParameterContainer($parameterContainer);
        }

        $statementContainer->setSql(
            $this->buildSqlString($adapter->getPlatform(), $adapter->getDriver(), $parameterContainer)
        );

        return $statementContainer;
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql;

use Zend\Db\Adapter\Driver\DriverInterface;
use Zend\Db\Adapter\ParameterContainer;
use Zend\Db\Adapter\Platform\PlatformInterface;

/**
 *
 * @property Where $where
 * @property Having $having
 */
class Select extends AbstractPreparableSql
{
    /**#@+
     * Constant
     * @const
     */
    const SELECT = 'select';
    const QUANTIFIER = 'quantifier';
    const COLUMNS = 'columns';
    const TABLE = 'table';
    const JOINS = 'joins';
    const WHERE = 'where';
    const GROUP = 'group';
    const HAVING = 'having';
    const ORDER = 'order';
    const LIMIT = 'limit';
    const OFFSET = 'offset';
    const QUANTIFIER_DISTINCT = 'DISTINCT';
    const QUANTIFIER_ALL = 'ALL';
    const JOIN_INNER = 'inner';
    const JOIN_OUTER = 'outer';
    const JOIN_LEFT = 'left';
    const JOIN_RIGHT = 'right';
    const JOIN_OUTER_RIGHT = 'outer right';
    const JOIN_OUTER_LEFT  = 'outer left';
    const SQL_STAR = '*';
    const ORDER_ASCENDING = 'ASC';
    const ORDER_DESCENDING = 'DESC';
    const COMBINE = 'combine';
    const COMBINE_UNION = 'union';
    const COMBINE_EXCEPT = 'except';
    const COMBINE_INTERSECT = 'intersect';
    /**#@-*/

    /**
     * @var array Specifications
     */
    protected $specifications = array(
        'statementStart' => '%1$s',
        self::SELECT => array(
            'SELECT %1$s FROM %2$s' => array(
                array(1 => '%1$s', 2 => '%1$s AS %2$s', 'combinedby' => ', '),
                null
            ),
            'SELECT %1$s %2$s FROM %3$s' => array(
                null,
                array(1 => '%1$s', 2 => '%1$s AS %2$s', 'combinedby' => ', '),
                null
            ),
            'SELECT %1$s' => array(
                array(1 => '%1$s', 2 => '%1$s AS %2$s', 'combinedby' => ', '),
            ),
        ),
        self::JOINS  => array(
            '%1$s' => array(
                array(3 => '%1$s JOIN %2$s ON %3$s', 'combinedby' => ' ')
            )
        ),
        self::WHERE  => 'WHERE %1$s',
        self::GROUP  => array(
            'GROUP BY %1$s' => array(
                array(1 => '%1$s', 'combinedby' => ', ')
            )
        ),
        self::HAVING => 'HAVING %1$s',
        self::ORDER  => array(
            'ORDER BY %1$s' => array(
                array(1 => '%1$s', 2 => '%1$s %2$s', 'combinedby' => ', ')
            )
        ),
        self::LIMIT  => 'LIMIT %1$s',
        self::OFFSET => 'OFFSET %1$s',
        'statementEnd' => '%1$s',
        self::COMBINE => '%1$s ( %2$s )',
    );

    /**
     * @var bool
     */
    protected $tableReadOnly = false;

    /**
     * @var bool
     */
    protected $prefixColumnsWithTable = true;

    /**
     * @var string|array|TableIdentifier
     */
    protected $table = null;

    /**
     * @var null|string|Expression
     */
    protected $quantifier = null;

    /**
     * @var array
     */
    protected $columns = array(self::SQL_STAR);

    /**
     * @var array
     */
    protected $joins = array();

    /**
     * @var Where
     */
    protected $where = null;

    /**
     * @var array
     */
    protected $order = array();

    /**
     * @var null|array
     */
    protected $group = null;

    /**
     * @var null|string|array
     */
    protected $having = null;

    /**
     * @var int|null
     */
    protected $limit = null;

    /**
     * @var int|null
     */
    protected $offset = null;

    /**
     * @var array
     */
    protected $combine = array();

    /**
     * Constructor
     *
     * @param  null|string|array|TableIdentifier $table
     */
    public function __construct($table = null)
    {
        if ($table) {
            $this->from($table);
            $this->tableReadOnly = true;
        }

        $this->where = new Where;
        $this->having = new Having;
    }

    /**
     * Create from clause
     *
     * @param  string|array|TableIdentifier $table
     * @throws Exception\InvalidArgumentException
     * @return Select
     */
    public function from($table)
    {
        if ($this->tableReadOnly) {
            throw new Exception\InvalidArgumentException('Since this object was created with a table and/or schema in the constructor, it is read only.');
        }

        if (!is_string($table) && !is_array($table) && !$table instanceof TableIdentifier) {
            throw new Exception\InvalidArgumentException('$table must be a string, array, or an instance of TableIdentifier');
        }

        if (is_array($table) && (!is_string(key($table)) || count($table) !== 1)) {
            throw new Exception\InvalidArgumentException('from() expects $table as an array is a single element associative array');
        }

        $this->table = $table;
        return $this;
    }

    /**
     * @param string|Expression $quantifier DISTINCT|ALL
     * @return Select
     */
    public function quantifier($quantifier)
    {
        if (!is_string($quantifier) && !$quantifier instanceof ExpressionInterface) {
            throw new Exception\InvalidArgumentException(
                'Quantifier must be one of DISTINCT, ALL, or some platform specific object implementing ExpressionInterface'
            );
        }
        $this->quantifier = $quantifier;
        return $this;
    }

    /**
     * Specify columns from which to select
     *
     * Possible valid states:
     *
     *   array(*)
     *
     *   array(value, ...)
     *     value can be strings or Expression objects
     *
     *   array(string => value, ...)
     *     key string will be use as alias,
     *     value can be string or Expression objects
     *
     * @param  array $columns
     * @param  bool  $prefixColumnsWithTable
     * @return Select
     */
    public function columns(array $columns, $prefixColumnsWithTable = true)
    {
        $this->columns = $columns;
        $this->prefixColumnsWithTable = (bool) $prefixColumnsWithTable;
        return $this;
    }

    /**
     * Create join clause
     *
     * @param  string|array $name
     * @param  string $on
     * @param  string|array $columns
     * @param  string $type one of the JOIN_* constants
     * @throws Exception\InvalidArgumentException
     * @return Select
     */
    public function join($name, $on, $columns = self::SQL_STAR, $type = self::JOIN_INNER)
    {
        if (is_array($name) && (!is_string(key($name)) || count($name) !== 1)) {
            throw new Exception\InvalidArgumentException(
                sprintf("join() expects '%s' as an array is a single element associative array", array_shift($name))
            );
        }
        if (!is_array($columns)) {
            $columns = array($columns);
        }
        $this->joins[] = array(
            'name'    => $name,
            'on'      => $on,
            'columns' => $columns,
            'type'    => $type
        );
        return $this;
    }

    /**
     * Create where clause
     *
     * @param  Where|\Closure|string|array|Predicate\PredicateInterface $predicate
     * @param  string $combination One of the OP_* constants from Predicate\PredicateSet
     * @throws Exception\InvalidArgumentException
     * @return Select
     */
    public function where($predicate, $combination = Predicate\PredicateSet::OP_AND)
    {
        if ($predicate instanceof Where) {
            $this->where = $predicate;
        } else {
            $this->where->addPredicates($predicate, $combination);
        }
        return $this;
    }

    public function group($group)
    {
        if (is_array($group)) {
            foreach ($group as $o) {
                $this->group[] = $o;
            }
        } else {
            $this->group[] = $group;
        }
        return $this;
    }

    /**
     * Create where clause
     *
     * @param  Where|\Closure|string|array $predicate
     * @param  string $combination One of the OP_* constants from Predicate\PredicateSet
     * @return Select
     */
    public function having($predicate, $combination = Predicate\PredicateSet::OP_AND)
    {
        if ($predicate instanceof Having) {
            $this->having = $predicate;
        } else {
            $this->having->addPredicates($predicate, $combination);
        }
        return $this;
    }

    /**
     * @param string|array $order
     * @return Select
     */
    public function order($order)
    {
        if (is_string($order)) {
            if (strpos($order, ',') !== false) {
                $order = preg_split('#,\s+#', $order);
            } else {
                $order = (array) $order;
            }
        } elseif (!is_array($order)) {
            $order = array($order);
        }
        foreach ($order as $k => $v) {
            if (is_string($k)) {
                $this->order[$k] = $v;
            } else {
                $this->order[] = $v;
            }
        }
        return $this;
    }

    /**
     * @param int $limit
     * @return Select
     */
    public function limit($limit)
    {
        if (!is_numeric($limit)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects parameter to be numeric, "%s" given',
                __METHOD__,
                (is_object($limit) ? get_class($limit) : gettype($limit))
            ));
        }

        $this->limit = $limit;
        return $this;
    }

    /**
     * @param int $offset
     * @return Select
     */
    public function offset($offset)
    {
        if (!is_numeric($offset)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects parameter to be numeric, "%s" given',
                __METHOD__,
                (is_object($offset) ? get_class($offset) : gettype($offset))
            ));
        }

        $this->offset = $offset;
        return $this;
    }

    /**
     * @param Select $select
     * @param string $type
     * @param string $modifier
     * @return Select
     * @throws Exception\InvalidArgumentException
     */
    public function combine(Select $select, $type = self::COMBINE_UNION, $modifier = '')
    {
        if ($this->combine !== array()) {
            throw new Exception\InvalidArgumentException('This Select object is already combined and cannot be combined with multiple Selects objects');
        }
        $this->combine = array(
            'select' => $select,
            'type' => $type,
            'modifier' => $modifier
        );
        return $this;
    }

    /**
     * @param string $part
     * @return Select
     * @throws Exception\InvalidArgumentException
     */
    public function reset($part)
    {
        switch ($part) {
            case self::TABLE:
                if ($this->tableReadOnly) {
                    throw new Exception\InvalidArgumentException(
                        'Since this object was created with a table and/or schema in the constructor, it is read only.'
                    );
                }
                $this->table = null;
                break;
            case self::QUANTIFIER:
                $this->quantifier = null;
                break;
            case self::COLUMNS:
                $this->columns = array();
                break;
            case self::JOINS:
                $this->joins = array();
                break;
            case self::WHERE:
                $this->where = new Where;
                break;
            case self::GROUP:
                $this->group = null;
                break;
            case self::HAVING:
                $this->having = new Having;
                break;
            case self::LIMIT:
                $this->limit = null;
                break;
            case self::OFFSET:
                $this->offset = null;
                break;
            case self::ORDER:
                $this->order = array();
                break;
            case self::COMBINE:
                $this->combine = array();
                break;
        }
        return $this;
    }

    public function setSpecification($index, $specification)
    {
        if (!method_exists($this, 'process' . $index)) {
            throw new Exception\InvalidArgumentException('Not a valid specification name.');
        }
        $this->specifications[$index] = $specification;
        return $this;
    }

    public function getRawState($key = null)
    {
        $rawState = array(
            self::TABLE      => $this->table,
            self::QUANTIFIER => $this->quantifier,
            self::COLUMNS    => $this->columns,
            self::JOINS      => $this->joins,
            self::WHERE      => $this->where,
            self::ORDER      => $this->order,
            self::GROUP      => $this->group,
            self::HAVING     => $this->having,
            self::LIMIT      => $this->limit,
            self::OFFSET     => $this->offset,
            self::COMBINE    => $this->combine
        );
        return (isset($key) && array_key_exists($key, $rawState)) ? $rawState[$key] : $rawState;
    }

    /**
     * Returns whether the table is read only or not.
     *
     * @return bool
     */
    public function isTableReadOnly()
    {
        return $this->tableReadOnly;
    }

    /**
     * Render table with alias in from/join parts
     *
     * @todo move TableIdentifier concatination here
     * @param string $table
     * @param string $alias
     * @return string
     */
    protected function renderTable($table, $alias = null)
    {
        return $table . ($alias ? ' AS ' . $alias : '');
    }

    protected function processStatementStart(?PlatformInterface $platform, ?DriverInterface $driver = null, ?ParameterContainer $parameterContainer = null)
    {
        if ($this->combine !== array()) {
            return array('(');
        }
    }

    protected function processStatementEnd(?PlatformInterface $platform, ?DriverInterface $driver = null, ?ParameterContainer $parameterContainer = null)
    {
        if ($this->combine !== array()) {
            return array(')');
        }
    }

    /**
     * Process the select part
     *
     * @param PlatformInterface $platform
     * @param DriverInterface $driver
     * @param ParameterContainer $parameterContainer
     * @return null|array
     */
    protected function processSelect(?PlatformInterface $platform, ?DriverInterface $driver = null, ?ParameterContainer $parameterContainer = null)
    {
        $expr = 1;

        list($table, $fromTable) = $this->resolveTable($this->table, $platform, $driver, $parameterContainer);
        // process table columns
        $columns = array();
        foreach ($this->columns as $columnIndexOrAs => $column) {
            if ($column === self::SQL_STAR) {
                $columns[] = array($fromTable . self::SQL_STAR);
                continue;
            }

            $columnName = $this->resolveColumnValue(
                array(
                    'column'       => $column,
                    'fromTable'    => $fromTable,
                    'isIdentifier' => true,
                ),
                $platform,
                $driver,
                $parameterContainer,
                (is_string($columnIndexOrAs) ? $columnIndexOrAs : 'column')
            );
            // process As portion
            if (is_string($columnIndexOrAs)) {
                $columnAs = $platform->quoteIdentifier($columnIndexOrAs);
            } elseif (stripos($columnName, ' as ') === false) {
                $columnAs = (is_string($column)) ? $platform->quoteIdentifier($column) : 'Expression' . $expr++;
            }
            $columns[] = (isset($columnAs)) ? array($columnName, $columnAs) : array($columnName);
        }

        // process join columns
        foreach ($this->joins as $join) {
            $joinName = (is_array($join['name'])) ? key($join['name']) : $join['name'];
            $joinName = parent::resolveTable($joinName, $platform, $driver, $parameterContainer);

            foreach ($join['columns'] as $jKey => $jColumn) {
                $jColumns = array();
                $jFromTable = is_scalar($jColumn)
                            ? $joinName . $platform->getIdentifierSeparator()
                            : '';
                $jColumns[] = $this->resolveColumnValue(
                    array(
                        'column'       => $jColumn,
                        'fromTable'    => $jFromTable,
                        'isIdentifier' => true,
                    ),
                    $platform,
                    $driver,
                    $parameterContainer,
                    (is_string($jKey) ? $jKey : 'column')
                );
                if (is_string($jKey)) {
                    $jColumns[] = $platform->quoteIdentifier($jKey);
                } elseif ($jColumn !== self::SQL_STAR) {
                    $jColumns[] = $platform->quoteIdentifier($jColumn);
                }
                $columns[] = $jColumns;
            }
        }

        if ($this->quantifier) {
            $quantifier = ($this->quantifier instanceof ExpressionInterface)
                    ? $this->processExpression($this->quantifier, $platform, $driver, $parameterContainer, 'quantifier')
                    : $this->quantifier;
        }

        if (!isset($table)) {
            return array($columns);
        } elseif (isset($quantifier)) {
            return array($quantifier, $columns, $table);
        } else {
            return array($columns, $table);
        }
    }

    protected function processJoins(?PlatformInterface $platform, ?DriverInterface $driver = null, ?ParameterContainer $parameterContainer = null)
    {
        if (!$this->joins) {
            return;
        }

        // process joins
        $joinSpecArgArray = array();
        foreach ($this->joins as $j => $join) {
            $joinName = null;
            $joinAs = null;

            // table name
            if (is_array($join['name'])) {
                $joinName = current($join['name']);
                $joinAs = $platform->quoteIdentifier(key($join['name']));
            } else {
                $joinName = $join['name'];
            }

            if ($joinName instanceof Expression) {
                $joinName = $joinName->getExpression();
            } elseif ($joinName instanceof TableIdentifier) {
                $joinName = $joinName->getTableAndSchema();
                $joinName = ($joinName[1] ? $platform->quoteIdentifier($joinName[1]) . $platform->getIdentifierSeparator() : '') . $platform->quoteIdentifier($joinName[0]);
            } elseif ($joinName instanceof Select) {
                $joinName = '(' . $this->processSubSelect($joinName, $platform, $driver, $parameterContainer) . ')';
            } elseif (is_string($joinName) || (is_object($joinName) && is_callable(array($joinName, '__toString')))) {
                $joinName = $platform->quoteIdentifier($joinName);
            } else {
                throw new Exception\InvalidArgumentException(sprintf('Join name expected to be Expression|TableIdentifier|Select|string, "%s" given', gettype($joinName)));
            }

            $joinSpecArgArray[$j] = array(
                strtoupper($join['type']),
                $this->renderTable($joinName, $joinAs),
            );

            // on expression
            // note: for Expression objects, pass them to processExpression with a prefix specific to each join (used for named parameters)
            $joinSpecArgArray[$j][] = ($join['on'] instanceof ExpressionInterface)
                ? $this->processExpression($join['on'], $platform, $driver, $parameterContainer, 'join' . ($j+1) . 'part')
                : $platform->quoteIdentifierInFragment($join['on'], array('=', 'AND', 'OR', '(', ')', 'BETWEEN', '<', '>')); // on
        }

        return array($joinSpecArgArray);
    }

    protected function processWhere(?PlatformInterface $platform, ?DriverInterface $driver = null, ?ParameterContainer $parameterContainer = null)
    {
        if ($this->where->count() == 0) {
            return;
        }
        return array(
            $this->processExpression($this->where, $platform, $driver, $parameterContainer, 'where')
        );
    }

    protected function processGroup(PlatformInterface $platform, ?DriverInterface $driver = null, ?ParameterContainer $parameterContainer = null)
    {
        if ($this->group === null) {
            return;
        }
        // process table columns
        $groups = array();
        foreach ($this->group as $column) {
            $groups[] = $this->resolveColumnValue(
                array(
                    'column'       => $column,
                    'isIdentifier' => true,
                ),
                $platform,
                $driver,
                $parameterContainer,
                'group'
            );
        }
        return array($groups);
    }

    protected function processHaving(?PlatformInterface $platform, ?DriverInterface $driver = null, ?ParameterContainer $parameterContainer = null)
    {
        if ($this->having->count() == 0) {
            return;
        }
        return array(
            $this->processExpression($this->having, $platform, $driver, $parameterContainer, 'having')
        );
    }

    protected function processOrder(?PlatformInterface $platform, ?DriverInterface $driver = null, ?ParameterContainer $parameterContainer = null)
    {
        if (empty($this->order)) {
            return;
        }
        $orders = array();
        foreach ($this->order as $k => $v) {
            if ($v instanceof ExpressionInterface) {
                $orders[] = array(
                    $this->processExpression($v, $platform, $driver, $parameterContainer)
                );
                continue;
            }
            if (is_int($k)) {
                if (strpos($v, ' ') !== false) {
                    list($k, $v) = preg_split('# #', $v, 2);
                } else {
                    $k = $v;
                    $v = self::ORDER_ASCENDING;
                }
            }
            if (strtoupper($v) == self::ORDER_DESCENDING) {
                $orders[] = array($platform->quoteIdentifierInFragment($k), self::ORDER_DESCENDING);
            } else {
                $orders[] = array($platform->quoteIdentifierInFragment($k), self::ORDER_ASCENDING);
            }
        }
        return array($orders);
    }

    protected function processLimit(?PlatformInterface $platform, ?DriverInterface $driver = null, ?ParameterContainer $parameterContainer = null)
    {
        if ($this->limit === null) {
            return;
        }
        if ($parameterContainer) {
            $parameterContainer->offsetSet('limit', $this->limit, ParameterContainer::TYPE_INTEGER);
            return array($driver->formatParameterName('limit'));
        }
        return array($platform->quoteValue($this->limit));
    }

    protected function processOffset(?PlatformInterface $platform, ?DriverInterface $driver = null, ?ParameterContainer $parameterContainer = null)
    {
        if ($this->offset === null) {
            return;
        }
        if ($parameterContainer) {
            $parameterContainer->offsetSet('offset', $this->offset, ParameterContainer::TYPE_INTEGER);
            return array($driver->formatParameterName('offset'));
        }

        return array($platform->quoteValue($this->offset));
    }

    protected function processCombine(?PlatformInterface $platform, ?DriverInterface $driver = null, ?ParameterContainer $parameterContainer = null)
    {
        if ($this->combine == array()) {
            return;
        }

        $type = $this->combine['type'];
        if ($this->combine['modifier']) {
            $type .= ' ' . $this->combine['modifier'];
        }

        return array(
            strtoupper($type),
            $this->processSubSelect($this->combine['select'], $platform, $driver, $parameterContainer),
        );
    }

    /**
     * Variable overloading
     *
     * @param  string $name
     * @throws Exception\InvalidArgumentException
     * @return mixed
     */
    public function __get($name)
    {
        switch (strtolower($name)) {
            case 'where':
                return $this->where;
            case 'having':
                return $this->having;
            default:
                throw new Exception\InvalidArgumentException('Not a valid magic property for this object');
        }
    }

    /**
     * __clone
     *
     * Resets the where object each time the Select is cloned.
     *
     * @return void
     */
    public function __clone()
    {
        $this->where  = clone $this->where;
        $this->having = clone $this->having;
    }

    /**
     * @param string|TableIdentifier|Select $table
     * @param PlatformInterface $platform
     * @param DriverInterface $driver
     * @param ParameterContainer $parameterContainer
     * @return string
     */
    protected function resolveTable($table, ?PlatformInterface $platform, ?DriverInterface $driver = null, ?ParameterContainer $parameterContainer = null)
    {
        $alias = null;

        if (is_array($table)) {
            $alias = key($table);
            $table = current($table);
        }

        $table = parent::resolveTable($table, $platform, $driver, $parameterContainer);

        if ($alias) {
            $fromTable = $platform->quoteIdentifier($alias);
            $table = $this->renderTable($table, $fromTable);
        } else {
            $fromTable = $table;
        }

        if ($this->prefixColumnsWithTable && $fromTable) {
            $fromTable .= $platform->getIdentifierSeparator();
        } else {
            $fromTable = '';
        }

        return array(
            $table,
            $fromTable
        );
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql;

use Zend\Db\Adapter\ParameterContainer;
use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\Adapter\Driver\DriverInterface;
use Zend\Stdlib\PriorityList;

/**
 *
 * @property Where $where
 */
class Update extends AbstractPreparableSql
{
    /**@#++
     * @const
     */
    const SPECIFICATION_UPDATE = 'update';
    const SPECIFICATION_WHERE = 'where';

    const VALUES_MERGE = 'merge';
    const VALUES_SET   = 'set';
    /**@#-**/

    protected $specifications = array(
        self::SPECIFICATION_UPDATE => 'UPDATE %1$s SET %2$s',
        self::SPECIFICATION_WHERE => 'WHERE %1$s'
    );

    /**
     * @var string|TableIdentifier
     */
    protected $table = '';

    /**
     * @var bool
     */
    protected $emptyWhereProtection = true;

    /**
     * @var PriorityList
     */
    protected $set;

    /**
     * @var string|Where
     */
    protected $where = null;

    /**
     * Constructor
     *
     * @param  null|string|TableIdentifier $table
     */
    public function __construct($table = null)
    {
        if ($table) {
            $this->table($table);
        }
        $this->where = new Where();
        $this->set = new PriorityList();
        $this->set->isLIFO(false);
    }

    /**
     * Specify table for statement
     *
     * @param  string|TableIdentifier $table
     * @return Update
     */
    public function table($table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Set key/value pairs to update
     *
     * @param  array $values Associative array of key values
     * @param  string $flag One of the VALUES_* constants
     * @throws Exception\InvalidArgumentException
     * @return Update
     */
    public function set(array $values, $flag = self::VALUES_SET)
    {
        if ($values === null) {
            throw new Exception\InvalidArgumentException('set() expects an array of values');
        }

        if ($flag == self::VALUES_SET) {
            $this->set->clear();
        }
        $priority = is_numeric($flag) ? $flag : 0;
        foreach ($values as $k => $v) {
            if (!is_string($k)) {
                throw new Exception\InvalidArgumentException('set() expects a string for the value key');
            }
            $this->set->insert($k, $v, $priority);
        }
        return $this;
    }

    /**
     * Create where clause
     *
     * @param  Where|\Closure|string|array $predicate
     * @param  string $combination One of the OP_* constants from Predicate\PredicateSet
     * @throws Exception\InvalidArgumentException
     * @return Update
     */
    public function where($predicate, $combination = Predicate\PredicateSet::OP_AND)
    {
        if ($predicate instanceof Where) {
            $this->where = $predicate;
        } else {
            $this->where->addPredicates($predicate, $combination);
        }
        return $this;
    }

    public function getRawState($key = null)
    {
        $rawState = array(
            'emptyWhereProtection' => $this->emptyWhereProtection,
            'table' => $this->table,
            'set' => $this->set->toArray(),
            'where' => $this->where
        );
        return (isset($key) && array_key_exists($key, $rawState)) ? $rawState[$key] : $rawState;
    }

    protected function processUpdate(?PlatformInterface $platform, ?DriverInterface $driver = null, ?ParameterContainer $parameterContainer = null)
    {
        $setSql = array();
        foreach ($this->set as $column => $value) {
            $prefix = $platform->quoteIdentifier($column) . ' = ';
            if (is_scalar($value) && $parameterContainer) {
                $setSql[] = $prefix . $driver->formatParameterName($column);
                $parameterContainer->offsetSet($column, $value);
            } else {
                $setSql[] = $prefix . $this->resolveColumnValue(
                    $value,
                    $platform,
                    $driver,
                    $parameterContainer
                );
            }
        }

        return sprintf(
            $this->specifications[static::SPECIFICATION_UPDATE],
            $this->resolveTable($this->table, $platform, $driver, $parameterContainer),
            implode(', ', $setSql)
        );
    }

    protected function processWhere(?PlatformInterface $platform, ?DriverInterface $driver = null, ?ParameterContainer $parameterContainer = null)
    {
        if ($this->where->count() == 0) {
            return;
        }
        return sprintf(
            $this->specifications[static::SPECIFICATION_WHERE],
            $this->processExpression($this->where, $platform, $driver, $parameterContainer, 'where')
        );
    }

    /**
     * Variable overloading
     *
     * Proxies to "where" only
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (strtolower($name) == 'where') {
            return $this->where;
        }
    }

    /**
     * __clone
     *
     * Resets the where object each time the Update is cloned.
     *
     * @return void
     */
    public function __clone()
    {
        $this->where = clone $this->where;
        $this->set = clone $this->set;
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql;

use Zend\Db\Adapter\ParameterContainer;
use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\Adapter\Driver\DriverInterface;

class Insert extends AbstractPreparableSql
{
    /**#@+
     * Constants
     *
     * @const
     */
    const SPECIFICATION_INSERT = 'insert';
    const SPECIFICATION_SELECT = 'select';
    const VALUES_MERGE = 'merge';
    const VALUES_SET   = 'set';
    /**#@-*/

    /**
     * @var array Specification array
     */
    protected $specifications = array(
        self::SPECIFICATION_INSERT => 'INSERT INTO %1$s (%2$s) VALUES (%3$s)',
        self::SPECIFICATION_SELECT => 'INSERT INTO %1$s %2$s %3$s',
    );

    /**
     * @var string|TableIdentifier
     */
    protected $table            = null;
    protected $columns          = array();

    /**
     * @var array|Select
     */
    protected $select           = null;

    /**
     * Constructor
     *
     * @param  null|string|TableIdentifier $table
     */
    public function __construct($table = null)
    {
        if ($table) {
            $this->into($table);
        }
    }

    /**
     * Create INTO clause
     *
     * @param  string|TableIdentifier $table
     * @return Insert
     */
    public function into($table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Specify columns
     *
     * @param  array $columns
     * @return Insert
     */
    public function columns(array $columns)
    {
        $this->columns = array_flip($columns);
        return $this;
    }

    /**
     * Specify values to insert
     *
     * @param  array|Select $values
     * @param  string $flag one of VALUES_MERGE or VALUES_SET; defaults to VALUES_SET
     * @throws Exception\InvalidArgumentException
     * @return Insert
     */
    public function values($values, $flag = self::VALUES_SET)
    {
        if ($values instanceof Select) {
            if ($flag == self::VALUES_MERGE) {
                throw new Exception\InvalidArgumentException(
                    'A Zend\Db\Sql\Select instance cannot be provided with the merge flag'
                );
            }
            $this->select = $values;
            return $this;
        }

        if (!is_array($values)) {
            throw new Exception\InvalidArgumentException(
                'values() expects an array of values or Zend\Db\Sql\Select instance'
            );
        }
        if ($this->select && $flag == self::VALUES_MERGE) {
            throw new Exception\InvalidArgumentException(
                'An array of values cannot be provided with the merge flag when a Zend\Db\Sql\Select instance already exists as the value source'
            );
        }

        if ($flag == self::VALUES_SET) {
            $this->columns = $values;
        } else {
            foreach ($values as $column=>$value) {
                $this->columns[$column] = $value;
            }
        }
        return $this;
    }

    /**
     * Create INTO SELECT clause
     *
     * @param Select $select
     * @return self
     */
    public function select(Select $select)
    {
        return $this->values($select);
    }

    /**
     * Get raw state
     *
     * @param string $key
     * @return mixed
     */
    public function getRawState($key = null)
    {
        $rawState = array(
            'table' => $this->table,
            'columns' => array_keys($this->columns),
            'values' => array_values($this->columns)
        );
        return (isset($key) && array_key_exists($key, $rawState)) ? $rawState[$key] : $rawState;
    }

    protected function processInsert(?PlatformInterface $platform, ?DriverInterface $driver = null, ?ParameterContainer $parameterContainer = null)
    {
        if ($this->select) {
            return;
        }
        if (!$this->columns) {
            throw new Exception\InvalidArgumentException('values or select should be present');
        }

        $columns = array();
        $values  = array();
        foreach ($this->columns as $column=>$value) {
            $columns[] = $platform->quoteIdentifier($column);
            if (is_scalar($value) && $parameterContainer) {
                $values[] = $driver->formatParameterName($column);
                $parameterContainer->offsetSet($column, $value);
            } else {
                $values[] = $this->resolveColumnValue(
                    $value,
                    $platform,
                    $driver,
                    $parameterContainer
                );
            }
        }
        return sprintf(
            $this->specifications[static::SPECIFICATION_INSERT],
            $this->resolveTable($this->table, $platform, $driver, $parameterContainer),
            implode(', ', $columns),
            implode(', ', $values)
        );
    }

    protected function processSelect(?PlatformInterface $platform, ?DriverInterface $driver = null, ?ParameterContainer $parameterContainer = null)
    {
        if (!$this->select) {
            return;
        }
        $selectSql = $this->processSubSelect($this->select, $platform, $driver, $parameterContainer);

        $columns = array_map(array($platform, 'quoteIdentifier'), array_keys($this->columns));
        $columns = implode(', ', $columns);

        return sprintf(
            $this->specifications[static::SPECIFICATION_SELECT],
            $this->resolveTable($this->table, $platform, $driver, $parameterContainer),
            $columns ? "($columns)" : "",
            $selectSql
        );
    }

    /**
     * Overloading: variable setting
     *
     * Proxies to values, using VALUES_MERGE strategy
     *
     * @param  string $name
     * @param  mixed $value
     * @return Insert
     */
    public function __set($name, $value)
    {
        $this->columns[$name] = $value;
        return $this;
    }

    /**
     * Overloading: variable unset
     *
     * Proxies to values and columns
     *
     * @param  string $name
     * @throws Exception\InvalidArgumentException
     * @return void
     */
    public function __unset($name)
    {
        if (!isset($this->columns[$name])) {
            throw new Exception\InvalidArgumentException('The key ' . $name . ' was not found in this objects column list');
        }

        unset($this->columns[$name]);
    }

    /**
     * Overloading: variable isset
     *
     * Proxies to columns; does a column of that name exist?
     *
     * @param  string $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->columns[$name]);
    }

    /**
     * Overloading: variable retrieval
     *
     * Retrieves value by column name
     *
     * @param  string $name
     * @throws Exception\InvalidArgumentException
     * @return mixed
     */
    public function __get($name)
    {
        if (!isset($this->columns[$name])) {
            throw new Exception\InvalidArgumentException('The key ' . $name . ' was not found in this objects column list');
        }
        return $this->columns[$name];
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql;

use Zend\Db\Adapter\ParameterContainer;
use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\Adapter\Driver\DriverInterface;

/**
 *
 * @property Where $where
 */
class Delete extends AbstractPreparableSql
{
    /**@#+
     * @const
     */
    const SPECIFICATION_DELETE = 'delete';
    const SPECIFICATION_WHERE = 'where';
    /**@#-*/

    /**
     * {@inheritDoc}
     */
    protected $specifications = array(
        self::SPECIFICATION_DELETE => 'DELETE FROM %1$s',
        self::SPECIFICATION_WHERE => 'WHERE %1$s'
    );

    /**
     * @var string|TableIdentifier
     */
    protected $table = '';

    /**
     * @var bool
     */
    protected $emptyWhereProtection = true;

    /**
     * @var array
     */
    protected $set = array();

    /**
     * @var null|string|Where
     */
    protected $where = null;

    /**
     * Constructor
     *
     * @param  null|string|TableIdentifier $table
     */
    public function __construct($table = null)
    {
        if ($table) {
            $this->from($table);
        }
        $this->where = new Where();
    }

    /**
     * Create from statement
     *
     * @param  string|TableIdentifier $table
     * @return Delete
     */
    public function from($table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * @param null $key
     *
     * @return mixed
     */
    public function getRawState($key = null)
    {
        $rawState = array(
            'emptyWhereProtection' => $this->emptyWhereProtection,
            'table' => $this->table,
            'set' => $this->set,
            'where' => $this->where
        );
        return (isset($key) && array_key_exists($key, $rawState)) ? $rawState[$key] : $rawState;
    }

    /**
     * Create where clause
     *
     * @param  Where|\Closure|string|array $predicate
     * @param  string $combination One of the OP_* constants from Predicate\PredicateSet
     *
     * @return Delete
     */
    public function where($predicate, $combination = Predicate\PredicateSet::OP_AND)
    {
        if ($predicate instanceof Where) {
            $this->where = $predicate;
        } else {
            $this->where->addPredicates($predicate, $combination);
        }
        return $this;
    }

    /**
     * @param PlatformInterface       $platform
     * @param DriverInterface|null    $driver
     * @param ParameterContainer|null $parameterContainer
     *
     * @return string
     */
    protected function processDelete(?PlatformInterface $platform, ?DriverInterface $driver = null, ?ParameterContainer $parameterContainer = null)
    {
        return sprintf(
            $this->specifications[static::SPECIFICATION_DELETE],
            $this->resolveTable($this->table, $platform, $driver, $parameterContainer)
        );
    }

    /**
     * @param PlatformInterface       $platform
     * @param DriverInterface|null    $driver
     * @param ParameterContainer|null $parameterContainer
     *
     * @return null|string
     */
    protected function processWhere(?PlatformInterface $platform, ?DriverInterface $driver = null, ?ParameterContainer $parameterContainer = null)
    {
        if ($this->where->count() == 0) {
            return;
        }

        return sprintf(
            $this->specifications[static::SPECIFICATION_WHERE],
            $this->processExpression($this->where, $platform, $driver, $parameterContainer, 'where')
        );
    }

    /**
     * Property overloading
     *
     * Overloads "where" only.
     *
     * @param  string $name
     *
     * @return Where|null
     */
    public function __get($name)
    {
        switch (strtolower($name)) {
            case 'where':
                return $this->where;
        }
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Platform\SqlServer;

use Zend\Db\Adapter\Driver\DriverInterface;
use Zend\Db\Adapter\ParameterContainer;
use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\Sql\Platform\PlatformDecoratorInterface;
use Zend\Db\Sql\Select;

class SelectDecorator extends Select implements PlatformDecoratorInterface
{
    /**
     * @var Select
     */
    protected $subject = null;

    /**
     * @param Select $select
     */
    public function setSubject($select)
    {
        $this->subject = $select;
    }

    protected function localizeVariables()
    {
        parent::localizeVariables();
        // set specifications
        unset($this->specifications[self::LIMIT]);
        unset($this->specifications[self::OFFSET]);

        $this->specifications['LIMITOFFSET'] = null;
    }

    /**
     * @param PlatformInterface $platform
     * @param DriverInterface $driver
     * @param ParameterContainer $parameterContainer
     * @param $sqls
     * @param $parameters
     * @return null
     */
    protected function processLimitOffset(?PlatformInterface $platform, ?DriverInterface $driver = null, ?ParameterContainer $parameterContainer = null, &$sqls=null, &$parameters=null)
    {
        if ($this->limit === null && $this->offset === null) {
            return;
        }

        $selectParameters = $parameters[self::SELECT];

        $starSuffix = $platform->getIdentifierSeparator() . self::SQL_STAR;
        foreach ($selectParameters[0] as $i => $columnParameters) {
            if ($columnParameters[0] == self::SQL_STAR || (isset($columnParameters[1]) && $columnParameters[1] == self::SQL_STAR) || strpos($columnParameters[0], $starSuffix)) {
                $selectParameters[0] = array(array(self::SQL_STAR));
                break;
            }
            if (isset($columnParameters[1])) {
                array_shift($columnParameters);
                $selectParameters[0][$i] = $columnParameters;
            }
        }

        // first, produce column list without compound names (using the AS portion only)
        array_unshift($sqls, $this->createSqlFromSpecificationAndParameters(
            array('SELECT %1$s FROM (' => current($this->specifications[self::SELECT])),
            $selectParameters
        ));

        if ($parameterContainer) {
            // create bottom part of query, with offset and limit using row_number
            $limitParamName = $driver->formatParameterName('limit');
            $offsetParamName = $driver->formatParameterName('offset');
            $offsetForSumParamName = $driver->formatParameterName('offsetForSum');
            array_push($sqls, ') AS [ZEND_SQL_SERVER_LIMIT_OFFSET_EMULATION] WHERE [ZEND_SQL_SERVER_LIMIT_OFFSET_EMULATION].[__ZEND_ROW_NUMBER] BETWEEN '
                . $offsetParamName . '+1 AND ' . $limitParamName . '+' . $offsetForSumParamName);
            $parameterContainer->offsetSet('offset', $this->offset);
            $parameterContainer->offsetSet('limit', $this->limit);
            $parameterContainer->offsetSetReference('offsetForSum', 'offset');
        } else {
            array_push($sqls, ') AS [ZEND_SQL_SERVER_LIMIT_OFFSET_EMULATION] WHERE [ZEND_SQL_SERVER_LIMIT_OFFSET_EMULATION].[__ZEND_ROW_NUMBER] BETWEEN '
                . (int) $this->offset . '+1 AND '
                . (int) $this->limit . '+' . (int) $this->offset
            );
        }

        if (isset($sqls[self::ORDER])) {
            $orderBy = $sqls[self::ORDER];
            unset($sqls[self::ORDER]);
        } else {
            $orderBy = 'ORDER BY (SELECT 1)';
        }

        // add a column for row_number() using the order specification
        $parameters[self::SELECT][0][] = array('ROW_NUMBER() OVER (' . $orderBy . ')', '[__ZEND_ROW_NUMBER]');

        $sqls[self::SELECT] = $this->createSqlFromSpecificationAndParameters(
            $this->specifications[self::SELECT],
            $parameters[self::SELECT]
        );
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Ddl;

use Zend\Db\Sql\SqlInterface as BaseSqlInterface;

interface SqlInterface extends BaseSqlInterface
{
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Ddl;

use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\Sql\AbstractSql;

class CreateTable extends AbstractSql implements SqlInterface
{
    const COLUMNS     = 'columns';
    const CONSTRAINTS = 'constraints';
    const TABLE       = 'table';

    /**
     * @var Column\ColumnInterface[]
     */
    protected $columns = array();

    /**
     * @var string[]
     */
    protected $constraints = array();

    /**
     * @var bool
     */
    protected $isTemporary = false;

    /**
     * {@inheritDoc}
     */
    protected $specifications = array(
        self::TABLE => 'CREATE %1$sTABLE %2$s (',
        self::COLUMNS  => array(
            "\n    %1\$s" => array(
                array(1 => '%1$s', 'combinedby' => ",\n    ")
            )
        ),
        'combinedBy' => ",",
        self::CONSTRAINTS => array(
            "\n    %1\$s" => array(
                array(1 => '%1$s', 'combinedby' => ",\n    ")
            )
        ),
        'statementEnd' => '%1$s',
    );

    /**
     * @var string
     */
    protected $table = '';

    /**
     * @param string $table
     * @param bool   $isTemporary
     */
    public function __construct($table = '', $isTemporary = false)
    {
        $this->table = $table;
        $this->setTemporary($isTemporary);
    }

    /**
     * @param  bool $temporary
     * @return self
     */
    public function setTemporary($temporary)
    {
        $this->isTemporary = (bool) $temporary;
        return $this;
    }

    /**
     * @return bool
     */
    public function isTemporary()
    {
        return $this->isTemporary;
    }

    /**
     * @param  string $name
     * @return self
     */
    public function setTable($name)
    {
        $this->table = $name;
        return $this;
    }

    /**
     * @param  Column\ColumnInterface $column
     * @return self
     */
    public function addColumn(Column\ColumnInterface $column)
    {
        $this->columns[] = $column;
        return $this;
    }

    /**
     * @param  Constraint\ConstraintInterface $constraint
     * @return self
     */
    public function addConstraint(Constraint\ConstraintInterface $constraint)
    {
        $this->constraints[] = $constraint;
        return $this;
    }

    /**
     * @param  string|null $key
     * @return array
     */
    public function getRawState($key = null)
    {
        $rawState = array(
            self::COLUMNS     => $this->columns,
            self::CONSTRAINTS => $this->constraints,
            self::TABLE       => $this->table,
        );

        return (isset($key) && array_key_exists($key, $rawState)) ? $rawState[$key] : $rawState;
    }

    /**
     * @param PlatformInterface $adapterPlatform
     *
     * @return string[]
     */
    protected function processTable(?PlatformInterface $adapterPlatform = null)
    {
        return array(
            $this->isTemporary ? 'TEMPORARY ' : '',
            $adapterPlatform->quoteIdentifier($this->table),
        );
    }

    /**
     * @param PlatformInterface $adapterPlatform
     *
     * @return string[][]|null
     */
    protected function processColumns(?PlatformInterface $adapterPlatform = null)
    {
        if (! $this->columns) {
            return;
        }

        $sqls = array();

        foreach ($this->columns as $column) {
            $sqls[] = $this->processExpression($column, $adapterPlatform);
        }

        return array($sqls);
    }

    /**
     * @param PlatformInterface $adapterPlatform
     *
     * @return array|string
     */
    protected function processCombinedby(?PlatformInterface $adapterPlatform = null)
    {
        if ($this->constraints && $this->columns) {
            return $this->specifications['combinedBy'];
        }
    }

    /**
     * @param PlatformInterface $adapterPlatform
     *
     * @return string[][]|null
     */
    protected function processConstraints(?PlatformInterface $adapterPlatform = null)
    {
        if (!$this->constraints) {
            return;
        }

        $sqls = array();

        foreach ($this->constraints as $constraint) {
            $sqls[] = $this->processExpression($constraint, $adapterPlatform);
        }

        return array($sqls);
    }

    /**
     * @param PlatformInterface $adapterPlatform
     *
     * @return string[]
     */
    protected function processStatementEnd(?PlatformInterface $adapterPlatform = null)
    {
        return array("\n)");
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Platform\SqlServer\Ddl;

use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\Sql\Ddl\CreateTable;
use Zend\Db\Sql\Platform\PlatformDecoratorInterface;

class CreateTableDecorator extends CreateTable implements PlatformDecoratorInterface
{
    /**
     * @var CreateTable
     */
    protected $subject;

    /**
     * @param CreateTable $subject
     * @return self
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @param PlatformInterface $adapterPlatform
     * @return array
     */
    protected function processTable(?PlatformInterface $adapterPlatform = null)
    {
        $table = ($this->isTemporary ? '#' : '') . ltrim($this->table, '#');
        return array(
            '',
            $adapterPlatform->quoteIdentifier($table),
        );
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Ddl;

use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\Sql\AbstractSql;

class AlterTable extends AbstractSql implements SqlInterface
{
    const ADD_COLUMNS      = 'addColumns';
    const ADD_CONSTRAINTS  = 'addConstraints';
    const CHANGE_COLUMNS   = 'changeColumns';
    const DROP_COLUMNS     = 'dropColumns';
    const DROP_CONSTRAINTS = 'dropConstraints';
    const TABLE            = 'table';

    /**
     * @var array
     */
    protected $addColumns = array();

    /**
     * @var array
     */
    protected $addConstraints = array();

    /**
     * @var array
     */
    protected $changeColumns = array();

    /**
     * @var array
     */
    protected $dropColumns = array();

    /**
     * @var array
     */
    protected $dropConstraints = array();

    /**
     * Specifications for Sql String generation
     * @var array
     */
    protected $specifications = array(
        self::TABLE => "ALTER TABLE %1\$s\n",
        self::ADD_COLUMNS  => array(
            "%1\$s" => array(
                array(1 => "ADD COLUMN %1\$s,\n", 'combinedby' => "")
            )
        ),
        self::CHANGE_COLUMNS  => array(
            "%1\$s" => array(
                array(2 => "CHANGE COLUMN %1\$s %2\$s,\n", 'combinedby' => ""),
            )
        ),
        self::DROP_COLUMNS  => array(
            "%1\$s" => array(
                array(1 => "DROP COLUMN %1\$s,\n", 'combinedby' => ""),
            )
        ),
        self::ADD_CONSTRAINTS  => array(
            "%1\$s" => array(
                array(1 => "ADD %1\$s,\n", 'combinedby' => ""),
            )
        ),
        self::DROP_CONSTRAINTS  => array(
            "%1\$s" => array(
                array(1 => "DROP CONSTRAINT %1\$s,\n", 'combinedby' => ""),
            )
        )
    );

    /**
     * @var string
     */
    protected $table = '';

    /**
     * @param string $table
     */
    public function __construct($table = '')
    {
        ($table) ? $this->setTable($table) : null;
    }

    /**
     * @param  string $name
     * @return self
     */
    public function setTable($name)
    {
        $this->table = $name;

        return $this;
    }

    /**
     * @param  Column\ColumnInterface $column
     * @return self
     */
    public function addColumn(Column\ColumnInterface $column)
    {
        $this->addColumns[] = $column;

        return $this;
    }

    /**
     * @param  string $name
     * @param  Column\ColumnInterface $column
     * @return self
     */
    public function changeColumn($name, Column\ColumnInterface $column)
    {
        $this->changeColumns[$name] = $column;

        return $this;
    }

    /**
     * @param  string $name
     * @return self
     */
    public function dropColumn($name)
    {
        $this->dropColumns[] = $name;

        return $this;
    }

    /**
     * @param  string $name
     * @return self
     */
    public function dropConstraint($name)
    {
        $this->dropConstraints[] = $name;

        return $this;
    }

    /**
     * @param  Constraint\ConstraintInterface $constraint
     * @return self
     */
    public function addConstraint(Constraint\ConstraintInterface $constraint)
    {
        $this->addConstraints[] = $constraint;

        return $this;
    }

    /**
     * @param  string|null $key
     * @return array
     */
    public function getRawState($key = null)
    {
        $rawState = array(
            self::TABLE => $this->table,
            self::ADD_COLUMNS => $this->addColumns,
            self::DROP_COLUMNS => $this->dropColumns,
            self::CHANGE_COLUMNS => $this->changeColumns,
            self::ADD_CONSTRAINTS => $this->addConstraints,
            self::DROP_CONSTRAINTS => $this->dropConstraints,
        );

        return (isset($key) && array_key_exists($key, $rawState)) ? $rawState[$key] : $rawState;
    }

    protected function processTable(?PlatformInterface $adapterPlatform = null)
    {
        return array($adapterPlatform->quoteIdentifier($this->table));
    }

    protected function processAddColumns(?PlatformInterface $adapterPlatform = null)
    {
        $sqls = array();
        foreach ($this->addColumns as $column) {
            $sqls[] = $this->processExpression($column, $adapterPlatform);
        }

        return array($sqls);
    }

    protected function processChangeColumns(?PlatformInterface $adapterPlatform = null)
    {
        $sqls = array();
        foreach ($this->changeColumns as $name => $column) {
            $sqls[] = array(
                $adapterPlatform->quoteIdentifier($name),
                $this->processExpression($column, $adapterPlatform)
            );
        }

        return array($sqls);
    }

    protected function processDropColumns(?PlatformInterface $adapterPlatform = null)
    {
        $sqls = array();
        foreach ($this->dropColumns as $column) {
            $sqls[] = $adapterPlatform->quoteIdentifier($column);
        }

        return array($sqls);
    }

    protected function processAddConstraints(?PlatformInterface $adapterPlatform = null)
    {
        $sqls = array();
        foreach ($this->addConstraints as $constraint) {
            $sqls[] = $this->processExpression($constraint, $adapterPlatform);
        }

        return array($sqls);
    }

    protected function processDropConstraints(?PlatformInterface $adapterPlatform = null)
    {
        $sqls = array();
        foreach ($this->dropConstraints as $constraint) {
            $sqls[] = $adapterPlatform->quoteIdentifier($constraint);
        }

        return array($sqls);
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Platform\Mysql\Ddl;

use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\Sql\Ddl\AlterTable;
use Zend\Db\Sql\Platform\PlatformDecoratorInterface;

class AlterTableDecorator extends AlterTable implements PlatformDecoratorInterface
{
    /**
     * @var AlterTable
     */
    protected $subject;

    /**
     * @var int[]
     */
    protected $columnOptionSortOrder = array(
        'unsigned'      => 0,
        'zerofill'      => 1,
        'identity'      => 2,
        'serial'        => 2,
        'autoincrement' => 2,
        'comment'       => 3,
        'columnformat'  => 4,
        'format'        => 4,
        'storage'       => 5,
    );

    /**
     * @param AlterTable $subject
     * @return \Zend\Db\Sql\Platform\PlatformDecoratorInterface
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @param string $sql
     * @return array
     */
    protected function getSqlInsertOffsets($sql)
    {
        $sqlLength   = strlen($sql);
        $insertStart = array();

        foreach (array('NOT NULL', 'NULL', 'DEFAULT', 'UNIQUE', 'PRIMARY', 'REFERENCES') as $needle) {
            $insertPos = strpos($sql, ' ' . $needle);

            if ($insertPos !== false) {
                switch ($needle) {
                    case 'REFERENCES':
                        $insertStart[2] = !isset($insertStart[2]) ? $insertPos : $insertStart[2];
                    // no break
                    case 'PRIMARY':
                    case 'UNIQUE':
                        $insertStart[1] = !isset($insertStart[1]) ? $insertPos : $insertStart[1];
                    // no break
                    default:
                        $insertStart[0] = !isset($insertStart[0]) ? $insertPos : $insertStart[0];
                }
            }
        }

        foreach (range(0, 3) as $i) {
            $insertStart[$i] = isset($insertStart[$i]) ? $insertStart[$i] : $sqlLength;
        }

        return $insertStart;
    }

    /**
     * @param PlatformInterface $adapterPlatform
     * @return array
     */
    protected function processAddColumns(?PlatformInterface $adapterPlatform = null)
    {
        $sqls = array();

        foreach ($this->addColumns as $i => $column) {
            $sql           = $this->processExpression($column, $adapterPlatform);
            $insertStart   = $this->getSqlInsertOffsets($sql);
            $columnOptions = $column->getOptions();

            uksort($columnOptions, array($this, 'compareColumnOptions'));

            foreach ($columnOptions as $coName => $coValue) {
                $insert = '';

                if (! $coValue) {
                    continue;
                }

                switch ($this->normalizeColumnOption($coName)) {
                    case 'unsigned':
                        $insert = ' UNSIGNED';
                        $j = 0;
                        break;
                    case 'zerofill':
                        $insert = ' ZEROFILL';
                        $j = 0;
                        break;
                    case 'identity':
                    case 'serial':
                    case 'autoincrement':
                        $insert = ' AUTO_INCREMENT';
                        $j = 1;
                        break;
                    case 'comment':
                        $insert = ' COMMENT ' . $adapterPlatform->quoteValue($coValue);
                        $j = 2;
                        break;
                    case 'columnformat':
                    case 'format':
                        $insert = ' COLUMN_FORMAT ' . strtoupper($coValue);
                        $j = 2;
                        break;
                    case 'storage':
                        $insert = ' STORAGE ' . strtoupper($coValue);
                        $j = 2;
                        break;
                }

                if ($insert) {
                    $j = isset($j) ? $j : 0;
                    $sql = substr_replace($sql, $insert, $insertStart[$j], 0);
                    $insertStartCount = count($insertStart);
                    for (; $j < $insertStartCount; ++$j) {
                        $insertStart[$j] += strlen($insert);
                    }
                }
            }
            $sqls[$i] = $sql;
        }
        return array($sqls);
    }

    /**
     * @param PlatformInterface $adapterPlatform
     * @return array
     */
    protected function processChangeColumns(?PlatformInterface $adapterPlatform = null)
    {
        $sqls = array();
        foreach ($this->changeColumns as $name => $column) {
            $sql           = $this->processExpression($column, $adapterPlatform);
            $insertStart   = $this->getSqlInsertOffsets($sql);
            $columnOptions = $column->getOptions();

            uksort($columnOptions, array($this, 'compareColumnOptions'));

            foreach ($columnOptions as $coName => $coValue) {
                $insert = '';

                if (! $coValue) {
                    continue;
                }

                switch ($this->normalizeColumnOption($coName)) {
                    case 'unsigned':
                        $insert = ' UNSIGNED';
                        $j = 0;
                        break;
                    case 'zerofill':
                        $insert = ' ZEROFILL';
                        $j = 0;
                        break;
                    case 'identity':
                    case 'serial':
                    case 'autoincrement':
                        $insert = ' AUTO_INCREMENT';
                        $j = 1;
                        break;
                    case 'comment':
                        $insert = ' COMMENT ' . $adapterPlatform->quoteValue($coValue);
                        $j = 2;
                        break;
                    case 'columnformat':
                    case 'format':
                        $insert = ' COLUMN_FORMAT ' . strtoupper($coValue);
                        $j = 2;
                        break;
                    case 'storage':
                        $insert = ' STORAGE ' . strtoupper($coValue);
                        $j = 2;
                        break;
                }

                if ($insert) {
                    $j = isset($j) ? $j : 0;
                    $sql = substr_replace($sql, $insert, $insertStart[$j], 0);
                    $insertStartCount = count($insertStart);
                    for (; $j < $insertStartCount; ++$j) {
                        $insertStart[$j] += strlen($insert);
                    }
                }
            }
            $sqls[] = array(
                $adapterPlatform->quoteIdentifier($name),
                $sql
            );
        }

        return array($sqls);
    }

    /**
     * @param string $name
     *
     * @return string
     */
    private function normalizeColumnOption($name)
    {
        return strtolower(str_replace(array('-', '_', ' '), '', $name));
    }

    /**
     * @internal @private this method is only public for PHP 5.3 compatibility purposes.
     *
     * @param string $columnA
     * @param string $columnB
     *
     * @return int
     */
    public function compareColumnOptions($columnA, $columnB)
    {
        $columnA = $this->normalizeColumnOption($columnA);
        $columnA = isset($this->columnOptionSortOrder[$columnA])
            ? $this->columnOptionSortOrder[$columnA] : count($this->columnOptionSortOrder);

        $columnB = $this->normalizeColumnOption($columnB);
        $columnB = isset($this->columnOptionSortOrder[$columnB])
            ? $this->columnOptionSortOrder[$columnB] : count($this->columnOptionSortOrder);

        return $columnA - $columnB;
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Platform\Mysql\Ddl;

use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\Sql\Ddl\CreateTable;
use Zend\Db\Sql\Platform\PlatformDecoratorInterface;

class CreateTableDecorator extends CreateTable implements PlatformDecoratorInterface
{
    /**
     * @var CreateTable
     */
    protected $subject;

    /**
     * @var int[]
     */
    protected $columnOptionSortOrder = array(
        'unsigned'      => 0,
        'zerofill'      => 1,
        'identity'      => 2,
        'serial'        => 2,
        'autoincrement' => 2,
        'comment'       => 3,
        'columnformat'  => 4,
        'format'        => 4,
        'storage'       => 5,
    );

    /**
     * @param CreateTable $subject
     *
     * @return self
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @param string $sql
     * @return array
     */
    protected function getSqlInsertOffsets($sql)
    {
        $sqlLength   = strlen($sql);
        $insertStart = array();

        foreach (array('NOT NULL', 'NULL', 'DEFAULT', 'UNIQUE', 'PRIMARY', 'REFERENCES') as $needle) {
            $insertPos = strpos($sql, ' ' . $needle);

            if ($insertPos !== false) {
                switch ($needle) {
                    case 'REFERENCES':
                        $insertStart[2] = !isset($insertStart[2]) ? $insertPos : $insertStart[2];
                        // no break
                    case 'PRIMARY':
                    case 'UNIQUE':
                        $insertStart[1] = !isset($insertStart[1]) ? $insertPos : $insertStart[1];
                        // no break
                    default:
                        $insertStart[0] = !isset($insertStart[0]) ? $insertPos : $insertStart[0];
                }
            }
        }

        foreach (range(0, 3) as $i) {
            $insertStart[$i] = isset($insertStart[$i]) ? $insertStart[$i] : $sqlLength;
        }

        return $insertStart;
    }

    /**
     * {@inheritDoc}
     */
    protected function processColumns(?PlatformInterface $platform = null)
    {
        if (! $this->columns) {
            return;
        }

        $sqls = array();

        foreach ($this->columns as $i => $column) {
            $sql           = $this->processExpression($column, $platform);
            $insertStart   = $this->getSqlInsertOffsets($sql);
            $columnOptions = $column->getOptions();

            uksort($columnOptions, array($this, 'compareColumnOptions'));

            foreach ($columnOptions as $coName => $coValue) {
                $insert = '';

                if (! $coValue) {
                    continue;
                }

                switch ($this->normalizeColumnOption($coName)) {
                    case 'unsigned':
                        $insert = ' UNSIGNED';
                        $j = 0;
                        break;
                    case 'zerofill':
                        $insert = ' ZEROFILL';
                        $j = 0;
                        break;
                    case 'identity':
                    case 'serial':
                    case 'autoincrement':
                        $insert = ' AUTO_INCREMENT';
                        $j = 1;
                        break;
                    case 'comment':
                        $insert = ' COMMENT ' . $platform->quoteValue($coValue);
                        $j = 2;
                        break;
                    case 'columnformat':
                    case 'format':
                        $insert = ' COLUMN_FORMAT ' . strtoupper($coValue);
                        $j = 2;
                        break;
                    case 'storage':
                        $insert = ' STORAGE ' . strtoupper($coValue);
                        $j = 2;
                        break;
                }

                if ($insert) {
                    $j = isset($j) ? $j : 0;
                    $sql = substr_replace($sql, $insert, $insertStart[$j], 0);
                    $insertStartCount = count($insertStart);
                    for (; $j < $insertStartCount; ++$j) {
                        $insertStart[$j] += strlen($insert);
                    }
                }
            }

            $sqls[$i] = $sql;
        }

        return array($sqls);
    }

    /**
     * @param string $name
     *
     * @return string
     */
    private function normalizeColumnOption($name)
    {
        return strtolower(str_replace(array('-', '_', ' '), '', $name));
    }

    /**
     * @internal @private this method is only public for PHP 5.3 compatibility purposes.
     *
     * @param string $columnA
     * @param string $columnB
     *
     * @return int
     */
    public function compareColumnOptions($columnA, $columnB)
    {
        $columnA = $this->normalizeColumnOption($columnA);
        $columnA = isset($this->columnOptionSortOrder[$columnA])
            ? $this->columnOptionSortOrder[$columnA] : count($this->columnOptionSortOrder);

        $columnB = $this->normalizeColumnOption($columnB);
        $columnB = isset($this->columnOptionSortOrder[$columnB])
            ? $this->columnOptionSortOrder[$columnB] : count($this->columnOptionSortOrder);

        return $columnA - $columnB;
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Platform\IbmDb2;

use Zend\Db\Adapter\Driver\DriverInterface;
use Zend\Db\Adapter\ParameterContainer;
use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\Sql\Platform\PlatformDecoratorInterface;
use Zend\Db\Sql\Select;

class SelectDecorator extends Select implements PlatformDecoratorInterface
{
    /**
     * @var bool
     */
    protected $isSelectContainDistinct= false;

    /**
     * @var Select
     */
    protected $subject = null;

    /**
     * @return bool
     */
    public function getIsSelectContainDistinct()
    {
        return $this->isSelectContainDistinct;
    }

    /**
     * @param boolean $isSelectContainDistinct
     */
    public function setIsSelectContainDistinct($isSelectContainDistinct)
    {
        $this->isSelectContainDistinct = $isSelectContainDistinct;
    }

    /**
     * @param Select $select
     */
    public function setSubject($select)
    {
        $this->subject = $select;
    }

    /**
     * @see Select::renderTable
     */
    protected function renderTable($table, $alias = null)
    {
        return $table . ' ' . $alias;
    }

    protected function localizeVariables()
    {
        parent::localizeVariables();
        // set specifications
        unset($this->specifications[self::LIMIT]);
        unset($this->specifications[self::OFFSET]);

        $this->specifications['LIMITOFFSET'] = null;
    }

    /**
     * @param  PlatformInterface  $platform
     * @param  DriverInterface    $driver
     * @param  ParameterContainer $parameterContainer
     * @param  array              $sqls
     * @param  array              $parameters
     */
    protected function processLimitOffset(?PlatformInterface $platform, ?DriverInterface $driver = null, ?ParameterContainer $parameterContainer = null, &$sqls=null, &$parameters=null)
    {
        if ($this->limit === null && $this->offset === null) {
            return;
        }

        $selectParameters = $parameters[self::SELECT];

        $starSuffix = $platform->getIdentifierSeparator() . self::SQL_STAR;
        foreach ($selectParameters[0] as $i => $columnParameters) {
            if ($columnParameters[0] == self::SQL_STAR
                || (isset($columnParameters[1]) && $columnParameters[1] == self::SQL_STAR)
                || strpos($columnParameters[0], $starSuffix)
            ) {
                $selectParameters[0] = array(array(self::SQL_STAR));
                break;
            }

            if (isset($columnParameters[1])) {
                array_shift($columnParameters);
                $selectParameters[0][$i] = $columnParameters;
            }
        }

        // first, produce column list without compound names (using the AS portion only)
        array_unshift($sqls, $this->createSqlFromSpecificationAndParameters(
            array('SELECT %1$s FROM (' => current($this->specifications[self::SELECT])),
            $selectParameters
        ));

        if (preg_match('/DISTINCT/i', $sqls[0])) {
            $this->setIsSelectContainDistinct(true);
        }

        if ($parameterContainer) {
            // create bottom part of query, with offset and limit using row_number
            $limitParamName        = $driver->formatParameterName('limit');
            $offsetParamName       = $driver->formatParameterName('offset');

            array_push($sqls, sprintf(
                ") AS ZEND_IBMDB2_SERVER_LIMIT_OFFSET_EMULATION WHERE ZEND_IBMDB2_SERVER_LIMIT_OFFSET_EMULATION.ZEND_DB_ROWNUM BETWEEN %s AND %s",
                $offsetParamName,
                $limitParamName
            ));

            if ((int) $this->offset > 0) {
                $parameterContainer->offsetSet('offset', (int) $this->offset + 1);
            } else {
                $parameterContainer->offsetSet('offset', (int) $this->offset);
            }

            $parameterContainer->offsetSet('limit', (int) $this->limit + (int) $this->offset);
        } else {
            if ((int) $this->offset > 0) {
                $offset = (int) $this->offset + 1;
            } else {
                $offset = (int) $this->offset;
            }

            array_push($sqls, sprintf(
                ") AS ZEND_IBMDB2_SERVER_LIMIT_OFFSET_EMULATION WHERE ZEND_IBMDB2_SERVER_LIMIT_OFFSET_EMULATION.ZEND_DB_ROWNUM BETWEEN %d AND %d",
                $offset,
                (int) $this->limit + (int) $this->offset
            ));
        }

        if (isset($sqls[self::ORDER])) {
            $orderBy = $sqls[self::ORDER];
            unset($sqls[self::ORDER]);
        } else {
            $orderBy = '';
        }

        // add a column for row_number() using the order specification //dense_rank()
        if ($this->getIsSelectContainDistinct()) {
            $parameters[self::SELECT][0][] = array('DENSE_RANK() OVER (' . $orderBy . ')', 'ZEND_DB_ROWNUM');
        } else {
            $parameters[self::SELECT][0][] = array('ROW_NUMBER() OVER (' . $orderBy . ')', 'ZEND_DB_ROWNUM');
        }

        $sqls[self::SELECT] = $this->createSqlFromSpecificationAndParameters(
            $this->specifications[self::SELECT],
            $parameters[self::SELECT]
        );
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Platform\Oracle;

use Zend\Db\Adapter\Driver\DriverInterface;
use Zend\Db\Adapter\ParameterContainer;
use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\Sql\Platform\PlatformDecoratorInterface;
use Zend\Db\Sql\Select;

class SelectDecorator extends Select implements PlatformDecoratorInterface
{
    /**
     * @var Select
     */
    protected $subject = null;

    /**
     * @param Select $select
     */
    public function setSubject($select)
    {
        $this->subject = $select;
    }

    /**
     * @see \Zend\Db\Sql\Select::renderTable
     */
    protected function renderTable($table, $alias = null)
    {
        return $table . ($alias ? ' ' . $alias : '');
    }

    protected function localizeVariables()
    {
        parent::localizeVariables();
        unset($this->specifications[self::LIMIT]);
        unset($this->specifications[self::OFFSET]);

        $this->specifications['LIMITOFFSET'] = null;
    }

    /**
     * @param PlatformInterface $platform
     * @param DriverInterface $driver
     * @param ParameterContainer $parameterContainer
     * @param $sqls
     * @param $parameters
     * @return null
     */
    protected function processLimitOffset(?PlatformInterface $platform, ?DriverInterface $driver = null, ?ParameterContainer $parameterContainer = null, &$sqls=null, &$parameters=null)
    {
        if ($this->limit === null && $this->offset === null) {
            return;
        }

        $selectParameters = $parameters[self::SELECT];

        $starSuffix = $platform->getIdentifierSeparator() . self::SQL_STAR;
        foreach ($selectParameters[0] as $i => $columnParameters) {
            if ($columnParameters[0] == self::SQL_STAR || (isset($columnParameters[1]) && $columnParameters[1] == self::SQL_STAR) || strpos($columnParameters[0], $starSuffix)) {
                $selectParameters[0] = array(array(self::SQL_STAR));
                break;
            }
            if (isset($columnParameters[1])) {
                array_shift($columnParameters);
                $selectParameters[0][$i] = $columnParameters;
            }
        }

        if ($this->offset === null) {
            $this->offset = 0;
        }

        // first, produce column list without compound names (using the AS portion only)
        array_unshift($sqls, $this->createSqlFromSpecificationAndParameters(
            array('SELECT %1$s FROM (SELECT b.%1$s, rownum b_rownum FROM (' => current($this->specifications[self::SELECT])), $selectParameters
        ));

        if ($parameterContainer) {
            if ($this->limit === null) {
                array_push($sqls, ') b ) WHERE b_rownum > (:offset)');
                $parameterContainer->offsetSet('offset', $this->offset, $parameterContainer::TYPE_INTEGER);
            } else {
                // create bottom part of query, with offset and limit using row_number
                array_push($sqls, ') b WHERE rownum <= (:offset+:limit)) WHERE b_rownum >= (:offset + 1)');
                $parameterContainer->offsetSet('offset', $this->offset, $parameterContainer::TYPE_INTEGER);
                $parameterContainer->offsetSet('limit', $this->limit, $parameterContainer::TYPE_INTEGER);
            }
        } else {
            if ($this->limit === null) {
                array_push($sqls, ') b ) WHERE b_rownum > ('. (int) $this->offset. ')'
                );
            } else {
                array_push($sqls, ') b WHERE rownum <= ('
                        . (int) $this->offset
                        . '+'
                        . (int) $this->limit
                        . ')) WHERE b_rownum >= ('
                        . (int) $this->offset
                        . ' + 1)'
                );
            }
        }

        $sqls[self::SELECT] = $this->createSqlFromSpecificationAndParameters(
            $this->specifications[self::SELECT], $parameters[self::SELECT]
        );
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Platform\Mysql;

use Zend\Db\Adapter\Driver\DriverInterface;
use Zend\Db\Adapter\ParameterContainer;
use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\Sql\Platform\PlatformDecoratorInterface;
use Zend\Db\Sql\Select;

class SelectDecorator extends Select implements PlatformDecoratorInterface
{
    /**
     * @var Select
     */
    protected $subject = null;

    /**
     * @param Select $select
     */
    public function setSubject($select)
    {
        $this->subject = $select;
    }

    protected function localizeVariables()
    {
        parent::localizeVariables();
        if ($this->limit === null && $this->offset !== null) {
            $this->specifications[self::LIMIT] = 'LIMIT 18446744073709551615';
        }
    }

    protected function processLimit(?PlatformInterface $platform, ?DriverInterface $driver = null, ?ParameterContainer $parameterContainer = null)
    {
        if ($this->limit === null && $this->offset !== null) {
            return array('');
        }
        if ($this->limit === null) {
            return;
        }
        if ($parameterContainer) {
            $parameterContainer->offsetSet('limit', $this->limit, ParameterContainer::TYPE_INTEGER);
            return array($driver->formatParameterName('limit'));
        }

        return array($this->limit);
    }

    protected function processOffset(?PlatformInterface $platform, ?DriverInterface $driver = null, ?ParameterContainer $parameterContainer = null)
    {
        if ($this->offset === null) {
            return;
        }
        if ($parameterContainer) {
            $parameterContainer->offsetSet('offset', $this->offset, ParameterContainer::TYPE_INTEGER);
            return array($driver->formatParameterName('offset'));
        }

        return array($this->offset);
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql;

interface ExpressionInterface
{
    const TYPE_IDENTIFIER = 'identifier';
    const TYPE_VALUE = 'value';
    const TYPE_LITERAL = 'literal';
    const TYPE_SELECT = 'select';

    /**
     * @abstract
     *
     * @return array of array|string should return an array in the format:
     *
     * array (
     *    // a sprintf formatted string
     *    string $specification,
     *
     *    // the values for the above sprintf formatted string
     *    array $values,
     *
     *    // an array of equal length of the $values array, with either TYPE_IDENTIFIER or TYPE_VALUE for each value
     *    array $types,
     * )
     *
     */
    public function getExpressionData();
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Predicate;

use Zend\Db\Sql\ExpressionInterface;

interface PredicateInterface extends ExpressionInterface
{
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Predicate;

use Countable;
use Zend\Db\Sql\Exception;

class PredicateSet implements PredicateInterface, Countable
{
    const COMBINED_BY_AND = 'AND';
    const OP_AND          = 'AND';

    const COMBINED_BY_OR  = 'OR';
    const OP_OR           = 'OR';

    protected $defaultCombination = self::COMBINED_BY_AND;
    protected $predicates         = array();

    /**
     * Constructor
     *
     * @param  null|array $predicates
     * @param  string $defaultCombination
     */
    public function __construct(?array $predicates = null, $defaultCombination = self::COMBINED_BY_AND)
    {
        $this->defaultCombination = $defaultCombination;
        if ($predicates) {
            foreach ($predicates as $predicate) {
                $this->addPredicate($predicate);
            }
        }
    }

    /**
     * Add predicate to set
     *
     * @param  PredicateInterface $predicate
     * @param  string $combination
     * @return PredicateSet
     */
    public function addPredicate(PredicateInterface $predicate, $combination = null)
    {
        if ($combination === null || !in_array($combination, array(self::OP_AND, self::OP_OR))) {
            $combination = $this->defaultCombination;
        }

        if ($combination == self::OP_OR) {
            $this->orPredicate($predicate);
            return $this;
        }

        $this->andPredicate($predicate);
        return $this;
    }

    /**
     * Add predicates to set
     *
     * @param PredicateInterface|\Closure|string|array $predicates
     * @param string $combination
     * @return PredicateSet
     */
    public function addPredicates($predicates, $combination = self::OP_AND)
    {
        if ($predicates === null) {
            throw new Exception\InvalidArgumentException('Predicate cannot be null');
        }
        if ($predicates instanceof PredicateInterface) {
            $this->addPredicate($predicates, $combination);
            return $this;
        }
        if ($predicates instanceof \Closure) {
            $predicates($this);
            return $this;
        }
        if (is_string($predicates)) {
            // String $predicate should be passed as an expression
            $predicates = (strpos($predicates, Expression::PLACEHOLDER) !== false)
                ? new Expression($predicates) : new Literal($predicates);
            $this->addPredicate($predicates, $combination);
            return $this;
        }
        if (is_array($predicates)) {
            foreach ($predicates as $pkey => $pvalue) {
                // loop through predicates
                if (is_string($pkey)) {
                    if (strpos($pkey, '?') !== false) {
                        // First, process strings that the abstraction replacement character ?
                        // as an Expression predicate
                        $predicates = new Expression($pkey, $pvalue);
                    } elseif ($pvalue === null) { // Otherwise, if still a string, do something intelligent with the PHP type provided
                        // map PHP null to SQL IS NULL expression
                        $predicates = new IsNull($pkey);
                    } elseif (is_array($pvalue)) {
                        // if the value is an array, assume IN() is desired
                        $predicates = new In($pkey, $pvalue);
                    } elseif ($pvalue instanceof PredicateInterface) {
                        throw new Exception\InvalidArgumentException(
                            'Using Predicate must not use string keys'
                        );
                    } else {
                        // otherwise assume that array('foo' => 'bar') means "foo" = 'bar'
                        $predicates = new Operator($pkey, Operator::OP_EQ, $pvalue);
                    }
                } elseif ($pvalue instanceof PredicateInterface) {
                    // Predicate type is ok
                    $predicates = $pvalue;
                } else {
                    // must be an array of expressions (with int-indexed array)
                    $predicates = (strpos($pvalue, Expression::PLACEHOLDER) !== false)
                        ? new Expression($pvalue) : new Literal($pvalue);
                }
                $this->addPredicate($predicates, $combination);
            }
        }
        return $this;
    }

    /**
     * Return the predicates
     *
     * @return PredicateInterface[]
     */
    public function getPredicates()
    {
        return $this->predicates;
    }

    /**
     * Add predicate using OR operator
     *
     * @param  PredicateInterface $predicate
     * @return PredicateSet
     */
    public function orPredicate(PredicateInterface $predicate)
    {
        $this->predicates[] = array(self::OP_OR, $predicate);
        return $this;
    }

    /**
     * Add predicate using AND operator
     *
     * @param  PredicateInterface $predicate
     * @return PredicateSet
     */
    public function andPredicate(PredicateInterface $predicate)
    {
        $this->predicates[] = array(self::OP_AND, $predicate);
        return $this;
    }

    /**
     * Get predicate parts for where statement
     *
     * @return array
     */
    public function getExpressionData()
    {
        $parts = array();
        for ($i = 0, $count = count($this->predicates); $i < $count; $i++) {
            /** @var $predicate PredicateInterface */
            $predicate = $this->predicates[$i][1];

            if ($predicate instanceof PredicateSet) {
                $parts[] = '(';
            }

            $parts = array_merge($parts, $predicate->getExpressionData());

            if ($predicate instanceof PredicateSet) {
                $parts[] = ')';
            }

            if (isset($this->predicates[$i+1])) {
                $parts[] = sprintf(' %s ', $this->predicates[$i+1][0]);
            }
        }
        return $parts;
    }

    /**
     * Get count of attached predicates
     *
     * @return int
     */
    #[\ReturnTypeWillChange]
    public function count()
    {
        return count($this->predicates);
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Predicate;

use Zend\Db\Sql\Exception\RuntimeException;

/**
 * @property Predicate $and
 * @property Predicate $or
 * @property Predicate $AND
 * @property Predicate $OR
 * @property Predicate $NEST
 * @property Predicate $UNNEST
 */
class Predicate extends PredicateSet
{
    protected $unnest = null;
    protected $nextPredicateCombineOperator = null;

    /**
     * Begin nesting predicates
     *
     * @return Predicate
     */
    public function nest()
    {
        $predicateSet = new Predicate();
        $predicateSet->setUnnest($this);
        $this->addPredicate($predicateSet, ($this->nextPredicateCombineOperator) ?: $this->defaultCombination);
        $this->nextPredicateCombineOperator = null;
        return $predicateSet;
    }

    /**
     * Indicate what predicate will be unnested
     *
     * @param  Predicate $predicate
     * @return void
     */
    public function setUnnest(Predicate $predicate)
    {
        $this->unnest = $predicate;
    }

    /**
     * Indicate end of nested predicate
     *
     * @return Predicate
     * @throws RuntimeException
     */
    public function unnest()
    {
        if ($this->unnest === null) {
            throw new RuntimeException('Not nested');
        }
        $unnest       = $this->unnest;
        $this->unnest = null;
        return $unnest;
    }

    /**
     * Create "Equal To" predicate
     *
     * Utilizes Operator predicate
     *
     * @param  int|float|bool|string $left
     * @param  int|float|bool|string $right
     * @param  string $leftType TYPE_IDENTIFIER or TYPE_VALUE by default TYPE_IDENTIFIER {@see allowedTypes}
     * @param  string $rightType TYPE_IDENTIFIER or TYPE_VALUE by default TYPE_VALUE {@see allowedTypes}
     * @return Predicate
     */
    public function equalTo($left, $right, $leftType = self::TYPE_IDENTIFIER, $rightType = self::TYPE_VALUE)
    {
        $this->addPredicate(
            new Operator($left, Operator::OPERATOR_EQUAL_TO, $right, $leftType, $rightType),
            ($this->nextPredicateCombineOperator) ?: $this->defaultCombination
        );
        $this->nextPredicateCombineOperator = null;

        return $this;
    }

    /**
     * Create "Not Equal To" predicate
     *
     * Utilizes Operator predicate
     *
     * @param  int|float|bool|string $left
     * @param  int|float|bool|string $right
     * @param  string $leftType TYPE_IDENTIFIER or TYPE_VALUE by default TYPE_IDENTIFIER {@see allowedTypes}
     * @param  string $rightType TYPE_IDENTIFIER or TYPE_VALUE by default TYPE_VALUE {@see allowedTypes}
     * @return Predicate
     */
    public function notEqualTo($left, $right, $leftType = self::TYPE_IDENTIFIER, $rightType = self::TYPE_VALUE)
    {
        $this->addPredicate(
            new Operator($left, Operator::OPERATOR_NOT_EQUAL_TO, $right, $leftType, $rightType),
            ($this->nextPredicateCombineOperator) ?: $this->defaultCombination
        );
        $this->nextPredicateCombineOperator = null;

        return $this;
    }

    /**
     * Create "Less Than" predicate
     *
     * Utilizes Operator predicate
     *
     * @param  int|float|bool|string $left
     * @param  int|float|bool|string $right
     * @param  string $leftType TYPE_IDENTIFIER or TYPE_VALUE by default TYPE_IDENTIFIER {@see allowedTypes}
     * @param  string $rightType TYPE_IDENTIFIER or TYPE_VALUE by default TYPE_VALUE {@see allowedTypes}
     * @return Predicate
     */
    public function lessThan($left, $right, $leftType = self::TYPE_IDENTIFIER, $rightType = self::TYPE_VALUE)
    {
        $this->addPredicate(
            new Operator($left, Operator::OPERATOR_LESS_THAN, $right, $leftType, $rightType),
            ($this->nextPredicateCombineOperator) ?: $this->defaultCombination
        );
        $this->nextPredicateCombineOperator = null;

        return $this;
    }

    /**
     * Create "Greater Than" predicate
     *
     * Utilizes Operator predicate
     *
     * @param  int|float|bool|string $left
     * @param  int|float|bool|string $right
     * @param  string $leftType TYPE_IDENTIFIER or TYPE_VALUE by default TYPE_IDENTIFIER {@see allowedTypes}
     * @param  string $rightType TYPE_IDENTIFIER or TYPE_VALUE by default TYPE_VALUE {@see allowedTypes}
     * @return Predicate
     */
    public function greaterThan($left, $right, $leftType = self::TYPE_IDENTIFIER, $rightType = self::TYPE_VALUE)
    {
        $this->addPredicate(
            new Operator($left, Operator::OPERATOR_GREATER_THAN, $right, $leftType, $rightType),
            ($this->nextPredicateCombineOperator) ?: $this->defaultCombination
        );
        $this->nextPredicateCombineOperator = null;

        return $this;
    }

    /**
     * Create "Less Than Or Equal To" predicate
     *
     * Utilizes Operator predicate
     *
     * @param  int|float|bool|string $left
     * @param  int|float|bool|string $right
     * @param  string $leftType TYPE_IDENTIFIER or TYPE_VALUE by default TYPE_IDENTIFIER {@see allowedTypes}
     * @param  string $rightType TYPE_IDENTIFIER or TYPE_VALUE by default TYPE_VALUE {@see allowedTypes}
     * @return Predicate
     */
    public function lessThanOrEqualTo($left, $right, $leftType = self::TYPE_IDENTIFIER, $rightType = self::TYPE_VALUE)
    {
        $this->addPredicate(
            new Operator($left, Operator::OPERATOR_LESS_THAN_OR_EQUAL_TO, $right, $leftType, $rightType),
            ($this->nextPredicateCombineOperator) ?: $this->defaultCombination
        );
        $this->nextPredicateCombineOperator = null;

        return $this;
    }

    /**
     * Create "Greater Than Or Equal To" predicate
     *
     * Utilizes Operator predicate
     *
     * @param  int|float|bool|string $left
     * @param  int|float|bool|string $right
     * @param  string $leftType TYPE_IDENTIFIER or TYPE_VALUE by default TYPE_IDENTIFIER {@see allowedTypes}
     * @param  string $rightType TYPE_IDENTIFIER or TYPE_VALUE by default TYPE_VALUE {@see allowedTypes}
     * @return Predicate
     */
    public function greaterThanOrEqualTo($left, $right, $leftType = self::TYPE_IDENTIFIER, $rightType = self::TYPE_VALUE)
    {
        $this->addPredicate(
            new Operator($left, Operator::OPERATOR_GREATER_THAN_OR_EQUAL_TO, $right, $leftType, $rightType),
            ($this->nextPredicateCombineOperator) ?: $this->defaultCombination
        );
        $this->nextPredicateCombineOperator = null;

        return $this;
    }

    /**
     * Create "Like" predicate
     *
     * Utilizes Like predicate
     *
     * @param  string $identifier
     * @param  string $like
     * @return Predicate
     */
    public function like($identifier, $like)
    {
        $this->addPredicate(
            new Like($identifier, $like),
            ($this->nextPredicateCombineOperator) ?: $this->defaultCombination
        );
        $this->nextPredicateCombineOperator = null;

        return $this;
    }
    /**
     * Create "notLike" predicate
     *
     * Utilizes In predicate
     *
     * @param  string $identifier
     * @param  string $notLike
     * @return Predicate
     */
    public function notLike($identifier, $notLike)
    {
        $this->addPredicate(
            new NotLike($identifier, $notLike),
            ($this->nextPredicateCombineOperator) ? : $this->defaultCombination
        );
        $this->nextPredicateCombineOperator = null;
        return $this;
    }

    /**
     * Create an expression, with parameter placeholders
     *
     * @param $expression
     * @param $parameters
     * @return $this
     */
    public function expression($expression, $parameters)
    {
        $this->addPredicate(
            new Expression($expression, $parameters),
            ($this->nextPredicateCombineOperator) ?: $this->defaultCombination
        );
        $this->nextPredicateCombineOperator = null;

        return $this;
    }

    /**
     * Create "Literal" predicate
     *
     * Literal predicate, for parameters, use expression()
     *
     * @param  string $literal
     * @return Predicate
     */
    public function literal($literal)
    {
        // process deprecated parameters from previous literal($literal, $parameters = null) signature
        if (func_num_args() >= 2) {
            $parameters = func_get_arg(1);
            $predicate = new Expression($literal, $parameters);
        }

        // normal workflow for "Literals" here
        if (!isset($predicate)) {
            $predicate = new Literal($literal);
        }

        $this->addPredicate(
            $predicate,
            ($this->nextPredicateCombineOperator) ?: $this->defaultCombination
        );
        $this->nextPredicateCombineOperator = null;

        return $this;
    }

    /**
     * Create "IS NULL" predicate
     *
     * Utilizes IsNull predicate
     *
     * @param  string $identifier
     * @return Predicate
     */
    public function isNull($identifier)
    {
        $this->addPredicate(
            new IsNull($identifier),
            ($this->nextPredicateCombineOperator) ?: $this->defaultCombination
        );
        $this->nextPredicateCombineOperator = null;

        return $this;
    }

    /**
     * Create "IS NOT NULL" predicate
     *
     * Utilizes IsNotNull predicate
     *
     * @param  string $identifier
     * @return Predicate
     */
    public function isNotNull($identifier)
    {
        $this->addPredicate(
            new IsNotNull($identifier),
            ($this->nextPredicateCombineOperator) ?: $this->defaultCombination
        );
        $this->nextPredicateCombineOperator = null;

        return $this;
    }

    /**
     * Create "IN" predicate
     *
     * Utilizes In predicate
     *
     * @param  string $identifier
     * @param  array|\Zend\Db\Sql\Select $valueSet
     * @return Predicate
     */
    public function in($identifier, $valueSet = null)
    {
        $this->addPredicate(
            new In($identifier, $valueSet),
            ($this->nextPredicateCombineOperator) ?: $this->defaultCombination
        );
        $this->nextPredicateCombineOperator = null;

        return $this;
    }

    /**
     * Create "NOT IN" predicate
     *
     * Utilizes NotIn predicate
     *
     * @param  string $identifier
     * @param  array|\Zend\Db\Sql\Select $valueSet
     * @return Predicate
     */
    public function notIn($identifier, $valueSet = null)
    {
        $this->addPredicate(
            new NotIn($identifier, $valueSet),
            ($this->nextPredicateCombineOperator) ?: $this->defaultCombination
        );
        $this->nextPredicateCombineOperator = null;

        return $this;
    }

    /**
     * Create "between" predicate
     *
     * Utilizes Between predicate
     *
     * @param  string $identifier
     * @param  int|float|string $minValue
     * @param  int|float|string $maxValue
     * @return Predicate
     */
    public function between($identifier, $minValue, $maxValue)
    {
        $this->addPredicate(
            new Between($identifier, $minValue, $maxValue),
            ($this->nextPredicateCombineOperator) ?: $this->defaultCombination
        );
        $this->nextPredicateCombineOperator = null;

        return $this;
    }

    /**
     * Use given predicate directly
     *
     * Contrary to {@link addPredicate()} this method respects formerly set
     * AND / OR combination operator, thus allowing generic predicates to be
     * used fluently within where chains as any other concrete predicate.
     *
     * @param  PredicateInterface $predicate
     * @return Predicate
     */
    public function predicate(PredicateInterface $predicate)
    {
        $this->addPredicate(
            $predicate,
            $this->nextPredicateCombineOperator ?: $this->defaultCombination
        );
        $this->nextPredicateCombineOperator = null;

        return $this;
    }

    /**
     * Overloading
     *
     * Overloads "or", "and", "nest", and "unnest"
     *
     * @param  string $name
     * @return Predicate
     */
    public function __get($name)
    {
        switch (strtolower($name)) {
            case 'or':
                $this->nextPredicateCombineOperator = self::OP_OR;
                break;
            case 'and':
                $this->nextPredicateCombineOperator = self::OP_AND;
                break;
            case 'nest':
                return $this->nest();
            case 'unnest':
                return $this->unnest();
        }
        return $this;
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql;

abstract class AbstractExpression implements ExpressionInterface
{
    /**
     * @var string[]
     */
    protected $allowedTypes = array(
        self::TYPE_IDENTIFIER,
        self::TYPE_LITERAL,
        self::TYPE_SELECT,
        self::TYPE_VALUE,
    );

    /**
     * Normalize Argument
     *
     * @param mixed $argument
     * @param string $defaultType
     *
     * @return array
     *
     * @throws Exception\InvalidArgumentException
     */
    protected function normalizeArgument($argument, $defaultType = self::TYPE_VALUE)
    {
        if ($argument instanceof ExpressionInterface || $argument instanceof SqlInterface) {
            return $this->buildNormalizedArgument($argument, self::TYPE_VALUE);
        }

        if (is_scalar($argument) || $argument === null) {
            return $this->buildNormalizedArgument($argument, $defaultType);
        }

        if (is_array($argument)) {
            $value = current($argument);

            if ($value instanceof ExpressionInterface || $value instanceof SqlInterface) {
                return $this->buildNormalizedArgument($value, self::TYPE_VALUE);
            }

            $key = key($argument);

            if (is_integer($key) && ! in_array($value, $this->allowedTypes)) {
                return $this->buildNormalizedArgument($value, $defaultType);
            }

            return $this->buildNormalizedArgument($key, $value);
        }

        throw new Exception\InvalidArgumentException(sprintf(
            '$argument should be %s or %s or %s or %s or %s, "%s" given',
            'null',
            'scalar',
            'array',
            'Zend\Db\Sql\ExpressionInterface',
            'Zend\Db\Sql\SqlInterface',
            is_object($argument) ? get_class($argument) : gettype($argument)
        ));
    }

    /**
     * @param mixed  $argument
     * @param string $argumentType
     *
     * @return array
     *
     * @throws Exception\InvalidArgumentException
     */
    private function buildNormalizedArgument($argument, $argumentType)
    {
        if (! in_array($argumentType, $this->allowedTypes)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Argument type should be in array(%s)',
                implode(',', $this->allowedTypes)
            ));
        }

        return array(
            $argument,
            $argumentType,
        );
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Predicate;

use Zend\Db\Sql\AbstractExpression;

class IsNull extends AbstractExpression implements PredicateInterface
{
    /**
     * @var string
     */
    protected $specification = '%1$s IS NULL';

    /**
     * @var
     */
    protected $identifier;

    /**
     * Constructor
     *
     * @param  string $identifier
     */
    public function __construct($identifier = null)
    {
        if ($identifier) {
            $this->setIdentifier($identifier);
        }
    }

    /**
     * Set identifier for comparison
     *
     * @param  string $identifier
     * @return IsNull
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * Get identifier of comparison
     *
     * @return null|string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Set specification string to use in forming SQL predicate
     *
     * @param  string $specification
     * @return IsNull
     */
    public function setSpecification($specification)
    {
        $this->specification = $specification;
        return $this;
    }

    /**
     * Get specification string to use in forming SQL predicate
     *
     * @return string
     */
    public function getSpecification()
    {
        return $this->specification;
    }

    /**
     * Get parts for where statement
     *
     * @return array
     */
    public function getExpressionData()
    {
        $identifier = $this->normalizeArgument($this->identifier, self::TYPE_IDENTIFIER);
        return array(array(
            $this->getSpecification(),
            array($identifier[0]),
            array($identifier[1]),
        ));
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Predicate;

use Zend\Db\Sql\Exception;
use Zend\Db\Sql\AbstractExpression;

class Operator extends AbstractExpression implements PredicateInterface
{
    const OPERATOR_EQUAL_TO                  = '=';
    const OP_EQ                              = '=';

    const OPERATOR_NOT_EQUAL_TO              = '!=';
    const OP_NE                              = '!=';

    const OPERATOR_LESS_THAN                 = '<';
    const OP_LT                              = '<';

    const OPERATOR_LESS_THAN_OR_EQUAL_TO     = '<=';
    const OP_LTE                             = '<=';

    const OPERATOR_GREATER_THAN              = '>';
    const OP_GT                              = '>';

    const OPERATOR_GREATER_THAN_OR_EQUAL_TO  = '>=';
    const OP_GTE                             = '>=';

    /**
     * {@inheritDoc}
     */
    protected $allowedTypes  = array(
        self::TYPE_IDENTIFIER,
        self::TYPE_VALUE,
    );

    /**
     * @var int|float|bool|string
     */
    protected $left;

    /**
     * @var int|float|bool|string
     */
    protected $right;

    /**
     * @var string
     */
    protected $leftType = self::TYPE_IDENTIFIER;

    /**
     * @var string
     */
    protected $rightType = self::TYPE_VALUE;

    /**
     * @var string
     */
    protected $operator = self::OPERATOR_EQUAL_TO;

    /**
     * Constructor
     *
     * @param int|float|bool|string $left
     * @param string $operator
     * @param int|float|bool|string $right
     * @param string $leftType TYPE_IDENTIFIER or TYPE_VALUE by default TYPE_IDENTIFIER {@see allowedTypes}
     * @param string $rightType TYPE_IDENTIFIER or TYPE_VALUE by default TYPE_VALUE {@see allowedTypes}
     */
    public function __construct(
        $left = null,
        $operator = self::OPERATOR_EQUAL_TO,
        $right = null,
        $leftType = self::TYPE_IDENTIFIER,
        $rightType = self::TYPE_VALUE
    ) {
        if ($left !== null) {
            $this->setLeft($left);
        }

        if ($operator !== self::OPERATOR_EQUAL_TO) {
            $this->setOperator($operator);
        }

        if ($right !== null) {
            $this->setRight($right);
        }

        if ($leftType !== self::TYPE_IDENTIFIER) {
            $this->setLeftType($leftType);
        }

        if ($rightType !== self::TYPE_VALUE) {
            $this->setRightType($rightType);
        }
    }

    /**
     * Set left side of operator
     *
     * @param  int|float|bool|string $left
     *
     * @return Operator
     */
    public function setLeft($left)
    {
        $this->left = $left;

        if (is_array($left)) {
            $left = $this->normalizeArgument($left, $this->leftType);
            $this->leftType = $left[1];
        }

        return $this;
    }

    /**
     * Get left side of operator
     *
     * @return int|float|bool|string
     */
    public function getLeft()
    {
        return $this->left;
    }

    /**
     * Set parameter type for left side of operator
     *
     * @param  string $type TYPE_IDENTIFIER or TYPE_VALUE {@see allowedTypes}
     *
     * @return Operator
     *
     * @throws Exception\InvalidArgumentException
     */
    public function setLeftType($type)
    {
        if (!in_array($type, $this->allowedTypes)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Invalid type "%s" provided; must be of type "%s" or "%s"',
                $type,
                __CLASS__ . '::TYPE_IDENTIFIER',
                __CLASS__ . '::TYPE_VALUE'
            ));
        }

        $this->leftType = $type;

        return $this;
    }

    /**
     * Get parameter type on left side of operator
     *
     * @return string
     */
    public function getLeftType()
    {
        return $this->leftType;
    }

    /**
     * Set operator string
     *
     * @param  string $operator
     * @return Operator
     */
    public function setOperator($operator)
    {
        $this->operator = $operator;

        return $this;
    }

    /**
     * Get operator string
     *
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * Set right side of operator
     *
     * @param  int|float|bool|string $right
     *
     * @return Operator
     */
    public function setRight($right)
    {
        $this->right = $right;

        if (is_array($right)) {
            $right = $this->normalizeArgument($right, $this->rightType);
            $this->rightType = $right[1];
        }

        return $this;
    }

    /**
     * Get right side of operator
     *
     * @return int|float|bool|string
     */
    public function getRight()
    {
        return $this->right;
    }

    /**
     * Set parameter type for right side of operator
     *
     * @param  string $type TYPE_IDENTIFIER or TYPE_VALUE {@see allowedTypes}
     * @throws Exception\InvalidArgumentException
     * @return Operator
     */
    public function setRightType($type)
    {
        if (!in_array($type, $this->allowedTypes)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Invalid type "%s" provided; must be of type "%s" or "%s"',
                $type,
                __CLASS__ . '::TYPE_IDENTIFIER',
                __CLASS__ . '::TYPE_VALUE'
            ));
        }

        $this->rightType = $type;

        return $this;
    }

    /**
     * Get parameter type on right side of operator
     *
     * @return string
     */
    public function getRightType()
    {
        return $this->rightType;
    }

    /**
     * Get predicate parts for where statement
     *
     * @return array
     */
    public function getExpressionData()
    {
        list($values[], $types[]) = $this->normalizeArgument($this->left, $this->leftType);
        list($values[], $types[]) = $this->normalizeArgument($this->right, $this->rightType);

        return array(array(
            '%s ' . $this->operator . ' %s',
            $values,
            $types
        ));
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql;

class Literal implements ExpressionInterface
{
    /**
     * @var string
     */
    protected $literal = '';

    /**
     * @param $literal
     */
    public function __construct($literal = '')
    {
        $this->literal = $literal;
    }

    /**
     * @param string $literal
     * @return Literal
     */
    public function setLiteral($literal)
    {
        $this->literal = $literal;
        return $this;
    }

    /**
     * @return string
     */
    public function getLiteral()
    {
        return $this->literal;
    }

    /**
     * @return array
     */
    public function getExpressionData()
    {
        return array(array(
            str_replace('%', '%%', $this->literal),
            array(),
            array()
        ));
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Predicate;

use Zend\Db\Sql\Literal as BaseLiteral;

class Literal extends BaseLiteral implements PredicateInterface
{
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql;

class Expression extends AbstractExpression
{
    /**
     * @const
     */
    const PLACEHOLDER = '?';

    /**
     * @var string
     */
    protected $expression = '';

    /**
     * @var array
     */
    protected $parameters = array();

    /**
     * @var array
     */
    protected $types = array();

    /**
     * @param string $expression
     * @param string|array $parameters
     * @param array $types @deprecated will be dropped in version 3.0.0
     */
    public function __construct($expression = '', $parameters = null, array $types = array())
    {
        if ($expression !== '') {
            $this->setExpression($expression);
        }

        if ($types) { // should be deprecated and removed version 3.0.0
            if (is_array($parameters)) {
                foreach ($parameters as $i=>$parameter) {
                    $parameters[$i] = array(
                        $parameter => isset($types[$i]) ? $types[$i] : self::TYPE_VALUE,
                    );
                }
            } elseif (is_scalar($parameters)) {
                $parameters = array(
                    $parameters => $types[0],
                );
            }
        }

        if ($parameters) {
            $this->setParameters($parameters);
        }
    }

    /**
     * @param $expression
     * @return Expression
     * @throws Exception\InvalidArgumentException
     */
    public function setExpression($expression)
    {
        if (!is_string($expression) || $expression == '') {
            throw new Exception\InvalidArgumentException('Supplied expression must be a string.');
        }
        $this->expression = $expression;
        return $this;
    }

    /**
     * @return string
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * @param $parameters
     * @return Expression
     * @throws Exception\InvalidArgumentException
     */
    public function setParameters($parameters)
    {
        if (!is_scalar($parameters) && !is_array($parameters)) {
            throw new Exception\InvalidArgumentException('Expression parameters must be a scalar or array.');
        }
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @deprecated
     * @param array $types
     * @return Expression
     */
    public function setTypes(array $types)
    {
        $this->types = $types;
        return $this;
    }

    /**
     * @deprecated
     * @return array
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * @return array
     * @throws Exception\RuntimeException
     */
    public function getExpressionData()
    {
        $parameters = (is_scalar($this->parameters)) ? array($this->parameters) : $this->parameters;
        $parametersCount = count($parameters);
        $expression = str_replace('%', '%%', $this->expression);

        if ($parametersCount == 0) {
            return array(
                str_ireplace(self::PLACEHOLDER, '', $expression)
            );
        }

        // assign locally, escaping % signs
        $expression = str_replace(self::PLACEHOLDER, '%s', $expression, $count);
        if ($count !== $parametersCount) {
            throw new Exception\RuntimeException('The number of replacements in the expression does not match the number of parameters');
        }
        foreach ($parameters as $parameter) {
            list($values[], $types[]) = $this->normalizeArgument($parameter, self::TYPE_VALUE);
        }
        return array(array(
            $expression,
            $values,
            $types
        ));
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Predicate;

use Zend\Db\Sql\Expression as BaseExpression;

class Expression extends BaseExpression implements PredicateInterface
{
    /**
     * Constructor
     *
     * @param string $expression
     * @param int|float|bool|string|array $valueParameter
     */
    public function __construct($expression = null, $valueParameter = null /*[, $valueParameter, ... ]*/)
    {
        if ($expression) {
            $this->setExpression($expression);
        }

        $this->setParameters(is_array($valueParameter) ? $valueParameter : array_slice(func_get_args(), 1));
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql;

class Where extends Predicate\Predicate
{
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql;

class Having extends Predicate\Predicate
{
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Exception;

use Zend\Db\Exception;

interface ExceptionInterface extends Exception\ExceptionInterface
{
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Exception;

use Zend\Db\Exception;

class InvalidArgumentException extends Exception\InvalidArgumentException implements ExceptionInterface
{
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Serializer;

use Zend\Serializer\Adapter\AdapterInterface as Adapter;

abstract class Serializer
{
    /**
     * Plugin manager for loading adapters
     *
     * @var null|AdapterPluginManager
     */
    protected static $adapters;

    /**
     * The default adapter.
     *
     * @var string|Adapter
     */
    protected static $defaultAdapter = 'PhpSerialize';

    /**
     * Create a serializer adapter instance.
     *
     * @param  string|Adapter $adapterName Name of the adapter class
     * @param  array |\Traversable|null $adapterOptions Serializer options
     * @return Adapter
     */
    public static function factory($adapterName, $adapterOptions = null)
    {
        if ($adapterName instanceof Adapter) {
            return $adapterName; // $adapterName is already an adapter object
        }

        return static::getAdapterPluginManager()->get($adapterName, $adapterOptions);
    }

    /**
     * Change the adapter plugin manager
     *
     * @param  AdapterPluginManager $adapters
     * @return void
     */
    public static function setAdapterPluginManager(AdapterPluginManager $adapters)
    {
        static::$adapters = $adapters;
    }

    /**
     * Get the adapter plugin manager
     *
     * @return AdapterPluginManager
     */
    public static function getAdapterPluginManager()
    {
        if (static::$adapters === null) {
            static::$adapters = new AdapterPluginManager();
        }
        return static::$adapters;
    }

    /**
     * Resets the internal adapter plugin manager
     *
     * @return AdapterPluginManager
     */
    public static function resetAdapterPluginManager()
    {
        static::$adapters = new AdapterPluginManager();
        return static::$adapters;
    }

    /**
     * Change the default adapter.
     *
     * @param string|Adapter $adapter
     * @param array|\Traversable|null $adapterOptions
     */
    public static function setDefaultAdapter($adapter, $adapterOptions = null)
    {
        static::$defaultAdapter = static::factory($adapter, $adapterOptions);
    }

    /**
     * Get the default adapter.
     *
     * @return Adapter
     */
    public static function getDefaultAdapter()
    {
        if (!static::$defaultAdapter instanceof Adapter) {
            static::setDefaultAdapter(static::$defaultAdapter);
        }
        return static::$defaultAdapter;
    }

    /**
     * Generates a storable representation of a value using the default adapter.
     * Optionally different adapter could be provided as second argument
     *
     * @param  mixed $value
     * @param  string|Adapter $adapter
     * @param  array|\Traversable|null $adapterOptions Adapter constructor options
     *                                                 only used to create adapter instance
     * @return string
     */
    public static function serialize($value, $adapter = null, $adapterOptions = null)
    {
        if ($adapter !== null) {
            $adapter = static::factory($adapter, $adapterOptions);
        } else {
            $adapter = static::getDefaultAdapter();
        }

        return $adapter->serialize($value);
    }

    /**
     * Creates a PHP value from a stored representation using the default adapter.
     * Optionally different adapter could be provided as second argument
     *
     * @param  string $serialized
     * @param  string|Adapter $adapter
     * @param  array|\Traversable|null $adapterOptions Adapter constructor options
     *                                                 only used to create adapter instance
     * @return mixed
     */
    public static function unserialize($serialized, $adapter = null, $adapterOptions = null)
    {
        if ($adapter !== null) {
            $adapter = static::factory($adapter, $adapterOptions);
        } else {
            $adapter = static::getDefaultAdapter();
        }

        return $adapter->unserialize($serialized);
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Serializer;

use Zend\ServiceManager\AbstractPluginManager;

/**
 * Plugin manager implementation for serializer adapters.
 *
 * Enforces that adapters retrieved are instances of
 * Adapter\AdapterInterface. Additionally, it registers a number of default
 * adapters available.
 */
class AdapterPluginManager extends AbstractPluginManager
{
    /**
     * Default set of adapters
     *
     * @var array
     */
    protected $invokableClasses = array(
        'igbinary'     => 'Zend\Serializer\Adapter\IgBinary',
        'json'         => 'Zend\Serializer\Adapter\Json',
        'msgpack'      => 'Zend\Serializer\Adapter\MsgPack',
        'phpcode'      => 'Zend\Serializer\Adapter\PhpCode',
        'phpserialize' => 'Zend\Serializer\Adapter\PhpSerialize',
        'pythonpickle' => 'Zend\Serializer\Adapter\PythonPickle',
        'wddx'         => 'Zend\Serializer\Adapter\Wddx',
    );

    /**
     * Validate the plugin
     *
     * Checks that the adapter loaded is an instance
     * of Adapter\AdapterInterface.
     *
     * @param  mixed $plugin
     * @return void
     * @throws Exception\RuntimeException if invalid
     */
    public function validatePlugin($plugin)
    {
        if ($plugin instanceof Adapter\AdapterInterface) {
            // we're okay
            return;
        }

        throw new Exception\RuntimeException(sprintf(
            'Plugin of type %s is invalid; must implement %s\Adapter\AdapterInterface',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
            __NAMESPACE__
        ));
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Serializer\Adapter;

interface AdapterInterface
{
    /**
     * Generates a storable representation of a value.
     *
     * @param  mixed $value Data to serialize
     * @return string
     * @throws \Zend\Serializer\Exception\ExceptionInterface
     */
    public function serialize($value);

    /**
     * Creates a PHP value from a stored representation.
     *
     * @param  string $serialized Serialized string
     * @return mixed
     * @throws \Zend\Serializer\Exception\ExceptionInterface
     */
    public function unserialize($serialized);
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Serializer\Adapter;

abstract class AbstractAdapter implements AdapterInterface
{
    /**
     * @var AdapterOptions
     */
    protected $options = null;

    /**
     * Constructor
     *
     * @param array|\Traversable|AdapterOptions $options
     */
    public function __construct($options = null)
    {
        if ($options !== null) {
            $this->setOptions($options);
        }
    }

    /**
     * Set adapter options
     *
     * @param  array|\Traversable|AdapterOptions $options
     * @return AbstractAdapter
     */
    public function setOptions($options)
    {
        if (!$options instanceof AdapterOptions) {
            $options = new AdapterOptions($options);
        }

        $this->options = $options;
        return $this;
    }

    /**
     * Get adapter options
     *
     * @return AdapterOptions
     */
    public function getOptions()
    {
        if ($this->options === null) {
            $this->options = new AdapterOptions();
        }
        return $this->options;
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Serializer\Adapter;

use Zend\Serializer\Exception;
use Zend\Stdlib\ErrorHandler;

class PhpSerialize extends AbstractAdapter
{
    /**
     * Serialized boolean false value
     *
     * @var null|string
     */
    private static $serializedFalse = null;

    /**
     * Constructor
     */
    public function __construct($options = null)
    {
        // needed to check if a returned false is based on a serialize false
        // or based on failure (igbinary can overwrite [un]serialize functions)
        if (static::$serializedFalse === null) {
            static::$serializedFalse = serialize(false);
        }

        parent::__construct($options);
    }

    /**
     * Serialize using serialize()
     *
     * @param  mixed $value
     * @return string
     * @throws Exception\RuntimeException On serialize error
     */
    public function serialize($value)
    {
        ErrorHandler::start();
        $ret = serialize($value);
        $err = ErrorHandler::stop();
        if ($err) {
            throw new Exception\RuntimeException('Serialization failed', 0, $err);
        }

        return $ret;
    }

    /**
     * Unserialize
     *
     * @todo   Allow integration with unserialize_callback_func
     * @param  string $serialized
     * @return mixed
     * @throws Exception\RuntimeException on unserialize error
     */
    public function unserialize($serialized)
    {
        if (!is_string($serialized) || !preg_match('/^((s|i|d|b|a|O|C):|N;)/', $serialized)) {
            return $serialized;
        }

        // If we have a serialized boolean false value, just return false;
        // prevents the unserialize handler from creating an error.
        if ($serialized === static::$serializedFalse) {
            return false;
        }

        ErrorHandler::start(E_NOTICE);
        $ret = unserialize($serialized);
        $err = ErrorHandler::stop();
        if ($ret === false) {
            throw new Exception\RuntimeException('Unserialization failed', 0, $err);
        }

        return $ret;
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Stdlib;

use ErrorException;

/**
 * ErrorHandler that can be used to catch internal PHP errors
 * and convert to an ErrorException instance.
 */
abstract class ErrorHandler
{
    /**
     * Active stack
     *
     * @var array
     */
    protected static $stack = [];

    /**
     * Check if this error handler is active
     *
     * @return bool
     */
    public static function started()
    {
        return (bool) static::getNestedLevel();
    }

    /**
     * Get the current nested level
     *
     * @return int
     */
    public static function getNestedLevel()
    {
        return count(static::$stack);
    }

    /**
     * Starting the error handler
     *
     * @param int $errorLevel
     */
    public static function start($errorLevel = \E_WARNING)
    {
        if (!static::$stack) {
            set_error_handler([get_called_class(), 'addError'], $errorLevel);
        }

        static::$stack[] = null;
    }

    /**
     * Stopping the error handler
     *
     * @param  bool $throw Throw the ErrorException if any
     * @return null|ErrorException
     * @throws ErrorException If an error has been catched and $throw is true
     */
    public static function stop($throw = false)
    {
        $errorException = null;

        if (static::$stack) {
            $errorException = array_pop(static::$stack);

            if (!static::$stack) {
                restore_error_handler();
            }

            if ($errorException && $throw) {
                throw $errorException;
            }
        }

        return $errorException;
    }

    /**
     * Stop all active handler
     *
     * @return void
     */
    public static function clean()
    {
        if (static::$stack) {
            restore_error_handler();
        }

        static::$stack = [];
    }

    /**
     * Add an error to the stack
     *
     * @param int    $errno
     * @param string $errstr
     * @param string $errfile
     * @param int    $errline
     * @return void
     */
    public static function addError($errno, $errstr = '', $errfile = '', $errline = 0)
    {
        $stack = & static::$stack[count(static::$stack) - 1];
        $stack = new ErrorException($errstr, 0, $errno, $errfile, $errline, $stack);
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Storage;

use ArrayObject;

class PostEvent extends Event
{
    /**
     * The result/return value
     *
     * @var mixed
     */
    protected $result;

    /**
     * Constructor
     *
     * Accept a target and its parameters.
     *
     * @param  string           $name
     * @param  StorageInterface $storage
     * @param  ArrayObject      $params
     * @param  mixed            $result
     */
    public function __construct($name, StorageInterface $storage, ArrayObject $params, & $result)
    {
        parent::__construct($name, $storage, $params);
        $this->setResult($result);
    }

    /**
     * Set the result/return value
     *
     * @param  mixed $value
     * @return PostEvent
     */
    public function setResult(& $value)
    {
        $this->result = & $value;
        return $this;
    }

    /**
     * Get the result/return value
     *
     * @return mixed
     */
    public function & getResult()
    {
        return $this->result;
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Storage;

use ArrayObject;
use Exception;

class ExceptionEvent extends PostEvent
{
    /**
     * The exception to be thrown
     *
     * @var Exception
     */
    protected $exception;

    /**
     * Throw the exception or use the result
     *
     * @var bool
     */
    protected $throwException = true;

    /**
     * Constructor
     *
     * Accept a target and its parameters.
     *
     * @param  string $name
     * @param  StorageInterface $storage
     * @param  ArrayObject $params
     * @param  mixed $result
     * @param  Exception $exception
     */
    public function __construct($name, StorageInterface $storage, ArrayObject $params, & $result, Exception $exception)
    {
        parent::__construct($name, $storage, $params, $result);
        $this->setException($exception);
    }

    /**
     * Set the exception to be thrown
     *
     * @param  Exception $exception
     * @return ExceptionEvent
     */
    public function setException(Exception $exception)
    {
        $this->exception = $exception;
        return $this;
    }

    /**
     * Get the exception to be thrown
     *
     * @return Exception
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * Throw the exception or use the result
     *
     * @param  bool $flag
     * @return ExceptionEvent
     */
    public function setThrowException($flag)
    {
        $this->throwException = (bool) $flag;
        return $this;
    }

    /**
     * Throw the exception or use the result
     *
     * @return bool
     */
    public function getThrowException()
    {
        return $this->throwException;
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Exception;

interface ExceptionInterface
{
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Exception;

class InvalidArgumentException extends \InvalidArgumentException implements
    ExceptionInterface
{
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Exception;

class RuntimeException extends \RuntimeException implements ExceptionInterface
{
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Authentication\Adapter;

interface AdapterInterface
{
    /**
     * Performs an authentication attempt
     *
     * @return \Zend\Authentication\Result
     * @throws \Zend\Authentication\Adapter\Exception\ExceptionInterface If authentication cannot be performed
     */
    public function authenticate();
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Stdlib\StringWrapper;

interface StringWrapperInterface
{
    /**
     * Check if the given character encoding is supported by this wrapper
     * and the character encoding to convert to is also supported.
     *
     * @param string      $encoding
     * @param string|null $convertEncoding
     */
    public static function isSupported($encoding, $convertEncoding = null);

    /**
     * Get a list of supported character encodings
     *
     * @return string[]
     */
    public static function getSupportedEncodings();

    /**
     * Set character encoding working with and convert to
     *
     * @param string      $encoding         The character encoding to work with
     * @param string|null $convertEncoding  The character encoding to convert to
     * @return StringWrapperInterface
     */
    public function setEncoding($encoding, $convertEncoding = null);

    /**
     * Get the defined character encoding to work with (upper case)
     *
     * @return string
     */
    public function getEncoding();

    /**
     * Get the defined character encoding to convert to (upper case)
     *
     * @return string|null
     */
    public function getConvertEncoding();

    /**
     * Returns the length of the given string
     *
     * @param string $str
     * @return int|false
     */
    public function strlen($str);

    /**
     * Returns the portion of string specified by the start and length parameters
     *
     * @param string   $str
     * @param int      $offset
     * @param int|null $length
     * @return string|false
     */
    public function substr($str, $offset = 0, $length = null);

    /**
     * Find the position of the first occurrence of a substring in a string
     *
     * @param string $haystack
     * @param string $needle
     * @param int    $offset
     * @return int|false
     */
    public function strpos($haystack, $needle, $offset = 0);

    /**
     * Convert a string from defined encoding to the defined convert encoding
     *
     * @param string  $str
     * @param bool $reverse
     * @return string|false
     */
    public function convert($str, $reverse = false);

    /**
     * Wraps a string to a given number of characters
     *
     * @param  string  $str
     * @param  int $width
     * @param  string  $break
     * @param  bool $cut
     * @return string
     */
    public function wordWrap($str, $width = 75, $break = "\n", $cut = false);

    /**
     * Pad a string to a certain length with another string
     *
     * @param  string  $input
     * @param  int $padLength
     * @param  string  $padString
     * @param  int $padType
     * @return string
     */
    public function strPad($input, $padLength, $padString = ' ', $padType = STR_PAD_RIGHT);
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Stdlib\StringWrapper;

use Zend\Stdlib\Exception;
use Zend\Stdlib\StringUtils;

abstract class AbstractStringWrapper implements StringWrapperInterface
{
    /**
     * The character encoding working on
     * @var string|null
     */
    protected $encoding = 'UTF-8';

    /**
     * An optionally character encoding to convert to
     * @var string|null
     */
    protected $convertEncoding;

    /**
     * Check if the given character encoding is supported by this wrapper
     * and the character encoding to convert to is also supported.
     *
     * @param  string      $encoding
     * @param  string|null $convertEncoding
     * @return bool
     */
    public static function isSupported($encoding, $convertEncoding = null)
    {
        $supportedEncodings = static::getSupportedEncodings();

        if (!in_array(strtoupper($encoding), $supportedEncodings)) {
            return false;
        }

        if ($convertEncoding !== null && !in_array(strtoupper($convertEncoding), $supportedEncodings)) {
            return false;
        }

        return true;
    }

    /**
     * Set character encoding working with and convert to
     *
     * @param string      $encoding         The character encoding to work with
     * @param string|null $convertEncoding  The character encoding to convert to
     * @return StringWrapperInterface
     */
    public function setEncoding($encoding, $convertEncoding = null)
    {
        $supportedEncodings = static::getSupportedEncodings();

        $encodingUpper = strtoupper($encoding);
        if (!in_array($encodingUpper, $supportedEncodings)) {
            throw new Exception\InvalidArgumentException(
                'Wrapper doesn\'t support character encoding "' . $encoding . '"'
            );
        }

        if ($convertEncoding !== null) {
            $convertEncodingUpper = strtoupper($convertEncoding);
            if (!in_array($convertEncodingUpper, $supportedEncodings)) {
                throw new Exception\InvalidArgumentException(
                    'Wrapper doesn\'t support character encoding "' . $convertEncoding . '"'
                );
            }

            $this->convertEncoding = $convertEncodingUpper;
        } else {
            $this->convertEncoding = null;
        }
        $this->encoding = $encodingUpper;

        return $this;
    }

    /**
     * Get the defined character encoding to work with
     *
     * @return string
     * @throws Exception\LogicException If no encoding was defined
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * Get the defined character encoding to convert to
     *
     * @return string|null
    */
    public function getConvertEncoding()
    {
        return $this->convertEncoding;
    }

    /**
     * Convert a string from defined character encoding to the defined convert encoding
     *
     * @param string  $str
     * @param bool $reverse
     * @return string|false
     */
    public function convert($str, $reverse = false)
    {
        $encoding        = $this->getEncoding();
        $convertEncoding = $this->getConvertEncoding();
        if ($convertEncoding === null) {
            throw new Exception\LogicException(
                'No convert encoding defined'
            );
        }

        if ($encoding === $convertEncoding) {
            return $str;
        }

        $from = $reverse ? $convertEncoding : $encoding;
        $to   = $reverse ? $encoding : $convertEncoding;
        throw new Exception\RuntimeException(sprintf(
            'Converting from "%s" to "%s" isn\'t supported by this string wrapper',
            $from,
            $to
        ));
    }

    /**
     * Wraps a string to a given number of characters
     *
     * @param  string  $string
     * @param  int $width
     * @param  string  $break
     * @param  bool $cut
     * @return string|false
     */
    public function wordWrap($string, $width = 75, $break = "\n", $cut = false)
    {
        $string = (string) $string;
        if ($string === '') {
            return '';
        }

        $break = (string) $break;
        if ($break === '') {
            throw new Exception\InvalidArgumentException('Break string cannot be empty');
        }

        $width = (int) $width;
        if ($width === 0 && $cut) {
            throw new Exception\InvalidArgumentException('Cannot force cut when width is zero');
        }

        if (StringUtils::isSingleByteEncoding($this->getEncoding())) {
            return wordwrap($string, $width, $break, $cut);
        }

        $stringWidth = $this->strlen($string);
        $breakWidth  = $this->strlen($break);

        $result    = '';
        $lastStart = $lastSpace = 0;

        for ($current = 0; $current < $stringWidth; $current++) {
            $char = $this->substr($string, $current, 1);

            $possibleBreak = $char;
            if ($breakWidth !== 1) {
                $possibleBreak = $this->substr($string, $current, $breakWidth);
            }

            if ($possibleBreak === $break) {
                $result    .= $this->substr($string, $lastStart, $current - $lastStart + $breakWidth);
                $current   += $breakWidth - 1;
                $lastStart  = $lastSpace = $current + 1;
                continue;
            }

            if ($char === ' ') {
                if ($current - $lastStart >= $width) {
                    $result    .= $this->substr($string, $lastStart, $current - $lastStart) . $break;
                    $lastStart  = $current + 1;
                }

                $lastSpace = $current;
                continue;
            }

            if ($current - $lastStart >= $width && $cut && $lastStart >= $lastSpace) {
                $result    .= $this->substr($string, $lastStart, $current - $lastStart) . $break;
                $lastStart  = $lastSpace = $current;
                continue;
            }

            if ($current - $lastStart >= $width && $lastStart < $lastSpace) {
                $result    .= $this->substr($string, $lastStart, $lastSpace - $lastStart) . $break;
                $lastStart  = $lastSpace = $lastSpace + 1;
                continue;
            }
        }

        if ($lastStart !== $current) {
            $result .= $this->substr($string, $lastStart, $current - $lastStart);
        }

        return $result;
    }

    /**
     * Pad a string to a certain length with another string
     *
     * @param  string  $input
     * @param  int $padLength
     * @param  string  $padString
     * @param  int $padType
     * @return string
     */
    public function strPad($input, $padLength, $padString = ' ', $padType = STR_PAD_RIGHT)
    {
        if (StringUtils::isSingleByteEncoding($this->getEncoding())) {
            return str_pad($input, $padLength, $padString, $padType);
        }

        $lengthOfPadding = $padLength - $this->strlen($input);
        if ($lengthOfPadding <= 0) {
            return $input;
        }

        $padStringLength = $this->strlen($padString);
        if ($padStringLength === 0) {
            return $input;
        }

        $repeatCount = floor($lengthOfPadding / $padStringLength);

        if ($padType === STR_PAD_BOTH) {
            $repeatCountLeft = $repeatCountRight = ($repeatCount - $repeatCount % 2) / 2;

            $lastStringLength       = $lengthOfPadding - 2 * $repeatCountLeft * $padStringLength;
            $lastStringLeftLength   = $lastStringRightLength = floor($lastStringLength / 2);
            $lastStringRightLength += $lastStringLength % 2;

            $lastStringLeft  = $this->substr($padString, 0, $lastStringLeftLength);
            $lastStringRight = $this->substr($padString, 0, $lastStringRightLength);

            return str_repeat($padString, $repeatCountLeft) . $lastStringLeft
                . $input
                . str_repeat($padString, $repeatCountRight) . $lastStringRight;
        }

        $lastString = $this->substr($padString, 0, $lengthOfPadding % $padStringLength);

        if ($padType === STR_PAD_LEFT) {
            return str_repeat($padString, $repeatCount) . $lastString . $input;
        }

        return $input . str_repeat($padString, $repeatCount) . $lastString;
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Stdlib\StringWrapper;

use Zend\Stdlib\Exception;

class MbString extends AbstractStringWrapper
{
    /**
     * List of supported character sets (upper case)
     *
     * @var null|string[]
     * @link http://php.net/manual/mbstring.supported-encodings.php
     */
    protected static $encodings = null;

    /**
     * Get a list of supported character encodings
     *
     * @return string[]
     */
    public static function getSupportedEncodings()
    {
        if (static::$encodings === null) {
            static::$encodings = array_map('strtoupper', mb_list_encodings());

            // FIXME: Converting € (UTF-8) to ISO-8859-16 gives a wrong result
            $indexIso885916 = array_search('ISO-8859-16', static::$encodings, true);
            if ($indexIso885916 !== false) {
                unset(static::$encodings[$indexIso885916]);
            }
        }

        return static::$encodings;
    }

    /**
     * Constructor
     *
     * @throws Exception\ExtensionNotLoadedException
     */
    public function __construct()
    {
        if (!extension_loaded('mbstring')) {
            throw new Exception\ExtensionNotLoadedException(
                'PHP extension "mbstring" is required for this wrapper'
            );
        }
    }

    /**
     * Returns the length of the given string
     *
     * @param string $str
     * @return int|false
     */
    public function strlen($str)
    {
        return mb_strlen($str, $this->getEncoding());
    }

    /**
     * Returns the portion of string specified by the start and length parameters
     *
     * @param string   $str
     * @param int      $offset
     * @param int|null $length
     * @return string|false
     */
    public function substr($str, $offset = 0, $length = null)
    {
        return mb_substr($str, $offset, $length, $this->getEncoding());
    }

    /**
     * Find the position of the first occurrence of a substring in a string
     *
     * @param string $haystack
     * @param string $needle
     * @param int    $offset
     * @return int|false
     */
    public function strpos($haystack, $needle, $offset = 0)
    {
        return mb_strpos($haystack, $needle, $offset, $this->getEncoding());
    }

    /**
     * Convert a string from defined encoding to the defined convert encoding
     *
     * @param string  $str
     * @param bool $reverse
     * @return string|false
     */
    public function convert($str, $reverse = false)
    {
        $encoding        = $this->getEncoding();
        $convertEncoding = $this->getConvertEncoding();

        if ($convertEncoding === null) {
            throw new Exception\LogicException(
                'No convert encoding defined'
            );
        }

        if ($encoding === $convertEncoding) {
            return $str;
        }

        $fromEncoding = $reverse ? $convertEncoding : $encoding;
        $toEncoding   = $reverse ? $encoding : $convertEncoding;
        return mb_convert_encoding($str, $toEncoding, $fromEncoding);
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Stdlib;

use Zend\Stdlib\StringWrapper\StringWrapperInterface;

/**
 * Utility class for handling strings of different character encodings
 * using available PHP extensions.
 *
 * Declared abstract, as we have no need for instantiation.
 */
abstract class StringUtils
{
    /**
     * Ordered list of registered string wrapper instances
     *
     * @var StringWrapperInterface[]
     */
    protected static $wrapperRegistry = null;

    /**
     * A list of known single-byte character encodings (upper-case)
     *
     * @var string[]
     */
    protected static $singleByteEncodings = [
        'ASCII', '7BIT', '8BIT',
        'ISO-8859-1', 'ISO-8859-2', 'ISO-8859-3', 'ISO-8859-4', 'ISO-8859-5',
        'ISO-8859-6', 'ISO-8859-7', 'ISO-8859-8', 'ISO-8859-9', 'ISO-8859-10',
        'ISO-8859-11', 'ISO-8859-13', 'ISO-8859-14', 'ISO-8859-15', 'ISO-8859-16',
        'CP-1251', 'CP-1252',
        // TODO
    ];

    /**
     * Is PCRE compiled with Unicode support?
     *
     * @var bool
     **/
    protected static $hasPcreUnicodeSupport = null;

    /**
     * Get registered wrapper classes
     *
     * @return string[]
     */
    public static function getRegisteredWrappers()
    {
        if (static::$wrapperRegistry === null) {
            static::$wrapperRegistry = [];

            if (extension_loaded('intl')) {
                static::$wrapperRegistry[] = 'Zend\Stdlib\StringWrapper\Intl';
            }

            if (extension_loaded('mbstring')) {
                static::$wrapperRegistry[] = 'Zend\Stdlib\StringWrapper\MbString';
            }

            if (extension_loaded('iconv')) {
                static::$wrapperRegistry[] = 'Zend\Stdlib\StringWrapper\Iconv';
            }

            static::$wrapperRegistry[] = 'Zend\Stdlib\StringWrapper\Native';
        }

        return static::$wrapperRegistry;
    }

    /**
     * Register a string wrapper class
     *
     * @param string $wrapper
     * @return void
     */
    public static function registerWrapper($wrapper)
    {
        $wrapper = (string) $wrapper;
        if (!in_array($wrapper, static::$wrapperRegistry, true)) {
            static::$wrapperRegistry[] = $wrapper;
        }
    }

    /**
     * Unregister a string wrapper class
     *
     * @param string $wrapper
     * @return void
     */
    public static function unregisterWrapper($wrapper)
    {
        $index = array_search((string) $wrapper, static::$wrapperRegistry, true);
        if ($index !== false) {
            unset(static::$wrapperRegistry[$index]);
        }
    }

    /**
     * Reset all registered wrappers so the default wrappers will be used
     *
     * @return void
     */
    public static function resetRegisteredWrappers()
    {
        static::$wrapperRegistry = null;
    }

    /**
     * Get the first string wrapper supporting the given character encoding
     * and supports to convert into the given convert encoding.
     *
     * @param string      $encoding        Character encoding to support
     * @param string|null $convertEncoding OPTIONAL character encoding to convert in
     * @return StringWrapperInterface
     * @throws Exception\RuntimeException If no wrapper supports given character encodings
     */
    public static function getWrapper($encoding = 'UTF-8', $convertEncoding = null)
    {
        foreach (static::getRegisteredWrappers() as $wrapperClass) {
            if ($wrapperClass::isSupported($encoding, $convertEncoding)) {
                $wrapper = new $wrapperClass($encoding, $convertEncoding);
                $wrapper->setEncoding($encoding, $convertEncoding);
                return $wrapper;
            }
        }

        throw new Exception\RuntimeException(
            'No wrapper found supporting "' . $encoding . '"'
            . (($convertEncoding !== null) ? ' and "' . $convertEncoding . '"' : '')
        );
    }

    /**
     * Get a list of all known single-byte character encodings
     *
     * @return string[]
     */
    public static function getSingleByteEncodings()
    {
        return static::$singleByteEncodings;
    }

    /**
     * Check if a given encoding is a known single-byte character encoding
     *
     * @param string $encoding
     * @return bool
     */
    public static function isSingleByteEncoding($encoding)
    {
        return in_array(strtoupper($encoding), static::$singleByteEncodings);
    }

    /**
     * Check if a given string is valid UTF-8 encoded
     *
     * @param string $str
     * @return bool
     */
    public static function isValidUtf8($str)
    {
        return is_string($str) && ($str === '' || preg_match('/^./su', $str) == 1);
    }

    /**
     * Is PCRE compiled with Unicode support?
     *
     * @return bool
     */
    public static function hasPcreUnicodeSupport()
    {
        if (static::$hasPcreUnicodeSupport === null) {
            ErrorHandler::start();
            static::$hasPcreUnicodeSupport = defined('PREG_BAD_UTF8_OFFSET_ERROR') && preg_match('/\pL/u', 'a') == 1;
            ErrorHandler::stop();
        }
        return static::$hasPcreUnicodeSupport;
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Validator;

interface ValidatorInterface
{
    /**
     * Returns true if and only if $value meets the validation requirements
     *
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     *
     * @param  mixed $value
     * @return bool
     * @throws Exception\RuntimeException If validation of $value is impossible
     */
    public function isValid($value);

    /**
     * Returns an array of messages that explain why the most recent isValid()
     * call returned false. The array keys are validation failure message identifiers,
     * and the array values are the corresponding human-readable message strings.
     *
     * If isValid() was never called or if the most recent isValid() call
     * returned true, then this method returns an empty array.
     *
     * @return array
     */
    public function getMessages();
}

/**
 * Zend Framework (http://framework.zend.com/)
 *;
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Validator\Translator;

interface TranslatorAwareInterface
{
    /**
     * Sets translator to use in helper
     *
     * @param  TranslatorInterface $translator  [optional] translator.
     *             Default is null, which sets no translator.
     * @param  string $textDomain  [optional] text domain
     *             Default is null, which skips setTranslatorTextDomain
     * @return self
     */
    public function setTranslator(?TranslatorInterface $translator = null, $textDomain = null);

    /**
     * Returns translator used in object
     *
     * @return TranslatorInterface|null
     */
    public function getTranslator();

    /**
     * Checks if the object has a translator
     *
     * @return bool
     */
    public function hasTranslator();

    /**
     * Sets whether translator is enabled and should be used
     *
     * @param  bool $enabled [optional] whether translator should be used.
     *                  Default is true.
     * @return self
     */
    public function setTranslatorEnabled($enabled = true);

    /**
     * Returns whether translator is enabled and should be used
     *
     * @return bool
     */
    public function isTranslatorEnabled();

    /**
     * Set translation text domain
     *
     * @param  string $textDomain
     * @return TranslatorAwareInterface
     */
    public function setTranslatorTextDomain($textDomain = 'default');

    /**
     * Return the translation text domain
     *
     * @return string
     */
    public function getTranslatorTextDomain();
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Validator;

use Traversable;
use Zend\Stdlib\ArrayUtils;

abstract class AbstractValidator implements
    Translator\TranslatorAwareInterface,
    ValidatorInterface
{
    /**
     * The value to be validated
     *
     * @var mixed
     */
    protected $value;

    /**
     * Default translation object for all validate objects
     * @var Translator\TranslatorInterface
     */
    protected static $defaultTranslator;

    /**
     * Default text domain to be used with translator
     * @var string
     */
    protected static $defaultTranslatorTextDomain = 'default';

    /**
     * Limits the maximum returned length of an error message
     *
     * @var int
     */
    protected static $messageLength = -1;

    protected $abstractOptions = [
        'messages'             => [], // Array of validation failure messages
        'messageTemplates'     => [], // Array of validation failure message templates
        'messageVariables'     => [], // Array of additional variables available for validation failure messages
        'translator'           => null,    // Translation object to used -> Translator\TranslatorInterface
        'translatorTextDomain' => null,    // Translation text domain
        'translatorEnabled'    => true,    // Is translation enabled?
        'valueObscured'        => false,   // Flag indicating whether or not value should be obfuscated
                                           // in error messages
    ];

    /**
     * Abstract constructor for all validators
     * A validator should accept following parameters:
     *  - nothing f.e. Validator()
     *  - one or multiple scalar values f.e. Validator($first, $second, $third)
     *  - an array f.e. Validator(array($first => 'first', $second => 'second', $third => 'third'))
     *  - an instance of Traversable f.e. Validator($config_instance)
     *
     * @param array|Traversable $options
     */
    public function __construct($options = null)
    {
        // The abstract constructor allows no scalar values
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        if (isset($this->messageTemplates)) {
            $this->abstractOptions['messageTemplates'] = $this->messageTemplates;
        }

        if (isset($this->messageVariables)) {
            $this->abstractOptions['messageVariables'] = $this->messageVariables;
        }

        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    /**
     * Returns an option
     *
     * @param string $option Option to be returned
     * @return mixed Returned option
     * @throws Exception\InvalidArgumentException
     */
    public function getOption($option)
    {
        if (array_key_exists($option, $this->abstractOptions)) {
            return $this->abstractOptions[$option];
        }

        if (isset($this->options) && array_key_exists($option, $this->options)) {
            return $this->options[$option];
        }

        throw new Exception\InvalidArgumentException("Invalid option '$option'");
    }

    /**
     * Returns all available options
     *
     * @return array Array with all available options
     */
    public function getOptions()
    {
        $result = $this->abstractOptions;
        if (isset($this->options)) {
            $result += $this->options;
        }
        return $result;
    }

    /**
     * Sets one or multiple options
     *
     * @param  array|Traversable $options Options to set
     * @throws Exception\InvalidArgumentException If $options is not an array or Traversable
     * @return AbstractValidator Provides fluid interface
     */
    public function setOptions($options = [])
    {
        if (!is_array($options) && !$options instanceof Traversable) {
            throw new Exception\InvalidArgumentException(__METHOD__ . ' expects an array or Traversable');
        }

        foreach ($options as $name => $option) {
            $fname = 'set' . ucfirst($name);
            $fname2 = 'is' . ucfirst($name);
            if (($name != 'setOptions') && method_exists($this, $name)) {
                $this->{$name}($option);
            } elseif (($fname != 'setOptions') && method_exists($this, $fname)) {
                $this->{$fname}($option);
            } elseif (method_exists($this, $fname2)) {
                $this->{$fname2}($option);
            } elseif (isset($this->options)) {
                $this->options[$name] = $option;
            } else {
                $this->abstractOptions[$name] = $option;
            }
        }

        return $this;
    }

    /**
     * Returns array of validation failure messages
     *
     * @return array
     */
    public function getMessages()
    {
        return array_unique($this->abstractOptions['messages'], SORT_REGULAR);
    }

    /**
     * Invoke as command
     *
     * @param  mixed $value
     * @return bool
     */
    public function __invoke($value)
    {
        return $this->isValid($value);
    }

    /**
     * Returns an array of the names of variables that are used in constructing validation failure messages
     *
     * @return array
     */
    public function getMessageVariables()
    {
        return array_keys($this->abstractOptions['messageVariables']);
    }

    /**
     * Returns the message templates from the validator
     *
     * @return array
     */
    public function getMessageTemplates()
    {
        return $this->abstractOptions['messageTemplates'];
    }

    /**
     * Sets the validation failure message template for a particular key
     *
     * @param  string $messageString
     * @param  string $messageKey     OPTIONAL
     * @return AbstractValidator Provides a fluent interface
     * @throws Exception\InvalidArgumentException
     */
    public function setMessage($messageString, $messageKey = null)
    {
        if ($messageKey === null) {
            $keys = array_keys($this->abstractOptions['messageTemplates']);
            foreach ($keys as $key) {
                $this->setMessage($messageString, $key);
            }
            return $this;
        }

        if (!isset($this->abstractOptions['messageTemplates'][$messageKey])) {
            throw new Exception\InvalidArgumentException("No message template exists for key '$messageKey'");
        }

        $this->abstractOptions['messageTemplates'][$messageKey] = $messageString;
        return $this;
    }

    /**
     * Sets validation failure message templates given as an array, where the array keys are the message keys,
     * and the array values are the message template strings.
     *
     * @param  array $messages
     * @return AbstractValidator
     */
    public function setMessages(array $messages)
    {
        foreach ($messages as $key => $message) {
            $this->setMessage($message, $key);
        }
        return $this;
    }

    /**
     * Magic function returns the value of the requested property, if and only if it is the value or a
     * message variable.
     *
     * @param  string $property
     * @return mixed
     * @throws Exception\InvalidArgumentException
     */
    public function __get($property)
    {
        if ($property == 'value') {
            return $this->value;
        }

        if (array_key_exists($property, $this->abstractOptions['messageVariables'])) {
            $result = $this->abstractOptions['messageVariables'][$property];
            if (is_array($result)) {
                return $this->{key($result)}[current($result)];
            }
            return $this->{$result};
        }

        if (isset($this->messageVariables) && array_key_exists($property, $this->messageVariables)) {
            $result = $this->{$this->messageVariables[$property]};
            if (is_array($result)) {
                return $this->{key($result)}[current($result)];
            }
            return $this->{$result};
        }

        throw new Exception\InvalidArgumentException("No property exists by the name '$property'");
    }

    /**
     * Constructs and returns a validation failure message with the given message key and value.
     *
     * Returns null if and only if $messageKey does not correspond to an existing template.
     *
     * If a translator is available and a translation exists for $messageKey,
     * the translation will be used.
     *
     * @param  string              $messageKey
     * @param  string|array|object $value
     * @return string
     */
    protected function createMessage($messageKey, $value)
    {
        if (!isset($this->abstractOptions['messageTemplates'][$messageKey])) {
            return;
        }

        $message = $this->abstractOptions['messageTemplates'][$messageKey];

        $message = $this->translateMessage($messageKey, $message);

        if (is_object($value) &&
            !in_array('__toString', get_class_methods($value))
        ) {
            $value = get_class($value) . ' object';
        } elseif (is_array($value)) {
            $value = var_export($value, 1);
        } else {
            $value = (string) $value;
        }

        if ($this->isValueObscured()) {
            $value = str_repeat('*', strlen($value));
        }

        $message = str_replace('%value%', (string) $value, $message);
        foreach ($this->abstractOptions['messageVariables'] as $ident => $property) {
            if (is_array($property)) {
                $value = $this->{key($property)}[current($property)];
                if (is_array($value)) {
                    $value = '[' . implode(', ', $value) . ']';
                }
            } else {
                $value = $this->$property;
            }
            $message = str_replace("%$ident%", (string) $value, $message);
        }

        $length = self::getMessageLength();
        if (($length > -1) && (strlen($message) > $length)) {
            $message = substr($message, 0, ($length - 3)) . '...';
        }

        return $message;
    }

    /**
     * @param  string $messageKey
     * @param  string $value      OPTIONAL
     * @return void
     */
    protected function error($messageKey, $value = null)
    {
        if ($messageKey === null) {
            $keys = array_keys($this->abstractOptions['messageTemplates']);
            $messageKey = current($keys);
        }

        if ($value === null) {
            $value = $this->value;
        }

        $this->abstractOptions['messages'][$messageKey] = $this->createMessage($messageKey, $value);
    }

    /**
     * Returns the validation value
     *
     * @return mixed Value to be validated
     */
    protected function getValue()
    {
        return $this->value;
    }

    /**
     * Sets the value to be validated and clears the messages and errors arrays
     *
     * @param  mixed $value
     * @return void
     */
    protected function setValue($value)
    {
        $this->value               = $value;
        $this->abstractOptions['messages'] = [];
    }

    /**
     * Set flag indicating whether or not value should be obfuscated in messages
     *
     * @param  bool $flag
     * @return AbstractValidator
     */
    public function setValueObscured($flag)
    {
        $this->abstractOptions['valueObscured'] = (bool) $flag;
        return $this;
    }

    /**
     * Retrieve flag indicating whether or not value should be obfuscated in
     * messages
     *
     * @return bool
     */
    public function isValueObscured()
    {
        return $this->abstractOptions['valueObscured'];
    }

    /**
     * Set translation object
     *
     * @param  Translator\TranslatorInterface|null $translator
     * @param  string          $textDomain (optional)
     * @return AbstractValidator
     * @throws Exception\InvalidArgumentException
     */
    public function setTranslator(?Translator\TranslatorInterface $translator = null, $textDomain = null)
    {
        $this->abstractOptions['translator'] = $translator;
        if (null !== $textDomain) {
            $this->setTranslatorTextDomain($textDomain);
        }
        return $this;
    }

    /**
     * Return translation object
     *
     * @return Translator\TranslatorInterface|null
     */
    public function getTranslator()
    {
        if (! $this->isTranslatorEnabled()) {
            return;
        }

        if (null === $this->abstractOptions['translator']) {
            $this->abstractOptions['translator'] = self::getDefaultTranslator();
        }

        return $this->abstractOptions['translator'];
    }

    /**
     * Does this validator have its own specific translator?
     *
     * @return bool
     */
    public function hasTranslator()
    {
        return (bool) $this->abstractOptions['translator'];
    }

    /**
     * Set translation text domain
     *
     * @param  string $textDomain
     * @return AbstractValidator
     */
    public function setTranslatorTextDomain($textDomain = 'default')
    {
        $this->abstractOptions['translatorTextDomain'] = $textDomain;
        return $this;
    }

    /**
     * Return the translation text domain
     *
     * @return string
     */
    public function getTranslatorTextDomain()
    {
        if (null === $this->abstractOptions['translatorTextDomain']) {
            $this->abstractOptions['translatorTextDomain'] =
                self::getDefaultTranslatorTextDomain();
        }
        return $this->abstractOptions['translatorTextDomain'];
    }

    /**
     * Set default translation object for all validate objects
     *
     * @param  Translator\TranslatorInterface|null $translator
     * @param  string          $textDomain (optional)
     * @return void
     * @throws Exception\InvalidArgumentException
     */
    public static function setDefaultTranslator(?Translator\TranslatorInterface $translator = null, $textDomain = null)
    {
        static::$defaultTranslator = $translator;
        if (null !== $textDomain) {
            self::setDefaultTranslatorTextDomain($textDomain);
        }
    }

    /**
     * Get default translation object for all validate objects
     *
     * @return Translator\TranslatorInterface|null
     */
    public static function getDefaultTranslator()
    {
        return static::$defaultTranslator;
    }

    /**
     * Is there a default translation object set?
     *
     * @return bool
     */
    public static function hasDefaultTranslator()
    {
        return (bool) static::$defaultTranslator;
    }

    /**
     * Set default translation text domain for all validate objects
     *
     * @param  string $textDomain
     * @return void
     */
    public static function setDefaultTranslatorTextDomain($textDomain = 'default')
    {
        static::$defaultTranslatorTextDomain = $textDomain;
    }

    /**
     * Get default translation text domain for all validate objects
     *
     * @return string
     */
    public static function getDefaultTranslatorTextDomain()
    {
        return static::$defaultTranslatorTextDomain;
    }

    /**
     * Indicate whether or not translation should be enabled
     *
     * @param  bool $flag
     * @return AbstractValidator
     */
    public function setTranslatorEnabled($flag = true)
    {
        $this->abstractOptions['translatorEnabled'] = (bool) $flag;
        return $this;
    }

    /**
     * Is translation enabled?
     *
     * @return bool
     */
    public function isTranslatorEnabled()
    {
        return $this->abstractOptions['translatorEnabled'];
    }

    /**
     * Returns the maximum allowed message length
     *
     * @return int
     */
    public static function getMessageLength()
    {
        return static::$messageLength;
    }

    /**
     * Sets the maximum allowed message length
     *
     * @param int $length
     */
    public static function setMessageLength($length = -1)
    {
        static::$messageLength = $length;
    }

    /**
     * Translate a validation message
     *
     * @param  string $messageKey
     * @param  string $message
     * @return string
     */
    protected function translateMessage($messageKey, $message)
    {
        $translator = $this->getTranslator();
        if (!$translator) {
            return $message;
        }

        return $translator->translate($message, $this->getTranslatorTextDomain());
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Validator;

use Countable;
use Zend\Stdlib\PriorityQueue;

class ValidatorChain implements
    Countable,
    ValidatorInterface
{
    /**
     * Default priority at which validators are added
     */
    const DEFAULT_PRIORITY = 1;

    /**
     * @var ValidatorPluginManager
     */
    protected $plugins;

    /**
     * Validator chain
     *
     * @var PriorityQueue
     */
    protected $validators;

    /**
     * Array of validation failure messages
     *
     * @var array
     */
    protected $messages = [];

    /**
     * Initialize validator chain
     */
    public function __construct()
    {
        $this->validators = new PriorityQueue();
    }

    /**
     * Return the count of attached validators
     *
     * @return int
     */
    #[\ReturnTypeWillChange]
    public function count()
    {
        return count($this->validators);
    }

    /**
     * Get plugin manager instance
     *
     * @return ValidatorPluginManager
     */
    public function getPluginManager()
    {
        if (!$this->plugins) {
            $this->setPluginManager(new ValidatorPluginManager());
        }
        return $this->plugins;
    }

    /**
     * Set plugin manager instance
     *
     * @param  ValidatorPluginManager $plugins Plugin manager
     * @return ValidatorChain
     */
    public function setPluginManager(ValidatorPluginManager $plugins)
    {
        $this->plugins = $plugins;
        return $this;
    }

    /**
     * Retrieve a validator by name
     *
     * @param  string     $name    Name of validator to return
     * @param  null|array $options Options to pass to validator constructor (if not already instantiated)
     * @return ValidatorInterface
     */
    public function plugin($name, ?array $options = null)
    {
        $plugins = $this->getPluginManager();
        return $plugins->get($name, $options);
    }

    /**
     * Attach a validator to the end of the chain
     *
     * If $breakChainOnFailure is true, then if the validator fails, the next validator in the chain,
     * if one exists, will not be executed.
     *
     * @param  ValidatorInterface $validator
     * @param  bool               $breakChainOnFailure
     * @param  int                $priority            Priority at which to enqueue validator; defaults to
     *                                                          1 (higher executes earlier)
     *
     * @throws Exception\InvalidArgumentException
     *
     * @return self
     */
    public function attach(
        ValidatorInterface $validator,
        $breakChainOnFailure = false,
        $priority = self::DEFAULT_PRIORITY
    ) {
        $this->validators->insert(
            [
                'instance'            => $validator,
                'breakChainOnFailure' => (bool) $breakChainOnFailure,
            ],
            $priority
        );

        return $this;
    }

    /**
     * Proxy to attach() to keep BC
     *
     * @deprecated Please use attach()
     * @param  ValidatorInterface      $validator
     * @param  bool                 $breakChainOnFailure
     * @param  int                  $priority
     * @return ValidatorChain Provides a fluent interface
     */
    public function addValidator(ValidatorInterface $validator, $breakChainOnFailure = false, $priority = self::DEFAULT_PRIORITY)
    {
        return $this->attach($validator, $breakChainOnFailure, $priority);
    }

    /**
     * Adds a validator to the beginning of the chain
     *
     * If $breakChainOnFailure is true, then if the validator fails, the next validator in the chain,
     * if one exists, will not be executed.
     *
     * @param  ValidatorInterface      $validator
     * @param  bool                 $breakChainOnFailure
     * @return ValidatorChain Provides a fluent interface
     */
    public function prependValidator(ValidatorInterface $validator, $breakChainOnFailure = false)
    {
        $priority = self::DEFAULT_PRIORITY;

        if (!$this->validators->isEmpty()) {
            $extractedNodes = $this->validators->toArray(PriorityQueue::EXTR_PRIORITY);
            rsort($extractedNodes, SORT_NUMERIC);
            $priority = $extractedNodes[0] + 1;
        }

        $this->validators->insert(
            [
                'instance'            => $validator,
                'breakChainOnFailure' => (bool) $breakChainOnFailure,
            ],
            $priority
        );
        return $this;
    }

    /**
     * Use the plugin manager to add a validator by name
     *
     * @param  string $name
     * @param  array $options
     * @param  bool $breakChainOnFailure
     * @param  int $priority
     * @return ValidatorChain
     */
    public function attachByName($name, $options = [], $breakChainOnFailure = false, $priority = self::DEFAULT_PRIORITY)
    {
        if (isset($options['break_chain_on_failure'])) {
            $breakChainOnFailure = (bool) $options['break_chain_on_failure'];
        }

        if (isset($options['breakchainonfailure'])) {
            $breakChainOnFailure = (bool) $options['breakchainonfailure'];
        }

        $this->attach($this->plugin($name, $options), $breakChainOnFailure, $priority);

        return $this;
    }

    /**
     * Proxy to attachByName() to keep BC
     *
     * @deprecated Please use attachByName()
     * @param  string $name
     * @param  array  $options
     * @param  bool   $breakChainOnFailure
     * @return ValidatorChain
     */
    public function addByName($name, $options = [], $breakChainOnFailure = false)
    {
        return $this->attachByName($name, $options, $breakChainOnFailure);
    }

    /**
     * Use the plugin manager to prepend a validator by name
     *
     * @param  string $name
     * @param  array  $options
     * @param  bool   $breakChainOnFailure
     * @return ValidatorChain
     */
    public function prependByName($name, $options = [], $breakChainOnFailure = false)
    {
        $validator = $this->plugin($name, $options);
        $this->prependValidator($validator, $breakChainOnFailure);
        return $this;
    }

    /**
     * Returns true if and only if $value passes all validations in the chain
     *
     * Validators are run in the order in which they were added to the chain (FIFO).
     *
     * @param  mixed $value
     * @param  mixed $context Extra "context" to provide the validator
     * @return bool
     */
    public function isValid($value, $context = null)
    {
        $this->messages = [];
        $result         = true;
        foreach ($this->validators as $element) {
            $validator = $element['instance'];
            if ($validator->isValid($value, $context)) {
                continue;
            }
            $result         = false;
            $messages       = $validator->getMessages();
            $this->messages = array_replace_recursive($this->messages, $messages);
            if ($element['breakChainOnFailure']) {
                break;
            }
        }
        return $result;
    }

    /**
     * Merge the validator chain with the one given in parameter
     *
     * @param ValidatorChain $validatorChain
     * @return ValidatorChain
     */
    public function merge(ValidatorChain $validatorChain)
    {
        foreach ($validatorChain->validators->toArray(PriorityQueue::EXTR_BOTH) as $item) {
            $this->attach($item['data']['instance'], $item['data']['breakChainOnFailure'], $item['priority']);
        }

        return $this;
    }

    /**
     * Returns array of validation failure messages
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Get all the validators
     *
     * @return PriorityQueue
     */
    public function getValidators()
    {
        return $this->validators->toArray(PriorityQueue::EXTR_DATA);
    }

    /**
     * Invoke chain as command
     *
     * @param  mixed $value
     * @return bool
     */
    public function __invoke($value)
    {
        return $this->isValid($value);
    }

    /**
     * Deep clone handling
     */
    public function __clone()
    {
        $this->validators = clone $this->validators;
    }

    /**
     * Prepare validator chain for serialization
     *
     * Plugin manager (property 'plugins') cannot
     * be serialized. On wakeup the property remains unset
     * and next invocation to getPluginManager() sets
     * the default plugin manager instance (ValidatorPluginManager).
     *
     * @return array
     */
    public function __sleep()
    {
        return ['validators', 'messages'];
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Validator;

class EmailAddress extends AbstractValidator
{
    const INVALID            = 'emailAddressInvalid';
    const INVALID_FORMAT     = 'emailAddressInvalidFormat';
    const INVALID_HOSTNAME   = 'emailAddressInvalidHostname';
    const INVALID_MX_RECORD  = 'emailAddressInvalidMxRecord';
    const INVALID_SEGMENT    = 'emailAddressInvalidSegment';
    const DOT_ATOM           = 'emailAddressDotAtom';
    const QUOTED_STRING      = 'emailAddressQuotedString';
    const INVALID_LOCAL_PART = 'emailAddressInvalidLocalPart';
    const LENGTH_EXCEEDED    = 'emailAddressLengthExceeded';

    /**
     * @var array
     */
    protected $messageTemplates = [
        self::INVALID            => "Invalid type given. String expected",
        self::INVALID_FORMAT     => "The input is not a valid email address. Use the basic format local-part@hostname",
        self::INVALID_HOSTNAME   => "'%hostname%' is not a valid hostname for the email address",
        self::INVALID_MX_RECORD  => "'%hostname%' does not appear to have any valid MX or A records for the email address",
        self::INVALID_SEGMENT    => "'%hostname%' is not in a routable network segment. The email address should not be resolved from public network",
        self::DOT_ATOM           => "'%localPart%' can not be matched against dot-atom format",
        self::QUOTED_STRING      => "'%localPart%' can not be matched against quoted-string format",
        self::INVALID_LOCAL_PART => "'%localPart%' is not a valid local part for the email address",
        self::LENGTH_EXCEEDED    => "The input exceeds the allowed length",
    ];

    /**
     * @var array
     */
    protected $messageVariables = [
        'hostname'  => 'hostname',
        'localPart' => 'localPart'
    ];

    /**
     * @var string
     */
    protected $hostname;

    /**
     * @var string
     */
    protected $localPart;

    /**
     * Returns the found mx record informations
     *
     * @var array
     */
    protected $mxRecord;

    /**
     * Internal options array
     */
    protected $options = [
        'useMxCheck'        => false,
        'useDeepMxCheck'    => false,
        'useDomainCheck'    => true,
        'allow'             => Hostname::ALLOW_DNS,
        'strict'            => true,
        'hostnameValidator' => null,
    ];

    /**
     * Instantiates hostname validator for local use
     *
     * The following additional option keys are supported:
     * 'hostnameValidator' => A hostname validator, see Zend\Validator\Hostname
     * 'allow'             => Options for the hostname validator, see Zend\Validator\Hostname::ALLOW_*
     * 'strict'            => Whether to adhere to strictest requirements in the spec
     * 'useMxCheck'        => If MX check should be enabled, boolean
     * 'useDeepMxCheck'    => If a deep MX check should be done, boolean
     *
     * @param array|\Traversable $options OPTIONAL
     */
    public function __construct($options = [])
    {
        if (!is_array($options)) {
            $options = func_get_args();
            $temp['allow'] = array_shift($options);
            if (!empty($options)) {
                $temp['useMxCheck'] = array_shift($options);
            }

            if (!empty($options)) {
                $temp['hostnameValidator'] = array_shift($options);
            }

            $options = $temp;
        }

        parent::__construct($options);
    }

    /**
     * Sets the validation failure message template for a particular key
     * Adds the ability to set messages to the attached hostname validator
     *
     * @param  string $messageString
     * @param  string $messageKey     OPTIONAL
     * @return AbstractValidator Provides a fluent interface
     */
    public function setMessage($messageString, $messageKey = null)
    {
        if ($messageKey === null) {
            $this->getHostnameValidator()->setMessage($messageString);
            parent::setMessage($messageString);
            return $this;
        }

        if (!isset($this->messageTemplates[$messageKey])) {
            $this->getHostnameValidator()->setMessage($messageString, $messageKey);
        } else {
            parent::setMessage($messageString, $messageKey);
        }

        return $this;
    }

    /**
     * Returns the set hostname validator
     *
     * If was not previously set then lazy load a new one
     *
     * @return Hostname
     */
    public function getHostnameValidator()
    {
        if (!isset($this->options['hostnameValidator'])) {
            $this->options['hostnameValidator'] = new Hostname($this->getAllow());
        }

        return $this->options['hostnameValidator'];
    }

    /**
     * @param Hostname $hostnameValidator OPTIONAL
     * @return EmailAddress Provides a fluent interface
     */
    public function setHostnameValidator(?Hostname $hostnameValidator = null)
    {
        $this->options['hostnameValidator'] = $hostnameValidator;

        return $this;
    }

    /**
     * Returns the allow option of the attached hostname validator
     *
     * @return int
     */
    public function getAllow()
    {
        return $this->options['allow'];
    }

    /**
     * Sets the allow option of the hostname validator to use
     *
     * @param int $allow
     * @return EmailAddress Provides a fluent interface
     */
    public function setAllow($allow)
    {
        $this->options['allow'] = $allow;
        if (isset($this->options['hostnameValidator'])) {
            $this->options['hostnameValidator']->setAllow($allow);
        }

        return $this;
    }

    /**
     * Whether MX checking via getmxrr is supported or not
     *
     * @return bool
     */
    public function isMxSupported()
    {
        return function_exists('getmxrr');
    }

    /**
     * Returns the set validateMx option
     *
     * @return bool
     */
    public function getMxCheck()
    {
        return $this->options['useMxCheck'];
    }

    /**
     * Set whether we check for a valid MX record via DNS
     *
     * This only applies when DNS hostnames are validated
     *
     * @param  bool $mx Set allowed to true to validate for MX records, and false to not validate them
     * @return EmailAddress Fluid Interface
     */
    public function useMxCheck($mx)
    {
        $this->options['useMxCheck'] = (bool) $mx;
        return $this;
    }

    /**
     * Returns the set deepMxCheck option
     *
     * @return bool
     */
    public function getDeepMxCheck()
    {
        return $this->options['useDeepMxCheck'];
    }

    /**
     * Use deep validation for MX records
     *
     * @param  bool $deep Set deep to true to perform a deep validation process for MX records
     * @return EmailAddress Fluid Interface
     */
    public function useDeepMxCheck($deep)
    {
        $this->options['useDeepMxCheck'] = (bool) $deep;
        return $this;
    }

    /**
     * Returns the set domainCheck option
     *
     * @return bool
     */
    public function getDomainCheck()
    {
        return $this->options['useDomainCheck'];
    }

    /**
     * Sets if the domain should also be checked
     * or only the local part of the email address
     *
     * @param  bool $domain
     * @return EmailAddress Fluid Interface
     */
    public function useDomainCheck($domain = true)
    {
        $this->options['useDomainCheck'] = (bool) $domain;
        return $this;
    }

    /**
     * Returns if the given host is reserved
     *
     * The following addresses are seen as reserved
     * '0.0.0.0/8', '10.0.0.0/8', '127.0.0.0/8'
     * '100.64.0.0/10'
     * '172.16.0.0/12'
     * '198.18.0.0/15'
     * '169.254.0.0/16', '192.168.0.0/16'
     * '192.0.2.0/24', '192.88.99.0/24', '198.51.100.0/24', '203.0.113.0/24'
     * '224.0.0.0/4', '240.0.0.0/4'
     * @see http://en.wikipedia.org/wiki/Reserved_IP_addresses
     *
     * As of RFC5753 (JAN 2010), the following blocks are no longer reserved:
     *   - 128.0.0.0/16
     *   - 191.255.0.0/16
     *   - 223.255.255.0/24
     * @see http://tools.ietf.org/html/rfc5735#page-6
     *
     * As of RFC6598 (APR 2012), the following blocks are now reserved:
     *   - 100.64.0.0/10
     * @see http://tools.ietf.org/html/rfc6598#section-7
     *
     * @param string $host
     * @return bool Returns false when minimal one of the given addresses is not reserved
     */
    protected function isReserved($host)
    {
        if (!preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $host)) {
            $host = gethostbynamel($host);
        } else {
            $host = [$host];
        }

        if (empty($host)) {
            return false;
        }

        foreach ($host as $server) {
            // Search for 0.0.0.0/8, 10.0.0.0/8, 127.0.0.0/8
            if (!preg_match('/^(0|10|127)(\.([0-9]|[1-9][0-9]|1([0-9][0-9])|2([0-4][0-9]|5[0-5]))){3}$/', $server) &&
                // Search for 100.64.0.0/10
                !preg_match('/^100\.(6[0-4]|[7-9][0-9]|1[0-1][0-9]|12[0-7])(\.([0-9]|[1-9][0-9]|1([0-9][0-9])|2([0-4][0-9]|5[0-5]))){2}$/', $server) &&
                // Search for 172.16.0.0/12
                !preg_match('/^172\.(1[6-9]|2[0-9]|3[0-1])(\.([0-9]|[1-9][0-9]|1([0-9][0-9])|2([0-4][0-9]|5[0-5]))){2}$/', $server) &&
                // Search for 198.18.0.0/15
                !preg_match('/^198\.(1[8-9])(\.([0-9]|[1-9][0-9]|1([0-9][0-9])|2([0-4][0-9]|5[0-5]))){2}$/', $server) &&
                // Search for 169.254.0.0/16, 192.168.0.0/16
                !preg_match('/^(169\.254|192\.168)(\.([0-9]|[1-9][0-9]|1([0-9][0-9])|2([0-4][0-9]|5[0-5]))){2}$/', $server) &&
                // Search for 192.0.2.0/24, 192.88.99.0/24, 198.51.100.0/24, 203.0.113.0/24
                !preg_match('/^(192\.0\.2|192\.88\.99|198\.51\.100|203\.0\.113)\.([0-9]|[1-9][0-9]|1([0-9][0-9])|2([0-4][0-9]|5[0-5]))$/', $server) &&
                // Search for 224.0.0.0/4, 240.0.0.0/4
                !preg_match('/^(2(2[4-9]|[3-4][0-9]|5[0-5]))(\.([0-9]|[1-9][0-9]|1([0-9][0-9])|2([0-4][0-9]|5[0-5]))){3}$/', $server)
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Internal method to validate the local part of the email address
     *
     * @return bool
     */
    protected function validateLocalPart()
    {
        // First try to match the local part on the common dot-atom format
        $result = false;

        // Dot-atom characters are: 1*atext *("." 1*atext)
        // atext: ALPHA / DIGIT / and "!", "#", "$", "%", "&", "'", "*",
        //        "+", "-", "/", "=", "?", "^", "_", "`", "{", "|", "}", "~"
        $atext = 'a-zA-Z0-9\x21\x23\x24\x25\x26\x27\x2a\x2b\x2d\x2f\x3d\x3f\x5e\x5f\x60\x7b\x7c\x7d\x7e';
        if (preg_match('/^[' . $atext . ']+(\x2e+[' . $atext . ']+)*$/', $this->idnToAscii($this->localPart))) {
            $result = true;
        } else {
            // Try quoted string format (RFC 5321 Chapter 4.1.2)

            // Quoted-string characters are: DQUOTE *(qtext/quoted-pair) DQUOTE
            $qtext      = '\x20-\x21\x23-\x5b\x5d-\x7e'; // %d32-33 / %d35-91 / %d93-126
            $quotedPair = '\x20-\x7e'; // %d92 %d32-126
            if (preg_match('/^"(['. $qtext .']|\x5c[' . $quotedPair . '])*"$/', $this->localPart)) {
                $result = true;
            } else {
                $this->error(self::DOT_ATOM);
                $this->error(self::QUOTED_STRING);
                $this->error(self::INVALID_LOCAL_PART);
            }
        }

        return $result;
    }

    /**
     * Returns the found MX Record information after validation including weight for further processing
     *
     * @return array
     */
    public function getMXRecord()
    {
        return $this->mxRecord;
    }

    /**
     * Internal method to validate the servers MX records
     *
     * @return bool
     */
    protected function validateMXRecords()
    {
        $mxHosts = [];
        $weight  = [];
        $result = getmxrr($this->idnToAscii($this->hostname), $mxHosts, $weight);
        if (!empty($mxHosts) && !empty($weight)) {
            $this->mxRecord = array_combine($mxHosts, $weight);
        } else {
            $this->mxRecord = $mxHosts;
        }

        arsort($this->mxRecord);

        // Fallback to IPv4 hosts if no MX record found (RFC 2821 SS 5).
        if (!$result) {
            $result = gethostbynamel($this->hostname);
            if (is_array($result)) {
                $this->mxRecord = array_flip($result);
            }
        }

        if (!$result) {
            $this->error(self::INVALID_MX_RECORD);
            return $result;
        }

        if (!$this->options['useDeepMxCheck']) {
            return $result;
        }

        $validAddress = false;
        $reserved     = true;
        foreach ($this->mxRecord as $hostname => $weight) {
            $res = $this->isReserved($hostname);
            if (!$res) {
                $reserved = false;
            }

            if (!$res
                && (checkdnsrr($hostname, "A")
                || checkdnsrr($hostname, "AAAA")
                || checkdnsrr($hostname, "A6"))
            ) {
                $validAddress = true;
                break;
            }
        }

        if (!$validAddress) {
            $result = false;
            $error  = ($reserved) ? self::INVALID_SEGMENT : self::INVALID_MX_RECORD;
            $this->error($error);
        }

        return $result;
    }

    /**
     * Internal method to validate the hostname part of the email address
     *
     * @return bool
     */
    protected function validateHostnamePart()
    {
        $hostname = $this->getHostnameValidator()->setTranslator($this->getTranslator())
                         ->isValid($this->hostname);
        if (!$hostname) {
            $this->error(self::INVALID_HOSTNAME);
            // Get messages and errors from hostnameValidator
            foreach ($this->getHostnameValidator()->getMessages() as $code => $message) {
                $this->abstractOptions['messages'][$code] = $message;
            }
        } elseif ($this->options['useMxCheck']) {
            // MX check on hostname
            $hostname = $this->validateMXRecords();
        }

        return $hostname;
    }

    /**
     * Splits the given value in hostname and local part of the email address
     *
     * @param string $value Email address to be split
     * @return bool Returns false when the email can not be split
     */
    protected function splitEmailParts($value)
    {
        $value = is_string($value) ? $value : '';

        // Split email address up and disallow '..'
        if (strpos($value, '..') !== false
            || ! preg_match('/^(.+)@([^@]+)$/', $value, $matches)
        ) {
            return false;
        }

        $this->localPart = $matches[1];
        $this->hostname  = $matches[2];

        return true;
    }

    /**
     * Defined by Zend\Validator\ValidatorInterface
     *
     * Returns true if and only if $value is a valid email address
     * according to RFC2822
     *
     * @link   http://www.ietf.org/rfc/rfc2822.txt RFC2822
     * @link   http://www.columbia.edu/kermit/ascii.html US-ASCII characters
     * @param  string $value
     * @return bool
     */
    public function isValid($value)
    {
        if (!is_string($value)) {
            $this->error(self::INVALID);
            return false;
        }

        $length  = true;
        $this->setValue($this->idnToUtf8($value));

        // Split email address up and disallow '..'
        if (!$this->splitEmailParts($this->getValue())) {
            $this->error(self::INVALID_FORMAT);
            return false;
        }

        if ($this->getOption('strict') && (strlen($this->localPart) > 64) || (strlen($this->hostname) > 255)) {
            $length = false;
            $this->error(self::LENGTH_EXCEEDED);
        }

        // Match hostname part
        if ($this->options['useDomainCheck']) {
            $hostname = $this->validateHostnamePart();
        }

        $local = $this->validateLocalPart();

        // If both parts valid, return true
        if ($local && $length) {
            if (($this->options['useDomainCheck'] && $hostname) || !$this->options['useDomainCheck']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Safely convert UTF-8 encoded domain name to ASCII
     * @param string $email  the UTF-8 encoded email
     * @return string
     */
    protected function idnToAscii($email)
    {
        if (extension_loaded('intl')) {
            return (idn_to_ascii($email) ?: $email);
        }
        return $email;
    }

    /**
     * Safely convert ASCII encoded domain name to UTF-8
     * @param string $email the ASCII encoded email
     * @return string
     */
    protected function idnToUtf8($email)
    {
        if (extension_loaded('intl')) {
            // The documentation does not clarify what kind of failure
            // can happen in idn_to_utf8. One can assume if the source
            // is not IDN encoded, it would fail, but it usually returns
            // the source string in those cases.
            // But not when the source string is long enough.
            // Thus we default to source string ourselves.
            return idn_to_utf8($email) ?: $email;
        }
        return $email;
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Validator;

use Zend\Stdlib\StringUtils;

/**
 * Please note there are two standalone test scripts for testing IDN characters due to problems
 * with file encoding.
 *
 * The first is tests/Zend/Validator/HostnameTestStandalone.php which is designed to be run on
 * the command line.
 *
 * The second is tests/Zend/Validator/HostnameTestForm.php which is designed to be run via HTML
 * to allow users to test entering UTF-8 characters in a form.
 */
class Hostname extends AbstractValidator
{
    const CANNOT_DECODE_PUNYCODE  = 'hostnameCannotDecodePunycode';
    const INVALID                 = 'hostnameInvalid';
    const INVALID_DASH            = 'hostnameDashCharacter';
    const INVALID_HOSTNAME        = 'hostnameInvalidHostname';
    const INVALID_HOSTNAME_SCHEMA = 'hostnameInvalidHostnameSchema';
    const INVALID_LOCAL_NAME      = 'hostnameInvalidLocalName';
    const INVALID_URI             = 'hostnameInvalidUri';
    const IP_ADDRESS_NOT_ALLOWED  = 'hostnameIpAddressNotAllowed';
    const LOCAL_NAME_NOT_ALLOWED  = 'hostnameLocalNameNotAllowed';
    const UNDECIPHERABLE_TLD      = 'hostnameUndecipherableTld';
    const UNKNOWN_TLD             = 'hostnameUnknownTld';

    /**
     * @var array
     */
    protected $messageTemplates = [
        self::CANNOT_DECODE_PUNYCODE  => "The input appears to be a DNS hostname but the given punycode notation cannot be decoded",
        self::INVALID                 => "Invalid type given. String expected",
        self::INVALID_DASH            => "The input appears to be a DNS hostname but contains a dash in an invalid position",
        self::INVALID_HOSTNAME        => "The input does not match the expected structure for a DNS hostname",
        self::INVALID_HOSTNAME_SCHEMA => "The input appears to be a DNS hostname but cannot match against hostname schema for TLD '%tld%'",
        self::INVALID_LOCAL_NAME      => "The input does not appear to be a valid local network name",
        self::INVALID_URI             => "The input does not appear to be a valid URI hostname",
        self::IP_ADDRESS_NOT_ALLOWED  => "The input appears to be an IP address, but IP addresses are not allowed",
        self::LOCAL_NAME_NOT_ALLOWED  => "The input appears to be a local network name but local network names are not allowed",
        self::UNDECIPHERABLE_TLD      => "The input appears to be a DNS hostname but cannot extract TLD part",
        self::UNKNOWN_TLD             => "The input appears to be a DNS hostname but cannot match TLD against known list",
    ];

    /**
     * @var array
     */
    protected $messageVariables = [
        'tld' => 'tld',
    ];

    const ALLOW_DNS   = 1;  // Allows Internet domain names (e.g., example.com)
    const ALLOW_IP    = 2;  // Allows IP addresses
    const ALLOW_LOCAL = 4;  // Allows local network names (e.g., localhost, www.localdomain)
    const ALLOW_URI   = 8;  // Allows URI hostnames
    const ALLOW_ALL   = 15;  // Allows all types of hostnames

    /**
     * Array of valid top-level-domains
     *
     * @see ftp://data.iana.org/TLD/tlds-alpha-by-domain.txt  List of all TLDs by domain
     * @see http://www.iana.org/domains/root/db/ Official list of supported TLDs
     * @var array
     */
    protected $validTlds = [
        'abb',
        'abbott',
        'abogado',
        'ac',
        'academy',
        'accenture',
        'accountant',
        'accountants',
        'active',
        'actor',
        'ad',
        'ads',
        'adult',
        'ae',
        'aero',
        'af',
        'afl',
        'ag',
        'agency',
        'ai',
        'aig',
        'airforce',
        'al',
        'allfinanz',
        'alsace',
        'am',
        'amsterdam',
        'an',
        'android',
        'ao',
        'apartments',
        'aq',
        'aquarelle',
        'ar',
        'archi',
        'army',
        'arpa',
        'as',
        'asia',
        'associates',
        'at',
        'attorney',
        'au',
        'auction',
        'audio',
        'auto',
        'autos',
        'aw',
        'ax',
        'axa',
        'az',
        'ba',
        'band',
        'bank',
        'bar',
        'barclaycard',
        'barclays',
        'bargains',
        'bauhaus',
        'bayern',
        'bb',
        'bbc',
        'bd',
        'be',
        'beer',
        'berlin',
        'best',
        'bf',
        'bg',
        'bh',
        'bi',
        'bid',
        'bike',
        'bingo',
        'bio',
        'biz',
        'bj',
        'black',
        'blackfriday',
        'bloomberg',
        'blue',
        'bm',
        'bmw',
        'bn',
        'bnpparibas',
        'bo',
        'boats',
        'bond',
        'boo',
        'boutique',
        'br',
        'bridgestone',
        'broker',
        'brother',
        'brussels',
        'bs',
        'bt',
        'budapest',
        'build',
        'builders',
        'business',
        'buzz',
        'bv',
        'bw',
        'by',
        'bz',
        'bzh',
        'ca',
        'cab',
        'cafe',
        'cal',
        'camera',
        'camp',
        'cancerresearch',
        'canon',
        'capetown',
        'capital',
        'caravan',
        'cards',
        'care',
        'career',
        'careers',
        'cars',
        'cartier',
        'casa',
        'cash',
        'casino',
        'cat',
        'catering',
        'cbn',
        'cc',
        'cd',
        'center',
        'ceo',
        'cern',
        'cf',
        'cfa',
        'cfd',
        'cg',
        'ch',
        'channel',
        'chat',
        'cheap',
        'chloe',
        'christmas',
        'chrome',
        'church',
        'ci',
        'cisco',
        'citic',
        'city',
        'ck',
        'cl',
        'claims',
        'cleaning',
        'click',
        'clinic',
        'clothing',
        'club',
        'cm',
        'cn',
        'co',
        'coach',
        'codes',
        'coffee',
        'college',
        'cologne',
        'com',
        'community',
        'company',
        'computer',
        'condos',
        'construction',
        'consulting',
        'contractors',
        'cooking',
        'cool',
        'coop',
        'corsica',
        'country',
        'coupons',
        'courses',
        'cr',
        'credit',
        'creditcard',
        'cricket',
        'crs',
        'cruises',
        'cu',
        'cuisinella',
        'cv',
        'cw',
        'cx',
        'cy',
        'cymru',
        'cyou',
        'cz',
        'dabur',
        'dad',
        'dance',
        'date',
        'dating',
        'datsun',
        'day',
        'dclk',
        'de',
        'deals',
        'degree',
        'delivery',
        'democrat',
        'dental',
        'dentist',
        'desi',
        'design',
        'dev',
        'diamonds',
        'diet',
        'digital',
        'direct',
        'directory',
        'discount',
        'dj',
        'dk',
        'dm',
        'dnp',
        'do',
        'docs',
        'dog',
        'doha',
        'domains',
        'doosan',
        'download',
        'durban',
        'dvag',
        'dz',
        'earth',
        'eat',
        'ec',
        'edu',
        'education',
        'ee',
        'eg',
        'email',
        'emerck',
        'energy',
        'engineer',
        'engineering',
        'enterprises',
        'epson',
        'equipment',
        'er',
        'erni',
        'es',
        'esq',
        'estate',
        'et',
        'eu',
        'eurovision',
        'eus',
        'events',
        'everbank',
        'exchange',
        'expert',
        'exposed',
        'express',
        'fail',
        'faith',
        'fan',
        'fans',
        'farm',
        'fashion',
        'feedback',
        'fi',
        'film',
        'finance',
        'financial',
        'firmdale',
        'fish',
        'fishing',
        'fit',
        'fitness',
        'fj',
        'fk',
        'flights',
        'florist',
        'flowers',
        'flsmidth',
        'fly',
        'fm',
        'fo',
        'foo',
        'football',
        'forex',
        'forsale',
        'foundation',
        'fr',
        'frl',
        'frogans',
        'fund',
        'furniture',
        'futbol',
        'fyi',
        'ga',
        'gal',
        'gallery',
        'garden',
        'gb',
        'gbiz',
        'gd',
        'gdn',
        'ge',
        'gent',
        'gf',
        'gg',
        'ggee',
        'gh',
        'gi',
        'gift',
        'gifts',
        'gives',
        'gl',
        'glass',
        'gle',
        'global',
        'globo',
        'gm',
        'gmail',
        'gmo',
        'gmx',
        'gn',
        'gold',
        'goldpoint',
        'golf',
        'goo',
        'goog',
        'google',
        'gop',
        'gov',
        'gp',
        'gq',
        'gr',
        'graphics',
        'gratis',
        'green',
        'gripe',
        'gs',
        'gt',
        'gu',
        'guge',
        'guide',
        'guitars',
        'guru',
        'gw',
        'gy',
        'hamburg',
        'hangout',
        'haus',
        'healthcare',
        'help',
        'here',
        'hermes',
        'hiphop',
        'hitachi',
        'hiv',
        'hk',
        'hm',
        'hn',
        'hockey',
        'holdings',
        'holiday',
        'homes',
        'honda',
        'horse',
        'host',
        'hosting',
        'house',
        'how',
        'hr',
        'ht',
        'hu',
        'ibm',
        'icbc',
        'icu',
        'id',
        'ie',
        'ifm',
        'il',
        'im',
        'immo',
        'immobilien',
        'in',
        'industries',
        'infiniti',
        'info',
        'ing',
        'ink',
        'institute',
        'insure',
        'int',
        'international',
        'investments',
        'io',
        'iq',
        'ir',
        'irish',
        'is',
        'it',
        'iwc',
        'java',
        'jcb',
        'je',
        'jetzt',
        'jewelry',
        'jll',
        'jm',
        'jo',
        'jobs',
        'joburg',
        'jp',
        'juegos',
        'kaufen',
        'kddi',
        'ke',
        'kg',
        'kh',
        'ki',
        'kim',
        'kitchen',
        'kiwi',
        'km',
        'kn',
        'koeln',
        'komatsu',
        'kp',
        'kr',
        'krd',
        'kred',
        'kw',
        'ky',
        'kyoto',
        'kz',
        'la',
        'lacaixa',
        'land',
        'lat',
        'latrobe',
        'lawyer',
        'lb',
        'lc',
        'lds',
        'lease',
        'leclerc',
        'legal',
        'lgbt',
        'li',
        'liaison',
        'lidl',
        'life',
        'lighting',
        'limited',
        'limo',
        'link',
        'lk',
        'loan',
        'loans',
        'lol',
        'london',
        'lotte',
        'lotto',
        'love',
        'lr',
        'ls',
        'lt',
        'ltda',
        'lu',
        'lupin',
        'luxe',
        'luxury',
        'lv',
        'ly',
        'ma',
        'madrid',
        'maif',
        'maison',
        'management',
        'mango',
        'market',
        'marketing',
        'markets',
        'marriott',
        'mba',
        'mc',
        'md',
        'me',
        'media',
        'meet',
        'melbourne',
        'meme',
        'memorial',
        'men',
        'menu',
        'mg',
        'mh',
        'miami',
        'mil',
        'mini',
        'mk',
        'ml',
        'mm',
        'mma',
        'mn',
        'mo',
        'mobi',
        'moda',
        'moe',
        'monash',
        'money',
        'mormon',
        'mortgage',
        'moscow',
        'motorcycles',
        'mov',
        'movie',
        'mp',
        'mq',
        'mr',
        'ms',
        'mt',
        'mtn',
        'mtpc',
        'mu',
        'museum',
        'mv',
        'mw',
        'mx',
        'my',
        'mz',
        'na',
        'nadex',
        'nagoya',
        'name',
        'navy',
        'nc',
        'ne',
        'nec',
        'net',
        'network',
        'neustar',
        'new',
        'news',
        'nexus',
        'nf',
        'ng',
        'ngo',
        'nhk',
        'ni',
        'nico',
        'ninja',
        'nissan',
        'nl',
        'no',
        'np',
        'nr',
        'nra',
        'nrw',
        'ntt',
        'nu',
        'nyc',
        'nz',
        'okinawa',
        'om',
        'one',
        'ong',
        'onl',
        'online',
        'ooo',
        'oracle',
        'org',
        'organic',
        'osaka',
        'otsuka',
        'ovh',
        'pa',
        'page',
        'panerai',
        'paris',
        'partners',
        'parts',
        'party',
        'pe',
        'pf',
        'pg',
        'ph',
        'pharmacy',
        'philips',
        'photo',
        'photography',
        'photos',
        'physio',
        'piaget',
        'pics',
        'pictet',
        'pictures',
        'pink',
        'pizza',
        'pk',
        'pl',
        'place',
        'plumbing',
        'plus',
        'pm',
        'pn',
        'pohl',
        'poker',
        'porn',
        'post',
        'pr',
        'praxi',
        'press',
        'pro',
        'prod',
        'productions',
        'prof',
        'properties',
        'property',
        'ps',
        'pt',
        'pub',
        'pw',
        'py',
        'qa',
        'qpon',
        'quebec',
        'racing',
        're',
        'realtor',
        'recipes',
        'red',
        'redstone',
        'rehab',
        'reise',
        'reisen',
        'reit',
        'ren',
        'rent',
        'rentals',
        'repair',
        'report',
        'republican',
        'rest',
        'restaurant',
        'review',
        'reviews',
        'rich',
        'rio',
        'rip',
        'ro',
        'rocks',
        'rodeo',
        'rs',
        'rsvp',
        'ru',
        'ruhr',
        'run',
        'rw',
        'ryukyu',
        'sa',
        'saarland',
        'sale',
        'samsung',
        'sap',
        'sarl',
        'saxo',
        'sb',
        'sc',
        'sca',
        'scb',
        'schmidt',
        'scholarships',
        'school',
        'schule',
        'schwarz',
        'science',
        'scot',
        'sd',
        'se',
        'seat',
        'sener',
        'services',
        'sew',
        'sex',
        'sexy',
        'sg',
        'sh',
        'shiksha',
        'shoes',
        'show',
        'shriram',
        'si',
        'singles',
        'site',
        'sj',
        'sk',
        'sky',
        'sl',
        'sm',
        'sn',
        'so',
        'soccer',
        'social',
        'software',
        'sohu',
        'solar',
        'solutions',
        'sony',
        'soy',
        'space',
        'spiegel',
        'spreadbetting',
        'sr',
        'st',
        'study',
        'style',
        'su',
        'sucks',
        'supplies',
        'supply',
        'support',
        'surf',
        'surgery',
        'suzuki',
        'sv',
        'swiss',
        'sx',
        'sy',
        'sydney',
        'systems',
        'sz',
        'taipei',
        'tatar',
        'tattoo',
        'tax',
        'taxi',
        'tc',
        'td',
        'team',
        'tech',
        'technology',
        'tel',
        'temasek',
        'tennis',
        'tf',
        'tg',
        'th',
        'thd',
        'theater',
        'tickets',
        'tienda',
        'tips',
        'tires',
        'tirol',
        'tj',
        'tk',
        'tl',
        'tm',
        'tn',
        'to',
        'today',
        'tokyo',
        'tools',
        'top',
        'toray',
        'toshiba',
        'tours',
        'town',
        'toys',
        'tr',
        'trade',
        'trading',
        'training',
        'travel',
        'trust',
        'tt',
        'tui',
        'tv',
        'tw',
        'tz',
        'ua',
        'ug',
        'uk',
        'university',
        'uno',
        'uol',
        'us',
        'uy',
        'uz',
        'va',
        'vacations',
        'vc',
        've',
        'vegas',
        'ventures',
        'versicherung',
        'vet',
        'vg',
        'vi',
        'viajes',
        'video',
        'villas',
        'vision',
        'vlaanderen',
        'vn',
        'vodka',
        'vote',
        'voting',
        'voto',
        'voyage',
        'vu',
        'wales',
        'wang',
        'watch',
        'webcam',
        'website',
        'wed',
        'wedding',
        'weir',
        'wf',
        'whoswho',
        'wien',
        'wiki',
        'williamhill',
        'win',
        'wme',
        'work',
        'works',
        'world',
        'ws',
        'wtc',
        'wtf',
        'xerox',
        'xin',
        '佛山',
        '慈善',
        '集团',
        '在线',
        '한국',
        'ভারত',
        '八卦',
        'موقع',
        '公益',
        '公司',
        '移动',
        '我爱你',
        'москва',
        'қаз',
        'онлайн',
        'сайт',
        'срб',
        'бел',
        '时尚',
        '淡马锡',
        'орг',
        '삼성',
        'சிங்கப்பூர்',
        '商标',
        '商店',
        '商城',
        'дети',
        'мкд',
        '工行',
        '中文网',
        '中信',
        '中国',
        '中國',
        '娱乐',
        '谷歌',
        'భారత్',
        'ලංකා',
        'ભારત',
        'भारत',
        '网店',
        'संगठन',
        '网络',
        'укр',
        '香港',
        '飞利浦',
        '台湾',
        '台灣',
        '手机',
        'мон',
        'الجزائر',
        'عمان',
        'ایران',
        'امارات',
        'بازار',
        'الاردن',
        'بھارت',
        'المغرب',
        'السعودية',
        'سودان',
        'مليسيا',
        '政府',
        'شبكة',
        'გე',
        '机构',
        '组织机构',
        '健康',
        'ไทย',
        'سورية',
        'рус',
        'рф',
        'تونس',
        'みんな',
        'グーグル',
        '世界',
        'ਭਾਰਤ',
        '网址',
        '游戏',
        'vermögensberater',
        'vermögensberatung',
        '企业',
        '信息',
        'مصر',
        'قطر',
        '广东',
        'இலங்கை',
        'இந்தியா',
        'հայ',
        '新加坡',
        'فلسطين',
        '政务',
        'xxx',
        'xyz',
        'yachts',
        'yandex',
        'ye',
        'yodobashi',
        'yoga',
        'yokohama',
        'youtube',
        'yt',
        'za',
        'zip',
        'zm',
        'zone',
        'zuerich',
        'zw',
    ];

    /**
     * Array for valid Idns
     * @see http://www.iana.org/domains/idn-tables/ Official list of supported IDN Chars
     * (.AC) Ascension Island http://www.nic.ac/pdf/AC-IDN-Policy.pdf
     * (.AR) Argentina http://www.nic.ar/faqidn.html
     * (.AS) American Samoa http://www.nic.as/idn/chars.cfm
     * (.AT) Austria http://www.nic.at/en/service/technical_information/idn/charset_converter/
     * (.BIZ) International http://www.iana.org/domains/idn-tables/
     * (.BR) Brazil http://registro.br/faq/faq6.html
     * (.BV) Bouvett Island http://www.norid.no/domeneregistrering/idn/idn_nyetegn.en.html
     * (.CAT) Catalan http://www.iana.org/domains/idn-tables/tables/cat_ca_1.0.html
     * (.CH) Switzerland https://nic.switch.ch/reg/ocView.action?res=EF6GW2JBPVTG67DLNIQXU234MN6SC33JNQQGI7L6#anhang1
     * (.CL) Chile http://www.iana.org/domains/idn-tables/tables/cl_latn_1.0.html
     * (.COM) International http://www.verisign.com/information-services/naming-services/internationalized-domain-names/index.html
     * (.DE) Germany http://www.denic.de/en/domains/idns/liste.html
     * (.DK) Danmark http://www.dk-hostmaster.dk/index.php?id=151
     * (.EE) Estonia https://www.iana.org/domains/idn-tables/tables/pl_et-pl_1.0.html
     * (.ES) Spain https://www.nic.es/media/2008-05/1210147705287.pdf
     * (.FI) Finland http://www.ficora.fi/en/index/palvelut/fiverkkotunnukset/aakkostenkaytto.html
     * (.GR) Greece https://grweb.ics.forth.gr/CharacterTable1_en.jsp
     * (.HU) Hungary http://www.domain.hu/domain/English/szabalyzat/szabalyzat.html
     * (.IL) Israel http://www.isoc.org.il/domains/il-domain-rules.html
     * (.INFO) International http://www.nic.info/info/idn
     * (.IO) British Indian Ocean Territory http://www.nic.io/IO-IDN-Policy.pdf
     * (.IR) Iran http://www.nic.ir/Allowable_Characters_dot-iran
     * (.IS) Iceland http://www.isnic.is/domain/rules.php
     * (.KR) Korea http://www.iana.org/domains/idn-tables/tables/kr_ko-kr_1.0.html
     * (.LI) Liechtenstein https://nic.switch.ch/reg/ocView.action?res=EF6GW2JBPVTG67DLNIQXU234MN6SC33JNQQGI7L6#anhang1
     * (.LT) Lithuania http://www.domreg.lt/static/doc/public/idn_symbols-en.pdf
     * (.MD) Moldova http://www.register.md/
     * (.MUSEUM) International http://www.iana.org/domains/idn-tables/tables/museum_latn_1.0.html
     * (.NET) International http://www.verisign.com/information-services/naming-services/internationalized-domain-names/index.html
     * (.NO) Norway http://www.norid.no/domeneregistrering/idn/idn_nyetegn.en.html
     * (.NU) Niue http://www.worldnames.net/
     * (.ORG) International http://www.pir.org/index.php?db=content/FAQs&tbl=FAQs_Registrant&id=2
     * (.PE) Peru https://www.nic.pe/nuevas_politicas_faq_2.php
     * (.PL) Poland http://www.dns.pl/IDN/allowed_character_sets.pdf
     * (.PR) Puerto Rico http://www.nic.pr/idn_rules.asp
     * (.PT) Portugal https://online.dns.pt/dns_2008/do?com=DS;8216320233;111;+PAGE(4000058)+K-CAT-CODIGO(C.125)+RCNT(100);
     * (.RU) Russia http://www.iana.org/domains/idn-tables/tables/ru_ru-ru_1.0.html
     * (.SA) Saudi Arabia http://www.iana.org/domains/idn-tables/tables/sa_ar_1.0.html
     * (.SE) Sweden http://www.iis.se/english/IDN_campaignsite.shtml?lang=en
     * (.SH) Saint Helena http://www.nic.sh/SH-IDN-Policy.pdf
     * (.SJ) Svalbard and Jan Mayen http://www.norid.no/domeneregistrering/idn/idn_nyetegn.en.html
     * (.TH) Thailand http://www.iana.org/domains/idn-tables/tables/th_th-th_1.0.html
     * (.TM) Turkmenistan http://www.nic.tm/TM-IDN-Policy.pdf
     * (.TR) Turkey https://www.nic.tr/index.php
     * (.UA) Ukraine http://www.iana.org/domains/idn-tables/tables/ua_cyrl_1.2.html
     * (.VE) Venice http://www.iana.org/domains/idn-tables/tables/ve_es_1.0.html
     * (.VN) Vietnam http://www.vnnic.vn/english/5-6-300-2-2-04-20071115.htm#1.%20Introduction
     *
     * @var array
     */
    protected $validIdns = [
        'AC'  => [1 => '/^[\x{002d}0-9a-zà-öø-ÿāăąćĉċčďđēėęěĝġģĥħīįĵķĺļľŀłńņňŋőœŕŗřśŝşšţťŧūŭůűųŵŷźżž]{1,63}$/iu'],
        'AR'  => [1 => '/^[\x{002d}0-9a-zà-ãç-êìíñ-õü]{1,63}$/iu'],
        'AS'  => [1 => '/^[\x{002d}0-9a-zà-öø-ÿāăąćĉċčďđēĕėęěĝğġģĥħĩīĭįıĵķĸĺļľłńņňŋōŏőœŕŗřśŝşšţťŧũūŭůűųŵŷźż]{1,63}$/iu'],
        'AT'  => [1 => '/^[\x{002d}0-9a-zà-öø-ÿœšž]{1,63}$/iu'],
        'BIZ' => 'Hostname/Biz.php',
        'BR'  => [1 => '/^[\x{002d}0-9a-zà-ãçéíó-õúü]{1,63}$/iu'],
        'BV'  => [1 => '/^[\x{002d}0-9a-zàáä-éêñ-ôöøüčđńŋšŧž]{1,63}$/iu'],
        'CAT' => [1 => '/^[\x{002d}0-9a-z·àç-éíïòóúü]{1,63}$/iu'],
        'CH'  => [1 => '/^[\x{002d}0-9a-zà-öø-ÿœ]{1,63}$/iu'],
        'CL'  => [1 => '/^[\x{002d}0-9a-záéíñóúü]{1,63}$/iu'],
        'CN'  => 'Hostname/Cn.php',
        'COM' => 'Hostname/Com.php',
        'DE'  => [1 => '/^[\x{002d}0-9a-zà-öø-ÿăąāćĉčċďđĕěėęēğĝġģĥħĭĩįīıĵķĺľļłńňņŋŏőōœĸŕřŗśŝšşťţŧŭůűũųūŵŷźžż]{1,63}$/iu'],
        'DK'  => [1 => '/^[\x{002d}0-9a-zäéöü]{1,63}$/iu'],
        'EE'  => [1 => '/^[\x{002d}0-9a-zäõöüšž]{1,63}$/iu'],
        'ES'  => [1 => '/^[\x{002d}0-9a-zàáçèéíïñòóúü·]{1,63}$/iu'],
        'EU'  => [1 => '/^[\x{002d}0-9a-zà-öø-ÿ]{1,63}$/iu',
            2 => '/^[\x{002d}0-9a-zāăąćĉċčďđēĕėęěĝğġģĥħĩīĭįıĵķĺļľŀłńņňŉŋōŏőœŕŗřśŝšťŧũūŭůűųŵŷźżž]{1,63}$/iu',
            3 => '/^[\x{002d}0-9a-zșț]{1,63}$/iu',
            4 => '/^[\x{002d}0-9a-zΐάέήίΰαβγδεζηθικλμνξοπρςστυφχψωϊϋόύώ]{1,63}$/iu',
            5 => '/^[\x{002d}0-9a-zабвгдежзийклмнопрстуфхцчшщъыьэюя]{1,63}$/iu',
            6 => '/^[\x{002d}0-9a-zἀ-ἇἐ-ἕἠ-ἧἰ-ἷὀ-ὅὐ-ὗὠ-ὧὰ-ὼώᾀ-ᾇᾐ-ᾗᾠ-ᾧᾰ-ᾴᾶᾷῂῃῄῆῇῐ-ῒΐῖῗῠ-ῧῲῳῴῶῷ]{1,63}$/iu'],
        'FI'  => [1 => '/^[\x{002d}0-9a-zäåö]{1,63}$/iu'],
        'GR'  => [1 => '/^[\x{002d}0-9a-zΆΈΉΊΌΎ-ΡΣ-ώἀ-ἕἘ-Ἕἠ-ὅὈ-Ὅὐ-ὗὙὛὝὟ-ώᾀ-ᾴᾶ-ᾼῂῃῄῆ-ῌῐ-ΐῖ-Ίῠ-Ῥῲῳῴῶ-ῼ]{1,63}$/iu'],
        'HK'  => 'Hostname/Cn.php',
        'HU'  => [1 => '/^[\x{002d}0-9a-záéíóöúüőű]{1,63}$/iu'],
        'IL'  => [1 => '/^[\x{002d}0-9\x{05D0}-\x{05EA}]{1,63}$/iu',
            2 => '/^[\x{002d}0-9a-z]{1,63}$/i'],
        'INFO'=> [1 => '/^[\x{002d}0-9a-zäåæéöøü]{1,63}$/iu',
            2 => '/^[\x{002d}0-9a-záéíóöúüőű]{1,63}$/iu',
            3 => '/^[\x{002d}0-9a-záæéíðóöúýþ]{1,63}$/iu',
            4 => '/^[\x{AC00}-\x{D7A3}]{1,17}$/iu',
            5 => '/^[\x{002d}0-9a-zāčēģīķļņōŗšūž]{1,63}$/iu',
            6 => '/^[\x{002d}0-9a-ząčėęįšūųž]{1,63}$/iu',
            7 => '/^[\x{002d}0-9a-zóąćęłńśźż]{1,63}$/iu',
            8 => '/^[\x{002d}0-9a-záéíñóúü]{1,63}$/iu'],
        'IO'  => [1 => '/^[\x{002d}0-9a-zà-öø-ÿăąāćĉčċďđĕěėęēğĝġģĥħĭĩįīıĵķĺľļłńňņŋŏőōœĸŕřŗśŝšşťţŧŭůűũųūŵŷźžż]{1,63}$/iu'],
        'IS'  => [1 => '/^[\x{002d}0-9a-záéýúíóþæöð]{1,63}$/iu'],
        'IT'  => [1 => '/^[\x{002d}0-9a-zàâäèéêëìîïòôöùûüæœçÿß-]{1,63}$/iu'],
        'JP'  => 'Hostname/Jp.php',
        'KR'  => [1 => '/^[\x{AC00}-\x{D7A3}]{1,17}$/iu'],
        'LI'  => [1 => '/^[\x{002d}0-9a-zà-öø-ÿœ]{1,63}$/iu'],
        'LT'  => [1 => '/^[\x{002d}0-9ąčęėįšųūž]{1,63}$/iu'],
        'MD'  => [1 => '/^[\x{002d}0-9ăâîşţ]{1,63}$/iu'],
        'MUSEUM' => [1 => '/^[\x{002d}0-9a-zà-öø-ÿāăąćċčďđēėęěğġģħīįıķĺļľłńņňŋōőœŕŗřśşšţťŧūůűųŵŷźżžǎǐǒǔ\x{01E5}\x{01E7}\x{01E9}\x{01EF}ə\x{0292}ẁẃẅỳ]{1,63}$/iu'],
        'NET' => 'Hostname/Com.php',
        'NO'  => [1 => '/^[\x{002d}0-9a-zàáä-éêñ-ôöøüčđńŋšŧž]{1,63}$/iu'],
        'NU'  => 'Hostname/Com.php',
        'ORG' => [1 => '/^[\x{002d}0-9a-záéíñóúü]{1,63}$/iu',
            2 => '/^[\x{002d}0-9a-zóąćęłńśźż]{1,63}$/iu',
            3 => '/^[\x{002d}0-9a-záäåæéëíðóöøúüýþ]{1,63}$/iu',
            4 => '/^[\x{002d}0-9a-záéíóöúüőű]{1,63}$/iu',
            5 => '/^[\x{002d}0-9a-ząčėęįšūųž]{1,63}$/iu',
            6 => '/^[\x{AC00}-\x{D7A3}]{1,17}$/iu',
            7 => '/^[\x{002d}0-9a-zāčēģīķļņōŗšūž]{1,63}$/iu'],
        'PE'  => [1 => '/^[\x{002d}0-9a-zñáéíóúü]{1,63}$/iu'],
        'PL'  => [1 => '/^[\x{002d}0-9a-zāčēģīķļņōŗšūž]{1,63}$/iu',
            2 => '/^[\x{002d}а-ик-ш\x{0450}ѓѕјљњќџ]{1,63}$/iu',
            3 => '/^[\x{002d}0-9a-zâîăşţ]{1,63}$/iu',
            4 => '/^[\x{002d}0-9а-яё\x{04C2}]{1,63}$/iu',
            5 => '/^[\x{002d}0-9a-zàáâèéêìíîòóôùúûċġħż]{1,63}$/iu',
            6 => '/^[\x{002d}0-9a-zàäåæéêòóôöøü]{1,63}$/iu',
            7 => '/^[\x{002d}0-9a-zóąćęłńśźż]{1,63}$/iu',
            8 => '/^[\x{002d}0-9a-zàáâãçéêíòóôõúü]{1,63}$/iu',
            9 => '/^[\x{002d}0-9a-zâîăşţ]{1,63}$/iu',
            10=> '/^[\x{002d}0-9a-záäéíóôúýčďĺľňŕšťž]{1,63}$/iu',
            11=> '/^[\x{002d}0-9a-zçë]{1,63}$/iu',
            12=> '/^[\x{002d}0-9а-ик-шђјљњћџ]{1,63}$/iu',
            13=> '/^[\x{002d}0-9a-zćčđšž]{1,63}$/iu',
            14=> '/^[\x{002d}0-9a-zâçöûüğış]{1,63}$/iu',
            15=> '/^[\x{002d}0-9a-záéíñóúü]{1,63}$/iu',
            16=> '/^[\x{002d}0-9a-zäõöüšž]{1,63}$/iu',
            17=> '/^[\x{002d}0-9a-zĉĝĥĵŝŭ]{1,63}$/iu',
            18=> '/^[\x{002d}0-9a-zâäéëîô]{1,63}$/iu',
            19=> '/^[\x{002d}0-9a-zàáâäåæçèéêëìíîïðñòôöøùúûüýćčłńřśš]{1,63}$/iu',
            20=> '/^[\x{002d}0-9a-zäåæõöøüšž]{1,63}$/iu',
            21=> '/^[\x{002d}0-9a-zàáçèéìíòóùú]{1,63}$/iu',
            22=> '/^[\x{002d}0-9a-zàáéíóöúüőű]{1,63}$/iu',
            23=> '/^[\x{002d}0-9ΐά-ώ]{1,63}$/iu',
            24=> '/^[\x{002d}0-9a-zàáâåæçèéêëðóôöøüþœ]{1,63}$/iu',
            25=> '/^[\x{002d}0-9a-záäéíóöúüýčďěňřšťůž]{1,63}$/iu',
            26=> '/^[\x{002d}0-9a-z·àçèéíïòóúü]{1,63}$/iu',
            27=> '/^[\x{002d}0-9а-ъьюя\x{0450}\x{045D}]{1,63}$/iu',
            28=> '/^[\x{002d}0-9а-яёіў]{1,63}$/iu',
            29=> '/^[\x{002d}0-9a-ząčėęįšūųž]{1,63}$/iu',
            30=> '/^[\x{002d}0-9a-záäåæéëíðóöøúüýþ]{1,63}$/iu',
            31=> '/^[\x{002d}0-9a-zàâæçèéêëîïñôùûüÿœ]{1,63}$/iu',
            32=> '/^[\x{002d}0-9а-щъыьэюяёєіїґ]{1,63}$/iu',
            33=> '/^[\x{002d}0-9א-ת]{1,63}$/iu'],
        'PR'  => [1 => '/^[\x{002d}0-9a-záéíóúñäëïüöâêîôûàèùæçœãõ]{1,63}$/iu'],
        'PT'  => [1 => '/^[\x{002d}0-9a-záàâãçéêíóôõú]{1,63}$/iu'],
        'RU'  => [1 => '/^[\x{002d}0-9а-яё]{1,63}$/iu'],
        'SA'  => [1 => '/^[\x{002d}.0-9\x{0621}-\x{063A}\x{0641}-\x{064A}\x{0660}-\x{0669}]{1,63}$/iu'],
        'SE'  => [1 => '/^[\x{002d}0-9a-zäåéöü]{1,63}$/iu'],
        'SH'  => [1 => '/^[\x{002d}0-9a-zà-öø-ÿăąāćĉčċďđĕěėęēğĝġģĥħĭĩįīıĵķĺľļłńňņŋŏőōœĸŕřŗśŝšşťţŧŭůűũųūŵŷźžż]{1,63}$/iu'],
        'SI'  => [
            1 => '/^[\x{002d}0-9a-zà-öø-ÿ]{1,63}$/iu',
            2 => '/^[\x{002d}0-9a-zāăąćĉċčďđēĕėęěĝğġģĥħĩīĭįıĵķĺļľŀłńņňŉŋōŏőœŕŗřśŝšťŧũūŭůűųŵŷźżž]{1,63}$/iu',
            3 => '/^[\x{002d}0-9a-zșț]{1,63}$/iu'],
        'SJ'  => [1 => '/^[\x{002d}0-9a-zàáä-éêñ-ôöøüčđńŋšŧž]{1,63}$/iu'],
        'TH'  => [1 => '/^[\x{002d}0-9a-z\x{0E01}-\x{0E3A}\x{0E40}-\x{0E4D}\x{0E50}-\x{0E59}]{1,63}$/iu'],
        'TM'  => [1 => '/^[\x{002d}0-9a-zà-öø-ÿāăąćĉċčďđēėęěĝġģĥħīįĵķĺļľŀłńņňŋőœŕŗřśŝşšţťŧūŭůűųŵŷźżž]{1,63}$/iu'],
        'TW'  => 'Hostname/Cn.php',
        'TR'  => [1 => '/^[\x{002d}0-9a-zğıüşöç]{1,63}$/iu'],
        'UA'  => [1 => '/^[\x{002d}0-9a-zабвгдежзийклмнопрстуфхцчшщъыьэюяѐёђѓєѕіїјљњћќѝўџґӂʼ]{1,63}$/iu'],
        'VE'  => [1 => '/^[\x{002d}0-9a-záéíóúüñ]{1,63}$/iu'],
        'VN'  => [1 => '/^[ÀÁÂÃÈÉÊÌÍÒÓÔÕÙÚÝàáâãèéêìíòóôõùúýĂăĐđĨĩŨũƠơƯư\x{1EA0}-\x{1EF9}]{1,63}$/iu'],
        'мон' => [1 => '/^[\x{002d}0-9\x{0430}-\x{044F}]{1,63}$/iu'],
        'срб' => [1 => '/^[\x{002d}0-9а-ик-шђјљњћџ]{1,63}$/iu'],
        'сайт' => [1 => '/^[\x{002d}0-9а-яёіїѝйўґг]{1,63}$/iu'],
        'онлайн' => [1 => '/^[\x{002d}0-9а-яёіїѝйўґг]{1,63}$/iu'],
        '中国' => 'Hostname/Cn.php',
        '中國' => 'Hostname/Cn.php',
        'ලංකා' => [1 => '/^[\x{0d80}-\x{0dff}]{1,63}$/iu'],
        '香港' => 'Hostname/Cn.php',
        '台湾' => 'Hostname/Cn.php',
        '台灣' => 'Hostname/Cn.php',
        'امارات'   => [1 => '/^[\x{0621}-\x{0624}\x{0626}-\x{063A}\x{0641}\x{0642}\x{0644}-\x{0648}\x{067E}\x{0686}\x{0698}\x{06A9}\x{06AF}\x{06CC}\x{06F0}-\x{06F9}]{1,30}$/iu'],
        'الاردن'    => [1 => '/^[\x{0621}-\x{0624}\x{0626}-\x{063A}\x{0641}\x{0642}\x{0644}-\x{0648}\x{067E}\x{0686}\x{0698}\x{06A9}\x{06AF}\x{06CC}\x{06F0}-\x{06F9}]{1,30}$/iu'],
        'السعودية' => [1 => '/^[\x{0621}-\x{0624}\x{0626}-\x{063A}\x{0641}\x{0642}\x{0644}-\x{0648}\x{067E}\x{0686}\x{0698}\x{06A9}\x{06AF}\x{06CC}\x{06F0}-\x{06F9}]{1,30}$/iu'],
        'ไทย' => [1 => '/^[\x{002d}0-9a-z\x{0E01}-\x{0E3A}\x{0E40}-\x{0E4D}\x{0E50}-\x{0E59}]{1,63}$/iu'],
        'рф' => [1 => '/^[\x{002d}0-9а-яё]{1,63}$/iu'],
        'تونس' => [1 => '/^[\x{0621}-\x{0624}\x{0626}-\x{063A}\x{0641}\x{0642}\x{0644}-\x{0648}\x{067E}\x{0686}\x{0698}\x{06A9}\x{06AF}\x{06CC}\x{06F0}-\x{06F9}]{1,30}$/iu'],
        'مصر' => [1 => '/^[\x{0621}-\x{0624}\x{0626}-\x{063A}\x{0641}\x{0642}\x{0644}-\x{0648}\x{067E}\x{0686}\x{0698}\x{06A9}\x{06AF}\x{06CC}\x{06F0}-\x{06F9}]{1,30}$/iu'],
        'இலங்கை' => [1 => '/^[\x{0b80}-\x{0bff}]{1,63}$/iu'],
        'فلسطين' => [1 => '/^[\x{0621}-\x{0624}\x{0626}-\x{063A}\x{0641}\x{0642}\x{0644}-\x{0648}\x{067E}\x{0686}\x{0698}\x{06A9}\x{06AF}\x{06CC}\x{06F0}-\x{06F9}]{1,30}$/iu'],
        'شبكة'  => [1 => '/^[\x{0621}-\x{0624}\x{0626}-\x{063A}\x{0641}\x{0642}\x{0644}-\x{0648}\x{067E}\x{0686}\x{0698}\x{06A9}\x{06AF}\x{06CC}\x{06F0}-\x{06F9}]{1,30}$/iu'],
    ];

    protected $idnLength = [
        'BIZ' => [5 => 17, 11 => 15, 12 => 20],
        'CN'  => [1 => 20],
        'COM' => [3 => 17, 5 => 20],
        'HK'  => [1 => 15],
        'INFO'=> [4 => 17],
        'KR'  => [1 => 17],
        'NET' => [3 => 17, 5 => 20],
        'ORG' => [6 => 17],
        'TW'  => [1 => 20],
        'امارات' => [1 => 30],
        'الاردن' => [1 => 30],
        'السعودية' => [1 => 30],
        'تونس' => [1 => 30],
        'مصر' => [1 => 30],
        'فلسطين' => [1 => 30],
        'شبكة' => [1 => 30],
        '中国' => [1 => 20],
        '中國' => [1 => 20],
        '香港' => [1 => 20],
        '台湾' => [1 => 20],
        '台灣' => [1 => 20],
    ];

    protected $tld;

    /**
     * Options for the hostname validator
     *
     * @var array
     */
    protected $options = [
        'allow'       => self::ALLOW_DNS, // Allow these hostnames
        'useIdnCheck' => true,  // Check IDN domains
        'useTldCheck' => true,  // Check TLD elements
        'ipValidator' => null,  // IP validator to use
    ];

    /**
     * Sets validator options.
     *
     * @param int  $allow       OPTIONAL Set what types of hostname to allow (default ALLOW_DNS)
     * @param bool $useIdnCheck OPTIONAL Set whether IDN domains are validated (default true)
     * @param bool $useTldCheck Set whether the TLD element of a hostname is validated (default true)
     * @param Ip   $ipValidator OPTIONAL
     * @see http://www.iana.org/cctld/specifications-policies-cctlds-01apr02.htm  Technical Specifications for ccTLDs
     */
    public function __construct($options = [])
    {
        if (!is_array($options)) {
            $options = func_get_args();
            $temp['allow'] = array_shift($options);
            if (!empty($options)) {
                $temp['useIdnCheck'] = array_shift($options);
            }

            if (!empty($options)) {
                $temp['useTldCheck'] = array_shift($options);
            }

            if (!empty($options)) {
                $temp['ipValidator'] = array_shift($options);
            }

            $options = $temp;
        }

        if (!array_key_exists('ipValidator', $options)) {
            $options['ipValidator'] = null;
        }

        parent::__construct($options);
    }

    /**
     * Returns the set ip validator
     *
     * @return Ip
     */
    public function getIpValidator()
    {
        return $this->options['ipValidator'];
    }

    /**
     *
     * @param Ip $ipValidator OPTIONAL
     * @return Hostname;
     */
    public function setIpValidator(?Ip $ipValidator = null)
    {
        if ($ipValidator === null) {
            $ipValidator = new Ip();
        }

        $this->options['ipValidator'] = $ipValidator;
        return $this;
    }

    /**
     * Returns the allow option
     *
     * @return int
     */
    public function getAllow()
    {
        return $this->options['allow'];
    }

    /**
     * Sets the allow option
     *
     * @param  int $allow
     * @return Hostname Provides a fluent interface
     */
    public function setAllow($allow)
    {
        $this->options['allow'] = $allow;
        return $this;
    }

    /**
     * Returns the set idn option
     *
     * @return bool
     */
    public function getIdnCheck()
    {
        return $this->options['useIdnCheck'];
    }

    /**
     * Set whether IDN domains are validated
     *
     * This only applies when DNS hostnames are validated
     *
     * @param  bool $useIdnCheck Set to true to validate IDN domains
     * @return Hostname
     */
    public function useIdnCheck($useIdnCheck)
    {
        $this->options['useIdnCheck'] = (bool) $useIdnCheck;
        return $this;
    }

    /**
     * Returns the set tld option
     *
     * @return bool
     */
    public function getTldCheck()
    {
        return $this->options['useTldCheck'];
    }

    /**
     * Set whether the TLD element of a hostname is validated
     *
     * This only applies when DNS hostnames are validated
     *
     * @param  bool $useTldCheck Set to true to validate TLD elements
     * @return Hostname
     */
    public function useTldCheck($useTldCheck)
    {
        $this->options['useTldCheck'] = (bool) $useTldCheck;
        return $this;
    }

    /**
     * Defined by Interface
     *
     * Returns true if and only if the $value is a valid hostname with respect to the current allow option
     *
     * @param  string $value
     * @return bool
     */
    public function isValid($value)
    {
        if (!is_string($value)) {
            $this->error(self::INVALID);
            return false;
        }

        $this->setValue($value);
        // Check input against IP address schema
        if (preg_match('/^[0-9a-f:.]*$/i', $value)
            && $this->getIpValidator()->setTranslator($this->getTranslator())->isValid($value)
        ) {
            if (!($this->getAllow() & self::ALLOW_IP)) {
                $this->error(self::IP_ADDRESS_NOT_ALLOWED);
                return false;
            }

            return true;
        }

        // Local hostnames are allowed to be partial (ending '.')
        if ($this->getAllow() & self::ALLOW_LOCAL) {
            if (substr($value, -1) === '.') {
                $value = substr($value, 0, -1);
                if (substr($value, -1) === '.') {
                    // Empty hostnames (ending '..') are not allowed
                    $this->error(self::INVALID_LOCAL_NAME);
                    return false;
                }
            }
        }

        $domainParts = explode('.', $value);

        // Prevent partial IP V4 addresses (ending '.')
        if (count($domainParts) == 4 && preg_match('/^[0-9.a-e:.]*$/i', $value)
            && $this->getIpValidator()->setTranslator($this->getTranslator())->isValid($value)
        ) {
            $this->error(self::INVALID_LOCAL_NAME);
        }

        $utf8StrWrapper = StringUtils::getWrapper('UTF-8');

        // Check input against DNS hostname schema
        if (count($domainParts) > 1
            && $utf8StrWrapper->strlen($value) >= 4
            && $utf8StrWrapper->strlen($value) <= 254
        ) {
            $status = false;

            do {
                // First check TLD
                $matches = [];
                if (preg_match('/([^.]{2,63})$/iu', end($domainParts), $matches)
                    || (array_key_exists(end($domainParts), $this->validIdns))
                ) {
                    reset($domainParts);

                    // Hostname characters are: *(label dot)(label dot label); max 254 chars
                    // label: id-prefix [*ldh{61} id-prefix]; max 63 chars
                    // id-prefix: alpha / digit
                    // ldh: alpha / digit / dash

                    // Match TLD against known list
                    $this->tld = strtoupper($matches[1]);
                    if ($this->getTldCheck()) {
                        if (!in_array(strtolower($this->tld), $this->validTlds)
                            && !in_array($this->tld, $this->validTlds)) {
                            $this->error(self::UNKNOWN_TLD);
                            $status = false;
                            break;
                        }
                        // We have already validated that the TLD is fine. We don't want it to go through the below
                        // checks as new UTF-8 TLDs will incorrectly fail if there is no IDN regex for it.
                        array_pop($domainParts);
                    }

                    /**
                     * Match against IDN hostnames
                     * Note: Keep label regex short to avoid issues with long patterns when matching IDN hostnames
                     *
                     * @see Hostname\Interface
                     */
                    $regexChars = [0 => '/^[a-z0-9\x2d]{1,63}$/i'];
                    if ($this->getIdnCheck() && isset($this->validIdns[$this->tld])) {
                        if (is_string($this->validIdns[$this->tld])) {
                            true;
                        } else {
                            $regexChars += $this->validIdns[$this->tld];
                        }
                    }

                    // Check each hostname part
                    $check = 0;
                    foreach ($domainParts as $domainPart) {
                        // Decode Punycode domain names to IDN
                        if (strpos($domainPart, 'xn--') === 0) {
                            $domainPart = $this->decodePunycode(substr($domainPart, 4));
                            if ($domainPart === false) {
                                return false;
                            }
                        }

                        // Check dash (-) does not start, end or appear in 3rd and 4th positions
                        if ($utf8StrWrapper->strpos($domainPart, '-') === 0
                            || ($utf8StrWrapper->strlen($domainPart) > 2
                                && $utf8StrWrapper->strpos($domainPart, '-', 2) == 2
                                && $utf8StrWrapper->strpos($domainPart, '-', 3) == 3
                            )
                            || ($utf8StrWrapper->strpos($domainPart, '-') === ($utf8StrWrapper->strlen($domainPart) - 1))
                        ) {
                            $this->error(self::INVALID_DASH);
                            $status = false;
                            break 2;
                        }

                        // Check each domain part
                        $checked = false;
                        foreach ($regexChars as $regexKey => $regexChar) {
                            $status = preg_match($regexChar, $domainPart);
                            if ($status > 0) {
                                $length = 63;
                                if (array_key_exists($this->tld, $this->idnLength)
                                    && array_key_exists($regexKey, $this->idnLength[$this->tld])
                                ) {
                                    $length = $this->idnLength[$this->tld];
                                }

                                if ($utf8StrWrapper->strlen($domainPart) > $length) {
                                    $this->error(self::INVALID_HOSTNAME);
                                    $status = false;
                                } else {
                                    $checked = true;
                                    break;
                                }
                            }
                        }

                        if ($checked) {
                            ++$check;
                        }
                    }

                    // If one of the labels doesn't match, the hostname is invalid
                    if ($check !== count($domainParts)) {
                        $this->error(self::INVALID_HOSTNAME_SCHEMA);
                        $status = false;
                    }
                } else {
                    // Hostname not long enough
                    $this->error(self::UNDECIPHERABLE_TLD);
                    $status = false;
                }
            } while (false);

            // If the input passes as an Internet domain name, and domain names are allowed, then the hostname
            // passes validation
            if ($status && ($this->getAllow() & self::ALLOW_DNS)) {
                return true;
            }
        } elseif ($this->getAllow() & self::ALLOW_DNS) {
            $this->error(self::INVALID_HOSTNAME);
        }

        // Check for URI Syntax (RFC3986)
        if ($this->getAllow() & self::ALLOW_URI) {
            if (preg_match("/^([a-zA-Z0-9-._~!$&\'()*+,;=]|%[[:xdigit:]]{2}){1,254}$/i", $value)) {
                return true;
            }

            $this->error(self::INVALID_URI);
        }

        // Check input against local network name schema; last chance to pass validation
        $regexLocal = '/^(([a-zA-Z0-9\x2d]{1,63}\x2e)*[a-zA-Z0-9\x2d]{1,63}[\x2e]{0,1}){1,254}$/';
        $status = preg_match($regexLocal, $value);

        // If the input passes as a local network name, and local network names are allowed, then the
        // hostname passes validation
        $allowLocal = $this->getAllow() & self::ALLOW_LOCAL;
        if ($status && $allowLocal) {
            return true;
        }

        // If the input does not pass as a local network name, add a message
        if (!$status) {
            $this->error(self::INVALID_LOCAL_NAME);
        }

        // If local network names are not allowed, add a message
        if ($status && !$allowLocal) {
            $this->error(self::LOCAL_NAME_NOT_ALLOWED);
        }

        return false;
    }

    /**
     * Decodes a punycode encoded string to it's original utf8 string
     * Returns false in case of a decoding failure.
     *
     * @param  string $encoded Punycode encoded string to decode
     * @return string|false
     */
    protected function decodePunycode($encoded)
    {
        if (!preg_match('/^[a-z0-9-]+$/i', $encoded)) {
            // no punycode encoded string
            $this->error(self::CANNOT_DECODE_PUNYCODE);
            return false;
        }

        $decoded = [];
        $separator = strrpos($encoded, '-');
        if ($separator > 0) {
            for ($x = 0; $x < $separator; ++$x) {
                // prepare decoding matrix
                $decoded[] = ord($encoded[$x]);
            }
        }

        $lengthd = count($decoded);
        $lengthe = strlen($encoded);

        // decoding
        $init  = true;
        $base  = 72;
        $index = 0;
        $char  = 0x80;

        for ($indexe = ($separator) ? ($separator + 1) : 0; $indexe < $lengthe; ++$lengthd) {
            for ($oldIndex = $index, $pos = 1, $key = 36; 1; $key += 36) {
                $hex   = ord($encoded[$indexe++]);
                $digit = ($hex - 48 < 10) ? $hex - 22
                       : (($hex - 65 < 26) ? $hex - 65
                       : (($hex - 97 < 26) ? $hex - 97
                       : 36));

                $index += $digit * $pos;
                $tag    = ($key <= $base) ? 1 : (($key >= $base + 26) ? 26 : ($key - $base));
                if ($digit < $tag) {
                    break;
                }

                $pos = (int) ($pos * (36 - $tag));
            }

            $delta   = intval($init ? (($index - $oldIndex) / 700) : (($index - $oldIndex) / 2));
            $delta  += intval($delta / ($lengthd + 1));
            for ($key = 0; $delta > 910 / 2; $key += 36) {
                $delta = intval($delta / 35);
            }

            $base   = intval($key + 36 * $delta / ($delta + 38));
            $init   = false;
            $char  += (int) ($index / ($lengthd + 1));
            $index %= ($lengthd + 1);
            if ($lengthd > 0) {
                for ($i = $lengthd; $i > $index; $i--) {
                    $decoded[$i] = $decoded[($i - 1)];
                }
            }

            $decoded[$index++] = $char;
        }

        // convert decoded ucs4 to utf8 string
        foreach ($decoded as $key => $value) {
            if ($value < 128) {
                $decoded[$key] = chr($value);
            } elseif ($value < (1 << 11)) {
                $decoded[$key]  = chr(192 + ($value >> 6));
                $decoded[$key] .= chr(128 + ($value & 63));
            } elseif ($value < (1 << 16)) {
                $decoded[$key]  = chr(224 + ($value >> 12));
                $decoded[$key] .= chr(128 + (($value >> 6) & 63));
                $decoded[$key] .= chr(128 + ($value & 63));
            } elseif ($value < (1 << 21)) {
                $decoded[$key]  = chr(240 + ($value >> 18));
                $decoded[$key] .= chr(128 + (($value >> 12) & 63));
                $decoded[$key] .= chr(128 + (($value >> 6) & 63));
                $decoded[$key] .= chr(128 + ($value & 63));
            } else {
                $this->error(self::CANNOT_DECODE_PUNYCODE);
                return false;
            }
        }

        return implode($decoded);
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Validator;

use Traversable;

class Ip extends AbstractValidator
{
    const INVALID        = 'ipInvalid';
    const NOT_IP_ADDRESS = 'notIpAddress';

    /**
     * @var array
     */
    protected $messageTemplates = [
        self::INVALID        => 'Invalid type given. String expected',
        self::NOT_IP_ADDRESS => "The input does not appear to be a valid IP address",
    ];

    /**
     * Internal options
     *
     * @var array
     */
    protected $options = [
        'allowipv4'      => true, // Enable IPv4 Validation
        'allowipv6'      => true, // Enable IPv6 Validation
        'allowipvfuture' => false, // Enable IPvFuture Validation
        'allowliteral'   => true, // Enable IPs in literal format (only IPv6 and IPvFuture)
    ];

    /**
     * Sets the options for this validator
     *
     * @param array|Traversable $options
     * @throws Exception\InvalidArgumentException If there is any kind of IP allowed or $options is not an array or Traversable.
     * @return AbstractValidator
     */
    public function setOptions($options = [])
    {
        parent::setOptions($options);

        if (!$this->options['allowipv4'] && !$this->options['allowipv6'] && !$this->options['allowipvfuture']) {
            throw new Exception\InvalidArgumentException('Nothing to validate. Check your options');
        }

        return $this;
    }

    /**
     * Returns true if and only if $value is a valid IP address
     *
     * @param  mixed $value
     * @return bool
     */
    public function isValid($value)
    {
        if (!is_string($value)) {
            $this->error(self::INVALID);
            return false;
        }

        $this->setValue($value);

        if ($this->options['allowipv4'] && $this->validateIPv4($value)) {
            return true;
        } else {
            if ((bool) $this->options['allowliteral']) {
                static $regex = '/^\[(.*)\]$/';
                if ((bool) preg_match($regex, $value, $matches)) {
                    $value = $matches[1];
                }
            }

            if (($this->options['allowipv6'] && $this->validateIPv6($value)) ||
                ($this->options['allowipvfuture'] && $this->validateIPvFuture($value))
            ) {
                return true;
            }
        }
        $this->error(self::NOT_IP_ADDRESS);
        return false;
    }

    /**
     * Validates an IPv4 address
     *
     * @param string $value
     * @return bool
     */
    protected function validateIPv4($value)
    {
        if (preg_match('/^([01]{8}.){3}[01]{8}\z/i', $value)) {
            // binary format  00000000.00000000.00000000.00000000
            $value = bindec(substr($value, 0, 8)) . '.' . bindec(substr($value, 9, 8)) . '.'
                   . bindec(substr($value, 18, 8)) . '.' . bindec(substr($value, 27, 8));
        } elseif (preg_match('/^([0-9]{3}.){3}[0-9]{3}\z/i', $value)) {
            // octet format 777.777.777.777
            $value = (int) substr($value, 0, 3) . '.' . (int) substr($value, 4, 3) . '.'
                   . (int) substr($value, 8, 3) . '.' . (int) substr($value, 12, 3);
        } elseif (preg_match('/^([0-9a-f]{2}.){3}[0-9a-f]{2}\z/i', $value)) {
            // hex format ff.ff.ff.ff
            $value = hexdec(substr($value, 0, 2)) . '.' . hexdec(substr($value, 3, 2)) . '.'
                   . hexdec(substr($value, 6, 2)) . '.' . hexdec(substr($value, 9, 2));
        }

        $ip2long = ip2long($value);
        if ($ip2long === false) {
            return false;
        }

        return ($value == long2ip($ip2long));
    }

    /**
     * Validates an IPv6 address
     *
     * @param  string $value Value to check against
     * @return bool True when $value is a valid ipv6 address
     *                 False otherwise
     */
    protected function validateIPv6($value)
    {
        if (strlen($value) < 3) {
            return $value == '::';
        }

        if (strpos($value, '.')) {
            $lastcolon = strrpos($value, ':');
            if (!($lastcolon && $this->validateIPv4(substr($value, $lastcolon + 1)))) {
                return false;
            }

            $value = substr($value, 0, $lastcolon) . ':0:0';
        }

        if (strpos($value, '::') === false) {
            return preg_match('/\A(?:[a-f0-9]{1,4}:){7}[a-f0-9]{1,4}\z/i', $value);
        }

        $colonCount = substr_count($value, ':');
        if ($colonCount < 8) {
            return preg_match('/\A(?::|(?:[a-f0-9]{1,4}:)+):(?:(?:[a-f0-9]{1,4}:)*[a-f0-9]{1,4})?\z/i', $value);
        }

        // special case with ending or starting double colon
        if ($colonCount == 8) {
            return preg_match('/\A(?:::)?(?:[a-f0-9]{1,4}:){6}[a-f0-9]{1,4}(?:::)?\z/i', $value);
        }

        return false;
    }

    /**
     * Validates an IPvFuture address.
     *
     * IPvFuture is loosely defined in the Section 3.2.2 of RFC 3986
     *
     * @param  string $value Value to check against
     * @return bool True when $value is a valid IPvFuture address
     *                 False otherwise
     */
    protected function validateIPvFuture($value)
    {
        /*
         * ABNF:
         * IPvFuture  = "v" 1*HEXDIG "." 1*( unreserved / sub-delims / ":" )
         * unreserved    = ALPHA / DIGIT / "-" / "." / "_" / "~"
         * sub-delims    = "!" / "$" / "&" / "'" / "(" / ")" / "*" / "+" / ","
         *               / ";" / "="
         */
        static $regex = '/^v([[:xdigit:]]+)\.[[:alnum:]\-\._~!\$&\'\(\)\*\+,;=:]+$/';

        $result = (bool) preg_match($regex, $value, $matches);

        /*
         * "As such, implementations must not provide the version flag for the
         *  existing IPv4 and IPv6 literal address forms described below."
         */
        return ($result && $matches[1] != 4 && $matches[1] != 6);
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Validator\Hostname;

/**
 * Resource file for com and net idn validation
 */
return [
    1  => '/^[\x{002d}0-9\x{0400}-\x{052f}]{1,63}$/iu',
    2  => '/^[\x{002d}0-9\x{0370}-\x{03ff}]{1,63}$/iu',
    3  => '/^[\x{002d}0-9a-z\x{ac00}-\x{d7a3}]{1,17}$/iu',
    4  => '/^[\x{002d}0-9a-z·à-öø-ÿāăąćĉċčďđēĕėęěĝğġģĥħĩīĭįıĵķĸĺļľłńņňŋōŏőœŕŗřśŝşšţťŧũūŭůűųŵŷźżž]{1,63}$/iu',
    5  => '/^[\x{002d}0-9A-Za-z\x{3400}-\x{3401}\x{3404}-\x{3406}\x{340C}\x{3416}\x{341C}' .
'\x{3421}\x{3424}\x{3428}-\x{3429}\x{342B}-\x{342E}\x{3430}-\x{3434}\x{3436}' .
'\x{3438}-\x{343C}\x{343E}\x{3441}-\x{3445}\x{3447}\x{3449}-\x{3451}\x{3453}' .
'\x{3457}-\x{345F}\x{3463}-\x{3467}\x{346E}-\x{3471}\x{3473}-\x{3477}\x{3479}-\x{348E}\x{3491}-\x{3497}' .
'\x{3499}-\x{34A1}\x{34A4}-\x{34AD}\x{34AF}-\x{34B0}\x{34B2}-\x{34BF}\x{34C2}-\x{34C5}\x{34C7}-\x{34CC}' .
'\x{34CE}-\x{34D1}\x{34D3}-\x{34D8}\x{34DA}-\x{34E4}\x{34E7}-\x{34E9}\x{34EC}-\x{34EF}\x{34F1}-\x{34FE}' .
'\x{3500}-\x{3507}\x{350A}-\x{3513}\x{3515}\x{3517}-\x{351A}\x{351C}-\x{351E}\x{3520}-\x{352A}' .
'\x{352C}-\x{3552}\x{3554}-\x{355C}\x{355E}-\x{3567}\x{3569}-\x{3573}\x{3575}-\x{357C}\x{3580}-\x{3588}' .
'\x{358F}-\x{3598}\x{359E}-\x{35AB}\x{35B4}-\x{35CD}\x{35D0}\x{35D3}-\x{35DC}\x{35E2}-\x{35ED}' .
'\x{35F0}-\x{35F6}\x{35FB}-\x{3602}\x{3605}-\x{360E}\x{3610}-\x{3611}\x{3613}-\x{3616}\x{3619}-\x{362D}' .
'\x{362F}-\x{3634}\x{3636}-\x{363B}\x{363F}-\x{3645}\x{3647}-\x{364B}\x{364D}-\x{3653}\x{3655}' .
'\x{3659}-\x{365E}\x{3660}-\x{3665}\x{3667}-\x{367C}\x{367E}\x{3680}-\x{3685}\x{3687}' .
'\x{3689}-\x{3690}\x{3692}-\x{3698}\x{369A}\x{369C}-\x{36AE}\x{36B0}-\x{36BF}\x{36C1}-\x{36C5}' .
'\x{36C9}-\x{36CA}\x{36CD}-\x{36DE}\x{36E1}-\x{36E2}\x{36E5}-\x{36FE}\x{3701}-\x{3713}\x{3715}-\x{371E}' .
'\x{3720}-\x{372C}\x{372E}-\x{3745}\x{3747}-\x{3748}\x{374A}\x{374C}-\x{3759}\x{375B}-\x{3760}' .
'\x{3762}-\x{3767}\x{3769}-\x{3772}\x{3774}-\x{378C}\x{378F}-\x{379C}\x{379F}\x{37A1}-\x{37AD}' .
'\x{37AF}-\x{37B7}\x{37B9}-\x{37C1}\x{37C3}-\x{37C5}\x{37C7}-\x{37D4}\x{37D6}-\x{37E0}\x{37E2}' .
'\x{37E5}-\x{37ED}\x{37EF}-\x{37F6}\x{37F8}-\x{3802}\x{3804}-\x{381D}\x{3820}-\x{3822}\x{3825}-\x{382A}' .
'\x{382D}-\x{382F}\x{3831}-\x{3832}\x{3834}-\x{384C}\x{384E}-\x{3860}\x{3862}-\x{3863}\x{3865}-\x{386B}' .
'\x{386D}-\x{3886}\x{3888}-\x{38A1}\x{38A3}\x{38A5}-\x{38AA}\x{38AC}\x{38AE}-\x{38B0}' .
'\x{38B2}-\x{38B6}\x{38B8}\x{38BA}-\x{38BE}\x{38C0}-\x{38C9}\x{38CB}-\x{38D4}\x{38D8}-\x{38E0}' .
'\x{38E2}-\x{38E6}\x{38EB}-\x{38ED}\x{38EF}-\x{38F2}\x{38F5}-\x{38F7}\x{38FA}-\x{38FF}\x{3901}-\x{392A}' .
'\x{392C}\x{392E}-\x{393B}\x{393E}-\x{3956}\x{395A}-\x{3969}\x{396B}-\x{397A}\x{397C}-\x{3987}' .
'\x{3989}-\x{3998}\x{399A}-\x{39B0}\x{39B2}\x{39B4}-\x{39D0}\x{39D2}-\x{39DA}\x{39DE}-\x{39DF}' .
'\x{39E1}-\x{39EF}\x{39F1}-\x{3A17}\x{3A19}-\x{3A2A}\x{3A2D}-\x{3A40}\x{3A43}-\x{3A4E}\x{3A50}' .
'\x{3A52}-\x{3A5E}\x{3A60}-\x{3A6D}\x{3A6F}-\x{3A77}\x{3A79}-\x{3A82}\x{3A84}-\x{3A85}\x{3A87}-\x{3A89}' .
'\x{3A8B}-\x{3A8F}\x{3A91}-\x{3A93}\x{3A95}-\x{3A96}\x{3A9A}\x{3A9C}-\x{3AA6}\x{3AA8}-\x{3AA9}' .
'\x{3AAB}-\x{3AB1}\x{3AB4}-\x{3ABC}\x{3ABE}-\x{3AC5}\x{3ACA}-\x{3ACB}\x{3ACD}-\x{3AD5}\x{3AD7}-\x{3AE1}' .
'\x{3AE4}-\x{3AE7}\x{3AE9}-\x{3AEC}\x{3AEE}-\x{3AFD}\x{3B01}-\x{3B10}\x{3B12}-\x{3B15}\x{3B17}-\x{3B1E}' .
'\x{3B20}-\x{3B23}\x{3B25}-\x{3B27}\x{3B29}-\x{3B36}\x{3B38}-\x{3B39}\x{3B3B}-\x{3B3C}\x{3B3F}' .
'\x{3B41}-\x{3B44}\x{3B47}-\x{3B4C}\x{3B4E}\x{3B51}-\x{3B55}\x{3B58}-\x{3B62}\x{3B68}-\x{3B72}' .
'\x{3B78}-\x{3B88}\x{3B8B}-\x{3B9F}\x{3BA1}\x{3BA3}-\x{3BBA}\x{3BBC}\x{3BBF}-\x{3BD0}' .
'\x{3BD3}-\x{3BE6}\x{3BEA}-\x{3BFB}\x{3BFE}-\x{3C12}\x{3C14}-\x{3C1B}\x{3C1D}-\x{3C37}\x{3C39}-\x{3C4F}' .
'\x{3C52}\x{3C54}-\x{3C5C}\x{3C5E}-\x{3C68}\x{3C6A}-\x{3C76}\x{3C78}-\x{3C8F}\x{3C91}-\x{3CA8}' .
'\x{3CAA}-\x{3CAD}\x{3CAF}-\x{3CBE}\x{3CC0}-\x{3CC8}\x{3CCA}-\x{3CD3}\x{3CD6}-\x{3CE0}\x{3CE4}-\x{3CEE}' .
'\x{3CF3}-\x{3D0A}\x{3D0E}-\x{3D1E}\x{3D20}-\x{3D21}\x{3D25}-\x{3D38}\x{3D3B}-\x{3D46}\x{3D4A}-\x{3D59}' .
'\x{3D5D}-\x{3D7B}\x{3D7D}-\x{3D81}\x{3D84}-\x{3D88}\x{3D8C}-\x{3D8F}\x{3D91}-\x{3D98}\x{3D9A}-\x{3D9C}' .
'\x{3D9E}-\x{3DA1}\x{3DA3}-\x{3DB0}\x{3DB2}-\x{3DB5}\x{3DB9}-\x{3DBC}\x{3DBE}-\x{3DCB}\x{3DCD}-\x{3DDB}' .
'\x{3DDF}-\x{3DE8}\x{3DEB}-\x{3DF0}\x{3DF3}-\x{3DF9}\x{3DFB}-\x{3DFC}\x{3DFE}-\x{3E05}\x{3E08}-\x{3E33}' .
'\x{3E35}-\x{3E3E}\x{3E40}-\x{3E47}\x{3E49}-\x{3E67}\x{3E6B}-\x{3E6F}\x{3E71}-\x{3E85}\x{3E87}-\x{3E8C}' .
'\x{3E8E}-\x{3E98}\x{3E9A}-\x{3EA1}\x{3EA3}-\x{3EAE}\x{3EB0}-\x{3EB5}\x{3EB7}-\x{3EBA}\x{3EBD}' .
'\x{3EBF}-\x{3EC4}\x{3EC7}-\x{3ECE}\x{3ED1}-\x{3ED7}\x{3ED9}-\x{3EDA}\x{3EDD}-\x{3EE3}\x{3EE7}-\x{3EE8}' .
'\x{3EEB}-\x{3EF2}\x{3EF5}-\x{3EFF}\x{3F01}-\x{3F02}\x{3F04}-\x{3F07}\x{3F09}-\x{3F44}\x{3F46}-\x{3F4E}' .
'\x{3F50}-\x{3F53}\x{3F55}-\x{3F72}\x{3F74}-\x{3F75}\x{3F77}-\x{3F7B}\x{3F7D}-\x{3FB0}\x{3FB6}-\x{3FBF}' .
'\x{3FC1}-\x{3FCF}\x{3FD1}-\x{3FD3}\x{3FD5}-\x{3FDF}\x{3FE1}-\x{400B}\x{400D}-\x{401C}\x{401E}-\x{4024}' .
'\x{4027}-\x{403F}\x{4041}-\x{4060}\x{4062}-\x{4069}\x{406B}-\x{408A}\x{408C}-\x{40A7}\x{40A9}-\x{40B4}' .
'\x{40B6}-\x{40C2}\x{40C7}-\x{40CF}\x{40D1}-\x{40DE}\x{40E0}-\x{40E7}\x{40E9}-\x{40EE}\x{40F0}-\x{40FB}' .
'\x{40FD}-\x{4109}\x{410B}-\x{4115}\x{4118}-\x{411D}\x{411F}-\x{4122}\x{4124}-\x{4133}\x{4136}-\x{4138}' .
'\x{413A}-\x{4148}\x{414A}-\x{4169}\x{416C}-\x{4185}\x{4188}-\x{418B}\x{418D}-\x{41AD}\x{41AF}-\x{41B3}' .
'\x{41B5}-\x{41C3}\x{41C5}-\x{41C9}\x{41CB}-\x{41F2}\x{41F5}-\x{41FE}\x{4200}-\x{4227}\x{422A}-\x{4246}' .
'\x{4248}-\x{4263}\x{4265}-\x{428B}\x{428D}-\x{42A1}\x{42A3}-\x{42C4}\x{42C8}-\x{42DC}\x{42DE}-\x{430A}' .
'\x{430C}-\x{4335}\x{4337}\x{4342}-\x{435F}\x{4361}-\x{439A}\x{439C}-\x{439D}\x{439F}-\x{43A4}' .
'\x{43A6}-\x{43EC}\x{43EF}-\x{4405}\x{4407}-\x{4429}\x{442B}-\x{4455}\x{4457}-\x{4468}\x{446A}-\x{446D}' .
'\x{446F}-\x{4476}\x{4479}-\x{447D}\x{447F}-\x{4486}\x{4488}-\x{4490}\x{4492}-\x{4498}\x{449A}-\x{44AD}' .
'\x{44B0}-\x{44BD}\x{44C1}-\x{44D3}\x{44D6}-\x{44E7}\x{44EA}\x{44EC}-\x{44FA}\x{44FC}-\x{4541}' .
'\x{4543}-\x{454F}\x{4551}-\x{4562}\x{4564}-\x{4575}\x{4577}-\x{45AB}\x{45AD}-\x{45BD}\x{45BF}-\x{45D5}' .
'\x{45D7}-\x{45EC}\x{45EE}-\x{45F2}\x{45F4}-\x{45FA}\x{45FC}-\x{461A}\x{461C}-\x{461D}\x{461F}-\x{4631}' .
'\x{4633}-\x{4649}\x{464C}\x{464E}-\x{4652}\x{4654}-\x{466A}\x{466C}-\x{4675}\x{4677}-\x{467A}' .
'\x{467C}-\x{4694}\x{4696}-\x{46A3}\x{46A5}-\x{46AB}\x{46AD}-\x{46D2}\x{46D4}-\x{4723}\x{4729}-\x{4732}' .
'\x{4734}-\x{4758}\x{475A}\x{475C}-\x{478B}\x{478D}\x{4791}-\x{47B1}\x{47B3}-\x{47F1}' .
'\x{47F3}-\x{480B}\x{480D}-\x{4815}\x{4817}-\x{4839}\x{483B}-\x{4870}\x{4872}-\x{487A}\x{487C}-\x{487F}' .
'\x{4883}-\x{488E}\x{4890}-\x{4896}\x{4899}-\x{48A2}\x{48A4}-\x{48B9}\x{48BB}-\x{48C8}\x{48CA}-\x{48D1}' .
'\x{48D3}-\x{48E5}\x{48E7}-\x{48F2}\x{48F4}-\x{48FF}\x{4901}-\x{4922}\x{4924}-\x{4928}\x{492A}-\x{4931}' .
'\x{4933}-\x{495B}\x{495D}-\x{4978}\x{497A}\x{497D}\x{4982}-\x{4983}\x{4985}-\x{49A8}' .
'\x{49AA}-\x{49AF}\x{49B1}-\x{49B7}\x{49B9}-\x{49BD}\x{49C1}-\x{49C7}\x{49C9}-\x{49CE}\x{49D0}-\x{49E8}' .
'\x{49EA}\x{49EC}\x{49EE}-\x{4A19}\x{4A1B}-\x{4A43}\x{4A45}-\x{4A4D}\x{4A4F}-\x{4A9E}' .
'\x{4AA0}-\x{4AA9}\x{4AAB}-\x{4B4E}\x{4B50}-\x{4B5B}\x{4B5D}-\x{4B69}\x{4B6B}-\x{4BC2}\x{4BC6}-\x{4BE8}' .
'\x{4BEA}-\x{4BFA}\x{4BFC}-\x{4C06}\x{4C08}-\x{4C2D}\x{4C2F}-\x{4C32}\x{4C34}-\x{4C35}\x{4C37}-\x{4C69}' .
'\x{4C6B}-\x{4C73}\x{4C75}-\x{4C86}\x{4C88}-\x{4C97}\x{4C99}-\x{4C9C}\x{4C9F}-\x{4CA3}\x{4CA5}-\x{4CB5}' .
'\x{4CB7}-\x{4CF8}\x{4CFA}-\x{4D27}\x{4D29}-\x{4DAC}\x{4DAE}-\x{4DB1}\x{4DB3}-\x{4DB5}\x{4E00}-\x{4E54}' .
'\x{4E56}-\x{4E89}\x{4E8B}-\x{4EEC}\x{4EEE}-\x{4FAC}\x{4FAE}-\x{503C}\x{503E}-\x{51E5}\x{51E7}-\x{5270}' .
'\x{5272}-\x{56A1}\x{56A3}-\x{5840}\x{5842}-\x{58B5}\x{58B7}-\x{58CB}\x{58CD}-\x{5BC8}\x{5BCA}-\x{5C01}' .
'\x{5C03}-\x{5C25}\x{5C27}-\x{5D5B}\x{5D5D}-\x{5F08}\x{5F0A}-\x{61F3}\x{61F5}-\x{63BA}\x{63BC}-\x{6441}' .
'\x{6443}-\x{657C}\x{657E}-\x{663E}\x{6640}-\x{66FC}\x{66FE}-\x{6728}\x{672A}-\x{6766}\x{6768}-\x{67A8}' .
'\x{67AA}-\x{685B}\x{685D}-\x{685E}\x{6860}-\x{68B9}\x{68BB}-\x{6AC8}\x{6ACA}-\x{6BB0}\x{6BB2}-\x{6C16}' .
'\x{6C18}-\x{6D9B}\x{6D9D}-\x{6E12}\x{6E14}-\x{6E8B}\x{6E8D}-\x{704D}\x{704F}-\x{7113}\x{7115}-\x{713B}' .
'\x{713D}-\x{7154}\x{7156}-\x{729F}\x{72A1}-\x{731E}\x{7320}-\x{7362}\x{7364}-\x{7533}\x{7535}-\x{7551}' .
'\x{7553}-\x{7572}\x{7574}-\x{75E8}\x{75EA}-\x{7679}\x{767B}-\x{783E}\x{7840}-\x{7A62}\x{7A64}-\x{7AC2}' .
'\x{7AC4}-\x{7B06}\x{7B08}-\x{7B79}\x{7B7B}-\x{7BCE}\x{7BD0}-\x{7D99}\x{7D9B}-\x{7E49}\x{7E4C}-\x{8132}' .
'\x{8134}\x{8136}-\x{81D2}\x{81D4}-\x{8216}\x{8218}-\x{822D}\x{822F}-\x{83B4}\x{83B6}-\x{841F}' .
'\x{8421}-\x{86CC}\x{86CE}-\x{874A}\x{874C}-\x{877E}\x{8780}-\x{8A32}\x{8A34}-\x{8B71}\x{8B73}-\x{8B8E}' .
'\x{8B90}-\x{8DE4}\x{8DE6}-\x{8E9A}\x{8E9C}-\x{8EE1}\x{8EE4}-\x{8F0B}\x{8F0D}-\x{8FB9}\x{8FBB}-\x{9038}' .
'\x{903A}-\x{9196}\x{9198}-\x{91A3}\x{91A5}-\x{91B7}\x{91B9}-\x{91C7}\x{91C9}-\x{91E0}\x{91E2}-\x{91FB}' .
'\x{91FD}-\x{922B}\x{922D}-\x{9270}\x{9272}-\x{9420}\x{9422}-\x{9664}\x{9666}-\x{9679}\x{967B}-\x{9770}' .
'\x{9772}-\x{982B}\x{982D}-\x{98ED}\x{98EF}-\x{99C4}\x{99C6}-\x{9A11}\x{9A14}-\x{9A27}\x{9A29}-\x{9D0D}' .
'\x{9D0F}-\x{9D2B}\x{9D2D}-\x{9D8E}\x{9D90}-\x{9DC5}\x{9DC7}-\x{9E77}\x{9E79}-\x{9EB8}\x{9EBB}-\x{9F20}' .
'\x{9F22}-\x{9F61}\x{9F63}-\x{9FA5}\x{FA28}]{1,20}$/iu',
    6 => '/^[\x{002d}0-9A-Za-z]{1,63}$/iu',
    7 => '/^[\x{00A1}-\x{00FF}]{1,63}$/iu',
    8 => '/^[\x{0100}-\x{017f}]{1,63}$/iu',
    9 => '/^[\x{0180}-\x{024f}]{1,63}$/iu',
    10 => '/^[\x{0250}-\x{02af}]{1,63}$/iu',
    11 => '/^[\x{02b0}-\x{02ff}]{1,63}$/iu',
    12 => '/^[\x{0300}-\x{036f}]{1,63}$/iu',
    13 => '/^[\x{0370}-\x{03ff}]{1,63}$/iu',
    14 => '/^[\x{0400}-\x{04ff}]{1,63}$/iu',
    15 => '/^[\x{0500}-\x{052f}]{1,63}$/iu',
    16 => '/^[\x{0530}-\x{058F}]{1,63}$/iu',
    17 => '/^[\x{0590}-\x{05FF}]{1,63}$/iu',
    18 => '/^[\x{0600}-\x{06FF}]{1,63}$/iu',
    19 => '/^[\x{0700}-\x{074F}]{1,63}$/iu',
    20 => '/^[\x{0780}-\x{07BF}]{1,63}$/iu',
    21 => '/^[\x{0900}-\x{097F}]{1,63}$/iu',
    22 => '/^[\x{0980}-\x{09FF}]{1,63}$/iu',
    23 => '/^[\x{0A00}-\x{0A7F}]{1,63}$/iu',
    24 => '/^[\x{0A80}-\x{0AFF}]{1,63}$/iu',
    25 => '/^[\x{0B00}-\x{0B7F}]{1,63}$/iu',
    26 => '/^[\x{0B80}-\x{0BFF}]{1,63}$/iu',
    27 => '/^[\x{0C00}-\x{0C7F}]{1,63}$/iu',
    28 => '/^[\x{0C80}-\x{0CFF}]{1,63}$/iu',
    29 => '/^[\x{0D00}-\x{0D7F}]{1,63}$/iu',
    30 => '/^[\x{0D80}-\x{0DFF}]{1,63}$/iu',
    31 => '/^[\x{0E00}-\x{0E7F}]{1,63}$/iu',
    32 => '/^[\x{0E80}-\x{0EFF}]{1,63}$/iu',
    33 => '/^[\x{0F00}-\x{0FFF}]{1,63}$/iu',
    34 => '/^[\x{1000}-\x{109F}]{1,63}$/iu',
    35 => '/^[\x{10A0}-\x{10FF}]{1,63}$/iu',
    36 => '/^[\x{1100}-\x{11FF}]{1,63}$/iu',
    37 => '/^[\x{1200}-\x{137F}]{1,63}$/iu',
    38 => '/^[\x{13A0}-\x{13FF}]{1,63}$/iu',
    39 => '/^[\x{1400}-\x{167F}]{1,63}$/iu',
    40 => '/^[\x{1680}-\x{169F}]{1,63}$/iu',
    41 => '/^[\x{16A0}-\x{16FF}]{1,63}$/iu',
    42 => '/^[\x{1700}-\x{171F}]{1,63}$/iu',
    43 => '/^[\x{1720}-\x{173F}]{1,63}$/iu',
    44 => '/^[\x{1740}-\x{175F}]{1,63}$/iu',
    45 => '/^[\x{1760}-\x{177F}]{1,63}$/iu',
    46 => '/^[\x{1780}-\x{17FF}]{1,63}$/iu',
    47 => '/^[\x{1800}-\x{18AF}]{1,63}$/iu',
    48 => '/^[\x{1E00}-\x{1EFF}]{1,63}$/iu',
    49 => '/^[\x{1F00}-\x{1FFF}]{1,63}$/iu',
    50 => '/^[\x{2070}-\x{209F}]{1,63}$/iu',
    51 => '/^[\x{2100}-\x{214F}]{1,63}$/iu',
    52 => '/^[\x{2150}-\x{218F}]{1,63}$/iu',
    53 => '/^[\x{2460}-\x{24FF}]{1,63}$/iu',
    54 => '/^[\x{2E80}-\x{2EFF}]{1,63}$/iu',
    55 => '/^[\x{2F00}-\x{2FDF}]{1,63}$/iu',
    56 => '/^[\x{2FF0}-\x{2FFF}]{1,63}$/iu',
    57 => '/^[\x{3040}-\x{309F}]{1,63}$/iu',
    58 => '/^[\x{30A0}-\x{30FF}]{1,63}$/iu',
    59 => '/^[\x{3100}-\x{312F}]{1,63}$/iu',
    60 => '/^[\x{3130}-\x{318F}]{1,63}$/iu',
    61 => '/^[\x{3190}-\x{319F}]{1,63}$/iu',
    62 => '/^[\x{31A0}-\x{31BF}]{1,63}$/iu',
    63 => '/^[\x{31F0}-\x{31FF}]{1,63}$/iu',
    64 => '/^[\x{3200}-\x{32FF}]{1,63}$/iu',
    65 => '/^[\x{3300}-\x{33FF}]{1,63}$/iu',
    66 => '/^[\x{3400}-\x{4DBF}]{1,63}$/iu',
    67 => '/^[\x{4E00}-\x{9FFF}]{1,63}$/iu',
    68 => '/^[\x{A000}-\x{A48F}]{1,63}$/iu',
    69 => '/^[\x{A490}-\x{A4CF}]{1,63}$/iu',
    70 => '/^[\x{AC00}-\x{D7AF}]{1,63}$/iu',
    73 => '/^[\x{F900}-\x{FAFF}]{1,63}$/iu',
    74 => '/^[\x{FB00}-\x{FB4F}]{1,63}$/iu',
    75 => '/^[\x{FB50}-\x{FDFF}]{1,63}$/iu',
    76 => '/^[\x{FE20}-\x{FE2F}]{1,63}$/iu',
    77 => '/^[\x{FE70}-\x{FEFF}]{1,63}$/iu',
    78 => '/^[\x{FF00}-\x{FFEF}]{1,63}$/iu',
    79 => '/^[\x{20000}-\x{2A6DF}]{1,63}$/iu',
    80 => '/^[\x{2F800}-\x{2FA1F}]{1,63}$/iu',
];

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Stdlib\StringWrapper;

use Zend\Stdlib\Exception;

class Intl extends AbstractStringWrapper
{
    /**
     * List of supported character sets (upper case)
     *
     * @var string[]
     */
    protected static $encodings = ['UTF-8'];

    /**
     * Get a list of supported character encodings
     *
     * @return string[]
     */
    public static function getSupportedEncodings()
    {
        return static::$encodings;
    }

    /**
     * Constructor
     *
     * @throws Exception\ExtensionNotLoadedException
     */
    public function __construct()
    {
        if (!extension_loaded('intl')) {
            throw new Exception\ExtensionNotLoadedException(
                'PHP extension "intl" is required for this wrapper'
            );
        }
    }

    /**
     * Returns the length of the given string
     *
     * @param string $str
     * @return int|false
     */
    public function strlen($str)
    {
        return grapheme_strlen($str);
    }

    /**
     * Returns the portion of string specified by the start and length parameters
     *
     * @param string   $str
     * @param int      $offset
     * @param int|null $length
     * @return string|false
     */
    public function substr($str, $offset = 0, $length = null)
    {
        // Due fix of PHP #62759 The third argument returns an empty string if is 0 or null.
        if ($length !== null) {
            return grapheme_substr($str, $offset, $length);
        }

        return grapheme_substr($str, $offset);
    }

    /**
     * Find the position of the first occurrence of a substring in a string
     *
     * @param string $haystack
     * @param string $needle
     * @param int    $offset
     * @return int|false
     */
    public function strpos($haystack, $needle, $offset = 0)
    {
        return grapheme_strpos($haystack, $needle, $offset);
    }
}

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Stdlib\Exception;

/**
 * Exception marker interface
 */
interface ExceptionInterface
{
}


/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Stdlib\Exception;

/**
 * Bad method call exception
 */
class BadMethodCallException extends \BadMethodCallException implements ExceptionInterface
{
}
