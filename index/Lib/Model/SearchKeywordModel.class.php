<?php
class SearchKeywordModel extends CommonModel {

  protected $_auto = array(
    array('ipaddress','get_client_ip',1,'function'),
    array('addtime','time',1,'function'),
  );

}
