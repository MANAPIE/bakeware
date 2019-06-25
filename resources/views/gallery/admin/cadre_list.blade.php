@extends('admin.layout',['title'=>'&gt; 갤러리 &gt; '.$gallery->name.' &gt; 액자 목록'])

@section('body')
	<h3 class="menu_title">{{$gallery->name}} 액자 목록</h3>
	
	@if(session('message'))
		<div class="message success">{!!session('message')!!}</div>
	@endif
	
	<form method="post" id="delete" action="{{url('/admin/gallery/cadres/delete')}}{{$_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''}}">
		{!!csrf_field()!!}
		<input type="hidden" name="gallery" value="{{$gallery->id}}">
		
		<div class="table_wrap">
			<table>
				<tr>
					<th></th>
					@if(count($gallery->categories()))
						<th>분류</th>
					@endif
					<th></th>
					<th>조회 수</th>
					<th>만든이</th>
				</tr>
				@foreach($gallery->cadres() as $d)
				<tr>
					<td class="no">
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
			</table>
		</div>
	</form>
	
	<div class="search_wrap" style="margin-bottom:0">
		<form method="get" action="{{url('/admin/gallery/cadre')}}{{$_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''}}">
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
		<?php
			$half_total_links=floor(($link_limit+2)/2);
			$from=$gallery->cadres()->currentPage()-$half_total_links;
			$to=$gallery->cadres()->currentPage()+$half_total_links;
			if($gallery->cadres()->currentPage()<$half_total_links){
				$to+=$half_total_links-$gallery->cadres()->currentPage();
			}
			if($gallery->cadres()->lastPage()-$gallery->cadres()->currentPage() < $half_total_links){
				$from-=$half_total_links-($gallery->cadres()->lastPage()-$gallery->cadres()->currentPage())-1;
			}
		?>
		@if($gallery->cadres()->currentPage()>ceil($link_limit/2))
			<li class="first"><a href="{{$gallery->cadres()->url($from)}}@foreach($_GET as $k=>$v){{$k!='page'?'&'.$k.'='.$v:''}}@endforeach">&lt;</a></li>
		@endif
		@for($i=1;$i<=$gallery->cadres()->lastPage();$i++)
			@if ($from < $i && $i < $to)
				<li class="{{($gallery->cadres()->currentPage() == $i)?' active':''}}">
					<a href="{{$gallery->cadres()->url($i)}}@foreach($_GET as $k=>$v){{$k!='page'?'&'.$k.'='.$v:''}}@endforeach">{{$i}}</a>
				</li>
			@endif
		@endfor
		@if($gallery->cadres()->currentPage()<=$gallery->cadres()->lastPage()-ceil($link_limit/2))
			<li class="last"><a href="{{$gallery->cadres()->url($to)}}@foreach($_GET as $k=>$v){{$k!='page'?'&'.$k.'='.$v:''}}@endforeach">&gt;</a></li>
		@endif
	</ul>
	@show

	<div class="btnArea" style="margin:0 5px">
		<a href="{{url('/admin/gallery/'.$gallery->id)}}{{$_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''}}" class="button gray" style="float:left">돌아가기</a>
				
		<a href="{{url('/admin/gallery/'.$gallery->id.'/cadres/create')}}{{$_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''}}" class="button blue">액자 만들기</a>
		<button type="button" class="button gray" onclick="if($('input:checked').length<1){alert('삭제할 액자을 선택해주세요.');return false;} if(confirm('정말로 삭제하시겠습니까?'))$('#delete').submit();return false"><span>일괄 삭제</span></button>
	</div>
	
@endsection