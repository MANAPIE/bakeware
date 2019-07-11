@extends('common',['title'=>' 관리 '.($title??'')])

@section('head')
	@parent
	<link rel="stylesheet" href="{{url('/style/admin.css')}}" />
	<script src="{{url('/script/admin.js')}}"></script>
@endsection

@section('container')
	<div id="header">
		<h1 class="logo"><a href="{{url('/')}}" target="_blank"><img src="{{url('/admin/logo')}}" alt="{{config('app.name')}}"></a></h1>
		<div class="user">
			<div class="thumbnail" @if(Auth::user()->thumbnail()) style="background-image:url('{{url(Auth::user()->thumbnail())}}')" @endif></div>
			<div class="nickname">
				<div class="nick">{{Auth::user()->nickname}}</div>
				<div class="name">
					{{Auth::user()->name}}
					<a href="{{url('/logout')}}" class="logout">로그아웃</a>
				</div>
			</div>
			<div class="clear"></div>
		</div>
		<nav>
			<ul>
				<li><a href="{{url('/admin')}}"@if($current[0]=='dashboard') class="active"@endif>대시보드</a></li>
				<li><a href="{{url('/admin/visitor')}}"@if($current[0]=='visitor') class="active"@endif>방문 기록</a></li>
				<li><a href="{{url('/admin/notification')}}"@if($current[0]=='notification') class="active"@endif>알림</a></li>
				@if(array_key_exists(1,Auth::user()->groups()))
					<li><a href="{{url('/admin/setting')}}"@if($current[0]=='setting') class="active"@endif>사이트 기본 설정</a></li>
					<li><a href="{{url('/admin/manager')}}"@if($current[0]=='manager') class="active"@endif>관리 권한 설정</a></li>
				@endif
				
				@foreach(\App\Http\Controllers\AdminController::menu() as $modules)
					@if(count($modules))
						<li class="gap"></li>
					@endif
					@foreach($modules as $mainmenu)
						@foreach($mainmenu as $menu)
							<li>
								<a href="{{url($menu['url'])}}"@if($menu['external']) target="_blank"@endif @if($current[0]==$menu['current']) class="active"@endif>{{$menu['name']}}</a>
								@if(count($menu['submenu']))
									<ul>
										@foreach($menu['submenu'] as $m)
											<li>
												<a href="{{url($m['url'])}}"@if($m['external']) target="_blank"@endif @if($current[0]==$menu['current']&&$current[1]==$m['current']) class="active"@endif>{{$m['name']}}</a>
											</li>
										@endforeach
									</ul>
								@endif
							</li>
						@endforeach
					@endforeach
				@endforeach
				
				@if(array_key_exists(1,Auth::user()->groups()))
					<li class="gap"></li>
					<li><a href="{{url('/admin/resource')}}"@if($current[0]=='resource') class="active"@endif>첨부파일 관리</a>
						<ul>
							<li>
								<a href="{{url('/admin/resource')}}"@if($current[0]=='resource') class="active"@endif>첨부파일 관리</a>
							</li>
						</ul>
					</li>
				@endif
			</ul>
		</nav>
		
		<address>Powered by <a href="http://bakeware.manapie.me/" target="_blank" class="manapie"><span class="blind">MANAPIE</span></a></address>
	</div>
	
	<div id="body">
		@yield('body')
		<div class="navigation_button"><span></span><span></span><span></span></div>
		<div class="navigation_shadow"></div>
	</div>
	
@endsection