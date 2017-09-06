<?php
namespace wstmart\mobile\controller;
use wstmart\common\model\GoodsCats;
use wstmart\mobile\model\Goods;
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
 * 门店控制器
 */
class Shops extends Base{
    /**
     * 店铺街
     */
    public function shopStreet(){
    	$gc = new GoodsCats();
    	$goodsCats = $gc->listQuery(0);
    	$this->assign('goodscats',$goodsCats);
    	$this->assign("keyword", input('keyword'));
    	return $this->fetch('shop_street');
    }
    /**
     * 店铺首页
     */
    public function index(){
        $s = model('shops');
        $shopId = (int)input('shopId',1);
        $data = $s->getShopSummary($shopId);
        $this->assign('data',$data);
        // 是否已关注
        $isFavor = model('favorites')->checkFavorite($shopId,1);
        $this->assign('isFavor',$isFavor);
        $this->assign("goodsName", input('goodsName'));
        return $this->fetch('shop_index');
    }
    /**
    * 店铺详情
    */
    public function home(){
        $s = model('shops');
        $shopId = (int)input("param.shopId/d",1);
        $data['shop'] = $s->getShopInfo($shopId);

        $ct1 = input("param.ct1/d",0);
        $ct2 = input("param.ct2/d",0);
        $goodsName = input("param.goodsName");
        if(($data['shop']['shopId']==1 || $shopId==0) && $ct1==0 && !isset($goodsName))
            $this->redirect('mobile/shops/selfShop');

        $gcModel = model('ShopCats');
        $data['shopcats'] = $gcModel->getShopCats($shopId);
        
        $this->assign('shopId',$shopId);//店铺id

        $this->assign('ct1',$ct1);//一级分类
        $this->assign('ct2',$ct2);//二级分类
        
        $this->assign('goodsName',urldecode($goodsName));//搜索
        $this->assign('data',$data);

        // 是否已关注
        $isFavor = model('favorites')->checkFavorite($shopId,1);
        $this->assign('isFavor',$isFavor);
        
        $cart = model('carts')->getCartInfo();
        $this->assign('cart',$cart);
        return $this->fetch('shop_home');
    }
    /**
    * 获取店铺商品
    */
    public function getShopGoods(){
        $shopId = (int)input('shopId',1);
        $g = model('goods');
        $rs = $g->shopGoods($shopId);
        foreach($rs['Rows'] as $k=>$v){
            $rs['Rows'][$k]['goodsImg'] = WSTImg($v['goodsImg'],2);
        }
        return $rs;
    }

    /**
    * 自营店铺
    */
    public function selfShop(){
        $s = model('shops');
        $data['shop'] = $s->getShopInfo(1);
        if(empty($data['shop']))return $this->fetch('error_lost');
        $this->assign('selfShop',1);
        $data['shopcats'] = model('ShopCats')->getShopCats(1);
        $this->assign('goodsName',urldecode(input("param.goodsName")));//搜索
        // 店长推荐
        $data['rec'] = $s->getRecGoods('rec');
        // 热销商品
        $data['hot'] = $s->getRecGoods('hot');
        $this->assign('data',$data);
        // 是否已关注
        $isFavor = model('favorites')->checkFavorite(1,1);
        $this->assign('isFavor',$isFavor);
        $this->assign("keyword", input('keyword'));
        return $this->fetch('self_shop');
    }
    public function getFloorData(){
        $s = model('shops');
        $rs = $s->getFloorData();
        if(isset($rs['goods'])){
            foreach($rs['goods'] as $k=>$v){
                $rs['goods'][$k]['goodsImg'] = WSTImg($v['goodsImg'],2);
            }
        }
        return $rs;
    }

    /**
     * 店铺街列表
     */
    public function pageQuery(){
    	$m = model('shops');
    	$rs = $m->pageQuery(input('pagesize/d'));
    	foreach ($rs['Rows'] as $key =>$v){
    		$rs['Rows'][$key]['shopImg'] = WSTImg($v['shopImg'],3);
    	}
    	return $rs;
    }


	/**
	 * 评论
	 * 
	 * */
	public function comment(){
		return $this->fetch('comment');
	}
}
