define([
    'Magento_Ui/js/grid/columns/select'
], function (Column) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'ui/grid/cells/html'
        },
        getLabel: function (data) {
            var lbl = this._super(data);

            if (lbl !== '') {
                if (data.status == 1) {
                    lbl = '<span class="mt-smtp-grid-notice grid-severity-notice"><span>' + lbl + '</span></span>';
                } else {
                    lbl = '<span class="mt-smtp-grid-notice grid-severity-minor"><span>' + lbl + '</span></span>';
                }
            }

            return lbl;
        }
    });
});

