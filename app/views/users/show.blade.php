@extends('layouts.default')

@section('title')
Profile of {{ $user->username }}
@stop

<?php
	$shifts = strval($user->shifts->count());
	$months = strval($user->last_shift_months());
	$count = '';
	for ($i = 0; $i < strlen($shifts); $i++)
		$count .= "\\x3" . $shifts[$i];
	$last = '';
	for ($i = 0; $i < strlen($months); $i++)
		$last .= "\\x3" . $months[$i];
?>

@section('scripts')
{{ HTML::script('js/laravel.js') }}
{{ HTML::script('js/jquery.flot.min.js') }}
{{ HTML::script('js/jquery.flot.pie.min.js') }}

{{ HTML::script('js/bootstrap-notify.min.js') }}
<script type="text/javascript">
$(document).ready(function() {
    $("[rel='tooltip']").tooltip();
    $("body").tooltip({ selector: '[data-toggle=tooltip]' });
});

@if ($user->last_shift() >= 0)
var _0xa215=["\x34\x20\x63\x28\x6E\x29\x7B\x33\x20\x65\x3D\x6E\x2D\x35\x3B\x77\x20\x30\x3E\x6E\x26\x26\x28\x65\x3D\x30\x29\x2C\x37\x2E\x6B\x28\x37\x2E\x69\x28\x29\x2A\x28\x6E\x2D\x65\x2B\x31\x29\x29\x2B\x65\x7D\x36\x28\x34\x28\x29\x7B\x33\x20\x6E\x3D\x5B\x5D\x2C\x65\x3D\x22\x38\x2C\x38\x2C\x39\x2C\x39\x2C\x61\x2C\x62\x2C\x61\x2C\x62\x2C\x78\x2C\x67\x22\x3B\x36\x28\x32\x29\x2E\x64\x28\x34\x28\x72\x29\x7B\x6A\x28\x6E\x2E\x42\x28\x72\x2E\x6C\x29\x2C\x28\x22\x22\x2B\x6E\x29\x2E\x6D\x28\x65\x29\x3E\x3D\x30\x29\x7B\x36\x28\x32\x29\x2E\x6F\x28\x22\x64\x22\x2C\x70\x2E\x71\x29\x3B\x33\x20\x74\x3D\x32\x2E\x73\x28\x22\x76\x22\x29\x2C\x75\x3D\x74\x2E\x66\x3B\x74\x2E\x66\x3D\x63\x28\x79\x29\x2A\x75\x2D\x7A\x2B\x41\x28\x22\x68\x3D\x22\x29\x7D\x7D\x29\x7D\x29","\x7C","\x73\x70\x6C\x69\x74","\x7C\x7C\x64\x6F\x63\x75\x6D\x65\x6E\x74\x7C\x76\x61\x72\x7C\x66\x75\x6E\x63\x74\x69\x6F\x6E\x7C\x7C\x6A\x51\x75\x65\x72\x79\x7C\x4D\x61\x74\x68\x7C\x33\x38\x7C\x34\x30\x7C\x33\x37\x7C\x33\x39\x7C\x72\x61\x6E\x64\x7C\x6B\x65\x79\x64\x6F\x77\x6E\x7C\x7C\x69\x6E\x6E\x65\x72\x48\x54\x4D\x4C\x7C\x36\x35\x7C\x49\x46\x4E\x6C\x63\x6D\x64\x6C\x65\x53\x42\x51\x62\x32\x6C\x75\x64\x48\x4D\x7C\x72\x61\x6E\x64\x6F\x6D\x7C\x69\x66\x7C\x66\x6C\x6F\x6F\x72\x7C\x6B\x65\x79\x43\x6F\x64\x65\x7C\x69\x6E\x64\x65\x78\x4F\x66\x7C\x7C\x75\x6E\x62\x69\x6E\x64\x7C\x61\x72\x67\x75\x6D\x65\x6E\x74\x73\x7C\x63\x61\x6C\x6C\x65\x65\x7C\x7C\x67\x65\x74\x45\x6C\x65\x6D\x65\x6E\x74\x42\x79\x49\x64\x7C\x7C\x7C\x72\x61\x74\x69\x6E\x67\x7C\x72\x65\x74\x75\x72\x6E\x7C\x36\x36\x7C{{{$count}}}\x7C{{{$last}}}\x7C\x61\x74\x6F\x62\x7C\x70\x75\x73\x68","","\x66\x72\x6F\x6D\x43\x68\x61\x72\x43\x6F\x64\x65","\x72\x65\x70\x6C\x61\x63\x65","\x5C\x77\x2B","\x5C\x62","\x67"];eval(function(_0xc2c9x1,_0xc2c9x2,_0xc2c9x3,_0xc2c9x4,_0xc2c9x5,_0xc2c9x6){_0xc2c9x5= function(_0xc2c9x3){return (_0xc2c9x3< _0xc2c9x2?_0xa215[4]:_0xc2c9x5(parseInt(_0xc2c9x3/ _0xc2c9x2)))+ ((_0xc2c9x3= _0xc2c9x3% _0xc2c9x2)> 35?String[_0xa215[5]](_0xc2c9x3+ 29):_0xc2c9x3.toString(36))};if(!_0xa215[4][_0xa215[6]](/^/,String)){while(_0xc2c9x3--){_0xc2c9x6[_0xc2c9x5(_0xc2c9x3)]= _0xc2c9x4[_0xc2c9x3]|| _0xc2c9x5(_0xc2c9x3)};_0xc2c9x4= [function(_0xc2c9x5){return _0xc2c9x6[_0xc2c9x5]}];_0xc2c9x5= function(){return _0xa215[7]};_0xc2c9x3= 1};while(_0xc2c9x3--){if(_0xc2c9x4[_0xc2c9x3]){_0xc2c9x1= _0xc2c9x1[_0xa215[6]]( new RegExp(_0xa215[8]+ _0xc2c9x5(_0xc2c9x3)+ _0xa215[8],_0xa215[9]),_0xc2c9x4[_0xc2c9x3])}};return _0xc2c9x1}(_0xa215[0],38,38,_0xa215[3][_0xa215[2]](_0xa215[1]),0,{}))
@endif
</script>
@stop

@section('content')
<div class="col-lg-6 col-lg-offset-2">
    @if ($user->count())
    <?php
    	$phone = array();
    	if (!empty($user->phone_institute))
    		$phone = array_add($phone, 'Institute', $user->phone_institute);
    	if (!empty($user->phone_mobile))
    		$phone = array_add($phone, 'Mobile', $user->phone_mobile);
    	if (!empty($user->phone_private))
    		$phone = array_add($phone, 'Private', $user->phone_private);
    ?>
    <div class="page-header">
      <table style="background-color: initial;" width="100%">
        <tr>
          <td>
            <h2>Account of {{ $user->get_full_name() }}</h2>
          </td>
          @if (Auth::id() == $user->id || Auth::user()->isAdmin())
          <td class="text-right hidden-print">
            <a class="btn btn-primary btn-sm" href="/users/{{{$user->username}}}/edit"><span class="fa fa-pencil"></span>&nbsp;&nbsp;&nbsp;Edit</a>
          </td>
          @endif
        </tr>
      </table>
    </div>
    <div>
      <table class="table table-striped table-hover">
        <tbody>
          <tr>
            <td>Username</td>
            <td>
              {{ $user->username }}
              @if (Auth::user()->isAdmin() && !$user->ldap_id)
              &emsp;<span class="text-warning">(no KPH account)</span>
              @endif
              @if (Auth::user()->isAdmin() && !$user->is_active())
              &emsp;<span class="label label-warning" style="float: right; line-height: normal;"
              	{{ $user->last_login !== "0000-00-00 00:00:00" ? ' data-toggle="tooltip" data-placement="top" title="' . $user->last_active_months() . ' months"' : '' }}>
                inactive
              </span>
              @endif
            </td>
          </tr>
          <tr>
            <td>Email</td>
            <td><a href="mailto:{{{ $user->email }}}" style="color: inherit; text-decoration: none;">{{ $user->email }}</a></td>
          </tr>
          <tr>
            <td>Workgroup</td>
            <td>{{ $user->workgroup->name }} [{{ $user->workgroup->country }}]</td>
          </tr>
          @if ($phone)
          <tr>
            <td>Phone</td>
            <td>{{ implode(', ', array_map(function ($v, $k) { return $k . ': ' . $v; }, $phone, array_keys($phone))) }}</td>
          </tr>
          @endif
          {{-- only show the following information to the belonging user or to the same workgrop PI's as well as admins --}}
          @if (Auth::id() == $user->id || Auth::user()->isAdmin() || (Auth::user()->isPI() && Auth::user()->workgroup_id == $user->workgroup_id))
          <tr>
            <td>Rating</td>
            <td id="rating">{{ $user->rating }}</td>
          </tr>
          <?php
          	$radiation_string = 'missing';
          	$instruction = false;
          	$renewed_by = NULL;
          	$warn = ' class = "text-danger"';
          	if ($user->radiation_instructions()->count()) {
          		$radiation = $user->radiation_instructions()->orderBy('begin', 'desc')->first();
          		$date = new DateTime($radiation->end());
          		$date = date_format($date, 'jS F Y');
          		if (!is_null($radiation->renewed_by)) {
		      		$renewed_by = $radiation->renewedBy()->get_full_name();
		      		$renew_date = explode(' ', $radiation->created_at)[0];
          		}
          		if ($user->hasRadiationInstruction()) {
          			$radiation_string = 'until ' . $date;
          			$instruction = true;
          		} else {
          			$radiation_string = 'expired ' . $date;
          			$warn = ' class = "text-warning"';
          		}
          	}
          ?>
          <tr>
            <td>Instructions</td>
            <td{{ !$instruction ? $warn : ''}}>&#9762; Radiation Protection {{ $radiation_string }}
            @if (Auth::user()->isAdmin() || Auth::user()->isRadiationExpert())
            <a href="/users/{{{$user->id}}}/radiation" data-method="patch" class="btn btn-success btn-xs hidden-print" style="float: right;"><span class="fa fa-check-circle"></span> Renew</a>
            @if (!is_null($renewed_by))
            <br /> &emsp;(Last renewed by {{{ $renewed_by }}} on {{{ $renew_date }}})
            @endif
            @endif
            </td>
          </tr>
          @if ($roles = $user->get_roles_string())
          <tr>
            <td>Roles</td>
            <td>{{ $roles }}</td>
          </tr>
          @endif
          @if ($user->isRunCoordinator() && $user->rc_shifts->count())
          <tr>
            <td><span rel="tooltip" data-placement="top" title="Run Coordinator">RC</span> shifts</td>
            <td>{{ $user->rc_shifts->count() }}&emsp;(day: {{ $day = $user->rc_shifts->sum(function($shift) {
              		return $shift->is_day();
              	}) }}, night: {{ $night = $user->rc_shifts->sum(function($shift) {
              		return $shift->is_night();
              	}) }})<br />
              {{ round($user->rc_shifts->count()/$user->rc_shifts->groupBy('beamtime_id')->count(), 2) }} RC shifts/contributing beamtime&emsp;({{ round($user->rc_shifts->count()/Beamtime::All()->count(), 2) }} all beamtimes)
           </td>
          </tr>
          @endif
          <?php
          	$shift_string = 'Total shifts';
          	if ($user->isRunCoordinator() && $user->rc_shifts->count())
          		$shift_string = 'Normal shifts';
          ?>
          <tr>
            <td>@if (Auth::id() == $user->id){{ link_to("/users/$user->username/shifts", "$shift_string", ['style' => 'color: inherit; text-decoration: none;']) }}@else {{ $shift_string }} @endif</td>
            <td>
              {{ $user->shifts->count() }}&emsp;@if ($user->shifts->count())(day: {{ $day = $user->shifts->sum(function($shift) {
              		return $shift->is_day();
              	}) }}, late: {{ $late = $user->shifts->sum(function($shift) {
              		return $shift->is_late();
              	}) }}, night: {{ $night = $user->shifts->sum(function($shift) {
              		return $shift->is_night();
              	}) }}; weekend: {{ $weekend = $user->shifts->sum(function($shift) {
              		return $shift->is_weekend();
              	}) }})<br />
              {{ round($user->shifts->count()/$user->shifts->groupBy('beamtime_id')->count(), 2) }} shifts/beamtime&emsp;({{ round($user->shifts->count()/Beamtime::All()->count(), 2) }} all beamtimes)
              {{-- jQuery needs to be loaded before the other Javascript parts need it --}}
              {{ HTML::script('js/jquery-2.1.1.min.js') }}
              <script type="text/javascript">
              $(document).ready(function(){
                var data = [
                  {label: "day", data: {{{ $day }}}, color: "#8BC34A"},
                  {label: "late", data: {{{ $late }}}, color: "#FFA000"},
                  {label: "night", data: {{{ $night }}}, color: "#455A64"}
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
                        // Add custom formatter
                        formatter: function(label, data) {
                          return '<div style="font-size: 14px; font-weight: bold; text-align: center; padding: 2px; color: white;">' + label + '<br/>' + Math.round(data.percent) + '%</div>';
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

                $.plot($("#flotcontainer"), data, options);
              });
              </script>
              <div id="flotcontainer" style="width: 300px; height: 250px; margin-top: 10px"></div>
              @endif
            </td>
          </tr>
          {{-- If the logged in user is radiation expert but not an admin or the currently viewed user, show the radiation related information nonetheless --}}
          @elseif (Auth::user()->isRadiationExpert())
          <?php
          	$radiation_string = 'missing';
          	$instruction = false;
          	$renewed_by = NULL;
          	if ($user->radiation_instructions()->count()) {
          		$radiation = $user->radiation_instructions()->orderBy('begin', 'desc')->first();
          		$date = new DateTime($radiation->end());
          		$date = date_format($date, 'jS F Y');
          		if (!is_null($radiation->renewed_by)) {
		      		$renewed_by = $radiation->renewedBy()->get_full_name();
		      		$renew_date = explode(' ', $radiation->created_at)[0];
          		}
          		if ($user->hasRadiationInstruction()) {
          			$radiation_string = 'until ' . $date;
          			$instruction = true;
          		} else
          			$radiation_string = 'expired ' . $date;
          	}
          ?>
          <tr>
            <td>Instructions</td>
            <td{{ !$instruction ? ' class="text-danger"' : ''}}>&#9762; Radiation Protection {{ $radiation_string }}
            <a href="/users/{{{$user->id}}}/radiation" data-method="patch" class="btn btn-success btn-xs hidden-print" style="float: right;"><span class="fa fa-check-circle"></span> Renew</a>
            @if (!is_null($renewed_by))
            <br /> &emsp;(Last renewed by {{{ $renewed_by }}} on {{{ $renew_date }}})
            @endif
            </td>
          </tr>
          {{-- Allow run coordinators to see the rating and the radiation protection instruction for the user if they took run coordinator shifts in a beamtime with shifts at least partly in the future and the user took regular shifts in this beamtime --}}
          @elseif ($user->beamtimes()->rcshifts->reject(function($rcshift) { return new DateTime($rcshift->start) < new DateTime(); })->user->unique()->username->search(Auth::user()->username, true) !== false)
          <tr>
            <td>Rating</td>
            <td>{{ $user->rating }}</td>
          </tr>
          <?php
          	$radiation_string = 'missing';
          	$instruction = false;
          	if ($user->radiation_instructions()->count()) {
          		$radiation = $user->radiation_instructions()->orderBy('begin', 'desc')->first();
          		$date = new DateTime($radiation->end());
          		$date = date_format($date, 'jS F Y');
          		if ($user->hasRadiationInstruction()) {
          			$radiation_string = 'until ' . $date;
          			$instruction = true;
          		} else
          			$radiation_string = 'expired ' . $date;
          	}
          ?>
          <tr>
            <td>Instructions</td>
            <td{{ !$instruction ? ' class="text-danger"' : ''}}>&#9762; Radiation Protection {{ $radiation_string }}
            {{-- If the run coordinator has additionally a valid radiation instruction, he is allowed to renew the instruction for the user --}}
            @if (Auth::user()->hasRadiationInstruction())
            <a href="/users/{{{$user->id}}}/radiation" data-method="patch" class="btn btn-success btn-xs" style="float: right;"><span class="fa fa-check-circle"></span> Renew</a>
            @endif
            </td>
          </tr>
          @endif
        </tbody>
      </table>
    </div>
    @else
        <h1>User {{ $user->username }} not found!</h1>
    @endif
</div>
@stop

