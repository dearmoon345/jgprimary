<?php 
namespace wstmart\home\validate;
use think\Validate;
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
 * 店铺验证器
 */
class Shops extends Validate{
	protected $rule = [
        ['shopImg'  ,'require','请上传店铺图标'],
        ['isInvoice'  ,'in:0,1','无效的发票类型'],
        ['invoiceRemarks','checkInvoiceRemark:1','请输入发票说明'],
        ['serviceStartTime','require','请选择服务时间'],
        ['serviceEndTime','require','请选择服务时间'],
        ['freight','integer','请输入运费'],
        ['bankId'  ,'require','请选择结算银行'],
        ['bankAreaId'  ,'require','请选择开户所地区'],
        ['bankNo'  ,'require','请选择银行账号'],
        ['bankUserName'  ,'require|max:100','请输入持卡人名称|持卡人名称长度不能能超过50个字符']
    ];

    protected $scene = [
        'editInfo'   =>  ['shopImg','isInvoice','serviceStartTime','serviceEndTime','freight'],
        'editBank'  =>  ['bankId','bankAreaId','bankNo','bankUserName']
    ]; 
    
    protected function checkInvoiceRemark($value){
    	$isInvoice = Input('post.isInvoice/d',0);
    	$key = Input('post.invoiceRemarks');
    	return ($isInvoice==1 && $key=='')?'请输入发票说明':true;
    }
}