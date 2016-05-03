@extends('layouts.default')

@section('title')
Non-KPH Account Users
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
        <h2>Users Without KPH Account</h2>
    </div>

    @if ($users->count())
    <div class="table-responsive">
    <table class="table table-striped table-hover">
      <thead>
        <tr>
          <th>Name</th>
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
            <a href="/users/kph/{{{$user->id}}}" class="btn btn-primary btn-xs"><span class="fa fa-check"></span> Make KPH</a>
            <a href="/users/{{{$user->id}}}" data-method="delete" data-confirm="Are you sure you want to delete this user?" class="btn btn-danger btn-xs"><span class="fa fa-times"></span> Delete</a>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    </div>
    @else
    <h3 class="text-info">There are no users without KPH account currently</h3>
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

