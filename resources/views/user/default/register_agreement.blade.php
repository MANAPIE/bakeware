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
						
			<div class="selects">
				<span>이용 약관</span>
				<div class="text">{!!App\UserSetting::find('term_service')->content!!}</div>
				
				<label class="select_wrap" onclick="$(this).find('input').each(function(){$(this).prop('checked',!$(this).prop('checked'));});$(this).find('a').toggleClass('active');return false">
					<input type="checkbox" name="agree_term" value="Y" class="blind">
					<a href="#" onclick="return false">✔︎</a>
					<span>이용 약관을 확인했으며 이에 동의합니다.</span>
				</label>
			</div>
			<span class="description">이용 약관에 동의하지 않으면 서비스를 이용할 수 없습니다.</span>
			
			<div class="selects">
				<span>개인정보<br>처리방침</span>
				<div class="text">{!!App\UserSetting::find('term_privacy')->content!!}</div>
				
				<label class="select_wrap" onclick="$(this).find('input').each(function(){$(this).prop('checked',!$(this).prop('checked'));});$(this).find('a').toggleClass('active');return false">
					<input type="checkbox" name="agree_privacy" value="Y" class="blind">
					<a href="#" onclick="return false">✔︎</a>
					<span>개인정보처리방침을 확인했으며 이에 동의합니다.</span>
				</label>
			</div>
			<span class="description">개인정보처리방침에 동의하지 않으면 서비스를 이용할 수 없습니다.</span>
	
			<div class="btnArea" style="margin-top:-10px">
				<a href="{{url('/register/join')}}" class="button black" onclick="if(!$('input[name=agree_term]').prop('checked')){alert('이용 약관에 동의하지 않으면 서비스를 이용할 수 없습니다.');return false;}else if(!$('input[name=agree_privacy]').prop('checked')){alert('개인정보처리방침에 동의하지 않으면 서비스를 이용할 수 없습니다.');return false;}">다음 &gt;</a>
			</div>
		
		</div>
	</form>
	
	
@endsection