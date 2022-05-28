<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/style.css')}}">
    <link rel="shortcut icon" type="image/png" href="">
    <script src="{{asset('assets/js/jquery-3.5.1.min.js')}}"></script>
    <link rel="stylesheet" type="text/css" href="{{asset('assets/wowslider/style.css')}}" />
    <script src="{{asset('assets/bootstrap/bootstrap.min.js')}}"></script>
    <link href="{{asset('assets/bootstrap/bootstrap.min.css')}}" rel="stylesheet"/>
    <script src="https://pv.sohu.com/cityjson" page="login"></script>
    <title>LG88CASINO</title>
    <script type="text/javascript">
        $(function () {
            // 调用 ip
            var real_ip = returnCitySN.cip;
            $('#real_ip').val(real_ip);
        });
    </script>
</head>
<body>

<div class="container">
    <div class="row">
        <div class="col-4"></div>
        <div class="col-8 text-center pt-2 pb-4">
            @auth
                <span style="color: #eeb701">
               Welcome:
                <b  style="color: #ffffff;margin-left: 5px">{{ Auth::user()->sn }} </b>
               </span>
                <span style="color: #eeb701;margin-left: 20px">
                Balance:(IDR)
                <b style="color: #ffffff;margin-left: 5px" id="user-balance">{{ Auth::user()->point }} </b></span>
                <span style="margin-left: 20px">
                 <a href="{{ route('deposit') }}"  style="color: #eeb701">DEPOSIT</a>
                </span>
                <span>
                 <a href="{{ route('withdraw') }}"  style="color: #eeb701">WITHDRAW</a>
                </span>
                <span>
                <a href="{{ route('person') }}"  style="color: #eeb701">INFO</a>
               </span>
                <span>
                    <a href="{{ route('password.confirm') }}"  style="color: #eeb701">PASSWORD</a>
                </span>
                <span>
                         <a href="{{ route('game-logout') }}" style="color: #eeb701">LOGOUT</a>
                </span>
            </span>
            @else
                <form action="{{ route('login') }}" id="form" method="post" onsubmit="return check()">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="real_ip" id="real_ip" value="8.8.8.8">
                    <input type="text" name="username" onfocus="qudiao()"  placeholder="Username" id="username" />
                    <input type="password" name="password" onfocus="qudiao()"   placeholder="Password" id="password" />
                    <input type="submit" name="submit" value="LOGIN" class="login">
                    <input type="button" value="DAFTAR" class="daftar" onclick="location.href='{{ route('register') }}';">
                    @error('username')
                     </br>
                      <span id="errorUser"><i style="color: red">Invalid username or password</i></span>
                    @enderror
                    @error('userClosed')
                    </br>
                    <span id="errorUser"><i style="color: red">Wrong password several times, please contact our customer service</i></span>
                    @enderror
                    @error('userNot')
                    </br>
                    <span id="errorUser"><i style="color: red">User does not exist!</i></span>
                    @enderror
                </form>
            @endauth
        </div>
    </div>
</div>
<div class="bg-menu">
    <div class="container">
        <div class="row">
            <div class="col-4">
                <a href="">
                    <img src="{{asset('assets/img/logo.png')}}" alt="logo">
                </a>
            </div>
            <div class="col-8 menu">

                <div class="row p-0 m-0">
                    <div id="home" class="ml-5 col-2 text-center item-menu">
                        <a href="{{ route('index') }}">
                            <p class="m-0 p-0">
                                <img src="{{asset('assets/img/btn/home.png')}}" alt="home">
                            </p>
                            <p class="m-0 p-0">HOME</p>
                        </a>
                    </div>
                    <div  id="game" class="col-2 text-center item-menu">
                        <a href="{{ route('game') }}">
                            <p class="m-0 p-0">
                                <img src="{{asset('assets/img/btn/casino.png')}}" alt="casino">
                            </p>
                            <p class="m-0 p-0">CASINO</p>
                        </a>
                    </div>

                    <div  id="rule" class="col-2 text-center item-menu">
                        <a href="{{ route('gamerule') }}">
                            <p class="m-0 p-0">
                                <img src="{{asset('assets/img/btn/peraturan.png')}}" alt="peraturan">
                            </p>
                            <p class="m-0 p-0">PERATURAN</p>
                        </a>
                    </div>

                    <div id="bonus" class="col-2 text-center item-menu">
                        <a href="{{ route('bonus') }}">
                            <p class="m-0 p-0">
                                <img src="{{asset('assets/img/btn/bonus.png')}}" alt="bonus">
                            </p>
                            <p class="m-0 p-0">BONUS</p>
                        </a>
                    </div>

                </div>

            </div>
        </div>
    </div>
</div>
@yield('content')
<div class="bg-black">
    <div class="container">
        <div class="row py-3">
            <div class="col-6">
                <img src="{{asset('assets/img/gambling.png')}}" alt="responsible gambling">
            </div>
            <div class="col-6 text-right">
                Copyright LG88CASINO.COM. All rights reserved.
            </div>
        </div>
    </div>
</div>
</body>
<script>
    // if (/(iPhone|iPad|iPod|iOS)/i.test(navigator.userAgent)) {
    //     window.location.href ="https://wap.lg88casino.net/";
    // } else if (/(Android)/i.test(navigator.userAgent)) {
    //     window.location.href ="https://wap.lg88casino.net/";
    // }
    $(document).ready(function() {
        $('#myCarousel').carousel({
            interval: 10000
        })
    });
    $(document).ready(function() {
        $('#myCarousel1').carousel({
            interval: 10000
        })
    });
    function qudiao(){
        $('#errorUser').hide();
    }


</script>
</html>
