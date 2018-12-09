<!doctype html>
<html lang="en">
    <head>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>a-nhl-ytics</title>

    </head>


    <body>
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
    echo '<table class="table w-50">';
    if ($position == 'F') {
        echo "<th><tr>Left Wing</tr><tr>Center</tr><tr>Right Wing<tr></th>";
    } else if ($position == 'F') {
        echo "<th><tr>Left Defense</tr><tr>Right Defense<tr></th>";
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