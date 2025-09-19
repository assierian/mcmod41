<?php
// Check if all required parameters are set
$requiredParams = [
    'requestId',
    'activityName',
    'buildingName',
    'roomName',
    'reservationDate',
    'startTime',
    'endTime',
    'participants',
    'purpose'
];

// Get parameters from either POST or GET
$requestData = $_GET ?: $_POST;

foreach ($requiredParams as $param) {
    if (!isset($requestData[$param])) {
        die("Error: Missing required parameter: $param");
    }
}

// Get data
$requestId = $requestData['requestId'];
$activityName = $requestData['activityName'];
$buildingName = $requestData['buildingName'];
$roomName = $requestData['roomName'];
$reservationDate = $requestData['reservationDate'];
$startTime = $requestData['startTime'];
$endTime = $requestData['endTime'];
$participants = $requestData['participants'];
$purpose = $requestData['purpose'];
$status = $requestData['status'] ?? 'approved';

// Get current date
$currentDate = date('F j, Y');

// Get user info from session
$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];
db();

// Get user information based on role
if ($userRole == 'Student') {
    $sql = "SELECT FirstName, LastName, Department, Program, YearSection FROM student WHERE StudentID = ?";
} else if ($userRole == 'Teacher') {
    $sql = "SELECT FirstName, LastName, Department, Position, Specialization FROM teacher WHERE TeacherID = ?";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();

// Close database connection
$conn->close();

// HTML content generation