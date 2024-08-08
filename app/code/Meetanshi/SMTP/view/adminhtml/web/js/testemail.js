define([
    "jquery",
    "Magento_Ui/js/modal/alert",
    "mage/translate",
    "jquery/ui"
], function ($, alert, $t) {
    "use strict";

    $.widget('meetanshi.testEmail', {
        options: {
            ajaxUrl: '',
            testEmail: '#smtp_configuration_option_test_email_sent',
            fromEmail: '#smtp_configuration_option_test_email_from',
            hostVal: '#smtp_configuration_option_host',
            portVal: '#smtp_configuration_option_port',
            auth: '#smtp_configuration_option_authentication',
            protocolVal: '#smtp_configuration_option_protocol',
            usernameVal: '#smtp_configuration_option_username',
            passwordVal: '#smtp_configuration_option_password',
            rerutnPath: '#smtp_configuration_option_return_path_email'
        },
        _create: function () {
            var self = this;

            $(this.options.testEmail).click(function (e) {
                e.preventDefault();
                if (self.element.val()) {
                    self._ajaxSubmit();
                }
            });
        },

        _ajaxSubmit: function () {
            $.ajax({
                url: this.options.ajaxUrl,
                data: {
                    from: $(this.options.fromEmail).val(),
                    to: this.element.val(),
                    host: $(this.options.hostVal).val(),
                    port: $(this.options.portVal).val(),
                    authentication: $(this.options.auth).val(),
                    protocol: $(this.options.protocolVal).val(),
                    username: $(this.options.usernameVal).val(),
                    password: $(this.options.passwordVal).val(),
                    returnpath: $(this.options.rerutnPath).val()
                },
                dataType: 'json',
                showLoader: true,
                success: function (result) {
                    alert({
                        title: result.status ? $t('Success') : $t('Error'),
                        content: result.content
                    });
                }
            });
        }
    });

    return $.meetanshi.testEmail;
});
