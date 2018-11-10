@extends('admin.layout',['title'=>'&gt; 메뉴'])

@section('head')
	@parent
	<script type="text/javascript" src="{{url('/script/jquery-ui.min.js')}}"></script>
	<script type="text/javascript" src="{{url('/script/jquery.ui.touch-punch.min.js')}}"></script>
	<script>
	$(function(){
		$('.menu_list ul').sortable();
	});
	</script>
@stop

@section('body')
	<h3 class="menu_title">메뉴 관리</h3>

	@if(count($menus))
		@if(session('message'))
			<div class="message success">{!!session('message')!!}</div>
		@endif
		
		<div class="menus">
			@foreach($menus as $menu)
				<div class="menu">
					@if(session('message'.$menu->id))
						<div class="message success">{!!session('message'.$menu->id)!!}</div>
					@endif
					
					<form class="form" method="post" action="{{url('/admin/menu/edit')}}">
						<div class="menu_name">
							<label class="input_wrap light" onclick="$('.menu_list').not('#list{{$menu->id}}').slideUp();$('#list{{$menu->id}}').slideDown();">
								<input type="text" name="menu_name" value="{{$menu->name}}">
								<span>이름</span>
							</label>
						</div>
						<div class="menu_list" id="list{{$menu->id}}" @if(!session('message'.$menu->id))style="display:none"@endif>
							{!!csrf_field()!!}
							<input type="hidden" name="id" value="{{$menu->id}}">
							
							<ul id="menu{{$menu->id}}">
								@foreach($menu->items() as $item)
									<li>
										<input type="hidden" name="parent[]" value="{{$item->parent?'1':'0'}}">
										<div class="item_wrap">
											<div class="item_name">
												<label class="input_wrap white">
													<input type="text" name="name[]" value="{{$item->name}}">
													<span>이름</span>
												</label>
											</div>
											<div class="item_url">
												<label class="input_wrap white">
													<input type="text" name="url[]" value="{{$item->url}}">
													<span>주소</span>
												</label>
											</div>
											<div class="clear"></div>
										</div>
										<div class="item_options">
											<span class="select_wrap indent down">
												<a href="#" class="active" onclick="$(this).parents('li').find('input[name=\'parent[]\']').val('0');$(this).parents('li').removeClass('indent')">&lt;</a>
												<span>&nbsp;</span>
											</span>
											<span class="select_wrap indent up">
												<a href="#" class="active" onclick="$(this).parents('li').find('input[name=\'parent[]\']').val('1');$(this).parents('li').addClass('indent')">&gt;</a>
												<span>&nbsp;</span>
											</span>
											
											<label class="select_wrap" onclick="$(this).find('input').each(function(){$(this).prop('checked',!$(this).prop('checked'));});$(this).find('a').toggleClass('active');return false">
												<input type="checkbox" name="external[]" value="1" class="blind" @if($item->external) checked @endif>
												<input type="checkbox" name="external[]" value="0" class="blind" @if(!$item->external) checked @endif>
												<a href="#" onclick="return false" @if($item->external) class="active" @endif>✔︎</a>
												<span>새 창</span>
											</label>
											<label class="select_wrap">
												<a href="#" class="active" onclick="$(this).parents('li').remove();return false">&times;</a>
												<span>&nbsp;</span>
											</label>
										</div>
									</li>
									@foreach($item->submenu() as $i)
										<li class="indent">
											<input type="hidden" name="parent[]" value="{{$i->parent?'1':'0'}}">
											<div class="item_wrap">
												<div class="item_name">
													<label class="input_wrap white">
														<input type="text" name="name[]" value="{{$i->name}}">
														<span>이름</span>
													</label>
												</div>
												<div class="item_url">
													<label class="input_wrap white">
														<input type="text" name="url[]" value="{{$i->url}}">
														<span>주소</span>
													</label>
												</div>
												<div class="clear"></div>
											</div>
											<div class="item_options">
												<span class="select_wrap indent down">
													<a href="#" class="active" onclick="$(this).parents('li').find('input[name=\'parent[]\']').val('0');$(this).parents('li').removeClass('indent')">&lt;</a>
													<span>&nbsp;</span>
												</span>
												<span class="select_wrap indent up">
													<a href="#" class="active" onclick="$(this).parents('li').find('input[name=\'parent[]\']').val('1');$(this).parents('li').addClass('indent')">&gt;</a>
													<span>&nbsp;</span>
												</span>
												
												<label class="select_wrap" onclick="$(this).find('input').each(function(){$(this).prop('checked',!$(this).prop('checked'));});$(this).find('a').toggleClass('active');return false">
													<input type="checkbox" name="external[]" value="1" class="blind" @if($i->external) checked @endif>
													<input type="checkbox" name="external[]" value="0" class="blind" @if(!$i->external) checked @endif>
													<a href="#" onclick="return false" @if($i->external) class="active" @endif>✔︎</a>
													<span>새 창</span>
												</label>
												<label class="select_wrap">
													<a href="#" class="active" onclick="$(this).parents('li').remove();return false">&times;</a>
													<span>&nbsp;</span>
												</label>
											</div>
										</li>
									@endforeach
								@endforeach
								<li id="blank{{$menu->id}}" class="blind">
									<input type="hidden" name="parent[]" value="0">
									<div class="item_wrap">
										<div class="item_name">
											<label class="input_wrap white">
												<input type="text" name="name[]" value="">
												<span>이름</span>
											</label>
										</div>
										<div class="item_url">
											<label class="input_wrap white">
												<input type="text" name="url[]" value="">
												<span>주소</span>
											</label>
										</div>
										<div class="clear"></div>
									</div>
									<div class="item_options">
										<span class="select_wrap indent down">
											<a href="#" class="active" onclick="$(this).parents('li').find('input[name=\'parent[]\']').val('0');$(this).parents('li').removeClass('indent')">&lt;</a>
											<span>&nbsp;</span>
										</span>
										<span class="select_wrap indent up">
											<a href="#" class="active" onclick="$(this).parents('li').find('input[name=\'parent[]\']').val('1');$(this).parents('li').addClass('indent')">&gt;</a>
											<span>&nbsp;</span>
										</span>
											
										<label class="select_wrap" onclick="$(this).find('input').each(function(){$(this).prop('checked',!$(this).prop('checked'));});$(this).find('a').toggleClass('active');return false">
											<input type="checkbox" name="external[]" value="1" class="blind">
											<input type="checkbox" name="external[]" value="0" class="blind" checked>
											<a href="#" onclick="return false">✔︎</a>
											<span>새 창</span>
										</label>
										<label class="select_wrap">
											<a href="#" class="active" onclick="$(this).parents('li').remove();return false">&times;</a>
											<span>&nbsp;</span>
										</label>
									</div>
								</li>
							</ul>
							<div class="btnArea">
								<span class="description">메뉴는 최대 2단까지 가능하며, 드래그해서 위치를 조정할 수 있습니다.</span>
								
								<button type="button" class="button white" onclick="if(confirm('메뉴가 연결된 레이아웃이 있으면 해당 레이아웃을 사용하는 페이지에서는 에러가 발생할 수 있습니다. 정말로 삭제하시겠습니까?'))$('#menu{{$menu->id}}delete').submit();return false"><span>메뉴판 삭제</span></button>
								<button type="button" class="button gray" onclick="$('#blank{{$menu->id}}').before($('#blank{{$menu->id}}').clone().removeAttr('id').removeClass('blind'));return false"><span>메뉴 추가</span></button>
								<button type="submit" class="button blue"><span>메뉴 저장하기</span></button>
							</div>
						</div>
					</form>
					<form id="menu{{$menu->id}}delete" class="form" method="post" action="{{url('/admin/menu/delete')}}">
						{!!csrf_field()!!}
						<input type="hidden" name="id" value="{{$menu->id}}">
					</form>
				</div>
			@endforeach
		</div>
		
		<form class="form" method="post" action="{{url('/admin/menu/create')}}">
			<div class="form_wrap">
				<legend>새 메뉴판 만들기</legend>
				{!!csrf_field()!!}
				
				<label class="input_wrap" onclick="$('.menu_list').not('#list0').slideUp();$('#list0').slideDown()">
					<input type="text" name="name" value="">
					<span>이름</span>
				</label>
				
				<div class="menu_list" id="list0" style="margin-top:-10px;display:none">
					<div class="btnArea">
						<button type="submit" class="button blue"><span>메뉴판 만들기</span></button>
					</div>
				</div>
			</div>
		</form>
		
	@else
		<div class="no_item">
			<form class="form" method="post" action="{{url('/admin/menu/create')}}">
				<div class="form_wrap">
					<legend>첫 번째 메뉴를 만듭니다.</legend>
					{!!csrf_field()!!}
					
					<label class="input_wrap white">
						<input type="text" name="name" value="메인 메뉴">
						<span>이름</span>
					</label>
					
					<div class="btnArea">
						<button type="submit" class="button blue"><span>메뉴 만들기</span></button>
					</div>
				</div>
			</form>
		</div>
	@endif

@endsection