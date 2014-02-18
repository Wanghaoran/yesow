<?php
class CrontabAction extends Action {

  //每五分钟执行的任务
  public function fiveminute(){
    /*  ---- 切换 提醒邮箱  ----  */
    D('CompanyRemindEmail') -> cutemail();
  }

  //每天执行的任务
  public function oneday(){
    //清空定时提醒邮箱每日发送邮件数量
    D('CompanyRemindEmail') -> where(1) -> save(array('sum' => 0));
  }
}
