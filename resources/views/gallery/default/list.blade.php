@extends($layout?'layout.'.$layout->path.'.layout':'common',['title'=>$gallery->name?'&gt; '.(\App\Encryption::checkEncrypted($gallery->name)?\App\Encryption::decrypt($gallery->name):$gallery->name):''])

@section($gallery->layout?'body':'container')
	<h3 class="table_caption">{{$gallery->name}}</h3>
	
	@yield('read')

	@if(session('message'))
		<div class="message success">{!!session('message')!!}</div>
	@endif
	
	@if($gallery->authority())	
		@if(count($gallery->categories()))
			<div class="selects nolabel">
				<label class="select_wrap">
					<a href="{{url('/'.$gallery->url)}}" @if(!isset($_GET['category'])) class="active" @endif >✔︎</a>
					<span onclick="location.href='{{url('/'.$gallery->url)}}'">전체</span>
				</label>
				@foreach($gallery->categories() as $category)
					<label class="select_wrap">
						<a href="{{url('/'.$gallery->url.'?category='.$category->id)}}" @if(isset($_GET['category'])&&$_GET['category']==$category->id) class="active" @endif >✔︎</a>
						<span onclick="location.href='{{url('/'.$gallery->url.'?category='.$category->id)}}'">{{$category->name}}</span>
					</label>
				@endforeach
			</div>
		@endif
		
		@if(count($gallery->cadres()))
			<div class="gallery_wrap">
				<ul class="cadres">
					@foreach($gallery->cadres() as $d)
						<li @if(isset($cadre)&&$cadre->id==$d->id) class="active" @endif>
							<a href="{{url('/'.$gallery->url.'/'.$d->id)}}{{$_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''}}">
								@foreach($d->files() as $file)
									<img src="/file/thumb/{{$file->name}}" alt="">
									@break
								@endforeach
							</a>
						</li>
					@endforeach
					
					<li class="clear"></li>
				</ul>
			</div>
		
		@else
			<div class="no_item">
				액자가 없습니다.
			</div>
		
		@endif
		
		@section('pagination')
		<?php $link_limit=5; ?>
		<ul class="pagination" style="float:left;margin:13px 10px 5px 13px">
			<?php
				$half_total_links=floor(($link_limit+2)/2);
				$from=$gallery->cadres()->currentPage()-$half_total_links;
				$to=$gallery->cadres()->currentPage()+$half_total_links;
				if($gallery->cadres()->currentPage()<$half_total_links){
					$to+=$half_total_links-$gallery->cadres()->currentPage();
				}
				if($gallery->cadres()->lastPage()-$gallery->cadres()->currentPage() < $half_total_links){
					$from-=$half_total_links-($gallery->cadres()->lastPage()-$gallery->cadres()->currentPage())-1;
				}
			?>
			@if($gallery->cadres()->currentPage()>ceil($link_limit/2))
				<li class="first"><a href="{{url('/'.$gallery->url)}}?page={{$from>1?$from:'1'}}@foreach($_GET as $k=>$v){{$k!='page'?'&'.$k.'='.$v:''}}@endforeach">&lt;</a></li>
			@endif
			@for($i=1;$i<=$gallery->cadres()->lastPage();$i++)
				@if ($from < $i && $i < $to)
					<li class="{{($gallery->cadres()->currentPage() == $i)?' active':''}}">
						<a href="{{url('/'.$gallery->url)}}?page={{$i}}@foreach($_GET as $k=>$v){{$k!='page'?'&'.$k.'='.$v:''}}@endforeach">{{$i}}</a>
					</li>
				@endif
			@endfor
			@if($gallery->cadres()->currentPage()<=$gallery->cadres()->lastPage()-ceil($link_limit/2))
				<li class="last"><a href="{{url('/'.$gallery->url)}}?page={{$to}}@foreach($_GET as $k=>$v){{$k!='page'?'&'.$k.'='.$v:''}}@endforeach">&gt;</a></li>
			@endif
		</ul>
		@show
	
		@if($gallery->authority('cadre'))
			<div class="btnArea">
				<a href="{{url('/'.$gallery->url.'/create')}}{{$_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''}}" class="button blue">액자 만들기</a>
			</div>
		@endif
	@endif
	
@endsection