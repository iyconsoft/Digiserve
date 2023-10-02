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
							
				$sendMessage = "Welcome to Vender USSD Platform".$this->newLine."1. Buy Now".$this->newLine."2. My Purchases";
				
				$output['session_operation'] = "continue";		
				$output['session_msg'] = $sendMessage;
				
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
							
							$output['session_operation'] = "continue";		
							$output['session_msg'] = $sendMessage;
							
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
								$output['session_operation'] = "end";		
								$output['session_msg'] = "No payment found";
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
								$output['session_operation'] = "continue";		
								$output['session_msg'] = $sendMessage;
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
							
							$output['session_operation'] = "continue";		
							$output['session_msg'] = $sendMessage;
							
							
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
							
							$output['session_operation'] = "end";		
							$output['session_msg'] = "Date: ".$info_SelectedPayment->created_at->format('d/M/Y')."".$this->newLine."".$info_SelectedPayment->service_option." Prepaid Meter Token: ".$info_SelectedPayment->meter_no."".$this->newLine."Payment Ref: ".$info_SelectedPayment->payment_reference."";
							
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
							
							$output['session_operation'] = "continue";		
							$output['session_msg'] = $sendMessage;
							
							
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
								
								$output['session_operation'] = "continue";
							}
							else
							{
								$info_UserAccount = UserService::FindOrFail($menuSession->user_account_id);
								$info_UserAccount->amount = $message;
								$info_UserAccount->Save();
								
								$sendMessage = "Please confirm your details".$this->newLine.$this->newLine."Name: ".$info_UserAccount->name."".$this->newLine."Mobile: ".$info_UserAccount->msisdn."".$this->newLine."Metre Name: ".$info_UserAccount->service_option."".$this->newLine."Metre No: ".$info_UserAccount->meter_no."".$this->newLine."Amount: ".$message."".$this->newLine."Convenience fee: 100".$this->newLine.$this->newLine."1. Confirm";
								$menuSession->amount = $message;
							}
							
							$output['session_operation'] = "continue";		
							$output['session_msg'] = $sendMessage;
							
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
								
								$output['session_operation'] = "continue";
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
								$output['session_operation'] = "end";
								$menuSession->delete();
							}
							$output['session_msg'] = $sendMessage;
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
								
								$output['session_operation'] = "continue";
							}
							else
							{
								//End
							}
							
									
							$output['session_msg'] = $sendMessage;
							
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
								$output['session_operation'] = "end";
								$menuSession->delete();
							}
							else //2. My Purchases
							{
								//End
							}	
							$output['session_msg'] = $sendMessage;
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
		//	$output['session_msg'] = 'Invalid reply';
		//	$output['session_operation'] = "end";
		//	if($menuSession)
		//		$menuSession->delete();
		//}
		\Log::info('Response Message: '.$output['session_msg']);
		return $output;
	}
	
	function getServices()
	{
		return Service::Where('status','1')->Get();
	}
	function getSelectedService($rowno)
	{
		$i=1;
		foreach(Service::Where('status','1')->Get() as $Service)
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
		
		$incomeSplitconfig[] = array ("subAccountCode" => "MFY_SUB_297755232263", "feePercentage"=> 100, "splitPercentage"=> 100, "feeBearer"=> true);

		$username = 'MK_PROD_KVWB6PZPLS';
		$password = 'S39TDFPVHUQGH6PWV4A287KLVUZJYVK4';
		$contractCode = "881254378615";
		
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
		
		
		$account_no = $decodeData->product->reference;
		$info_UserService = UserService::Where('account_no',$account_no)->First();
		
		$info_Payment = new Payment;
		$info_Payment->user_service_id = $info_UserService->id;
		$info_Payment->payment_reference = $decodeData->paymentReference;
		$info_Payment->amount = $decodeData->amountPaid;
		$info_Payment->is_paid = '1';
		$info_Payment->service_delivered = date('Y-m-d H:i:s');
		$info_Payment->Saave();
		 
		$merchantReferenceNumber = $decodeData->paymentReference;
		$amount = $decodeData->amountPaid;
		$merchantAccount = $account_no;
		$referenceNumber = $info_UserService->meter_no;
		$merchantService = $info_UserService->service;
		$hashkey = 'c198c27d34f5400d9b06ee60e5ef6baebdd74323804c4726a5f6276e0f4420fbf871689cd51347c7a8e7ee30157d617c1d7c9f498d0f4bc1b21425eb1eaa88cf
';
		//PAGA
		$data = '{
			"merchantReferenceNumber": "'.$merchantReferenceNumber.'",
			"amount": '.$amount.',
			"merchantAccount": "'.$merchantAccount.'",
			"referenceNumber": "'.$referenceNumber.'",
			"currency": "NGN",
			"merchantService": [
				"'.$merchantService.'"
			],
			"locale": "NG"
		}';
		\Log::info('PAGA Payload: '.$data);
		
		
		$hashed = hash("sha512", $referenceNumber + $amount + $merchantAccount + $merchantReferenceNumber + $hashkey);
		
		
		$db_payment->ibris_request_dump = $data;
		$db_payment->save();
		
		$curl = curl_init();
		curl_setopt_array($curl, array(
		//CURLOPT_URL => 'http://ics3staging.abiairs.gov.ng/assessment-api/api/vendor/payment/validation', //Test
		CURLOPT_URL => 'https://www.abiairs.gov.ng/assessment-api/api/vendor/payment/notification', //Live
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_POSTFIELDS => $data,
		CURLOPT_HTTPHEADER => array(
				"principal:0CA31F18-1F98-49CF-A82B-5826102165FA",
				"credentials:fK4#cg@WDN*eMfM",
				"Content-Type:application/json",
				"hash:" . $hashed
			),
		));
		$response = curl_exec($curl);
		\Log::info('PAGA Response: '.$response);
		curl_close($curl);
		
		
		$sendMessage = "Your payment was successful, you have received  N".$decodeData->amountPaid." worth of unit for Meter ".$info_UserService->meter_no."
".$info_UserService->option." Prepaid Meter Token: ".$info_UserService->meter_no."
Payment Ref: ".$decodeData->paymentReference;
		
		if(env('APP_ENV')=="production")
		{
			\Log::info("Send SMS: http://3.131.19.214:8802/?phonenumber=234".substr($info_UssdUser->msisdn,-10)."&text=".urlencode($sendMessage)."&sender=SELFSERVE&user=selfserve&password=1234567891");
			file_get_contents("http://3.131.19.214:8802/?phonenumber=234".substr($info_UssdUser->msisdn,-10)."&text=".urlencode($sendMessage)."&sender=SELFSERVE&user=selfserve&password=1234567891");
		}
		return "OK";
	}
}