<?php
class ShopCommentModel extends Model {
  protected $_validate = array(
    array('content','require','{%ARTICLE_COMMIT_CONTENT_EMPTY}'), 
  );

  protected $_auto = array(
    array('addtime','time',1,'function'),
    array('floor', 'getfloor', 1, 'callback'),
  );

  public function getfloor(){
    $floor = $this -> field('floor') -> where(array('sid' => $_POST['sid'])) -> order('floor DESC') -> find();
    return (intval($floor['floor']) + 1);
  }
}
