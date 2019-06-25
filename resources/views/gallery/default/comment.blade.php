@extends($layout?'layout.'.$layout->path.'.layout':'common',['title'=>$board->name?'&gt; '.(\App\Encryption::checkEncrypted($board->name)?\App\Encryption::decrypt($board->name):$board->name):''])

@section('head')
	@parent
	<script type="text/javascript" src="{{url('/ckeditor/ckeditor.js')}}"></script>
	<script type="text/javascript" src="{{url('/script/dropzone.js')}}"></script>
	<link rel="stylesheet" href="{{url('/style/dropzone.css')}}" />
	<script>
	Dropzone.autoDiscover = false;
	$(function(){
		CKEDITOR.replace('content',{});
		
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
		@if($comment->files())
		@foreach($comment->files() as $file)
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

@section($board->layout?'body':'container')
	<h3 class="table_caption">{{$board->name}}</h3>
	
	<div id="comment_write">
		@if(session('message'))
			<div class="message">{!!session('message')!!}</div>
		@endif
	
		<form method="post" action="{{url('/board/comment/'.$comment->id)}}{{$_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''}}">
			{!! csrf_field() !!}
			
			@if(session('error'))
				<div class="error">{{session('error')}}</div>
			@endif
			
			<input type="text" name="title" value="" class="blind">
			<div class="content" style="margin:10px">
				<textarea name="content">{{$comment->content}}</textarea>
			</div>
			
			<div id="dropzone" class="dropzone"></div>
			<div id="attatched">
				@if($comment->files())
				@foreach($comment->files() as $file)
					<input type="hidden" name="attach_dropzone[]" value="{{$file->name}}" data-name="{{$file->original}}" />
				@endforeach
				@endif
			</div>
			
			@if(Auth::check())
				<div class="selects nolabel" style="text-align:right">
					@if($document->isMine())
					<label class="select_wrap" onclick="$(this).find('input').each(function(){$(this).prop('checked',!$(this).prop('checked'));});$(this).find('a').toggleClass('active');return false">
						<input type="checkbox" name="notice" value="1" class="blind" @if($comment->notice) checked @endif >
						<a href="#" onclick="return false" @if($comment->notice) class="active" @endif >✔︎</a>
						<span>공지</span>
					</label>
					@endif
					
					<label class="select_wrap" onclick="$(this).find('input').each(function(){$(this).prop('checked',!$(this).prop('checked'));});$(this).find('a').toggleClass('active');return false">
						<input type="checkbox" name="secret" value="1" class="blind" @if($comment->secret) checked @endif >
						<a href="#" onclick="return false" @if($comment->secret) class="active" @endif >✔︎</a>
						<span>비밀 댓글</span>
					</label>
				</div>
			@endif
			
			<div class="btnArea" style="margin-top:-10px">
				<button type="submit" class="button black">등록하기</button>
				<span></span>
				<a href="{{url('/'.$board->url.(isset($document)?'/'.$document->id:''))}}{{$_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''}}#comment{{$comment->id}}" class="button gray" style="float:left">취소하기</a>
			</div>
		</form>
	</div>
	
@endsection