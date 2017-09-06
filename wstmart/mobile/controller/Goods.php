<?php
namespace wstmart\mobile\controller;
use wstmart\common\model\GoodsCats;
use wstmart\home\model\GoodsAttr;//添加代码
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
 * 商品控制器
 */
class Goods extends Base{
    protected $beforeActionList = [
          'checkAuth' => ['only'=>'history']
    ];

    function getCombinationToString($arr, $len){
        if ($len == 1) {
            return $arr[0];
        }
        $tempArr = $arr;
        unset($tempArr[0]);
        $returnarr = [];
        $len2 = count($arr);
        $ret = $this->getCombinationToString(array_values($tempArr), ($len - 1));
        foreach ($arr[$len2 - $len] as $alv) {
            foreach ($ret as $rv) {
                if (is_array($rv)) {
                    array_unshift($rv, $alv);
                    $returnarr[] = array_values($rv);
                } else {
                    $returnarr[] = [$alv,$rv];
                }
            }
        }
        return $returnarr;
    }

	/**
	 * 商品主页
	 */
	public function detail(){

        //添加代码start  修改错误的数据
        $where['goodsId'] = input('goodsId/d');
        $where['skuProps'] = '';
        $where['isSpec'] = 2;
        $info = Db::name('goods')->where($where)->update(['isSpec' => 0]);// 把没有规格的数据改成 0
        //添加代码end
        
		$m = model('goods');
        $goods = $m->getBySale(input('goodsId/d')); 

        if(!empty($goods)){

            //添加代码start
            if ($goods['isSpec'] == 2  && $goods['skuProps'] != '') {
                //组合淘宝导入csv销售属性
                $saleAttr = combineSaleAttr($goods['skuProps']);
                if (!empty($saleAttr)) {
                    $saleAttrArr = array();     //属性组合数组
                    $model = new GoodsAttr();
                    foreach ($saleAttr as $item) {
                        $valueArr = explode(';', $item);
                        if (!!strstr($valueArr[0], '1627207')) {
                            for ($i=0; $i<count($valueArr); $i++) {
                                $result = $model -> where('code', $valueArr[$i]) -> find();
                                if ($result) {
                                    $valueArr[$i] = $result['value'];
                                }else {
                                    $valueArr[$i] = urlencode('暂无');
                                }
                            }
                        }else {
                            $j = 0;
                            for ($i=count($valueArr); $i>0; $i--) {
                                $result = $model -> where('code', $valueArr[($i - 1)]) -> find();
                                if ($result) {
                                    $valueArr[$j] = $result['value'];
                                }else {
                                    $valueArr[$j] = urlencode('暂无');
                                }
                                $j++;
                            }
                        }
                        $saleAttrArr[] = $valueArr;
                    }

                    //添加代码start
                    //根据商品规格生成  当isSpec = 2  时spec_items 和 goods_spec  表的基本规格数据
                    if($goods['isSpec'] == 2){

                        $a = explode('_',$goods['goodsCatIdPath']);

                        $spec_cats = Db::name('spec_cats')->field('catId')
                                                   ->where(['goodsCatPath'=>$goods['goodsCatIdPath'],'isShow'=>1,'dataFlag'=>1])->select();
                        if (!$spec_cats) {
                            $spec_cats = Db::name('spec_cats')->field('catId')
                                                   ->where(['goodsCatId'=>$a[2],'isShow'=>1,'dataFlag'=>1])->select();
                        }
                        if (!$spec_cats) {
                            $spec_cats = Db::name('spec_cats')->field('catId')
                                                   ->where(['goodsCatPath'=>$a[0].'_'.$a[1].'_','isShow'=>1,'dataFlag'=>1])->select();
                        }
                        if (!$spec_cats) {
                            $spec_cats = Db::name('spec_cats')->field('catId')
                                                   ->where(['goodsCatPath'=>$a[0].'_','isShow'=>1,'dataFlag'=>1])->select();
                        }
                        if (!$spec_cats) {
                           die('商品规格有误，请联系管理员添加商品属性');
                        }

                        $isSpec3 = Db::name('goods')->where(['goodsId' =>$goods['goodsId']])->update(['isSpec' =>1]);//isSpec=2 -> isSpec=1
                        //把原先 isSpec == 2 的基本规格数据删除
                        $det = Db::name('spec_items')->where(['goodsId' =>$goods['goodsId']])->delete();
                        $det = Db::name('goods_specs')->where(['goodsId' =>$goods['goodsId']])->delete();
                        // 生成catId
                        // 生成 itemName    

                        foreach ($spec_cats as $key => $value) {
                            $arr[$key] = [];
                        }                                            

                        foreach ($saleAttrArr as $key1 => $value1) {
                            foreach ($value1 as $key2 => $value2) {
                                if(isset($value1[$key2]) && !in_array($value1[$key2],$arr[$key2])){
                                    $arr[$key2][] = $value1[$key2];
                                }
                            }
                        }
                        // echo "<pre>";
                        // print_r($saleAttrArr);                  
                        // echo "<pre>";
                        // print_r($arr);

                        //生成spec_items数据
                        foreach ($arr as $key1 => $value1) {
                            foreach ($value1 as $key2 => $itemName) {

                                if($itemName == urlencode('暂无')){
                                    $itemName = urldecode($itemName);
                                }                                                           
                                // echo "<pre>";
                                // print_r($spec_cats);
                                foreach ($spec_cats as $k=> $v) {
                                    if ($k == $key1) {
                                         $arr2[$v['catId']][] = $itemName;
                                    }
                                }
                                // echo "<pre>";
                                // print_r($arr2);die;                              
                            }
                        }
                        // header("content-type:text/html; charset=utf-8");
                        // echo "<pre>";
                        // print_r($arr2); 

                        foreach ($arr2 as $k => $v) {
                            foreach ($v as $key => $value) {
                                $data = [
                                    'shopId' =>$goods['shopId'],
                                    'catId' =>$k,
                                    'goodsId' =>$goods['goodsId'],
                                    // 'itemImg' =>$goods['goodsImg'],
                                    'createTime' =>date('Y-m-d H:i:s',time()),
                                    'itemName' =>$value
                                ];
                                $spec_items = Db::name('spec_items')->insert($data);
                            }
                        }

                        foreach ($arr2 as $key1 => $value1) {
                            foreach ($value1 as $k => $v) {
                                $sizeID = Db::query("SELECT * FROM   `jingo_spec_items` where goodsId=? and itemName=? limit 1",[$goods['goodsId'],$v]);
                                foreach ($sizeID as $key2 => $value2) {
                                    $arr3[$key1][$k] = $value2['itemId'];

                                }
                            }
                        }

                        if(count($arr3) > 1){
                            // echo "<pre>";
                            // print_r($arr3);
                            foreach ($arr3 as  $value) {
                                $arr4[] = $value;
                            }
                            // echo "<pre>";
                            // print_r($arr4);
                            $len = count($arr4);
                            $specIds = $this->getCombinationToString($arr4, $len);
                            foreach ($specIds as &$av) {
                                    $av = implode(':', $av);
                            } 
                        }else{
                            foreach ($arr3 as  $value) {
                                foreach ($value as  $v) {
                                    $specIds[] = $v;
                                }
                            }
                        }                                                 
                        
                        // var_dump($data);
                      
                       
                        
                        // header("content-type:text/html; charset=utf-8");
                        // echo "<pre>";
                        // print_r($specIds);     

                        //生成goods_specs表数据
                        $data = [
                            'shopId' =>$goods['shopId'],
                            'goodsId' =>$goods['goodsId'],
                            'productNo' =>$goods['productNo'],
                            'specIds' =>$value,
                            'isDefault' =>1,
                            'marketPrice' =>$goods['marketPrice'],
                            'specPrice' =>$goods['shopPrice'],
                            'specStock' =>$goods['goodsStock'],
                            'warnStock' =>$goods['warnStock']
                        ];
                        foreach ($specIds as $key => $value) {
                            header("content-type:text/html; charset=utf-8"); 
                            if($key ==0){
                                 $data['specIds'] = $value;
                                 $data['isDefault'] = 1;
                            
                            }else{
                                $data['specIds'] = $value;
                                $data['isDefault'] = 0;
                            }
                            $goods_spec = Db::name('goods_specs')->insert($data);
                        }
                    }
                }
            }
            //添加代码end

            $history = cookie("wx_history_goods");
            $history = is_array($history)?$history:[];
            array_unshift($history, (string)$goods['goodsId']);
            $history = array_values(array_unique($history));
            if(!empty($history)){
                cookie("wx_history_goods",$history,25920000);
            }
        }
        $goods = $m->getBySale(input('goodsId/d'));//添加代码

        hook('mobileControllerGoodsIndex',['getParams'=>input()]);
        // 找不到商品记录
        if(empty($goods))return $this->fetch('error_lost');
        $goods['goodsDesc']=htmlspecialchars_decode($goods['goodsDesc']);
        $rule = '/<img src="\/(upload.*?)"/';
        preg_match_all($rule, $goods['goodsDesc'], $images);

        foreach($images[0] as $k=>$v){
            $goods['goodsDesc'] = str_replace('/'.$images[1][$k], request()->root().'/'.WSTConf("CONF.goodsLogo") . "\"  data-echo=\"".request()->root()."/".WSTImg($images[1][$k],3), $goods['goodsDesc']);
        }

		$this->assign("info", $goods);
        // header("content-type:text/html; charset=utf-8");
        //     echo "<pre>";
        //     print_r($goods);die;
		return $this->fetch('goods_detail');
	}
	/**
     * 商品列表
     */
    public function lists(){
    	$this->assign("keyword", input('keyword'));
    	$this->assign("catId", input('catId/d'));
    	$this->assign("brandId", input('brandId/d'));
    	return $this->fetch('goods_list');
    }
    /**
     * 获取列表
     */
    public function pageQuery(){
        $m = model('goods');
        $gc = new GoodsCats();
        $catId = (int)input('catId');
        $isSearch = 0;
        if($catId>0){
            $goodsCatIds[] = $gc->getParentIs($catId);
        }else{
             $isSearch = 1;
            $data['keyword'] = input('keyword');
            $model = model('GoodsCats')->where('catName',$data['keyword'])->field('catId')->select(); 
            if($model){
                foreach ($model as $key => $value) {
                    $goodsCatIds[] = $gc->getParentIs($value['catId']);
                }
            }else{
                $goodsCatIds = [];
            } 
        }
        
        $rs = $m->pageQuery($goodsCatIds,$isSearch);
        foreach ($rs['Rows'] as $key =>$v){
            $rs['Rows'][$key]['goodsImg'] = WSTImg($v['goodsImg'],2);
        }
        return $rs;
    }

    /**
    * 浏览历史页面
    */
    public function history(){
        return $this->fetch('users/history/list');
    }
    /**
    * 获取浏览历史
    */
    public function historyQuery(){
        $rs = model('goods')->historyQuery();
        if(!empty($rs)){
	        foreach($rs['Rows'] as $k=>$v){
	            $rs['Rows'][$k]['goodsImg'] = WSTImg($v['goodsImg'],3);
	        }
        }
        return $rs;
    }

    /**
    *   获取 新品、热销、精品 商品列表
    */
    public function list_goods(){
        // ['goodsId' => ['>' , 6]]
        $field = input('get.field');
        $field = 'isNew';
        $rs = Db::name('goods')->where($field,1)->limit(3)->select();
        // $rs = Db::name('users')->where('userStatus',1)->select();
        // header("content-type:text/html; charset=utf-8");
        // echo "<pre>";
        // print_r($rs);die;
        // var_dump($rs);die;
    }

    /**
    *   获取 搜索分类
    */
    public function getCats(){
        $keyword ="'".'%'.input('post.keyword').'%'."'";

        // echo  $keyword;die;
        $rs = Db::name('goods_cats')->where("catName like $keyword")->field('catName')->select();
        if ($rs) {
            return WSTReturn("", 1,$rs);
        }
      
    }

}
