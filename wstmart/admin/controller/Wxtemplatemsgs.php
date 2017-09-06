<?php
namespace wstmart\admin\controller;
use wstmart\admin\model\TemplateMsgs as M;
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
 * 微信消息模板控制器
 */
class Wxtemplatemsgs extends Base{
	
    public function index(){
    	return $this->fetch("list");
    }
    /**
     * 获取分页
     */
    public function pageQuery(){
        $m = new M();
        return $m->pageQuery(3,9);
    }
    /**
     * 跳转去新增页面
     */ 
    public function toEdit(){
        $id = (int)input('id');
        $m = new M();
        if($id>0){
            $data = $m->getById($id);
        }else{
            $data = $m->getEModel('template_msgs');
        }
        $this->assign('object',$data);
        return $this->fetch("edit");
    }

    /**
    * 发送消息
    */
    public function edit(){
        return model('WxTemplateParams')->edit();
    }

    /**
     * 获取参数列表
     */
    public function listQuery(){
        return model('WxTemplateParams')->listQuery((int)input('post.parentId'));
    }

}
