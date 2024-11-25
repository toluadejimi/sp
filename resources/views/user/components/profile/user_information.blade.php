@if ($basic_settings->kyc_verification == true && Auth::user()->strowallet_customer == null)
    <h3 class="title">{{ __("User Information") }} &nbsp;
    </h3>



    <p>{{ __("Please make sure your details is valid") }}</p>

    <form action="{{ setRoute('user.authorize.updateuserinfo.submit') }}" class="account-form" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row ml-b-20">
            <div class="col-lg-12 form-group text-center">
                <label class="d-flex justify-content-start">First Name</label>
                <input class="form-control text-dark" name="firstname" value="{{Auth::user()->firstname}}">
            </div>

            <div class="col-lg-12 form-group text-center">
                <label class="d-flex justify-content-start">Middle Name</label>
                <input class="form-control text-dark" name="middlename" value="{{Auth::user()->middlename}}">
            </div>

            <div class="col-lg-12 form-group text-center">
                <label class="d-flex justify-content-start">Last Name</label>
                <input class="form-control text-dark" name="lastname" value="{{Auth::user()->lastname}}">
            </div>

            <div class="col-lg-12 form-group text-center">
                <label class="d-flex justify-content-start">Date of Birth</label>
                <input type="date" class="form-control text-dark" name="dob" value="{{Auth::user()->dob}}">
            </div>

            <div class="col-lg-12 form-group text-center">
                <label class="d-flex justify-content-start">Phone</label>
                <input class="form-control text-dark" placeholder="Enter phone number without country code" name="mobile" value="{{Auth::user()->mobile}}">
            </div>


            <hr>
            <p>{{ __("Address information") }}</p>
            <div class="row">

                <div class="col-lg-3 col-sm-6 form-group text-center">
                    <label class="d-flex justify-content-start">House No</label>
                    <input class="form-control text-dark" name="houseNumber" value="{{Auth::user()->houseNumber}}">
                </div>


                <div class="col-lg-9 col-sm-6 form-group text-center">
                    <label class="d-flex justify-content-start">Street</label>
                    <input class="form-control text-dark" name="line1" value="{{Auth::user()->line1}}">
                </div>



            </div>


            <div class="row">

                <div class="col-lg-6 col-sm-12 form-group text-center">
                    <label class="d-flex justify-content-start">City</label>
                    <input class="form-control text-dark" name="city" value="{{Auth::user()->city}}">
                </div>


                <div class="col-lg-6 col-sm-12 form-group text-center">
                    <label class="d-flex justify-content-start">State</label>
                    <input class="form-control text-dark" name="state" value="{{Auth::user()->state}}">
                </div>



            </div>


            <hr>
            <p>{{ __("Document information") }}</p>
            <div class="row">

                <div class="col-lg-12 col-sm-12 form-group text-center">
                    <label class="d-flex justify-content-start">Type</label>
                    <select class="form-control" name="doc_type" required>
                        <option value=""> Select Document Type </option>
                        <option value="NIN"> NIN CARD </option>
                        <option value="BVN"> BVN CARD </option>
                        <option value="PASSPORT"> PASSPORT DATA PAGE </option>
                    </select>
                </div>


                <div class="col-lg-12 col-sm-12 form-group text-center">
                    <label class="d-flex justify-content-start">Enter ID No(NIN, BVN or Passport No)</label>
                    <input type="text" class="form-control text-dark" name="doc_no" value="{{Auth::user()->doc_number}}">
                </div>

                <div class="col-lg-12 col-sm-12 form-group text-center">
                    <label class="d-flex justify-content-start">Upload Front Doc (PNG, JPEG allowed)</label>
                    <input type="file" class="form-control text-dark" name="doc_image" value="{{Auth::user()->doc_image}}">
                </div>

                <div class="col-lg-12 col-sm-12 form-group text-center">
                    <label class="d-flex justify-content-start">Upload Clear Selfie (PNG, JPEG allowed)</label>
                    <input type="file" class="form-control text-dark" name="selfie_image" value="{{Auth::user()->selfie}}">
                </div>
            </div>


            <div class="col-lg-12 form-group text-center">
                <button type="submit" class="btn--base w-100">{{ __("Update Information") }}</button>
            </div>
        </div>
    </form>


    @else

    <div class="pending text--warning kyc-text">{{ __("Your KYC information is submitted. Please wait for admin confirmation. When you are KYC verified you will show your submited information here.") }}</div>


@endif



