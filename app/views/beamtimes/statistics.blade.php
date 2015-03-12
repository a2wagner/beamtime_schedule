@extends('layouts.default')

@section('title')
@parent
:: Statistics
@stop

@section('content')
<div class="col-lg-10 col-lg-offset-1">
Total beamtimes: {{{ Beamtime::all()->count() }}} <br />

<?php
$year = date('Y');
//$year = array(2014, 2015, 2016);

$beamtimes = Beamtime::all()
	->filter(function($beamtime) use($year)
	{
		return $beamtime->is_year($year);
		//return $beamtime->is_in_years($year);
		//return $beamtime->is_in_range(2016, 2014);
	});

if (!$beamtimes->count())
	echo 'No beamtimes found for ' . $year . "!\n";
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
$beamtimes->shifts->users->workgroup
	->groupBy('name', 'country')
	->orderBy('country')
	->orderBy('name')
	->each(function($item) use(&$info)
	{
		$info[] = array('id' => $item[0]->id, 'sum' => count($item));
	});
//dd($info);

?>

{{ Form::selectYear('year', 2013, $year, $year, []) }}

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
	echo $workgroup->name . ' (' . $workgroup->country . ') has taken a total of ' . $group['sum'] . ' shifts (shifts/head ratio is ' . $group['sum']/$workgroup->members->count() . ")<br />\n";
}

}
?>

</div>
@stop
