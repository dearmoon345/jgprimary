<?php
namespace wstmart\mobile\model;
use wstmart\common\model\Shops as CShops;
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
 * 门店类
 */
class Shops extends CShops{
    /**
     * 店铺街列表
     */
    public function pageQuery($pagesize){
    	$catId = (int)input("id");
    	$keyword = input("keyword");
    	$condition = input("condition");
    	$desc = input("desc");
    	$datas = array('sort'=>array('1'=>'ss.totalScore/ss.totalUsers'),'desc'=>array('desc','asc'));
    	$rs = $this->alias('s');
    	$where = [];
    	$where['s.dataFlag'] = 1;
    	$where['s.shopStatus'] = 1;
    	if($keyword!='')$where['s.shopName'] = ['like','%'.$keyword.'%'];
    	if($catId>0){
    		$rs->join('__CAT_SHOPS__ cs','cs.shopId = s.shopId','left');
    		$where['cs.catId'] = $catId;
    	}
    	$order = ['s.shopId'=>'asc'];
    	if($condition>0){
    		$order = [$datas['sort'][$condition]=>$datas['desc'][$desc]];
    	}
    	$page = $rs->join('__SHOP_SCORES__ ss','ss.shopId = s.shopId','left')
    	->where($where)->order($order)
    	->field('s.shopId,s.shopImg,s.shopName,s.shopCompany,ss.totalScore,ss.totalUsers,ss.goodsScore,ss.goodsUsers,ss.serviceScore,ss.serviceUsers,ss.timeScore,ss.timeUsers,s.areaIdPath')
    	->paginate($pagesize)->toArray();
    	if(empty($page['Rows']))return $page;
    	$shopIds = [];
    	$areaIds = [];
    	foreach ($page['Rows'] as $key =>$v){
    		$shopIds[] = $v['shopId'];
    		$tmp = explode('_',$v['areaIdPath']);
    		$areaIds[] = $tmp[1];
    		$page['Rows'][$key]['areaId'] = $tmp[1];
    		//总评分
    		$page['Rows'][$key]['totalScore'] = WSTScore($v["totalScore"]/3, $v["totalUsers"]);
    		$page['Rows'][$key]['goodsScore'] = WSTScore($v['goodsScore'],$v['goodsUsers']);
    		$page['Rows'][$key]['serviceScore'] = WSTScore($v['serviceScore'],$v['serviceUsers']);
    		$page['Rows'][$key]['timeScore'] = WSTScore($v['timeScore'],$v['timeUsers']);
		}
		$rccredMap = [];
		$goodsCatMap = [];
		$areaMap = [];
		//认证、地址、分类
		if(!empty($shopIds)){
			$rccreds = Db::name('shop_accreds')->alias('sac')->join('__ACCREDS__ a','a.accredId=sac.accredId and a.dataFlag=1','left')
			             ->where('shopId','in',$shopIds)->field('sac.shopId,accredName,accredImg')->select();
			foreach ($rccreds as $v){
				$rccredMap[$v['shopId']][] = $v;
			}
			$goodsCats = Db::name('cat_shops')->alias('cs')->join('__GOODS_CATS__ gc','cs.catId=gc.catId and gc.dataFlag=1','left')
			               ->where('shopId','in',$shopIds)->field('cs.shopId,gc.catName')->select();
		    foreach ($goodsCats as $v){
				$goodsCatMap[$v['shopId']][] = $v['catName'];
			}
			$areas = Db::name('areas')->alias('a')->join('__AREAS__ a1','a1.areaId=a.parentId','left')
			           ->where('a.areaId','in',$areaIds)->field('a.areaId,a.areaName areaName2,a1.areaName areaName1')->select();
		    foreach ($areas as $v){
				$areaMap[$v['areaId']] = $v;
			}         
		}
		foreach ($page['Rows'] as $key =>$v){
			$page['Rows'][$key]['accreds'] = (isset($rccredMap[$v['shopId']]))?$rccredMap[$v['shopId']]:[];
			$page['Rows'][$key]['catshops'] = (isset($goodsCatMap[$v['shopId']]))?implode(',',$goodsCatMap[$v['shopId']]):'';
			$page['Rows'][$key]['areas'] = ['areaName1'=>$areaMap[$v['areaId']]['areaName1'],'areaName2'=>$areaMap[$v['areaId']]['areaName2']];
		}
    	return $page;
    }
    /**
     * 获取卖家中心信息
     */
    public function getShopSummary(){
        $shopId = (int)input('shopId',1);
        $shop = $this->alias('s')->join('__SHOP_SCORES__ cs','cs.shopId = s.shopId','left')
                   ->where(['s.shopId'=>$shopId,'dataFlag'=>1])
        ->field('s.shopId,shopImg,shopName,shopkeeper,shopAddress,shopQQ,shopTel,serviceStartTime,serviceEndTime,isInvoice,invoiceRemarks,cs.*')
        ->find();
        //评分
        $scores['totalScore'] = WSTScore($shop['totalScore'],$shop['totalUsers']);
        $scores['goodsScore'] = WSTScore($shop['goodsScore'],$shop['goodsUsers']);
        $scores['serviceScore'] = WSTScore($shop['serviceScore'],$shop['serviceUsers']);
        $scores['timeScore'] = WSTScore($shop['timeScore'],$shop['timeUsers']);
        WSTUnset($shop, 'totalUsers,goodsUsers,serviceUsers,timeUsers');
        $shop['scores'] = $scores;
        //认证
        $accreds = $this->shopAccreds($shopId);
        $shop['accreds'] = $accreds;
        return ['shop'=>$shop];
    }
    /**
    * 自营店铺楼层
    */
    public function getFloorData(){
        $limit = (int)input('post.currPage');
        if($limit>4)return;
        $cacheData = cache('WX_SHOP_FLOOR'.$limit);
        if($cacheData)return $cacheData;
        $rs = Db::name('shop_cats')->where(['dataFlag'=>1,'isShow'=>1,'parentId'=>0,'shopId'=>1])->field('catId,catName')->order('catSort asc')->limit($limit,1)->select();
        if($rs){
            $rs= $rs[0];
            $goods = Db::name('goods')->where('shopCatId1','=',$rs['catId'])->where(['dataFlag'=>1,'goodsStatus'=>1])->field('goodsId,goodsName,goodsImg,shopPrice,saleNum')->order('isHot desc')->limit(4)->select();
            $rs['goods'] = $goods;
            $rs['currPage'] = $limit;
        }
        cache('WX_SHOP_FLOOR'.$limit,$rs,86400);
        return $rs;
    }
    /**
    * 根据订单id 获取店铺名称
    */
    public function getShopName($oId){
        return $this->alias('s')
                    ->join('__ORDERS__ o',"s.shopId=o.shopId",'inner')
                    ->where("o.orderId=$oId")
                    ->value('s.shopName');

    }
}
