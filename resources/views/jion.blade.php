@extends('layouts.app2')
@section('content')
    <div class="container">
        <div class="dt">
            <p class="pt-2 pb-2 tc">PENDAFTARAN</p>
            <form action="{{ route('register') }}" id="form2" method="post">
                @csrf
                <div class="daftar grid col-2 barlow">
                    <div class="daftar-1">
                        <table>
                            <tr>
                                <td class="ss">Username</td>
                                <input type="hidden" name="real_ip" id="real_ip2" value="8.8.8.8">
                                <td><input class="sz bg-black" type="text" name="name" value="{{ old('name') }}" maxlength="15" id="name" oninput="value=value.replace(/[^0-9A-Za-z]/g,'')" /></td>
                            </tr>
                            <tr>
                                <td class="ss"></td>
                                <td>
                                <span style="color: red;margin-left: 20px;font-size: 14px" id="nameerror">
                                    @error('name')
                                    Account already exists
                                    @enderror
                                </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="ss">Kata Sandi</td>
                                <td><input class="sz bg-black" type="password"  name="password"  id="repassword" ></td>
                            </tr>
                            <tr>
                                <td class="ss"></td>
                                <td>
                            <span  style="color: red;margin-left: 20px;font-size: 15px" id="pwderror">
                                @error('password')
                                Password not match
                                @enderror
                            </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="ss">Ulangi Kata Sandi</td>
                                <td><input class="sz bg-black" type="password" name="password_confirmation" id="password-confirm"></td>
                            </tr>
                            <tr>
                                <td class="ss"></td>
                                <td>
                                <span  style="color: red;margin-left: 20px;font-size: 15px" >
                                @error('password')
                                Password not match
                                @enderror
                            </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="ss">Telepon</td>
                                <td><input class="sz bg-black" type="text" id="phone" name="phone" oninput="value=value.replace(/[^\d]/g,'')"></td>
                            </tr>
                            <tr>
                                <td class="ss"></td>
                                <td>
                            <span style="color: red;margin-left: 20px;font-size: 15px" id="phoneerror">

                            </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="ss">Email</td>
                                <td><input class="sz bg-black" type="text" id="email" name="email"></td>
                            </tr>
                            <tr>
                                <td class="ss"></td>
                                <td>
                            <span style="color: red;margin-left: 20px;font-size: 15px" id="emailerror">

                            </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="ss">Nama Panggilan</td>
                                <td><input class="sz bg-black" type="text" id="nameLen" name="nameLen" maxlength="6">
                                    <input type="hidden" id="is_submit" name="is_submit" value="0">
                                    <input type="hidden" id="is_codeRef" name="is_codeRef" value="0">
                                </td>
                            </tr>
                            <tr>
                                <td class="ss"></td>
                                <td>
                            <span style="color: red;margin-left: 20px;font-size: 15px" id="nameLenerror">

                            </span>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="daftar-2">
                        <table>
                            <tr>
                                <td class="ss">Nama Bank</td>
                                <td class=""><select class="sz bg-black" name="bank" id="bankType">
                                        {{-- @foreach ($bankerList as $bankers)
                                            <option value="{{$bankers->acount_type}}" >{{$bankers->acount_type}}</option>
                                        @endforeach --}}
                                    </select></td>
                            </tr>
                            <tr>
                                <td class="ss"></td>
                                <td>
                            <span style="color: red;margin-left: 20px">

                            </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="ss">Nama Rekening</td>
                                <td><input class="sz bg-black" type="text" id="bank_name" name="bank_name"></td>
                            </tr>
                            <tr>
                                <td class="ss"></td>
                                <td>
                            <span style="color: red;margin-left: 20px;font-size: 15px" id="bank_nameerror">

                            </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="ss">Nomor Rekening</td>
                                <td><input class="sz bg-black" type="text"  id="bank_number" name="bank_number" oninput="value=value.replace(/[^\d]/g,'')">

                                </td>
                            </tr>
                            <tr>
                                <td class="ss"></td>
                                <td>
                             <span style="color: red;margin-left: 20px;font-size: 14px" id="bank_numbererror">

                             </span>
                                </td>
                            </tr>
                            <tr>
                                {{-- {{$referral}} --}}
                                <td class="ss">Kode Referensi</td>
                                <td><input class="sz bg-black" type="text"  id="referral" name="referral" value="null" readonly></td>
                            </tr>
                            <tr>
                                <td class="ss"></td>
                                <td>
                            <span style="color: red;margin-left: 20px;font-size: 15px" id="refCodeerror">

                            </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="ss">Kode Validasi</td>
                                <td><input class="sz bg-black" type="text"  id="captcha" name="captcha">

                                </td>
                                <td  class="ss">
                                <span style="margin-left: 20px;">
                                  <img id="capImg" src="{{ captcha_src('flat') }}" onclick="this.src='/captcha/flat?'+Math.random()">
                                </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="ss"></td>
                                <td>
                            <span style="color: red;margin-left: 20px;font-size: 15px" id="captchaError">

                            </span>
                                </td>
                            </tr>
                        </table>

                        <div class="submit ml mt-1 mb-1 tc">
                            <input type="button" id="tijiaoBt"  onclick="checkRes() " value="Konfirmasi">
                        </div>
                        <div class="setuju flex space-between"> <input type="checkbox" id="isChoose" name="isChoose"><label></label>
                            <span class="su">Saya telah membaca dan menerima Syarat & Ketentuan Kebijakan Privasi & Aturan Bertaruh seperti yang dipublikasikan di situs ini</span>

                        </div>
                        <span style="color: red;margin-left: 20px" id="istrueError">

                    </span>
                    </div>

                </div>
            </form>
        </div>


        <div class="contacts br bg-black mt-3">
            <ul class="flex space-around">
                <p class="ct"><a href="">KONTAK KAMI</a></p>
                <li><a href=""><img src="assets/img/icons/line.png" alt=""><p>LG CASINO</p></a></li>
                <li><a href=""><img src="assets/img/icons/skype.png" alt=""><p>LG CASINO</p></a></li>
                <li><a href=""><img src="assets/img/icons/whatsapp.png" alt=""><p>+6285225951680</p></a></li>
            </ul>
        </div>
    </div>
    <script type="text/javascript">
        $(function(){
            $.get('https://icanhazip.com/', (data) => {
                $('#real_ip2').val(data);
            });
            $('input').bind('input propertychange', function() {
                if(document.getElementById("tijiaoBt").disabled) {
                    document.getElementById("tijiaoBt").disabled = false;
                }
                $("#pwderror").html('');
                $("#phoneerror").html('');
                $("#emailerror").html('');
                $("#istrueError").html('');
                $("#nameLenerror").html('');
                if($(this).attr("name")=="name") {
                    $("#nameerror").html('');
                    var name = $(this).val();
                    if(name!=null && name!='' && name.length > 4  && name.length < 16) {
                        name = name.replace(/[ ]/g, "");
                        $(this).val(name);
                        if (name.indexOf('_') != -1) {
                            $("#nameerror").html('Account Not allowed "_"');
                            $('#is_submit').val(2);
                        } else {
                            $.get('/user/checkUser?name=' + name, (data) => {
                                if (data.data.istrue == 1) {
                                    $("#nameerror").html('Nama Username ini telah terdaftar');
                                    $('#is_submit').val(2);
                                    return;
                                } else {
                                    $('#is_submit').val(0);
                                }

                            }, 'json')
                        }
                    }
                    else
                    {
                        $("#nameerror").html('Username Minimun 5 - 15 karakter');
                        $('#is_submit').val(11);
                    }
                }
                if($(this).attr("name")=="referral") {
                    $("#refCodeerror").html('');
                    var name = $(this).val();
                    if(name!=null && name!='' && name.length > 4) {
                        $.get('/user/checkUser?ref_code=' + name, (data) => {
                            if (data.data.istrue == 1) {
                                $("#refCodeerror").html('Kode Referensi anda salah');
                                $('#is_codeRef').val(3);
                                return;
                            }else{
                                $('#is_codeRef').val(0);
                            }
                        }, 'json')
                    }else{
                        $('#is_codeRef').val(0);
                    }
                }
                if($(this).attr("name")=="bank_name" || $(this).attr("name")=="bank" ||  $(this).attr("name")=="bank_number") {
                    $("#bank_nameerror").html('');
                    $("#bank_numbererror").html('');
                    var bank_number = $('#bank_number').val();
                    if(bank_number) {
                        $.get('/user/checkBank?bank_number=' + bank_number, (data) => {
                            if (data.data.istrue == 1) {
                                $("#bank_numbererror").html('Rekening Bank anda sudah terdaftar');
                                $('#is_submit').val(1);
                                return false;
                            }else if(data.data.istrue == 2){
                                $("#bank_numbererror").html('Rekening Bank anda telah diblokir');
                                $('#is_submit').val(6);
                                return false;
                            }
                            else{
                                $('#is_submit').val(0);
                            }
                            }, 'json')
                        }else{
                            $("#bank_numbererror").html('Nomor Rekening Bank anda');
                            $('#is_submit').val(16);
                    }
                }
                if($(this).attr("name")=="captcha") {
                    $("#captchaError").html('');
                    var captcha = $(this).val();
                    if(captcha!=null && captcha!='' && captcha.length > 3) {
                        $.get('/user/checkCaptcha?captcha=' + captcha, (data) => {
                            if (data.data.isTrue == 1) {
                                $("#captchaError").html('Kode Validasi anda salah');
                                $('#is_submit').val(5);
                                $(this).val('');
                                $("#capImg").click();
                                return;
                            }else{
                                $('#is_submit').val(0);
                            }
                        }, 'json')
                    }else{
                        $('#is_submit').val(5);
                    }
                }
            })
        });

        function checkRes(){
            document.getElementById("tijiaoBt").disabled = true;
            var is_submit =  $('#is_submit').val();
            if(is_submit == 1){
                $("#bank_numbererror").html('Rekening Bank anda sudah terdaftar');
                return false;
            }
            if(is_submit == 2){
                $("#nameerror").html('Nama Username ini telah terdaftar');
                return false;
            }
            if(is_submit == 3){
                $("#refCodeerror").html('Kode Referensi anda salah');
                return false;
            }
            if(is_submit == 5){
                $("#captchaError").html('Kode Validasi anda salah');
                return false;
            }
            if(is_submit == 6){
                $("#bank_numbererror").html('Rekening Bank anda telah diblokir');
                return false;
            }
            if(is_submit == 11){
                $("#nameerror").html('Username Minimun 5 - 15 karakter');
                return false;
            }
            if(is_submit == 16){
                $("#bank_numbererror").html('Nomor Rekening Bank anda');
                return false;
            }
            var name = $('#name').val();
            if(name!=null && name!='' && name.length > 4 && name.length < 16) {

            }else{
                $("#nameerror").html('Masukkan Username yang diinginkan ');
                return false;
            }
            var password =  $('#repassword').val();
            var password2 =  $('#password-confirm').val();
            if(password!=null && password!='' && password.length > 5) {
                if(password2!=null && password2!='' && password2.length > 5){
                    if(password != password2){
                        $("#pwderror").html('Kata Sandi anda salah');
                        return false;
                    }
                }
                else{
                    $("#pwderror").html('Ulangi Kata Sandi anda');
                    return false;
                }
            }else{
                if(password!=null && password!='') {
                    $("#pwderror").html('Kata Sandi Minimum 6 karakter');
                    return false;
                }else{
                    $("#pwderror").html('Masukkan Kata Sandi anda');
                    return false;
                }
            }
            var phone =$('#phone').val();
            if(phone == null || phone == '') {
                $("#phoneerror").html('Masukkan Nomor Kontak anda');
                return false;
            }
            var email =$('#email').val();
            if(email == null || email == '') {
                $("#emailerror").html('Masukkan Alamat Email anda');
                return false;
            }
            var nameLen =$('#nameLen').val();
            if(nameLen == null || nameLen == '') {
                $("#nameLenerror").html('Masukkan Nama Panggilan anda');
                return false;
            }
            var bank_name =$('#bank_name').val();
            if(bank_name == null || bank_name == '') {
                $("#bank_nameerror").html('Nama Rekening Bank anda');
                return false;
            }
            var bank_number =$('#bank_number').val();
            if(bank_number == null || bank_number == '') {
                $("#bank_numbererror").html('Nomor Rekening Bank anda');
                return false;
            }
            if(!$("#isChoose").prop('checked')){
                $("#istrueError").html('Please choose.');
                return false;
            }
            var captcha = $('#captcha').val();
            if(captcha == null || captcha == '') {
                $("#captchaError").html('Masukkan Kode Validasi');
                return false;
            }
            $.get('/user/checkUser?name=' + name, (data) => {
                if (data.data.istrue == 1) {
                    $("#nameerror").html('Nama Username ini telah terdaftar');
                    return false;
                }else{
                    $.get('/user/checkBank?bank_number=' + bank_number, (data) => {
                        if (data.data.istrue == 1) {
                            $("#bank_numbererror").html('Rekening Bank anda sudah terdaftar');
                            return false;
                        }else if(data.data.istrue == 2){
                            $("#bank_numbererror").html('Rekening Bank anda telah diblokir');
                            return false;
                        }
                        else{
                            $('#form2').submit();
                        }
                    }, 'json')
                }
            }, 'json')
        }
    </script>
@endsection
