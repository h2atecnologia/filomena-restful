<?php
//if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 70000)
//	die('FilomenaRESTful requires PHP 7.0 or higher');
if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50534)
	die('FilomenaRESTful requires PHP 5.5.34 or higher');

define('FILOMENARESTFUL_VERSION_ID','1.0');

if (!defined('FILOMENARESTFUL_AUTOLOAD_PREPEND'))
	define('FILOMENARESTFUL_AUTOLOAD_PREPEND',true);

require __DIR__.'/lib/Singleton.php';
require __DIR__.'/lib/Config.php';
require __DIR__.'/lib/Exceptions.php';
require __DIR__.'/lib/RouteTable.php';
require __DIR__.'/lib/Bootstrap.php';
require __DIR__.'/lib/Controller/Exceptions.php';
require __DIR__.'/lib/Controller/Scaffolding.php';
require __DIR__.'/lib/Controller/BaseController.php';

if (!defined('FILOMENARESTFUL_AUTOLOAD_DISABLE'))
	spl_autoload_register('restful_autoload',false, FILOMENARESTFUL_AUTOLOAD_PREPEND);

function restful_autoload($class_name)
{
	//trigger_error("Class auto loaded: $class_name", E_USER_NOTICE);

	$path = RESTful\Config::instance()->get_plugin_path();
	$root = realpath(isset($path) ? $path : '.');

	if (($namespaces = get_namespaces($class_name)))
	{
		$class_name = array_pop($namespaces);
		//$directories = array();

		//foreach ($namespaces as $directory)
		//	$directories[] = $directory;

		//$root .= DIRECTORY_SEPARATOR . implode($directories, DIRECTORY_SEPARATOR);
	}

	$file = "$root/$class_name.php";

	if (file_exists($file))
		require_once $file;
}

function get_namespaces($class_name)
{
	if (has_namespace($class_name))
		return explode('\\', $class_name);
	return null;
}

function has_namespace($class_name)
{
	if (strpos($class_name, '\\') !== false)
		return true;
	return false;
}

?>