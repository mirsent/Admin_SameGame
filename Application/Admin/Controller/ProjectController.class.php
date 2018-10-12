<?php
namespace Admin\Controller;
use Common\Controller\AdminBaseController;
class ProjectController extends AdminBaseController{

    /**
     * 获取项目信息
     */
    public function get_project_info(){
        $ms = D('Project');
        $cond = [
            'p.status' => C('STATUS_Y')
        ];

        $recordsTotal = $ms->alias('p')->where($cond)->count();

        // 搜索
        $search = I('search');
        $searchName = I('project_name');
        $searchDate = I('publish_time');
        if (strlen($search)>0) {
            $cond['project_name|project_desc'] = array('like', '%'.$search.'%');
        }
        if ($searchName) $cond['project_name'] = $searchName;
        if ($searchDate) $cond['publish_time'] = array('between', [$searchDate.' 00:00:01', $searchDate.' 23:59:59']);

        $recordsFiltered = $ms->getProjectNumber($cond);

        // 排序
        $orderObj = I('order')[0];
        $orderColumn = $orderObj['column']; // 排序列，从0开始
        $orderDir = $orderObj['dir'];       // ase desc
        if(isset(I('order')[0])){
            $i = intval($orderColumn);
            switch($i){
                case 0: $ms->order('project_name '.$orderDir); break;
                case 3: $ms->order('project_publisher '.$orderDir); break;
                case 4: $ms->order('publish_time '.$orderDir); break;
                case 5: $ms->order('status '.$orderDir); break;
                default: break;
            }
        } else {
            $ms->order('status desc', 'publish_time desc');
        }

        // 分页
        $start = I('start');
        $limit = I('limit');
        $page = I('page');

        $infos = $ms->page($page, $limit)->getProjectData($cond);

        echo json_encode(array(
            "draw" => intval(I('draw')),
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => $infos
        ), JSON_UNESCAPED_UNICODE);
    }

    /**
     * 修改项目
     */
    public function edit_project()
    {
        $projectId = I('id');

        if ($projectId) {
            $project = D('Project');
            $cond['id'] = $projectId;
            $data = I('post.');
            $res = $project->editProject($cond, $data);

            if ($res === false) {
                ajax_return(0, '修改项目信息出错');
            }
            ajax_return(1, '修改项目信息成功');
        }

        ajax_return(0, '传递参数不合法');
    }

    /**
     * 删除项目
     */
    public function delete_project()
    {
        $projectId = I('id');

        if ($projectId) {
            $res = D('Project')->deleteProject(I('id'));
            if ($res === false) {
                ajax_return(0, '删除项目出错');
            }
            ajax_return(1, '删除项目成功');
        }

        ajax_return(0, '传递参数不合法');
    }
}
