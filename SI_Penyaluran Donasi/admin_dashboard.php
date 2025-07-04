<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_username'])) {
    header("Location: admin_login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "donation_system";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch pending donation centers
    $stmt = $conn->prepare("SELECT dc.id, dc.name, dc.address, dc.contact_info, dc.signup_id, su.full_name
                            FROM donation_centers dc
                            INNER JOIN signup su ON dc.signup_id = su.id
                            WHERE dc.status = 'Pending'");
    $stmt->execute();
    $pending_centers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch financial donations with the associated donation center
    $stmt = $conn->prepare("SELECT fd.id, fd.amount, fd.contact_info, fd.status, su.full_name, dc.name AS donation_center_name
                            FROM financial_donations fd
                            INNER JOIN signup su ON fd.signup_id = su.id
                            INNER JOIN donation_centers dc ON dc.id = fd.donation_center_id
                            WHERE fd.status = 'Pending'");
    $stmt->execute();
    $financial_donations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch item donations with the associated donation center
    $stmt = $conn->prepare("SELECT id.id, id.item_type, id.location, id.contact_info, id.status, su.full_name, dc.name AS donation_center_name
                            FROM item_donations id
                            INNER JOIN signup su ON id.signup_id = su.id
                            INNER JOIN donation_centers dc ON dc.id = id.donation_center_id
                            WHERE id.status = 'Pending'");
    $stmt->execute();
    $item_donations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch donation requests with the associated donor's name
    $stmt = $conn->prepare("SELECT dr.id, dr.assistance_type, dr.description, dr.status, su.full_name AS donor_name
                            FROM donation_requests dr
                            INNER JOIN signup su ON dr.signup_id = su.id
                            WHERE dr.status = 'Pending'");
    $stmt->execute();
    $donation_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch contact messages
    $stmt = $conn->prepare("SELECT cm.full_name, cm.email, cm.message FROM contact_messages cm");
    $stmt->execute();
    $contact_messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Handle form submissions
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $id = $_POST['id'] ?? null;
        $action = $_POST['action'] ?? null;

        if ($action === 'approve_center') {
            $stmt = $conn->prepare("UPDATE donation_centers SET status = 'Approved' WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
        } elseif ($action === 'reject_center') {
            $reason = $_POST['reason'] ?? '';
            $stmt = $conn->prepare("UPDATE donation_centers SET status = 'Rejected', rejection_reason = :reason WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':reason', $reason);
            $stmt->execute();
        } elseif ($action === 'confirm_financial') {
            $stmt = $conn->prepare("UPDATE financial_donations SET status = 'Confirmed' WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
        } elseif ($action === 'confirm_item') {
            $stmt = $conn->prepare("UPDATE item_donations SET status = 'Confirmed' WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
        } elseif ($action === 'approve_request') {
            $stmt = $conn->prepare("UPDATE donation_requests SET status = 'Approved' WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
        } elseif ($action === 'reject_request') {
            $reason = $_POST['reason'] ?? '';
            $stmt = $conn->prepare("UPDATE donation_requests SET status = 'Rejected', rejection_reason = :reason WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':reason', $reason);
            $stmt->execute();
        }
        header("Location: admin_dashboard.php");
        exit();
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">
    <style>
        body { font-family: Arial, sans-serif; }
        .container { padding-top: 20px; }
        .section { display: none; }
        .section.active { display: block; }
        .table-wrapper { margin-top: 20px; }
        .nav-tabs .nav-link { cursor: pointer; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <a class="navbar-brand" href="#">Admin Dashboard</a>
        <button class="btn btn-danger" onclick="window.location.href='logout.php'">Logout</button>
    </nav>

    <div class="container">
        <!-- Navigation -->
        <ul class="nav nav-tabs">
            <li class="nav-item"><a class="nav-link active" onclick="showSection('centers')">Donation Centers</a></li>
            <li class="nav-item"><a class="nav-link" onclick="showSection('financial')">Financial Donations</a></li>
            <li class="nav-item"><a class="nav-link" onclick="showSection('items')">Item Donations</a></li>
            <li class="nav-item"><a class="nav-link" onclick="showSection('requests')">Assistance Requests</a></li>
            <li class="nav-item"><a class="nav-link" onclick="showSection('messages')">Messages</a></li>
        </ul>

        <!-- Donation Centers -->
        <div id="centers" class="section active">
            <h2>Pending Donation Centers</h2>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Address</th>
                            <th>Contact</th>
                            <th>Submitted By</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_centers as $center): ?>
                        <tr>
                            <td><?= htmlspecialchars($center['name']) ?></td>
                            <td><?= htmlspecialchars($center['address']) ?></td>
                            <td><?= htmlspecialchars($center['contact_info']) ?></td>
                            <td><?= htmlspecialchars($center['full_name']) ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $center['id'] ?>">
                                    <button class="btn btn-success btn-sm" name="action" value="approve_center">Approve</button>
                                </form>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $center['id'] ?>">
                                    <input type="text" name="reason" placeholder="Reason" required>
                                    <button class="btn btn-danger btn-sm" name="action" value="reject_center">Reject</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Financial Donations -->
        <div id="financial" class="section">
            <h2>Pending Financial Donations</h2>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Donatur</th>
                            <th>Amount</th>
                            <th>Contact Info</th>
                            <th>Donation Center</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($financial_donations as $donation): ?>
                        <tr>
                            <td><?= htmlspecialchars($donation['full_name']) ?></td>
                            <td><?= htmlspecialchars($donation['amount']) ?></td>
                            <td><?= htmlspecialchars($donation['contact_info']) ?></td>
                            <td><?= htmlspecialchars($donation['donation_center_name']) ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $donation['id'] ?>">
                                    <button class="btn btn-success btn-sm" name="action" value="confirm_financial">Confirm</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Item Donations -->
        <div id="items" class="section">
            <h2>Pending Item Donations</h2>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Donatur</th>
                            <th>Item Type</th>
                            <th>Location</th>
                            <th>Contact Info</th>
                            <th>Donation Center</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($item_donations as $donation): ?>
                        <tr>
                            <td><?= htmlspecialchars($donation['full_name']) ?></td>
                            <td><?= htmlspecialchars($donation['item_type']) ?></td>
                            <td><?= htmlspecialchars($donation['location']) ?></td>
                            <td><?= htmlspecialchars($donation['contact_info']) ?></td>
                            <td><?= htmlspecialchars($donation['donation_center_name']) ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $donation['id'] ?>">
                                    <button class="btn btn-success btn-sm" name="action" value="confirm_item">Confirm</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Donation Requests -->
        <div id="requests" class="section">
            <h2>Pending Donation Requests</h2>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Assistance Type</th>
                            <th>Description</th>
                            <th>Donor Name</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($donation_requests as $request): ?>
                        <tr>
                            <td><?= htmlspecialchars($request['assistance_type']) ?></td>
                            <td><?= htmlspecialchars($request['description']) ?></td>
                            <td><?= htmlspecialchars($request['donor_name']) ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $request['id'] ?>">
                                    <button class="btn btn-success btn-sm" name="action" value="approve_request">Approve</button>
                                </form>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $request['id'] ?>">
                                    <input type="text" name="reason" placeholder="Reason" required>
                                    <button class="btn btn-danger btn-sm" name="action" value="reject_request">Reject</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Contact Messages -->
        <div id="messages" class="section">
            <h2>Contact Messages</h2>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contact_messages as $message): ?>
                        <tr>
                            <td><?= htmlspecialchars($message['full_name']) ?></td>
                            <td><?= htmlspecialchars($message['email']) ?></td>
                            <td><?= htmlspecialchars($message['message']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function showSection(section) {
            var sections = document.querySelectorAll('.section');
            sections.forEach(function(sec) {
                sec.classList.remove('active');
            });

            document.getElementById(section).classList.add('active');

            var navLinks = document.querySelectorAll('.nav-link');
            navLinks.forEach(function(link) {
                link.classList.remove('active');
            });

            document.querySelector(`[onclick="showSection('${section}')"]`).classList.add('active');
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
