<?php
class CompanyReportModel extends CommonModel {
  protected $_validate = array(
    array('cetid','require','{%COMPANY_REPORT_CETID_EMPTY}',1), 
  );

  protected $_map = array(
    'content' => 'description',
  );

  protected $_auto = array(
    array('addtime','time',1,'function'),
  );
}
