<?php
if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 70000)
	die('FilomenaRESTful requires PHP 7.0 or higher');

define('FILOMENARESTFUL_VERSION_ID','1.0');

if (!defined('FILOMENARESTFUL_AUTOLOAD_PREPEND'))
	define('FILOMENARESTFUL_AUTOLOAD_PREPEND',true);

require __DIR__.'/lib/HTTP.php';
require __DIR__.'/lib/Exceptions.php';
require __DIR__.'/lib/RouteTable.php';
require __DIR__.'/lib/Bootstrap.php';
require __DIR__.'/lib/Controller/Exceptions.php';
require __DIR__.'/lib/Controller/Scaffolding.php';
require __DIR__.'/lib/Controller/BaseController.php';

/*if (!defined('FILOMENARESTFUL_AUTOLOAD_DISABLE'))
	spl_autoload_register('restful_autoload',false, FILOMENARESTFUL_AUTOLOAD_PREPEND);

function restful_autoload($class_name)
{
	$path = ActiveRecord\Config::instance()->get_model_directory();
	$root = realpath(isset($path) ? $path : '.');

	if (($namespaces = ActiveRecord\get_namespaces($class_name)))
	{
		$class_name = array_pop($namespaces);
		$directories = array();

		foreach ($namespaces as $directory)
			$directories[] = $directory;

		$root .= DIRECTORY_SEPARATOR . implode($directories, DIRECTORY_SEPARATOR);
	}

	$file = "$root/$class_name.php";

	if (file_exists($file))
		require_once $file;
}*/
?>