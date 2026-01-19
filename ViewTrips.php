<?php
session_start();
require_once 'dbConnect.php'; 

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM trip_planner WHERE user_id = :user_id ORDER BY start_date DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute(['user_id' => $user_id]);
$trips = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>View My Trips</title>
  <style>
    body {
      font-family: 'Poppins', Verdana, Arial, Helvetica, sans-serif;
      background: #f9f9f9;
      color: #333;
      max-width: 900px;
      margin: 40px auto;
      padding: 0 20px;
    }
    h1 {
      text-align: center;
      color: #aa6413;
      margin-bottom: 30px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 40px;
    }
    th, td {
      border: 1px solid #aa6413;
      padding: 12px 15px;
      text-align: left;
    }
    th {
      background-color: #aa6413;
      color: white;
    }
    tr:nth-child(even) {
      background-color: #ffefcc;
    }
    .no-trips {
      text-align: center;
      font-size: 1.2rem;
      color: #666;
      margin-top: 50px;
    }
    .back-button {
      display: inline-block;
      background-color: #613604;
      color: white;
      padding: 10px 20px;
      border-radius: 6px;
      text-decoration: none;
      transition: background-color 0.3s ease;
      user-select: none;
    }
    .back-button:hover {
      background-color: #aa6413;
    }
  </style>
</head>
<body>

  <h1>My Planned Trips</h1>

  <?php if (count($trips) === 0): ?>
    <p class="no-trips">You have not planned any trips yet.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Destination</th>
          <th>Start Date</th>
          <th>End Date</th>
          <th>Notes</th>
          <th>Created At</th>
          <th>Activities</th>
          <th>Trip Info Needs</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($trips as $trip): ?>
          <tr>
            <td><?= htmlspecialchars($trip['city'] ?? '') . ', ' . htmlspecialchars($trip['region'] ?? '') ?></td>
            <td><?= htmlspecialchars($trip['start_date']) ?></td>
            <td><?= htmlspecialchars($trip['end_date']) ?></td>
            <td><?= nl2br(htmlspecialchars($trip['notes'])) ?></td>
            <td><?= htmlspecialchars($trip['created_at'] ?? 'N/A') ?></td>
            <td>
              <?php
              $activities_arr = json_decode($trip['activities'] ?? '[]', true);
              echo htmlspecialchars(implode(', ', $activities_arr));
              ?>
            </td>
            <td>
              <?php
              $trip_info_needs_arr = json_decode($trip['trip_info_needs'] ?? '[]', true);
              echo htmlspecialchars(implode(', ', $trip_info_needs_arr));
              ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

  <a href="TripPlanner.php" class="back-button">← Back to Trip Planner</a>

</body>
</html>
