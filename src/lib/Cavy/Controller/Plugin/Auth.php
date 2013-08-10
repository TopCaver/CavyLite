<?php
/**
 * Cavy Lite Framework 
 * 
 * Authentication and Access control plugin
 * 认证和访问控制插件
 *
 * @author     TopCaver
 * @copyright  Copyright (c) 2013 TopCaver
 * @license    New BSD License
 * @version    $Id$
 */

class Cavy_Controller_Plugin_Auth extends Cavy_Controller_Plugin_Abstract {

	public function preDispatch($action) {
		if (self::_doAuth()) {
			self::_doAccessControl($action);
		}
		return $action;
	}

	private function _doAuth() {
		$uri = Cavy::$ENV['REQUEST_URI'];
		$loginpage = '/user/requireLogin';
		if ($uri == $loginpage || $uri == '/user/login' || $uri == '/user/logout') {
			return false;
		}
		if (!isset($_SESSION['login.user'])) {
			$_SESSION['auth.last.uri'] = $uri;
			Header('Location:' . Cavy::$ENV['CONTEXT_PATH'].$loginpage);
			exit();
		}
		return true;
	}
	
	private function _doAccessControl(&$action) {
		if (!isset($_SESSION['login.user'])) {
			return;
		}
		$user = $_SESSION['login.user'];
		if (!Cavy::getModel('user')->hasPermission($user['roles'],Cavy::$ENV['REQUEST_URI'])) {
			$action->setControllerName("user");
			$action->setActionName("authFailed");
		}
	}
}
?>
