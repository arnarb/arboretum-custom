jQuery(document).ready(function() {


  jQuery('.my-account-event__cancel__button').on('click', function(el) {
    el.preventDefault();

    console.log(el);
    const button = el.currentTarget;// document.querySelector('.my-account-event__cancel__button');
    const ticket_id = button.dataset.ticket;
    const nonce = button.dataset.nonce;
    const data  = {
      action: 'arboretum_ticket_cancelation',
      ticket_id: ticket_id,
      nonce: nonce
    };
    alert(`${ticket_id} and ${nonce}`);

    jQuery.ajax({
      type: 'post',
      dataType: 'json',
      url: arbAjax.ajaxurl,
      data: data,
      success: function(response) {
        if (response.type == 'success') {

          const ticket = jQuery(`.my-account-event[data-ticket=${ticket_id}]`);
          if (ticket) {
            ticket.remove();
          }

          updateView();
        }
      }
    });
  });
});

function updateView() {
  location.reload();
  // const containers = document.querySelectorAll('.my-account-event');

  // containers.forEach(container => {

  // });
  console.log("Reload the page - UPDATE VIEW");
}
