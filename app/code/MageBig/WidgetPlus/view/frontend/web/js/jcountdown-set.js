define([
    'jquery',
    'MageBig_WidgetPlus/js/jcountdown'
], function ($) {
    'use strict';

    $.widget('magebig.jCoundown', {
        options: {
        },
        _create: function() {
            var template = "<div class='cd-sale day'><span class='num'>%d</span><span class='unit'>%td</span></div><div class='cd-sale hour'><span class='num'>%h</span><span class='unit'>%th</span></div><div class='cd-sale minute'><span class='num'>%i</span><span class='unit'>%ti</span></div><div class='cd-sale second'><span class='num'>%s</span><span class='unit'>%ts</span></div>",
                hoursOnly = false,
                minsOnly = false,
                secsOnly = false,
                elm = $(this.element);

            if (elm.data('day-loop') == true) {
                var today = new Date();
                today.setDate(today.getDate() + 1);

                var monthNames = [
                    "January", "February", "March",
                    "April", "May", "June", "July",
                    "August", "September", "October",
                    "November", "December"
                ];
                var dateString = monthNames[today.getMonth()] + " " + today.getDate() + ", " + today.getFullYear();
            }

            if (elm.data('hours-only') == true) {
                hoursOnly = true;
                template = "<div class='cd-sale hour'><span class='num'>%h</span><span class='unit'>%th</span></div><div class='cd-sale minute'><span class='num'>%i</span><span class='unit'>%ti</span></div><div class='cd-sale second'><span class='num'>%s</span><span class='unit'>%ts</span></div>";
            }

            if (elm.data('mins-only') == true && !hoursOnly) {
                minsOnly = true;
                template = "<div class='cd-sale minute'><span class='num'>%i</span><span class='unit'>%ti</span></div><div class='cd-sale second'><span class='num'>%s</span><span class='unit'>%ts</span></div>";
            }

            if (elm.data('secs-only') == true && !hoursOnly && !minsOnly) {
                minsOnly = true;
                template = "<div class='cd-sale second'><span class='num'>%s</span><span class='unit'>%ts</span></div>";
            }

            var countdownConfig = {
                date: dateString ? dateString : null,
                dataAttr: dateString ? null : this.options.dataAttr,
                template: template,
                dayText: this.options.dayText,
                hourText: this.options.hourText,
                minText: this.options.minText,
                secText: this.options.secText,
                daySingularText: this.options.daySingularText,
                hourSingularText: this.options.hourSingularText,
                minSingularText: this.options.minSingularText,
                secSingularText: this.options.secSingularText,
                leadingZero: true,
                offset: this.options.offset,
                hoursOnly: hoursOnly,
                minsOnly: minsOnly,
                secsOnly: secsOnly
            };

            elm.countdown(countdownConfig);
        }
    });

    return $.magebig.jCoundown;
});