@extends($layout?'layout.'.$layout->path.'.layout':'common',['title'=>$board->name?'&gt; '.$board->name:''])

@section($board->layout?'body':'container')
	<h3 class="table_caption">{{$board->name}}</h3>
	
	@yield('read')

	@if(session('message'))
		<div class="message success">{!!session('message')!!}</div>
	@endif
	
	@if($board->authority())	
		@if(count($board->categories()))
			<div class="selects nolabel">
				<label class="select_wrap">
					<a href="{{url('/'.$board->url)}}" @if(!isset($_GET['category'])) class="active" @endif >✔︎</a>
					<span onclick="location.href='{{url('/'.$board->url)}}'">전체</span>
				</label>
				@foreach($board->categories() as $category)
					<label class="select_wrap">
						<a href="{{url('/'.$board->url.'?category='.$category->id)}}" @if(isset($_GET['category'])&&$_GET['category']==$category->id) class="active" @endif >✔︎</a>
						<span onclick="location.href='{{url('/'.$board->url.'?category='.$category->id)}}'">{{$category->name}}</span>
					</label>
				@endforeach
			</div>
		@endif
		
		@if(count($board->notices())||count($board->documents()))
			<div class="table_wrap">
				<table>
					<tr>
						@if(count($board->categories()))
							<th>분류</th>
						@endif
						<th>제목</th>
						<th>작성자</th>
						<th>작성일</th>
					</tr>
					
					@foreach($board->notices() as $d)
						<tr class="notice @if(isset($document)&&$document->id==$d->id)active @endif">
							@if(count($board->categories()))
								<td class="date">
									@if($d->category())
										{{$d->category()->name}}
									@endif
								</td>
							@endif
							<td class="link"><a href="{{url('/'.$board->url.'/'.$d->id)}}{{$_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''}}">
								@if($d->secret)🔒@endif
								@if($d->notice)📣@endif
								{{$d->title}}
								&nbsp;<span class="arrow">&gt;</span>
							</a></td>
							<td class="date">
								@if($board->anonymous==2)
									<i>익명</i>
								@elseif($board->anonymous==1)
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
							<td class="date">{{date('Y-m-d',strtotime($d->created_at))}}</td>
						</tr>
					@endforeach
					
					@foreach($board->documents() as $d)
						<tr @if(isset($document)&&$document->id==$d->id) class="active" @endif>
							
							@if(count($board->categories()))
								<td class="date">
									@if($d->category())
										{{$d->category()->name}}
									@endif
								</td>
							@endif
							<td class="link"><a href="{{url('/'.$board->url.'/'.$d->id)}}{{$_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''}}">
								@if($d->secret)🔒@endif
								@if($d->notice)📣@endif
								{{$d->title}}
								&nbsp;<span class="arrow">&gt;</span>
							</a></td>
							<td class="date">
								@if($board->anonymous==2)
									<i>익명</i>
								@elseif($board->anonymous==1)
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
							<td class="date">{{date('Y-m-d',strtotime($d->created_at))}}</td>
						</tr>
					@endforeach
				</table>
			</div>
		
		@else
			<div class="no_item">
				게시글이 없습니다.
			</div>
		
		@endif
			
		<div class="search_wrap">
			<form method="get" action="{{url('/'.$board->url)}}">
				<label class="input_wrap">
					@foreach($_GET as $k=>$v)
						@if($k!='keyword'&&$k!='page')
							<input type="hidden" name="{{$k}}" value="{{$v}}">
						@endif
					@endforeach
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
				<li class="first"><a href="{{url('/'.$board->url)}}?page={{$from>1?$from:'1'}}@foreach($_GET as $k=>$v){{$k!='page'?'&'.$k.'='.$v:''}}@endforeach">&lt;</a></li>
			@endif
			@for($i=1;$i<=$board->documents()->lastPage();$i++)
				@if ($from < $i && $i < $to)
					<li class="{{($board->documents()->currentPage() == $i)?' active':''}}">
						<a href="{{url('/'.$board->url)}}?page={{$i}}@foreach($_GET as $k=>$v){{$k!='page'?'&'.$k.'='.$v:''}}@endforeach">{{$i}}</a>
					</li>
				@endif
			@endfor
			@if($board->documents()->currentPage()<=$board->documents()->lastPage()-ceil($link_limit/2))
				<li class="last"><a href="{{url('/'.$board->url)}}?page={{$to}}@foreach($_GET as $k=>$v){{$k!='page'?'&'.$k.'='.$v:''}}@endforeach">&gt;</a></li>
			@endif
		</ul>
		@show
	
		@if($board->authority('document'))
			<div class="btnArea">
				<a href="{{url('/'.$board->url.'/create')}}{{$_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''}}" class="button blue">글 쓰기</a>
			</div>
		@endif
	@endif
	
@endsection