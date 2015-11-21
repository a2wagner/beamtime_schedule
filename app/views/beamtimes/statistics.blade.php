@extends('layouts.default')

@section('title')
@parent
:: Statistics
@stop

@section('scripts')
<script type='text/javascript'>
$(document).ready(function(){
    $('#select-year').on('change', function(e){
        console.log('onchange select year detected');
        var select = $(this), form = select.closest('form');
        form.attr('action', '/statistics/' + select.val());
        //form.attr('method', 'get');
        form.submit();
    });
    // hide submit button
    //$(this).find('input[type=submit]').hide();
});
</script>
@stop

@section('content')
<?php
if (!$year)
	$year = date('Y');
$current_year = date('Y');
?>
<div class="row">
  <div class="col-lg-4 col-lg-offset-1">
    <div class="panel panel-default">
      <div class="panel-body">
        Total beamtimes: {{{ Beamtime::all()->count() }}} <br />
      </div>
    </div>
    <div class="panel panel-primary">
      <div class="panel-heading">
        <h4 class="panel-title">Please select a year</h3>
      </div>
      <div class="panel-body">
{{ Form::open(['route' => 'statistics', 'method' => 'get', 'class' => 'form-horizontal', 'role' => 'form']) }}
{{ Form::selectYear('year', 2013, $current_year, $year, array('id' => 'select-year')) }}
{{ Form::close() }}
      </div>
    </div>
  </div>
</div>
<div class="row">
  <div class="col-lg-10 col-lg-offset-1">

<?php
$beamtimes = Beamtime::all()
	->filter(function($beamtime) use($year)
	{
		return $beamtime->is_year($year);
		//return $beamtime->is_in_years($year);
		//return $beamtime->is_in_range(2016, 2014);
	});

if (!$beamtimes->count())
	echo '<h3 class="text-info">No beamtimes found for ' . $year . "!</h3>\n";
else {


$hours = $beamtimes->sum(function($beamtime)
	{
		return $beamtime->shifts->sum('duration');
	});

// remove maintenance shifts from the Collection
$shifts = $beamtimes->shifts->reject(function($shift)
	{
		return $shift->maintenance;
	});

 // --> $results = $articles->groupBy('category');

$info = array();
// initialise array with every contributing workgroup and the total shift amount
$beamtimes->shifts->users->workgroup
	->groupBy('name', 'country')
	->orderBy('country')
	->orderBy('name')
	->each(function($item) use(&$info)
	{
		$info[$item[0]->id] = array(
			'id' => $item[0]->id,
			'sum' => count($item),
			'day' => 0,
			'late' => 0,
			'night' => 0
		);
	});
// add the specific shift type information to the array
$beamtimes->shifts->each(function($shift) use(&$info)
	{
		if ($shift->is_day())
			$shift->users->workgroup->each(function($workgroup) use(&$info)
			{
				$info[$workgroup->id]['day']++;
			});
		elseif ($shift->is_late())
			$shift->users->workgroup->each(function($workgroup) use(&$info)
			{
				$info[$workgroup->id]['late']++;
			});
		else
			$shift->users->workgroup->each(function($workgroup) use(&$info)
			{
				$info[$workgroup->id]['night']++;
			});
	});
//dd($info);

?>

<div class="page-header">
    <h2>Statistics for {{{ $year }}}</h2>
</div>

{{{ $beamtimes->count() }}} beamtimes with {{{ $shifts->count() }}} shifts (plus {{{ $beamtimes->shifts->count() - $shifts->count() }}} maintenance shifts, {{{ $beamtimes->shifts->count() }}} total)<br />
{{{ $beamtimes->shifts->users->count() }}} total individual shifts taken of possible {{{ $beamtimes->shifts->sum('n_crew') }}} individual shifts<br />
{{-- dd( $beamtimes->shifts->users->workgroup->groupBy('name', 'country')->orderBy('country')->orderBy('name') ) --}}



   {{-- oben ist einfacher, remove (nur da für später, falls ich was ähnliches brauche) --}}
   <!--{{{ $beamtimes->count() }}} beamtimes with {{{ $beamtimes->sum(function($beamtime){ return $beamtime->shifts->count(); }) }}} shifts<br />-->
Total beamtime: {{{ $hours }}} hours ({{{ round($hours/24, 1) }}} days)

<h3>Contributing Workgroups:</h3>

<?php
foreach ($info as $group) {
	$workgroup = Workgroup::find($group['id']);
	echo '<p>' . $workgroup->name . ' (' . $workgroup->country . ') has taken a total of ' . $group['sum'] . " shifts<br />\n";
	echo '&emsp;&emsp;shifts/head ratio is ' . $group['sum']/$workgroup->members->count() . "<br />\n";
	echo '&emsp;&emsp;taken shift types: day: ' . round($group['day']/$group['sum']*100, 2) . '%, late: ' . round($group['late']/$group['sum']*100, 2) . '%, night: ' . round($group['night']/$group['sum']*100, 2) . "%<p>\n";
}

}
?>

  </div>
</div>
@stop
