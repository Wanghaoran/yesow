<?php
class MediaShowModel extends Model {
  protected $_auto = array(
    array('starttime', 'strtotime', 3, 'function'),
    array('endtime', 'strtotime', 3, 'function'),
  );
}
