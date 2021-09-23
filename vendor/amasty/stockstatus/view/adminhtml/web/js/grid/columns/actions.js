define([
    'Magento_Ui/js/grid/columns/actions'
], function (actions) {
    /**
     * Checks if specified action requires a handler function.
     * Custom check if url need use POST method type.
     *
     * @param {String} actionIndex - Actions' identifier.
     * @param {Number} rowIndex - Index of a row.
     * @returns {Boolean}
     */
    return actions.extend({
        isHandlerRequired: function (actionIndex, rowIndex) {
            return this._super(actionIndex, rowIndex) || this.getAction(rowIndex, actionIndex).post;
        }
    });
});
