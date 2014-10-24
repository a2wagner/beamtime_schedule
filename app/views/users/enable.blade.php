@extends('layouts.default')

@section('title')
Enable Users
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
        <h2>Newly Registered Shift Workers</h2>
    </div>

    @if ($users->count())
    <div class="table-responsive">
    <table class="table table-striped table-hover">
      <thead>
        <tr>
          <th rowspan="2">Name</th>
          <th rowspan="2">Workgroup</th>
          <th rowspan="2">Email</th>
          <th colspan="3" class="text-center">Phone Number</th>
          <th rowspan="2" class="text-center">Action</th>
        </tr>
        <tr>
          <th>Institute</th>
          <th>Private</th>
          <th>Mobile</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($users as $user)
        <tr>
          <td>{{ link_to("/users/{$user->username}", $user->first_name." ".$user->last_name) }}</td>
          <td>{{ $user->workgroup->name }}</td>
          <td>{{ $user->email }}</td>
          <td>{{ $user->phone_institute }}</td>
          <td>{{ $user->phone_private }}</td>
          <td>{{ $user->phone_mobile }}</td>
          <td class="text-center">
            <a href="/users/{{{$user->id}}}/enable" data-method="patch" class="btn btn-primary btn-xs"><span class="fa fa-check"></span> Enable</a>
            <a href="/users/{{{$user->id}}}" data-method="delete" data-confirm="Are you sure you want to delete this user?" class="btn btn-danger btn-xs"><span class="fa fa-times"></span> Delete</a>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    </div>
    @else
    <h3 class="text-danger">There are no new users currently</h3>
    @endif
</div>
@endif
@stop

