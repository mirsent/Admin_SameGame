<?php
namespace Common\Model;
use Common\Model\BaseModel;
class TaskModel extends BaseModel{

    protected $_auto=array(
        array('status','get_default_status',1,'callback')
    );

    /**
     * 获取任务数量
     */
    public function getTaskNumber($cond=[])
    {
        $data = $this
            ->alias('t')
            ->join('__PROJECT__ p ON p.id = t.project_id','LEFT')
            ->join('__MEMBER__ executive ON executive.id = t.task_executive_id') // 执行人
            ->join('__MEMBER__ publisher ON publisher.id = t.task_publisher_id') // 发布人
            ->where($cond)
            ->count();
        return $data;
    }

    /**
     * 获取任务列表
     */
    public function getTaskData($cond=[])
    {
        $data = $this
            ->alias('t')
            ->join('__PROJECT__ p ON p.id = t.project_id', 'LEFT')
            ->join('__MEMBER__ executive ON executive.id = t.task_executive_id', 'LEFT') // 执行人
            ->join('__MEMBER__ publisher ON publisher.id = t.task_publisher_id') // 发布人
            ->field('t.*,project_name,executive.member_name as task_executive,publisher.member_name as task_publisher')
            ->where($cond)
            ->select();
        return $data;
    }

    /**
     * 获取任务详情列表
     * @return show 是否展开
     * @return is_finish 是否完成
     * @return is_delay 是否延期
     */
    public function getTaskList($cond)
    {
        $data = $this
            ->alias('t')
            ->join('__PROJECT__ p ON p.id = t.project_id', 'LEFT')
            ->join('__MEMBER__ executive ON executive.id = t.task_executive_id', 'LEFT') // 执行人
            ->field('t.id,task_name,task_desc,difficult,deadline_time,DATE_FORMAT(deadline_time,"%m/%d") as deadline_date,complete_date,t.status,project_name,executive.member_name as executive')
            ->order('t.status,deadline_time desc')
            ->where($cond)
            ->select();

        foreach ($data as $key => $value) {
            $data[$key]['is_delay'] = 0;

            // 第一个默认展开
            if ($key == 0) {
                $data[$key]['show'] = true;
            } else {
                $data[$key]['show'] = false;
            }

            if ($value['status'] == C('TASK_F')) {
                // 已完成
                $data[$key]['is_finish'] = true;
                if (strtotime($value['complete_date']) > strtotime($value['deadline_time'])) {
                    $data[$key]['is_delay'] = 1;
                }
            } else {
                $data[$key]['is_finish'] = false;
                if (strtotime(date('Y-m-d')) > strtotime($value['deadline_time'])) {
                    $data[$key]['is_delay'] = 1;
                }
            }
        }

        return $data;
    }

    /**
     * 获取任务详情
     */
    public function getTaskDetail($taskId)
    {
        $cond['t.id'] = $taskId;
        $data = $this
            ->alias('t')
            ->join('__PROJECT__ p ON p.id = t.project_id')
            ->join('__MEMBER__ executive ON executive.id = t.task_executive_id') // 执行人
            ->join('__MEMBER__ publisher ON publisher.id = t.task_publisher_id') // 发布人
            ->field('t.*,project_name,executive.member_name as task_executive,publisher.member_name as task_publisher')
            ->where($cond)
            ->find();
        return $data;
    }

    /**
     * 编辑任务
     */
    public function editTask($cond, $data)
    {
        if (!$data = $this->create($data)) {
            return false;
        }
        $res = $this
            ->where($cond)
            ->save($data);
        return $res;
    }

    /**
     * 删除任务
     */
    public function deleteTask($taskId)
    {
        $cond['id'] = $taskId;
        $data['status'] = C('STATUS_N');
        $res = $this
            ->where($cond)
            ->save($data);
        return $res;
    }
}
