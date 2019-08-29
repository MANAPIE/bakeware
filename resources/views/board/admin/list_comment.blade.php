@extends('admin.layout',['title'=>'&gt; 게시판 &gt; 댓글'])

@section('body')
	<h3 class="menu_title">댓글 관리</h3>
	
	@if(session('message'))
		<div class="message success">{!!session('message')!!}</div>
	@endif
	
	<form method="post" id="delete" action="{{url('/admin/board/comment/delete')}}{{isset($_SERVER['QUERY_STRING'])?'?'.$_SERVER['QUERY_STRING']:''}}">
		{!!csrf_field()!!}
		
		<div class="table_wrap">
			<table>
				<tr>
					<th></th>
					<th>게시판</th>
					<th>게시글</th>
					<th>요약</th>
					<th>글쓴이</th>
				</tr>
				@foreach($comments as $d)
				<tr>
					<td class="no">
						<input type="checkbox" name="documents[]" value="{{$d->id}}">
					</td>
					<td class="link date"><a href="{{url('/admin/board/'.$d->board()->id)}}">{{$d->board()->name}}&nbsp;<span class="arrow">&gt;</span></a></a></td>
					<td class="link date"><a href="{{url('/'.$d->board()->url().'/'.$d->document)}}" target="_blank">{{$d->document()->title}}&nbsp;<span class="arrow">&gt;</span></a></td>
					<td>
						@if($d->secret)🔒@endif
						@if($d->notice)📣@endif
						{{$d->summary(100)}}
					</td>
					<td class="date">
						@if($d->board()->anonymous==2)
							<i>익명</i>
						@elseif($d->board()->anonymous==1)
							@if($d->author()&&array_key_exists(2,$d->author()->groups()))
								{{$d->author()->nickname}}
							@else
								<i>익명</i>
							@endif
						@else
							@if($d->author())
								{{$d->author()->nickname}}
							@else
								<i>비회원</i>
							@endif
						@endif
					</td>
				</tr>
				@endforeach
			</table>
		</div>
	</form>
	
	<div class="search_wrap" style="margin-bottom:0">
		<form method="get" action="{{url('/admin/board/comment')}}{{isset($_SERVER['QUERY_STRING'])?'?'.$_SERVER['QUERY_STRING']:''}}">
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
			$from=$comments->currentPage()-$half_total_links;
			$to=$comments->currentPage()+$half_total_links;
			if($comments->currentPage()<$half_total_links){
				$to+=$half_total_links-$comments->currentPage();
			}
			if($comments->lastPage()-$comments->currentPage() < $half_total_links){
				$from-=$half_total_links-($comments->lastPage()-$comments->currentPage())-1;
			}
		?>
		@if($comments->currentPage()>ceil($link_limit/2))
			<li class="first"><a href="{{$comments->url($from)}}@foreach($_GET as $k=>$v){{$k!='page'?'&'.$k.'='.$v:''}}@endforeach">&lt;</a></li>
		@endif
		@for($i=1;$i<=$comments->lastPage();$i++)
			@if ($from < $i && $i < $to)
				<li class="{{($comments->currentPage() == $i)?' active':''}}">
					<a href="{{$comments->url($i)}}@foreach($_GET as $k=>$v){{$k!='page'?'&'.$k.'='.$v:''}}@endforeach">{{$i}}</a>
				</li>
			@endif
		@endfor
		@if($comments->currentPage()<=$comments->lastPage()-ceil($link_limit/2))
			<li class="last"><a href="{{$comments->url($to)}}@foreach($_GET as $k=>$v){{$k!='page'?'&'.$k.'='.$v:''}}@endforeach">&gt;</a></li>
		@endif
	</ul>
	@show

	<div class="btnArea" style="margin:0 5px">
		<button type="button" class="button gray" onclick="if($('input:checked').length<1){alert('삭제할 게시글을 선택해주세요.');return false;} if(confirm('정말로 삭제하시겠습니까?'))$('#delete').submit();return false"><span>일괄 삭제</span></button>
	</div>
	
@endsection