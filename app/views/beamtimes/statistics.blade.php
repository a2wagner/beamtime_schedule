@extends('layouts.default')

@section('title')
@parent
:: Statistics
@stop

@section('content')
<div class="col-lg-10 col-lg-offset-1">
Total beamtimes: {{{ Beamtime::all()->count() }}} <br />
<?php
$year = 2015;
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

#$beamtimes->each(function($beamtime) use(&$shifts)
#	{
#		$shifts->merge($beamtime->shifts);
#	});

 // --> $results = $articles->groupBy('category');

?>

<div class="page-header">
    <h2>Statistics for {{{ $year }}}</h2>
</div>

{{{ $beamtimes->count() }}} beamtimes with {{{ $beamtimes->shifts->count() }}} shifts<br />
Total beamtime: {{{ $hours }}} hours ({{{ round($hours/24, 1) }}} days)<br />

<?php
}
?>

</div>
@stop
