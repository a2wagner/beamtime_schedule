@extends('layouts.default')

@section('title')
@parent
:: Home
@stop

@section('scripts')
{{ HTML::script('js/laravel.js') }}
@stop

@section('content')
@if (!Auth::check())
@section('css')
@parent
{{ HTML::style('css/xmas.css') }}
@stop
<div class="xmasTree"></div>

<div class="col-lg-8 col-lg-offset-2">
    <div class="page-header">
        <h2>Welcome to the A2 Beamtime Management</h2>
    </div>
    <p>
        <h3>
          <p>Do you already have an account?</p>
          <p>You can use your KPH credentials.</p>
        </h3>
        {{ HTML::link('login', 'Login', ['class' => 'btn btn-primary']) }}
    </p>
    <p>
        <h3>Need a new account?</h3>
        {{ link_to_route('users.create', 'Register', null, ['class' => 'btn btn-primary']) }}
    </p>
</div>
@else
<div class="col-lg-10 col-lg-offset-1">
    <div class="page-header">
        <h2>Christmas Party Shift Schedules</h2>
    </div>
    <?php
    	$beamtimes = Beamtime::all();
		// add the start of the beamtime to every entry of the Collection of Beamtimes if they contain shifts
    	foreach ($beamtimes as $beamtime)
			if (!is_null($beamtime->shifts->first()))
				$beamtime->start = $beamtime->start_string();
			else
				$beamtime->start = '9';  // just set a high number that beamtimes with no shifts are shown at the top
    	$beamtimes = $beamtimes->sortByDesc('start')->take(5);
    ?>
    @if ($beamtimes->count())
    <div class="table-responsive">
    <table class="table table-striped table-hover">
      <thead>
        <tr>
          <th>Beamtime Name</th>
          <th>Start</th>
          <th>#Shifts</th>
          <th>Status</th>
          @if (Auth::user()->isRunCoordinator())
          <th>Action</th>
          @endif
        </tr>
      </thead>
      <tbody>
        @foreach ($beamtimes as $beamtime)
        <tr>
          {{-- Check if the beamtime contain shifts to avoid errors --}}
          @if (is_null($beamtime->shifts->first()))
          @if (Auth::user()->isAdmin())
          <td colspan="4"><h4 class="text-danger">Beamtime contains no shifts!</h4></td>
          <td><a href="/beamtimes/{{{$beamtime->id}}}" data-method="delete" data-confirm="Are you sure to delete this beamtime?" class="btn btn-danger btn-sm"><span class="fa fa-times"></span>Delete</a></td>
          @endif
          @else
          <td style="vertical-align: middle;">{{ link_to("/beamtimes/{$beamtime->id}", $beamtime->name) }}</td>
          <td style="vertical-align: middle;">{{ $beamtime->start_string() }}</td>
          <td style="vertical-align: middle;">{{ $beamtime->shifts()->count() }}</td>
          <?php
          	$now = new DateTime();
          	$start = $beamtime->start();
          	$end = $beamtime->end();
          ?>
          @if ($now > $end)
          <?php $diff = $now->diff($end); ?>
          <td class="text-muted">Ended {{{ $diff->format('%a days ago') }}}</td>
          @elseif ($now < $start)
          <?php $diff = $now->diff($start); ?>
          <td><span class="text-primary">Beamtime will start <?php  // show time difference until beamtime starts according to the time span
          	if ($diff->days > 0)
          		echo $diff->format('in %a days and %h hours.');
          	elseif ($diff->days === 0 && $diff->h > 0)
          		echo $diff->format('in %h hours and %i minutes.');
          	elseif ($diff->h === 0 && $diff->i > 2)
          		echo $diff->format('in %i minutes.');
          	else
          		echo 'shortly.';
          ?></span><br />
          @else
          <?php  // calculate progress of the current beamtime
          	$diff = $now->diff($start);
          ?>
          <td><span class="text-success">Running for <?php  // show time span for how long beamtime is running more precise
          	if ($diff->days > 0)
          		echo $diff->format('%a days and %h hours.');
          	elseif ($diff->days === 0 && $diff->h > 0)
          		echo $diff->format('%h hours and %i minutes.');
          	else
          		echo $diff->format('%i minutes.');
          ?></span><br />
          @endif
          @if ($now <= $end)
          <?php $individual_open = $beamtime->shifts->sum(function($shift){
          	if ($shift->users->count() > $shift->n_crew)
      			return 0;
      		else
      			return $shift->n_crew - $shift->users->count();
          }); ?>
          @if ($individual_open > 0)
          Shifts: {{ $beamtime->shifts->filter(function($shift){ return $shift->users->count() < $shift->n_crew; })->count() }}/{{ $beamtime->shifts->filter(function($shift){ return !$shift->maintenance; })->count() }} open ({{ $individual_open }}/{{ $beamtime->shifts->sum('n_crew') }} individual shifts open)</td>
          @else
          All shifts filled.</td>
          @endif
          @endif
          @if (Auth::user()->isRunCoordinator())
          <td>
            @if ($now < $end)
            <a class='btn btn-warning btn-xs' href="/beamtimes/{{{$beamtime->id}}}/rc"><span class="fa fa-calendar-o"></span> RC shifts</a>
            @endif
          </td>
          @endif
          @endif  {{-- end of check if beamtime contains shifts --}}
        </tr>
        @endforeach
      </tbody>
    </table>
    </div>
    <div class="page-header" style="padding-top:10%;">
        <h2>Top Users in the Current 12 Months</h2>
	</div>
    <div class="list-group clearfix">
    <div class="col-sm-5 col-md-4" style="padding:0;">
      <a href="#" class="list-group-item disabled">
        <span class="badge">#Shifts</span> User
      </a>
      <?php
		$future = new DateTime("+2 months");
		$past = new DateTime("-10 months");
		$beamtimes = Beamtime::all()->filter(function($beamtime) use($future, $past)
			{
				return $beamtime->start() > $past && $beamtime->start() < $future;
			});
		if (!$beamtimes->count())
			echo "No beamtimes within the current 12 months.";
		else if (!$beamtimes->shifts->users->count())
			echo "No shifts taken within the current 12 months.";
		else {
			$shifts_user = array();
			$count = 0;
			$covered = 0;
			$beamtimes->shifts->users->groupBy('id')->each(function($user_shifts) use(&$shifts_user, &$count)
				{
					if (Auth::user()->username === $user_shifts[0]->username)
						$count = count($user_shifts);
					array_push($shifts_user, [$user_shifts[0]->username, $user_shifts[0]->get_full_name(), $user_shifts[0]->workgroup->short, count($user_shifts)]);
				});
			uasort($shifts_user, function($a, $b)
				{
					return $a[3] < $b[3];
				});

			// take the top 5 to calculate the percentage they cover and check if users have the same amount of shifts
			$shifts_user = array_slice($shifts_user, 0, 5, true);
			$found = false;
			// prev and counter are used to determine if the fourth (or even fifth) user has the same amount of shifts as the third one
			$prev = 0;
			$counter = 0;
			foreach ($shifts_user as $user) {
				$covered += $user[3];
				// if the top third user has the same amount of shifts as the following, then show them as well, if not continue to at least get all the covered shits of the top 5
				if ($counter >= 3 && $user[3] < $prev)
					continue;

				$list_class = "list-group-item";
				if (Auth::user()->username === $user[0]) {
					$found = true;
					$user[1] = "You";
					$list_class .= " list-group-item-success";
				}
				echo '<a href="/users/' . $user[0] . "\" class=\" . $list_class . \">\n"
					. '<span class="badge">' . $user[3] . "</span>\n"
					. $user[1] . ' (' . $user[2] . ")</a>\n";

				$prev = $user[3];
				$counter++;
			};
			if (!$found) {
				echo "<a href=\"#\" class=\"list-group-item disabled\">&hellip;</a>\n";
				echo '<a href="/users/' . Auth::user()->username . "\" class=\"list-group-item\">\n"
					. '<span class="badge">' . $count . "</span>\n"
					. "You</a>\n";
			}
		}
	  ?>
    </div>
    </div>
    {{-- the variable covered is only declared if we have beamtimes in this period as well as users taken shifts in those beamtimes --}}
    @if (isset($covered))
    <p>
        The top 5 users covered {{{ round($covered/$beamtimes->shifts->users->count()*100, 1) }}}% of all the taken individual shifts within this period.<br />
        (Total shift coverage is {{{ round($beamtimes->shifts->users->count()/$beamtimes->shifts->sum('n_crew')*100, 1) }}}%)
    </p>
    @endif
    @else
    <h4 class="text-danger">No beamtimes found</h4>
    @endif
    <a name="rating-guide"></a>
    <div class="page-header" style="padding-top:10%;">
        <h2>Rating Guidance for Shift Experience</h2>
    </div>
    <div class="table-responsive">
      <?php $guide = new RatingGuide(); $guide->show(); ?>
    </div>
    <div class="page-header" style="padding-top:10%;">
        <h3>KPH Beamtime Schedule</h3>
    </div>
    You can find the current beamtime schedule for MAMI <a href="https://admin.kph.uni-mainz.de/strahlzeitplan?changeLang(en)">here</a>.
@endif
</div>
@stop
