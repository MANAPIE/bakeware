<style>
	#MANAPIE{background:url('{{url('/image/admin_login.jpg')}}') no-repeat center center;background-size:cover;padding:30px 15px}
	#header{text-align:center;padding-bottom:30px}
	#header img{width:200px;height:auto}
	#body{background:#fff;padding:20px;line-height:1.7}
	#body .content{border-top:1px solid #ddd;padding-top:20px;margin-top:20px}
	#body .content img{max-width:100%}
	#body .content .question{margin-bottom:-10px;color:#999;font-weight:bold;position:relative;margin-left:10px}
	#body .content .question::before{content:" ";background:#0096e0;height:80%;width:3px;border-radius:5px;position:absolute;top:10%;left:-10px}
</style>
<div id="MANAPIE">
	<div id="header">
		<img src="{{url('/image/admin_logo.png')}}" alt="{{\App\Setting::find('app_name')->content}}">
	</div>
	<div id="body">
		{!!$data!!}
	</div>
</div>