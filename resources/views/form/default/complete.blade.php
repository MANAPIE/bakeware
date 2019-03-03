@extends($layout?'layout.'.$layout->path.'.layout':'common',['title'=>$form->name?'&gt; '.(\App\Encryption::checkEncrypted($form->name)?\App\Encryption::decrypt($form->name):$form->name):''])

@section($form->layout?'body':'container')
	<h3 class="table_caption">{{$form->name}}</h3>
	
	<div class="no_item">
		답변을 완료했습니다.<br>
		감사합니다.
	</div>
@endsection