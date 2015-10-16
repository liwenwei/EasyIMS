<?php
namespace Admin\Controller;
use Think\Controller;
class ErrorController extends Controller {
	public function errorPage(){
		$this->display("errorPage");
	}
}