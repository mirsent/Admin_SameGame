<?php
namespace Home\Controller;
use Think\Controller;
class TaskController extends Controller {

    /**
     * 按团队 获取任务列表（完成、未完成）
     * 截止时间倒序
     * @param int team_uuid 团队uuid
     * @param task_executive_id 执行人ID
     * @return show 是否展开
     * @return is_finish 是否完成
     * @return number_all 任务总数量
     * @return number_complete 任务完成数量
     * @return rate 任务完成百分比
     */
    public function get_task_list()
    {
        $task = D('Task');

        $today = date('Y-m-d');
        $cond_today = $cond_other = [
            't.status'          => array('neq', C('STATUS_N')),
            't.team_uuid'       => I('team_uuid'),
            'task_executive_id' => I('task_executive_id'),
        ];
        $cond_today['deadline_time'] = $today;
        $cond_other['deadline_time'] = array('neq', $today);

        // 已完成
        $status = C('TASK_F');

        // 今日列表相关
        $todayList = $task->getTaskList($cond_today);
        $todayAllN = count($todayList);
        $todayCompleteN = array_count_values(array_column($todayList, 'status'))[$status]?:0;
        $todayRate = intval($todayCompleteN/$todayAllN*100);

        $otherList = $task->getTaskList($cond_other);
        $otherAllN = count($otherList);
        $otherCompleteN = array_count_values(array_column($otherList, 'status'))[$status]?:0;
        $otherRate = intval($otherCompleteN/$otherAllN*100);

        $data = [
            'today' => [
                'list'            => $todayList,
                'number_all'      => $todayAllN,
                'number_complete' => $todayCompleteN,
                'rate'            => $todayRate
            ],
            'other' => [
                'list'            => $otherList,
                'number_all'      => $otherAllN,
                'number_complete' => $otherCompleteN,
                'rate'            => $otherRate
            ],
        ];

        ajax_return(1, '任务列表', $data);
    }



    /**
     * 按团队 获取今日任务列表（完成、未完成）
     * 截止时间倒序
     * @param int team_uuid 团队uuid
     * @param task_executive_id 执行人ID
     * @return show 是否展开
     * @return is_finish 是否完成
     * @return number_all 任务总数量
     * @return number_complete 任务完成数量
     * @return rate 任务完成百分比
     */
    public function get_task_today_list()
    {
        $today = date('Y-m-d');
        $cond = [
            't.status'          => array('neq', C('STATUS_N')),
            't.team_uuid'       => I('team_uuid'),
            'task_executive_id' => I('task_executive_id'),
            'deadline_time'     => $today
        ];
        $list = D('Task')->getTaskList($cond);

        $number_all = count($list); // 总任务数量
        $status = C('TASK_F');
        $number_complete = array_count_values(array_column($list, 'status'))[$status]?:0;
        $rate = intval($number_complete/$number_all*100);

        $data = [
            'list'            => $list,
            'number_all'      => $number_all,
            'number_complete' => $number_complete,
            'rate'            => $rate
        ];

        ajax_return(1, '今日任务列表', $data);
    }

    /**
     * 获取其他任务（未完成）
     * @param team_uuid 团队uuid
     * @param task_executive_id 执行人ID
     * @return show 是否展开
     * @return is_finish 是否完成
     * @return number_all 任务总数量
     * @return number_complete 任务完成数量
     * @return rate 任务完成百分比
     */
    public function get_task_other_list()
    {
        $today = date('Y-m-d');
        $cond = [
            't.status'          => array('neq', C('STATUS_N')),
            't.team_uuid'       => I('team_uuid'),
            'task_executive_id' => I('task_executive_id'),
            'deadline_time'     => array('neq', $today)
        ];
        $list = D('Task')->getTaskList($cond);

        $number_all = count($list); // 总任务数量
        $status = C('TASK_F');
        $number_complete = array_count_values(array_column($list, 'status'))[$status]?:0;
        $rate = intval($number_complete/$number_all*100);

        $data = [
            'list'            => $list,
            'number_all'      => $number_all,
            'number_complete' => $number_complete,
            'rate'            => $rate
        ];

        ajax_return(1, '其他任务列表', $data);
    }

    /**
     * 获取任务大厅所有任务
     * @param type 1：今日任务 2：其他
     * @param team_uuid 团队uuid
     */
    public function get_task_hall()
    {
        $cond = [
            't.status'    => array('neq', C('STATUS_N')),
            't.team_uuid' => I('team_uuid'),
        ];

        $type = I('type');
        if ($type) {
            switch ($type) {
                case '1':
                    $cond['deadline_time'] = date('Y-m-d');
                    break;

                case '2':
                    $cond['deadline_time'] = array('neq', date('Y-m-d'));
                    break;

                default:
                    break;
            }
        }

        $data = D('Task')->getTaskList($cond);

        ajax_return(1, '任务大厅列表', $data);
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
     * @param task_desc 任务描述
     * @param task_executive_id 任务执行人ID
     * @param deadline_time 截止时间
     * @param task_publisher_id 任务发布人
     */
    public function add_task()
    {
        $task = D('Task');
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
            'operator_id' => I('task_publisher_id'),
            'operate_content' => '创建了任务',
            'operate_type' => C('LOG_T'),
            'object_id' => $res
        ];
        D('Log')->addLog($data_log);

        ajax_return(1);
    }

    /**
     * 领取任务
     * @param member_id 领取人ID
     * @param task_id 任务ID
     */
    public function receive_task()
    {
        $cond['id'] = I('task_id');
        $data['task_executive_id'] = I('member_id');
        $res = M('task')->where($cond)->save($data);

        if ($res === false) {
            ajax_return(0, '任务跑丢了，请稍后重试');
        }
        ajax_return(1, '领取成功');
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
                    'operator_id' => I('task_publisher_id'),
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

    /**
     * 完成任务
     * @param task_id 任务ID
     */
    public function complete_task()
    {
        $cond['id'] = I('task_id');
        $data = [
            'status'        => C('TASK_F'),
            'complete_time' => date('Y-m-d H:i:s'),
            'complete_date' => date('Y-m-d')
        ];
        $res = M('task')->where($cond)->save($data);
        if ($res === false) {
            ajax_return(0, '完成任务失败');
        }
        ajax_return(1, '完成任务成功');
    }

    /**
     * 取消完成任务
     * @param task_id 任务ID
     */
    public function cancel_task()
    {
        $cond['id'] = I('task_id');
        $data = [
            'status'        => C('TASK_I'),
            'complete_time' => null
        ];
        $res = M('task')->where($cond)->save($data);

        if ($res === false) {
            ajax_return(0, '取消完成任务失败');
        }
        ajax_return(1, '取消完成任务成功');
    }
}
