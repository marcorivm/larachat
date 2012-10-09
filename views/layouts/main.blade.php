<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
    <title>Chat</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le styles -->
    {{ Laravel\Asset::styles() }}

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
    {{ Laravel\Asset::scripts() }}

</head>

@section('body-open')
<body>
@yield_section

    <div class="container-fluid">
        @yield('main-content')
        <div class="row-fluid">
            <div class="span9">
                @yield('main-chat')
            </div>
            <div class="span3">
                @yield('right-bar')
            </div>
        </div>
    </div>

    <footer class="footer">
        @yield('footer')
    </footer>

</body>
</html>