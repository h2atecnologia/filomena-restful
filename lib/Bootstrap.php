<?php

namespace RESTful;

class Bootstrap
{
	private $controller_directory = "./controller/";
	private $default_cookie_name = "app_default_cookie";
	private $route_table;
	private $root_path;
	
	public function __construct( $root = null, RouteTable $route_table = null, $controller_dir = null )
	{
		if(!defined("__APP_RUNNING_MODE__"))
			define("__APP_RUNNING_MODE__", 1);

		if($root == null || gettype($root) != "string")
			if(__APP_RUNNING_MODE__ == 1)
				throw new RESTfulBootstrapException("Root value is a mandatory parameter.");
			else
				die("Error# Root value is a mandatory parameter.");

		if($route_table == null || gettype($route_table) != "object")
			if(__APP_RUNNING_MODE__ == 1)
				throw new RESTfulBootstrapException("RouteTable instance is a mandatory parameter.");
			else
				die("Error# RouteTable instance is a mandatory parameter.");

		$this->root_path = $root;
		
		$this->route_table = $route_table;
		
		if($controller_dir != null)
			$this->controller_directory = $controller_dir;
	}

	public function set_default_cookie_name($name)
	{
		$this->default_cookie_name = $name;
	}
	
	public function set_cache($maxAge = 60)
	{
		//Set up no cache
		header("Expires: Mon, 06 Jan 1990 00:00:01 GMT");             // Date in the past
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified
		header("Cache-Control: no-cache, max-age=$maxAge, must-revalidate");
		header("Pragma: no-cache");
	}

	public function handle_cors(array $allowed_origins = array(), array $send_headers = array(), $send_credentials = true)
	{
		// Set $send_headers if needed (ex: X-Requested-With, X-HTTP-Method-Override, Content-Type, Authorization, Accept, Origin)
		// Set $send_credentials to TRUE if expects credential requests (Cookies, Authentication, SSL certificates)
		
		if(\HTTP\Request::is_cors())
		{			
			if(count($allowed_origins) !=0 )
			{
				// Origin is not allowed ?
				if (!in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins))
					if(__APP_RUNNING_MODE__ == 1)
						throw new RESTfulBootstrapException("Origin: " . $_SERVER['HTTP_ORIGIN'] . " unauthorized.");
					else
						die("Error# Origin: " . $_SERVER['HTTP_ORIGIN'] . " unauthorized.");
			}

			$origin = count($allowed_origins) ==0 && !$send_credentials ? "*" : $_SERVER['HTTP_ORIGIN'];
			
			$headers = count($send_headers) == 0 ? "Origin" : implode(", ", $send_headers);
			
			header("Access-Control-Allow-Origin: $origin");
			if ($send_credentials)
				header("Access-Control-Allow-Credentials: true");
			header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
			header("Access-Control-Allow-Headers: $headers");
			header('P3P: CP="CAO PSA OUR"');		// Makes IE to support cookies

			// Handling the Preflight
			if(\HTTP\Request::is_options())
				exit();
		}
	}
	
	private function validate_method($method)
	{
		if($method=="default" || $method=="all")
			return true;

		$r = preg_split("/[\s,;\|]+/", $method);

		$preg = "/^(" . implode("|", $r) .")$/i";

		return 
			preg_match($preg, $_SERVER['REQUEST_METHOD']);			
	}

	public function run()
	{
		$_qs = "";

		if(isset($_SERVER["PATH_INFO"]))
		{
			$_qs = ltrim($this->root_path=="/" ? $_SERVER["PATH_INFO"] : str_replace($this->root_path,"",$_SERVER["PATH_INFO"]),"/");
		} else {
			if(isset($_SERVER["REDIRECT_URL"]) && $_SERVER["REDIRECT_URL"]!="")
			{
				$_qs = ltrim($this->root_path=="/" ? $_SERVER["REDIRECT_URL"] : str_replace($this->root_path,"",$_SERVER["REDIRECT_URL"]),"/");
			}
			else if(isset($_GET["path_info"]))
			{
				$_qs = $_GET["path_info"];
			} 
		}

		$_route = $this->route_table->match_route($_qs);
		
		if($_route == null)	// nenhum route foi encontrado
			if(__APP_RUNNING_MODE__ == 1)
				throw new RESTfulBootstrapException("RouteTable cannot discover a route.");
			else
				die("Error# RouteTable cannot discover a route.");

		if($_route["ajax_only"] && !\HTTP\Request::is_ajax())
			if(__APP_RUNNING_MODE__ == 1)
				throw new RESTfulBootstrapException("Only ajax is allowed for this call.");
			else
				die("Error# Only ajax is allowed for this call.");
			
		if(!$this->validate_method( $_route["method"]))
			if(__APP_RUNNING_MODE__ == 1)
				throw new RESTfulBootstrapException("Method: " . strtolower($_SERVER['REQUEST_METHOD']) . " not allowed in rule: " .  $_route["method"] . ".");
			else
				die("Error# Method: " . strtolower($_SERVER['REQUEST_METHOD']) . " not allowed in rule: " .  $_route["method"] . ".");
		
		//------  route values
		$_values = $this->route_table->strip_values( $_route["values"] );

		//------ get valies
		if(\HTTP\Request::is_get())
			$_values[] = \HTTP\Request::get_get_object();
		//------ post values
		else if(\HTTP\Request::is_post())
			$_values[] = \HTTP\Request::get_post_object();
		//------ put values
		else if(\HTTP\Request::is_put())
			$_values[] = \HTTP\Request::get_put_object();
		
		//------ controller
		
		$_controller =  "RESTful\\Controller\\".$_route["values"]["controller"];

		if(!file_exists($this->controller_directory . strtolower($_route["values"]["controller"]) . ".php"))
		{
			if(__APP_RUNNING_MODE__ == 1)
				throw new RESTfulBootstrapException("Controller file: " . strtolower($_route["values"]["controller"]) . " not found.");
			else
				die("Error# Controller file: " . strtolower($_route["values"]["controller"]) . " not found.");
		} else {
			require_once( $this->controller_directory . strtolower($_route["values"]["controller"]) . ".php" );
		}

		//------  action
					
		$_action = $_route["method"] == "default" ? ucfirst($_SERVER['REQUEST_METHOD']) : $_route["action"];
		
		if(!class_exists($_controller))
			if(__APP_RUNNING_MODE__ == 1)
				throw new RESTfulBootstrapException("Controller instance: " . $_route["values"]["controller"] . " not found.");
			else
				die("Error# Controller instance: " . $_route["values"]["controller"] . " not found.");
		
		$_instance = new $_controller( $this->default_cookie_name );

		if(method_exists($_instance, $_action))
		{
			call_user_func_array(array($_instance, $_action), $_values);
		} else {
			if(__APP_RUNNING_MODE__ == 1)
				throw new RESTfulBootstrapException("Action method: " . $_action . " not found.");
			else
				die("Error# Action method: " . $_action . " not found.");
		}

	}
}

?>