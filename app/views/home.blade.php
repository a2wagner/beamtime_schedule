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
        <h2>Most recent beamtimes</h2>
    </div>
    <?php $beamtimes = Beamtime::orderBy('id', 'desc')->take(5)->get(); ?>
    @if ($beamtimes->count())
    <div class="table-responsive">
    <table class="table table-striped table-hover">
      <thead>
        <tr>
          <th>Beamtime Name</th>
          <th>Start</th>
          <th>#Shifts</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($beamtimes as $beamtime)
        <tr>
          {{-- Check if the beamtime contain shifts to avoid errors --}}
          @if (is_null($beamtime->shifts->first()))
          @if (Auth::user()->isAdmin)
          <td colspan="3"><h4 class="text-danger">Beamtime contains no shifts!</h4></td>
          <td class="text-center"><a href="/beamtimes/{{{$beamtime->id}}}" data-method="delete" data-confirm="Are you sure to delete this beamtime?" class="btn btn-danger btn-sm"><span class="fa fa-times"></span>Delete</a></td>
          @endif
          @else
          <td>{{ link_to("/beamtimes/{$beamtime->id}", $beamtime->name) }}</td>
          <td>{{ $beamtime->shifts->first()->start }}</td>
          <td>{{ $beamtime->shifts()->count() }}</td>
          <?php  // calculate some time information of the beamtime
          	$now = new DateTime();
          	$start = new DateTime($beamtime->shifts->first()->start);
          	$end = new DateTime($beamtime->shifts->last()->start);
          	$dur = 'PT' . $beamtime->shifts->last()->duration . 'H';
          	$end->add(new DateInterval($dur));
          ?>
          @if ($now < $start)
          <?php $diff = $now->diff($start); ?>
          <td class="text-primary">Beamtime will start <?php  // show time difference until beamtime starts according to the time span
          	if ($diff->d > 0)
          		echo $diff->format('in %a days and %h hours.');
          	elseif ($diff->d === 0 && $diff->h > 0)
          		echo $diff->format('in %h hours and %i minutes.');
          	elseif ($diff->h === 0 && $diff->i > 2)
          		echo $diff->format('in %i minutes.');
          	else
          		echo 'shortly.';
          ?></td>
          @elseif ($now > $end)
          <?php $diff = $now->diff($end); ?>
          <td class="text-muted">Ended {{{ $diff->format('%a days ago') }}}</td>
          @else
          <?php  // calculate progress of the current beamtime
          	$diff = $now->diff($start);
          ?>
          <td class="text-success">Running for <?php  // show time span for how long beamtime is running more precise
          	if ($diff->d > 0)
          		echo $diff->format('%a days and %h hours.');
          	elseif ($diff->d === 0 && $diff->h > 0)
          		echo $diff->format('%h hours and %i minutes.');
          	else
          		echo $diff->format('%i minutes.');
          ?></td>
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
@endif
</div>
@stop
