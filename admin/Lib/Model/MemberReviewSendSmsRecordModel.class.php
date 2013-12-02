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

    //读取目前启用的端口
    $SmsApi = M('SmsApi');
    $sms_url = $SmsApi -> field('id,name,url') -> where('enable=1') -> find();
    //读取端口参数
    $SmsApiParameters = M('SmsApiParameters');
    $sms_parameter = $SmsApiParameters -> field('key,value,callback') -> where(array('aid' => $sms_url['id'])) -> select();

    //制作参数替换数组
    $parament_key_arr = array();
    $parament_value_arr = array();
    foreach($sms_parameter as $value33){
      $parament_key_arr[$value33['key']] = '{' . $value33['key'] . '}';
      $parament_value_arr[$value33['key']] = $value33['value'];
    }
    if($sms_url['id'] == 3 || $sms_url['id'] == 4){
      $parament_value_arr['MOBILE'] = $company_info['new_mobilephone'];
      $parament_value_arr['CONTENT'] = urlencode($content);
    }else if($sms_url['id'] == 5){
      $parament_value_arr['Mobi'] = $company_info['new_mobilephone'];
      $parament_value_arr['Content'] = urlencode($content .'【易搜】');
      //$parament_value_arr['SendType'] = $_POST['sendtype'];
    }

    $sms_send_url = str_replace($parament_key_arr, $parament_value_arr, $sms_url['url']);

    $fp = fopen($sms_send_url, 'rb');
    $ret= fgetss($fp,255);
    $ret = intval($ret);
    fclose($fp);

    //读取返回参数
    $SmsApiCallback = M('SmsApiCallback');
    $call_back = $SmsApiCallback -> field('value,status') -> where(array('key' => $ret, 'aid' => $sms_url['id'])) -> find();

    $data = array();
    $data['accepttel'] = $company_info['new_mobilephone'];
    $data['content'] = $content;
    $data['sendtime'] = time();
    $data['status'] = $call_back['value'];
    $this -> add($data);
  }
}
