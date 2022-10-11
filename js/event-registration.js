jQuery(document).ready(function() {

  if(document.querySelector('.arb-form__register')) {

    document.querySelectorAll('[data-required-field]').forEach(reqElement => {

      // const parentElement = element.parentElement;
      // if(element.dataset.questionType == 'radio' && element.dataset.questionType == 'source' && element.dataset.questionType == 'checkbox') {
      //   validationElement = document.querySelector(`.${target.dataset.requiredField}`);
      // } else {
      //   validationElement = document.querySelector(`.${parentElement.dataset.requiredField}`);
      // }

      
      const elements = reqElement.querySelectorAll('input, option');

      if(reqElement.dataset.questionType != 'select' && elements != null && elements.length > 0) {
        elements.forEach(element => {
          element.addEventListener('click', resetValidationCheck, false);
          element.addEventListener('focus', resetValidationCheck, false);
        });
      } else {
        reqElement.addEventListener('click', resetValidationCheck, false);
        reqElement.addEventListener('focus', resetValidationCheck, false);
      }
    });

    const venueSelect = document.querySelector('.arb-form__venue-select');
    if (venueSelect) {
      if (venueSelect.value != '') {
        toggleVenue(venueSelect.value);
      }
      venueSelect.addEventListener('change', toggleVenue);
    }
    
    document.querySelector('.arb-form__register').addEventListener('click', submitForm);
  }
});

// Clear validation text on focus
function resetValidationCheck(event) {
  console.log('Event target');
  console.log(event.target);
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
}

function submitForm() {
  // el.preventDefault();
  console.log('submit form');
  // let requestedNum;
  let topElement = null;

  const requiredElements = document.querySelectorAll('[data-required-field]');

  // Reset the validation elements
  requiredElements.forEach(reqElement => {
    const validationElement = document.querySelector(`.${reqElement.dataset.requiredField}`);
    validationElement.innerHTML = '';
  });

  // Check form validation
  requiredElements.forEach(reqElement => {
    const elements = reqElement.querySelectorAll('input, option');

    if(reqElement.dataset.questionType != 'select' && elements != null && elements.length > 0) {
      let input = false;
      elements.forEach(element => {
        // console.log(element);
        if (element.checked) {
          input = true;
        }
      })

      // For checkboxes check if there is at least one entry
      if(!input) {
        // console.log('no input');
        topElement = validationCheck(reqElement, topElement);
      }
    } else if(reqElement.value == '' || reqElement.value == 0 || reqElement.value == null){
      // console.log('empty value');
      topElement = validationCheck(reqElement, topElement);
    } else if(reqElement.id == 'e-mail') {
      var mailformat = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
      if(!reqElement.value.toLowerCase().match(mailformat)) {
        // console.log('invalid email');
        topElement = validationCheck(reqElement, topElement);
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
    })

    if (document.querySelector('#venue')) {
      data.location = document.querySelector('.arb-form__venue:not(.arb-form__hidden)').dataset.venue;
    } 
    data.email = document.querySelector('#e-mail').value;
    data.firstName = document.querySelector('#first-name').value;
    data.lastName = document.querySelector('#last-name').value;
    data.action = 'arboretum_event_registration';
    // data.availability = returned.dataset.availability;
    data.event = returned.dataset.event;
    if (returned.dataset.user) {
      data.user = returned.dataset.user;
    } else {
      data.user = 68;
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
        } else {
          alert(JSON.stringify(response));
        }
      },
      error: function(response) {
        alert(JSON.stringify(response));
      }
    })
    .done(function(data) {
      alert("DONE");
      alert(data);
    });
  }
}