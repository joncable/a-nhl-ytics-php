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
    $result = pg_query($pg_conn, "SELECT player_id FROM lines WHERE team_id=${team_id}");

    return $result;
}

$result = get_team_lines(5);

print "<pre>\n";

if (!pg_num_rows($result)) {
  print("Your connection is working, but your database is empty.\nFret not. This is expected for new apps.\n");
} else {
  print "Player ids for team_id=${team_id}:\n";
  while ($row = pg_fetch_row($result)) { print("- $row[0]\n"); }
}

print "\n";

?>