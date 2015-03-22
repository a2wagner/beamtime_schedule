@extends('layouts.default')

@section('title')
Profile of {{ $user->username }}
@stop

@section('content')
<div class="col-lg-6 col-lg-offset-2">
    @if ($user->count())
    <?php
    	$phone = array();
    	if ($user->phone_institute !== '')
    		$phone = array_add($phone, 'Institute', $user->phone_institute);
    	if ($user->phone_mobile !== '')
    		$phone = array_add($phone, 'Mobile', $user->phone_mobile);
    	if ($user->phone_private !== '')
    		$phone = array_add($phone, 'Private', $user->phone_private);
    ?>
    <div class="page-header">
        <h2>Account of {{ $user->first_name." ".$user->last_name }}</h2>
    </div>
    <div>
      <table class="table table-striped table-hover">
        <tbody>
          <tr>
            <td>Username</td>
            <td>{{ $user->username }}</td>
          </tr>
          <tr>
            <td>Email</td>
            <td>{{ $user->email }}</td>
          </tr>
          <tr>
            <td>Workgroup</td>
            <td>{{ $user->workgroup->name }} [{{ $user->workgroup->country }}]</td>
          </tr>
          @if ($phone)
          <tr>
            <td>Phone</td>
            <td>{{ implode(', ', array_map(function ($v, $k) { return $k . ': ' . $v; }, $phone, array_keys($phone))) }}</td>
          </tr>
          @endif
          {{-- only show the following information to the belonging user or to the same workgrop PI's as well as admins --}}
          @if (Auth::id() == $user->id || Auth::user()->isAdmin() || (Auth::user()->isPI() && Auth::user()->workgroup_id == $user->workgroup_id))
          <tr>
            <td>Rating</td>
            <td>{{ $user->rating }}</td>
          </tr>
          <tr>
            <td>Total shifts</td>
            <td>
              {{ $user->shifts->count() }}&emsp;@if ($user->shifts->count()) (day: {{ $user->shifts->sum(function($shift) { return $shift->is_day(); }) }}, late: {{ $user->shifts->sum(function($shift) { return $shift->is_late(); }) }}, night: {{ $user->shifts->sum(function($shift) { return $shift->is_night(); }) }}) @endif
            </td>
          </tr>
          @endif
        </tbody>
      </table>
    </div>
    @else
        <h1>User {{ $user->username }} not found!</h1>
    @endif
</div>
@stop

