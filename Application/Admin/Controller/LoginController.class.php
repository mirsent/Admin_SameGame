<?php
namespace Admin\Controller;
use Think\Controller;
class LoginController extends Controller{
    /**
     * 登录验证
     */
    public function check_user(){
        $cond = [
            'status'    => C('STATUS_Y'),
            'user_name' => I('user_name')
        ];
        $user = M('user')->where($cond)->find();
        if ($user) {
            echo "true";
        } else {
            echo "false";
        }
    }
    public function check_psw(){
        $userPsw = M('user')->getfieldByJobNumber(I('user_name'), 'user_psw');
        if ($userPsw == md5(I('user_psw'))) {
            echo "true";
        } else {
            echo "false";
        }
    }

    /**
     * 登录
     */
    public function login(){
        $cond['user_name'] = I('user_name');
        $user = M('user')
            ->where($cond)
            ->find();
        if ($user === false) {
            ajax_return(0, "登录出错");
        }
        session(C('USER_AUTH_KEY'), $user);
        ajax_return(1);
    }

    /**
     * 登出
     */
    public function logout(){
        session(C('USER_AUTH_KEY'), null);
        $this->redirect(C('USER_AUTH_GATEWAY'));
    }
}
