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
 * 商品类
 */
class Goods extends Base{
     /**
      *  上架商品列表
      */
	public function saleByPage(){
		$where = [];
		$where['g.goodsStatus'] = 1;
		$where['g.dataFlag'] = 1;
		$where['g.isSale'] = 1;
		$areaIdPath = input('areaIdPath');
		$goodsCatIdPath = input('goodsCatIdPath');
		$goodsName = input('goodsName');
		$shopName = input('shopName');
		if($areaIdPath !='')$where['areaIdPath'] = ['like',$areaIdPath."%"];
		if($goodsCatIdPath !='')$where['goodsCatIdPath'] = ['like',$goodsCatIdPath."%"];
		if($goodsName != '')$where['goodsName|goodsSn'] = ['like',"%$goodsName%"];
		if($shopName != '')$where['shopName|shopSn'] = ['like',"%$shopName%"];
		$keyCats = model('GoodsCats')->listKeyAll();
		$rs = $this->alias('g')->join('__SHOPS__ s','g.shopId=s.shopId','left')
		    ->where($where)
			->field('goodsId,goodsName,goodsSn,saleNum,shopPrice,g.shopId,goodsImg,s.shopName,goodsCatIdPath')
			->order('saleTime', 'desc')
			->paginate(input('pagesize/d'))->toArray();
		foreach ($rs['Rows'] as $key => $v){
			$rs['Rows'][$key]['verfiycode'] = WSTShopEncrypt($v['shopId']);
			$rs['Rows'][$key]['goodsCatName'] = self::getGoodsCatNames($v['goodsCatIdPath'],$keyCats);
		}
		return $rs;
	}
    public function getGoodsCatNames($goodsCatPath, $keyCats){
		$catIds = explode("_",$goodsCatPath);
		$catNames = array();
		for($i=0,$k=count($catIds);$i<$k;$i++){
			if($catIds[$i]=='')continue;
			if(isset($keyCats[$catIds[$i]]))$catNames[] = $keyCats[$catIds[$i]];
		}
		return implode("→",$catNames);
	}
	/**
	 * 审核中的商品
	 */
public function auditByPage(){
    	$where['goodsStatus'] = 0;
		$where['g.dataFlag'] = 1;
		$where['isSale'] = 1;
		$areaIdPath = input('areaIdPath');
		$goodsCatIdPath = input('goodsCatIdPath');
		$goodsName = input('goodsName');
		$shopName = input('shopName');
		if($areaIdPath !='')$where['areaIdPath'] = ['like',$areaIdPath."%"];
		// if($goodsCatIdPath !='')$where['goodsCatIdPath'] = ['like',$goodsCatIdPath."%"];
		if($goodsName != '')$where['goodsName|goodsSn'] = ['like',"%$goodsName%"];
		if($shopName != '')$where['shopName|shopSn'] = ['like',"%$shopName%"];
		$keyCats = model('GoodsCats')->listKeyAll();
		
		//添加代码start
		if(!strpos($goodsCatIdPath,"_") && $goodsCatIdPath){
			$goodsCatIdPath1 ="'".$goodsCatIdPath."|_%" ."'". "escape '|' ";
			$rs = $this->alias('g')->join('__SHOPS__ s','g.shopId=s.shopId','left')
			->where("goodsCatIdPath like ".$goodsCatIdPath1)
		    ->where($where)
			->field('goodsId,goodsName,goodsSn,saleNum,shopPrice,goodsImg,s.shopName,s.shopId,goodsCatIdPath')
			->order('saleTime', 'desc')
			->paginate(input('pagesize/d'))->toArray();
		}else{
			if($goodsCatIdPath !='')$where['goodsCatIdPath'] = ['like',$goodsCatIdPath."%"];
			$rs = $this->alias('g')->join('__SHOPS__ s','g.shopId=s.shopId','left')
		    ->where($where)
			->field('goodsId,goodsName,goodsSn,saleNum,shopPrice,goodsImg,s.shopName,s.shopId,goodsCatIdPath')
			->order('saleTime', 'desc')
			->paginate(input('pagesize/d'))->toArray();
		}
        foreach ($rs['Rows'] as $key => $v){
			$rs['Rows'][$key]['verfiycode'] =  WSTShopEncrypt($v['shopId']);
			$rs['Rows'][$key]['goodsCatName'] = self::getGoodsCatNames($v['goodsCatIdPath'],$keyCats);
		}
		return $rs;
	}
	/**
	 * 违规的商品 
	 */
	public function illegalByPage(){
		$where['goodsStatus'] = -1;
		$where['g.dataFlag'] = 1;
		$where['isSale'] = 1;
		$areaIdPath = input('areaIdPath');
		$goodsCatIdPath = input('goodsCatIdPath');
		$goodsName = input('goodsName');
		$shopName = input('shopName');
		if($areaIdPath !='')$where['areaIdPath'] = ['like',$areaIdPath."%"];
		if($goodsCatIdPath !='')$where['goodsCatIdPath'] = ['like',$goodsCatIdPath."%"];
		if($goodsName != '')$where['goodsName|goodsSn'] = ['like',"%$goodsName%"];
		if($shopName != '')$where['shopName|shopSn'] = ['like',"%$shopName%"];
		$keyCats = model('GoodsCats')->listKeyAll();
		$rs = $this->alias('g')->join('__SHOPS__ s','g.shopId=s.shopId','left')
		    ->where($where)
			->field('goodsId,goodsName,goodsSn,goodsImg,s.shopName,s.shopId,illegalRemarks,goodsCatIdPath')
			->order('saleTime', 'desc')
			->paginate(input('pagesize/d'))->toArray();
		foreach ($rs['Rows'] as $key => $v){
			$rs['Rows'][$key]['verfiycode'] = WSTShopEncrypt($v['shopId']);
			$rs['Rows'][$key]['goodsCatName'] = self::getGoodsCatNames($v['goodsCatIdPath'],$keyCats);
		}
		return $rs;
	}
	
	/**
	 * 删除商品
	 */
	public function del(){
	    $id = input('post.id/d');
		$data = [];
		$data['dataFlag'] = -1;
		$data['isSale'] = 0;
		Db::startTrans();
		try{
		    $result = $this->update($data,['goodsId'=>$id]);
	        if(false !== $result){
	        	Db::name('carts')->where('goodsId',$id)->delete();
	        	WSTUnuseImage('goods','goodsImg',$id);
		        WSTUnuseImage('goods','gallery',$id);
		        Db::commit();
		        //标记删除购物车
	        	return WSTReturn("删除成功", 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
        }
        return WSTReturn('删除失败',-1);
	}
	/**
	  * 批量删除商品
	  */
	 public function batchDel(){
	 	$shopId = (int)session('WST_USER.shopId');
	   	$ids = input('post.ids/a');
	   	Db::startTrans();
		try{
		   	$rs = $this->where(['goodsId'=>['in',$ids],
		   						'shopId'=>$shopId])->setField(['dataFlag'=>-1,'isSale'=>0]);
			if(false !== $rs){
				Db::name('carts')->where(['goodsId'=>['in',$ids]])->delete();
				//标记删除购物车
			    foreach ($ids as $v){
					WSTUnuseImage('goods','goodsImg',(int)$v);
			        WSTUnuseImage('goods','gallery',(int)$v);
				}
				Db::commit();
	        	return WSTReturn("删除成功", 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
        }
        return WSTReturn('删除失败',-1);
	 }

	/**
	* 设置商品违规状态
	*/
	public function illegal(){
		$illegalRemarks = input('post.illegalRemarks');		
		$id = (int)input('post.id');
		if($illegalRemarks=='')return WSTReturn("请输入违规原因");
		//判断商品状态
		$rs = $this->alias('g')->join('__SHOPS__ s','g.shopId=s.shopId','left')->where('goodsId',$id)
		           ->field('s.userId,g.goodsName,g.goodsSn,g.goodsStatus,g.goodsId')->find();
		if((int)$rs['goodsId']==0)return WSTReturn("无效的商品");
		if((int)$rs['goodsStatus']!=1)return WSTReturn("操作失败，商品状态已发生改变，请刷新后再尝试");
		Db::startTrans();
		try{
			$res = $this->where('goodsId',$id)->setField(['goodsStatus'=>-1,'illegalRemarks'=>$illegalRemarks]);
			if($res!==false){
				Db::name('carts')->where(['goodsId'=>$id])->delete();
				//发送一条商家信息
				$tpl = WSTMsgTemplates('GOODS_REJECT');
		        if($tpl['tplContent']!=''){
		            $find = ['${GOODS}','${GOODS_SN}','${TIME}','${REASON}'];
		            $replace = [$rs['goodsName'],$rs['goodsSn'],date('Y-m-d H:i:s'),$illegalRemarks];
		            WSTSendMsg($rs['userId'],str_replace($find,$replace,$tpl['tplContent']),['from'=>2,'dataId'=>$id]);
		        } 
		        if((int)WSTConf('CONF.wxenabled')==1){
					$params = [];
					$params['GOODS'] = $rs['goodsName'];
					$params['GOODS_SN'] = $rs['goodsSn'];
					$params['TIME'] = date('Y-m-d H:i:s'); 
					$params['REASON'] = $illegalRemarks;          
					WSTWxMessage(['CODE'=>'WX_GOODS_REJECT','userId'=>$rs['userId'],'params'=>$params]);
				}
				Db::commit();
				return WSTReturn('操作成功',1);
			}
		}catch (\Exception $e) {
            Db::rollback();
        }
        return WSTReturn('操作失败',-1);
	}
   /**
	* 通过商品审核
	*/
	public function allow(){	
		$id = (int)input('post.id');
		//判断商品状态
		$rs = $this->alias('g')->join('__SHOPS__ s','g.shopId=s.shopId','left')->where('goodsId',$id)
		           ->field('s.userId,g.goodsName,g.goodsSn,g.goodsStatus,g.goodsId')->find();
		if((int)$rs['goodsId']==0)return WSTReturn("无效的商品");
		if((int)$rs['goodsStatus']==1)return WSTReturn("操作失败，商品状态已发生改变，请刷新后再尝试");
		Db::startTrans();
		try{
			$res = $this->setField(['goodsId'=>$id,'goodsStatus'=>1]);
			if($res!==false){
				//发送一条商家信息
				$tpl = WSTMsgTemplates('GOODS_ALLOW');
		        if($tpl['tplContent']!=''){
		            $find = ['${GOODS}','${GOODS_SN}','${TIME}'];
		            $replace = [$rs['goodsName'],$rs['goodsSn'],date('Y-m-d H:i:s')];
		            WSTSendMsg($rs['userId'],str_replace($find,$replace,$tpl['tplContent']),['from'=>2,'dataId'=>$id]);
		        } 
		        if((int)WSTConf('CONF.wxenabled')==1){
					$params = [];
					$params['GOODS'] = $rs['goodsName'];
					$params['GOODS_SN'] = $rs['goodsSn'];
					$params['TIME'] = date('Y-m-d H:i:s');           
					WSTWxMessage(['CODE'=>'WX_GOODS_ALLOW','userId'=>$rs['userId'],'params'=>$params]);
				}
				Db::commit();
				return WSTReturn('操作成功',1);
			}
		}catch (\Exception $e) {
            Db::rollback();
        }
        return WSTReturn('操作失败',-1);
	}

	/**
	 * 查询商品
	 */
	public function searchQuery(){
		$goodsCatatId = (int)input('post.goodsCatId');
		if($goodsCatatId<=0)return [];
		$goodsCatIds = WSTGoodsCatPath($goodsCatatId);
		$key = input('post.key');
		$where = [];
		$where['g.dataFlag'] = 1;
		$where['g.isSale'] = 1;
		$where['g.goodsStatus'] = 1;
		$where['goodsCatIdPath'] = ['like',implode('_',$goodsCatIds).'_%'];
		if($key!='')$where['goodsName|shopName'] = ['like','%'.$key.'%'];
		return $this->alias('g')->join('__SHOPS__ s','g.shopId=s.shopId','inner')
		     ->where($where)->field('g.goodsName,s.shopName,g.goodsId')->limit(50)->select();
	}

	/**
	 * 根据下架指定店铺下的所有商品
	 */
	public function unsaleByshopId($shopId){
        //下架商品
		$data = [];
		$data['isSale'] = 0;
		$goodsIds = [];
		$goods = $this->where(['shopId'=>$shopId,'isSale'=>1])->field('goodsId')->select();
		if(!empty($goods)){
			foreach ($goods as $key => $v) {
				$goodsIds[] = $v['goodsId'];
			}
		}
		$result = $this->where(['shopId'=>$shopId])->update($data);
		if(false !== $result){
		    //删除推荐商品
		    if(count($goodsIds)>0)Db::name('recommends')->where(['dataSrc'=>0,'dataId'=>['in',$goodsIds]])->delete();
		    Db::commit();
			return WSTReturn('操作成功',1);
		}
        return WSTReturn('删除失败',-1);
    }
}
