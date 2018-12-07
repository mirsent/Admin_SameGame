<?php
namespace Home\Controller;
use Think\Controller;
class LoginController extends Controller{
    /**
     * 验证邮箱是否注册
     */
    public function check_email()
    {
        $mail = I('mail');
        $cond = [
            'status' => C('STATUS_Y'),
            'mail'   => $mail
        ];
        $res = M('member')->where($cond)->find();
        if ($res) {
            ajax_return(1, '会员已注册');
        }
        ajax_return(0, '会员未注册');
    }

    /**
     * 验证会员
     */
    public function check_member()
    {
        $cond = [
            'status' => C('STATUS_Y'),
            'id'     => I('member_id')
        ];
        $res = M('member')->where($cond)->find();
        if ($res) {
            ajax_return(1, '允许会员登录');
        }
        ajax_return(0, '不允许会员登录');
    }

    /**
     * 注册会员
     */
    public function register()
    {
        $member = D('Member');
        $member->create();
        $member->member_name = I('mail');
        $member->password = md5(md5(I('password')));
        $res = $member->add();

        if ($res === false) {
            ajax_return(0, '注册会员失败');
        }
        $data = [
            'id'          => $res,
            'member_name' => I('mail')
        ];
        ajax_return(1, '注册会员成功', $data);
    }

    /**
     * 登录
     */
    public function login()
    {
        $cond = [
            'mail'     => I('mail'),
        ];
        $memberInfo = M('member') ->where($cond)->find();

        if (md5(md5(I('password'))) == $memberInfo['password']) {
            ajax_return(1, '登录成功', $memberInfo);
        }
        ajax_return(0, '密码错误');
    }

}
