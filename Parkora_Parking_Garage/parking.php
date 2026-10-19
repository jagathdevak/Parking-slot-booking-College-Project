<?php

require 'config.php';

user_required();

$uid = $_SESSION['user_id'];

$c = $pdo->prepare(
    "SELECT id
     FROM parking_sessions
     WHERE user_id = ?
     AND status = 'Active'"
);

$c->execute([$uid]);

$active_session = $c->fetchColumn();

if ($active_session) {
    go('user_dashboard.php');
}

if (isset($_GET['park'])) {

    $slot_id = (int) $_GET['park'];

    $s = $pdo->prepare(
        "SELECT *
         FROM parking_slots
         WHERE id = ?
         AND status = 'Available'"
    );

    $s->execute([$slot_id]);

    $slot = $s->fetch();

    if ($slot) {

        $_SESSION['park_slot'] = $slot_id;

        go('park_vehicle.php');
    }
}

$q = trim($_GET['q'] ?? '');

$sql = "
    SELECT *
    FROM parking_slots
    WHERE status = 'Available'
";

$params = [];

if ($q !== '') {

    $sql .= "
        AND (
            slot_code LIKE ?
            OR floor LIKE ?
            OR zone LIKE ?
            OR vehicle_type LIKE ?
        )
    ";

    $search = "%{$q}%";

    $params = [
        $search,
        $search,
        $search,
        $search
    ];
}

$sql .= "
    ORDER BY floor, slot_code
";

$s = $pdo->prepare($sql);

$s->execute($params);

$slots = $s->fetchAll();

$page_title = 'Find Parking';

require 'includes/header.php';
?>

<section class="wrap">

    <div class="head">

        <div>

            <span class="eyebrow">
                PARKING DIRECTORY
            </span>

            <h2>
                Available spaces.
            </h2>

            <p class="muted">
                Choose an available slot and start your parking session.
            </p>

        </div>

        <span class="badge available">
            AVAILABLE NOW
        </span>

    </div>

    <form
        method="get"
        action="parking.php"
        style="display:flex;gap:10px;margin-bottom:25px"
    >

        <input
            class="search"
            name="q"
            value="<?= e($q) ?>"
            placeholder="Search slot, floor, zone or vehicle type..."
        >

        <button
            class="btn dark"
            type="submit"
        >
            Search
        </button>

    </form>

    <div class="cards">

        <?php foreach ($slots as $slot): ?>

            <div class="card">

                <div class="slot">

                    <div>

                        <div class="code">
                            <?= e($slot['slot_code']) ?>
                        </div>

                        <div class="muted">
                            <?= e($slot['floor']) ?>
                            · Zone
                            <?= e($slot['zone']) ?>
                        </div>

                    </div>

                    <span class="badge available">
                        AVAILABLE
                    </span>

                </div>

                <div class="rate">

                    ₹<?= number_format($slot['hourly_rate']) ?>

                    <small class="muted">
                        / hour
                    </small>

                </div>

                <p class="muted">
                    Suitable for:
                    <?= e($slot['vehicle_type']) ?>
                </p>

                <a
                    class="btn primary"
                    style="width:100%;text-align:center"
                    href="parking.php?park=<?= (int) $slot['id'] ?>"
                >
                    Park here →
                </a>

            </div>

        <?php endforeach; ?>

    </div>

    <?php if (!$slots): ?>

        <div class="card">

            <h3>
                No available spaces.
            </h3>

            <p class="muted">
                Try another search or check later.
            </p>

        </div>

    <?php endif; ?>

</section>

<?php require 'includes/footer.php'; ?>