@extends('admin.layout',['title'=>'&gt; 갤러리'])

@section('body')
	<h3 class="menu_title">갤러리 관리</h3>
	
	@if(session('message'))
		<div class="message success">{!!session('message')!!}</div>
	@endif
	
	<div class="table_wrap">
		<table>
			<tr>
				<th>주소</th>
				<th>이름</th>
				<th>액자 수</th>
			</tr>
			@foreach($galleries as $gallery)
			<tr>
				<td class="link date"><a href="{{url($gallery->url)}}" target="_blank">{{$gallery->url}}&nbsp;<span class="arrow">&gt;</span></a></a></td>
				<td class="link"><a href="{{url('/admin/gallery/'.$gallery->id)}}{{isset($_SERVER['QUERY_STRING'])?'?'.$_SERVER['QUERY_STRING']:''}}">{{$gallery->name}}&nbsp;<span class="arrow">&gt;</span></a></td>
				<td class="count">{{$gallery->count_cadre}}</td>
			</tr>
			@endforeach
		</table>
	</div>
	
	<div class="search_wrap">
		<form method="get" action="{{url('/admin/gallery')}}">
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
			$from=$galleries->currentPage()-$half_total_links;
			$to=$galleries->currentPage()+$half_total_links;
			if($galleries->currentPage()<$half_total_links){
				$to+=$half_total_links-$galleries->currentPage();
			}
			if($galleries->lastPage()-$galleries->currentPage() < $half_total_links){
				$from-=$half_total_links-($galleries->lastPage()-$galleries->currentPage())-1;
			}
		?>
		@if($galleries->currentPage()>ceil($link_limit/2))
			<li class="first"><a href="{{$galleries->url($from)}}@foreach($_GET as $k=>$v){{$k!='page'?'&'.$k.'='.$v:''}}@endforeach">&lt;</a></li>
		@endif
		@for($i=1;$i<=$galleries->lastPage();$i++)
			@if ($from < $i && $i < $to)
				<li class="{{($galleries->currentPage() == $i)?' active':''}}">
					<a href="{{$galleries->url($i)}}@foreach($_GET as $k=>$v){{$k!='page'?'&'.$k.'='.$v:''}}@endforeach">{{$i}}</a>
				</li>
			@endif
		@endfor
		@if($galleries->currentPage()<=$galleries->lastPage()-ceil($link_limit/2))
			<li class="last"><a href="{{$galleries->url($to)}}@foreach($_GET as $k=>$v){{$k!='page'?'&'.$k.'='.$v:''}}@endforeach">&gt;</a></li>
		@endif
	</ul>
	@show
	
@endsection