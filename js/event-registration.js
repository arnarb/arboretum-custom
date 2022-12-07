jQuery(document).ready(function() {
  if(document.querySelector('.arb-form__register')) {
    document.querySelectorAll('[data-required-field]').forEach(requiredElement => {

      // const parentElement = element.parentElement;
      // if(element.dataset.questionType == 'radio' && element.dataset.questionType == 'source' && element.dataset.questionType == 'checkbox') {
      //   validationElement = document.querySelector(`.${target.dataset.requiredField}`);
      // } else {
      //   validationElement = document.querySelector(`.${parentElement.dataset.requiredField}`);
      // }

      
      const elements = requiredElement.querySelectorAll('input, option');
      console.log(`Elements: %o`, elements);

      if(requiredElement.dataset.questionType != 'select' && elements != null && elements.length > 0) {
        elements.forEach(element => {
          element.addEventListener('click', resetValidationCheck);
          element.addEventListener('focus', resetValidationCheck);
        });
      } else {
        requiredElement.addEventListener('click', resetValidationCheck);
        requiredElement.addEventListener('focus', resetValidationCheck);
      }
    });

    // Add the venue select
    const venueSelect = document.querySelector('.arb-form__venue-select');
    if (venueSelect) {
      if (venueSelect.value != '') {
        toggleVenue(venueSelect.value);
      }
      venueSelect.addEventListener('change', toggleVenue);
    }
    
    // Set the venue limit
    toggleLimit();

    // Validate the form and submit it
    document.querySelector('.arb-form__register').addEventListener('click', submitForm);
  }
});

// Clear validation text on focus
function resetValidationCheck(event) {

  if (event.target.classList.contains('event-calendar__set-date') || event.target.classList.contains('pager ')) {
    console.log('Dont react to these elements');
    return;
  }
  console.log(`Event: %o, target %o`, event, event.target);
  let target = event.target;
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

// Check validation
function validationCheck(element, topElement) {
  const validationElement = document.querySelector(`.${element.dataset.requiredField}`);
  validationElement.innerHTML = element.dataset.requiredText;
  if(topElement === null) {
    topElement = validationElement;
  }
  
  return topElement;
}

// Show the venue description
function toggleVenue(event) {
  document.querySelectorAll('.arb-form__venue').forEach(venue => {
    venue.classList.add('arb-form__hidden');
  })

  // Get this to work for original pass or if they change it
  const value = event.target ? event.target.value : event;

  const venueDiv = document.querySelector(`[data-venue="${value}"]`); // (`[data-venue="${event.target.value}"]`);
  console.log('venue changed');
  console.log(venueDiv);

  venueDiv.classList.remove('arb-form__hidden');

  toggleLimit();
}

// Switch up the values for the limit of each venue
function toggleLimit() {
  const limit = document.querySelectorAll('.arb-form__venue:not(.hidden)').dataset.limit;
  const requested = document.querySelector('#requested');

   for(let i = requested.options.length - 1; i >= 0; i--) {
      selectElement.remove(i);
   }

  const option = document.createElement('option');
  option.value = '';
  option.selected;
  option.disabled;

  requested.appendChild(option);
  for (let n = 0; n < limit; n++) {
    option = document.createElement('option');
    option.value = n;
    option.innerHTML = n;

    requested.appendChild(option);
  }
}

function submitForm() {
  // el.preventDefault();
  console.log('submit form');
  // let requestedNum;
  let topElement = null;

  const requiredElements = document.querySelectorAll('[data-required-field]');

  // Reset the validation elements
  requiredElements.forEach(requiredElement => {
    const validationElement = document.querySelector(`.${requiredElement.dataset.requiredField}`);
    validationElement.innerHTML = '';
  // });

  // // Check form validation
  // requiredElements.forEach(requiredElement => {
    const elements = requiredElement.querySelectorAll('input, option');

    if(requiredElement.dataset.questionType != 'select' && elements != null && elements.length > 0) {
      let input = false;
      elements.forEach(element => {
        // console.log(element);
        if (element.checked) {
          input = true;
        }
      });

      // For checkboxes check if there is at least one entry
      if(!input) {
        // console.log('no input');
        topElement = validationCheck(requiredElement, topElement);
      }
    } else if (requiredElement.dataset.date) {
      console.log("************************ FOUND THE DATE!");
    } else if(requiredElement.value == '' || requiredElement.value == 0 || requiredElement.value == null){
      // console.log('empty value');
      topElement = validationCheck(requiredElement, topElement);
    } else if(requiredElement.id == 'e-mail') {
      var mailformat = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
      if(!requiredElement.value.toLowerCase().match(mailformat)) {
        // console.log('invalid email');
        topElement = validationCheck(requiredElement, topElement);
      }
    }
  })
  
  if(topElement != null) {
    // If didn't pass validation, set the first faulty element as the scroll focus and return
    topElement.scrollIntoView();

    return;
  } else {

    // Passed validation, send the data
    let n = 0;
    let data = {};
    const form = document.querySelector('#event-registration-form');
    const returned = document.querySelector('.arb-form__register');
    const customQuestions = document.querySelectorAll('.custom-question');
    form.elements.forEach(element => {
      if (element.dataset.formRequired === 'true') {
        data[element.name] = element.value;
      }
    });
    data.requested = document.querySelector('#requested').value;
    data.questions = returned.dataset.customQuestions;

    // Store custom question/answer pairs
    customQuestions.forEach(customQuestion => {
      let answer;
      let elements = [];
      let question = customQuestion.dataset.question;
      let questionType = customQuestion.dataset.questionType;

      answer = [];
      elements = customQuestion.querySelectorAll('input');
      elements.forEach(element => {
        if (element.checked) {
          answer.push(element.value);
        }
      })

        alert(`${questionType}: ${answer.join()}`);
      answer = answer.join(', ');
      
      data[`question_${n}`] = question;
      data[`answer_${n}`] = answer;
      n++;
    });

    const venue = document.querySelector('#venue');

    data.email = document.querySelector('#e-mail').value;
    data.firstName = document.querySelector('#first-name').value;
    data.lastName = document.querySelector('#last-name').value;
    data.location = venue.querySelector('.arb-form__venue:not(.arb-form__hidden)').dataset.venue;
    data.type = venue.querySelector('.arb-form__venue:not(.arb-form__hidden) .arb-form__venue__type').dataset.type;
    data.key = venue.querySelector('.arb-form__venue:not(.arb-form__hidden) .arb-form__venue__type').dataset.key;
    data.date = venue.querySelector('.arb-form__venue:not(.arb-form__hidden) .arb-form__venue__date-time').dataset.date;
    data.action = 'arboretum_event_registration';

    // Consent form data
    data.consentName = returned.dataset.consentName;
    data.consentDate = returned.dataset.consentDate;
    data.participantNum = returned.dataset.participantNum;

    for (n = 1; n <= returned.dataset.participantNum; n++) {
      data[`participantName${n}`] = returned.getAttribute(`data-participant-name__${n}`);
      data[`participantDate${n}`] = returned.getAttribute(`data-participant-date__${n}`);
    }

    data.guardianName = returned.dataset.guardianName;
    data.guardianDate = returned.dataset.guardianDate;

    // data.location = document.querySelector('.arb-form__venue__location.active').dataaset.location;

    ////////////////////////////////
    // data.venue
    // data.date = document.querySelector('').value;
    ////////////////////////////////


    // data.availability = returned.dataset.availability;
    data.event = returned.dataset.event;
    if (returned.dataset.user) {
      data.user = returned.dataset.user;
    } else {
      data.user = 68; /// hardcoded of the Guest user
    }
    data.nonce = returned.dataset.nonce;

    console.log(data);
    alert(JSON.stringify(data));
    
    document.querySelector('#event-registration-form').remove();
    document.querySelector('#result').classList.remove('arb-form__hidden');

    //      
    //  dataType: 'json',
    jQuery.ajax({
      type: 'post',
      url: arbAjax.ajaxurl,
      data: data,
      success: function(response) {
        if (response.type == 'success') {
          alert("Success - Woohoo");
          console.log(JSON.stringify(response))
        } else {
          alert(JSON.stringify(response));
          console.log(JSON.stringify(response))
        }
      },
      error: function(response) {
        alert(JSON.stringify(response));
        console.log(JSON.stringify(response));
      }
    })
    .done(function(data) {
      alert("DONE");
      alert(data);
    });
  }
}