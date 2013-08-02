<?php
// +----------------------------------------------------------------------
// | Yesow 网站公共配置文件
// +-----------------------------------------------------------------------
// | Last Update Time : 2012-11-19 19:58
// +----------------------------------------------------------------------

if (!defined('THINK_PATH')) exit();
return array(

  'APP_FILE_CASE' => true,  //是否检查文件的大小写，仅对Window平台有效

  'LANG_SWITCH_ON' => true,  //开启语言包
  'DEFAULT_LANG' => 'zh-cn', // 默认语言

  'TMPL_L_DELIM' => '<!--{',  //模板左分隔符
  'TMPL_R_DELIM' => '}--!>',  //模板右分隔符

  'DB_TYPE' => 'mysql',  //数据库类型
  'DB_HOST' => 'localhost',  //服务器地址
  'DB_NAME' => 'yesow',  //数据库名
  'DB_USER' => 'root',  //用户名
  'DB_PWD' => 'jilexingqiu',  //密码
  'DB_PORT' => '3306',  //端口
  'DB_PREFIX' => 'yesow_',  //数据库表前缀

  'URL_CASE_INSENSITIVE' => true,  // 默认false 表示URL区分大小写 true则表示不区分大小写
  'UPLOAD_PATH' => './Upload', //文件上传地址
  'SAVE_PATH' => __ROOT__ . '/Upload', //文件保存地址

  /* -- 发送邮件设置 -- */
  'MAIL_ADDRESS'=>'yesow8@163.com', // 邮箱地址
  'MAIL_SMTP'=>'smtp.163.com', // 邮箱SMTP服务器
  'MAIL_LOGINNAME'=>'yesow8', // 邮箱登录帐号
  'MAIL_PASSWORD'=>'yesow123', // 邮箱密码
  'MAIL_CHARSET'=>'UTF-8',//编码
  'MAIL_AUTH'=>true,//邮箱认证
  'MAIL_HTML'=>true,//true HTML格式 false TXT格式
  //密钥
  'KEY' => 'yesow',
  //网站主目录
  'WEBSITE' => 'http://42.121.116.205/yesow/',
  //企业形象照片保存路径
  'COMPANY_PIC_PATH' => './Upload/companypic/',
  //企业形象照片显示地址
  'COMPANY_PIC_PATH_SAVE' => __ROOT__ . '/Upload/companypic/',

  //商品图片保存路径
  'SHOP_PIC_PATH' => './Upload/shop/',
  //商品图片显示
  'SHOP_PIC_PATH_SAVE' => __ROOT__ . '/Upload/shop/', 

  //广告图片保存路径
  'AD_PIC_PATH' => './Upload/ad/',
  //广告图片显示
  'AD_PIC_PATH_SAVE' => __ROOT__ . '/Upload/ad/', 

  //动感传媒图片保存路径
  'MEDIA_PIC_PATH' => './Upload/media/',
  //动感传媒图片显示
  'MEDIA_PIC_PATH_SAVE' => __ROOT__ . '/Upload/media/',

  //旺铺出租图片保存路径
  'STORE_PIC_PATH' => './Upload/store/',
  //旺铺出租图片显示
  'STORE_PIC_PATH_SAVE' => __ROOT__ . '/Upload/store/',

  //二手滞销图片保存路径
  'SELLUSED_PIC_PATH' => './Upload/sellused/',
  //二手滞销图片显示
  'SELLUSED_PIC_PATH_SAVE' => __ROOT__ . '/Upload/sellused/',

  //企业形象图片保存路径
  'COMPANY_PIC_PATH' => './Upload/companypic/',
  //企业形象图片显示
  'COMPANY_PIC_PATH_SAVE' => __ROOT__ . '/Upload/companypic/',

  //企业形象资料保存路径
  'COMPANY_PIC_DATA_PATH' => './Upload/companypicdata/',
  //企业形象资料下载地址
  'COMPANY_PIC_DATA_PATH_SAVE' => __ROOT__ . '/Upload/companypicdata/',

  //网站临时上传目录
  'TEMP_UPLOAD_PATH' => './Upload/temp/',

  /*
  //重新定义__ROOT__常量，线上项目需要设置此项
  'TMPL_PARSE_STRING' => array(
    '__ROOT__' => 'http://' . $_SERVER['HTTP_HOST'];
  ),
   */
);
