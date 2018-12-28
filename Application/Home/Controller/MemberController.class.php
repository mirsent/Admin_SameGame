<?php
namespace Home\Controller;
use Think\Controller;
class MemberController extends Controller{

    /**
     * 修改会员信息
     * @param member_id 会员ID
     */
    public function edit_member()
    {
        $cond['id'] = I('member_id');
        $member = D('Member');
        $member->create();
        $res = $member->where($cond)->save();

        if ($res === false) {
            ajax_return(0, '修改会员信息失败');
        }
        $memberInfo = $member->where($cond)->find();
        ajax_return(1, '修改会员信息成功', $memberInfo);
    }

    /**
     * 根据团队获取成员
     * @param team_uuid 团队uuid
     */
    public function get_members_by_team()
    {
        $teamuuid = I('team_uuid');
        $members = D('Member')->getMemberByTeam($teamuuid);
        foreach ($members as $member) {
            $data[] = [
                'value' => $member['id'],
                'text'  => $member['member_name']
            ];
        }
        ajax_return(1, '团队成员列表', $data);
    }
}