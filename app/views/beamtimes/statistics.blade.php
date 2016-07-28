@extends('layouts.default')

@section('title')
@parent
:: Statistics
@stop

@section('scripts')
{{ HTML::script('js/jquery.flot.min.js') }}
{{ HTML::script('js/jquery.flot.pie.min.js') }}
<script type='text/javascript'>
$(document).ready(function(){
    $('#select-year').on('change', function(e){
        var select = $(this), form = select.closest('form');
        form.attr('action', '/statistics/' + select.val());
        form.submit();
    });
});
</script>
@stop

@section('content')
<?php
$current_year = date('Y');
if (empty($year))
	$year = $current_year;
?>
<div class="row">
  <div class="col-lg-5 col-lg-offset-1">
    <div class="panel panel-default">
      <div class="panel-body">
        {{ link_to('/statistics/all', 'Total beamtimes: ' . Beamtime::all()->count(), ['style' => 'color: inherit;']) }}
      </div>
    </div>
    <div class="panel panel-primary">
      <div class="panel-heading">
        <h3 class="panel-title">Select a year</h3>
      </div>
      <div class="panel-body">
        {{ Form::open(['route' => 'statistics', 'class' => 'form-horizontal', 'role' => 'form']) }}
          <div style="margin-left: 10px;">
            {{ Form::selectYear('year', Session::get('first'), Session::get('last'), $year, array('id' => 'select-year')) }}
          </div>
        {{ Form::close() }}
      </div>
    </div>
    <div class="panel panel-primary">
      <div class="panel-heading">
        <h3 class="panel-title">Select a year range</h3>
      </div>
      <div class="panel-body">
        {{ Form::open(['route' => array('statistics', 'range'), 'class' => 'form-inline', 'role' => 'form']) }}
          <div class="form-group">
            {{ Form::label('year1', 'From: ', array('class' => 'col-lg-4 control-label')) }}
            <div class="col-lg-2">
              {{ Form::selectYear('year1', Session::get('first'), Session::get('last'), $year, array('id' => 'select-year')) }}
            </div>
          </div>
          <div class="form-group">
            {{ Form::label('year2', 'To: ', array('class' => 'col-lg-2 control-label')) }}
            <div class="col-lg-2">
              {{ Form::selectYear('year2', Session::get('first'), Session::get('last'), $year, array('id' => 'select-year')) }}
            </div>
          </div>
          <div class="form-group">
            <div class="col-lg-2">
              {{ Form::submit('Submit', array('class' => 'btn btn-primary btn-sm')) }}
            </div>
          </div>
        {{ Form::close() }}
      </div>
    </div>
  </div>
</div>
<div class="row">
  <div class="col-lg-10 col-lg-offset-1">
    @if (!$beamtimes->count())
    <h3 class="text-info">No beamtimes found for {{{ empty($range) ? $year : $range }}}!</h3>
    @else

<?php
$hours = $beamtimes->sum(function($beamtime)
	{
		return $beamtime->shifts->sum('duration');
	});

// remove maintenance shifts from the Collection
$shifts = $beamtimes->shifts->reject(function($shift)
	{
		return $shift->maintenance;
	});

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
			'night' => 0,
			'weekend' => 0
		);
	});
// sort the workgroup order according to the sum of taken shifts; use uasort to maintain key association
uasort($info, function($a, $b)
	{
		return $b['sum'] - $a['sum'];
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
		if ($shift->is_weekend())
			$shift->users->workgroup->each(function($workgroup) use(&$info)
			{
				$info[$workgroup->id]['weekend']++;
			});
	});

?>

      <div class="page-header">
        <h2>Statistics for {{{ empty($range) ? $year : $range }}}</h2>
      </div>

      {{{ $beamtimes->count() }}} beamtimes with {{{ $shifts->count() }}} shifts (plus {{{ $beamtimes->shifts->count() - $shifts->count() }}} maintenance shifts, {{{ $beamtimes->shifts->count() }}} total)<br />
      {{{ $beamtimes->shifts->users->count() }}} total individual shifts taken of possible {{{ $beamtimes->shifts->sum('n_crew') }}} individual shifts<br />
      {{-- dd( $beamtimes->shifts->users->workgroup->groupBy('name', 'country')->orderBy('country')->orderBy('name') ) --}}

      Total beamtime: {{{ $hours }}} hours ({{{ round($hours/24, 1) }}} days)

      @if (!$beamtimes->shifts->users->count())
      <h3 class="text-info">No shifts taken!</h3>
      @else
      <h3>Contributing Workgroups:</h3>
      {{-- jQuery needs to be loaded before the other Javascript parts need it --}}
      {{ HTML::script('js/jquery-2.1.1.min.js') }}
<?php
foreach ($info as $group) {
	$workgroup = Workgroup::find($group['id']);
	echo '<p><h4>' . $workgroup->name . ' (' . $workgroup->country . ")</h4>\n";
	echo '&emsp;&emsp;has taken a total of ' . $group['sum'] . " shifts<br />\n";
	echo '&emsp;&emsp;of which ' . $group['weekend'] . " were during the weekend<br />\n";
	$members = $workgroup->members->count();
	echo '&emsp;&emsp;shifts/head ratio is ' . round($group['sum']/$members, 2) . "<br />\n";
	$s = '';
	if ($members > 1)
		$s = 's';
	echo '&emsp;&emsp;' . $members . ' registered member' . $s . "<br />\n";
	//echo '&emsp;&emsp;taken shift types: day: ' . round($group['day']/$group['sum']*100, 2) . '%, late: ' . round($group['late']/$group['sum']*100, 2) . '%, night: ' . round($group['night']/$group['sum']*100, 2) . "%<p>\n";
	echo "&emsp;&emsp;taken shift types:\n";
	echo '<script type="text/javascript">
$(document).ready(function(){
    var data = [
        {label: "day", data: ' . round($group['day']/$group['sum']*100, 2) . ', color: "#8BC34A"},
        {label: "late", data: ' . round($group['late']/$group['sum']*100, 2) . ', color: "#FFA000"},
        {label: "night", data: ' . round($group['night']/$group['sum']*100, 2) . ', color: "#455A64"}
    ];

    var options = {
        series: {
            pie: {
                show: true,
                radius: 1,
                stroke: {
                    width: 0
                },
                label: {
                    show: true,
                    radius: 2/3,
                    // Added custom formatter here...
                    //formatter: function(label, point){
                    //    return(point.percent.toFixed(2) + \'%\');
                    //},
                    formatter: function(label, series) {
                        return \'<div style="font-size: 14px; font-weight: bold; text-align: center; padding: 2px; color: white;">\'+label+\'<br/>\'+Math.round(series.percent)+\'%</div>\';
                    },
                    threshold: 0.1
                }
            }
        },
        legend: {
            show: false
        },
        grid: {
            hoverable: true,
            clickable: true
        }
    };

    $.plot($("#flotcontainer'.$group['id'].'"), data, options);
});
</script>';
	echo '<div id="flotcontainer'.$group['id'].'" style="width: 400px; height: 250px; margin-bottom: 2em;"></div></p>';
}

?>
      @endif  {{-- Workgroups --> shifts taken? --}}
    @endif  {{-- Beamtimes found? --}}
  </div>
</div>
@stop
