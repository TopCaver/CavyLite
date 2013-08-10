<?php
class HelloHelper extends Cavy_View_Helper  {
	public function show_name($name){
		echo "Hello $name";
	}
}