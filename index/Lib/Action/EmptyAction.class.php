<?php
class EmptyAction extends Action {
  public function index(){
    $this -> display('Empty:index');
  }

  public function _empty(){
    $this -> display('Empty:index');
  }
}
