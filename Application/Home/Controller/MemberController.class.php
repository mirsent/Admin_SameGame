<?php
namespace Home\Controller;
use Think\Controller;
class MemberController extends Controller{
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