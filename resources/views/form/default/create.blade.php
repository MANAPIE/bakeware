@extends($layout?'layout.'.$layout->path.'.layout':'common',['title'=>$form->name?'&gt; '.(\App\Encryption::checkEncrypted($form->name)?\App\Encryption::decrypt($form->name):$form->name):''])

@section('head')
	@parent
	<script type="text/javascript" src="{{url('/script/jquery-ui.min.js.bakeware')}}"></script>
	<script type="text/javascript" src="{{url('/script/jquery.ui.touch-punch.min.js.bakeware')}}"></script>
	<script>
	$(function(){
		$('.order_list ul').sortable();
	});
	</script>
@stop

@section($form->layout?'body':'container')
	<h3 class="table_caption">{{$form->name}}</h3>
	
	@if($form->inPeriod())
		<form method="post" action="{{url('/'.$form->url.'/'.(isset($document)?$document->id.'/edit':'create'))}}{{$_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''}}" enctype="multipart/form-data">
			<div class="form_wrap">
				{!!csrf_field()!!}
					
				<input type="text" name="title" value="" class="blind">
				
				@if(count($form->questions()))
					@foreach($form->questions() as $extravar)
						@if($extravar->type=='text')
							<label class="input_wrap wide">
								<input type="text" name="extravar{{$extravar->id}}" value="@if(isset($document)&&$document->extravar($extravar->id)){{$document->extravar($extravar->id)}}@elseif($extravar->content){{$extravar->content}}@endif">
								<span>@if($extravar->type){{$extravar->name}}@endif</span>
							</label>
							
						@elseif($extravar->type=='textarea')
							<label class="input_wrap wide">
								<textarea name="extravar{{$extravar->id}}">@if(isset($document)&&$document->extravar($extravar->id)){{$document->extravar($extravar->id)}}@elseif($extravar->content){{$extravar->content}}@endif</textarea>
								<span>@if($extravar->type){{$extravar->name}}@endif</span>
							</label>
							
						@elseif($extravar->type=='radio')
							<div class="selects wide" id="extravar{{$extravar->id}}">
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
							<div class="selects wide">
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
							<div class="selects wide">
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
							<label class="input_wrap wide">
								@if(isset($document)&&$document->extravar($extravar->id))
									<div class="thumbnail"><img src="{{url($document->extravar($extravar->id))}}" alt=""></div>
								@endif
								<input type="file" name="extravar{{$extravar->id}}" accept="image/*">
								<input type="hidden" name="extravar{{$extravar->id}}_original" value="@if(isset($document)&&$document->extravar($extravar->id)){{$document->extravar($extravar->id)}}@endif">
								<span>@if($extravar->type){{$extravar->name}}@endif</span>
							</label>
							
						@elseif($extravar->type=='file')
							<label class="input_wrap wide">
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
		
				<div class="btnArea" style="margin-top:-10px">
					<button type="submit" class="button blue">등록하기</button>
				</div>
			
			</div>
		</form>
	@else
		
		<div class="no_item">
			답변 기간이 아닙니다.<br>
			<br>
			답변 기간: {{$form->start_at}} ~ {{$form->end_at}}
		</div>
		
	@endif
	
@endsection