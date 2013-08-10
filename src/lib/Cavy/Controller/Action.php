<?php
/**
 * Cavy Lite Framework 
 * 
 * Cavy Controller Action
 * Controller 基类
 *
 * @author     TopCaver
 * @copyright  Copyright (c) 2013 TopCaver
 * @license    New BSD License
 * @version    $Id$
 */

Cavy::loadClass('Cavy_View');
Cavy::loadClass('Cavy_Controller_Validator');

abstract class Cavy_Controller_Action
{
	/**
	 * layout setting
	 * 布局设置。
	 * Example:
	 *   $layout = "no";  // no layout
	 *   $layout = "site";  // use layout called 'site'
	 *   $layout = array('name'=>'site','except'=>'add,index'); // use layout called 'site' except 'add' & 'index' actions
	 *   $layout = array('name'=>'site','only'=>'remove,edit'); // use layout called 'site' only 'remove' & 'edit' actions 
	 *   $layout = array('function'=>'determineLayout'); // use a method to generate layout name when action call '_render' method
	 */	 
	public $layout = null;

	/**
	 * Models name which will be used in a controller.
	 * controller 需要用到的 models
	 * Example:
	 *   $_models = "Foo,User";
	 */	 
	public $models = null;

	/**
	 * Hidden actions wound't be called
	 * 隐藏的action方法，不会被外部调用。
	 * Example:
	 *   $_hiddenActions = 'add,update';
	 */	 
	public $hiddenActions = null;

	/**
     * Cavy_Controller_Token object wrapping this controller/action call.
     * 
     * @var Cavy_Controller_Token
     */
	protected $_action = null;

	/**
     * Parameters, copied from Cavy_Controller_Token object
     * 参数列表，从Cavy_Controller_Token复制。
     * @var array
     */
	protected $_params = null;

	/**
     * Cavy_Controller_Token object wrapping the controller/action for the next
     * call.  This is set by Cavy_Controller_Action::_forward().
     * 下一个action调用，Cavy_Controller_Action::_forward()可设置。
     * @var Cavy_Controller_Token
     */
	private $_nextAction = null;

	/**
	 * array("message")
	 */
	private $_errors = array ();

	/**
	 * array("field"=>array("message","message"))
	 */
	private $_fieldErrors = array();


	/**
	 * Do validate before some actions calling. Example:update,insert.
	 * 使用validate方法进行校验的方法,例如update,insert等,以','分隔
	 */
	public $validateOn = null;

	/**
     * Any controller extending Cavy_Controller_Action must provide an index()
     * method.  The index() method is the default action for the controller
     * when no action is specified.
     *
     * 任何controller都必须提供index方法，如果URI中不指定action，那么index是默认方法。
     */
	abstract public function index();


	/**
     * Class constructor
     */
	public function __construct()
	{}

	/**
	 * Get general errors array
	 */
	public function getErrors() {
		return $this->_errors;
	}

	/**
	 * return a map key=>field,value=>err-msg
	 */	
	public function getFieldErrors() {
		return $this->_fieldErrors;
	}

	/**
	 * return a field error message
	 */	
	public function getFieldError($field) {
		return $this->_fieldErrors[$field];
	}

	/**
	 * Get filters by action and 'filters' property
	 * @param string $action action name
	 */
	final public function getFilters($action) {
		$filters = array();
		foreach ($this->filters as $filterarray) {
			$filter = $filterarray[0];
			$only = $filterarray['only'];
			$except = $filterarray['except'];
			if ($only != null) {
				$array = explode(',',$only);
				if (in_array($action,$array)) {
					$filters[] = $filter;
				}
			}
			else if ($except != null) {
				$array = explode(',',$except);
				if (!in_array($action,$array)) {
					$filters[] = $filter;
				}
			}
			else {
				// all
				$filters[] = $filter;
			}
		}
		return $filters;
	}

	/**
     * Initialize the class instance variables and then call the action.
     * 调用controller的action方法。
     *
     * @param Cavy_Controller_Token $action
     */
	final public function run(Cavy_Controller_Dispatcher $dispatcher, Cavy_Controller_Token   $action)
	{

		$this->_action     = $action;
		$this->_params     = $action->getParams();

		if (!strlen( $action->getActionName() )) {
			$action->setActionName('index');
		}

		$methodName = $dispatcher->formatActionName($action->getActionName());

		if (method_exists($this, $methodName)) {
			// block illegal hidden action call.
			// 阻止对声明为action的外部调用。
			$hiddenActions = explode(',',$this->hiddenActions);
			if (in_array($methodName,$hiddenActions)) {
				throw new Exception('Illegal action called.');
			} else {
				// validate action is public or static;
				// 验证调用的方法，是否为public或者static属性。
				$this->_doValidation($action->getActionName());
				$method = new ReflectionMethod($this, $methodName);
				if ($method->isPublic() && !$method->isStatic()) {
					$this->{$methodName}();
				} else {
					throw new Exception('Illegal action called.');
				}
			}
		}else {
			throw new Exception($methodName." Not Found.");
		}

		$nextAction = $this->_nextAction;
		$this->_nextAction = null;
		return $nextAction;
	}

	/**
	 * Do validation before action calling.
	 * 前置验证器。
	 *
	 * @param Cavy_Controller_Token $action
	 * @return boolen Is there any errors.
	 */
	final private function _doValidation($action) {
		$validator = new Cavy_Controller_Validator(&$this);
		$validation_method = 'validateOn'.ucfirst($action);
		$validate_methods = explode(',',$this->validateOn);
		if (in_array($action,$validate_methods)) {
			$this->validate($validator);
		}
		if (method_exists($this,$validation_method)) {
			$this->$validation_method($validator);
		}
		return (empty($this->_errors) && empty($this->_fieldErrors));
	}

	/**
     * Test whether input errors has happen
     * Errors is checked by 'validate' method or 'validateOnXXX' method
     * 检查是否存在错误，Errors域可以被validate设置。
     */
	final protected function _hasError() {
		return (!empty($this->_errors) || !empty($this->_fieldErrors));
	}

	/**
	 * Get default layout name from $layout property and action name
	 * 从$layout属性，获取布局文件。
	 */    
	final protected function _getLayoutName() {
		if ($this->layout == null || $this->layout == 'no') {
			return null;
		}
		if (is_string($this->layout)) {
			return $this->layout;
		}
		if (is_array($this->layout)) {
			$func = $this->layout['function'];
			if ($func != null) {
				return $this->$func();
			}
			$name = $this->layout['name'];
			$except = $this->layout['except'];
			$only = $this->layout['only'];
			if ($name == null) {
				throw new Exception("Invalid layout");
			}
			if ($except == null && $only == null) {
				return $name;
			}
			// only or except
			if ($except != null) {
				$exceptActions = explode(',',$except);
				if (in_array($this->_action->getActionName(),$exceptActions)) {
					return null;
				}
				return $name;
			} else {
				// only
				$onlyActions = explode(',',$only);
				if (!in_array($this->_action->getActionName(),$onlyActions)) {
					return null;
				}
				return $name;
			}
		} else {
			throw new Exception("Invalid layout");
		}
	}

	/**
     * Gets a parameter that was passed to this controller.  If the
     * parameter does not exist, FALSE will be return.
     *
     * If the parameter does not exist and $default is set, then
     * $default will be returned instead of FALSE.
     *
     * @param string $paramName
     * @param string $default
     * @return boolean
     */
	final protected function _getParam($paramName, $default=null)
	{
		if (array_key_exists($paramName, $this->_params)) {
			return $this->_params[$paramName];
		}

		if ($default===null) {
			return false;
		} else {
			return $default;
		}
	}


	/**
     * Return all parameters that were passed to the controller
     * as an associative array.
     *
     * @return array
     */
	final protected function _getAllParams()
	{
		return $this->_params;
	}

	/**
     * Forward to another controller/action.
     * 转发下一个action，action的名字是不带格式的。
     *
     * @param string $action action name
     * @param string $controller controller name
     * @param array $params parameters
     */
	final protected function _forward($action,$controller=null, $params=array()) {
		if ($controller == null) {
			$controller = $this->_action->getControllerName();
		}
		$this->_nextAction = new Cavy_Controller_Token($controller, $action, $params);
	}

	/**
	 * make url from action/controller/params
	 * 通过action/controller/params 构造一个url。
	 * 
     * @param string $action action
     * @param string $controller controller
     * @param array $params parameters
	 */
	final protected function _urlFor($action=null,$controller=null,$params=array()) {
		if ($action == null) $action = 'index';
		if ($controller == null) $controller = $this->_action->getControllerName();
		$url = Cavy::$ENV['CONTEXT_PATH'].'/'.$controller.'/'.$action;
		foreach ($params as $key => $value) {
			if ($value === null) continue;
			$url.= '/'.$key.'/'.$value;
		}
		return $url;
	}

	/**
     * Redirect to another URL
     * 重定向页面到指定的URL
     * 
     * @param string $action action
     * @param string $controller controller
     * @param array $params parameters
     */
	final protected function _redirect($action,$controller=null,$params=array()) {
		$url = $this->_urlFor($action,$controller,$params);
		if (headers_sent()) {
			throw new Exception('Cannot redirect because headers have already been sent.');
		}

		// prevent header injections
		$url = str_replace(array("\n", "\r"), '', $url);
		// redirect
		header("Location: $url");
		exit();
	}

	/**
	 * render to view
	 * 展示view
	 * 
	 * @param data data array('key' => value) or one object
	 * @param template template name
	 * @param layout layout name
	 */
	final protected function _render($data = array(), $template = null, $layout = null) {
		$view = new Cavy_View($this);
		if ($template == null) {
			$template = $this->_action->getActionName();
		}
		if (!empty($template) && $template[0] == '/') {
			$template = substr($template,0);
		} else {
			$template = $this->_action->getControllerName()
			.'/'.$template;
		}
		$view->data = $data;
		if (is_array($data)) {
			foreach ($data as $key => $value) {
				$view->$key = $value;
			}
		}
		$layoutName = $layout != null ? $layout : $this->_getLayoutName();
		if ($layoutName != null) {
			$view->setContent($template);
			$view->renderLayout($layoutName);
		} else {
			$view->render($template);
		}
		$this->_rendered = true;
	}

	public function validate($validator) {
	}

	/**
	 * set locale to $_SESSION
	 * 设置语言到$_SESSION
	 *
	 * @param unknown_type $locale
	 */
	final protected function _setLocale($locale) {
		$_SESSION['locale.current'] = $locale;
	}

	/**
	 * add error to any field
	 * 将错误添加到指定的域。
	 *
	 * @param string $field
	 * @param string $message
	 */
	public function addFieldError($field, $message=null) {
		if ($message == null) {
			$message = 'invalid.'.$field;
		}
		if ($this->_fieldErrors[$field] == null) {
			$this->_fieldErrors[$field] = array ();
		}
		if (!in_array($message,$this->_fieldErrors[$field])) {
			$this->_fieldErrors[$field][] = $message;
		}
	}

	/**
	 * add error
	 * 添加错误信息
	 *
	 * @param string $message
	 */
	public function addError($message) {
		$this->_errors[] = $message;
	}

	private function _lcfirst($word) {
		if ($word == null) return $word;
		$word[0] = strtolower($word[0]);
		return $word;
	}

	/**
	 * dymatic load model to action_token, when it would be calling
	 * 动态加载model，只有在需要时才实例化。
	 *
	 * @param string $name
	 * @return Cavy_Model|null
	 */
	public function __get($name){
		//动态加载model
		if (in_array(strtolower($name), explode(',',strtolower(str_replace(" ", "", $this->models))))){
			$modelName = $this->_lcfirst(trim($name));
			$model = Cavy::getModel($modelName);
			if (!$model) {
				throw new Cavy_Controller_Action_Exception(
				"Model not found:'$modelName' in '".
				ucfirst($this->_action->getControllerName()) . "Controller'");
			}else {
				return $model;
			}
		}else{
			return null;
		}
	}
}
