<?php
/**
 * Cavy Lite Framework 
 * 
 * Plugin Abstract.
 * 插件基类
 *
 * @author     TopCaver
 * @copyright  Copyright (c) 2013 TopCaver
 * @license    New BSD License
 * @version    $Id$
 */

abstract class Cavy_Controller_Plugin_Abstract 
{
	
	/**
	 * Called before an action is dispatched by Cavy_Controller_Dispatcher.
	 * 插件前置拦截器，分发开始前将被调用。
	 *
	 * @param  Cavy_Controller_Token|boolean $action
	 * @return Cavy_Controller_Token|boolean
	 */
	public function preDispatch($action)
	{
	    return $action;
	}

	/**
	 * Called after an action is dispatched by Cavy_Controller_Dispatcher.
	 * 插件后置拦截器，分发后将被调用。
	 *
	 * @param  Cavy_Controller_Token|boolean $action
	 * @return Cavy_Controller_Token|boolean
	 */
	public function postDispatch($action)
	{
	    return $action;
	}
}