@extends('layouts.default')

@section('title')
Activate KPH Account
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
<div class="col-lg-6 col-lg-offset-2">
    <div class="page-header">
        <h2>Activate KPH Account</h2>
    </div>

    @if (!$none)
    {{ Form::open(['route' => array('users.activateKPHaccount'), 'method' => 'PATCH', 'class' => 'form-horizontal']) }}
        <fieldset>
            <div class="form-group {{{ $errors->has('user_id') ? 'has-error' : '' }}}">
                {{ Form::label('user_id', 'User: ', array('class' => 'col-lg-3 control-label')) }}
                <div class="col-lg-8">
    	            {{ Form::select('user_id', $users, !empty($selected) ? $selected->id : Input::old('user_id'), ['class' => 'form-control']) }}
                    {{ $errors->first('user_id') }}
                </div>
            </div>
            <div class="form-group {{{ $errors->has('username') ? 'has-error' : '' }}}">
                {{ Form::label('username', 'KPH username: ', array('class' => 'col-lg-3 control-label')) }}
                <div class="col-lg-8">
                    {{ Form::text('username', Input::old('username'), array('class' => 'form-control')) }}
                    {{ $errors->first('username') }}
                </div>
            </div>

            <div class="form-group">
                <div class="col-lg-8 col-lg-offset-3">
                    {{ Form::submit('Change to KPH account', array('class' => 'btn btn-primary')) }}
                    {{ Form::reset('Clear', array('class' => 'btn btn-default')) }}
                </div>
            </div>
        </fieldset>
    {{ Form::close() }}
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

