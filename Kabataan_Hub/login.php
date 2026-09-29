<link
    rel="stylesheet"
    href="assets/style.css"
>
<?php

require 'config.php';

$e = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get user by email
    $s = $conn->prepare(
        'SELECT * FROM users WHERE email=?'
    );

    $s->bind_param(
        's',
        $_POST['email']
    );

    $s->execute();

    $u = $s->get_result()->fetch_assoc();

    // Check password
    $ok = $u && password_verify(
        $_POST['password'],
        $u['password']
    );

    // Upgrade old MD5 password to password_hash
    if (
        $u &&
        !$ok &&
        strlen($u['password']) === 32 &&
        hash_equals(
            $u['password'],
            md5($_POST['password'])
        )
    ) {

        $new = password_hash(
            $_POST['password'],
            PASSWORD_DEFAULT
        );

        $q = $conn->prepare(
            'UPDATE users SET password=? WHERE id=?'
        );

        $q->bind_param(
            'si',
            $new,
            $u['id']
        );

        $q->execute();

        $ok = true;
    }

    // Login successful
    if ($ok) {

        session_regenerate_id(true);

        $_SESSION = $u;

        $_SESSION['password'] = $new ?? $u['password'];

        redirect('dashboard.php');
    }

    // Login failed
    $e = 'Invalid email or password.';
}

?>

<!doctype html>

<html>

<head>

    <meta
        name="viewport"
        content="width=device-width,initial-scale=1"
    >

    <title>Login</title>

    <link
        rel="stylesheet"
        href="assets/style.css"
    >

</head>

<style>body.auth {
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
}

/* WHITE LOGIN BOX */
body.auth .box {
    width: 100%;
    max-width: 380px;
    padding: 25px;
    margin: 0 auto;
    box-sizing: border-box;

    background: rgba(255, 255, 255, 0.70);
    border-radius: 3px;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
}</style>
<body class="auth">
<header class="navbar">
    <div class="logo">
        <img src="assets/kabataan-watermark.png" alt="Kabataan Hub Logo">

        <div class="logo-text">
            <span>Kabataan <b>Hub</b></span>
            <small>Kabataan para sa Mas Maliwanag na Bukas</small>
        </div>
    </div>

    <!-- Navigation -->
</header>

    <form
        class="box"
        method="post"
    >

        <h1>
            Kabataan <i>Hub</i>
        </h1>

        <h2>Login</h2>

        <?php if (isset($_GET['registered'])): ?>

            <div class="success">
                Account created. You can now login.
            </div>

        <?php endif; ?>


        <?php if ($e): ?>

            <div class="error">
                <?= e($e) ?>
            </div>

        <?php endif; ?>


        <input
            name="email"
            type="email"
            placeholder="Email"
            required
        >

        <input
            name="password"
            type="password"
            placeholder="Password"
            required
        >

        <button class="btn">
            Login
        </button>

        <p>
            No youth account?
            <a href="register.php">Register</a>
        </p>

        <small>
            Admin demo:
            admin@kabataan.local / admin123
        </small>

    </form>

</body>

</html>