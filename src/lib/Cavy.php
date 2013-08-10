<?php
/**
 * Cavy Lite Framework 
 * 
 * class Cavy.
 * Cavy类
 *
 * @author     TopCaver
 * @copyright  Copyright (c) 2013 TopCaver
 * @license    New BSD License
 * @version    $Id$
 */

final class Cavy
{
	/**
     * Object registry provides storage for shared objects
     * @var array
     */
	static private $_registry = array();

	/**
     * Path of M-V-C, helper, config and i18n.
     * 一些重要路径的全局设置，包括MVC结构，helper，配置和国际化相关的路径。
     *
     * @var array
     */
	static private $_PATH = array(
	'CONTROLLER_PATH' => 'controllers',
	'MODEL_PATH' => 'models',
	'VIEW_PATH' => 'views',
	'HELPER_PATH' => 'helpers',
	'LAYOUT_PATH' => 'views/layouts',
	'LOCALE_PATH' => '/app/locale',
	'CONFIG_PATH' => '/app/config'
	);

	/**
	 * name => model object
	 */
	static private $_models = array();

	/**
	 * name => app path
	 */
	static private $_modelPathes = array();

	/**
	 * name => filter object
	 */
	static private $_filters = array();

	/**
	 * 'theme' => [string]
	 * 'locale' => [string]
	 * 'auth' => [boolean]
	 * 'CONTEXT_PATH'
	 * 'BASE_DIR'
	 * 'app.path' # array, default is parent directory of www  
	 */
	static public $ENV = array(
	'auth' => true
	);

	static private $_currentAppPath = null;

	/**
     * Singleton Pattern
     */
	private function __construct()
	{}


	/**
     * Loads a class from a PHP file.  The filename must be formatted
     * as "$class.php". It will split the class name at underscores to
     * generate a path hierarchy (e.g., "Cavy_Example_Class" will map
     * to "Cavy/Example/Class.php").
     *
     * 从PHP文件加载类，文件名应与类名相对应"$class.php"，带下划线的类，将按下划线
     * 分割为对应的目录。（例如：Cavy_Example_Class 将对应 Cavy/Example/Class.php文件）
     *
     * @param string $class
     * @return void
     */
	static public function loadClass($class)
	{
		if (class_exists($class, false)) {
			return;
		}

		// autodiscover the path from the class name
		$path = str_replace('_', DIRECTORY_SEPARATOR, $class);
		// use the autodiscovered path
		$dirs = dirname($path);
		$file = basename($path) . '.php';

		try {
			self::loadFile($file, $dirs, true);
		} catch (Exception $e) {
			// do nothing
		}
	}

	/**
	 * Load controller.
	 * 加载controller。
	 *
	 * @param string $controllerName
	 */
	static public function loadController($controllerName) {
		$className = ucfirst($controllerName) . 'Controller';
		$fn = $className.'.php';
		$dirs = array();
		$found = false;
		foreach (self::$ENV['app.path'] as $apppath) {
			try {
				$ret = self::loadFile($fn,$apppath . DIRECTORY_SEPARATOR . 'controllers',true);
				if ($ret) {
					$found = true;
					self::$_currentAppPath = $apppath;
					break;
				}
			} catch (Exception $e) {
				// do nothing
			}
		}
		if (!class_exists($className)) {
			throw new Exception("Controller $controllerName not found");
		}
	}

	/**
	 * find template
	 * 查找模板
	 *
	 * @param string $template
	 * @return file path
	 */
	static public function findTemplate($template) {
		$dirs = array();
		$found = false;
		foreach (self::$ENV['app.path'] as $apppath) {
			$fn = $apppath . '/views/' . $template;
			if (file_exists($fn)) {
				$found = true;
				break;
			}
			$fn = $apppath . '/views/' . $template . ".tpl.php";
			if (file_exists($fn)) {
				$found = true;
				break;
			}
		}
		if (!$found) {
			throw new Exception("Template $fn not found");
		}
		return $fn;
	}

	/**
	 * find layout 
	 * 查找布局文件
	 * 
	 * @return file path
	 */
	static public function findLayout($layout) {
		$dirs = array();
		$found = false;
		foreach (self::$ENV['app.path'] as $apppath) {
			$fn = $apppath . DIRECTORY_SEPARATOR . 'views' .
			DIRECTORY_SEPARATOR . 'layouts' .
			DIRECTORY_SEPARATOR . $layout . ".tpl.php";
			if (file_exists($fn)) {
				$found = true;
				break;
			}
		}
		if (!$found) {
			throw new Exception("Template $fn not found");
		}
		return $fn;
	}

	/**
	 * load helper to view from app/helpers
	 * 加载用户自定义的helper
	 *
	 * @param string $helperName
	 * @return Helper helper object
	 */
	static public function loadHelper($helperName) {

		$fn = ucfirst($helperName) . 'Helper.php';
		$dirs = array();
		$found = false;
		foreach (self::$ENV['app.path'] as $apppath) {
			try {
				if (self::loadFile($fn,$apppath . DIRECTORY_SEPARATOR . 'helpers',true)) {
					$found = true;
					break;
				}
			}
			catch (Exception $e) {
				// do nothing
			}
		}
		if (!$found) {
			throw new Exception("Helper $helperName not found");
		}
		$className = ucfirst($helperName) . 'Helper';
		return $className;
	}

	/**
	 * check controller file is exist
	 * 检查controller是否存在。
	 *
	 * @param unknown_type $controllerName
	 * @return unknown
	 */
	static public function controllerExists($controllerName) {
		$found = false;
		foreach (self::$ENV['app.path'] as $apppath) {
			$fn = ucfirst($controllerName) . 'Controller.php';
			if (self::isReadable($apppath."/controllers/$fn")) {
				return true;
			}
		}
		return false;
	}

	/**
	 * get locale file path
	 * 获取本地化文件的路径。
	 *
	 * @return string localpath
	 */
	static public function getLocalePath() {
		return self::$_currentAppPath . '/locale';
	}

	static public function getSysLocalePath() {
		return self::$ENV['LIB_PATH'].'/Cavy/locale';
	}

	static public function getLocale() {
		return self::$ENV['locale'];
	}

	static public function setLocale($locale) {
		self::$ENV['locale'] = $locale;
	}

	static public function getWebRootPath() {
		return self::$ENV['BASE_DIR'].'/www';
	}

	static public function getMainAppPath() {
		return self::$ENV['BASE_DIR'].'/app';
	}

	/**
	 * Load model by name
	 * 加载指定名字的model
	 *
	 * @param unknown_type $modelName
	 * @return unknown
	 */
	static private function loadModel($modelName) {
		foreach (self::$ENV['app.path'] as $apppath) {
			$fn = ucfirst($modelName) . ".php";
			try {
				if (self::loadFile($fn, $apppath."/models", true)) {
					return $apppath;
				}
			} catch (Cavy_Loader_File_NotFound_Exception $e) {
				// do nothing
			}
		}
		return false;
	}

	static public function getAppPathOnModel($modelName) {
		return self::$_modelPathes[$modelName];
	}

	/**
	 * Get model by model name
	 * 根据model名，获取model。
	 *
	 * @param string $modelName
	 * @return Cavy_Model cavy model class
	 */
	static public function getModel($modelName) {
		if (!isset(self::$_models[$modelName])) {
			$app_path = self::loadModel($modelName);
			if (!$app_path) return null;
			$clazz = ucfirst($modelName);
			self::$_modelPathes[$modelName] = $app_path;
			$model_obj = new $clazz();
			self::$_models[$modelName] = $model_obj;
		}
		return self::$_models[$modelName];
	}

	static public function getAppPath() {
		return self::$_currentAppPath;
	}

	/**
     * Loads a PHP file.  This is a wrapper for PHP's include() function.
     *
     * $filename must be the complete filename, including any extension such as ".php".  
     * If $once is TRUE, it will use include_once() instead of include().
     * 
     * 加载php文件，这是include的一个封装。
     *
     * @param  string        $filename
     * @param  string|null   $directory
     * @param  boolean       $once
     * @return boolean
     */
	static private function loadFile($filename, $dirs=null, $once=true)
	{

		$filespec = $filename;
		$filespec = rtrim($dirs, '\\/') . DIRECTORY_SEPARATOR . $filename;
		if(self::isReadable($filespec)){
			if ($once) {
				return (bool)(include_once $filespec);
			} else {
				return (bool)(include $filespec);
			}
		}
	}


	/**
     * Returns TRUE if the $filename is readable, or FALSE otherwise. 
     * 检查文件是否存在并可读。由于php内置的is_readable不会包括include，因此用此替代。
     *
     * @param string $filename
     * @return boolean
     */
	static public function isReadable($filename)
	{
		$f = @fopen($filename, 'r', true);
		$readable = is_resource($f);
		if ($readable) {
			fclose($f);
		}
		return $readable;
	}
}
