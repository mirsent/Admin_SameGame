<?php
namespace Common\Model;
use Common\Model\BaseModel;
class ProjectTypeModel extends BaseModel{

    protected $_auto=array(
        array('status','get_default_status',1,'callback')
    );

    public function getDataForDt()
    {
        $cond['status'] = C('STATUS_Y');
        $data = $this
            ->where($cond)
            ->select();
        return $data;
    }
}

