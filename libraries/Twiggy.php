<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Twiggy - Twig template engine implementation for CodeIgniter
 *
 * Twiggy is not just a simple implementation of Twig template engine 
 * for CodeIgniter. It supports themes, layouts, templates for regular
 * apps and also for apps that use HMVC (module support).
 * 
 * @package   			CodeIgniter
 * @subpackage			Twiggy
 * @category  			Libraries
 * @author    			Edmundas Kondrašovas <as@edmundask.lt>
 * @license   			http://www.opensource.org/licenses/MIT
 * @version   			0.8.0
 * @copyright 			Copyright (c) 2012 Edmundas Kondrašovas <as@edmundask.lt>
 */

if(!defined('TWIGGY_ROOT')) define('TWIGGY_ROOT', dirname(__DIR__));

require_once(TWIGGY_ROOT . '/vendor/Twig/lib/Twig/Autoloader.php');
Twig_Autoloader::register();

class Twiggy
{
	private $CI;

	private $_config = array();
	private $_template_locations = array();
	private $_data = array();
	private $_themes_base_dir;
	private $_theme;
	private $_layout;
	private $_template;
	private $_twig;
	private $_twig_loader;
	private $_module;
	
	/**
	* Constructor
	*/

	public function __construct()
	{
		log_message('debug', 'Twiggy: library initialized');

		$this->CI =& get_instance();

		$this->_config = $this->CI->config->item('twiggy');

		$this->_themes_base_dir = ($this->_config['include_apppath']) ? APPPATH . $this->_config['themes_base_dir'] : $this->_config['themes_base_dir'];
		$this->_set_template_locations($this->_config['default_theme']);

		try
		{
			$this->_twig_loader = new Twig_Loader_Filesystem($this->_template_locations);
		}
		catch(Twig_Error_Loader $e)
		{
			log_message('error', 'Twiggy: failed to load the default theme');
			show_error($e->getRawMessage());
		}
		
		$this->_twig = new Twig_Environment($this->_twig_loader, $this->_config['environment']);
		$this->_twig->setLexer(new Twig_Lexer($this->_twig, $this->_config['delimiters']));

		// Initialize defaults
		$this->theme($this->_config['default_theme'])
			 ->layout($this->_config['default_layout'])
			 ->template($this->_config['default_template']);

		// Auto-register functions and filters.
		if(count($this->_config['register_functions']) > 0)
		{
			foreach($this->_config['register_functions'] as $function) $this->register_function($function);
		}

		if(count($this->_config['register_filters']) > 0)
		{
			foreach($this->_config['register_filters'] as $filter) $this->register_filter($filter);
		}
	}

	/**
	 * Set data
	 * 
	 * @access	public
	 * @param 	mixed  	key (variable name) or an array of variable names with values
	 * @param 	mixed  	data
	 * @param 	boolean	(optional) is this a global variable?
	 * @return	object 	instance of this class
	 */

	public function set($key, $value, $global = FALSE)
	{
		if(is_array($key))
		{
			foreach($key as $k => $v) $this->set($k, $v, $global);
		}
		else
		{
			if($global)
			{
				$this->_twig->addGlobal($key, $value);
			}
			else
			{
			 	$this->_data[$key] = $value;
			}	
		}

		return $this;
	}

	/**
	 * Unset a particular variable
	 * 
	 * @access	public
	 * @param 	mixed  	key (variable name)
	 * @return	object 	instance of this class
	 */

	public function unset_data($key)
	{
		if(array_key_exists($key, $this->_data)) unset($this->_data[$key]);

		return $this;
	}

	/**
	 * Register a function in Twig environment
	 * 
	 * @access	public
	 * @param 	string	the name of an existing function
	 * @return	object	instance of this class
	 */

	public function register_function($name)
	{
		$this->_twig->addFunction($name, new Twig_Function_Function($name));

		return $this;
	}

	/**
	 * Register a filter in Twig environment
	 * 
	 * @access	public
	 * @param 	string	the name of an existing function
	 * @return	object	instance of this class
	 */

	public function register_filter($name)
	{
		$this->_twig->addFilter($name, new Twig_Filter_Function($name));

		return $this;
	}

	/**
	* Load theme
	*
	* @access	public
	* @param 	string	name of theme to load
	* @return	object	instance of this class
	*/       	

	public function theme($theme)
	{
		if(!is_dir(realpath($this->_themes_base_dir. $theme)))
		{
			log_message('error', 'Twiggy: requested theme '. $theme .' has not been loaded because it does not exist.');
			show_error("Theme does not exist in {$this->_themes_base_dir}{$theme}.");
		}

		$this->_theme = $theme;
		$this->_set_template_locations($theme);

		return $this;
	}

	/**
	 * Set layout
	 * 
	 * @access	public
	 * @param 	string	name of the layout
	 * @return	object	instance of this class
	 */

	public function layout($name)
	{
		$this->_layout = $name;
		$this->_twig->addGlobal('_layout', '_layouts/'. $this->_layout . $this->_config['template_file_ext']);

		return $this;
	}

	/**
	 * Set template
	 * 
	 * @access	public
	 * @param 	string	name of the template file
	 * @return	object	instance of this class
	 */

	public function template($name)
	{
		$this->_template = $name;

		return $this;
	}

	/**
	 * Load template and return output object
	 * 
	 * @access	private
	 * @return	object	output
	 */

	private function _load()
	{
		return $this->_twig->loadTemplate($this->_template . $this->_config['template_file_ext']);
	}

	/**
	 * Render and return compiled HTML
	 * 
	 * @access	public
	 * @return	string	compiled HTML
	 */

	public function render()
	{
		return $this->_load()->render($this->_data);
	}

	/**
	 * Display the compiled HTML content
	 *
	 * @access	public
	 * @return	void
	 */

	public function display()
	{
		try
		{
			$this->_load()->display($this->_data);
		}
		catch(Twig_Error_Loader $e)
		{
			show_error($e->getRawMessage());
		}
	}

	/**
	* Set template locations
	*
	* @access	private
	* @param 	string	name of theme to load
	* @return	void
	*/

	private function _set_template_locations($theme)
	{
		// Check if HMVC is installed.
		// NOTE: there may be a simplier way to check it but this seems good enough.
		if(method_exists($this->CI->router, 'fetch_module'))
		{
			$this->_module = $this->CI->router->fetch_module();

			// Only if the current page is served from a module do we need to add extra template locations.
			if(!empty($this->_module))
			{
				$module_locations = Modules::$locations;

				foreach($module_locations as $loc => $offset)
				{
					$this->_template_locations[] = $loc . $this->_module . '/' . $this->_config['themes_base_dir'] . $theme;
				}
			}
		}

		$this->_template_locations[] =  $this->_themes_base_dir . $theme;

		// Reset the paths if needed.
		if(is_object($this->_twig_loader))
		{
			$this->_twig_loader->setPaths($this->_template_locations);
		}
	}

	/**
	* Get current theme
	*
	* @access	public
	* @return	string	name of the currently loaded theme
	*/

	public function get_theme()
	{
		return $this->_theme;
	}

	/**
	* Get current layout
	*
	* @access	public
	* @return	string	name of the currently used layout
	*/

	public function get_layout()
	{
		return $this->_layout;
	}

	/**
	* Get template
	*
	* @access	public
	* @return	string	name of the loaded template file (without the extension)
	*/

	public function get_template()
	{
		return $this->_template;
	}

	/**
	* Magic method __get()
	*/

	public function __get($variable)
	{
		if($variable == 'twig') return $this->_twig;

		return (array_key_exists($variable, $this->_data)) ? $this->_data[$variable] : FALSE;
	}
}
// End Class