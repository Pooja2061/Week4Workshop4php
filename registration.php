<?php
$name = "";
$email = "";
$errors = [];
$success = "";

$usersFile = __DIR__ . "/users.json";
if (!file_exists($usersFile)) {
    file_put_contents($usersFile, json_encode([]));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($name === "") $errors['name'] = "Name is required.";

    if ($email === "") $errors['email'] = "Email is required.";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = "Invalid email format.";

    if ($password === "") $errors['password'] = "Password is required.";
    elseif (strlen($password) < 8) $errors['password'] = "Password must be at least 8 characters.";
    elseif (!preg_match('/^(?=.*[A-Za-z])(?=.*\d).+$/', $password)) $errors['password'] = "Password must contain letters and numbers.";

    if ($password !== $confirm_password) $errors['confirm_password'] = "Passwords do not match.";

    $usersData = file_get_contents($usersFile);
    $users = json_decode($usersData, true);
    if (!is_array($users)) $users = [];

    foreach ($users as $user) {
        if ($user['email'] === $email) {
            $errors['email'] = "This email is already registered.";
            break;
        }
    }

    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $users[] = [
            "name" => $name,
            "email" => $email,
            "password" => $hashedPassword,
            "registered_at" => date("Y-m-d H:i:s")
        ];

        $saved = file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));

        if ($saved) {
            $success = "Registration successful!";
            $name = "";
            $email = "";
        } else {
            $errors['file'] = "Error saving user data.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Registration</title>
<style>
body { font-family: Arial; max-width: 600px; margin: 40px auto; background: #f4f4f4; padding: 20px; }
.container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
label { margin-top: 12px; display: block; font-weight: bold; }
input { width: 100%; padding: 10px; margin-top: 5px; border-radius: 5px; border: 1px solid #ccc; }
button { padding: 12px; background: #007bff; color: #fff; border: none; border-radius: 5px; cursor: pointer; margin-top: 20px; }
button:hover { background: #0056b3; }
.error { color: red; font-size: 0.9em; }
.success { color: green; background: #d4edda; padding: 12px; border-radius: 5px; margin: 15px 0; text-align: center; }
</style>
</head>
<body>
<div class="container">
<h2>User Registration</h2>

<?php if ($success): ?>
<div class="success"><?= $success ?></div>
<?php endif; ?>

<form method="POST">
<label>Full Name</label>
<input type="text" name="name" value="<?= htmlspecialchars($name) ?>">
<div class="error"><?= $errors['name'] ?? '' ?></div>

<label>Email</label>
<input type="email" name="email" value="<?= htmlspecialchars($email) ?>">
<div class="error"><?= $errors['email'] ?? '' ?></div>

<label>Password</label>
<input type="password" name="password">
<div class="error"><?= $errors['password'] ?? '' ?></div>

<label>Confirm Password</label>
<input type="password" name="confirm_password">
<div class="error"><?= $errors['confirm_password'] ?? '' ?></div>

<div class="error"><?= $errors['file'] ?? '' ?></div>

<button type="submit">Register</button>
</form>
</div>
</body>
</html>
