@extends('layouts.default')

@section('title')
Change Passwords
@stop

@section('styles')
@parent
.nounderline {text-decoration: none !important;}
@stop

@section('scripts')
{{ HTML::script('js/laravel.js') }}
@stop

@section('content')
@if (Auth::user()->isAdmin())
<div class="col-lg-10 col-lg-offset-1">
    <div class="page-header">
        <h2>Registered Users</h2>
    </div>

    @if (!empty($users))
    <div class="table-responsive">
    <table class="table table-striped table-hover">
      <thead>
        <tr>
          <th>Name {{ link_to('users/password_reset?sort=asc', '', ['class' => 'nounderline fa fa-sort-alpha-asc hidden-print']) }} {{ link_to('users/password_reset?sort=desc', '', ['class' => 'nounderline fa fa-sort-alpha-desc hidden-print']) }}</th>
          <th>Workgroup</th>
          <th>Email</th>
          <th>Registered</th>
          <th>Last Updated</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($users as $user)
        <tr>
          <td>{{ link_to("/users/{$user->username}", $user->get_full_name()) }}</td>
          <td>{{ $user->workgroup->name }}</td>
          <td>{{ $user->email }}</td>
          <?php
          	$registered = substr($user->created_at, 0, strrpos($user->created_at, ":"));
          	$updated = substr($user->updated_at, 0, strrpos($user->updated_at, ":"));
          ?>
          <td>{{ $registered }}</td>
          <td>{{ $updated }}</td>
          <td>
            <a href="/users/password/{{{$user->id}}}" class="btn btn-primary btn-xs"><span class="fa fa-pencil"></span> Change Password</a>
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

