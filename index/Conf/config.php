<?php
$common_config = include './Public/config.inc.php';

$app_config = array(
  'USER_AUTH_KEY' => 'user_id',
  'URL_ROUTER_ON' => true,  //开启url路由
  'URL_ROUTE_RULES' => array(
    'info' => 'info/info',//资讯首页
    'infolist/:id' => 'info/infolist', //资讯一级栏目页
    'infodetail/:id' => 'info/infodetail', //资讯一级栏目页
    'article/:id' => 'info/article',//查看文章
    'commit' => 'info/commit',//提交评论
    'noticelist' => 'index/noticelist',//站点公告列表
    'notice/:id' => 'index/notice',//站点公告详情
    'company/:id\d' => 'company/info',//速查详情页
    'report/:id\d' => 'company/report',//速查报错页
    'change/:id\d' => 'company/change',//速查改错页
    'search' => 'company/search',//搜索
    'clickrank' => 'company/clickrank',//点击排名
    'scorerank' => 'company/scorerank',//正负排名
    'aboutus/:id\d' => 'index/aboutus',//关于我们
    'aboutus' => 'index/aboutus',//关于我们
    'agent/:id\d' => 'agent/index',//代理加盟
    'shop/:id\d' => 'shop/info',//商品详情
    'shoplist/:cid' => 'shop/index',//商品列表
    'shoplist' => 'shop/index',//商品列表
    'dgcm/:id\d' => 'index/dgcminfo',//动感传媒详情
    'dgcm' => 'index/dgcm',//动感传媒列表页
    'storerent/:id\d' => 'hire/storerentinfo',//旺铺出租详情页
    'storerent' => 'hire/storerentlist',//旺铺出租列表页
    'sellused/:id\d' => 'hire/sellusedinfo',//二手滞销详情页
    'sellused' => 'hire/sellusedlist',//二手滞销列表页
    'recruit/:id\d' => 'hire/recruitinfo',//人才招聘详情页
    'recruit' => 'hire/recruitlist',//人才招聘列表页
    'applylink' => 'index/applylink',//申请友链
  ),
);

return array_merge($common_config, $app_config);
?>
