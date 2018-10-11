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
    public function getTaskNumber($cond='')
    {
        $data = $this
            ->alias('t')
            ->join('__PROJECT__ p ON p.id = t.project_id')
            ->join('__MEMBER__ executive ON executive.id = t.task_executive_id') // 执行人
            ->join('__MEMBER__ publisher ON publisher.id = t.task_publisher_id') // 发布人
            ->where($cond)
            ->count();
        return $data;
    }

    /**
     * 获取任务列表
     */
    public function getTaskData($cond='')
    {
        $data = $this
            ->alias('t')
            ->join('__PROJECT__ p ON p.id = t.project_id')
            ->join('__MEMBER__ executive ON executive.id = t.task_executive_id') // 执行人
            ->join('__MEMBER__ publisher ON publisher.id = t.task_publisher_id') // 发布人
            ->field('t.*,project_name,executive.member_name as task_executive,publisher.member_name as task_publisher')
            ->where($cond)
            ->select();
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
