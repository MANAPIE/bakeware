@extends('admin.layout',['title'=>'&gt; 회원 &gt; 회원 설정'])

@section('head')
	@parent
	<script type="text/javascript" src="{{url('/script/jquery-ui.min.js')}}"></script>
	<script type="text/javascript" src="{{url('/script/jquery.ui.touch-punch.min.js')}}"></script>
	<script>
	$(function(){
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
			<span class="description" style="display:inline-block">회원 정보에 추가적인 항목을 기재하도록 할 수 있습니다. 선택 항목은 각 값들은 기본 값에 | (파이프 문자)로 구분하세요. 첨부에는 기본 값이 적용되지 않습니다.@if(isset($board))<br>항목을 삭제해도 게시글에 이미 작성된 항목은 삭제되지 않으나, 이름이나 종류를 수정한 경우에는 수정된 값이 반영됩니다.@endif</span>
			
			<div class="btnArea">
				<button type="submit" class="button blue"><span>저장하기</span></button>
			</div>
		</div>
	</form>

@endsection