require(
    [
        'jquery',
        "Magento_Ui/js/lib/view/utils/dom-observer",
        'mage/translate'
    ],
    function ($, domObserver) {
        domObserver.get('#amlocator_schedule_fill', function () {
            $('#amlocator_schedule_fill').click(function (e) {
                var monday = $('div[data-index="monday"]').find('.admin__field-control'),
                    mondayData = [],
                    days = ['tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']
                monday.each(function () {
                    var field = $(this).find('select');
                    if (field.length !== null && !$(this).hasClass('admin__control-grouped')) {
                        mondayData.push(field.val());
                    }
                });

                days.forEach(function (day, index) {
                    var day = $('div[data-index="' + day + '"]').find('.admin__field-control'),
                        step = 0;
                    day.each(function () {
                        var dayField = $(this).find('select');
                        if (dayField.length !== null && !$(this).hasClass('admin__control-grouped')) {
                            dayField.val(mondayData[step]).change();
                            step++;
                        }
                    });
                });
            });
        });
    }
);