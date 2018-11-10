@extends('admin.layout',['title'=>'&gt; 레이아웃'])

@section('body')
	<h3 class="menu_title">레이아웃 관리</h3>

	@if(count($layouts))
		@if(session('message'))
			<div class="message success">{!!session('message')!!}</div>
		@endif
		
		<div class="menus">
			@foreach($layouts as $layout)
				<div class="menu">
					@if(session('message'.$layout->id))
						<div class="message success">{!!session('message'.$layout->id)!!}</div>
					@endif
					
					<form class="form" method="post" action="{{url('/admin/layout/edit')}}" enctype="multipart/form-data">
						{!!csrf_field()!!}
						<input type="hidden" name="id" value="{{$layout->id}}">
							
						<div class="menu_name">
							<label class="input_wrap" onclick="$('.menu_list').not('#list{{$layout->id}}').slideUp();$('#list{{$layout->id}}').slideDown();">
								<input type="text" name="layout_name" value="{{$layout->name}}">
								<span>이름</span>
							</label>
						</div>
		
						<div class="menu_list" id="list{{$layout->id}}" @if(!session('message'.$layout->id))style="display:none"@endif>
							<ul>
								<li>
									<div class="selects white">
										<span>레이아웃</span>
										
										<label class="select_wrap">
											<a href="#" onclick="$(this).parent().click();return false" class="active">✔︎</a>
											<span>{{$layout->path}}</span>
										</label>
									</div>
								</li>
								<li>
									<div class="selects white" id="layout{{$layout->id}}menu">
										<span>메뉴</span>
										@foreach($menus as $menu)
											<label class="select_wrap" onclick="$('#layout{{$layout->id}}menu a').removeClass('active');$(this).find('a').addClass('active')">
												<input type="radio" name="menu{{$layout->id}}" value="{{$menu->id}}" class="blind" @if($layout->menu==$menu->id) checked @endif>
												<a href="#" onclick="$(this).parent().click();return false" @if($layout->menu==$menu->id) class="active" @endif>✔︎</a>
												<span>{{$menu->name}}</span>
											</label>
										@endforeach
									</div>
								</li>
								@if($layout->configs())
									@foreach($layout->configs() as $config)
										<li>
											@if($config->type=='text')
												<label class="input_wrap white">
													<input type="text" name="config_{{$config->name}}" value="{{$layout->config($config->name)}}">
													<span>{{$config->nickname}}</span>
												</label>
												@if($config->description)
													<span class="description">{{$config->description}}</span>
												@endif
											@elseif($config->type=='textarea')
												<label class="input_wrap white">
													<textarea name="config_{{$config->name}}">{{$layout->config($config->name)}}</textarea>
													<span>{{$config->nickname}}</span>
												</label>
												@if($config->description)
													<span class="description">{{$config->description}}</span>
												@endif
											@elseif($config->type=='image')
												<label class="input_wrap white">
													@if($layout->config($config->name))
														<div class="thumbnail"><img src="{{url($layout->config($config->name))}}" alt=""></div>
													@endif
													<input type="file" name="config_{{$config->name}}" accept="image/*">
													<input type="hidden" name="config_{{$config->name}}_original" value="{{$layout->config($config->name)}}">
													<span>{{$config->nickname}}</span>
												</label>
												@if($config->description)
													<span class="description">{{$config->description}}</span>
												@endif
											@endif
										</li>
									@endforeach
								@endif
							</ul>
							
							<div class="btnArea" style="margin-top:-10px">
								<button type="button" class="button white" onclick="if(confirm('게시판 등에 연결된 레이아웃이 있으면 해당 페이지에서는 에러가 발생할 수 있습니다. 정말로 삭제하시겠습니까?'))$('#layout{{$layout->id}}delete').submit();return false"><span>레이아웃 삭제</span></button>
								<button type="submit" class="button blue"><span>레이아웃 저장하기</span></button>
							</div>
						</div>
					</form>
					<form id="layout{{$layout->id}}delete" class="form" method="post" action="{{url('/admin/layout/delete')}}">
						{!!csrf_field()!!}
						<input type="hidden" name="id" value="{{$layout->id}}">
					</form>
				</div>
			@endforeach
		</div>
		
		<form class="form" method="post" action="{{url('/admin/layout/create')}}">
			<div class="form_wrap">
				<legend>새 레이아웃 만들기</legend>
				{!!csrf_field()!!}
				
				<label class="input_wrap" onclick="$('.menu_list').not('#list0').slideUp();$('#list0').slideDown()">
					<input type="text" name="name" value="">
					<span>이름</span>
				</label>
			
				<div class="menu_list" id="list0" style="display:none">
					<div class="selects" id="layout0path">
						<span>레이아웃</span>
						@foreach($paths as $path)
							<label class="select_wrap" onclick="$('#layout0path a').removeClass('active');$(this).find('a').addClass('active')">
								<input type="radio" name="path0" value="{{$path}}" class="blind">
								<a href="#" onclick="$(this).parent().click();return false">✔︎</a>
								<span>{{$path}}</span>
							</label>
						@endforeach
					</div>
					
					<div class="selects" id="layout0menu" style="margin-top:0">
						<span>메뉴</span>
						@foreach($menus as $menu)
							<label class="select_wrap" onclick="$('#layout0menu a').removeClass('active');$(this).find('a').addClass('active')">
								<input type="radio" name="menu0" value="{{$menu->id}}" class="blind">
								<a href="#" onclick="$(this).parent().click();return false">✔︎</a>
								<span>{{$menu->name}}</span>
							</label>
						@endforeach
					</div>
					
					<div class="btnArea" style="margin-top:-10px">
						<button type="submit" class="button blue"><span>레이아웃 만들기</span></button>
					</div>
				</div>
			</div>
		</form>
		
	@else
		<div class="no_item">
			<form class="form" method="post" action="{{url('/admin/layout/create')}}">
				<div class="form_wrap">
					<legend>첫 번째 레이아웃을 만듭니다.</legend>
					{!!csrf_field()!!}
					
					<label class="input_wrap white">
						<input type="text" name="name" value="메인 레이아웃">
						<span>이름</span>
					</label>
				
					<div class="selects white" id="layout0path">
						<span>레이아웃</span>
						@foreach($paths as $path)
							<label class="select_wrap" onclick="$('#layout0path a').removeClass('active');$(this).find('a').addClass('active')">
								<input type="radio" name="path0" value="{{$path}}" class="blind">
								<a href="#" onclick="$(this).parent().click();return false">✔︎</a>
								<span>{{$path}}</span>
							</label>
						@endforeach
					</div>
			
					<div class="selects white" id="layout0menu">
						<span>메뉴</span>
						@foreach($menus as $menu)
							<label class="select_wrap" onclick="$('#layout0menu a').removeClass('active');$(this).find('a').addClass('active')">
								<input type="radio" name="menu0" value="{{$menu->id}}" class="blind">
								<a href="#" onclick="$(this).parent().click();return false">✔︎</a>
								<span>{{$menu->name}}</span>
							</label>
						@endforeach
					</div>
					
					<div class="btnArea">
						<button type="submit" class="button blue"><span>레이아웃 만들기</span></button>
					</div>
				</div>
			</form>
		</div>
	@endif

@endsection