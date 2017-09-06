<?php
namespace wstmart\admin\model;
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
 * 退款订单业务处理类
 */
class OrderRefunds extends Base{
	
    /**
	 * 获取用户退款订单列表
	 */
	public function refundPageQuery(){
		$where = ['o.dataFlag'=>1];
		$where['orderStatus'] = ['in',[-1,-3]];
		$orderNo = input('orderNo');
		$shopName = input('shopName');
		$deliverType = (int)input('deliverType',-1);
		$areaId1 = (int)input('areaId1');
		if($areaId1>0){
			$where['s.areaIdPath'] = ['like',"$areaId1%"];
			$areaId2 = (int)input("areaId1_".$areaId1);
			if($areaId2>0)$where['s.areaIdPath'] = ['like',$areaId1."_"."$areaId2%"];
			$areaId3 = (int)input("areaId1_".$areaId1."_".$areaId2);
			if($areaId3>0)$where['s.areaId'] = $areaId3;
		}
		$isRefund = (int)input('isRefund',-1);
		if($orderNo!='')$where['orderNo'] = ['like','%'.$orderNo.'%'];
		if($shopName!='')$where['shopName|shopSn'] = ['like','%'.$shopName.'%'];
		
		if($deliverType!=-1)$where['o.deliverType'] = $deliverType;
		if($isRefund!=-1)$where['o.isRefund'] = $isRefund;
		$page = Db::name('orders')->alias('o')->join('__SHOPS__ s','o.shopId=s.shopId','left')
		     ->join('__USERS__ u','o.userId=u.userId','left')
		     ->join('__ORDER_REFUNDS__ orf ','o.orderId=orf.orderId and refundStatus in (1,2)') 
		     ->where($where)
		     ->field('orf.id refundId,o.orderId,o.orderNo,s.shopName,s.shopId,s.shopQQ,s.shopWangWang,o.goodsMoney,o.totalMoney,o.realTotalMoney,
		              o.orderStatus,u.loginName,o.deliverType,payType,payFrom,o.orderStatus,orderSrc,orf.backMoney,orf.refundRemark,isRefund,orf.createTime')
			 ->order('orf.createTime', 'desc')
			 ->paginate(input('pagesize/d'))->toArray();
	    if(count($page['Rows'])>0){
	    	 foreach ($page['Rows'] as $key => $v){
	    	 	 $page['Rows'][$key]['payType'] = WSTLangPayType($v['payType']);
	    	 	 $page['Rows'][$key]['deliverType'] = WSTLangDeliverType($v['deliverType']==1);
	    	 	 $page['Rows'][$key]['status'] = WSTLangOrderStatus($v['orderStatus']);
	    	 }
	    }
	    return $page;
	}
	/**
	 * 获取退款资料
	 */
	public function getInfoByRefund(){
		return $this->alias('orf')->join('__ORDERS__ o','orf.orderId=o.orderId')->where(['orf.id'=>(int)input('get.id'),'isRefund'=>0,'orderStatus'=>['in',[-1,-3]],'refundStatus'=>1])
		         ->field('orf.id refundId,orderNo,o.orderId,goodsMoney,refundReson,refundOtherReson,totalMoney,realTotalMoney,deliverMoney,payType,payFrom,backMoney,o.useScore,o.scoreMoney,tradeNo')
		         ->find();
	}
	/**
	 * 退款
	 */
	public function orderRefund(){
		$id = (int)input('post.id');
		$content = input('post.content');
		if($id==0)return WSTReturn("操作失败!");
		$refund = $this->get($id);
		if(empty($refund) || $refund->refundStatus!=1)return WSTReturn("该退款订单不存在或已退款!");
		Db::startTrans();
        try{
        	$order = model('orders')->get($refund->orderId);
        	if(!in_array($order->orderStatus,[-1,-3]) && ($order->isPay==1 || ($order->payType==0 && $oder->useScore>0)))return WSTReturn("无效的退款订单!");
			//修改退款单信息
			$refund->refundRemark = $content;
			$refund->refundTime = date('Y-m-d H:i:s');
			$refund->refundStatus = 2;
			$refund->save();
			//修改订单状态
			$order->isRefund = 1;
			$order->save();	
			//创建用户资金流水记录
			if($refund->backMoney>0){
				$lm = [];
				$lm['targetType'] = 0;
				$lm['targetId'] = $order->userId;
				$lm['dataId'] = $order->orderId;
				$lm['dataSrc'] = 1;
				$lm['remark'] = '订单【'.$order->orderNo.'】退款¥'.$refund->backMoney."。".(($content!='')?"【退款备注】：".$content:'');
				$lm['moneyType'] = 1;
				$lm['money'] = $refund->backMoney;
				$lm['payType'] = 0;
				$lm['createTime'] = date('Y-m-d H:i:s');
				model('common/LogMoneys')->add($lm);
			}
			
			if($order->useScore>0){
				$score = [];
				$score['userId'] = $order->userId;
				$score['score'] = $order->useScore;
				$score['dataSrc'] = 4;
				$score['dataId'] = $id;
				$score['dataRemarks'] = "返还订单【".$order->orderNo."】积分".$order->useScore."个";
				$score['scoreType'] = 1;
				model('common/UserScores')->add($score);
			}
			//发送一条用户信息
			$tpl = WSTMsgTemplates('ORDER_REFUND_SUCCESS');
	        if($tpl['tplContent']!=''){
	            $find = ['${ORDER_NO}','${REMARK}'];
	            $replace = [$order->orderNo,$content];
	            WSTSendMsg($order->userId,str_replace($find,$replace,$tpl['tplContent']),['from'=>1,'dataId'=>$order->orderId]);
	        } 
			//微信消息
			if((int)WSTConf('CONF.wxenabled')==1){
				$reasonData = WSTDatas(4,$refund->refundReson);
				$params = [];
				$params['ORDER_NO'] = $order->orderNo;
				$params['REASON'] = $reasonData['dataName'].(($refund->refundReson==10000)?" - ".$refund->refundOtherReson:"");           
				$params['MONEY'] = $refund->backMoney.(($order['useScore']>0)?("【退回积分：".$order['useScore']."】"):"");
				WSTWxMessage(['CODE'=>'WX_ORDER_REFUND_SUCCESS','userId'=>$order->userId,'params'=>$params]);
			}
			//如果有钱剩下，那么就退回到商家钱包
			$shopMoneys = $order->realTotalMoney-$refund->backMoney;
			if($shopMoneys>0){
                //创建商家资金流水
                $lm = [];
				$lm['targetType'] = 1;
				$lm['targetId'] = $order->shopId;
				$lm['dataId'] = $order->orderId;
				$lm['dataSrc'] = 1;
				$lm['remark'] = '订单【'.$order->orderNo.'】退款，返回商家金额¥'.$shopMoneys."。";
				$lm['moneyType'] = 1;
				$lm['money'] = $shopMoneys;
				$lm['payType'] = 0;
				$lm['createTime'] = date('Y-m-d H:i:s');
				model('LogMoneys')->add($lm);
				//发送商家信息
				$tpl = WSTMsgTemplates('ORDER_SHOP_REFUND');
		        if($tpl['tplContent']!=''){
		            $find = ['${ORDER_NO}','${MONEY}'];
		            $replace = [$order->orderNo,$shopMoneys];
		            $shop = model('shops')->where('shopId',$order->shopId)->field('userId')->find();
		            WSTSendMsg($shop['userId'],str_replace($find,$replace,$tpl['tplContent']),['from'=>1,'dataId'=>$order->orderId]);
		        } 
		        //微信消息
				if((int)WSTConf('CONF.wxenabled')==1){
					$reasonData = WSTDatas(4,$refund->refundReson);
					$params = [];
					$params['ORDER_NO'] = $order->orderNo;
					$params['REASON'] = $reasonData['dataName'].(($refund->refundReson==10000)?" - ".$refund->refundOtherReson:"");
					$params['SHOP_MONEY'] = $shopMoneys;             
				    $params['MONEY'] = $refund->backMoney.(($order['useScore']>0)?("【退回积分：".$order['useScore']."】"):"");
				    WSTWxMessage(['CODE'=>'WX_ORDER_SHOP_REFUND','userId'=>$order['userId'],'params'=>$params]);
				} 
			}
			Db::commit();
			return WSTReturn("操作成功",1); 
        }catch (\Exception $e) {
            Db::rollback();
        }
		return WSTReturn("操作失败，请刷新后再重试"); 
	}
}
