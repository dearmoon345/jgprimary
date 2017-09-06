<?php
use think\Db;
use think\Session;
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
 */
/**
 * 查询网站帮助
 * @param $pnum 父级记录数
 * @param $cnum 子记录数
 */
function WSTHelps($pnum = 5,$cnum = 5){
	$cats = Db::table('__ARTICLE_CATS__')->where(['catType'=>1, 'dataFlag'=>1, 'isShow' => 1])
	            ->field("catName,catId")->order('catSort asc')->limit($pnum)->select();
	if(!empty($cats)){
	    foreach($cats as $key =>$v){
	        $cats[$key]['articlecats'] = Db::table('__ARTICLES__')->where(['dataFlag'=>1,'isShow' => 1,'catId'=>$v['catId']])
	            ->field("articleId, catId, articleTitle")->order('createTime asc')->limit($cnum)->select(); 
	    }   
	}
	return $cats;
}

/**
 * 获取前台菜单
 */
function WSTHomeMenus($menuType){
	$m1 = model('home/HomeMenus')->getMenus();	
	$menuId1 = (int)input("get.homeMenuId");

	$menus = [];
	$menus['menus'] = $m1[$menuType];
	//判断menuId1是否有效
	if($menuId1==0){
		$menuId1 = (int)session('WST_MENID'.$menuType);
	}else{
		session('WST_MENID'.$menuType,$menuId1);
	}
	//检测第一级菜单是否有效
	$tmpMenuId1 = 0;
	$isFind = false;
	foreach ($menus['menus'] as $key => $v){
		if($tmpMenuId1==0)$tmpMenuId1 = $key;
		if($key==$menuId1)$isFind = true;
	}
	if($isFind){
		$menus['menuId1'] = $menuId1;

	}else{
		$menus['menuId1'] = $tmpMenuId1;
	}

    var_dump(session('WST_MENUID3'.$menuType));
	$menus['menuId3'] = session('WST_MENUID3'.$menuType);

	return $menus;
}
/**
 * 获取指定父级的商家店铺分类
 */
function WSTShopCats($parentId){
	$shopId = (int)session('WST_USER.shopId');
	$dbo = Db::table('__SHOP_CATS__')->where(['dataFlag'=>1, 'isShow' => 1,'parentId'=>$parentId,'shopId'=>$shopId]);
	return $dbo->field("catName,catId")->order('catSort asc')->select();
}
/**
 * 获取商城搜索关键字
 */
function WSTSearchKeys(){
	$keys = WSTConf("CONF.hotWordsSearch");
	if($keys!='')$keys = explode(',',$keys);
	return $keys;
}
/**
 * 获取首页左侧分类、推荐品牌和广告
 */
function WSTSideCategorys(){
	$data = cache('WST_SIDE_CATS');
	if(!$data){
		$cats1 = Db::table('__GOODS_CATS__')->where(['dataFlag'=>1, 'isShow' => 1,'parentId'=>0])->field("catName,catId")->order('catSort asc')->select();
		if(count($cats1)>0){
			$ids1 = [];$ids2 = [];$cats2 = [];$cats3 = [];$mcats3 = [];$mcats2 = [];
			foreach ($cats1 as $key =>$v){
				$ids1[] = $v['catId'];
			}
			$tmp2 = Db::table('__GOODS_CATS__')->where(['dataFlag'=>1, 'isShow' => 1,'parentId'=>['in',$ids1]])->field("catName,catId,parentId")->order('catSort asc')->select();
			if(count($tmp2)>0){
				foreach ($tmp2 as $key =>$v){
				    $ids2[] = $v['catId'];
			    }
			    $tmp3 = Db::table('__GOODS_CATS__')->where(['dataFlag'=>1, 'isShow' => 1,'parentId'=>['in',$ids2]])->field("catName,catId,parentId")->order('catSort asc')->select();
			    if(count($tmp3)>0){
			    	//组装第三级
				    foreach ($tmp3 as $key =>$v){
					    $mcats3[$v['parentId']][] = $v;
				    }
			    }
			    //组装第二级
			    foreach ($tmp2 as $key =>$v){
			    	if(isset($mcats3[$v['catId']]))$v['list'] = $mcats3[$v['catId']];
					$mcats2[$v['parentId']][] = $v;
				}
				//组装第一级
		        foreach ($cats1 as $key =>$v){
		        	if(isset($mcats2[$v['catId']]))$cats1[$key]['list'] = $mcats2[$v['catId']];
		        }
			}	
			unset($ids1,$ids2,$cats2,$cats3,$mcats3,$mcats2);
		}
		cache('WST_SIDE_CATS',$cats1);
		return $cats1;
	}
	return $data;
}

/**
 * 获取导航菜单
 */
function WSTNavigations($navType){
	$data = cache('WST_NAVS');
	if(!$data){
		$rs = Db::table('__NAVS__')->where(['isShow'=>1,'navType'=>$navType])->order('navSort asc')->select();
		foreach ($rs as $key => $v){
			if(stripos($v['navUrl'],'https://')===false &&  stripos($v['navUrl'],'http://')===false){
				$rs[$key]['navUrl'] = str_replace('/index.php','',\think\Request::instance()->root())."/".$v['navUrl'];
			}
		}
		cache('WST_NAVS',$data);
		return $rs;
	}
	return $data;
}
/**
 * 根据指定的商品分类获取其路径
 */
function WSTPathGoodsCat($catId,$data = array()){
	$rs = Db::table('__GOODS_CATS__')->where(['isShow'=>1,'dataFlag'=>1,'catId'=>$catId])->field("parentId,catName,catId")->find();
	$data[] = $rs;
	if($rs['parentId']==0){;
		krsort($data);
		return $data;
	}else{
		return WSTPathGoodsCat($rs['parentId'],$data);
	}
}
/**
 * 处理商家结算信息提示
 */
function WSTShopMessageBox(){
	$USER = session('WST_USER');
	$msg = [];
	if($USER['shopMoney']<0){
		$msg[] = '您的账户欠费¥'.$USER['shopMoney'].'，请及时充值。';
	}
	if(($USER['noSettledOrderFee']+$USER['paymentMoney'])<0 && (($USER['shopMoney']+$USER['noSettledOrderFee']+$USER['paymentMoney'])<0)){
		$msg[] = '您的账户余额【¥'.$USER['shopMoney'].'】不足以缴纳订单佣金【¥'.(-1*($USER['noSettledOrderFee']+$USER['paymentMoney'])).'】，请及时充值。';
	}
	return implode('||',$msg);
}

//转换函数(用于将淘宝csv文件中的图片代码转换成相应图片路径)
function convert($str) {
    $str = str_replace('"', '', $str);
    $arr = explode(';', trim($str));
    array_splice($arr, count($arr) - 1);
    $convertArr = array();
    $date = date('Y-m', time());
    for ($i = 0; $i < count($arr); $i++) {
        $arr_i = explode(':', $arr[$i]);
        $convertArr[$i] = 'upload/goods/'.$date.'/'.$arr_i[0].'.jpg';
    }
    $convertStr = implode(',', $convertArr);
    return $convertStr;
}

//将unicode编码转换为utf8编码(主要用于淘宝csv文件)
function unicodeToUtf8($str, $order = "little")
{
    $utf8string = "";
    $n = strlen($str);
    for ($i = 0; $i < $n; $i++) {
        if ($order == "little") {
            $val = str_pad(dechex(ord($str[$i + 1])), 2, 0, 0) .
                str_pad(dechex(ord($str[$i])), 2, 0, 0);
        } else {
            $val = str_pad(dechex(ord($str[$i])), 2, 0, 0) .
                str_pad(dechex(ord($str[$i + 1])), 2, 0, 0);
        }
        $val = intval($val, 16); // 由于上次的.连接，导致$val变为字符串，这里得转回来。
        $i++; // 两个字节表示一个unicode字符。
        $c = "";
        if ($val < 0x7F) { // 0000-007F
            $c .= chr($val);
        } elseif ($val < 0x800) { // 0080-07F0
            $c .= chr(0xC0 | ($val / 64));
            $c .= chr(0x80 | ($val % 64));
        } else { // 0800-FFFF
            $c .= chr(0xE0 | (($val / 64) / 64));
            $c .= chr(0x80 | (($val / 64) % 64));
            $c .= chr(0x80 | ($val % 64));
        }
        $utf8string .= $c;
    }
    /* 去除bom标记 才能使内置的iconv函数正确转换 */
    if (ord(substr($utf8string, 0, 1)) == 0xEF && ord(substr($utf8string, 1, 2)) == 0xBB && ord(substr($utf8string, 2, 1)) == 0xBF) {
        $utf8string = substr($utf8string, 3);
    }
    return $utf8string;
}

//组合销售属性的函数(用于识别csv文件中的销售属性组合,并转换成可识别的代码)
function combineSaleAttr($str) {
    $skuPropsArr = array(1627207, 20509, 20549, 20518, 122216343);
    $arr = preg_split('/[\:]|[\;]/', $str);
    $attr = array();
    for ($i=0; $i < count($arr); $i++) {
        if (in_array($arr[$i], $skuPropsArr)) {
            $attr[] = $arr[$i].':'.$arr[$i+1];
        }
    }
    $saleAttr = array();
    for ($i=0; $i < count($attr); $i++) {
        if (($i+1) < count($attr) && (substr($attr[$i], 0, 5) != substr($attr[$i+1], 0, 5))) {     //判断是否为双重销售属性
            $saleAttr[] = $attr[$i].';'.$attr[$i+1];
            $i++;
        }else {         //如果是单重属性
            $saleAttr[] = $attr[$i];
        }
    }
    return $saleAttr;
    /*if (strstr($str, '::')) {
        $arr = preg_split('/[\:][\:]/', $str);
        array_shift($arr);
        $saleAttr = array();
        foreach ($arr as $v) {
            $index = strrpos($v, ';');
            $saleAttr[] = substr($v, 0, $index);
        }
        return $saleAttr;
    }elseif (strstr($str, '#')) {
        $arr = preg_split('/\#/', $str);
        array_shift($arr);
        $saleAttr = array();
        foreach ($arr as $v) {
            $index = strrpos($v, ';');
            $saleAttr[] = substr($v, 0, $index);
        }
        return $saleAttr;
    }*/
}

//获取token函数
function getToken($member_id, $member_name, $member_avatar) {
    srand((double)microtime()*1000000);

    $appKey = 'p5tvi9dsp4v14';
    $appSecret = 'ySJHJumM2i'; // 开发者平台分配的 App Secret。

    $nonce = rand(); // 获取随机数。
    $timestamp = time(); // 获取时间戳。

    $signature = sha1($appSecret.$nonce.$timestamp);

    $url = 'http://api.cn.ronghub.com/user/getToken.json';

    $postData = 'userId=' . $member_id . '&name=' . $member_name . '&portraitUri=' . $member_avatar;

    $httpHeader = array(

        'App-Key:' . $appKey,   //平台分配

        'Nonce:' . $nonce,        //随机数

        'Timestamp:' . $timestamp,    //时间戳

        'Signature:' . $signature,         //签名

        'Content-Type: application/x-www-form-urlencoded',

    );

//创建http header

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);

    curl_setopt($ch, CURLOPT_POST, 1);

    if ($postData != '') {

        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

    } else {

        showMsg(0, '缺少相应参数');

    }

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($ch, CURLOPT_HEADER, false);

    curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeader);

    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);


    $result = curl_exec($ch);
    $token = json_decode($result) -> token;
    curl_close($ch);
    return $token;
}

