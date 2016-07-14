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
        <h2>Most Recent Beamtimes</h2>
    </div>
    <?php
    	$beamtimes = Beamtime::all();
    	foreach ($beamtimes as $beamtime)
    		$beamtime->start = $beamtime->start_string();
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
          <td colspan="3"><h4 class="text-danger">Beamtime contains no shifts!</h4></td>
          <td class="text-center"><a href="/beamtimes/{{{$beamtime->id}}}" data-method="delete" data-confirm="Are you sure to delete this beamtime?" class="btn btn-danger btn-sm"><span class="fa fa-times"></span>Delete</a></td>
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
    You can find the current beamtime schedule for MAMI <a href="http://www.kph.uni-mainz.de/793.php">here</a>.
@endif
</div>
@stop
