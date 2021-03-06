@extends('layouts.default')

@section('title')
Create New Profile
@stop

@section('styles')
@parent
.form-group.required .control-label:after {
    font-family: 'FontAwesome';
    font-weight: normal;
    font-size: 7px;
    margin-left: 1px;
    //position: relative;
    //top: -8px;
    vertical-align: super;
    content: "\f069";
    color: red;
}
@stop

@section('content')
<div class="col-lg-6 col-lg-offset-2">
    <div class="page-header">
        <h2>Create a new account</h2>
    </div>

    {{ Form::open(['route' => 'users.store', 'class' => 'form-horizontal']) }}
        <fieldset>
            <div class="form-group required {{{ $errors->has('first_name') ? 'has-error has-feedback' : '' }}}">
                {{ Form::label('first_name', 'First&nbsp;name: ', array('class' => 'col-lg-3 control-label')) }}
                <div class="col-lg-9">
                    {{ Form::text('first_name', Input::old('first_name'), array('class' => 'form-control', 'id' => 'inputError2', 'autofocus' => 'autofocus')) }}
                    {{ $errors->has('first_name') ? '<span class="glyphicon glyphicon-remove form-control-feedback"></span>' : '' }}
                    <p class="help-block">{{ $errors->first('first_name') }}</p>
                </div>
            </div>
            <div class="form-group required {{{ $errors->has('last_name') ? 'has-error has-feedback' : '' }}}">
                {{ Form::label('last_name', 'Last&nbsp;name: ', array('class' => 'col-lg-3 control-label')) }}
                <div class="col-lg-9">
                    {{ Form::text('last_name', Input::old('last_name'), array('class' => 'form-control', 'id' => 'inputError2')) }}
                    {{ $errors->has('last_name') ? '<span class="glyphicon glyphicon-remove form-control-feedback"></span>' : '' }}
                    <p class="help-block">{{ $errors->first('last_name') }}</p>
                </div>
            </div>
            <div class="form-group required {{{ $errors->has('username') ? 'has-error has-feedback' : '' }}}">
                {{ Form::label('username', 'Username: ', array('class' => 'col-lg-3 control-label')) }}
                <div class="col-lg-9">
                    {{ Form::text('username', Input::old('username'), array('class' => 'form-control', 'id' => 'inputError2')) }}
                    {{ $errors->has('username') ? '<span class="glyphicon glyphicon-remove form-control-feedback"></span>' : '' }}
                    <p class="help-block">{{ $errors->first('username') }}</p>
                </div>
            </div>

            <div class="form-group required {{{ $errors->has('email') ? 'has-error has-feedback' : '' }}}">
                {{ Form::label('email', 'Email: ', array('class' => 'col-lg-3 control-label')) }}
                <div class="col-lg-9">
                    {{ Form::email('email', Input::old('email'), array('class' => 'form-control', 'id' => 'inputError2')) }}
                    {{ $errors->has('email') ? '<span class="glyphicon glyphicon-remove form-control-feedback"></span>' : '' }}
                    <p class="help-block">{{ $errors->first('email') }}</p>
                </div>
            </div>

            <div class="form-group required {{{ $errors->has('workgroup_id') ? 'has-error has-feedback' : '' }}}">
                {{ Form::label('workgroup_id', 'Workgroup: ', array('class' => 'col-lg-3 control-label')) }}
                <div class="col-lg-9">
                    {{ Form::select('workgroup_id', $workgroups, Input::old('workgroup_id'), ['class' => 'form-control', 'id' => 'inputError2']) }}
                    {{ $errors->has('workgroup_id') ? '<span class="glyphicon glyphicon-remove form-control-feedback"></span>' : '' }}
                    <p class="help-block">{{ $errors->first('workgroup_id') }}</p>
                </div>
            </div>

            <div class="form-group {{{ $errors->has('phone_institute') ? 'has-error has-feedback' : '' }}}">
                {{ Form::label('phone_institute', 'Phone institute: ', array('class' => 'col-lg-3 control-label')) }}
                <div class="col-lg-9">
                    {{ Form::text('phone_institute', Input::old('phone_institute'), array('class' => 'form-control', 'id' => 'inputError2')) }}
                    {{ $errors->has('phone_institute') ? '<span class="glyphicon glyphicon-remove form-control-feedback"></span>' : '' }}
                    <p class="help-block">{{ $errors->first('phone_institute') }}</p>
                </div>
            </div>
            <div class="form-group {{{ $errors->has('phone_private') ? 'has-error has-feedback' : '' }}}">
                {{ Form::label('phone_private', 'Phone private: ', array('class' => 'col-lg-3 control-label')) }}
                <div class="col-lg-9">
                    {{ Form::text('phone_private', Input::old('phone_private'), array('class' => 'form-control', 'id' => 'inputError2')) }}
                    {{ $errors->has('phone_private') ? '<span class="glyphicon glyphicon-remove form-control-feedback"></span>' : '' }}
                    <p class="help-block">{{ $errors->first('phone_private') }}</p>
                </div>
            </div>
            <div class="form-group {{{ $errors->has('phone_mobile') ? 'has-error has-feedback' : '' }}}">
                {{ Form::label('phone_mobile', 'Phone mobile: ', array('class' => 'col-lg-3 control-label')) }}
                <div class="col-lg-9">
                    {{ Form::text('phone_mobile', Input::old('phone_mobile'), array('class' => 'form-control', 'id' => 'inputError2')) }}
                    {{ $errors->has('phone_mobile') ? '<span class="glyphicon glyphicon-remove form-control-feedback"></span>' : '' }}
                    <p class="help-block">{{ $errors->first('phone_mobile') }}</p>
                </div>
            </div>

            <div class="form-group required {{{ $errors->has('rating') ? 'has-error has-feedback' : '' }}}">
                {{ Form::label('rating', 'Rating: ', array('class' => 'col-lg-3 control-label')) }}
                <div class="col-lg-6">
                    {{ Form::select('rating', array(
                        '1' => '1&ensp;&mdash;&ensp;New/Inexperienced', 
                        '2' => '2&ensp;&mdash;&ensp;Basic Knowledge', 
                        '3' => '3&ensp;&mdash;&ensp;Experienced', 
                        '4' => '4&ensp;&mdash;&ensp;Expert'), '1', ['class' => 'form-control', 'id' => 'inputError2']) }}
                    {{ $errors->has('rating') ? '<span class="glyphicon glyphicon-remove form-control-feedback"></span>' : '' }}
                    <p class="help-block">{{ $errors->first('rating') }}</p>
                </div>
                <div class="col-lg-2">
                    <button type="button" class="btn btn-default" data-toggle="modal" data-target=".rating-modal-lg">Rating Help</button>
                    <?php $guide = new RatingGuide(); $guide->modal('rating-modal'); ?>
                </div>
            </div>

            <div class="form-group required {{{ $errors->has('password') ? 'has-error has-feedback' : '' }}}">
                {{ Form::label('password', 'Password: ', array('class' => 'col-lg-3 control-label')) }}
                <div class="col-lg-9">
                    {{ Form::password('password', array('class' => 'form-control', 'id' => 'inputError2')) }}
                    {{ $errors->has('password') ? '<span class="glyphicon glyphicon-remove form-control-feedback"></span>' : '' }}
                    <p class="help-block">{{ $errors->first('password') }}</p>
                </div>
            </div>
            <div class="form-group required {{{ $errors->has('password_confirmation') ? 'has-error has-feedback' : '' }}}">
                {{ Form::label('password_confirmation', 'Confirm Password: ', array('class' => 'col-lg-3 control-label')) }}
                <div class="col-lg-9">
                    {{ Form::password('password_confirmation', array('class' => 'form-control', 'id' => 'inputError2')) }}
                    {{ $errors->has('password_confirmation') ? '<span class="glyphicon glyphicon-remove form-control-feedback"></span>' : '' }}
                    <p class="help-block">{{ $errors->first('password_confirmation') }}</p>
                </div>
            </div>

            <div class="form-group">
                <div class="col-lg-9 col-lg-offset-3">
                    {{ Form::submit('Create Profile', array('class' => 'btn btn-primary')) }}
                    {{ Form::reset('Clear', array('class' => 'btn btn-default')) }}
                </div>
            </div>
        </fieldset>
    {{ Form::close() }}
</div>
@stop
