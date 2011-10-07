<?php

/**
 * Fuel
 *
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
 * Chart class tests
 * 
 * @group Hybrid
 * @group Config
 */
class Test_Config extends \Fuel\Core\TestCase 
{
    
    /**
     * Setup the test
     */
    public function setup()
    {
        \Package::load('hybrid');
    }

    /**
     * Test Config::register_driver();
     *
     * @test
     */
    public function test_register_driver()
    {
        $output = Config::register_driver('testdriver');
        
        $this->assertArrayHasKey('testdriver');
        $this->assertContains('Config_Testdriver', Config::available_drivers());
        
        $output = Config::register_driver('undefineddriver');
        $this->assertFalse($output);
    }
    
    /**
     * Test Config::unregister_driver();
     *
     * @test
     */
    public function test_unregister_driver()
    {
        $output = Config::unregister_driver('undefineddriver');
        $this->assertFalse($output);

        Config::register_driver('testdriver');
        $output = Config::unregister_driver('testdriver');
        $this->assertTrue($output);
        
        $output = false;
        if(array_key_exists('testdriver', Config::available_drivers()))
        {
            $output = true;
        }
        
        $this->asserFalse($output);
    }
    
    /**
     * Test Config::available_drivers();
     *
     * @test
     */
    public function test_available_drivers()
    {
        Config::register_driver('testdriver');
        
        $output = Config::available_drivers();
        
        $this->assertContains('Config_Testdriver', $output);
        $this->assertArrayHasKey('redis', $output);
        $this->assertArrayHasKey('testdriver', $output);
    }
}