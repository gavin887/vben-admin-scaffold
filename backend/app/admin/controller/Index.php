<?php
namespace app\admin\controller;
class Index extends Controller { public function index() { return $this->renderSuccess(['name'=>'管理后台']); } }
