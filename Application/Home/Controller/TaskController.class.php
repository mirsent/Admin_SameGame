<?php
namespace Home\Controller;
use Think\Controller;
class TaskController extends Controller {
    /**
     * 按团队 获取今日任务列表（完成、未完成）
     * 截止时间倒序
     * @param int team_uuid 团队uuid
     * @param task_executive_id 执行人ID
     */
    public function get_task_today_list()
    {
        $today = date('Y-m-d');
        $cond = [
            't.status'          => array('neq', C('STATUS_N')),
            't.team_uuid'       => I('team_uuid'),
            'task_executive_id' => I('task_executive_id'),
            'deadline_time'     => array('between', [$today.' 00:00:01', $today.' 23:59:59'])
        ];
        $data = M('task')
            ->alias('t')
            ->join('__PROJECT__ p ON p.id = t.project_id')
            ->field('t.id,task_name,task_desc,difficult,deadline_time,t.status,project_name')
            ->order('deadline_time desc')
            ->where($cond)
            ->select();
        ajax_return(1, '今日任务列表', $data);
    }

    /**
     * 获取未来任务（未完成）
     * @param team_uuid 团队uuid
     * @param task_executive_id 执行人ID
     */
    public function get_task_future_list()
    {
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $cond = [
            't.status'          => C('STATUS_Y'),
            't.team_uuid'       => I('team_uuid'),
            'task_executive_id' => I('task_executive_id'),
            'deadline_time'     => array('gt', $tomorrow)
        ];
        $data = M('task')
            ->alias('t')
            ->join('__PROJECT__ p ON p.id = t.project_id')
            ->field('id,project_name,task_name')
            ->order('deadline_time desc')
            ->where($cond)
            ->select();
        ajax_return(1, '未来任务列表', $data);
    }

    /**
     * 获取任务详情
     * @param task_id 任务ID
     */
    public function get_task_detail()
    {
        $taskId = I('task_id');
        if ($taskId) {
            $data = D('Task')->getTaskDetail($taskId);
            ajax_return(1, '任务详情', $data);
        }
        ajax_return(0, '参数不合法');
    }

    /**
     * 发布任务
     * @param team_uuid 团队uuid
     * @param project_id 项目ID
     * @param task_name 任务名字
     * @param task_executive_id 任务执行人ID
     * @param deadline_time 截止时间
     * @param task_publisher_id 任务发布人
     */
    public function add_task()
    {
        $task = M('task');
        $task->create();
        $task->publish_time = date('Y-m-d H:i:s');
        $res = $task->add();

        if ($res === false) {
            ajax_return(0, '发布任务出错');
        }

        // 记录
        $data_log = [
            'team_uuid' => I('team_uuid'),
            'project_id' => I('project_id'),
            'operator_id' => I('operator_id'),
            'operate_content' => '创建了任务',
            'operate_type' => C('LOG_T'),
            'object_id' => $res
        ];
        D('Log')->addLog($data_log);

        ajax_return(1);
    }

    /**
     * 修改任务
     * @param task_id 任务ID
     */
    public function edit_task()
    {
        $taskId = I('task_id');

        if ($taskId) {
            $cond['id'] = $taskId;
            $task = M('task');

            $taskOriginal = $task->where($cond)->find(); // 任务原始信息

            $task->create();
            $res = $task->where($cond)->save();

            if ($res === false) {
                ajax_return(0, '修改任务出错');
            }

            // 记录
            if (I('task_name') != $taskOriginal['task_name']) {
                // 判断任务名称
                $logArr[] = '将任务“'.$taskOriginal['task_name'].'”修改为“'.I('task_name').'”';
            }
            if (I('task_desc') != $taskOriginal['task_desc']) {
                // 判断任务描述
                $logArr[] = '编辑了任务描述';
            }
            if (I('deadline_time') != $taskOriginal['deadline_time']) {
                // 判断任务完成时间
                $logArr[] = '将任务完成时间从“'.$taskOriginal['deadline_time'].'”修改为“'.I('deadline_time').'”';
            }
            if (I('task_executive_id') != $taskOriginal['task_executive_id']) {
                // 判断任务执行人
                $member = D('Member');
                $executive = $member->getMemberById(I('task_executive_id'));
                $executiveOriginal = $member->getMemberById($taskOriginal['task_executive_id']);
                $logArr[] = '将'.$executive.'的任务指派给了'.$executiveOriginal;
            }

            $log = D('Log');
            foreach ($logArr as $logContent) {
                $data_log = [
                    'team_uuid' => I('team_uuid'),
                    'project_id' => I('project_id'),
                    'operator_id' => I('operator_id'),
                    'operate_content' => $logContent,
                    'operate_type' => C('LOG_T'),
                    'object_id' => $taskId
                ];
                $log->addLog($data_log);
            }

            ajax_return(1, '修改任务成功');
        }

        ajax_return(0, '参数不合法');
    }

    /**
     * 删除任务
     * @param task_id 任务ID
     */
    public function delete_task()
    {
        $taskId = I('task_id');

        if ($taskId) {
            $res = D('Task')->deleteTask($taskId);
            if ($res === false) {
                ajax_return(0, '删除任务出错');
            }

            // 记录
            $data_log = [
                'team_uuid' => I('team_uuid'),
                'project_id' => I('project_id'),
                'operator_id' => I('operator_id'),
                'operate_content' => '删除了任务',
                'operate_type' => C('LOG_T'),
                'object_id' => $taskId
            ];
            D('Log')->addLog($data_log);

            ajax_return(1, '删除任务成功');
        }

        ajax_return(0, '参数不合法');
    }
}
