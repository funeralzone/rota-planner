<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $pageTitle }}</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--[if lte IE 9]>
    <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
    <link rel="stylesheet" href="https://unpkg.com/purecss@1.0.0/build/pure-min.css" integrity="sha384-nn4HPE8lTHyVtfCBi5yW9d20FjT8BJwUXyWZT9InLYax14RDjBj46LmSztkmNP9w" crossorigin="anonymous">
    <style>
        body {
            margin: 1em;
            color: slategray;
        }
        h1 {
            font-size: 1.3em;
        }
    </style>
</head>

<body>
@yield('content')
</body>
</html>
