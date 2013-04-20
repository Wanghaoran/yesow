<?php
class ShopModel extends CommonModel {
  protected $_auto = array(
    array('addtime', 'time', 1, 'function'),
    array('updatetime', 'addtime', 1, 'field'),
    array('updatetime', 'time', 2, 'function'),
  );
}
