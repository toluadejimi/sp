<?php

namespace App\Http\Controllers\User;

use App\Constants\GlobalConst;
use App\Http\Controllers\Controller;
use App\Models\Admin\SetupKyc;
use App\Models\User;
use App\Models\UserAuthorization;
use App\Models\VirtualAccount;
use App\Notifications\User\Auth\SendAuthorizationCode;
use App\Providers\Admin\BasicSettingsProvider;
use App\Traits\ControlDynamicInputFields;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthorizationController extends Controller
{
    use ControlDynamicInputFields;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function showMailFrom($token)
    {
        $page_title = __("Mail Authorization");
        return view('user.auth.authorize.verify-mail', compact("page_title", "token"));
    }

    /**
     * Verify authorizaation code.
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function mailVerify(Request $request, $token)
    {
        $request->merge(['token' => $token]);
        $request->validate([
            'token' => "required|string|exists:user_authorizations,token",
            'code' => "required|array",
            'code.*' => "required|numeric",
        ]);
        $code = $request->code;
        $code = implode("", $code);
        $otp_exp_sec = BasicSettingsProvider::get()->otp_exp_seconds ?? GlobalConst::DEFAULT_TOKEN_EXP_SEC;
        $auth_column = UserAuthorization::where("token", $request->token)->where("code", $code)->first();
        if (!$auth_column) {
            return back()->with(['error' => [__('Verification code does not match')]]);
        }
        if ($auth_column->created_at->addSeconds($otp_exp_sec) < now()) {
            $this->authLogout($request);
            return redirect()->route('index')->with(['error' => [__('Session expired. Please try again')]]);
        }

        try {
            $auth_column->user->update([
                'email_verified' => true,
            ]);
            $auth_column->delete();
        } catch (Exception $e) {
            $this->authLogout($request);
            return redirect()->route('user.login')->with(['error' => [__('Something went wrong! Please try again')]]);
        }

        return redirect()->intended(route("user.dashboard"))->with(['success' => [__('Account successfully verified')]]);
    }

    public function resendCode()
    {
        $user = auth()->user();
        $resend = UserAuthorization::where("user_id", $user->id)->first();
        if (Carbon::now() <= $resend->created_at->addMinutes(GlobalConst::USER_VERIFY_RESEND_TIME_MINUTE)) {
            throw ValidationException::withMessages([
                'code' => __('You can resend verification code after') . ' ' . Carbon::now()->diffInSeconds($resend->created_at->addMinutes(GlobalConst::USER_VERIFY_RESEND_TIME_MINUTE)) . ' seconds',
            ]);
        }
        $data = [
            'user_id' => $user->id,
            'code' => generate_random_code(),
            'token' => generate_unique_string("user_authorizations", "token", 200),
            'created_at' => now(),
        ];

        DB::beginTransaction();
        try {
            UserAuthorization::where("user_id", $user->id)->delete();
            DB::table("user_authorizations")->insert($data);
            $user->notify(new SendAuthorizationCode((object)$data));
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with(['error' => [__('Something went wrong! Please try again')]]);
        }

        return redirect()->route('user.authorize.mail', $data['token'])->with(['success' => [__('Verification  code resend success')]]);

    }

    public function authLogout(Request $request)
    {
        auth()->guard("web")->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    public function showKycFrom()
    {
        $user = auth()->user();
        $page_title = __("KYC Verification");
        $user_kyc = SetupKyc::userKyc()->first();
        if (!$user_kyc) return back();
        $kyc_data = $user_kyc->fields;
        $kyc_fields = [];
        if ($kyc_data) {
            $kyc_fields = array_reverse($kyc_data);
        }
        return view('user.sections.verify-kyc', compact("page_title", "kyc_fields", "user_kyc"));
    }

    public function kycSubmit(Request $request)
    {

        $user = auth()->user();
        if ($user->kyc_verified == GlobalConst::VERIFIED) return back()->with(['success' => [__('You are already KYC Verified User')]]);

        $user_kyc_fields = SetupKyc::userKyc()->first()->fields ?? [];
        $validation_rules = $this->generateValidationRules($user_kyc_fields);

        $validated = Validator::make($request->all(), $validation_rules)->validate();
        $get_values = $this->placeValueWithFields($user_kyc_fields, $validated);

        $create = [
            'user_id' => auth()->user()->id,
            'data' => json_encode($get_values),
            'created_at' => now(),
        ];

        DB::beginTransaction();
        try {
            DB::table('user_kyc_data')->updateOrInsert(["user_id" => $user->id], $create);
            $user->update([
                'kyc_verified' => GlobalConst::PENDING,
            ]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $user->update([
                'kyc_verified' => GlobalConst::DEFAULT,
            ]);
            $this->generatedFieldsFilesDelete($get_values);
            return back()->with(['error' => [__('Something went wrong! Please try again')]]);
        }

        return redirect()->route("user.authorize.kyc")->with(['success' => [__('KYC information successfully submitted')]]);
    }


    public function updateinfo(Request $request)
    {


        $request->validate([
            'firstname' => "required|string",
            'middlename' => "required|string",
            'lastname' => "required|string",
            'mobile' => "required|string|min:11",

        ]);

        User::where('id', Auth::id())->update([
            'firstname' => $request->firstname,
            'middlename' => $request->middlename,
            'lastname' => $request->lastname,
            'full_mobile' => "234".$request->mobile,
            'mobile' => $request->mobile,


        ]);

        return redirect()->route("user.authorize.kyc")->with(['success' => [__('User information successfully updated')]]);


    }

    public function updateverifyinfo(Request $request)
    {

        $request->validate([
            'nin' => "required|int",
            'bvn' => "required|int",
        ]);

        User::where('id', Auth::id())->update([
            'nin' => $request->nin,
            'bvn' => $request->bvn,


        ]);



        $first_name = Auth::user()->firstname;
        $last_name = Auth::user()->lastname;
        $middle_name = Auth::user()->middlename;
        $email = Auth::user()->email;
        $phone = Auth::user()->full_mobile;
        $nin = Auth::user()->nin;
        $bvn = Auth::user()->bvn;
        $amount = $request->amount;





        $key = env('WOVENKEY');
        $databody = array(
            "customer_reference" => $last_name . "_" . $first_name.date('his'),
            "name" => $first_name . " $middle_name " . $last_name,
            "email" => $email,
            "mobile_number" => $phone,
            "bvn" => $bvn,
            "nin" => $nin,
            "callback_url" => url('') . "/api/callback-woven",
            "collection_bank" => "000017"//"060001" //"000017",
        );




        $post_data = json_encode($databody);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.woven.finance/v2/api/vnubans/create_customer',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $post_data,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                "api_secret: $key"
            ),
        ));
        $var = curl_exec($curl);
        curl_close($curl);
        $var = json_decode($var);
        $status = $var->status ?? null;


        if($status == "success"){
            $va = new VirtualAccount();
            $va->user_id = Auth::id();
            $va->bank = $var->data->bank_name;
            $va->bank_code = $var->data->bank_code;
            $va->account_no = $var->data->vnuban;
            $va->account_name = $var->data->account_name;
            $va->status = 2;
            $va->save();

            User::where('id', Auth::id())->update(['kyc_verified' => 1]);

            return redirect()->route("user.authorize.kyc")->with(['success' => [__('Account has been verified')]]);


        }


        return redirect()->route("user.authorize.kyc")->with(['error' => [__("$var->message")]]);




    }


}
