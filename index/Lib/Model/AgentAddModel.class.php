<?php
class AgentAddModel extends CommonModel {

  protected $_auto = array(
    array('p_birthday','strtotime',1,'function'),
    array('starttime','strtotime',1,'function'),
  );


}
