@extends('admin.layout',['title'=>'&gt; 갤러리 &gt; '.$gallery->name.' &gt; 액자 만들기'])

@section('head')
	@parent
	<script type="text/javascript" src="{{url('/ckeditor/ckeditor.js')}}"></script>
	<script type="text/javascript" src="{{url('/script/jquery-ui.min.js.bakeware')}}"></script>
	<script type="text/javascript" src="{{url('/script/jquery.ui.touch-punch.min.js.bakeware')}}"></script>
	<script type="text/javascript" src="{{url('/script/dropzone.js.bakeware')}}"></script>
	<link rel="stylesheet" href="{{url('/style/dropzone.css.bakeware')}}" />
	<script>
	Dropzone.autoDiscover = false;
	var myDropzone;
	$(function(){
		CKEDITOR.replace('content',{});
		$('.order_list ul').sortable();
		
		Dropzone.autoDiscover = false;
	    myDropzone = new Dropzone("#dropzone",{
		    dictDefaultMessage: "사진 파일을 드래그하거나 이곳을 눌러 업로드하세요",
		    acceptedFiles: "image/*",
			init: function(){
				this.on("success", function(file, responseText){
					file.previewTemplate.appendChild(document.createTextNode(responseText));
					sortFile();
					$('#dropzone').sortable();
					$('#dropzone').off('sortupdate');
					$('#dropzone').on('sortupdate',function(){
						sortFile();
					});
				});
			},
	    	url: "/upload/dropzone/image",
	    	addRemoveLinks: true,
	    	removedfile: function(file){
	    		$('#attatched input[data-name=\''+file.name+'\']').remove();
	    		var _ref;
	    		return (_ref = file.previewElement) != null ? _ref.parentNode.removeChild(file.previewElement) : void 0;
	    	},
	    });
		myDropzone.on("success", function(file,data){
            preview = file.previewElement;
            
            $(preview).append('<div class="dz-dataname blind">'+data.name+'</div>');
            
            sortFile();
		});
		@if(isset($cadre)&&$cadre->files())
				
	        var previews = document.getElementsByClassName('dz-preview');
	        var previews_i=0;
			@foreach($cadre->files() as $file)
				var mockFile={name:"{{$file->original}}", size:{{$file->size}}};
				myDropzone.emit("addedfile", mockFile);
				myDropzone.emit("complete", mockFile);
				myDropzone.emit("thumbnail", mockFile,"/file/thumb/{{$file->name}}");
	            preview = previews[previews_i];
	            $(preview).append('<div class="dz-dataname blind">{{$file->name}}</div>');
	            previews_i++;
			@endforeach
			$('#dropzone').sortable();
			$('#dropzone').off('sortupdate');
			$('#dropzone').on('sortupdate',function(){
				sortFile();
			});
		@endif
	});
	
	function sortFile(){
		setTimeout(function(){
			$('#attatched').empty();
			$('#dropzone .dz-complete').each(function(){
				var name=$(this).find('.dz-dataname').text().split('/');
				addFile(name[name.length-1],'dropzone',$(this).find('.dz-filename').text());
			});
		},500);
	}
	
	function addFile(name,type,dataname){
		$('#attatched').append('<input type="hidden" name="attach_'+type+'[]" value="'+name+'" data-name="'+dataname+'" />');
	}
	</script>
@stop

@section('body')
	<h3 class="menu_title">{{$gallery->name}} 액자 @if(isset($cadre))수정@else만들기@endif</h3>

	@if(session('message'))
		<div class="message success">{!!session('message')!!}</div>
	@endif
	
	<form class="form" method="post" action="{{url('/admin/gallery/cadres/'.(isset($cadre)?'edit':'create'))}}{{$_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''}}" enctype="multipart/form-data">
		<div class="form_wrap">
			{!!csrf_field()!!}
			<input type="hidden" name="gallery" value="{{$gallery->id}}">
			@if(isset($cadre))
				<input type="hidden" name="id" value="{{$cadre->id}}">
			@endif
			
			@if(count($gallery->categories()))
				<div class="selects" id="category">
					<span>분류</span>
					@foreach($gallery->categories() as $category)
						<label class="select_wrap" onclick="$('#category a').removeClass('active');$(this).find('a').addClass('active')">
							<input type="radio" name="category" value="{{$category->id}}" class="blind" @if(isset($cadre)&&$cadre->category==$category->id) checked @endif>
							<a href="#" onclick="return false" @if(isset($cadre)&&$cadre->category==$category->id) class="active" @endif >✔︎</a>
							<span>{{$category->name}}</span>
						</label>
					@endforeach
				</div>
			@endif
			
			<label class="blind">
				<input type="text" name="title" value="">
			</label>
							
			<div id="dropzone" class="dropzone"></div>
			<div id="attatched">
				@if(isset($cadre)&&$cadre->files())
				@foreach($cadre->files() as $file)
					<input type="hidden" name="attach_dropzone[]" value="{{$file->name}}" data-name="{{$file->original}}" />
				@endforeach
				@endif
			</div>
			<span class="description">업로드된 사진은 드래그하여 순서를 조정할 수 있습니다.</span>
			
			
			@if(count($gallery->extravars()))
				@foreach($gallery->extravars() as $extravar)
					<?php
						$extravar->content=\App\Encryption::checkEncrypted($extravar->content)?\App\Encryption::decrypt($extravar->content):$extravar->content;
					?>
					@if($extravar->type=='text')
						<label class="input_wrap">
							<input type="text" name="extravar{{$extravar->id}}" value="@if(isset($cadre)&&$cadre->extravar($extravar->id)){{$cadre->extravar($extravar->id)}}@elseif($extravar->content){{$extravar->content}}@endif">
							<span>@if($extravar->type){{$extravar->name}}@endif</span>
						</label>
						
					@elseif($extravar->type=='textarea')
						<label class="input_wrap">
							<textarea name="extravar{{$extravar->id}}">@if(isset($cadre)&&$cadre->extravar($extravar->id)){{$cadre->extravar($extravar->id)}}@elseif($extravar->content){{$extravar->content}}@endif</textarea>
							<span>@if($extravar->type){{$extravar->name}}@endif</span>
						</label>
						
					@elseif($extravar->type=='radio')
						<div class="selects" id="extravar{{$extravar->id}}">
							<span>@if($extravar->type){{$extravar->name}}@endif</span>
							@if($extravar->content)
								@foreach(explode('|',$extravar->content) as $content)
									<label class="select_wrap" onclick="$('#extravar{{$extravar->id}} a').removeClass('active');$(this).find('a').addClass('active')">
										<input type="radio" name="extravar{{$extravar->id}}" value="{{$content}}" class="blind" @if(isset($cadre)&&$cadre->extravar($extravar->id)==$content) checked @endif>
										<a href="#" onclick="$(this).parent().click();return false" @if(isset($cadre)&&$cadre->extravar($extravar->id)==$content) class="active" @endif >✔︎</a>
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
										<input type="checkbox" name="extravar{{$extravar->id}}[]" value="{{$content}}" class="blind" @if(isset($cadre)&&in_array($content,$cadre->extravar($extravar->id))) checked @endif >
										<a href="#" onclick="return false" @if(isset($cadre)&&in_array($content,$cadre->extravar($extravar->id))) class="active" @endif >✔︎</a>
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
									@if(isset($cadre))
										@if(count($cadre->extravar($extravar->id)))
											@foreach($cadre->extravar($extravar->id) as $content)
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
							@if(isset($cadre)&&$cadre->extravar($extravar->id))
								<div class="thumbnail"><img src="{{url($cadre->extravar($extravar->id))}}" alt=""></div>
							@endif
							<input type="file" name="extravar{{$extravar->id}}" accept="image/*">
							<input type="hidden" name="extravar{{$extravar->id}}_original" value="@if(isset($cadre)&&$cadre->extravar($extravar->id)){{$cadre->extravar($extravar->id)}}@endif">
							<span>@if($extravar->type){{$extravar->name}}@endif</span>
						</label>
						
					@elseif($extravar->type=='file')
						<label class="input_wrap">
							@if(isset($cadre)&&$cadre->extravar($extravar->id))
								<div class="thumbnail"><a href="{{url($cadre->extravar($extravar->id))}}">{{\App\File::where('name',str_replace('/file/','',$cadre->extravar($extravar->id)))->first()->original}}</a></div>
							@endif
							<input type="file" name="extravar{{$extravar->id}}">
							<input type="hidden" name="extravar{{$extravar->id}}_original" value="@if(isset($cadre)&&$cadre->extravar($extravar->id)){{$cadre->extravar($extravar->id)}}@endif">
							<span>@if($extravar->type){{$extravar->name}}@endif</span>
						</label>
					
					@endif
				@endforeach
			@endif
		
			<label class="input_wrap label">
				<span>내용</span>
			</label>
			<div class="editor_wrap">
				<textarea id="content" name="content">@if(isset($cadre)){{$cadre->content}}@endif</textarea>
			</div>
			
			<div class="btnArea" style="margin-top:-10px">
				<button type="submit" class="button blue">등록하기</button>
				<span></span>
				<a href="{{url('/admin/gallery/'.$gallery->id.'/cadres')}}{{$_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''}}" class="button gray" style="float:left">취소하기</a>
			</div>
		</div>
	</form>

@endsection