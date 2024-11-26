@extends('user.layouts.master')

@push('css')

@endpush

@section('breadcrumb')
    @include('user.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("user.dashboard"),
        ]
    ], 'active' => __(@$page_title)])
@endsection

@section('content')

    <div class="body-wrapper">
        <div class="deposit-wrapper ptb-50">
            <div class="container">
                <div class="row justify-content-center">

                    <div class="col-lg-6 col-md-8">

                        @if($va == null)
                            <div class="deposit-form">
                                <div class="form-title text-center pb-4">
                                    <h3 class="title">Get your own account to fund your wallet</h3>

                                    <p style="font-size: 12px">Create an instant account to top up your wallet</p>

                                    <div class="preview-item d-flex justify-content-between">

                                        <a href="/user/add-money/get-account" class="btn btn--base w-100 d-flex justify-content-center"> Get Account</a>


                                    </div>







                                    <script>
                                        function copyText() {
                                            const text = document.getElementById("textToCopy").innerText;
                                            const tempInput = document.createElement("input");
                                            document.body.appendChild(tempInput);
                                            tempInput.value = text;
                                            tempInput.select();
                                            document.execCommand("copy");
                                            document.body.removeChild(tempInput);
                                            alert("Account number copied successfully!");
                                        }
                                    </script>


                                </div>


                                {{--                        <div class="preview-item d-flex justify-content-between">--}}
                                {{--                            <div class="preview-content">--}}
                                {{--                                <p>{{ __("Enter Amount") }}</p>--}}
                                {{--                            </div>--}}
                                {{--                            <div class="preview-content">--}}
                                {{--                                <p class="request-amount"> </p>--}}
                                {{--                            </div>--}}
                                {{--                        </div>--}}

                                {{--                        <div class="preview-item d-flex justify-content-between">--}}
                                {{--                            <div class="preview-content">--}}
                                {{--                                <p>{{ __("Exchange Rate") }}</p>--}}
                                {{--                            </div>--}}
                                {{--                            <div class="preview-content">--}}
                                {{--                                <p class="rate-show">--</p>--}}
                                {{--                            </div>--}}
                                {{--                        </div>--}}
                                {{--                        <div class="preview-item d-flex justify-content-between">--}}
                                {{--                            <div class="preview-content">--}}
                                {{--                                <p>{{__("Fees & Charges")}}</p>--}}
                                {{--                            </div>--}}
                                {{--                            <div class="preview-content">--}}
                                {{--                                <p class="fees">--</p>--}}
                                {{--                            </div>--}}
                                {{--                        </div>--}}
                                {{--                        <div class="preview-item d-flex justify-content-between">--}}
                                {{--                            <div class="preview-content">--}}
                                {{--                                <p>{{__("Conversion Amount")}}</p>--}}
                                {{--                            </div>--}}
                                {{--                            <div class="preview-content">--}}
                                {{--                                <p class="conversionAmount">--</p>--}}
                                {{--                            </div>--}}
                                {{--                        </div>--}}
                                {{--                        <div class="preview-item d-flex justify-content-between">--}}
                                {{--                            <div class="preview-content">--}}
                                {{--                                <p>{{__("Will Get")}}</p>--}}
                                {{--                            </div>--}}
                                {{--                            <div class="preview-content">--}}
                                {{--                                <p class="will-get">--</p>--}}
                                {{--                            </div>--}}
                                {{--                        </div>--}}

                                {{--                        <div class="preview-item d-flex justify-content-between">--}}
                                {{--                            <div class="preview-content">--}}
                                {{--                                <p>{{ __("Total Payable Amount") }}</p>--}}
                                {{--                            </div>--}}
                                {{--                            <div class="preview-content">--}}
                                {{--                                <p class="pay-in-total">--</p>--}}
                                {{--                            </div>--}}
                                {{--                        </div>--}}

                            </div>
                        @else
                            <div class="deposit-form">
                                <div class="form-title text-center pb-4">
                                    <h3 class="title">Top up your wallet</h3>

                                    <p style="font-size: 12px">Send money to the account below, your NGN wallet will be
                                        funded</p>

                                    <div class="preview-item d-flex justify-content-between">
                                        <div class="preview-content">
                                            <p>Bank</p>
                                        </div>
                                        <div class="preview-content">
                                            <p class="">{{$va->bank}}</p>
                                        </div>
                                    </div>


                                    <div class="preview-item d-flex justify-content-between">
                                        <div class="preview-content">
                                            <p>Account Name</p>
                                        </div>
                                        <div class="preview-content">
                                            <p class="">{{$va->account_name}}</p>
                                        </div>
                                    </div>


                                    <div class="preview-item d-flex justify-content-between">
                                        <div class="preview-content">
                                            <p>Account No</p>
                                        </div>
                                        <div class="preview-content">
                                            <p style="color: transparent" id="textToCopy"
                                               class="">{{$va->account_no}}</p>
                                        </div>

                                        <div class="preview-content">
                                            <span class="copy-icon" onclick="copyText()">{{$va->account_no}}📋</span>
                                        </div>
                                    </div>


                                    <script>
                                        function copyText() {
                                            const text = document.getElementById("textToCopy").innerText;
                                            const tempInput = document.createElement("input");
                                            document.body.appendChild(tempInput);
                                            tempInput.value = text;
                                            tempInput.select();
                                            document.execCommand("copy");
                                            document.body.removeChild(tempInput);
                                            alert("Account number copied successfully!");
                                        }
                                    </script>


                                </div>


                                {{--                        <div class="preview-item d-flex justify-content-between">--}}
                                {{--                            <div class="preview-content">--}}
                                {{--                                <p>{{ __("Enter Amount") }}</p>--}}
                                {{--                            </div>--}}
                                {{--                            <div class="preview-content">--}}
                                {{--                                <p class="request-amount"> </p>--}}
                                {{--                            </div>--}}
                                {{--                        </div>--}}

                                {{--                        <div class="preview-item d-flex justify-content-between">--}}
                                {{--                            <div class="preview-content">--}}
                                {{--                                <p>{{ __("Exchange Rate") }}</p>--}}
                                {{--                            </div>--}}
                                {{--                            <div class="preview-content">--}}
                                {{--                                <p class="rate-show">--</p>--}}
                                {{--                            </div>--}}
                                {{--                        </div>--}}
                                {{--                        <div class="preview-item d-flex justify-content-between">--}}
                                {{--                            <div class="preview-content">--}}
                                {{--                                <p>{{__("Fees & Charges")}}</p>--}}
                                {{--                            </div>--}}
                                {{--                            <div class="preview-content">--}}
                                {{--                                <p class="fees">--</p>--}}
                                {{--                            </div>--}}
                                {{--                        </div>--}}
                                {{--                        <div class="preview-item d-flex justify-content-between">--}}
                                {{--                            <div class="preview-content">--}}
                                {{--                                <p>{{__("Conversion Amount")}}</p>--}}
                                {{--                            </div>--}}
                                {{--                            <div class="preview-content">--}}
                                {{--                                <p class="conversionAmount">--</p>--}}
                                {{--                            </div>--}}
                                {{--                        </div>--}}
                                {{--                        <div class="preview-item d-flex justify-content-between">--}}
                                {{--                            <div class="preview-content">--}}
                                {{--                                <p>{{__("Will Get")}}</p>--}}
                                {{--                            </div>--}}
                                {{--                            <div class="preview-content">--}}
                                {{--                                <p class="will-get">--</p>--}}
                                {{--                            </div>--}}
                                {{--                        </div>--}}

                                {{--                        <div class="preview-item d-flex justify-content-between">--}}
                                {{--                            <div class="preview-content">--}}
                                {{--                                <p>{{ __("Total Payable Amount") }}</p>--}}
                                {{--                            </div>--}}
                                {{--                            <div class="preview-content">--}}
                                {{--                                <p class="pay-in-total">--</p>--}}
                                {{--                            </div>--}}
                                {{--                        </div>--}}

                            </div>
                        @endif


                    </div>
                </div>
            </div>
        </div>
        <div class="dashboard-list-area mt-20">
            <div class="dashboard-header-wrapper">
                <h4 class="title">{{ __("Add Money Log") }}</h4>
                <div class="dashboard-btn-wrapper">
                    <div class="dashboard-btn mb-2">
                        <a href="{{ setRoute('user.transactions.index','add-money') }}"
                           class="btn--base">{{__("View More")}}</a>
                    </div>
                </div>
            </div>
            <div class="dashboard-list-wrapper">
                @include('user.components.transaction-log',compact("transactions"))
            </div>
        </div>

    </div>
@endsection

@push('script')
    <script>
        var defualCurrency = "{{ get_default_currency_code() }}";
        var defualCurrencyRate = "{{ get_default_currency_rate() }}";
        var presion = 4;

        $('select[name=currency]').on('change', function () {
            if (acceptVar().cryptoType == 1) {
                presion = 8;
            } else {
                presion = 4;
            }
            getExchangeRate($(this));
            getLimit();
            getFees();
            getPreview();
        });
        $(document).ready(function () {
            if (acceptVar().cryptoType == 1) {
                presion = 8;
            } else {
                presion = 4;
            }
            getExchangeRate();
            getLimit();
            getFees();
            getPreview();
        });
        $("input[name=amount]").keyup(function () {
            getFees();
            getPreview();
        });
        $("input[name=amount]").focusout(function () {
            enterLimit();
        });

        function getExchangeRate(event) {
            var element = event;
            var currencyCode = acceptVar().currencyCode;
            var currencyRate = acceptVar().currencyRate;
            var currencyMinAmount = acceptVar().currencyMinAmount;
            var currencyMaxAmount = acceptVar().currencyMaxAmount;
            $('.rate-show').html("1 " + defualCurrency + " = " + parseFloat(currencyRate).toFixed(presion) + " " + currencyCode);
        }

        function getLimit() {
            var sender_currency = acceptVar().currencyCode;
            var sender_currency_rate = acceptVar().currencyRate;
            var min_limit = acceptVar().currencyMinAmount;
            var max_limit = acceptVar().currencyMaxAmount;
            if ($.isNumeric(min_limit) || $.isNumeric(max_limit)) {
                var min_limit_calc = parseFloat(min_limit / sender_currency_rate).toFixed(4);
                var max_limit_clac = parseFloat(max_limit / sender_currency_rate).toFixed(4);
                $('.limit-show').html("{{ __('Limit') }} " + min_limit_calc + " " + defualCurrency + " - " + max_limit_clac + " " + defualCurrency);
                return {
                    minLimit: min_limit_calc,
                    maxLimit: max_limit_clac,
                };
            } else {
                $('.limit-show').html("--");
                return {
                    minLimit: 0,
                    maxLimit: 0,
                };
            }
        }

        function enterLimit() {
            var sender_currency_rate = acceptVar().currencyRate;
            var min_limit = acceptVar().currencyMinAmount;
            var max_limit = acceptVar().currencyMaxAmount;
            if ($.isNumeric(min_limit) || $.isNumeric(max_limit)) {
                var min_limit_calc = parseFloat(min_limit / sender_currency_rate).toFixed(presion);
                var max_limit_clac = parseFloat(max_limit / sender_currency_rate).toFixed(presion);

            }
            var sender_amount = parseFloat($("input[name=amount]").val());

            if (sender_amount < min_limit_calc) {
                throwMessage('error', ["{{ __('Please follow the mimimum limit') }}"]);
                $('.sendBtn').attr('disabled', true)
            } else if (sender_amount > max_limit_clac) {
                throwMessage('error', ["{{ __('Please follow the maximum limit') }}"]);
                $('.sendBtn').attr('disabled', true)
            } else {
                $('.sendBtn').attr('disabled', false)
            }

        }


        function acceptVar() {
            var selectedVal = $("select[name=currency] :selected");
            var currencyCode = $("select[name=currency] :selected").attr("data-currency");
            var currencyRate = $("select[name=currency] :selected").attr("data-rate");
            var cryptoType = $("select[name=currency] :selected").attr("data-crypto");
            var currencyMinAmount = $("select[name=currency] :selected").attr("data-min_amount");
            var currencyMaxAmount = $("select[name=currency] :selected").attr("data-max_amount");
            var currencyFixedCharge = $("select[name=currency] :selected").attr("data-fixed_charge");
            var currencyPercentCharge = $("select[name=currency] :selected").attr("data-percent_charge");

            return {
                currencyCode: currencyCode,
                currencyRate: currencyRate,
                cryptoType: cryptoType,
                currencyMinAmount: currencyMinAmount,
                currencyMaxAmount: currencyMaxAmount,
                currencyFixedCharge: currencyFixedCharge,
                currencyPercentCharge: currencyPercentCharge,
                selectedVal: selectedVal,

            };
        }

        function feesCalculation() {
            var sender_currency = acceptVar().currencyCode;
            var sender_currency_rate = acceptVar().currencyRate;
            var sender_amount = $("input[name=amount]").val();
            sender_amount == "" ? (sender_amount = 0) : (sender_amount = sender_amount);

            var fixed_charge = acceptVar().currencyFixedCharge;
            var percent_charge = acceptVar().currencyPercentCharge;
            if ($.isNumeric(percent_charge) && $.isNumeric(fixed_charge) && $.isNumeric(sender_amount)) {
                // Process Calculation
                var fixed_charge_calc = parseFloat(sender_currency_rate * fixed_charge);
                var percent_charge_calc = parseFloat(sender_currency_rate) * (parseFloat(sender_amount) / 100) * parseFloat(percent_charge);
                var total_charge = parseFloat(fixed_charge_calc) + parseFloat(percent_charge_calc);
                total_charge = parseFloat(total_charge).toFixed(presion);
                // return total_charge;
                return {
                    total: total_charge,
                    fixed: fixed_charge_calc,
                    percent: percent_charge,
                };
            } else {
                // return "--";
                return false;
            }
        }

        function getFees() {
            var sender_currency = acceptVar().currencyCode;
            var percent = acceptVar().currencyPercentCharge;
            var charges = feesCalculation();
            if (charges == false) {
                return false;
            }
            $(".fees-show").html("{{ __('Charge') }}: " + parseFloat(charges.fixed).toFixed(presion) + " " + sender_currency + " + " + parseFloat(charges.percent).toFixed(presion) + "%");
        }

        function getPreview() {
            var senderAmount = $("input[name=amount]").val();
            var sender_currency = acceptVar().currencyCode;
            var sender_currency_rate = acceptVar().currencyRate;
            // var receiver_currency = acceptVar().rCurrency;
            senderAmount == "" ? senderAmount = 0 : senderAmount = senderAmount;

            // Sending Amount
            $('.request-amount').text(senderAmount + " " + defualCurrency);

            // Fees
            var charges = feesCalculation();
            // console.log(total_charge + "--");
            $('.fees').text(charges.total + " " + sender_currency);

            var conversionAmount = senderAmount * sender_currency_rate;
            $('.conversionAmount').text(parseFloat(conversionAmount).toFixed(presion) + " " + sender_currency);
            // will get amount
            // var willGet = parseFloat(senderAmount) - parseFloat(charges.total);
            var willGet = parseFloat(senderAmount).toFixed(4);
            $('.will-get').text(willGet + " " + defualCurrency);

            // Pay In Total
            var totalPay = parseFloat(senderAmount) * parseFloat(sender_currency_rate)
            var pay_in_total = parseFloat(charges.total) + parseFloat(totalPay);
            $('.pay-in-total').text(parseFloat(pay_in_total).toFixed(presion) + " " + sender_currency);

        }


    </script>
@endpush
