@extends('layouts.app2')
@section('content')
        <form class="pass klavika" action="{{ route('password.update') }}" id="form2" method="post">
            @csrf
            <p class="tc mt-4">Username :  {{ Auth::user()->show_name }}</p>
            <div class="profile-2 barlow">
                <table>
                    <tr>
                        <td class="ss">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Kata Sandi Lama :</td>
                        <td><input class="st bg-black" type="password" placeholder="Masukkan Kata Sandi Lama" name="passwordold" id="passwordold" maxlength="15"></td>
                    </tr>
                    <tr>
                        <td class="ss"></td>
                        <td>
                            <span style="color: red; font-size: 15px;margin-left: 20px" id="oldpwderror">
                                 @error('email')
                                Kata Sandi Lama anda salah
                                @enderror
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="ss">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Kata Sandi Baru :</td>
                        <td><input class="st bg-black" type="password" placeholder="Masukkan Kata Sandi Baru Anda" name="password" id="repassword" maxlength="15"></td>
                    </tr>
                    <tr>
                        <td class="ss"></td>
                        <td>
                            <span style="color: red; font-size: 15px;margin-left: 20px" id="pwderror">

                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="ss">Konfirmasi Kata Sandi :</td>
                        <td><input class="st bg-black" type="password" placeholder="Konfirmasi Kata Sandi Baru Anda"  name="password_confirmation" id="password-confirm" maxlength="15"></td>
                    </tr>
                </table>
                <div class="submit mt-2 tc flex space-around">
                    <input type="button" name="check-confirm" onclick="check()" value="Konfirmasi">
                </div>
            </div>
          </form>
         <div class="contact flex">
				<a href="#"><i class="fab fa-whatsapp"></i><h3>Whtasapp</h3>1234567890</a>
				<a href="#"><i class="fab fa-facebook"></i><h3>Facebook</h3>1234567890</a>
				<a href="#"><i class="fab fa-instagram"></i><h3>Instagram</h3>1234567890</a>
				<a href="#"><i class="fab fa-telegram-plane"></i><h3>Telegram</h3>1234567890</a>
				<a href="#"><i class="fab fa-line"></i><h3>Line</h3>1234567890</a>
		</div>
		<div class="content">
			<h2>Title Here</h2>
			Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi
			ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui
			officia deserunt mollit anim id est laborum.
			<br>
			Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta
			sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem
			ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem
			ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat
			quo voluptas nulla pariatur?
       </div> 


        <script type="text/javascript">
            $('input').bind('input propertychange', function() {
                $("#oldpwderror").html('');
                $("#pwderror").html('');
            });
            function check(){
                var oldpwd = $('#passwordold').val();
                if(oldpwd === null || oldpwd === ''){
                    $("#oldpwderror").html('Kata Sandi Lama anda salah');
                    return false;
                }
                var password = $('#repassword').val();
                var password2 = $('#password-confirm').val();
                if(password!=null && password!='' && password.length > 5) {
                    if(password2!=null && password2!='' && password2.length > 5){
                        if(password != password2){
                            $("#pwderror").html('Kata Sandi baru anda salah');
                            return false;
                        }
                    }
                    else{
                        $("#pwderror").html('Kata Sandi baru anda salah');
                        return false;
                    }
                }else{
                    $("#pwderror").html('Kata Sandi baru anda salah');
                    return false;
                }
                $.get('/user/checkUser?oldpwd=' + oldpwd, (data) => {
                    if (data.data.istrue == 1) {
                        alert("Berhasil");
                        $('#form2').submit();
                    } else {
                        $("#oldpwderror").html('Kata Sandi Lama anda salah');
                    }

                }, 'json')
              //  alert("Berhasil");
            }
            function reback(){
                window.location.href = "{{ route('index') }}";
            }
        </script>
@endsection
