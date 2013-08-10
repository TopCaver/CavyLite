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

require_once 'Cavy/Controller/Plugin/Abstract.php';

class Cavy_Controller_Plugin_Broker extends Cavy_Controller_Plugin_Abstract
{

    /**
     * Array of instance of objects extends Cavy_Controller_Plugin_Abstract
     * 插件对象数组。插件对象继承自插件虚类。
     * 
     * @var Cavy_Controller_Plugin
     */
    protected $_plugins = array();


    /**
     * Register a plugin.
     * 注册插件
     *
     * @param Cavy_Controller_Plugin $plugin
     * @return Cavy_Controller_Plugin_Broker
     */
    public function registerPlugin($plugin)
    {
        if (array_search($plugin, $this->_plugins, true) == false) {
            $this->_plugins[] = $plugin;
        }        
        return $this;
    }


    /**
     * Unregister a plugin.
     * 注销插件
     *
     * @param Cavy_Controller_Plugin $plugin
     * @return Cavy_Controller_Plugin_Broker
     */
    public function unregisterPlugin($plugin)
    {
        $key = array_search($plugin, $this->_plugins, true);
        if ($key) {
             unset($this->_plugins[$key]);
        }
        return $this;
    }

	/**
	 * Called before an action is dispatched by Cavy_Controller_Dispatcher.
	 * 插件前置拦截器，所有插件的前置拦截器将在分发前被调用。
	 *
	 * @param  Cavy_Controller_Token|boolean $action
	 * @return Cavy_Controller_Token|boolean
	 */
	public function preDispatch($action)
	{
	    foreach ($this->_plugins as $plugin) {
	        $action = $plugin->preDispatch($action);
	    }
	    return $action;
	}


	/**
	 * Called after an action is dispatched by Cavy_Controller_Dispatcher.
	 * 插件后置拦截器，所有插件的此方法将在分发后被调用。
	 *
	 * @param  Cavy_Controller_Token|boolean $action
	 * @return Cavy_Controller_Token|boolean
	 */
	public function postDispatch($action)
	{
	    foreach ($this->_plugins as $plugin) {
	        $action = $plugin->postDispatch($action);
	    }
	    return $action;
	}
}