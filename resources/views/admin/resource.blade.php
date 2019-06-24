@extends('admin.layout',['title'=>'&gt; 첨부파일'])

@section('body')
	<h3 class="menu_title">첨부파일 관리</h3>
	
	@if(session('message'))
		<div class="message success">{!!session('message')!!}</div>
	@endif
	
	<form method="post" id="delete" action="{{url('/admin/resource/delete')}}{{$_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''}}">
		{!!csrf_field()!!}
		<div class="table_wrap">
			<table>
				<tr>
					<th></th>
					<th>상태</th>
					<th>이름</th>
					<th>종류</th>
					<th>크기</th>
					<th>다운로드 수</th>
					<th>업로드 날짜</th>
				</tr>
				@foreach($resources as $resource)
				<tr>
					<td class="no">
						<input type="checkbox" name="resources[]" value="{{$resource->id}}">
					</td>
					<td class="no"><span class="online @if($resource->state==200) active @else inactive @endif "></span></td>
					<td class="link"><a @if($resource->state==200) href="{{url('/file/'.($resource->type=='image'?'image/':'').$resource->name)}}" target="_blank" @else href="#" onclick="alert('삭제된 파일입니다.');return false" @endif >{{$resource->original}}&nbsp;@if($resource->state==200)<span class="arrow">&gt;</span>@else<span class="arrow red">&times;</span>@endif</a></td>
					<td class="date">{{$resource->mime}}</td>
					<td class="amount">{{round($resource->size/1024)}} KB</td>
					<td class="count">{{$resource->count_download}}</td>
					<td class="count">{{$resource->created_at}}</td>
				</tr>
				@endforeach
			</table>
		</div>
		
		<div class="search_wrap" style="margin-bottom:0">
			<form method="get" action="{{url('/admin/resources')}}">
				<label class="input_wrap">
					<input type="text" name="keyword" value="@if(isset($_GET['keyword'])){{$_GET['keyword']}}@endif">
					<span>검색</span>
					<button type="submit" class="blind">검색하기</button>
				</label>
			</form>
		</div>
	</form>
	
	@section('pagination')
	<?php $link_limit=5; ?>
	<ul class="pagination">
		<?php
			$half_total_links=floor(($link_limit+2)/2);
			$from=$resources->currentPage()-$half_total_links;
			$to=$resources->currentPage()+$half_total_links;
			if($resources->currentPage()<$half_total_links){
				$to+=$half_total_links-$resources->currentPage();
			}
			if($resources->lastPage()-$resources->currentPage() < $half_total_links){
				$from-=$half_total_links-($resources->lastPage()-$resources->currentPage())-1;
			}
		?>
		@if($resources->currentPage()>ceil($link_limit/2))
			<li class="first"><a href="{{$resources->url($from)}}@foreach($_GET as $k=>$v){{$k!='page'?'&'.$k.'='.$v:''}}@endforeach">&lt;</a></li>
		@endif
		@for($i=1;$i<=$resources->lastPage();$i++)
			@if ($from < $i && $i < $to)
				<li class="{{($resources->currentPage() == $i)?' active':''}}">
					<a href="{{$resources->url($i)}}@foreach($_GET as $k=>$v){{$k!='page'?'&'.$k.'='.$v:''}}@endforeach">{{$i}}</a>
				</li>
			@endif
		@endfor
		@if($resources->currentPage()<=$resources->lastPage()-ceil($link_limit/2))
			<li class="last"><a href="{{$resources->url($to)}}@foreach($_GET as $k=>$v){{$k!='page'?'&'.$k.'='.$v:''}}@endforeach">&gt;</a></li>
		@endif
	</ul>
	@show

	<div class="btnArea" style="margin:0 5px">
		<button type="button" class="button gray" onclick="if($('input:checked').length<1){alert('삭제할 파일을 선택해주세요.');return false;} if(confirm('삭제한 파일은 복구할 수 없습니다.\n정말로 삭제하시겠습니까?'))$('#delete').submit();return false"><span>일괄 삭제</span></button>
	</div>
	
@endsection