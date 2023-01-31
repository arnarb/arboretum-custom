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
      // console.log(`Elements: %o`, elements);

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

    const waitlistCheckbox = document.querySelector('.arb-form__waitlist-confirm input');
    if (waitlistCheckbox) {
      waitlistCheckbox.addEventListener('change', toggleWaitlistCheckbox);
    }
    // const requestedSelect = document.querySelector('.arb-form__requested');
    // if (requestedSelect) {
    //   requestedSelect.addEventListener('change', changeRequestedNumber);
    // }
  }
});

// This will handle when the checkbox for accepting the waitlist is selected
function toggleWaitlistCheckbox(event) {
  console.log(`Toggle the form view ${event}`);

  console.log(`Is checked : ${event.checked}`);
}

// Clear validation text on focus
function resetValidationCheck(event) {

  if (event.target.classList.contains('event-calendar__set-date') || event.target.classList.contains('pager ')) {
    // console.log('Dont react to these elements');
    return;
  }
  // console.log(`Event: %o, target %o`, event, event.target);
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
  });

  // Get this to work for original pass or if they change it
  const value = event.target ? event.target.value : event;

  const venueDiv = document.querySelector(`[data-venue="${value}"]`); // (`[data-venue="${event.target.value}"]`);
  console.log('venue changed');
  console.log(venueDiv);

  venueDiv.classList.remove('arb-form__hidden');

  toggleLimit();

  // Set to waitlist text
  // const requested = document.querySelector('#requested');
  // const remainingCapacity = parseInt(venueDiv.dataset.remainingCapacity);
  // const header = document.querySelector('.arb-form__title');
//   const capacity = venueDiv.querySelector('.arb-form__venue__capacity');

//   if (remainingCapacity <= 0) {
//     header.innerHTML = 'Waitlist Registration';
//     // TODO: If there is a waitlist
//     capacity.innerHTML = 'Waitlist';
// //////    capacity.innerHTML = ``;
//   } else {
//     header.innerHTML = 'Event Registration';
//     capacity.innerHTML = `Spots remaining: ${remainingCapacity} / ${venueDiv.dataset.capacity}`;
//   }
// }
}

// Switch up the values for the limit of each venue
function toggleLimit() {
  const notice = document.querySelector('.arb-form__notice');
  const header = document.querySelector('.arb-form__title');
  notice.innerHTML = '';
  const venueDiv = document.querySelector('.arb-form__venue:not(.arb-form__hidden)');
  const limit = venueDiv.dataset.limit;
  const requested = document.querySelector('#requested');
  const remainingCapacity = parseInt(venueDiv.dataset.remainingCapacity);
  const hasWaitlist = venueDiv.dataset.hasWaitlist;
  const capacity = venueDiv.querySelector('.arb-form__venue__capacity');

   for (let i = requested.options.length - 1; i >= 0; i--) {
    requested.remove(i);
   }

  let option = document.createElement('option');
  option.value = '';
  option.selected;
  option.disabled;

  let out = false;
  let canBeOut = true;

  requested.appendChild(option);
  for (let n = 1; n <= limit; n++) {

    // Limit the maximum values
    if (n === 1 && remainingCapacity <= 0) {
      if (hasWaitlist) {
        // if (waitlist checkbox checked) {
          header.innerHTML = 'Waitlist Registration';
          capacity.innerHTML = '<strong>Waitlist</strong>';
          notice.innerHTML = `These reservations will be added to the waitlist and you will be notified if a spot opens up.`;
          canBeOut = false;
          document.querySelector('.arb-form__rest-of-form').classList.remove('hidden');
        //  
        // } else {
        //  document.querySelector('.arb-form__rest-of-form').classList.add('hidden');
        //}
      } else {
        capacity.innerHTML = 'Sorry, this event is sold out and has no waitlist.';
        document.querySelector('.arb-form__rest-of-form').classList.remove('hidden');
      }
    } else if(canBeOut) {
      header.innerHTML = 'Event Registration';
      capacity.innerHTML = `Spots remaining: ${remainingCapacity} / ${venueDiv.dataset.capacity}`;
    }

    if (out) {
      return;
    }
    else if (!canBeOut || remainingCapacity - parseInt(n) + 1 > 0) {
      option = document.createElement('option');
      option.value = n;
      option.innerHTML = n;

      requested.appendChild(option);
    } else {
      const number = n === 2 ? `is only 1 spot` : `are only ${remainingCapacity} spots`;
      const number2 = n === 2 ? `spot` : `${remainingCapacity} spots`
      notice.innerHTML = `There ${number} left.  If you would like to register for more spots, please register for the remaining ${number2} and refresh the page to add participants to the waitlist.`;
      if (canBeOut) {
        out = true;
      }
    }
  }
}

//
// function changeRequestedNumber() {
//   // console.log(`changed requested number: %o`, e);

//   const venue = document.querySelector('.arb-form__venue:not(.arb-form__hidden)');
//   const requested = document.querySelector('#requested');

//   // const capacity = parseInt(venue.dataset.capacity);
//   const remainingCapacity = parseInt(venue.dataset.remainingCapacity)

//   // const notice = document.querySelector('.arb-form__notice');

//   // console.log(`Capacity %o, new value %o, remaining capacity %o`, capacity, parseInt(requested.value), remainingCapacity);
//   // if (remainingCapacity - parseInt(requested.value) < 0) {
//   //   notice.innerHTML = notice.dataset.overLimit;
//   // } else {
//   //   notice.innerHTML = '';
//   // }
// }

function submitForm() {
  // el.preventDefault();
  // console.log('submit form');
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
      // console.log("************************ FOUND THE DATE!");
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
  });
  
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
    data.questions = returned.dataset.customQuestions;// + 1;

    // Source question
    const sourceQuestion = document.querySelector('#source_question');
    let answer;
    let elements = [];

    let question = sourceQuestion.dataset.question;
    let questionType = sourceQuestion.dataset.questionType;

    answer = [];
    elements = sourceQuestion.querySelectorAll('input');
    elements.forEach(element => {
      if (element.checked) {
        answer.push(element.value);
      }
    });
    
    answer = answer.join(', ');

    // data[`question${n}`] = question;
    // data[`answer${n}`] = answer;
    // n++;
    data['source'] = answer;
    
    // Store custom question/answer pairs
    customQuestions.forEach(customQuestion => {
      question = customQuestion.dataset.question;
      questionType = customQuestion.dataset.questionType;

      answer = [];
      elements = customQuestion.querySelectorAll('input');
      elements.forEach(element => {
        if (element.checked) {
          answer.push(element.value);
        }
      })

      // alert(`${questionType}: ${answer.join()}`);
      answer = answer.join(', ');
      
      data[`question${n}`] = question;
      data[`answer${n}`] = answer;
      n++;
    });

    const venue = document.querySelector('#venue');

    data.email = document.querySelector('#e-mail').value;
    data.firstName = document.querySelector('#first-name').value;
    data.lastName = document.querySelector('#last-name').value;
    data.location = venue.querySelector('.arb-form__venue:not(.arb-form__hidden)').dataset.venue;
    data.type = venue.querySelector('.arb-form__venue:not(.arb-form__hidden) .arb-form__venue__type').dataset.type;
    // data.key = venue.querySelector('.arb-form__venue:not(.arb-form__hidden) .arb-form__venue__type').dataset.key;
    data.date = venue.querySelector('.arb-form__venue:not(.arb-form__hidden) .arb-form__venue__date-time').dataset.date;
    data.endTime = venue.querySelector('.arb-form__venue:not(.arb-form__hidden) .arb-form__venue__date-time').dataset.endTime;
    data.action = 'arboretum_event_registration';

    data.eventTitle = returned.dataset.eventTitle;
    data.locationTitle = venue.querySelector('.arb-form__venue:not(.arb-form__hidden)').dataset.venueTitle;

    const result = document.querySelector('#result');
    // Consent form data ///////////////////////////////////////////
    // data.consentName = returned.dataset.consentName;
    // data.consentDate = returned.dataset.consentDate;
    // data.participantNum = returned.dataset.participantNum;

    // for (n = 1; n <= returned.dataset.participantNum; n++) {
    //   data[`participantName${n}`] = returned.getAttribute(`data-participant-name__${n}`);
    //   data[`participantDate${n}`] = returned.getAttribute(`data-participant-date__${n}`);
    // }

    // data.guardianName = returned.dataset.guardianName;
    // data.guardianDate = returned.dataset.guardianDate;
    /////////////////////////////////////////////////////////////////  

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

    let successMessage = result.innerHTML;
    const dateOptions = { weekday: 'long', year: 'numeric', month: 'short', day: 'numeric' };
    const timeOptions = { timeStyle: 'short', timeZone: 'America/New_York' };

    // const requested = data.requested;
    // const eventTitle = data.eventTitle;
    const dateRaw = new Date(data.date);
    const date = dateRaw.toLocaleString(this.US, dateOptions);
    const time = dateRaw.toLocaleString(this.US, timeOptions) + ' - ' + data.endTime;
    const title = '<strong>' + data.eventTitle + '</strong>';
    // const locationTitle = data.locationTitle;


    // const tags               = array('[requested]', '[title]', '[date]', '[time]', '[location]');
  
    // data.requested
    // data.eventTitle     -- get eventname
    // data.date      -- convert from 2022-12-21 17:00:00 to Dec 12, 2022 at 5pm.
    // data.endTime
    // data.locationTitle
    // $date               = new Date(data.date);
    // $time               = date("g:ma",strtotime(data.date)) . ' - ' . data.endTime;

    // $values             = array($event->title, $date, $time, $location->post_title, $cancel_link, $directions, $map_link);
    // $body               = str_replace($tags, $values, $body);
    // $successMessage.replace('[title]', `${event->}`)

    alert(JSON.stringify(data));

    successMessage = successMessage.replace('[requested]', data.requested);
    successMessage = successMessage.replace('[title]', title);
    successMessage = successMessage.replace('[date]', date);
    successMessage = successMessage.replace('[time]', time);
    successMessage = successMessage.replace('[location]', data.locationTitle);

    result.innerHTML = successMessage;
    result.classList.remove('arb-form__hidden');

    document.querySelector('#event-registration-form').remove();

    //      
    //  dataType: 'json',
    jQuery.ajax({
      type: 'post',
      url: arbAjax.ajaxurl,
      data: data,
      success: function(response) {
        if (response.type == 'success') {
          console.log(JSON.stringify(response));
          alert(JSON.stringify(response));

        } else {
          console.log(JSON.stringify(response));
        }
      },
      error: function(response) {
      }
    })
    .done(function(data) {
    });
  }
}