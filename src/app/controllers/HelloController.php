<?php
class HelloController extends Cavy_Controller_Action {
	public $models = 'Hello';
	public function index(){
		$input = $this->_params['name'];
		$result = $this->Hello->show_name($input);
		$this->_render(array("name"=>$result));
	}
}