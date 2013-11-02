<?php
class OrderAcceptEmailModel extends Model {
  public function sendOrderEmail($model, $ordernum){
    $oper_arr = array(
      'ShopOrder' => array(
	'field' => 'o.ordernum,o.paytotal,o.addtime,o.paytype,m.name',
	'join' => 'yesow_member as m ON o.mid = m.id',
	'name' => '商城购物',
      ),
      'RmbOrder' => array(
	'field' => 'o.ordernum,o.price,o.addtime,o.paytype,m.name',
	'join' => 'yesow_member as m ON o.mid = m.id',
	'name' => 'RMB充值',
      ),
      'MonthlyOrder' => array(
	'field' => 'o.ordernum,o.price,o.addtime,o.paytype,m.name',
	'join' => 'yesow_member as m ON o.mid = m.id',
	'name' => '会员包月',
      ),
      'QqonlineOrder' => array(
	'field' => 'o.ordernum,o.price,o.addtime,o.paytype,m.name',
	'join' => 'yesow_member as m ON o.mid = m.id',
	'name' => '在线QQ',
      ),
      'CompanypicOrder' => array(
	'field' => 'o.ordernum,o.price,o.addtime,o.paytype,m.name',
	'join' => 'yesow_member as m ON o.mid = m.id',
	'name' => '企业形象',
      ),
      'AdvertOrder' => array(
	'field' => 'o.ordernum,o.price,o.addtime,o.paytype,m.name',
	'join' => 'yesow_member as m ON o.mid = m.id',
	'name' => '页面广告',
      ),
      'SearchRankOrder' => array(
	'field' => 'o.ordernum,o.price,o.addtime,o.paytype,m.name',
	'join' => 'yesow_member as m ON o.mid = m.id',
	'name' => '速查排名',
      ),
      'RecommendCompanyOrder' => array(
	'field' => 'o.ordernum,o.price,o.addtime,o.paytype,m.name',
	'join' => 'yesow_member as m ON o.mid = m.id',
	'name' => '推荐商家',
      ),
      'MediaShowOrder' => array(
	'field' => 'o.ordernum,o.price,o.addtime,o.paytype,m.name',
	'join' => 'yesow_member as m ON o.mid = m.id',
	'name' => '动感传媒',
      ),
    
    );

    $info = M($model) -> alias('o') -> field($oper_arr[$model]['field']) -> join($oper_arr[$model]['join']) -> where(array('o.ordernum' => $ordernum)) -> find();
    $info['addtime'] = date('Y-m-d H:i:s', $info['addtime']);
    $info['typename'] = $oper_arr[$model]['name'];
    $info['paytype_alipay'] = '支付宝';
    $info['paytype_tenpay'] = '财富通';
    $info['paytype_k99bill'] = '快钱';

    $search = array('{ordernum}', '{paytotal}', '{addtime}', '{paytype}', '{mname}', '{typename}', 'alipay', 'tenpay', 'k99bill');

    $content = '亲爱的易搜管理员:<br/>会员 <b style="color:red">{mname}</b> 在 <b style="color:red">{addtime}</b> 完成了 <b style="color:red">{typename}</b> 付款操作。付款金额 <b style="color:red">{paytotal}</b> 元。付款方式： <b style="color:red">{paytype}</b>。  订单号： <b style="color:red">{ordernum}</b>。 请尽快确认，谢谢！';

    $title = '易搜 {typename} 付款提醒邮件';

    $email_content = str_replace($search, $info, $content);
    $email_title = str_replace($search, $info, $title);

    //config
    $config = M('MassEmailSetting') -> alias('e') -> field('e.id,e.send_address,e.email_smtp,e.send_account,e.send_pwd,t.title,t.content') -> join('yesow_mass_email_template as t ON t.eid = e.id') -> where(array('e.type_en' => 'member_check')) -> find();

    //sendEmail
    $email_arr = $this -> field('email_address') -> select();

    C('MAIL_ADDRESS', $config['send_address']);
    C('MAIL_SMTP', $config['email_smtp']);
    C('MAIL_LOGINNAME', $config['send_account']);
    C('MAIL_PASSWORD', $config['send_pwd']);
    import('ORG.Util.Mail');

    foreach($email_arr as $key => $value){
      if(@SendMail($value['email_address'], $email_title, $email_content, 'yesow管理员')){
	if($key == 0){
	  $add_data = array();
	  $add_data['accept_email'] = $value['email_address'];
	  $add_data['send_type'] = $oper_arr[$model]['name'];
	  $add_data['title'] = $email_title;
	  $add_data['content'] = $email_content;
	  $add_data['send_time'] = time();
	  $add_data['status'] = 1;
	  M('OrderAcceptRecord') -> add($add_data);
	}
      }else{
	if($key == 0){
	  $add_data = array();
	  $add_data['accept_email'] = $value['email_address'];
	  $add_data['send_type'] = $oper_arr[$model]['name'];
	  $add_data['title'] = $email_title;
	  $add_data['content'] = $email_content;
	  $add_data['send_time'] = time();
	  $add_data['status'] = 0;
	  M('OrderAcceptRecord') -> add($add_data);
	}
      }
    }
    usleep(100000);
  }
}
