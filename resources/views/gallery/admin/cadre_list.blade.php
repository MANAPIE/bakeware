@extends('admin.layout',['title'=>'&gt; 갤러리 &gt; '.$gallery->name.' &gt; 액자 목록'])

@section('head')
	@parent
	<script type="text/javascript" src="{{url('/script/jquery-ui.min.js.bakeware')}}"></script>
	<script type="text/javascript" src="{{url('/script/jquery.ui.touch-punch.min.js.bakeware')}}"></script>
	<script>
	$(function(){
		$('.table_wrap tbody').css('cursor','ns-resize');
		$('.table_wrap tbody').sortable({
			helper:function(e,tr){
				var $originals=tr.children();
				var $helper=tr.clone();
				$helper.children().each(function(index){
				  $(this).width($originals.eq(index).width());
				});
				return $helper;
			},
			change:function(e){
				$('#btnDelete').hide();
				$('#btnOrder').show();
				$('#delete').attr('action','{{url('/admin/gallery/cadres/order')}}');
				$('.cadre_no input').each(function(){
					$(this).attr('type','hidden')
				});
			},
		});
	});
	</script>
@stop

@section('body')
	<h3 class="menu_title">{{$gallery->name}} 액자 목록</h3>
	
	@if(session('message'))
		<div class="message success">{!!session('message')!!}</div>
	@endif
	
	<form method="post" id="delete" action="{{url('/admin/gallery/cadres/delete')}}{{isset($_SERVER['QUERY_STRING'])?'?'.$_SERVER['QUERY_STRING']:''}}">
		{!!csrf_field()!!}
		<input type="hidden" name="gallery" value="{{$gallery->id}}">
		
		<div class="table_wrap">
			<table>
				<thead>
					<tr>
						<th></th>
						@if(count($gallery->categories()))
							<th>분류</th>
						@endif
						<th></th>
						<th>조회 수</th>
						<th>만든이</th>
					</tr>
				</thead>
				<tbody>
					@foreach($gallery->cadres(0) as $d)
					<tr>
						<td class="no cadre_no">
							<input type="checkbox" name="cadres[]" value="{{$d->id}}">
						</td>
						@if(count($gallery->categories()))
							<td class="date">
								@if($d->category())
									{{$d->category()->name}}
								@endif
							</td>
						@endif
						<td class="link thumbnails"><a href="{{url('/admin/gallery/'.$gallery->id.'/cadres/'.$d->id)}}">
							@foreach($d->files() as $file)
								<img src="/file/thumb/{{$file->name}}" alt="">
							@endforeach
							&nbsp;<span class="arrow">&gt;</span></a></td>
						<td class="count link"><a href="{{url('/'.$d->gallery()->url.'/'.$d->id)}}" target="_blank">
							{{$d->count_read}}
						&nbsp;<span class="arrow">&gt;</span></a></td>
						<td class="date">
							@if($d->author())
								{{$d->author()->nickname}}
							@else
								<i>비회원</i>
							@endif
						</td>
					</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</form>
	
	<div class="search_wrap" style="margin-bottom:0">
		<form method="get" action="{{url('/admin/gallery/cadre')}}{{isset($_SERVER['QUERY_STRING'])?'?'.$_SERVER['QUERY_STRING']:''}}">
			<label class="input_wrap">
				<input type="text" name="keyword" value="@if(isset($_GET['keyword'])){{$_GET['keyword']}}@endif">
				<span>검색</span>
				<button type="submit" class="blind">검색하기</button>
			</label>
		</form>
	</div>
	
	@section('pagination')
	<?php $link_limit=5; ?>
	<ul class="pagination">
		<li class="description">액자를 드래그하여 순서를 조절하고 [순서 저장]을 눌러 저장합니다.</li>
	</ul>
	@show

	<div class="btnArea" style="margin:0 5px">
		<a href="{{url('/admin/gallery/'.$gallery->id)}}{{isset($_SERVER['QUERY_STRING'])?'?'.$_SERVER['QUERY_STRING']:''}}" class="button gray" style="float:left">돌아가기</a>
		
		<span id="btnDelete">
			<a href="{{url('/admin/gallery/'.$gallery->id.'/cadres/create')}}{{isset($_SERVER['QUERY_STRING'])?'?'.$_SERVER['QUERY_STRING']:''}}" class="button blue">액자 만들기</a>
			<button type="button" class="button gray" onclick="if($('input:checked').length<1){alert('삭제할 액자을 선택해주세요.');return false;} if(confirm('정말로 삭제하시겠습니까?'))$('#delete').submit();return false"><span>일괄 삭제</span></button>
		</span>
		
		<span id="btnOrder" style="display:none">
			<a href="#" onclick="location.reload();return false" class="button gray">취소</a>
			<button type="button" class="button red" onclick="$('#delete').submit();return false"><span>순서 저장</span></button>
		</span>
	</div>
	
@endsection