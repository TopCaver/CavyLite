<?php
/**
 * Cavy Lite Framework 
 * 
 * Helper abstract class.
 * Helper is a view helper, help template page to process render and logic
 * 视图层 Helper 虚类
 * Helper是一个视图层辅助工具，用来帮助视图层的渲染和逻辑处理。
 *
 * @author     TopCaver
 * @copyright  Copyright (c) 2013 TopCaver
 * @license    New BSD License
 * @version    $Id$
 */

abstract class Cavy_View_Helper {
	
	protected $_view = null;
		
	/**
	 * Set Cavy_View
	 */
	public function setView($view) {
		$this->_view = $view; 	
	}
	 
    protected function _url($action=null,$controller=null,$params=array()) {
		if ($action == null) $action = $this->_view->getAction()->getActionName();
		if ($controller == null) $controller = $this->_view->getAction()->getControllerName();
		$url = Cavy::$ENV['CONTEXT_PATH'].'/'.$controller.'/'.$action;
		foreach ($params as $key => $value) {
			if ($value === null) continue;
			$url.= '/'.$key.'/'.$value;			
		}
		return $url;		
    }
}
?>