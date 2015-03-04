<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="author" content="Sascha Wagner" />
        <meta name="description" content="A2 Beamtime Shift Schedule" />
        <link rel="shortcut icon" href={{ asset("favicon.png") }} />
        <title>
            @section('title')
            A2 Beamtime Schedule
            @show
        </title>

        <!-- CSS are placed here -->
        @section('css')
        {{ HTML::style('css/bootstrap.min.css') }}
        {{ HTML::style('css/font-awesome.min.css') }}
        @show

        <style>
            @section('styles')
            body {
                padding-top: 60px;
                padding-bottom: 60px;
            }
            @show
        </style>
    </head>

    <body>
        <!-- Navbar -->
        <div class="navbar navbar-default navbar-fixed-top" role="navigation">
            <div class="container">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-responsive-collapse">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" rel="home" href="/" title="A2 Beamtimes">
                      <img style="max-width: 65px; margin-top: -10px;" src="/img/a2logo_light_mid.png">
                    </a>

                    @if (!Auth::check())
                    <span class="navbar-brand">A2 Beamtime Scheduler</span>
                    @endif
                </div>
                <!-- Everything you want hidden at 940px or less, place within here -->
                <div class="collapse navbar-collapse navbar-responsive-collapse">
                    <ul class="nav navbar-nav">
                        @if (Auth::guest())
                        <li>{{ HTML::link('login', ' Login', ['class' => 'fa fa-sign-in']) }}</li>
                        @else
                        @if (Auth::user()->isAdmin)
                        <li class="dropdown">
                          <a href="#" class="dropdown-toggle fa fa-calendar" data-toggle="dropdown"> Beamtimes <span class="fa fa-caret-down"></span></a>
                          <ul class="dropdown-menu">
                            <li><a href="/beamtimes"><i class="fa fa-bars fa-fw"></i> Overview</a></li>
                            <li><a href="/beamtimes/create"><i class="fa fa-plus fa-fw"></i> Create</a></li>
                            <li><a href="/statistics"><span class="glyphicon glyphicon-stats fa-fw"></span> Statistics</a></li>
                            <li class="divider"></li>
                            <li><a href="#">Some other stuff</a></li>
                          </ul>
                        </li>
                        <li class="dropdown">
                          <a href="#" class="dropdown-toggle fa fa-users" data-toggle="dropdown"> Users <span class="fa fa-caret-down"></span></a>
                          <ul class="dropdown-menu">
                            <li><a href="/users"><i class="fa fa-bars fa-fw"></i> All Users</a></li>
                            <li><a href="/users/enable"><i class="fa fa-check-square-o fa-fw"></i> Enable Users</a></li>
                            <li><a href="/users/admins"><i class="fa fa-wrench fa-fw"></i> Manage Admins</a></li>
                          </ul>
                        </li>
                        @else
                        <li>
                          <a class="fa fa-calendar" href="/beamtimes"> Beamtimes</a>
                        </li>
                        <li>{{ HTML::link('users', ' All Users', ['class' => 'fa fa-users']) }}</li>
                        @endif
                        <li>{{ HTML::link('users/'.Auth::user()->username.'/edit', ' Edit Profile', ['class' => 'fa fa-edit']) }}</li>
                    </ul>
                    {{ Form::open(['route' => 'users.index', 'method' => 'get', 'class' => 'navbar-form navbar-left']) }}
                      <div class="form-group input-group">
                        {{ Form::text('search', '', array('class' => 'form-control', 'placeholder' => 'Search')) }}
                        <span class="input-group-btn">
                          <button class="btn btn-primary" type="submit"><span class="fa fa-search"></span></button>
                        </span>
                      </div>
                    {{ Form::close() }}
                    <ul class="nav navbar-nav navbar-right">
                        <li>{{ HTML::link('logout', ' Logout', ['class' => 'fa fa-sign-out']) }}</li>
                        @endif
                    </ul> 
                </div>
            </div>
        </div> 

        <!-- Container -->
        <div class="container">

            <!-- Success-Messages -->
            @if ($message = Session::get('success'))
            <div class="alert alert-success alert-dismissable fade in hidden-print">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <h4>Success</h4>
                {{{ $message }}}
            </div>
            @endif
            @if ($message = Session::get('error'))
            <div class="alert alert-danger alert-dismissable fade in hidden-print">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <h4>Error</h4>
                {{{ $message }}}
            </div>
            @endif
            @if ($message = Session::get('warning'))
            <div class="alert alert-warning alert-dismissable fade in hidden-print">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <h4>Warning</h4>
                {{{ $message }}}
            </div>
            @endif
            @if ($message = Session::get('info'))
            <div class="alert alert-info alert-dismissable fade in hidden-print">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <h4>Info</h4>
                {{{ $message }}}
            </div>
            @endif

            <!-- Content -->
            @yield('content')

        </div>

        <!-- Scripts are placed here -->
        {{ HTML::script('js/jquery-2.1.1.min.js') }}
        {{ HTML::script('js/bootstrap.min.js') }}
        @yield('scripts')

    </body>
</html>
