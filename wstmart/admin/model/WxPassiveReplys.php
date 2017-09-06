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
 * 微信被动回复业务处理
 */
class WxPassiveReplys extends Base{
	/**
     * 文本消息分页
     */
    public function textPageQuery(){
        return $this->where(['dataFlag'=>1,'msgType'=>'text'])->field(true)->order('id desc')->paginate(input('pagesize/d'));
    }
    /**
     * 图文消息分页
     */
    public function newsPageQuery(){
        $rs = $this->where(['dataFlag'=>1,'msgType'=>'news'])->field(true)->order('id desc')->paginate(input('pagesize/d'));
        return WSTReturn('',1,$rs);
    }

    public function getById($id){
        return $this->get(['id'=>$id,'dataFlag'=>1]);
    }



    /**
     * 新增
     */
    public function add(){
        $data = input('post.');
        $data['createTime'] = date('Y-m-d H:i:s');
        WSTUnset($data,'id');
       
        $result = $this->allowField(true)->save($data);
        if(false !== $result){
            return WSTReturn("新增成功", 1);
        }
        return WSTReturn($this->getError(), -1);

    }
    /**
     * 编辑
     */
    public function edit(){
        $Id = (int)input('post.id');
        $data = input('post.');
        WSTUnset($data,'createTime');
        $result = $this->allowField(true)->save($data,['id'=>$Id]);
        if(false !== $result){
            return WSTReturn("编辑成功", 1);
        }
        return WSTReturn('编辑失败',-1);
    }
    /**
     * 删除
     */
    public function del(){
        $id = (int)input('post.id/d');
        $data = [];
        $data['dataFlag'] = -1;
        $result = $this->update($data,['id'=>$id]);
        if(false !== $result){
            return WSTReturn("删除成功", 1);
        }
        return WSTReturn('编辑失败',-1);
    }
}
