<?php
namespace Home\Controller;
use Think\Controller;
class ProjectController extends Controller {
    /**
     * 获取参与项目列表
     * @param member_id 会员ID
     */
    public function get_project_join_list()
    {
        $cond = [
            'status'  => C('STATUS_Y'),
            '_string' => 'FIND_IN_SET('.I('member_id').', project_member_ids)'
        ];
        $data = M('project')
            ->where($cond)
            ->field('id,project_name')
            ->select();
        ajax_return(1, '参与项目列表', $data);
    }

    /**
     * 获取项目详情
     * @param project_id 项目ID
     */
    public function get_project_detail()
    {
        $projectId = I('project_id');
        if ($projectId) {
            $data = D('Project')->getProjectDetail($projectId);
            ajax_return(1, '项目详情', $data);
        }
        ajax_return(0, '参数不合法');
    }
}
