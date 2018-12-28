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
            $data['openid'] = I('openid');
            M('member')->where($cond)->save($data);
            ajax_return(1, '登录成功', $memberInfo);
        }
        ajax_return(0, '密码错误');
    }

    /**
     * 登录凭证校验
     * @param js_code 登录凭证code
     */
    public function code_2_session()
    {
        $appid = C('WX_CONFIG.APPID');
        $secret = C('WX_CONFIG.APPSECRET');
        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid='.$appid.'&secret='.$secret.'&js_code='.I('js_code').'&grant_type=authorization_code';
        $info = file_get_contents($url);
        $json = json_decode($info, true);

        $openid = $json['openid'];

        $cond = [
            'status' => C('STATUS_Y'),
            'openid' => $openid
        ];
        $memberInfo = M('member')->where($cond)->find();

        $data = [
            'openid' => $openid,
            'member' => $memberInfo
        ];

        ajax_return(1, '凭证校验', $data);
    }

    /**
     * 获取公告信息
     * @param team_uuid
     */
    public function get_notice()
    {
        $cond = [
            'team_uuid'   => I('team_uuid'),
            'expire_time' => array('gt', date('Y-m-d')),
            'status'      => C('STATUS_Y')
        ];
        $data = M('notice')->where($cond)->order('notice_time desc')->limit(1)->find();

        ajax_return(1, '公告信息', $data);
    }

}
