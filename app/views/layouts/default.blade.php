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
        {{ HTML::style('css/source-sans-pro.css') }}
        @if (Auth::check())
        {{ HTML::style('css/bootstrap.min.'.Auth::user()->css.'.css') }}
        @else
        {{ HTML::style('css/bootstrap.min.css') }}
        @endif
        {{ HTML::style('css/font-awesome.min.css') }}
        @show

        <style>
            @section('styles')
            body {
                padding-top: 60px;
                padding-bottom: 60px;
            }
            @media print {
                a[href]:after {
                    content: "" !important;
                }
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
                        <li><a href="/login"><i class="fa fa-sign-in fa-fw"></i> Login</a></li>
                        @else
                        @if (Auth::user()->isAdmin() || Auth::user()->isPI())
                        <li class="dropdown">
                          <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-calendar fa-fw"></i> Beamtimes <span class="fa fa-caret-down"></span></a>
                          <ul class="dropdown-menu">
                            <li><a href="/beamtimes"><i class="fa fa-bars fa-fw"></i> Overview</a></li>
                            <li><a href="/beamtimes/create"><i class="fa fa-plus fa-fw"></i> Create</a></li>
                            <li class="divider"></li>
                            <li><a href="/statistics"><i class="fa fa-bar-chart fa-fw"></i> Statistics</a></li>
                          </ul>
                        </li>
                        <li class="dropdown">
                          <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-users fa-fw"></i> Users <span class="fa fa-caret-down"></span></a>
                          <ul class="dropdown-menu">
                            <li><a href="/users"><i class="fa fa-bars fa-fw"></i> All Users</a></li>
                            <li class="divider"></li>
                            <li><a href="/users/enable"><i class="fa fa-check-square-o fa-fw"></i> Enable Users</a></li>
                            @if((Auth::user()->isRunCoordinator() && Auth::user()->hasRadiationInstruction()) || Auth::user()->isRadiationExpert())
                            <li><a href="/users/radiation"><i class="fa">&thinsp;&#9762;&nbsp;</i> Radiation Instruction</a></li>
                            @endif
                            @if(Auth::user()->isAdmin())
                            <li><a href="/users/kph"><i class="fa fa-user-plus fa-fw"></i> Add KPH Account</a></li>
                            @endif
                            <li><a href="/users/manage"><i class="fa fa-sliders fa-fw"></i> Manage Users</a></li>
                          </ul>
                        </li>
                        @elseif ((Auth::user()->isRunCoordinator() && Auth::user()->hasRadiationInstruction()) || Auth::user()->isRadiationExpert())
                        <li>
                          <a href="/beamtimes"><i class="fa fa-calendar fa-fw"></i> Beamtimes</a>
                        </li>
                        <li class="dropdown">
                          <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-users fa-fw"></i> Users <span class="fa fa-caret-down"></span></a>
                          <ul class="dropdown-menu">
                            <li><a href="/users"><i class="fa fa-bars fa-fw"></i> All Users</a></li>
                            <li><a href="/users/radiation"><i class="fa">&thinsp;&#9762;&nbsp;</i> Radiation Instruction</a></li>
                          </ul>
                        </li>
                        @else
                        <li>
                          <a href="/beamtimes"><i class="fa fa-calendar fa-fw"></i> Beamtimes</a>
                        </li>
                        <li><a href="/users"><i class="fa fa-users fa-fw"></i> All Users</a></li>
                        @endif
                        <li class="dropdown">
                          <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-user"></span> Profile <span class="fa fa-caret-down"></span></a>
                          <ul class="dropdown-menu">
                            <li><a href="/users/{{ Auth::user()->username }}"><i class="fa fa-eye fa-fw"></i> View</a></li>
                            <li><a href="/users/{{ Auth::user()->username }}/edit"><i class="fa fa-edit fa-fw"></i> Edit</a></li>
                            <li><a href="/users/{{ Auth::user()->username }}/shifts"><i class="fa fa-list-alt fa-fw"></i> Shifts</a></li>
                            <li class="divider"></li>
                            <li><a href="/users/settings"><i class="fa fa-sliders fa-fw"></i> Settings</a></li>
                          </ul>
                        </li>
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
                        <li><a href="/logout"><i class="fa fa-sign-out fa-fw"></i> Logout</a></li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        <!-- Container -->
        <div class="container">

            @if (Auth::user() && !Auth::user()->ldap_id)
            <div class="alert alert-warning hidden-print">
                <h4>Important</h4>
                <p>You do not have a KPH account yet. Please request an account with <a style="color: black;" href="https://portal.kph.uni-mainz.de/registration/">this form</a>.<br />
                More information can be found <a style="color: black;" href="http://edv.kph.uni-mainz.de/en/accounts.html">here</a>.</p>
                <p>If you have a KPH account, please contact one of the admins to change your account accordingly.</p>
            </div>
            @endif

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
