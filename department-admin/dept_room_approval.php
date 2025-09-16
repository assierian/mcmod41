<?php
require '../auth/middleware.php';
checkAccess(['Department Admin']);

// Include room status handler to automatically update room statuses
require_once '../auth/room_status_handler.php';

require 'includes/approve-reject.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Approval</title>
    <link rel="stylesheet" href="https://cdn.materialdesignicons.com/4.9.95/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="../public/css/admin_styles/main.css">
    <link rel="stylesheet" href="../public/css/admin_styles/main_2.css">
    <link rel="stylesheet" href="../public/css/admin_styles/style-all.css">
    <link rel="stylesheet" href="../public/css/admin_styles/form_2.css">
    <link rel="stylesheet" href="../public/css/admin_styles/room_approval.css">
    <link rel="stylesheet" href="../public/css/admin_styles/today_badge.css">

</head>

<body>
    <div id="app">
        <?php include 'layout/topnav.php'; ?>
        <?php include 'layout/sidebar.php'; ?>


        <div class="main-container">
            <div>
                <?php include 'components/main_contents/room_approval.php'; ?>
            </div>
        </div>

        <?php include 'components/modals/approval_rejection_modal.php'; ?>
        <?php include 'components/modals/approval_requests_modal.php'; ?>

    </div>

    <script type="text/javascript" src="../public/js/admin_scripts/main.min.js"></script>
    <script>
        // Pass PHP data to JavaScript
        window.totalRequests = <?php echo $requestCount; ?>;
    </script>
    <script>
        function showRequestDetails(requestId) {
            // Prepare the data for the modal
            <?php include 'components/shared/request_details_script.php'; ?>

            document.getElementById('detailsModal').style.display = 'block';
        }
    </script>
    <script type="text/javascript" src="../public/js/admin_scripts/room_approval.js"></script>

</body>

</html>