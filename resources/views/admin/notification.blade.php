@extends('admin.layout',['title'=>'&gt; 알림'])

@section('body')
	<h3 class="menu_title">알림</h3>
	
	@if(session('message'))
		<div class="message success">{!!session('message')!!}</div>
	@endif
	
	<div class="table_wrap">
		<table>
			<tr>
				<th>시각</th>
				<th>회원</th>
				<th>대상</th>
				<th>메세지</th>
			</tr>
			@foreach($notifications as $notification)
			<tr>
				<td class="date">{{$notification->created_at}}</td>
				<td>@if($notification->author){{\App\User::find($notification->author)->nickname}}@else<i>비회원</i>@endif</td>
				<td>@if($notification->user){{\App\User::find($notification->user)->nickname}}@else<i>비회원</i>@endif</td>
				<td>{!!$notification->message!!}</td>
			</tr>
			@endforeach
		</table>
	</div>
	
	<div class="search_wrap">
		<form method="get" action="{{url('/admin/notification')}}">
			<label class="input_wrap">
				<input type="date" name="from" value="@if(isset($_GET['from'])){{$_GET['from']}}@endif">
				<span>시작 날짜</span>
			</label>
			<label class="input_wrap">
				<input type="date" name="to" value="@if(isset($_GET['to'])){{$_GET['to']}}@endif">
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
			$from=$notifications->currentPage()-$half_total_links;
			$to=$notifications->currentPage()+$half_total_links;
			if($notifications->currentPage()<$half_total_links){
				$to+=$half_total_links-$notifications->currentPage();
			}
			if($notifications->lastPage()-$notifications->currentPage() < $half_total_links){
				$from-=$half_total_links-($notifications->lastPage()-$notifications->currentPage())-1;
			}
		?>
		@if($notifications->currentPage()>ceil($link_limit/2))
			<li class="first"><a href="{{$notifications->url($from)}}@foreach($_GET as $k=>$v){{$k!='page'?'&'.$k.'='.$v:''}}@endforeach">&lt;</a></li>
		@endif
		@for($i=1;$i<=$notifications->lastPage();$i++)
			@if ($from < $i && $i < $to)
				<li class="{{($notifications->currentPage() == $i)?' active':''}}">
					<a href="{{$notifications->url($i)}}@foreach($_GET as $k=>$v){{$k!='page'?'&'.$k.'='.$v:''}}@endforeach">{{$i}}</a>
				</li>
			@endif
		@endfor
		@if($notifications->currentPage()<=$notifications->lastPage()-ceil($link_limit/2))
			<li class="last"><a href="{{$notifications->url($to)}}@foreach($_GET as $k=>$v){{$k!='page'?'&'.$k.'='.$v:''}}@endforeach">&gt;</a></li>
		@endif
	</ul>
	@show

@endsection