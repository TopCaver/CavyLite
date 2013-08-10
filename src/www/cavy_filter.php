<?php
/**
 * Cavy Lite Framework 
 * 
 * This file is the entrance of CavyLite.
 * All request URLs will rewrite to this file.
 * 此文件是每个请求的入口点，所有的请求都将被重写到此文件。
 *
 * @author     TopCaver
 * @copyright  Copyright (c) 2013 TopCaver
 * @license    New BSD License
 * @version    $Id$
 */

/**
 * Get base dir of cavylite.
 * 获取CavyLite的绝对路径。
 *
 * @return string 
 */
function get_base_dir() {
	$dir = str_replace('\\','/',dirname(__FILE__));
	$dir = substr($dir,0,strrpos($dir,'/'));
	return $dir;
}

// Set cavylite lib to include_path.
// 将lib目录加入到php的include path
$basedir = get_base_dir();
$lib_path = $basedir.'/lib';
set_include_path(get_include_path() . PATH_SEPARATOR . $lib_path );

include 'cavy_startup.php';
cavy_startup($basedir,$lib_path,detectContextPath()); // start up

/**
 * Get the context path of request uri.
 * 从请求或者此脚本的URI，探测上下文的相对根目录。
 *
 * @return string
 */
function detectContextPath() {
	$base = '';
	if (empty($_SERVER['SCRIPT_NAME'])) {
		$base = $_SERVER['REQUEST_URI'];
	}else if ($pos = strrpos($_SERVER['SCRIPT_NAME'], '/')) {
		$base = substr($_SERVER['REQUEST_URI'], 0, $pos);
	}
	return rtrim($base, '/');
}
?>