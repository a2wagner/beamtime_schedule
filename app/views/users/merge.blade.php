@extends('layouts.default')

@section('title')
Merge User Accounts
@stop

@section('scripts')
{{ HTML::script('js/laravel.js') }}
<script type="text/javascript">
$(document).ready(function() {
    //$("[rel='tooltip']").tooltip();
    $("body").tooltip({ selector: '[data-toggle=tooltip]' });

});
</script>
@stop

@section('content')
{{ Form::open(['route' => array('users.merge'), 'method' => 'PUT']) }}
<div class="col-lg-10 col-lg-offset-1">
    <div class="page-header">
        <h2>Registered Users</h2>
    </div>
    <h4>Choose user accounts for merging</h4>
    <p style="padding: 15px;">
      All registered users are shown below. Choose two user accounts in order to merge them.<br />
      Please note that only two accounts can be merged at a time and make sure to keep the KPH account!
    </p>
    <h4>Choose which account information and credentials should be kept</h4>
    <div style="padding: 1px 0 25px 15px;">
      <div class="radio">
        <label>
          <input type="radio" name="keep" id="radio1" value="earlier" checked="">
          Account registered <b>earlier</b>
        </label>
      </div>
      <div class="radio">
        <label>
          <input type="radio" name="keep" id="radio2" value="later">
          Account registered <b>later</b>
        </label>
      </div>
    </div>

    @if ($users->count())
    <div class="table-responsive">
    <table class="table table-striped table-hover">
      <thead>
        <tr>
          <th>Name @if (!Input::has('search')) {{ link_to('users/merge?sort=asc', '', ['class' => 'nounderline fa fa-sort-alpha-asc hidden-print']) }} {{ link_to('users/merge?sort=desc', '', ['class' => 'nounderline fa fa-sort-alpha-desc hidden-print']) }} @endif</th>
          <th>Workgroup</th>
          <th>Email</th>
          <th>Registered</th>
          <th>Last Updated</th>
          <th>Last Login</th>
          <th class="text-center">Merge</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($users as $user)
        <tr>
          {{-- show an extra icon in front of every other admin --}}
          <td>@if ($user->isAdmin()) <span class="fa fa-user hidden-print"></span> @endif {{ link_to("/users/{$user->username}", $user->get_full_name()) }}</td>
          <td>{{ $user->workgroup->short }}</td>
          <td>{{ $user->email }}</td>
          <?php
          	$registered = substr($user->created_at, 0, strrpos($user->created_at, ":"));
          	$updated = substr($user->updated_at, 0, strrpos($user->updated_at, ":"));
          	$last_login = substr($user->last_login, 0, strrpos($user->last_login, ":"));
          ?>
          <td>{{ $registered }}</td>
          <td>{{ $updated }}</td>
          <td>{{ $last_login }}</td>
          <td class="text-center">
            <div class="checkbox">
              <label>
                {{ Form::checkbox('merge[]', $user->id, false) }}
                <a rel="tooltip" data-toggle="tooltip" data-placement="top" data-original-title="Choose to merge" class="btn btn-default btn-xs"><span class="fa fa-compress"></span></a>
              </label>
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    </div>
    <div align="right">
      {{ Form::submit('Merge Accounts', array('class' => 'btn btn-primary')) }}
    </div>
    @else
    <h3 class="text-danger">No users found</h3>
    @endif
</div>
{{ Form::close() }}
@stop

