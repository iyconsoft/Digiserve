<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\MenuSession;
use App\Models\Service;
use App\Models\Option;
use App\Models\UserService;
use App\Models\Payment;
use DB;

class UssdController extends Controller
{
	
	public $newLine = "\n";
	
    public function index(Request $request)
	{
		//try
		{
			$msisdn = substr($request->msisdn,-13);
			$message = strtoupper($request->message);
			$shortcode = $request->shortcode;
			$gateway = $request->gateway;
			 
			$menuSession = MenuSession::Where('msisdn',$msisdn)->First();
			if(!$menuSession || $message == "*1220#" || $message == "1220" || $message == "*1220")
			{
				
				DB::delete("delete from menu_session where msisdn='".$msisdn."'");
				$menuSession = new MenuSession;
				
				$menuSession->msisdn = $msisdn;
				$menuSession->keyword = $message;
				$menuSession->level = 1;
				$menuSession->parent = '0';
				$menuSession->s_parent = '0';
				$menuSession->g_parent = '0';
				$menuSession->created_at = date('Y-m-d H:i:s');
				$menuSession->save();
							
				$sendMessage = "Welcome to Borrow Power  USSD Platform".$this->newLine."1. Buy Now".$this->newLine."2. My Purchases";
				
				$output['operation'] = "continue";		
				$output['message'] = $sendMessage;
				
				return $output;
			}
			else
			{
				switch($menuSession->level)
				{
					case '1':
						
						if($message == "1") //1. Buy Now
						{
							$info_Services = $this->getServices();
							$sendMessage = "Select ";
							$line = 1;
							foreach($info_Services as $info_Service)
							{
								$sendMessage = $sendMessage.$this->newLine. $line . ". ".$info_Service->name;
								$line++;
							}
							
							$output['operation'] = "continue";		
							$output['message'] = $sendMessage;
							
							$menuSession->keyword = $message;
							$menuSession->g_parent = $message;
							$menuSession->level = 2;
							$menuSession->save();
						}
						else //2. My Purchases
						{
							$info_Payments = $this->getPayments($msisdn);
							if($info_Payments->Count() === 0)
							{
								$output['operation'] = "end";		
								$output['message'] = "No payment found";
								$menuSession->delete();
							}
							else
							{
								$sendMessage = "Select ";
								$line = 1;
								foreach($info_Payments as $info_Payment)
								{
									$sendMessage = $sendMessage.$this->newLine. $line . ". ".$info_Payment->service;
									$line++;
								}
								$output['operation'] = "continue";		
								$output['message'] = $sendMessage;
								$menuSession->keyword = $message;
								$menuSession->g_parent = $message;
								$menuSession->level = 2;
								$menuSession->save();
							}
						}
					break;
					
					case '2':
						
						if($menuSession->g_parent == "1")//1. Buy Now
						{
							$info_SelectedService = $this->getSelectedService($message);
							$info_UserAccounts = $this->getUserAccounts($msisdn,$info_SelectedService->name);
							$is_new = '1';
							if($info_UserAccounts->Count()>0)
							{
								$is_new = '0';
							}
						 
							if($is_new == "1")
							{
								$sendMessage = "Enter Your Full Name:";
							}
							else
							{
								$sendMessage = "Select ";
								$line = 1;
								foreach($info_UserAccounts as $info_UserAccount)
								{
									$sendMessage = $sendMessage.$this->newLine. $line . ". ".$info_UserAccount->name;
									$line++;
								}
	 							$sendMessage = $sendMessage.$this->newLine. $line . ". New Account";
								$menuSession->create_new_account_id = $line;
							}
							
							$output['operation'] = "continue";		
							$output['message'] = $sendMessage;
							
							
							$menuSession->service_id = $info_SelectedService->id;
							$menuSession->service = $info_SelectedService->name;
							$menuSession->is_new = $is_new;
							$menuSession->keyword = $message;
							$menuSession->level = 3;
							$menuSession->save();
						}
						else //2. My Purchases
						{
							$info_SelectedPayment = $this->getSelectedPayments($msisdn,$message);
							
							$output['operation'] = "end";		
							$output['message'] = "Date: ".$info_SelectedPayment->created_at->format('d/M/Y')."".$this->newLine."".$info_SelectedPayment->service_option." Prepaid Meter Token: ".$info_SelectedPayment->meter_no."".$this->newLine."Payment Ref: ".$info_SelectedPayment->payment_reference."";
							
							$menuSession->delete();

						}
					break;
					
					case '3':
						if($menuSession->g_parent == "1")//1. Buy Now
						{
							if($menuSession->is_new == "1")
							{
								$info_ServiceOptions = $this->getServiceOptions($menuSession->service_id);
								$sendMessage = "Select ";
								$line = 1;
								foreach($info_ServiceOptions as $info_ServiceOption)
								{
									$sendMessage = $sendMessage.$this->newLine. $line . ". ".$info_ServiceOption->name;
									$line++;
								}
								$menuSession->name = $message;
								$menuSession->keyword = $message;
								$menuSession->level = 4;
								$menuSession->save();
							}
							else
							{
								if($menuSession->create_new_account_id == $message)
								{
									$sendMessage = "Enter Your Full Name:";
									$menuSession->is_new = '1';
									$menuSession->keyword = $message;
									$menuSession->level = 3;
									$menuSession->save();
								}
								else
								{
									$info_UserAccount = $this->getSelectedUserAccounts($msisdn, $menuSession->service, $message);
									$sendMessage = "Enter Amount to buy:";
									$menuSession->user_account_id = $info_UserAccount->id;
									
									$menuSession->keyword = $message;
									$menuSession->level = 4;
									$menuSession->save();
								}
							}
							
							$output['operation'] = "continue";		
							$output['message'] = $sendMessage;
							
							
						}
						else //2. My Purchases
						{
							/////
						}
					break;
					
					case '4':
						if($menuSession->g_parent == "1")//1. Buy Now
						{
							if($menuSession->is_new == "1")
							{
								$info_ServiceOption = $this->getSelectedServiceOptions($menuSession->service_id, $message);
								$sendMessage = "Please enter your METER number";
								 
								$menuSession->option = $info_ServiceOption->name;
								
								$output['operation'] = "continue";
							}
							else
							{
								$info_UserAccount = UserService::FindOrFail($menuSession->user_account_id);
								$info_UserAccount->amount = $message;
								$info_UserAccount->Save();
								
								$sendMessage = "Please confirm your details".$this->newLine.$this->newLine."Name: ".$info_UserAccount->name."".$this->newLine."Mobile: ".$info_UserAccount->msisdn."".$this->newLine."Metre Name: ".$info_UserAccount->service_option."".$this->newLine."Metre No: ".$info_UserAccount->meter_no."".$this->newLine."Amount: ".$message."".$this->newLine."Convenience fee: 100".$this->newLine.$this->newLine."1. Confirm";
								$menuSession->amount = $message;
							}
							
							$output['operation'] = "continue";		
							$output['message'] = $sendMessage;
							
							$menuSession->keyword = $message;
							$menuSession->level = 5;
							$menuSession->save();
						}
						else //2. My Purchases
						{
							/////
						}
					break;
					
					case '5':
						if($menuSession->g_parent == "1")//1. Buy Now
						{
							if($menuSession->is_new == "1")
							{
								$sendMessage = "Enter Amount to buy";
								
								$menuSession->meter_no = $message;
								
								$output['operation'] = "continue";
								$menuSession->keyword = $message;
								$menuSession->level = 6;
								$menuSession->save();
							}
							else
							{
								$info_UserAccount = UserService::FindOrFail($menuSession->user_account_id);
								
								$sendSMSMessage = "Thank you for Choosing vender.ng
	Kindly transfer N".$info_UserAccount->amount." to ".$info_UserAccount->account_no." ".$info_UserAccount->bank." to purchase your units
	Call 0908811212 for any issues";
								$this->sendSMS($msisdn,$sendSMSMessage);
								$sendMessage = "Thanks, you will receive SMS Instruction on payment shortly";
								$output['operation'] = "end";
								$menuSession->delete();
							}
							$output['message'] = $sendMessage;
						}
						else //2. My Purchases
						{
							/////
						}
					break;
					
					case '6':
						if($menuSession->g_parent == "1")//1. Buy Now
						{
							if($menuSession->is_new == "1")
							{
								$info_UserAccount = UserService::Where('msisdn',$menuSession->msisdn)->Where('service',$menuSession->service)->Where('service_option',$menuSession->option)->First();
								
								if(!$info_UserAccount)
								{
									$info_Service = Service::FindOrFail($menuSession->service_id);
									if($info_Service->notification_type == "1")
									{
										$next_notification = date('Y-m-d H:i:s',strtotime("+7 days", strtotime(date('Y-m-d H:i:s'))));
									}
									else
									if($info_Service->notification_type == "2")
									{
										$next_notification = date('Y-m-d H:i:s',strtotime("+14 days", strtotime(date('Y-m-d H:i:s'))));
									}
									else
									if($info_Service->notification_type == "3")
									{
										$next_notification = date('Y-m-d H:i:s',strtotime("+30 days", strtotime(date('Y-m-d H:i:s'))));
									}
									
									$info_UserAccount = new UserService;
									$info_UserAccount->msisdn = $menuSession->msisdn;
									$info_UserAccount->name = $menuSession->name;
									$info_UserAccount->service = $menuSession->service;
									$info_UserAccount->service_option = $menuSession->option;
									$info_UserAccount->meter_no = $menuSession->meter_no;
									$info_UserAccount->notification_type = $info_Service->notification_type;
									$info_UserAccount->last_notification = date('Y-m-d H:i:s');
									$info_UserAccount->next_notification = $next_notification;
									$info_UserAccount->notificaton_message = $menuSession->msisdn;
									$info_UserAccount->amount = $message;
									$info_UserAccount->Save();
									
									
									$uniqeID = uniqid();
									$MonifyAccount = $this->createMonifyAccount($info_UserAccount, $uniqeID);
						 
									$account_no = "";
									$bankName = "";
									if($MonifyAccount != "")
									{
										$account_no = $MonifyAccount['responseBody']['accountNumber'];
										$bankName = $MonifyAccount['responseBody']['bankName'];
									}
	
									$info_UserAccount->account_no = $account_no;
									$info_UserAccount->bank = $bankName;
									$info_UserAccount->payment_reference = $uniqeID;
									$info_UserAccount->Save();
								}
								
								$sendMessage = "Please confirm your details".$this->newLine.$this->newLine."Name: ".$info_UserAccount->name."".$this->newLine."Mobile: ".$info_UserAccount->msisdn."".$this->newLine."Metre Name: ".$info_UserAccount->service_option."".$this->newLine."Metre No: ".$info_UserAccount->meter_no."".$this->newLine."Amount: ".$message."".$this->newLine."Convenience fee: 100".$this->newLine.$this->newLine."1. Confirm";
								
								$menuSession->amount = $message;
								$menuSession->user_account_id = $info_UserAccount->id;
								
								$output['operation'] = "continue";
							}
							else
							{
								//End
							}
							
									
							$output['message'] = $sendMessage;
							
							$menuSession->keyword = $message;
							$menuSession->level = 7;
							$menuSession->save();
						}
						else //2. My Purchases
						{
							/////
						}
					break;
					
					case '7':
						if($menuSession->g_parent == "1")//1. Buy Now
						{
							if($menuSession->is_new == "1")
							{
								$info_UserAccount = UserService::FindOrFail($menuSession->user_account_id);
								
								$sendSMSMessage = "Thank you for Choosing vender.ng
	Kindly transfer N".$info_UserAccount->amount." to ".$info_UserAccount->account_no." ".$info_UserAccount->bank." to purchase your units
	Call 0908811212 for any issues";
								$this->sendSMS($msisdn,$sendSMSMessage);
								$sendMessage = "Thanks, you will receive SMS Instruction on payment shortly";
								$output['operation'] = "end";
								$menuSession->delete();
							}
							else //2. My Purchases
							{
								//End
							}	
							$output['message'] = $sendMessage;
						}
						else
						{
							/////
						}
					break;
				}
			}
		}
		//catch(\Exception $e)
		//{
		//	$output['message'] = 'Invalid reply';
		//	$output['operation'] = "end";
		//	if($menuSession)
		//		$menuSession->delete();
		//}
		return $output;
	}
	
	function getServices()
	{
		return Service::All();
	}
	function getSelectedService($rowno)
	{
		$i=1;
		foreach(Service::All() as $Service)
		{
			if($rowno == $i)
			{
				return $Service;
			}
			$i++;
		}
	}
	
	function getServiceOptions($service)
	{
		$info_Service = Service::FindOrFail($service);
		return $info_Service->Option()->Get();		
	}
	function getSelectedServiceOptions($service, $rowno)
	{
		$info_Service = Service::FindOrFail($service);
		$i=1;
		foreach($info_Service->Option()->Get() as $Options)
		{
			if($rowno == $i)
			{
				return $Options;
			}
			$i++;
		}
	}
	
	function getUserAccounts($msisdn, $service)
	{
		return UserService::Where('msisdn',$msisdn)->Where('service',$service)->Get();
	}
	function getSelectedUserAccounts($msisdn, $service, $rowno)
	{
		$i=1;
		foreach(UserService::Where('msisdn',$msisdn)->Where('service',$service)->Get() as $UserService)
		{
			if($rowno == $i)
			{
				return $UserService;
			}
			$i++;
		}
	}
	
	function getPayments($msisdn)
	{
		return Payment::Select(DB::RAW("user_services.*, payments.service_delivered, payments.amount, payments.is_paid, payments.payment_reference"))
		->leftJoin('user_services', 'user_services.id', '=', 'payments.user_service_id')
		->where('user_services.msisdn',$msisdn)
		->orderBy('id','desc')
		->limit(3)->Get();
	}
	function getSelectedPayments($msisdn, $rowno)
	{
		$info_Payments =Payment::Select(DB::RAW("user_services.*, payments.service_delivered, payments.amount, payments.is_paid, payments.payment_reference"))
		->leftJoin('user_services', 'user_services.id', '=', 'payments.user_service_id')
		->where('user_services.msisdn',$msisdn)
		->orderBy('id','desc')
		->limit(3)->Get();
		
		$i=1;
		foreach($info_Payments as $info_Payments)
		{
			if($rowno == $i)
			{
				return $info_Payments;
			}
			$i++;
		}
	}
	
	function sendSMS($msisdn, $Message)
	{
		\Log::info("Message: ".$Message);
		\Log::info("Send SMS: http://3.131.19.214:8802/?phonenumber=234".substr($msisdn,-10)."&text=".urlencode($Message)."&sender=SELFSERVE&user=selfserve&password=1234567891");
		if(env('APP_ENV')=="production")
		{
			file_get_contents("http://3.131.19.214:8802/?phonenumber=234".substr($msisdn,-10)."&text=".urlencode($Message)."&sender=SELFSERVE&user=selfserve&password=1234567891");
		}	
	}
	
	function createMonifyAccount($info_UserAccount, $uniqeID)
	{
		$url = "https://api.monnify.com/api/v1/bank-transfer/reserved-accounts";
		$login_url = "https://api.monnify.com/api/v1/auth/login";
		
		$incomeSplitconfig[] = array ("subAccountCode" => "MFY_SUB_529328264055", "feePercentage"=> 100, "splitPercentage"=> 100, "feeBearer"=> true);

		$username = 'MK_PROD_QAC28QUESH';
		$password = 'QNZXRQPRRZ4ATFWQZYBEE2QUU7QXRF3G';
		$contractCode = "768651769665";
		
		$data = json_encode(array("accountReference" => $uniqeID, "accountName"  => $info_UserAccount->name."-".$info_UserAccount->service."-".$info_UserAccount->service_option, 
		"currencyCode" => "NGN", "contractCode" => $contractCode, 
		"customerEmail" => 'name@selfserve.ng', "customerName" => $info_UserAccount->name, "incomeSplitConfig" => $incomeSplitconfig));
		 
		//Login
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $login_url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_POSTFIELDS => $data,
		CURLOPT_HTTPHEADER => array(
				"Authorization: Basic " . base64_encode("$username:$password")
			),
		));
		$response = curl_exec($curl);
		curl_close($curl);
		
		$responseData = json_decode($response);
		$token = ($responseData->responseBody->accessToken);
		
		//Call Account
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_POSTFIELDS => $data,
		CURLOPT_HTTPHEADER => array(
				"Content-Type: application/json",
				"Authorization: Bearer " . $token
			),
		));
		$response = curl_exec($curl);
		\Log::info('GetMonifyAccount Invoice Response: '.$response);
		curl_close($curl);
		$responseData = json_decode($response,true);
		\Log::info("GetMonifyAccount Invoice Response:".$response);
		if(isset($responseData['responseCode']) && $responseData['responseCode'] == 0)
		{
			//return $responseData['responseBody']->accountNumber;
			return $responseData;
		}
		else
		{
			return '';
		}
	}
	
	function MonnifyCallback(Request $request)
	{
		$json = (file_get_contents('php://input'));
		$decodeData = json_decode($json);
		
		if(isset($decodeData->eventData))
		{
			$decodeData = $decodeData->eventData;
		}
		\Log::info('MonnifyCallback: '.$json);
			
		$info_UssdUser = UssdUser::Where('payment_reference', $decodeData->paymentReference)->First();
		$info_UssdUser->is_paid = "1";
		$info_UssdUser->payment_date = date("Y-m-d H:i:s");
		$info_UssdUser->Save();
		
		$sendMessage = "Thank you ".$info_UssdUser->name." for your contribution towards rescuing our state. Together a new Kogi is possible.".$this->newLine."From: Alh. Murtala Yakubu Ajaka (MURI)";
		
		if(env('APP_ENV')=="production")
		{
			\Log::info("Send SMS: http://3.131.19.214:8802/?phonenumber=234".substr($info_UssdUser->msisdn,-10)."&text=".urlencode($sendMessage)."&sender=SELFSERVE&user=selfserve&password=1234567891");
			file_get_contents("http://3.131.19.214:8802/?phonenumber=234".substr($info_UssdUser->msisdn,-10)."&text=".urlencode($sendMessage)."&sender=SELFSERVE&user=selfserve&password=1234567891");
		}
		return "OK";
	}
}