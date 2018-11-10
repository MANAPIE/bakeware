@extends('admin.layout',['title'=>isset($form)?'&gt; 폼 &gt; '.$form->name:'&gt; 폼 &gt; 추가'])

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
	<h3 class="menu_title">폼 @if(isset($form))관리@else추가@endif</h3>

	@if(session('message'))
		<div class="message success">{!!session('message')!!}</div>
	@endif
	
	<form class="form" method="post" action="{{url('/admin/form/'.(isset($form)?'edit':'create'))}}{{$_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''}}">
		<div class="form_wrap">
			{!!csrf_field()!!}
			@if(isset($form))
				<input type="hidden" name="id" value="{{$form->id}}">
			@endif
			
			<label class="input_wrap">
				<input type="text" name="url" value="@if(isset($form)){{$form->url}}@endif">
				<span>주소</span>
			</label>
			<span class="description">'{{url('')}}/주소'으로 접속할 수 있습니다.</span>
			
			<script>
			$(function(){
				@if(!isset($form))
					$.get('{{url('/admin/check/url/')}}'+'?url='+$(this).val(),function(data){
						if(data=='Y')
							$('input[name=url')[0].setCustomValidity('이미 존재하는 주소입니다.');
						else
							$('input[name=url')[0].setCustomValidity('');
					});
				@endif
				$('input[name=url').keyup(function(){
					$.get('{{url('/admin/check/url/')}}'+'?url='+$(this).val()@if(isset($form))+'&original={{$form->url}}'@endif,function(data){
						if(data=='Y')
							$('input[name=url')[0].setCustomValidity('이미 존재하는 주소입니다.');
						else
							$('input[name=url')[0].setCustomValidity('');
					});
				});
			});
			</script>
			
			<label class="input_wrap">
				<input type="text" name="name" value="@if(isset($form)){{$form->name}}@endif">
				<span>이름</span>
			</label>
			
			<div class="selects" id="layout">
				<span>레이아웃</span>
				<label class="select_wrap" onclick="$('#layout a').removeClass('active');$(this).find('a').addClass('active')">
					<input type="radio" name="layout" value="0" class="blind" @if(!isset($form)||!$form->layout) checked @endif>
					<a href="#" onclick="$(this).parent().click();return false" @if(!isset($form)||!$form->layout) class="active" @endif>✔︎</a>
					<span>(없음)</span>
				</label>
				@foreach($layouts as $layout)
					<label class="select_wrap" onclick="$('#layout a').removeClass('active');$(this).find('a').addClass('active')">
						<input type="radio" name="layout" value="{{$layout->id}}" class="blind" @if(isset($form)&&$form->layout==$layout->id) checked @endif>
						<a href="#" onclick="$(this).parent().click();return false" @if(isset($form)&&$form->layout==$layout->id) class="active" @endif>✔︎</a>
						<span>{{$layout->name}}</span>
					</label>
				@endforeach
			</div>
				
			<div class="selects" id="skin">
				<span>스킨</span>
				@foreach($paths as $path)
					<label class="select_wrap" onclick="$('#skin a').removeClass('active');$(this).find('a').addClass('active')">
						<input type="radio" name="skin" value="{{$path}}" class="blind" @if((!isset($form)&&$path=='default')||(isset($form)&&$form->skin==$path)) checked @endif>
						<a href="#" onclick="$(this).parent().click();return false" @if((!isset($form)&&$path=='default')||(isset($form)&&$form->skin==$path)) class="active" @endif>✔︎</a>
						<span>{{$path}}</span>
					</label>
				@endforeach
			</div>
			
			<div class="selects">
				<span>답변 권한</span>
				@foreach($groups as $group)
					<label class="select_wrap" onclick="$(this).find('input').each(function(){$(this).prop('checked',!$(this).prop('checked'));});$(this).find('a').toggleClass('active');return false">
						<input type="checkbox" name="group[]" value="{{$group->id}}" class="blind" @if(isset($form)&&in_array($group->id,$form->groups())) checked @endif >
						<a href="#" onclick="return false" @if(isset($form)&&in_array($group->id,$form->groups())) class="active" @endif >✔︎</a>
						<span>{{$group->name}}</span>
					</label>
				@endforeach
			</div>
			<span class="description">아무 것도 선택하지 않으면 비회원도 폼 주소를 통해 답변을 할 수 있습니다.</span>
			
			<div class="selects">
				<span>메일 발송</span>
				@foreach($groups as $group)
					<label class="select_wrap" onclick="$(this).find('input').each(function(){$(this).prop('checked',!$(this).prop('checked'));});$(this).find('a').toggleClass('active');return false">
						<input type="checkbox" name="group_mail[]" value="{{$group->id}}" class="blind" @if(isset($form)&&in_array($group->id,$form->groups_mail())) checked @endif >
						<a href="#" onclick="return false" @if(isset($form)&&in_array($group->id,$form->groups_mail())) class="active" @endif >✔︎</a>
						<span>{{$group->name}}</span>
					</label>
				@endforeach
			</div>
			<span class="description">답변이 작성되면 해당 그룹에 속해 있는 회원에게 이메일이 발송됩니다.</span>
			
			<div class="selects">
				<span>질문</span>
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
						@if(isset($form)&&$form->questions())
							@foreach($form->questions() as $extravar)
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
			<span class="description" style="display:inline-block">선택 항목은 각 값들은 기본 값에 | (파이프 문자)로 구분하세요. 첨부에는 기본 값이 적용되지 않습니다.@if(isset($form))<br>항목을 삭제해도 답변에 이미 작성된 항목은 삭제되지 않으나, 이름이나 종류를 수정한 경우에는 수정된 값이 반영됩니다.@endif</span>
			
			<label class="input_wrap">
				<input type="datetime-local" name="start_at" value="@if(isset($form)){{date('Y-m-d\TH:i',strtotime($form->start_at))}}@else{{date('Y-m-d\TH:i')}}@endif">
				<span>시작 시각</span>
			</label>
			
			<label class="input_wrap">
				<input type="datetime-local" name="end_at" value="@if(isset($form)){{date('Y-m-d\TH:i',strtotime($form->end_at))}}@else{{date('Y-m-d',strtotime('+10years')).'T23:59'}}@endif">
				<span>종료 시각</span>
			</label>
			
			<div class="btnArea">
				@if(isset($form))
					<a href="{{url('/'.$form->url)}}" class="button black" target="_blank" style="float:left">폼 보기</a>
					<button type="button" class="button white" onclick="if(confirm('정말로 삭제하시겠습니까?'))$('#form{{$form->id}}delete').submit();return false"><span>폼 삭제</span></button>
					<a href="{{url('/admin/form')}}{{$_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''}}" class="button gray">돌아가기</a>
					<button type="submit" class="button blue"><span>저장하기</span></button>
				@else
					<button type="submit" class="button blue"><span>폼 추가하기</span></button>
				@endif
			</div>
		</div>
	</form>
	
	@if(isset($form))
		<form id="form{{$form->id}}delete" class="form" method="post" action="{{url('/admin/form/delete')}}{{$_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''}}">
			{!!csrf_field()!!}
			<input type="hidden" name="id" value="{{$form->id}}">
		</form>
	@endif

@endsection