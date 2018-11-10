@extends('admin.layout',['title'=>'&gt; 방문 기록'])

@section('body')
	<h3 class="menu_title">방문 기록</h3>
	
	@if(session('message'))
		<div class="message success">{!!session('message')!!}</div>
	@endif
	
	<div class="visitors">
		@if(isset($_GET['from'])){{$_GET['from']}}@else{{date('Y-m-d')}}@endif ~<br>
		@if(isset($_GET['to'])){{$_GET['to']}}@else{{date('Y-m-d')}}@endif 기간 중 방문자 수
		<h5>{{$count}}</h5>
	</div>
	
	<div class="table_wrap">
		<table>
			<tr>
				<th>시각</th>
				<th>접속 주소</th>
				<th>리퍼러</th>
				<th>IP 주소</th>
				<th>사용자 정보</th>
			</tr>
			@foreach($logs as $log)
			<tr>
				<td class="date">{{$log->created_at}}</td>
				<td>{{$log->url}}</td>
				<td>{{$log->referer}}</td>
				<td class="date">{{$log->ip_address}}</td>
				<td class="date">{{$log->user_agent}}</td>
			</tr>
			@endforeach
		</table>
	</div>
	
	<div class="search_wrap">
		<form method="get" action="{{url('/admin/visitor')}}">
			<label class="input_wrap">
				<input type="date" name="from" value="@if(isset($_GET['from'])){{$_GET['from']}}@else{{date('Y-m-d')}}@endif">
				<span>시작 날짜</span>
			</label>
			<label class="input_wrap">
				<input type="date" name="to" value="@if(isset($_GET['to'])){{$_GET['to']}}@else{{date('Y-m-d')}}@endif">
				<span>종료 날짜</span>
			</label>
			<button type="submit" class="blind">검색하기</button>
		</form>
	</div>
	
	@section('pagination')
	<?php $link_limit=5; ?>
	<ul class="pagination">
		<?php
			$half_total_links=floor(($link_limit+2)/2);
			$from=$logs->currentPage()-$half_total_links;
			$to=$logs->currentPage()+$half_total_links;
			if($logs->currentPage()<$half_total_links){
				$to+=$half_total_links-$logs->currentPage();
			}
			if($logs->lastPage()-$logs->currentPage() < $half_total_links){
				$from-=$half_total_links-($logs->lastPage()-$logs->currentPage())-1;
			}
		?>
		@if($logs->currentPage()>ceil($link_limit/2))
			<li class="first"><a href="{{$logs->url($from)}}@foreach($_GET as $k=>$v){{$k!='page'?'&'.$k.'='.$v:''}}@endforeach">&lt;</a></li>
		@endif
		@for($i=1;$i<=$logs->lastPage();$i++)
			@if ($from < $i && $i < $to)
				<li class="{{($logs->currentPage() == $i)?' active':''}}">
					<a href="{{$logs->url($i)}}@foreach($_GET as $k=>$v){{$k!='page'?'&'.$k.'='.$v:''}}@endforeach">{{$i}}</a>
				</li>
			@endif
		@endfor
		@if($logs->currentPage()<=$logs->lastPage()-ceil($link_limit/2))
			<li class="last"><a href="{{$logs->url($to)}}@foreach($_GET as $k=>$v){{$k!='page'?'&'.$k.'='.$v:''}}@endforeach">&gt;</a></li>
		@endif
	</ul>
	@show

@endsection