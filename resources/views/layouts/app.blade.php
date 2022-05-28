<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/png" href="">
    <!-- Start WOWSlider.com HEAD section -->
    <link rel="stylesheet" type="text/css" href="{{asset('assets/engine1/style.css')}}"/>
    <script type="text/javascript" src="{{asset('assets/engine1/jquery.js')}}"></script>
    <!-- End WOWSlider.com HEAD section -->
    <link rel="stylesheet" href="{{asset('assets/css/style.css')}}">
    <script src="https://pv.sohu.com/cityjson" page="login"></script>
    <title>DewaLG: Situs Judi Online Casino Bola Slot Online Terpercaya</title>
<meta name="description" content="DewaLG merupakan situs judi online dengan permainan casino online, bola online, slot online, sabung ayam, dan poker gacor serta terlengkap di Indonesia." />
<meta name="keywords" content="dewalg, dewa lg, judi online, situs judi online, casino online, bola online, slot online, sabung ayam" />
    <meta name="google-site-verification" content="PoG6YujJW2Ck6YAf4L6fC5Kj9RHCgXpFBY8g2_5bzdA" />
    <script type="text/javascript">
       function f_check_IP(ip){  
            var re=/^(\d+)\.(\d+)\.(\d+)\.(\d+)$/;//正则表达式   
            if(re.test(ip))   
            {   
                if( RegExp.$1<256 && RegExp.$2<256 && RegExp.$3<256 && RegExp.$4<256) 
                {
                    return true; 
                }  
            }     
            return false;    
        }

        var domain = document.domain;
        var protocol=document.location.protocol;
        var domianname=document.domain.split('.').slice(-2).join('.');
        if(protocol==="https:"){
            if(f_check_IP(domain))
            {
            }
            else
            {
                if(domain.search("www")!=-1){
                }
                else
                {
                    var url="https://www."+domain;
                    //window.location.replace(url);
                }
            }
        }
        if(protocol==="http:"){
            if(f_check_IP(domain))
            {
                var url="https://"+domain;
                //window.location.replace(url);
                
            }
            else{
                if(domain.search("www")!=-1){
                    var url="https://"+domain
                    //window.location.replace(url);
                }
                else
                {
                    var url="https://www."+domain;
                    //window.location.replace(url);
                }
            }
        }
        
        var h5url="https://wap."+domianname;
        if(f_check_IP(domain))
        {
            h5url="https://"+domain+"/m";
           // console.log("test");
        }

        //console.log(domain);
        //console.log(h5url);
        
        if (/(iPhone|iPad|iPod|iOS)/i.test(navigator.userAgent)) {
            //window.location.href ="http://54.251.83.21:8161/";
            window.location.href =h5url;
        } else if (/(Android)/i.test(navigator.userAgent)) {
            //window.location.href ="http://54.251.83.21:8161/";
            window.location.href =h5url;
        }
        //console.log(h5url);
        $(function () {
            // 调用 ip
            var real_ip = returnCitySN.cip;
            $('#real_ip').val(real_ip);
        });
    </script>
</head>
<body>

<div class="container-fluid bg-black">
    @auth
    <div class="container form klavika pt-1 pb-1 flex-end">
        <div class="member">
            <ul class="flex-center">
                <li class="welcome"><a href="{{ route('person') }}"><p>Selamat Datang,<span class="user">{{ Auth::user()->show_name }}</span></a><span class="line-1">|</span></li>
                <li><p><a href="">Saldo (IDR) :<span class="depo" id="user-balance"></span></a><span class="line-1">|</span></p></li>
                <li><p><a href="{{ route('deposit') }}">Setoran</a><span class="line-1">|</span></p></li>
                <li><p><a href="{{ route('withdraw') }}">Penarikan</a><span class="line-1">|</span></p></li>
                <li><p><a href="{{ route('reportList') }}">Laporan</a><span class="line-1">|</span></p></li>
                <li><p><a href="{{ route('dw-list') }}">Riwayat</a><span class="line-1">|</span></p></li>
                <li><p><a href="{{ route('refList') }}">Referensi</a><span class="line-1">|</span></p></li>
                <li><p><a href="{{ route('msglist') }}">Memo</a><span class="line-1">|</span></p></li>
                <li><p><a href="{{ route('password.confirm') }}">Kata Sandi</a><span class="line-1">|</span></p></li>
                <li><p><a href="{{ route('game-logout') }}">Keluar</a></p></li>
            </ul>
        </div>
    </div>
    @else
    <div class="container form pt-1 pb-1 flex-end">     
        <form action="{{ route('login') }}" id="form" method="post" onsubmit="return check()">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="real_ip" id="real_ip" value="8.8.8.8">
        <input class="username bg-black" type="text" name="username" onfocus="qudiao()"  placeholder="Username" id="username">
        <input class="password bg-black" type="password" name="password" onfocus="qudiao()"   placeholder="Password" id="password">
        <a href="#"><input class="login bg-black klavika" type="submit" value="MASUK"></a>
        <a href="{{ route('register') }}"><input class="register bg-black klavika" type="button" value="DAFTAR"></a>
       
        @error('username')
        </br>
        <span id="errorUser"><i style="color: red">Username atau Kata Sandi anda salah</i></span>
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
       {{-- GOT TO RDP777 ADD NEW     --}}
        <a href="{{ route('rdpindex') }}"><input class="login bg-black klavika" type="submit" value="RDP77"></a>
        {{-- END RDP777 --}}
    </div>
 @endauth
</div>

<div class="container-fluid bg-gray main-menu">
    <div class="header container grid">
        <div class="logo">
            <img src="{{asset('assets/img/logo/logo.png')}}" alt="">
        </div>
        <div class="navigation">
            <ul class="menus flex space-between">
                <li class="menu" data-id="home"><a href="{{ route('index') }}"><img src="{{asset('assets/img/icons/home.png')}}" onmouseover="this.src='{{asset('assets/img/icons/home-hover.png')}}';" onmouseout="this.src='{{asset('assets/img/icons/home.png')}}'"; alt=""></a>
                </li>
                <li class="menu" data-id="1"><a href="{{ route('game') }}"><img src="{{asset('assets/img/icons/casino.png')}}" onmouseover="this.src='{{asset('assets/img/icons/casino-hover.png')}}';" onmouseout="this.src='{{asset('assets/img/icons/casino.png')}}'"; alt=""></a>
                </li>
                <li class="menu" data-id="2"><a href="{{ route('goSlot') }}"><img src="{{asset('assets/img/icons/slot.png')}}" onmouseover="this.src='{{asset('assets/img/icons/slot-hover.png')}}';" onmouseout="this.src='{{asset('assets/img/icons/slot.png')}}'"; alt=""></a>
                </li>
                <li class="menu" data-id="3"><a href="{{ route('sports')}}"><img src="{{asset('assets/img/icons/sports.png')}}" onmouseover="this.src='{{asset('assets/img/icons/sports-hover.png')}}';" onmouseout="this.src='{{asset('assets/img/icons/sports.png')}}'"; alt=""></a>
                </li>
                <li class="menu"><a href="{{ route('sabungAyam')}}"><img src="{{asset('assets/img/icons/sa1.png')}}" onmouseover="this.src='{{asset('assets/img/icons/sa1-hover.png')}}';" onmouseout="this.src='{{asset('assets/img/icons/sa1.png')}}'"; alt=""></a>
                </li>
                <li class="menu"><a href="{{ route('poker')}}"><img src="{{asset('assets/img/icons/poker.png')}}" onmouseover="this.src='{{asset('assets/img/icons/poker-hover.png')}}';" onmouseout="this.src='{{asset('assets/img/icons/poker.png')}}'"; alt=""></a>
                </li>
                <li class="menu"><a href="{{ route('bonus') }}"><img src="{{asset('assets/img/icons/bonus.png')}}" onmouseover="this.src='{{asset('assets/img/icons/bonus-hover.png')}}';" onmouseout="this.src='{{asset('assets/img/icons/bonus.png')}}'"; alt=""></a>
                </li>
            </ul>
        </div>
    </div>
</div>

<div class="container-fluid dropdown tc bg-black">
    <div class="container">

        <div class="games grid col-6 klavika" data-id="1" style="display: none;">
            <div class="game"><a href="{{ route('play') }}"><img src="{{asset('assets/img/logo/lg-casino.png')}}" alt=""></a><p>LG88</p>
            </div>

        </div>

        <div class="games grid col-6 klavika" data-id="2" style="display: none;">
            <div class="game" id="showpp" ><a href="{{ route('slot') }}"><img src="{{asset('assets/img/logo/pragmatic.png')}}" alt=""><p>Pragmatic Play</p></a>
            </div>
            <div class="game" id="showhb"><a href="{{ route('hbslot') }}"><img src="{{asset('assets/img/logo/habanero.png')}}" alt=""><p>Habanero Play</p></a>
            </div>
            <div class="game" id="showpg"><a href="{{ route('pgslot') }}"><img src="{{asset('assets/img/logo/pg-logo.png')}}" alt=""><p>PG SLOTS</p></a>
            </div>
        </div>

    <div class="games grid col-6 klavika" data-id="3" style="display: none;">
{{--            <div class="game"><a href=""><img src="{{asset('assets/img/logo/united-gaming.png')}}" alt=""><p>United Gaming</p></a>--}}
{{--            </div>--}}
{{--            <div class="game"><a href=""><img src="{{asset('assets/img/logo/m8bet.png')}}" alt=""><p>M8Sport</p></a>--}}
{{--            </div>--}}
           <div class="game"><a onclick="getAfbSport()"><img src="{{asset('assets/img/logo/afb-1.png')}}" alt=""><p>AFB1188</p></a>
           </div>
{{--            <div class="game"><a href=""><img src="{{asset('assets/img/logo/m8bet.png')}}" alt=""><p>M8Sport CW</p></a>--}}
{{--            </div>--}}
{{--            <div class="game"><a href=""><img src="{{asset('assets/img/logo/d-sport.png')}}" alt=""><p>D-Sport</p></a>--}}
{{--            </div>--}}
        </div>

        <div class="games grid col-6 klavika" data-id="4" style="display: none;">
            <div class="game"><a href=""><img src="{{asset('assets/img/logo/sv388.png')}}" alt=""><p>SV388</p></a>
            </div>
        </div>

        <div class="games grid col-6 klavika" data-id="5" style="display: none;">
            <div class="game"><a href=""><img src="{{asset('assets/img/logo/pkv.png')}}" alt=""><p>SV388</p></a>
            </div>
        </div>

    </div>
</div>

@yield('content')

<div class="container-fluid bg-black mt-3">
    <div class="container">
        <div class="about">
            <ul class="flex-center">
                <li><p><a href="">Tentang DEWALG</a><span class="line">|</span></p></li>
                <li><p><a href="">Responsible Gambling</a><span class="line">|</span></p></li>
                <li><p><a href="">Syarat dan Ketentuan</a><span class="line">|</span></p></li>
                <li><p><a href="">Pusat Bantuan</a></p></li>
            </ul>
        </div>

        <div class="wording fg tc">
          <h1 style="padding-bottom:1px;">DewaLG Situs Judi Online dan Casino Online Terpercaya Indonesia</h1>
            <p>DewaLG ialah salah satu tempat bermain judi online yang terbilang sudah terpercaya sejak beberapa tahun lalu. Disini kalian bisa menikmati banyak sekali permainan menarik seperti casino online, slot game, bola online, sabung ayam, serta poker. Permainan tersebut terbilang sudah terkenal di Indonesia dan sering dimainkan oleh warga Indonesia. Dengan menggunakan fitur canggih yang dipastikan dapat memberikan sistem permainan yang lebih nyaman serta cepat.</p>
            <h2 style="padding-bottom:1px;">Situs Bola Online dengan Permainan Slot Online Gacor Winrate Tertinggi</h2>
			<p>Dengan bermain disini, kalian akan dipastikan untuk mendapatkan keamanan berkualitas dan permainannya yang 100% adil. Kalian harus mencoba pada permainan bola online ataupun slot online yang dibilang permainan populer di DewaLG. Kami juga menyediakan aplikasi super ringan yang cocok di semua jenis smartphone yang kalian gunakan. Kami akan menjamin bahwa chip kalian tidak akan keboboloan atau dicuri oleh hacker</p>
			<h3 style="padding-bottom:1px;">Situs Casino Online &amp; Slot Online Transaksi Tercepat di Indonesia</h3>
            <p>DewaLG menerima berbagai macam jenis transaksi dari virtual akun, bank lokal, dan juga pulsa selama 24 jam. Jenis transaksi virtual akun yang diterima seperti ovo, gopay, dana, dan linkaja dengan minimal deposit yang minimum. Juga menerima pulsa telkomsel dengan rate tertinggi yang pastinya membuat kalian lebih senang bermain di DewaLG. Kami jamin bahwa transaksi yang kami berikan ini lebih cepat dan aman yang akan membantu performa permainan kalian lebih lancar.</p>
            <p>Kami juga memberikan pelayanan terbaik yang online 24 jam dan siap membantu kalian yang mengalami berbagai masalah saat bermain di DewaLG. Setiap customer service kami terlatih dan dapat dipastikan bisa berbicara menggunakan bahasa indonesia yang benar dan santun. Kami yakin bahwa situs kami inilah yang cocok dan terbaik untuk kalian coba mainkan setiap harinya. Apabila kalian ingin mencoba bermain disini, silahkan di klik menu daftar diatas untuk melakukan pembuatan user id.</p>
        </div>

        <div class="footer klavika flex space-between">
            <div class="logo-footer">
                <p>SERTIFICATE :</p>
                <img src="{{asset('assets/img/logo/logo-footer.png')}}" alt="">
            </div>
            <div class="bank">
                <p>BANK SUPPORT :</p>
                <div class="banks grid col-5">
                    <div class="bank">
                        <img src="{{asset('assets/img/bank/green.png')}}" alt="">
                        <img src="{{asset('assets/img/bank/bca.png')}}" alt="">
                    </div>
                    <div class="bank">
                        <img src="{{asset('assets/img/bank/green.png')}}" alt="">
                        <img src="{{asset('assets/img/bank/bri.png')}}" alt="">
                    </div>
                    <div class="bank">
                        <img src="{{asset('assets/img/bank/green.png')}}" alt="">
                        <img src="{{asset('assets/img/bank/mandiri.png')}}" alt="">
                    </div>
                    <div class="bank">
                        <img src="{{asset('assets/img/bank/green.png')}}" alt="">
                        <img src="{{asset('assets/img/bank/bni.png')}}" alt="">
                    </div>
                    <div class="bank">
                        <img src="{{asset('assets/img/bank/red.png')}}" alt="">
                        <img src="{{asset('assets/img/bank/danamon.png')}}" alt="">
                    </div>
                </div>
            </div>
        </div>

        <div class="text-footer tc fg">
            <p>Copyright © 2021-2022 LG DEWALG Online Game. All rights reserved.</p>
        </div>

    </div>
</div>
 <script>
    $(document).ready(function() {
        let n = !1;
        $(".main-menu li[data-id]").mouseenter(function() {
            n = !0;
            $(".games").hide();
            $('.games[data-id="' + $(this).data("id") + '"]').show()
        }).mouseleave(function() {
            n = !1;
            setTimeout(function() {
                n || $(".games").hide()
            }, 25)
        });
        $(".games").mouseenter(function() {
            n = !0
        }).mouseleave(function() {
            n = !1;
            $(this).hide()
        })
    })
    function qudiao(){
        $('#errorUser').hide();
    }
</script>
<script type="text/javascript">
    $(function() {
        @auth
        var stop = 0;
        var token = "{{Auth::user()->token}}";
        var fb = "{{Auth::user()->point}}";
        fb = Number(fb).toFixed(2);
        fb = fb.toString().replace(/\d+/, function(n){ // 先提取整数部分
            return n.replace(/(\d)(?=(\d{3})+$)/g,function($1){
                return $1+",";
            });
        })
        $('#user-balance').text(fb)
        if($('#personMw').text()) {
            $('#personMw').text(fb)
        }
        setInterval(()=>{
                if(stop === 0) {
                    $.get('/user/getBalance?token='+token, (data) => {
                        if (data.code === 1) {
                            stop = 1;
                            window.location.href = "game/logout";
                        }
                        if (data.code === 2) {
                            window.location.href = "play";
                        }
                         var balance = data.data.balance;
                         balance = Number(balance).toFixed(2);
                         balance = balance.toString().replace(/\d+/, function(n){ // 先提取整数部分
                            return n.replace(/(\d)(?=(\d{3})+$)/g,function($1){
                                return $1+",";
                            });
                         })
                        $('#user-balance').text(balance)
                        if($('#personMw').text()) {
                            $('#personMw').text(balance)
                        }
                    }, 'json').error(function () {
                            stop = 1;
                            window.location.href = "home";
                        }
                    )
                }
            },2000
        );
        @endauth
        $.get('/getCustomer', (data) => {
            if (data.code === 1) {
                getLiveChat(data.url);
                $('#liveshow').show();
                document.getElementById("livehref").href="https://direct.lc.chat/"+data.url+"/";
            }
            if (data.code === 2) {
                getTwo(data.url);
            }
            if (data.code === 3) {
                getZe(data.url);
            }
            if(data.ispp === 1){
                $("#showpp").show();
                $('#ppdetail').show();
            }else{
                $('#ppdetail').hide();
            }
        }, 'json').error(function () {

            }
        )
    });

    function getTwo(id){
        var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
        (function(){
            var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
            s1.async=true;
            s1.src='https://embed.tawk.to/'+id+'/default';
            s1.charset='UTF-8';
            s1.setAttribute('crossorigin','*');
            s0.parentNode.insertBefore(s1,s0);
        })();
    }
    function getZe(id){
        window.$zopim||(function(d,s){var z=$zopim=function(c){z._.push(c)},$=z.s=
            d.createElement(s),e=d.getElementsByTagName(s)[0];z.set=function(o){z.set.
        _.push(o)};z._=[];z.set._=[];$.async=!0;$.setAttribute("charset","utf-8");
            $.src="//v2.zopim.com/?"+id;z.t=+new Date;$.
                type="text/javascript";e.parentNode.insertBefore($,e)})(document,"script");
    }
    //前端通过ajax获取客服信息
    function getLiveChat(id) {
        window.__lc = window.__lc || {};
        window.__lc.license = id;
        ;(function (n, t, c) {
            function i(n) {
                return e._h ? e._h.apply(null, n) : e._q.push(n)
            }

            var e = {
                _q: [], _h: null, _v: "2.0", on: function () {
                    i(["on", c.call(arguments)])
                }, once: function () {
                    i(["once", c.call(arguments)])
                }, off: function () {
                    i(["off", c.call(arguments)])
                }, get: function () {
                    if (!e._h) throw new Error("[LiveChatWidget] You can't use getters before load.");
                    return i(["get", c.call(arguments)])
                }, call: function () {
                    i(["call", c.call(arguments)])
                }, init: function () {
                    var n = t.createElement("script");
                    n.async = !0, n.type = "text/javascript", n.src = "https://cdn.livechatinc.com/tracking.js", t.head.appendChild(n)
                }
            };
            !n.__lc.asyncInit && e.init(), n.LiveChatWidget = n.LiveChatWidget || e
        }(window, document, [].slice))
    }

    function getQfw(obj){
       var fb = obj.toString().replace(/\d+/, function(n){ // 先提取整数部分
            return n.replace(/(\d)(?=(\d{3})+$)/g,function($1){
                return $1+",";
            });
        })
        return fb;
    }

    function getAfbSport(){
        @auth
        $.get('/afbsport', (data) => {
            if (data.code === 1) {
                window.open(data.url);
            }
        }, 'json').error(function () {

            }
        )
        @else
            window.location.href = "sports";
        @endauth

    }
</script>
<noscript>
    <div id="liveshow" style="display: none">
       <a id="livehref" href="https://direct.lc.chat/13218531/" rel="nofollow">Chat with us</a>, powered by <a href="https://www.livechatinc.com/?welcome" rel="noopener nofollow" target="_blank">LiveChat</a>
    </div>
</noscript>

<!-- End of LiveChat code -->
</body>
</html>
