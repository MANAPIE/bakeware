@extends('admin.layout',['title'=>'&gt; 암호화 설정'])

@section('body')
	<h3 class="menu_title">암호화 설정</h3>

	@if(session('message'))
		<div class="message success">{!!session('message')!!}</div>
	@endif
	
	<form class="form" method="post" action="{{url('/admin/encryption')}}">
		<div class="form_wrap">
			{!!csrf_field()!!}
			
			<div class="description">
				모듈 별로 암호화 설정을 하실 수 있습니다.<br>
				암호화가 켜진 상태에서 각 모듈에서 만들어지는 데이터 중 일부는 데이터베이스에 암호화되어 저장되어 유출 시 원문을 알 수 없게 합니다.<br>
				설정이 되어있지 않은 상태에서 저장된 평문은 암호화를 켜도 다시 암호화되어 저장되지 않습니다.<br>
				모듈 별로 지원되는 암호화의 범위는 아래와 같습니다. 언급이 없는 모듈은 암호화 기능이 없는 모듈로, 암호화를 켜도 다르게 작동하지 않습니다.
				<ul>
					<li>메뉴: 메뉴 이름과 각 항목의 이름 및 주소</li>
					<li>회원: 아이디, 이름, 이메일, 비고, 추가 항목 (비밀번호는 기본적으로 복호화가 불가능한 알고리즘으로 별도로 암호화됩니다.)</li>
					<li>페이지: 이름, 내용(편집 페이지에서)</li>
					<li>원페이지: 이름</li>
					<li>게시판: 게시판 이름, 게시글 제목, 게시글 내용, 댓글 내용</li>
					<li>폼: 폼 이름, 질문 이름, 질문 기본 값, 답변 내용</li>
				</ul>
			</div>
			
			@foreach($modules as $module)
				<legend>{{$module->name}}</legend>
				<div class="selects" style="padding-left:5px">
					<?php $setting=\DB::table('encryption_settings')->where('module',$module->module)->first(); ?>
					
					<input type="hidden" name="module[]" value="{{$module->module}}">
					<label class="select_wrap" onclick="$(this).find('input').each(function(){$(this).prop('checked',!$(this).prop('checked'));});$(this).find('a').toggleClass('active');return false">
						<input type="checkbox" name="{{$module->module}}[]" value="0" class="blind"@if(!$setting) checked @endif>
						<a href="#" onclick="return false"@if(!$setting) class="active" @endif>✔︎</a>
						<span>사용 안 함</span>
					</label>
					
					<input type="hidden" name="module[]" value="{{$module->module}}">
					<label class="select_wrap" onclick="$(this).find('input').each(function(){$(this).prop('checked',!$(this).prop('checked'));});$(this).find('a').toggleClass('active');return false">
						<input type="checkbox" name="{{$module->module}}[]" value="1" class="blind"@if($setting) checked @endif>
						<a href="#" onclick="return false"@if($setting) class="active" @endif>✔︎</a>
						<span>암호화하여 저장</span>
					</label>
				</div>
			@endforeach
		</div>
	</form>

@endsection