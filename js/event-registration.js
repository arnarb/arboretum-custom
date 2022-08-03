jQuery(document).ready(function() {

  if(document.querySelector('.arb-form__register')) {

    document.querySelectorAll('[data-required-field]').forEach(element => {
      element.addEventListener('click', resetValidationCheck, false);
    });

    document.querySelector('.arb-form__venue').addEventListener('change', toggleVenue);
    
    document.querySelector('.arb-form__register').addEventListener('click', submitForm);
  }
});

// Clear validation text on focus
function resetValidationCheck(event) {
  const validationElement = document.querySelector(`.${event.currentTarget.dataset.requiredField}`);
  validationElement.innerHTML = '';
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

  const venueDiv = document.querySelector(`[data-venue="${event.currentTarget.value}"]`);
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
  requiredElements.forEach(element => {
    const validationElement = document.querySelector(`.${element.dataset.requiredField}`);
    validationElement.innerHTML = '';
  });

  // Check form validation
  requiredElements.forEach(element => {
    const elements = element.querySelectorAll('input, option');

    if(element.dataset.questionType != 'select' && elements != null && elements.length > 0) {
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
        topElement = validationCheck(element, topElement);
      }
    } else if(element.value == '' || element.value == 0 || element.value == null){
      // console.log('empty value');
      topElement = validationCheck(element, topElement);
    } else if(element.id == 'e-mail') {
      var mailformat = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
      if(!element.value.toLowerCase().match(mailformat)) {
        // console.log('invalid email');
        topElement = validationCheck(element, topElement);
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

    data.venue = document.querySelector('#venue').value;
    data.email = document.querySelector('#e-mail').value;
    data.firstName = document.querySelector('#first-name').value;
    data.lastName = document.querySelector('#last-name').value;
    data.action = 'arboretum_event_registration';
    // data.availability = returned.dataset.availability;
    data.event = returned.dataset.event;
    data.user = returned.dataset.user;
    data.nonce = returned.dataset.nonce;

    console.log(data);
    alert(JSON.stringify(data));
    
    document.querySelector('#event-registration-form').remove();
    document.querySelector('#result').innerHTML = 'Thank you for registering! You will receive a confirmation email with more information about the program.';

    jQuery.ajax({
      type: 'post',
      dataType: 'json',
      url: arbAjax.ajaxurl,
      data: data,
      success: function(response) {
        if (response.type == 'success') {
          alert("Success - Woohoo");
        } else {
          alert('failure');
        }
      }
    })
    .done(function(data) {
      alert("DONE");
      alert(data);
    });
  }
}