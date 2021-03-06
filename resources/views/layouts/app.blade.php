<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

    <!-- Scripts -->
    <script>
        window.Laravel = {!! json_encode([
            'csrfToken' => csrf_token(),
        ]) !!};
    </script>
    <style>
        .mark{
            border: 2px solid black;
        }
        .mini-holder-wrapper{
            border: 2px solid black;
            margin-bottom: 30px;
            height: 409px;
        }
        .mini-holder-wrapper:hover{
            box-shadow: 0 2px 12px;
        }
        .mini-holder-block > a:hover{
            text-decoration: none;
        }
        .mini-holder-wrapper .profile-picture-holder img{
            border-top: 2px solid black;
            width: 100%;
            max-height: 280px;
        }
        .user-metainfo-holder{
            border:2px solid black;
            margin-bottom: 30px;
            padding: 15px;
        }
        .user-metainfo-holder table.general-info tr > td:first-child{
            min-width: 130px;
            /*border:1px solid red;*/
            vertical-align: top;
        }
        .user-metainfo-holder table tr > td:nth-child(2){
            vertical-align: top;
        }
        .user-metainfo-holder table.activity-info tr > td:first-child{
            padding-right: 6px;
        }
        }
        .floating_alert {
            position: absolute;
            top: 20px;
            right: 20px;
            border: 1px solid red;
        }
        .strike-out{
            text-decoration: line-through;
        }
    </style>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-default navbar-static-top">
            <div class="container">
                <div class="navbar-header">

                    <!-- Collapsed Hamburger -->
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse">
                        <span class="sr-only">Toggle Navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>

                    <!-- Branding Image -->
                    <a class="navbar-brand" href="{{ url('/') }}">
                        {{ config('app.name', 'Laravel') }}
                    </a>
                </div>

                <div class="collapse navbar-collapse" id="app-navbar-collapse">
                    <!-- Left Side Of Navbar -->
                    <ul class="nav navbar-nav">
                        <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li><a href="{{ url('/users') }}">Users</a></li>
                        <li><a href="{{ url('/events') }}">Events</a></li>
                        <li><a href="{{ url('/news') }}">News</a></li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Posts <span class="caret"></a>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="{{ route('posts.index') }}">All Posts</a></li>
                                <li><a href="{{ route('posts.for-sale') }}">Posts for Sale</a></li>
                                <li><a href="{{ route('posts.discover') }}">Discover Posts</a></li>
                                <li><a href="{{ route('posts.trending') }}">Trending Posts</a></li>
                                <li><a href="{{ route('posts.arteprize') }}">Arteprize Posts</a></li>
                                <li><a href="{{ route('posts.buy') }}">Selected Posts to buy</a></li>
                                <li><a href="{{ route('posts.curators-choice') }}">Curators Choices</a></li>
                            </ul>
                        </li>
                        <li><a href="{{ route('mail.templates') }}">Email Templates</a></li>
                        <li><a href="{{ url('/settings') }}">Settings</a></li>
                    </ul>

                    <form method="POST" action="/search-users" class="navbar-form navbar-left">
                        {{ csrf_field() }}
                        <div class="form-group">
                            <input name='search_string' type="text" class="form-control" placeholder="Enter username">
                        </div>
                        <button type="submit" class="btn btn-default">Search User</button>
                    </form>

                    <!-- Right Side Of Navbar -->
                    <ul class="nav navbar-nav navbar-right">
                        <!-- Authentication Links -->
                        @if (Auth::guest())
                            <li><a href="{{ route('login') }}">Login</a></li>
                        @else
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>

                                <ul class="dropdown-menu" role="menu">
                                    <li>
                                        <a href="{{ route('logout') }}"
                                            onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                            Logout
                                        </a>

                                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                            {{ csrf_field() }}
                                        </form>
                                    </li>
                                    <li><a href="{{ route('user.send-notification-form') }}">Send global notification</a></li>
                                </ul>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </nav>
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    @if (session('status'))
                        <div class="alert alert-success alert-dismissable floating_alert">
                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                            {{ session('status') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @yield('content')
    </div>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script>
      $( function() {
        $( ".datepicker" ).datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true
        });
      } );

  </script>
  <script src="https://use.fontawesome.com/a1e873a5d5.js"></script>
{{-- One Signal official Opt-in form starts --}}
    {{-- @auth
        <script src="https://cdn.onesignal.com/sdks/OneSignalSDK.js" async='async'></script>
        <script>
        var OneSignal = window.OneSignal || [];
        OneSignal.push(["init", {
          appId: "{{ env('ONESIGNAL_APP_ID') }}",
          autoRegister: false, /* Set to true to automatically prompt visitors */
          notifyButton: {
              enable: true /* Set to false to hide */
          }
        }]);
        OneSignal.push(["sendTag", "channel", "User-{{ Auth::user()->id }}"])
        </script>
    @endauth --}}
{{-- One Signal official Opt-in form ends --}}
  @yield('script')
</body>
</html>
