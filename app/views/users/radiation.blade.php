@extends('layouts.default')

@section('title')
Renew Radiation Protection Instruction
@stop

@section('css')
{{ HTML::style('css/datepicker.css') }}
@parent
@stop

@section('styles')
@parent
.nounderline {text-decoration: none !important;}
.input-date {display: none;}

.datepicker.dropdown-menu {
  top: 0;
  left: 0;
  padding: 4px;
  margin-top: 1px;
}

.fixed-text-input {
  position: absolute;
  display: block;
  right: 70px;
  top: 10px;
  z-index: 3;
}
@stop

@section('scripts')
{{ HTML::script('js/bootstrap-datepicker.js') }}

<script type="text/javascript">
$(".change-date").on("click", function() {
  $(".input-date").hide();
  $(".change-date").show();

  var fields = $(".input-date");
  var buttons = $(".change-date");

  var idx = buttons.index($(this));  // index of the clicked checkbox element

  $(".input-date:eq("+idx+")").show();
  $(".change-date:eq("+idx+")").hide();

  /* date-picker */
  var nowTemp = new Date();
  var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);

  var begin = $(".input-date:eq("+idx+")").datepicker({
    weekStart: 1  //0 sunday, 1 monday ...
  }).on('changeDate', function(ev) {
    begin.hide();
  }).data('datepicker');
  $(".input-date:eq("+idx+")")[0].focus();
});
</script>
@stop

@section('content')
@if (Auth::user()->isRadiationExpert() || Auth::user()->isRunCoordinator())
<div class="col-lg-10 col-lg-offset-1">
    <div class="page-header">
        <h2>Renew Radiation Protection Instruction</h2>
    </div>

    @if ($users->count())
    @if (Auth::user()->isRadiationExpert())<h3>Registered Shift Workers</h3>@endif
    <div class="table-responsive">
    <table class="table table-striped table-hover">
      <thead>
        <tr>
          <th>Name {{ link_to('users/radiation?sort=asc', '', ['class' => 'nounderline fa fa-sort-alpha-asc hidden-print']) }} {{ link_to('users/radiation?sort=desc', '', ['class' => 'nounderline fa fa-sort-alpha-desc hidden-print']) }}</th>
          <th>Workgroup</th>
          <th>Radiation Instruction {{ link_to('users/radiation', '', ['class' => 'nounderline fa fa-sort-amount-asc hidden-print']) }}</th>
          <th></th>
          <th class="text-center">Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($users as $user)
        <?php
        	$radiation_string = 'missing';
        	$instruction = false;
          	$warn = ' class = "text-danger"';
        	if ($user->radiation_instructions()->count()) {
        		$radiation = $user->radiation_instructions()->orderBy('begin', 'desc')->first();
        		$date = new DateTime($radiation->end());
        		$date = date_format($date, 'jS F Y');
        		if ($user->hasRadiationInstruction()) {
        			$radiation_string = 'valid until ' . $date;
        			$instruction = true;
        		} else {
        			$radiation_string = 'expired ' . $date;
        			$warn = ' class = "text-warning"';
        		}
        	}
        ?>
        <tr>
          {{ Form::open(['url' => '/users/'.$user->id.'/radiation', 'method' => 'PATCH', 'id' => $user->id,'class' => 'form-horizontal', 'role' => 'form']) }}
          <td>{{ link_to("/users/{$user->username}", $user->first_name." ".$user->last_name) }}</td>
          <td>{{ $user->workgroup->name }}</td>
          <td{{ !$instruction ? $warn : ''}}>{{ $radiation_string }}</td>
          <td>
            <a id="button-{{{$user->id}}}" class="change-date btn btn-default btn-xs"><span class="fa fa-calendar"></span> Change Date</a>
              {{ Form::text('date', date('Y-m-d'), array('class' => 'input-sm form-control input-date datepicker', 'size' => '10', 'id' => 'date-'.$user->id, 'data-date-format' => 'yyyy-mm-dd')) }}
          </td>
          <td class="text-center">
              {{--<a href="/users/{{{$user->id}}}/radiation" data-method="patch" class="btn btn-success btn-xs"><span class="fa fa-check-circle"></span> Renew</a>--}}
              <button type="submit" class="btn btn-success btn-xs">
                <i class="fa fa-check-circle"></i> Renew
              </button>
          </td>
          {{ Form::close() }}
        </tr>
        @endforeach
      </tbody>
    </table>
    </div>
    @else
    <h3 class="text-danger">No users found where you're allowed to renew the Radiation Protection Instruction.</h3>
    @endif
</div>
@else
<div class="col-lg-10 col-lg-offset-1">
    <div class="page-header">
        <h2>User Management</h2>
    </div>

    <h3 class="text-warning">It seems you're not on the page you've been looking for. You may want to go to an {{ link_to("/users", "overview of all registered shift workers") }} instead.</h3>
</div>
@endif
@stop

