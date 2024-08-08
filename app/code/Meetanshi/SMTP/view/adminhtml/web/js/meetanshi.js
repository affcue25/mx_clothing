require(['jquery'], function ($) {
    var hostBox = $('#smtp_configuration_option_host'),
        portBox = $('#smtp_configuration_option_port'),
        protocolBox = $('#smtp_configuration_option_protocol');

    $(function () {
        $('#smtp_configuration_option_provider').on('change', function () {
            var host, protocol, port;
            if($(this).val() == 'Gmail') {
                host = 'smtp.gmail.com';
                port = '465';
                protocol = 'ssl';
            }
            else  if($(this).val() == 'Zoho') {
                host = 'smtp.zoho.in';
                port = '465';
                protocol = 'ssl';
            } else if($(this).val() == 'amazon_ses') {
                host = 'email-smtp.us-east-1.amazonaws.com';
                port = '587';
                protocol = 'tls';
            } else if($(this).val() == 'mandrill') {
                host = 'smtp.mandrillapp.com';
                port = '587';
                protocol = 'tls';
            }
            hostBox.val(host);
            portBox.val(port);
            protocolBox.val(protocol);
        });
    });
});