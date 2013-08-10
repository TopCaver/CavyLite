<?php
/**
 * Cavy Lite Framework 
 * 
 * Cavy Dispatcher
 * 核心分发器
 *
 * @author     TopCaver
 * @copyright  Copyright (c) 2013 TopCaver
 * @license    New BSD License
 * @version    $Id$
 */

Cavy::loadClass('Cavy_Controller_Action');

class Cavy_Controller_Dispatcher
{
	/**
     * Formats a string into a controller name.
     * 格式化controller name
     *
     * @param string $unformatted
     * @return string
     */
	public function formatControllerName($unformatted){
		return ucfirst($this->_formatName($unformatted)) . 'Controller';
	}

	/**
     * Formats a string into an action name.
     * 格式化action name
     *
     * @param string $unformatted
     * @return string
     */
	public function formatActionName($unformatted)
	{
		$formatted = $this->_formatName($unformatted);
		return strtolower(substr($formatted, 0, 1)) . substr($formatted, 1);
	}


	/**
     * Formats a string from a URI into a PHP-friendly name.
     * 格式化URI字符串到PHP友好的字符。
     *
     * @param string $unformatted
     * @return string
     */
	protected function _formatName($unformatted)
	{
		$unformatted = str_replace(array('-', '_', '.'), ' ', strtolower($unformatted));
		$unformatted = preg_replace('[^a-z0-9 ]', '', $unformatted);
		return str_replace(' ', '', ucwords($unformatted));
	}

	/**
	 * Dispatch.
	 * 分发。
	 *
	 * @param Cavy_Controller_Token $action
	 * @return boolean|Cavy_Controller_Token
	 */
	public function dispatch(Cavy_Controller_Token $action)
	{
		$className  = $this->formatControllerName($action->getControllerName());

		Cavy::loadController($action->getControllerName());
		$controller = new $className();

		if ($controller instanceof Cavy_Controller_Action) {
			$controller->run($this,$action);
		}
		return null;
	}
}
