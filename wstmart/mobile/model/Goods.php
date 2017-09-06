<?php
namespace wstmart\mobile\model;
use wstmart\common\model\Goods as CGoods;
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
 * 商品类
 */
class Goods extends CGoods{
	/**
	 * 获取列表
	 */
	public function pageQuery($goodsCatIds = [],$isSearch = 0){
		//查询条件
		$keyword = input('keyword');
		$brandId = input('brandId/d');
		$where = $where2 = [];
		$where['goodsStatus'] = 1;
		$where['g.dataFlag'] = 1;
		$where['isSale'] = 1;

		//添加代码start
		$flag = false;//判断商品是否存在
		if(!empty($goodsCatIds)){
			foreach ($goodsCatIds as $key => $value1) {
	            $rs = Db::name('goods')->where(['goodsCatIdPath'=>['like',implode('_',$value1).'%']])->find();
	            if($rs){
	            	$flag = true;
	            	break;
				}else if($isSearch ==0){
					$where['goodsCatIdPath'] = ['like','-.,*(_+a_z!$^.<>_null_'];
				}
        	}	
		}

		if($flag == true){
        	if(count($goodsCatIds) > 1){
        		$where['goodsCatIdPath']=[];
        		foreach ($goodsCatIds as $key => $value2) {
	            	array_push($where['goodsCatIdPath'],['like',implode('_',$value2).'%']);
        		}
        		array_push($where['goodsCatIdPath'],'or');
        	}else{
        		$where['goodsCatIdPath'] = ['like',implode('_',$value1).'%'];
        	}
		}
		if($flag == false){
			if ($keyword!=''){
				$re = Db::name("goods")->where('goodsName','like','%'.$keyword.'%')->find();
				if($re){
					$where['goodsName'] = ['like','%'.$keyword.'%']; 
				}else{
					$where['goodsCatIdPath'] = ['like','-.,*(_+a_z!$^.<>_null_'];
				}
			}
		}
		//添加代码end

		// if($keyword!='')$where['goodsName'] = ['like','%'.$keyword.'%'];
		if($brandId>0)$where['g.brandId'] = $brandId;
		//排序条件
		$orderBy = input('condition/d',0);
		$orderBy = ($orderBy>=0 && $orderBy<=4)?$orderBy:0;
		$order = (input('desc/d',0)==1)?1:0;
		$pageBy = ['saleNum','shopPrice','visitNum','saleTime'];
		$pageOrder = ['desc','asc'];
		// if(!empty($goodsCatIds))$where['goodsCatIdPath'] = ['like',implode('_',$goodsCatIds).'_%'];
		$list = Db::name('goods')->alias('g')->join("__SHOPS__ s","g.shopId = s.shopId")
		->where($where)
		->field('goodsId,goodsName,saleNum,shopPrice,marketPrice,isSpec,goodsImg,appraiseNum,visitNum,s.shopId,shopName')
		->order($pageBy[$orderBy]." ".$pageOrder[$order].",goodsId asc")
		->paginate(input('pagesize/d'))->toArray();

		return $list;
	}

	/**
	 * 获取商品资料在前台展示
	 */
	public function getBySale($goodsId){
		$key = input('key');
		// 浏览量
		$this->where('goodsId',$goodsId)->setInc('visitNum',1);
		$rs = Db::name('goods')->where(['goodsId'=>$goodsId,'dataFlag'=>1])->find();
		if(!empty($rs)){
			$rs['read'] = false;
			//判断是否可以公开查看
			$viKey = WSTShopEncrypt($rs['shopId']);
			if(($rs['isSale']==0 || $rs['goodsStatus']==0) && $viKey != $key)return [];
			if($key!='')$rs['read'] = true;
			//获取店铺信息
			$rs['shop'] = model('shops')->getBriefShop((int)$rs['shopId']);
			if(empty($rs['shop']))return [];
			$goodsCats = Db::name('cat_shops')->alias('cs')->join('__GOODS_CATS__ gc','cs.catId=gc.catId and gc.dataFlag=1','left')->join('__SHOPS__ s','s.shopId = cs.shopId','left')
			->where('cs.shopId',$rs['shopId'])->field('cs.shopId,s.shopTel,gc.catId,gc.catName')->select();
			$rs['shop']['catId'] = $goodsCats[0]['catId'];
			$rs['shop']['shopTel'] = $goodsCats[0]['shopTel'];

			$cat = [];
			foreach ($goodsCats as $v){
				$cat[] = $v['catName'];
			}
			$rs['shop']['cat'] = implode('，',$cat);
			if(empty($rs['shop']))return [];
			$gallery = [];
			$gallery[] = $rs['goodsImg'];
			if($rs['gallery']!=''){
				$tmp = explode(',',$rs['gallery']);
				$gallery = array_merge($gallery,$tmp);
			}
			$rs['gallery'] = $gallery;
			//获取规格值
			$specs = Db::name('spec_cats')->alias('gc')->join('__SPEC_ITEMS__ sit','gc.catId=sit.catId','inner')
			->where(['sit.goodsId'=>$goodsId,'gc.isShow'=>1,'sit.dataFlag'=>1])
			->field('gc.isAllowImg,gc.catName,sit.catId,sit.itemId,sit.itemName,sit.itemImg')
			->order('gc.isAllowImg desc,gc.catSort asc,gc.catId asc')->select();
			$rs['spec']=[];
			foreach ($specs as $key =>$v){
				$rs['spec'][$v['catId']]['name'] = $v['catName'];
				$rs['spec'][$v['catId']]['list'][] = $v;
			}
			//获取销售规格
			$sales = Db::name('goods_specs')->where('goodsId',$goodsId)->field('id,isDefault,productNo,specIds,marketPrice,specPrice,specStock')->select();
			if(!empty($sales)){
				foreach ($sales as $key =>$v){
					$str = explode(':',$v['specIds']);
					sort($str);
					unset($v['specIds']);
					$rs['saleSpec'][implode(':',$str)] = $v;
				}
			}
			//获取商品属性
			$rs['attrs'] = Db::name('attributes')->alias('a')->join('goods_attributes ga','a.attrId=ga.attrId','inner')
			->where(['a.isShow'=>1,'dataFlag'=>1,'goodsId'=>$goodsId])->field('a.attrName,ga.attrVal')
			->order('attrSort asc')->select();
			//获取商品评分
			$rs['scores'] = Db::name('goods_scores')->where('goodsId',$goodsId)->field('totalScore,totalUsers')->find();
			$rs['scores']['totalScores'] = ($rs['scores']['totalScore']==0)?5:WSTScore($rs['scores']['totalScore'],$rs['scores']['totalUsers'],5,0,3);
			WSTUnset($rs, 'totalUsers');
			//关注
			$f = model('Favorites');
			$rs['favShop'] = $f->checkFavorite($rs['shopId'],1);
			$rs['favGood'] = $f->checkFavorite($goodsId,0);
		}
		return $rs;
	}


	public function historyQuery(){
		$ids = cookie("wx_history_goods");
		if(empty($ids))return [];
	    $where = [];
	    $where['isSale'] = 1;
	    $where['goodsStatus'] = 1; 
	    $where['dataFlag'] = 1; 
	    $where['goodsId'] = ['in',$ids];
        return Db::name('goods')
                   ->where($where)->field('goodsId,goodsName,goodsImg,saleNum,shopPrice')
                   ->paginate((int)input('pagesize'))->toArray();
	}
}
