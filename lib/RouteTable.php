<?php

namespace RESTful;

class RouteTable
{
	private $_routes = array();

	public function __construct()
	{
		if(!defined("__APP_RUNNING_MODE__"))
			define("__APP_RUNNING_MODE__", 1);

		$this->_routes["default"] = 
			array(
				"template" => "{controller}/{id}",
				"default" => array(
					//"controller" => "Home"
				),
				"constraint" => array(
					"controller" => "[a-z][a-z0-9_]+", 
					"id" => "[\d]*"
				),
				"ajax_only" => true,	// definição opcional, assume true					
				// usar:
				//
				// default - assume o action igual método chamado (RESTful)
				//
				// se action for definido é necessário alguma das opções abaixo para method
				//
				// all - para qualquer método
				// um destes: get ou post ou put ou delete
				// combinação de: get e/ou post e/ou put e/ou delete
				//
				"method" => "default",	// definição opcional, assume "default"
				"action" => null	// definição opcional, assume null
			);
	}
	
	private function _defaults(array $route)
	{
		if(!isset($route["default"]))
			$route["default"] = array();
		if(!isset($route["constraint"]))
			$route["constraint"] = array();
		if(!isset($route["method"]))
			$route["method"] = "default";
		else
			$route["method"] = strtolower($route["method"]);
		if(!isset($route["action"]))
			$route["action"] = null;
		if(!isset($route["ajax_only"]))
			$route["ajax_only"] = true;

		return $route;
	}
	
	public function set_default(array $route)
	{
		$this->_routes["default"] = $this->_defaults($route);
	}

	public function add_route($name, array $route)
	{
		if(preg_match("/^default$/i", trim($name)))
		{
			if(__APP_RUNNING_MODE__ == 1)
				throw new RESTfulRouteTableException("The default route already exists. Use set_default() method to change its value.");
			else
				die("Error# The default route already exists. Use set_default() method to change its value.");
		} else {
			if(!isset($route["method"]))
				$route["method"] = "default";

			$this->_routes[strtolower($name)] = $this->_defaults($route);
		}
	}

	public function remove_route($name)
	{
		if(preg_match("/^default$/i", trim($name)))
			if(__APP_RUNNING_MODE__ == 1)
				throw new RESTfulRouteTableException("The default route cannot be removed.");
			else
				die("Error# The default route cannot be removed.");
			
		unset($this->_routes[strtolower($name)]);
	}

	public function get_all()
	{
		return $this->_routes;
	}

	public function get_route($name)
	{
		if(isset($this->_routes[strtolower($name)]))
			return $this->_routes[strtolower($name)];
		else
			return null;
	}

	public function match_route($url)
	{
		$url = trim($url, "/");

		if(!empty($url))
		{
			$route = $this->_match($url, false);
	
			if($route != null)
				return $route;
		}
		
		return $this->_match($url, true);
	}

	public function strip_values(array $values)
	{
		$_v = array();
		foreach( $values as $k => $v )
		{
			if(preg_match("/^controller$/i", $k) != true)
			{
				$_v[] = $v;
			}
		}
		return $_v;
	}

	private function _match($url, $verify_default)
	{	
		foreach( $this->_routes as $name => $route)
		{
			if((!$verify_default && $name == "default") || ($verify_default && $name != "default"))
				continue;

			$p = explode("/", $route["template"]);

			$a = empty($url) ? array() : explode("/",  $url);

			if($verify_default && $name == "default")
			{
				for($i = 0; $i < count($p); $i++)
				{
					if( count($a) < count($p))
						array_push($a, "");

					if( empty($a[$i]) && preg_match("/{(.*)}/", $p[$i], $_out) )
					{
						if( isset($route["default"][$_out[1]]) )
							$a[$i] = $route["default"][$_out[1]];
					}
				}
				$url = implode("/", $a);
			}

			$r = array();

			for($i = 0; $i < count($p); $i++)
			{
				if( preg_match("/{(.*)}/", $p[$i], $_out) )
				{
					if( isset($route["constraint"][$_out[1]]) )
						array_push( $r, $route["constraint"][$_out[1]] );
					else
						array_push( $r, "[A-Za-z0-9_]+" );

				} else {
					array_push( $r, $p[$i] );
				}
			}
		
			$preg = "/^" . implode("\\/", $r) ."$/i";

			$_m = preg_match($preg, $url) != false;

			if($_m)
			{
				$route["values"] = $route["default"];

				for($i = 0; $i < count($p); $i++)
				{
					if( preg_match("/{(.*)}/", $p[$i], $_out) && !empty($a[$i]))
						$route["values"][$_out[1]] = $a[$i];
				}

				return $route;
			}
		
			if($verify_default && $name == "default")
				break;
		}
	
		return null;	// se não encontrado, retorna null !!
	}
}

?>