@extends($layout?'layout.'.$layout->path.'.layout':'common',['title'=>'회원 가입'])

@section($layout?'body':'container')
	<h3 class="table_caption">회원 가입</h3>
	
	<div class="no_item">
		{!!session('message')!!}
		<div class="btnArea" style="text-align:center;@if(session('message'))padding-top:10px;@endif">
			<a href="{{'/'}}" class="button blue"><span>처음으로 돌아가기</span></a>
		</div>
	</div>
@endsection