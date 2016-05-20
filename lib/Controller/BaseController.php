<?php

namespace RESTful\Controller;

class BaseController extends Scaffolding
{
	private $default_cookie_name;
	
	public function __construct( $cookie_name )
	{
		$this->default_cookie_name = $cookie_name;
		
		$this->class_name = "\\" . $this->get_called_class();
	}

	final protected function get_called_class()
	{
		$dummy = explode("\\", get_class($this));
		return array_pop($dummy);
	}
	
	public function get_cookie()
	{
		return 
			\HTTP\Request::get_cookie($this->default_cookie_name);
	}

	public function set_cookie(array $arrayValue, $validate = "", $path = "", $domain = "")
	{
		\HTTP\Response::set_cookie($this->default_cookie_name, $arrayValue, $validate, $path, $domain);
	}

	public function handle_status_code($status, $statusText = null)
	{
		\HTTP\Response::http_action_result($status, $statusText);
	}

	public function bad_request($statusText = null)
	{
		\HTTP\Response::http_action_result(400, $statusText);
	}

	public function unauthorized($statusText = null)
	{
		\HTTP\Response::http_action_result(401, $statusText);
	}

	public function forbidden($statusText = null)
	{
		\HTTP\Response::http_action_result(403, $statusText);
	}

	public function error($statusText = null)
	{
		\HTTP\Response::http_action_result(500, $statusText);
	}

	public function render($response)	// conforme Accept Header
	{
		if(\HTTP\Request::accept_xml())
			$this->render_xml($response);

		$this->render_json($response);
	}

	public function render_json($response)
	{
		\HTTP\Response::http_json();
		if(version_compare(PHP_VERSION, '5.4.0') >= 0)
			echo json_encode($response, JSON_BIGINT_AS_STRING);
		else
			echo json_encode($response);

		exit();
	}

	public function render_xml($response, $namespace)
	{
		\HTTP\Response::http_xml();
		// TODO encode XML 
		// TODO encode XML 
		// TODO encode XML 
		// TODO encode XML 

		throw new Exception("Render XML not implemented.");
	}

	public function render_as_text($response)
	{
		\HTTP\Response::http_text();
		echo $response;

		exit();
	}
}

?>