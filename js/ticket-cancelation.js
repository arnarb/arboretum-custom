jQuery(document).ready(function() {


  jQuery('.my-account-event__cancel__button').on('click', function(el) {
    el.preventDefault();

    const button = document.querySelector('.my-account-event__cancel__button');
    const ticket_id = button.dataset.ticket;
    const nonce = button.dataset.nonce;
    const data  = {
      action: 'arboretum_ticket_cancelation',
      ticket_id: ticket_id,
      nonce: nonce
    };
    alert(`WTF ${ticket_id} and ${nonce}`);

    jQuery.ajax({
      type: 'post',
      dataType: 'json',
      url: arbAjax.ajaxurl,
      data: data,
      success: function(response) {
        if (response.type == 'success') {
          alert('success');
          console.log("WTF");

          jQuery(`.my-account-event[data-ticket=${ticket_id}]`).remove();
        }
      }
    });
  });
});
