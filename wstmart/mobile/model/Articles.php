<?php
namespace wstmart\mobile\model;
use think\Db;
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
 *  文章类
 */
class Articles extends Base{
	/**
	* 获取资讯中心的子集分类id
	*/
	public function getChildIds(){
		$ids = [];
		$data = Db::name('article_cats')->cache(true)->select();
			foreach($data as $k=>$v){
				if($v['parentId']!=7 && $v['catId']!=7 && $v['parentId']!=0 ){
					$ids[] = $v['catId'];
				}
			}
		return $ids;
	}

	/**
	* 获取咨询中中心所有文章
	*/
	public function getArticles(){
		// 获取咨询中心下的所有分类id
		$ids = $this->getChildIds();
		$rs = $this->alias('a')
			  ->field('a.*')
			  ->join('__ARTICLE_CATS__ ac','a.catId=ac.catId','inner')
			  ->where(['a.catId'=>['in',$ids],
			  		   'ac.dataFlag'=>1,
			  		   'ac.isShow'=>1,
			  		   'ac.parentId'=>['<>',7],
			  		   ])
			  ->order('createTime desc')
			  ->paginate((int)input('pagesize'));
		return $rs;
	}


	/**
	*  根据id获取资讯文章
	*/
	public function getNewsById(){
		$id = (int)input('id');
		return $this->alias('a')
					->field('a.*')
					->join('__ARTICLE_CATS__ ac','a.catId=ac.catId','inner')
					->where('a.catId<>7 and ac.parentId<>7 and a.dataFlag=1')
					->cache(true)
					->find($id);
	}
	
}
