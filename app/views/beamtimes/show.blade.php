@extends('layouts.default')

@section('title')
{{ $beamtime->name }}
@stop

@section('css')
{{ HTML::style('css/animate.min.css') }}
@parent
@stop

@section('scripts')
{{ HTML::script('js/bootstrap-notify.min.js') }}
<script type="text/javascript">
$(document).ready(function() {
    //$("[rel='tooltip']").tooltip();
    $("body").tooltip({ selector: '[data-toggle=tooltip]' });

    var msg = sessionStorage.getItem('msg');
    var type = sessionStorage.getItem('type');
    sessionStorage.removeItem('msg');
    sessionStorage.removeItem('type');
    if (msg) {
        var notify = $.notify({
            message: msg
          },{
            element: 'body',
            position: null,
            type: type,
            allow_dismiss: true,
            newest_on_top: false,
            showProgressbar: false,
            placement: {
                from: "top",
                align: "center"
            },
            offset: 60,
            spacing: 10,
            z_index: 1031,
            delay: 2500,
        });
    }
});

function sub(e) {
    $btn = $(document.activeElement).attr("disabled", true);
    $.ajax({
        url: e.action,
        type: e.method,
        data: {_method: e._method.value, event: e.event.value},
        success: function(data, textStatus, jqXHR)
        {
            sessionStorage.setItem('type', data[0]);
            sessionStorage.setItem('msg', data[1]);
        }
    });
    setTimeout(window.location.reload(), 50);
    return false;
}
</script>
@stop

@section('content')
<div class="col-lg-10 col-lg-offset-1">
    <div class="page-header">
      <table width="100%">
        <tr>
          <td>
            <h2>Beamtime: {{{ $beamtime->name }}}</h2>
          </td>
          <td class="text-right hidden-print">
            @if (Auth::user()->isAdmin())
            <a class="btn btn-primary btn" href="/beamtimes/{{{$beamtime->id}}}/edit"><span class="fa fa-pencil"></span>&nbsp;&nbsp;&nbsp;Edit Beamtime</a>
            @endif
            {{ link_to('/beamtimes', 'Back', ['class' => 'btn btn-default']) }}
          </td>
        </tr>
      </table>
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
      	$end = $beamtime->shifts->last()->end();
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
        <?php
        	$day = date("l, d.m.Y", strtotime($shift->start));
        	$date = new DateTime($shift->start);
        	$rc_day = $rc_shifts->filter(function($rc_shift) use($date)
        	{
        		$day = new DateTime($rc_shift->start);
        		return $day->format('Y-m-d') === $date->format('Y-m-d');
        	});
        	$rc_info = array();
        	$rc_day->each(function($rc_shift) use(&$rc_info)
        	{
        		if ($rc_shift->user->count())
        			$rc_info[] = $rc_shift->type() . ': ' . $rc_shift->user->first()->get_full_name();
        		else
        			$rc_info[] = $rc_shift->type() . ': open';
        	});
        	$rc = '';
        	if (!$rc_day->user->isEmpty())
        		$rc = 'Run Coordinators: ' . implode(', ', $rc_info);
        	elseif ($rc_day->count())
        		$rc = "Run Coordinators: TBA";
        ?>
        <thead>
          <tr class="active" style="padding-left:20px;">
            <th colspan=7>{{ $day }} &emsp;&emsp;&emsp; {{{ $rc }}}</th>
          </tr>
        </thead>
        @endif
        <tr>
          <?php $td = ""; if ($n = $shift->users->count() > 0) $td = '<td rowspan="' . $n . '">'; else $td = '<td>'; ?>
          {{ $td }}<span{{ $shift->users->count() < $shift->n_crew ? ' class="text-danger"' : ''}}>{{ ++$i }}&emsp;({{{ $shift->type() }}})</span></td>
          {{ $td }}<span{{ $shift->users->count() < $shift->n_crew ? ' class="text-danger"' : ''}}>{{ $shift->start }}</span></td>
          <?php  // calculate actual duration depending on local timezone
          	$start = new DateTime($shift->start);
          	$end = $shift->end();
          ?>
          {{ $td }}{{ $start->diff($end)->h }} hours</td>
          {{-- check if users subscribed to this shift and it's not maintenance --}}
          @if ($shift->users->isEmpty() && !$shift->maintenance)
          {{-- if not, then display this --}}
          <td>Nobody subscribed yet</td>
          @else
          {{-- otherwise show the subscribed users and display open shifts --}}
          <td><?php $shift->users->each(function($user) use($shift)  // $shift->users returns a Collection of User objects which are connected to the current Shift object via the corresponding pivot table; with Collection::each we can iterate over this Collection instead of creating a foreach loop
          {
          	$rad = '';
          	$warn = '';
          	if (!$user->hasRadiationInstruction($shift->start)) {
          		$rad = '<span rel="tooltip" data-toggle="tooltip" data-placement="top" title="Radiation Protection Instruction missing!" class="text-danger">&#9762; </span>';
          		$warn = ' class="text-danger"';
          	}
          	echo $rad . '<span rel="tooltip" data-toggle="tooltip" data-placement="top" title="Rating: ' . $user->rating . '"' . $warn . '>' . link_to("/users/$user->username", $user->get_full_name(), ['style' => 'color: inherit; text-decoration: none;']) . '</span><span class="hidden-print"> (' . $user->workgroup->name . ')</span><br />';
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
          {{ Form::open(['route' => array('shifts.update', $shift->id), 'method' => 'PATCH', 'class' => 'hidden-print', 'role' => 'form', 'onsubmit' => 'return sub(this);']) }}
              {{ Form::hidden('event', 'subscribe') }}
              <button type="submit" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Subscribe"><i class="fa fa-check fa-lg"></i></button>
          {{ Form::close() }}
          @endif
          @else
          {{ Form::open(['route' => array('shifts.update', $shift->id), 'method' => 'PATCH', 'class' => 'hidden-print', 'style' => 'float: left; margin-right: 5px;', 'role' => 'form', 'onsubmit' => 'return sub(this);']) }}
              {{ Form::hidden('event', 'unsubscribe') }}
              <button type="submit" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Unsubscribe"><i class="fa fa-times fa-lg"></i></button>
          {{ Form::close() }}
          {{ Form::open(['route' => array('swaps.create', $shift->id), 'class' => 'hidden-print', 'style' => 'float: left;', 'role' => 'form']) }}
              <button type="submit" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Swap Shift"><i class="fa fa-exchange fa-lg"></i></button>
          {{ Form::close() }}
          @endif
          @endif  {{-- maintenance and future check --}}
          </td>
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

