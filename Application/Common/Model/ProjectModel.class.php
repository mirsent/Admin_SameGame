<?php
namespace Common\Model;
use Common\Model\BaseModel;
class ProjectModel extends BaseModel{

    protected $_auto=array(
        array('status','get_default_status',1,'callback')
    );

    /**
     * 根据团队获取项目
     */
    public function getProjectByTeam($teamuuid)
    {
        $cond = [
            'status' => C('STATUS_Y'),
            'team_uuid' => $teamuuid
        ];
        $data = $this->where($cond)->field('id,project_name')->select();
        return $data;
    }

    /**
     * 获取项目数量
     */
    public function getProjectNumber($cond=[])
    {
        $data = $this
            ->alias('p')
            ->join('__PROJECT_TYPE__ pt ON pt.id = p.project_type_id')
            ->join('__MEMBER__ m ON m.id = p.project_publisher_id')
            ->where($cond)
            ->count();
        return $data;
    }

    /**
     * 获取项目列表
     */
    public function getProjectData($cond=[])
    {
        $data = $this
            ->alias('p')
            ->join('__PROJECT_TYPE__ pt ON pt.id = p.project_type_id')
            ->join('__MEMBER__ m ON m.id = p.project_publisher_id')
            ->field('p.*,project_type_name,member_name as project_publisher')
            ->where($cond)
            ->select();
        $member = D('Member');
        foreach ($data as $key => $value) {
            $cond_member = [
                'status' => C('STATUS_Y'),
                'id'     => array('in', $value['project_member_ids'])
            ];
            $data[$key]['project_members'] = $member->getMemberFieldName($cond_member);
        }
        return $data;
    }

    /**
     * 获取项目详情
     */
    public function getProjectDetail($projectId)
    {
        $cond['p.id'] = $projectId;
        $data = $this
            ->alias('p')
            ->join('__PROJECT_TYPE__ pt ON pt.id = p.project_type_id')
            ->join('__MEMBER__ m ON m.id = p.project_publisher_id')
            ->field('p.*,project_type_name,member_name as project_publisher')
            ->where($cond)
            ->find();
        return $data;
    }

    /**
     * 修改项目
     */
    public function editProject($cond, $data)
    {
        if(!$data = $this->create($data)){
            return false;
        }else{
            $data['project_member_ids'] = implode(',', $data['project_member_ids']);
            $res = $this
                ->where($cond)
                ->save($data);
            return $res;
        }
    }

    /**
     * 删除项目
     */
    public function deleteProject($projectId)
    {
        $cond['id'] = $projectId;
        $data['status'] = C('STATUS_N');
        $res = $this->where($cond)->save($data);
        return $res;
    }
}
