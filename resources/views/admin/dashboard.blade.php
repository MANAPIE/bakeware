@extends('admin.layout')

@section('head')
	@parent
	<script src="{{url('/script/isotope.pkgd.min.js')}}"></script>
	<script>
	$(function(){
		$('.cards').isotope({
			itemSelector:'.card',
			masonry:{
				horizontalOrder:false
			},
			transitionDuration:0
		});
	});
	</script>
@endsection

@section('body')
	<div class="cards">
	@foreach($cards as $module_group)
		<div class="gap"></div>
		@foreach($module_group as $module)
			@foreach($module as $card)
				<div class="card"><div class="card_wrap">
					{!!$card!!}
				</div></div>
			@endforeach
		@endforeach
	@endforeach
	</div>
@endsection