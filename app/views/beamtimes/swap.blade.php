@extends('layouts.default')

@section('title')
{{ $beamtime->name }}
@stop

@section('scripts')
<script type="text/javascript">
$(document).ready(function() {
    //$("[rel='tooltip']").tooltip();
    $("body").tooltip({ selector: '[data-toggle=tooltip]' });
});
</script>
@stop

@section('content')
<div class="col-lg-10 col-lg-offset-1">
    <div class="page-header">
      @if (!empty($current))
      <table width="100%">
        <tr>
          <td>
            <h2>Beamtime: {{{ $beamtime->name }}}</h2>
          </td>
          <td class="text-right hidden-print">
            {{ link_to(URL::previous(), 'Cancel', ['class' => 'btn btn-default']) }}
          </td>
        </tr>
      </table>
      @else
      <h2>Beamtime: {{{ $beamtime->name }}}</h2>
      @endif
    </div>

    {{-- Check if the beamtime contain shifts to avoid errors --}}
    @if (is_null($beamtime->shifts->first()))
    <h3 class="text-danger">Beamtime contains no shifts!</h3>
    @else
    @if (isset($beamtime))
    {{-- show a button to accept the swap request here if it is currently shown --}}
    @if (!empty($org) && !empty($req))
    <table width="100%">
      <tr>
        <td>
          <h3 class="text-warning"><b>Swap Request</b></h3>
          <p>Do you accept this swap request from {{{ User::find(Swap::whereHash($swap)->first()->user_id)->get_full_name() }}}?</p>
        </td>
        <td style="padding-left:20px; padding-top:15px;">
          {{ Form::open(['route' => array('swaps.update', $swap), 'class' => 'hidden-print', 'style' => 'float: left; margin-right: 5px;', 'role' => 'form']) }}
            {{ Form::hidden('_method', 'PUT') }}
            {{ Form::submit('Accept', array('class' => 'btn btn-primary')) }}
          {{ Form::close() }}
          {{ Form::open(['route' => array('swaps.update', $swap), 'class' => 'hidden-print', 'role' => 'form']) }}
            {{ Form::hidden('_method', 'PUT') }}
            {{ Form::hidden('action', 'decline') }}
            {{ Form::submit('Decline', array('class' => 'btn btn-danger')) }}
          {{ Form::close() }}
        </td>
      </tr>
    </table>
    @else
    <div class="hidden-print">
      <h3>Progress</h3>
      <?php  // calculate some time information for later usage
      	$now = new DateTime();
      	$start = new DateTime($beamtime->shifts->first()->start);
      	$end = new DateTime($beamtime->shifts->last()->start);
      	$dur = 'PT' . $beamtime->shifts->last()->duration . 'H';
      	$end->add(new DateInterval($dur));
      ?>
      @if ($now < $start)
      <?php $diff = $now->diff($start); ?>
      <p class="text-primary">Beamtime will start in <?php  // show time difference until beamtime starts according to the time span
      	if ($diff->d > 0)
      		echo $diff->format('%a days and %h hours.');
      	elseif ($diff->d === 0 && $diff->h > 0)
      		echo $diff->format('%h hours and %i minutes.');
      	else
      		echo $diff->format('%i minutes.');
      ?></p>
      @elseif ($now > $end)
      <?php $diff = $now->diff($end); ?>
      <p class="text-success">Beamtime ended {{{ $diff->format('%a days ago') }}}.</p>
      @else
      <?php  // calculate progress of the current beamtime
      	$length = $end->getTimestamp() - $start->getTimestamp();
      	$elapsed = $now->getTimestamp() - $start->getTimestamp();
      	$progress = round($elapsed/$length*100, 2);
      ?>
      <div class="progress progress-striped">
        <div class="progress-bar progress-bar-success" style="width: {{{ $progress }}}%"></div>
      </div>
      @endif
    </div>
    @endif  {{-- swap request --}}
    <div class="table-responsive">
    <table class="table table-striped table-hover">
      <thead>
        <tr>
          <th>#Shift</th>
          <th>Start</th>
          <th>Duration</th>
          <th>Shift Workers</th>
          <th>Remarks</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php $day = ""; $other_user = 0; if (!empty($current)) $other_user = $shifts->find($current)->get_other_user_id(Auth::id());  // save the id of the other user on the original shift; 0 if not existent ?>
        @foreach ($shifts as $shift)
        @if ($day !== date("l, d.m.Y", strtotime($shift->start)))
        <?php $day = date("l, d.m.Y", strtotime($shift->start)); ?>
        <thead>
          <tr class="active" style="padding-left:20px;">
            <th colspan=7>{{ $day }}</th>
          </tr>
        </thead>
        @endif
        @if (!empty($current) && $current == $shift->id)
        <tr class="info">
        @elseif (!empty($org) && $org == $shift->id)
        <tr class="info">
        @elseif (!empty($req) && $req == $shift->id)
        <tr class="info">
        @else
        <tr>
        @endif
          <?php $td = ""; if ($n = $shift->users->count() > 0) $td = '<td rowspan="' . $n . '">'; else $td = '<td>'; ?>
          {{ $td }}</td>
          {{ $td }}{{ $shift->start }}</td>
          {{ $td }}{{ $shift->duration }} hours</td>
          {{-- check if users subscribed to this shift and it's not maintenance --}}
          @if ($shift->users->isEmpty() && !$shift->maintenance)
          {{-- if not, then display this --}}
          <td>Nobody subscribed yet</td>
          @else
          {{-- otherwise show the subscribed users and display open shifts --}}
          <td><?php $shift->users->each(function($user)  // $shift->users returns a Collection of User objects which are connected to the current Shift object via the corresponding pivot table; with Collection::each we can iterate over this Collection instead of creating a foreach loop
          {
          	echo '<span rel="tooltip" data-toggle="tooltip" data-placement="top" title="Rating: ' . $user->rating . '">' . $user->first_name . ' ' . $user->last_name . '</span> (' . $user->workgroup->name . ')<br />';
          });
          ?></td>
          @endif
          {{ $td }}{{ $shift->remark }}</td>
          {{ $td }}@if ($shift->maintenance) <a href="#" class="btn btn-info btn-sm disabled">Maintenance</a>
          @elseif ($shift->users->count() == 0) <a href="#" class="btn btn-danger btn-sm disabled">Empty</a>
          @elseif ($shift->users->sum('rating') < 5 ) <a href="#" class="btn btn-warning btn-sm disabled">Bad</a>
          @elseif ($shift->users->sum('rating') < 8 ) <a href="#" class="btn btn-primary btn-sm disabled">Okay</a>
          @else <a href="#" class="btn btn-success btn-sm disabled">Perfect</a>
          @endif</td>
          {{-- only show swap buttons if shift is not empty (which is true for maintenance) and not in the future as well as the $now and $current variable is set which should be true in case of swap selection ($org and $req not set); additionally check if another user is subscribed to the original shift that this user is not the only one subscribed to this shift as well --}}
          {{ $td }}@if (!$shift->users->find(Auth::id()) && !$shift->users->isEmpty() && !empty($now) && $now < new DateTime($shift->start) && !empty($current) && ( !$shift->users->find($other_user) || $shift->users->count() > 1 ))
          {{ Form::open(['route' => array('swaps.store', $current, $shift->id), 'class' => 'hidden-print', 'style' => 'float: left;', 'role' => 'form']) }}
              <button type="submit" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Swap with this shift"><i class="fa fa-exchange fa-lg"></i></button>
          {{ Form::close() }}
          @endif</td>
        </tr>
        @endforeach
      </tbody>
    </table>
    </div>
    <div>
      Total {{ $shifts->filter(function($shift){ return !$shift->maintenance; })->count() }} shifts, {{{ $shifts->filter(function($shift){ return $shift->maintenance; })->count() }}} maintenance shifts, {{ $shifts->sum('n_crew') }} individual shifts
    </div>
    @else
    <h3 class="text-danger">Beamtime not found!</h3>
    @endif
    @endif  {{-- end of check if beamtime contains shifts --}}
</div>
@stop

