@extends('admin.layout',['title'=>'&gt; 폼'])

@section('body')
	<h3 class="menu_title">폼 관리</h3>
	
	@if(session('message'))
		<div class="message success">{!!session('message')!!}</div>
	@endif
	
	<div class="table_wrap">
		<table>
			<tr>
				<th>주소</th>
				<th>이름</th>
				<th>시작 시간</th>
				<th>종료 시간</th>
				<th></th>
			</tr>
			@foreach($forms as $form)
			<tr>
				<td class="link date"><a href="{{$form->url()}}" target="_blank">{{($form->domain?$form->domain.'/':'').$form->url}}&nbsp;<span class="arrow">&gt;</span></a></a></td>
				<td class="link"><a href="{{url('/admin/form/'.$form->id)}}{{isset($_SERVER['QUERY_STRING'])?'?'.$_SERVER['QUERY_STRING']:''}}">{{$form->name}}&nbsp;<span class="arrow">&gt;</span></a></td>
				<td class="date">{{$form->start_at}}</td>
				<td class="date">{{$form->end_at}}</td>
				<td class="no"><span class="online @if($form->inPeriod()) active @else inactive @endif "></span></td>
			</tr>
			@endforeach
		</table>
	</div>
	
	<div class="search_wrap">
		<form method="get" action="{{url('/admin/form')}}">
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
			$from=$forms->currentPage()-$half_total_links;
			$to=$forms->currentPage()+$half_total_links;
			if($forms->currentPage()<$half_total_links){
				$to+=$half_total_links-$forms->currentPage();
			}
			if($forms->lastPage()-$forms->currentPage() < $half_total_links){
				$from-=$half_total_links-($forms->lastPage()-$forms->currentPage())-1;
			}
		?>
		@if($forms->currentPage()>ceil($link_limit/2))
			<li class="first"><a href="{{$forms->url($from)}}@foreach($_GET as $k=>$v){{$k!='page'?'&'.$k.'='.$v:''}}@endforeach">&lt;</a></li>
		@endif
		@for($i=1;$i<=$forms->lastPage();$i++)
			@if ($from < $i && $i < $to)
				<li class="{{($forms->currentPage() == $i)?' active':''}}">
					<a href="{{$forms->url($i)}}@foreach($_GET as $k=>$v){{$k!='page'?'&'.$k.'='.$v:''}}@endforeach">{{$i}}</a>
				</li>
			@endif
		@endfor
		@if($forms->currentPage()<=$forms->lastPage()-ceil($link_limit/2))
			<li class="last"><a href="{{$forms->url($to)}}@foreach($_GET as $k=>$v){{$k!='page'?'&'.$k.'='.$v:''}}@endforeach">&gt;</a></li>
		@endif
	</ul>
	@show
	
@endsection