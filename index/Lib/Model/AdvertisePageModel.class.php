<?php
class AdvertisePageModel extends Model {
  //获取页面广告位
  public function getad($module_name, $action_name){
    $module_name = !empty($module_name) ? $module_name :  strtolower(MODULE_NAME);
    $action_name = !empty($action_name) ? $action_name :  strtolower(ACTION_NAME);
    $where = array();
    $where['csid'] = D('admin://ChildSite') -> getidc();
    $where['module_name'] = $module_name;
    $where['action_name'] = $action_name;
    $pid = $this -> where($where) -> getField('id');
    $result = '';
    //如果存在此页面广告，则读取里面的广告内容
    if($pid){
      $result = D('index://Advertise') -> getadimg($pid);
    }
    return $result;
    
  }
}
