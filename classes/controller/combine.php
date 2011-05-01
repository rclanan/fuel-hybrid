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
 * @category    Controller_Rest
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */
abstract class Controller_Combine extends \Fuel\Core\Controller {
	
	private $_is_restful = true;
	
	protected $rest_format = null;
	protected $set_content_type = true; // set the default content type using PHP Header
	
	public $template = 'themes/default';
	public $auto_render = true;

	final protected function _acl($resource, $type = null) 
	{
		$status = \Hybrid\Acl::access_status($resource, $type);

		switch ($status) 
		{
			case 401 :
				if ($this->_is_restful === true)
				{
					$this->response(array('text' => 'You doesn\'t have privilege to do this action'), 401);
					print $this->response->body;
					exit();
				}
				else
				{
					\Request::show_404();
				}
			break;
		}
	}

	public function before() 
	{
		$this->language = \Hybrid\Factory::get_language();
		$this->user = \Hybrid\Acl_User::get();

		\Event::trigger('controller_before');
		
		$this->_is_restful = \Hybrid\Restful::is_rest();

		if ($this->_is_restful === false)
		{
			$this->_prepare_template();
		}
		else {
			$this->_prepare_restful();
		}

		return parent::before();
	}

	public function after() 
	{
		\Event::trigger('controller_after');
		
		if ($this->_is_restful === false)
		{
			$this->_render_template();
		}
		else {
			$this->_render_restful();
		}

		return parent::after();
	}

	/**
	 * Requests are not made to methods directly The request will be for an "object".
	 * this simply maps the object and method to the correct Controller method.
	 * 
	 * @param	Request		$resource
	 * @param	array		$arguments
	 */
	public function router($resource, $arguments) 
	{
		$pattern = \Hybrid\Restful::$pattern;
		
		// Remove the extension from arguments too
		$resource = preg_replace($pattern, '', $resource);
		
		// If they call user, go to $this->post_user();
		$controller_method = strtolower(\Hybrid\Input::method()) . '_' . $resource;
		
		if (method_exists($this, $controller_method)) 
		{
			$this->_is_restful = true;
			call_user_func(array($this, $controller_method));
		}
		elseif (method_exists($this, 'action_'.$resource)) 
		{
			$this->_is_restful = false;
			call_user_func(array($this, 'action_'.$resource), $arguments);
		}
		else 
		{
			if ($this->_is_restful === true)
			{
				$this->response->status = 404;
				return;
			}
			else
			{
				\Request::show_404();
			}
		}
	}

	/**
	 * Takes pure data and optionally a status code, then creates the response
	 * 
	 * @param	array		$data
	 * @param	int			$http_code
	 */
	protected function response($data = array(), $http_code = 200) 
	{
		if ($this->_is_restful === true)
		{
			$restful = \Hybrid\Restful::factory($data, $http_code)->format($this->rest_format)->execute();
			$this->response->body($restful->body);
			$this->response->status = $restful->status;

			if ($this->set_content_type === true) 
			{
				// Set the correct format header
				$this->response->set_header('Content-Type', \Hybrid\Restful::content_type($restful->format));
			}
		}
		else 
		{
			$this->response->status = $http_code;
			
			if (is_array($data) and count($data) > 0)
			{
				foreach ($data as $key => $value)
				{
					$this->template->set($key, $value);
				}
			}
			
		}
	}
	
	protected function _prepare_template()
	{
		$file = \Config::get('app.template');

		if (is_file(APPPATH . 'views/themes/' . $file . '.php')) 
		{
			$this->template = 'themes/' . $file;
		}
		
		if ($this->auto_render === true)
		{
			// Load the template
			$this->template = \View::factory($this->template);
		}
	}
	
	protected function _render_template()
	{
		//we dont want to accidentally change our site_name
		$this->template->site_name = \Config::get('app.site_name');
		
		if ($this->auto_render === true)
		{
			$this->response->body($this->template);
		}
	}
	
	protected function _prepare_restful()
	{
		if (\Hybrid\Request::main() !== \Hybrid\Request::active()) 
		{
			$this->set_content_type = false;
		}

		\Hybrid\Restful::auth();
	}
	
	protected function _render_restful() {}
	
}