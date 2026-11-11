<style>

.input-label {
    position: absolute;
    top: -17px;
    left: 11px;
    font-size: 0.6em;
    color: #666;
    opacity: 0;
    transition: all 0.3s ease;
    background: white;
    padding: 0 5px;
    z-index: 1;
}

.column-item .input-label {
    top: -17px;
}

.input-label-msg{
    position: absolute;
    top: -133px;
    left: 11px;
    font-size: 0.6em;
    color: #666;
    opacity: 0;
    transition: all 0.3s ease;
    background: white;
    padding: 0 5px;
    z-index: 1;
}

.wpcf7-form-control:focus + .input-label,
.wpcf7-form-control:not(:placeholder-shown) + .input-label {
    opacity: 1;
    transform: translateY(-5px);
}

.error {
    border-color: #ff0000 !important;
}

.two-column-group {
    display: flex;
    flex-wrap: wrap;
    margin-left: -1rem;
    margin-right: -1rem;
}

.column-item {
    width: 50%;
    padding: 0 1rem;
    box-sizing: border-box;
}

.modal-content-fix h3 {
    margin-top: 0 !important; 
    margin-bottom: 5px !important;
}

.modal-content-fix p {
    margin-top: 5px !important;
    margin-bottom: 15px !important;
}

.modal-content-fix {
    padding: 20px !important; 
}

.cf7-custom-submit {
    padding: 0 !important;
    margin: 0 !important;
}

.cf7-custom-submit p {
    padding: 0 !important;
    margin: 0 !important;
    line-height: 1px !important;
    font-size: 0 !important;
}

.cf7-custom-submit p button {
    font-size: 16px !important;
    line-height: normal !important;
}

#verification-btn {
    padding-top: 10px !important;
    padding-bottom: 10px !important;
    margin-top: 5px !important;
}

.wpcf7-response-output {
    display: none !important;
    visibility: hidden !important;
    opacity: 0 !important;
    height: 0 !important;
    padding: 0 !important;
    margin: 0 !important;
}
.wpcf7-not-valid-tip {
    color: #dc3232;
    font-size: 0.7em !important;
    font-weight: normal;
    display: block;
}
</style>

<div class="two-column-group">
    <div class="column-item mb-4">
        [text* your-name id:name class:form-input-field placeholder "Name *"]
    </div>

    <div class="column-item mb-4">
        [email* your-email id:email class:form-input-field placeholder "Email *"]
    </div>
</div>

<div class="mb-4">
    [tel* your-phone id:phone class:form-input-field placeholder "Phone *"]
</div>

<div class="mb-4">
    [text* your-street id:street class:form-input-field placeholder "Street Address *"]
</div>

<div class="mb-4">
    [text* your-city id:city class:form-input-field placeholder "City *"]
</div>

<div class="mb-4">
    [text* your-zip id:zip class:form-input-field placeholder "ZIP Code *"]
    <span id="zip-error-msg" style="display:block; margin-top:5px; font-size:0.7em; color:#ff0000;"></span>
</div>

<div class="mb-4">
    [textarea* your-message id:details class:form-input-field rows:5 maxlength:100 placeholder "Let us know the details of what you are looking for *"]
</div>

<div class="mb-6 w-full">
    <div class="flex justify-between items-center w-full">
        <button type="button" id="attach-files-btn" class="text-[var(--color-text-medium)] hover:text-[var(--color-dark-primary)] flex items-center text-sm font-medium focus:outline-none focus:ring-2 focus:ring-[var(--color-gray-300)] rounded-md py-1 px-1">
            <i class="fas fa-paperclip mr-2" aria-hidden="true"></i> Attach File
        </button>

        <span id="attachments-count" class="text-[var(--color-text-medium)] text-sm ml-auto">Attachments (0)</span>
    </div>

    [file your-attachments id:file-attachments class:hidden filetypes:image/*|pdf/*|docx limit:10mb]
</div>

<div class="mb-6">
    <label for="email-list-signup" class="flex items-center text-[var(--color-text-medium)] cursor-pointer mt-0" style="margin-left:-20px; margin-top:-14px; font-size:15px !important;">
        [acceptance acceptance-682 optional class:form-checkbox2 class:text-sm class:ml-2] Sign up for our email list for updates, promotions, and more. [/acceptance]
    </label>
</div>

<div class="cf7-custom-submit">
    <button type="button" id="verification-btn" class="btn-primary w-full py-3 px-6 text-lg uppercase tracking-wide">
        SEND & VERIFY
    </button>
</div>

[hidden submission-key]

<div id="verification-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999;">
    <div class="modal-content-fix" style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); background:white; padding:30px; border-radius:8px; max-width:400px; width:90%;">
        <h3>📧 Email Verification</h3>
        <p id="modal-message">A code has been sent to your email address.</p>
        <div id="modal-loader" style="text-align:center; padding:10px;"><i class="fas fa-spinner fa-spin"></i> Sending code...</div>
        
        <div id="verification-form-area" style="display:none;">
            <input type="text" id="verification-code" placeholder="Enter 6-digit code" class="form-input-field" style="margin-bottom:15px; width:100%;">
            <button type="button" id="verify-code-btn" class="btn-primary w-full py-2">Verify Code</button>
            <p id="verification-error" style="color:red; margin-top:10px; text-align:center; display:none;"></p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function removeCf7BrTags() {
        const submitDiv = document.querySelector('.cf7-custom-submit');
        if (submitDiv) {
            const brTags = submitDiv.querySelectorAll('br');
            brTags.forEach(br => {
                if (br && br.parentNode) { 
                    br.parentNode.removeChild(br);
                }
            });
            const pTag = submitDiv.querySelector('p');
            if (pTag) {
                pTag.innerHTML = pTag.innerHTML.trim();
            }
        }
    }
    
    removeCf7BrTags(); 

    let phoneTouched = false;
    let submissionWasSuccessful = false;

    const verificationBtn = document.getElementById('verification-btn');
    const verificationModal = document.getElementById('verification-modal');
    const verifyCodeBtn = document.getElementById('verify-code-btn');
    const verificationCodeInput = document.getElementById('verification-code');
    const modalLoader = document.getElementById('modal-loader');
    const verificationFormArea = document.getElementById('verification-form-area');
    const verificationError = document.getElementById('verification-error');
    const form = document.querySelector('.wpcf7-form'); 
    const submissionKeyField = document.querySelector('input[name="submission-key"]'); 
    const modalMessage = document.getElementById('modal-message');
    
    const phoneInput = document.getElementById('phone');
    const streetInput = document.getElementById('street');
    const cityInput = document.getElementById('city');
    const zipInput = document.getElementById('zip');
    const zipErrorMsg = document.getElementById('zip-error-msg');
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const detailsInput = document.getElementById('details');

    if (!verificationBtn || !form) return;
    
    const inputFields = [
        { id: 'name', label: 'Name *' }, { id: 'email', label: 'Email *' }, { id: 'phone', label: 'Phone *' },
        { id: 'street', label: 'Street Address *' }, { id: 'city', label: 'City *' }, { id: 'zip', label: 'ZIP Code *' },
        { id: 'details', label: 'Details *' }
    ];

    inputFields.forEach(field => {
        let inputElement = document.getElementById(field.id);
        if (inputElement) {
            let labelSpan = document.createElement('span');
            labelSpan.textContent = field.label;
            labelSpan.classList.add('input-label');
            if (field.id === 'details') { labelSpan.classList.add('input-label-msg'); }
            inputElement.parentNode.appendChild(labelSpan);
        }
    });
    
    const phoneFormatSpan = document.createElement('span');
    phoneFormatSpan.style.display = 'block';
    phoneFormatSpan.style.marginTop = '5px';
    phoneFormatSpan.style.fontSize = '0.7em';
    phoneFormatSpan.style.color = '#ff0000';
    if (phoneInput && phoneInput.parentNode) phoneInput.parentNode.appendChild(phoneFormatSpan);

    const floridaZipRegex = /^(32\d{3}|33\d{3}|34\d{3})$/; 

    function validatePhoneNumber(phone) { 
        if (phone === '') return false;
        const phoneRegex = /^\(\d{3}\) \d{3}-\d{4}$/;
        if (!phoneRegex.test(phone)) return false;
        const digits = phone.replace(/\D/g, '');
        const areaCode = digits.substring(0, 3);
        const exchangeCode = digits.substring(3, 6);
        const validAreaCode = /^[2-9]\d{2}$/.test(areaCode) && areaCode !== '555';
        const validExchangeCode = /^[2-9]\d{2}$/.test(exchangeCode) && exchangeCode !== '555' && !/^95[89]$/.test(exchangeCode);
        return validAreaCode && validExchangeCode;
    }
    
    function validateZipCode(zip) {
        if (!/^\d{5}$/.test(zip)) return { isValid: false, message: 'ZIP Code must be a 5-digit number.' };
        if (!floridaZipRegex.test(zip)) return { isValid: false, message: 'We only serve Florida (32xxx, 33xxx, 34xxx) ZIP codes.' };
        return { isValid: true, message: '' };
    }
    function validateStreet(street) { return street.trim() !== '' && /^[a-zA-Z0-9\s.,#-]*$/.test(street); }
    function validateCity(city) { return city.trim() !== '' && /^[a-zA-Z\s-]*$/.test(city); }

    function checkFormValidity() {
        const nameInput = document.getElementById('name');
        const emailInput = document.getElementById('email');
        const detailsInput = document.getElementById('details');

        const phoneValue = phoneInput ? phoneInput.value.trim() : '';
        let isPhoneValid = validatePhoneNumber(phoneValue);
        let phoneErrorMessage = '';
            
        if (phoneValue.length === 0) {
            isPhoneValid = false;
            if (!phoneTouched) { phoneErrorMessage = ''; }
        } else if (!isPhoneValid) {
            const digits = phoneValue.replace(/\D/g, '');
            if (digits.length !== 10) { phoneErrorMessage = 'Phone number must be 10 digits'; } 
            else {
                const areaCode = digits.substring(0, 3);
                const exchangeCode = digits.substring(3, 6);
                
                if (/^[01]/.test(areaCode)) { phoneErrorMessage = 'Area code cannot start with 0 or 1'; } 
                else if (/^[01]/.test(exchangeCode)) { phoneErrorMessage = 'Exchange code cannot start with 0 or 1'; } 
                else if (areaCode === '555') { phoneErrorMessage = '555 area code is not valid'; } 
                else if (exchangeCode === '555') { phoneErrorMessage = '555 exchange code is not valid'; } 
                else if (/^95[89]$/.test(exchangeCode)) { phoneErrorMessage = '958/959 exchange codes are reserved for testing'; } 
                else { phoneErrorMessage = 'Invalid phone number format'; }
            }
        } else { phoneErrorMessage = ''; }
            
        phoneFormatSpan.textContent = phoneErrorMessage;
        if (phoneInput) {
            const showErrorBorder = !isPhoneValid && (phoneValue.length > 0);
            phoneInput.classList.toggle('error', showErrorBorder);
        }

        const isNameValid = nameInput ? nameInput.value.trim() !== '' : false;
        const isEmailValid = emailInput ? (emailInput.value.trim() !== '' && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value.trim())) : false;
        const isDetailsValid = detailsInput ? detailsInput.value.trim() !== '' : false;
        const streetValue = streetInput ? streetInput.value.trim() : '';
        const cityValue = cityInput ? cityInput.value.trim() : '';
        const zipValue = zipInput ? zipInput.value.trim() : '';
        const isStreetValid = validateStreet(streetValue);
        const isCityValid = validateCity(cityValue);
        const isZipValidForSubmission = zipValue.length > 0 ? validateZipCode(zipValue).isValid : false;
        
        return isPhoneValid && isNameValid && isEmailValid && isDetailsValid && isStreetValid && isCityValid && isZipValidForSubmission;
    }

    if (phoneInput) {
        phoneInput.addEventListener('blur', () => { phoneTouched = true; if (verificationBtn) verificationBtn.disabled = !checkFormValidity(); });
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '').substring(0, 10);
            let formattedValue = '';
            if (value.length > 0) formattedValue = '(' + value.substring(0, 3);
            if (value.length > 3) formattedValue += ') ' + value.substring(3, 6);
            if (value.length > 6) formattedValue += '-' + value.substring(6, 10);
            e.target.value = formattedValue;
            if (verificationBtn) verificationBtn.disabled = !checkFormValidity();
        });
    }

    if (zipInput) {
        zipInput.addEventListener('input', () => { 
            const zipValidationResult = validateZipCode(zipInput.value.trim());
            zipInput.classList.toggle('error', !zipValidationResult.isValid && zipInput.value.trim() !== '');
            zipErrorMsg.textContent = zipValidationResult.isValid || zipInput.value.trim() === '' ? '' : zipValidationResult.message;
            if (verificationBtn) verificationBtn.disabled = !checkFormValidity();
        });
    }

    const inputsToMonitor = [nameInput, emailInput, detailsInput, streetInput, cityInput];
    inputsToMonitor.forEach(input => {
        if (input) {
            input.addEventListener('input', () => { if (verificationBtn) verificationBtn.disabled = !checkFormValidity(); });
        }
    });

    if (verificationBtn) verificationBtn.disabled = !checkFormValidity();
    
    function showLoader(message) {
        modalLoader.textContent = message;
        modalLoader.style.display = 'block';
        verificationFormArea.style.display = 'none';
        verificationError.style.display = 'none';
        
        const finalCloseBtn = document.getElementById('final-close-btn');
        if (finalCloseBtn) finalCloseBtn.style.display = 'none'; 
    }

    function hideLoader() {
        modalLoader.style.display = 'none';
        verificationFormArea.style.display = 'block';
    }

    function displayError(message) {
        verificationError.textContent = message;
        verificationError.style.display = 'block';
    }
    
    function showFinalStatus(isSuccess, message) {
        const modalTitle = verificationModal.querySelector('h3');
        const modalContentArea = verificationModal.querySelector('div:nth-child(1)');
        
        modalLoader.style.display = 'none';
        verificationFormArea.style.display = 'none';
        verificationError.style.display = 'none';

        modalTitle.textContent = isSuccess ? '✅ Submission Successful' : '❌ Submission Failed';
        modalMessage.innerHTML = message;
        
        let closeBtn = document.getElementById('final-close-btn');
        if (!closeBtn) {
            closeBtn = document.createElement('button');
            closeBtn.id = 'final-close-btn';
            closeBtn.className = 'btn-primary w-full py-2';
            closeBtn.textContent = 'Close';
            closeBtn.style.marginTop = '20px';
            
            closeBtn.addEventListener('click', () => {
                verificationModal.style.display = 'none';
                form.reset(); 
                verificationModal.querySelector('h3').textContent = '📧 Email Verification';
                modalMessage.textContent = 'A code has been sent to your email address.';
                
                const responseOutput = form.querySelector('.wpcf7-response-output');
                if (responseOutput) {
                    responseOutput.textContent = ''; 
                }
            });
            modalContentArea.appendChild(closeBtn);
        }
        closeBtn.style.display = 'block';
        
        verificationModal.style.display = 'block';
    }

    verificationBtn.addEventListener('click', async function(e) {
        e.preventDefault();
        
        if (!checkFormValidity()) {
             alert("Please fill out all required fields correctly before sending the verification code.");
             return;
        }
        
        submissionWasSuccessful = false; 

        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries()); 
        const emailInput = document.getElementById('email');

        verificationModal.style.display = 'block';
        showLoader("Sending verification code...");

        try {
            const response = await fetch('/wp-json/cf7/v1/send-code', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                submissionKeyField.value = result.submission_key;
                modalMessage.textContent = 'A code has been sent to ' + emailInput.value + '. Enter it below.';
                verificationCodeInput.value = '';
                hideLoader();
                verificationModal.querySelector('h3').textContent = '📧 Email Verification';
            } else {
                displayError(result.message || "Failed to send code.");
                hideLoader();
                setTimeout(() => verificationModal.style.display = 'none', 3000);
            }
        } catch (error) {
            showFinalStatus(false, "Network error. Could not connect to the server.");
        }
    });

    verifyCodeBtn.addEventListener('click', async function() {
        const code = verificationCodeInput.value.trim();
        const key = submissionKeyField.value;

        if (code.length !== 6 || !key) {
            displayError("Please enter a valid 6-digit code.");
            return;
        }

        showLoader("Verifying code...");
        verificationError.style.display = 'none';

        try {
            const response = await fetch('/wp-json/cf7/v1/verify-code', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    submission_key: key,
                    verification_code: code
                })
            });

            const result = await response.json();

            if (result.success) {
                showLoader("Verification successful. Submitting final form...");
                form.dispatchEvent(new Event('submit', { cancelable: true }));

            } else {
                hideLoader();
                displayError(result.message || "Invalid verification code or code expired.");
                verificationFormArea.style.display = 'block';
            }
        } catch (error) {
            showFinalStatus(false, "Network error during verification. Please try again.");
        }
    });
    
    form.addEventListener('wpcf7mailsent', function(event) {
        submissionWasSuccessful = true;
        const successMessage = "Thank you for your message. We will contact you shortly.";
        showFinalStatus(true, successMessage);
    }, false);

    form.addEventListener('wpcf7mailfailed', function(event) {
        if (submissionWasSuccessful) return;
        const failedMessage = "The submission failed due to a server error. Please try again or contact us directly.";
        showFinalStatus(false, failedMessage);
    }, false);
    
    form.addEventListener('wpcf7invalid', function(event) {
        if (submissionWasSuccessful) return;
        const invalidMessage = "The submission was stopped, possibly due to spam detection. Please ensure all fields are correct and try again.";
        showFinalStatus(false, invalidMessage);
    }, false);

    verificationModal.addEventListener('click', function(e) {
        const finalCloseBtn = document.getElementById('final-close-btn');
        if (e.target === verificationModal && (!finalCloseBtn || finalCloseBtn.style.display === 'none')) {
            verificationModal.style.display = 'none';
        }
    });
});
</script>