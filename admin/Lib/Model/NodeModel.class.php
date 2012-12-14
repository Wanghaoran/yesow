<?php
class NodeModel extends CommonModel {
  protected $_validate = array(
    array('name', 'require', '{%NODE_NAME_EMPTY}'),
    array('title', 'require', '{%NODE_TITLE_EMPTY}'),
  );
}
