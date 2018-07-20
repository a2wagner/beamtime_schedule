@extends('layouts.default')

@section('title')
Edit User {{ $user->username }}
@stop

@section('scripts')
{{ HTML::script('js/laravel.js') }}
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
@if (Auth::user()->isAdmin() || Auth::user()->id == $user->id)
<div class="col-lg-6 col-lg-offset-2">

    @if ($user->count())
    <div class="page-header">
        <h2>Edit Account Information</h2>
    </div>

    @if ($user->ldap_id)
    <p class="col-lg-offset-2">
      Note: This will only change the local data stored for the Beamtime Scheduler, <b>not</b> your KPH account itself.
    </p>
    @endif
    {{ Form::model($user, ['route' => array('users.update', $user->id), 'method' => 'PATCH', 'class' => 'form-horizontal']) }}
        <fieldset>
            <div class="form-group {{{ $errors->has('first_name') ? 'has-error' : '' }}}">
                {{ Form::label('first_name', 'First&nbsp;name: ', array('class' => 'col-lg-2 control-label')) }}
                <div class="col-lg-10">
                    {{ Form::text('first_name', Input::old('first_name'), array('class' => 'form-control', 'autofocus' => 'autofocus')) }}
                    {{ $errors->first('first_name') }}
                </div>
            </div>
            <div class="form-group {{{ $errors->has('last_name') ? 'has-error' : '' }}}">
                {{ Form::label('last_name', 'Last&nbsp;name: ', array('class' => 'col-lg-2 control-label')) }}
                <div class="col-lg-10">
                    {{ Form::text('last_name', Input::old('last_name'), array('class' => 'form-control')) }}
                    {{ $errors->first('last_name') }}
                </div>
            </div>
            <div class="form-group">
                {{ Form::label('username', 'Username: ', array('class' => 'col-lg-2 control-label')) }}
                <div class="col-lg-10">
                    {{ Form::text('username', Input::old('username'), array('class' => 'form-control', 'id' => 'disabledInput', 'disabled' => '')) }}
                </div>
            </div>

            <div class="form-group {{{ $errors->has('email') ? 'has-error' : '' }}}">
                {{ Form::label('email', 'Email: ', array('class' => 'col-lg-2 control-label')) }}
                <div class="col-lg-10">
                    {{ Form::email('email', Input::old('email'), array('class' => 'form-control')) }}
                    {{ $errors->first('email') }}
                </div>
            </div>

            <div class="form-group {{{ $errors->has('workgroup_id') ? 'has-error' : '' }}}">
                {{ Form::label('workgroup_id', 'Workgroup: ', array('class' => 'col-lg-2 control-label')) }}
                <div class="col-lg-10">
    	            {{ Form::select('workgroup_id', $workgroups, Input::old('workgroup_id'), ['class' => 'form-control']) }}
                    {{ $errors->first('workgroup_id') }}
                </div>
            </div>

            <div class="form-group {{{ $errors->has('phone_institute') ? 'has-error' : '' }}}">
                {{ Form::label('phone_institute', 'Phone institute: ', array('class' => 'col-lg-2 control-label')) }}
                <div class="col-lg-10">
                    {{ Form::text('phone_institute', Input::old('phone_institute'), array('class' => 'form-control')) }}
                    {{ $errors->first('phone_institute') }}
                </div>
            </div>
            <div class="form-group {{{ $errors->has('phone_private') ? 'has-error' : '' }}}">
                {{ Form::label('phone_private', 'Phone private: ', array('class' => 'col-lg-2 control-label')) }}
                <div class="col-lg-10">
                    {{ Form::text('phone_private', Input::old('phone_private'), array('class' => 'form-control')) }}
                    {{ $errors->first('phone_private') }}
                </div>
            </div>
            <div class="form-group {{{ $errors->has('phone_mobile') ? 'has-error' : '' }}}">
                {{ Form::label('phone_mobile', 'Phone mobile: ', array('class' => 'col-lg-2 control-label')) }}
                <div class="col-lg-10">
                    {{ Form::text('phone_mobile', Input::old('phone_mobile'), array('class' => 'form-control')) }}
                    {{ $errors->first('phone_mobile') }}
                </div>
            </div>

            <div class="form-group {{{ $errors->has('rating') ? 'has-error' : '' }}}">
                {{ Form::label('rating', 'Rating: ', array('class' => 'col-lg-2 control-label')) }}
                <div class="col-lg-7">
                    {{ Form::select('rating', array(
                        '1' => '1&ensp;&mdash;&ensp;New/Inexperienced', 
                        '2' => '2&ensp;&mdash;&ensp;Basic Knowledge', 
                        '3' => '3&ensp;&mdash;&ensp;Experienced', 
                        '4' => '4&ensp;&mdash;&ensp;Expert'), Input::old('rating'), ['class' => 'form-control']) }}
                    {{ $errors->first('rating') }}
                </div>
                <div class="col-lg-2">
                    <button type="button" class="btn btn-default" data-toggle="modal" data-target=".rating-modal-lg">Rating Help</button>
                    <?php $guide = new RatingGuide(); $guide->modal('rating-modal'); ?>
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

    @if ($user->ldap_id)
    <p class="col-lg-offset-2">
      You can change your password via the KPH internal pages<br />
      (You have to be in the KPH network to do this):<br />
      <a href="http://master.kph.uni-mainz.de/passwort/" target="_blank">Change KPH account password</a>
    </p>
    @elseif ($user->id === Auth::user()->id)
    {{ Form::open(['route' => array('users.update', $user->id), 'method' => 'PUT', 'class' => 'form-horizontal']) }}
        <fieldset>
            <div class="form-group {{{ $errors->has('password_old') ? 'has-error' : '' }}}">
                {{ Form::label('password_old', 'Old Password: ', array('class' => 'col-lg-2 control-label')) }}
                <div class="col-lg-10">
                    {{ Form::password('password_old', array('class' => 'form-control')) }}
                    {{ $errors->first('password_old') }}
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
    @else  {{-- at this point the user does not have a LDAP id (account registered manually) and the only remaining user category after the previous checks is an admin --}}
    <div class="col-lg-offset-2">
      <a class="btn btn-primary" href="/users/password/{{{$user->id}}}">Change Password</a>
    </div>
    @endif
    <div class="col-lg-offset-2">
    @if (Auth::user()->isAdmin() && $user->ldap_id)  {{-- Cover the case that the user has a LDAP id separate --}}
      <p>
        Note: Changing the password here will only change it locally, the LDAP password will not be changed.
      </p>
      <a class="btn btn-primary" href="/users/password/{{{$user->id}}}">Change Password</a>
    @if ($user->password !== 'ldap')  {{-- User has a ldap_id, which means he has a KPH account, but a different password locally --}}
      <a class="btn btn-success" href="/users/password/{{{$user->id}}}" data-method="patch">Link Password to KPH account</a>
    @endif
    @endif
    </div>

    <div class="page-header">
        <h2>Delete your account</h2>
    </div>

    <div class="col-lg-offset-2">
        @if ($user->ldap_id)
        <p>
          Note: This will only affect your locally stored data for the Beamtime Scheduler, <b>not</b> your KPH account.
        </p>
        @endif
        <div id="formAlert" class="alert alert-warning hide" role="alert">
          <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
          <h4>Delete Account</h4>
          <p><strong>Warning!</strong> Are you sure to delete your account {{ $user->username }}?</p>
          <p>
            <button type="button" class="btn btn-danger" onclick="submit();">Delete</button>
            <button type="button" class="btn btn-default" onclick="hide();">Cancel</button>
          </p>
        </div>
        {{ Form::open(['route' => array('users.destroy', $user->id), 'method' => 'DELETE', 'name' => 'delete']) }}
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
@else
<div class="col-lg-10 col-lg-offset-1">
    <div class="page-header">
        <h2>User Management</h2>
    </div>

    <h3 class="text-warning">It seems you're not on the page you've been looking for. You may want to go to an {{ link_to("/users", "overview of all registered shift workers") }} instead.</h3>
</div>
@endif
@stop

