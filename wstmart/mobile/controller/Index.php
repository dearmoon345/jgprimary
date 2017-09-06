<?php
namespace wstmart\mobile\controller;
use wstmart\mobile\model\Index as M;
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
 * 默认控制器
 */
class Index extends Base{
	/**
     * 首页
     */
    public function index(){
        $param = request() -> param();
        //手机端自动登陆商城
        if (!empty($param['user_id'])) {
            $userToken = db('user_token');
            $param['token'] = str_replace('\/', '/', $param['token']);
            $arr = $userToken -> field('login_time') -> where('member_id', $param['user_id']) -> where('token', $param['token']) -> find();
            if ($arr) {
                if ((time() - intval($arr['login_time'])) < 7*24*3600) {
                    session('WST_USER.userName',$param['user_name']);
                    session('WST_USER.userId',$param['user_id']);
                    session('clientType',1);
                }else {
                    session('WST_USER.userName',null);
                    session('WST_USER.userId',null);
                }
            }else {
                session('WST_USER.userName',null);
                session('WST_USER.userId',null);
            }
        }
    	$m = new M();
    	hook('mobileControllerIndexIndex',['getParams'=>input()]);
    	$news = $m->getSysMsg('msg');
    	$this->assign('news',$news);
    	return $this->fetch('index');
    }
    /**
     * 楼层
     */
    public function pageQuery(){
    	$m = new M();
    	$rs = $m->pageQuery();
    	if(isset($rs['goods'])){
    		foreach ($rs['goods'] as $key =>$v){
    			$rs['goods'][$key]['goodsImg'] = WSTImg($v['goodsImg'],2);
    		}
    	}
        return $rs;
    }

    /**
     * 转换url
     */
    public function transfor(){
        $data = input('param.');
        $url = $data['url'];
        unset($data['url']);
        echo Url($url,$data);
    }
}
