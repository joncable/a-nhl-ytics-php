<!doctype html>
<html lang="en">
    <head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>

    <style type="text/css">
        .bs-example{
            margin: 20px;
        }
    </style>

    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>a-nhl-ytics</title>

    </head>

    <body>
        <div class="bs-example">
            <nav id="myNavbar" class="navbar navbar-default" role="navigation">
                <!-- Brand and toggle get grouped for better mobile display -->
                <div class="container">
                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                            <span class="sr-only">Toggle navigation</span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>
                        <a class="navbar-brand" href="#">Brand</a>
                    </div>
                    <!-- Collect the nav links, forms, and other content for toggling -->
                    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                        <ul class="nav navbar-nav">
                            <li><a href="#">Home</a></li>
                            <li><a href="#">Profile</a></li>
                            <li class="dropdown">
                                <a href="#" data-toggle="dropdown" class="dropdown-toggle">Messages <b class="caret"></b></a>
                                <ul class="dropdown-menu">
                                    <li><a href="#">Inbox</a></li>
                                    <li><a href="#">Drafts</a></li>
                                    <li><a href="#">Sent Items</a></li>
                                    <li class="divider"></li>
                                    <li><a href="#">Trash</a></li>
                                </ul>
                            </li>
                        </ul>
                        <ul class="nav navbar-nav navbar-right">
                            <li class="dropdown">
                                <a href="#" data-toggle="dropdown" class="dropdown-toggle">Admin <b class="caret"></b></a>
                                <ul class="dropdown-menu">
                                    <li><a href="#">Action</a></li>
                                    <li><a href="#">Another action</a></li>
                                    <li class="divider"></li>
                                    <li><a href="#">Settings</a></li>
                                </ul>
                            </li>
                        </ul>
                    </div><!-- /.navbar-collapse -->
                </div>
            </nav>
        </div>

        <div class="container">
              <h1>NHL Lines</h1>
              <p class="lead">Computed NHL Lines calculated based on their ice time together in most recent game</p>
<?php

// This function reads your DATABASE_URL config var and returns a connection
// string suitable for pg_connect. Put this in your app.
function pg_connection_string_from_database_url() {
    extract(parse_url($_ENV["DATABASE_URL"]));
    return "user=$user password=$pass host=$host dbname=" . substr($path, 1); # <- you may want to add sslmode=require there too
}

function get_team_lines($team_id) {
    // Establish database connection
    $pg_conn = pg_connect(pg_connection_string_from_database_url());

    // Now let's use the connection for something silly just to prove it works:
    $result = pg_query($pg_conn, "SELECT depth,player_id,position FROM lines WHERE team_id=${team_id}");

    $lines = [];
    while ($row = pg_fetch_row($result)) {
        $depth = $row[0];
        $player_id = $row[1];
        $position = $row[2] == 'D' ? 'D' : 'F';

        $lines[$position][$depth][] = $player_id;
     }

    return $lines;
}

$lines = get_team_lines(5);

foreach ($lines as $position => $depths) {
    echo '<table class="table">';
    if ($position == 'F') {
        echo "<tr><th>Left Wing</th><th>Center</th><th>Right Wing<th></tr>";
    } else if ($position == 'D') {
        echo "<tr><th>Defense</th></tr>";
    }
    foreach ($depths as $depth => $players) {
        echo "<tr>";
        foreach ($players as $player_id) {
            echo "<td>${player_id}</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
}

?>

    </div> <!-- /container -->
</body>
</html>