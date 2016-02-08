@extends('layouts.default')

@section('title')
User Management
@stop

@section('styles')
@parent
.nounderline {text-decoration: none !important;}
@stop

@section('scripts')
{{ HTML::script('js/laravel.js') }}
@stop

@section('content')
@if (Auth::user()->isAdmin() || Auth::user()->isPI())
<div class="col-lg-10 col-lg-offset-1">
    <div class="page-header">
        <h2>User Management</h2>
    </div>

    <h3>What do you want to do?</h3>
    <ul>
      @if (Auth::user()->isAdmin())
      <li>{{ link_to("/users/admins", "Manage admins") }}</li>
      @endif
      <li>{{ link_to("/users/run_coordinators", "Manage run coordinators") }}</li>
      <li>{{ link_to("/users/principle_investigators", "Manage principle investigators") }}</li>
      @if (Auth::user()->isAdmin() || Auth::user()->hasRadiationInstruction())
      <li>{{ link_to("/users/radiation", "Renew Radiation Instruction") }}</li>
      @endif
      <li>{{ link_to("/users/enable", "Enable new users") }}</li>
      <li>{{ link_to("/users", "Go to users overview") }}</li>
    </ul>
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

