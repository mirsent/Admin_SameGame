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
        $data = I('post.');
        $res = D('Team')->addTeam($data);

        if ($res === false) {
            ajax_return(0, '新建团队出错');
        }
        ajax_return(1, '新建团队成功');
    }

    /**
     * 加入团队
     * @param member_id 加入人
     * @param team_no 团队码
     */
    public function join_team()
    {

        $teamNo = I('team_no');
        $memberId = I('member_id');

        $team = M('team');
        $cond = [
            'status'  => C('STATUS_Y'),
            'team_no' => $teamNo
        ];

        // 验证团队码
        $is_exist = $team->where($cond)->find();

        if (!$is_exist) {
            ajax_return(0, '城堡编号不存在...');
        }

        // 查看是否已在此团队
        $cond_in = [
            'status'  => C('STATUS_Y'),
            'team_no' => $teamNo,
            '_string' => 'FIND_IN_SET('.$memberId.', member_ids)'
        ];
        $is_in = $team->where($cond_in)->find();

        if ($is_in) {
            ajax_return(0, '您已入驻此城堡...');
        }

        $data['member_ids'] = $is_exist['member_ids'].','.$memberId;
        $res = $team->where($cond)->save($data);

        if ($res === false) {
            ajax_return(0, '城堡正忙，休息一会再来');
        }
        ajax_return(1, '加入城堡成功');
    }
}
