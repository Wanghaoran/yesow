<?php
class RecruitJobsCommentModel extends Model {
  protected $_validate = array(
    array('content','require','{%ARTICLE_COMMIT_CONTENT_EMPTY}'), 
  );

  protected $_auto = array(
    array('addtime','time',1,'function'),
    array('floor', 'getfloor', 1, 'callback'),
  );


  public function getfloor(){
    $floor = $this -> field('floor') -> where(array('rjid' => $_POST['rjid'])) -> order('floor DESC') -> find();
    return (intval($floor['floor']) + 1);
  }
}
