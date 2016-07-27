@extends('layouts.default')

@section('title')
Merge Beamtimes
@stop

@section('scripts')
{{ HTML::script('js/laravel.js') }}
<script type="text/javascript">
$(document).ready(function() {
    //$("[rel='tooltip']").tooltip();
    $("body").tooltip({ selector: '[data-toggle=tooltip]' });

});
</script>
@stop

@section('content')
{{ Form::open(['route' => array('merge'), 'method' => 'PUT']) }}
<div class="col-lg-10 col-lg-offset-1">
    <div class="page-header">
        <h2>Last Created Beamtimes</h2>
    </div>
    <h4>Choose Beamtimes for merging</h4>
    <p style="padding: 15px;">
      The last created beamtimes are shown below. Choose two beamtimes in order to merge them.<br />
      Only beamtimes which are in direct succession can be merged!
    </p>

    @if ($beamtimes->count())
    <div class="table-responsive">
    <table class="table table-striped table-hover">
      <thead>
        <tr>
          <th>Beamtime Name</th>
          <th>Start</th>
          <th>#Shifts</th>
          <th>Status</th>
          <th class="text-center">Merge</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($beamtimes as $beamtime)
        <tr>
          {{-- Check if the beamtime contain shifts to avoid errors --}}
          @if (is_null($beamtime->shifts->first()))
          <td colspan="4"><h4 class="text-danger">Beamtime contains no shifts!</h4></td>
          <td class="text-center"><a href="/beamtimes/{{{$beamtime->id}}}" data-method="delete" data-confirm="Are you sure to delete this beamtime?" class="btn btn-danger btn-sm"><span class="fa fa-times"></span>Delete</a></td>
          @else
          <td>{{ link_to("/beamtimes/{$beamtime->id}", $beamtime->name) }}</td>
          <td>{{ $beamtime->start_string() }}</td>
          <td>{{ $beamtime->shifts()->count() }}</td>
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
          <td><span class="text-primary">Beamtime will start in <?php  // show time difference until beamtime starts according to the time span
          	if ($diff->days > 0)
          		echo $diff->format('%a days and %h hours.');
          	elseif ($diff->days === 0 && $diff->h > 0)
          		echo $diff->format('%h hours and %i minutes.');
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
          All shifts filled ({{ $beamtime->shifts->filter(function($shift){ return !$shift->maintenance; })->count() }} total, {{ $beamtime->shifts->sum('n_crew') }} individual).</td>
          @endif
          @endif
          <td class="text-center">
            <div class="checkbox">
              <label>
                {{ Form::checkbox('merge[]', $beamtime->id, false) }}
                <a rel="tooltip" data-toggle="tooltip" data-placement="top" data-original-title="Choose to merge" class="btn btn-default btn-xs"><span class="fa fa-compress"></span></a>
              </label>
            </div>
          </td>
          @endif  {{-- end of check if beamtime contains shifts --}}
        </tr>
        @endforeach
      </tbody>
    </table>
    </div>
    <div align="right">
      {{ Form::submit('Merge Beamtimes', array('class' => 'btn btn-primary')) }}
    </div>
    @else
    <h3 class="text-danger">No beamtimes found</h3>
    @endif
</div>
{{ Form::close() }}
@stop

