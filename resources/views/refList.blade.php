@extends('layouts.app2')
@section('content')
    <p class="tc fs-2 pt-2">REFERENCES</p>
    <div class="referensi">
        <div class="bg-black flex space-between">
            <input class="sv bg-black" type="text" id="oldText" value="{{$jionUrl}}={{Auth::user()->ref_code}}" readonly="" autofocus required="">
            <div class="submit-1 tc">
                <input type="submit" onclick="copyText()" value="Salin">
            </div>
        </div>

        <div class="table-1 bg-black">
            <table>
                @foreach ($refList as $uList)
                <tr class="tc">
                    <td colspan="2"> {{ $uList->sn }}</td>
                    <td colspan="2">{{ $uList->created_at}}</td>
                </tr>
                @endforeach
            </table>
        </div>
        <div style="float: right;display: inline;">
            {{ $refList->links() }}
        </div>
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
        function copyText() {
            var input = document.getElementById("oldText");
            input.select(); // 选中文本
            document.execCommand("copy"); // 执行浏览器复制命令
           //alert("Copy Successfully");
        }
    </script>
@endsection
