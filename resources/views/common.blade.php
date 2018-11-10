<!DOCTYPE html>
<html lang="ko">
	<head>
	@section('head')
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=yes">
		<meta name="generator" content="MANAPIE CMS">
		
		<meta name="robots" content="index,follow,noimageindex">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		
		<meta property="og:type" content="website">
		<meta property="og:title" content="{{\App\Setting::find('app_name')->content}}">
		<meta property="og:description" content="{{\App\Setting::find('app_description')->content}}">
		@if(\App\Setting::find('app_preview')->content)<meta property="og:image" content="{{url(\App\Setting::find('app_preview')->content)}}">@endif
		<meta property="og:url" content="{{url('')}}">
		
		<link rel="stylesheet" href="{{url('/style/common.css')}}">
		<link rel="stylesheet" href="{{url('/style/manapie.css')}}">
		<link rel="stylesheet" href="{{url('/style/content.css')}}">
	
		<script src="{{url('/script/jquery-3.3.1.min.js')}}"></script>
		<script src="{{url('/script/manapie.js')}}"></script>
		
		<title>{{\App\Setting::find('app_name')->content}} {{$title or ''}}</title>
	@show
</head>
<body>

<div id="MANAPIE">
	@if(preg_match('/(?i)msie [5-9]/',$_SERVER['HTTP_USER_AGENT']))
		<div style="background:#333;padding:15px 13px 10px 13px">
			<h4 style="color:#fff;font-size:20px;margin-bottom:10px">오래된 브라우저를 사용하고 있습니다.</h4>
			<p style="color:#bbb;line-height:1.5">
				사용 중인 브라우저에서는 {{\App\Setting::find('app_name')->content}}에 사용된 최신 기술의 정상적인 동작을 기대하기 어렵습니다.<br>
				<a style="color:#bbb" href="http://windows.microsoft.com/ko-kr/internet-explorer/download-ie">Internet Explorer를 업그레이드 하거나</a>, <a style="color:#bbb" href="https://www.google.com/intl/ko/chrome">Google Chrome</a> 또는 <a style="color:#bbb" href="https://www.mozilla.org/ko/">Firefox</a> 브라우저를 이용해주세요. 
			</p>
		</div>
	@endif
	
	@yield('container')
</div>
</body>
</html>