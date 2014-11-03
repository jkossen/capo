////////////////////////////////////////////////////////////////////////////
// CAPO.settings.event_log sub namespace
// Functionality for the 'settings / API accounts' screen
////////////////////////////////////////////////////////////////////////////
/*
    Capo, a web interface for querying multiple Cacti instances
    Copyright (C) 2013  Jochem Kossen <jochem@jkossen.nl>

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

CAPO.settings = CAPO.settings || {};
CAPO.settings.api_accounts = CAPO.settings.api_accounts || {};

(function(ns) {
    "use strict";
    var _scroller = null;
    var tpl_api_accounts_result_list_item = ns.html.api_accounts_result_list_item();
    var tpl_col_api_account_is_active = ns.html.col_api_account_is_active();
    var tpl_api_account_show_username = ns.html.api_account_show_username();
    var tpl_api_account_edit_username = ns.html.api_account_edit_username();
    var tpl_api_account_show_secret = ns.html.api_account_show_secret();
    var tpl_api_account_edit_secret = ns.html.api_account_edit_secret();
    var tpl_api_account_cacti_instances_list_item = ns.html.api_account_cacti_instances_list_item();

    //
    // Load required HTML templates
    //
    var tpl_error_msg = ns.html.msg_container('error');

    var show_error = function(msg) {
        return $('#error_container').append(
            tpl_error_msg({
                'msg': msg
            }));
    };

    var init = function() {
        // Prevent submitting the search form on enter
        $('#search-form').on('submit', function(event) {
            event.preventDefault();
        });

        enable_search_input();
        enable_results();
        enable_add_account_form();

        $('#active_accounts_only').on('change', function(event) {
            event.preventDefault();
            refresh_results();
        });        
    }

    var refresh_results = function() {
        _scroller.reset();
        load_results(true);
    };

    var load_cacti_instances = function(account, available) {
        var el = '#api-account-' + account.id + '-ci-select_selected';
        var data = {
            page: _scroller.page,
            page_limit: _scroller.per_page
        };

        if (available) {
            data['exclude_api_account_id'] = account.id;
            el = '#api-account-' + account.id + '-ci-select_deselected';
        } else {
            data['api_account_id'] = account.id;
        }

        $.ajax({
            url: ns.get('base_url') + 'api/admin/get_cacti_instances/',
            type: ns.get('request_method'),
            dataType: 'json',
            data: data,
            success: function(response, textStatus, jqXHR) {
                $.each(response.cacti_instances, function(index, ci) {
                    $('<option>').attr({
                        'value': ci.id
                    })
                    .text(ci.name)
                    .appendTo($(el));
                });
            }
        });
    }

    var edit_cacti_instances_row = function(account) {
        var row_id = 'api-account-edit-cacti-instances-' + account.id;

        $('#api-account-' + account.id).after(
            tpl_api_account_cacti_instances_list_item({
                'id': account.id
            }));

        load_cacti_instances(account, false);
        load_cacti_instances(account, true);

        $('#btn-edit-access-' + account.id).unbind('click')
            .on('click', function(event) {
                event.preventDefault();
                $('#' + row_id).toggle();
            });

        // event handler: remove selected cacti instance from account
        $('#api-account-' + account.id +'-ci-select_btn_deselect').on('click', function(event) {
            event.preventDefault();
            var cacti_instance_id = $('#api-account-' + account.id + '-ci-select_selected option:selected').val();
            if (cacti_instance_id != undefined) {
                disable_cacti_instance_for_account(account, cacti_instance_id);
            }
        });

        // event handler: add selected cacti instance to account
        $('#api-account-' + account.id +'-ci-select_btn_select').on('click', function(event) {
            event.preventDefault();
            var cacti_instance_id = $('#api-account-' + account.id + '-ci-select_deselected option:selected').val();
            if (cacti_instance_id != undefined) {
                enable_cacti_instance_for_account(account, cacti_instance_id);
            }
        });
    }

    // Load a set of user search results
    var load_results = function(clear) {
        $.ajax({
            url: ns.get('base_url') + 'api/admin/get_api_accounts/',
            type: ns.get('request_method'),
            dataType: 'json',
            data: {
                page: _scroller.page,
                page_limit: _scroller.per_page,
                q: $('#filter_1').val(),
                active_accounts_only: $('#active_accounts_only').is(':checked') ? 1 : 0
            },
            success: function(response, textStatus, jqXHR) {
                _scroller.total = response.api_accounts_total;

                if (clear) {
                    $('#results').scrollTop(0);
                    $('#results_list').empty();
                }

                $.each(response.api_accounts, function(index, account) {
                    $('#results_list').append(
                        tpl_api_accounts_result_list_item({
                            'id': account.id,
                        })
                    );
                    account_name(account);
                    account_secret(account);
                    account_is_active(account);

                    $('#btn-edit-access-' + account.id)
                        .on('click',
                            function(event) {
                                event.preventDefault();
                                edit_cacti_instances_row(account);
                            });
                });

                $('#result_count')
                .html('matches: ' + _scroller.total);
                _scroller.unlock();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                _scroller.unlock();
                var ret = $.parseJSON(jqXHR.responseText);
                show_error(jqXHR.status + ' ' + errorThrown +
                   '. ' + ret.message
                );
            }
        });
    };

    // The API account name column
    var account_name = function(account, edit) {
        var col = 'col-account-name-' + account.id;
        var account_id = 'account-name-' + account.id;

        var handler = function(el) {
            if (account.username != el.val()) {
                account.username = el.val();
                account_update(account, function() {});
            }
            account_name(account, false);
        }

        if (edit) {
            $('#' + col).html(tpl_api_account_edit_username({
                'id': account_id,
                'name': account.username
            }));

            $('#' + account_id)
                .focus()
                .on('keypress', function(event) {
                    if (event.which === 13) {
                        event.preventDefault();
                        handler($(this));
                    }
                })
                .on('focusout', function(event) {
                    event.preventDefault();
                    handler($(this));
                });
        } else {
            $('#' + col).html(
                tpl_api_account_show_username({
                    'id': account_id,
                    'name': account.username
                }));

            $('#' + account_id)
                .on('click', function(event) {
                    event.preventDefault();
                    account_name(account, true);
                });
        }
    };

    // The API account 'secret' column
    var account_secret = function(account, edit) {
        var col = 'col-account-secret-' + account.id;
        var el_id = 'account-secret-' + account.id;

        var handler = function(el) {
            if (account.password != el.val()) {
                account.password = el.val();
                account_update(account, function() {});
            }
            account_secret(account, false);
        }

        if (edit) {
            $('#' + col).html(tpl_api_account_edit_secret({
                'id': el_id,
                'secret': account.password
            }));

            $('#' + el_id)
                .focus()
                .on('keypress', function(event) {
                    if (event.which === 13) {
                        event.preventDefault();
                        handler($(this));
                    }
                })
                .on('focusout', function(event) {
                    event.preventDefault();
                    handler($(this));
                });
        } else {
            $('#' + col).html(
                tpl_api_account_show_secret({
                    'id': el_id,
                    'secret': account.password
                }));

            $('#' + el_id)
                .on('click', function(event) {
                    event.preventDefault();
                    account_secret(account, true);
                });
        }
    };

    // The is active column
    var account_is_active = function(account) {
        var btn_class = (account.active) ? 'success' : 'danger';
        var icon_class = (account.active) ? 'ok' : 'remove';
        var col_id = 'col-account-active-' + account.id;
        var btn_id = 'btn-account-is-active-' + account.id;

        var btn = tpl_col_api_account_is_active({
            'btn_id': btn_id,
            'btn_class': btn_class,
            'icon_class': icon_class
        });

        $('#' + col_id).html(btn);

        $('#' + btn_id)
            .on('click', function(event) {
                event.preventDefault();
                if (account.active) {
                    if (! confirm('This will prevent this API account from using Capo. Are you sure?')) {
                        return;
                    }
                }

                account.active = !account.active;
                account_update(account, function() {
                    account_is_active(account);
                });
            });
    };

    var enable_cacti_instance_for_account = function(account, cacti_instance_id) {
        var data = {
            'cacti_instance_id': cacti_instance_id,
            'api_user_id': account.id
        };

        var url = ns.get('base_url') + 'api/admin/enable_cacti_instance_for_api_user/';

        $.ajax({
            url: url,
            type: ns.get('request_method'),
            dataType: 'json',
            data: data,
            success: function(response, textStatus, jqXHR) {
                $('#api-account-' + account.id + '-ci-select_selected').append(
                    $('#api-account-' + account.id + '-ci-select_deselected option:selected'));
            },
            error: function(jqXHR, textStatus, errorThrown) {
                var ret = $.parseJSON(jqXHR.responseText);
                show_error(jqXHR.status + ' ' + errorThrown +
                   '. ' + ret.message
                );
            }
        });
    };

    var disable_cacti_instance_for_account = function(account, cacti_instance_id) {
        var data = {
            'cacti_instance_id': cacti_instance_id,
            'api_user_id': account.id
        };

        var url = ns.get('base_url') + 'api/admin/disable_cacti_instance_for_api_user/';

        $.ajax({
            url: url,
            type: ns.get('request_method'),
            dataType: 'json',
            data: data,
            success: function(response, textStatus, jqXHR) {
                $('#api-account-' + account.id + '-ci-select_deselected').append(
                    $('#api-account-' + account.id + '-ci-select_selected option:selected'));
            },
            error: function(jqXHR, textStatus, errorThrown) {
                var ret = $.parseJSON(jqXHR.responseText);
                show_error(jqXHR.status + ' ' + errorThrown +
                   '. ' + ret.message
                );
            }
        });
    };

    // Save a change to a Account
    var account_update = function(account, fn_success) {
        var url = ns.get('base_url') + 'api/admin/api_user/update/';
        $.ajax({
            url: url,
            type: ns.get('request_method'),
            dataType: 'json',
            data: {
                id: account.id,
                username: account.username,
                password: account.password,
                active: account.active ? 1 : 0,
            },
            success: function(response, textStatus, jqXHR) {
                return fn_success();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                var ret = $.parseJSON(jqXHR.responseText);
                show_error(jqXHR.status + ' ' + errorThrown +
                   '. ' + ret.message
                );
            }
        });
    };

    // Event handler for adding a new Account
    var enable_add_account_form = function() {
        $('#add-account-form').on('submit', function(event) {
            event.preventDefault();
            if ($('#new-account-name').val() != '') {
                $.ajax({
                    url: ns.get('base_url') + 'api/admin/api_user/create/',
                    type: ns.get('request_method'),
                    dataType: 'json',
                    data: {
                        username: $('#new-account-name').val()
                    },
                    success: function(response, textStatus, jqXHR) {
                        refresh_results();
                        $('#add-account-form')[0].reset();
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        _scroller.unlock();

                        var ret = $.parseJSON(jqXHR.responseText);
                        show_error(jqXHR.status + ' ' + errorThrown +
                            '. ' + ret.message
                        );
                    }
                });
            }
        })
    };

    // Refresh results list when typing in the search input box
    var enable_search_input = function() {
        var filter_1_prev_len = 0;
        var filter_1_prev = '';

        $('#filter_1').on('keyup', function(event) {
            event.preventDefault();
            ns.delay(function() {
                var filter_1_cur = $('#filter_1').val();
                if ((filter_1_cur.length === 0 && filter_1_prev.length !== 0) ||
                   (filter_1_cur !== filter_1_prev && filter_1_cur.length >= 2)) {
                        refresh_results();
                        filter_1_prev = filter_1_cur;
                }
            }, 500);
        });
    };

    // Enable the resulting cacti instances list
    var enable_results = function() {
        _scroller = ns.create_infinite_scroller($('#results'), load_results);
        // Initial load of the cacti instances list
        refresh_results();
    };

    ns.settings.api_accounts.init = init;
}(CAPO));
