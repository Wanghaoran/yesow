<?php
class NoticeModel extends CommonModel {
  protected $_validate = array(
    array('title', 'require', '{%ARTICLE_TITLE_EMPTY}'),
    array('keywords', 'require', '{%ARTICLE_KEYWORD_EMPTY}'),
    array('content', 'require', '{%ARTICLE_CONTENT_EMPTY}'),
    array('source', 'require', '{%ARTICLE_SOURCE_EMPTY}'),
  );

  protected $_auto = array(
    array('addtime','time',1,'function'), 
  );
}
