<?php
namespace Home\Controller;
use Think\Controller;
class TaskController extends Controller {
    /**
     * 获取今日任务列表（完成、未完成）
     * 按项目分组
     * @param task_executive_id 执行人ID
     */
    public function get_task_today_list()
    {
        $today = date('Y-m-d');
        $cond = [
            'status'            => array('neq', C('STATUS_N')),
            'task_executive_id' => I('task_executive_id'),
            'deadline_time'     => array('between', [$today.' 00:00:01', $today.' 23:59:59'])
        ];
        $data = M('task')
            ->alias('t')
            ->join('__PROJECT__ p ON p.id = t.project_id')
            ->field('id,project_name,task_name')
            ->group('project_id')
            ->where($cond)
            ->select();
        ajax_return(1, '今日任务列表', $data);
    }

    /**
     * 获取未来任务（未完成）
     * 按项目分组
     * @param task_executive_id 执行人ID
     */
    public function get_task_future_list()
    {
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $cond = [
            'status'            => C('STATUS_Y'),
            'task_executive_id' => I('task_executive_id'),
            'deadline_time'     => array('gt', $tomorrow)
        ];
        $data = M('task')
            ->alias('t')
            ->join('__PROJECT__ p ON p.id = t.project_id')
            ->field('id,project_name,task_name')
            ->group('project_id')
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
            $task->create();
            $res = $task->where($cond)->save();

            if ($res === false) {
                ajax_return(0, '修改任务出错');
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
            ajax_return(1, '删除任务成功');
        }

        ajax_return(0, '参数不合法');
    }
}
