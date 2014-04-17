<?php
class CrontabAction extends Action {

  public function oneminute(){
    //清空定时发送邮箱区间计数
    M('TimingSendEmailSetting') -> where(1) -> save(array('min_limit2' => 0));
    //执行后台定时邮件发送
    R('Public/timingsendemail');
    /*  ---- 切换 提醒邮箱  ----  */
    D('CompanyRemindEmail') -> cutemail();
    //会员定时邮件发送
    R('Public/membertimingsendemail');
  
  }

  public function twominute(){
    
  }

  //每五分钟执行的任务
  public function fiveminute(){
    
  }

  //每天执行的任务
  public function oneday(){
    //清空定时提醒邮箱每日发送邮件数量
    D('CompanyRemindEmail') -> where(1) -> save(array('sum' => 0));
    //清空定时发送邮箱区间计数
    M('TimingSendEmailSetting') -> where(1) -> save(array('sendnum' => 0));
  }
}
