////////////////////////////////////////////////////////////////////////////
// CAPO.settings.users sub namespace
// Functionality for the 'settings / users' screen
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
CAPO.settings.users = CAPO.settings.users || {};

(function(ns) {
    "use strict";
    var _scroller = null;

    //
    // Load required HTML templates
    //
    var tpl_error_msg = ns.html.msg_container('error');
    var tpl_users_result_list_item = ns.html.users_result_list_item();
    var tpl_col_user_is_active = ns.html.col_user_is_active();

    var show_error = function(msg) {
        return $('#error-container').append(
            tpl_error_msg({
                'msg': msg
            }));
    };

    var init = function() {
        // Prevent submitting the search form on enter
        $('#search-form').on('submit', function(event) {
            event.preventDefault();
        });

        enable_search_user_input();
        enable_user_results();

        $('#active_users_only').on('change', function(event) {
            event.preventDefault();
            refresh_results();
        });
    };


    var select2_graph_selection_format = function (item) {
        return item.name + ' (' + item.created.date + ')';
    };


    var refresh_results = function() {
        _scroller.reset();
        load_results(true);
    };

    // The Cacti Instance is active column
    var user_is_active = function(user) {
        var btn_class = (user.enabled) ? 'success' : 'danger';
        var icon_class = (user.enabled) ? 'ok' : 'remove';
        var col_id = 'col-user-active-' + user.id;
        var btn_id = 'btn-user-is-active-' + user.id;

        var btn = tpl_col_user_is_active({
            'btn_id': btn_id,
            'btn_class': btn_class,
            'icon_class': icon_class
        });

        $('#' + col_id).html(btn);

        $('#' + btn_id)
            .on('click', function(event) {
                event.preventDefault();
                user.enabled = !user.enabled;
                user_update(user, function() {
                    user_is_active(user);
                });
            });
    };

    // Load a set of user search results
    var load_results = function(clear) {
        $.ajax({
            url: ns.get('base_url') + 'api/admin/get_users/',
            type: ns.get('request_method'),
            dataType: 'json',
            data: {
                page: _scroller.page,
                page_limit: _scroller.per_page,
                q: $('#filter_1').val(),
                active_users_only: $('#active_users_only').is(':CHECKED') ? 1 : 0
            },
            success: function(response, textStatus, jqXHR) {
                _scroller.total = response.users_total;

                if (clear) {
                    $('#results').scrollTop(0);
                    $('#results_list').empty();
                }

                $.each(response.users, function(index, user) {
                    $('#results_list').append(
                        tpl_users_result_list_item({
                            'id': user.id,
                            'name': user.username,
                            'last_login': (user.lastLogin == null) ? 'never' : user.lastLogin.date,
                            'group_id': user.group.id
                        })
                    );
                    user_is_active(user);
                    user_groupselect(user);
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

    var user_groupselect = function(user) {
        return $('#group-' + user.id).select2({
            placeholder: 'Select group',
            allowClear: false,
            width: '350px',
            minimumInputLength: 0,
            ajax: {
                url: ns.get('base_url') + 'api/admin/get_groups/',
                type: ns.get('request_method'),
                dataType: 'json',
                quietMillis: 100,
                data: function(term, page) {
                    return {
                        q: term,
                        page_limit: 25,
                        page: page
                    };
                },
                results: function (data, page) {
                    // whether or not there are more results available
                    var more = (page * 25) < data.groups_total;

                    // notice we return the value of more so Select2 knows if
                    // more results can be loaded
                    return { results: data.groups, more: more };
                }
            },
            initSelection: function(element, callback) {
                var id = user.group.id;
                callback(user.group);
            },
            formatResult: function(item) { return item.name; },
            formatSelection: function(item) { return item.name; },
            dropdownCssClass: "bigdrop" // apply css that makes the dropdown taller
        }).on('change', function(event) {
            event.preventDefault();
            change_group_for_user(user);
        });
    }

    var change_group_for_user = function(user) {
        var data = {
            'user_id': user.id,
            'group_id': $('#group-' + user.id).val()
        };

        var url = ns.get('base_url') + 'api/admin/user/change_group/';

        $.ajax({
            url: url,
            type: ns.get('request_method'),
            dataType: 'json',
            data: data,
            success: function(response, textStatus, jqXHR) {
                $('#col-group-' + user.id).animateHighlight();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                var ret = $.parseJSON(jqXHR.responseText);
                show_error(jqXHR.status + ' ' + errorThrown +
                   '. ' + ret.message
                );
            }
        });
    }

    // Save a change to a User
    var user_update = function(user, fn_success) {
        var url = ns.get('base_url') + 'api/admin/user/update/';
        $.ajax({
            url: url,
            type: ns.get('request_method'),
            dataType: 'json',
            data: {
                id: user.id,
                enabled: user.enabled ? 1 : 0,
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

    // Refresh cacti instances results list when typing in the search input box
    var enable_search_user_input = function() {
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
    var enable_user_results = function() {
        _scroller = ns.create_infinite_scroller($('#results'), load_results);
        // Initial load of the cacti instances list
        refresh_results();
    };

    ns.settings.users.init = init;
}(CAPO));
