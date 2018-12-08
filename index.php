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

print "<pre>\n";

foreach ($lines as $position => $depths) {
    print '<table style="width:100%">';
    print "<tr><th>${position}</th></tr>";
    foreach ($depths as $depth => $players) {
        print "<tr>";
        foreach ($players as $player_id) {
            print "<td>${player_id}</td>";
        }
        print "</tr>";
    }
    print "</table>";
}



?>