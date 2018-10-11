<?php
namespace Admin\Controller;
use Common\Controller\AdminBaseController;
class TeamController extends AdminBaseController{
    /**
     * 根据团队获取项目
     */
    public function get_project_by_team()
    {
        $data = D('Project')->getProjectByTeam(I('team_uuid'));
        ajax_return(1, '获取项目成功', $data);
    }

    /**
     * 根据团队获取成员
     */
    public function get_member_by_team()
    {
        $data = D('Member')->getMemberByTeam(I('team_uuid'));
        ajax_return(1, '获取团队成员成功', $data);
    }

    /**
     * 根据团队获取项目+成员
     */
    public function get_project_member_by_team()
    {
        $data['project'] = D('Project')->getProjectByTeam(I('team_uuid'));
        $data['member'] = D('Member')->getMemberByTeam(I('team_uuid'));
        ajax_return(1, '获取项目成员成功', $data);
    }
}
