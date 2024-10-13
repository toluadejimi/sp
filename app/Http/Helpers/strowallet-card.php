<?php

use App\Constants\GlobalConst;
use App\Models\StrowalletVirtualCard;
use GuzzleHttp\Client;
use App\Models\VirtualCardApi;


function stro_wallet_create_user($user,$formData,$public_key,$base_url){


    $client = new Client();

    $response               = $client->request('POST', $base_url.'create-user/', [
        'headers'           => [
            'accept'        => 'application/json',
        ],
        'form_params'       => [
            'public_key'    => $public_key,
            'houseNumber'   => $formData['house_number'],
            'firstName'     => $formData['first_name'],
            'lastName'      => $formData['last_name'],

            'idNumber'      => rand(123456789,987654321),
            'customerEmail' => $formData['customer_email'],
            'phoneNumber'   => $formData['phone'],
            'dateOfBirth'   => $formData['date_of_birth'],
            'idImage'       => "https://ssl.gstatic.com/ui/v1/icons/mail/rfr/logo_gmail_lockup_dark_1x_r5.png",
            'userPhoto'     => "https://ssl.gstatic.com/ui/v1/icons/mail/rfr/logo_gmail_lockup_dark_1x_r5.png",
            'line1'         => $formData['line1'],
            'state'         => 'Accra',
            'zipCode'       => $formData['zip_code'],
            'city'          => 'Accra',
            'country'       => 'Ghana',
            'idType'        => 'PASSPORT',
        ],
    ]);

    $result         = $response->getBody();
    $decodedResult  = json_decode($result, true);

    if(isset($decodedResult['success']) && $decodedResult['success'] == true ){
        $data =[
            'status'        => true,
            'message'       => "Create Customer Successfully.",
            'data'          => $decodedResult['response'],
        ];
    }else{
        $data =[
            'status'        => false,
            'message'       => $decodedResult['message'] ?? 'Something is wrong! Contact With Admin',
            'data'          => null,
        ];
    }

    return $data;

}
// create virtual card for strowallet
function create_strowallet_virtual_card($user,$cardAmount,$customer,$public_key,$base_url,$formData){
    $method = VirtualCardApi::first();
    $mode = $method->config->strowallet_mode??GlobalConst::SANDBOX;
    $data = [
        'name_on_card' => $formData['name_on_card'] ?? $user->username,
        'card_type' => $customer->card_brand,
        'public_key' => $public_key,
        'amount' => $cardAmount,
        'customerEmail' => $customer->customerEmail,
    ];

    if ($mode === GlobalConst::SANDBOX) {
        $data['mode'] = "sandbox";
    }
    $data['developer_code'] = 'appdevsx';

    $curl = curl_init();

    curl_setopt_array($curl, [
    CURLOPT_URL => $base_url."create-card/",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => [
        "accept: application/json",
        "content-type: application/json"
    ],
    ]);
    $response = curl_exec($curl);

    curl_close($curl);
    $result  = json_decode($response, true);


    if(isset($result['success']) && $result['success'] == true ){
        $data =[
            'status'        => true,
            'message'       => "Create Card Successfully.",
            'data'          => $result['response'],
        ];
    }else{
        $data =[
            'status'        => false,
            'message'       => $result['message']??$result['error']??"",
            'data'          => null,
        ];
    }

    return $data;
}
// card details
function card_details($card_id,$public_key,$base_url){
    $curl = curl_init();

    curl_setopt_array($curl, [
    CURLOPT_URL => $base_url . "fetch-card-detail/",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => json_encode([
        'public_key'    => $public_key,
        'card_id'       => $card_id
    ]),
    CURLOPT_HTTPHEADER => [
        "accept: application/json",
        "content-type: application/json"
    ],
    ]);

    $response = curl_exec($curl);

    curl_close($curl);

    $result  = json_decode($response, true);

    if(isset($result['success']) && $result['success'] == true ){
        $data =[
            'status'        => true,
            'message'       => "Card Details Retrieved Successfully.",
            'data'          => $result['response'],
        ];
    }else{
        $data =[
            'status'        => false,
            'message'       => $result['message'] ?? 'Your Card Is Pending!Please Contact With Admin',
            'data'          => null,
        ];
    }

    return $data;
}
function strowalletBalance(){
    $currency_code = get_default_currency_code();
    $method = VirtualCardApi::first();
    $publicKey =  $method->config->strowallet_public_key;
    $url = 'https://strowallet.com/api/wallet/balance/'.$currency_code.'/?public_key='  . $publicKey;

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Accept: application/json'
    ));

    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode(  $response,true);
    if(isset($result['balance']) ){
        $data =[
            'status'        => true,
            'message'       => "Account Balance Get Successfully",
            'balance'          => $result['balance'],
        ];
    }else{
        $data =[
            'status'        => false,
            'message'       => $result['message'] ?? 'Something is wrong! Contact With Admin',
            'balance'          => 0
        ];
    }
    return $data;
}
function updateStroWalletCardBalance($user,$card_id,$response){
    $card = StrowalletVirtualCard::where('user_id',$user->id)->where('card_id',$card_id)->first();
    $card->balance = $response['data']['card_detail']['balance']??$card->balance;
    $card->save();
    return  $card->balance??0;
}
