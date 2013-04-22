<?php
class SellUsedModel extends Model {

  protected $_auto = array(
    array('addtime', 'time', 1, 'function'),
    array('updatetime', 'time', 2, 'function'),
    array('updatetime', 'addtime', 1, 'field'),
    array('endtime', 'strtotime', 3, 'function'),
    array('ischeck', '0', 3),
  );
}
