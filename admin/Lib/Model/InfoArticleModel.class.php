<?php
class InfoArticleModel extends Model {
  protected $_validate = array(
    array('classid', 'require', '{%ARTICLE_CLASSID_EMPTY}'),
    array('colid', 'require', '{%ARTICLE_COLID_EMPTY}'),
    array('tid', 'require', '{%ARTICLE_TID_EMPTY}'),
    array('conid', 'require', '{%ARTICLE_CONID_EMPTY}'),
    array('title', 'require', '{%ARTICLE_TITLE_EMPTY}'),
    array('content', 'require', '{%ARTICLE_CONTENT_EMPTY}'),
    array('source', 'require', '{%ARTICLE_SOURCE_EMPTY}'),
    array('keyword', 'require', '{%ARTICLE_KEYWORD_EMPTY}'),
  );
}
