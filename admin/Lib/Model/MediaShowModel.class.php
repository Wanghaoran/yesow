<?php
class MediaShowModel extends Model {
  protected $_auto = array(
    array('starttime', 'strtotime', 3, 'function'),
    array('endtime', 'strtotime', 3, 'function'),
    array('addtime', 'time', 1, 'function'),
    array('updatetime', 'addtime', 1, 'field'),
    array('updatetime', 'time', 2, 'function'),
  );
}
