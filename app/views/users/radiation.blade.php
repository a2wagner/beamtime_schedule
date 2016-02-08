@extends('layouts.default')

@section('title')
Renew Radiation Protection Instruction
@stop

@section('styles')
@parent
.nounderline {text-decoration: none !important;}
@stop

@section('scripts')
{{ HTML::script('js/laravel.js') }}
@stop

@section('content')
@if (Auth::user()->isAdmin() || Auth::user()->isRunCoordinator())
<div class="col-lg-10 col-lg-offset-1">
    <div class="page-header">
        <h2>Renew Radiation Protection Instruction</h2>
    </div>

    @if ($users->count())
    <h3>Registered Shift Workers</h3>
    <div class="table-responsive">
    <table class="table table-striped table-hover">
      <thead>
        <tr>
          <th>Name</th>
          <th>Workgroup</th>
          <th>Radiation Instruction</th>
          <th class="text-center">Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($users as $user)
        <?php
        	$radiation_string = 'missing';
        	$instruction = false;
        	if ($user->radiation_instructions()->count()) {
        		$radiation = $user->radiation_instructions()->orderBy('begin', 'desc')->first();
        		$date = new DateTime($radiation->end());
        		$date = date_format($date, 'jS F Y');
        		if ($user->hasRadiationInstruction()) {
        			$radiation_string = 'valid until ' . $date;
        			$instruction = true;
        		} else
        			$radiation_string = 'expired ' . $date;
        	}
        ?>
        <tr>
          {{-- show an extra icon in front of every other admin --}}
          <td>{{ link_to("/users/{$user->username}", $user->first_name." ".$user->last_name) }}</td>
          <td>{{ $user->workgroup->name }}</td>
          <td{{ !$instruction ? ' class="text-danger"' : ''}}>{{ $radiation_string }}</td>
          <td class="text-center">
            <a href="/users/{{{$user->id}}}/radiation" data-method="patch" class="btn btn-success btn-xs"><span class="fa fa-check-circle"></span> Renew</a>
          </td>
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

