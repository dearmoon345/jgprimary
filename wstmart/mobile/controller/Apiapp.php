<?php 
namespace wstmart\purse\controller;

use wstmart\common\model\Sdk as S;

	class Apiapp {

		// token 身份验证
		protected function token() {
			// $_POST['token'] = strtr($_POST['token'],'+','%2B');
			$to = \think\Db::name('user_token')->where('member_id',(int)input('post.id'))->order('token_id desc')->find();
			if($to['token']==input('post.token')){
				return 'success';
			}else{
				return 'error';
			}
		}

		//账单查询
		//@param post 用户的id token 
		//可选month 1 近一个月 2 近2个月
		//type      1 充值 2 提现 3 转账 4 消费 5 贷款 6 还款
		//status    0 全部 1 进行中 2 改成 3 失败
		//st & et   时间区间查询 格式 2017-1-1
		//sk  		收款记录查询	
		//page 分页参数 每页显示10条数据
		public function bill(){
			$s = new S();
			if(!empty($_POST['id']) && !empty($_POST['token']) && $_POST['token']!=null && $_POST['id']!=null){
				// $_POST['token'] = strtr($_POST['token'],'+','%2B');
				if(self::token($_POST['id'],$_POST['token'])==='success'){
				$where ="targetId=".$_POST['id'];
		        $da1 = date('Y-m-d H:i:s',strtotime('-1 month'));
		        $da2 = date('Y-m-d H:i:s',strtotime('-3 month'));
		        $da2 = date('Y-m-d H:i:s',strtotime('-6 month'));
		        $da3 = date('Y-m-d H:i:s',strtotime('-1 year'));
		        $month_array = array(1=>$da1,2=>$da2,3=>$da3);
		        $type_array = array(1=>'充值',2=>'提现',3=>'%转账%',4=>'消费',5=>'贷款',6=>'还款');
		        $status_array = array(1=>2,2=>1,3=>0);
	
		        if(isset($_POST['month'])&&($_POST['month']!=0))
		         	$where .= " AND createTime>'".$month_array[$_POST['month']]."'";
		        if(isset($_POST['type'])&&($_POST['type']!=0))
		        	$where .= " AND dataSrc like '".$type_array[$_POST['type']]."'";
		        if(isset($_POST['status'])&&($_POST['status']!=0))
		         	$where .= " AND dataFlag='".$status_array[$_POST['status']]."'";
		        if(isset($_POST['st'])&&($_POST['et']!='')&&($_POST['st']!=$_POST['et'])&&$_POST['st']!='')
		         	$where.=" AND createTime>='".$_POST['st']."'"." AND createTime<='".date('Y-m-d',strtotime($_POST['et'] . "+1 day"))."'";
		        if(isset($_POST['st'])&&($_POST['et']==''))
		         	$where.=" AND createTime>='".$_POST['st']."'";
		        if(isset($_POST['et'])&&($_POST['st']==''))
		         	$where.=" AND createTime<='".date('Y-m-d',strtotime($_POST['et'] . "+1 day"))."'";
		        if(isset($_POST['st'])&&($_POST['et']==$_POST['st']))
		         	$where.=" AND createTime LIKE '%".$_POST['st']."%'";
		         if(isset($_POST['sk'])&&($_POST['sk']))
		         	$where.=" AND dataId= '".$_POST['sk']."'";
		         if(isset($_POST['red'])&&($_POST['red']!=''))
		         	$where.=" AND dataId='".'19'."'"." OR dataId='".'20'."'"." OR dataId='".'21'."'"." OR dataId='".'19'."'";
		         // dump($where);die;
		        $users = \think\Db::name('log_moneys')
		        ->where($where)
		        ->where('dataFlag','not in','-1')
		        ->where('dataSrc','not in','1')
		        ->order('id','desc')
		        ->paginate(10,false,['query'=>$_POST]);
		        	// dump($users);
		        $user = \think\Db::name('users')->where('userId',$_POST['id'])->find();
		        // dump($user);die;
		        foreach ($users as $k => $v) {
		        	$v['monday'] = date('w',strtotime($v['createTime']));

		        	// $v['dataSrc']=
		        	// $v['photo'] = $user['userPhoto'];
		        	$users[$k] = $v;
		        }

					return json_encode(array('data'=>$users,'info'=>'success'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);


				}else{
					return json_encode(array('resultcode'=>$s->token_ne(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
				}
			}else{
					return json_encode(array('resultcode'=>$s->token_id(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
			}
	
		}
		//资金变动提醒
		public function bills(){
			$s = new S();
			if(!empty($_POST['id']) && !empty($_POST['token']) && $_POST['token']!=null && $_POST['id']!=null){
				// $_POST['token'] = strtr($_POST['token'],'+','%2B');
				if(self::token($_POST['id'],$_POST['token'])==='success'){
				$where ="targetId=".$_POST['id'];
		         // dump($where);
		        $users = \think\Db::name('log_moneys')
		        ->where($where)
		        ->where('dataFlag','not in','-1')
		        ->where('dataSrc','not in','1')
		        ->where('dataFlag',1)
		        ->order('id','desc')
		        ->paginate(10,false,['query'=>$_POST]);
		        	// dump($users);
		        $user = \think\Db::name('users')->where('userId',$_POST['id'])->find();
		        // dump($user);die;
		        foreach ($users as $k => $v) {
		        	$v['monday'] = date('w',strtotime($v['createTime']));
		        	// $v['dataSrc']=
		        	// $v['photo'] = $user['userPhoto'];
		        	$users[$k] = $v;
		        }
					return json_encode(array('data'=>$users,'info'=>'success'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
				}else{
					return json_encode(array('resultcode'=>$s->token_ne(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
				}
			}else{
					return json_encode(array('resultcode'=>$s->token_id(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
			}
	
		}

		#@param .post 用户id token
		//是否可以贷款 
		//可以返回success 不可以返回error
		//可以贷款显示 贷款额度和信用积分//信用分达到1000 并且没有贷款的情况下
		//不可以贷款   1,信用分不足1000 2,已经贷款.显示需要还款的金额及还款时间和信用积分
		public function loan_index($id) {
			// echo 1;
			$s = new S();
			if(!empty($_POST) && $_POST['token'] != null && $_POST['token'] != null){
				if(self::token($_POST['id'],$_POST['token'])==='success'){
					$users = \think\Db::name('users')
					->where(array('userId' => (int)$id))
					->field('residual_repayment,userScore,repayment_date,loanTimes')
					->find();
					// if($users['loanTimes']<3){
						if($users['userScore']>=1000 && $users['residual_repayment'] == 0 && $users['loanTimes']<3){
							$re=[];
							// $re['monery'] = $users['residual_repayment'];
							// echo '<pre>';
							if($users['residual_repayment']==0){
								// $re['monery'] = '10000';
								$re['money']=sprintf("%.2f", 10000);
								$re['userScore']=$users['userScore'];
							}else{
								// $re['monery'] = $users['residual_repayment'];
								$re['money']=sprintf("%.2f", $users['residual_repayment']);
								$re['userScore']=$users['userScore'];
							}
							// echo 111;
							return json_encode(array('data'=>$re,'resultcode'=>$s->Sure(),'info'=>'success'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
							// echo '</pre>';
						}elseif($users['userScore']>=1000 && $users['residual_repayment'] != 0 && $users['loanTimes']<3){
							$re=[];
							// $re['repayment_date'] = $users['repayment_date'];
							$re['repayment_date'] = date('Y-m-d',strtotime($users['repayment_date']));
							$re['money'] = $users['residual_repayment'];
							$re['userScore'] = $users['userScore'];
							// echo '<pre>';
							return json_encode(array('data'=>$re,'resultcode'=>$s->Maynot(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
							// echo '</pre>';
						}elseif($users['userScore']<1000 && $users['residual_repayment']==0){
							$re=[];
							$re['userScore'] = $users['userScore'];
							$re['money']	 = 0;
							// echo '<pre>';
							return json_encode(array('data'=>$re,'resultcode'=>$s->Jf_enough(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
							// echo '</pre>';
						}elseif($users['userScore']<1000 && $users['residual_repayment']>0){//积分不足但是还没有还款
							$re=[];
							// $re['repayment_date'] = $users['repayment_date'];
							$re['repayment_date'] = date('Y-m-d',strtotime($users['repayment_date']));
							$re['money'] = $users['residual_repayment'];
							$re['userScore'] = $users['userScore'];
							// echo '<pre>';
							return json_encode(array('data'=>$re,'resultcode'=>$s->Maynot(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
						}elseif($users['userScore']>=1000 && $users['residual_repayment'] > 0 && $users['loanTimes']==3){
							$re=[];
							// $re['repayment_date'] = $users['repayment_date'];
							$re['repayment_date'] = date('Y-m-d',strtotime($users['repayment_date']));
							$re['money'] = $users['residual_repayment'];
							$re['userScore'] = $users['userScore'];
							return json_encode(array('data'=>$re,'resultcode'=>$s->Maynot(),'info'=>'error'));
						}elseif($users['userScore']>=1000 && $users['residual_repayment'] == 0 && $users['loanTimes']==3){
							$re['money']=sprintf("%.2f", 10000);
							$re['userScore']=$users['userScore'];
							return json_encode(['data'=>$re,'resultcode'=>$s->Loan_Upperlimit(),'info'=>'error']);
						}
					// }else{
					// 	return json_encode(array('resultcode'=>$s->Loan_Upperlimit(),'info'=>'error'));
					// }
				}else{
					return json_encode(array('resultcode'=>$s->token_ne(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
				}
			}else{
					return json_encode(array('resultcode'=>$s->token_id(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
			}
		}



		//我要贷款
		//@param 用户id token 贷款金额 参数 loan_amount
		//信用分.不需要还款可以贷款 额度10000
		public function loan($id){
			$s = new S();
			if(!empty($_POST) && $_POST['id'] != null && $_POST['token'] != null){
				// dump($_POST);die;
				if(self::token($_POST['id'],$_POST['token'])==='success'){
					//查询贷款用户的信息
			        $user = \think\Db::name('users')
			        ->where(['userId' => $id])
			        ->field('repayment_date,residual_repayment,userMoney,userType,userId,userScore,loanTimes')
			        ->find();
			// dump($user['userType']);
			//贷款次数上限每月只能贷款3次
				if($user['loanTimes'] < 3){
					if($user['userScore']>=1000){ 
							if(is_numeric($_POST['loan_amount']) && $_POST['loan_amount']>0 && $user['residual_repayment']==0){
					            if($user['residual_repayment'] + $_POST['loan_amount'] <= 10000) {
					                // 启动事务
					                \think\Db::startTrans();
					                try{
					                //修改用户表的信息
					                $users['userMoney']           =   $user['userMoney'] + $_POST['loan_amount'];
					                $users['residual_repayment']  =   round($_POST['loan_amount']*0.0082,2)+$_POST['loan_amount'];
					                $users['loanAmount']		  =	  round($_POST['loan_amount']*0.0082,2)+$_POST['loan_amount'];
					                $users['loanTimes']		   	  =   $user['loanTimes']+1;
					                $users['loan_date']           =   date('Y-m-d H:i:s');
					                $users['repayment_date']      =   date('Y-m-d H:i:s',strtotime("+1 month"));
					                $userId = \think\Db::name('users')
					                ->where(['userId' => (int)$id])
					                ->update($users);
									//dump($user);die;
					                //将信息添加到还贷款日志
					                $alNo = $this->makeSn();
									//dump($alNo);die;
					                $data['user_id']        =  (int)$id;
					                $data['al_type']        =  '贷款';
					                $data['al_sn']          =  $alNo;
					                $data['al_amonut']      =  round($_POST['loan_amount'],2);
					                $data['al_payment']     =  '钱袋子';
					                $data['al_status']      =  '贷款成功';
					                $data['al_add_time']    =  date('Y-m-d H:i:s');
					                $da = \think\Db::name('also_loan')->insert($data);
									//dump($da);die;
					                //将信息添加到资金流水
					                $insert['targetType']   =  $user['userType'];
					                $insert['targetId']     =  $user['userId'];
					                $insert['dataId']       =  8;
					                $insert['dataSrc']      =  '贷款';
					                $insert['moneyType']    =  1;
					                $insert['money']        =  round($_POST['loan_amount'],2);
					                $insert['tradeNo']      =  $alNo;
					                $insert['isrepayment']	=  2;
					                $insert['createTime']   =  date('Y-m-d H:i:s');
					                $insert['Headportrait'] =  htmlspecialchars('upload/users/2016-10/20170603155425.png');//交易对方头像
					                $insert['endTime']      =  date('Y-m-d H:i:s');
					                // dump($insert);
					                $in = \think\Db::name('log_moneys')->insert($insert);
					                // echo $in;
					                //将信息添加到 消息表中
					                $da1['msgType']			 =	 0;
					                $da1['sendUserId']		 =	 1;
					                $da1['receiveUserId']	 =   $user['userId'];
					                $da1['msgTitle']		 =   '贷款';
					                $da1['msgContent']		 =	 '贷款成功';
					                $da1['amount']			 =   round($_POST['loan_amount'],2);
					                $da1['msgDesc']			 =	 '贷款';	
					                $da1['tradeNo']			 =	 $alNo;
					                $da1['repaymentDate']	 =	 time()+2592000;//date('Y-m-d H:i:s',strtotime("+1 month"));
					                $da1['completeTime']     =   date('Y-m-d H:i:s');
					                $da1['msgStatus']        =	 0;
					                $da1['dataFlag']		 =	 1;
					                $da1['createTime'] 		 =	 date('Y-m-d H:i:s');

					                $dd = \think\Db::name('messages')->insert($da1);
					                // dump($da1);die;
					                    // 提交事务
					                    \think\Db::commit();
					                } catch (\Exception $e) {
					                    // 回滚事务
					                    \think\Db::rollback();
					                }
					                if($userId && $da && $in && $dd) {
					                   
					        			return json_encode(array('resultcode'=>$s->Loan_success(),'info'=>'success'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
					        			
					                } else {
					                    return json_encode(array('resultcode'=>$s->Loan_error(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
					                }

					            } else {
					               // return json_encode(array('resultcode'=>$s->Loan_error(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
					               return json_encode(array('resultcode'=>$s->Loan_quota(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);

					            }
					        } else {
					            return json_encode(array('resultcode'=>$s->format_error(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
					    }

					}else{
						return json_encode(array('resultcode'=>$s->Jf_enough(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
					}

				}else{
					return json_encode(array('resultcode'=>$s->Loan_Upperlimit(),'info'=>'error'));
				}	

		       }else{
		       		return json_encode(array('resultcode'=>$s->token_ne(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
		       }
		    }else{
					return json_encode(array('resultcode'=>$s->token_id(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
		    }
		}


		//还款
		//@param .post 用户id token  
		//@param 还款金额参数 repayment_amount 
		//@param  判断还款是否逾期 没有逾期还款时增加信用分 还款金额的 1%
		//@param  逾期还款2天 已贷款的金额 提升5%
		public function repayment($id){
			$s = new S();
			if(!empty($_POST) && $_POST['token'] != null && $_POST['id'] != null){
				if(self::token($_POST['id'],$_POST['token'])==='success'){
					$alNo = $this->makeSn();
			//        var_dump($_POST);die;
			        //查询还款用户的信息
			        $user = \think\Db::name('users')
			        ->where(['userId' => (int)$id])
			        ->field('residual_repayment,userMoney,userId,userType,userScore,repayment_date')
			        ->find();
			       // dump($user);
			        if(is_numeric($_POST['repayment_amount']) && $_POST['repayment_amount']>0){
			        	if($user['userMoney'] >= $_POST['repayment_amount']){
				            if($user['residual_repayment'] >= $_POST['repayment_amount']) {
				                // 启动事务
				                \think\Db::startTrans();
				                try{
				                //判断还款是否逾期//没有逾期
				                if(strtotime($user['repayment_date']) >= time() ){
				                	$users['userScore'] = floor(input('post.repayment_amount')*0.01)+$user['userScore'];
				                }else{
				                	$users['overdue'] = 1;
				                }
				                //贷款是否还清
				                if($user['residual_repayment']-$_POST['repayment_amount']==0){
				                	$users['loanAmount']	=	0;
				                	$value					= 	'payoff';
				                	$insert['isrepayment']	=	1;//是否还清贷款还清
				                	$str=\think\Db::name('log_moneys')->where('targetId',(int)$id)->where('dataId','in','8,9')->update(['isrepayment'=>1]);
				                }else{
				                	$value					=	'no';
				                	$insert['isrepayment']	=	0;//是否还清贷款未还清(没有完全还清贷款)
				                }
				                //修改用户表的信息
				                $users['userMoney']           = $user['userMoney'] - $_POST['repayment_amount'];
				                $users['residual_repayment']  = $user['residual_repayment'] - $_POST['repayment_amount'];

				                $userId = \think\Db::name('users')->where(['userId' => (int)$id])->update($users);
				               // dump($user);die;
				                //将信息添加到还贷款日志
				                // $alNo = $this->makeSn();
				                $data['user_id']         =   $user['userId'];
				                $data['al_type']         =   '还款';
				                $data['al_sn']           =   $alNo;
				                $data['al_payment']      =   '钱袋子';
				                $data['al_amonut']       =   $_POST['repayment_amount'];
				                $data['al_status']       =   '还款成功';
				                $data['al_add_time']     =   date('Y-m-d H:i:s');
				                $da = \think\Db::name('also_loan')->insert($data);
				               // dump($da);die;
				                //将信息添加到资金流水
				                $insert['targetType']    =   $user['userType'];
				                $insert['targetId']      =   $user['userId'];
				                $insert['dataId']        =   9;
				                $insert['dataSrc']       =   '贷款';
				                // $insert['dataSrc']		 =   '还款';
				                $insert['moneyType']     =   0;
				                $insert['money']         =   $_POST['repayment_amount'];
				                $insert['tradeNo']       =   $alNo;
				                $insert['payType']       =   0;
				                $insert['createTime']    =   date('Y-m-d H:i:s');
				                $insert['Headportrait']  =   htmlspecialchars('upload/users/2016-10/20170603155425.png');//交易对方头像
				                $insert['endTime']       =   date('Y-m-d H:i:s');
				                // $insert['remark']        =   '';
				                $in = \think\Db::name('log_moneys')->insert($insert);

				                //将信息添加到 消息表中
				                $da1['msgType']			 =	 0;
				                $da1['sendUserId']		 =	 1;
				                $da1['receiveUserId']	 =   $user['userId'];
				                $da1['msgTitle']		 =   '还款';
				                $da1['msgContent']		 =	 '还款成功';
				                $da1['amount']			 =   $_POST['repayment_amount'];
				                $da1['msgDesc']			 =	 '钱袋子';	
				                $da1['tradeNo']			 =	 $alNo;
				                $da1['completeTime']     =   date('Y-m-d H:i:s');
				                $da1['msgStatus']        =	 0;
				                $da1['dataFlag']		 =	 1;
				                $da1['createTime'] 		 =	 date('Y-m-d H:i:s');
				                $dd = \think\Db::name('messages')->insert($da1);
				              
				                //判断是否是从信息页面跳转过来的
				                if(!empty($_POST['loan'])){
				                    //将信息标为已完成
				                    \think\Db::name('news')->where(['id'=>$_POST['loan']])->update(['is_complete'=>1,'complete_time'=>date('Y-m-d H:i:s')]);
				                }
				                //     // 提交事务
				                    \think\Db::commit();
				                } catch (\Exception $e) {
				                    // 回滚事务
				                    \think\Db::rollback();
				                }
				                if($userId && $da && $in && $dd) {

				                    return json_encode(array('resultcode'=>$s->repayment_success(),'info'=>'success','isrepayment'=>$value),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
				                    
				                } else {
				                	//将信息添加到资金流水//失败信息
					                $insert['targetType']    =   $user['userType'];
					                $insert['targetId']      =   $user['userId'];
					                $insert['dataId']        =   9;
					                // $insert['dataSrc']       =   '贷款';
				                	$insert['dataSrc']		 =   '还款';
					                $insert['moneyType']     =   0;
					                $insert['money']         =   $_POST['repayment_amount'];
					                $insert['tradeNo']       =   $alNo;
					                $insert['payType']       =   1;
					                $insert['createTime']    =   date('Y-m-d H:i:s');
					                $insert['Headportrait']  =   htmlspecialchars('upload/users/2016-10/20170603155425.png');//交易对方头像
					                $insert['endTime'] = date('Y-m-d H:i:s');
					                
					                $in = \think\Db::name('log_moneys')->insert($insert);
				                    return json_encode(array('resultcode'=>$s->repayment_error(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
				                }
				            } else {
				            	//将信息添加到资金流水//失败信息
					                $insert['targetType']    =   $user['userType'];
					                $insert['targetId']      =   $user['userId'];
					                $insert['dataId']        =   9;
					                // $insert['dataSrc']       =   '贷款';
				                	$insert['dataSrc']		 =   '还款';
					                $insert['moneyType']     =   0;
					                $insert['money']         =   $_POST['repayment_amount'];
					                $insert['tradeNo']       =   $alNo;
					                $insert['payType']       =   1;
					                $insert['createTime']    =   date('Y-m-d H:i:s');
					                $insert['Headportrait']  =   htmlspecialchars('upload/users/2016-10/20170603155425.png');//交易对方头像
					                $insert['endTime'] = date('Y-m-d H:i:s');
					                $in = \think\Db::name('log_moneys')->insert($insert);
				                return json_encode(array('resultcode'=>$s->repayment_exceed(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
				            }
			       	 	}else{
			        		return json_encode(array('resultcode'=>$s->transfer_purse_monery(),'info'=>'error'));
			       		}
			        } else {
			        	//将信息添加到资金流水//失败信息
				                $insert['targetType']    =   $user['userType'];
				                $insert['targetId']      =   $user['userId'];
				                $insert['dataId']        =   9;
				                // $insert['dataSrc']       =   '贷款';
			                	$insert['dataSrc']		 =   '还款';
				                $insert['moneyType']     =   0;
				                $insert['money']         =   $_POST['repayment_amount'];
				                $insert['tradeNo']       =   $alNo;
				                $insert['payType']       =   1;
				                $insert['createTime']    =   date('Y-m-d H:i:s');
				                $insert['Headportrait']  =   htmlspecialchars('upload/users/2016-10/20170603155425.png');//交易对方头像
				                $insert['endTime'] = date('Y-m-d H:i:s');
				                $in = \think\Db::name('log_moneys')->insert($insert);
			            return json_encode(array('resultcode'=>$s->format_error(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
			        }
			    }else{
		       		return json_encode(array('resultcode'=>$s->token_ne(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
			    }
			}else{
					return json_encode(array('resultcode'=>$s->token_id(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);	
			}

    }


		//金豆查询
		//@param 用户id token
		//@param dataId 10充金豆 11金豆提现 12金豆转账 13收金豆
		public function imazamox_index($id){
			$s = new S();
			if(!empty($_POST) && $_POST['id'] != null && $_POST['token'] != null){
				if(self::token($_POST['id'],$_POST['token'])==='success'){
					$users = \think\Db::name('users')
					->where(array('userId' => (int)$_POST['id']))
					->find();
					$u=[];
					$u['imazamox_number'] = $users['imazamox_number'];
					//dump($users);
		       		//获取金豆变更表的记录
			        $im = \think\Db::name('log_moneys')
			        ->where(array('targetId' => (int)$id))
			        ->where('dataId','in','10,11,12,13,18,20')
			        ->where('dataFlag','not in','-1')
			        ->order('id desc')
			        ->paginate(10);
			        // echo '<pre>';
			        //添加星期 
		    		foreach ($im as $k => $v) {
			        	$v['monday'] = date('w',strtotime($v['createTime']));
			        	// $v['photo'] = $user['userPhoto'];
			        	$im[$k] = $v;
		        	}
		        	// echo '<pre>';
		        	if($im){
			        	return json_encode(array('data'=>$im,'info'=>'success','money'=>$u),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
		        	}else{
			        	return json_encode(array('info'=>'error','money'=>'0'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
		        	}
			        // echo '</pre>';
				}else{
		       		return json_encode(array('resultcode'=>$s->token_ne(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
					
				}
			}else{
					return json_encode(array('resultcode'=>$s->token_id(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
			}

		}


	
	//转账到钱袋//用户转账
	//@param 用户id 和token 选择参数 purse
	//purse == purse       转账
	//purse == new         发送收款消息
	//@param member_name   对方的账号或者手机号
	//@param amount        转账或者收款 的金额
	//@param desc          备注
    public function transfer_purse() {
    	// dump($_POST);
    	$s = new S();
    	if(!empty($_POST) && $_POST['id'] != null && $_POST['token'] != null){
	    	if(self::token($_POST['id'],$_POST['token'])==='success'){
	    		$tradeNo = $this->makeSn();

	    		if($_POST['purse']=='purse'){//存在是
	    			//信息过滤
		            //获取当前用户的信息
		            $user = \think\Db::name('users')
		            ->where(array('userId' => (int)$_POST['id']))
		            ->field('loginName,userMoney,userId,userType,userPhoto')
		            ->find();
		            //获取收款人的信息
		            $users = \think\Db::name('users')
		            ->where('loginName|userPhone',$_POST['member_name'])
		            ->field('loginName,userMoney,userId,userType,userPhoto')
		            ->find();
		            if ($users) {
			            if($users['loginName'] != $user['loginName']) {
			                if (is_numeric($_POST['amount']) && $_POST['amount'] >= '0.01') {
			                    if ($user['userMoney'] >= $_POST['amount']) {
		                        
		           						// dump($users);die;
		                                // 启动事务
		                                \think\Db::startTrans();
		                                try{
		                          
		                                	//修改用户的余额
		                                    $me = \think\Db::name('users')
		                                    ->where(array('userId' => $_POST['id']))
		                                    ->update(['userMoney' => $user['userMoney'] - $_POST['amount']]);
		                                    //修改收款人的余额
		                                    $mem = \think\Db::name('users')
		                                    ->where(array('loginName' => $users['loginName']))
		                                    ->update(['userMoney' => $users['userMoney'] + $_POST['amount']]);

		                                        // if(!empty($_POST['code'])){//判断是否是二维码
		                                        // 	$code = 1;
		                                        // }else{
		                                        // 	$code = 0;
		                                        // }

		                                        $data = array(
		                                        	'targetType'   =>   $user['userType'], 
		                                        	'targetId'     =>   $_POST['id'], 
		                                        	'dataId'       =>   4, 
		                                        	'dataSrc'      =>   '转账', 
		                                        	'moneyType'    =>   0, 
		                                        	'money'        =>   $_POST['amount'], 
		                                        	'payName'      =>   $users['loginName'],
		                                        	'tradeNo'      =>   $tradeNo, 
		                                        	'Headportrait' =>   isset($users['userPhoto'])?$users['userPhoto']:'',
		                                        	'payType'      =>   0, 
		                                        	'dataFlag'     =>   1, 
		                                        	// 'QRcode'	   =>	$code,
		                                        	'createTime'   =>   date('Y-m-d H:i:s'), 
		                                        	'remark'       =>   htmlspecialchars($_POST['desc']),
		                                        	'endTime'      =>   date('Y-m-d H:i:s'));
		                                        $data1 = array(
		                                        	'targetType'   =>   $users['userType'], 
		                                        	'targetId'     =>   $users['userId'], 
		                                        	'dataId'       =>   5, 
		                                        	'dataSrc'      =>   '转账', 
		                                        	'moneyType'    =>   1, 
		                                        	'money'        =>   $_POST['amount'], 
		                                        	'payName'      =>   $user['loginName'],
		                                        	'tradeNo'      =>   $tradeNo, 
		                                        	'Headportrait' =>   isset($user['userPhoto'])?$user['userPhoto']:'',
		                                        	'payType'      =>   0, 
		                                        	'dataFlag'     =>   1, 
		                                        	// 'QRcode'	   =>	$code,
		                                        	'createTime'   =>   date('Y-m-d H:i:s'), 
		                                        	'remark'       =>   htmlspecialchars($_POST['desc']),
		                                        	'endTime'      =>   date('Y-m-d H:i:s'),
		                                        	);
		                                       \think\Db::name('log_moneys')->insert($data);
		                                       \think\Db::name('log_moneys')->insert($data1);
		                                    // }
		                                    // dump($user['userType']);die;
		                                    //将信息添加到转账表里面
			                                    $da = array(
			                                    	'targetType'  =>    $user['userType'], 
			                                    	'targetId'    =>    $user['userId'],  
			                                    	'moneyType'   =>    0, 
			                                    	'money'       =>    $_POST['amount'], 
			                                    	'payName'     =>    $users['loginName'],
			                                    	'tradeNo'     =>    $tradeNo, 
			                                    	'payType'     =>    0, 
			                                    	'dataFlag'    =>    1, 
			                                    	'createTime'  =>    date('Y-m-d H:i:s'), 
			                                    	'remark'      =>    htmlspecialchars($_POST['desc']));

		                                    // dump($da);die;
			                                    $da1 = array(
			                                    	'targetType'  =>    $users['userType'], 
			                                    	'targetId'    =>    $users['userId'], 
			                                    	'moneyType'   =>    1, 
			                                    	'money'       =>    $_POST['amount'], 
			                                    	'payName'     =>    $user['loginName'],
			                                    	'tradeNo'     =>    $tradeNo, 
			                                    	'payType'     =>    0, 
			                                    	'dataFlag'    =>    1, 
			                                    	'createTime'  =>    date('Y-m-d H:i:s'), 
			                                    	'remark'      =>    htmlspecialchars($_POST['desc']));
		                                    \think\Db::name('transfer')->insert($da);
		                                    \think\Db::name('transfer')->insert($da1);		                               
		                                    // 提交事务
		                                    \think\Db::commit();

		                                    return json_encode(array('resultcode'=>$s->transfer_purse_ok(),'info'=>'success','order'=>$tradeNo),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);

		                                } catch (\Exception $e) {
		                                //     // 回滚事务
		                                    \think\Db::rollback();
		                                    return json_encode(array('resultcode'=>$s->transfer_purse_fail(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
		                                    // echo 0;
		                                }

			                    } else {
			                        return json_encode(array('resultcode'=>$s->transfer_purse_monery(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
			                    }
			                } else {
			                    return json_encode(array('resultcode'=>$s->format_error(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
			                }
			            } else {		            			
			                return json_encode(array('resultcode'=>$s->transfer_purse_own(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
			            }
                    } else {
                        return json_encode(array('resultcode'=>$s->name_no(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
                    }

				}elseif($_POST['purse']=='new'){
					//消息发送
					$tradeNo = $this->makeSn();//流水号
					$users = \think\Db::name('users')
					->where('userId',(int)$_POST['id'])
					->field('loginName,userPhone,userId,userType')
					->find();
					//获取转账用户的信息
				     $user = \think\Db::name('users')->where('loginName|userPhone',$_POST['member_name'])->find();
					// dump($user);die;
					if($user){
						if($_POST['member_name'] != $users['loginName'] && $_POST['member_name'] != $users['userPhone']) {
				            if (is_numeric($_POST['amount']) && $_POST['amount'] >= '0.01') {
			                    // 启动事务
			                    \think\Db::startTrans();
			                    try{
			                        $data['msgType']       =   2;
			                        $data['receiveUserId'] =   $user['userId'];
			                        $data['sendUserId']    =   $users['userId'];
			                        $data['msgTitle']      =   '收款';
			                        $data['msgContent']    =   '用户' . $users['loginName'] . '向'.$user['loginName'].'收款' . $_POST['amount'] . '元，说明：' . htmlspecialchars($_POST['desc']);
			                        $data['amount']        =   $_POST['amount'];
			                        $data['tradeNo']       =   $tradeNo;
			                        $data['dataFlag']      =   0;
			                        $data['msgDesc']       =   htmlspecialchars($_POST['desc']);
			                        $data['createTime']    =   date('Y-m-d H:i:s');
			                        $news = \think\Db::name('messages')->insert($data);
			                        // echo $news;die;
			                        // $data2['msgType']       =   2;
			                        // $data2['receiveUserId'] =   $user['userId'];
			                        // $data2['sendUserId']    =   $users['userId'];
			                        // $data2['msgTitle']      =   '收款';
			                        // $data2['msgContent']    =   '你向用户'.$user['loginName'].'收款'. $_POST['amount'] . '元，说明：' . htmlspecialchars($_POST['desc']);
			                        // $data2['amount']        =   $_POST['amount'];
			                        // $data2['tradeNo']       =   $tradeNo;
			                        // $data2['dataFlag']      =   0;
			                        // $data2['msgDesc']       =   htmlspecialchars($_POST['desc']);
			                        // $data2['createTime']    =   date('Y-m-d H:i:s');
			                        // $new1 = \think\Db::name('messages')->insert($data2);
			                        // dump($news);die;
			                        // 提交事务
			                        \think\Db::commit();
			                    } catch (\Exception $e) {
			                        // 回滚事务
			                        \think\Db::rollback();
			                    }
			                    if ($news) {
			                        // 启动事务
			                        \think\Db::startTrans();
			                        try{
			                        //将信息添加到资金流水表
			                        $data1 = array(
			                        	'targetType' =>   $user['userType'], 
			                        	'targetId'   =>   $user['userId'], 
			                        	'dataId'     =>   4, 
			                        	'dataSrc'    =>   '转账', 
			                        	'moneyType'  =>   0, 
			                        	'money'      =>   $_POST['amount'], 
			                        	'Headportrait'=>  isset($users['userPhoto'])?$users['userPhoto']:'',
			                        	'payName'    =>   $users['loginName'],
			                        	'tradeNo'    =>   $tradeNo, 
			                        	'payType'    =>   0, 
			                        	'dataFlag'   =>   2, 
			                        	'createTime' =>   date('Y-m-d H:i:s'), 
			                        	'remark'     =>   htmlspecialchars($_POST['desc']),
			                        	);
			                        \think\Db::name('log_moneys')->insert($data1);
			                        //将信息添加到资金流水表
			                        $in = array(
			                        	'targetType' =>   $users['userType'], 
			                        	'targetId'   =>   $users['userId'], 
			                        	'dataId'     =>   5, 
			                        	'dataSrc'    =>   '转账', 
			                        	'moneyType'  =>   1, 
			                        	'money'      =>   $_POST['amount'], 
			                        	'payName'    =>   $user['loginName'],
			                        	'tradeNo'    =>   $tradeNo,
			                        	'Headportrait'=>  isset($user['userPhoto'])?$user['userPhoto']:'',  
			                        	'payType'    =>   0, 
			                        	'dataFlag'   =>   2, //进行中
			                        	'createTime' =>   date('Y-m-d H:i:s'), 
			                        	'remark'     =>   htmlspecialchars($_POST['desc']),
			                        	);
			                        \think\Db::name('log_moneys')->insert($in);
			                        // 提交事务
			                        \think\Db::commit();
			                    } catch (\Exception $e) {
			                        // 回滚事务
			                        \think\Db::rollback();
			                    }
									return json_encode(array('resultcode'=>$s->new_ok(),'info'=>'success','order'=>$tradeNo),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);

			                    } else {
			                    	//将数据添加到资金流水//失败数据
				                    $insert['targetType']  =   $user['userType'];
				                    $insert['targetId']    =   $user['userId'];
				                    $insert['dataId']      =   5;
				                    $insert['dataSrc']     =   '转账';
				                    $insert['moneyType']   =   1;
				                    $insert['dataFlag']    =   0;
				                    $insert['money']       =   $_POST['amount'];
				                    $insert['payName']     =   $user['loginName'];
				                    $insert['tradeNo']     =   $tradeNo;
				                    $insert['payType']     =   1;
				                    $insert['createTime']  =   date('Y-m-d H:i:s');
				                    $insert['remark']      =   htmlspecialchars($_POST['desc']);
				                    $in = \think\Db::name('log_moneys')->insert($insert);

			                        return json_encode(array('resultcode'=>$s->new_fail(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
			                    }
				               
				            }else{
				            	//将数据添加到资金流水//失败数据
			                    $insert['targetType']  =   $user['userType'];
			                    $insert['targetId']    =   $user['userId'];
			                    $insert['dataId']      =   5;
			                    $insert['dataSrc']     =   '转账';
			                    $insert['moneyType']   =   1;
			                    $insert['dataFlag']    =   0;
			                    $insert['money']       =   $_POST['amount'];
			                    $insert['payName']     =   $user['loginName'];
			                    $insert['tradeNo']     =   $tradeNo;
			                    $insert['payType']     =   1;
			                    $insert['createTime']  =   date('Y-m-d H:i:s');
			                    $insert['remark']      =   htmlspecialchars($_POST['desc']);
			                    $in = \think\Db::name('log_moneys')->insert($insert);
				                return json_encode(array('resultcode'=>$s->new_money(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
				            }
				        } else {
				        	//将数据添加到资金流水//失败数据
		                    $insert['targetType']  =   $user['userType'];
		                    $insert['targetId']    =   $user['userId'];
		                    $insert['dataId']      =   5;
		                    $insert['dataSrc']     =   '转账';
		                    $insert['moneyType']   =   1;
		                    $insert['dataFlag']    =   0;
		                    $insert['money']       =   $_POST['amount'];
		                    $insert['payName']     =   $user['loginName'];
		                    $insert['tradeNo']     =   $tradeNo;
		                    $insert['payType']     =   1;
		                    $insert['createTime']  =   date('Y-m-d H:i:s');
		                    $insert['remark']      =   htmlspecialchars($_POST['desc']);
		                    $in = \think\Db::name('log_moneys')->insert($insert);
				            return json_encode(array('resultcode'=>$s->new_own(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
				        }
					}else{
			 		// echo 2;
                	//将数据添加到资金流水//失败数据
                    $insert['targetType']  =   '';
                    $insert['targetId']    =   htmlspecialchars($_POST['member_name']);
                    $insert['dataId']      =   5;
                    $insert['dataSrc']     =   '转账';
                    $insert['moneyType']   =   1;
                    $insert['dataFlag']    =   0;
                    $insert['money']       =   $_POST['amount'];
                    $insert['payName']     =   '';
                    $insert['tradeNo']     =   $tradeNo;
                    $insert['payType']     =   1;
                    $insert['createTime']  =   date('Y-m-d H:i:s');
                    $insert['remark']      =   htmlspecialchars($_POST['desc']);
                    $in = \think\Db::name('log_moneys')->insert($insert);
                    return json_encode(array('resultcode'=>$s->name_no(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
                }
            }
			}else{
				return json_encode(array('resultcode'=>$s->token_ne(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
			} 
		}else{
			return json_encode(array('resultcode'=>$s->token_id(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);

		}	

	}   



   //贷款记录查询
   //@param 用户的id token
   //@param dataId 8贷款 9还款
    public function loan_record(){
    	$s = new S();
    	if(!empty($_POST) && $_POST['id'] != null && $_POST['token'] !=null){
	    	if(self::token($_POST['id'],$_POST['token'])==='success'){
	    		//只有贷款没有还款的查询
		    	$al = \think\Db::name('log_moneys')
		    	->where('targetId',(int)$_POST['id'])
		    	->where('dataId','in','8,9')
		    	->where('dataFlag','not in','-1')
		    	->order('id desc')
		    	->paginate(10);
		    	// return 
		    	if($al){
		    		//添加星期 
		    		foreach ($al as $k => $v) {
			        	$v['monday'] = date('w',strtotime($v['createTime']));
			        	// $v['photo'] = $user['userPhoto'];
			        	$al[$k] = $v;
		        	}
		    		// echo '<pre>';
		    		return json_encode(array('data'=>$al,'info'=>'success'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
		    		// echo '</pre>';
		    	}else{
					return json_encode(array('info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
		    	}
	    	}else{
	    		return json_encode(array('resultcode'=>$s->token_ne(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
	    	}
	  	}else{
			return json_encode(array('resultcode'=>$s->token_id(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);

	  	}
    }

    //钱袋子记录
    //@param 用户的id token
    //@param dataId 
    //4钱袋转账 
    //5钱袋收款
    //6充值到钱袋 
    //7钱袋提现 
    //8贷款 
    //9还款 
    //14向商家付款(二维码) 
    //15钱袋充值到金豆(钱袋记录)
    //16金豆提钱袋(钱袋记录)
    public function index(){
    	$s= new S();
    	// dump($s);
    	// if(!empty($_POST)){
	    		if(!empty($_POST) && $_POST['token'] != null && $_POST['id'] != null){
		    	if(self::token($_POST['id'],$_POST['token'])==='success'){
			    	$user=\think\Db::name('users')->where('userId',$_POST['id'])->find();
			    	// if($user){
				    	$u=[];
				    	$u['userMoney']  =  $user['userMoney'];
				    	$u['lockMoney']  =  $user['lockMoney'];
				    	$al = \think\Db::name('log_moneys')
				    	->where('targetId',$_POST['id'])
				    	->where('dataId','in','4,5,6,7,8,9,14,15,16,17,19,21')
				    	->where('dataFlag','not in','-1')
				    	->order('id desc')
				    	->paginate(10);
				    	foreach ($al as $k => $v) {
						        	$v['monday'] = date('w',strtotime($v['createTime']));
						        	$al[$k] = $v;
					        	}
					    // echo '<pre>';
				    	return json_encode(array('data'=>$al,'info'=>'success','money'=>$u),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
					// echo '</pre>' ;
		    	}else{
		    		// echo $s->token_ne()
			    	return json_encode(array('resultcode'=>$s->token_ne(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
		    	}
	    	}else{
				return json_encode(array('resultcode'=>$s->token_id(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);

	    	}
    }
    

    //金豆转账
    //@param 用户的id token
    //@param target 对方的账号或者手机号
    //@param amount 金豆数量
    public function imazamox(){
    	$s= new S();
    	if(!empty($_POST) && $_POST['id'] != null && $_POST['token'] !=null){
	    	if(self::token($_POST['id'],$_POST['token'])==='success'){
		    	$tradeNo = $this->makeSn();
		    	// dump($_POST);
		    	//用户名是否存在判断
		    	$users = \think\Db::name('users')
		    	->where('loginName|userPhone',$_POST['target'])
		    	->field('userId,imazamox_number,userType,loginName,userPhoto')
		    	->find();
		    	if($users){
		    		//本人
		    		$user = \think\Db::name('users')
		    		->where('userId',(int)$_POST['id'])
		    		->field('userId,imazamox_number,userType,loginName,userPhoto')
		    		->find();
		    		if($user['loginName'] != $users['loginName']){
			    		if($user['imazamox_number']-$_POST['amount']>=0){
			    			// 启动事物
			    			//修改自己金豆信息
			    			\think\Db::startTrans();
			                try{
				    			$me = \think\Db::name('users')
				    			->where('userId',(int)$_POST['id'])
				    			->update(['imazamox_number'=>$user['imazamox_number']-$_POST['amount']]);

				    			$mem = \think\Db::name('users')
				    			->where('loginName|userPhone',$_POST['target'])
				    			->update(['imazamox_number'=>$users['imazamox_number']+$_POST['amount']]);
				    			// 提交事务
				                \think\Db::commit();
			    			} catch (\Exception $e) {
			                    // 回滚事务
			                    \think\Db::rollback();
			                }
			    			if($me==1 && $mem==1){
			    				//将信息添加到资金流水表
			    				\think\Db::startTrans();
			                    try{
				                    $data1 = array(
				                   		'targetType'   =>    $user['userType'],
				                    	'targetId'     =>    $user['userId'], 
				                    	'dataId'       =>    12,
				                    	'dataSrc'      =>    '转账',
				                    	'moneyType'    =>    0,
				                    	'money'        =>    $_POST['amount'],
				                    	'payName'      =>    $users['loginName'],
				                    	'tradeNo'      =>    $tradeNo,
				                    	'payType'      =>    0,
				                    	'dataFlag'     =>    1,
				                    	'Headportrait' =>    isset($users['userPhoto'])?$users['userPhoto']:'',  
				                    	'createTime'   =>    date('Y-m-d H:i:s'),
				                    	'remark'       =>    htmlspecialchars($_POST['desc']),
				                    	'endTime'      =>    date('Y-m-d H:i:s'));
				                		\think\Db::name('log_moneys')->insert($data1);	    		
				                    //将信息添加到资金流水表
				                    $in = array(
				                    	'targetType'   =>    $users['userType'], 
				                    	'targetId' 	   =>    $users['userId'], 
				                    	'dataId'       =>    13, 
				                    	'dataSrc'      =>    '转账', 
				                    	'moneyType'    =>    1, 
				                    	'money'        =>    $_POST['amount'], 
				                    	'payName'      =>    $user['loginName'],
				                    	'Headportrait' =>    isset($user['userPhoto'])?$user['userPhoto']:'',
				                    	'tradeNo'      =>    $tradeNo, 
				                    	'payType'      =>    0, 
				                    	'dataFlag'     =>    1, 
				                    	'createTime'   =>    date('Y-m-d H:i:s'), 
				                    	'remark'       =>    htmlspecialchars($_POST['desc']),
				                   		'endTime'      =>    date('Y-m-d H:i:s'));
				                        \think\Db::name('log_moneys')->insert($in);

					                    //将信息添加到转账表里面
					                    $tradeNo = $this->makeSn();
					                    $da = array(//支出
					                    'targetType'   =>    $user['userType'], 
					                    'targetId'     =>    $user['userId'],  
					                    'moneyType'    =>    0, 
					                    'money'        =>    $_POST['amount'], 
					                    'payName'      =>    $users['loginName'],
					                    'tradeNo'      =>    $tradeNo, 
					                    'payType'      =>    0, 
					                    'imazamox'     =>    1,
					                    'dataFlag'     =>    1, 
					                    'createTime'   =>    date('Y-m-d H:i:s'), 
					                    'remark'       =>    htmlspecialchars($_POST['desc']));
				                     // dump($da);die;
					                     $da1 = array(//收入
					                    'targetType'   =>    $users['userType'], 
					                    'targetId'     =>    $users['userId'], 
					                    'moneyType'    =>    1, 
					                    'money'        =>    $_POST['amount'], 
					                    'payName'      =>    $user['loginName'],
					                    'tradeNo'      =>    $tradeNo, 
					                    'payType'      =>    0, 
					                    'imazamox'     =>    1,
					                    'dataFlag'     =>    1, 
					                    'createTime'   =>    date('Y-m-d H:i:s'), 
					                    'remark'       =>    htmlspecialchars($_POST['desc']));
				                        \think\Db::name('transfer')->insert($da);
				                        \think\Db::name('transfer')->insert($da1);

				                	\think\Db::commit();
			                    } catch (\Exception $e) {
			                        // 回滚事务
			                        \think\Db::rollback();
			                    }
				                return json_encode(array('info'=>'success','resultcode'=>$s->imazamox_success()),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
			    			}else{
			    				//将数据添加到资金流水//失败数据
				   				$in = array(
				                    	'targetType' =>   $users['userType'], 
				                    	'targetId' 	 =>   $users['userId'], 
				                    	'dataId'     =>   12, 
				                    	'dataSrc'    =>   '转账', 
				                    	'moneyType'  =>   1, 
				                    	'money'      =>   $_POST['amount'], 
				                    	'payName'    =>   $user['loginName'],
				                    	'Headportrait'=>  isset($user['userPhoto'])?$user['userPhoto']:'',
				                    	'tradeNo'    =>   $tradeNo, 
				                    	'payType'    =>   0, 
				                    	'dataFlag'   =>   0, 
				                    	'createTime' =>   date('Y-m-d H:i:s'), 
				                    	'remark' 	 =>   htmlspecialchars($_POST['desc']),
				                   		'endTime'    =>   date('Y-m-d H:i:s'));
				                        \think\Db::name('log_moneys')->insert($in);
				                return json_encode(array('resultcode'=>$s->imazamox_error(),'info'=>'error',),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);	
			    			}
			    		}else{
			    			//将数据添加到资金流水//失败数据
				   				$in = array(
				                    	'targetType'   =>   $users['userType'], 
				                    	'targetId' 	   =>   $users['userId'], 
				                    	'dataId'       =>   13, 
				                    	'dataSrc'      =>   '转账', 
				                    	'moneyType'    =>   1, 
				                    	'money'        =>   $_POST['amount'], 
				                    	'payName'      =>   $user['loginName'],
				                    	'Headportrait' =>   isset($user['userPhoto'])?$user['userPhoto']:'',
				                    	'tradeNo'      =>   $tradeNo, 
				                    	'payType'      =>   0, 
				                    	'dataFlag'     =>   0, 
				                    	'createTime'   =>   date('Y-m-d H:i:s'), 
				                    	'remark' 	   =>   htmlspecialchars($_POST['desc']),
				                   		'endTime'      =>   date('Y-m-d H:i:s'));
				                        \think\Db::name('log_moneys')->insert($in);
				            return json_encode(array('resultcode'=>$s->imazamox_null(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);	
			    		}
		    		}else{
		    			return json_encode(array('resultcode'=>$s->transfer_purse_own(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
		    		}

		    	}else{
		    		//将数据添加到资金流水//失败数据
	   				$in = array(
	                    	'targetType'   =>   $users['userType'], 
	                    	'targetId' 	   =>   $users['userId'], 
	                    	'dataId'       =>   13, 
	                    	'dataSrc'      =>   '转账', 
	                    	'moneyType'    =>   1, 
	                    	'money'        =>   $_POST['amount'], 
	                    	'payName'      =>   $user['loginName'],
				            'Headportrait' =>   isset($user['userPhoto'])?$user['userPhoto']:'',
	                    	'tradeNo'      =>   $tradeNo, 
	                    	'payType'      =>   0, 
	                    	'dataFlag'     =>   0, 
	                    	'createTime'   =>   date('Y-m-d H:i:s'), 
	                    	'remark' 	   =>   htmlspecialchars($_POST['desc']),
	                   		'endTime'      =>   date('Y-m-d H:i:s'));
	                        \think\Db::name('log_moneys')->insert($in);
		    		return json_encode(array('resultcode'=>$s->name_no(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
		    	}
		    }else{
		    	return json_encode(array('resultcode'=>$s->token_ne(),'info'=>'error',),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
		    }
		}else{
			return json_encode(array('resultcode'=>$s->token_id(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);

		}
	}

	//收金豆消息发送 
	//@param 用户id token
	//对方账户或者手机号 targrt
	//需要收款的金额  amount
	public function imazamox_new(){
		$s = new S();
		if(!empty($_POST) && $_POST['id'] != null && $_POST['token'] != null){
			if(self::token($_POST['id'],$_POST['token'])==='success'){
		            $tradeNo = $this->makeSn();
					$users = \think\Db::name('users')
					->where('userId',(int)$_POST['id'])
					->field('userId,loginName,userPhone,userType,userPhoto')
					->find();

					// dump($users);die;
					if($_POST['target'] != $users['loginName'] && $_POST['target'] != $users['userPhone']) {
		            if (is_numeric($_POST['amount']) && $_POST['amount'] >= 0.01) {
		                //获取转账用户的信息
		                $user = \think\Db::name('users')
		                ->where('loginName|userPhone',$_POST['target'])
		                ->field('userId,loginName,userPhone,userType,userPhoto')
		                ->find();

		                // dump($user);die;
		                if($user) {
		                    // 启动事务
		                    \think\Db::startTrans();
		                    try{
		                        $data['msgType']       =    2;
		                        $data['receiveUserId'] =    $user['userId'];
		                        $data['sendUserId']    =    $users['userId'];
		                        $data['msgTitle']      =    '金豆收款';
		                        $data['msgContent']    =    '用户' . $users['loginName'] . '向你收款' . $_POST['amount'] . '金豆，说明：' . htmlspecialchars($_POST['desc']);
		                        $data['amount']        =    $_POST['amount'];
		                        $data['tradeNo']       =    $tradeNo;
		                        $data['dataFlag']      =    0;// 0未完成 1成功 2过期
		                        $data['msgDesc']       =    htmlspecialchars($_POST['desc']);
		                        $data['createTime']    =    date('Y-m-d H:i:s');
		                        $news = \think\Db::name('messages')->insert($data);
		                        // dump($news);die;
		                        // 提交事务
		                        \think\Db::commit();
		                    } catch (\Exception $e) {
		                        // 回滚事务
		                        \think\Db::rollback();
		                    }
		                    if ($news) {
		                        // 启动事务
		                        \think\Db::startTrans();
		                        try{
		                        //将信息添加到资金流水表
		                        $data1 = array(
		                        	'targetType'   =>   $user['userType'], 
		                        	'targetId'     =>   (int)$user['userId'], 
		                        	'dataId'       =>   12, 
		                        	'dataSrc'      =>   '转账', 
		                        	'moneyType'    =>   0, 
		                        	'money'        =>   $_POST['amount'], 
		                        	'payName'      =>   $users['loginName'],
				                    'Headportrait' =>   isset($users['userPhoto'])?$users['userPhoto']:'',
		                        	'tradeNo'      =>   $tradeNo, 
		                        	'payType'      =>   0, 
		                        	'dataFlag'     =>   2, 
		                        	'createTime'   =>   date('Y-m-d H:i:s'), 
		                        	'remark'       =>   htmlspecialchars($_POST['desc']),
		                        	'endTime'      =>   date('Y-m-d H:i:s')
		                        	);
		                        \think\Db::name('log_moneys')->insert($data1);
		                        //将信息添加到资金流水表
		                        $in = array(
		                        	'targetType'   =>   $users['userType'], 
		                        	'targetId'     =>   $users['userId'], 
		                        	'dataId'       =>   13, 
		                        	'dataSrc'      =>   '转账', 
		                        	'moneyType'    =>   1, 
		                        	'money'        =>   $_POST['amount'], 
		                        	'payName'      =>   $user['loginName'],
		                        	'Headportrait' =>   isset($users['userPhoto'])?$users['userPhoto']:'',
		                        	'tradeNo'      =>   $tradeNo, 
		                        	'payType'      =>   0, 
		                        	'dataFlag'     =>   2, 
		                        	'createTime'   =>   date('Y-m-d H:i:s'), 
		                        	'remark'       =>   htmlspecialchars($_POST['desc']),
		                        	'endTime'      =>   date('Y-m-d H:i:s'));
		                        \think\Db::name('log_moneys')->insert($in);
		                        // 提交事务
		                        \think\Db::commit();
		                    } catch (\Exception $e) {
		                        // 回滚事务
		                        \think\Db::rollback();
		                    }
								return json_encode(array('resultcode'=>$s->new_ok(),'info'=>'success','order'=>$tradeNo),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);

		                    } else {
		                    	
		                        return json_encode(array('resultcode'=>$s->new_fail(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
		                    }
		                } else {
		                	//用户不存在
		                    return json_encode(array('resultcode'=>$s->name_no(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
		                }
		            } else {

		                //金额错误或者金额必须大于0.01
		                return json_encode(array('resultcode'=>$s->format_error(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
		            }
		        } else {

		            return json_encode(array('resultcode'=>$s->new_own(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
		        }
			}else{
				return json_encode(array('resultcode'=>$s->token_ne(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
			}
		}else{
			return json_encode(array('resultcode'=>$s->token_id(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);

		}
	}

	//钱袋交易记录
	// public function bills(){
	// 	$s = new S();
	// 	// dump($_POST);
	// 	if(!empty($_POST) && $_POST['id'] != null && $_POST['token'] !=null){
	// 		if (self::token($_POST['id'],$_POST['token'])==='success') {
	// 			$user = \think\Db::name('users')->where('userId',(int)$_POST['id'])->where('')->find();


	// 		}else{
	// 			return json_encode(array('resultcode'=>$s->token_ne(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
	// 		}
	// 	}else{
	// 		return json_encode(array('resultcode'=>$s->token_id(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);

	// 	}


	// }
	 
	 
	
	//金豆充值 钱袋子充金豆
	//@param 用户id 和token
	//充值的金额 number
	public function imazamox_Recharge(){
		$s = new S();
		// dump($_POST);
		if(!empty($_POST) && $_POST['id'] != null && $_POST['token'] !=null){
			if (self::token($_POST['id'],$_POST['token'])==='success') {
				$makeSn = $this->makeSn();
				$user = \think\Db::name('users')
				->field('userMoney,imazamox_number,userId,userType,loginName,userPhoto')
				->where('userId',(int)$_POST['id'])
				->find();

				if(is_numeric($_POST['number'])&&$user){
					// dump($user['userMoney']);die;
					if($user['userMoney'] >= $_POST['number'] && $_POST['number'] >= '0.01'){

						$users = \think\Db::name('users')->where('userId',(int)$_POST['id'])
						->update(['userMoney'=>$user['userMoney']-number_format($_POST['number'],2),
								  'imazamox_number'=>$user['imazamox_number']+number_format($_POST['number'],2)]);	
						if($users){

							// 启动事务
						    \think\Db::startTrans();
						    try{	

							$data1 = array(
									//金豆的流水
			                   		'targetType'   =>   $user['userType'],
			                    	'targetId'     =>   $user['userId'], 
			                    	'dataId'       =>   10,
			                    	'dataSrc'      =>   '金豆充值',
			                    	'moneyType'    =>   1,
			                    	'money'        =>   $_POST['number'],
			                    	'payName'      =>   $user['loginName'],
			                    	'tradeNo'      =>   $makeSn,
			                    	'payType'      =>   0,
			                    	'dataFlag'     =>   1,
			                    	'Headportrait' =>   $user['userPhoto'],
			                    	'createTime'   =>   date('Y-m-d H:i:s'),
			                    	// 'remark'	 =>	  ''
			                    	);
							$data2 = array(
							// 		//钱袋的流水
			                   		'targetType'   =>   $user['userType'],
			                    	'targetId'     =>   $user['userId'], 
			                    	'dataId'       =>   15,
			                    	'dataSrc'      =>   '金豆充值',
			                    	'moneyType'    =>   0,
			                    	'money'        =>   $_POST['number'],
			                    	'payName'      =>   $user['loginName'],
			                    	'tradeNo'      =>   $makeSn,
			                    	'payType'      =>   0,
			                    	'dataFlag'     =>   1,
			                    	'Headportrait' =>   $user['userPhoto'],
			                    	'createTime'   =>   date('Y-m-d H:i:s'),
			                    	// 'remark'	 =>	  ''
			                    	);
			                		\think\Db::name('log_moneys')->insert($data2);
			                		\think\Db::name('log_moneys')->insert($data1);

			                		// $im = \think\Db::name('imazamox_flow_log')->field('imazamox_number')->where('user_id',(int)$_POST['id'])->find();

			                		//将数据添加到金豆变更表
									$insert['user_id']         =  $user['userId'];
									$insert['imazamox_sn']     =  $makeSn;
									$insert['imazamox_type']   =  '充值';
									$insert['imazamox_number'] =  $_POST['number'];
									$insert['imazamox_change'] =  $_POST['number'];
									$insert['is_recharge']     =  1;
									$insert['imazamox_desc']   =  '钱袋子';
									$insert['add_time']        =  date('Y-m-d H:i:s');
									$in = \think\Db::name('imazamox_flow_log')->insert($insert);

									// 将信息添加到充值表
									$arr['imr_sn']             =   $insert['imazamox_sn'];
									$arr['imr_user_id']        =   $user['userId'];
									$arr['imr_amount']         =   $_POST['number'];
									$arr['imr_payment_code']   =   'purse';
									$arr['imr_payment_name']   =   '钱袋子';
									$arr['imr_add_time']       =   date('Y-m-d H:i:s');
									\think\Db::name('im_recharge')->insert($arr);

									// 提交事务
				                    \think\Db::commit();
				                } catch (\Exception $e) {
									return json_encode(array('resultcode'=>$s->imazamox_czpurse_no(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
				                    // 回滚事务
				                    \think\Db::rollback();
				                }	
							return json_encode(array('resultcode'=>$s->imazamox_czpurse_ok(),'info'=>'success','order'=>$makeSn),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
						}else{
							//失败信息添加
							$data1 = array(
			                   		'targetType' =>   '',
			                    	'targetId'   =>   '', 
			                    	'dataId'     =>   10,
			                    	'dataSrc'    =>   '金豆充值',
			                    	'moneyType'  =>   0,
			                    	'money'      =>   $_POST['number'],
			                    	'payName'    =>   $user['loginName'],
			                    	'tradeNo'    =>   $makeSn,
			                    	'payType'    =>   0,
			                    	'dataFlag'   =>   0,
			                    	'createTime' =>   date('Y-m-d H:i:s'),
			                    	'remark'     =>   '钱袋子');
			                		\think\Db::name('log_moneys')->insert($data1);
							return json_encode(array('resultcode'=>$s->imazamox_czpurse_no(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
						}
					}else{
						//失败信息添加
							$data1 = array(
			                   		'targetType' =>   $user['userType'],
			                    	'targetId'   =>   $user['userId'], 
			                    	'dataId'     =>   10,
			                    	'dataSrc'    =>   '金豆充值',
			                    	'moneyType'  =>   0,
			                    	'money'      =>   $_POST['number'],
			                    	'payName'    =>   $user['loginName'],
			                    	'tradeNo'    =>   $makeSn,
			                    	'payType'    =>   0,
			                    	'dataFlag'   =>   0,
			                    	'createTime' =>   date('Y-m-d H:i:s'),
			                    	'remark'     =>   '钱袋子');
			                		\think\Db::name('log_moneys')->insert($data1);
						return json_encode(array('resultcode'=>$s->transfer_purse_monery(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT); 
					}

				}else{
					return json_encode(array('resultcode'=>$s->imazamox_qd_no(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT); 
				}

			}else{
				return json_encode(array('resultcode'=>$s->token_ne(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
			}
		}else{
			return json_encode(array('resultcode'=>$s->token_id(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);

		}
	}




	 //金豆提现 提现到钱袋
	 //用户的id和token
	 //@param 需要提现到钱袋的金额 amount
    public function Cash_in_cash(){
    	// dump($_POST);die;
	    $s = new S();
    	if(!empty($_POST) && $_POST['token'] != null && $_POST['id'] != null){
	    	$make = $this->makeSn();
	        //获取当前用户的信息
	        if(self::token($_POST['id'],$_POST['token'])==='success'){

	        	$user=\think\Db::name('users')
	        	->where('userId',(int)$_POST['id'])
	        	->field('userId,userMoney,imazamox_number,userType,loginName')
	        	->find();

	        	//参数判断
	        	if(is_numeric($_POST['amount'])){
	        		if($user['imazamox_number']>=$_POST['amount']){
		        		//修改钱袋和金豆的钱
			        	$m = \think\Db::name('users')->where('userId',(int)$_POST['id'])->update([
			        		'userMoney'=>$user['userMoney']+$_POST['amount'],
			        		'imazamox_number'=>$user['imazamox_number']-$_POST['amount']
			        		]);
			        	if($m){
			        	// 启动事务
					    \think\Db::startTrans();
					    try{
			        		//流水表记录
			        	$data1 = array(//金豆记录
			                   		'targetType' =>   $user['userType'],
			                    	'targetId'   =>   $user['userId'], 
			                    	'dataId'     =>   11,
			                    	'dataSrc'    =>   '金豆提钱袋',
			                    	'moneyType'  =>   0,
			                    	'money'      =>   $_POST['amount'],
			                    	'payName'    =>   $user['loginName'],
			                    	'tradeNo'    =>   $make,
			                    	'payType'    =>   0,
			                    	'dataFlag'   =>   1,
			                    	'createTime' =>   date('Y-m-d H:i:s'),
			                    	// 'remark'     =>   ''
			                    	);
			                		$j=\think\Db::name('log_moneys')->insert($data1);

			            $data2 = array(//钱袋记录
			                   		'targetType' =>   $user['userType'],
			                    	'targetId'   =>   $user['userId'], 
			                    	'dataId'     =>   16,
			                    	'dataSrc'    =>   '金豆提钱袋',
			                    	'moneyType'  =>   1,
			                    	'money'      =>   $_POST['amount'],
			                    	'payName'    =>   $user['loginName'],
			                    	'tradeNo'    =>   $make,
			                    	'payType'    =>   0,
			                    	'dataFlag'   =>   1,
			                    	'createTime' =>   date('Y-m-d H:i:s'),
			                    	// 'remark'     =>   ''
			                    	);
			                		$d=\think\Db::name('log_moneys')->insert($data2);
			            $da = array(//金豆转钱袋
				                     'targetType'  =>   $user['userType'], 
				                    'targetId'     =>    $user['userId'], 
				                    'moneyType'    =>    1, 
				                    'money'        =>    $_POST['amount'], 
				                    'payName'      =>    $user['loginName'],
				                    'tradeNo'      =>    $make, 
				                    'payType'      =>    0, 
				                    'imazamox'     =>    2,
				                    'dataFlag'     =>    1, 
				                    'createTime'   =>    date('Y-m-d H:i:s'), 
				                    'remark'       =>    '金豆转钱袋'
				                    );
			                        $c=\think\Db::name('transfer')->insert($da);
						            //金豆变更表
					          		$insert['user_id']         =   $user['userId'];
				                    $insert['imazamox_sn']     =   $make;
				                    $insert['imazamox_type']   =   '转钱袋';
				                    $insert['imazamox_number'] =   $_POST['amount'];
				                    $insert['imazamox_change'] =   $_POST['amount'];
				                    $insert['payName']         =   $user['loginName'];
				                    $insert['is_recharge']     =   1;
				                    $insert['imazamox_desc']   =   '金豆转钱袋';
				                    $insert['add_time']        =   date('Y-m-d H:i:s');
				                    $z=$in = \think\Db::name('imazamox_flow_log')->insert($insert);

							// 提交事务
			                    \think\Db::commit();
			                } catch (\Exception $e) {
								return json_encode(array('resultcode'=>$s->Cash_no(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
			                    // 回滚事务
			                    \think\Db::rollback();
			                }            
			        	}
			        	
			        	if($m&&$j&&$d&&$c&&$z){
			        		// echo 1;
	    					return json_encode(array('resultcode'=>$s->Cash_ok(),'info'=>'success','order'=>$make),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);	
			        	}else{
			        				//流水表记录//失败记录
			        				$data1 = array(
			                   		'targetType' =>   $user['userType'],
			                    	'targetId'   =>   $user['userId'], 
			                    	'dataId'     =>   11,
			                    	'dataSrc'    =>   '金豆提钱袋',
			                    	'moneyType'  =>   0,
			                    	'money'      =>   $_POST['amount'],
			                    	'payName'    =>   $user['loginName'],
			                    	'tradeNo'    =>   $make,
			                    	'payType'    =>   0,
			                    	'dataFlag'   =>   0,
			                    	'createTime' =>   date('Y-m-d H:i:s'),
			                    	);
			                		\think\Db::name('log_moneys')->insert($data1);
			                		// echo 2;
	    					return json_encode(array('resultcode'=>$s->Cash_no(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);	
			        	}

		        	}else{
		        					//流水表记录//失败记录
			        				$data1 = array(
			                   		'targetType' =>   $user['userType'],
			                    	'targetId'   =>   $user['userId'], 
			                    	'dataId'     =>   11,
			                    	'dataSrc'    =>   '金豆提钱袋',
			                    	'moneyType'  =>   0,
			                    	'money'      =>   $_POST['amount'],
			                    	'payName'    =>   $user['loginName'],
			                    	'tradeNo'    =>   $make,
			                    	'payType'    =>   0,
			                    	'dataFlag'   =>   0,
			                    	'createTime' =>   date('Y-m-d H:i:s'),
			                    	);
			                		\think\Db::name('log_moneys')->insert($data1);
			                		// echo 3;
	    				return json_encode(array('resultcode'=>$s->transfer_purse_monery(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);	
		        	}
	        	}else{
	        						//流水表记录//失败记录
			        				$data1 = array(
			                   		'targetType' =>   $user['userType'],
			                    	'targetId'   =>   $user['userId'], 
			                    	'dataId'     =>   11,
			                    	'dataSrc'    =>   '金豆提钱袋',
			                    	'moneyType'  =>   0,
			                    	'money'      =>   $_POST['amount'],
			                    	'payName'    =>   $user['loginName'],
			                    	'tradeNo'    =>   $make,
			                    	'payType'    =>   0,
			                    	'dataFlag'   =>   0,
			                    	'createTime' =>   date('Y-m-d H:i:s'),
			                    	);
			                		\think\Db::name('log_moneys')->insert($data1);
			                		// echo 4;
	        		return json_encode(array('resultcode'=>$s->format_error(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);        	
	        	}
	    	}else{
	    		// echo 5;
	    		return json_encode(array('resultcode'=>$s->token_ne(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
	   	 	}
	   	}else{
	   		// echo 6;
			return json_encode(array('resultcode'=>$s->token_id(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
	   	}
	        
    }




   //还款后的详情
   public function repayment_details(){
   		if(!empty($_POST) && $_POST['id'] != null && $_POST['token'] != null){
	   		$s = new S();
	   		if(self::token($_POST['id'],$_POST['token'])==='success'){
	   			// echo $_POST['id'];die;
	   			$al=\think\Db::name('log_moneys')
	   			->field('createTime,tradeNo,money,payType,dataFlag')
	   			->where('targetId',(int)$_POST['id'])
	   			->where('dataId',9)
	   			->find();
	   			// dump($al);die;
	   			if($al){
					return json_encode(array('data'=>$al,'info'=>'success'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
	   			}else{
					return json_encode(array('info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
	   			}
	   		}else{
		        return json_encode(array('resultcode'=>$s->Withdrawals_error(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);    
	   		}
	   	}else{
			return json_encode(array('resultcode'=>$s->token_id(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
	   	}
   }



	//金豆钱袋数量
	public function purse_imazamox(){
		if(!empty($_POST) && $_POST['token'] != null && $_POST['id'] != null){
			$s = new S();
			if(self::token($_POST['id'],$_POST['token'])==='success'){
				$user = \think\Db::name('users')->field('userMoney,imazamox_number')->where('userId',(int)$_POST['id'])->find();
				if($user){
					return json_encode(array('data'=>$user,'info'=>'success'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT); 
				}else{
					return json_encode(array('info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT); 
				}
			}else{
		    	return json_encode(array('resultcode'=>$s->token_ne(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
			}
		}else{
			return json_encode(array('resultcode'=>$s->token_id(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);

		}

	}


		//流水号生成
    public function makeSn()
	{
		return date('YmdHis')
		. sprintf('%03d', (float)microtime() * 1000)
		. session('WST_USER.userId');

	}

	//支付方式
	public function payments(){
		if(!empty($_POST) && $_POST['id'] != null && $_POST['token'] != null){
			$s = new S();
			if(self::token($_POST['id'],$_POST['token'])==='success'){
			$pay = \think\Db::name('payments')->field('payName,payDesc,payOrder')->where('payOrder','in','4,5')->where('enabled',1)->select();
			$user = \think\Db::name('users')->field('userMoney,imazamox_number')->where('userId',(int)$_POST['id'])->find();
				if($pay && $user){
					return json_encode(array('data'=>$pay,'info'=>'success','money'=>$user),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
				}else{
					return json_encode(array('info'=>'error','money'=>''),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
				}		
			}else{
		    	return json_encode(array('resultcode'=>$s->token_ne(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
			}
		}else{
			return json_encode(array('resultcode'=>$s->token_id(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);

		}
	}


	//好友转账验证
	public function firendVerification(){
		$s= new S();
		if(!empty($_POST) && $_POST['id'] != null && $_POST['token'] != null){
			if(self::token($_POST['id'],$_POST['token'])==='success'){
				$user = \think\Db::name('users')->where('loginName|userPhone',$_POST['name'])->field('loginName,userName,userPhoto,userPhone,userId')->find();
				if($user){
					$friend = \think\Db::name('user_friends')->where(['userId'=>$_POST['id'],'friendID'=>$user['userId']])->field('nickName')->find();
					if($friend){
						$friend['loginName']	=	$user['loginName'];
						$friend['userName']		=	$user['userName'];
						$friend['userPhone']	= 	$user['userPhone'];
						$friend['userPhoto']	=	$user['userPhoto'];
						$friend['is_friend']	= 	0;
						return json_encode(array('data'=>$friend,'info'=>'success'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);//好友
					}else{
						$user['is_friend']		=	1;
						return json_encode(array('data'=>$user,'info'=>'success'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);//不是好友
					}
					
				}else{
					return json_encode(array('info'=>'error','resultcode'=>$s->name_no()),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);//用户名不存在
				}
			}else{
		    	return json_encode(array('resultcode'=>$s->token_ne(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
			}
		}else{
			return json_encode(array('resultcode'=>$s->token_id(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
		}
		
		
	} 




	//好友列表
	public function friendsList(){
		$s= new S();
		if(!empty($_POST) && $_POST['id'] != null && $_POST['token'] != null){
			if(self::token($_POST['id'],$_POST['token'])==='success'){
				$friends =\think\Db::table('jingo_user_friends')
				->alias('a')->join('jingo_users b','a.friendId = b.userId')
				->where(['a.userId'=>(int)$_POST['id'],'a.isAgreed'=>1,'a.isBlackList'=>0,'a.isDelete'=>0])
				->order('b.imazamox_number desc')
				->select();
				return json_encode(array('data'=>$friends,'info'=>'success'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
			}else{
		    	return json_encode(array('resultcode'=>$s->token_ne(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
			}
		}else{
			return json_encode(array('resultcode'=>$s->token_id(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
		}
	}


	//二维码向商家付款
	// public function code_accounts(){
	// 	// dump($_POST);die;
	// 	$s = new S();
	// 	//name money 
	// 	if(!empty($_POST) && $_POST['id'] != null && $_POST['token'] != null){
	// 		if(self::token($_POST['id'],$_POST['token'])==='success'){
	// 			//收钱的
	// 			$user=\think\Db::name('users')->where('userId',(int)$_POST['id'])->field('userMoney,userType,loginName,userId')->find();
	// 			//付钱的
	// 			$users=\think\Db::name('users')->where('loginName',$_POST['name'])->field('userMoney,userType,userId,loginName')->find();

	// 			if($user['userMoney'] >= $_POST['amount'] && $_POST['amount'] >='0.1'){
	// 				$tradeNo = $this->makeSn();

	// 				  // 启动事务
 //                   	 	\think\Db::startTrans();
 //                    	try{
	// 				//修改用户余额//收钱的
	// 				$me=\think\Db::name('users')->where('userId',(int)$_POST['id'])->update(['userMoney'=>$user['userMoney']+$_POST['amount']]);
	// 				//付钱的
	// 				$u=\think\Db::name('users')->where('loginName',$_POST['name'])->update(['userMoney'=>$users['userMoney']-$_POST['amount']]);

	// 				// 提交事务
	//                         \think\Db::commit();
	//                     } catch (\Exception $e) {
	//                         // 回滚事务
	//                         \think\Db::rollback();
	//                         return '失败';
	//                     }


	// 				if($me==1 && $u==1){
	// 					  // 启动事务
 //                   	 	\think\Db::startTrans();
 //                    	try{

	// 					// 收款人
	// 					$data = array(
	//                     	'targetType'   =>   $user['userType'], 
	//                     	'targetId'     =>   $_POST['id'], 
	//                     	'dataId'       =>   5, 
	//                     	'dataSrc'      =>   '转账', 
	//                     	'moneyType'    =>   1, 
	//                     	'money'        =>   $_POST['amount'], 
	//                     	'payName'      =>   $users['loginName'],
	//                     	'tradeNo'      =>   $tradeNo, 
	//                     	'Headportrait' =>   isset($users['userPhoto'])?$users['userPhoto']:'',
	//                     	'payType'      =>   0, 
	//                     	'dataFlag'     =>   1,
	//                     	'QRcode'	  =>	1, 
	//                     	'createTime'   =>   date('Y-m-d H:i:s'), 
	//                     	'endTime'      =>   date('Y-m-d H:i:s'));
	// 					//付款人
	//                     $data1 = array(
	//                     	'targetType'   =>   $users['userType'], 
	//                     	'targetId'     =>   $users['userId'], 
	//                     	'dataId'       =>   4, 
	//                     	'dataSrc'      =>   '转账', 
	//                     	'moneyType'    =>   0, 
	//                     	'money'        =>   $_POST['amount'], 
	//                     	'payName'      =>   $user['loginName'],
	//                     	'tradeNo'      =>   $tradeNo, 
	//                     	'Headportrait' =>   isset($user['userPhoto'])?$user['userPhoto']:'',
	//                     	'payType'      =>   0, 
	//                     	'dataFlag'     =>   1, 
	//                     	'QRcode'	  =>	1,
	//                     	'createTime'   =>   date('Y-m-d H:i:s'), 
	//                     	'endTime'      =>   date('Y-m-d H:i:s'),
 //                        	);
 //                       \think\Db::name('log_moneys')->insert($data);
 //                       \think\Db::name('log_moneys')->insert($data1);

	// 					//收款人
	// 					 $da = array(
 //                        	'targetType'  =>    $user['userType'], 
 //                        	'targetId'    =>    $user['userId'],  
 //                        	'moneyType'   =>    1, 
 //                        	'money'       =>    $_POST['amount'], 
 //                        	'payName'     =>    $users['loginName'],
 //                        	'tradeNo'     =>    $tradeNo, 
 //                        	'payType'     =>    0, 
 //                        	'dataFlag'    =>    1, 
 //                        	'QRcode'	  =>	1,
 //                        	'createTime'  =>    date('Y-m-d H:i:s'), 
 //                        	);
 //                    	// 付款人
 //                        $da1 = array(
 //                        	'targetType'  =>    $users['userType'], 
 //                        	'targetId'    =>    $users['userId'], 
 //                        	'moneyType'   =>    0, 
 //                        	'money'       =>    $_POST['amount'], 
 //                        	'payName'     =>    $user['loginName'],
 //                        	'tradeNo'     =>    $tradeNo, 
 //                        	'payType'     =>    0, 
 //                        	'dataFlag'    =>    1, 
 //                        	'QRcode'	  =>	1,
 //                        	'createTime'  =>    date('Y-m-d H:i:s'), 
 //                        	);
	//                     \think\Db::name('transfer')->insert($da);
	//                     \think\Db::name('transfer')->insert($da1);

	//                     // 提交事务
	//                         \think\Db::commit();
	//                     } catch (\Exception $e) {
	//                         // 回滚事务
	//                         \think\Db::rollback();
	//                         return json_encode(array('resultcode'=>$s->receivables_no(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
	//                     }
	// 					return json_encode(array('resultcode'=>$s->receivables_ok(),'info'=>'success'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
	// 				}else{
	// 					return json_encode(array('resultcode'=>$s->receivables_no(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
	// 				}
	// 			}else{
	// 				//余额不足	
	// 				return json_encode(array('resultcode'=>$s->receivables_mq(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
	// 			}
	// 		}else{
	// 	    	return json_encode(array('resultcode'=>$s->token_ne(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
	// 		}
	// 	}else{
	// 		return json_encode(array('resultcode'=>$s->token_id(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
	// 	}	
	// }



	//二维码转账
	public function code_purse(){
		// dump($_POST);die;
    	$s = new S();
    	if(!empty($_POST) && $_POST['id'] != null && $_POST['token'] != null){
	    	if(self::token($_POST['id'],$_POST['token'])==='success'){
	    		$tradeNo = $this->makeSn();
	    			//信息过滤
		            //获取当前用户的信息 少钱的人
		            $user = \think\Db::name('users')
		            ->where(array('userId' => (int)$_POST['id']))
		            ->field('loginName,userMoney,userId,userType,userPhoto')
		            ->find();
		            //获取收款人的信息 多钱的人
		            $users = \think\Db::name('users')
		            ->where('loginName|userPhone',$_POST['member_name'])
		            ->field('loginName,userMoney,userId,userType,userPhoto')
		            ->find();

		            if($users){
			            if($users['loginName'] != $user['loginName']) {
			                if (is_numeric($_POST['amount']) && $_POST['amount'] >='0.1') {
			                    if ($user['userMoney'] >= $_POST['amount']) {
	           						// dump($users);die;
	                                // 启动事务
	                                \think\Db::startTrans();
	                                try{
	                          
	                                	//修改用户的余额
	                                    $me = \think\Db::name('users')
	                                    ->where('userId',$_POST['id'])
	                                    ->update(['userMoney' => $user['userMoney'] - $_POST['amount']]);
	                                    //修改收款人的余额
	                                    $mem = \think\Db::name('users')
	                                    ->where('loginName',$users['loginName'])
	                                    ->update(['userMoney' => $users['userMoney'] + $_POST['amount']]);
	                                // 提交事务
	                                    \think\Db::commit();
	                                } catch (\Exception $e) {
	                                //     // 回滚事务
	                                    \think\Db::rollback();
	                                    return json_encode(array('resultcode'=>$s->code_purse_no(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
	                                    // echo 0;
	                                }

	                                    // dump($me);die;
	                                      // 启动事务
	                                \think\Db::startTrans();
	                                try{
	                                        $data = array(
	                                        	'targetType'   =>   $user['userType'], 
	                                        	'targetId'     =>   $_POST['id'], 
	                                        	'dataId'       =>   4, 
	                                        	'dataSrc'      =>   '转账', 
	                                        	'moneyType'    =>   0, 
	                                        	'money'        =>   $_POST['amount'], 
	                                        	'payName'      =>   $users['loginName'],
	                                        	'tradeNo'      =>   $tradeNo, 
	                                        	'Headportrait' =>   isset($users['userPhoto'])?$users['userPhoto']:'',
	                                        	'payType'      =>   0, 
	                                        	'dataFlag'     =>   1, 
	                                        	'QRcode'	   =>	1,
	                                        	'createTime'   =>   date('Y-m-d H:i:s'), 
	                                        	'remark'       =>   htmlspecialchars($_POST['desc']),
	                                        	'endTime'      =>   date('Y-m-d H:i:s'));
	                                        // dump($data);die;
	                                        $data1 = array(
	                                        	'targetType'   =>   $users['userType'], 
	                                        	'targetId'     =>   $users['userId'], 
	                                        	'dataId'       =>   5, 
	                                        	'dataSrc'      =>   '转账', 
	                                        	'moneyType'    =>   1, 
	                                        	'money'        =>   $_POST['amount'], 
	                                        	'payName'      =>   $user['loginName'],
	                                        	'tradeNo'      =>   $tradeNo, 
	                                        	'Headportrait' =>   isset($user['userPhoto'])?$user['userPhoto']:'',
	                                        	'payType'      =>   0, 
	                                        	'dataFlag'     =>   1, 
	                                        	'QRcode'	   =>	1,
	                                        	'createTime'   =>   date('Y-m-d H:i:s'), 
	                                        	'remark'       =>   htmlspecialchars($_POST['desc']),
	                                        	'endTime'      =>   date('Y-m-d H:i:s'),
	                                        	);
	                                       $u=\think\Db::name('log_moneys')->insert($data);
	                                       $s=\think\Db::name('log_moneys')->insert($data1);
	                                       // dump($u);
	                                    // }
	                                    // dump($user['userType']);die;
	                                    //将信息添加到转账表里面
		                                    $da = array(
		                                    	'targetType'  =>    $user['userType'], 
		                                    	'targetId'    =>    $user['userId'],  
		                                    	'moneyType'   =>    0, 
		                                    	'money'       =>    $_POST['amount'], 
		                                    	'payName'     =>    $users['loginName'],
		                                    	'tradeNo'     =>    $tradeNo, 
		                                    	'payType'     =>    0, 
		                                    	'dataFlag'    =>    1, 
		                                    	'createTime'  =>    date('Y-m-d H:i:s'), 
		                                    	'remark'      =>    htmlspecialchars($_POST['desc']));
	                                    // dump($da);die;
		                                    $da1 = array(
		                                    	'targetType'  =>    $users['userType'], 
		                                    	'targetId'    =>    $users['userId'], 
		                                    	'moneyType'   =>    1, 
		                                    	'money'       =>    $_POST['amount'], 
		                                    	'payName'     =>    $user['loginName'],
		                                    	'tradeNo'     =>    $tradeNo, 
		                                    	'payType'     =>    0, 
		                                    	'dataFlag'    =>    1, 
		                                    	'createTime'  =>    date('Y-m-d H:i:s'), 
		                                    	'remark'      =>    htmlspecialchars($_POST['desc']));
	                                    \think\Db::name('transfer')->insert($da);
	                                    \think\Db::name('transfer')->insert($da1);		                               
	                                    // 提交事务
	                                    \think\Db::commit();
	                                } catch (\Exception $e) {
	                                //     // 回滚事务
	                                    \think\Db::rollback();
	                                    return json_encode(array('resultcode'=>$s->code_purse_no(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
	                                    // echo 0;
	                                }

	                                    return json_encode(array('resultcode'=>40006,'info'=>'success','order'=>$tradeNo),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);

	                                // echo '成功';
			                    }  else {
			                        return json_encode(array('resultcode'=>$s->transfer_purse_monery(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
			                    }
			                } else {
			                    return json_encode(array('resultcode'=>$s->Withdrawals_je(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
			                }
			            } else {		            			
			                return json_encode(array('resultcode'=>$s->transfer_purse_own(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
			            }
		        	}else{
		           		return json_encode(array('resultcode'=>$s->name_no(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
		    	}
			}else{
				return json_encode(array('resultcode'=>$s->token_ne(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
			} 
		}else{
				return json_encode(array('resultcode'=>$s->token_id(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
		}	

	}





	//消息获取发送者消息获取
	public function messagesSendout(){
		//name money 
		// if(!empty($_POST) && $_POST['id'] != null && $_POST['token'] != null){
		// 	if(self::token($_POST['id'],$_POST['token'])==='success'){

			$mess=\think\Db::name('messages')->where('sendUserId',(int)$_POST['id'])->where('msgType',2)->field('sendUserId,receiveUserId,msgTitle,msgTitle,msgContent,amount,msgDesc,tradeNo,msgStatus,dataFlag,createTime')->order('id desc')->select();
			// echo '<pre>';
			return json_encode(array('data'=>$mess,'info'=>'success'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
			// echo '</pre>';
		// 	}else{
		//     	return json_encode(array('resultcode'=>$s->token_ne(),'resule'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
		// 	}
		// }else{
		// 	return json_encode(array('resultcode'=>$s->token_id(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
		// }	
	}

	//消息获取 接收者消息获取
	public function messagesReceive(){
		// if(!empty($_POST) && $_POST['id'] != null && $_POST['token'] != null){
		// 	if(self::token($_POST['id'],$_POST['token'])==='success'){
			$mess=\think\Db::name('messages')->where('receiveUserId',(int)$_POST['id'])->where('msgType',2)->field('sendUserId,receiveUserId,msgTitle,msgTitle,msgContent,amount,msgDesc,tradeNo,msgStatus,dataFlag,createTime,completeTime')->order('id desc')->select();
			// echo '<pre>';
			return json_encode(array('data'=>$mess,'info'=>'success'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
		// echo '</pre>';
		// 	}else{
		//     	return json_encode(array('resultcode'=>$s->token_ne(),'resule'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
		// 	}
		// }else{
		// 	return json_encode(array('resultcode'=>$s->token_id(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
		// }	
	}


	//消息转账验证
	public function messagesPurse(){
		if(!empty($_POST) && $_POST['id'] != null && $_POST['token'] != null){
			if(self::token($_POST['id'],$_POST['token'])==='success'){
			$s = new S();
			//接收流水号是否存在
			$new = 	\think\Db::name('messages')->where('tradeNo',$_POST['tradeNo'])
			->field('id,sendUserId,receiveUserId,msgTitle,msgTitle,msgContent,amount,msgDesc,tradeNo,msgStatus,dataFlag,createTime,completeTime')
			->find();

				if($new && $new['dataFlag']==0){
						// 启动事务
			            \think\Db::startTrans();
			            try{	
						//少钱的
						$users = \think\Db::name('users')->where('userId',(int)$new['receiveUserId'])->field('userType,loginName,userId,userPhoto,userId,userMoney')->find();
						//收钱的
						$user = \think\Db::name('users')->where('userId',(int)$new['sendUserId'])->field('userType,loginName,userId,userPhoto,userId,userMoney')->find();
						// 提交事务
				            \think\Db::commit();
				        } catch (\Exception $e) {
				            // 回滚事务
				            \think\Db::rollback();
				            return json_encode(array('resultcode'=>$s->receivables_no(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
				        }

						if($users['userMoney'] >= $new['amount']){
								// 启动事务
					            \think\Db::startTrans();
					            try{
							//付钱的
							$mi = \think\Db::name('users')->where('userId',(int)$users['userId'])->update(['userMoney'=>$users['userMoney']-$new['amount']]);
							// echo $mi;die;
							//收钱的
							$me = \think\Db::name('users')->where('userId',(int)$new['sendUserId'])->update(['userMoney'=>$user['userMoney']+$new['amount']]);
							// echo $me;die;
							//消息修改
							$messages = \think\Db::name('messages')->where('tradeNo',$_POST['tradeNo'])->update(['completeTime'=>date('Y-m-d H:i:s'),'msgStatus'=>1,'dataFlag'=>1]);
							 	// 提交事务
						            \think\Db::commit();
						        } catch (\Exception $e) {
						            // 回滚事务
						            \think\Db::rollback();
						            return json_encode(array('resultcode'=>$s->receivables_no(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
						        }
							// echo $messages['dataFlag'];die;
							if($mi ==1 && $me == 1){
								
									// 启动事务
						            \think\Db::startTrans();
						            try{
								//收钱的
								$log = \think\Db::name('log_moneys')->where(['tradeNo'=>$_POST['tradeNo'],'targetId'=>$user['userId']])->update(['dataFlag'=>1,'endTime'=>date('Y-m-d H:i:s')]);
								//付钱的
								$logs = \think\Db::name('log_moneys')->where(['tradeNo'=>$_POST['tradeNo'],'targetId'=>$users['userId']])->update(['dataFlag'=>1,'endTime'=>date('Y-m-d H:i:s')]);
								//收款人
								 $da = array(
				                	'targetType'  =>    $user['userType'], 
				                	'targetId'    =>    $user['userId'],  
				                	'moneyType'   =>    1, 
				                	'money'       =>    $new['amount'], 
				                	'payName'     =>    $users['loginName'],
				                	'tradeNo'     =>    $_POST['tradeNo'], 
				                	'payType'     =>    0, 
				                	'dataFlag'    =>    1, 
				                	'createTime'  =>    date('Y-m-d H:i:s'), 
				                	);
				            	// 付款人
				                $da1 = array(
				                	'targetType'  =>    $users['userType'], 
				                	'targetId'    =>    $users['userId'], 
				                	'moneyType'   =>    0, 
				                	'money'       =>    $new['amount'], 
				                	'payName'     =>    $user['loginName'],
				                	'tradeNo'     =>    $_POST['tradeNo'], 
				                	'payType'     =>    0, 
				                	'dataFlag'    =>    1, 
				                	'createTime'  =>    date('Y-m-d H:i:s'), 
				                	);
				                $q=\think\Db::name('transfer')->insert($da);
				                $u=\think\Db::name('transfer')->insert($da1);
					                // 提交事务
							            \think\Db::commit();
							        } catch (\Exception $e) {
							            // 回滚事务
							            \think\Db::rollback();
							            return json_encode(array('resultcode'=>$s->transfer_purse_fail(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
							        }    
				                if($log==1 && $log ==1){
				                	 // return '转账成功';
				                	return json_encode(array('resultcode'=>$s->transfer_purse_ok(),'info'=>'success','order'=>$_POST['tradeNo']),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
				                }else{
				               		// return '失败';
				               		return json_encode(array('resultcode'=>$s->transfer_purse_fail(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
				                } 

							}else{
								// return '失败';
								return json_encode(array('resultcode'=>$s->transfer_purse_fail(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
							}	
						}else{
							// 40002余额不足
							return json_encode(array('resultcode'=>$s->transfer_purse_monery(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
						}
				}else{
					// return '交易已经完成或者消息不存在';
					return json_encode(array('resultcode'=>$s->Receivables_purese_no(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
				}
			}else{
		    	return json_encode(array('resultcode'=>$s->token_ne(),'resule'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
			}
		}else{
			return json_encode(array('resultcode'=>$s->token_id(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
		}	
	}

	//查看收款状态
	public function messagesState(){
		// if(!empty($_POST) && $_POST['id'] != null && $_POST['token'] != null){
			// if(self::token($_POST['id'],$_POST['token'])==='success'){
		$state = \think\Db::name('messages')
		->field('dataFlag,createTime,sendUserId,receiveUserId,completeTime')
		->where('tradeNo',$_POST['tradeNo'])->find();
		//查看消息是否过期
		$date = time()-strtotime($state['createTime']);
		if($state['dataFlag']==1){
			return json_encode(array('data'=>$state['dataFlag'],'info'=>'success','time'=>$state['completeTime']),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
		}else{
			if($state && $date<=86400){
				return json_encode(array('data'=>$state['dataFlag'],'info'=>'success'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
			}elseif($state && $date>=86400){
					$messages = \think\Db::name('messages')->where('tradeNo',$_POST['tradeNo'])->update(['completeTime'=>date('Y-m-d H:i:s'),'msgStatus'=>1,'dataFlag'=>2]);
					//收钱的
					$log = \think\Db::name('log_moneys')->where(['tradeNo'=>$_POST['tradeNo'],'targetId'=>$state['sendUserId']])->update(['dataFlag'=>0,'endTime'=>date('Y-m-d H:i:s')]);
					//付钱的
					$logs = \think\Db::name('log_moneys')->where(['tradeNo'=>$_POST['tradeNo'],'targetId'=>$state['receiveUserId']])->update(['dataFlag'=>0,'endTime'=>date('Y-m-d H:i:s')]);
				return json_encode(array('data'=>$state['dataFlag'],'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
			}
		}
			
		// 	}else{
		//     	return json_encode(array('resultcode'=>$s->token_ne(),'resule'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
		// 	}
		// }else{
		// 	return json_encode(array('resultcode'=>$s->token_id(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
		// }	
	}



	//给别人充值金豆
	//@param id token name
	//@param dataId 17 给好友充值金豆
	//@param dataId 18 收到好友充值的金豆
	public function givefriendsRecharge(){
		$s = new S();
		if(!empty($_POST) && $_POST['id'] != null && $_POST['token'] != null){
			if(self::token($_POST['id'],$_POST['token'])==='success'){
		$amount = input('post.amount');
		$name   = input('post.name');
		$id     = input('post.id');

		//充值账号用户查询 name
 		$users = \think\Db::name('users')
 		->where('userId',$name)
 		->field('userId,imazamox_number,userType,userPhoto,loginName')
 		->find();
 		//查询本人信息
 		$user = \think\Db::name('users')
 		->where('userId',(int)$id)
 		->field('userId,userType,userMoney,loginName,userPhoto')
 		->find();
 		//判断是不是好友
 		// $fri = \think\Db::name('f')
 		// dump($user).'<br/>';
 		// dump($users);
 			if($users==null){
 				return json_encode(['resultcode'=>$s->givefriendsRecharge_no(),'info'=>'error']);
 			}else{
	 				//判断余额是否充足
			 		if($user['userMoney']>=input('post.amount')){
			 			// 启动事务
			            \think\Db::startTrans();
			            try{

			 			//修改本人 钱袋的钱
			 			$u = \think\Db::name('users')
			 		    ->where('userId',(int)$id)
			 			->update(['userMoney'=>$user['userMoney']-input('post.amount')]);
			 			//修改对方的金豆数量
			 			$us = \think\Db::name('users')
			 			->where('userId',input('post.name'))
			 			->update(['imazamox_number'=>$users['imazamox_number']+input('post.amount')]);

			 			  // 提交事务
				            \think\Db::commit();
				        } catch (\Exception $e) {
				            // 回滚事务
				            \think\Db::rollback();
				            return json_encode(array('resultcode'=>$s->givefriendsRecharge_no(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
				        } 

			 			if($u&&$us){
			 				$tradeNo = $this->makeSn();//流水号生成
			 				//uers 本人账户流水记录 
			 				// 启动事务
				            \think\Db::startTrans();
				            try{

			 				$data = array(
			                	'targetType'   =>   $user['userType'], 
			                	'targetId'     =>   $id, 
			                	'dataId'       =>   17, 
			                	'dataSrc'      =>   '金豆充值', 
			                	'moneyType'    =>   0, 
			                	'money'        =>   input('post.amount'), 
			                	'payName'      =>   $users['loginName'],
			                	'tradeNo'      =>   $tradeNo, 
			                	'Headportrait' =>   isset($users['userPhoto'])?$users['userPhoto']:'',
			                	'payType'      =>   0, 
			                	'dataFlag'     =>   1, 
			                	'createTime'   =>   date('Y-m-d H:i:s'), 
			                	// 'remark'       =>   htmlspecialchars($_POST['desc']),
			                	'endTime'      =>   date('Y-m-d H:i:s'));
			                // dump($data);die;
			                $data1 = array(//收到好友的金豆充值
			                	'targetType'   =>   $users['userType'], 
			                	'targetId'     =>   $users['userId'], 
			                	'dataId'       =>   18, 
			                	'dataSrc'      =>   '金豆充值', 
			                	'moneyType'    =>   1, 
			                	'money'        =>   $amount, 
			                	'payName'      =>   $user['loginName'],
			                	'tradeNo'      =>   $tradeNo, 
			                	'Headportrait' =>   isset($user['userPhoto'])?$user['userPhoto']:'',
			                	'payType'      =>   0, 
			                	'dataFlag'     =>   1, 
			                	'createTime'   =>   date('Y-m-d H:i:s'), 
			                	// 'remark'       =>   htmlspecialchars($_POST['desc']),
			                	'endTime'      =>   date('Y-m-d H:i:s'),
			                	);
			               \think\Db::name('log_moneys')->insert($data);
			               \think\Db::name('log_moneys')->insert($data1);

			               //钱带流水记录
			            	$arrs['targetType']  =    $user['userType']; 
			            	$arrs['targetId']    =    $user['userId'];  
			            	$arrs['moneyType']   =    1; 
			            	$arrs['money']       =    input('post.amount'); 
			            	$arrs['payName']     =    $users['loginName'];
			            	$arrs['tradeNo']     =    $tradeNo;
			            	$arrs['payType']     =    0; 
			            	$arrs['dataFlag']    =    1;
			            	$arrs['imazamox']	 =	  3; 	  
			            	$arrs['createTime']  =    date('Y-m-d H:i:s');
			            	\think\Db::name('transfer')->insert($arrs);
			               //金豆充值记录//将信息添加到充值表
							$arr['imr_sn'] = $tradeNo;
							$arr['imr_user_id'] = $users['userId'];
							$arr['imr_amount'] = $amount;
							$arr['imr_payment_code'] = 'purse';
							$arr['imr_payment_name'] = '钱袋子';
							$arr['imr_yourself']=$id;
							$arr['imr_payment_state']=1;
							$arr['imr_add_time'] = date('Y-m-d H:i:s');
							$arr['imr_payment_time']=date('Y-m-d H:i:s');
							\think\Db::name('im_recharge')->insert($arr);

								  // 提交事务
					            \think\Db::commit();
					        } catch (\Exception $e) {
					            // 回滚事务
					            \think\Db::rollback();

					            return json_encode(array('resultcode'=>$s->givefriendsRecharge_no(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
					        }  

			 				return json_encode(array('resultcode'=>$s->givefriendsRecharge_ok(),'info'=>'success','order'=>$tradeNo));
			 			}else{
			 				return json_encode(array('resultcode'=>$s->givefriendsRecharge_no(),'info'=>'error'));
			 				// echo '充值失败';
			 			}
			 		}else{
			 			return json_encode(array('resultcode'=>$s->transfer_purse_monery(),'info'=>'error'));
			 		}
		 		}
 			}else{
		    	return json_encode(array('resultcode'=>$s->token_ne(),'resule'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
			}
		}else{
			return json_encode(array('resultcode'=>$s->token_id(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
		}
	}

	//给好友充值流水常看详情
	public function friendsRechargedetails(){
		if(!empty($_POST) && $_POST['id'] != null && $_POST['token'] != null){
			if(self::token($_POST['id'],$_POST['token'])==='success'){
				$tradeNo = input('post.tradeNo');
				if($tradeNo!=null){
					$data = \think\Db::name('log_moneys')->where('tradeNo',$tradeNo)->find();
					if($data){
						return json_encode(array('data'=>$data,'info'=>'success'));
					}else{
						return json_encode(array('info'=>'error'));
					}
				}else{
					return json_encode(array('info'=>'error'));
				}
				
			}else{              
			    return json_encode(array('resultcode'=>$s->token_ne(),'resule'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
			}
		}else{
			return json_encode(array('resultcode'=>$s->token_id(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
		}
	}

	//金豆收款消息发送后对方付款
	public function imazamoxPayment(){
		$s = new S();
		if(!empty($_POST) && $_POST['id'] != null && $_POST['token'] != null){
			if(self::token($_POST['id'],$_POST['token'])==='success'){
		//获取流水号
				$da=\think\Db::name('messages')->where('tradeNo',$_POST['tradeNo'])->find();
				if($da){
					if($da['dataFlag']==0){
						$user=\think\Db::name('users')->where('userId',$da['sendUserId'])->field('imazamox_number')->find();//发送者的信息
						$users=\think\Db::name('users')->where('userId',input('post.id'))->field('imazamox_number')->find();//接收者的信息
						if($users['imazamox_number']>=$da['amount']){
							// 启动事务
				            \think\Db::startTrans();
				            try{
							//修改信息发送者信息
							$fa=\think\Db::name('users')->where('userId',$da['sendUserId'])->update(['imazamox_number'=>$user['imazamox_number']+$da['amount']]);
							//修改接收者信息
							$jie=\think\Db::name('users')->where('userId',input('post.id'))->update(['imazamox_number'=>$users['imazamox_number']-$da['amount']]);
							//修改流水记录
							$s=\think\Db::name('log_moneys')->where('tradeNo',input('post.tradeNo'))->update(['dataFlag'=>1]);
							$mess=\think\Db::name('messages')->where('tradeNo',input('post.tradeNo'))->update(['dataFlag'=>1]);
								// 提交事务
					            \think\Db::commit();
					        } catch (\Exception $e) {
					            // 回滚事务
					            \think\Db::rollback();

					            return json_encode(array('resultcode'=>$s->imazamox_error(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
					        }  
							if($fa&&$jie&&$s&&$mess){
								return json_encode(['resultcode'=>60000,'info'=>'success'],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
							}else{
								return json_encode(array('resultcode'=>$s->imazamox_error(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
							}
						}else{
							return json_encode(array('resultcode'=>$s->imazamox_null(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
						}
					}elseif($da['dataFlag']==1){
						// echo '消息已经完成';
						return json_encode(['resultcode'=>$s->imazamox_complete(),'info'=>'error'],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
					}elseif($da['dataFlag']==2){
						// echo '过期';
						return json_encode(array('resultcode'=>$s->imazamox_over(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
					}
				}else{
					// echo '消息不存在';
					return json_encode(['resultcode'=>$s->imazamox_non(),'info'=>'error'],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
				}
			}else{              
		    	return json_encode(array('resultcode'=>$s->token_ne(),'resule'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
			}
		}else{
			return json_encode(array('resultcode'=>$s->token_id(),'info'=>'error'),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
		}

	}

		// token 获取
		public function tokenhuoqu(){
			$token =	\think\Db::name('user_token')->where('member_id',$_POST['id'])->order('token_id desc')->find();
			dump($token);
		}

		public function tokenhuoqu1(){
			$token =	\think\Db::name('user_token')->where('token',$_POST['id'])->order('token_id desc')->find();
			dump($token);
		}



		// 删除贷款记录
		public function shanchujilu(){
			$user = \think\Db::name('log_moneys')->where('targetId',input('post.id'))->where('dataId','in','8,9')->delete();
			echo $user;
		}

		//查询接收读取token
		public function tokenss(){
			echo input('post.token').'<br/>';
			$token = \think\Db::name('user_token')->where('member_id',$_POST['id'])->order('token_id desc')->find();
			echo $token['token'];
		}

		public function wanglijun(){
			$user =\think\Db::name('users')->where('loginName|userPhone|userId',input('get.name'))->find();
			dump($user);
		}



		//加信用分
		public function xyfzj(){
			$user=\think\Db::name('users')->where('userId',$_GET['id'])->update(['userScore'=>$_GET['member']]);
			echo $user;
		}
	// floor(im/3000)+1

		public function quxiaojiao(){
			$user = \think\Db::name('users')->where('userId',$_GET['id'])->update(['loanTimes'=>0]);
			echo $user;
		}

		//清除所有人的 余额及金豆数
		// public function qingchu(){
		// 	$users=\think\Db::name('users')->field('userId')->select();
		// 	foreach ($users as $v) {
		// 		$user=\think\Db::name('users')->where('userId',$v['userId'])->update(['imazamox_number'=>0,'lockMoney'=>0,'userMoney'=>100]);
		// 		echo $user;
		// 	}
			
		// }


}

 