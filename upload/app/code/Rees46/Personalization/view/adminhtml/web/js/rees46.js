<!--
/**
 * Copyright © 2017 REES46, INC. All rights reserved.
 */
-->
function rees46Init() {
    require(['jquery'], function($){
        $('#container').prepend('<div class="messages" id="rees46_messages"></div>');

        $('#rees46_login_button').click(function() {
            $('#rees46_auth_block + .section-config').fadeIn();
            $('#rees46_auth_block + div + .section-config').css('display', 'none');
        });

        $('#rees46_register_button').click(function() {
            $('#rees46_auth_block + .section-config').css('display', 'none');
            $('#rees46_auth_block + div + .section-config').fadeIn();
        });

        $('#submitUserRegister').click(function() {
            rees46UserRegister();
        });

        $('#submitShopLogin').click(function() {
            rees46ShopXML(true);
        });

        $('#submitShopXML').click(function() {
            rees46ShopXML();
        });

        $('#submitShopOrders').click(function() {
            rees46ShopOrders();
        });

        $('#submitShopCustomers').click(function() {
            rees46ShopCustomers();
        });

        $('#submitShopFile1').click(function() {
            rees46ShopFiles();
        });

        $('#submitShopFile2').click(function() {
            rees46ShopFiles();
        });

        rees46LeadTracking();
    });
}

function rees46LeadTracking() {
    require(['jquery'], function($){
        $.ajax({
            url: rees46AjaxUrl,
            data: {
                form_key: window.FORM_KEY,
                action: 'rees46LeadTracking',
            },
            type: 'post',
            dataType: 'json',
            showLoader: false
        });

        return false;
    });
}

function rees46UserRegister() {
    require(['jquery'], function($){
        $.ajax({
            url: rees46AjaxUrl,
            data: {
                form_key: window.FORM_KEY,
                action: 'rees46UserRegister',
                email: $('#rees46_register_email').val(),
                phone: $('#rees46_register_phone').val(),
                first_name: $('#rees46_register_first_name').val(),
                last_name: $('#rees46_register_last_name').val(),
                country_code: $('#rees46_register_country_code').val(),
                category: $('#rees46_register_category').val()
            },
            type: 'post',
            dataType: 'json',
            showLoader: true
        }).done(function(json) {
            $('#rees46_messages').empty();
            $('html, body').animate({ scrollTop: 0 }, 'slow');

            if (json.success) {
                $('#rees46_messages').append('<div class="message message-success success"><div data-ui-id="messages-message-success">' + json.success + '</div></div>');

                rees46ShopRegister();
            }

            if (json.message) {
                $('#rees46_messages').append('<div class="message message-error error"><div data-ui-id="messages-message-error">' + json.message + '</div></div>');
            } else if (json.error) {
                $('#rees46_messages').append('<div class="message message-error error"><div data-ui-id="messages-message-error">' + json.error + '</div></div>');
            }
        });

        return false;
    });
}

function rees46ShopRegister() {
    require(['jquery'], function($){
        $.ajax({
            url: rees46AjaxUrl,
            data: {
                form_key: window.FORM_KEY,
                action: 'rees46ShopRegister',
            },
            type: 'post',
            dataType: 'json',
            showLoader: true
        }).done(function(json) {
            $('#rees46_messages').empty();
            $('html, body').animate({ scrollTop: 0 }, 'slow');

            if (json.success) {
                $('#rees46_messages').append('<div class="message message-success success"><div data-ui-id="messages-message-success">' + json.success + '</div></div>');

                rees46ShopXML(true);
            }

            if (json.message) {
                $('#rees46_messages').append('<div class="message message-error error"><div data-ui-id="messages-message-error">' + json.message + '</div></div>');
            } else if (json.error) {
                $('#rees46_messages').append('<div class="message message-error error"><div data-ui-id="messages-message-error">' + json.error + '</div></div>');
            }
        });

        return false;
    });
}

function rees46ShopXML(auth = false) {
    require(['jquery'], function($){
        $.ajax({
            url: rees46AjaxUrl,
            data: {
                form_key: window.FORM_KEY,
                action: 'rees46ShopXML',
                store_key: $('#rees46_login_store_key').val(),
                secret_key: $('#rees46_login_secret_key').val()
            },
            type: 'post',
            dataType: 'json',
            showLoader: true
        }).done(function(json) {
            $('#rees46_messages').empty();
            $('html, body').animate({ scrollTop: 0 }, 'slow');

            if (json.success) {
                $('#submitShopXML').remove();

                $('#rees46_messages').append('<div class="message message-success success"><div data-ui-id="messages-message-success">' + json.success + '</div></div>');

                if (auth) {
                    rees46ShopOrders(1, true);
                }
            }

            if (json.message) {
                $('#rees46_messages').append('<div class="message message-error error"><div data-ui-id="messages-message-error">' + json.message + '</div></div>');
            } else if (json.error) {
                $('#rees46_messages').append('<div class="message message-error error"><div data-ui-id="messages-message-error">' + json.error + '</div></div>');
            }
        });

        return false;
    });
}

function rees46ShopOrders(next = 1, auth = false) {
    require(['jquery'], function($){
        $.ajax({
            url: rees46AjaxUrl,
            data: {
                form_key: window.FORM_KEY,
                action: 'rees46ShopOrders',
                next: next,
            },
            type: 'post',
            dataType: 'json',
            showLoader: true
        }).done(function(json) {
            $('#rees46_messages').empty();
            $('html, body').animate({ scrollTop: 0 }, 'slow');

            if (json.success) {
                $('#rees46_messages').append('<div class="message message-success success"><div data-ui-id="messages-message-success">' + json.success + '</div></div>');
            }

            if (json.next) {
                rees46ShopOrders(json.next);
            } else {
                if (!auth && json.success) {
                    $('#submitShopOrders').remove();
                }

                if (auth) {
                    rees46ShopCustomers(1, true);
                }
            }

            if (json.message) {
                $('#rees46_messages').append('<div class="message message-error error"><div data-ui-id="messages-message-error">' + json.message + '</div></div>');
            } else if (json.error) {
                $('#rees46_messages').append('<div class="message message-error error"><div data-ui-id="messages-message-error">' + json.error + '</div></div>');
            }
        });

        return false;
    });
}

function rees46ShopCustomers(next = 1, auth = false) {
    require(['jquery'], function($){
        $.ajax({
            url: rees46AjaxUrl,
            data: {
                form_key: window.FORM_KEY,
                action: 'rees46ShopCustomers',
                next: next,
            },
            type: 'post',
            dataType: 'json',
            showLoader: true
        }).done(function(json) {
            $('#rees46_messages').empty();
            $('html, body').animate({ scrollTop: 0 }, 'slow');

            if (json.success) {
                $('#rees46_messages').append('<div class="message message-success success"><div data-ui-id="messages-message-success">' + json.success + '</div></div>');
            }

            if (json.next) {
                rees46ShopCustomers(json.next);
            } else {
                if (!auth && json.success) {
                    $('#submitShopCustomers').remove();
                }

                if (auth) {
                    rees46ShopFiles(true);
                }
            }

            if (json.message) {
                $('#rees46_messages').append('<div class="message message-error error"><div data-ui-id="messages-message-error">' + json.message + '</div></div>');
            } else if (json.error) {
                $('#rees46_messages').append('<div class="message message-error error"><div data-ui-id="messages-message-error">' + json.error + '</div></div>');
            }
        });

        return false;
    });
}

function rees46ShopFiles(auth = false) {
    require(['jquery'], function($){
        $.ajax({
            url: rees46AjaxUrl,
            data: {
                form_key: window.FORM_KEY,
                action: 'rees46ShopFiles',
            },
            type: 'post',
            dataType: 'json',
            showLoader: true
        }).done(function(json) {
            $('#rees46_messages').empty();
            $('html, body').animate({ scrollTop: 0 }, 'slow');

            if (json.success) {
                $('#submitShopFile1').remove();
                $('#submitShopFile2').remove();

                $.map(json.success, function(success) {
                    $('#rees46_messages').append('<div class="message message-success success"><div data-ui-id="messages-message-success">' + success + '</div></div>');
                });
            }

            if (json.message) {
                $('#rees46_messages').append('<div class="message message-error error"><div data-ui-id="messages-message-error">' + json.message + '</div></div>');
            } else if (json.error) {
                $.map(json.error, function(error) {
                    $('#rees46_messages').append('<div class="message message-error error"><div data-ui-id="messages-message-error">' + error + '</div></div>');
                });
            }

            if (auth) {
                if ($('#rees46_login_store_key').val() == '' && $('#rees46_login_secret_key').val() == '') {
                    rees46ShopFinish();
                } else {
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                }
            }
        });

        return false;
    });
}

function rees46ShopFinish() {
    require(['jquery'], function($){
        $.ajax({
            url: rees46AjaxUrl,
            data: {
                form_key: window.FORM_KEY,
                action: 'rees46ShopFinish',
            },
            type: 'post',
            dataType: 'json',
            showLoader: true
        }).done(function(json) {
            if (json) {
                $('body').append(json);

                setTimeout(function() {
                    $('#submitShopFinish').submit();
                }, 1000);

                setTimeout(function() {
                    location.reload();
                }, 2000);
            }
        });

        return false;
    });
}
