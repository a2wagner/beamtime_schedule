@extends('layouts.default')

@section('title')
Edit User {{ $user->username }}
@stop

@section('scripts')
<script type="text/javascript">
$(document).ready(function () {
    // Run this code only when the DOM (all elements) are ready

    $('form[name="delete"] input').on("click", function (e) {  // Find all <form>s with the name "delete", and bind a "submit" event handler
		e.preventDefault();  // Stop the form from submitting
		$("#formAlert").slideDown(400);  // Show the Alert
        $("#formAlert").removeClass('hide');  // Remove class hide to make the alert visible
    });

    $(".alert").find(".close").on("click", function (e) {  // Find all elements with the "alert" class, get all descendant elements with the class "close", and bind a "click" event handler
        e.stopPropagation();  // Don't allow the click to bubble up the DOM
        e.preventDefault();  // Don't let any default functionality occur (in case it's a link)
        $(this).closest(".alert").slideUp(400, function() {  // Hide this specific Alert
            $("#formAlert").addClass('hide');  // Add class hide after slideUp() has finished
        });
    });
});

function submit()
{
    $('form[name="delete"]').submit();
}

function hide()
{
    $("#formAlert").slideUp(400, function() {
        $("#formAlert").addClass('hide');
    });
}
</script>
@stop

@section('content')
<div class="col-lg-6 col-lg-offset-2">

    @if ($user->count())
    <div class="page-header">
        <h2>Edit Account Information</h2>
    </div>

    {{ Form::open(['route' => array('users.update', $user->id), 'method' => 'PATCH', 'class' => 'form-horizontal']) }}
    {{-- maybe try using form model binding: http://laravel.com/docs/html#form-model-binding --}}
    {{-- more infos: http://scotch.io/tutorials/simple-laravel-crud-with-resource-controllers   http://stackoverflow.com/questions/22844022/laravel-use-same-form-for-create-and-edit --}}
        <fieldset>
            <div class="form-group {{{ $errors->has('first_name') ? 'has-error' : '' }}}">
                {{ Form::label('first_name', 'First&nbsp;name: ', array('class' => 'col-lg-2 control-label')) }}
                <div class="col-lg-10">
                    {{ Form::text('first_name', $user->first_name, array('class' => 'form-control', 'autofocus' => 'autofocus')) }}
                    {{ $errors->first('first_name') }}
                </div>
            </div>
            <div class="form-group {{{ $errors->has('last_name') ? 'has-error' : '' }}}">
                {{ Form::label('last_name', 'Last&nbsp;name: ', array('class' => 'col-lg-2 control-label')) }}
                <div class="col-lg-10">
                    {{ Form::text('last_name', $user->last_name, array('class' => 'form-control')) }}
                    {{ $errors->first('last_name') }}
                </div>
            </div>
            <div class="form-group">
                {{ Form::label('username', 'Username: ', array('class' => 'col-lg-2 control-label')) }}
                <div class="col-lg-10">
                    {{ Form::text('username', $user->username, array('class' => 'form-control', 'id' => 'disabledInput', 'disabled' => '')) }}
                </div>
            </div>

            <div class="form-group {{{ $errors->has('email') ? 'has-error' : '' }}}">
                {{ Form::label('email', 'Email: ', array('class' => 'col-lg-2 control-label')) }}
                <div class="col-lg-10">
                    {{ Form::email('email', $user->email, array('class' => 'form-control')) }}
                    {{ $errors->first('email') }}
                </div>
            </div>

            <div class="form-group {{{ $errors->has('workgroup_id') ? 'has-error' : '' }}}">
                {{ Form::label('workgroup_id', 'Workgroup: ', array('class' => 'col-lg-2 control-label')) }}
                <div class="col-lg-10">
    	            {{ Form::select('workgroup_id', $workgroups, $user->workgroup_id, ['class' => 'form-control']) }}
                    {{ $errors->first('workgroup_id') }}
                </div>
            </div>

            <div class="form-group {{{ $errors->has('phone_institute') ? 'has-error' : '' }}}">
                {{ Form::label('phone_institute', 'Phone institute: ', array('class' => 'col-lg-2 control-label')) }}
                <div class="col-lg-10">
                    {{ Form::text('phone_institute', $user->phone_institute, array('class' => 'form-control')) }}
                    {{ $errors->first('phone_institute') }}
                </div>
            </div>
            <div class="form-group {{{ $errors->has('phone_private') ? 'has-error' : '' }}}">
                {{ Form::label('phone_private', 'Phone private: ', array('class' => 'col-lg-2 control-label')) }}
                <div class="col-lg-10">
                    {{ Form::text('phone_private', $user->phone_private, array('class' => 'form-control')) }}
                    {{ $errors->first('phone_private') }}
                </div>
            </div>
            <div class="form-group {{{ $errors->has('phone_mobile') ? 'has-error' : '' }}}">
                {{ Form::label('phone_mobile', 'Phone mobile: ', array('class' => 'col-lg-2 control-label')) }}
                <div class="col-lg-10">
                    {{ Form::text('phone_mobile', $user->phone_mobile, array('class' => 'form-control')) }}
                    {{ $errors->first('phone_mobile') }}
                </div>
            </div>

            <div class="form-group {{{ $errors->has('rating') ? 'has-error' : '' }}}">
                {{ Form::label('rating', 'Rating: ', array('class' => 'col-lg-2 control-label')) }}
                <div class="col-lg-10">
                    {{ Form::select('rating', array(
                        '1' => 'I\'m totally new', 
                        '2' => 'I did only a few shifts', 
                        '3' => 'I know how to do shifts', 
                        '4' => 'I\'m experienced', 
                        '5' => 'I\'m an expert'), $user->rating, ['class' => 'form-control']) }}
                    {{ $errors->first('rating') }}
                </div>
            </div>

            <div class="form-group">
                <div class="col-lg-10 col-lg-offset-2">
                    {{ Form::submit('Change profile data', array('class' => 'btn btn-primary')) }}
                    {{ Form::reset('Clear', array('class' => 'btn btn-default')) }}
                </div>
            </div>
        </fieldset>
    {{ Form::close() }}

    <div class="page-header">
        <h2>Change your password</h2>
    </div>

    {{ Form::open(['route' => 'users.update', 'class' => 'form-horizontal']) }}
        <fieldset>
            <div class="form-group {{{ $errors->has('password') ? 'has-error' : '' }}}">
                {{ Form::label('password', 'Old Password: ', array('class' => 'col-lg-2 control-label')) }}
                <div class="col-lg-10">
                    {{ Form::password('password', array('class' => 'form-control')) }}
                    {{ $errors->first('password') }}
                </div>
            </div>
            <div class="form-group {{{ $errors->has('password') ? 'has-error' : '' }}}">
                {{ Form::label('password', 'New Password: ', array('class' => 'col-lg-2 control-label')) }}
                <div class="col-lg-10">
                    {{ Form::password('password', array('class' => 'form-control')) }}
                    {{ $errors->first('password') }}
                </div>
            </div>
            <div class="form-group {{{ $errors->has('password_confirmation') ? 'has-error' : '' }}}">
                {{ Form::label('password_confirmation', 'Confirm Password: ', array('class' => 'col-lg-2 control-label')) }}
                <div class="col-lg-10">
                    {{ Form::password('password_confirmation', array('class' => 'form-control')) }}
                    {{ $errors->first('password_confirmation') }}
                </div>
            </div>

            <div class="form-group">
                <div class="col-lg-10 col-lg-offset-2">
                    {{ Form::submit('Change password', array('class' => 'btn btn-primary')) }}
                </div>
            </div>
        </fieldset>
    {{ Form::close() }}

    <div class="page-header">
        <h2>Delete your account</h2>
    </div>

    <div class="col-lg-10 col-lg-offset-2">
        <div id="formAlert" class="alert alert-warning hide" role="alert">
          <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
          <h4>Delete Account</h4>
          <p><strong>Warning!</strong> Are you sure to delete your account {{ $user->username }}?</p>
          <p>
            <button type="button" class="btn btn-danger" onclick="submit();">Delete</button>
            <button type="button" class="btn btn-default" onclick="hide();">Cancel</button>
          </p>
        </div>
        {{ Form::open(['route' => 'users.destroy', 'name' => 'delete']) }}
            <div class="form-group">
                {{ Form::submit('Delete Account', array('class' => 'btn btn-danger')) }}
            </div>
        {{ Form::close() }}
    </div>
    @else
    <div class="page-header">
        <h2>User not found</h2>
    </div>
    @endif
</div>
@stop

