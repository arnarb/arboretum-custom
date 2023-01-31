jQuery(document).ready(function() {
    if(document.querySelector('.js-logout-button')) {
        document.querySelector('.js-logout-button').addEventListener('click', (e) => {
            console.log('logout button clicked');
            console.log(e);
            e.preventDefault();
            jQuery.ajax({
                type: 'post',
                url: arbAjax.ajax_url,
                data: {
                    'action': 'ajaxlogout', //calls wp_ajax_nopriv_ajaxlogout
                    'ajaxsecurity': arbAjax.logout_nonce
                },
                success: function(response){
                    // When the response comes back
                    console.log(response);
                    window.location = arbAjax.home_url;
                },
                error: function(response){
                    console.log(response);
                }
            });
        });
    };
});