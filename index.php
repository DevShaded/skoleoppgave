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

// Funksjon for å legge til en ny bruker
function leggTilBruker($username, $password, $name, $email, $phone) {
    global $conn;
    try {
        // Krypter passordet før lagring
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO Users (username, password, name, email, phone) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$username, $hashed_password, $name, $email, $phone]);
        return true;
    } catch(PDOException $e) {
        return "Feil: " . $e->getMessage();
    }
}

// Funksjon for å logge inn brukeren
function loggInn($username, $password) {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT * FROM Users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        } else {
            return false;
        }
    } catch(PDOException $e) {
        return "Feil: " . $e->getMessage();
    }
}

// Omdiriger brukeren hvis allerede logget inn
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

// Behandle registreringsskjema
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    $result = leggTilBruker($username, $password, $name, $email, $phone);
    if ($result === true) {
        header("Location: login.php");
        exit;
    } else {
        $register_error = $result;
    }
}

// Behandle innloggingsskjema
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $user = loggInn($username, $password);
    if ($user !== false) {
        $_SESSION['user_id'] = $user['user_id'];
        header("Location: dashboard.php");
        exit;
    } else {
        $login_error = "Feil brukernavn eller passord.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrering og innlogging</title>
</head>
<body>
<h2>Registrering</h2>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <input type="text" name="username" placeholder="Brukernavn" required><br>
    <input type="password" name="password" placeholder="Passord" required><br>
    <input type="text" name="name" placeholder="Navn" required><br>
    <input type="email" name="email" placeholder="E-post" required><br>
    <input type="text" name="phone" placeholder="Telefonnummer" required><br>
    <input type="submit" name="register" value="Registrer">
</form>

<h2>Innlogging</h2>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <input type="text" name="username" placeholder="Brukernavn" required><br>
    <input type="password" name="password" placeholder="Passord" required><br>
    <input type="submit" name="login" value="Logg inn">
</form>

<?php
if (isset($register_error)) {
    echo "<p>$register_error</p>";
}
if (isset($login_error)) {
    echo "<p>$login_error</p>";
}
?>
</body>
</html>
