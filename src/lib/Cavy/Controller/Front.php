<?php
/**
 * Cavy Lite Framework 
 * 
 * Cavy Front Controller
 * 前端控制器
 *
 * @author     TopCaver
 * @copyright  Copyright (c) 2013 TopCaver
 * @license    New BSD License
 * @version    $Id$
 */

require_once 'Cavy/Controller/Plugin/Broker.php';
require_once 'Cavy/Controller/Token.php';

class Cavy_Controller_Front
{
	/**
     * Instance of Cavy_Controller_Front
     * Cavy_Controller_Front 实例
     * 
     * @var Cavy_Controller_Front
     */
	static private $_instance = null;

	/**
     * Instance of Cavy_Controller_Router
     * 路由器实例
     * 
     * @var Cavy_Controller_Router
     */
	private $_router = null;

	/**
     * Instance of Cavy_Controller_Dispatcher
     * 分发器实例
     * 
     * @var Cavy_Controller_Dispatcher
     */
	private $_dispatcher = null;

	/**
     * Instance of Cavy_Controller_Plugin_Broker
     * 插件Broker实例
     * 
     * @var Cavy_Controller_Plugin_Broker
     */
	private $_plugins = null;


	/**
	 * Singleton pattern
	 * Instantiate the plugin broker.
	 * 
	 * 单例模式，插件代理实例。
	 */
	private function __construct()
	{
		$this->_plugins = new Cavy_Controller_Plugin_Broker();
	}

	/**
	 * Singleton pattern
	 * 单例模式
	 */
	private function __clone()
	{}


	/**
	 * Return one and only one instance of the Cavy_Controller_Front object
	 * 只返回一个前端控制器实例。
	 *
	 * @return Cavy_Controller_Front
	 */
	static public function getInstance()
	{
		if (!self::$_instance instanceof self) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	
	/**
	 * Evalute URI to controller/action
	 * 根据URI计算对应的controller/action
	 *
	 * @return Cavy_Controller_Dispatcher_Token
	 */
	public function route(){
		$path = substr($_SERVER['REQUEST_URI'],strlen(Cavy::$ENV['CONTEXT_PATH']));
		if (strstr($path, '?')) {
			$path = substr($path, 0, strpos($path, '?'));
		}
		$path = explode('/', trim($path, '/'));

       	// The controller/action are always the first two piece of the URI.
		// controller是URI的第一个参数，action是第二个。这是一个固定的格式。
		$controller = $path[0];
		$action     = isset($path[1]) ? $path[1] : null;

        // If no controller has been set, IndexController::index() will be used.
        // 无controller/action，将使用IndexController::index()做默认值。
		if (!strlen($controller)) {
			$controller = 'index';
		}
		if ($action == null) {
			$action = 'index';
		}

		// Any parameters after the action are stored in an array of key/value pairs
        // action之后的URL将解析为参数对
		$params = array();
		for ($i=2; $i<sizeof($path); $i=$i+2) {
			$params[$path[$i]] = isset($path[$i+1]) ? $path[$i+1] : null;
		}
		if ($_SERVER['REQUEST_METHOD'] == 'GET') {
			$params = array_merge($params,$_GET);
		}
		else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$params = array_merge($params,$_POST);
		}
		$actionObj = new Cavy_Controller_Token($controller, $action, $params);
		return $actionObj;
	}

	/**
	 * Return the dispatcher object.
	 * 实例化一个分发器实例。
	 *
	 * @return Cavy_Controller_Dispatcher
	 */
	public function getDispatcher()
	{
	    // Instantiate the default dispatcher if one was not set.
	    // 实例化一个默认分发器。
		if (!$this->_dispatcher instanceof Cavy_Controller_Dispatcher) {
			require_once 'Cavy/Controller/Dispatcher.php';
			$this->_dispatcher = new Cavy_Controller_Dispatcher();
		}
		return $this->_dispatcher;
	}


	/**
	 * Register a plugin.
	 * 注册插件
	 *
	 * @param Cavy_Controller_Plugin
	 * @return Cavy_Controller_Front
	 */
	public function registerPlugin($plugin)
	{
		$this->_plugins->registerPlugin($plugin);
		return $this;
	}


	/**
	 * Unregister a plugin.
	 * 注销插件
	 *
	 * @param Cavy_Controller_Plugin
	 * @return Cavy_Controller_Front
	 */
	public function unregisterPlugin($plugin)
	{
		$this->_plugins->unregisterPlugin($plugin);
		return $this;
	}


	/**
	 * Dispatch an HTTP request to a controller/action.
	 * 分发HTTP请求到 controller/action
	 */
	public function dispatch()
	{
		// Route a URI to a controller/action.
		// 路由URI请求到对应的controller/action。
		$action = $this->route();

		// Attempt to dispatch to the controller/action.
		// 分发到controller/action
		while ($action instanceof Cavy_Controller_Token) {
			// notify plugins that a dispatch is about to occur
			// 通知所有插件，分发过程马上开始。
			$action = $this->_plugins->preDispatch($action);

			// Dispatch
			// 核心分发过程
			$action = $this->getDispatcher()->dispatch($action);

			// notify plugins that the dispatch has finish
			// 通知所有插件，分发过程已经结束。
			$action = $this->_plugins->postDispatch($action);
		}
	}
}