@extends('admin.layout',['title'=>isset($user)?'&gt; 회원 &gt; '.$user->nickname:'&gt; 회원 &gt; 추가'])

@section('body')
	<h3 class="menu_title">회원 @if(isset($user))관리@else추가@endif</h3>

	@if(session('message'))
		<div class="message success">{!!session('message')!!}</div>
	@endif
	
	<form class="form" method="post" action="{{url('/admin/user/'.(isset($user)?'edit':'create'))}}{{$_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''}}" enctype="multipart/form-data">
		<div class="form_wrap">
			{!!csrf_field()!!}
			@if(isset($user))
				<input type="hidden" name="id" value="{{$user->id}}">
			@endif
			
			<label class="input_wrap">
				<input type="text" name="name" value="@if(isset($user)){{$user->name}}@endif" required>
				<span>아이디</span>
			</label>
			
			<label class="input_wrap">
				<input type="password" name="password" value="" minlength="8" @if(!isset($user))required @endif>
				<span>비밀번호</span>
			</label>
			<span class="description">@if(isset($user))비밀번호를 바꾸려면 입력해주세요.@endif 비밀번호는 8자 이상입니다.</span>
			
			<label class="input_wrap">
				<input type="password" name="password_confirm" value="" minlength="8" @if(!isset($user))required @endif>
				<span>한 번 더</span>
			</label>
			<span class="description">@if(isset($user))비밀번호를 바꾸려면 다시 한 번 입력해주세요.@else비밀번호를 다시 한 번 입력해주세요.@endif</span>
			
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
			
			<div class="selects">
				<span>역할</span>
				@foreach($groups as $group)
					<label class="select_wrap" onclick="$(this).find('input').each(function(){$(this).prop('checked',!$(this).prop('checked'));});$(this).find('a').toggleClass('active');return false">
						<input type="checkbox" name="group[]" value="{{$group->id}}" class="blind" @if((isset($user)&&array_key_exists($group->id,$user->groups()))||(!isset($user)&&$group->id==3)) checked @endif >
						<a href="#" onclick="return false" @if((isset($user)&&array_key_exists($group->id,$user->groups()))||(!isset($user)&&$group->id==3)) class="active" @endif >✔︎</a>
						<span>{{$group->name}}</span>
					</label>
				@endforeach
			</div>
			
			<label class="input_wrap">
				<input type="text" name="nickname" value="@if(isset($user)){{$user->nickname}}@endif" required>
				<span>이름</span>
			</label>
			
			<label class="input_wrap">
				@if(isset($user)&&$user->thumbnail())
					<div class="thumbnail"><img src="{{url($user->thumbnail())}}" alt=""></div>
				@endif
				<input type="file" name="profile" accept="image/*">
				<input type="hidden" name="profile_original" value="@if(isset($user)&&$user->thumbnail()){{$user->thumbnail()}}@endif">
				<span>사진</span>
			</label>
			@if(isset($user))
				<span class="description">프로필 사진을 바꾸면 적용되는데 최대 하루 정도 걸립니다. 바로 보고 싶으면 저장 후에 Shift+새로고침을 해주세요.</span>
			@endif
			
			<label class="input_wrap">
				<input type="email" name="email" value="@if(isset($user)){{$user->email}}@endif">
				<span>이메일</span>
			</label>
			
			@if(count(\App\User::extravars()))
				@foreach(\App\User::extravars() as $extravar)
					<?php
						$extravar->content=\App\Encryption::checkEncrypted($extravar->content)?\App\Encryption::decrypt($extravar->content):$extravar->content;
					?>
					@if($extravar->type=='text')
						<label class="input_wrap">
							<input type="text" name="extravar{{$extravar->id}}" value="@if(isset($user)&&$user->extravar($extravar->id)){{$user->extravar($extravar->id)}}@elseif($extravar->content){{$extravar->content}}@endif">
							<span>@if($extravar->type){{$extravar->name}}@endif</span>
						</label>
						
					@elseif($extravar->type=='textarea')
						<label class="input_wrap">
							<textarea name="extravar{{$extravar->id}}">@if(isset($user)&&$user->extravar($extravar->id)){{$user->extravar($extravar->id)}}@elseif($extravar->content){{$extravar->content}}@endif</textarea>
							<span>@if($extravar->type){{$extravar->name}}@endif</span>
						</label>
						
					@elseif($extravar->type=='radio')
						<div class="selects" id="extravar{{$extravar->id}}">
							<span>@if($extravar->type){{$extravar->name}}@endif</span>
							@if($extravar->content)
								@foreach(explode('|',$extravar->content) as $content)
									<label class="select_wrap" onclick="$('#extravar{{$extravar->id}} a').removeClass('active');$(this).find('a').addClass('active')">
										<input type="radio" name="extravar{{$extravar->id}}" value="{{$content}}" class="blind" @if(isset($user)&&$user->extravar($extravar->id)==$content) checked @endif>
										<a href="#" onclick="$(this).parent().click();return false" @if(isset($user)&&$user->extravar($extravar->id)==$content) class="active" @endif >✔︎</a>
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
										<input type="checkbox" name="extravar{{$extravar->id}}[]" value="{{$content}}" class="blind" @if(isset($user)&&in_array($content,$user->extravar($extravar->id))) checked @endif >
										<a href="#" onclick="return false" @if(isset($user)&&in_array($content,$user->extravar($extravar->id))) class="active" @endif >✔︎</a>
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
									@if(isset($user))
										@if(count($user->extravar($extravar->id)))
											@foreach($user->extravar($extravar->id) as $content)
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
							@if(isset($user)&&$user->extravar($extravar->id))
								<div class="thumbnail"><img src="{{url($user->extravar($extravar->id))}}" alt=""></div>
							@endif
							<input type="file" name="extravar{{$extravar->id}}" accept="image/*">
							<input type="hidden" name="extravar{{$extravar->id}}_original" value="@if(isset($user)&&$user->extravar($extravar->id)){{$user->extravar($extravar->id)}}@endif">
							<span>@if($extravar->type){{$extravar->name}}@endif</span>
						</label>
						
					@elseif($extravar->type=='file')
						<label class="input_wrap">
							@if(isset($user)&&$user->extravar($extravar->id))
								<div class="thumbnail"><a href="{{url($user->extravar($extravar->id))}}">{{\App\File::where('name',str_replace('/file/','',$user->extravar($extravar->id)))->first()->original}}</a></div>
							@endif
							<input type="file" name="extravar{{$extravar->id}}">
							<input type="hidden" name="extravar{{$extravar->id}}_original" value="@if(isset($user)&&$user->extravar($extravar->id)){{$user->extravar($extravar->id)}}@endif">
							<span>@if($extravar->type){{$extravar->name}}@endif</span>
						</label>
					
					@endif
					
					@if($extravar->description)
						<span class="description">{{$extravar->description}}</span>
					@endif
				@endforeach
			@endif
			
			<label class="input_wrap">
				<textarea name="note">@if(isset($user)){{$user->note}}@endif</textarea>
				<span>비고</span>
			</label>
			
			<div class="btnArea">
				@if(isset($user))
					<button type="button" class="button white" onclick="if(confirm('정말로 삭제하시겠습니까?'))$('#user{{$user->id}}delete').submit();return false"><span>회원 삭제</span></button>
					<a href="{{url('/admin/user')}}{{$_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''}}" class="button gray">돌아가기</a>
					<button type="submit" class="button blue"><span>저장하기</span></button>
				@else
					<button type="submit" class="button blue"><span>회원 추가하기</span></button>
				@endif
			</div>
		</div>
	</form>
	
	@if(isset($user))
		<form id="user{{$user->id}}delete" class="form" method="post" action="{{url('/admin/user/delete')}}{{$_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''}}">
			{!!csrf_field()!!}
			<input type="hidden" name="id" value="{{$user->id}}">
		</form>
	@endif

@endsection