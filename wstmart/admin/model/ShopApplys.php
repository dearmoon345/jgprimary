<?php
namespace wstmart\admin\model;
use Think\Db;
use wstmart\admin\model\Shops;
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
 * 门店申请业务处理
 */
class ShopApplys extends Base{
    /**
	 * 分页
	 */
	public function pageQuery(){
		$page = Db::name('shop_applys')->alias('s')->join('__USERS__ u','s.userId=u.userId and u.dataFlag=1','left')
			->where(['s.dataFlag'=>1])
			->field('u.loginName,s.userId,s.shopId,s.linkman,s.phoneNo,applyStatus,s.createTime,applyDesc,applyId')
			->order('s.applyId', 'desc')
			->paginate(input('pagesize/d'))->toArray();
		return $page;
	}
	
	/**
	 * 获取信息
	 */
	public function getById($id){
		return Db::name('shop_applys')->alias('s')->join('__USERS__ u','s.userId=u.userId and u.dataFlag=1 and s.dataFlag=1','left')
			->where(['s.dataFlag'=>1,'s.applyId'=>$id])
			->field('u.loginName,s.*')->find();
	}
	
	/**
	 * 删除菜单
	 */
	public function del(){
	    $id = input('post.id/d');
		$data = [];
		$data['dataFlag'] = -1;
	    $result = $this->update($data,['applyId'=>$id]);
        if(false !== $result){
        	return WSTReturn("删除成功", 1);
        }else{
        	return WSTReturn($this->getError(),-1);
        }
	}
	
	/**
	 * 处理申请
	 */
	public function handle(){
		$id = input('post.applyId/d');
		$data = [];
		$data['applyStatus'] = input('post.applyStatus/d');
		$data['handleDesc'] = input('post.handleDesc');
		if(!in_array($data['applyStatus'],array(-1,1)))return WSTReturn("无效的处理状态", -1);
		if($data['applyStatus']==-1 && $data['handleDesc']==''){return WSTReturn("请输入申请失败原因", -1);}
		$userInfo = Db::name('shop_applys')->field('phoneNo,userId,createTime')->where("applyId=$id")->find();
	    $userPhone = $userInfo['phoneNo'];
	    $userId = (int)$userInfo['userId'];
		//审核店铺未通过发送通知给用户
		if($data['applyStatus']==-1 && $data['handleDesc']!=''){
			//发送短信消息
	        $tpl = WSTMsgTemplates('PHONE_SHOP_OPEN_FAIL');
	        if($tpl['tplContent']!=''){
	            $params = ['tpl'=>$tpl,'params'=>['MALL_NAME'=>WSTConf("CONF.mallName"),'REASON'=>$data['handleDesc']]];
	            $rv = model('admin/LogSms')->sendSMS(0,$userId,$userPhone,$params,'handle');
	        }
			if($userId>0){
				$user = model('users')->get($userId);
	    		// 会员发送一条商城消息
	    		$tpl = WSTMsgTemplates('SHOP_OPEN_FAIL');
		        if($tpl['tplContent']!=''){
		            $find = ['${LOGIN_NAME}','${MALL_NAME}','${REASON}'];
		            $replace = [$user->loginName,WSTConf("CONF.mallName"),$data['handleDesc']];
		            WSTSendMsg($userId,str_replace($find,$replace,$tpl['tplContent']),['from'=>0,'dataId'=>$id]);
		        }
		        //微信消息
				if((int)WSTConf('CONF.wxenabled')==1){
					$params = [];
					$params['APPLY_TIME'] = $userInfo['createTime'];
					$params['REASON'] = $data['handleDesc'];
					WSTWxMessage(['CODE'=>'WX_SHOP_OPEN_FAIL','userId'=>$userId,'params'=>$params]);
				} 
			}
		}
		$result = $this->where(['applyId'=>$id])->update($data);
        if(false !== $result){
        	return WSTReturn("操作成功", 1);
        }else{
        	return WSTReturn($this->getError(),-1);
        }
	}
	
	/**
	 * 检测该开店申请是否开店
	 */
	public function checkOpenShop($id){
		return Db::name('shop_applys')->alias('s')
			->where(['s.dataFlag'=>1,'applyId'=>$id])
			->field('s.userId,s.shopId')
			->find();
	}
	/**
	 * 修改开店状态
	 */
	public function editApplyOpenStatus($id,$shopId){
		$this->where(['applyId'=>$id,'shopId'=>0])->update(['shopId'=>$shopId]);
	}
}
