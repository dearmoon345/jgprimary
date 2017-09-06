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
 * 微信消息参数模板业务处理
 */
class WxTemplateParams extends Base{
	/**
	 * 分页
	 */
	public function listQuery($parentId){
		$rs =  $this->where('parentId',$parentId)->select();
		return WSTReturn('',1,$rs);
	}
    /**
	 * 编辑
	 */
	public function edit(){
		$id = (int)input('post.id/d');
		$tplCode = input('post.tplCode');
		$data = [];
		$data['tplContent'] = input('post.tplContent');
		$data['tplExternaId'] = input('post.tplExternaId');
	    $result = model('admin/TemplateMsgs')->save($data,['id'=>$id,'tplCode'=>$tplCode]);
        if(false !== $result){
        	cache('WST_MSG_TEMPLATES',null);
        	$this->where('parentId',$id)->delete();
        	$num = (int)input('num');
        	if($num>0){
        		$tdata = [];
        		for($i=0;$i<=$num;$i++){
        			$code = input('code_'.$i);
        			if($code=='')continue;
        			$data = [];
        			$data['parentId'] = $id;
                    $data['fieldCode'] = $code;
                    $data['fieldVal'] = input('val_'.$i);
                    $tdata[] = $data;
        		}
        		if(count($tdata)>0)$this->saveAll($tdata);
        	}
        	return WSTReturn("编辑成功", 1);
        }else{
        	return WSTReturn($this->getError(),-1);
        }
	}
	
}
