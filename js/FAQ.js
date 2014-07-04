var FAQ = (function(jQuery){
    return {
        expresscurateSupportSubmit: function(){
            jQuery('#expresscurate_support_form .errorMessage').remove();
            var valid_msg = true;
            var valid_email = true;
            var msg = jQuery("#expresscurate_support_message").val();
            var regularExpression = /^[\w\-\s]+$/;
            valid_msg = regularExpression.test(msg);
            if (msg == "" || msg == null) {
                valid_msg = false;
                jQuery("#expresscurate_support_message").after('<label class="errorMessage">Please enter the message</label>');
            } else if (!valid_msg) {
                jQuery("#expresscurate_support_message").after('<label class="errorMessage">Input is not alphanumeric</label>');
            } else jQuery("#expresscurate_support_message").next('.errorMessage').remove();

            var email = jQuery("#expresscurate_support_email").val();
            var regularExpression = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            valid_email = regularExpression.test(email);
            if (email == "" || email == null) {
                valid_email = false;
                jQuery("#expresscurate_support_email").after('<label class="errorMessage">Please enter the email</label>');
            } else if (!valid_email) {
                jQuery("#expresscurate_support_email").after('<label class="errorMessage">Email is not valid</label>');
            } else jQuery("#expresscurate_support_email").next('.errorMessage').remove();
            if (valid_email && valid_msg) {
                jQuery("#expresscurate_support_form").submit();
            }
            return false;
        }
    }
})(window.jQuery);