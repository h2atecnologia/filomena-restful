<?php

namespace HTTP;

class Request
{
	public static function is_cors()
	{
		return isset($_SERVER['HTTP_ORIGIN']);
	}
	
	public static function is_ajax()
	{
		return !empty($_SERVER["HTTP_X_REQUESTED_WITH"]) &&
			$_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest";
	}

	public static function accept_json()
	{
		if(isset($_SERVER['HTTP_ACCEPT']))
		{
			$p = explode(";", $_SERVER['HTTP_ACCEPT']);
			$pp = preg_split("/[\s,]+/", $p[0]);		
			return in_array("application/json", $pp);
		}		
		return false;
	}

	public static function accept_xml()
	{
		if(isset($_SERVER['HTTP_ACCEPT']))
		{
			$p = explode(";", $_SERVER['HTTP_ACCEPT']);
			$pp = preg_split("/[\s,]+/", $p[0]);		
			return in_array("application/xml", $pp);
		}		
		return false;
	}

	public static function is_post()
	{
		return ($_SERVER['REQUEST_METHOD']=="POST");
	}

	public static function is_get()
	{
		return ($_SERVER['REQUEST_METHOD']=="GET");
	}

	public static function is_put()
	{
		return ($_SERVER['REQUEST_METHOD']=="PUT");
	}

	public static function is_delete()
	{
		return ($_SERVER['REQUEST_METHOD']=="DELETE");
	}

	public static function is_options()
	{
		return ($_SERVER['REQUEST_METHOD']=="OPTIONS");
	}		
	
	public static function get_get_object()
	{
		unset($_GET["_"]);	// limpa jQuery cache hint
		return count($_GET) ? (object) $_GET : null;
	}
	
	public static function get_post_object()
	{
		return count($_POST) ? (object) $_POST : null;
	}
	
	public static function get_put_object()
	{
		if($_SERVER['CONTENT_LENGTH'])
		{				
			parse_str(file_get_contents('php://input', false , null, -1 , $_SERVER['CONTENT_LENGTH']), $_PUT);
			return (object) $_PUT;
		}
		return null;
	}
	
	public static function get_cookie($cookie_name)
	{
		if(isset($_COOKIE[$cookie_name]))
		{ 
			$tmparray=unserialize(stripslashes($_COOKIE[$cookie_name])); 
		} else { 
			$tmparray = array(); 
		} 
		return $tmparray; 
	}
}

class Response
{
	public static function redirect($status, $url)
	{
		header("Cache-Control: private");
		self::http_action_result($status);	// para o IE
		header( "Location: $url" );
		header( "Connection: close" );				// para o IE
		exit();
	}

	public static function http_text()
	{
		header('Content-Type: text/plain; charset=utf-8');
	}

	public static function http_json()
	{
		header('Content-Type: application/json; charset=utf-8');
	}

	public static function http_xml()
	{
		header('Content-Type: application/xml; charset=utf-8');
	}

	public static function http_csv()
	{
		header('Content-Type: application/csv; charset=utf-8');
	}

	public static function http_action_result($status, $statusText = null) {

		switch ($status) {
			case 100: $text = 'Continue'; break;
			case 101: $text = 'Switching Protocols'; break;
			case 200: $text = 'OK'; break;
			case 201: $text = 'Created'; break;
			case 202: $text = 'Accepted'; break;
			case 203: $text = 'Non-Authoritative Information'; break;
			case 204: $text = 'No Content'; break;
			case 205: $text = 'Reset Content'; break;
			case 206: $text = 'Partial Content'; break;
			case 300: $text = 'Multiple Choices'; break;
			case 301: $text = 'Moved Permanently'; break;
			case 302: $text = 'Moved Temporarily'; break;
			case 303: $text = 'See Other'; break;
			case 304: $text = 'Not Modified'; break;
			case 305: $text = 'Use Proxy'; break;
			case 400: $text = 'Bad Request'; break;
			case 401: $text = 'Unauthorized'; break;
			case 402: $text = 'Payment Required'; break;
			case 403: $text = 'Forbidden'; break;
			case 404: $text = 'Not Found'; break;
			case 405: $text = 'Method Not Allowed'; break;
			case 406: $text = 'Not Acceptable'; break;
			case 407: $text = 'Proxy Authentication Required'; break;
			case 408: $text = 'Request Time-out'; break;
			case 409: $text = 'Conflict'; break;
			case 410: $text = 'Gone'; break;
			case 411: $text = 'Length Required'; break;
			case 412: $text = 'Precondition Failed'; break;
			case 413: $text = 'Request Entity Too Large'; break;
			case 414: $text = 'Request-URI Too Large'; break;
			case 415: $text = 'Unsupported Media Type'; break;
			case 500: $text = 'Internal Server Error'; break;
			case 501: $text = 'Not Implemented'; break;
			case 502: $text = 'Bad Gateway'; break;
			case 503: $text = 'Service Unavailable'; break;
			case 504: $text = 'Gateway Time-out'; break;
			case 505: $text = 'HTTP Version not supported'; break;
			default:
				exit('Unknown http status code "' . htmlentities($code) . '"');
				break;
		}

		$protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');

		header($protocol . ' ' . $status . ' ' . utf8_decode($statusText === null ? $text : $statusText));
	
		if($status >= 400)
			exit();
	}

	public static function set_cookie($cookie_name, array $arrayValue, $validate = "", $path = "", $domain = "")
	{
		$tmpstring = serialize($arrayValue); 
		if( $validate == '' )
		{
			setcookie($cookie_name,$tmpstring ); // cookie de sessão!
		} else {
			setcookie($cookie_name,$tmpstring, $validate, $path, $domain, false, true); 
		}
		unset($tmpstring); 
	}
}

?>