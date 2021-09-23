define(['jquery'], function ($) {
    var amstockstatus = {
        amstockstatus_ranges_cnt: 0,
        addRange: function (id, from, to, status, rule) {
            var statuses = window.amstatuses,
                rules = window.amrules;

            id = 'undefined' != typeof (id) ? id : '';
            from = 'undefined' != typeof (from) ? from : '';
            status = 'undefined' != typeof (status) ? status : '';
            to = 'undefined' != typeof (to) ? to : '';
            rule = 'undefined' != typeof (rule) ? rule : '';

            var tbd = $('#ranges_table_body'),
                row = $('<tr/>', {
                    id: 'amstockstatus_range_row_' + this.amstockstatus_ranges_cnt,
                    class: "amstockstatus_range_row"
                }).appendTo(tbd),
                cell = $('<td/>', {
                    html: '<input name="amstockstatus_range[' + this.amstockstatus_ranges_cnt + '][entity_id]" value="' + id + '" type="hidden"/><input class="input-text" type="text" size="11" name="amstockstatus_range[' + this.amstockstatus_ranges_cnt + '][qty_from]" value="' + from + '" />'
                }).appendTo(row);

            cell = $('<td/>', {
                html: '<input class="input-text" type="text" size="11" name="amstockstatus_range[' + this.amstockstatus_ranges_cnt + '][qty_to]" value="' + to + '" />'
            }).appendTo(row);

            var text = '<select name="amstockstatus_range[' + this.amstockstatus_ranges_cnt + '][status_id]">';

            $.each(statuses, function (key, item) {
                selected = (status == item.option_id) ? 'selected="selected"' : '';
                text += '<option value="' + item.option_id + '"' + selected + '>' + item.value + '</option>';
            });

            cell = $('<td/>', {
                html: text
            }).appendTo(row);

            if (rules && rules.length > 1) {
                text = '<select style="min-width:110px;" name="amstockstatus_range[' + this.amstockstatus_ranges_cnt + '][rule]">';

                $.each(rules, function (key, item) {
                    selected = (rule == item.option_id) ? 'selected="selected"' : '';
                    text += '<option value="' + item.option_id + '"' + selected + '>' + item.value + '</option>';
                });

                cell = $('<td/>', {
                    html: text
                }).appendTo(row);
            }

            cell = $('<td/>', {
                html: '<button class="am-delete-range action- scalable delete"><span>X</span></button>'
            }).appendTo(row);

            this.amstockstatus_ranges_cnt++;
        },

        removeCurrentRow: function (element) {
            $(element).parents(".amstockstatus_range_row").remove();
        },
    };

    return amstockstatus;
});
