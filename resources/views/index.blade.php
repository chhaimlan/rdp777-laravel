@extends('layouts.app2')
@section('content')
    <div class="carousel">
        <!-- Start WOWSlider.com BODY section -->
        <div id="wowslider-container1">
            <div class="ws_images mt-2"> 
                <ul>
                    <li><img src="assets/img/banner/b1.jpg" alt="javascript slider" title="" id="slider1" class="responsive" /></li>
                    <li><img src="assets/img/banner/b2.jpg" alt="javascript slider" title="" id="slider2" class="responsive" /></li>
                    <li><img src="assets/img/banner/b3.jpg" alt="javascript slider" title="" id="slider3" class="responsive"/></li>
                    <li><img src="assets/img/banner/b2.jpg" alt="javascript slider" title="" id="slider4" class="responsive"/></li>
                </ul>
            </div>
            {{-- icons scroll below img --}}
            <div class="ws_bullets"><div>
                    <a href="#" title="Casino"><span>1</span></a>
                    <a href="#" title="Slot"><span>2</span></a>
                    <a href="#" title="Sports"><span>3</span></a>
                    <a href="#" title="Poker"><span>4</span></a>
                </div></div><div class="ws_script" style="position:absolute;left:-99%"></div>
            <div class="ws_shadow"></div>
        </div>
        <script type="text/javascript" src="assets/engine1/wowslider.js"></script>
        <script type="text/javascript" src="assets/engine1/script.js"></script>
        <!-- End WOWSlider.com BODY section -->
    </div>
    <div class="scrolltext-index br flex mt-2">
			<div class="running-text"><span class="date">{{date("F j, Y, g:i a")}}</span></div>
            <img src="assets/img/icons/info.png" alt="">
			<div class="scrolltextinside">
				<marquee scrolldelay="100" onmouseover="this.stop();" onmouseout="this.start();" id="noticeHtml">Welcome to the LGCASINO Website</marquee>
			</div>    
		</div>
   
   <div class="contact flex">
				<a href="#"><i class="fab fa-whatsapp"></i><h3>Whtasapp</h3>1234567890</a>
				<a href="#"><i class="fab fa-facebook"></i><h3>Facebook</h3>1234567890</a>
				<a href="#"><i class="fab fa-instagram"></i><h3>Instagram</h3>1234567890</a>
				<a href="#"><i class="fab fa-telegram-plane"></i><h3>Telegram</h3>1234567890</a>
				<a href="#"><i class="fab fa-line"></i><h3>Line</h3>1234567890</a>
		</div>
		<div class="game-category flex">
			<a href="#"><img src="rdp777/css/img/sportsbook.jpg"></a>
			<a href="#"><img src="rdp777/css/img/live-casino.jpg"></a>
			<a href="#"><img src="rdp777/css/img/slot.jpg"></a>
			<a href="#"><img src="rdp777/css/img/poker.jpg"></a>
			<a href="#"><img src="rdp777/css/img/promotion.jpg"></a>
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
    <script>
        function milliFormat(num) {
            return num && num.toString()
            .replace(/\d+/, function(s){
             return s.replace(/(\d)(?=(\d{3})+$)/g, '$1,')
            })
        }
        $.get('/api/newList',(data)=>{
            if(data.data.notices) {
                var str;
                for (var i = 0; i < data.data.notices.length; i++) {
                    var name = data.data.notices[i].notice_name;
                    var content = data.data.notices[i].content;
                    var contents = [content];
                    if (content.indexOf(',') > 0) {
                        contents = content.split(',');
                    }
                    var number = i+1;
                    if(i===0) {
                        str = content;
                    }else{
                        str = str + '&nbsp;&nbsp;&nbsp;' + content;
                    }
                }
                $('#noticeHtml').html(str);
            }
        },'json');
        $.get('/getTransferShow?type=1',(data)=>{
            var html = "<tr> <th colspan=\"3\">PENARIKAN DANA</th> </tr>";
            if(data.items) {
                for (var i = 0; i < data.items.length; i++) {
                    html += "<tr><td class=\"fg\">"+data.items[i].username+"</td>"
                        + "<td class=\"fg\">"+data.items[i].date_time+"</td>"
                        + "<td>"+milliFormat(data.items[i].amount)+"</td> </tr>"
                }
                $('#widthShow').html(html);
            }
        },'json');
        $.get('/getSLotWin',(data)=>{
            var html = "<p class=\"win klavika\">PEMENANG SLOT</p>"
            if(data.items) {
                for (var i = 0; i < data.items.length; i++) {
                    html += "<div class=\"winners br grid bg-gray\" > "
                        + "<div class=\"win-1\">"
                        + " <a href><img width=\"90\" height=\"90\" src="+data.items[i].pic+"></a></div>"
                        + " <div class=\"win-2 klavika\">"
                        + "<p>"+data.items[i].username+"</p>"
                        + "<p>IDR <span class=\"idr\">"+milliFormat(data.items[i].amount)+"</span></p>"
                        + "  <p>"+data.items[i].gamename+"</p>"
                        +"</div></div>"
                }
                $('#slotshow').html(html);
            }
        },'json');
        //----- virtual jackpot---------------------
        function randomNum(minNum, maxNum) {
            switch (arguments.length) {
                case 1:
                    return parseInt(Math.random() * minNum + 1);
                    break;
                case 2:
                    return parseInt(Math.random() * (maxNum - minNum + 1) + minNum);
                    break;
                default:
                    return 0;
                    break;
            }
        }


        function thousandBitSeparator(num) {

            return num && (num.toString().indexOf('.') != -1 ? num.toString().replace(/(\d)(?=(\d{3})+\.)/g, function($0, $1) {
                return $1 + ",";
            }) : num.toString().replace(/(\d)(?=(\d{3}))/g, function($0, $1) {
                return $1 + ",";
            }));
        }
        function NumAutoPlusAnimation(targetEle, options) {
            /*可以自己改造下传入的参数，按照自己的需求和喜好封装该函数*/
            //不传配置就把它绑定在相应html元素的data-xxxx属性上吧
            options = options || {};
            var $this = document.getElementById(targetEle),
                time = options.time || $this.data('time'), //总时间--毫秒为单位
                finalNum = options.num || $this.data('value'), //要显示的真实数值
                regulator = options.regulator || 1000, //调速器，改变regulator的数值可以调节数字改变的速度
                step = finalNum / (time / regulator),/*每30ms增加的数值--*/
                count = 25311000.25; //计数器
            initial = 0;
            $this.innerHTML = count;
            var timer = setInterval(function() {
                step=randomNum(200,300)/100;
                count = count + step;

                //t未发生改变的话就直接返回
                //避免调用text函数，提高DOM性能
                var t = Math.floor(count);
                initial = count;
                var jackpot=(initial).toLocaleString();
                $this.innerHTML = jackpot;
            }, 3000);
        }

        NumAutoPlusAnimation("jackpotshow", {
            time: 1000000,
            num: 120000,
            regulator: 0.5
        })
    </script>
@endsection

