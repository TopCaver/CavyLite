<?php
/**
 * Cavy Lite Framework 
 * 
 * Cavy View Helper Manager
 * 视图层Helper管理器
 *
 * @author     TopCaver
 * @copyright  Copyright (c) 2013 TopCaver
 * @license    New BSD License
 * @version    $Id$
 */

class Cavy_View_Helper_Manager {
	
	public $helperNames = array();
	
	/**
	 * Singleton
	 * 单例
	 */
	private static $_inst = null;
	
    /**
     * hash of helper objects. array('helper_name'=>'helper object')
     * hepler对象数组
     *
     * @var array
     */
    private $_helpers = array();
    
    /**
     * Instances of array($class_name,$method_name)
     *
     * @var array
     */
    private $_helperCaller = array();
    
    private function __construct() {}

	public function init() {
		foreach ($this->helperNames as $helper_name) {
			$this->addHelper($helper_name);
		}
	}
	
	/**
	 * 添加Helper
	 *
	 * @param string $helper_name
	 */
	public function addHelper($helper_name) {
		if (isset($this->_helpers[$helper_name])) {
			return;
		}
		$clazz = Cavy::loadHelper($helper_name);
		$this->_helpers[$helper_name] = new $clazz();
	}
	
	/**
	 * 获取helper对象
	 *
	 * @param string $helper_name
	 * @return Helper
	 */
	public function getHelper($helper_name) {
		if (!isset($this->_helpers[$helper_name])) {
			$this->addHelper($helper_name);
		}
		return $this->_helpers[$helper_name];
	}
	
	/**
	 * Cavy_View __call will call helper this function.
	 * Helper供给View的__call调用的方法。
	 *
	 * @param Cavy_View $view
	 * @param string $name function name
	 * @param string $args function params
	 * @return mixed
	 */
    public function call($view,$name,$args) {
        // is the helper already loaded?
        if (!isset($this->_helperCaller[$name])) {
            // load class and create instance
            foreach($this->_helpers as $helper_name=>$helper) {
				if (method_exists($helper,$name)) {
					$helperCaller = array($helper,$name);
					$this->_helperCaller[$name] = $helperCaller;
					break;
				}            	
            }
        }
        if (!isset($this->_helperCaller[$name])) {
        	throw new Exception("Helper not found:".$name);
        }
    	$helperCaller = $this->_helperCaller[$name];
        // call the helper method
		$helperCaller[0]->setView($view);

        return call_user_func_array($helperCaller,$args);
    }
    
    
    public static function getInstance() {
    	if (self::$_inst == null) {
    		self::$_inst = new Cavy_View_Helper_Manager();
    		self::$_inst->init();
    	}
    	return self::$_inst;
    }
}
?>
