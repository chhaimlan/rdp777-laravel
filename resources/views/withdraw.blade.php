@extends('layouts.app2')
@section('content')

        <div class="setoran">
            <p class="tc pt-2">WITHDRAW</p> <h4 style="color: red; font-size: 16px;text-align: center;" id="pwderror">
                {{$fail_msg}}
            </h4>
            <form id="form2" method="post" action="{{ route('withdrawSb') }}">
                @csrf
            <div class="profile-2 barlow">
                <table>
                    <tr>
                        <td class="ss">Username  :</td>
                        <td><input class="st bg-black" type="text" name="username" class="username" value="{{ Auth::user()->show_name }}" readonly=""></td>
                    </tr>
                    <tr>
                        <td class="ss">Jumlah :</td>
                        <td>
                            <input class="st bg-black" type="text" placeholder="Masukkan Jumlah"  name="moneyValue" maxlength="9" id="moneyValue" oninput="value=value.replace(/[^\d]/g,'')">
                            <input type="hidden" id="blance" name="blance" value="{{ $blance }}"/>
                            <input type="hidden" id="min_withdraw" name="min_withdraw" value="{{ $min_withdraw }}"/>
                        </td>
                    </tr>
                    <tr>
                        <td class="ss">Nama Bank :</td>
                        <td><input class="st bg-black" type="text"  name="mebankerName" id="mebankerName" value="{{$mebankerName}}" readonly></td>
                    </tr>
                    <tr>
                        <td class="ss">Nama Rekening :</td>
                        <td><input class="st bg-black" type="text" name="meAccountName" id="meAccountName" value="{{$meAccountName}}" readonly></td>
                    </tr>
                    <tr>
                        <td class="ss">Nomor Rekening :</td>
                        <td><input class="st bg-black" type="text" name="meAccountNumber" id="meAccountNumber" value="{{$meAccountNumber}}" readonly></td>
                    </tr>
                </table>
                <div class="submit mt-1 tc">
                    <input type="button" onclick="check()"  value="Konfirmasi">
                </div>

            </div>
            </form>
        </div>
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
        $(function(){
            $('input').bind('input propertychange', function() {
                if($(this).attr("type")=="number"){
                    //获取输入框的最大长度
                    var mxaL= 20;
                    var numbers = $(this).val();
                    //如果输入的长度超过最大长度
                    if($(this).val().length>mxaL){
                        $(this).val($(this).val().slice(0,mxaL));
                    }
                    if(numbers.indexOf('-') == 0){
                        $(this).val($(this).val().slice(1,$(this).val().length));
                    }
                }
                    @if(!$fail_msg){
                    $('#pwderror').html('');
                }
                   @endif
            });
        });

        function check(){
            if(!$('#moneyValue').val()){
                $('#pwderror').html('Wrong amount');
                return false;
            }
            if(Number($('#moneyValue').val()) < 0 || Number($('#moneyValue').val()) === 0 ){
                $('#pwderror').html('Withdrawal is not allowed Amount 0');
                return false;
            }
            if(Number($('#moneyValue').val()) < Number($('#min_withdraw').val())){
                var minAmount = $('#min_withdraw').val();
                minAmount = parseFloat(minAmount).toFixed(0);
                minAmount = getQfw(minAmount);
                $('#pwderror').html('Minimal Penarikan '+ minAmount);
                return false;
            }
            if(Number($('#moneyValue').val()) > Number($('#blance').val())){
                $('#pwderror').html('Saldo anda tidak mencukupi');
                return false;
            }

            $('#form2').submit();
        }
        function reback(){
            window.history.go(-1)
        }

    </script>
@endsection
