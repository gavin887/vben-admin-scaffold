<?php
namespace app\controller;
class Index extends \app\BaseController
{
 public function index() { return json(['code'=>200,'message'=>'success','data'=>['name'=>'ThinkPHP Vben Scaffold','status'=>'ok']]); }
}
