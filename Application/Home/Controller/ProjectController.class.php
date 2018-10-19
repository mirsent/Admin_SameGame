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
            ->field('id,project_name,datediff(now(), publish_time) as days')
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

    /**
     * 创建项目
     * @param team_uuid 团队uuid
     * @param project_name 项目名字
     * @param project_type_id 项目类型
     * @param project_desc 项目描述
     * @param project_member_ids 项目成员
     * @param project_publisher_id 项目发布人
     */
    public function add_project()
    {
        $res = D('Project')->addData(I('post.'));
        if ($res === false) {
            ajax_return(0, '创建项目出错');
        }

        // 记录
        $data_log = [
            'team_uuid' => I('team_uuid'),
            'project_id' => I('project_id'),
            'operator_id' => I('operator_id'),
            'operate_content' => '创建了项目',
            'operate_type' => C('LOG_P'),
            'object_id' => $res
        ];
        D('Log')->addLog($data_log);

        ajax_return(1, '创建项目成功');
    }

    /**
     * 修改项目
     * @param project_id 项目ID
     */
    public function edit_project()
    {
        $projectId = I('project_id');

        if ($projectId) {
            $project = D('Project');
            $cond['id'] = $projectId;
            $data = I('post.');
            $res = $project->editProject($cond, $data);

            if ($res === false) {
                ajax_return(0, '修改项目信息出错');
            }

            // 记录
            if (I('project_name') != $taskOriginal['project_name']) {
                // 判断项目名称
                $logArr[] = '将项目“'.$taskOriginal['project_name'].'”修改为“'.I('project_name').'”';
            }
            if (I('project_desc') != $taskOriginal['project_desc']) {
                // 判断项目描述
                $logArr[] = '编辑了项目描述';
            }
            if (I('project_member_ids') != $taskOriginal['project_member_ids']) {
                // 判断项目成员
                $member = D('Member');
                $cond_member = [
                    'status' => C('STATUS_Y'),
                    'id'     => array('in', I('project_member_ids'))
                ];
                $members = $member->getMemberFieldName($cond_member);
                $cond_original_member = [
                    'status' => C('STATUS_Y'),
                    'id'     => array('in', $taskOriginal['project_member_ids'])
                ];
                $membersOriginal = $member->getMemberFieldName($cond_original_member);
                $logArr[] = '将项目成员从'.$members.'修改为'.$membersOriginal;
            }
            $log = D('Log');
            foreach ($logArr as $logContent) {
                $data_log = [
                    'team_uuid' => I('team_uuid'),
                    'project_id' => I('project_id'),
                    'operator_id' => I('operator_id'),
                    'operate_content' => $logContent,
                    'operate_type' => C('LOG_P'),
                    'object_id' => $projectId
                ];
                $log->addLog($data_log);
            }

            ajax_return(1, '修改项目信息成功');
        }

        ajax_return(0, '传递参数不合法');
    }

    /**
     * 删除项目
     * @param project_id 项目Id
     * @todo 判断下面是否有任务
     */
    public function delete_project()
    {
        $projectId = I('project_id');

        if ($projectId) {
            $res = D('Project')->deleteProject($projectId);
            if ($res === false) {
                ajax_return(0, '删除项目出错');
            }

            // 记录
            $data_log = [
                'team_uuid' => I('team_uuid'),
                'project_id' => I('project_id'),
                'operator_id' => I('operator_id'),
                'operate_content' => '删除了任务',
                'operate_type' => C('LOG_T'),
                'object_id' => $res
            ];
            D('Log')->addLog($data_log);

            ajax_return(1, '删除项目成功');
        }

        ajax_return(0, '参数不合法');
    }
}
