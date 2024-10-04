    <?php
    include 'database.php';
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="style.css">
        <title>Cricket Analytics - Home</title>
    </head>
    <body>
        <header>
            <div class="header-container">
                <h1>Cricket Analytics</h1>
                <nav class="nav-bar">
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="players.php">Players</a></li>
                        <li><a href="matches.php">Matches</a></li>
                        <li><a href="venue.php">Venue</a></li>
                        <li><a href="board.php">Board</a></li>
                        <li><a href="tournament.php">Tournament</a></li>
                        <li><a href="team.php">Teams</a></li>
                        <li><a href="admin_login.php">Admin</a></li>
                    </ul>
                </nav>
            </div>
        </header>

        <main>
            <div class="content-container">


            <h2>Search the Database</h2>
            <!-- Include the search form -->
            <form method="POST" action="index.php" class="search-form">
                <input type="text" name="search" placeholder="Enter a name, match type, team, etc.">
                
                <label for="searchBy">Search By:</label>
                <select id="searchBy" name="searchBy">
                    <option value="all">All</option>
                    <option value="player">Player</option>
                    <option value="team">Team</option>
                    <option value="match">Match</option>
                    <option value="tournament">Tournament</option>
                </select>

                <label for="matchType">Match Type:</label>
                <select id="matchType" name="matchType">
                    <option value="">All Types</option>
                    <option value="ODI">ODI</option>
                    <option value="T20">T20</option>
                    <option value="Test">Test</option>
                </select>

                <label for="dateRange">Date Range:</label>
                <input type="date" name="startDate" placeholder="Start Date">
                <input type="date" name="endDate" placeholder="End Date">

                <button type="submit" class="btn btn-primary">Search</button>
                <button type="button" class="btn btn-secondary" id="clearButton" onclick="clearSearch()">Clear</button>
            </form>

            <!-- Display search results -->
            <?php
            if (isset($_POST['search'])) {
                $searchTerm = $_POST['search'];
                $searchBy = $_POST['searchBy'];
                $matchType = $_POST['matchType'];
                $startDate = $_POST['startDate'];
                $endDate = $_POST['endDate'];

                // Construct SQL query based on selected filters
                $sql = "SELECT 'Player' AS Type, Name AS Result, 'Players' AS Source FROM Players WHERE Name LIKE '%$searchTerm%'";

                if ($searchBy == 'team' || $searchBy == 'all') {
                    $sql .= " UNION SELECT 'Team' AS Type, Name AS Result, 'Teams' AS Source FROM Team WHERE Name LIKE '%$searchTerm%'";
                }

                if ($searchBy == 'match' || $searchBy == 'all') {
                    $sql .= " UNION SELECT 'Match' AS Type, CONCAT(t1.Name, ' vs ', t2.Name) AS Result, 'Matches' AS Source 
                              FROM Matches 
                              JOIN Team t1 ON Matches.WinningTeamID = t1.TeamID
                              JOIN Team t2 ON Matches.LosingTeamID = t2.TeamID 
                              WHERE (t1.Name LIKE '%$searchTerm%' OR t2.Name LIKE '%$searchTerm%' OR Matches.MatchType LIKE '%$searchTerm%')";
                    if ($matchType) {
                        $sql .= " AND Matches.MatchType = '$matchType'";
                    }
                    if ($startDate && $endDate) {
                        $sql .= " AND Matches.Date BETWEEN '$startDate' AND '$endDate'";
                    }
                }

                if ($searchBy == 'tournament' || $searchBy == 'all') {
                    $sql .= " UNION SELECT 'Tournament' AS Type, Name AS Result, 'Tournaments' AS Source FROM Tournaments WHERE Name LIKE '%$searchTerm%'";
                }

                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    echo '<h3>Search Results</h3>';
                    echo '<table class="table">';
                    echo '<thead><tr><th>Type</th><th>Result</th><th>Source</th></tr></thead>';
                    echo '<tbody>';
                    while ($row = $result->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($row['Type']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['Result']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['Source']) . '</td>';
                        echo '</tr>';
                    }
                    echo '</tbody>';
                    echo '</table>';
                } else {
                    echo "<p>No results found for '$searchTerm'.</p>";
                }
            }
            ?>
        </div>
    </main>
                <h2>Upcoming and Ongoing Matches</h2>
                <div class="matches-list">
                    <?php
                    // Fetch upcoming and ongoing matches
                    $sql = "SELECT m.MatchID, m.Date, m.Time, t1.Name AS WinningTeam, t2.Name AS LosingTeam 
                            FROM Matches m 
                            JOIN Team t1 ON m.WinningTeamID = t1.TeamID 
                            JOIN Team t2 ON m.LosingTeamID = t2.TeamID 
                            WHERE m.Date >= CURDATE() 
                            ORDER BY m.Date ASC";

                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo '<div class="match-info">';
                            echo '<p>Date: ' . $row['Date'] . ' | Time: ' . $row['Time'] . '</p>';
                            echo '<p>Match: ' . $row['WinningTeam'] . ' vs ' . $row['LosingTeam'] . '</p>';
                            echo '</div>';
                        }
                    } else {
                        echo "<p>No upcoming or ongoing matches found.</p>";
                    }
                    ?>
                </div>

                <h2>Tournaments</h2>
                <div class="tournaments-list">
                    <?php
                    // Fetch tournaments
                    $sql = "SELECT * FROM Tournaments";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo '<div class="tournament-info">';
                            echo '<h3>' . $row['Name'] . ' (' . $row['Year'] . ')</h3>';
                            echo '<p>Winning Team: ' . $row['WinningTeam'] . '</p>';
                            echo '<p>Matches: ' . $row['NumberOfMatches'] . '</p>';
                            echo '</div>';
                        }
                    } else {
                        echo "<p>No tournaments found.</p>";
                    }
                    $conn->close();
                    ?>
                </div>
            </div>
        </main>

        <footer>
            <div class="footer-container">
                <p>&copy; 2024 Cricket Analytics. All rights reserved.</p>
                <ul class="social-icons">
                    <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                    <li><a href="#"><i class="fab fa-twitter"></i></a></li>
                    <li><a href="#"><i class="fab fa-instagram"></i></a></li>
                </ul>
            </div>
        </footer>
        <script src="script.js"></script>
    </body>
    </html>
