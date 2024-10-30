<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StrowalletVirtualCard;
use App\Models\Transaction;
use App\Models\UserWallet;
use App\Models\VirtualCard;
use Illuminate\Http\Request;

class CardController extends Controller
{
   public function card(request $request)
   {

      $card =  StrowalletVirtualCard::where('card_number', $request->card_no)->first() ?? null;

      if($card != null){
          return response()->json([
             'status' => "success",
             'message' => 'card found'
          ]);
      }else{
          return response()->json([
              'status' => "failed",
              'message' => 'card not found'
          ]);
      }

   }


    public function charge_card(request $request)
    {

        $card =  StrowalletVirtualCard::where('card_number', $request->card_no)->first() ?? null;

        if($card != null){

            $user_id = $card->card_user_id;
            $wallet = UserWallet::where('user_id', $user_id)->first()->balance ?? null;

            if($request->amount > $wallet){
                return response()->json([
                    'status' => "failed",
                    'message' => 'Insufficient Funds'
                ]);
            }else{

                UserWallet::where('user_id', $user_id)->decrement('balance', $request->amount);
                $user_wallet_id =  UserWallet::where('user_id', $user_id)->first()->id;
                $available_balance =UserWallet::where('user_id', $user_id)->first()->balance;
                $trx = new Transaction();
                $trx->user_id = $user_id;
                $trx->type = "CARD-BUY";
                $trx->user_wallet_id = $user_wallet_id;
                $trx->trx_id = "SPR".date('ymhhis');
                $trx->request_amount = $request->amount;
                $trx->payable = $request->amount;
                $trx->available_balance = $available_balance;
                $trx->status = 1;
                $trx->attribute = 'SEND';
                $trx->save();

                return response()->json([
                    'status' => "success",
                    'message' => 'Payment successful'
                ]);


            }


        }else{
            return response()->json([
                'status' => "failed",
                'message' => 'card not found'
            ]);
        }

    }
}
