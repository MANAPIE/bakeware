@extends('common')

@section('head')
	@parent
	<link rel="stylesheet" href="{{url('/style/admin.css.bakeware')}}" />
@endsection


@section('container')
<div class="admin_login">
	<div class="admin_login_right">
		<div class="logo">
			<div class="logo_wrap">
				<h1 class="logo"><img src="{{url('/admin/logo')}}" alt="{{config('app.name')}}"></h1>
				<address>Powered by <a href="http://bakeware.manapie.me/" target="_blank" class="manapie"><span class="blind">MANAPIE</span></a></address>
			</div>
		</div>
	</div>
	
	<div class="admin_login_left">
		<form class="form" method="post" action="{{url('/login')}}">
			<div class="form_wrap">
				{!!csrf_field()!!}
				<legend>관리자 로그인</legend>
				
				@if($errors->has('name') || $errors->has('password'))
					<div class="message error">
						@if ($errors->has('name')) {{$errors->first('name')}}
						@elseif($errors->has('password')) {{$errors->first('password')}}
						@endif
					</div>
				@endif
				
				<label class="input_wrap black">
					<input type="text" name="name" value="{{old('name')}}" required>
					<span>아이디</span>
				</label>
				<label class="input_wrap black">
					<input type="password" name="password" minlength="8" required>
					<span>비밀번호</span>
				</label>
				
				<div class="btnArea">
					<button type="submit" class="button blue"><span>로그인</span></button>
				</div>
			</div>
		</form>
	</div>
	
	<div class="clear"></div>
</div>
@endsection