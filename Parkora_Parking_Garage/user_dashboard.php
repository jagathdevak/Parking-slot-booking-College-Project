<?php

require 'config.php';

user_required();

$uid = $_SESSION['user_id'];

$notice = '';

if (isset($_GET['parked'])) {
    $notice = 'Parking session started. Your slot is now reserved.';
}

if (isset($_GET['returned'])) {
    $notice = 'Payment completed at the gate. Vehicle exited successfully.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['exit_session'])) {

    $id = (int) $_POST['exit_session'];

    $s = $pdo->prepare(
        "SELECT ps.*, s.slot_code, s.floor, s.hourly_rate
         FROM parking_sessions ps
         JOIN parking_slots s
         ON s.id = ps.slot_id
         WHERE ps.id = ?
         AND ps.user_id = ?
         AND ps.status = 'Active'"
    );

    $s->execute([
        $id,
        $uid
    ]);

    $x = $s->fetch();

    if ($x) {

        $entry = new DateTime($x['entry_time']);
        $exit = new DateTime();

        $seconds = $exit->getTimestamp() - $entry->getTimestamp();

        $minutes = max(
            1,
            (int) floor($seconds / 60)
        );

        $hours = max(
            1,
            (int) ceil($minutes / 60)
        );

        $amount = $hours * (float) $x['hourly_rate'];

        try {

            $pdo->beginTransaction();

            $pdo->prepare(
                "UPDATE parking_sessions
                 SET exit_time = NOW(),
                     amount = ?,
                     status = 'Completed'
                 WHERE id = ?"
            )->execute([
                $amount,
                $id
            ]);

            $pdo->prepare(
                "UPDATE parking_slots
                 SET status = 'Available'
                 WHERE id = ?"
            )->execute([
                $x['slot_id']
            ]);

            $pdo->commit();

            unset($_SESSION['park_slot']);
            unset($_SESSION['parking_payment']);
            unset($_SESSION['payment_transaction']);
            unset($_SESSION['payment_time']);
            unset($_SESSION['payment_method']);

            go('user_dashboard.php?returned=1');

        } catch (Throwable $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $notice = 'Unable to complete the exit payment.';
        }
    }
}

$s = $pdo->prepare(
    "SELECT ps.*,
            s.slot_code,
            s.floor,
            s.hourly_rate
     FROM parking_sessions ps
     JOIN parking_slots s
     ON s.id = ps.slot_id
     WHERE ps.user_id = ?
     ORDER BY ps.id DESC"
);

$s->execute([$uid]);

$all = $s->fetchAll();

$active = array_filter(
    $all,
    fn($x) => $x['status'] === 'Active'
);

$done = array_filter(
    $all,
    fn($x) => $x['status'] === 'Completed'
);

$page_title = 'My Garage';

require 'includes/header.php';
?>

<section class="dashboard-head">

    <span class="eyebrow">
        DRIVER DASHBOARD
    </span>

    <h1>
        Good to see you, <?= e($_SESSION['user_name']) ?>.
    </h1>

    <p class="muted">
        YOUR VEHICLE, CURRENT SPACE AND COMPLETE PARKING HISTORY.
    </p>

    <p class="muted">
        ONLY ONE ACTIVE PARKING SLOT PER USER
    </p>

</section>

<?php if ($notice): ?>

    <div
        class="alert success"
        style="margin:0 7% 25px"
    >
        <?= e($notice) ?>
    </div>

<?php endif; ?>

<div class="stats">

    <div class="stat">

        <span class="muted">
            Active parking
        </span>

        <strong>
            <?= count($active) ?>
        </strong>

    </div>

    <div class="stat">

        <span class="muted">
            Completed visits
        </span>

        <strong>
            <?= count($done) ?>
        </strong>

    </div>

    <div class="stat">

        <span class="muted">
            Total visits
        </span>

        <strong>
            <?= count($all) ?>
        </strong>

    </div>

    <div class="stat">

        <span class="muted">
            New parking
        </span>

        <?php if ($active): ?>

<a
    class="btn primary"
    href="#current-session"
    style="margin-top:8px"
    onclick="return checkParkingBeforeBooking(event)"
>
    Find a slot
</a>

<?php else: ?>

<a
    class="btn primary"
    href="parking.php"
    style="margin-top:8px"
>
    Find a slot
</a>

<?php endif; ?>

    </div>

</div>

<?php if ($active): ?>

<section
    class="wrap"
    id="current-session"
    style="padding-top:0"
>

    <div class="head">

        <div>

            <span class="eyebrow">
                CURRENT SESSION
            </span>

            <h2>
                Your vehicle is parked.
            </h2>

        </div>

    </div>

    <div class="cards">

        <?php foreach ($active as $x): ?>

            <?php

            $entry = new DateTime($x['entry_time']);
            $now = new DateTime();

            $seconds = max(
                60,
                $now->getTimestamp() - $entry->getTimestamp()
            );

            $minutes = (int) floor($seconds / 60);

            $hours = max(
                1,
                (int) ceil($minutes / 60)
            );

            $estimated_amount =
                $hours * (float) $x['hourly_rate'];

            ?>

            <div class="card">

                <div class="slot">

                    <div>

                        <div class="code">
                            <?= e($x['slot_code']) ?>
                        </div>

                        <div class="muted">
                            <?= e($x['vehicle_number']) ?>
                            ·
                            <?= e($x['vehicle_type']) ?>
                        </div>

                    </div>

                    <span class="badge occupied">
                        OCCUPIED
                    </span>

                </div>

                <p class="muted">

                    Entered
                    <?= date(
                        'd M Y, h:i A',
                        strtotime($x['entry_time'])
                    ) ?>

                    <br>

                    Rate
                    ₹<?= number_format($x['hourly_rate'], 2) ?>/hour

                </p>

                <div class="exit-preview">

                    <div>

                        <span>
                            Current estimated charge
                        </span>

                        <strong>
                            ₹<?= number_format($estimated_amount, 2) ?>
                        </strong>

                    </div>

                    <small>
                        Final charge is calculated when you exit.
                    </small>

                </div>

                <button
                    type="button"
                    class="btn dark"
                    style="width:100%;margin-top:15px"
                    onclick="openExitModal(
                        <?= (int) $x['id'] ?>,
                        '<?= e($x['slot_code']) ?>',
                        '<?= e($x['vehicle_number']) ?>',
                        '<?= e($x['vehicle_type']) ?>',
                        <?= (float) $x['hourly_rate'] ?>,
                        <?= (int) $minutes ?>,
                        <?= (float) $estimated_amount ?>
                    )"
                >
                    End parking & exit →
                </button>

            </div>

        <?php endforeach; ?>

    </div>

</section>

<?php endif; ?>

<section class="wrap">

    <div class="head">

        <div>

            <span class="eyebrow">
                PARKING HISTORY
            </span>

            <h2>
                Your visits.
            </h2>

        </div>

    </div>

    <div class="table-card">

        <table class="table">

            <tr>

                <th>
                    Slot
                </th>

                <th>
                    Vehicle
                </th>

                <th>
                    Entry
                </th>

                <th>
                    Exit
                </th>

                <th>
                    Charge
                </th>

                <th>
                    Status
                </th>

            </tr>

            <?php foreach ($all as $x): ?>

                <tr>

                    <td>

                        <b>
                            <?= e($x['slot_code']) ?>
                        </b>

                        <br>

                        <small>
                            <?= e($x['floor']) ?>
                        </small>

                    </td>

                    <td>
                        <?= e($x['vehicle_number']) ?>
                    </td>

                    <td>
                        <?= date(
                            'd M Y, h:i A',
                            strtotime($x['entry_time'])
                        ) ?>
                    </td>

                    <td>

                        <?= $x['exit_time']
                            ? date(
                                'd M Y, h:i A',
                                strtotime($x['exit_time'])
                            )
                            : '—'
                        ?>

                    </td>

                    <td>

                        <?= $x['amount'] !== null
                            ? '₹' . number_format(
                                $x['amount'],
                                2
                            )
                            : 'Running'
                        ?>

                    </td>

                    <td>

                        <span
                            class="badge <?= $x['status'] === 'Active'
                                ? 'occupied'
                                : 'available' ?>"
                        >
                            <?= e($x['status']) ?>
                        </span>

                    </td>

                </tr>

            <?php endforeach; ?>

        </table>

    </div>

</section>

<div
    id="exitModal"
    class="exit-modal"
    onclick="closeExitModal(event)"
>

    <div
        class="exit-modal-card"
        onclick="event.stopPropagation()"
    >

        <button
            type="button"
            class="exit-close"
            onclick="closeExitModal()"
        >
            ×
        </button>

        <div class="exit-icon">
            🚗
        </div>

        <span class="eyebrow">
            GATE EXIT
        </span>

        <h2>
            Ready to leave?
        </h2>

        <p class="muted">
            Your parking session is about to end.
            Please complete the parking payment at the gate before leaving the parking lot.
        </p>

        <div class="exit-details">

            <div>

                <span>
                    Parking slot
                </span>

                <strong id="exitSlot">
                    —
                </strong>

            </div>

            <div>

                <span>
                    Vehicle
                </span>

                <strong id="exitVehicle">
                    —
                </strong>

            </div>

            <div>

                <span>
                    Parking duration
                </span>

                <strong id="exitDuration">
                    —
                </strong>

            </div>

            <div class="exit-total">

                <span>
                    Amount payable at gate
                </span>

                <strong id="exitAmount">
                    ₹0.00
                </strong>

            </div>

        </div>

        <div class="gate-notice">

            <span>
                ✓
            </span>

            <div>

                <strong>
                    Payment required before exit
                </strong>

                <p>
                    Pay the displayed parking charge at the gate.
                    Once payment is completed, your parking slot will be released.
                </p>

            </div>

        </div>

        <form
            method="post"
            id="exitForm"
        >

            <input
                type="hidden"
                name="exit_session"
                id="exitSession"
            >

            <button
                type="submit"
                class="btn primary exit-pay-btn"
            >
                Pay ₹0.00 & Exit
            </button>

        </form>

        <button
            type="button"
            class="exit-cancel"
            onclick="closeExitModal()"
        >
            Keep parking
        </button>

    </div>

</div>

<div
    id="alreadyParkedModal"
    class="already-parked-modal"
    onclick="closeAlreadyParked(event)"
>

    <div
        class="already-parked-card"
        onclick="event.stopPropagation()"
    >

        <button
            type="button"
            class="already-parked-close"
            onclick="closeAlreadyParked()"
        >
            ×
        </button>

        <div class="already-parked-icon">
            🚗
        </div>

        <span class="eyebrow">
            PARKING ACTIVE
        </span>

        <h2>
            You're already parked.
        </h2>

        <p>
            Please exit your current parking session before booking another parking slot.
        </p>

        <a
            href="#current-session"
            class="btn primary"
            onclick="closeAlreadyParked()"
        >
            View current parking →
        </a>

        <div class="already-parked-count">
            This message will close automatically.
        </div>

    </div>

</div>

<script>

let alreadyParkedTimer;

function checkParkingBeforeBooking(event) {

    event.preventDefault();

    const modal =
        document.getElementById('alreadyParkedModal');

    modal.classList.add('show');

    document.body.classList.add('modal-open');

    clearTimeout(alreadyParkedTimer);

    alreadyParkedTimer = setTimeout(
        function() {
            closeAlreadyParked();
        },
        4500
    );

    return false;
}

function closeAlreadyParked(event) {

    if (
        event &&
        event.target !==
        document.getElementById('alreadyParkedModal')
    ) {
        return;
    }

    document
        .getElementById('alreadyParkedModal')
        .classList.remove('show');

    document.body.classList.remove('modal-open');

    clearTimeout(alreadyParkedTimer);
}

function openExitModal(
    sessionId,
    slot,
    vehicle,
    vehicleType,
    rate,
    minutes,
    amount
) {

    document.getElementById('exitSession').value =
        sessionId;

    document.getElementById('exitSlot').textContent =
        slot;

    document.getElementById('exitVehicle').textContent =
        vehicle + ' · ' + vehicleType;

    let durationText;

    if (minutes < 60) {

        durationText =
            minutes + ' minute' +
            (minutes === 1 ? '' : 's');

    } else {

        const hours =
            Math.floor(minutes / 60);

        const remaining =
            minutes % 60;

        durationText =
            hours + ' hour' +
            (hours === 1 ? '' : 's');

        if (remaining > 0) {

            durationText +=
                ' ' +
                remaining +
                ' min';

        }

    }

    document.getElementById('exitDuration').textContent =
        durationText;

    const formatted =
        '₹' +
        Number(amount).toLocaleString(
            'en-IN',
            {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }
        );

    document.getElementById('exitAmount').textContent =
        formatted;

    document.querySelector('.exit-pay-btn').textContent =
        'Pay ' + formatted + ' & Exit';

    document
        .getElementById('exitModal')
        .classList.add('show');

    document.body.classList.add('modal-open');
}

function closeExitModal(event) {

    if (
        event &&
        event.target !==
        document.getElementById('exitModal')
    ) {
        return;
    }

    document
        .getElementById('exitModal')
        .classList.remove('show');

    document.body.classList.remove('modal-open');
}

</script>

<?php require 'includes/footer.php'; ?>
