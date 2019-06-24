@extends($layout?'layout.'.$layout->path.'.layout':'common',['title'=>'회원 가입'])

@section('head')
	@parent
	<script type="text/javascript" src="{{url('/script/jquery-ui.min.js')}}"></script>
	<script type="text/javascript" src="{{url('/script/jquery.ui.touch-punch.min.js')}}"></script>
	<script>
	$(function(){
		$('.order_list ul').sortable();
	});
	</script>
@stop

@section($layout?'body':'container')
	<h3 class="table_caption">회원 가입</h3>
	
	@yield('read')

	@if(session('message'))
		<div class="message success">{!!session('message')!!}</div>
	@endif
	
	<form method="post" action="{{url('/register/join')}}{{$_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''}}" enctype="multipart/form-data">
		<div class="form_wrap">
			{!!csrf_field()!!}
				
			<input type="text" name="title" value="" class="blind">
			
			<label class="input_wrap">
				<input type="text" name="name" value="" required>
				<span>아이디</span>
			</label>
			
			<label class="input_wrap">
				<input type="password" name="password" value="" minlength="8" required>
				<span>비밀번호</span>
			</label>
			<span class="description">비밀번호는 8자 이상입니다.</span>
			
			<label class="input_wrap">
				<input type="password" name="password_confirm" value="" minlength="8" required>
				<span>한 번 더</span>
			</label>
			<span class="description">비밀번호를 다시 한 번 입력해주세요.</span>
			
			<script>
			$(function(){
				$('input[name=name').keyup(function(){
					if($(this).val()){
						$.get('{{url('/user/check/')}}'+'?name='+$(this).val(),function(data){
							if(data=='Y')
								$('input[name=name]')[0].setCustomValidity('이미 존재하는 아이디입니다.');
							else
								$('input[name=name]')[0].setCustomValidity('');
						});
					}
				});
				
				$('input[name=password').keyup(function(){
					$('input[name=password_confirm').val('');
				});
				
				$('input[name=password_confirm').keyup(function(){
					if($('input[name=password').val()&&$('input[name=password]').val()!=$('input[name=password_confirm]').val())
						this.setCustomValidity('비밀번호 재입력이 일치하지 않습니다.');
					else
						this.setCustomValidity('');
				});
			});
			</script>
			
			<label class="input_wrap">
				<input type="text" name="nickname" value="" required>
				<span>이름</span>
			</label>
			
			<label class="input_wrap">
				<input type="file" name="profile" accept="image/*">
				<input type="hidden" name="profile_original" value="">
				<span>사진</span>
			</label>
			
			<label class="input_wrap">
				<input type="email" name="email" value="">
				<span>이메일</span>
			</label>
			
			@if(count(\App\User::extravars()))
				@foreach(\App\User::extravars() as $extravar)
					<?php
						$extravar->content=\App\Encryption::checkEncrypted($extravar->content)?\App\Encryption::decrypt($extravar->content):$extravar->content;
					?>
					@if($extravar->type=='text')
						<label class="input_wrap">
							<input type="text" name="extravar{{$extravar->id}}" value="{{$extravar->content}}">
							<span>@if($extravar->type){{$extravar->name}}@endif</span>
						</label>
						
					@elseif($extravar->type=='textarea')
						<label class="input_wrap">
							<textarea name="extravar{{$extravar->id}}">{{$extravar->content}}</textarea>
							<span>@if($extravar->type){{$extravar->name}}@endif</span>
						</label>
						
					@elseif($extravar->type=='radio')
						<div class="selects" id="extravar{{$extravar->id}}">
							<span>@if($extravar->type){{$extravar->name}}@endif</span>
							@if($extravar->content)
								@foreach(explode('|',$extravar->content) as $content)
									<label class="select_wrap" onclick="$('#extravar{{$extravar->id}} a').removeClass('active');$(this).find('a').addClass('active')">
										<input type="radio" name="extravar{{$extravar->id}}" value="{{$content}}" class="blind">
										<a href="#" onclick="$(this).parent().click();return false">✔︎</a>
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
										<input type="checkbox" name="extravar{{$extravar->id}}[]" value="{{$content}}" class="blind">
										<a href="#" onclick="return false">✔︎</a>
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
									@if($extravar->content)
										@foreach(explode('|',$extravar->content) as $content)
											<li>
												<input type="hidden" name="extravar{{$extravar->id}}[]" value="{{$content}}">
												{{$content}}
											</li>
										@endforeach
									@endif
								</ul>
							</div>
						</div>
						
					@elseif($extravar->type=='image')
						<label class="input_wrap">
							<input type="file" name="extravar{{$extravar->id}}" accept="image/*">
							<input type="hidden" name="extravar{{$extravar->id}}_original" value="">
							<span>@if($extravar->type){{$extravar->name}}@endif</span>
						</label>
						
					@elseif($extravar->type=='file')
						<label class="input_wrap">
							<input type="file" name="extravar{{$extravar->id}}">
							<input type="hidden" name="extravar{{$extravar->id}}_original" value="">
							<span>@if($extravar->type){{$extravar->name}}@endif</span>
						</label>
					
					@endif
					
					@if($extravar->description)
						<span class="description">{{$extravar->description}}</span>
					@endif
				@endforeach
			@endif
	
			<div class="btnArea" style="margin-top:-10px">
				<button type="submit" class="button blue">등록하기</button>
			</div>
		
		</div>
	</form>
	
	
@endsection