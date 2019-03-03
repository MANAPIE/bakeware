@extends('admin.layout',['title'=>'&gt; 게시판 &gt; '.$board->name.' &gt; 글 쓰기'])

@section('head')
	@parent
	<script type="text/javascript" src="{{url('/ckeditor/ckeditor.js')}}"></script>
	<script type="text/javascript" src="{{url('/script/jquery-ui.min.js')}}"></script>
	<script type="text/javascript" src="{{url('/script/jquery.ui.touch-punch.min.js')}}"></script>
	<script type="text/javascript" src="{{url('/script/dropzone.js')}}"></script>
	<link rel="stylesheet" href="{{url('/style/dropzone.css')}}" />
	<script>
	Dropzone.autoDiscover = false;
	$(function(){
		CKEDITOR.replace('content',{});
		$('.order_list ul').sortable();
		
		Dropzone.autoDiscover = false;
	    var myDropzone = new Dropzone("#dropzone",{
			init: function(){
				this.on("success", function(file, responseText){
					file.previewTemplate.appendChild(document.createTextNode(responseText));
				});
			},
	    	url: "/upload/dropzone",
	    	addRemoveLinks: true,
	    	removedfile: function(file){
	    		$('#attatched input[data-name=\''+file.name+'\']').remove();
	    		var _ref;
	    		return (_ref = file.previewElement) != null ? _ref.parentNode.removeChild(file.previewElement) : void 0;
	    	},
	    });
		myDropzone.on("success", function(file,data){
			addFile(data.name,'dropzone',file.name);
		});
		@if(isset($document)&&$document->files())
		@foreach($document->files() as $file)
			var mockFile={name:"{{$file->original}}", size:{{$file->size}}};
			myDropzone.emit("addedfile", mockFile);
			myDropzone.emit("complete", mockFile);
		@endforeach
		@endif
	});
	
	function addFile(name,type,dataname){
		$('#attatched').append('<input type="hidden" name="attach_'+type+'[]" value="'+name+'" data-name="'+dataname+'" />')
	}
	</script>
@stop

@section('body')
	<h3 class="menu_title">{{$board->name}} 글 @if(isset($document))수정@else쓰기@endif</h3>

	@if(session('message'))
		<div class="message success">{!!session('message')!!}</div>
	@endif
	
	<form class="form" method="post" action="{{url('/admin/board/documents/'.(isset($document)?'edit':'create'))}}{{$_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''}}" enctype="multipart/form-data">
		<div class="form_wrap">
			{!!csrf_field()!!}
			<input type="hidden" name="board" value="{{$board->id}}">
			@if(isset($document))
				<input type="hidden" name="id" value="{{$document->id}}">
			@endif
			
			@if(count($board->categories()))
				<div class="selects" id="category">
					<span>분류</span>
					@foreach($board->categories() as $category)
						<label class="select_wrap" onclick="$('#category a').removeClass('active');$(this).find('a').addClass('active')">
							<input type="radio" name="category" value="{{$category->id}}" class="blind" @if(isset($document)&&$document->category==$category->id) checked @endif>
							<a href="#" onclick="return false" @if(isset($document)&&$document->category==$category->id) class="active" @endif >✔︎</a>
							<span>{{$category->name}}</span>
						</label>
					@endforeach
				</div>
			@endif
			
			<label class="blind">
				<input type="text" name="title" value="">
			</label>
			<label class="input_wrap">
				<input type="text" name="title_real" value="@if(isset($document)){{$document->title}}@endif">
				<span>제목</span>
			</label>
			
			
			@if(count($board->extravars()))
				@foreach($board->extravars() as $extravar)
					<?php
						$extravar->content=\App\Encryption::checkEncrypted($extravar->content)?\App\Encryption::decrypt($extravar->content):$extravar->content;
					?>
					@if($extravar->type=='text')
						<label class="input_wrap">
							<input type="text" name="extravar{{$extravar->id}}" value="@if(isset($document)&&$document->extravar($extravar->id)){{$document->extravar($extravar->id)}}@elseif($extravar->content){{$extravar->content}}@endif">
							<span>@if($extravar->type){{$extravar->name}}@endif</span>
						</label>
						
					@elseif($extravar->type=='textarea')
						<label class="input_wrap">
							<textarea name="extravar{{$extravar->id}}">@if(isset($document)&&$document->extravar($extravar->id)){{$document->extravar($extravar->id)}}@elseif($extravar->content){{$extravar->content}}@endif</textarea>
							<span>@if($extravar->type){{$extravar->name}}@endif</span>
						</label>
						
					@elseif($extravar->type=='radio')
						<div class="selects" id="extravar{{$extravar->id}}">
							<span>@if($extravar->type){{$extravar->name}}@endif</span>
							@if($extravar->content)
								@foreach(explode('|',$extravar->content) as $content)
									<label class="select_wrap" onclick="$('#extravar{{$extravar->id}} a').removeClass('active');$(this).find('a').addClass('active')">
										<input type="radio" name="extravar{{$extravar->id}}" value="{{$content}}" class="blind" @if(isset($document)&&$document->extravar($extravar->id)==$content) checked @endif>
										<a href="#" onclick="$(this).parent().click();return false" @if(isset($document)&&$document->extravar($extravar->id)==$content) class="active" @endif >✔︎</a>
										<span>{{$content}}</span>
									</label>
								@endforeach
							@endif
						</div>
						
					@elseif($extravar->type=='checkbox')
						<div class="selects">
							<span>@if($extravar->type){{$extravar->name}}@endif</span>
							@if($extravar->content)
								@foreach(explode('|',$extravar->content) as $content)
									<label class="select_wrap" onclick="$(this).find('input').each(function(){$(this).prop('checked',!$(this).prop('checked'));});$(this).find('a').toggleClass('active');return false">
										<input type="checkbox" name="extravar{{$extravar->id}}[]" value="{{$content}}" class="blind" @if(isset($document)&&in_array($content,$document->extravar($extravar->id))) checked @endif >
										<a href="#" onclick="return false" @if(isset($document)&&in_array($content,$document->extravar($extravar->id))) class="active" @endif >✔︎</a>
										<span>{{$content}}</span>
									</label>
								@endforeach
							@endif
						</div>
						
					@elseif($extravar->type=='order')
						<div class="selects">
							<span>@if($extravar->type){{$extravar->name}}@endif</span>
							<div class="order_list">
								<ul>
									@if(isset($document))
										@if(count($document->extravar($extravar->id)))
											@foreach($document->extravar($extravar->id) as $content)
												<li>
													<input type="hidden" name="extravar{{$extravar->id}}[]" value="{{$content}}">
													{{$content}}
												</li>
											@endforeach
										@endif
									@else
										@if($extravar->content)
											@foreach(explode('|',$extravar->content) as $content)
												<li>
													<input type="hidden" name="extravar{{$extravar->id}}[]" value="{{$content}}">
													{{$content}}
												</li>
											@endforeach
										@endif
									@endif
								</ul>
							</div>
						</div>
						
					@elseif($extravar->type=='image')
						<label class="input_wrap">
							@if(isset($document)&&$document->extravar($extravar->id))
								<div class="thumbnail"><img src="{{url($document->extravar($extravar->id))}}" alt=""></div>
							@endif
							<input type="file" name="extravar{{$extravar->id}}" accept="image/*">
							<input type="hidden" name="extravar{{$extravar->id}}_original" value="@if(isset($document)&&$document->extravar($extravar->id)){{$document->extravar($extravar->id)}}@endif">
							<span>@if($extravar->type){{$extravar->name}}@endif</span>
						</label>
						
					@elseif($extravar->type=='file')
						<label class="input_wrap">
							@if(isset($document)&&$document->extravar($extravar->id))
								<div class="thumbnail"><a href="{{url($document->extravar($extravar->id))}}">{{\App\File::where('name',str_replace('/file/','',$document->extravar($extravar->id)))->first()->original}}</a></div>
							@endif
							<input type="file" name="extravar{{$extravar->id}}">
							<input type="hidden" name="extravar{{$extravar->id}}_original" value="@if(isset($document)&&$document->extravar($extravar->id)){{$document->extravar($extravar->id)}}@endif">
							<span>@if($extravar->type){{$extravar->name}}@endif</span>
						</label>
					
					@endif
				@endforeach
			@endif
		
			<label class="input_wrap label">
				<span>내용</span>
			</label>
			<div class="editor_wrap">
				<textarea id="content" name="content">@if(isset($document)){{$document->content}}@endif</textarea>
			</div>
							
			<div id="dropzone" class="dropzone"></div>
			<div id="attatched">
				@if(isset($document)&&$document->files())
				@foreach($document->files() as $file)
					<input type="hidden" name="attach_dropzone[]" value="{{$file->name}}" data-name="{{$file->original}}" />
				@endforeach
				@endif
			</div>
			
			<div class="selects nolabel" style="text-align:right">
				@if(Auth::check()&&array_key_exists(2,Auth::user()->groups()))
				<label class="select_wrap" onclick="$(this).find('input').each(function(){$(this).prop('checked',!$(this).prop('checked'));});$(this).find('a').toggleClass('active');return false">
					<input type="checkbox" name="notice" value="1" class="blind" @if(isset($document)&&$document->notice) checked @endif >
					<a href="#" onclick="return false" @if(isset($document)&&$document->secret) class="active" @endif >✔︎</a>
					<span>공지</span>
				</label>
				@endif
				
				<label class="select_wrap" onclick="$(this).find('input').each(function(){$(this).prop('checked',!$(this).prop('checked'));});$(this).find('a').toggleClass('active');return false">
					<input type="checkbox" name="secret" value="1" class="blind" @if(isset($document)&&$document->secret) checked @endif >
					<a href="#" onclick="return false" @if(isset($document)&&$document->secret) class="active" @endif >✔︎</a>
					<span>비밀 글</span>
				</label>
				
				<label class="select_wrap" onclick="$(this).find('input').each(function(){$(this).prop('checked',!$(this).prop('checked'));});$(this).find('a').toggleClass('active');return false">
					<input type="checkbox" name="allow_comment" value="1" class="blind" @if(!isset($document)||$document->allow_comment) checked @endif >
					<a href="#" onclick="return false" @if(!isset($document)||$document->allow_comment) class="active" @endif >✔︎</a>
					<span>댓글 허용</span>
				</label>
			</div>
			
			<div class="btnArea" style="margin-top:-10px">
				<button type="submit" class="button blue">등록하기</button>
				<span></span>
				<a href="{{url('/admin/board/'.$board->id.'/documents')}}{{$_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''}}" class="button gray" style="float:left">취소하기</a>
			</div>
		</div>
	</form>

@endsection