@extends('admin.layout',['title'=>'&gt; 게시판'])

@section('body')
	<h3 class="menu_title">게시판 관리</h3>
	
	@if(session('message'))
		<div class="message success">{!!session('message')!!}</div>
	@endif
	
	<div class="table_wrap">
		<table>
			<tr>
				<th>주소</th>
				<th>이름</th>
				<th>게시글 수</th>
			</tr>
			@foreach($boards as $board)
			<tr>
				<td class="link date"><a href="{{url($board->url)}}" target="_blank">{{$board->url}}&nbsp;<span class="arrow">&gt;</span></a></a></td>
				<td class="link"><a href="{{url('/admin/board/'.$board->id)}}{{$_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''}}">{{$board->name}}&nbsp;<span class="arrow">&gt;</span></a></td>
				<td class="count">{{$board->count_document}}</td>
			</tr>
			@endforeach
		</table>
	</div>
	
	<div class="search_wrap">
		<form method="get" action="{{url('/admin/board')}}">
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
			$from=$boards->currentPage()-$half_total_links;
			$to=$boards->currentPage()+$half_total_links;
			if($boards->currentPage()<$half_total_links){
				$to+=$half_total_links-$boards->currentPage();
			}
			if($boards->lastPage()-$boards->currentPage() < $half_total_links){
				$from-=$half_total_links-($boards->lastPage()-$boards->currentPage())-1;
			}
		?>
		@if($boards->currentPage()>ceil($link_limit/2))
			<li class="first"><a href="{{$boards->url($from)}}@foreach($_GET as $k=>$v){{$k!='page'?'&'.$k.'='.$v:''}}@endforeach">&lt;</a></li>
		@endif
		@for($i=1;$i<=$boards->lastPage();$i++)
			@if ($from < $i && $i < $to)
				<li class="{{($boards->currentPage() == $i)?' active':''}}">
					<a href="{{$boards->url($i)}}@foreach($_GET as $k=>$v){{$k!='page'?'&'.$k.'='.$v:''}}@endforeach">{{$i}}</a>
				</li>
			@endif
		@endfor
		@if($boards->currentPage()<=$boards->lastPage()-ceil($link_limit/2))
			<li class="last"><a href="{{$boards->url($to)}}@foreach($_GET as $k=>$v){{$k!='page'?'&'.$k.'='.$v:''}}@endforeach">&gt;</a></li>
		@endif
	</ul>
	@show
	
@endsection