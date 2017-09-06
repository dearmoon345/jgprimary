<?php
namespace wstmart\admin\controller;
use wstmart\purse\controller\Commision;
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
 * 经销商控制器
 */
class Dealer extends Base{
   /**
	* 获取经销商列表
	*/
	public function index(){
		return $this->fetch('edit');
	}
    /**
    * 获取经销商列表
    */
    public function indexList(){
        //获取经销商信息
        $users = Db::name('users');
        $salesendID = $users->where('salesendID','>',0)->field('salesendID')->select();
        foreach ($salesendID as $value) {
            $salesendIDs[] = $value['salesendID'];
        }
        $salesendIDs = array_unique($salesendIDs); //数组去重
        $info = $users->where('userId','in',$salesendIDs)->where('isProvince',0)->select();

        //获取佣金
        $salesend = Db::name('salesend');
        $start = strtotime(date('Y').'-'.date('m').'-01');//该月的月初时间戳
        $end = strtotime(date('Y').'-'.(date('m')+1).'-1')-86400;//该月的月末时间戳

        $Commision = new Commision();
        foreach ($info as $key => &$value) {
            //获取 经销商 所属省份
            if ($value['userPhone'] != '') {
                $_GET['p'] = $value['userPhone'];
                $ProvinceJson = $Commision->phone();
                $Province = json_decode($ProvinceJson)->province;
            }else{
                $Province = '暂无';
            }
            $value['Province'] = $Province;
            //获取佣金
            $a = $value['userId'];
            $sql = "SELECT sum(CommissionFee) FROM `jingo_salesend` where salesendID = $a and UNIX_TIMESTAMP(`createTime`) > $start and UNIX_TIMESTAMP(`createTime`) < $end ";
            $date = Db::query($sql);
            if (empty($date[0]['sum(CommissionFee)'])) {
                $value['CommissionFee'] = 0;
            }else{
                $value['CommissionFee'] = round($date[0]['sum(CommissionFee)']);
            }
            $list2['Rows'][] = $value; 
        }
        // header("content-type:text/html; charset=utf-8");
        //     echo "<pre>";
        //     print_r($list2);die;
        return $list2;
    }  
    /**
    * 获取经销商列表 详情
    */
    public function indexDetail(){
        $salesendID = input('get.salesendID');
        //获取佣金
        $salesend = Db::name('salesend');
        $start = strtotime(date('Y').'-'.date('m').'-01');//该月的月初时间戳
        $end = strtotime(date('Y').'-'.(date('m')+1).'-1')-86400;//该月的月末时间戳
        $sql = "SELECT salesendID,orderId,CommissionFee FROM `jingo_salesend` where salesendID = $salesendID and UNIX_TIMESTAMP(`createTime`) > $start and UNIX_TIMESTAMP(`createTime`) < $end ";
            $date = Db::query($sql);
        if (empty($date)) {
            return [];
        }
       // header("content-type:text/html; charset=utf-8");
       //      echo "<pre>";
       //      print_r($date);die;
        $order = Db::name('orders');
        foreach ($date as $key => &$value) {
            $res = $order->where('orderId',$value['orderId'])->find();
            $value['orderNo'] = $res['orderNo']; 
            $value['closeTime'] = $res['closeTime']; 
            $value['commisionTime'] = $res['commisionTime'];  
            $value['payTime'] = $res['payTime']; //实际支付时间
            $list2['Rows'][] = $value; 
            //        header("content-type:text/html; charset=utf-8");
            // echo "<pre>";
            // print_r($date);die;
        }
        return $list2;

    }
    /**
    * 获取省经销商列表
    */
    public function Province(){
        return $this->fetch('Province');
    }
    /**
    * 获取省经销商列表
    */
    public function ProvinceList(){
        //获取经销商信息
        $users = Db::name('users');
        $salesendID = $users->where('salesendID','>',0)->field('salesendID')->select();
        foreach ($salesendID as $value) {
            $salesendIDs[] = $value['salesendID'];
        }
        $salesendIDs = array_unique($salesendIDs); //数组去重
        $info = $users->where('userId','in',$salesendIDs)->where('isProvince',1)->select();

        //获取佣金
        $salesend = Db::name('salesend');
        $start = strtotime(date('Y').'-'.date('m').'-01');//该月的月初时间戳
        $end = strtotime(date('Y').'-'.(date('m')+1).'-1')-86400;//该月的月末时间戳

        $Commision = new Commision();
        foreach ($info as $key => &$value) {
            //获取 经销商 所属省份
            if ($value['userPhone'] != '') {
                $_GET['p'] = $value['userPhone'];
                $ProvinceJson = $Commision->phone();
                $Province = json_decode($ProvinceJson)->province;
            }else{
                $Province = '暂无';
            }
           
            $value['Province'] = $Province;
            //获取佣金
            $a = $value['userId'];
            $sql = "SELECT sum(CommissionFee) FROM `jingo_salesend` where salesendID = $a and UNIX_TIMESTAMP(`createTime`) > $start and UNIX_TIMESTAMP(`createTime`) < $end ";
            $date = Db::query($sql);
            if (empty($date[0]['sum(CommissionFee)'])) {
                $value['CommissionFee'] = 0;
            }else{
                $value['CommissionFee'] = round($date[0]['sum(CommissionFee)']);
            }
            $list2['Rows'][] = $value; 
        }
        // header("content-type:text/html; charset=utf-8");
        //     echo "<pre>";
        //     print_r($list2);die;
        return $list2;
    }
    /**
    * 获取经销商列表 详情
    */
    public function distributor(){
        // echo $_GET['salesendID'];die;
        $this->assign('salesendID',$_GET['salesendID']);
        return $this->fetch('distributor_list');
    }  
    /**
    * 获 省取经销商列表 详情
    */
    public function distributorProvince(){
        // echo $_GET['salesendID'];die;
        $this->assign('salesendID',$_GET['salesendID']);
        return $this->fetch('distributor_list_Province');
    }  
    //经销商 历史报表
    public function history_report(){ 
        return $this->fetch('history_report');
    }  
    // 省经销商 历史报表
    public function history_report_Province(){     
        return $this->fetch('history_report_Province');
    }
    public function provincial_list(){
        return $this->fetch('provincial_list');
    } 
}
