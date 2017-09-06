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
 * 微信用户业务处理
 */
class WxUsers extends Base{
	/**
	 * 分页
	 */
	public function pageQuery(){
		$key = input('get.key');
		$where = [];
		if($key!='')$where['userName'] = ['like','%'.$key.'%'];
		return $this->where($where)->order('subscribeTime desc,userId desc')->paginate(input('post.pagesize/d'))->toArray();
	}
	
	/**
	 * 获取指定对象
	 */
	public function getById($id){
		return $this->where(['userId'=>$id])->find();
	}
	
	/**
	 * 与微信用户管理同步
	 */
	public function synchroWx(){
		$this->where('userId>0')->delete();
		$wx = WXAdmin();
		$data = $wx->wxUserGet();
		if(isset($data['errcode'])){
			if($data['errcode']!=0)return WSTReturn('与微信同步失败,请清除缓存重试');
		}
		if(isset($data['data']) && count($data['data']['openid'])>0){
			$dataList = [];
			foreach($data['data']['openid'] as $key=>$v){
				$datas = [];
				$datas['openId'] = $v;
				$datas['userFill'] = -1;
				$dataList[] = $datas;
			}
			$this->insertAll($dataList);
			return WSTReturn("共".$data['total']."个用户需同步", 1,$dataList);
		}
	}
	
	public function wxLoad(){
		$openId = input('post.id');
		$wx = WXAdmin();
		$userInfo = $wx->wxUserInfo($openId);
		if(isset($userInfo['errcode'])){
			if($userInfo['errcode']!=0)return WSTReturn('与微信同步失败,请清除缓存重试');
		}
		$data = [];
		$data['userName'] = $userInfo['nickname'];
		$data['userSex'] = $userInfo['sex'];
		$data['userAreas'] = $userInfo['country'].$userInfo['province'].$userInfo['city'];
		$data['userPhoto'] = $userInfo['headimgurl'];
		$data['userRemark'] = $userInfo['remark'];
		$data['subscribeTime'] = date('Y-m-d H:i:s',$userInfo['subscribe_time']);
		$data['groupId'] = $userInfo['groupid'];
		$data['openId'] = $userInfo['openid'];
		$data['userFill'] = 1;
		$result = $this->update($data,['openId'=>$openId,'userFill'=>-1]);
		if(false !== $result){
			return WSTReturn("", 1);
		}else{
			return WSTReturn($this->getError(),-1);
		}
	}
	
	/**
	 * 编辑
	 */
	public function edit(){
		$userId = input('post.id/d');
		$data = input('post.');
		WSTUnset($data,'userId,userName,userSex,userAreas,userPhoto,subscribeTime,groupId,openId');
		$result = $this->allowField(true)->save($data,['userId'=>$userId]);
		if(false !== $result){
			$info = $this->getById($userId);
			$wdata = [];
			$wdata["openid"] = $info["openId"];
			$wdata["remark"] = $info["userRemark"];
			$wdata = json_encode($wdata,JSON_UNESCAPED_UNICODE);
			$wx = WXAdmin();
			$data = $wx->wxUpdateremark($wdata);
			return WSTReturn("修改成功", 1);
		}else{
			return WSTReturn($this->getError(),-1);
		}
	}
}
