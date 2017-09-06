<?php 
namespace wstmart\mobile\validate;
use think\Validate;
/**
 * ============================================================================
 * WSTMart开源商城
 * 官网地址:http://www.wstmall.com 
 * 联系QQ:707563272
 * ============================================================================
 * 用户地址验证器
 */
class UserAddress extends Validate{
	protected $rule = [
        ['userName'  ,'require','请输入联系名称'],
		['userPhone'  ,'require','请输入手机号码'],
		['areaId'  ,'require','请选择完整地址'],
	    ['userAddress'  ,'require','请输入详细地址']
    ];

    protected $scene = [
        'add'   =>  ['userName','userPhone','areaId','userAddress'],
        'edit'  =>  ['userName','userPhone','areaId','userAddress']
    ]; 
}