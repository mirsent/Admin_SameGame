<?php
namespace Home\Controller;
use Think\Controller;
class LogController extends Controller{
    /**
     * 获取记录列表
     */
    public function get_log_list(){
        $data = D('Log')->getLogGroupDate();
        ajax_return(1, '记录列表', $data);
    }
}
