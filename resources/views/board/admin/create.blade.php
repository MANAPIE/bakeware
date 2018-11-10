@extends('admin.layout',['title'=>isset($board)?'&gt; 게시판 &gt; '.$board->name:'&gt; 게시판 &gt; 추가'])

@section('head')
	@parent
	<script type="text/javascript" src="{{url('/ckeditor/ckeditor.js')}}"></script>
	<script type="text/javascript" src="{{url('/script/jquery-ui.min.js')}}"></script>
	<script type="text/javascript" src="{{url('/script/jquery.ui.touch-punch.min.js')}}"></script>
	<script>
	$(function(){
		CKEDITOR.replace('content',{});
		$('.page_list ul').sortable();
	});
	</script>
@stop

@section('body')
	<h3 class="menu_title">게시판 @if(isset($board))관리@else추가@endif</h3>

	@if(session('message'))
		<div class="message success">{!!session('message')!!}</div>
	@endif
	
	<form class="form" method="post" action="{{url('/admin/board/'.(isset($board)?'edit':'create'))}}{{$_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''}}">
		<div class="form_wrap">
			{!!csrf_field()!!}
			@if(isset($board))
				<input type="hidden" name="id" value="{{$board->id}}">
			@endif
			
			<label class="input_wrap">
				<input type="text" name="url" value="@if(isset($board)){{$board->url}}@endif">
				<span>주소</span>
			</label>
			<span class="description">'{{url('')}}/주소'으로 접속할 수 있습니다.</span>
			
			<script>
			$(function(){
				@if(!isset($board))
					$.get('{{url('/admin/check/url/')}}'+'?url='+$(this).val(),function(data){
						if(data=='Y')
							$('input[name=url')[0].setCustomValidity('이미 존재하는 주소입니다.');
						else
							$('input[name=url')[0].setCustomValidity('');
					});
				@endif
				$('input[name=url').keyup(function(){
					$.get('{{url('/admin/check/url/')}}'+'?url='+$(this).val()@if(isset($board))+'&original={{$board->url}}'@endif,function(data){
						if(data=='Y')
							$('input[name=url')[0].setCustomValidity('이미 존재하는 주소입니다.');
						else
							$('input[name=url')[0].setCustomValidity('');
					});
				});
			});
			</script>
			
			<label class="input_wrap">
				<input type="text" name="name" value="@if(isset($board)){{$board->name}}@endif">
				<span>이름</span>
			</label>
			
			<div class="selects" id="layout">
				<span>레이아웃</span>
				<label class="select_wrap" onclick="$('#layout a').removeClass('active');$(this).find('a').addClass('active')">
					<input type="radio" name="layout" value="0" class="blind" @if(!isset($board)||!$board->layout) checked @endif>
					<a href="#" onclick="$(this).parent().click();return false" @if(!isset($board)||!$board->layout) class="active" @endif>✔︎</a>
					<span>(없음)</span>
				</label>
				@foreach($layouts as $layout)
					<label class="select_wrap" onclick="$('#layout a').removeClass('active');$(this).find('a').addClass('active')">
						<input type="radio" name="layout" value="{{$layout->id}}" class="blind" @if(isset($board)&&$board->layout==$layout->id) checked @endif>
						<a href="#" onclick="$(this).parent().click();return false" @if(isset($board)&&$board->layout==$layout->id) class="active" @endif>✔︎</a>
						<span>{{$layout->name}}</span>
					</label>
				@endforeach
			</div>
				
			<div class="selects" id="skin">
				<span>스킨</span>
				@foreach($paths as $path)
					<label class="select_wrap" onclick="$('#skin a').removeClass('active');$(this).find('a').addClass('active')">
						<input type="radio" name="skin" value="{{$path}}" class="blind" @if((!isset($board)&&$path=='default')||(isset($board)&&$board->skin==$path)) checked @endif>
						<a href="#" onclick="$(this).parent().click();return false" @if((!isset($board)&&$path=='default')||(isset($board)&&$board->skin==$path)) class="active" @endif>✔︎</a>
						<span>{{$path}}</span>
					</label>
				@endforeach
			</div>
			
			<div class="selects" id="anonymous">
				<span>익명성</span>
				<label class="select_wrap" onclick="$('#anonymous a').removeClass('active');$(this).find('a').addClass('active')">
					<input type="radio" name="anonymous" value="0" class="blind" @if(!isset($board)||(isset($board)&&$board->anonymous==0)) checked @endif>
					<a href="#" onclick="$(this).parent().click();return false" @if(!isset($board)||(isset($board)&&$board->anonymous==0)) class="active" @endif>✔︎</a>
					<span>익명 아님</span>
				</label>
				<label class="select_wrap" onclick="$('#anonymous a').removeClass('active');$(this).find('a').addClass('active')">
					<input type="radio" name="anonymous" value="1" class="blind" @if(isset($board)&&$board->anonymous==1) checked @endif>
					<a href="#" onclick="$(this).parent().click();return false" @if(isset($board)&&$board->anonymous==1) class="active" @endif>✔︎</a>
					<span>관리자만 익명 아님</span>
				</label>
				<label class="select_wrap" onclick="$('#anonymous a').removeClass('active');$(this).find('a').addClass('active')">
					<input type="radio" name="anonymous" value="2" class="blind" @if(isset($board)&&$board->anonymous==2) checked @endif>
					<a href="#" onclick="$(this).parent().click();return false" @if(isset($board)&&$board->anonymous==2) class="active" @endif>✔︎</a>
					<span>모두 다 익명</span>
				</label>
			</div>
			
			<div class="selects">
				<span>목록 권한</span>
				@foreach($groups as $group)
					<label class="select_wrap" onclick="$(this).find('input').each(function(){$(this).prop('checked',!$(this).prop('checked'));});$(this).find('a').toggleClass('active');return false">
						<input type="checkbox" name="group[]" value="{{$group->id}}" class="blind" @if(isset($board)&&in_array($group->id,$board->groups())) checked @endif >
						<a href="#" onclick="return false" @if(isset($board)&&in_array($group->id,$board->groups())) class="active" @endif >✔︎</a>
						<span>{{$group->name}}</span>
					</label>
				@endforeach
			</div>
			<span class="description">아무 것도 선택하지 않으면 비회원도 게시판 주소를 통해 목록에 접근할 수 있습니다.</span>
			
			<div class="selects">
				<span>열람 권한</span>
				@foreach($groups as $group)
					<label class="select_wrap" onclick="$(this).find('input').each(function(){$(this).prop('checked',!$(this).prop('checked'));});$(this).find('a').toggleClass('active');return false">
						<input type="checkbox" name="group_read[]" value="{{$group->id}}" class="blind" @if(isset($board)&&in_array($group->id,$board->groups_read())) checked @endif >
						<a href="#" onclick="return false" @if(isset($board)&&in_array($group->id,$board->groups_read())) class="active" @endif >✔︎</a>
						<span>{{$group->name}}</span>
					</label>
				@endforeach
			</div>
			<span class="description">아무 것도 선택하지 않으면 비회원도 게시글 주소를 통해 게시글을 열람할 수 있습니다.</span>
			
			<div class="selects">
				<span>작성 권한</span>
				@foreach($groups as $group)
					<label class="select_wrap" onclick="$(this).find('input').each(function(){$(this).prop('checked',!$(this).prop('checked'));});$(this).find('a').toggleClass('active');return false">
						<input type="checkbox" name="group_document[]" value="{{$group->id}}" class="blind" @if(isset($board)&&in_array($group->id,$board->groups_document())) checked @endif >
						<a href="#" onclick="return false" @if(isset($board)&&in_array($group->id,$board->groups_document())) class="active" @endif >✔︎</a>
						<span>{{$group->name}}</span>
					</label>
				@endforeach
			</div>
			<span class="description">아무 것도 선택하지 않으면 비회원도 게시글 작성 주소를 통해 게시글을 작성할 수 있습니다.</span>
			
			<div class="selects">
				<span>댓글 권한</span>
				@foreach($groups as $group)
					<label class="select_wrap" onclick="$(this).find('input').each(function(){$(this).prop('checked',!$(this).prop('checked'));});$(this).find('a').toggleClass('active');return false">
						<input type="checkbox" name="group_comment[]" value="{{$group->id}}" class="blind" @if(isset($board)&&in_array($group->id,$board->groups_comment())) checked @endif >
						<a href="#" onclick="return false" @if(isset($board)&&in_array($group->id,$board->groups_comment())) class="active" @endif >✔︎</a>
						<span>{{$group->name}}</span>
					</label>
				@endforeach
			</div>
			<span class="description">아무 것도 선택하지 않으면 비회원도 게시글 주소를 통해 댓글을 작성할 수 있습니다.</span>
			
			<div class="selects">
				<span>메일 발송</span>
				@foreach($groups as $group)
					<label class="select_wrap" onclick="$(this).find('input').each(function(){$(this).prop('checked',!$(this).prop('checked'));});$(this).find('a').toggleClass('active');return false">
						<input type="checkbox" name="group_mail[]" value="{{$group->id}}" class="blind" @if(isset($board)&&in_array($group->id,$board->groups_mail())) checked @endif >
						<a href="#" onclick="return false" @if(isset($board)&&in_array($group->id,$board->groups_mail())) class="active" @endif >✔︎</a>
						<span>{{$group->name}}</span>
					</label>
				@endforeach
			</div>
			<span class="description">게시글이 작성되면 해당 그룹에 속해 있는 회원에게 이메일이 발송됩니다.</span>
			
			<div class="selects">
				<span>분류</span>
				<div class="page_list">
					<ul style="padding-top:8px">
						@if(isset($board)&&$board->categories())
							@foreach($board->categories() as $category)
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
			<span class="description">분류가 있으면 글을 작성할 때 분류 선택은 필수입니다.@if(isset($board)) 분류를 삭제해도 해당 분류의 게시글은 삭제되지 않습니다.@endif</span>
			
			<div class="selects" id="sort_by">
				<span>정렬 기준</span>
				<label class="select_wrap" onclick="$('#sort_by a').removeClass('active');$(this).find('a').addClass('active')">
					<input type="radio" name="sort_by" value="created_at" class="blind" @if(!isset($board)||(isset($board)&&$board->sort_by=='created_at')) checked @endif>
					<a href="#" onclick="$(this).parent().click();return false" @if(!isset($board)||(isset($board)&&$board->sort_by=='created_at')) class="active" @endif>✔︎</a>
					<span>작성 날짜</span>
				</label>
				<label class="select_wrap" onclick="$('#sort_by a').removeClass('active');$(this).find('a').addClass('active')">
					<input type="radio" name="sort_by" value="updated_at" class="blind" @if(isset($board)&&$board->sort_by=='updated_at') checked @endif>
					<a href="#" onclick="$(this).parent().click();return false" @if(isset($board)&&$board->sort_by=='updated_at') class="active" @endif>✔︎</a>
					<span>업데이트 날짜</span>
				</label>
				<label class="select_wrap" onclick="$('#sort_by a').removeClass('active');$(this).find('a').addClass('active')">
					<input type="radio" name="sort_by" value="title" class="blind" @if(isset($board)&&$board->sort_by=='title') checked @endif>
					<a href="#" onclick="$(this).parent().click();return false" @if(isset($board)&&$board->sort_by=='title') class="active" @endif>✔︎</a>
					<span>제목</span>
				</label>
				<label class="select_wrap" onclick="$('#sort_by a').removeClass('active');$(this).find('a').addClass('active')">
					<input type="radio" name="sort_by" value="count_read" class="blind" @if(isset($board)&&$board->sort_by=='count_read') checked @endif>
					<a href="#" onclick="$(this).parent().click();return false" @if(isset($board)&&$board->sort_by=='count_read') class="active" @endif>✔︎</a>
					<span>조회 수</span>
				</label>
				<label class="select_wrap" onclick="$('#sort_by a').removeClass('active');$(this).find('a').addClass('active')">
					<input type="radio" name="sort_by" value="count_comment" class="blind" @if(isset($board)&&$board->sort_by=='count_comment') checked @endif>
					<a href="#" onclick="$(this).parent().click();return false" @if(isset($board)&&$board->sort_by=='count_comment') class="active" @endif>✔︎</a>
					<span>댓글 수</span>
				</label>
			</div>
			
			<div class="selects" id="sort_order">
				<span>정렬 순서</span>
				<label class="select_wrap" onclick="$('#sort_order a').removeClass('active');$(this).find('a').addClass('active')">
					<input type="radio" name="sort_order" value="asc" class="blind" @if(isset($board)&&$board->sort_order=='asc') checked @endif>
					<a href="#" onclick="$(this).parent().click();return false" @if(isset($board)&&$board->sort_order=='asc') class="active" @endif>✔︎</a>
					<span>오름차순</span>
				</label>
				<label class="select_wrap" onclick="$('#sort_order a').removeClass('active');$(this).find('a').addClass('active')">
					<input type="radio" name="sort_order" value="desc" class="blind" @if(!isset($board)||(isset($board)&&$board->sort_order=='desc')) checked @endif>
					<a href="#" onclick="$(this).parent().click();return false" @if(!isset($board)||(isset($board)&&$board->sort_order=='desc')) class="active" @endif>✔︎</a>
					<span>내림차순</span>
				</label>
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
						@if(isset($board)&&$board->extravars())
							@foreach($board->extravars() as $extravar)
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
			<span class="description" style="display:inline-block">글을 작성할 때 제목과 내용 외에 추가적인 항목을 작성하도록 할 수 있습니다. 선택 항목은 각 값들은 기본 값에 | (파이프 문자)로 구분하세요. 첨부에는 기본 값이 적용되지 않습니다.@if(isset($board))<br>항목을 삭제해도 게시글에 이미 작성된 항목은 삭제되지 않으나, 이름이나 종류를 수정한 경우에는 수정된 값이 반영됩니다.@endif</span>
			
			<label class="input_wrap label">
				<span>양식</span>
			</label>
			<div class="editor_wrap">
				<textarea id="content" name="content">@if(isset($board)){{$board->content}}@endif</textarea>
			</div>
			<span class="description">게시글을 작성할 때 내용에 미리 나타나는 양식입니다.</span>
			
			<div class="btnArea">
				@if(isset($board))
					<a href="{{url('/'.$board->url)}}" class="button black" target="_blank" style="float:left">게시판 보기</a>
					<a href="{{url('/admin/board/'.$board->id.'/documents')}}{{$_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''}}" class="button gray" style="float:left">게시글 목록</a>
					<button type="button" class="button white" onclick="if(confirm('정말로 삭제하시겠습니까?'))$('#board{{$board->id}}delete').submit();return false"><span>게시판 삭제</span></button>
					<a href="{{url('/admin/board')}}{{$_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''}}" class="button gray">돌아가기</a>
					<button type="submit" class="button blue"><span>저장하기</span></button>
				@else
					<button type="submit" class="button blue"><span>게시판 추가하기</span></button>
				@endif
			</div>
		</div>
	</form>
	
	@if(isset($board))
		<form id="board{{$board->id}}delete" class="form" method="post" action="{{url('/admin/board/delete')}}{{$_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''}}">
			{!!csrf_field()!!}
			<input type="hidden" name="id" value="{{$board->id}}">
		</form>
	@endif

@endsection