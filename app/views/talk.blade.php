<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="author" content="Sascha Wagner" />
        <meta name="description" content="A2 Beamtime Shift Schedule" />
        <link rel="shortcut icon" href={{ asset("favicon.png") }} />
        <title>
            A2 Beamtime Scheduler :: Talk
        </title>

        {{ HTML::style('css/bootstrap.min.css') }}
        {{ HTML::style('css/font-awesome.min.css') }}
        {{ HTML::style('css/reveal.css') }}
        {{ HTML::style('css/white-theme.css', array('id' => 'theme')) }}

        <style>
            body {
                padding-top: 60px;
                padding-bottom: 60px;
            }
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
                        @if (Auth::user()->isAdmin())
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


        @if (Auth::id() == 1)
        <div class="reveal">
            <div class="slides">
                <section>
                    <h1>A New Beamtime Scheduler for A2</h1>
                    <h4>
                        <p>Sascha Wagner (Uni Mainz)</p>
                        <p>A2 Collaboration Meeting</p>
                    </h4>
                    <p>
                    Mainz, March 12th, 2015
                </section>
                <section>
                    <h3>Why do we need a new<br />beamtime scheduler?</h3>
                    <p align="left" style="padding-left: 50px;">Status quo:</p>
                    <ul>
                        <li>Strongly depending on Joomla</li>
                        <li>Editing of beamtimes not possible</li>
                        <li>Manually subscribe two maintenance users to maintenance shifts to block it</li>
                        <li>No shift exchange possible</li>
                        <li>Run coordinators not foreseen</li>
                        <li>Rather hard to extend</li>
                    </ul>
                </section>
                <section>
                    <h2>What was done?</h2>
                    <ul>
                        <li>Written a modern beamtime management using concepts like MVC, RESTful Routing, and more</li>
                        <li>Mostly written in PHP</li>
                        <li>
                            Used Frameworks:
                            <ul>
                                <li>Laravel 4.2</li>
                                <li>Bootstrap 3.2</li>
                                <li>jQuery, Font Awesome, (AngularJS), ...</li>
                            </ul>
                        </li>
                        <li>Implementation of the missing features</li>
                        <li>Possibility to include mail notifications for certain events</li>
                        <li>Hosted on Github: https://github.com/a2wagner/beamtime_schedule</li>
                    </ul>
                </section>
                <section>
                    <h2>Outlook</h2>
                    <ul>
                        <li>Improve features like swapping shifts, inclusion of run coordinators</li>
                        <li>Finish beamtime statistics</li>
                        <li>Once it is finished: Replace the existing one</li>
                        <li>Include Phil's Author List</li>
                    </ul>
                </section>
            </div>
        </div>
        @endif


        {{ HTML::script('js/jquery-2.1.1.min.js') }}
        {{ HTML::script('js/bootstrap.min.js') }}
        {{ HTML::script('js/reveal.js') }}
        <script>
            Reveal.initialize();
        </script>

    </body>
</html>

