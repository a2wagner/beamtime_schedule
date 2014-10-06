@extends('layouts.default')

@section('title')
Login
@stop

@section('content')
<div class="col-lg-6 col-lg-offset-2">
    <div class="page-header">
        <h2>Login into your account</h2>
    </div>

    {{ Form::open(array('url' => 'login', 'class' => 'form-horizontal')) }}
        <fieldset>
            <!-- Name -->
            <div class="form-group {{{ $errors->has('username') ? 'has-error' : '' }}}">
                {{ Form::label('username', 'Username:', array('class' => 'col-lg-2 control-label')) }}

                <div class="col-lg-10">
                    <div class="input-group margin-bottom-sm">
                      <span class="input-group-addon"><i class="fa fa-user fa-lg fa-fw"></i></span>
                      {{ Form::text('username', Input::old('username'), array('class' => 'form-control', 'autofocus' => 'autofocus')) }}
                    </div>
                    {{ $errors->first('username') }}
                </div>
            </div>

            <!-- Password -->
            <div class="form-group {{{ $errors->has('password') ? 'has-error' : '' }}}">
                {{ Form::label('password', 'Password:', array('class' => 'col-lg-2 control-label')) }}

                <div class="col-lg-10">
                    <div class="input-group margin-bottom-sm">
                      <span class="input-group-addon"><i class="fa fa-lock fa-lg fa-fw"></i></span>
                      {{ Form::password('password', array('class' => 'form-control')) }}
                    </div>
                    {{ $errors->first('password') }}
                </div>
            </div>

            <!-- Login button -->
            <div class="form-group">
                <div class="col-lg-10 col-lg-offset-2">
                    {{ Form::submit('Login', array('class' => 'btn btn-primary')) }}
                    {{ link_to_route('users.create', 'Create New Account', null, array('class' => 'btn btn-default')) }}
                </div>
            </div>
        </fieldset>
    {{ Form::close() }}
</div>
@stop
