<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
error_log("PDF Generation - Page accessed at " . date('Y-m-d H:i:s'));

try {
    require_once '../auth/middleware.php';
    checkAccess(['Student', 'Teacher']);
    error_log("PDF Generation - Middleware check passed");
} catch (Exception $e) {
    error_log("PDF Generation - Middleware error: " . $e->getMessage());
    die("Access error: Please ensure you are logged in and try again.");
}

include 'includes/pdf_contents.php'
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <title>Room Requisition Form</title>
    <!-- Include html2pdf.js library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <link href="../public/css/user_styles/generate_pdf.css" rel="stylesheet">
</head>

<body>

    <div class="button-container">
        <button class="action-button" id="backButton" onclick="window.location.href='users_reservation_history.php';">
            <i class="fa fa-arrow-left"></i> Back to Reservation
        </button>
        <button class="action-button" id="downloadPdf">
            <i class="fa fa-download"></i> Download PDF
        </button>
        <div id="download-message" style="display: none; margin-top: 10px; color: #28a745;">
            <i class="fa fa-check-circle"></i> PDF is being prepared...
        </div>
    </div>

    <?php include 'components/main_contents/pdf_contents.php'; ?>


    <script>
        document.getElementById('downloadPdf').addEventListener('click', function() {
            // Add class to body to preserve PDF formatting on mobile
            document.body.classList.add('pdf-generating');
            
            // Get the element to be converted to PDF
            const pdfContent = document.getElementById('pdf-content');

            // Show download message
            document.getElementById('download-message').style.display = 'block';

            // Set options for html2pdf
            const opt = {
                margin: 0,
                filename: 'room_requisition_form_<?php echo $requestId; ?>.pdf',
                image: {
                    type: 'jpeg',
                    quality: 0.98
                },
                html2canvas: {
                    scale: 2,
                    useCORS: true,
                    letterRendering: true,
                    width: 816,  // Legal size width in pixels at 96 DPI
                    height: 1344 // Legal size height in pixels at 96 DPI
                },
                jsPDF: {
                    unit: 'mm',
                    format: 'legal',
                    orientation: 'portrait'
                }
            };

            // Generate and download PDF
            html2pdf().set(opt).from(pdfContent).save().then(() => {
                // Remove the class after PDF generation
                document.body.classList.remove('pdf-generating');
                // Change message after download starts
                document.getElementById('download-message').innerHTML = '<i class="fa fa-check-circle"></i> PDF downloaded successfully!';
            }).catch(() => {
                // Remove the class if there's an error
                document.body.classList.remove('pdf-generating');
            });
        });
    </script>
</body>

</html>