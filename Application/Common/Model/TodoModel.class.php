<?php
namespace Common\Model;
use Common\Model\BaseModel;
class TodoModel extends BaseModel{

    protected $_auto=array(
        array('status','get_default_status',1,'callback'),
        array('publish_time','get_now_time',1,'callback')
    );

}