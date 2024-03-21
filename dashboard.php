<?php
session_start();

// Databasekonfigurasjon
$host = 'localhost';
$dbname = 'dashboardtestng';
$username = 'fredrik';
$password = 'password';

// Forsøk å koble til databasen
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Sett PDO-feilmeldinger for å vise i stedet for stille feil
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Feil ved tilkobling til databasen: " . $e->getMessage());
}

// Omdiriger brukeren hvis ikke logget inn
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Logg ut
if (isset($_POST['logout'])) {
    session_unset(); // Fjern alle sesjonsvariabler
    session_destroy(); // Ødelegg sesjonen
    header("Location: login.php"); // Omdiriger til innloggingssiden
    exit;
}

// Legg til brukerinformasjon
function leggTilBrukerInfo($firstName, $lastName, $location) {
    global $conn;
    try {
        $stmt = $conn->prepare("INSERT INTO UserInformations (user_id, first_name, last_name, location) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $firstName, $lastName, $location]);
        return true;
    } catch(PDOException $e) {
        return "Feil: " . $e->getMessage();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_user_info'])) {
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $location = $_POST['location'];

    $result = leggTilBrukerInfo($firstName, $lastName, $location);
    if ($result === true) {
        $user_info_added = true;
    } else {
        $user_info_error = $result;
    }
}

// Hent brukerinformasjon
function hentBrukerInfo($user_id) {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT * FROM User_info WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user_info = $stmt->fetch();
        return $user_info;
    } catch(PDOException $e) {
        return "Feil: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>
<body>
<h2>Dashboard</h2>
<p>Velkommen til din dashboard!</p>

<!-- Legg til brukerinformasjon-skjema -->
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <input type="text" name="firstName" placeholder="Fornavn" required>
    <input type="text" name="lastName" placeholder="Etternavn" required>
    <input type="text" name="location" placeholder="Sted" required>
    <input type="submit" name="add_user_info" value="Legg til brukerinformasjon">
</form>

<?php
// Vis brukerinformasjon hvis tilgjengelig
$user_info = hentBrukerInfo($_SESSION['user_id']);

if ($user_info !== false) {
    echo "<h3>Brukerinformasjon</h3>";
    echo "<p>Fornavn: " . $user_info['first_name'] . "</p>";
    echo "<p>Etternavn: " . $user_info['last_name'] . "</p>";
    echo "<p>Sted: " . $user_info['location'] . "</p>";
} else {
    echo "<p>Du har ikke lagt til brukerinformasjon enda.</p>";
}
?>

<!-- Logg ut-skjema -->
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <input type="submit" name="logout" value="Logg ut">
</form>
</body>
</html>
