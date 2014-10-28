@extends('layouts.default')

@section('title')
Registered Users
@stop

@section('styles')
@parent
.nounderline {text-decoration: none !important;}
@stop

@section('scripts')
{{ HTML::script('js/laravel.js') }}
@stop

@section('content')
@if (Auth::user()->isAdmin)
<div class="col-lg-10 col-lg-offset-1">
    <div class="page-header">
        <h2>Registered Shift Workers</h2>
    </div>
    
    @if ($users->count())
    <div class="table-responsive">
    <table class="table table-striped table-hover">
      <thead>
        <tr>
          <th>Name</th>
          <th>Workgroup</th>
          <th>Email</th>
          <th class="text-center">Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($users as $user)
        <tr>
          {{-- show an extra icon in front of every other admin --}}
          <td>@if ($user->isAdmin) <span class="fa fa-user"></span> @endif {{ link_to("/users/{$user->username}", $user->first_name." ".$user->last_name) }}</td>
          <td>{{ $user->workgroup->name }}</td>
          <td>{{ $user->email }}</td>
          <td class="text-center">
            @if ($user->isAdmin)
            {{-- prevent the last remaining admin from removing his admin privileges --}}
            <?php
		    	$admins = $users->filter(function($user)
		    	{
		    		if ($user->isAdmin)
		    			return true;
		    	});
            ?>
            @if ($admins->count() > 1)
            <a href="/users/{{{$user->id}}}/admin" data-method="patch" class="btn btn-warning btn-xs"><span class="fa fa-times-circle"></span> Remove Admin</a>
            @endif
            @else
            <a href="/users/{{{$user->id}}}/admin" data-method="patch" class="btn btn-success btn-xs"><span class="fa fa-check-circle"></span> Add Admin</a>
            @endif
          </td>
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

