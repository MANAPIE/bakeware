@extends('admin.layout',['title'=>'&gt; 페이지'])

@section('body')
	<h3 class="menu_title">페이지 관리</h3>
			
	<div class="description">
		'편집' 페이지는 내용을 수정할 수 있으나 프로그래밍 요소는 사용할 수 없습니다.<br>
		'외부' 페이지는 내용을 수정하려면 개발사에 문의해야 하나 프로그래밍 요소를 사용할 수 있습니다.
	</div>
	
	@if(session('message'))
		<div class="message success">{!!session('message')!!}</div>
	@endif
	
	<div class="table_wrap">
		<table>
			<tr>
				<th>주소</th>
				<th>이름</th>
				<th>방문 수</th>
				<th>종류</th>
			</tr>
			@foreach($pages as $page)
			<tr>
				<td class="link date"><a href="{{$page->url()}}" target="_blank">{{($page->domain?$page->domain.'/':'').$page->url}}&nbsp;<span class="arrow">&gt;</span></a></a></td>
				<td class="link"><a href="{{url('/admin/page/'.$page->id)}}{{isset($_SERVER['QUERY_STRING'])?'?'.$_SERVER['QUERY_STRING']:''}}">{{$page->name()}}&nbsp;<span class="arrow">&gt;</span></a></td>
				<td class="count">{{$page->count_read}}</td>
				<td class="date">{{$page->type()}}</td>
			</tr>
			@endforeach
		</table>
	</div>
	
	@if(!\App\Encryption::isEncrypt('page'))
	<div class="search_wrap">
		<form method="get" action="{{url('/admin/page')}}">
			<label class="input_wrap">
				<input type="text" name="keyword" value="@if(isset($_GET['keyword'])){{$_GET['keyword']}}@endif">
				<span>검색</span>
				<button type="submit" class="blind">검색하기</button>
			</label>
		</form>
	</div>
	@endif
	
	@section('pagination')
	<?php $link_limit=5; ?>
	<ul class="pagination">
		<?php
			$half_total_links=floor(($link_limit+2)/2);
			$from=$pages->currentPage()-$half_total_links;
			$to=$pages->currentPage()+$half_total_links;
			if($pages->currentPage()<$half_total_links){
				$to+=$half_total_links-$pages->currentPage();
			}
			if($pages->lastPage()-$pages->currentPage() < $half_total_links){
				$from-=$half_total_links-($pages->lastPage()-$pages->currentPage())-1;
			}
		?>
		@if($pages->currentPage()>ceil($link_limit/2))
			<li class="first"><a href="{{$pages->url($from)}}@foreach($_GET as $k=>$v){{$k!='page'?'&'.$k.'='.$v:''}}@endforeach">&lt;</a></li>
		@endif
		@for($i=1;$i<=$pages->lastPage();$i++)
			@if ($from < $i && $i < $to)
				<li class="{{($pages->currentPage() == $i)?' active':''}}">
					<a href="{{$pages->url($i)}}@foreach($_GET as $k=>$v){{$k!='page'?'&'.$k.'='.$v:''}}@endforeach">{{$i}}</a>
				</li>
			@endif
		@endfor
		@if($pages->currentPage()<=$pages->lastPage()-ceil($link_limit/2))
			<li class="last"><a href="{{$pages->url($to)}}@foreach($_GET as $k=>$v){{$k!='page'?'&'.$k.'='.$v:''}}@endforeach">&gt;</a></li>
		@endif
	</ul>
	@show
	
@endsection