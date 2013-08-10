<?php
/**
 * Cavy Lite Framework 
 * 
 * Cavy Controller Token
 * 分发令牌
 *
 * @author     TopCaver
 * @copyright  Copyright (c) 2013 TopCaver
 * @license    New BSD License
 * @version    $Id$
 */

class Cavy_Controller_Token
{
    /**
     * Name of the controller to dispatch.
     * controller名字，这是原始controller名字。分发器负责把它格式化。
     * 
     * @var string
     */
    protected $_controller = null;

    /**
     * Name of the action to dispatch.
     * Action的名字，通常是controller的一个方法。
     * 
     * @var string
     */
	protected $_action     = null;

	/**
	 * Array of key/value pairs to pass as parameters to the controller.
	 * 传递给controller的参数数组。
	 * 
	 * @var array
	 */
	protected $_params     = array();


	/**
	 * Class constructor.  A Cavy_Controller_Token object must be built with a controller
	 * name and an action, but parameters are optional.
	 * Cavy_Controller_Token构造函数，controller/action是必须的。
	 *
	 * @param string $controllerName
	 * @param string $actionName
	 * @param array $params
	 */
	public function __construct($controllerName, $actionName, $params=array())
	{
	    $this->_controller = $controllerName;
	    $this->_action     = $actionName;
	    $this->_params     = $params;
	}


	/**
	 * Sets the controller name. 
	 * 设置controller name
	 *
	 * @param string $controllerName
	 * @return Cavy_Controller_Dispatcher_Token
	 */
	public function setControllerName($controllerName)
	{
	    $this->_controller = $controllerName;
	    return $this;
	}


	/**
	 * Returns the controller name, in the raw form.
	 * 获取controller name
	 *
	 * @return string
	 */
	public function getControllerName()
	{
	    return $this->_controller;
	}


	/**
	 * Returns the action name, in the raw form.
	 * 获取action name
	 *
	 * @return string
	 */
	public function getActionName()
	{
	    return $this->_action;
	}


	/**
	 * Sets the action name. 
	 * 设置action name
	 *
	 * @param string $actionName
	 * @return Cavy_Controller_Dispatcher_Token
	 */
	public function setActionName($actionName)
	{
	    $this->_action = $actionName;
	    return $this;
	}


	/**
	 * Get the parameters array.
	 * 获取参数数组
	 *
	 * @return array
	 */
    public function getParams()
    {
       return $this->_params;
    }


    /**
     * Sets the parameters array.
     * 设置参数数组
     *
     * @param string $paramsArray
     * @return Cavy_Controller_Token
     */
    public function setParams($paramsArray)
    {
        if (!is_array($paramsArray)) {
            throw new Exception('Parameters must be set as an array.');
        }
        $this->_params = $paramsArray;
        return $this;
    }
}

