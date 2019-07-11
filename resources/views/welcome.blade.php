@extends('common')

@section('head')
	@parent
	<style>
		.background{position:fixed;top:0;left:0;width:100%;height:100%;z-index:-1;overflow:hidden}
		.background .line{position:absolute;width:25px;height:100px;background:#aaa;opacity:0;border-radius:100px;transform:rotate(45deg)}
		.background .line.l1{top:-20%;right:-20%;animation:diagonal1 10s infinite;animation-delay:2s}
		.background .line.l2{top:-20%;right:0%;animation:diagonal2 9s infinite}
		.background .line.l3{top:-20%;right:30%;animation:diagonal3 8s infinite;animation-delay:3s}
		.background .line.l4{top:60%;right:10%;animation:diagonal4 5s infinite}
		.background .line.l5{top:30%;right:40%;animation:diagonal5 8s infinite;animation-delay:2s}
		.background .line.l6{top:30%;right:-10%;animation:diagonal6 7s infinite}
		.background .line.l7{top:0%;right:40%;animation:diagonal7 4s infinite;animation-delay:3s}
		.background .line.l8{top:40%;right:50%;animation:diagonal8 7s infinite}
		.background .line.l9{top:20%;right:10%;animation:diagonal9 6s infinite;animation-delay:2s}
		
		@keyframes diagonal1{50%{opacity:0.2} 100% {top:120%;right:110%}}
		@keyframes diagonal2{50%{opacity:0.2} 100% {top:120%;right:130%}}
		@keyframes diagonal3{50%{opacity:0.2} 100% {top:120%;right:160%}}
		@keyframes diagonal4{50%{opacity:0.2} 100% {top:200%;right:140%}}
		@keyframes diagonal5{50%{opacity:0.2} 100% {top:170%;right:170%}}
		@keyframes diagonal6{50%{opacity:0.2} 100% {top:170%;right:120%}}
		@keyframes diagonal7{50%{opacity:0.2} 100% {top:140%;right:170%}}
		@keyframes diagonal8{50%{opacity:0.2} 100% {top:180%;right:180%}}
		@keyframes diagonal9{50%{opacity:0.2} 100% {top:160%;right:150%}}
		
		.body_wrap{display:table;width:100%;height:100%;position:absolute;top:0;left:0}
		#body{display:table-cell;vertical-align:middle;overflow:auto;-webkit-overflow-scrolling:touch}
		
		#body .logo{max-width:350px;margin:50px auto;text-align:center}
		#body .logo img{width:100%;max-width:400px;height:auto}
		
		#body .paragraph{padding:0 20px}
		#body .description{max-width:400px;margin:0 auto;text-align:center;margin:50px auto;background:rgba(220,220,220,0.3);padding:50px 0;color:#999}
		
		#body address{font-style:normal;font-size:12px;color:#bbb;text-align:center;padding:10px;box-sizing:border-box;margin:50px auto 80px auto}
		#body address .manapie{background:url('/image/manapie_blk.png') transparent no-repeat center center;background-size:100% 100%;width:88px;height:11px;display:inline-block}
	</style>
@endsection

@section('container')
<div class="background">
	<div class="line l1"></div>
	<div class="line l2"></div>
	<div class="line l3"></div>
	<div class="line l4"></div>
	<div class="line l5"></div>
	<div class="line l6"></div>
	<div class="line l7"></div>
	<div class="line l8"></div>
	<div class="line l9"></div>
</div>
<div class="body_wrap">
	<div id="body">
		<div class="paragraph">
			<div class="logo">
				<img src="{{url('/image/bakeware.png')}}" alt="bakeware">
			</div>
		</div>
		<div class="paragraph">
			<div class="description">
				MANAPIE의 새로운 프로젝트를 환영합니다.<br>
				<a href="{{url('/admin')}}">관리자페이지</a>에 접속해 세팅을 시작해주세요.
			</div>
		</div>
		<div class="paragraph">
			<address>Powered by <a href="http://cms.manapie.me/" target="_blank" class="manapie"><span class="blind">MANAPIE</span></a></address>
		</div>
	</div>
</div>
@endsection