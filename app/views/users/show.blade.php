@extends('layouts.default')

@section('title')
Profile of {{ $user->username }}
@stop

@section('scripts')
{{ HTML::script('js/laravel.js') }}
{{ HTML::script('js/jquery.flot.min.js') }}
{{ HTML::script('js/jquery.flot.pie.min.js') }}
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
      <table width="100%">
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
            <td>{{ $user->username }}</td>
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
            @if (Auth::user()->isAdmin())
            <a href="/users/{{{$user->id}}}/radiation" data-method="patch" class="btn btn-success btn-xs" style="float: right;"><span class="fa fa-check-circle"></span> Renew</a>
            @endif
            </td>
          </tr>
          @if ($roles = $user->get_roles_string())
          <tr>
            <td>Roles</td>
            <td>{{ $roles }}</td>
          </tr>
          @endif
          <tr>
            <td>@if (Auth::id() == $user->id){{ link_to("/users/$user->username/shifts", "Total shifts", ['style' => 'color: inherit; text-decoration: none;']) }}@else Total shifts @endif</td>
            <td>
              {{ $user->shifts->count() }}&emsp;@if ($user->shifts->count())(day: {{ $day = $user->shifts->sum(function($shift) {
              		return $shift->is_day();
              	}) }}, late: {{ $late = $user->shifts->sum(function($shift) {
              		return $shift->is_late();
              	}) }}, night: {{ $night = $user->shifts->sum(function($shift) {
              		return $shift->is_night();
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

