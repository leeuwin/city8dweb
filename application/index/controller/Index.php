<?php

namespace app\index\controller;


use think\Controller;

class Index extends Controller
{

    public function index(){
        header("HTTP/1.1 404 Not Found");
        header("Status: 404 Not Found");
        exit;
    }
}