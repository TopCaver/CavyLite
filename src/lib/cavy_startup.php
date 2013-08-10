<?php
/**
 * Cavy Lite Framework 
 * 
 * The main startup function of CavyLite.
 * Init plugin/model, load cavy.conf, then dispatch.
 * CavyLite 主启动函数，初始化插件，读取配置文件，分发请求。
 *
 * @author     TopCaver
 * @copyright  Copyright (c) 2013 TopCaver
 * @license    New BSD License
 * @version    $Id$
 */


function cavy_startup($baseDir,$lib_path,$ctxt_path='') {

	require "Cavy.php";

	session_start();
	Cavy::loadClass('Cavy_Controller_Front');
	Cavy::loadClass('Cavy_Controller_Plugin_Auth');
	Cavy::loadClass('Cavy_Model');
	Cavy::loadClass('Cavy_View_Helper');

	// load cavy.conf to Cavy::$ENV and parse app.path
	// 加载cavy.conf.php配置到Cavy::$ENV， 解析app.path，加载扩展应用。
	if (file_exists($baseDir.'/app/config/cavy.conf.php')) {
		$env = null;
		include "$baseDir/app/config/cavy.conf.php";
		Cavy::$ENV = $env;
		if (isset($env['app.path']) && !empty($env['app.path'])) {
			Cavy::$ENV['app.path'] = array_merge(array($baseDir . '/app'),explode(";",$env['app.path']));
		} else {
			Cavy::$ENV['app.path'] = array($baseDir . '/app');
		}
		unset($env);
	}

	Cavy::$ENV['CONTEXT_PATH'] = $ctxt_path;
	Cavy::$ENV['BASE_DIR'] = $baseDir;
	Cavy::$ENV['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'],strlen(Cavy::$ENV['CONTEXT_PATH']));
	Cavy::$ENV['LIB_PATH'] = $lib_path;

	// Get intstance of cavy_controller_front
	// 获取一个Front实例。
	$frontController = Cavy_Controller_Front :: getInstance();

	// Register auth plugin if noauth disabled.
	// 检查是否需要认证，如果需要加载认证插件。
	if (!isset(Cavy::$ENV['noauth']) || !Cavy::$ENV['noauth']) {
		$frontController->registerPlugin(new Cavy_Controller_Plugin_Auth());
	}

	// run bootstrap if it exists.
	// 如果存在自定义启动器，运行自定义启动器。
	if(file_exists($baseDir.'/app/bootstrap.php')) {
		include $baseDir.'/app/bootstrap.php';
	}

	// dispatch().
	// 启动分发
	$frontController->dispatch();
}
?>