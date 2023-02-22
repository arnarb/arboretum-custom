jQuery(document).ready(function() {
    if(document.querySelector('.js-new-user-registration')) {

        console.log('new user reg');

        const country = document.querySelector('#country');        
        country.addEventListener('change', () => toggleUSFields(country));

        document.querySelectorAll('[data-required-field]').forEach(requiredElement => {
  
            console.log(`Required Element :`);
            console.log(requiredElement);
            // const parentElement = element.parentElement;
            // if(element.dataset.questionType == 'radio' && element.dataset.questionType == 'source' && element.dataset.questionType == 'checkbox') {
            //   validationElement = document.querySelector(`.${target.dataset.requiredField}`);
            // } else {
            //   validationElement = document.querySelector(`.${parentElement.dataset.requiredField}`);
            // }
    
            
            const elements = requiredElement.querySelectorAll('input, option');
            // console.log(`Elements: %o`, elements);
    
            if(requiredElement.dataset.questionType != 'select' && elements != null && elements.length > 0) {
                elements.forEach(element => {
                    // element.addEventListener('click', resetUserRegistrationValidationCheck);
                    element.addEventListener('focus', resetUserRegistrationValidationCheck);
                });
            } else {
                // requiredElement.addEventListener('click', resetUserRegistrationValidationCheck);
                requiredElement.addEventListener('focus', resetUserRegistrationValidationCheck);
            }
        });
  
  
        // ALL OF THIS CAN GO TO THE OTHER JS FILE
        // Add the venue select
        // const venueSelect = document.querySelector('.arb-form__venue-select');
        // if (venueSelect) {
        //   if (venueSelect.value != '') {
        //     toggleVenue(venueSelect.value);
        //   }
        //   venueSelect.addEventListener('change', toggleVenue);
        // }
        
        // // Set the venue limit
        // toggleLimit();
  
        // // Validate the form and submit it
        document.querySelector('.arb-form__register').addEventListener('click', submitUserRegistrationForm);
  
        // const waitlistCheckbox = document.querySelector('.arb-form__waitlist-confirm input');
        // if (waitlistCheckbox) {
        //   waitlistCheckbox.addEventListener('change', toggleWaitlistCheckbox);
        // }
        // const requestedSelect = document.querySelector('.arb-form__requested');
        // if (requestedSelect) {
        //   requestedSelect.addEventListener('change', changeRequestedNumber);
        // }
    }
    //
});
  
function toggleUSFields(country) {
    // console.log(`Country`);
    // console.log(country);
    const city = document.querySelector('.arb-form__row__city');
    const state = document.querySelector('.arb-form__row__state');
    const zip = document.querySelector('.arb-form__row__zip');

    if (country.value === 'United States') {
        city.classList.remove('arb-form__hidden');
        state.classList.remove('arb-form__hidden');
        zip.classList.remove('arb-form__hidden');
    } else {
        city.classList.add('arb-form__hidden');
        state.classList.add('arb-form__hidden');
        zip.classList.add('arb-form__hidden');

        resetUserRegistrationValidationCheck(city.querySelector('.arb-form__input'));
        resetUserRegistrationValidationCheck(state.querySelector('.arb-form__input'));
        resetUserRegistrationValidationCheck(zip.querySelector('.arb-form__input'));
    }
}

// Clear validation text on focus
function resetUserRegistrationValidationCheck(event) {
  
    console.log(`Event :`);
    console.log(event);
    // if (event.target.classList.contains('event-calendar__set-date') || event.target.classList.contains('pager ')) {
    //   // console.log('Dont react to these elements');
    //     return;
    // }
    // console.log(`Event: %o, target %o`, event, event.target);
    let target = event.target ? event.target : event;
    let validationElement = "";

    const parentElement = target.parentElement;
    if(parentElement.dataset.requiredField) {
        validationElement = document.querySelector(`.${parentElement.dataset.requiredField}`);
    } else if(target.dataset.questionType != 'radio' && target.dataset.questionType != 'source' && target.dataset.questionType != 'checkbox') {
        validationElement = document.querySelector(`.${target.dataset.requiredField}`);
    }

    if(validationElement) {
        validationElement.innerHTML = '';
    }
}
  
// Check user validation
function userRegistrationValidationCheck(element, topElement) {
    const validationElement = document.querySelector(`.${element.dataset.requiredField}`);
    validationElement.innerHTML = element.dataset.requiredText;
    if(topElement === null) {
        topElement = validationElement;
    }

    return topElement;
}
  
// Submit the form if validation passes
function submitUserRegistrationForm() {
    // el.preventDefault();
    console.log('submit form');
    // let requestedNum;
    let topElement = null;
  
    const requiredElements = document.querySelectorAll('[data-required-field]');
  
    // Reset the validation elements
    requiredElements.forEach(requiredElement => {
        const country = document.querySelector('#country');        
        const name = requiredElement.name;
        let skip = false;

        if (country.value != 'United States' && (name === 'state' || name === 'city' || name === 'zip')) {
            console.log(`Skip `);
            console.log(requiredElement);
            skip = true;
        }


        const validationElement = document.querySelector(`.${requiredElement.dataset.requiredField}`);
        validationElement.innerHTML = '';
    // });
  
    // // Check form validation
    // requiredElements.forEach(requiredElement => {
        const elements = requiredElement.querySelectorAll('input, option');
    
        console.log(elements);
        if (!skip) {
            if (requiredElement.dataset.questionType != 'select' && elements != null && elements.length > 0) {
                let input = false;
                elements.forEach(element => {
                    // console.log(element);
                    if (element.checked) {
                        input = true;
                    }
                });

                    // For checkboxes check if there is at least one entry
                if (!input) {
                // console.log('no input');
                    topElement = userRegistrationValidationCheck(requiredElement, topElement);
                }
            } else if (requiredElement.dataset.date) {
                // console.log("************************ FOUND THE DATE!");
            } else if (requiredElement.value == '' || requiredElement.value == 0 || requiredElement.value == null){
                // console.log('empty value');
                topElement = userRegistrationValidationCheck(requiredElement, topElement);
            } else if (requiredElement.id == 'e-mail') {
                var mailformat = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                if(!requiredElement.value.toLowerCase().match(mailformat)) {
                    // console.log('invalid email');
                    topElement = userRegistrationValidationCheck(requiredElement, topElement);
                }
            }
        }
    });
    
    if (topElement != null) {
        // If didn't pass validation, set the first faulty element as the scroll focus and return
        topElement.scrollIntoView();
    
        return;
    } else {
        // Passed validation, send the data
        let data = {};
           
        data.email = document.querySelector('#e-mail').value;
        data.firstName = document.querySelector('#first-name').value;
        data.lastName = document.querySelector('#last-name').value;
        data.password = document.querySelector('#password').value;
        data.action = 'arboretum_new_user_registration';

        

        alert(JSON.stringify(data));
    
        // Show success message
        document.querySelector('.arb-form').remove();
    
        // Move to top of screen
        window.scrollTo(0, 0);
    
        jQuery.ajax({
            type: 'post',
            url: arbAjax.ajaxurl,
            data: data,
            dataType: 'json',
            success: function(response) {
                const result = document.createElement('div');
                result.innerHTML = `Thank you for registering a new user. You will recieve a confirmation email at ${$email}.`;
                document.querySelector('.basic-page__body-width').appendChild(result);
            },
            error: function(response) {
                console.log('error');
                console.log(response);
                console.log(JSON.parse(response));

                // Error handling here
            }
        })
        ;
    }
}