<?php
namespace Common\Model;
use Common\Model\BaseModel;
class TeamModel extends BaseModel{

    protected $_auto=array(
        array('status','get_default_status',1,'callback'),
        array('found_time','get_now_time',1,'callback'),
    );

    public function getTeamFields($cond='', $field='team_name')
    {
        $data = $this
            ->where($cond)
            ->getField($field, true);
        return $data;
    }

    /**
     * 获取团队列表
     */
    public function getTeamData($cond='')
    {
        $data = $this
            ->alias('t')
            ->join('__MEMBER__ m ON m.id = t.founder_id')
            ->field('t.*, member_name as founder')
            ->where($cond)
            ->select();
        $member = D('Member');
        foreach ($data as $key => $value) {
            $cond_member = [
                'status' => C('STATUS_Y'),
                'id'     => array('in', $value['member_ids'])
            ];
            $data[$key]['members'] = $member->getMemberFieldName($cond_member);
        }

        return $data;
    }

    /**
     * 添加团队
     */
    public function addTeam($data)
    {
        if(!$data=$this->create($data)){
            return false;
        }else{
            $teamuuid = keyGen();
            $data['member_ids'] = I('founder_id');
            $data['team_uuid'] = $teamuuid;
            $data['team_no'] = substr($teamuuid, -5).rand_number(0,9);
            $result = $this
                ->add($data);
            return $result;
        }
    }

    /**
     * 修改团队信息
     */
    public function editTeam($cond, $data)
    {
        if(!$data=$this->create($data)){
            return false;
        }else{
            $data['member_ids'] = implode(',', $data['member_ids']);
            $result=$this
                ->where($cond)
                ->save($data);
            return $result;
        }
    }

    /**
     * 删除团队
     */
    public function deleteTeam($teamId)
    {
        $cond['id'] = $teamId;
        $data['status'] = C('STATUS_N');
        $res = $this
            ->where($cond)
            ->save($data);
        return $res;
    }
}
