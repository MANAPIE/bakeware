@extends('admin.layout',['title'=>'&gt; 사이트 기본 설정'])

@section('body')
	<h3 class="menu_title">사이트 기본 설정</h3>

	@if(session('message'))
		<div class="message success">{!!session('message')!!}</div>
	@endif
	
	<form class="form" method="post" action="{{url('/admin/setting')}}" enctype="multipart/form-data">
		<div class="form_wrap">
			{!!csrf_field()!!}
			
			<div class="description">
				'마스터' 역할을 가진 관리자는 사이트의 기본 설정을 할 수 있습니다.
			</div>
			
			<label class="input_wrap">
				<input type="text" name="app_name" value="{{\App\Setting::find('app_name')->content}}" required>
				<span>회사 이름</span>
			</label>
			
			<label class="input_wrap">
				<input type="text" name="app_description" value="{{\App\Setting::find('app_description')->content}}" required>
				<span>회사 설명</span>
			</label>
			
			<label class="input_wrap">
				<div class="thumbnail"><img src="{{\App\Setting::find('app_preview')->content}}" alt=""></div>
				<input type="file" name="app_preview" accept="image/*">
				<input type="hidden" name="app_preview_original" value="{{\App\Setting::find('app_preview')->content}}">
				<span>미리보기</span>
			</label>
			
			<div class="description">
				SSL 인증서가 적용되어야만 HTTPS 보안 접속을 할 수 있습니다. 언제나 적용 옵션을 선택하기 전에 https가 제대로 설정되었는지 확인하십시오.
			</div>
			
			<div class="selects" id="https">
				<span>HTTPS</span>
				<label class="select_wrap" onclick="$('#https a').removeClass('active');$(this).find('a').addClass('active')">
					<input type="radio" name="mail_template" value="N" class="blind" @if(\App\Setting::find('https')->content=='N') checked @endif>
					<a href="#" onclick="$(this).parent().click();return false" @if(\App\Setting::find('https')->content=='N') class="active" @endif>✔︎</a>
					<span>미적용</span>
				</label>
				<label class="select_wrap" onclick="$('#https a').removeClass('active');$(this).find('a').addClass('active')">
					<input type="radio" name="mail_template" value="A" class="blind" @if(\App\Setting::find('https')->content=='Y') checked @endif>
					<a href="#" onclick="$(this).parent().click();return false" @if(\App\Setting::find('https')->content=='Y') class="active" @endif>✔︎</a>
					<span>언제나 적용</span>
				</label>
			</div>
			
			<div class="description">
				메일 보내는 서버(SMTP) 설정은 메일 제공 업체에서 확인하세요.
			</div>
			
			<label class="input_wrap">
				<input type="text" name="mail_address" value="{{\App\Setting::find('mail_address')->content}}">
				<span>메일 주소</span>
			</label>
			
			<label class="input_wrap">
				<input type="text" name="mail_host" value="{{\App\Setting::find('mail_host')->content}}">
				<span>메일 서버</span>
			</label>
			
			<label class="input_wrap">
				<input type="text" name="mail_port" value="{{\App\Setting::find('mail_port')->content}}">
				<span>메일 포트</span>
			</label>
			
			<label class="input_wrap">
				<input type="text" name="mail_username" value="{{\App\Setting::find('mail_username')->content}}">
				<span>메일 ID</span>
			</label>
			
			<label class="input_wrap">
				<input type="password" name="mail_password" value="{{\App\Setting::find('mail_password')->content}}">
				<span>메일 PW</span>
			</label>
			
			<label class="input_wrap">
				<input type="text" name="mail_encryption" value="{{\App\Setting::find('mail_encryption')->content}}">
				<span>메일 보안</span>
			</label>
			
			<div class="selects" id="mail_template">
				<span>메일 템플릿</span>
				@foreach($mail_templates as $template)
					<label class="select_wrap" onclick="$('#mail_template a').removeClass('active');$(this).find('a').addClass('active')">
						<input type="radio" name="mail_template" value="{{$template}}" class="blind" @if(\App\Setting::find('mail_template')->content==$template) checked @endif>
						<a href="#" onclick="$(this).parent().click();return false" @if(\App\Setting::find('mail_template')->content==$template) class="active" @endif>✔︎</a>
						<span>{{$template}}</span>
					</label>
				@endforeach
			</div>
			
			<div class="btnArea">
				<button type="submit" class="button blue"><span>저장하기</span></button>
			</div>
		</div>
	</form>

@endsection