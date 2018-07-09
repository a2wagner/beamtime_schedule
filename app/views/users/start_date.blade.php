@extends('layouts.default')

@section('title')
Manage User Start Dates
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
{{ HTML::script('js/laravel.js') }}

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
@if (Auth::user()->isAdmin())
<div class="col-lg-10 col-lg-offset-1">
    <div class="page-header">
        <h2>Manage User Start Dates</h2>
    </div>

    @if ($users->count())
    <h3>Registered Shift Workers</h3>
    <div class="table-responsive">
    <table class="table table-striped table-hover">
      <thead>
        <tr>
          <th>Name {{ link_to('users/start_date?sort=asc', '', ['class' => 'nounderline fa fa-sort-alpha-asc hidden-print']) }} {{ link_to('users/start_date?sort=desc', '', ['class' => 'nounderline fa fa-sort-alpha-desc hidden-print']) }}</th>
          <th>Workgroup</th>
	  <th class="text-center">Start Date</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($users as $user)
        <tr>
          {{-- show an extra icon in front of every other admin --}}
          <td>@if ($user->isAdmin()) <span class="fa fa-user"></span> @endif {{ link_to("/users/{$user->username}", $user->first_name." ".$user->last_name) }}</td>
          <td>{{ $user->workgroup->name }}</td>
	  <td class="text-center">
		{{ substr($user->start_date,0,7)}}
	  </td>	
          {{ Form::open(['url' => '/users/'.$user->id.'/start_date', 'method' => 'PATCH', 'id' => $user->id,'class' => 'form-horizontal', 'role' => 'form']) }}
	  <td>
	    <a id="button-{{{$user->id}}}" class="change-date btn btn-default btn-xs"><span class="fa fa-calendar"></span> Change Start Date</a>
	    {{ Form::text('date', date('Y-m'), array('class' => 'input-sm form-control input-date datepicker', 'size' => '5', 'id' => 'date-'.$user->id, 'data-date-format' => 'yyyy-mm-dd')) }}
          </td>
	  <td>
              {{--<a href="/users/{{{$user->id}}}/start_date" data-method="patch" class="btn btn-success btn-xs"><span class="fa fa-check-circle"></span> Update</a>--}}
              <button type="submit" class="btn btn-success btn-xs">
                <i class="fa fa-check-circle"></i> Update
	      </button>
          </td>
	  {{ Form::close() }}

        </tr>
        @endforeach
      </tbody>
    </table>
    </div>
    @else
    <h3 class="text-danger">No users found</h3>
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

