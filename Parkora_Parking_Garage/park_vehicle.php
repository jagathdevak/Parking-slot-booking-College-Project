<?php

require 'config.php';

user_required();

$id = (int) ($_SESSION['park_slot'] ?? 0);

$s = $pdo->prepare(
    "SELECT *
     FROM parking_slots
     WHERE id = ?"
);

$s->execute([$id]);

$slot = $s->fetch();

if (!$slot || $slot['status'] !== 'Available') {

    unset($_SESSION['park_slot']);

    go('parking.php');
}

$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $v = strtoupper(
        preg_replace(
            '/\s+/',
            '',
            trim($_POST['vehicle_number'] ?? '')
        )
    );

    $t = $_POST['vehicle_type'] ?? 'Car';

    $c = $pdo->prepare(
        "SELECT id
         FROM parking_sessions
         WHERE user_id = ?
         AND status = 'Active'
         LIMIT 1"
    );

    $c->execute([
        $_SESSION['user_id']
    ]);

    if (!$v) {

        $err = 'Enter your vehicle registration number.';

    } elseif ($c->fetch()) {

        $err = 'You already have an active parking session.';

    } else {

        $c = $pdo->prepare(
            "SELECT ps.id
             FROM parking_sessions ps
             WHERE REPLACE(UPPER(ps.vehicle_number), ' ', '') = ?
             AND ps.status = 'Active'
             LIMIT 1"
        );

        $c->execute([$v]);

        if ($c->fetch()) {

            $err = 'This vehicle is already parked by another user.';

        } else {

            try {

                $pdo->beginTransaction();

                $c = $pdo->prepare(
                    "SELECT status
                     FROM parking_slots
                     WHERE id = ?
                     FOR UPDATE"
                );

                $c->execute([$id]);

                if ($c->fetchColumn() !== 'Available') {

                    throw new Exception(
                        'This slot was just taken.'
                    );
                }

                $c = $pdo->prepare(
                    "SELECT ps.id
                     FROM parking_sessions ps
                     WHERE REPLACE(UPPER(ps.vehicle_number), ' ', '') = ?
                     AND ps.status = 'Active'
                     LIMIT 1"
                );

                $c->execute([$v]);

                if ($c->fetch()) {

                    throw new Exception(
                        'This vehicle is already parked by another user.'
                    );
                }

                $c = $pdo->prepare(
                    "INSERT INTO parking_sessions
                    (
                        user_id,
                        slot_id,
                        vehicle_number,
                        vehicle_type,
                        entry_time
                    )
                    VALUES
                    (
                        ?,
                        ?,
                        ?,
                        ?,
                        NOW()
                    )"
                );

                $c->execute([
                    $_SESSION['user_id'],
                    $id,
                    $v,
                    $t
                ]);

                $session_id = $pdo->lastInsertId();

                $pdo->prepare(
                    "UPDATE parking_slots
                     SET status = 'Occupied'
                     WHERE id = ?"
                )->execute([$id]);

                $pdo->commit();

                unset($_SESSION['park_slot']);

                unset($_SESSION['parking_payment']);
                unset($_SESSION['payment_transaction']);
                unset($_SESSION['payment_time']);
                unset($_SESSION['payment_method']);

                $_SESSION['last_parking_session'] =
                    $session_id;

                go('user_dashboard.php?parked=1');

            } catch (Throwable $e) {

                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                $err = $e->getMessage();
            }
        }
    }
}

$page_title = 'Start Parking';

require 'includes/header.php';
?>

<div class="form-wrap">

    <div class="form-card">

        <span class="eyebrow">
            START PARKING
        </span>

        <h1>
            Park at <?= e($slot['slot_code']) ?>.
        </h1>

        <p class="muted">
            <?= e($slot['floor']) ?>
            · Zone <?= e($slot['zone']) ?>
            · ₹<?= number_format($slot['hourly_rate']) ?> / hour
        </p>

        <?php if ($err): ?>

            <div class="alert">
                <?= e($err) ?>
            </div>

        <?php endif; ?>

        <form method="post">

            <div class="field">

                <label>
                    Vehicle registration
                </label>

                <input
                    name="vehicle_number"
                    placeholder="KL 01 AB 1234"
                    required
                    autocomplete="off"
                >

            </div>

            <div class="field">

                <label>
                    Vehicle type
                </label>

                <select name="vehicle_type">

                    <option>
                        Car
                    </option>

                    <option>
                        Bike
                    </option>

                    <option>
                        SUV
                    </option>

                    <option>
                        EV
                    </option>

                </select>

            </div>

            <button
                class="btn primary"
                style="width:100%"
                type="submit"
            >
                Start parking session →
            </button>

        </form>

    </div>

</div>

<?php require 'includes/footer.php'; ?>