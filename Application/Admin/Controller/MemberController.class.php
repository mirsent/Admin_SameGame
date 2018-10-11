<?php
namespace Admin\Controller;
use Common\Controller\AdminBaseController;
class MemberController extends AdminBaseController{

    /**
     * 获取会员
     */
    public function get_user_info()
    {
        $ms = D('Member');

        $recordsTotal = $ms->where($cond)->count();

        // 搜索
        $search = I('search');
        $searchMember = I('member_name');
        $searchMail = I('mail');
        $searchDate = I('search_date');
        if (strlen($search)>0) {
            $cond['member_name|mail'] = array('like', '%'.$search.'%');
        }
        if ($searchMember) $cond['member_name'] = $searchMember;
        if ($searchMail) $cond['mail'] = $searchMail;
        if ($searchDate) $cond['register_time'] = array('between', [$searchDate.' 00:00:01', $searchDate.' 23:59:59']);

        $recordsFiltered = $ms->where($cond)->count();

        // 排序
        $orderObj = I('order')[0];
        $orderColumn = $orderObj['column'];
        $orderDir = $orderObj['dir'];
        if(isset(I('order')[0])){
            $i = intval($orderColumn);
            switch($i){
                case 0: $ms->order('member_name '.$orderDir); break;
                case 2: $ms->order('mail '.$orderDir); break;
                case 5: $ms->order('register_time '.$orderDir); break;
                case 6: $ms->order('status '.$orderDir); break;
                default: break;
            }
        } else {
            $ms->order('status desc', 'register_time desc');
        }

        // 分页
        $start = I('start');
        $limit = I('limit');
        $page = I('page');

        $infos = $ms->page($page, $limit)->getMemberData($cond);

        $team = D('Team');
        foreach ($infos as $key => $value) {
            $memberId = $value['id'];

            // 创建的团队
            $cond_team_create = [
                'status'    => C('STATUS_Y'),
                'member_id' => $memberId
            ];
            $teamCreate = $team->getTeamFields($cond_team_create);
            $infos[$key]['team_create'] = implode(',', $teamCreate);

            // 加入的团队
            $cond_team_join = [
                'status'  => C('STATUS_Y'),
                '_string' => 'FIND_IN_SET('.$memberId.', member_ids)'
            ];
            $teamJoin = $team->getTeamFields($cond_team_join);
            $infos[$key]['team_join'] = implode(',', $teamJoin);
        }

        echo json_encode(array(
            "draw" => intval(I('draw')),
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => $infos
        ), JSON_UNESCAPED_UNICODE);
    }

    /**
     * 修改会员信息
     */
    public function edit_member()
    {
        $memberId = I('id');
        if ($memberId) {
            $member = D('member');
            $member->create();
            $cond['id'] = $memberId;
            $res = $member->where($cond)->save();

            if ($res === false) {
                ajax_return(0, '修改会员信息出错');
            }
            ajax_return(1, '修改会员信息成功');
        } else {
            ajax_return(0, '传递参数不合法');
        }
    }

    /**
     * 删除会员
     */
    public function delete_member()
    {
        $memberId = I('id');

        if ($memberId) {
            $res = D('Member')->deleteMember($memberId);
            if ($res === false) {
                ajax_return(0, '删除会员出错');
            }
            ajax_return(1, '删除会员成功');
        }

        ajax_return(0, '传递参数不合法');
    }

    /**
     * 上传会员头像
     */
    public function upload_member()
    {
        $cond['id'] = I('id');
        $data['member_head'] = upload_img('Head');
        $res = M('member')->where($cond)->save($data);
        if ($res === false) {
            ajax_return(0, '上传头像出错');
        }
        ajax_return(1);
    }

    /**
     * 删除会员头像
     */
    public function delete_member_head()
    {
        $cond['id'] = I('key');
        $data['member_head'] = '';
        $res = M('member')->where($cond)->save($data);
        if ($res === false) {
            ajax_return(0, '删除头像出错');
        }
        ajax_return(1);
    }

    /**
     * 获取团队
     */
    public function get_team_info()
    {
        $ms = D('Team');

        $recordsTotal = $ms->count();

        // 搜索
        $search = I('search');
        $searchTeam = I('team_name');
        $searchMember = I('member_name');
        $searchDate = I('found_time');
        if (strlen($search)>0) {
            $cond['team_name|member_name'] = array('like', '%'.$search.'%');
        }
        if ($searchMember) $cond['member_name'] = $searchMember;
        if ($searchTeam) $cond['team_name'] = $searchTeam;
        if ($searchDate) $cond['found_time'] = array('between', [$searchDate.' 00:00:01', $searchDate.' 23:59:59']);

        $recordsFiltered = $ms->where($cond)->count();

        // 排序
        $orderObj = I('order')[0];
        $orderColumn = $orderObj['column'];
        $orderDir = $orderObj['dir'];
        if(isset(I('order')[0])){
            $i = intval($orderColumn);
            switch($i){
                case 0: $ms->order('team_name '.$orderDir); break;
                case 1: $ms->order('member_ids '.$orderDir); break;
                case 2: $ms->order('member_name '.$orderDir); break;
                case 3: $ms->order('found_time '.$orderDir); break;
                case 4: $ms->order('status '.$orderDir); break;
                default: break;
            }
        } else {
            $ms->order('status desc', 'found_time desc');
        }

        // 分页
        $start = I('start');
        $limit = I('limit');
        $page = I('page');

        $infos = $ms->page($page, $limit)->getTeamData($cond);

        echo json_encode(array(
            "draw" => intval(I('draw')),
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => $infos
        ), JSON_UNESCAPED_UNICODE);
    }

    /**
     * 修改团队信息
     */
    public function edit_team()
    {
        $teamId = I('id');
        if ($teamId) {
            $team = D('Team');
            $cond['id'] = $teamId;
            $data = I('post.');
            $res = $team->editTeam($cond, $data);

            if ($res === false) {
                ajax_return(0, '修改团队信息出错');
            }
            ajax_return(1, '修改团队信息成功');
        } else {
            ajax_return(0, '传递参数不合法');
        }
    }

    /**
     * 删除团队
     */
    public function delete_team()
    {
        $teamId = I('id');

        if ($teamId) {
            $res = D('Team')->deleteTeam($teamId);
            if ($res === false) {
                ajax_return(0, '删除团队出错');
            }
            ajax_return(1, '删除团队成功');
        }

        ajax_return(0, '传递参数不合法');
    }
}
