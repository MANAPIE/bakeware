@extends($layout?'layout.'.$layout->path.'.layout':'common',['title'=>$gallery->name?'&gt; '.(\App\Encryption::checkEncrypted($gallery->name)?\App\Encryption::decrypt($gallery->name):$gallery->name):''])

@section($gallery->layout?'body':'container')
	<h3 class="table_caption">{{$gallery->name}}</h3>
	
	<div class="no_item">
		작성을 완료했습니다.<br>
		감사합니다.
	</div>
@endsection