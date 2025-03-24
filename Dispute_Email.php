<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

var_dump($_POST); exit;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recipient Email Address
    $to = "anita.kraus.13@student.hlg.edu";

    // Subject of the Email
    $subject = "Dispute Form Submission";

    // Function to Sanitize Input
    function sanitizeInput($data) {
        return htmlspecialchars(strip_tags($data), ENT_QUOTES, 'UTF-8');
    }

    // Sanitize Input Data
    $name = sanitizeInput($_POST["name"]);
    $address = sanitizeInput($_POST["address"]);
    $city = sanitizeInput($_POST["city"]);
    $phone = sanitizeInput($_POST["phone"]);
    $email = sanitizeInput($_POST["email"]);
    $contactMethod = isset($_POST["contactMethod"]) ? sanitizeInput($_POST["contactMethod"]) : "N/A";  // Handle unchecked checkboxes
    $debitCard = sanitizeInput($_POST["debitCard"]);
    $dateError = sanitizeInput($_POST["dateError"]);
    $contactMethod = isset($_POST["contactMethod"]) ? sanitizeInput($_POST["contactMethod"]) : "N/A"; // Checkbox.
    $lossType = isset($_POST["lossType"]) ? sanitizeInput($_POST["lossType"]) : "N/A";

    $merchantName = isset($_POST["merchant-name"]) ? sanitizeInput($_POST["merchant-name"]) : "N/A";
    $transactionAmount = isset($_POST["transaction-amount"]) ? sanitizeInput($_POST["transaction-amount"]) : "N/A";
    $transactionDate = isset($_POST["transaction-date"]) ? sanitizeInput($_POST["transaction-date"]) : "N/A";

    $disputeReason = isset($_POST["dispute-reason"]) ? sanitizeInput($_POST["dispute-reason"]) : "N/A";
    $cancellationDate = isset($_POST["cancellation-date"]) ? sanitizeInput($_POST["cancellation-date"]) : "N/A";
    $contactMethodDetail = isset($_POST["contact-method"]) ? sanitizeInput($_POST["contact-method"]) : "N/A";

    // Build the Email Body with Specific Formatting
    $message = "Dispute Form Submission Details:\n\n";
    $message .= "Name/Business Name: " . $name . "\n";
    $message .= "Address: " . $address . "\n";
    $message .= "City, State, Zip: " . $city . "\n";
    $message .= "Phone Number: " . $phone . "\n";
    $message .= "Email Address: " . $email . "\n";
    $message .= "Preferred Contact Method: " . $contactMethod . "\n";
    $message .= "Debit Card Number: " . $debitCard . "\n";
    $message .= "Awareness of the Error Date: " . $dateError . "\n";
    $message .= "How was this dispute sent?: " . $contactMethod . "\n";
    $message .= "Type of Loss: " . $lossType . "\n";
    $message .= "Transactions: " . $merchantName . " $" . $transactionAmount . " " . $transactionDate . "\n";
    $message .= "The following explains your dispute: " . $disputeReason . "\n";
    $message .= "Cancellation date: " . $cancellationDate . "\n";
    $message .= "Date and method of contact: " . $contactMethodDetail . "\n";

   
    // Additional Headers (Optional, but recommended)
    $headers = "From: webform@yourdomain.com\r\n"; // Replace with your domain (or a no-reply address)
    $headers .= "Reply-To: webform@yourdomain.com\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";  // Ensures proper character encoding
    $headers .= "X-Mailer: PHP/" . phpversion();

    // Send the Email
    if (mail($to, $subject, $message, $headers)) {
        // Success Message (You can redirect the user to a thank you page)
        //echo "<p>Thank you for your submission!  An email has been sent.</p>";
        //header("Location: thank_you.html"); // Redirect to a thank you page if you have one
      header("Location: Dispute_Form.html?status=success"); // Redirect back with success
    } else {
        // Error Message
        //echo "<p>Sorry, there was a problem sending your message. Please try again later.</p>";
        header("Location: Dispute_Form.html?status=error"); // Redirect back with error
    }
} else {
    // If someone tries to access the PHP file directly
    echo "<p>This page cannot be accessed directly.</p>";
}
?>