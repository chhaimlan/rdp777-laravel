
@extends('layouts.app2')
@section('content')
    <div style="height:880px;width:100%">
    <iframe id="togame"
        title="fggame"
        width="100%"
        height="100%"
        allowtransparency="true"
        allow="fullscreen"
        src="{{$gameUrl}}/?t={{$token}}&u={{$username}}&l={{$lang}}" style="border:none;overflow:hidden;">
    </iframe>
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

@endsection
