
//Phone Number Validation and Formatting
document.addEventListener("DOMContentLoaded", function () {
    // Auto-format for Phone Number (XXX) XXX-XXXX
    document.getElementById("phone").addEventListener("input", function (event) {
        let input = event.target.value.replace(/\D/g, ""); // Remove all non-digit characters
        let formattedInput = "";

        if (input.length > 0) {
            formattedInput = "(" + input.substring(0, 3);
        }
        if (input.length >= 4) {
            formattedInput += ") " + input.substring(3, 6);
        }
        if (input.length >= 7) {
            formattedInput += "-" + input.substring(6, 10);
        }

        event.target.value = formattedInput;
    });

// Auto-format for Debit Card Number #### #### #### #### (Limit to 16 digits)
    document.getElementById("debitCard").addEventListener("input", function (event) {
        let input = event.target.value.replace(/\D/g, ""); // Remove non-numeric characters

        if (input.length > 16) {
            input = input.substring(0, 16); // Limit to 16 digits
        }
    });
// Add a new transaction row
    const transactionContainer = document.getElementById("transactions-container");
    const addTransactionButton = document.getElementById("add-transaction");

    addTransactionButton.addEventListener("click", function (event) {
        event.preventDefault(); // Prevent link from navigating

        const transactionRows = document.querySelectorAll(".transaction-row");
        if (transactionRows.length < 20) {
            const newRow = document.createElement("div");
            newRow.classList.add("transaction-row");

            newRow.innerHTML = `
                <input type="text" class="merchant-name" placeholder="Merchant Name">
                <input type="number" class="transaction-amount" placeholder="$0.00" min="0" step="0.01">
                <input type="date" class="transaction-date">
                <button type="button" class="remove-transaction">X</button>
            `;

            transactionContainer.appendChild(newRow);

            newRow.querySelector(".remove-transaction").addEventListener("click", function () {
                newRow.remove();
            });
        } else {
            alert("You can only add up to 20 transactions.");
        }
    });
// Remove transaction row
    document.addEventListener("click", function (event) {
        if (event.target.classList.contains("remove-transaction")) {
            event.target.parentElement.remove();
        }
    });
//Currency Formatting Function in Tranaction's Field
    function formatCurrency(input) {
        // Remove any non-numeric characters
        let value = input.value.replace(/\D/g, "");

        // Convert value to cents, ensuring at least two digits
        if (value.length === 0) {
            input.value = "$0.00";
            return;
        }

        let numValue = parseFloat(value) / 100; // Convert to dollars (e.g., 1050 -> 10.50)

    // Format as currency
        input.value = `$${numValue.toFixed(2)}`;
    }

    document.querySelectorAll(".transaction-amount").forEach(input => {
        // Format the value as the user types
        input.addEventListener("input", function () {
            formatCurrency(input);
        });

        // Ensure formatting is applied when leaving the input field
        input.addEventListener("blur", function () {
            if (input.value === "" || input.value === "$0.00") {
                input.value = "$0.00"; // Default value
            }
        });
    });

    // Submittion For Required Fields and Validations
    document.getElementById("disputeForm").addEventListener("submit", function (event) {
        event.preventDefault();
        
        let name = document.getElementById("name").value;
        let email = document.getElementById("email").value;
        let debitCard = document.getElementById("debitCard").value.replace(/\s/g, ""); // Remove spaces for validation
        let contactMethod = document.querySelector('input[name="contactMethod"]:checked');

        if (name.trim() === "" || email.trim() === "") {
            alert("Please fill in all required fields.");
        } else if (debitCard.length !== 16) {
            alert("Please enter a valid 16-digit debit card number.");
        } else if (!contactMethod) {
            alert("Please select a Preferred Contact Method (Email or Mail).");
        } else {
            alert("Dispute Form Submitted Successfully!");
            this.reset();
        }
    });

//Not Received Merchandise Section (Initially Hidden)
    const disputeReason = document.getElementById("dispute-reason"); // The dropdown or selection field
    const notReceivedSection = document.getElementById("not-received-merchandise-section");

    // Show/Hide Not Received Merchandise Section
    function toggleNotReceivedSection() {
        if (disputeReason && disputeReason.value === "not-received-merchandise") {
            notReceivedSection.style.display = "block";
        } else {
            notReceivedSection.style.display = "none";
        }
    }

    // Run when the page loads
    toggleNotReceivedSection();

    // Run when user changes selection
    if (disputeReason) {
        disputeReason.addEventListener("change", toggleNotReceivedSection);
    }

    // --------------- Existing Code Below ---------------- //

    // 1. Handling "Other" Location Selection
    const otherLocationRadio = document.getElementById("other-location-radio");
    const otherLocationText = document.getElementById("other-location");

    otherLocationRadio.addEventListener("change", function() {
        otherLocationText.style.display = this.checked ? "block" : "none";
    });

    otherLocationText.style.display = otherLocationRadio.checked ? "block" : "none";

    // 2. Resolution Attempt Handling
    const merchResolveYesRadio = document.getElementById("merch-resolve-yes");
    const merchResolveNoRadio = document.getElementById("merch-resolve-no");
    const merchResolveDetailsDiv = document.getElementById("merch-resolve-details");
    const merchNoResolutionReasonDiv = document.getElementById("merch-no-resolution-reason");

    function handleMerchResolutionAttemptChange() {
        if (merchResolveYesRadio.checked) {
            merchResolveDetailsDiv.style.display = "block";
            merchNoResolutionReasonDiv.style.display = "none";
        } else if (merchResolveNoRadio.checked) {
            merchResolveDetailsDiv.style.display = "none";
            merchNoResolutionReasonDiv.style.display = "block";
        } else {
            merchResolveDetailsDiv.style.display = "none";
            merchNoResolutionReasonDiv.style.display = "none"; // Handle case where neither is selected
        }
    }

    merchResolveYesRadio.addEventListener("change", handleMerchResolutionAttemptChange);
    merchResolveNoRadio.addEventListener("change", handleMerchResolutionAttemptChange);

    // Initial setup: hide the resolution details and no-resolution reason divs
    handleMerchResolutionAttemptChange();

//Not Received Service Section (Initially Hidden)
    const disputeReasonService = document.getElementById("dispute-reason");
    const notReceivedServiceSection = document.getElementById("not-received-service-section");
    const serviceResolveYes = document.getElementById("service-resolve-yes");
    const serviceResolveNo = document.getElementById("service-resolve-no");
    const serviceResolveDetails = document.getElementById("service-resolve-details");
    const serviceNoResolutionReason = document.getElementById("service-no-resolution-reason");

    // Show section when "Not Received Service" is selected
    disputeReasonService.addEventListener("change", function () {
        if (this.value === "not-received-service") {
            notReceivedServiceSection.style.display = "block";
        } else {
            notReceivedServiceSection.style.display = "none";
        }
    });

    // Show resolution details when "Yes" is selected
    serviceResolveYes.addEventListener("change", function () {
        serviceResolveDetails.style.display = "block";
        serviceNoResolutionReason.style.display = "none"; // Hide "Why?" box if "Yes" is selected
    });

    // Show "Why?" input box when "No" is selected
    serviceResolveNo.addEventListener("change", function () {
        serviceNoResolutionReason.style.display = "block";
        serviceResolveDetails.style.display = "none"; // Hide resolution details if "No" is selected
    });

//Recurring Charge Cancelled Section (Initially Hidden)
    const disputeReasonCancelled = document.getElementById("dispute-reason");
    const recurringChargeCancelledSection = document.getElementById("recurring-charge-cancelled-section");

    // Show section when "Cancelled a Recurring Charge" is selected
    disputeReasonCancelled.addEventListener("change", function () {
        if (this.value === "recurring-charge-cancelled") {
            recurringChargeCancelledSection.style.display = "block";
        } else {
            recurringChargeCancelledSection.style.display = "none";
        }
    });

// Not Satisfied with Merchandise or Service Section (Initially Hidden)
    const disputeReasonSatisfied = document.getElementById("dispute-reason");
    const notSatisfiedSection = document.getElementById("not-satisfied-section");
    const notSatisfiedMerchandise = document.getElementById("not-satisfied-merchandise");
    const notSatisfiedService = document.getElementById("not-satisfied-service");
    const merchandiseSection = document.getElementById("not-satisfied-merchandise-section");
    const serviceSection = document.getElementById("not-satisfied-service-section");

    // Show section when "Not Satisfied with Merchandise or Service Received" is selected
    disputeReasonSatisfied.addEventListener("change", function () {
        if (this.value === "not-satisfied") {
            notSatisfiedSection.style.display = "block";
        } else {
            notSatisfiedSection.style.display = "none";
        }
    });

    // Show Merchandise or Service follow-up based on selection
    notSatisfiedMerchandise.addEventListener("change", function () {
        merchandiseSection.style.display = "block";
        serviceSection.style.display = "none";
    });

    notSatisfiedService.addEventListener("change", function () {
        serviceSection.style.display = "block";
        merchandiseSection.style.display = "none";
    });

//Overcharged or Incorrect Charge Section (Initially Hidden)
    const disputeReasonOvercharged = document.getElementById("dispute-reason");
    const overchargedSection = document.getElementById("overcharged-section");
    const overchargedAmount = document.getElementById("overcharged-amount");

    // Show section when "Overcharged or the Charged Amount is Not Correct" is selected
    disputeReasonOvercharged.addEventListener("change", function () {
        if (this.value === "overcharged") {
            overchargedSection.style.display = "block";
        } else {
            overchargedSection.style.display = "none";
        }
    });

    // Format amount field as currency ($0.00)
    overchargedAmount.addEventListener("input", function (event) {
        let value = event.target.value.replace(/\D/g, ""); // Remove non-numeric characters
        if (value.length > 0) {
            value = (parseFloat(value) / 100).toFixed(2); // Convert to currency format
        }
        event.target.value = `$${value}`;
    });

//Charged Twice for the Same Transaction Section (Initially Hidden)
    const disputeReasonCharged = document.getElementById("dispute-reason");
    const chargedTwiceSection = document.getElementById("charged-twice-section");
    const chargedTwiceAmount = document.getElementById("charged-twice-amount");

    // Show section when "Charged Twice for the Same Transaction" is selected
    disputeReasonCharged.addEventListener("change", function () {
        if (this.value === "charged-twice") {
            chargedTwiceSection.style.display = "block";
        } else {
            chargedTwiceSection.style.display = "none";
        }
    });

    // Format amount field as currency ($0.00)
    chargedTwiceAmount.addEventListener("input", function (event) {
        let value = event.target.value.replace(/\D/g, ""); // Remove non-numeric characters
        if (value.length > 0) {
            value = (parseFloat(value) / 100).toFixed(2); // Convert to currency format
        }
        event.target.value = `$${value}`;
    });

//Expecting a Credit from the Merchant Section (Initially Hidden)
    const disputeReasonCredit = document.getElementById("dispute-reason");
    const expectingCreditSection = document.getElementById("expecting-credit-section");
    const creditType = document.getElementById("credit-type");
    const merchandiseCredit = document.getElementById("merchandise-credit");
    const timeshareCredit = document.getElementById("timeshare-credit");
    const serviceCredit = document.getElementById("service-credit");
    const cancelYes = document.getElementById("timeshare-cancel-yes");
    const cancelNo = document.getElementById("timeshare-cancel-no");
    const cancelDateSection = document.getElementById("cancellation-date-section");

    // Show section when "Expecting a Credit from the Merchant" is selected
    if (disputeReasonCredit) {
        disputeReasonCredit.addEventListener("change", function () {
            if (expectingCreditSection) {
                expectingCreditSection.style.display = this.value === "expecting-credit" ? "block" : "none";
            }
        });
    }

    // Show relevant subsection based on selection
    if (creditType) {
        creditType.addEventListener("change", function () {
            if (merchandiseCredit) {
                merchandiseCredit.style.display = this.value === "merchandise" ? "block" : "none";
            }
            if (timeshareCredit) {
                timeshareCredit.style.display = this.value === "timeshare" ? "block" : "none";
            }
            if (serviceCredit) {
                serviceCredit.style.display = this.value === "service" ? "block" : "none";
            }
        });
    }

    // Show/Hide Cancellation Date field based on selection
    function toggleCancellationDate() {
        if (cancelDateSection) {
            cancelDateSection.style.display = cancelYes && cancelYes.checked ? "block" : "none";
        }
    }

    if (cancelYes && cancelNo) {
        cancelYes.addEventListener("change", function () {
            if (cancelYes.checked) {
                cancelNo.checked = false;
                toggleCancellationDate();
            }
        });

        cancelNo.addEventListener("change", function () {
            if (cancelNo.checked) {
                cancelYes.checked = false;
                toggleCancellationDate();
            }
        });
    }


    // ******************************************************************************************
    // ******************  service-credit section Javascript ***********************
    // ******************************************************************************************
    const serviceCreditYes = document.getElementById("service-credit-yes");
    const serviceCreditNo = document.getElementById("service-credit-no");
    const receiptUploadSection = document.getElementById("receipt-upload-section");
    const serviceCancelYes = document.getElementById("service-cancel-yes");
    const serviceCancelNo = document.getElementById("service-cancel-no");
    const merchantResolveYes = document.getElementById("merchant-resolve-yes");
    const merchantResolveNo = document.getElementById("merchant-resolve-no");
    const merchantContactSection = document.getElementById("merchant-contact-section");


    // Helper function to handle checkbox logic (uncheck the other)
    function handleCheckboxPair(checkbox1, checkbox2, elementToShow = null) {
        if (checkbox1 && checkbox2) { // Check for existence
            checkbox1.addEventListener("change", function () {
                if (this.checked) {
                    checkbox2.checked = false;
                    if (elementToShow) {
                        elementToShow.style.display = 'block';
                    }
                } else if (elementToShow) {
                    elementToShow.style.display = 'none';
                }
            });

            checkbox2.addEventListener("change", function () {
                if (this.checked) {
                    checkbox1.checked = false;
                    if (elementToShow) {
                        elementToShow.style.display = 'none';
                    }
                } else if (elementToShow) {
                    elementToShow.style.display = 'none';
                }
            });
        }
    }
//Completed This Transaction with Another Form of Payment Section
        const disputeReasonSelect = document.getElementById("dispute-reason");
        const paymentSection = document.getElementById("completed-other-payment-section");
        const resolveAttemptSelect = document.getElementById("payment-resolve-attempt");
        const resolveYesDiv = document.getElementById("payment-resolve-yes");
        const resolveNoDiv = document.getElementById("payment-resolve-no");

        function toggleVisibility(element, show) {
            element.style.display = show ? "block" : "none";
        }

        disputeReasonSelect.addEventListener("change", function() {
            toggleVisibility(paymentSection, this.value === "another-payment");
        });

        resolveAttemptSelect.addEventListener("change", function() {
            if (this.value === "yes") {
                toggleVisibility(resolveYesDiv, true);
                toggleVisibility(resolveNoDiv, false);
            } else if (this.value === "no" || this.value === "not-applicable") {
                toggleVisibility(resolveYesDiv, false);
                toggleVisibility(resolveNoDiv, true);
            } else {
                toggleVisibility(resolveYesDiv, false);
                toggleVisibility(resolveNoDiv, false);
            }
        });
 });
 