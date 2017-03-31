@extends('layouts.default')

@section('title')
@parent
:: Statistics
@stop

@section('scripts')
{{ HTML::script('js/jquery.flot.min.js') }}
{{ HTML::script('js/jquery.flot.pie.min.js') }}
{{ HTML::script('js/jquery.flot.axislabels.min.js') }}

<script type='text/javascript'>
$(document).ready(function(){
    $('#select-year').on('change', function(e){
        var select = $(this), form = select.closest('form');
        form.attr('action', '/statistics/' + select.val());
        form.submit();
    });
});

@if (Auth::user()->isAdmin())
function button_change()
{
    var elem = document.getElementById("toggle-ranking");
    if (elem.innerHTML == "Expand") elem.innerHTML = "Collapse";
    else elem.innerHTML = "Expand";
}
@endif

/* flot bar chart tooltip */
var previousPoint = null, previousLabel = null;

$.fn.UseTooltip = function () {
    $(this).bind("plothover", function (event, pos, item) {
        if (item) {
            if ((previousLabel != item.series.label) || (previousPoint != item.dataIndex)) {
                previousPoint = item.dataIndex;
                previousLabel = item.series.label;
                $("#tooltip").remove();

                var x = item.datapoint[0];
                var y = item.datapoint[1];

                var color = item.series.color;

                showTooltip(item.pageX,
                        item.pageY,
                        color,
                        "<strong>" + item.series.label + "</strong><br />" + item.series.xaxis.ticks[x].label + " : <strong>" + y + "</strong>");
            }
        } else {
            $("#tooltip").remove();
            previousPoint = null;
        }
    });
};

function showTooltip(x, y, color, contents) {
    $('<div id="tooltip">' + contents + '</div>').css({
        position: 'absolute',
        display: 'none',
        top: y - 40,
        left: x - 100,
        border: '2px solid ' + color,
        padding: '3px',
        'font-size': '10px',
        'border-radius': '5px',
        'background-color': 'inherit',
        //'font-family': 'Verdana, Arial, Helvetica, Tahoma, sans-serif',
        opacity: 0.9
    }).appendTo("body").fadeIn(200);
}
</script>
@stop

@section('styles')
@parent
  .axisLabels {
    font-size: 15px;
  }
  .xaxisLabel {
    color: #545454;
  }
  .yaxisLabel {
    color: #545454;
  }
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
			'weekend' => 0,
			'rc_sum' => 0,
			'rc_day' => 0,
			'rc_night' => 0
		);
	});
// count the RC shifts as well
$beamtimes->rcshifts->user->workgroup
	->groupBy('name', 'country')
	->each(function($item) use(&$info)
	{
		if (array_key_exists($item[0]->id, $info))
			$info[$item[0]->id]['rc_sum'] = count($item);
		else  // the case if only RC shifts have been taken, no normal shifts
			$info[$item[0]->id] = array(
				'id' => $item[0]->id,
				'sum' => 0,
				'day' => 0,
				'late' => 0,
				'night' => 0,
				'weekend' => 0,
				'rc_sum' => count($item),
				'rc_day' => 0,
				'rc_night' => 0
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
// add the RC shift types, too
$beamtimes->rcshifts->each(function($rcshift) use(&$info)
	{
		// skip RC shifts without a subscribed user
		if (!$rcshift->user->count())
			return;
		if ($rcshift->is_day())
			$info[$rcshift->user->first()->workgroup_id]['rc_day']++;
		else
			$info[$rcshift->user->first()->workgroup_id]['rc_night']++;
	});

?>

      <div class="page-header">
        <h2>Statistics for {{{ empty($range) ? $year : $range }}}</h2>
      </div>

      <h3>General Overview:</h3>
      <p>Total number of registered users: {{{ User::all()->count() }}}<br />
      Contributing users during the selected period: {{{ $beamtimes->shifts->users->unique()->count() }}}</p>

      <p>{{{ $beamtimes->count() }}} beamtimes with {{{ $shifts->count() }}} shifts (plus {{{ $beamtimes->shifts->count() - $shifts->count() }}} maintenance shifts, {{{ $beamtimes->shifts->count() }}} total)<br />
      {{{ $beamtimes->shifts->users->count() }}} total individual shifts taken out of possible {{{ $beamtimes->shifts->sum('n_crew') }}} individual shifts ({{{ round($beamtimes->shifts->users->count()/$beamtimes->shifts->sum('n_crew')*100, 1) }}}%)<br />

      Total beamtime: {{{ $hours }}} hours ({{{ round($hours/24, 1) }}} days)</p>

      <p style="padding-bottom: 20px;">{{{ $beamtimes->rcshifts->user->count() }}} run coordinator shifts taken out of possible {{{ $beamtimes->rcshifts->count() }}} RC shifts ({{{ round($beamtimes->rcshifts->user->count()/$beamtimes->rcshifts->count()*100, 1) }}}%)</p>

      @if (!$beamtimes->shifts->users->count())
      <h3 class="text-info">No shifts taken!</h3>
      @else
      {{-- jQuery needs to be loaded before the other Javascript parts need it --}}
      {{ HTML::script('js/jquery-2.1.1.min.js') }}
<?php
$data = array();
$ticks = array();
$count = 0;
foreach ($info as $group) {
	$workgroup = Workgroup::find($group['id']);
	array_push($data, [$count, round($group['sum']/$workgroup->members->count(), 2)]);
	array_push($ticks, [$count, $workgroup->short]);
	$count++;
}
$shifts_count = array();
$shifts_user = array();
$beamtimes->shifts->users->groupBy('id')->each(function($user_shifts) use(&$shifts_count, &$shifts_user)
	{
		$n = count($user_shifts);
		array_push($shifts_count, $n);
		array_push($shifts_user, [$user_shifts[0]->username, $user_shifts[0]->get_full_name(), $n]);
	});
// sort array after the number of shifts (3rd entry) in descending order, maintain key association with uasort
uasort($shifts_user, function($a, $b)
	{
		return $a[2] < $b[2];
	});
$users_no_shifts = User::all()->diff($beamtimes->shifts->users->unique());
$shift_data = array_fill(0, max($shifts_count)+1, 0);
foreach ($shifts_count as $count)
	$shift_data[$count]++;
$count = 0;
$shift_ticks = array();
foreach ($shift_data as $val) {
	$shift_data[$count] = [$count, $val ? $val : null];
	array_push($shift_ticks, [$count, strval($count)]);
	$count++;
}
$no_shifts = $users_no_shifts->count();
if ($no_shifts)
	$shift_data[0] = [0, $no_shifts];
?>
      <p><h4>&emsp;Shifts/Head Ratio for contributing workgroups</h4>
      <script type="text/javascript">
        $(document).ready(function(){
        var body = document.body;
        /*var ff = (body.currentStyle||
                (window.getComputedStyle&&getComputedStyle(body,null))
                ||body.style).fontFamily;*/
        var data = {{ json_encode($data) }};
        var ticks = {{ json_encode($ticks) }};
        var dataset = [
            { label: "shifts/head ratio", data: data, xaxis: 1, yaxis: 1, color: "#5482FF" }
        ];

        var options = {
            series: {
                bars: {
                    show: true
                }
            },
            bars: {
                align: "center",
                barWidth: 0.5
            },
            xaxis: {
                axisLabel: "Workgroups",
                axisLabelUseCanvas: false,
                //axisLabelFontSizePixels: 14,
                //axisLabelFontFamily: ff,
                axisLabelPadding: 10,
                ticks: ticks
            },
            yaxis: {
                axisLabel: "Shifts/Head ratio",
                axisLabelUseCanvas: false,
                //axisLabelFontSizePixels: 14,
                //axisLabelFontFamily: ff,
                axisLabelPadding: 3,
                /*tickFormatter: function (v, axis) {
                    return v + "°C";
                }*/
            },
            legend: {
                noColumns: 0,
                labelBoxBorderColor: "#000000",
                position: "nw"
            },
            grid: {
                hoverable: true,
                borderWidth: 2
            }
        };

        $.plot($("#flot-shift-head-ratio"), dataset, options);
        $("#flot-shift-head-ratio").UseTooltip();
        });
      </script>
      <div id="flot-shift-head-ratio" style="width: 500px; height: 250px; margin: 20px 0 2em 1em;"></div></p>
      <p><h4>&emsp;Shift Distribution for all users</h4>
      <script type="text/javascript">
        $(document).ready(function(){
        var data = {{ json_encode($shift_data) }};
        var ticks = {{ json_encode($shift_ticks) }};
        var dataset = [
            { label: "taken shifts per user", data: data, xaxis: 1, yaxis: 1, color: "#5482FF" }
        ];

        var options = {
            series: {
                bars: {
                    show: true
                }
            },
            bars: {
                align: "center",
                barWidth: 0.5
            },
            xaxis: {
                axisLabel: "#Shifts",
                axisLabelUseCanvas: false,
                axisLabelPadding: 10,
                ticks: ticks
            },
            yaxis: {
                axisLabel: "#Users",
                axisLabelUseCanvas: false,
                axisLabelPadding: 3
            },
            legend: {
                noColumns: 0,
                labelBoxBorderColor: "#000000",
                position: "nw"
            },
            grid: {
                hoverable: true,
                borderWidth: 2
            }
        };

        $.plot($("#flot-shift-hist"), dataset, options);
        $("#flot-shift-hist").UseTooltip();
        });
      </script>
      <div id="flot-shift-hist" style="width: 500px; height: 250px; margin: 20px 0 2em 1em;"></div>
      &emsp;{{{ $no_shifts }}} registered users haven't taken any shifts in the selected period.</p>
      <h3>Contributing Workgroups:</h3>
<?php

foreach ($info as $group) {
	$workgroup = Workgroup::find($group['id']);
	echo '<p><h4>' . $workgroup->name . ' (' . $workgroup->country . ")</h4>\n";
	if ($group['rc_sum'])
		echo '&emsp;&emsp;contributed with ' . $group['rc_sum'] . ' RC shifts (day: '
			. $group['rc_day'] . ', night: ' . $group['rc_night'] . ")<br />\n";
	else
		echo "&emsp;&emsp;didn't contribute with run coordinator shifts<br />\n";
	if (!$group['sum']) {
		echo "&emsp;&emsp;and hasn't taken any shifts<br />\n";
		continue;
	}
	echo '&emsp;&emsp;and has taken a total of ' . $group['sum'] . " shifts<br />\n";
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

      <div class="page-header" style="padding-top: 20px;">
        <h3>Regional Statistics</h3>
      </div>

<?php
// now create the same shift statistics for the different localities, grouped by region
$region = array();
// initialise array with every region with contributing workgroups and the total shift amount
$beamtimes->shifts->users->workgroup
	->groupBy('region')
	->each(function($item) use(&$region)
	{
		$region[$item[0]->region] = array(
			'region' => $item[0]->region,
			'sum' => count($item),
			'day' => 0,
			'late' => 0,
			'night' => 0,
			'weekend' => 0,
			'rc_sum' => 0,
			'rc_day' => 0,
			'rc_night' => 0
		);
	});
// count the RC shifts as well
$beamtimes->rcshifts->user->workgroup
	->groupBy('region')
	->each(function($item) use(&$region)
	{
		if (array_key_exists($item[0]->region, $region))
			$region[$item[0]->region]['rc_sum'] = count($item);
		else  // the case if only RC shifts have been taken, no normal shifts
			$region[$item[0]->region] = array(
				'region' => $item[0]->region,
				'sum' => 0,
				'day' => 0,
				'late' => 0,
				'night' => 0,
				'weekend' => 0,
				'rc_sum' => count($item),
				'rc_day' => 0,
				'rc_night' => 0
			);
	});
// sort the regional workgroup contributions according to the sum of taken shifts; use uasort to maintain key association
uasort($region, function($a, $b)
	{
		return $b['sum'] - $a['sum'];
	});
// add the specific shift type information to the array
$beamtimes->shifts->each(function($shift) use(&$region)
	{
		if ($shift->is_day())
			$shift->users->workgroup->each(function($workgroup) use(&$region)
			{
				$region[$workgroup->region]['day']++;
			});
		elseif ($shift->is_late())
			$shift->users->workgroup->each(function($workgroup) use(&$region)
			{
				$region[$workgroup->region]['late']++;
			});
		else
			$shift->users->workgroup->each(function($workgroup) use(&$region)
			{
				$region[$workgroup->region]['night']++;
			});
		if ($shift->is_weekend())
			$shift->users->workgroup->each(function($workgroup) use(&$region)
			{
				$region[$workgroup->region]['weekend']++;
			});
	});
// add the RC shift types, too
$beamtimes->rcshifts->each(function($rcshift) use(&$region)
	{
		// skip RC shifts without a subscribed user
		if (!$rcshift->user->count())
			return;
		if ($rcshift->is_day())
			$region[$rcshift->user->first()->workgroup->region]['rc_day']++;
		else
			$region[$rcshift->user->first()->workgroup->region]['rc_night']++;
	});

foreach ($region as $group) {
	echo '<p><h4>Workgroups ' . Workgroup::region_string($group['region']) . "</h4>\n";
	if ($group['rc_sum'])
		echo '&emsp;&emsp;contributed with ' . $group['rc_sum'] . ' RC shifts (day: '
			. $group['rc_day'] . ', night: ' . $group['rc_night'] . ")<br />\n";
	else
		echo "&emsp;&emsp;haven't contributed with run coordinator shifts<br />\n";
	if (!$group['sum']) {
		echo "&emsp;&emsp;and haven't taken any shifts<br />\n";
		continue;
	}
	echo '&emsp;&emsp;and have taken a total of ' . $group['sum'] . " shifts<br />\n";
	echo '&emsp;&emsp;of which ' . $group['weekend'] . " were during the weekend<br />\n";
	$members = Workgroup::whereregion($group['region'])->get()->members->count();
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

    $.plot($("#flotcontainer'.$group['region'].'"), data, options);
});
</script>';
	echo '<div id="flotcontainer'.$group['region'].'" style="width: 400px; height: 250px; margin-bottom: 2em;"></div></p>';
}
?>

@if (Auth::user()->isAdmin())
      <div class="page-header" style="padding-top: 20px;">
        <h3>Shift Ranking</h3>
      </div>
      <div class="col-lg-5">
        <div id="ranking" class="collapse">
          <div class="list-group">
<?php
foreach($shifts_user as $user) {
	echo '<a href="/users/' . $user[0] . "\" class=\"list-group-item\">\n"
		. '<span class="badge">' . $user[2] . "</span>\n"
		. $user[1] . "</a>\n";
}
foreach($users_no_shifts as $user) {
	echo '<a href="/users/' . $user->username . "\" class=\"list-group-item\">\n
		<span class=\"badge\">0</span>"
		. $user->get_full_name() . "</a>\n";
}
?>
		  </div>
        </div>
        <button class="btn btn-primary" id="toggle-ranking" data-toggle="collapse" data-target="#ranking" onclick="button_change()">Expand</button>
      </div>
@endif
      @endif  {{-- Workgroups --> shifts taken? --}}
    @endif  {{-- Beamtimes found? --}}
  </div>
</div>
@stop
