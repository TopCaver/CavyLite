<?php
/**
 * Cavy Lite Framework 
 * 
 * Plugin Broker.
 * 插件代理
 *
 * @author     TopCaver
 * @copyright  Copyright (c) 2013 TopCaver
 * @license    New BSD License
 * @version    $Id$
 */

abstract class Cavy_Model {

	public function create($array) {
		return (object)$array;
	}

	/**
	 * Get app path of this model.
	 * 得到Model所属的应用目录,例如'/xxx/app'
	 */
	public function getAppPath() {
		return Cavy :: getAppPathOnModel($this->getModelName());
	}
	
	/**
	 * Get model name
	 * 获取model的名字
	 *
	 * @return string model name
	 */
	protected function getModelName() {
		$class_name = get_class($this);
		$model_name = strtolower(substr($class_name,0,1)).substr($class_name,1);
		return $model_name;
	}

	/**
	 * Get config path
	 * 获取配置文件的路径。
	 *
	 * @return string config path
	 */
	public function getAppConfigPath() {
		return $this->getAppPath() . DIRECTORY_SEPARATOR . 'config';
	}
	
	/**
	 * Get config file by file name.
	 * 获得指定名称的配置文件。
	 *
	 * @param string $config_file
	 * @return string path
	 */
	protected function getConfigFile($config_file) {
		$base_path = Cavy :: getMainAppPath() . DIRECTORY_SEPARATOR . 'config' .
		DIRECTORY_SEPARATOR . $config_file;
		$path = file_exists($base_path) ? $base_path : ($this->getAppConfigPath() . DIRECTORY_SEPARATOR . $config_file);
		return $path;
	}
}
?>
