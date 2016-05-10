@extends('layouts.default')

@section('title')
Shifts of {{ $user->get_full_name() }}
@stop

@section('content')
<div class="col-lg-6 col-lg-offset-2">
    @if ($user->count())
    @if ($user->shifts->count())
    <div class="page-header">
        <h2>Your taken shifts</h2>
    </div>
    <div style="margin-left: 20px;">
      <p>
        Total shifts taken: {{ $user->shifts->count() }}<br />
        Time on shifts: {{ $user->shifts->sum('duration') }} hours
      </p>
      <p>Download your shifts as calendar file:&ensp;{{ link_to("/users/$user->username/ics", 'iCal', ['class' => 'btn btn-success btn-xs']) }}</p>
    </div>
    <div>
      <table class="table table-striped table-hover">
<?php $shifts->groupBy('beamtime_id')->each(function($item){
	$beamtime = $item[0]->beamtime;
	echo "<thead>\n";
	echo "  <tr>\n";
	echo "    <td>\n";
	echo '      <h3>' . link_to("/beamtimes/$beamtime->id", $beamtime->name, ['style' => 'color: inherit; text-decoration: none;']) . "</h3>\n";
	echo "    </td>\n";
	echo "  </tr>\n";
	echo "</thead>\n";
	echo "<tbody>\n";
	foreach($item as $shift) {
		echo "  <tr>\n";
		echo "    <td>\n";
		echo '      ' . $shift->start . ' (' . $shift->duration . " hours)<br />\n";
		echo "    </td>\n";
		echo "  </tr>\n";
	}
	echo "</tbody>\n";
}); ?>
      </table>
    </div>
    @else
        <h3 class="text-info">User {{ $user->username }} has not taken any shifts yet!</h3>
    @endif
    @else
        <h3 class="text-info">User {{ $user->username }} not found!</h3>
    @endif
</div>
@stop

