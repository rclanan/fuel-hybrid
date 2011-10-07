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
class Config_Yml extends Config_Driver
{
	
	public function load($file)
	{
		return \Format::forge(file_get_contents($file), 'yaml')->to_array();
	}
	
	/**
	 * 
	 */
	public function save($config)
	{
		$content = <<<YML
#
# Fuel is a fast, lightweight, community driven PHP5 framework.
#
# @package    Fuel
# @version    1.0
# @author     Fuel Development Team
# @license    MIT License
# @copyright  2010 - 2011 Fuel Development Team
# @link       http://fuelphp.com
#


YML;
		$content .= Format::forge($config)->to_yaml();		
		return $content;
	}
}