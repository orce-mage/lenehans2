require(["jquery",
    "Magento_Ui/js/modal/modal",
    "Wyomind_Framework/js/codemirror5/lib/codemirror",
    "Wyomind_Framework/js/codemirror5/addon/merge/merge",
    "Wyomind_Framework/js/codemirror5/addon/merge/diff_match_patch"
], function($, modal, codeMirror) {
    "use strict";
    $(document).on("click", ".history-modal", function(event) {
        event.preventDefault();
        let modalId = $(this).data("id");
        let modalContent = $(this).data("content");
        let modalContainer = $("#history-modal-popup-" + modalId);

        modalContainer.modal({
            title: $.mage.__("Action details (action_id " + modalId + ")"),
            type: "slide",
            responsive: true,
            innerScroll: true,
            buttons: []
        });

        modalContainer.modal("openModal");

        let target = document.getElementById("history-modal-popup-" + modalId);
        target.innerHTML = "";

        let original = $(this).data("original");
        let current = $(this).data("current");

        if (typeof $.parseJSON(JSON.stringify(original)) == "object") {
            original = JSON.stringify(original, null, "\t");
        }
        if (typeof $.parseJSON(JSON.stringify(current)) == "object") {
            current = JSON.stringify(current, null, "\t");
        }

        let mv = codeMirror.MergeView(target, {
            origLeft: original,
            value: current,
            lineNumbers: true,
            mode: "application/x-httpd-php",
            showDifferences: true,
            connect: 'align',
            collapseIdentical: false,
            readOnly: true,
            revertButtons: false,
            chunkClassLocation: ['background', 'gutter'],
            autoRefresh: true
        });
    });

    $(document).on("click","a.history-more-info.active", function(event) {
        event.preventDefault();
        $(this).toggleClass("active");
        $(this).next().toggleClass("active");
        $(this).parents("td").first().find(".history-details").toggleClass("active");
    });

    $(document).on("click","a.history-less-info.active", function(event) {
        event.preventDefault();
        $(this).prev().toggleClass("active");
        $(this).toggleClass("active");
        $(this).parents("td").first().find(".history-details").toggleClass("active");
    });
});