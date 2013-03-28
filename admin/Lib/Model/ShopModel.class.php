<?php
class ShopModel extends CommonModel {
  protected $_auto = array(
    array('addtime', 'time', 1, 'function'),
    array('updatetime', 'time', 2, 'function'),
  );
}
