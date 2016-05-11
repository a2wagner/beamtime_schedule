@extends('layouts.default')

@section('title')
Settings
@stop

@section('styles')
@parent
.nounderline {text-decoration: none !important;}
@stop

@section('scripts')
{{ HTML::script('js/laravel.js') }}
@stop

@section('content')
<div class="col-lg-12">
    <div class="page-header">
        <h2>Settings</h2>
    </div>

    <div class="page-header">
        <h3>iCal Link for Calendar Integration</h3>
    </div>
    @if (!Auth::user()->ical)
    <p>You can generate an individual link where you can access all your shifts without login. This can be used for integration in other calendars like Google Calendar or Thunderbird.</p>
    {{ link_to("/ical/" . Auth::user()->username, 'Generate iCal Link', ['class' => 'btn btn-primary', 'data-method' => 'patch']) }}
    @else
    <p>The following link can be used to integrate and update your shifts in a calendar like Google Calendar or Thunderbird:</p>
    <?php $link = url() . '/ical/' . Auth::user()->ical; ?>
    {{ link_to($link, $link, ['style' => 'color: inherit;']) }}
    @endif

    <div class="page-header">
        <h3>Available Styles</h3>
    </div>
    <p style="margin: 30px;">
        Current style: <b>{{{ ucfirst($style) }}}</b>
    </p>
    <div class="col-lg-4 text-center" style="margin-bottom: 30px;">
        <a href="/users/settings/cosmo" data-method="patch">
            <img alt="Cosmo" src="/img/styles/cosmo.png" width="100%">
            Cosmo (Default)
        </a>
    </div>
    <div class="col-lg-4 text-center" style="margin-bottom: 30px;">
        <a href="/users/settings/slate" data-method="patch">
            <img alt="Slate" src="/img/styles/slate.png" width="100%">
            Slate
        </a>
    </div>
    <div class="col-lg-4 text-center" style="margin-bottom: 30px;">
        <a href="/users/settings/sandstone" data-method="patch">
            <img alt="Sandstone" src="/img/styles/sandstone.png" width="100%">
            Sandstone
        </a>
    </div>
    <div class="col-lg-4 text-center" style="margin-bottom: 30px;">
        <a href="/users/settings/lumen" data-method="patch">
            <img alt="Lumen" src="/img/styles/lumen.png" width="100%">
            Lumen
        </a>
    </div>
    <div class="col-lg-4 text-center" style="margin-bottom: 30px;">
        <a href="/users/settings/cyborg" data-method="patch">
            <img alt="Cyborg" src="/img/styles/cyborg.png" width="100%">
            Cyborg
        </a>
    </div>
    <div class="col-lg-4 text-center" style="margin-bottom: 30px;">
        <a href="/users/settings/bootstrap" data-method="patch">
            <img alt="Bootstrap" src="/img/styles/bootstrap.png" width="100%">
            Bootstrap
        </a>
    </div>
</div>
@stop

