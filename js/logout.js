jQuery(document).ready(function() {
    if(document.querySelector('.js-logout-button')) {
        document.querySelector('.js-logout-button').addEventListener('click', (e) => {
            console.log('logout button clicked');
            console.log(e);
            e.preventDefault();
            jQuery.ajax({
                type: 'post',
                url: arbAjaxLogout.ajax_url,
                data: {
                    'action': 'ajaxlogout', //calls wp_ajax_nopriv_ajaxlogout
                    'ajaxsecurity': arbAjaxLogout.logout_nonce
                },
                success: function(response){
                    // When the response comes back
                    // alert('error - logout');
                    // console.log(response);
                    window.location = arbAjaxLogout.home_url;
                },
                error: function(response){
                    // console.log(response);
                }
            });
        });
    };
});