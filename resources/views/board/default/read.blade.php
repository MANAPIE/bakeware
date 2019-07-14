@extends('board.'.$board->skin.'.list',['title'=>($board->name?'&gt; '.(\App\Encryption::checkEncrypted($board->name)?\App\Encryption::decrypt($board->name):$board->name):'').($document->title?'&gt; '.$document->title:'')])

@section('read')
	<div class="read_wrap">
		@if($document->secret&&!$document->isMine())
			<div class="no_item">
				비밀 글입니다.
			</div>
			
		@else
			<div class="read_header">
				<h4 class="title">
					@if($document->category())
						<span class="category">{{$document->category()->name}}</span>
					@endif
					@if($document->secret)🔒@endif
					@if($document->notice)📣@endif
					{{$document->title}}
				</h4>
				<div class="meta">
					<span class="date">
						@if($board->anonymous==2)
							<i>익명</i>
						@elseif($board->anonymous==1)
							@if($document->author()&&array_key_exists(2,$document->author()->groups()))
								{{$document->author()->nickname}}
							@else
								<i>익명</i>
							@endif
						@else
							@if($document->author())
								{{$document->author()->nickname}}
							@else
								<i>비회원</i>
							@endif
						@endif
					</span>
					<span class="date">{{$document->created_at}} 작성</span>
					@if($board->sort_by=='updated_at')<span class="date">{{$document->updated_at}} 새 활동</span>@endif
				</div>
			</div>
			<div class="read_body">
				
				@if(count($board->extravars()))
					@foreach($board->extravars() as $extravar)
						<div class="extravar">
							<h6>{{$extravar->name}}</h6>
							@if($extravar->type=='text')
								@if($document->extravar($extravar->id)){{$document->extravar($extravar->id)}}@endif
							@elseif($extravar->type=='textarea')
								@if($document->extravar($extravar->id)){!!str_replace('&lt;br /&gt;','<br>',htmlspecialchars(nl2br($document->extravar($extravar->id))))!!}@endif
							@elseif($extravar->type=='radio')
								@if($document->extravar($extravar->id)){{$document->extravar($extravar->id)}}@endif
							@elseif($extravar->type=='checkbox')
								@if(count($document->extravar($extravar->id)))
									{{implode(', ',$document->extravar($extravar->id))}}
								@endif
							@elseif($extravar->type=='order')
								@if(count($document->extravar($extravar->id)))
									{{implode(', ',$document->extravar($extravar->id))}}
								@endif
							@elseif($extravar->type=='image')
								@if($document->extravar($extravar->id))<img src="{{url($document->extravar($extravar->id))}}" alt="">@endif
							@elseif($extravar->type=='file')
								@if($document->extravar($extravar->id))<a href="{{url($document->extravar($extravar->id))}}">💾 {{\App\File::where('name',str_replace('/file/','',$document->extravar($extravar->id)))->first()->original}}</a>@endif
							@endif
						</div>
					@endforeach
				@endif
				
				<div class="real_content">
					{!!$document->content()!!}
				</div>
				
				@if($document->files())
					<div class="download">
					@foreach($document->files() as $file)
						<a href="{{url('file/'.$file->name)}}">💾 {{$file->original}} ({{round($file->size/1024,2)}} KB)</a><br />
					@endforeach
					</div>
					@foreach($document->files() as $file)
					@if($file->mime=='application/pdf')
						<div class="pdf_viewer">
							<iframe src="https://docs.google.com/gview?url={{url('file/'.$file->name)}}&embedded=true"></iframe>
						</div>
					@endif
					@endforeach
				@endif
			</div>
			@if($document->isMine())
				<div class="read_footer">
					<div class="btnArea">
						<form id="board{{$document->id}}delete" class="form" method="post" action="{{url('/'.$board->url.'/'.$document->id.'/delete')}}{{$_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''}}">
							{!!csrf_field()!!}
						</form>
						<button style="float:left" type="button" class="button white" onclick="if(confirm('정말로 삭제하시겠습니까?'))$('#board{{$document->id}}delete').submit();return false"><span>삭제</span></button>
						
						<a href="{{url('/'.$board->url.'/'.$document->id.'/edit')}}{{$_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''}}" class="button gray">수정</a>
					</div>
				</div>
			@endif
		@endif
		
		
		@section('comment')
			<a id="comment"></a>
			
			@if($document->allow_comment && $board->authority('comment'))
				@section('head')
					@parent
					<script type="text/javascript" src="{{url('/ckeditor/ckeditor.js')}}"></script>
					<script type="text/javascript" src="{{url('/script/dropzone.js.bakeware')}}"></script>
					<link rel="stylesheet" href="{{url('/style/dropzone.css.bakeware')}}" />
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
					});
					
					function addFile(name,type,dataname){
						$('#attatched').append('<input type="hidden" name="attach_'+type+'[]" value="'+name+'" data-name="'+dataname+'" />')
					}
					</script>
				@stop
			@endif
			
			@if($document->count_comment>0)
				<div id="comment_list">
					<h3 class="comment_count">댓글 {{$document->count_comment}}개</h3>
					<ul class="comment_list">
					@foreach($document->comments(true) as $comment)
						<li>
							<a id="comment{{$comment->id}}"></a>
							<div class="meta">
								<span class="author">
									@if($comment->secret)🔒@endif
									📣	
									@if($board->anonymous==2)
										<i>익명</i>
									@elseif($board->anonymous==1)
										@if($comment->author()&&array_key_exists(2,$comment->author()->groups()))
											{{$comment->author()->nickname}}
										@else
											<i>익명</i>
										@endif
									@else
										@if($comment->author())
											{{$comment->author()->nickname}}
										@else
											<i>비회원</i>
										@endif
									@endif
								</span>
								<span class="date">
									{{$comment->created_at}} 작성
								</span>
								
								@if($comment->isMine() && $document->allow_comment && $board->authority('comment'))
									<div class="buttons">
										<a href="{{url('/board/comment/'.$comment->id)}}{{$_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''}}">수정</a>
										<a href="#" onclick="if(confirm('정말로 삭제하시겠습니까?'))$('#board{{$comment->id}}delete').submit();return false">삭제</a>
										<form id="board{{$comment->id}}delete" class="form" method="post" action="{{url('/board/comment/delete/'.$comment->id)}}{{$_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''}}">
											{!!csrf_field()!!}
										</form>
									</div>
								@endif
							</div>
							@if($comment->secret&&!$comment->isMine())
								<div class="no_item" style="padding:30px 0">
									비밀 댓글입니다.
								</div>
							@else
								<div class="content" style="background:#eee">
									<div class="real_content">
										{!!$comment->content()!!}
									</div>
								
									@if($comment->files())
										<div class="download">
										@foreach($comment->files() as $file)
											<a href="{{url('file/'.$file->name)}}">💾 {{$file->original}} ({{round($file->size/1024,2)}} KB)</a><br />
										@endforeach
										</div>
										@foreach($comment->files() as $file)
										@if($file->mime=='application/pdf')
											<div class="pdf_viewer">
												<iframe src="https://docs.google.com/gview?url={{url('file/'.$file->name)}}&embedded=true"></iframe>
											</div>
										@endif
										@endforeach
									@endif
								</div>
							@endif
						</li>
					@endforeach
					
					@foreach($document->comments() as $comment)
						<li>
							<a id="comment{{$comment->id}}"></a>
							<div class="meta">
								<span class="author">
									@if($comment->secret)🔒@endif
									@if($board->anonymous==2)
										<i>익명</i>
									@elseif($board->anonymous==1)
										@if($comment->author()&&array_key_exists(2,$comment->author()->groups()))
											{{$comment->author()->nickname}}
										@else
											<i>익명</i>
										@endif
									@else
										@if($comment->author())
											{{$comment->author()->nickname}}
										@else
											<i>비회원</i>
										@endif
									@endif
								</span>
								<span class="date">
									{{$comment->created_at}} 작성
								</span>
								
								@if($comment->isMine() && $document->allow_comment && $board->authority('comment'))
									<div class="buttons">
										<a href="{{url('/board/comment/'.$comment->id)}}{{$_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''}}">수정</a>
										<a href="#" onclick="if(confirm('정말로 삭제하시겠습니까?'))$('#board{{$comment->id}}delete').submit();return false">삭제</a>
										<form id="board{{$comment->id}}delete" class="form" method="post" action="{{url('/board/comment/delete/'.$comment->id)}}{{$_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''}}">
											{!!csrf_field()!!}
										</form>
									</div>
								@endif
							</div>
							@if($comment->secret&&!$comment->isMine())
								<div class="no_item" style="padding:30px 0">
									비밀 댓글입니다.
								</div>
							@else
								<div class="content">
									<div class="real_content">
										{!!$comment->content()!!}
									</div>
								
									@if($comment->files())
										<div class="download">
										@foreach($comment->files() as $file)
											<a href="{{url('file/'.$file->name)}}">💾 {{$file->original}} ({{round($file->size/1024,2)}} KB)</a><br />
										@endforeach
										</div>
										@foreach($comment->files() as $file)
										@if($file->mime=='application/pdf')
											<div class="pdf_viewer">
												<iframe src="https://docs.google.com/gview?url={{url('file/'.$file->name)}}&embedded=true"></iframe>
											</div>
										@endif
										@endforeach
									@endif
								</div>
							@endif
						</li>
					@endforeach
					</ul>
				</div>
			@endif
			
			@if($document->allow_comment && $board->authority('comment'))
				<div class="btnArea comment_write">
					<button type="button" class="button blue" onclick="$('#comment_write').slideDown();"><span>댓글 쓰기</span></button>
				</div>
				
				<div id="comment_write" style="display:none">
					<form method="post" action="{{url('/'.$board->url.'/'.$document->id.'/comment')}}{{$_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''}}">
						{!! csrf_field() !!}
						
						@if(session('error'))
							<div class="error">{{session('error')}}</div>
						@endif
						
						<input type="text" name="title" value="" class="blind">
						<div class="content" style="margin:10px">
							<textarea name="content">{{old('content')}}</textarea>
						</div>
						
						<div id="dropzone" class="dropzone"></div>
						<div id="attatched"></div>
						
						@if(Auth::check())
							<div class="selects nolabel" style="text-align:right">
								@if($document->isMine())
								<label class="select_wrap" onclick="$(this).find('input').each(function(){$(this).prop('checked',!$(this).prop('checked'));});$(this).find('a').toggleClass('active');return false">
									<input type="checkbox" name="notice" value="1" class="blind">
									<a href="#" onclick="return false">✔︎</a>
									<span>공지</span>
								</label>
								@endif
								
								<label class="select_wrap" onclick="$(this).find('input').each(function(){$(this).prop('checked',!$(this).prop('checked'));});$(this).find('a').toggleClass('active');return false">
									<input type="checkbox" name="secret" value="1" class="blind">
									<a href="#" onclick="return false">✔︎</a>
									<span>비밀 댓글</span>
								</label>
							</div>
						@endif
						
						<div class="btnArea" style="margin-top:-10px">
							<button type="submit" class="button black">등록하기</button>
						</div>
					</form>
				</div>
			@endif
		@show
		
	</div>
	
@endsection