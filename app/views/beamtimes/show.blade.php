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

    //$(".btn-default").attr("disabled", false);
    $(".nfrcxp").attr("disabled", false);

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

    // add a separate tooltip handler for the case of two data-toggle elements like modal + tooltip
    $('[data-tooltip="tooltip"]').tooltip();
});

function sub(e) {
    $(document.activeElement).attr("disabled", true);
    $.ajax({
        url: e.action,
        type: e.method,
        data: {_method: e._method.value, event: e.event.value},
        success: function(data, textStatus, jqXHR)
        {
            sessionStorage.setItem('type', data[0]);
            sessionStorage.setItem('msg', data[1]);
            //$(document.activeElement).attr("disabled", false);
            window.location.reload();
        }
    });
    return false;
}
</script>
@stop

@section('content')
<div class="col-lg-10 col-lg-offset-1">
    <div class="page-header">
      <table style="background-color: initial;" width="100%">
        <tr>
          <td>
            <h2>Beamtime: {{{ $beamtime->name }}}</h2>
          </td>
          <td class="text-right hidden-print">
            @if (Auth::user()->isAdmin())
            <a class="btn btn-primary btn" href="/beamtimes/{{{$beamtime->id}}}/edit"><span class="fa fa-pencil"></span>&nbsp;&nbsp;Edit Beamtime</a>
            @endif
            @if (Auth::user()->isAdmin() || (Auth::user()->isRunCoordinator() && $beamtime->run_coordinators()->contains(Auth::user())))
            <?php Session::flash('users', $beamtime->run_coordinators()->merge($beamtime->shifts->users->unique())); ?>
            <div style="display: inline-block">
            {{ Form::open(['route' => 'users.mail', 'class' => 'hidden-print', 'role' => 'form']) }}
              <button type="button" class="btn btn-success" data-toggle="modal" data-target=".mail-modal"><span class="fa fa-envelope"></span>&nbsp;&nbsp;Mail Subscribers</button>
              <div class="text-left">
                <?php $mail = new MailModal(); $mail->modal('mail-modal', 'Write Email to Shift Subscribers'); ?>
              </div>
            {{ Form::close() }}
            </div>
            <div style="display: inline-block">
            {{ Form::open(['route' => array('statistics', 'period'), 'class' => 'hidden-print', 'role' => 'form']) }}
              {{ Form::hidden('date1', $beamtime->start()->format('Y-m-d')) }}
              {{ Form::hidden('date2', $beamtime->start()->format('Y-m-d') . ' 23:59:59') }}
              {{ Form::hidden('date-end', $beamtime->end()->format('Y-m-d')) }}
              {{ Form::button('<i class="fa fa-bar-chart fa-fw" aria-hidden="true"></i> Statistics', ['class' => 'btn btn-info', 'type' => 'submit']) }}
            {{ Form::close() }}
            </div>
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
    <?php $rules_shown = false; ?>
    <div class="hidden-print">
      @if (Auth::user()->isAdmin() || (Auth::user()->isRunCoordinator() && $beamtime->run_coordinators()->contains(Auth::user())))
      <h3>Beamtime Settings</h3>
      <p><ul>
        <li>Enforcing Run Coordinators on shift before shift workers can subscribe to shifts on a day is {{{ $beamtime->enforce_rc ? '' : 'not' }}} activated</li>
        <li>Experience blocking for shift subscriptions to prevent two inexperienced users on a shift is {{{ $beamtime->experience_block ? '' : 'not' }}} enabled (current limit: {{{ Shift::EXPERIENCE_BLOCK }}} shifts)</li>
        <li>
          {{ $beamtime->subscription_start_string() }}@if($beamtime->enforce_subscription)<?php $rules_shown = true; ?><br />
          (workgroups from Europe are allowed to subscribe after {{{ BEAMTIME::SUBSCRIPTION_WAITING_DAYS_EUROPE }}} day, the local group is allowed to subscribe after {{{ BEAMTIME::SUBSCRIPTION_WAITING_DAYS_LOCAL }}} days)@endif
        </li>
      </ul></p>
      @endif
      <h3>Progress</h3>
      <?php
      	$now = new DateTime();
      	$start = $beamtime->start();
      	$end = $beamtime->end();
      	$sub = '';
      	if ($beamtime->enforce_subscription)
      		$sub = new DateTime($beamtime->subscription_start);
      ?>
      @if ($now < $start)
      <?php $diff = $now->diff($start); ?>
      <p class="text-primary">Beamtime will start in <?php  // show time difference until beamtime starts according to the time span
      	if ($diff->days > 0)
      		echo $diff->format('%a days and %h hours.');
      	elseif ($diff->days === 0 && $diff->h > 0)
      		echo $diff->format('%h hours and %i minutes.');
      	else
      		echo $diff->format('%i minutes.');
      ?></p>
      @if ($beamtime->enforce_subscription)
      <h4>Based on your affiliation you're allowed to subscribe to shifts <?php
      	$region = Auth::user()->workgroup->region;
      	if ($region === Workgroup::EUROPE)
      		$sub->modify('+' . Beamtime::SUBSCRIPTION_WAITING_DAYS_EUROPE . ' day');
      	if ($region === Workgroup::LOCAL)
      		$sub->modify('+' . Beamtime::SUBSCRIPTION_WAITING_DAYS_LOCAL . ' days');
      	if ($sub < $now)
      		echo 'right now';
      	else {
      		$diff = $now->diff($sub);
      		if ($diff->days > 0)
      			echo $diff->format('in %a days and %h hours');
      		elseif ($diff->days === 0 && $diff->h > 0)
      			echo $diff->format('in %h hours and %i minutes');
      		else
      			echo $diff->format('in %i minutes');
      	}
      	$sub = new DateTime($beamtime->subscription_start);
      ?></h4>
      <p class="text-primary">Beamtime shift subscription overall start{{ ($sub < $now) ? 'ed' : 's' }} on {{ $sub->format('l jS F Y \a\t g:i A \(T\)') }}</p>
      @if (!$rules_shown)
      <p class="text-muted" style="margin-top: -8px;">(workgroups from Europe are allowed to subscribe after {{{ BEAMTIME::SUBSCRIPTION_WAITING_DAYS_EUROPE }}} day, the local group is allowed to subscribe after {{{ BEAMTIME::SUBSCRIPTION_WAITING_DAYS_LOCAL }}} days)</p>
      @endif  {{-- subscription rules shown --}}
      @endif  {{-- enforce subscription check --}}
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
	$individual_open = 0;
	if ($shifts_total === 0) {
		echo "All shifts are maintenance shifts. Maybe the beamtime has been cancelled.";
	} else {
      		$shifts_open = $shifts->filter(function($shift){ return $shift->users->count() < $shift->n_crew; })->count();
      		$individual = $shifts->sum('n_crew');
      		$individual_open = $shifts->sum(function($shift){
      			if ($shift->users->count() > $shift->n_crew)
      				return 0;
      			else
      				return $shift->n_crew - $shift->users->count();
      		});
      		$empty_shifts = $shifts->sum(function($shift){ return $shift->users->isEmpty() && !$shift->maintenance; });
      		$full = round(($shifts_total - $shifts_open)/$shifts_total * 100, 2);
      		$empty = round($empty_shifts/$shifts_total * 100, 2);
      	?>
      <div class="progress">
        <div class="progress-bar progress-bar-success" style="width: {{{ $full }}}%"></div>
        <div class="progress-bar progress-bar-warning" style="width: {{{ 100 - $full - $empty }}}%"></div>
        <div class="progress-bar progress-bar-danger" style="width: {{{ $empty }}}%"></div>
      </div>
      <?php
      }
      ?>
      <p>
        @if ($individual_open > 0)
        {{{ $shifts_open }}} of {{{ $shifts_total }}} total shifts open, {{{ $individual_open }}} of {{{ $individual }}} individual shifts open. (<span class="text-success">full</span>, <span class="text-warning">partly filled</span>, <span class="text-danger">empty</span>)
        @elseif ($shifts_total !== 0)
        All shifts filled ({{{ $shifts_total }}} total, {{{ $individual }}} individual).
        @endif
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
          <th colspan=2 style="padding-left: 30px;">Status</th>
          <th class="hidden-print">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php $i = 0; $day = ""; ?>
        @foreach ($shifts as $shift)
        <?php
        	// experience block active?
        	$xp_block = $shift->users->count() === 1 && (!$shift->users->first()->experienced($shift) && $beamtime->experience_block && !Auth::user()->experienced($shift));
        ?>
        @if ($day !== date("l, d.m.Y", strtotime($shift->start)))
        <?php
        	$day = date("l, d.m.Y", strtotime($shift->start));
        	$date = new DateTime($shift->start);
        	$rc_day = $rc_shifts->filter(function($rc_shift) use($date)
        	{
        		$day = new DateTime($rc_shift->start);
        		return $day->format('Y-m-d') === $date->format('Y-m-d');
        	});
        	// include the night shift end time as well to ensure that the night shift, which might cover a part of the next day, is considered as well
        	// note and possible TODO: improve this check by some time overlap calculation of regular shifts and RC shifts to tighten the RC enforcement
        	$rc_day_enforce_check = $rc_shifts->filter(function($rc_shift) use($date)
        	{
        		$day = new DateTime($rc_shift->start);
        		$night = $rc_shift->end();
        		return $day->format('Y-m-d') === $date->format('Y-m-d') || $night->format('Y-m-d') === $date->format('Y-m-d');
        	});
        	$rc_info = array();
        	$rc_day->each(function($rc_shift) use(&$rc_info)
        	{
        		if ($rc_shift->user->count())
        			$rc_info[] = $rc_shift->type() . ': ' . link_to("/users/" . $rc_shift->user->first()->username, $rc_shift->user->first()->get_full_name(), ['style' => 'color: inherit; text-decoration: none;']);
        		else
        			$rc_info[] = $rc_shift->type() . ': open';
        	});
        	$rc = '';
        	if (!$rc_day->user->isEmpty())
        		$rc = 'Run Coordinators: ' . implode(', ', $rc_info);
        	elseif ($rc_day->count())
        		$rc = "Run Coordinators: TBA";
        	// enforce RC shifts?
        	$enforce = $beamtime->enforce_rc && $rc_day_enforce_check->user->isEmpty();
        ?>
        <thead>
          <tr class="active" style="padding-left:20px;">
            <th colspan=2>{{ $day }}</th>
            <th colspan=6>{{ $rc }}</th>
          </tr>
        </thead>
        @endif
        @if ($shift->is_current())
        <tr style="background-color: rgb(128, 223, 255); background-color: rgba(68, 208, 255, 0.4);">
        @else
        <tr>
        @endif
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
          	$rad_string = 'missing';
          	if (!$user->hasRadiationInstruction($shift->start)) {
          		$warn = ' class="text-danger"';
          		if ($user->radiation_instructions()->count()) {
          			$warn = ' class="text-warning"';
          			$rad_string = 'expired';
          		}
          		$rad = '<span rel="tooltip" data-toggle="tooltip" data-placement="top" title="Radiation Protection Instruction ' . $rad_string . '!"' . $warn . '>&#9762; </span>';
          	}
          	echo $rad . '<span rel="tooltip" data-toggle="tooltip" data-placement="top" title="Rating: ' . $user->rating . '"' . $warn . '>' . link_to("/users/$user->username", $user->get_full_name(), ['style' => 'color: inherit; text-decoration: none;']) . '</span><span class="hidden-print"> (' . $user->workgroup->name . ')</span><br />';
          });
          ?></td>
          @endif
          {{ $td }}{{ $shift->remark }}</td>
          {{ $td }}@if (!$shift->maintenance) {{ $shift->users->count() }}/{{ $shift->n_crew }} @endif</td>
          {{ $td }}@if ($shift->maintenance) <a href="#" class="btn btn-info btn-sm disabled">Maintenance</a>
          @elseif ($shift->rating() == 0) <a href="#" class="btn btn-danger btn-sm disabled">Empty</a>
          @elseif ($shift->rating() < Shift::RATING_GOOD) <a href="#" class="btn btn-warning btn-sm disabled">Bad</a>
          @elseif ($shift->rating() < Shift::RATING_PERFECT) <a href="#" class="btn btn-primary btn-sm disabled">Good</a>
          @else <a href="#" class="btn btn-success btn-sm disabled">Perfect</a>
          @endif</td>
          {{ $td }}
          {{-- only show un-/subscribe buttons if shift is not during maintenance and not in the future --}}
          @if (!$shift->maintenance && $now < new DateTime($shift->start))
          @if (!$shift->users->find(Auth::user()->id))
          @if ($shift->users->count() < $shift->n_crew)  {{-- only allow subscription if the shift's not full already --}}
          {{ Form::open(['route' => array('shifts.update', $shift->id), 'method' => 'PATCH', 'class' => 'hidden-print', 'role' => 'form', 'onsubmit' => 'return sub(this);']) }}
              {{ Form::hidden('event', 'subscribe') }}
              @if ($enforce)
              <button type="submit" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Run Coordinator needed!" disabled><i class="fa fa-check fa-lg"></i></button>
              @elseif ($xp_block)
              <button type="submit" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Insufficient shift experience for this shift ({{{ Auth::user()->experience($shift) }}}/{{{ Shift::EXPERIENCE_BLOCK }}})." disabled><i class="fa fa-check fa-lg"></i></button>
              @else
              <button type="submit" class="btn btn-default btn-sm nfrcxp" data-toggle="tooltip" data-placement="top" title="Subscribe"'><i class="fa fa-check fa-lg"></i></button>
              @endif
          {{ Form::close() }}
          @else
          <?php
          	$text = '<p>Do you want to send a request for this shift?</p>';
          	if ($shift->users->count() > 1) {
          		foreach ($shift->users as $user) {
          			$text .= "\n<div class='checkbox'>";
          			$text .= "\n  <label>";
          			$text .= "\n    <input type='checkbox' name='user[]' value='" . $user->id . "'>";
          			$text .= "\n    " . $user->first_name;
          			$text .= "\n  </label>";
          			$text .= "\n</div>";
          		}
          	}
          ?>
          {{ Form::open(['route' => array('shifts.request', $shift->id), 'class' => 'hidden-print', 'role' => 'form']) }}
              <button type="button" class="btn btn-link btn-sm" data-toggle="modal" data-target=".request-modal-{{{$shift->id}}}" data-tooltip="tooltip" data-placement="top" title="Can I haz shift?" style="color: inherit;"><i class="fa fa-arrow-left fa-lg" style="padding: 0px 1.5px;"></i></button>
              <?php $request = new ShiftRequest(); $request->modal('request-modal-'.$shift->id, $text); ?>
          {{ Form::close() }}
          @endif
          @else
          {{ Form::open(['route' => array('shifts.update', $shift->id), 'method' => 'PATCH', 'class' => 'hidden-print', 'style' => 'float: left; margin-right: 5px;', 'role' => 'form', 'onsubmit' => 'return sub(this);']) }}
              {{ Form::hidden('event', 'unsubscribe') }}
              <button type="submit" class="btn btn-default btn-sm {{{ $enforce || $xp_block ? '' : 'nfrcxp' }}}" data-toggle="tooltip" data-placement="top" title="Unsubscribe"><i class="fa fa-times fa-lg" style="padding: 0px 2px;"></i></button>
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
      @if ($individual_open > 0)
      <p>Total shifts: {{{ $shifts_total }}} ({{{ $shifts_open }}} open), {{{ $shifts->count() - $shifts_total }}} maintenance shifts, individual shifts: {{{ $individual }}} ({{{ $individual_open }}} open)</p>
      @elseif ($shifts_total === 0)
      No open shifts, all shifts are maintenance.
      @else
      <p>Total shifts: {{{ $shifts_total }}}, {{{ $shifts->count() - $shifts_total }}} maintenance shifts, individual shifts: {{{ $individual }}}, all shifts filled</p>
      @endif
      <p class="hidden-print">Download your shifts as calendar file:&ensp;{{ link_to("/beamtimes/$beamtime->id/ics", 'iCal', ['class' => 'btn btn-success btn-xs']) }}</p>
    </div>
    @else
    <h3 class="text-danger">Beamtime not found!</h3>
    @endif
    @endif  {{-- end of check if beamtime contains shifts --}}
</div>
@stop

