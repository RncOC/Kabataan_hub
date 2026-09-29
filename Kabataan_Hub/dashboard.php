<?php
require 'config.php';
require_login();
$admin = $_SESSION['role'] === 'admin';
$A = $conn->query('SELECT * FROM announcements ORDER BY created_at DESC');
$E = $conn->query('SELECT * FROM events ORDER BY event_date');
$P = $conn->query('SELECT * FROM programs ORDER BY created_at DESC');
$O = $conn->query('SELECT * FROM opportunities ORDER BY created_at DESC');
$X = $conn->query('SELECT * FROM projects ORDER BY created_at DESC');
$N = $conn->query("SELECT * FROM notifications WHERE user_id=" . (int)$_SESSION['id'] . " ORDER BY created_at DESC LIMIT 10");
$unread = $conn->query("SELECT COUNT(*) c FROM notifications WHERE user_id=" . (int)$_SESSION['id'] . " AND is_read=0")->fetch_assoc()['c'];
$regs = $conn->query("SELECT r.*,e.title,e.event_date FROM registrations r JOIN events e ON e.id=r.event_id WHERE r.user_id=" . (int)$_SESSION['id'] . " ORDER BY r.created_at DESC");
$fb = $conn->query("SELECT * FROM feedback WHERE user_id=" . (int)$_SESSION['id'] . " ORDER BY created_at DESC");
$sr = $conn->query("SELECT * FROM service_requests WHERE user_id=" . (int)$_SESSION['id'] . " ORDER BY created_at DESC");
?>
<!doctype html>
<html>

<head>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Dashboard</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    

    <!-- Navigation -->
</header>
    <nav>
        <div class="logo">
        <img src="assets/kabataan-watermark.png" alt="Kabataan Hub Logo">

        <div class="logo-text">
            <span>Kabataan <b>Hub</b></span><br>
             <small>Kabataan para sa Mas Maliwanag na Bukas</small>
        </div>
       <div></div><div></div><div></div>
        <div><span>Hi, <?= e($_SESSION['name']) ?></span><?php if ($admin): ?><a href="admin.php">Admin Panel</a><?php endif; ?><a href="#notifications">🔔 <?= $unread ?></a><a href="#profile">Profile</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>
    <main>
        <h1><?= $admin ? 'Admin' : 'Kabataan' ?> Dashboard</h1>
        <form class="search" method="get">
            <input id="searchBox" placeholder="Search announcements, events, programs, opportunities..." onkeyup="filterCards()">
        </form>
        <section>
            <h2>📢 Announcements</h2>
            <div class="grid searchable"><?php while ($x = $A->fetch_assoc()): ?><article>
                        <h3><?= e($x['title']) ?></h3>
                        <p><?= nl2br(e($x['content'])) ?></p>
                    </article><?php endwhile; ?></div>
        </section>
        <section>
            <h2>📅 Events</h2>
            <div class="grid searchable"><?php while ($x = $E->fetch_assoc()): ?><article>
                        <h3><?= e($x['title']) ?></h3>
                        <p><b><?= $x['event_date'] ?></b> • <?= e($x['location']) ?></p>
                        <p><?= e($x['description']) ?></p><?php if (!$admin): ?><form method="post" action="action.php">
                                <input type="hidden" name="action" value="register">
                                <input type="hidden" name="event_id" value="<?= $x['id'] ?>">
                                <button class="btn">Register</button>
                            </form><?php endif; ?>
                    </article><?php endwhile; ?></div>
        </section>
        <section>
            <h2>📋 Programs</h2>
            <div class="grid searchable"><?php while ($x = $P->fetch_assoc()): ?>
                    <article>
                        <h3><?= e($x['title']) ?></h3>
                        <p><?= e($x['description']) ?></p>
                        <span class="badge"><?= e($x['status']) ?></span>
                    </article><?php endwhile; ?>
            </div>
        </section>
        <section>
            <h2>🎓 Opportunities</h2>
            <div class="grid searchable"><?php while ($x = $O->fetch_assoc()): ?><article>
                        <h3><?= e($x['title']) ?></h3>
                        <p><?= e($x['description']) ?></p><?php if ($x['link']): ?><a class="btn small" href="<?= e($x['link']) ?>" target="_blank">View Opportunity</a><?php endif; ?>
                    </article><?php endwhile; ?></div>
        </section>
        <section>
            <h2>💰 Projects & Transparency</h2>
            <div class="grid searchable"><?php while ($x = $X->fetch_assoc()): ?><article>
                        <h3><?= e($x['title']) ?></h3>
                        <p><?= e($x['description']) ?></p><b>₱<?= number_format($x['budget'], 2) ?></b> • <?= e($x['status']) ?>
                    </article><?php endwhile; ?></div>
        </section>
        <?php if (!$admin): ?><section>
                <h2>📌 My Activity</h2>
                <div class="two">
                    <article>
                        <h3>Event Registrations</h3><?php while ($r = $regs->fetch_assoc()): ?>
                            <p><?= e($r['title']) ?> — <span class="badge"><?= e($r['status']) ?></span>
                            </p><?php endwhile; ?>
                    </article>
                    <article>
                        <h3>My Feedback</h3><?php while ($f = $fb->fetch_assoc()): ?><p><?= e($f['subject']) ?> — <span class="badge"><?= e($f['status']) ?></span></p><?php endwhile; ?>
                    </article>
                </div>
                <div class="two">
                    <article>
                        <h3>Service Requests</h3><?php while ($s = $sr->fetch_assoc()): ?><p><?= e($s['service_type']) ?> — <span class="badge"><?= e($s['status']) ?></span></p><?php endwhile; ?>
                    </article>
                    <form class="box" method="post" action="action.php"><input type="hidden" name="action" value="feedback">
                        <h3>💬 Submit Feedback</h3><input name="subject" placeholder="Subject" required><textarea name="message" placeholder="Message" required></textarea><button class="btn">Submit</button>
                    </form>
                </div>
                <div class="two">
                    <form class="box" method="post" action="action.php"><input type="hidden" name="action" value="service">
                        <h3>📝 Service Request</h3><input name="service_type" placeholder="Service type" required><textarea name="details" placeholder="Details" required></textarea><button class="btn">Submit</button>
                    </form>
                </div>
            </section><?php endif; ?>
        <section id="notifications">
            <h2>🔔 Notifications</h2>
            <form method="post" action="action.php">
                <input type="hidden" name="action" value="read_notifications">
                <button class="btn secondary">Mark all as read</button>
            </form>
            <?php while ($n = $N->fetch_assoc()): ?>
                <article class="<?= !$n['is_read'] ? 'unread' : '' ?>">
                    <b><?= e($n['title']) ?></b>
                    <p><?= e($n['message']) ?></p>
                    <small><?= $n['created_at'] ?></small>
                </article><?php endwhile; ?>
        </section>
        <section id="profile">
            <h2>⚙️ Profile & Account Settings</h2>
            <form class="box" method="post" action="action.php">
                <input type="hidden" name="action" value="profile">
                <input name="name" value="<?= e($_SESSION['name']) ?>" placeholder="Name" required><input name="email" type="email" value="<?= e($_SESSION['email']) ?>" placeholder="Email" required><input name="phone" value="<?= e($_SESSION['phone'] ?? '') ?>" placeholder="Phone"><input name="address" value="<?= e($_SESSION['address'] ?? '') ?>" placeholder="Address"><input name="new_password" type="password" minlength="6" placeholder="New password (leave blank to keep current)">
                <button class="btn">Save Account</button>
            </form>
        </section>
    </main>
    <script>
        function filterCards() {
            let q = document.getElementById('searchBox').value.toLowerCase();
            document.querySelectorAll('.searchable article').forEach(a => a.style.display = a.innerText.toLowerCase().includes(q) ? '' : 'none')
        }
    </script>
</body>

</html>