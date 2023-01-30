
    console.log('LOGOUT JS');

jQuery(document).ready(function() {
    if(document.querySelector('.js-logout-button')) {
        document.querySelector('.js-logout-button').addEventListener('click', (e) => {
            console.log('logout button clicked');
            console.log(e);
            e.preventDefault();
            jQuery.ajax({
                type: 'POST',
                url: arbAjax.ajax_url,
                data: {
                    'action': 'custom_ajax_logout', //calls wp_ajax_nopriv_ajaxlogout
                    'ajaxsecurity': arbAjax.logout_nonce
                },
                success: function(r){
                    // When the response comes back
                    window.location = arbAjax.home_url;
                }
            });
        });
    };
});