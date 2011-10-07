<?php

/**
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package    Fuel
 * @version    1.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2011 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Hybrid;

use \Fuel\Core\Inflector,
	\Fuel\Core\Fuel_Exception;

/**
 * Hybrid 
 * 
 * A set of class that extends the functionality of FuelPHP without 
 * affecting the standard workflow when the application doesn't actually 
 * utilize Hybrid feature.
 * 
 * @package     Fuel
 * @subpackage  Hybrid
 * @category    Config
 * @author      Ignacio "kavinsky" Muñoz Fernandez <nmunozfernandez@gmail.com>
 */
class Config extends \Fuel\Core\Config
{
	/**
	 * List of all available driver classes
	 * 
	 * @var array
	 */
	static protected $drivers = array(
			'php' => 'Config_Php',
			'yml' => 'Config_Yml',
			'xml' => 'Config_Xml',
			'ini' => 'Config_Ini',
			'db'  => 'Config_Db',
			'redis' => 'Config_Redis',
			'mongo' => 'Config_Mongo',
	);
	
	
	public static function _init()
	{
		$available_drivers = array();
		foreach(static::$drivers as $name => $driver)
		{
			$available_drivers[$name] = new $driver();
		}
		
		static::$drivers = $available_drivers;
	}
	
	
	public static function load($file, $group = null, $reload = false, $overwrite = false)
	{
		if ( ! is_array($file) && array_key_exists($file, static::$loaded_files) and ! $reload)
		{
			return false;
		}
		
		// check if is a direct include file
		if (is_array($file))
		{
			$config = $file;
		}
		elseif(is_string($file) and in_array(strtolower(substr($file, 0, 3)), array_keys(static::$drivers)))
		{
			$ext = strtolower(substr($file, 0, 3));
			$file = str_replace($ext.'::');
			
			if($paths = \Fuel::find_file('config', $file, '.'.$ext, true))
			{
				// Reverse the file list so that we load the core configs first and
				// the app can override anything.
				$paths = array_reverse($paths);
				foreach ($paths as $path)
				{
					$config = $overwrite ? array_merge($config, static::$drivers[$ext]->load($file)) : \Arr::merge($config, static::$drivers[$ext]->load($file));
				}
			}
		}
		else 
		{
			$paths = array();
			foreach(static::$drivers as $ext => $driver)
			{
				$paths = array_merge($paths, array_reverse(\Fuel::find_file('config', $file, '.'.$ext, true)));
			}
			
			if(!count($paths) > 0)
			{
				$file = $paths[0];
				$ext = end(explode("."), $paths[0]);
				$config = $overwrite ? array_merge($config, static::$drivers[$ext]->load($file)) : \Arr::merge($config, static::$drivers[$ext]->load($file));
			}
			
			
		}
		
		if ($group === null)
		{
			static::$items = $reload ? $config : ($overwrite ? array_merge(static::$items, $config) : \Arr::merge(static::$items, $config));
		}
		else
		{
			$group = ($group === true) ? $file : $group;
			if ( ! isset(static::$items[$group]) or $reload)
			{
				static::$items[$group] = array();
			}
			static::$items[$group] = $overwrite ? array_merge(static::$items[$group],$config) : \Arr::merge(static::$items[$group],$config);
		}

		if ( ! is_array($file))
		{
			static::$loaded_files[$file] = true;
		}
		return $config;
		
	}
	/**
	 * Used to register a new config driver class
	 * 
	 * @param string $driver_name	alias for the driver, used to be loaded as extension or symlink
	 * @param string $driver_class 	Name of the driver class
	 * @access public
	 * @static
	 * @return boolean
	 * @throws Fuel_Exception
	 */
	public static function register_driver($driver_name, $driver_class = null)
	{
		$driver_name = strtolower($driver_name);
		$driver_class = strtolower($driver_class);
		if(!$driver_class === "")
		{
			$driver_class = 'model_'.$drivername;
		}
		
		if(!class_exists($driver_class) and get_parent_class($driver_class) === "Config_Driver")
		{
			throw new Fuel_Exception("Config driver class $driver_class not found");
			return false;
		}
		
		$driver_class = Inflector::classify($driver_class);
		
		static::$drivers[$driver_name] = new $driver_class();
		return true;
	}
	
	/**
	 * Used to unregister a config driver
	 * 
	 * @param string $driver_name	Alias of the driver to unregister
	 * @access public
	 * @static
	 * @return boolean
	 * @throws Fuel_Exception
	 */
	public static function unregister_driver($driver_name)
	{
		if(array_key_exists($driver_name))
		{
			unset(static::$drivers[$driver_name]);
			return true;
		}
		
		throw new Fuel_Exception("Config driver $driver_name are not registered");
		return false;
	}
	
	public static function available_drivers()
	{
		return static::$drivers;
	}
	
	protected static function check_extension($string)
	{
		
		$string = substr($string, 0, 3);
		
	}
}
