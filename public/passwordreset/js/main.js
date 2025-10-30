const baseURL = 'https://v2.theshillaaccess.sg/api/'
let token = ""
var currentTab = 0; // Current tab is set to be the first tab (0)
var password = {
    password: false,
    confirm: false
}
showTab(currentTab); // Display the current tab

function showTab(n) {
    // This function will display the specified tab of the form...
    var x = document.getElementsByClassName("tab");
    x[n].style.display = "block";
    //... and fix the Previous/Next buttons:
    if (n == 0) {
        document.getElementById("prevBtn").style.display = "none";
    } else {
        document.getElementById("prevBtn").style.display = "inline";
    }
    if (n == (x.length - 1)) {
        document.getElementById("nextBtn").innerHTML = "Next";
    } else {
        document.getElementById("nextBtn").innerHTML = "Next";
    }
    //... and run a function that will display the correct step indicator:
    fixStepIndicator(n)
}

function nextPrev(n) {
    // This function will figure out which tab to display
    var x = document.getElementsByClassName("tab");
    // Exit the function if any field in the current tab is invalid:
    // if (n == 1 && !validateForm()) return false;
    // // Hide the current tab:
    // x[currentTab].style.display = "none";
    // // Increase or decrease the current tab by 1:
    // currentTab = currentTab + n;
    // // if you have reached the end of the form...
    // if (currentTab >= x.length) {
    //     // ... the form gets submitted:
    //     document.getElementById("regForm").submit();
    //     return false;
    // }
    // // Otherwise, display the correct tab:
    // showTab(currentTab);

    
    if (n == -1) {
        token = ''
        showTab(0);
        // This function will figure out which tab to display
        var x = document.getElementsByClassName("tab");
        // Exit the function if any field in the current tab is invalid:
        // Hide the current tab:
        x[currentTab].style.display = "none";
        // Increase or decrease the current tab by 1:
        currentTab = currentTab + n;
        // if you have reached the end of the form...
        if (currentTab >= x.length) {
            // ... the form gets submitted:
            document.getElementById("regForm").submit();
            return false;
        }
        // Otherwise, display the correct tab:
        showTab(currentTab);
        return;
    }
    

    if (!token) {

        var email = $('#email-input').val()
        if (!isEmail(email)) {
            Snackbar.show({ text: 'Please enter valid email address' });

            return;
        }
        $nextBtn = $('#nextBtn')
        $nextBtn.attr("disabled", true).text("Please wait...");
        $.ajax({
            url: baseURL + 'forget-password',
            method: 'POST',
            data: {
                email

                // add more key-value pairs as needed
            },
            success: function (response) {
                console.log(response);
                $nextBtn.attr("disabled", false).text('Next');

                if (response.status) {

                    Snackbar.show({ text: response.msg });
                    token = response.data.token || 'NOTOKNE';
                    $nextBtn.attr("disabled", false).text('Next');
                    var x = document.getElementsByClassName("tab");
                    // Exit the function if any field in the current tab is invalid:
                    if (n == 1 && !validateForm()) return false;
                    // Hide the current tab:
                    x[currentTab].style.display = "none";
                    // Increase or decrease the current tab by 1:
                    currentTab = currentTab + n;
                    // if you have reached the end of the form...
                    if (currentTab >= x.length) {
                        // ... the form gets submitted:
                        document.getElementById("regForm").submit();
                        return false;
                    }
                    // Otherwise, display the correct tab:
                    showTab(currentTab);
                } else {
                    Snackbar.show({ text: response.msg });

                }

                // do something with the response data
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(errorThrown);
                Snackbar.show({ text: 'Internal server error.' });

                $nextBtn.attr("disabled", false).text('Next');

                // handle the error case
            }
        });
    } else {
        // password verify
        $nextBtn = $('#nextBtn')
        $nextBtn.attr("disabled", true).text("Please wait...");
        var password_confirmation = $('#confirm').val();
        var password = $('#password').val();
        var otp = $('#otp_1').val() + $('#otp_2').val() + $('#otp_3').val() + $('#otp_4').val() + $('#otp_5').val() + $('#otp_6').val();
        $.ajax({
            url: baseURL + 'forget-password-verify',
            method: 'POST',
            data: {
                otp,
                password_confirmation,
                password

                // add more key-value pairs as needed
            },
            headers: {
                "Authorization": `Bearer ${token}`, 'Accept': 'application/json'
            },
            success: function (response) {
                console.log(response);
                $nextBtn.attr("disabled", false).text('Next');

                if (response.status) {

                    Snackbar.show({ text: response.msg });
                    token = ''

                    window.location.href = "https://v2.theshillaaccess.sg/passwordreset/download-app.html";
                    //    redirect to next page
                } else {
                    const data = response.data;
                    Snackbar.show({ text: response.msg });
                    if (data?.password) {
                        $('#password-span').text(data?.password[0]);
                    }
                    if (data?.password_confirmation) {
                        $('#confirm-span').text(data?.password_confirmation[0]);
                    }

                }

                // do something with the response data
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(errorThrown);
                Snackbar.show({ text: 'The APH / email or password entered is invalid. Please try again.' });

                $nextBtn.attr("disabled", false).text('Next');

                // handle the error case
            }
        });
    }

}

function validateForm() {
    // This function deals with validation of the form fields
    var x, y, i, valid = true;
    x = document.getElementsByClassName("tab");
    y = x[currentTab].getElementsByTagName("input");
    // A loop that checks every input field in the current tab:
    for (i = 0; i < y.length; i++) {
        // If a field is empty...
        if (y[i].value == "") {
            // add an "invalid" class to the field:
            y[i].className += " invalid";
            // and set the current valid status to false
            valid = false;
        }
    }
    // If the valid status is true, mark the step as finished and valid:
    if (valid) {
        document.getElementsByClassName("step")[currentTab].className += " finish";
    }
    return valid; // return the valid status
}

function fixStepIndicator(n) {
    // This function removes the "active" class of all steps...
    var i, x = document.getElementsByClassName("step");
    for (i = 0; i < x.length; i++) {
        x[i].className = x[i].className.replace(" active", "");
    }
    //... and adds the "active" class on the current step:
    x[n].className += " active";
}

function isEmail(emailAdress) {
    let regex = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;

    if (emailAdress.match(regex))
        return true;

    else
        return false;
}

function toggleHideShow(type) {
    password[type] = !password[type];
    if (password[type]) {
        // show password
        $('#' + type).attr('type', 'text')
    } else {
        // hide password
        $('#' + type).attr('type', 'password')
    }
    $('#' + type + '-icon').toggleClass('bi-eye-slash').toggleClass('bi bi-eye');
}
// =================================================================
//Initial references
const input = document.querySelectorAll("#otp > *[id]");
const inputField = document.querySelector("#otp");
// const submitButton = document.getElementById("submit");
let inputCount = 0,
    finalInput = "";

//Update input
const updateInputConfig = (element, disabledStatus) => {
    // element.disabled = disabledStatus;
    if (!disabledStatus) {
        element.focus();
    } else {
        element.blur();
    }
};

input.forEach((element) => {

    element.addEventListener("keyup", (e) => {
        
        inputCount = parseInt(e.target.dataset.index);

        e.target.value = e.target.value.replace(/[^0-9]/g, "");
        let { value } = e.target;

        if (value.length == 1) {
            updateInputConfig(e.target, true);
            if (inputCount <= 5 && e.key != "Backspace") {
                finalInput += value;
                if (inputCount <= 5) {
                    updateInputConfig(e.target.nextElementSibling, false);
                }
            }
            if (inputCount === 6) {
                $('#password').focus()
            }
            // inputCount += 1;
        } else if (value.length == 0 && e.key == "Backspace") {
            finalInput = finalInput.substring(0, finalInput.length - 1);
            
            if (inputCount == 1) {
                inputCount = 0
                updateInputConfig(e.target, false);
                return false;
            }
            
            
            updateInputConfig(e.target, true);
            // e.target.previousElementSibling.value = "";
            updateInputConfig(e.target.previousElementSibling, false);
            // inputCount -= 1;
        } else if (value.length > 1) {
            e.target.value = value.split("")[0];
            updateInputConfig(e.target, true);
            if (inputCount <= 5 && e.key != "Backspace") {
                finalInput += value;
                if (inputCount <= 5) {
                    updateInputConfig(e.target.nextElementSibling, false);
                }
            }
            if (inputCount === 6) {
                $('#password').focus()
            }
        } else {
            inputCount = parseInt(e.target.dataset.index);
            
        }
        

        // submitButton.classList.add("hide");
    });
});

window.addEventListener("keyup", (e) => {
    if (inputCount > 6) {
        // submitButton.classList.remove("hide");
        // submitButton.classList.add("show");
        if (e.key == "Backspace") {
            finalInput = finalInput.substring(0, finalInput.length - 1);
            updateInputConfig(inputField.lastElementChild, false);
            inputField.lastElementChild.value = "";
            inputCount -= 1;
            // submitButton.classList.add("hide");
        }
    }
});

// =================================================================
function OTPInput() {

    inputCount = 0;
    finalInput = "";
    input.forEach((element) => {
        element.value = "";
    });
    updateInputConfig(inputField.firstElementChild, false);

}
OTPInput();