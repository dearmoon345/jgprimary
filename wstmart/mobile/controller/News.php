<?php
namespace wstmart\mobile\controller;
use wstmart\mobile\model\Articles as M;
/**
 * ============================================================================
 * WSTMart多用户商城
 * 版权所有 2016-2066 广州商淘信息科技有限公司，并保留所有权利。
 * 官网地址:http://www.wstmart.net
 * 交流社区:http://bbs.shangtaosoft.com
 * 联系QQ:153289970
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！未经本公司授权您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * 新闻控制器
 */
class News extends Base{
	/**
	 * 列表查询
	 */
    public function view(){
        $articleId = (int)input('articleId');
        $this->assign('articleId',$articleId);
        return $this->fetch('articles/news_list');
    }
    /**
    * 获取商城快讯列表
    */
    public function getNewsList(){
    	$m = new M();
    	$data = $m->getArticles();
    	foreach($data as $k=>$v){
    		$data[$k]['articleContent'] = strip_tags(html_entity_decode($v['articleContent']));
            $data[$k]['createTime'] = date('Y-m-d',strtotime($data[$k]['createTime']));
    	}
    	return $data;
    }
    /**
    * 查看详情
    */
    public function getNews(){
    	$m = new M();
    	$data = $m->getNewsById();
        $data['articleContent']=htmlspecialchars_decode($data['articleContent']);
        $data['createTime'] = date('Y-m-d',strtotime($data['createTime']));
        return $data;
    }
}
