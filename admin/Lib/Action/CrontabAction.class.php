<?php
class CrontabAction extends Action {

  //每五分钟执行的任务
  public function fiveminute(){

    /*  ---- 切换 速查提醒邮箱  ----  */
    D('CompanyRemindEmail') -> cutemail();
  }
}
