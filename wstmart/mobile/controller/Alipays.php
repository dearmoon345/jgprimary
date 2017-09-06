<?php
namespace wstmart\mobile\controller;
use think\Loader;
use wstmart\common\model\Payments as M;
use wstmart\mobile\model\Orders as OM;
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
 * 阿里支付控制器
 */
class Alipays extends Base{

	/**
	 * 初始化
	 */
	private $alipayConfig;
	public function _initialize() {
		header ("Content-type: text/html; charset=utf-8");
		Loader::import ( 'alipay.Corefunction' );
    	Loader::import ( 'alipay.Md5function' );
    	Loader::import ( 'alipay.AlipayNotify' );
    	Loader::import ( 'alipay.AlipaySubmit' );
    	$m = new M();
    	$payment = $m->getPayment("alipays");
    	$this->alipayConfig = array(
    			'partner' =>$payment['parterID'],   //这里是你在成功申请支付宝接口后获取到的PID；
    			'key'=>$payment['parterKey'],//这里是你在成功申请支付宝接口后获取到的Key
    			'seller_email'=>$payment['payAccount'],
    			'sign_type'=>strtoupper('MD5'),
    			'input_charset'=> strtolower('utf-8'),
    			'cacert'=>'',
    			'transport'=> 'http'
    	);
	}
	
    /**
     * 支付宝支付跳转方法
     */
    public function toAliPay(){
    	$data = [];
    	$data['orderNo'] = input('orderNo');
    	$data['isBatch'] = (int)input('isBatch');
    	$data['userId'] = (int)session('WST_USER.userId');
    	$m = new OM();
    	$rs = $m->getOrderPayInfo($data);
    	if(empty($rs)){
    		$this->assign('type','');
    		return $this->fetch("users/orders/orders_list");
    	}else{
	    	$m = new M();
	    	$om = new OM();
	    	$userId = (int)session('WST_USER.userId');
	    	$obj["userId"] = $userId;
	    	$obj["orderNo"] = input("orderNo/s");
			$obj["isBatch"] = (int)input("isBatch/d");
			$data = $om->checkOrderPay($obj);
	    	if($data["status"]==-1){
	    		echo "<span style='font-size:40px;'>您的订单已支付，不要重复支付！</span>";
	    		return;
	    	}else if($data["status"]==-2){
	    		echo "<span style='font-size:40px;'>您的订单因商品库存不足，不能支付！</span>";
	    		return;
	    	}
	    	
	    	 
	    	$format = "xml";
	    	$v = "2.0";
	    	$req_id = date('Ymdhis');
	    	
	    	$call_back_url = url("mobile/orders/index","",true,true);
	    	$notify_url = url("mobile/alipays/aliNotify","",true,true);
	    	$subject = '支付购买商品费用';
	    	$merchant_url = "";
	    	$seller_email = $this->alipayConfig['seller_email'];
	    	
	    
	    	$order = $om->getPayOrders($obj);
	    	$total_fee = $order["needPay"];
	    	$payRand = $order["payRand"];
	    	$out_trade_no = $obj["orderNo"]."a".$payRand;
	    	$req_data = '<direct_trade_create_req><notify_url>' . $notify_url . '</notify_url><call_back_url>' . $call_back_url . '</call_back_url><seller_account_name>' . $seller_email . '</seller_account_name><out_trade_no>' . $out_trade_no . '</out_trade_no><subject>' . $subject . '</subject><total_fee>' . $total_fee . '</total_fee><merchant_url>' . $merchant_url . '</merchant_url></direct_trade_create_req>';
	    
	    	//构造要请求的参数数组，无需改动
	    	$para_token = array(
	    		"service" => "alipay.wap.trade.create.direct",
	    		"partner" => trim($this->alipayConfig['partner']),
	    		"sec_id" => trim($this->alipayConfig['sign_type']),
	    		"format"	=> $format,
	    		"v"	=> $v,
	    		"req_id"	=> $req_id,
	    		"req_data"	=> $req_data,
	    		"_input_charset"	=> trim(strtolower($this->alipayConfig['input_charset']))
	    	);
	    	//建立请求
	    	$alipaySubmit = new \AlipaySubmit($this->alipayConfig);
	    	$html_text = $alipaySubmit->buildRequestHttp($para_token);
	    	//URLDECODE返回的信息
	    	$html_text = urldecode($html_text);
	    	//解析远程模拟提交后返回的信息
	    	$para_html_text = $alipaySubmit->parseResponse($html_text);
	    	//获取request_token
	    	$request_token = $para_html_text['request_token'];
	    	//**************************根据授权码token调用交易接口alipay.wap.auth.authAndExecute**************************
	    	//业务详细
	    	$req_data = '<auth_and_execute_req><request_token>' . $request_token . '</request_token></auth_and_execute_req>';
	    	//必填
	
	    	//构造要请求的参数数组，无需改动
	    	$parameter = array(
	    		"service" => "alipay.wap.auth.authAndExecute",
	    		"partner" => trim($this->alipayConfig['partner']),
	    		"sec_id" => trim($this->alipayConfig['sign_type']),
	    		"format"	=> $format,
	    		"v"	=> $v,
	    		"req_id"	=> $req_id,
	    		"req_data"	=> $req_data,
	    		"_input_charset"	=> trim(strtolower($this->alipayConfig['input_charset']))
	    	);
	    	//建立请求
	    	$alipaySubmit = new \AlipaySubmit($this->alipayConfig);
	    	$html_text = $alipaySubmit->buildRequestForm($parameter, 'get', '');
	    	echo $html_text;
    	}
    }
    
    /**
     * 服务器异步通知页面方法
     *
     */
    function aliNotify() {
    	$om = new OM();
    	// 计算得出通知验证结果
    	$alipayNotify = new \AlipayNotify ( $this->alipayConfig );
    	$verify_result = $alipayNotify->verifyNotify ();
    	
    	if ($verify_result) {
    		$notify_data = $_POST['notify_data'];
    		// 获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
    		// 解析notify_data
    		// 注意：该功能PHP5环境及以上支持，需开通curl、SSL等PHP配置环境。建议本地调试时使用PHP开发软件
    		$doc = new \DOMDocument ();
    		$doc->loadXML ( $notify_data );
    		if (! empty ( $doc->getElementsByTagName ( "notify" )->item ( 0 )->nodeValue )) {
    			// 交易号
    			$trade_no = $doc->getElementsByTagName ( "trade_no" )->item ( 0 )->nodeValue;
    			// 商户订单号
    			$out_trade_no = $doc->getElementsByTagName ( "out_trade_no" )->item ( 0 )->nodeValue;
    
    			$total_fee = $doc->getElementsByTagName( "total_fee" )->item(0)->nodeValue;
    			// 支付宝交易号
    			$trade_no = $doc->getElementsByTagName ( "trade_no" )->item ( 0 )->nodeValue;
    			// 交易状态
    			$trade_status = $doc->getElementsByTagName ( "trade_status" )->item ( 0 )->nodeValue;
    			if ($trade_status == 'TRADE_FINISHED' OR $trade_status  == 'TRADE_SUCCESS') {
    				$obj["trade_no"] = $trade_no;
    				$tradeNo = explode("a",$out_trade_no);
      				$obj["out_trade_no"] = $tradeNo[0];
    				$obj["total_fee"] = $total_fee;
    				$obj["payFrom"] = 1;
    				$payFrom = $om->getOrderPayFrom($tradeNo[0]);
    				$obj["userId"] = $payFrom["userId"];
    				$obj["isBatch"] = $payFrom["isBatch"];
    				//支付成功业务逻辑
    				$rs = $om->complatePay($obj);
    				if($rs["status"]==1){
    					echo 'success';
    				}else{
    					echo 'fail';
    				}
    			}
    			echo "success"; // 请不要修改或删除
    		}
    	} else {
    		// 验证失败
    		echo "fail";
    	}
    }

}
