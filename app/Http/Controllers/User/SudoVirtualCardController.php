<?php

namespace App\Http\Controllers\User;

use App\Constants\GlobalConst;
use App\Constants\NotificationConst;
use App\Constants\PaymentGatewayConst;
use App\Http\Controllers\Controller;
use App\Http\Helpers\Response;
use App\Models\Admin\BasicSettings;
use App\Models\Admin\Currency;
use App\Models\Admin\TransactionSetting;
use App\Models\SudoVirtualCard;
use App\Models\Transaction;
use App\Models\UserNotification;
use App\Models\UserWallet;
use App\Models\VirtualCardApi;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SudoVirtualCardController extends Controller
{
    protected $api;
    protected $card_limit;
    public function __construct()
    {
        $cardApi = VirtualCardApi::first();
        $this->api =  $cardApi;
        $this->card_limit =  $cardApi->card_limit;
    }
    public function index()
    {

        $page_title = __("Virtual Card");
        $myCards = SudoVirtualCard::where('user_id',auth()->user()->id)->get();
        $totalCards = SudoVirtualCard::where('user_id',auth()->user()->id)->count();
        $cardCharge = TransactionSetting::where('slug','virtual_card')->where('status',1)->first();
        $cardReloadCharge = TransactionSetting::where('slug','reload_card')->where('status',1)->first();
        $transactions = Transaction::auth()->virtualCard()->latest()->take(5)->get();
        $cardApi = $this->api;
        return view('user.sections.virtual-card-sudo.index',compact(
            'page_title','myCards','cardApi','cardReloadCharge',
            'transactions','cardCharge','totalCards'
        ));
    }
    public function cardDetails($card_id)
    {
        $page_title = __("Card Details");
        $myCard = SudoVirtualCard::where('card_id',$card_id)->first();
        $cardToken = getCardToken($this->api->config->sudo_api_key,$this->api->config->sudo_url,$myCard->card_id);
        if($cardToken['statusCode'] == 200){
            $cardToken = $cardToken['data']['token'];
        }else{
            $cardToken = '';
        }
        $api_mode = $this->api->config->sudo_mode;
        $api_vault_id = $this->api->config->sudo_vault_id;
        $cardApi = $this->api;
        return view('user.sections.virtual-card-sudo.details',compact('page_title','myCard','cardToken','api_mode','api_vault_id','cardApi'));
    }
    public function makeDefaultOrRemove(Request $request) {
        $validated = Validator::make($request->all(),[
            'target'        => "required|numeric",
        ])->validate();
        $user = auth()->user();
        $targetCard =  SudoVirtualCard::where('id',$validated['target'])->where('user_id',$user->id)->first();
        $withOutTargetCards =  SudoVirtualCard::where('id','!=',$validated['target'])->where('user_id',$user->id)->get();

        try{
            $targetCard->update([
                'is_default'         => $targetCard->is_default ? 0 : 1,
            ]);
            if(isset(  $withOutTargetCards)){
                foreach(  $withOutTargetCards as $card){
                    $card->is_default = false;
                    $card->save();
                }
            }

        }catch(Exception $e) {
            return back()->with(['error' => [__('Something went wrong! Please try again')]]);
        }
        return back()->with(['success' => [__('Status Updated Successfully')]]);
    }
    public function cardTransaction($card_id) {
        $page_title = __("Virtual Card Transactions");
        $user = auth()->user();
        $card = SudoVirtualCard::where('user_id',$user->id)->where('card_id', $card_id)->first();
        $card_truns =  getCardTransactions($this->api->config->sudo_api_key,$this->api->config->sudo_url,$card->card_id);
        return view('user.sections.virtual-card-sudo.trx',compact('page_title','card','card_truns'));
    }
    public function cardBlockUnBlock(Request $request) {
        $validator = Validator::make($request->all(),[
            'status'                    => 'required|boolean',
            'data_target'               => 'required|string',
        ]);
        if ($validator->stopOnFirstFailure()->fails()) {
            $error = ['error' => $validator->errors()];
            return Response::error($error,null,400);
        }
        $validated = $validator->safe()->all();
        if($request->status == 1 ){
            $card = SudoVirtualCard::where('id',$request->data_target)->where('status',1)->first();
            $status = 'inactive';
            if(!$card){
                $error = ['error' => [__('Sorry,This Card Is Already Inactive')]];
                return Response::error($error,null,404);
            }
            $result = cardUpdate($this->api->config->sudo_api_key,$this->api->config->sudo_url,$card->card_id,$status);
            if(isset($result['statusCode'])){
                if($result['statusCode'] == 200){
                    $card->status = false;
                    $card->save();
                    $success = ['success' => [__('Card Inactive Successfully!')]];
                    return Response::success($success,null,200);
                }elseif($result['statusCode'] != 200){
                    $success = ['error' => [$result['message']??"Something is wrong"]];
                    return Response::success($success,null,200);
                }
            }
        }else{
        $card = SudoVirtualCard::where('id',$request->data_target)->where('status',0)->first();
        $status = 'active';
        if(!$card){
            $error = ['error' => [__('Something is wrong in your card')]];
            return Response::error($error,null,404);
        }
        $result = cardUpdate($this->api->config->sudo_api_key,$this->api->config->sudo_url,$card->card_id,$status);
        if(isset($result['statusCode'])){
            if($result['statusCode'] == 200){
                $card->status = true;
                $card->save();
                $success = ['success' => [__('Card Active Successfully!')]];
                return Response::success($success,null,200);
            }elseif($result['statusCode'] != 200){
                $success = ['error' => [$result['message']??"Something is wrong"]];
                return Response::success($success,null,200);
            }
        }

        }
    }

    public function cardBuy(Request $request)
    {
        $request->validate([
            'card_amount' => 'required|numeric|gt:0',
        ]);
        $basic_setting = BasicSettings::first();
        $user = auth()->user();
        if($basic_setting->kyc_verification){
            if( $user->kyc_verified == 0){
                return redirect()->route('user.profile.index')->with(['error' => [__('Please submit kyc information!')]]);
            }elseif($user->kyc_verified == 2){
                return redirect()->route('user.profile.index')->with(['error' => [__('Please wait before admin approved your kyc information')]]);
            }elseif($user->kyc_verified == 3){
                return redirect()->route('user.profile.index')->with(['error' => [__('Admin rejected your kyc information, Please re-submit again')]]);
            }
        }
        $amount = (float) $request->card_amount;
        $wallet = UserWallet::where('user_id',$user->id)->first();
        if(!$wallet){
            return back()->with(['error' => [__('User wallet not found')]]);
        }
        $cardCharge = TransactionSetting::where('slug','virtual_card')->where('status',1)->first();
        $baseCurrency = Currency::default();
        $rate = $baseCurrency->rate;
        if(!$baseCurrency){
            return back()->with(['error' => [ __('Default Currency Not Setup Yet')]]);
        }
        $minLimit =  $cardCharge->min_limit *  $rate;
        $maxLimit =  $cardCharge->max_limit *  $rate;
        if($amount < $minLimit || $amount > $maxLimit) {
            return back()->with(['error' => [__('Please follow the transaction limit')]]);
        }
        //charge calculations
        $fixedCharge = $cardCharge->fixed_charge *  $rate;
        $percent_charge = ($amount / 100) * $cardCharge->percent_charge;
        $total_charge = $fixedCharge + $percent_charge;
        $payable = $total_charge + $amount;
        if($payable > $wallet->balance ){
            return back()->with(['error' => [__('Sorry, insufficient balance')]]);
        }
        $currency = $baseCurrency->code;
        $supported_currency = ['USD','NGN'];
        if( !in_array($currency,$supported_currency??[])){
            return back()->with(['error' => [$currency." ". __("Currency doesn't supported for creating virtual card, Please contact")]]);
        }
        $funding_sources =  get_funding_source( $this->api->config->sudo_api_key,$this->api->config->sudo_url);
        if(isset( $funding_sources['statusCode'])){
            if($funding_sources['statusCode'] == 403){
                return back()->with(['error' => [$funding_sources['message']]]);
            }elseif($funding_sources['statusCode'] == 404){
                return back()->with(['error' => [$funding_sources['message']]]);
            }
        }
        $funding_sources =  get_funding_source( $this->api->config->sudo_api_key,$this->api->config->sudo_url);
        if(isset( $funding_sources['statusCode'])){
            if($funding_sources['statusCode'] == 403){
                return back()->with(['error' => [$funding_sources['message']]]);
            }elseif($funding_sources['statusCode'] == 404){
                return back()->with(['error' => [$funding_sources['message']]]);
            }
        }
        $account_type = 'default';
        $accountTypeArray = array_filter($funding_sources['data'], function($item) use ($account_type) {
            return $item['type'] === $account_type;
        });
        if( count($accountTypeArray) <=  0){
            $create_founding_source = funding_source_create( $this->api->config->sudo_api_key,$this->api->config->sudo_url);
            if($create_founding_source['status'] ===  false){
                return back()->with(['error' => [$create_founding_source['message']]]);
            }
            $bankCode = $create_founding_source['data']['_id']??'';
        }else{
            $funding_source_id =  array_values($accountTypeArray);
            $bankCode = $funding_source_id[0]['_id']??'';
        }

        $sudo_accounts =    get_sudo_accounts( $this->api->config->sudo_api_key,$this->api->config->sudo_url);
        $filteredArray = array_filter($sudo_accounts, function($item) use ($currency) {
            return $item['currency'] === $currency;
        });
        $matchingElements = array_values($filteredArray);
        $debitAccountId= $matchingElements[0]['_id']??"";

        if(  $debitAccountId == ""){
            //create debit account
            if( $user->sudo_account == null){
                //create account
                $store_account = create_sudo_account($this->api->config->sudo_api_key,$this->api->config->sudo_url, $currency);
                if( isset($store_account['error'])){
                    return back()->with(['error' => [__("Haven't any debit account this currency, Please contact with owner")]]);
                }
                $user->sudo_account =   (object)$store_account['data'];
                $user->save();

            }
        }else{
            $user->sudo_account = (object)$matchingElements[0];
            $user->save();
        }
        $debitAccountId = $user->sudo_account->_id??'';

        $issuerCountry = '';
        if(get_default_currency_code() == "NGN"){
            $issuerCountry = "NGA";
        }elseif(get_default_currency_code() === "USD"){
            $issuerCountry = "USA";
        }

        //check sudo customer have or not
       if( $user->sudo_customer == null){
        //create customer
        $store_customer = create_sudo_customer($this->api->config->sudo_api_key,$this->api->config->sudo_url,$user);

        if( isset($store_customer['error'])){
            return back()->with(['error' => [__("Customer doesn't created properly,Contact with owner")]]);
        }
        $user->sudo_customer =   (object)$store_customer['data'];
        $user->save();
        $customerId = $user->sudo_customer->_id;

       }else{
        $customerId = $user->sudo_customer->_id;
       }
       //create card now
       $created_card = create_virtual_card( $amount,$this->api->config->sudo_api_key,$this->api->config->sudo_url,
                            $customerId, $currency,$bankCode, $debitAccountId, $issuerCountry
                        );
       if(isset($created_card['statusCode'])){
        if($created_card['statusCode'] == 400){
            return back()->with(['error' => [$created_card['message']]]);
        }

       }
       if($created_card['statusCode']  = 200){
            $card_info = (object)$created_card['data'];
            $v_card = new SudoVirtualCard();
            $v_card->user_id = $user->id;
            $v_card->name = $user->fullname;
            $v_card->card_id = $card_info->_id;
            $v_card->business_id = $card_info->business;
            $v_card->customer = $card_info->customer;
            $v_card->account = $card_info->account;
            $v_card->fundingSource = $card_info->fundingSource;
            $v_card->type = $card_info->type;
            $v_card->brand = $card_info->brand;
            $v_card->currency = $card_info->currency;
            if($this->api->config->sudo_mode === GlobalConst::SANDBOX){
                $v_card->amount = $card_info->balance;
            }elseif($this->api->config->sudo_mode === GlobalConst::LIVE){
                $v_card->amount = $amount;
            }
            $v_card->charge = $total_charge;
            $v_card->maskedPan = $card_info->maskedPan;
            $v_card->last4 = $card_info->last4;
            $v_card->expiryMonth = $card_info->expiryMonth;
            $v_card->expiryYear = $card_info->expiryYear;
            $v_card->status = true;
            $v_card->isDeleted = $card_info->isDeleted;
            $v_card->billingAddress = $card_info->billingAddress;
            $v_card->save();

            $trx_id =  'CB'.getTrxNum();
            try{
                $sender = $this->insertCadrBuy( $trx_id,$user,$wallet,$amount, $v_card ,$payable);
                $this->insertBuyCardCharge( $fixedCharge,$percent_charge, $total_charge,$user,$sender,$v_card->maskedPan);
                return redirect()->route("user.sudo.virtual.card.index")->with(['success' => [__('Buy Card Successfully')]]);
            }catch(Exception $e){
                return back()->with(['error' => [__("Something Went Wrong! Please Try Again")]]);
            }

       }

    }
    //card buy helper
     public function insertCadrBuy( $trx_id,$user,$wallet,$amount, $v_card ,$payable) {
        $trx_id = $trx_id;
        $authWallet = $wallet;
        $afterCharge = ($authWallet->balance - $payable);
        $details =[
            'card_info' =>   $v_card??''
        ];
        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'user_id'                       => $user->id,
                'user_wallet_id'                => $authWallet->id,
                'payment_gateway_currency_id'   => null,
                'type'                          => PaymentGatewayConst::VIRTUALCARD,
                'trx_id'                        => $trx_id,
                'request_amount'                => $amount,
                'payable'                       => $payable,
                'available_balance'             => $afterCharge,
                'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::CARDBUY," ")),
                'details'                       => json_encode($details),
                'attribute'                      =>PaymentGatewayConst::RECEIVED,
                'status'                        => true,
                'created_at'                    => now(),
            ]);
            $this->updateSenderWalletBalance($authWallet,$afterCharge);

            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something Went Wrong! Please Try Again"));
        }
        return $id;
    }
    public function insertBuyCardCharge($fixedCharge,$percent_charge, $total_charge,$user,$id,$masked_card) {
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $id,
                'percent_charge'    => $percent_charge,
                'fixed_charge'      =>$fixedCharge,
                'total_charge'      =>$total_charge,
                'created_at'        => now(),
            ]);
            DB::commit();

            //notification
             $notification_content = [
                'title'         =>"Buy Card",
                'message'       => 'Buy card successful '.$masked_card,
                'image'         => files_asset_path('profile-default'),
            ];

            UserNotification::create([
                'type'      => NotificationConst::CARD_BUY,
                'user_id'  => $user->id,
                'message'   => $notification_content,
            ]);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something Went Wrong! Please Try Again"));
        }
    }

    public function cardFundConfirm(Request $request){
        $request->validate([
            'id' => 'required|integer',
            'fund_amount' => 'required|numeric|gt:0',
        ]);
        $user = auth()->user();
        $myCard =  SudoVirtualCard::where('user_id',auth()->user()->id)->where('id',$request->id)->first();
        if(!$myCard){
            return back()->with(['error' => [__("Something is wrong in your card")]]);
        }
        $amount = $request->fund_amount;
        $wallet = UserWallet::where('user_id',$user->id)->first();
        if(!$wallet){
            return back()->with(['error' => [__('User wallet not found')]]);
        }
        $cardCharge = TransactionSetting::where('slug','reload_card')->where('status',1)->first();
        $baseCurrency = Currency::default();
        $rate = $baseCurrency->rate;
        if(!$baseCurrency){
            return back()->with(['error' => [__('Default currency not found')]]);
        }
        $fixedCharge = $cardCharge->fixed_charge *  $rate;
        $percent_charge = ($amount / 100) * $cardCharge->percent_charge;
        $total_charge = $fixedCharge + $percent_charge;
        $payable = $total_charge + $amount;
        if($payable > $wallet->balance ){
            return back()->with(['error' => [__('Sorry, insufficient balance')]]);
        }
        $get_card_details =  getSudoCard($myCard->card_id);
        if($get_card_details['status'] === false){
            return back()->with(['error' => [__("Something is wrong in your card")]]);
        }
        $card_account_number =  $get_card_details['data']['account']['_id'];

        $card_fund_response = sudoFundCard( $card_account_number,(float)$amount);
        if(!empty($card_fund_response['status'])  && $card_fund_response['status'] === true){
            //added fund amount to card
            $myCard->amount += $amount;
            $myCard->save();
            $trx_id = 'CF'.getTrxNum();
            $sender = $this->insertCardFund( $trx_id,$user,$wallet,$amount, $myCard ,$payable);
            $this->insertFundCardCharge( $fixedCharge,$percent_charge, $total_charge,$user,$sender,$myCard->maskedPan,$amount);
            return redirect()->route("user.sudo.virtual.card.index")->with(['success' => [__('Card Funded Successfully')]]);

        }else{
            return redirect()->back()->with(['error' => [@$card_fund_response['message'].' ,'.__('Please Contact With Administration.')]]);
        }

    }
     //card fund helper
    public function insertCardFund( $trx_id,$user,$wallet,$amount, $myCard ,$payable) {
        $trx_id = $trx_id;
        $authWallet = $wallet;
        $afterCharge = ($authWallet->balance - $payable);
        $details =[
            'card_info' =>   $myCard??''
        ];
        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'user_id'                       => $user->id,
                'user_wallet_id'                => $authWallet->id,
                'payment_gateway_currency_id'   => null,
                'type'                          => PaymentGatewayConst::VIRTUALCARD,
                'trx_id'                        => $trx_id,
                'request_amount'                => $amount,
                'payable'                       => $payable,
                'available_balance'             => $afterCharge,
                'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::CARDFUND," ")),
                'details'                       => json_encode($details),
                'attribute'                      =>PaymentGatewayConst::RECEIVED,
                'status'                        => true,
                'created_at'                    => now(),
            ]);
            $this->updateSenderWalletBalance($authWallet,$afterCharge);

            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something Went Wrong! Please Try Again"));
        }
        return $id;
    }
    public function insertFundCardCharge($fixedCharge,$percent_charge, $total_charge,$user,$id,$masked_card,$amount) {
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $id,
                'percent_charge'    => $percent_charge,
                'fixed_charge'      =>$fixedCharge,
                'total_charge'      =>$total_charge,
                'created_at'        => now(),
            ]);
            DB::commit();

            //notification
            $notification_content = [
                'title'         =>"Card Fund",
                'message'       => __("Card fund successful card")." : ".$masked_card.' '.getAmount($amount,2).' '.get_default_currency_code(),
                'image'         => get_image($user->image,'user-profile'),
            ];

            UserNotification::create([
                'type'      => NotificationConst::CARD_FUND,
                'user_id'  => $user->id,
                'message'   => $notification_content,
            ]);

            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something Went Wrong! Please Try Again"));
        }
    }
    //update user balance
    public function updateSenderWalletBalance($authWallet,$afterCharge) {
        $authWallet->update([
            'balance'   => $afterCharge,
        ]);
    }
}
