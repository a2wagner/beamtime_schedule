@extends('layouts.default')

@section('title')
Manage Radiation Experts
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
        <h2>Manage Radiation Experts</h2>
    </div>

    @if ($users->count())
    <h3>Registered Shift Workers</h3>
    <div class="table-responsive">
    <table class="table table-striped table-hover">
      <thead>
        <tr>
          <th>Name {{ link_to('users/radiation_experts?sort=asc', '', ['class' => 'nounderline fa fa-sort-alpha-asc hidden-print']) }} {{ link_to('users/radiation_experts?sort=desc', '', ['class' => 'nounderline fa fa-sort-alpha-desc hidden-print']) }}</th>
          <th>Workgroup</th>
          <th>Email</th>
          <th class="text-center">Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($users as $user)
        <tr>
          {{-- show an extra icon in front of every other admin --}}
          <td>@if ($user->isAdmin()) <span class="fa fa-user"></span> @endif {{ link_to("/users/{$user->username}", $user->first_name." ".$user->last_name) }}</td>
          <td>{{ $user->workgroup->name }}</td>
          <td>{{ $user->email }}</td>
          <td class="text-center">
            @if ($user->isRadiationExpert())
            <a href="/users/{{{$user->id}}}/re" data-method="patch" class="btn btn-warning btn-xs"><span class="fa fa-times-circle"></span> Remove Radiation Expert</a>
            @else
            <a href="/users/{{{$user->id}}}/re" data-method="patch" class="btn btn-success btn-xs"><span class="fa fa-check-circle"></span> Add Radiation Expert</a>
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

