@extends('admin.layout',['title'=>'&gt; 게시판 &gt; '.$board->name.' &gt; 글 목록'])

@section('body')
	<h3 class="menu_title">{{$board->name}} 글 목록</h3>
	
	@if(session('message'))
		<div class="message success">{!!session('message')!!}</div>
	@endif
	
	<form method="post" id="delete" action="{{url('/admin/board/documents/delete')}}{{isset($_SERVER['QUERY_STRING'])?'?'.$_SERVER['QUERY_STRING']:''}}">
		{!!csrf_field()!!}
		<input type="hidden" name="board" value="{{$board->id}}">
		
		<div class="table_wrap">
			<table>
				<tr>
					<th></th>
					@if(count($board->categories()))
						<th>분류</th>
					@endif
					<th>제목</th>
					<th>조회 수</th>
					<th>댓글 수</th>
					<th>글쓴이</th>
				</tr>
				@foreach($board->notices() as $d)
				<tr class="notice">
					<td class="no">
						<input type="checkbox" name="documents[]" value="{{$d->id}}">
					</td>
					@if(count($board->categories()))
						<td class="date">
							@if($d->category())
								{{$d->category()->name}}
							@endif
						</td>
					@endif
					<td class="link"><a href="{{url('/admin/board/'.$board->id.'/documents/'.$d->id)}}">
						@if($d->secret)🔒@endif
						📣
						{{$d->title}}&nbsp;<span class="arrow">&gt;</span></a></td>
					<td class="count link"><a href="{{url('/'.$d->board()->url.'/'.$d->id)}}" target="_blank">
						{{$d->count_read}}
					&nbsp;<span class="arrow">&gt;</span></a></td>
					<td class="count">{{$d->count_comment}}</td>
					<td class="date">
						@if($d->author())
							{{$d->author()->nickname}}
						@else
							<i>비회원</i>
						@endif
					</td>
				</tr>
				@endforeach
				@foreach($board->documents() as $d)
				<tr>
					<td class="no">
						<input type="checkbox" name="documents[]" value="{{$d->id}}">
					</td>
					@if(count($board->categories()))
						<td class="date">
							@if($d->category())
								{{$d->category()->name}}
							@endif
						</td>
					@endif
					<td class="link"><a href="{{url('/admin/board/'.$board->id.'/documents/'.$d->id)}}">
						@if($d->secret)🔒@endif
						{{$d->title}}&nbsp;<span class="arrow">&gt;</span></a></td>
					<td class="count link"><a href="{{url('/'.$d->board()->url.'/'.$d->id)}}" target="_blank">
						{{$d->count_read}}
					&nbsp;<span class="arrow">&gt;</span></a></td>
					<td class="count">{{$d->count_comment}}</td>
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
		<form method="get" action="{{url('/admin/board/document')}}{{isset($_SERVER['QUERY_STRING'])?'?'.$_SERVER['QUERY_STRING']:''}}">
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
			$from=$board->documents()->currentPage()-$half_total_links;
			$to=$board->documents()->currentPage()+$half_total_links;
			if($board->documents()->currentPage()<$half_total_links){
				$to+=$half_total_links-$board->documents()->currentPage();
			}
			if($board->documents()->lastPage()-$board->documents()->currentPage() < $half_total_links){
				$from-=$half_total_links-($board->documents()->lastPage()-$board->documents()->currentPage())-1;
			}
		?>
		@if($board->documents()->currentPage()>ceil($link_limit/2))
			<li class="first"><a href="{{$board->documents()->url($from)}}@foreach($_GET as $k=>$v){{$k!='page'?'&'.$k.'='.$v:''}}@endforeach">&lt;</a></li>
		@endif
		@for($i=1;$i<=$board->documents()->lastPage();$i++)
			@if ($from < $i && $i < $to)
				<li class="{{($board->documents()->currentPage() == $i)?' active':''}}">
					<a href="{{$board->documents()->url($i)}}@foreach($_GET as $k=>$v){{$k!='page'?'&'.$k.'='.$v:''}}@endforeach">{{$i}}</a>
				</li>
			@endif
		@endfor
		@if($board->documents()->currentPage()<=$board->documents()->lastPage()-ceil($link_limit/2))
			<li class="last"><a href="{{$board->documents()->url($to)}}@foreach($_GET as $k=>$v){{$k!='page'?'&'.$k.'='.$v:''}}@endforeach">&gt;</a></li>
		@endif
	</ul>
	@show

	<div class="btnArea" style="margin:0 5px">
		<a href="{{url('/admin/board/'.$board->id)}}{{isset($_SERVER['QUERY_STRING'])?'?'.$_SERVER['QUERY_STRING']:''}}" class="button gray" style="float:left">돌아가기</a>
				
		<a href="{{url('/admin/board/'.$board->id.'/documents/create')}}{{isset($_SERVER['QUERY_STRING'])?'?'.$_SERVER['QUERY_STRING']:''}}" class="button blue">글 쓰기</a>
		<button type="button" class="button gray" onclick="if($('input:checked').length<1){alert('삭제할 게시글을 선택해주세요.');return false;} if(confirm('정말로 삭제하시겠습니까?'))$('#delete').submit();return false"><span>일괄 삭제</span></button>
	</div>
	
@endsection