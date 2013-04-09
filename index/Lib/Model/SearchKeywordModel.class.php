<?php
class SearchKeywordModel extends CommonModel {

  protected $_auto = array(
    array('ipaddress','get_client_ip',3,'function'),
    array('addtime','time',3,'function'),
  );

}
