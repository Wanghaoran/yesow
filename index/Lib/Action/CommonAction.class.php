<?php
class CommonAction extends Action {
  public function _initialize(){
    if(S('index_article_nav')){
      $this -> assign('index_article_nav', S('index_article_nav'));
    }else{
      $index_article_nav = R('Public/getonearticle');
      $this -> assign('index_article_nav', $index_article_nav);
      S('index_article_nav', $index_article_nav);   
    }
    if(S('index_investment_nav')){
      $this -> assign('index_investment_nav', S('index_investment_nav'));
    }else{
      $index_investment_nav = R('Public/getinvestment');
      $this -> assign('index_investment_nav', $index_investment_nav);
      S('index_investment_nav', $index_investment_nav);   
    }
    if(S('index_search_hot')){
      $this -> assign('index_search_hot', S('index_search_hot'));
    }else{
      $index_search_hot =R('Public/getsearchhot');
      $this -> assign('index_search_hot', $index_search_hot);
      S('index_search_hot', $index_search_hot);
    }
    if(S('index_footer_nav')){
      $this -> assign('index_footer_nav', S('index_footer_nav'));
    }else{
      $index_footer_nav = R('Public/getfooternav');
      $this -> assign('index_footer_nav', $index_footer_nav);
      S('index_footer_nav', $index_footer_nav);
    }
    if(S('index_agent_join')){
      $this -> assign('index_agent_join', S('index_agent_join'));
    }else{
      $index_agent_join = R('Public/getagentjoin');
      $this -> assign('index_agent_join', $index_agent_join);
      S('index_agent_join', $index_agent_join);
    }
    if(S('header_child_site')){
      $this -> assign('header_child_site', S('header_child_site'));
    }else{
      $header_child_site = R('Public/getchildsite');
      $this -> assign('header_child_site', $header_child_site);
      S('header_child_site', $header_child_site);
    }
    $qqonline_cache = S('index_qqonline');
    if($qqonline_cache && $qqonline_cache['childsite_name'] != $_SERVER['HTTP_HOST']){
      $index_qqonline = R('Public/getqqonline');
      $this -> assign('index_qqonline', $index_qqonline);
      S('index_qqonline', $index_qqonline);
    }else if($qqonline_cache){
      $this -> assign('index_qqonline', S('index_qqonline'));
    }else{
      $index_qqonline = R('Public/getqqonline');
      $this -> assign('index_qqonline', $index_qqonline);
      S('index_qqonline', $index_qqonline);
    }
    if(S('index_bottomhelp')){
      $this -> assign('index_bottomhelp', S('index_bottomhelp'));
    }else{
      $index_bottomhelp = R('Public/getbottomhelp');
      $this -> assign('index_bottomhelp', $index_bottomhelp);
      S('index_bottomhelp', $index_bottomhelp);
    }
    if(S('index_shop_nav')){
      $this -> assign('index_shop_nav', S('index_shop_nav'));
    }else{
      $index_shop_nav = R('Public/getshopnav');
      $this -> assign('index_shop_nav', $index_shop_nav);
      S('index_shop_nav', $index_shop_nav);   
    }
    $friendlink_cache = S('index_friend_link');
    if($friendlink_cache && $friendlink_cache['childsite_name'] != $_SERVER['HTTP_HOST']){
      $index_friend_link = R('Public/getfriendlink');
      $this -> assign('index_friend_link', $index_friend_link);
      S('index_friend_link', $index_friend_link);
    }else if($friendlink_cache){
      $this -> assign('index_friend_link', S('index_friend_link'));
    }else{
      $index_friend_link = R('Public/getfriendlink');
      $this -> assign('index_friend_link', $index_friend_link);
      S('index_friend_link', $index_friend_link);
    }
    $templatename = D('admin://ChildSite') -> gettemplatename();
    $this -> assign('templatename', $templatename);
    $ad_arr = D('index://AdvertisePage') -> getad();
    $this -> assign('ad_arr', $ad_arr);
    $childsite_name = D('admin://ChildSite') -> getname();
    $this -> assign('childsite_name', $childsite_name);
    $bottom_phone = D('index://ChildSitePhone') -> getphone();
    $this -> assign('bottom_phone', $bottom_phone);
  }
}
