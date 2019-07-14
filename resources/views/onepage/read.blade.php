@extends($layout?'layout.'.$layout->path.'.layout':'common',['title'=>$page->name?'&gt; '.(\App\Encryption::checkEncrypted($page->name)?\App\Encryption::decrypt($page->name):$page->name):''])

@section('head')
	@parent
	<link rel="stylesheet" href="{{url('/style/jquery.fullpage.min.css.bakeware')}}" />
	<style>
		#fullpage .section{background-size:cover;background-position:center center;padding:0 20px}
		/*
		#fullpage .section_wrap{padding-top:40px}
		@media only screen and (max-width:800px){
			#fullpage .section_wrap{padding-top:10px}
		}
		*/
	</style>
	<script src="{{url('/script/jquery.fullpage.min.js.bakeware')}}"></script>
	<script>
	$(function(){
		$('#MANAPIE').addClass('onepage');
		$('#fullpage').fullpage({
			onLeave:function(index,nextIndex,direction){
				if(index==1&&direction=='down'){
					if(typeof scrollDown=='function')
						scrollDown();
				}else if(index==2&&direction=='up'){
					if(typeof scrollUp=='function')
						scrollUp();
				}
			}
		});
	});
	</script>
@endsection

@section($page->layout?'body':'container')
	<div id="fullpage">
		<?php $i=0; ?>
		@foreach($page->pages() as $p)
			<div class="section" id="page{{$i}}"@if($p['background']) style="background-image:url('{{$p['background']}}')"@endif>
				<div class="section_wrap">
				@if($p['page']->type=='inner')
					{!!$p['page']->content()!!}
				@elseif($p['page']->type=='outer')
					@include('page.outer.'.$p['page']->content())
				@endif
			</div></div>
		@endforeach
	</div>
@endsection