@extends('gallery.'.$gallery->skin.'.list',['title'=>($gallery->name?'&gt; '.(\App\Encryption::checkEncrypted($gallery->name)?\App\Encryption::decrypt($gallery->name):$gallery->name):'').($cadre->title?'&gt; '.$cadre->title:'')])

@section('read')
	<div class="read_wrap">
		
		<div class="read_gallery">
			@if($cadre->files())
				@foreach($cadre->files() as $file)
				<div class="image">
					<img src="{{url('/file/image/'.$file->name)}}" alt="" />
				</div>
				@endforeach
			@endif
		</div>
		
		<div class="read_header">
			<h4 class="title">
				@if($cadre->category())
					<span class="category">{{$cadre->category()->name}}</span>
				@endif
			</h4>
			<div class="meta">
				<span class="date">
					@if($gallery->anonymous==2)
						<i>익명</i>
					@elseif($gallery->anonymous==1)
						@if($cadre->author()&&array_key_exists(2,$cadre->author()->groups()))
							{{$cadre->author()->nickname}}
						@else
							<i>익명</i>
						@endif
					@else
						@if($cadre->author())
							{{$cadre->author()->nickname}}
						@else
							<i>비회원</i>
						@endif
					@endif
				</span>
				<span class="date">{{$cadre->created_at}} 작성</span>
				@if($gallery->sort_by=='updated_at')<span class="date">{{$cadre->updated_at}} 새 활동</span>@endif
			</div>
		</div>
		<div class="read_body">
			
			@if(count($gallery->extravars()))
				@foreach($gallery->extravars() as $extravar)
					<div class="extravar">
						<h6>{{$extravar->name}}</h6>
						@if($extravar->type=='text')
							@if($cadre->extravar($extravar->id)){{$cadre->extravar($extravar->id)}}@endif
						@elseif($extravar->type=='textarea')
							@if($cadre->extravar($extravar->id)){!!str_replace('&lt;br /&gt;','<br>',htmlspecialchars(nl2br($cadre->extravar($extravar->id))))!!}@endif
						@elseif($extravar->type=='radio')
							@if($cadre->extravar($extravar->id)){{$cadre->extravar($extravar->id)}}@endif
						@elseif($extravar->type=='checkbox')
							@if(count($cadre->extravar($extravar->id)))
								{{implode(', ',$cadre->extravar($extravar->id))}}
							@endif
						@elseif($extravar->type=='order')
							@if(count($cadre->extravar($extravar->id)))
								{{implode(', ',$cadre->extravar($extravar->id))}}
							@endif
						@elseif($extravar->type=='image')
							@if($cadre->extravar($extravar->id))<img src="{{url($cadre->extravar($extravar->id))}}" alt="">@endif
						@elseif($extravar->type=='file')
							@if($cadre->extravar($extravar->id))<a href="{{url($cadre->extravar($extravar->id))}}">💾 {{\App\File::where('name',str_replace('/file/','',$cadre->extravar($extravar->id)))->first()->original}}</a>@endif
						@endif
					</div>
				@endforeach
			@endif
			
			<div class="real_content">
				{!!$cadre->content()!!}
			</div>
		</div>
		@if($cadre->isMine())
			<div class="read_footer">
				<div class="btnArea">
					<form id="gallery{{$cadre->id}}delete" class="form" method="post" action="{{url('/'.$gallery->url.'/'.$cadre->id.'/delete')}}{{isset($_SERVER['QUERY_STRING'])?'?'.$_SERVER['QUERY_STRING']:''}}">
						{!!csrf_field()!!}
					</form>
					<button style="float:left" type="button" class="button white" onclick="if(confirm('정말로 삭제하시겠습니까?'))$('#gallery{{$cadre->id}}delete').submit();return false"><span>삭제</span></button>
					
					<a href="{{url('/'.$gallery->url.'/'.$cadre->id.'/edit')}}{{isset($_SERVER['QUERY_STRING'])?'?'.$_SERVER['QUERY_STRING']:''}}" class="button gray">수정</a>
				</div>
			</div>
		@endif
		
	</div>
	
@endsection