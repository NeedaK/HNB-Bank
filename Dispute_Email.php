<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    function sanitizeInput($data)
    {
        return htmlspecialchars(stripslashes(trim($data)));
    }

    // Basic Info
    $name = sanitizeInput($_POST["name"] ?? "");
    $address = sanitizeInput($_POST["address"] ?? "");
    $city = sanitizeInput($_POST["city"] ?? "");
    $phone = sanitizeInput($_POST["phone"] ?? "");
    $email = sanitizeInput($_POST["email"] ?? "");

    $contactMethod = "";
    if (isset($_POST['contactMethod'])) {
        if (is_array($_POST['contactMethod'])) {
            $contactMethod = implode(", ", array_map('sanitizeInput', $_POST['contactMethod']));
        } else {
            $contactMethod = sanitizeInput($_POST['contactMethod']);
        }
    }

    $debitCard = sanitizeInput($_POST["debitCard"] ?? "");
    $dateError = sanitizeInput($_POST["dateError"] ?? "");
    $disputeReason = sanitizeInput($_POST["dispute-reason"] ?? "N/A");

    // Validation
    if (empty($name) || empty($email) || empty($address) || empty($city) || empty($phone) || empty($debitCard) || empty($dateError)) {
        header("Location: index.html?status=required_fields");
        exit();
    }

    $debitCard = preg_replace('/\s+/', '', $debitCard);
    if (strlen($debitCard) != 16) {
        header("Location: index.html?status=invalid_debitCard");
        exit();
    }

    if (empty($contactMethod)) {
        header("Location: index.html?status=no_contactMethod");
        exit();
    }

    // Build Email
    $messageBody = "Dispute Form Submission Details:\n\n";
    $messageBody .= "Name/Business Name: $name\n";
    $messageBody .= "Address: $address\n";
    $messageBody .= "City, State, Zip: $city\n";
    $messageBody .= "Phone Number: $phone\n";
    $messageBody .= "Email Address: $email\n";
    $messageBody .= "Preferred Contact Method: $contactMethod\n";
    $messageBody .= "Debit Card Number: $debitCard\n";
    $messageBody .= "Awareness of the Error Date: $dateError\n";

    // How was this dispute sent
    $disputeSent = [];
    foreach (["contactInPerson" => "In Person", "contactEmailMethod" => "Email", "contactOnline" => "Online", "contactTelephone" => "Telephone"] as $key => $label) {
        if (isset($_POST[$key])) {  // Check if the checkbox was checked
            $disputeSent[] = $label;
        }
    }
    $messageBody .= "How was this dispute sent?: " . implode(", ", $disputeSent) . "\n";

    // Loss type
    $lossTypes = [];
    foreach (["lostCard" => "Lost Card", "stolenCard" => "Stolen Card", "possessionCard" => "Card was in my possession"] as $key => $label) {
        if (isset($_POST[$key])) { // Check if the checkbox was checked
            $lossTypes[] = $label;
        }
    }
    $messageBody .= "Loss Type: " . implode(", ", $lossTypes) . "\n";

   // Transactions
    $messageBody .= "Transactions/Amount/Date:\n";
    if (isset($_POST['merchant-name']) && isset($_POST['transaction-amount']) && isset($_POST['transaction-date'])) {
        $merchantNames = $_POST['merchant-name'];
        $transactionAmounts = $_POST['transaction-amount'];
        $transactionDates = $_POST['transaction-date'];

        $count = count($merchantNames);  // Use merchantNames to determine the count

        for ($i = 0; $i < $count; $i++) {
            $merchant = sanitizeInput($merchantNames[$i] ?? '');
            $amount = sanitizeInput($transactionAmounts[$i] ?? '');
            $date = sanitizeInput($transactionDates[$i] ?? '');

            if (!empty($merchant) || !empty($amount) || !empty($date)) {
                $messageBody .= "$merchant  \$$amount  $date\n";
            }
        }
    } else {
        $messageBody .= "No transactions provided.\n";
    }
    $messageBody .= "\n";

    $messageBody .= "Dispute Reason: $disputeReason\n\n";

    // ---------------------  DISPUTE REASON SECTIONS  ---------------------

    // Initialize variables
    $notReceivedMerchandiseSection = "";
    $notReceivedServiceSection = "";
    $recurringChargeCancelledSection = "";
    $notSatisfiedSection = "";
    $overchargedSection = "";
    $chargedTwiceSection = "";
    $expectingCreditSection = "";
    $completedOtherPaymentSection = "";

    // Handle Not Received Merchandise Section
    if ($disputeReason == "not-received-merchandise") {
        $describeMerchandise = sanitizeInput($_POST["describe-merchandise"] ?? "");
        $expectedDate = sanitizeInput($_POST["expected-date"] ?? "");

        //Get radio button value if set
        $deliveryLocation = isset($_POST['delivery-location']) ? sanitizeInput($_POST['delivery-location']) : '';
        $otherLocation = sanitizeInput($_POST["other-location"] ?? "");

        //Get radio button value if set
        $merchandiseCancelled = isset($_POST['merchandise-cancelled']) ? sanitizeInput($_POST['merchandise-cancelled']) : '';

        //Get radio button value if set
        $attemptedResolution = isset($_POST['attempted-resolution']) ? sanitizeInput($_POST['attempted-resolution']) : '';

        $resolveDetails = sanitizeInput($_POST["resolve-details"] ?? "");
        $noResolution = sanitizeInput($_POST["no-resolution"] ?? "");

        // Build the content for the section
        $notReceivedMerchandiseSection = "--- Merchandise or Ticket Info ---\n";
        $notReceivedMerchandiseSection .= "Merchandise or ticket ordered Description: $describeMerchandise\n";
        $notReceivedMerchandiseSection .= "Expected Receipt Date: $expectedDate\n";
        $notReceivedMerchandiseSection .= "Agreed Delivery Location: $deliveryLocation";
        if (!empty($otherLocation)) {
            $notReceivedMerchandiseSection .= " - " . $otherLocation;
        }
        $notReceivedMerchandiseSection .= "\n";
        $notReceivedMerchandiseSection .= "Cancelled due to Non-receipt: $merchandiseCancelled\n";
        $notReceivedMerchandiseSection .= "Attempt to resolve: $attemptedResolution\n";

        if (!empty($resolveDetails)) {
            $notReceivedMerchandiseSection .= "Date of contact, method, and response: $resolveDetails\n";
        }

        if (!empty($noResolution)) {
            $notReceivedMerchandiseSection .= "Why did you not contact the merchant?: $noResolution\n";
        }

        $messageBody .= $notReceivedMerchandiseSection . "\n"; // Append to main message
    }

    // Handle Not Received Service Section
    if ($disputeReason == "not-received-service") {
        $describeService = sanitizeInput($_POST["describe-service"] ?? "");
        $expectedServiceDate = sanitizeInput($_POST["expected-service-date"] ?? "");

        //Get radio button value if set
        $serviceCancelled = isset($_POST['service-cancelled']) ? sanitizeInput($_POST['service-cancelled']) : '';

        //Get radio button value if set
        $serviceAttemptedResolution = isset($_POST['service-attempted-resolution']) ? sanitizeInput($_POST['service-attempted-resolution']) : '';

        $serviceResolveDetails = sanitizeInput($_POST["service-resolve-details"] ?? "");
        $serviceNoResolution = sanitizeInput($_POST["service-no-resolution"] ?? "");

        // Build the content for the section
        $notReceivedServiceSection = "--- Service Info ---\n";
        $notReceivedServiceSection .= "Services ordered Description: $describeService\n";
        $notReceivedServiceSection .= "Expected Receipt Date: $expectedServiceDate\n";
        $notReceivedServiceSection .= "Cancelled due to Non-receipt: $serviceCancelled\n";
        $notReceivedServiceSection .= "Attempt to resolve: $serviceAttemptedResolution\n";

        if (!empty($serviceResolveDetails)) {
            $notReceivedServiceSection .= "Date of contact and method: $serviceResolveDetails\n";
        }

        if (!empty($serviceNoResolution)) {
            $notReceivedServiceSection .= "Why did you not contact the merchant?: $serviceNoResolution\n";
        }

        $messageBody .= $notReceivedServiceSection . "\n"; // Append to main message
    }

    // Handle Recurring Charge Cancelled Section
    if ($disputeReason == "recurring-charge-cancelled") {
        $cancellationDate = sanitizeInput($_POST["cancellation-date"] ?? "");
        $contactMethod = sanitizeInput($_POST["contact-method"] ?? "");

        // Build the content for the section
        $recurringChargeCancelledSection = "--- Recurring Charge Cancelled Info ---\n";
        $recurringChargeCancelledSection .= "Cancellation date: $cancellationDate\n";
        $recurringChargeCancelledSection .= "Date and method of contact: $contactMethod\n";

        $messageBody .= $recurringChargeCancelledSection . "\n"; // Append to main message
    }

    // Handle Not Satisfied with Merchandise or Service Section
    if ($disputeReason == "not-satisfied") {
        $notSatisfiedType = sanitizeInput($_POST["not-satisfied-type"] ?? ""); //radio
        $merchandiseIssue = sanitizeInput($_POST["merchandise-issue"] ?? "");
        $merchandiseDetails = sanitizeInput($_POST["merchandise-details"] ?? "");
        $serviceDetails = sanitizeInput($_POST["service-details"] ?? "");

        // Build the content for the section
        $notSatisfiedSection = "--- Not satisfied with Merchandise or Service Received ---\n";
        $notSatisfiedSection .= "Type: $notSatisfiedType\n";

        if ($notSatisfiedType == "merchandise") {
            $notSatisfiedSection .= "Merchandise Issue: $merchandiseIssue\n";
            $notSatisfiedSection .= "Merchandise Details: $merchandiseDetails\n";
        } elseif ($notSatisfiedType == "service") {
            $notSatisfiedSection .= "Service Details: $serviceDetails\n";
        }

        $messageBody .= $notSatisfiedSection . "\n"; // Append to main message
    }

    // Handle Overcharged or Incorrect Charge Section
    if ($disputeReason == "overcharged") {
        $overchargedAmount = sanitizeInput($_POST["overcharged-amount"] ?? "");

        // Build the content for the section
        $overchargedSection = "--- Overcharged or Incorrect Charge Info ---\n";
        $overchargedSection .= "Amount on Sales Receipt: $overchargedAmount\n";

        $messageBody .= $overchargedSection . "\n"; // Append to main message
    }

    // Handle Charged Twice Section
    if ($disputeReason == "charged-twice") {
        $chargedTwiceAmount = sanitizeInput($_POST["charged-twice-amount"] ?? "");
        $chargedTwiceDate = sanitizeInput($_POST["charged-twice-date"] ?? "");

        // Build the content for the section
        $chargedTwiceSection = "--- Charged twice Info ---\n";
        $chargedTwiceSection .= "Transaction Amount: $chargedTwiceAmount\n";
        $chargedTwiceSection .= "Transaction Date: $chargedTwiceDate\n";

        $messageBody .= $chargedTwiceSection . "\n"; // Append to main message
    }

    // Handle Expecting a Credit Section
    if ($disputeReason == "expecting-credit") {
        $creditType = sanitizeInput($_POST["credit-type"] ?? "");

        // Build the content for the section
        $expectingCreditSection = "--- Expecting a credit Info ---\n";
        $expectingCreditSection .= "Type of purchase: $creditType\n";

        // Further details based on the credit type can be added here if needed.
        if ($creditType == "merchandise") {
            $merchandiseDetails = sanitizeInput($_POST["merchandise-details"] ?? "");
            $expectingCreditSection .= "Merchandise Details: $merchandiseDetails\n";
            // Add more merchandise-specific details as needed
        } elseif ($creditType == "timeshare") {
            $timeshareDetails = sanitizeInput($_POST["timeshare-details"] ?? "");
            $expectingCreditSection .= "Timeshare Details: $timeshareDetails\n";
             //Add more timeshare-specific details as needed
        } elseif ($creditType == "service") {
            $serviceDetails = sanitizeInput($_POST["service-details"] ?? "");
            $expectingCreditSection .= "Service Details: $serviceDetails\n";
             //Add more service-specific details as needed
        }
        $messageBody .= $expectingCreditSection . "\n"; // Append to main message
    }

    // Handle Completed This Transaction with Another Form of Payment Section
    if ($disputeReason == "another-payment") {
        $resolveAttempt = sanitizeInput($_POST["resolve-attempt"] ?? "");
        $resolveDetails = sanitizeInput($_POST["resolve-details"] ?? "");
        $noReason = sanitizeInput($_POST["no-reason"] ?? "");
        $paymentMethod = sanitizeInput($_POST["payment-method"] ?? "");

        // Build the content for the section
        $completedOtherPaymentSection = "--- Completed this Transaction with Another Form of Payment ---\n";
        $completedOtherPaymentSection .= "Did the cardholder attempt to resolve the issue with the merchant?: $resolveAttempt\n";

        if (!empty($resolveDetails)) {
            $completedOtherPaymentSection .= "Details of most recent contact with the merchant: $resolveDetails\n";
        }

        if (!empty($noReason)) {
            $completedOtherPaymentSection .= "Reason why no resolution attempt was made: $noReason\n";
        }

        $completedOtherPaymentSection .= "Payment Method: $paymentMethod\n";

        $messageBody .= $completedOtherPaymentSection . "\n"; // Append to main message
    }

    // ---------------------  END DISPUTE REASON SECTIONS  ---------------------

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'anita.kraus54@gmail.com';
        $mail->Password = 'mibjxikfivlzcroy';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('anita.kraus54@gmail.com', 'HNB Bank Dispute Form');
        $mail->addAddress('anita.kraus54@gmail.com', 'Anita');

        $mail->isHTML(false);
        $mail->Subject = 'Dispute Form Submission';
        $mail->Body = $messageBody;

        $mail->send();
        header("Location: index.html?status=success");
    } catch (Exception $e) {
        header("Location: index.html?status=mailer_error&error=" . urlencode($mail->ErrorInfo));
    }
} else {
    echo "This script can only be accessed through the form.";
}
