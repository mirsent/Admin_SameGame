<?php
namespace Common\Model;
use Common\Model\BaseModel;
class LogModel extends BaseModel{

    protected $_auto=array(
        array('operate_time','get_now_time',1,'callback'),
        array('operate_date','get_now_date',1,'callback')
    );

    /**
     * 获取记录
     */
    public function getLogGroupDate($cond=[])
    {
        // 按日期分组
        $dates = $this
            ->distinct('operate_date')
            ->where($cond)
            ->getField('operate_date', true);

        foreach ($dates as $date) {
            $cond['operate_date'] = $date;

            // 按项目分组
            $projects = $this
                ->alias('l')
                ->join('__PROJECT__ p ON p.id = l.project_id')
                ->distinct('p.id')
                ->where($cond)
                ->getField('p.id,project_name', true);

            foreach ($projects as $projectId => $project) {
                $cond_project['project_id'] = $projectId;

                $data[$date][$project] = $this
                    ->alias('l')
                    ->join('__PROJECT__ p ON p.id = l.project_id')
                    ->join('__MEMBER__ m ON m.id = l.operator_id')
                    ->field('
                        l.*,
                        project_name as p_project_name,
                        member_name as operator,
                        substring(operate_time,12,5) as operate_hm
                        ')
                    ->order('operate_time desc')
                    ->where($cond)
                    ->where($cond_project)
                    ->select();
            }
        }
        return $data;
    }

    /**
     * 添加记录
     */
    public function addLog($data)
    {
        if(!$data = $this->create($data)){
            return false;
        }else{
            $res = $this
                ->where($cond)
                ->add($data);
            return $res;
        }
    }
}
