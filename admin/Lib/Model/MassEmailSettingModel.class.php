<?php
class MassEmailSettingModel extends Model {
  public function sendEmail($type, $sendEmail, $id){
    $config = $this -> alias('e') -> field('e.id,e.send_address,e.email_smtp,e.send_account,e.send_pwd,t.title,t.content') -> join('yesow_mass_email_template as t ON t.eid = e.id') -> where(array('e.type_en' => $type)) -> find();

    C('MAIL_ADDRESS', $config['send_address']);
    C('MAIL_SMTP', $config['email_smtp']);
    C('MAIL_LOGINNAME', $config['send_account']);
    C('MAIL_PASSWORD', $config['send_pwd']);
    import('ORG.Util.Mail');


    if(strstr($type, 'company')){
      $info = M('Company') -> table('yesow_company as c') -> field('c.id,cs.name as csname,csa.name as csaname,c.name,c.address,c.mobilephone,c.companyphone,c.linkman,c.website,c.email,c.manproducts,c.qqcode,cs.domain') -> where(array('c.id' => $id)) -> join('yesow_child_site as cs ON c.csid = cs.id') -> join('yesow_child_site_area as csa ON c.csaid = csa.id') -> find();
      $search = array('{company_id}', '{company_csid}', '{company_csaid}', '{company_name}', '{company_address}', '{company_mobilephone}', '{company_companyphone}', '{company_linkman}', '{company_website}', '{company_email}', '{company_manproducts}', '{company_qqcode}', '{company_domain}');
    }else if(strstr($type, 'shop')){
      $info = M('ShopOrder') -> alias('so') -> field('sos.shoptitle,so.ordernum,so.result,so.addtime,so.address,so.username,so.tel,m.name,m.nickname') -> join('yesow_shop_order_shop as sos ON sos.ordernum = so.ordernum') -> join('yesow_member as m ON so.mid = m.id') -> where(array('so.id' => $id)) -> find();
      $info['addtime'] = date('Y年m月d日', $info['addtime']);
      $search = array('{shop_name}', '{shop_ordernum}', '{shop_result}', '{shop_addtime}', '{shop_address}', '{shop_linkman}', '{shop_mobilephone}', '{shop_member_name}', '{shop_member_nickname}');
    }else if(strstr($type, 'member')){
      $info = M('Member') -> alias('m') -> field('m.id,cs.name as csname,csa.name as csaname,m.name,m.nickname,m.fullname,m.idnumber,m.sex,m.tel,m.qqcode,m.msn,m.email,m.address,m.zipcode,m.unit,m.homepage') -> join('yesow_child_site as cs ON m.csid = cs.id') -> join('yesow_child_site_area as csa ON m.csaid = csa.id') -> where(array('m.id' => $id)) -> find();
      $info['sex'] = $info['sex'] == 1 ? '男' : '女';
      $search = array('{member_id}', '{member_csid}', '{member_csaid}', '{member_name}', '{member_nickname}', '{member_fullname}', '{member_idnumber}', '{member_sex}', '{member_tel}', '{member_qqcode}', '{member_msn}', '{member_email}', '{member_address}', '{member_zipcode}', '{member_unit}', '{member_homepage}');
    }

    $info['send_time'] = date('Y-m-d H:i:s');
    $search[] = '{send_time}';

    $email_content = str_replace($search, $info, $config['content']);
    $email_title = str_replace($search, $info, $config['title']);

    if(@SendMail($sendEmail, $email_title, $email_content, 'yesow管理员')){
      $add_data = array();
      $add_data['eid'] = $config['id'];
      $add_data['send_email'] = $config['send_address'];
      $add_data['accept_email'] = $sendEmail;
      $add_data['title'] = $email_title;
      $add_data['content'] = $email_content;
      $add_data['sendtime'] = time();
      $add_data['status'] = 1;
      M('MassEmailRecord') -> add($add_data);
    }else{
      $add_data = array();
      $add_data['eid'] = $config['id'];
      $add_data['send_email'] = $config['send_address'];
      $add_data['accept_email'] = $sendEmail;
      $add_data['title'] = $email_title;
      $add_data['content'] = $email_content;
      $add_data['sendtime'] = time();
      $add_data['status'] = 0;
      M('MassEmailRecord') -> add($add_data);
    }
  }
}
