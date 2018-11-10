@extends('common',['title'=>' 문제가 발생했습니다.'])

@section('container')
<div id="error">
	<div class="aht">앗</div>
	<div class="msg">
		작은 문제가 발생했습니다.<br>
		{{$message}}
	</div>
	<address>
		<div class="logo"><a href="{{'/'}}"><img src="{{url('/image/admin_logo.png')}}" alt="{{config('app.name')}}"></a></div>
		<div class="power">Powered by <a href="http://manapie.me/contact" target="_blank" class="manapie"><span class="blind">MANAPIE</span></a></div>
	</address>
</div>
@endsection