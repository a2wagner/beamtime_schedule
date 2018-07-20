@extends('layouts.default')

@section('title')
Change User Password
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
        <h2>Change User Password</h2>
    </div>

    @if (!empty($users))
    {{ Form::open(['route' => array('users.password'), 'method' => 'PATCH', 'class' => 'form-horizontal']) }}
        <fieldset>
            <div class="form-group {{{ $errors->has('user_id') ? 'has-error' : '' }}}">
                {{ Form::label('user_id', 'User: ', array('class' => 'col-lg-3 control-label')) }}
                <div class="col-lg-8">
    	            {{ Form::select('user_id', $users, !empty($selected) ? $selected->id : Input::old('user_id'), ['class' => 'form-control']) }}
                    {{ $errors->first('user_id') }}
                </div>
            </div>
            <div class="form-group required {{{ $errors->has('password') ? 'has-error has-feedback' : '' }}}">
                {{ Form::label('password', 'New Password: ', array('class' => 'col-lg-3 control-label')) }}
                <div class="col-lg-8">
                    {{ Form::password('password', array('class' => 'form-control', 'id' => 'inputError2')) }}
                    {{ $errors->has('password') ? '<span class="glyphicon glyphicon-remove form-control-feedback"></span>' : '' }}
                    <p class="help-block">{{ $errors->first('password') }}</p>
                </div>
            </div>
            <div class="form-group required {{{ $errors->has('password_confirmation') ? 'has-error has-feedback' : '' }}}">
                {{ Form::label('password_confirmation', 'Repeat Password: ', array('class' => 'col-lg-3 control-label')) }}
                <div class="col-lg-8">
                    {{ Form::password('password_confirmation', array('class' => 'form-control', 'id' => 'inputError2')) }}
                    {{ $errors->has('password_confirmation') ? '<span class="glyphicon glyphicon-remove form-control-feedback"></span>' : '' }}
                    <p class="help-block">{{ $errors->first('password_confirmation') }}</p>
                </div>
            </div>

            <div class="form-group">
                <div class="col-lg-8 col-lg-offset-3">
                    {{ Form::submit('Change Password', array('class' => 'btn btn-primary')) }}
                    {{ Form::reset('Clear', array('class' => 'btn btn-default')) }}
                </div>
            </div>
        </fieldset>
    {{ Form::close() }}
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

