@extends('admin.layout',['title'=>'&gt; 회원 &gt; 역할 관리'])

@section('body')
	<h3 class="menu_title">회원 역할 관리</h3>

	@if(session('message'))
		<div class="message success">{!!session('message')!!}</div>
	@endif
	
	<form class="form" method="post" action="{{url('/admin/user/group')}}">
		<div class="form_wrap">
			{!!csrf_field()!!}
			
			<div class="description">
				마스터, 관리자 역할는 이름을 바꾸거나 삭제할 수 없습니다.<br>
				회원 역할은 삭제할 수 없습니다.
			</div>
			
			<label class="input_wrap">
				<input type="text" value="마스터" disabled>
				<span>역할</span>
			</label>
			<label class="input_wrap">
				<input type="text" value="관리자" disabled>
				<span>역할</span>
			</label>
			
			@foreach($groups as $group)
				@if($group->id>2)
					<label class="input_wrap">
						<input type="hidden" name="id[]" value="{{$group->id}}">
						<input type="text" name="name[]" value="{{$group->name}}">
						<span>역할</span>
						@if($group->id>3)
							<a href="#" class="active" onclick="$('input[name=deleted]').val($('input[name=deleted]').val()+'{{$group->id}}|');$(this).parent().remove();return false">&times;</a>
						@endif
					</label>
				@endif
			@endforeach
			
			<label id="blank" class="input_wrap blind">
				<input type="hidden" name="id[]" value="0">
				<input type="text" name="name[]" value="">
				<span>역할</span>
				<a href="#" class="active" onclick="$(this).parent().remove();return false">&times;</a>
			</label>
			
			<div class="btnArea">
				<button type="button" class="button gray" onclick="$('#blank').before($('#blank').clone().removeAttr('id').removeClass('blind'));return false"><span>역할 추가</span></button>
				<button type="submit" class="button blue"><span>저장하기</span></button>
			</div>
			
			<input type="hidden" name="deleted" value="">
		</div>
	</form>
	
@endsection