<?php
class MemberBackgroundNoticeModel extends CommonModel {
  protected $_validate = array(
    array('title', 'require', '{%ARTICLE_TITLE_EMPTY}'),
  );

  protected $_auto = array(
    array('addtime', 'time', 1, 'function'),
  );
}
