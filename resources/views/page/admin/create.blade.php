@extends('admin.layout',['title'=>isset($page)?'&gt; 페이지 &gt; '.$page->name():'&gt; 페이지 &gt; 추가'])

@section('head')
	@parent
	<script type="text/javascript" src="{{url('/ckeditor/ckeditor.js')}}"></script>
	<script type="text/javascript" src="{{url('/script/jquery.ui.touch-punch.min.js')}}"></script>
	<script>
	$(function(){
		CKEDITOR.replace('content',{});
	});
	</script>
@stop

@section('body')
	<h3 class="menu_title">페이지 @if(isset($page))관리@else추가@endif</h3>

	@if(session('message'))
		<div class="message success">{!!session('message')!!}</div>
	@endif
	
	<form class="form" method="post" action="{{url('/admin/page/'.(isset($page)?'edit':'create'))}}{{$_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''}}">
		<div class="form_wrap">
			{!!csrf_field()!!}
			@if(isset($page))
				<input type="hidden" name="id" value="{{$page->id}}">
			@endif
			
			<div class="selects" id="type">
				<span>종류</span>
				@if(!isset($page))
					<label class="select_wrap" onclick="$('#type a').removeClass('active');$(this).find('a').addClass('active');$('#content_inner').slideDown();$('#content_outer').slideUp()">
						<input type="radio" name="type" value="inner" class="blind" checked>
						<a href="#" onclick="$(this).parent().click();return false" class="active">✔︎</a>
						<span>편집</span>
					</label>
					<label class="select_wrap" onclick="$('#type a').removeClass('active');$(this).find('a').addClass('active');$('#content_inner').slideUp();$('#content_outer').slideDown()">
						<input type="radio" name="type" value="outer" class="blind">
						<a href="#" onclick="$(this).parent().click();return false">✔︎</a>
						<span>외부</span>
					</label>
				@else
					@if($page->type=='inner')
						<label class="select_wrap">
							<a href="#" onclick="$(this).parent().click();return false" class="active">✔︎</a>
							<span>편집</span>
						</label>
					@elseif($page->type=='outer')
						<label class="select_wrap">
							<a href="#" onclick="$(this).parent().click();return false" class="active">✔︎</a>
							<span>외부</span>
						</label>
					@endif
				@endif
			</div>
			<span class="description">페이지의 종류는 만들어진 이후에는 바꿀 수 없습니다.</span>
			
			<label class="input_wrap">
				<input type="text" name="url" value="@if(isset($page)){{$page->url}}@endif">
				<span>주소</span>
			</label>
			<span class="description">'{{url('')}}/주소'으로 접속할 수 있습니다.</span>
			
			<script>
			$(function(){
				@if(!isset($page))
					$.get('{{url('/admin/check/url/')}}'+'?url='+$(this).val(),function(data){
						if(data=='Y')
							$('input[name=url')[0].setCustomValidity('이미 존재하는 주소입니다.');
						else
							$('input[name=url')[0].setCustomValidity('');
					});
				@endif
				$('input[name=url').keyup(function(){
					$.get('{{url('/admin/check/url/')}}'+'?url='+$(this).val()@if(isset($page))+'&original={{$page->url}}'@endif,function(data){
						if(data=='Y')
							$('input[name=url')[0].setCustomValidity('이미 존재하는 주소입니다.');
						else
							$('input[name=url')[0].setCustomValidity('');
					});
				});
			});
			</script>
			
			<label class="input_wrap">
				<input type="text" name="name" value="@if(isset($page)){{$page->name()}}@endif">
				<span>이름</span>
			</label>
			
			<div class="selects" id="layout">
				<span>레이아웃</span>
				<label class="select_wrap" onclick="$('#layout a').removeClass('active');$(this).find('a').addClass('active')">
					<input type="radio" name="layout" value="0" class="blind" @if(!isset($page)||!$page->layout()) checked @endif>
					<a href="#" onclick="$(this).parent().click();return false" @if(!isset($page)||!$page->layout()) class="active" @endif>✔︎</a>
					<span>(없음)</span>
				</label>
				@foreach($layouts as $layout)
					<label class="select_wrap" onclick="$('#layout a').removeClass('active');$(this).find('a').addClass('active')">
						<input type="radio" name="layout" value="{{$layout->id}}" class="blind" @if(isset($page)&&$page->layout()==$layout->id) checked @endif>
						<a href="#" onclick="$(this).parent().click();return false" @if(isset($page)&&$page->layout()==$layout->id) class="active" @endif>✔︎</a>
						<span>{{$layout->name}}</span>
					</label>
				@endforeach
			</div>
			
			<div class="selects">
				<span>접근 권한</span>
				@foreach($groups as $group)
					<label class="select_wrap" onclick="$(this).find('input').each(function(){$(this).prop('checked',!$(this).prop('checked'));});$(this).find('a').toggleClass('active');return false">
						<input type="checkbox" name="group[]" value="{{$group->id}}" class="blind" @if(isset($page)&&in_array($group->id,$page->groups())) checked @endif >
						<a href="#" onclick="return false" @if(isset($page)&&in_array($group->id,$page->groups())) class="active" @endif >✔︎</a>
						<span>{{$group->name}}</span>
					</label>
				@endforeach
			</div>
			<span class="description">아무 것도 선택하지 않으면 누구나 주소를 통해 접근할 수 있습니다.</span>
			
			<div id="content_inner" @if(isset($page)&&$page->type=='outer')style="display:none"@endif>
				<label class="input_wrap label">
					<span>내용</span>
				</label>
				<div class="editor_wrap">
					<textarea id="content" name="content_inner">@if(isset($page)){{$page->content()}}@endif</textarea>
				</div>
			</div>
			
			<div id="content_outer" @if(!isset($page)||$page->type=='inner')style="display:none"@endif>
				<label class="input_wrap">
					<input type="text" name="content_outer" value="@if(isset($page)){{$page->content()}}@endif">
					<span>뷰</span>
				</label>
			</div>
			
			<div class="btnArea" style="margin-top:-10px">
				@if(isset($page))
					<a href="{{url('/'.$page->url)}}" class="button black" target="_blank" style="float:left">페이지 보기</a>
					<button type="button" class="button white" onclick="if(confirm('정말로 삭제하시겠습니까?'))$('#page{{$page->id}}delete').submit();return false"><span>페이지 삭제</span></button>
					<a href="{{url('/admin/page')}}{{$_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''}}" class="button gray">돌아가기</a>
					<button type="submit" class="button blue"><span>저장하기</span></button>
				@else
					<button type="submit" class="button blue"><span>페이지 추가하기</span></button>
				@endif
			</div>
		</div>
	</form>
	
	@if(isset($page))
		<form id="page{{$page->id}}delete" class="form" method="post" action="{{url('/admin/page/delete')}}{{$_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''}}">
			{!!csrf_field()!!}
			<input type="hidden" name="id" value="{{$page->id}}">
		</form>
	@endif

@endsection