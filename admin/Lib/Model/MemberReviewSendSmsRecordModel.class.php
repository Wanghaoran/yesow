<?php
class MemberReviewSendSmsRecordModel extends Model {

  public function sendsms($rid){
    $MemberReview = M('MemberReview');
    $MemberReviewSetting = M('MemberReviewSetting');

    $result_content = $MemberReviewSetting -> getFieldByname('sendsmstemplate', 'value');

    $company_info = $MemberReview -> alias('r') -> field('r.name,r.new_linkman,r.new_companyphone,r.new_mobilephone,r.new_qqonline,r.new_email,tmc2.nexttime as nexttime,r.linkman,a.remark') -> join('yesow_admin as a ON r.aid = a.id') -> join('LEFT JOIN (SELECT rid,nexttime,status FROM (SELECT * FROM yesow_member_review_record ORDER BY rid ASC, nexttime DESC) as tmc GROUP BY rid) as tmc2 ON r.id = tmc2.rid') -> where(array('r.id' => $rid)) -> find();
    $company_info['sendtime'] = date('Y-m-d H:i:s');
    $company_info['nexttime'] = date('Y-m-d', $company_info['nexttime']);

    $search = array('{company_name}', '{company_new_linkman}', '{company_companyphone}', '{company_mobilephone}', '{company_qqcode}', '{company_email}', '{time_nexttime}', '{company_linkman}', '{company_admin}', '{time_sendtime}');

    $content = str_replace($search, $company_info, $result_content);

    $setting = M('SmsSetting');
    $sms_username = $setting -> getFieldByname('sms_username', 'value');
    $sms_password = $setting -> getFieldByname('sms_password', 'value');

    $url = "http://www.vip.86aaa.com/api.aspx?SendType=1&Code=utf-8&UserName={$sms_username}&Pwd={$sms_password}&Mobi={$company_info['new_mobilephone']}&Content={$content}【易搜】";
    $url = iconv('UTF-8', 'GB2312', $url);
    $fp = fopen($url, 'rb');
    $ret= fgetss($fp,255);
    fclose($fp);
    if($ret === false){
      $ret = 5;
    }

    $data = array();
    $data['accepttel'] = $company_info['new_mobilephone'];
    $data['content'] = $content;
    $data['sendtime'] = time();
    $data['status'] = $ret;
    $this -> add($data);
  }
}
