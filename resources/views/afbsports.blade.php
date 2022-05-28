@extends('layouts.app')
@section('content')
    <div class="carousel">
        <!-- Start WOWSlider.com BODY section -->
        <div id="wowslider-container1">
            <div class="ws_images">
                <ul>
                    <li><img src="assets/img/slider/sports.png" alt="javascript slider" title="" id="slider1" class="responsive" /></li>
                </ul>
            </div>

        </div>
        <script type="text/javascript" src="assets/engine1/wowslider.js"></script>
        <script type="text/javascript" src="assets/engine1/script.js"></script>
        <!-- End WOWSlider.com BODY section -->
    </div>

    <div class="container">
        <div class="play mt-4 grid col-6a">
            <a href="{{ route('afbsport') }}"><img src="assets/img/play/afb1.png"  width="214" height="261" alt=""></a>
            {{--            <a href=""><img src="assets/img/play/d-sports.png" alt=""></a>--}}
            {{--            <a href=""><img src="assets/img/play/m8-bet.png" alt=""></a>--}}
            {{--            <a href=""><img src="assets/img/play/ug.png" alt=""></a>--}}
        </div>

        <div class="container contacts br bg-black mt-3">

            <ul class="flex space-around">
                <p class="ct"><a href="">KONTAK KAMI</a></p>
                <li><a href=""><img src="assets/img/icons/line.png" alt=""><p>DewaLG</p></a></li>
                <li><a href=""><img src="assets/img/icons/telegram.png" alt=""><p>DewaLG</p></a></li>
                <li><a href=""><img src="assets/img/icons/whatsapp.png" alt=""><p>+081269200299</p></a></li>
            </ul>
        </div>
    </div>
    <script type="text/javascript">
       // window.open({{$url}},"PLAY AFB1188","height=800,width=800");
    </script>
@endsection

