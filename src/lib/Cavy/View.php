<?php
/**
 * Cavy Lite Framework 
 * 
 * Cavy View class for handling view scripts.
 * Cavy 视图层处理视图层脚本
 *
 * @author     TopCaver
 * @copyright  Copyright (c) 2013 TopCaver
 * @license    New BSD License
 * @version    $Id$
 */

Cavy::loadClass('Cavy_View_Helper_Manager');

class Cavy_View
{

    /**
     * Assigned variables.
     *
     * @var array
     */
    private $_vars = array();

    /**
     * Stack of executing script file names.
     *
     * @var array
     */
    private $_file = array();
    
	private $_helperMgr= null;
	
    /**
     * Stack of Cavy_View_Filter names to apply as filters.
     *
     * @var array
     */
    private $_filter = array();

    /**
     * Callback for escaping.
     *
     * @var string
     */
    private $_escape = 'htmlspecialchars';
    
    /**
     * content template
     */
    private $_content;

    /**
     * Cavy_Controller_Dispatcher_Token object wrapping this controller/action call.
     * @var Cavy_Controller_Dispatcher_Token
     */
	private $_action = null;
	
	/**
	 * Cavy_Controller_Action
	 */
	private $_controller = null;
	
	public function __construct($controller) {
		$this->_helperMgr = Cavy_View_Helper_Manager::getInstance();
		$this->_controller = $controller;
	}


    /**
     * Directly assigns a variable to the view script.
     * Note that variable names may not be prefixed with '_'.
     * 
     * 可以直接定义变量在视图脚本中。
     *
     * @param string $key The variable name.
     * @param mixed $val The variable value.
     * @return void
     */
    public function __set($key, $val) {
        if ($key[0] != '_') {
            $this->_vars[$key] = $val;
        }
    }

    /**
     * Retrieves an assigned variable.
     * Note that variable names may not be prefixed with '_'.
     *
     * @param string $key The variable name.
     * @return mixed The variable value.
     */
    public function __get($key) {
        if ($key[0] != '_') {
            return isset($this->_vars[$key]) ? $this->_vars[$key] : null;
        }
    }


    /**
     * Allows testing with empty() and isset() to work inside
     *
     * @param  string $key
     * @return boolean
     */
    public function __isset($key) {
        return array_key_exists($key, $this->_vars) && ($key[0] != '_');
    }


    /**
     * Accesses a helper object from within a script.
     * 直接调用helper对象中的方法。
     *
     * @param string $name The helper name.
     * @param array $args The parameters for the helper.
     * @return string The result of the helper output.
     */
    public function __call($name, $args) {
    	return $this->_helperMgr->call($this,$name, $args);
    }
    
    public function loadHelper($helperName) {
    	$this->_helperMgr->addHelper($helperName);
    }

	public function getHelper($helper_name) {
		return $this->_helperMgr->getHelper($helper_name);
	}
    
    public function getErrors() {
    	return $this->_controller->getErrors();
    }
    
    public function getFieldErrors() {
    	return $this->_controller->getFieldErrors();
    }
    
    public function getFieldError($field) {
    	return $this->_controller->getFieldError($field);
    }
    
    public function hasError() {
		$field_errors = $this->getFieldErrors();
		$errors = $this->getErrors();
    	return !empty($field_errors) || !empty($errors);
    }
    
    /**
     * Processes a view script and returns the output.
     * 处理视图层脚本，输出。
     *
     * @param string $name The script script name to process.
     * @return string The script output.
     */
    public function render($name)
    {
        $file = Cavy::findTemplate($name);
		$this->_renderFile($file);
    }
    
    private function _renderFile($file) {
        array_push($this->_file, $file);
        $this->_run($file); 
        array_pop($this->_file);

        if (empty($this->_file)) {
            // stack is empty, so that was the last script.
            // stop buffering, filter output, and return.
            return $this->_filter(ob_get_clean());
        }
    }
    
    public function renderLayout($layout) {
        $file = Cavy::findLayout($layout);
		$this->_renderFile($file);
    }
    
    public function renderContent() {
    	$this->render($this->_content);
    }
    
    public function setContent($content) {
    	$this->_content = $content;
    }

    /**
     * Applies the filter callback to a buffer.
     * 应用filter的过滤函数到缓冲区。
     *
     * @param string $buffer The buffer contents.
     * @return string The filtered buffer.
     */
    private function _filter($buffer) {
        // loop through each filter class
        foreach ($this->_filter as $name) {
            // load and apply the filter class
            $class = $this->_loadClass('filter', $name);
            $buffer = call_user_func(array($class, 'filter'), $buffer);
        }
        // done!
        return $buffer;
    }

    /**
     * Adds paths to the path stack in LIFO order.
     * 设置路径。
     *
     * @param string $type The path type ('script', 'helper', or 'filter').
     * @param string|array $path The path specification.
     * @return void
     */
    private function _addPath($type, $path) {
        // add the path to the stack
        foreach ((array) $path as $dir) {
        	// attempt to strip any possible separator and
        	// append the system directory separator
            $dir = rtrim($dir, '\\/' . DIRECTORY_SEPARATOR) 
                 . DIRECTORY_SEPARATOR;
            
            // add to the top of the stack.
            array_unshift($this->_path[$type], $dir);
        }
    }

    /**
     * Resets the path stack for helpers and filters.
     * 给helpers和filters重置path。
     *
     * @param string $type The path type ('helper' or 'filter').
     * @param string|array $path The directory (-ies) to set as the path.
     */
    private function _setPath($type, $path) {
        $dir = DIRECTORY_SEPARATOR . ucfirst($type) . DIRECTORY_SEPARATOR;
        $this->_path[$type] = array(dirname(__FILE__) . $dir);
        $this->_addPath($type, $path);
    }

    /**
     * Loads a helper or filter class.
     * 加载helper或者filter类
     *
     * @param string $type The class type ('helper' or 'filter').
     * @param string $name The base name.
     * @param string The full class name.
     */
    private function _loadClass_remove($type, $name) {
        // from $type & $name to Cavy_View_$Type_$Name
        $class = 'Cavy_View_' . ucfirst($type) . '_' . ucfirst($name);

        // if the class does not exist, attempt to load it from the path stack
        if (class_exists($class, false)) {
        	return $class;
        }
        
        // only look for "$Name.php"
        $file = ucfirst($name) . '.php';
        foreach ($this->_path[$type] as $dir) {
            if (is_readable($dir . $file)) {
                include $dir . $file;
                
                if (! class_exists($class, false)) {
                	$msg = "$type '$name' loaded but class '$class' not found within";
                	throw new Exception($msg);
                }
                return $class;
            }
        }
        throw new Exception("$type '$name' not found in path.");
    }
	/**
     * Includes the view script in a scope with only public $this variables.
     * Include 视图脚本。
     *
     * @param string The view script to execute.
     */
    protected function _run() {
    	$tpl = func_get_arg(0);
    	include $tpl;
    }
}
