@extends('admin.layout',['title'=>'&gt; 권한 설정'])

@section('body')
	<h3 class="menu_title">관리 권한 설정</h3>

	@if(session('message'))
		<div class="message success">{!!session('message')!!}</div>
	@endif
	
	<form class="form" method="post" action="{{url('/admin/manager')}}">
		<div class="form_wrap">
			{!!csrf_field()!!}
			
			<div class="description">
				관리자는 '관리자' 역할을 가져야 관리 대시보드에 로그인할 수 있습니다.<br>
				'마스터' 역할을 가진 관리자는 각 모듈의 관리 권한을 가지는 회원 그룹을 지정할 수 있습니다.<br>
				아무 역할에도 선택이 되어 있지 않으면 모든 관리자가 관리 권한을 갖게 됩니다.<br>
				회원의 역할 관리는 <a href="{{url('/admin/user')}}">회원 관리</a>에서, 역할의 추가·제거는 <a href="{{url('/admin/user/group')}}">회원 관리 &gt; 역할 관리</a>에서 할 수 있습니다.
			</div>
			
			@foreach($modules as $module)
				<legend>{{$module->name}}</legend>
				<div class="selects" style="padding-left:5px">
					<?php $manager=explode('|',$module->manager); ?>
					@foreach($groups as $group)
						@if($group->id>2)
						<input type="hidden" name="module[]" value="{{$module->module}}">
						<label class="select_wrap" onclick="$(this).find('input').each(function(){$(this).prop('checked',!$(this).prop('checked'));});$(this).find('a').toggleClass('active');return false">
							<input type="checkbox" name="{{$module->module}}[]" value="{{$group->id}}" class="blind"@if(in_array($group->id,$manager)) checked @endif>
							<a href="#" onclick="return false"@if(in_array($group->id,$manager)) class="active" @endif>✔︎</a>
							<span>{{$group->name}}</span>
						</label>
						@endif
					@endforeach
				</div>
			@endforeach
			
			<div class="btnArea">
				<button type="submit" class="button blue"><span>저장하기</span></button>
			</div>
		</div>
	</form>

@endsection