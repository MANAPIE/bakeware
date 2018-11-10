@extends('admin.layout',['title'=>'&gt; 회원'])

@section('body')
	<h3 class="menu_title">회원 관리</h3>
	
	@if(session('message'))
		<div class="message success">{!!session('message')!!}</div>
	@endif
	
	<div class="table_wrap">
		<table>
			<tr>
				<th>이름</th>
				<th>아이디</th>
			</tr>
			@foreach($users as $user)
			<tr>
				<td class="link"><a href="{{url('/admin/user/'.$user->id)}}{{$_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''}}">
					<span class="profile" @if($user->thumbnail()) style="background-image:url('{{url($user->thumbnail())}}')" @endif></span>
					{{$user->nickname}}&nbsp;<span class="arrow">&gt;</span>
				</a></td>
				<td class="date">{{$user->name}}</td>
			</tr>
			@endforeach
		</table>
	</div>
	
	<div class="search_wrap">
		<form method="get" action="{{url('/admin/user')}}">
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
			$from=$users->currentPage()-$half_total_links;
			$to=$users->currentPage()+$half_total_links;
			if($users->currentPage()<$half_total_links){
				$to+=$half_total_links-$users->currentPage();
			}
			if($users->lastPage()-$users->currentPage() < $half_total_links){
				$from-=$half_total_links-($users->lastPage()-$users->currentPage())-1;
			}
		?>
		@if($users->currentPage()>ceil($link_limit/2))
			<li class="first"><a href="{{$users->url($from)}}@foreach($_GET as $k=>$v){{$k!='page'?'&'.$k.'='.$v:''}}@endforeach">&lt;</a></li>
		@endif
		@for($i=1;$i<=$users->lastPage();$i++)
			@if ($from < $i && $i < $to)
				<li class="{{($users->currentPage() == $i)?' active':''}}">
					<a href="{{$users->url($i)}}@foreach($_GET as $k=>$v){{$k!='page'?'&'.$k.'='.$v:''}}@endforeach">{{$i}}</a>
				</li>
			@endif
		@endfor
		@if($users->currentPage()<=$users->lastPage()-ceil($link_limit/2))
			<li class="last"><a href="{{$users->url($to)}}@foreach($_GET as $k=>$v){{$k!='page'?'&'.$k.'='.$v:''}}@endforeach">&gt;</a></li>
		@endif
	</ul>
	@show

@endsection