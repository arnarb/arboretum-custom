jQuery(document).ready(function() {

  jQuery('.arb-form__register').click(function(el) {
    // el.preventDefault();

    const form = document.querySelector('#event-registration-form');
    const returned = document.querySelector('.arb-form__register');
    const requested = document.querySelector('#requested');
    const questionInputs = document.querySelectorAll('.custom-question');
    // let questionIds = [];
    let n = 0;

    questionInputs.forEach(questionInput => {
      // questionIds.push(questionId.name);
      let questionType = questionInput.dataset('question-type');

      switch (questionType) {
        case 'checkbox':
          alert("Checkbox");
          break;
        case 'radio':
          alert("Radio");
          break;
        case 'source':
          alert("Source");
          break;
        case 'text':
          alert("Text");
          break;
      }
    })

    // alert(questionIds);
    // Validate the form
    const requestedNum = parseInt(requested.value);
    if (isNaN(requestedNum) || !(requestedNum > 0)) {
      const req_validation = jQuery('.arb-form__requested-validation');
      // alert(`FAILURE! ${requestedNum}`);

      req_validation.html("You need to select a value greater than 0.");
      return;
    } else {

      alert(`heyo ${returned.dataset.availability}, ${returned.dataset.event}, ${returned.dataset.user}, ${returned.dataset.nonce}, ${requestedNum}`);
      data = {
        action: 'arboretum_event_registration',
        availability: returned.dataset.availability,
        event: returned.dataset.event,
        user: returned.dataset.user,
        nonce: returned.dataset.nonce,
        requested: requestedNum,
        // questionIds: questionIds
      };
      let elements = form.elements;

      for (let i = 0, element; element = elements[i++];){
        data[element.name] = element.value;
        // alert(`Element ${element.value}`);
      }

      // data += `&availability=${availability}&event=${event}&eventId=${eventId}&userId=${userId}&action=arboretum_event_registration`;

      document.querySelector('#event-registration-form').remove();
      document.querySelector('#result').innerHTML = 'Thank you for registering! You will receive a confirmation email with more information about the program.';
      alert("Woohoo");
      // alert(data);

      // alert(`Requested: ${requestedNum}   userId: ${data.userId}    eventId: ${data.eventId}`);
//////////////
      // form_data = jQuery('form').serializeArray();

      // form_data.foreach(element => {
      //   data[element['name']] = element['value'];
      // });
      // alert(`data: ${JSON.stringify(data)}`);
///////////////
      jQuery.ajax({
        type: 'post',
        dataType: 'json',
        url: arbAjax.ajaxurl,
        data: data,
        success: function(response) {
          if (response.type == 'success') {
            alert('success');

          } else {
            alert('failure');
          }
        }
      });
    }
  });
});


// class EventRegistration {
//   constructor() {
//     this.form = document.querySelector('#event-registration-form');
//     // this.login = this.form.querySelector('.submit input[type=submit]');
//     this.register = this.form.querySelector('.arb-form__register');
//     this.guest_register = this.form.querySelector('.arb-form__guest-register');
//     this.requested = this.form.querySelector('#requested');

//     this.availability = this.form.dataset.availability;
//     this.event = this.form.dataset.event;
//     this.eventId = this.form.dataset.eventId;
//     this.userId = this.form.dataset.userId;

//     this.init();
//   }

//   init() {
//     console.log(`this requested is : ${this.requested}`);
//     if (this.register) {
//       this.register.addEventListener('click', () => this.registerUser());
//     } else {
//       this.guest_register.addEventListener('click', () => this.registerUser());
//     }
//   }

//   registerUser() {
//     console.log(`Requested number ${this.requested.value}`);

//     if (parseInt(this.requested.value) === 0) {
//       const req_validation = this.form.querySelector('.arb-form__requested-validation');

//       req_validation.innerHTML = "You need to select a value greater than 0."
//       return;
//     }

//     const formData = new FormData(this.form);
//     const data = new URLSearchParams();
//     console.log('Form Data:');
//     //console.log(formData);


//     for (const [key, val] of formData.entries()) {
//       data.append(key, val);
//       console.log(key + ', ' + val);
//     }

//     data.append('availability', this.availability);
//     data.append('requested', this.requested.value);
//     data.append('event', this.event);
//     data.append('eventId', this.eventId);
//     data.append('userId', this.userId);
//     // data.append('userName', 'test@gmail.com');
//     // data.append('something', 'dumb');

//     // console.log('Data:');
//     // console.log(data);

//     this.form.remove();
//     document.getElementById("result").innerHTML = 'Thank you for registering!<br><br>You will receive a confirmation email.';

//     // let formBody = [];
//     // // for (var property in details) {
//     // for (const [key, val] of formData.entries()) {
//     //   var encodedKey = encodeURIComponent(key);
//     //   var encodedValue = encodeURIComponent(val);
//     //   formBody.push(encodedKey + "=" + encodedValue);
//     // }
//     // formBody = formBody.join("&");
//     // console.log('Form Body: %s', formBody);

//     fetch('/events/event-registration/', {
//       method: 'POST',
//       body: data
//     })
//     .then(response => response.text())
//     .catch(err => console.log(err));
//     // .then((response) => response.text())
//     // .then((res) => {
//     //   document.getElementById("result").innerHTML = 'Response:<br><br> ' + res;
//     //   this.form.remove();
//     // });




//     // /// JSON Attempt
//     // const headers = {
//     //   'Accept': 'application/json',
//     //   'Content-Type': 'application/json'
//     // };

//     // const datum = JSON.stringify({
//     //   'user': 'wtf'
//     // });

//     // console.log('JSON variation: ');
//     // console.log(datum);


//     // //
//     // // mode: 'cors',
//     // // headers: headers,
//     // //

//     // fetch('https://staging-arnoldarboretumwebsite.kinsta.cloud/events/event-registration/', {
//     //   method: 'POST',
//     //   body: datum
//     // })
//     // .then((response) => response.json())
//     // .then((res) => {
//     //   document.getElementById("result2").innerHTML += 'JSON Response:<br><br> ' + res;
//     //   this.form.remove();
//     // });

//   }
// }

// export default EventRegistration;
