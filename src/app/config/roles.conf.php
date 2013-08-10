<?php
$roles = array(	
	'ADMIN'=>array(	    
		'permit'=>array('/.*$/'), // 允许访问哪些URL的正则表达式
		'deny'=>array() // 禁止访问哪些URL的正则表达式
	)
);
?>