<?php
//芸众商城 QQ:913768135
if (!defined('IN_IA')) {
    exit('Access Denied');
}
class VerifyMobile extends Plugin
{
    public function __construct()
    {
        parent::__construct('verify');
    }
    public function check()
    {
        return $this->_exec_plugin(__FUNCTION__, false);
    }
    public function complete()
    {
        return $this->_exec_plugin(__FUNCTION__, false);
    }
    public function qrcode()
    {
        return $this->_exec_plugin(__FUNCTION__, false);
    }
    public function detail()
    {
        return $this->_exec_plugin(__FUNCTION__, false);
    }
    public function index()
    {
        $this->_exec_plugin(__FUNCTION__, false);
    }
    public function mystore()
    {
        $this->_exec_plugin(__FUNCTION__, false);
    }
    public function add()
    {
        $this->_exec_plugin(__FUNCTION__, false);
    }
    public function order()
    {
        $this->_exec_plugin(__FUNCTION__, false);
    }
    public function withdraw()
    {
        $this->_exec_plugin(__FUNCTION__, false);
    }
    public function log()
    {
        $this->_exec_plugin(__FUNCTION__, false);
    }
    public function my_pocket()
    {
        $this->_exec_plugin(__FUNCTION__, false);
    }
    public function ranking()
    {
        $this->_exec_plugin(__FUNCTION__, false);
    }
    public function select_category()
    {
        $this->_exec_plugin(__FUNCTION__, false);
    }
    public function select_goods()
    {
        $this->_exec_plugin(__FUNCTION__, false);
    }
}