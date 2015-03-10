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
      @if (Auth::user()->isAdmin)
      <table width="100%">
        <tr>
          <td>
            <h2>Beamtime: {{{ $beamtime->name }}}</h2>
          </td>
          <td class="text-right hidden-print">
            <a class="btn btn-primary btn" href="/beamtimes/{{{$beamtime->id}}}/edit"><span class="fa fa-pencil"></span>&nbsp;&nbsp;&nbsp;Edit Beamtime</a>
          </td>
        </tr>
      </table>
      @else
      <h2>Beamtime: {{{ $beamtime->name }}}</h2>
      @endif
    </div>
    @if (!empty($beamtime->description))
    <h4>Short beamtime description:</h4>
    <p style="white-space: pre-wrap;">{{ $beamtime->description }}</p>
    @endif

    {{-- Check if the beamtime contain shifts to avoid errors --}}
    @if (is_null($beamtime->shifts->first()))
    <h3 class="text-danger">Beamtime contains no shifts!</h3>
    @else
    @if (isset($beamtime))
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
      <div class="progress progress-striped active">
        <div class="progress-bar progress-bar-success" style="width: {{{ $progress }}}%"></div>
      </div>
      @endif
      <h3>Shift Status</h3>
      <?php  // calculate shift status
      	$shifts_total = $shifts->filter(function($shift){ return !$shift->maintenance; })->count();
      	$shifts_open = $shifts->filter(function($shift){ return $shift->users->count() != $shift->n_crew; })->count();
      	$individual = $shifts->sum('n_crew');
      	$individual_open = $shifts->sum(function($shift){ return $shift->n_crew - $shift->users->count(); }) ;
      	$empty_shifts = $shifts->sum(function($shift){ return $shift->users->isEmpty() && !$shift->maintenance; });
      	$full = round(($shifts_total - $shifts_open)/$shifts_total * 100, 2);
      	$empty = round($empty_shifts/$shifts_total * 100, 2);
      ?>
      <div class="progress">
        <div class="progress-bar progress-bar-success" style="width: {{{ $full }}}%"></div>
        <div class="progress-bar progress-bar-warning" style="width: {{{ 100 - $full - $empty }}}%"></div>
        <div class="progress-bar progress-bar-danger" style="width: {{{ $empty }}}%"></div>
      </div>
      <p>
        {{{ $shifts_open }}} of {{{ $shifts_total }}} total shifts open, {{{ $individual_open }}} of {{{ $individual }}} individual shifts open. (<span class="text-success">full</span>, <span class="text-warning">partly filled</span>, <span class="text-danger">empty</span>)
      </p>
    </div>
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
          <th class="hidden-print">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php $i = 0; $day = ""; ?>
        @foreach ($shifts as $shift)
        @if ($day !== date("l, d.m.Y", strtotime($shift->start)))
        <?php $day = date("l, d.m.Y", strtotime($shift->start)); ?>
        <thead>
          <tr class="active" style="padding-left:20px;">
            <th colspan=7>{{ $day }}</th>
          </tr>
        </thead>
        @endif
        <tr>
          <?php $td = ""; if ($n = $shift->users->count() > 0) $td = '<td rowspan="' . $n . '">'; else $td = '<td>'; ?>
          {{ $td }}{{ ++$i }}</td>
          {{ $td }}{{ $shift->start }}</td>
          <?php  // calculate actual duration depending on local timezone
          	$start = new DateTime($shift->start);
          	$end = clone($start);
          	$dur = 'PT' . $shift->duration . 'H';
          	$end->add(new DateInterval($dur));
          ?>
          {{ $td }}{{ $start->diff($end)->h }} hours</td>
          {{-- check if users subscribed to this shift and it's not maintenance --}}
          @if ($shift->users->isEmpty() && !$shift->maintenance)
          {{-- if not, then display this --}}
          <td>Nobody subscribed yet</td>
          @else
          {{-- otherwise show the subscribed users and display open shifts --}}
          <td><?php $shift->users->each(function($user)  // $shift->users returns a Collection of User objects which are connected to the current Shift object via the corresponding pivot table; with Collection::each we can iterate over this Collection instead of creating a foreach loop
          {
          	echo '<span rel="tooltip" data-toggle="tooltip" data-placement="top" title="Rating: ' . $user->rating . '">' . $user->first_name . ' ' . $user->last_name . '</span><span class="hidden-print"> (' . $user->workgroup->name . ')</span><br />';
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
          {{ $td }}
          {{-- only show un-/subscribe buttons if shift is not during maintenance and not in the future --}}
          @if (!$shift->maintenance && $now < new DateTime($shift->start))
          @if (!$shift->users->find(Auth::user()->id))
          @if ($shift->users->count() < $shift->n_crew)  {{-- only allow subscription if the shift's not full already --}}
          {{ Form::open(['route' => array('shifts.update', $shift->id), 'method' => 'PATCH', 'class' => 'hidden-print', 'role' => 'form']) }}
              {{ Form::hidden('action', 'subscribe') }}
              {{-- Form::submit('Subscribe', array('class' => 'btn btn-primary btn-sm')) --}}
              <button type="submit" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Subscribe"><i class="fa fa-check fa-lg"></i></button>
          {{ Form::close() }}
          @endif
          @else
          {{ Form::open(['route' => array('shifts.update', $shift->id), 'method' => 'PATCH', 'class' => 'hidden-print', 'style' => 'float: left; margin-right: 5px;', 'role' => 'form']) }}
              {{ Form::hidden('action', 'unsubscribe') }}
              {{-- Form::submit('Unsubscribe', array('class' => 'btn btn-default btn-sm')) --}}
              <button type="submit" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Unsubscribe"><i class="fa fa-times fa-lg"></i></button>
          {{ Form::close() }}
          {{ Form::open(['route' => array('swaps.create', $shift->id), 'class' => 'hidden-print', 'style' => 'float: left;', 'role' => 'form']) }}
              {{-- Form::submit('Swap', array('class' => 'btn btn-default btn-sm ')) --}}
              <button type="submit" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Swap Shift"><i class="fa fa-exchange fa-lg"></i></button>
          {{ Form::close() }}
          @endif
          @endif  {{-- maintenance and future check --}}
          </td>
          {{-- Form::open(['route' => 'shifts.update', 'class' => 'form-horizontal print-hidden', 'role' => 'form']) --}}
              {{-- <td>@if (Auth::user()->isAdmin || run_coordinator) zusätzliche buttons --}}
              {{-- is run coordinator?  --> vlt in methode auslagern, isRun_coordinator(beamtime_id) ? --}}
              {{-- evtl so was wie: @if ($shift->run_coordinators->filter(function($user) { return $user->id == Auth::user()->id; })->first()) {{ 'user is run coordinator' }} @endif --}}
              	{{-- filter nicht ausprobiert, antworten hier: http://stackoverflow.com/questions/20931020/laravel-get-object-from-collection-by-attribute  (muss true returnen für verbleibende elemente) --}}
              {{-- Form::submit('Swap Shift', array('class' => 'btn btn-primary btn-sm')) --}}
              {{-- unsubscribe, methode sendMail in User, evtl. erstes Argument shift_id, zweites Argument identifier für swap, unsubscribe wenn diff->d smaller 14, ..., drittes Argument Nachrichteninhalt --}}
          {{-- Form::close() --}}
        </tr>
        @endforeach
      </tbody>
    </table>
    </div>
    <div>
      Total shifts: {{{ $shifts_total }}} ({{{ $shifts_open }}} open), {{{ $shifts->count() - $shifts_total }}} maintenance shifts, individual shifts: {{{ $individual }}} ({{{ $individual_open }}} open), TODO: button iCal export...
    </div>
    @else
    <h3 class="text-danger">Beamtime not found!</h3>
    @endif
    @endif  {{-- end of check if beamtime contains shifts --}}
</div>
@stop

