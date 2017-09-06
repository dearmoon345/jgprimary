<?php
namespace wstmart\mobile\model;
use wstmart\common\model\Tags as T;
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
 * 默认类
 */
class Index extends Base{
	/**
	 * 楼层
	 */
	public function pageQuery(){
		$limit = (int)input('post.currPage');
		if($limit>9)return;
		$cacheData = cache('WX_CATS_ADS'.$limit);
		if($cacheData)return $cacheData;
		$rs = Db::name('goods_cats')->where(['dataFlag'=>1,'isShow'=>1,'parentId'=>0,'isFloor'=>1])->field('catId,catName')->order('catSort asc')->limit($limit,1)->select();
		if($rs){
			$rs= $rs[0];
			$t = new T();
			$rs['ads'] = $t->listAds('wx-ads-'.$limit,'1');

			//原代码
			// $rs['goods'] = Db::name('goods')->where(['goodsCatIdPath'=>['like',$rs['catId'].'_%'],'isSale'=>1,'dataFlag'=>1,'goodsStatus'=>1])->field('goodsId,goodsName,goodsImg,shopPrice,saleNum,marketPrice')->order('isHot desc')->limit(6)->select();

			//添加代码start
			$catId = "'".$rs['catId']."|_%" ."'". "escape '|' ";
			$rs['goods'] = Db::name('goods')->where(['isSale'=>1,'dataFlag'=>1,'goodsStatus'=>1])
											->where("goodsCatIdPath like ".$catId)
			                                ->field('goodsId,goodsName,goodsImg,shopPrice,saleNum,marketPrice')
			                                ->where("isHot",1)
			                                ->order('visitNum desc')
			                                ->limit(6)
			                                ->select();

			//添加代码end
			
			$rs['currPage'] = $limit;
		}
		cache('WX_CATS_ADS'.$limit,$rs,86400);
		return $rs;
	}

	/**
	* 获取系统消息
	*/
	function getSysMsg($msg='',$order=''){
		$data = [];
		$userId = (int)session('WST_USER.userId');
		if($msg!=''){
			$data['message']['num'] = Db::name('messages')->where(['receiveUserId'=>$userId,'msgStatus'=>0,'dataFlag'=>1])->count();
		}
		if($order!=''){
			$data['order']['waitPay'] = Db::name('orders')->where(['userId'=>$userId,'orderStatus'=>-2,'dataFlag'=>1])->count();
			$data['order']['waitSend'] = Db::name('orders')->where(['userId'=>$userId,'orderStatus'=>0,'dataFlag'=>1])->count();
			$data['order']['waitReceive'] = Db::name('orders')->where(['userId'=>$userId,'orderStatus'=>1,'dataFlag'=>1])->count();
			$data['order']['waitAppraise'] = Db::name('orders')->where(['userId'=>$userId,'orderStatus'=>2,'isAppraise'=>0,'dataFlag'=>1])->count();
		}
		return $data;
	}
}
