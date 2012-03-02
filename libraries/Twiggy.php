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
 * @version   			0.7.0
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
	private $_data;
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
			show_error("Default theme in {$this->_themes_base_dir}{$this->_config['default_theme']} does not exist.");
		}
		
		$this->_twig = new Twig_Environment($this->_twig_loader, $this->_config['environment']);
		$this->_twig->setLexer(new Twig_Lexer($this->_twig, $this->_config['delimiters']));

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
	 * @param 	string 	key (variable name)
	 * @param 	mixed  	data
	 * @param 	boolean	(optional) is this a global variable?
	 * @return	object 	instance of this class
	 */

	public function set($key, $value, $global = FALSE)
	{
		if($global)
		{
			$this->_twig->addGlobal($key, $value);
		}
		else
		{
			$this->_data[$key] = $value;
		}

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

		$this->_loaded_theme = $theme;
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
		$this->_layout = $name . $this->_config['template_file_ext'];
		$this->_twig->addGlobal('_layout', '_layouts/'. $this->_layout);

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
		$this->_template = $name . $this->_config['template_file_ext'];

		return $this;
	}

	/**
	 * Render and return compiled HTML
	 * 
	 * @access	public
	 * @return	string	compiled HTML
	 */

	public function render()
	{
		$output = $this->_twig->loadTemplate($this->_template);

		return $output->render($this->_data);
	}

	/**
	 * Display the compiled HTML content
	 *
	 * @access	public
	 * @return	void
	 */

	public function display()
	{
		$output = $this->_twig->loadTemplate($this->_template);
		$output->display($this->_data);
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
	* Magic method __get()
	*/

	public function __get($variable)
	{
		if($variable == 'twig') return $this->_twig;

		return (array_key_exists($variable, $this->_data)) ? $this->_data[$variable] : FALSE;
	}
}
// End Class