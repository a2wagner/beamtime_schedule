@extends('layouts.default')

@section('title')
@parent
:: Statistics
@stop

@section('content')
<div class="col-lg-10 col-lg-offset-1">
Total beamtimes: {{{ Beamtime::all()->count() }}} <br />
<?php
//use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Collection;

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

$shifts = new Collection();
$beamtimes->each(function($beamtime) use(&$shifts)
	{
		$shifts->merge($beamtime->shifts);
	});

echo '<h2>Statistics for ' . $year . "</h2>\n";
echo $beamtimes->count() . ' beamtimes with ' . $beamtimes->sum(function($beamtime){ return $beamtime->shifts->count(); }) . " shifts<br />\n";
echo 'Total beamtime: '. $hours . ' hours (' . round($hours/24, 1) . " days)<br />\n";


}

 // --> $results = $articles->groupBy('category');


//DB::table('name')->pluck('column')
//Beamtime::where(DB::raw('YEAR(created_at)'), '=', date('n') )->get()
?>
</div>
