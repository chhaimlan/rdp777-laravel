@extends('layouts.app2')
@section('content')
    <div class="container">
        <form id="form2" method="post" action="{{ route('depositSb') }}"  >
            @csrf
        <div class="setoran">
            <p class="tc pt-2">DEPOSIT</p>
            <h4 style="color: red; font-size: 16px;text-align: center;" id="amounterror">
                {{$fail_msg}}
            </h4>
            <div class="profile-2 barlow">
                <table>
                    <tr>
                        <td class="ss">Username :</td>
                        <td><input class="st bg-black" type="text" name="username" class="username" value="{{ Auth::user()->show_name }}"  readonly></td>
                    </tr>
                    <tr>
                        <td class="ss">Jumlah :</td>
                        <td><input class="st bg-black" type="text" placeholder="Masukkan Jumlah" name="moneyValue" id="moneyValue" maxlength="9" oninput="value=value.replace(/[^\d]/g,'')">
                        </td>

                    </tr>
                    <tr>
                        <td class="ss"></td>
                        <td>
                            <span style="color: red" id="showMoney"></span>
                        </td>
                    </tr>
                    <tr>
                        <td class="ss">Remark :</td>
                        <td><input class="st bg-black" type="text" name="remark" id="remark" maxlength="35"/> </td>
                    </tr>
                    <tr>
                        <td class="ss">Nama Bank :</td>
                        <td><input class="st bg-black" type="text" name="mebankerName" id="mebankerName" value="{{$mebankerName}}" readonly></td>
                    </tr>
                    <tr>
                        <td class="ss">Nama Rekening :</td>
                        <td><input class="st bg-black" type="text" id="meAccountName" name="meAccountName" value="{{$meAccountName}}" readonly></td>
                    </tr>
                    <tr>
                        <td class="ss">Nomor Rekening :</td>
                        <td><input class="st bg-black" type="text" id="meAccountNumber" name="meAccountNumber" value="{{$meAccountNumber}}" readonly></td>
                    </tr>
                    <tr>
                        <td class="ss">Nama Bank :</td>
                        <td class="">
                            <select name="cobankerNameId" id="cobankerName" onchange="selectBk()" class="stt bg-black">
                                @foreach ($bankerList as $bankers)
                                    <option value="{{$bankers->id}}"  @if($bankers->id === $banker->id) selected  @endif>{{$bankers->acount_type}}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="ss"></td>
                        <td>
                            <span style="color: red" id="showAmount"></span>
                        </td>
                    </tr>
                    <tr>
                        <td class="ss">Nama Rekening :</td>
                        <td><input class="st bg-black" type="text" name="coAccountName" id="coAccountName"  value="{{$banker->acount_name}}" readonly></td>
                    </tr>
                    <tr>
                        <td class="ss">Nomor Rekening :</td>
                        <td>
                            <input type="hidden" id="bankId" name="bankId" value="{{$banker->id}}"/>
                            <input type="hidden" id="minAmount" name="minAmount" value="{{$banker->min_amount}}"/>
                            <input type="hidden" id="cobName" name="cobankerName" value="{{$banker->acount_type}}"/>
                            <input type="hidden" id="category" name="category" value="{{$banker->category}}"/>
                            <input type="hidden" id="conversion" name="conversion" value="{{$banker->conversion}}"/>
                            <input class="st bg-black" type="text" name="coAccountNumber" id="coAccountNumber" value="{{$banker->acount_number}}" readonly>
                        </td>
                    </tr>
                    <tr>
                        <td class="ss"></td>
                        <td>
                            <span style="color: red" id="showErrorBank"></span>
                        </td>
                    </tr>
                </table>
                <div class="submit mt-1 tc">
                    <input type="button" onclick="check()" value="Konfirmasi">
                </div>

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
                    $('#amounterror').html('')
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
                if($(this).attr("name")=="moneyValue") {
                    $('#amounterror').html('');
                    var moneyValue = $(this).val();
                    var category = $('#category').val();
                    if(category == 'CELLULAR'){
                        var conversion = $('#conversion').val();
                        var tishi = getQfw(moneyValue * conversion/100);
                        $('#showMoney').html('Actual Amount:'+tishi);
                    }else{
                        $('#showMoney').html('');
                    }
                }
            });
            var category = $('#category').val();
            var conversion = $('#conversion').val();
            var moneyValue = $('#moneyValue').val()
            if(category == 'CELLULAR'){
                $('#showAmount').html('Konversi:'+conversion+'%');
                var tishi = getQfw(moneyValue * conversion/100);
                $('#showMoney').html('Hasil Konversi:'+tishi);
            }else{
                $('#showAmount').html('');
                $('#showMoney').html('');
            }
        });

        function check(){
            var minAmount = $('#minAmount').val();
            minAmount = parseFloat(minAmount).toFixed(0);
            if(!$('#moneyValue').val()){
                minAmount = getQfw(minAmount);
                $('#amounterror').html('Minimal Deposit ' +minAmount);
                return false;
            }
            if(parseFloat($('#moneyValue').val()) < parseFloat(minAmount)){
                minAmount = getQfw(minAmount);
                $('#amounterror').html('Minimal Deposit ' +minAmount);
                return false;
            }
            $bankId = $('#bankId').val();
            $.get('/user/checkComBank?bankId=' + $bankId, (data) => {
                if (data.data.isTrue == 1) {
                    $('#showErrorBank').html('Nomor Rekening ini sedang tidak aktif');
                    return false;
                }else{
                    $('#showErrorBank').html('');
                   $('#form2').submit();
                }
            }, 'json')
        }
        function selectBk(){
            $('#showErrorBank').html('');
            var cobankerNameId = $('#cobankerName').val();
            var bklist = @json($bankerList);
            var moneyValue = $('#moneyValue').val()
            bklist.forEach(bank=>{
                if(bank.id == cobankerNameId){
                    $('#cobName').val(bank.acount_type);
                    $('#coAccountName').val(bank.acount_name);
                    $('#coAccountNumber').val(bank.acount_number);
                    $('#bankId').val(bank.id);
                    $('#minAmount').val(bank.min_amount)
                    $('#category').val(bank.category)
                    $('#conversion').val(bank.conversion)
                    if(bank.category == 'CELLULAR'){
                        $('#showAmount').html('Konversi:'+bank.conversion+'%');
                        var tishi = getQfw(moneyValue * bank.conversion/100);
                        $('#showMoney').html('Hasil Konversi:'+tishi);
                    }else{
                        $('#showAmount').html('');
                        $('#showMoney').html('');
                    }
                }
            });
        }
        function reback(){
              window.history.go(-1)
        }

    </script>
@endsection
