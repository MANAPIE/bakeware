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
				암호화가 켜진 상태에서는 각 모듈에서 만들어지는 데이터가 데이터베이스에 암호화되어 저장되기 때문에 유출 시 원문을 알 수 없게 합니다.<br>
				암호화 설정이 꺼져 있는 상태에서 저장된 평문 데이터는 암호화를 켜도 다시 암호화되어 저장하지 않습니다.<br>
				반대로, 암호화 설정이 켜져있는 상태에서 저장된 암호화 데이터는 암호화를 꺼도 다시 복호화되어 평문으로 저장하지 않습니다.<br>
				모듈 별로 암호화하여 저장하는 데이터는 아래와 같습니다.
				<ul>
					<li>레이아웃: (없음)</li>
					<li>메뉴: 메뉴 이름, 항목 이름, 항목 주소</li>
					<li>회원: 아이디, 이름, 이메일, 비고, 추가 항목 (비밀번호는 복호화가 불가능하게 별도로 암호화됩니다.)</li>
					<li>페이지: 이름, 내용(편집 페이지에서)</li>
					<li>원페이지: 이름</li>
					<li>게시판: 게시판 이름, 게시판 양식, 분류 이름, 추가 항목 이름, 추가 항목 기본 값, 게시글 제목, 게시글 내용, 게시글 추가 항목, 댓글 내용</li>
					<li>폼: 폼 이름, 질문 이름, 질문 기본 값, 답변 내용</li>
				</ul>
			</div>
			
			@foreach($modules as $module)
				<legend>{{$module->name}}</legend>
				<div class="selects" id="{{$module->module}}" style="padding-left:5px">
					<?php $setting=\DB::table('encryption_settings')->where('module',$module->module)->first(); ?>
					<input type="hidden" name="module[]" value="{{$module->module}}">
					
					<label class="select_wrap" onclick="$('#{{$module->module}} a').removeClass('active');$(this).find('a').addClass('active')">
						<input type="radio" name="{{$module->module}}" value="0" class="blind"@if(!\App\Encryption::isEncrypt($module->module)) checked @endif>
						<a href="#" onclick="$(this).parent().click();return false"@if(!\App\Encryption::isEncrypt($module->module)) class="active" @endif>✔︎</a>
						<span>사용 안 함</span>
					</label>
					
					<label class="select_wrap" onclick="$('#{{$module->module}} a').removeClass('active');$(this).find('a').addClass('active')">
						<input type="radio" name="{{$module->module}}" value="1" class="blind"@if(\App\Encryption::isEncrypt($module->module)) checked @endif>
						<a href="#" onclick="$(this).parent().click();return false"@if(\App\Encryption::isEncrypt($module->module)) class="active" @endif>✔︎</a>
						<span>암호화하여 저장</span>
					</label>
				</div>
			@endforeach
			
			<div class="btnArea">
				<button type="submit" class="button blue"><span>저장하기</span></button>
			</div>
		</div>
	</form>

@endsection