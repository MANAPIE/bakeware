@extends($layout?'layout.'.$layout->path.'.layout':'common',['title'=>$page->name()?'&gt; '.(\App\Encryption::checkEncrypted($page->name())?\App\Encryption::decrypt($page->name()):$page->name()):''])

@section($page->layout()?'body':'container')
	@if($page->type=='inner')
		<div class="real_content">
			{!!$page->content()!!}
		</div>
	@elseif($page->type=='outer')
		@include('page.outer.'.$page->content())
	@endif
@endsection