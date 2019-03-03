@extends($layout?'layout.'.$layout->path.'.layout':'common',['title'=>$board->name?'&gt; '.(\App\Encryption::checkEncrypted($board->name)?\App\Encryption::decrypt($board->name):$board->name):''])

@section($board->layout?'body':'container')
	<h3 class="table_caption">{{$board->name}}</h3>
	
	<div class="no_item">
		작성을 완료했습니다.<br>
		감사합니다.
	</div>
@endsection