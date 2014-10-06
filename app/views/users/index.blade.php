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
<div class="col-lg-10 col-lg-offset-1">
    <div class="page-header">
        <h2>Registered Shift Workers</h2>
    </div>
    
    @if ($users->count())
    <div class="table-responsive">
    <table class="table table-striped table-hover">
      <thead>
        <tr>
          <th rowspan="2">Name @if (!Input::has('search')) {{ link_to('users?sort=asc', '', ['class' => 'nounderline fa fa-sort-alpha-asc']) }} {{ link_to('users?sort=desc', '', ['class' => 'nounderline fa fa-sort-alpha-desc']) }} @endif</th>
          <th rowspan="2">Workgroup</th>
          <th rowspan="2">Email</th>
          <th colspan="3" class="text-center">Phone Number</th>
          @if (Auth::user()->isAdmin)
          <th rowspan="2" class="text-center">Actions</th>
          @endif
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
          {{-- if the user is an admin, show an extra icon in front of every other admin --}}
          <td>@if (Auth::user()->isAdmin && $user->isAdmin) <span class="fa fa-user"></span> @endif {{ link_to("/users/{$user->username}", $user->first_name." ".$user->last_name) }}</td>
          <td>{{ $user->workgroup->name }}</td>
          <td>{{ $user->email }}</td>
          <td>{{ $user->phone_institute }}</td>
          <td>{{ $user->phone_private }}</td>
          <td>{{ $user->phone_mobile }}</td>
          @if (Auth::user()->isAdmin)
          <td class="text-center">
            <a class='btn btn-primary btn-xs' href="users/{{{$user->username}}}/edit"><span class="fa fa-pencil"></span> Edit</a> 
            <a href="/users/{{{$user->id}}}" data-method="delete" data-confirm="Are you sure to delete this user?" class="btn btn-danger btn-xs"><span class="fa fa-times"></span> Del</a> 
            <a class='btn btn-success btn-xs' href="#"><span class="fa fa-envelope"></span> Mail</a>
          </td>
          @endif
        </tr>
        @endforeach
      </tbody>
    </table>
    </div>
    {{ $users->links() }}
    @else
    <h3 class="text-danger">No users found</h3>
    @endif
</div>
@stop

