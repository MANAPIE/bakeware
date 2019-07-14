<!DOCTYPE html>
<html lang="ko">
	<head>
	@section('head')
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=yes" />
		<meta name="naver-site-verification" content="abd7f859bb066475397b68ee502d4c6c4d7f452a"/>
		
		<meta name="robots" content="index,follow,noimageindex" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		
		<meta property="og:type" content="website">
		<meta property="og:title" content="{{config('app.name')}}">
		<meta property="og:description" content="{{config('app.description')}}">
		<meta property="og:image" content="{{url('/image/og.jpg.bakeware')}}">
		<meta property="og:url" content="{{config('app.url')}}">
		\
		<link rel="stylesheet" href="{{url('/style/common.css.bakeware')}}" />
		<link rel="stylesheet" href="{{url('/style/manapie.css.bakeware')}}" />
	
		<script src="{{url('/script/jquery-3.3.1.min.js.bakeware')}}"></script>
		
		<title>MANAPIE</title>
	@show
</head>
<body>

<div id="MANAPIE">
	@if(preg_match('/(?i)msie [5-8]/',$_SERVER['HTTP_USER_AGENT']))
		<div style="background:#333;padding:15px 13px 10px 13px">
			<h4 style="color:#fff;font-size:20px;margin-bottom:10px">오래된 브라우저를 사용하고 있습니다.</h4>
			<p style="color:#bbb;line-height:1.5">
				사용 중인 브라우저에서는 {{config('app.name')}}에 사용된 최신 기술의 정상적인 동작을 기대하기 어렵습니다.<br />
				<a style="color:#bbb" href="http://windows.microsoft.com/ko-kr/internet-explorer/download-ie">Internet Explorer를 업그레이드 하거나</a>, <a style="color:#bbb" href="https://www.google.com/intl/ko/chrome">Google Chrome</a> 또는 <a style="color:#bbb" href="https://www.mozilla.org/ko/">Firefox</a> 브라우저를 이용해주세요. 
			</p>
		</div>
	@endif
	
	<div id="error">
		<div class="aht">앗</div>
		<div class="msg">
			설치가 완료되지 않았습니다.<br>
			터미널에서 데이터베이스를 마이그레이션하여 최초의 설정을 진행하십시오.
		</div>
		<address>
			<div class="logo"><a href="{{'/'}}"><img src="{{url('/image/admin_logo.png.bakeware')}}" alt="{{config('app.name')}}"></a></div>
			<div class="power">Powered by <a href="http://manapie.me/contact" target="_blank" class="manapie"><span class="blind">MANAPIE</span></a></div>
		</address>
	</div>
	
</div>
</body>
</html>