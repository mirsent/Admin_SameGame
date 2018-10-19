<?php
namespace Home\Controller;
use Think\Controller;
class TeamController extends Controller{
    /**
     * 获取团队列表
     * @return array 参与的团队列表
     */
    public function get_team_join_list()
    {
        $cond = [
            'status'  => C('STATUS_Y'),
            '_string' => 'FIND_IN_SET('.I('member_id').', member_ids)'
        ];
        $data = M('team')
            ->where($cond)
            ->field('id,team_uuid,team_name,datediff(now(), found_time) as days')
            ->select();
        ajax_return(1, '参与项目列表', $data);
    }

    /**
     * 新建团队
     * @param team_name 团队名称
     * @param founder_id 创建人ID member_ids 成员【默认创建人】
     */
    public function add_team()
    {
        $team = D('Team');
        $team->create();
        $team->member_ids = I('founder_id');
        $team->team_uuid = keyGen();
        $res = $team->add();

        if ($res === false) {
            ajax_return(0, '新建团队出错');
        }
        ajax_return(1, '新建团队成功');
    }
}
