<?php
class EmptyAction extends Action {
  public function index(){
    //根据域名判断分站 及 读取分站模板
    $templatename = D('admin://ChildSite') -> gettemplatename();
    $this -> assign('templatename', $templatename);
    $this -> display('Empty:index');
  }

  public function _empty(){
    //根据域名判断分站 及 读取分站模板
    $templatename = D('admin://ChildSite') -> gettemplatename();
    $this -> assign('templatename', $templatename);
    $this -> display('Empty:index');
  }
}
