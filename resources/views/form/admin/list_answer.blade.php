@extends('admin.layout',['title'=>'&gt; 폼 답변 보기 &gt; '.$form->name])

@section('body')
	<h3 class="menu_title">{{$form->name}} &gt; 답변 보기</h3>
	
	@if(session('message'))
		<div class="message success">{!!session('message')!!}</div>
	@endif
	<div class="answer_wrap">
		<div class="read_header">
			<div class="meta">
				<span class="date">{{$form->count_read}} 조회</span>
				<span class="date">{{$form->count_answer}} 답변</span>
				<span class="date">{{$form->start_at}} 시작</span>
				<span class="date">{{$form->end_at}} 종료</span>
				<span class="date"><span style="position:relative;top:2px" class="online @if($form->inPeriod()) active @else inactive @endif "></span></span>
			</div>
		</div>
		
		<div class="read_body">
			@if(count($form->questions()))
				@foreach($form->questions() as $question)
					<div class="question">
						<h6>{{$question->name}}</h6>
						<div class="answers">
							@if($question->type=='image')
								@foreach($form->question_statistic($question->id) as $key=>$val)
									@if($key)<img src="{{url($key)}}" alt="" onclick="$(this).toggleClass('active')">@endif
								@endforeach
							@elseif($question->type=='file')
								@foreach($form->question_statistic($question->id) as $key=>$val)
									@if($key)
										<div class="answer">
											<a href="{{url($key)}}">💾 {{\App\File::where('name',str_replace('/file/','',$key))->first()->original}}</a>
										</div>
									@endif
								@endforeach
							@else
								@foreach($form->question_statistic($question->id) as $key=>$val)
									<div class="answer">
										@if($question->type=='order')
											{{str_replace('|',', ',$key)}}
										@else
											{{$key}}
										@endif
										&nbsp;
										<span>{{$val}}<span>/{{$form->count_answer}}</span></span>
									</div>
								@endforeach
							@endif
						</div>
					</div>
				@endforeach
			@endif
		</div>
			
		<form method="post" id="delete" action="{{url('/admin/form/answer/delete')}}{{$_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''}}">
			{!!csrf_field()!!}
			
			<div class="table_wrap answers">
				<table>
				<tr>
				<th></th>
				<th>날짜</th>
				@foreach($form->questions() as $question)
					<th>
						{{mb_substr($question->name,0,20)}}...
					</th>
				@endforeach
				</tr>
				@foreach($form->answers() as $answer)
					<tr>
					<td class="no">
						<input type="checkbox" name="answers[]" value="{{$answer->id}}">
					</td>
					<td class="date">
						{{$answer->created_at}}
					</td>
					@foreach($form->questions() as $question)
						<td>
							@if($question->type=='text')
								@if($answer->item($question->id)){{$answer->item($question->id)}}@endif
							@elseif($question->type=='textarea')
								@if($answer->item($question->id)){!!str_replace('&lt;br /&gt;','<br>',htmlspecialchars(nl2br($answer->item($question->id))))!!}@endif
							@elseif($question->type=='radio')
								@if($answer->item($question->id)){{$answer->item($question->id)}}@endif
							@elseif($question->type=='checkbox')
								@if(count($answer->item($question->id)))
									{{implode(', ',$answer->item($question->id))}}
								@endif
							@elseif($question->type=='order')
								@if(count($answer->item($question->id)))
									{{implode(', ',$answer->item($question->id))}}
								@endif
							@elseif($question->type=='image')
								@if($answer->item($question->id))<img src="{{url($answer->item($question->id))}}" alt="" onclick="$(this).toggleClass('active')">@endif
							@elseif($question->type=='file')
								@if($answer->item($question->id))💾 {{\App\File::where('name',str_replace('/file/','',$answer->item($question->id)))->first()->original}}@endif
							@endif
							&nbsp;
						</td>
					@endforeach
					</tr>
				@endforeach
				</table>
			</div>
		</form>
	</div>
	
	@section('pagination')
	<?php $link_limit=5; ?>
	<ul class="pagination">
		<?php
			$half_total_links=floor(($link_limit+2)/2);
			$from=$form->answers()->currentPage()-$half_total_links;
			$to=$form->answers()->currentPage()+$half_total_links;
			if($form->answers()->currentPage()<$half_total_links){
				$to+=$half_total_links-$form->answers()->currentPage();
			}
			if($form->answers()->lastPage()-$form->answers()->currentPage() < $half_total_links){
				$from-=$half_total_links-($form->answers()->lastPage()-$form->answers()->currentPage())-1;
			}
		?>
		@if($form->answers()->currentPage()>ceil($link_limit/2))
			<li class="first"><a href="{{url('/admin/form/answer/'.$form->id)}}?page={{$from>1?$from:'1'}}@foreach($_GET as $k=>$v){{$k!='page'?'&'.$k.'='.$v:''}}@endforeach">&lt;</a></li>
		@endif
		@for($i=1;$i<=$form->answers()->lastPage();$i++)
			@if ($from < $i && $i < $to)
				<li class="{{($form->answers()->currentPage() == $i)?' active':''}}">
					<a href="{{url('/admin/form/answer/'.$form->id)}}?page={{$i}}@foreach($_GET as $k=>$v){{$k!='page'?'&'.$k.'='.$v:''}}@endforeach">{{$i}}</a>
				</li>
			@endif
		@endfor
		@if($form->answers()->currentPage()<=$form->answers()->lastPage()-ceil($link_limit/2))
			<li class="last"><a href="{{url('/admin/form/answer/'.$form->id)}}?page={{$to}}@foreach($_GET as $k=>$v){{$k!='page'?'&'.$k.'='.$v:''}}@endforeach">&gt;</a></li>
		@endif
	</ul>
	@show
		
	<div class="btnArea" style="margin-right:-5px">
		<a href="{{url('/admin/form/answer')}}{{$_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''}}" class="button white">돌아가기</a>
		<button type="button" class="button gray" onclick="if($('input:checked').length<1){alert('삭제할 답변을 선택해주세요.');return false;} if(confirm('정말로 삭제하시겠습니까?'))$('#delete').submit();return false"><span>일괄 삭제</span></button>
	</div>
	
@endsection