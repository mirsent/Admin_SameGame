<?php
namespace Admin\Controller;
use Common\Controller\AdminBaseController;
class WorkController extends AdminBaseController{
    /**
     * 项目类型
     */
    public function project_type()
    {
        $table = 'ProjectType';
        $name = 'project_type_name';
        $title = '项目类型';
        $this->assign(compact('table', 'name', 'title'));
        $this->display();
    }

    /**
     * 项目列表
     */
    public function project()
    {
        $cond['status'] = C('STATUS_Y');
        // 项目类型
        $projectType = M('project_type')
            ->where($cond)
            ->field('id,project_type_name')
            ->select();
        // 团队
        $team = M('team')
            ->where($cond)
            ->field('id,team_uuid,team_name')
            ->select();
        $this->assign(compact('projectType', 'team'));
        $this->display();
    }

    /**
     * 任务列表
     */
    public function task()
    {
        $this->display();
    }
}
