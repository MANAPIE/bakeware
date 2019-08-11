@extends('admin.layout',['title'=>isset($page)?'&gt; 원페이지 &gt; '.$page->name:'&gt; 원페이지 &gt; 추가'])

@section('head')
	@parent
	<script type="text/javascript" src="{{url('/script/jquery-ui.min.js.bakeware')}}"></script>
	<script type="text/javascript" src="{{url('/script/jquery.ui.touch-punch.min.js.bakeware')}}"></script>
	<script>
	$(function(){
		$('.page_list ul').sortable();
	});
	</script>
@stop

@section('body')
	<h3 class="menu_title">원페이지 @if(isset($page))관리@else추가@endif</h3>

	@if(session('message'))
		<div class="message success">{!!session('message')!!}</div>
	@endif
	
	<form class="form" method="post" action="{{url('/admin/page/onepage/'.(isset($page)?'edit':'create'))}}{{isset($_SERVER['QUERY_STRING'])?'?'.$_SERVER['QUERY_STRING']:''}}" enctype="multipart/form-data" onsubmit="setBackground()">
		<div class="form_wrap">
			{!!csrf_field()!!}
			@if(isset($page))
				<input type="hidden" name="id" value="{{$page->id}}">
			@endif
			
			<label class="input_wrap">
				<input type="text" name="domain" value="@if(isset($page)){{$page->domain}}@endif">
				<span>도메인</span>
			</label>
			<span class="description">특정 도메인에서만 접근할 수 있게 합니다. 프로토콜(https://)과 맨 뒤 슬래시(/)는 제외하고 입력해주세요. 비워두는 경우 연결된 도메인에서 접속이 가능합니다.</span>
			
			<label class="input_wrap">
				<input type="text" name="url" value="@if(isset($page)){{$page->url}}@endif">
				<span>주소</span>
			</label>
			<span class="description">'{{url('')}}/주소'으로 접속할 수 있습니다.</span>
			
			<script>
			$(function(){
				@if(!isset($page))
					$.get('{{url('/admin/check/url/')}}'+'?url='+encodeURI($('input[name=domain]').val()+'/'+$('input[name=url]').val()),function(data){
						if(data=='Y')
							$('input[name=url')[0].setCustomValidity('이미 존재하는 주소입니다.');
						else
							$('input[name=url')[0].setCustomValidity('');
					});
				@endif
				$('input[name=url],input[name=domain]').keyup(function(){
					$.get('{{url('/admin/check/url/')}}'+'?url='+encodeURI($('input[name=domain]').val()+'/'+$('input[name=url]').val())@if(isset($page))+'&original='+encodeURI('{{$page->url}}')@endif,function(data){
						if(data=='Y')
							$('input[name=url]')[0].setCustomValidity('이미 존재하는 주소입니다.');
						else
							$('input[name=url]')[0].setCustomValidity('');
					});
				});
			});
			</script>
			
			<label class="input_wrap">
				<input type="text" name="name" value="@if(isset($page)){{$page->name}}@endif">
				<span>이름</span>
			</label>
			
			<div class="selects" id="layout">
				<span>레이아웃</span>
				<label class="select_wrap" onclick="$('#layout a').removeClass('active');$(this).find('a').addClass('active')">
					<input type="radio" name="layout" value="0" class="blind" @if(!isset($page)||!$page->layout) checked @endif>
					<a href="#" onclick="$(this).parent().click();return false" @if(!isset($page)||!$page->layout) class="active" @endif>✔︎</a>
					<span>(없음)</span>
				</label>
				@foreach($layouts as $layout)
					<label class="select_wrap" onclick="$('#layout a').removeClass('active');$(this).find('a').addClass('active')">
						<input type="radio" name="layout" value="{{$layout->id}}" class="blind" @if(isset($page)&&$page->layout==$layout->id) checked @endif>
						<a href="#" onclick="$(this).parent().click();return false" @if(isset($page)&&$page->layout==$layout->id) class="active" @endif>✔︎</a>
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
			<span class="description">개별 페이지의 접근 권한을 무시합니다. 아무 것도 선택하지 않으면 누구나 주소를 통해 접근할 수 있습니다.</span>
			
			<script>
			function addPage(id,name){
				$('.page_list ul').prepend(`
					<li>
						<input type="hidden" name="page[]" value="`+id+`">
						<div class="item_wrap">
							<div class="item_name">
								<label class="input_wrap white">
									<input type="text" value="`+name+`" disabled>
									<span>페이지</span>
								</label>
							</div>
							<div class="item_url">
								<label class="input_wrap white">
									<input type="file" name="background" accept="image/*">
									<input type="hidden" name="background_original" value="">
									<span>배경</span>
								</label>
							</div>
							<div class="item_options">
								<label class="select_wrap">
									<a href="#" class="active" onclick="$(this).parents(\'li\').remove();return false">&times;</a>
									<span>&nbsp;</span>
								</label>
							</div>
							<div class="clear"></div>
						</div>
					</li>
				`);
			}
			
			function setBackground(){
				var i=0;
				$('.page_list li').each(function(){
					$(this).find('input[name=background]').attr('name','background'+i);
					$(this).find('input[name=background_original]').attr('name','background_original'+i);
					i++;
				});
			}
			</script>
			
			<div class="selects">
				<span>페이지</span>
				@foreach($pages as $p)
					<label class="select_wrap" onclick="addPage('{{$p->id}}','{{$p->name()}}');return false">
						<a href="#" class="active" onclick="return false">+</a>
						<span>{{$p->name()}}</span>
					</label>
				@endforeach
				
				<div class="page_list">
					<ul>
					@if(isset($page))
						@foreach($page->pages() as $p)
							<li>
								<input type="hidden" name="page[]" value="{{$p['page']->id}}">
								<div class="item_wrap">
									<div class="item_name">
										<label class="input_wrap white">
											<input type="text" value="{{$p['page']->name()}}" disabled>
											<span>페이지</span>
										</label>
									</div>
									<div class="item_url">
										<label class="input_wrap white">
											@if($p['background'])<div class="thumbnail"><img src="{{$p['background']}}" alt=""></div>@endif
											<input type="file" name="background" accept="image/*">
											<input type="hidden" name="background_original" value="{{$p['background']}}">
											<span>배경</span>
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
					</ul>
				</div>
			</div>
			<span class="description">업로드한 배경에 페이지의 내용이 얹어지며, 드래그해서 위치를 조절할 수 있습니다.</span>
			
			<div class="btnArea">
				@if(isset($page))
					<a href="{{$page->url()}}" class="button black" target="_blank" style="float:left">페이지 보기</a>
					<button type="button" class="button white" onclick="if(confirm('정말로 삭제하시겠습니까?'))$('#page{{$page->id}}delete').submit();return false"><span>페이지 삭제</span></button>
					<a href="{{url('/admin/page')}}{{isset($_SERVER['QUERY_STRING'])?'?'.$_SERVER['QUERY_STRING']:''}}" class="button gray">돌아가기</a>
					<button type="submit" class="button blue"><span>저장하기</span></button>
				@else
					<button type="submit" class="button blue"><span>페이지 추가하기</span></button>
				@endif
			</div>
		</div>
	</form>
	
	@if(isset($page))
		<form id="page{{$page->id}}delete" class="form" method="post" action="{{url('/admin/page/onepage/delete')}}{{isset($_SERVER['QUERY_STRING'])?'?'.$_SERVER['QUERY_STRING']:''}}">
			{!!csrf_field()!!}
			<input type="hidden" name="id" value="{{$page->id}}">
		</form>
	@endif

@endsection