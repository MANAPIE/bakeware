@extends('admin.layout',['title'=>isset($gallery)?'&gt; 갤러리 &gt; '.$gallery->name:'&gt; 갤러리 &gt; 추가'])

@section('head')
	@parent
	<script type="text/javascript" src="{{url('/ckeditor/ckeditor.js')}}"></script>
	<script type="text/javascript" src="{{url('/script/jquery-ui.min.js.bakeware')}}"></script>
	<script type="text/javascript" src="{{url('/script/jquery.ui.touch-punch.min.js.bakeware')}}"></script>
	<script>
	$(function(){
		CKEDITOR.replace('content',{});
		$('.page_list ul').sortable();
	});
	</script>
@stop

@section('body')
	<h3 class="menu_title">갤러리 @if(isset($gallery))관리@else추가@endif</h3>

	@if(session('message'))
		<div class="message success">{!!session('message')!!}</div>
	@endif
	
	<form class="form" method="post" action="{{url('/admin/gallery/'.(isset($gallery)?'edit':'create'))}}{{isset($_SERVER['QUERY_STRING'])?'?'.$_SERVER['QUERY_STRING']:''}}">
		<div class="form_wrap">
			{!!csrf_field()!!}
			@if(isset($gallery))
				<input type="hidden" name="id" value="{{$gallery->id}}">
			@endif
			
			<label class="input_wrap">
				<input type="text" name="domain" value="@if(isset($gallery)){{$gallery->domain}}@endif">
				<span>도메인</span>
			</label>
			<span class="description">특정 도메인에서만 접근할 수 있게 합니다. 프로토콜(https://)과 맨 뒤 슬래시(/)는 제외하고 입력해주세요. 비워두는 경우 연결된 도메인에서 접속이 가능합니다.</span>
			
			<label class="input_wrap">
				<input type="text" name="url" value="@if(isset($gallery)){{$gallery->url}}@endif">
				<span>주소</span>
			</label>
			<span class="description">'{{url('')}}/주소'으로 접속할 수 있습니다.</span>
			
			<script>
			$(function(){
				@if(!isset($gallery))
					$.get('{{url('/admin/check/url/')}}'+'?url='+encodeURI($('input[name=domain]').val()+'/'+$('input[name=url]').val()),function(data){
						if(data=='Y')
							$('input[name=url')[0].setCustomValidity('이미 존재하는 주소입니다.');
						else
							$('input[name=url')[0].setCustomValidity('');
					});
				@endif
				$('input[name=url],input[name=domain]').keyup(function(){
					$.get('{{url('/admin/check/url/')}}'+'?url='+encodeURI($('input[name=domain]').val()+'/'+$('input[name=url]').val())@if(isset($gallery))+'&original='+encodeURI('{{$gallery->url}}')@endif,function(data){
						if(data=='Y')
							$('input[name=url]')[0].setCustomValidity('이미 존재하는 주소입니다.');
						else
							$('input[name=url]')[0].setCustomValidity('');
					});
				});
			});
			</script>
			
			<label class="input_wrap">
				<input type="text" name="name" value="@if(isset($gallery)){{$gallery->name}}@endif">
				<span>이름</span>
			</label>
			
			<div class="selects" id="layout">
				<span>레이아웃</span>
				<label class="select_wrap" onclick="$('#layout a').removeClass('active');$(this).find('a').addClass('active')">
					<input type="radio" name="layout" value="0" class="blind" @if(!isset($gallery)||!$gallery->layout) checked @endif>
					<a href="#" onclick="$(this).parent().click();return false" @if(!isset($gallery)||!$gallery->layout) class="active" @endif>✔︎</a>
					<span>(없음)</span>
				</label>
				@foreach($layouts as $layout)
					<label class="select_wrap" onclick="$('#layout a').removeClass('active');$(this).find('a').addClass('active')">
						<input type="radio" name="layout" value="{{$layout->id}}" class="blind" @if(isset($gallery)&&$gallery->layout==$layout->id) checked @endif>
						<a href="#" onclick="$(this).parent().click();return false" @if(isset($gallery)&&$gallery->layout==$layout->id) class="active" @endif>✔︎</a>
						<span>{{$layout->name}}</span>
					</label>
				@endforeach
			</div>
				
			<div class="selects" id="skin">
				<span>스킨</span>
				@foreach($paths as $path)
					<label class="select_wrap" onclick="$('#skin a').removeClass('active');$(this).find('a').addClass('active')">
						<input type="radio" name="skin" value="{{$path}}" class="blind" @if((!isset($gallery)&&$path=='default')||(isset($gallery)&&$gallery->skin==$path)) checked @endif>
						<a href="#" onclick="$(this).parent().click();return false" @if((!isset($gallery)&&$path=='default')||(isset($gallery)&&$gallery->skin==$path)) class="active" @endif>✔︎</a>
						<span>{{$path}}</span>
					</label>
				@endforeach
			</div>
			
			<div class="selects">
				<span>목록 권한</span>
				@foreach($groups as $group)
					<label class="select_wrap" onclick="$(this).find('input').each(function(){$(this).prop('checked',!$(this).prop('checked'));});$(this).find('a').toggleClass('active');return false">
						<input type="checkbox" name="group[]" value="{{$group->id}}" class="blind" @if(isset($gallery)&&in_array($group->id,$gallery->groups())) checked @endif >
						<a href="#" onclick="return false" @if(isset($gallery)&&in_array($group->id,$gallery->groups())) class="active" @endif >✔︎</a>
						<span>{{$group->name}}</span>
					</label>
				@endforeach
			</div>
			<span class="description">아무 것도 선택하지 않으면 비회원도 갤러리 주소를 통해 목록에 접근할 수 있습니다.</span>
			
			<div class="selects">
				<span>열람 권한</span>
				@foreach($groups as $group)
					<label class="select_wrap" onclick="$(this).find('input').each(function(){$(this).prop('checked',!$(this).prop('checked'));});$(this).find('a').toggleClass('active');return false">
						<input type="checkbox" name="group_read[]" value="{{$group->id}}" class="blind" @if(isset($gallery)&&in_array($group->id,$gallery->groups_read())) checked @endif >
						<a href="#" onclick="return false" @if(isset($gallery)&&in_array($group->id,$gallery->groups_read())) class="active" @endif >✔︎</a>
						<span>{{$group->name}}</span>
					</label>
				@endforeach
			</div>
			<span class="description">아무 것도 선택하지 않으면 비회원도 액자 주소를 통해 액자를 열람할 수 있습니다.</span>
			
			<div class="selects">
				<span>작성 권한</span>
				@foreach($groups as $group)
					<label class="select_wrap" onclick="$(this).find('input').each(function(){$(this).prop('checked',!$(this).prop('checked'));});$(this).find('a').toggleClass('active');return false">
						<input type="checkbox" name="group_cadre[]" value="{{$group->id}}" class="blind" @if(isset($gallery)&&in_array($group->id,$gallery->groups_cadre())) checked @endif >
						<a href="#" onclick="return false" @if(isset($gallery)&&in_array($group->id,$gallery->groups_cadre())) class="active" @endif >✔︎</a>
						<span>{{$group->name}}</span>
					</label>
				@endforeach
			</div>
			<span class="description">아무 것도 선택하지 않으면 비회원도 액자 작성 주소를 통해 액자를 만들 수 있습니다.</span>
			
			<div class="selects">
				<span>분류</span>
				<div class="page_list">
					<ul style="padding-top:8px">
						@if(isset($gallery)&&$gallery->categories())
							@foreach($gallery->categories() as $category)
								<li>
									<input type="hidden" name="category[]" value="{{$category->id}}">
									<div class="item_wrap">
										<div class="item_full">
											<label class="input_wrap white">
												<input type="text" name="category_name[]" value="@if($category->name){{$category->name}}@endif">
												<span>이름</span>
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
						<li class="blind" id="blank">
							<input type="hidden" name="category[]" value="0">
							<div class="item_wrap">
								<div class="item_full">
									<label class="input_wrap white">
										<input type="text" name="category_name[]" value="">
										<span>이름</span>
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
						<button type="button" class="button gray" onclick="$('#blank').before($('#blank').clone().removeAttr('id').removeClass('blind'));return false">분류 추가</button>
					</div>
				</div>
			</div>
			<span class="description">분류가 있으면 액자를 만들 때 분류 선택은 필수입니다.@if(isset($gallery)) 분류를 삭제해도 해당 분류의 액자는 삭제되지 않습니다.@endif</span>
			
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
						@if(isset($gallery)&&$gallery->extravars())
							@foreach($gallery->extravars() as $extravar)
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
			<span class="description" style="display:inline-block">액자를 만들 때 제목과 내용 외에 추가적인 항목을 작성하도록 할 수 있습니다. 선택 항목은 각 값들은 기본 값에 | (파이프 문자)로 구분하세요. 첨부에는 기본 값이 적용되지 않습니다.@if(isset($gallery))<br>항목을 삭제해도 액자에 이미 작성된 항목은 삭제되지 않으나, 이름이나 종류를 수정한 경우에는 수정된 값이 반영됩니다.@endif</span>
			
			<label class="input_wrap label">
				<span>양식</span>
			</label>
			<div class="editor_wrap">
				<textarea id="content" name="content">@if(isset($gallery)){{$gallery->content}}@endif</textarea>
			</div>
			<span class="description">액자를 만들 때 내용에 미리 나타나는 양식입니다.</span>
			
			<div class="btnArea">
				@if(isset($gallery))
					<a href="{{$gallery->url()}}" class="button black" target="_blank" style="float:left">갤러리 보기</a>
					<a href="{{url('/admin/gallery/'.$gallery->id.'/cadres')}}{{isset($_SERVER['QUERY_STRING'])?'?'.$_SERVER['QUERY_STRING']:''}}" class="button gray" style="float:left">액자 목록</a>
					<button type="button" class="button white" onclick="if(confirm('정말로 삭제하시겠습니까?'))$('#gallery{{$gallery->id}}delete').submit();return false"><span>갤러리 삭제</span></button>
					<a href="{{url('/admin/gallery')}}{{isset($_SERVER['QUERY_STRING'])?'?'.$_SERVER['QUERY_STRING']:''}}" class="button gray">돌아가기</a>
					<button type="submit" class="button blue"><span>저장하기</span></button>
				@else
					<button type="submit" class="button blue"><span>갤러리 추가하기</span></button>
				@endif
			</div>
		</div>
	</form>
	
	@if(isset($gallery))
		<form id="gallery{{$gallery->id}}delete" class="form" method="post" action="{{url('/admin/gallery/delete')}}{{isset($_SERVER['QUERY_STRING'])?'?'.$_SERVER['QUERY_STRING']:''}}">
			{!!csrf_field()!!}
			<input type="hidden" name="id" value="{{$gallery->id}}">
		</form>
	@endif

@endsection