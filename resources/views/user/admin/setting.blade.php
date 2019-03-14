@extends('admin.layout',['title'=>'&gt; 회원 &gt; 회원 설정'])

@section('head')
	@parent
	<script type="text/javascript" src="{{url('/ckeditor/ckeditor.js')}}"></script>
	<script type="text/javascript" src="{{url('/script/jquery-ui.min.js')}}"></script>
	<script type="text/javascript" src="{{url('/script/jquery.ui.touch-punch.min.js')}}"></script>
	<script>
	$(function(){
		CKEDITOR.replace('term_service',{});
		CKEDITOR.replace('term_privacy',{});
		$('.page_list ul').sortable();
	});
	</script>
@stop

@section('body')
	<h3 class="menu_title">회원 설정</h3>

	@if(session('message'))
		<div class="message success">{!!session('message')!!}</div>
	@endif
	
	<form class="form" method="post" action="{{url('/admin/user/setting')}}{{$_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''}}">
		<div class="form_wrap">
			{!!csrf_field()!!}
			
			<div class="description">
				회원 정보에 추가적인 항목을 기재하도록 할 수 있습니다. 
			</div>
			
			<div class="selects">
				<span>추가 항목</span>
				<div class="page_list">
					<ul style="padding-top:8px">
						<?php
						$types=[
							'text'=>'한 줄 텍스트',
							'textarea'=>'여러 줄 텍스트',
							'radio'=>'하나 선택',
							'checkbox'=>'여러 개 선택',
							'order'=>'순서 선택',
							'image'=>'이미지 첨부',
							'file'=>'파일 첨부',
						];
						?>
						@if(\App\User::extravars())
							@foreach(\App\User::extravars() as $extravar)
								<li>
									<input type="hidden" name="extravar[]" value="{{$extravar->id}}">
									<div class="item_wrap">
										<div class="item_half">
											<label class="input_wrap white">
												<input type="text" name="extravar_name[]" value="@if($extravar->name){{$extravar->name}}@endif">
												<span>이름</span>
											</label>
										</div>
										<div class="item_full">
											<div class="selects white">
												<span>종류</span>
												<input type="hidden" name="extravar_type[]" value="{{$extravar->type}}">
												@foreach($types as $eng=>$kor)
													<label class="select_wrap" onclick="$(this).parent().find('a').removeClass('active');$(this).find('a').addClass('active');$(this).parent().find('input').val('{{$eng}}');">
														<a href="#" onclick="$(this).parent().click();return false" @if($extravar->type==$eng)class="active" @endif>✔︎</a>
														<span>{{$kor}}</span>
													</label>
												@endforeach
											</div>
										</div>
										<div class="item_full">
											<label class="input_wrap white">
												<input type="text" name="extravar_content[]" value="@if($extravar->content){{$extravar->content}}@endif">
												<span>기본 값</span>
											</label>
										</div>
										<div class="item_full">
											<label class="input_wrap white">
												<input type="text" name="extravar_description[]" value="@if($extravar->description){{$extravar->description}}@endif">
												<span>설명</span>
											</label>
										</div>
										<div class="item_options">
											<label class="select_wrap">
												<a href="#" class="active" onclick="$(this).parents('li').remove();return false">&times;</a>
												<span>&nbsp;</span>
											</label>
										</div>
										<div class="clear"></div>
									</div>
								</li>
							@endforeach
						@endif
						<li class="blind" id="blank2">
							<input type="hidden" name="extravar[]" value="0">
							<div class="item_wrap">
								<div class="item_full">
									<label class="input_wrap white">
										<input type="text" name="extravar_name[]" value="">
										<span>이름</span>
									</label>
								</div>
								<div class="item_full">
									<div class="selects white">
										<span>종류</span>
										<input type="hidden" name="extravar_type[]" value="text">
										@foreach($types as $eng=>$kor)
											<label class="select_wrap" onclick="$(this).parent().find('a').removeClass('active');$(this).find('a').addClass('active');$(this).parent().find('input').val('{{$eng}}');">
												<a href="#" onclick="$(this).parent().click();return false" @if($eng=='text')class="active" @endif>✔︎</a>
												<span>{{$kor}}</span>
											</label>
										@endforeach
									</div>
								</div>
								<div class="item_full">
									<label class="input_wrap white">
										<input type="text" name="extravar_content[]" value="">
										<span>기본 값</span>
									</label>
								</div>
								<div class="item_full">
									<label class="input_wrap white">
										<input type="text" name="extravar_description[]" value="">
										<span>설명</span>
									</label>
								</div>
								<div class="item_options">
									<label class="select_wrap">
										<a href="#" class="active" onclick="$(this).parents('li').remove();return false">&times;</a>
										<span>&nbsp;</span>
									</label>
								</div>
								<div class="clear"></div>
							</div>
						</li>
					</ul>
					<div class="btnArea">
						<button type="button" class="button gray" onclick="$('#blank2').before($('#blank2').clone().removeAttr('id').removeClass('blind'));return false">항목 추가</button>
					</div>
				</div>
			</div>
			<span class="description" style="display:inline-block">선택 항목은 각 값들은 기본 값에 | (파이프 문자)로 구분하세요. 첨부에는 기본 값이 적용되지 않습니다.<br><i>한 줄 텍스트</i>는 <a href="{{url('/admin/user')}}" target="_blank">회원 관리</a> 목록에 보여집니다.</span>
			
			<div class="description">
				회원 가입과 관련된 설정을 할 수 있습니다.<br>
				 
			</div>
			
			<div class="selects" id="allow_register">
				<span>회원 가입</span>
				<label class="select_wrap" onclick="$('#allow_register a').removeClass('active');$(this).find('a').addClass('active')">
					<input type="radio" name="allow_register" value="N" class="blind" @if(\App\UserSetting::find('allow_register')->content=='N') checked @endif>
					<a href="#" onclick="$(this).parent().click();return false" @if(\App\UserSetting::find('allow_register')->content=='N') class="active" @endif>✔︎</a>
					<span>받지 않음</span>
				</label>
				<label class="select_wrap" onclick="$('#allow_register a').removeClass('active');$(this).find('a').addClass('active')">
					<input type="radio" name="allow_register" value="Y" class="blind" @if(\App\UserSetting::find('allow_register')->content=='Y') checked @endif>
					<a href="#" onclick="$(this).parent().click();return false" @if(\App\UserSetting::find('allow_register')->content=='Y') class="active" @endif>✔︎</a>
					<span>받음</span>
				</label>
			</div>
			
			<div class="selects" id="layout">
				<span>레이아웃</span>
				<label class="select_wrap" onclick="$('#layout a').removeClass('active');$(this).find('a').addClass('active')">
					<input type="radio" name="layout" value="0" class="blind" @if(!\App\UserSetting::find('layout')->content) checked @endif>
					<a href="#" onclick="$(this).parent().click();return false" @if(!\App\UserSetting::find('layout')->content) class="active" @endif>✔︎</a>
					<span>(없음)</span>
				</label>
				@foreach($layouts as $layout)
					<label class="select_wrap" onclick="$('#layout a').removeClass('active');$(this).find('a').addClass('active')">
						<input type="radio" name="layout" value="{{$layout->id}}" class="blind" @if(\App\UserSetting::find('layout')->content==$layout->id) checked @endif>
						<a href="#" onclick="$(this).parent().click();return false" @if(\App\UserSetting::find('layout')->content==$layout->id) class="active" @endif>✔︎</a>
						<span>{{$layout->name}}</span>
					</label>
				@endforeach
			</div>
				
			<div class="selects" id="skin">
				<span>스킨</span>
				@foreach($paths as $path)
					<label class="select_wrap" onclick="$('#skin a').removeClass('active');$(this).find('a').addClass('active')">
						<input type="radio" name="skin" value="{{$path}}" class="blind" @if(\App\UserSetting::find('skin')->content==$path) checked @endif>
						<a href="#" onclick="$(this).parent().click();return false" @if(\App\UserSetting::find('skin')->content==$path) class="active" @endif>✔︎</a>
						<span>{{$path}}</span>
					</label>
				@endforeach
			</div>
			
			<div class="selects" id="auto_register">
				<span>가입 승인</span>
				<label class="select_wrap" onclick="$('#auto_register a').removeClass('active');$(this).find('a').addClass('active')">
					<input type="radio" name="auto_register" value="N" class="blind" @if(\App\UserSetting::find('auto_register')->content=='N') checked @endif>
					<a href="#" onclick="$(this).parent().click();return false" @if(\App\UserSetting::find('auto_register')->content=='N') class="active" @endif>✔︎</a>
					<span>승인이 필요함</span>
				</label>
				<label class="select_wrap" onclick="$('#auto_register a').removeClass('active');$(this).find('a').addClass('active')">
					<input type="radio" name="auto_register" value="Y" class="blind" @if(\App\UserSetting::find('auto_register')->content=='Y') checked @endif>
					<a href="#" onclick="$(this).parent().click();return false" @if(\App\UserSetting::find('auto_register')->content=='Y') class="active" @endif>✔︎</a>
					<span>자동으로 가입</span>
				</label>
			</div>
			<span class="description">승인이 필요한 경우 <a href="{{url('/admin/user')}}" target="_blank">회원 관리</a>에서 승인을 해주어야 로그인이 가능합니다.</span>
			
			
			<div class="selects">
				<span>첫 역할</span>
				<?php $first=explode('|',\App\UserSetting::find('first_groups')->content); ?>
				@foreach($groups as $group)
					@if($group->id>2)
						<label class="select_wrap" onclick="$(this).find('input').each(function(){$(this).prop('checked',!$(this).prop('checked'));});$(this).find('a').toggleClass('active');return false">
							<input type="checkbox" name="first_groups[]" value="{{$group->id}}" class="blind"@if(in_array($group->id,$first)) checked @endif>
							<a href="#" onclick="return false"@if(in_array($group->id,$first)) class="active" @endif>✔︎</a>
							<span>{{$group->name}}</span>
						</label>
					@endif
				@endforeach
			</div>
			<span class="description">회원 가입을 했을 때 처음에 자동으로 가지게 될 역할을 선택하세요.</span>
			
			<label class="input_wrap label">
				<span>이용약관</span>
			</label>
			<div class="editor_wrap">
				<textarea id="term_service" name="term_service">{{\App\UserSetting::find('term_service')->content}}</textarea>
			</div>
			<span class="description">회원 가입 폼을 작성하기 전에 필수적으로 동의해야하는 이용 약관을 작성합니다.</span>
			
			<label class="input_wrap label">
				<span>개인정보</span>
			</label>
			<div class="editor_wrap">
				<textarea id="term_privacy" name="term_privacy">{{\App\UserSetting::find('term_privacy')->content}}</textarea>
			</div>
			<span class="description">회원 가입 폼을 작성하기 전에 필수적으로 동의해야하는 개인정보처리방침(개인정보 수집 및 이용에 대한 안내)을 작성합니다.</span>
			
			<div class="btnArea">
				<button type="submit" class="button blue"><span>저장하기</span></button>
			</div>
		</div>
	</form>

@endsection