<?php
namespace wstmart\admin\model;
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
 * 消息模板业务处理
 */
class TemplateMsgs extends Base{
	/**
	 * 分页
	 */
	public function pageQuery($tplType,$dataType){
		$page =  $this->where(['dataFlag'=>1,'tplType'=>$tplType])->paginate(input('pagesize/d'))->toArray();
		if(count($page['Rows'])>0){
			foreach($page['Rows'] as $key =>$data){
                $d = WSTDatas($dataType,$data['tplCode']);
                $page['Rows'][$key]['tplCode'] = $d[$data['tplCode']]['dataName'];
			}
		}
		return $page;
	}
	public function pageEmailQuery(){
		$page =  $this->where(['dataFlag'=>1,'tplType'=>1])->paginate(input('pagesize/d'))->toArray();
		if(count($page['Rows'])>0){
			foreach($page['Rows'] as $key =>$data){
                $d = WSTDatas(7,$data['tplCode']);
                $page['Rows'][$key]['tplCode'] = $d[$data['tplCode']]['dataName'];
                $page['Rows'][$key]['tplContent'] = strip_tags(htmlspecialchars_decode($data['tplContent']));
			}
		}
		return $page;
	}
	/**
	 * 获取角色权限
	 */
	public function getById($id){
		return $this->get(['dataFlag'=>1,'id'=>$id]);
	}
    /**
	 * 编辑
	 */
	public function edit(){
		$id = (int)input('post.id/d');
		$tplCode = input('post.tplCode');
		$data = [];
		$data['tplContent'] = input('post.tplContent');
	    $result = $this->save($data,['id'=>$id,'tplCode'=>$tplCode]);
        if(false !== $result){
        	cache('WST_MSG_TEMPLATES',null);
        	return WSTReturn("编辑成功", 1);
        }else{
        	return WSTReturn($this->getError(),-1);
        }
	}
	
}
