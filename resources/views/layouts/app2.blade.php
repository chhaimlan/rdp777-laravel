<!DOCTYPE html>
<html lang="id">
<head>   
    {{-- <link rel="stylesheet" href="{{asset('assets/css/style.css')}}">
    <script type="text/javascript" src="{{asset('assets/engine1/jquery.js')}}"></script> --}}
    {{-- rdp777 --}}
    {{-- <title>The Title</title> --}}
    <title>LG88CASINO</title>
    {{-- path of real_ip = returnCity.zip --}}
    <script src="https://pv.sohu.com/cityjson" page="login"></script>
    
	<meta name="description" content="AGEN CASINO TERBAIK">
	<meta name="keywords" content="poker">
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.0/css/all.css">
	<link rel="stylesheet" type="text/css" href="{{asset('rdp777/css/style.css')}}">
    {{-- script --}}
    <link rel="stylesheet" type="text/css" href="{{asset('rdp777/engine1/style.css')}}" />
	<script type="text/javascript" src="{{asset('rdp777/engine1/jquery.js')}}"></script>
   {{-- endscript --}}
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<link rel="preconnect" href="https://fonts.gstatic.com">
	<link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,300;0,700;1,300;1,700&family=Oswald:wght@300&display=swap" rel="stylesheet">

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
<div class="wrapper">
    <div class="header flex">           		
    @auth
 
        <div class="container form klavika pt-1 pb-1 flex-end">
            <div class="member">
              <ul class="flex-center">
                <li class="welcome"><a href="{{ route('person') }}"><p>Welcome,<span class="user">{{ Auth::user()->show_name }}</span></a><span class="line-1">|</span></li>
                <li><p><a href="">Balance (IDR) :<span class="depo" id="user-balance"></span></a><span class="line-1">|</span></p></li>
                <li><p><a href="{{ route('deposit') }}">Deposit</a><span class="line-1">|</span></p></li>
                <li><p><a href="{{ route('withdraw') }}">Withdrawal</a><span class="line-1">|</span></p></li>
                <li><p><a href="{{ route('reportList') }}">Report</a><span class="line-1">|</span></p></li>
                <li><p><a href="{{ route('dw-list') }}">History</a><span class="line-1">|</span></p></li>
                <li><p><a href="{{ route('refList') }}">References</a><span class="line-1">|</span></p></li>
                <li><p><a href="{{ route('msglist') }}">Memo</a><span class="line-1">|</span></p></li>
                <li><p><a href="{{ route('password.confirm') }}">Password</a><span class="line-1">|</span></p></li>
                <li><p><a href="{{ route('game-logout') }}">Go Out</a></p></li>
              </ul>
          </div>
        </div>
     <div class="logo"><a href="#"><img src="{{asset('rdp777/css/img/logo.png')}}"></a></div>
    @else
     <div class="logo"><a href="#"><img src="{{asset('rdp777/css/img/logo.png')}}"></a></div>
			<div class="headright">				
                <form class="form flex" action="{{ route('login') }}" id="form" method="post" onsubmit="return check()">
	               <input type="hidden" name="_token" value="{{ csrf_token() }}">
                   <input type="hidden" name="real_ip" id="real_ip" value="8.8.8.8">
                   <input class="username" type="text" name="username" onfocus="qudiao()"  placeholder="Username" id="username">
                   <input class="password" type="password" name="password" onfocus="qudiao()"   placeholder="Password" id="password">

					<input type="text" name="CaptchaInput" id="CaptchaInput" onfocus="qudiao()" placeholder="kode" class="@error('CaptchaInput')
                        is-invalid
                      @enderror">
					<div class="captcha" id="CaptchaDiv"></div>
					<input type="hidden" id="txtCaptcha">
					 <a href="#"><button type="submit" class="color3 button"><i class="fas fa-user-lock"></i> Login</button></a>
                    {{-- <button type="button" class="color2 button register-button"><i class="fas fa-user-edit"></i>Daftar</button> --}}
					<a href="{{ route('register') }}"><button type="button" class="color2 button register-button"><i class="fas fa-user-edit"></i>Daftar</button>
                    
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
                      @error('CaptchaInput')
                      </br>
                      <span id="errorUser"><i style="color: red">Please check feild code!!!</i></span>
                      @enderror                                       
		         </form>
				<script type="text/javascript" src="{{asset('rdp777/script/captcha.js')}}"></script>
	        </div>
        @endauth
			<div class="wrap-collabsible color2">
				<input id="collapsible" class="toggle" type="checkbox">
					<label for="collapsible" class="lbl-toggle color-2">Menu</label>
						<div class="collapsible-content">
							<div class="content-inner">
								<div class="nav">
								    <ul class="navbar flex">
                                    
											<li><a href="{{ route('rdpindex') }}"><i class="fas fa-home"></i></a></li>
										    <li class="menudown"><a href="{{ route('sports') }}">SPORTSBOOK</a>
                                                <div class="dropdown flex flex-game">
                                                      <a onclick="getAfbSport()"><img src="{{asset('rdp777/css/img/game/sportsbook/afb88.png')}}" alt=""><h3>AFB1188</h3></a>
                                                      <a href="{{ route('play') }}"><img src="{{ asset('rdp777/css/img/game/sportsbook/tf.png')}}"><h3>LG88</h3></a>
													  <a href="{{ route('hbslot') }}"><img src="{{ asset('rdp777/css/img/game/sportsbook/sbo.png') }}"><h3>SV388</h3></a>
													  <a href="{{ route('pgslot') }}"><img src="{{ asset('rdp777/css/img/game/sportsbook/hracing.png') }}"><h3>SV388</h3></a>
                                                     
                                                </div>
                                            </li>                                                                           
											<li class="menudown"><a href="{{ route('game') }}">KASINO</a>
                                                <div class="dropdown flex flex-game">
                                                      <a href="{{ route('game') }}"><img src="{{ asset('rdp777/css/img/game/casino/casinotga.png')}}" alt=""><h3>CASINOTGA</h3></a>
                                                      <a href="{{ route('play') }}"><img src="{{ asset('rdp777/css/img/game/casino/gd88.png')}}"><h3>LG88</h3></a>
													  <a href="{{ route('hbslot') }}"><img src="{{ asset('rdp777/css/img/game/casino/sa.png') }}"><h3>SA88</h3></a>
													  <a href="{{ route('pgslot') }}"><img src="{{ asset('rdp777/css/img/game/casino/allbet.png') }}"><h3>ALL BET</h3></a>
                                                     
                                                </div>
                                            </li>
										    <li><a href=" {{ route('poker') }}">POKER</a></li>
											<li class="menudown"><a href="{{ route('goSlot') }}">slot</a>
												<div class="dropdown flex flex-game">
													<a href="{{ route('slot') }}"><img src="{{ asset('rdp777/css/img/game/slot/pp.png')}}"><h3>PRAGMATIC SLOTS</h3></a>
													<a href="{{ route('hbslot') }}"><img src="{{ asset('rdp777/css/img/game/slot/habanero.png') }}"><h3>HABA SLOTS</h3></a>
													<a href="{{ route('pgslot') }}"><img src="{{ asset('rdp777/css/img/game/slot/pg.png') }}"><h3>PG SLOTS</h3></a>
												</div>
											</li>
                                           
											<li><a href="{{ route('sabungAyam') }}">FISHING</a></li>
											<li><a href="{{ route('bonus') }}">PROMO</a></li>
											<li><a href=" {{ route('goSlot')}}">APP</a></li>
									</ul>
								</div>
							</div>
						</div>
			   </div>
		<div class="scrolltext flex">
			<div class="scrolltextleft"><i class="fas fa-bullhorn"></i> Info</div>
			<div class="scrolltextinside">
				<marquee>scroll text scroll text scroll text scroll text scroll text scroll text scroll text scroll text scroll text scroll text scroll text scroll text scroll text scroll text scroll text scroll text </marquee>
			</div>    
			<a href="{{ route('contact') }}" class="scrolltextright"><i class="fas fa-headset"></i> Livechat</a>
		</div>
	</div>
   
      @yield('content')
</div>    
		<div class="footer">
			Copyright © 2022.All rights reserved.
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
