<?php
namespace Common\Model;
use Common\Model\BaseModel;
class MemberModel extends BaseModel{

    protected $_auto=array(
        array('status','get_default_status',1,'callback')
    );

    public function getMemberData($cond='')
    {
        $data = $this
            ->where($cond)
            ->select();
        return $data;
    }

    /**
     * 获取会员 会员1，会员2
     */
    public function getMemberFieldName($cond='', $field = 'member_name')
    {
        $data = $this
            ->where($cond)
            ->getField($field, true);
        return implode(',', $data);
    }

    /**
     * 根据团队获取会员
     */
    public function getMemberByTeam($teamuuid)
    {
        // 团队成员
        $cond = [
            'team_uuid' => $teamuuid
        ];
        $projectMembers = M('team')->where($cond)->getField('member_ids');

        $cond_member = [
            'status' => C('STATUS_Y'),
            'id'     => array('in', $projectMembers)
        ];
        $data = $this->where($cond_member)->field('id,member_name')->select();
        return $data;
    }

    /**
     * 删除会员
     */
    public function deleteMember($memberId)
    {
        $cond['id'] = $memberId;
        $data['status'] = C('STATUS_N');
        $res = $this
            ->where($cond)
            ->save($data);
        return $res;
    }
}
