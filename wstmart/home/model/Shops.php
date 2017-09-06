<?php
namespace wstmart\home\model;
use wstmart\common\model\Shops as CShops;
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
use think\Db;
class Shops extends CShops{
    /**
     *  获取店铺的默认运费
     */
    public function getShopsFreight($shopId){
    	return $this->where(["dataFlag"=>1,"shopId"=>$shopId])->field('freight')->find();
    }
    
    /**
     * 店铺街列表
     */
    public function pageQuery($pagesize){
    	$catId = input("get.id/d");
    	$keyword = input("keyword");
    	$userId = (int)session('WST_USER.userId');
    	$rs = $this->alias('s');
    	$where = [];
    	$where['s.dataFlag'] = 1;
    	$where['s.shopStatus'] = 1;
    	if($keyword!='')$where['s.shopName'] = ['like','%'.$keyword.'%'];
    	if($catId>0){
    		$rs->join('__CAT_SHOPS__ cs','cs.shopId = s.shopId','left');
    		$where['cs.catId'] = $catId;
    	}
    	$page = $rs->join('__SHOP_SCORES__ ss','ss.shopId = s.shopId','left')
    	->join('__USERS__ u','u.userId = s.userId','left')
    	->join('__FAVORITES__ f','f.userId = '.$userId.' and f.favoriteType=1 and f.targetId=s.shopId','left')
    	->where($where)
    	->order('s.shopId asc')
    	->field('s.shopId,s.shopImg,s.shopName,s.shopTel,s.shopQQ,s.shopWangWang,s.shopCompany,ss.totalScore,ss.totalUsers,ss.goodsScore,ss.goodsUsers,ss.serviceScore,ss.serviceUsers,ss.timeScore,ss.timeUsers,.u.loginName,f.favoriteId,s.areaIdPath')
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
    		$page['Rows'][$key]['totalScore'] = WSTScore($v["totalScore"], $v["totalUsers"]);
    		$page['Rows'][$key]['goodsScore'] = WSTScore($v['goodsScore'],$v['goodsUsers']);
    		$page['Rows'][$key]['serviceScore'] = WSTScore($v['serviceScore'],$v['serviceUsers']);
    		$page['Rows'][$key]['timeScore'] = WSTScore($v['timeScore'],$v['timeUsers']);
    		//商品列表
    		$goods = Db::name('goods')->where(['dataFlag'=> 1,'goodsStatus'=>1,'isSale'=>1,'shopId'=> $v["shopId"]])->field('goodsId,goodsName,shopPrice,goodsImg')->limit(10)->order('saleTime desc')->select();
    		$page['Rows'][$key]['goods'] = $goods;
    		//店铺商品总数
    		$page['Rows'][$key]['goodsTotal'] = count($goods);
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
    public function getShopSummary($shopId){
    	$shop = $this->alias('s')->join('__SHOP_SCORES__ cs','cs.shopId = s.shopId','left')
    	           ->where(['s.shopId'=>$shopId,'dataFlag'=>1])
    	->field('s.shopMoney,s.noSettledOrderFee,s.paymentMoney,s.shopId,shopImg,shopName,shopAddress,shopQQ,shopTel,serviceStartTime,serviceEndTime,cs.*')
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
    	
        //查看商家钱包是否足够钱
        $USER = session('WST_USER');
        $USER['shopMoney'] = $shop['shopMoney'];
        $USER['noSettledOrderFee'] = $shop['noSettledOrderFee'];
        $USER['paymentMoney'] = $shop['paymentMoney'];
        session('WST_USER',$USER);
        
        
        $stat = array();
        $date = date("Y-m-d");
        $userId = session('WST_USER.userId');
        /**********今日动态**********/
        //待查看消息数
        $stat['messageCnt'] = Db::name('messages')->where(['receiveUserId'=>$userId,'msgStatus'=>0,'dataFlag'=>1])->count();
        //今日销售金额
        $stat['saleMoney'] = Db::name('orders')->where(['shopId'=>$shopId,'orderStatus'=>['egt',0],'dataFlag'=>1])->whereTime('createTime', 'between', [$date.' 00:00:00', $date.' 23:59:59'])->sum("goodsMoney");
        //今日订单数
        $stat['orderCnt'] = Db::name('orders')->where(['shopId'=>$shopId,'orderStatus'=>['egt',0],'dataFlag'=>1])->whereTime('createTime', 'between', [$date.' 00:00:00', $date.' 23:59:59'])->count();
        //待发货订单
        $stat['waitDeliveryCnt'] = Db::name('orders')->where(['shopId'=>$shopId,'orderStatus'=>0,'dataFlag'=>1])->count();
        //库存预警
        $goodsn = Db::name('goods')->where('shopId ='.$shopId.' and dataFlag = 1 and goodsStock <= warnStock and isSpec = 0 and warnStock>0')->cache('stockWarnCnt1',3600)->count();
        $specsn = Db::name('goods_specs')->where('shopId ='.$shopId.' and dataFlag = 1 and specStock <= warnStock and warnStock>0')->cache('stockWarnCnt2',3600)->count();
        $stat['stockWarnCnt'] = $goodsn+$specsn;
        
        /**********商品信息**********/
        //商品总数
        $stat['goodsCnt'] = Db::name('goods')->where(['shopId'=>$shopId,'dataFlag'=>1])->cache('goodsCnt',3600)->count();
        //上架商品
        $stat['onSaleCnt'] = Db::name('goods')->where(['shopId'=>$shopId,'dataFlag'=>1,'goodsStatus'=>1,'isSale'=>1])->cache('onSaleCnt',3600)->count();
        //待审核商品
        $stat['waitAuditCnt'] = Db::name('goods')->where(['shopId'=>$shopId,'dataFlag'=>1,'goodsStatus'=>0])->cache('waitAuditCnt',3600)->count();
        //仓库中的商品
        $stat['unSaleCnt'] = Db::name('goods')->where(['shopId'=>$shopId,'dataFlag'=>1,'goodsStatus'=>1,'isSale'=>0])->cache('unSaleCnt',3600)->count();
        //违规商品
        $stat['illegalCnt'] = Db::name('goods')->where(['shopId'=>$shopId,'dataFlag'=>1,'goodsStatus'=>-1])->cache('illegalCnt',3600)->count();
        //今日新品
        $stat['newGoodsCnt'] = Db::name('goods')->where(['shopId'=>$shopId,'dataFlag'=>1,'goodsStatus'=>1,'isSale'=>1,'isNew'=>1])->cache('newGoodsCnt',3600)->count();
        
        /**********订单信息**********/
        //待付款订单
        $stat['orderNeedpayCnt'] = Db::name('orders')->where(['userId'=>$userId,'orderStatus'=>-2,'dataFlag'=>1])->count();
        //待结束订单
        $stat['orderWaitCloseCnt'] = Db::name('orders')->where(['userId'=>$userId,'orderStatus'=>2,'dataFlag'=>1,'isClosed'=>0])->cache('orderWaitCloseCnt',3600)->count();
        //退货退款订单
        $stat['orderRefundCnt'] = Db::name('orders')->alias('o')->join('order_refunds orf','orf.orderId=o.orderId')->where(['shopId'=>$shopId,'refundStatus'=>0,'o.dataFlag'=>1])->count();
        //待评价订单
        $stat['orderWaitAppraisesCnt'] = Db::name('orders')->where(['shopId'=>$shopId,'orderStatus'=>2,'dataFlag'=>1,'isAppraise'=>0])->cache('orderWaitAppraisesCnt',3600)->count();

    	return ['shop'=>$shop,'stat'=>$stat];
    }    
    /**
     * 获取店铺信息
     */
	public function getByView($id){
		$shop = $this->alias('s')->join('__BANKS__ b','b.bankId=s.bankId','left')
		             ->where(['s.dataFlag'=>1,'shopId'=>$id])
		             ->field('s.*,b.bankName')->find();
	     $areaIds = [];
        $areaMaps = [];
        $tmp = explode('_',$shop['areaIdPath']);
        foreach ($tmp as $vv){
         	if($vv=='')continue;
         	if(!in_array($vv,$areaIds))$areaIds[] = $vv;
        }
        if(!empty($areaIds)){
	         $areas = Db::name('areas')->where(['dataFlag'=>1,'areaId'=>['in',$areaIds]])->field('areaId,areaName')->select();
	         foreach ($areas as $v){
	         	 $areaMaps[$v['areaId']] = $v['areaName'];
	         }
	         $tmp = explode('_',$shop['areaIdPath']);
	         $areaNames = [];
		     foreach ($tmp as $vv){
	         	 if($vv=='')continue;
	         	 $areaNames[] = $areaMaps[$vv];
	         	 $shop['areaName'] = implode('',$areaNames);
	         }
         }             
		                          
		//获取经营范围
		$goodsCats = Db::name('goods_cats')->where(['parentId'=>0,'isShow'=>1,'dataFlag'=>1])->field('catId,catName')->select();
		$catshops = Db::name('cat_shops')->where('shopId',$id)->select();
		$catshopMaps = [];
		foreach ($goodsCats as $v){
			$catshopMaps[$v['catId']] = $v['catName'];
		}
		$catshopNames = [];
		foreach ($catshops as $key =>$v){
			if(isset($catshopMaps[$v['catId']]))$catshopNames[] = $catshopMaps[$v['catId']];
		}
		$shop['catshopNames'] = implode('、',$catshopNames);
		//获取认证类型
	    $shop['accreds'] =Db::name('shop_accreds')->alias('sac')->join('__ACCREDS__ a','sac.accredId=a.accredId and a.dataFlag=1','inner')
	                    ->where('sac.shopId',$id)->field('accredName,accredImg')->select();
	    //开卡地址
        $areaNames  = model('areas')->getParentNames($shop['bankAreaId']);
        $shop['bankAreaName'] = implode('',$areaNames);
		return $shop;
	}
    /**
     * 获取店铺指定字段
     */
    public function getFieldsById($shopId,$fields){
        return $this->where(['shopId'=>$shopId,'dataFlag'=>1])->field($fields)->find();
    }

    /**
     * 编辑店铺资料
     */
    public function editInfo(){
        $shopId = (int)session('WST_USER.shopId');
        $result = $this->validate('Shops.editInfo')->allowField(['shopImg','isInvoice','invoiceRemarks','serviceStartTime','serviceEndTime','freight','shopQQ','shopWangWang'])->save(input('post.'),['shopId'=>$shopId]);
        if(false !== $result){
             return WSTReturn('操作成功!',1);
        }else{
             return WSTReturn($this->getError());
        }
    }

    /**
     * 获取店铺提现账号
     */
    public function getShopAccount(){
        $shopId = (int)session('WST_USER.shopId');
        $shops = Db::name('shops')->alias('s')->join('banks b','b.bankId=s.bankId','inner')->where('s.shopId',$shopId)->field('b.bankName,s.bankAreaId,bankNo,bankUserName')->find();
        return $shops;
    }
}
