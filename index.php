<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>NHLytics</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<style type="text/css">
	.bs-example{
    	margin: 20px;
    }
</style>
</head>
<body>

<div class="container">
    <h1>NHLytics</h1>
    <p class="lead">Computed NHL Lines calculated based on their ice time together in most recent game</p>
</div>


<?php

// This function reads your DATABASE_URL config var and returns a connection
// string suitable for pg_connect. Put this in your app.
function pg_connection_string_from_database_url() {
    extract(parse_url($_ENV["DATABASE_URL"]));
    return "user=$user password=$pass host=$host dbname=" . substr($path, 1); # <- you may want to add sslmode=require there too
}

function get_team_forward_lines($team_id) {
    // Establish database connection
    $pg_conn = pg_connect(pg_connection_string_from_database_url());

    // Now let's use the connection for something silly just to prove it works:
    $result = pg_query($pg_conn, "SELECT depth,player_id,position FROM lines WHERE team_id=${team_id} AND position IN ('L', 'C', 'R')");

    $lines = [];
    while ($row = pg_fetch_row($result)) {
        $depth = $row[0];
        $player_id = $row[1];
        $position = $row[2];

        $lines[$depth][$position] = $player_id;
     }

    return $lines;
}

function get_team_defense_lines($team_id) {
    // Establish database connection
    $pg_conn = pg_connect(pg_connection_string_from_database_url());

    // Now let's use the connection for something silly just to prove it works:
    $result = pg_query($pg_conn, "SELECT depth,player_id,position FROM lines WHERE team_id=${team_id} AND position IN ('D')");

    $lines = [];
    while ($row = pg_fetch_row($result)) {
        $depth = $row[0];
        $player_id = $row[1];
        $position = $row[2];

        $lines[$depth][$position][] = $player_id;
     }

    return $lines;
}

function get_teams() {
    // Establish database connection
    $pg_conn = pg_connect(pg_connection_string_from_database_url());

    // Now let's use the connection for something silly just to prove it works:
    $result = pg_query($pg_conn, "SELECT team_id,name FROM teams");

    $teams = [];
    while ($row = pg_fetch_row($result)) {
        $team_id = $row[0];
        $team_name = $row[1];

        // Index by team id
        $teams[$team_id] = $team_name;
     }

    // Sort alphabetically by team name
    asort($teams);

    return $teams;
}

function get_players() {
    // Establish database connection
    $pg_conn = pg_connect(pg_connection_string_from_database_url());

    // Now let's use the connection for something silly just to prove it works:
    $result = pg_query($pg_conn, "SELECT player_id,name FROM players");

    $players = [];
    while ($row = pg_fetch_row($result)) {
        $player_id = $row[0];
        $player_name = $row[1];

        // Index by player id
        $players[$player_id] = $player_name;
     }

    return $players;
}

$teams = get_teams();

?>

<div class="container">
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
                <a class="navbar-brand" href="#">NHLytics</a>
            </div>
            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav">
                    <li><a href="/">Home</a></li>
                    <li><a href="?about">About</a></li>
                    <li class="dropdown">
                        <a href="#" data-toggle="dropdown" class="dropdown-toggle">Lines <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            <?php
                                foreach ($teams as $team_id => $team_name) {
                                    echo "<li><a href='?team_id=${team_id}'>$team_name</a></li>";
                                }
                            ?>
                        </ul>
                    </li>
                </ul>
            </div><!-- /.navbar-collapse -->
        </div>
    </nav>
</div>



<?php

if (isset($_GET['about'])) {
?>
<div class="container">
    <p class="lead">Passion project to gather and analyze available hockey data which is hosted on <em>Heroku</em> and built using <em>Python</em> and <em>PostgreSQL</em>. Using <em>BeautifulSoup</em> to scrape shift data from the NHL Play-by-Play pages and making requests to available NHL APIs for player metadata, each team's lines are established by grouping players with whom they spent the most time on the ice. Scheduled jobs run frequently to gather new data, calculating and storing the lines in the <em>PostgreSQL</em> database.</p>
</div>
<?php
    return;
}

$team_ids = array_keys($teams);
$team_id = isset($_GET['team_id']) ? $_GET['team_id'] : $team_ids[array_rand($team_ids)];

?>

<div class="container">
    <h1><img src=<?php print "\"images/teams/${team_id}.png\"";?> alt=<?php print "\"$teams[$team_id]\"";?> style="height: 1em">  <?php print $teams[$team_id];?></h1>
</div>

<div class="container">

<?php

// get the calculated forward lines
$forward_lines = get_team_forward_lines($team_id);

// get the calculated defensive lines
$defense_lines = get_team_defense_lines($team_id);

// get mapping of player_id to player name
$player_names = get_players();

echo '<table class="table">';
echo "<tr><th>Left Wing</th><th>Center</th><th>Right Wing<th></tr>";

foreach ($forward_lines as $line) {
    echo "<tr>";

    $c_player_id = $line['C'];
    $lw_player_id = $line['L'];
    $rw_player_id = $line['R'];

    // print the forward line
    echo "<td>" . $player_names[$lw_player_id] . "</td><td>" . $player_names[$c_player_id] . "</td><td>" . $player_names[$rw_player_id] . "</td>";
    echo "</tr>";
}
echo "</table>";

echo '<table class="table">';
echo "<tr><th>Defense</th></tr>";
foreach ($defense_lines as $line) {
    echo "<tr>";

    $ld_player_id = $line['D'][0];
    $rd_player_id = $line['D'][1];

    echo "<td>" . $player_names[$ld_player_id] . "</td><td>" . $player_names[$rd_player_id] . "</td>";
    echo "</tr>";
}
echo "</table>";

?>

</div> <!-- /container -->

</body>
</html>