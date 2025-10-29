<?php
// Test script to verify reservation counting logic
session_start();
require_once '../auth/dbh.inc.php';

if (!isset($_SESSION['user_id'])) {
    die("Please log in first to test reservation counting.");
}

db();

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];
$idField = ($userRole === 'Student') ? 'StudentID' : 'TeacherID';

echo "<!DOCTYPE html><html><head><title>Reservation Count Test</title></head><body>";
echo "<h1>Reservation Count Test</h1>";
echo "<p>Current Server Time: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>User ID: $userId</p>";
echo "<p>User Role: $userRole</p>";
echo "<hr>";

// Get all reservations for this user
$sql = "SELECT 
            RequestID,
            ActivityName,
            ReservationDate,
            StartTime,
            EndTime,
            Status,
            CONCAT(ReservationDate, ' ', EndTime) as FullEndDateTime,
            CASE 
                WHEN CONCAT(ReservationDate, ' ', EndTime) > NOW() THEN 'FUTURE (Ongoing)'
                WHEN CONCAT(ReservationDate, ' ', EndTime) <= NOW() THEN 'PAST (Completed)'
            END as TimeStatus
        FROM room_requests 
        WHERE $idField = ?
        ORDER BY ReservationDate DESC, EndTime DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

echo "<h2>All Your Reservations:</h2>";
echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr style='background: #f0f0f0;'>
        <th>ID</th>
        <th>Activity</th>
        <th>Date</th>
        <th>Time</th>
        <th>Full End DateTime</th>
        <th>Status</th>
        <th>Time Status</th>
      </tr>";

$ongoing = 0;
$completed = 0;
$cancelled = 0;

while ($row = $result->fetch_assoc()) {
    $bgColor = '';
    if ($row['Status'] == 'approved') {
        if ($row['TimeStatus'] == 'PAST (Completed)') {
            $bgColor = 'background: #d4edda;'; // Green
            $completed++;
        } else {
            $bgColor = 'background: #fff3cd;'; // Yellow
            $ongoing++;
        }
    } else if ($row['Status'] == 'rejected') {
        $bgColor = 'background: #f8d7da;'; // Red
        $cancelled++;
    }
    
    echo "<tr style='$bgColor'>";
    echo "<td>" . $row['RequestID'] . "</td>";
    echo "<td>" . htmlspecialchars($row['ActivityName']) . "</td>";
    echo "<td>" . $row['ReservationDate'] . "</td>";
    echo "<td>" . $row['StartTime'] . " - " . $row['EndTime'] . "</td>";
    echo "<td><strong>" . $row['FullEndDateTime'] . "</strong></td>";
    echo "<td>" . $row['Status'] . "</td>";
    echo "<td><strong>" . $row['TimeStatus'] . "</strong></td>";
    echo "</tr>";
}

echo "</table>";

echo "<hr>";
echo "<h2>Expected Counts:</h2>";
echo "<ul>";
echo "<li><strong style='color: #ffc107;'>Ongoing:</strong> $ongoing</li>";
echo "<li><strong style='color: #28a745;'>Completed:</strong> $completed</li>";
echo "<li><strong style='color: #dc3545;'>Cancelled:</strong> $cancelled</li>";
echo "</ul>";

// Now test the actual count query
$countSql = "SELECT 
                COUNT(*) as TotalCount,
                SUM(CASE WHEN CONCAT(ReservationDate, ' ', EndTime) > NOW() AND Status = 'approved' THEN 1 ELSE 0 END) as UpcomingCount,
                SUM(CASE WHEN CONCAT(ReservationDate, ' ', EndTime) <= NOW() AND Status = 'approved' THEN 1 ELSE 0 END) as CompletedCount,
                SUM(CASE WHEN Status = 'rejected' THEN 1 ELSE 0 END) as CancelledCount
             FROM room_requests 
             WHERE $idField = ?";
$countStmt = $conn->prepare($countSql);
$countStmt->bind_param("i", $userId);
$countStmt->execute();
$countResult = $countStmt->get_result();
$counts = $countResult->fetch_assoc();

echo "<hr>";
echo "<h2>Actual Query Results:</h2>";
echo "<ul>";
echo "<li><strong>Total:</strong> " . $counts['TotalCount'] . "</li>";
echo "<li><strong style='color: #ffc107;'>Ongoing (UpcomingCount):</strong> " . ($counts['UpcomingCount'] ?? 0) . "</li>";
echo "<li><strong style='color: #28a745;'>Completed (CompletedCount):</strong> " . ($counts['CompletedCount'] ?? 0) . "</li>";
echo "<li><strong style='color: #dc3545;'>Cancelled:</strong> " . ($counts['CancelledCount'] ?? 0) . "</li>";
echo "</ul>";

if ($ongoing == ($counts['UpcomingCount'] ?? 0) && 
    $completed == ($counts['CompletedCount'] ?? 0) && 
    $cancelled == ($counts['CancelledCount'] ?? 0)) {
    echo "<h3 style='color: green;'>✓ Counts match perfectly! The fix is working.</h3>";
} else {
    echo "<h3 style='color: red;'>✗ Counts don't match. There may still be an issue.</h3>";
}

echo "<hr>";
echo "<p><a href='users_reservation_history.php'>Back to Reservation History</a></p>";
echo "</body></html>";

$conn->close();
?>
