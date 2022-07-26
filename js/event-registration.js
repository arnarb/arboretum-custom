jQuery(document).ready(function() {

  if(document.querySelector('.arb-form__register')) {
    document.querySelector('.arb-form__register').addEventListener('click', () => {
      // el.preventDefault();

      let data = {};
      const form = document.querySelector('#event-registration-form');
      const returned = document.querySelector('.arb-form__register');

      const requiredFields = document.querySelectorAll('[data-required-field');

      requiredFields.forEach(requiredField => {
        switch(requiredField.id){
          case 'venue':
              console.log('is venue');
            break;

          case 'first-name':
            console.log('is first-name');
            break;

          case 'last-name':
            console.log('is last-name');
            break;

          case 'email':
            console.log('is email');
            break;

          case 'requested':
            console.log('is requested');
            break;

          default:
            if(requiredField.id.includes('custom_question_')) {
              console.log('is custom question');

            } else {
              
              console.log('is something else');
            }
            break;
        }  
        console.log(requiredField);
      })
      
      // const requested = document.querySelector('#requested');
      const customQuestions = document.querySelectorAll('.custom-question');
      let n = 0;

      // Validate the form
      const requestedNum = parseInt(requested.value);
      if (isNaN(requestedNum) || !(requestedNum > 0)) {
        const req_validation = document.querySelector('.requested-validation');
        // alert(`FAILURE! ${requestedNum}`);

        req_validation.innerHTML = "You need to select a value greater than 0.";
        return;
      } else {


        const elements = form.elements;
        for (let i = 0, element; element = elements[i++];) {
          if (element.dataset.formRequired === 'true') {
            data[element.name] = element.value;
          }
        }
        data.requested = requestedNum;
        data.questions = returned.dataset.customQuestions;

        customQuestions.forEach(customQuestion => {
          let answer;
          let elements = [];
          let question = customQuestion.dataset.question;
          let questionType = customQuestion.dataset.questionType;

          switch (questionType) {
            case 'checkbox':  
              answer = [];
              elements = customQuestion.querySelectorAll('input[type="checkbox"]');
              elements.forEach(element => {
                if (element.checked) {
                  answer.push(element.value);
                }
              })

              alert(`${questionType}: ${answer.join()}`);
              answer = answer.join(', ');
              break;

            case 'radio':
            case 'source':
              answer = '';
              elements = customQuestion.querySelectorAll('input[type="radio"]');
              elements.forEach(element => {
                if (element.checked) {
                  answer = element.value;
                }
              })

              alert(`${questionType}: ${answer}`);
              break;

            case 'text':
              answer = '';
              element = customQuestion.querySelector('input');
              answer = element.value;
              
              alert(`Text: ${answer}`);
              break;
          }

          data[`question_${n}`] = question;
          data[`answer_${n}`] = answer;
          n++;
        })

        data.action = 'arboretum_event_registration';
        data.availability = returned.dataset.availability;
        data.event = returned.dataset.event;
        data.user = returned.dataset.user;
        data.nonce = returned.dataset.nonce;

        console.log(data);
        alert(JSON.stringify(data));
        
        document.querySelector('#event-registration-form').remove();
        document.querySelector('#result').innerHTML = 'Thank you for registering! You will receive a confirmation email with more information about the program.';

        // data += `&availability=${availability}&event=${event}&eventId=${eventId}&userId=${userId}&action=arboretum_event_registration`;
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
    });
  }
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
