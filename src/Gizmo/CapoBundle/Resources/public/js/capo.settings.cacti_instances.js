////////////////////////////////////////////////////////////////////////////
// CAPO.settings.cacti_instances sub namespace
// Functionality for the 'settings / cacti instances' screen
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
CAPO.settings.cacti_instances = CAPO.settings.cacti_instances || {};

(function(ns) {
    "use strict";
    var _scroller = null;

    //
    // Load required HTML templates
    //
    var tpl_error_msg = ns.html.msg_container('error');
    var tpl_cacti_instance_result_list_item = ns.html.cacti_instance_result_list_item();
    var tpl_cacti_instance_show_name = ns.html.cacti_instance_show_name();
    var tpl_cacti_instance_edit_name = ns.html.cacti_instance_edit_name();
    var tpl_cacti_instance_show_base_url = ns.html.cacti_instance_show_base_url();
    var tpl_cacti_instance_edit_base_url = ns.html.cacti_instance_edit_base_url();

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

        enable_search_cacti_instances_input();
        enable_cacti_instances_results();
        enable_add_cacti_instance_form();
    };

    var select2_graph_selection_format = function (item) {
        return item.name + ' (' + item.created.date + ')';
    };


    var refresh_results = function() {
        _scroller.reset();
        load_results(true);
    };

    // The Cacti Instance name column
    var cacti_name = function(cacti_instance, edit) {
        var column_id = 'col-name-' + cacti_instance.id;
        var name_id = 'cacti-instance-edit-name-' + cacti_instance.id;

        var handler = function(el) {
            if (cacti_instance.name != el.val()) {
                cacti_instance.name = el.val();
                cacti_instance_update(cacti_instance, function() {});
            }
            cacti_name(cacti_instance, false);
        }

        if (edit) {
            $('#' + column_id).html(
                tpl_cacti_instance_edit_name({
                    'id': name_id,
                    'name': cacti_instance.name
                }));

            $('#' + name_id)
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
            $('#' + column_id).html(
                tpl_cacti_instance_show_name({
                    'id': name_id,
                    'name': cacti_instance.name
                }));

            $('#' + name_id).on('click', function(event) {
                event.preventDefault();
                cacti_name(cacti_instance, true);
            });
        }
    };

    // The Cacti Instance URL column
    var cacti_url = function(cacti_instance, edit) {
        var column_id = 'col-base-url-' + cacti_instance.id;
        var base_url_id = 'cacti-instance-edit-base-url-' + cacti_instance.id;

        var handler = function(el) {
            if (cacti_instance.base_url != el.val()) {
                cacti_instance.base_url = el.val();
                cacti_instance_update(cacti_instance, function() {});
            }
            cacti_url(cacti_instance, false);
        }

        if (edit) {
            $('#' + column_id).html(
                tpl_cacti_instance_edit_base_url({
                    'id': base_url_id,
                    'base_url': cacti_instance.base_url
                }));

            $('#' + base_url_id)
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
            $('#' + column_id).html(
                tpl_cacti_instance_show_base_url({
                    'id': base_url_id,
                    'base_url': cacti_instance.base_url
                }));

            $('#' + base_url_id).on('click', function(event) {
                event.preventDefault();
                cacti_url(cacti_instance, true);
            });
        }
    };

    // Load a set of cacti instance search results
    var load_results = function(clear) {
        $.ajax({
            url: ns.get('base_url') + 'api/get_cacti_instances/',
            type: ns.get('request_method'),
            dataType: 'json',
            data: {
                page: _scroller.page,
                page_limit: _scroller.per_page,
                q: $('#filter_1').val(),
                active_only: 0
            },
            success: function(response, textStatus, jqXHR) {
                _scroller.total = response.cacti_instances_total;

                if (clear) {
                    $('#results').scrollTop(0);
                    $('#results_list').empty();
                }

                var curtime = (new Date).getTime();
                $.each(response.cacti_instances, function(index, cacti_instance) {
                    var d = cacti_instance.import_date.date
                        .replace(/[-,:\s]/g, ',').split(',');
                    var import_date = Date.UTC(d[0], d[1]-1, d[2], d[3], d[4]);
                    var import_date_class = 'import_date';
                    var queue_btn_class = 'success';
                    var activate_btn_class = 'success';
                    var queue_icon = 'ok';
                    var activate_icon = 'ok';

                    if (import_date + 93600000 < curtime) {
                        import_date_class = 'import_date stale';
                    }

                    var str_import_date = 'never';
                    if (cacti_instance.import_date.date !== '1970-01-01 00:00:00') {
                        str_import_date = cacti_instance.import_date.date;
                    }

                    if (cacti_instance.queue_import !== true) {
                        queue_btn_class = 'danger';
                        queue_icon = 'remove';
                    }

                    if (cacti_instance.active !== true) {
                        activate_btn_class = 'danger';
                        activate_icon = 'remove';
                    }

                    $('#results_list').append(
                        tpl_cacti_instance_result_list_item({
                            'id': cacti_instance.id,
                            'name': cacti_instance.name,
                            'import_date': str_import_date,
                            'import_date_class': import_date_class,
                            'queue_btn_class': queue_btn_class,
                            'activate_btn_class': activate_btn_class,
                            'queue_icon': queue_icon,
                            'activate_icon': activate_icon
                            })
                    );

                    cacti_name(cacti_instance, false);
                    cacti_url(cacti_instance, false);

                    $('#btn-queue-ci-' + cacti_instance.id).on('click', function(event) {
                        event.preventDefault();
                        cacti_instance.queue_import = !cacti_instance.queue_import;
                        var new_class = (cacti_instance.queue_import) ? 'btn btn-success' : 'btn btn-danger';
                        var new_icon = (cacti_instance.queue_import) ? 'ok' : 'remove';
                        cacti_instance_update(cacti_instance, function() {
                            $('#btn-queue-ci-' + cacti_instance.id).attr('class', new_class);
                            $('#icon-queue-ci-' + cacti_instance.id).attr('class', 'icon-white icon-' + new_icon);
                        });
                    });

                    $('#btn-activate-ci-' + cacti_instance.id).on('click', function(event) {
                        event.preventDefault();
                        cacti_instance.active = !cacti_instance.active;
                        var new_class = (cacti_instance.active) ? 'btn btn-success' : 'btn btn-danger';
                        var new_icon = (cacti_instance.active) ? 'ok' : 'remove';
                        cacti_instance_update(cacti_instance, function() {
                            $('#btn-activate-ci-' + cacti_instance.id).attr('class', new_class);
                            $('#icon-activate-ci-' + cacti_instance.id).attr('class', 'icon-white icon-' + new_icon);
                        });
                    });

                    $('#edit-cacti-instance-name-' + cacti_instance.id).on('click', function(event) {
                        event.preventDefault();
                        cacti_name(cacti_instance, true);
                    });

                    $('#edit-cacti-instance-base-url-' + cacti_instance.id).on('click', function(event) {
                        event.preventDefault();
                        cacti_url(cacti_instance, true);
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

    // Save a change to a Cacti instance
    var cacti_instance_update = function(cacti_instance, fn_success) {
        var url = ns.get('base_url') + 'api/admin/cacti_instance/update/';
        $.ajax({
            url: url,
            type: ns.get('request_method'),
            dataType: 'json',
            data: {
                id: cacti_instance.id,
                name: cacti_instance.name,
                base_url: cacti_instance.base_url,
                active: cacti_instance.active ? 1 : 0,
                queue_import: cacti_instance.queue_import ? 1 : 0
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

    // Event handler for adding a new Cacti instance
    var enable_add_cacti_instance_form = function() {
        $('#add-cacti-instance-form').on('submit', function(event) {
            event.preventDefault();
            if ($('#new-cacti-instance-name').val() != '' &&
            $('#new-cacti-instance-url').val() != '') {
                $.ajax({
                    url: ns.get('base_url') + 'api/admin/cacti_instance/create/',
                    type: ns.get('request_method'),
                    dataType: 'json',
                    data: {
                        name: $('#new-cacti-instance-name').val(),
                        base_url: $('#new-cacti-instance-url').val(),
                    },
                    success: function(response, textStatus, jqXHR) {
                        refresh_results();
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

    // Refresh cacti instances results list when typing in the search input box
    var enable_search_cacti_instances_input = function() {
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
    var enable_cacti_instances_results = function() {
        _scroller = ns.create_infinite_scroller($('#results'), load_results);
        // Initial load of the cacti instances list
        refresh_results();
    };

    ns.settings.cacti_instances.init = init;
}(CAPO));
