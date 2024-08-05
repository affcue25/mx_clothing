/*eslint-disable */
/* jscs:disable */

function _inheritsLoose(subClass, superClass) { subClass.prototype = Object.create(superClass.prototype); subClass.prototype.constructor = subClass; _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

define(["Magento_PageBuilder/js/mass-converter/widget-directive-abstract", "Magento_PageBuilder/js/utils/object"], function (_widgetDirectiveAbstract, _object) {
  /**
   * Copyright © Magento, Inc. All rights reserved.
   * See COPYING.txt for license details.
   */

  /**
   * @api
   */
  var WidgetDirective = /*#__PURE__*/function (_widgetDirectiveAbstr) {
    "use strict";

    _inheritsLoose(WidgetDirective, _widgetDirectiveAbstr);

    function WidgetDirective() {
      return _widgetDirectiveAbstr.apply(this, arguments) || this;
    }

    var _proto = WidgetDirective.prototype;

    /**
     * Convert value to internal format
     *
     * @param {object} data
     * @param {object} config
     * @returns {object}
     */
    _proto.fromDom = function fromDom(data, config) {
      var attributes = _widgetDirectiveAbstr.prototype.fromDom.call(this, data, config);

      data.display_type = attributes.display_type;
      data.category_ids = attributes.category_ids;
      data.product_count = attributes.product_count;
      data.product_type = attributes.product_type;
      data.enable_autoplay = attributes.enable_autoplay;
      data.enable_slide_loop = attributes.enable_slide_loop;
      data.show_slide_nav = attributes.show_slide_nav;
      data.show_slide_page = attributes.show_slide_page;
      data.desktop_slide_columns = attributes.desktop_slide_columns;
      data.tablet_slide_columns = attributes.tablet_slide_columns;
      data.mobile_slide_columns = attributes.mobile_slide_columns;
      data.extra_class = attributes.extra_class;

      return data;
    }
    /**
     * Convert value to knockout format
     *
     * @param {object} data
     * @param {object} config
     * @returns {object}
     */
    ;

    _proto.toDom = function toDom(data, config) {
      var attributes = {
        type: "Smartwave\\Filterproducts\\Block\\Widget\\Products",
        template: "Smartwave_Filterproducts::widget/owl_list.phtml",
        show_pager: 0,
        display_type: data.display_type,
        category_ids: data.category_ids,
        product_count: data.product_count,
        product_type: data.product_type,
        enable_autoplay: data.enable_autoplay,
        enable_slide_loop: data.enable_slide_loop,
        show_slide_nav: data.show_slide_nav,
        show_slide_page: data.show_slide_page,
        desktop_slide_columns: data.desktop_slide_columns,
        tablet_slide_columns: data.tablet_slide_columns,
        mobile_slide_columns: data.mobile_slide_columns,
        extra_class: data.extra_class
      };

      (0, _object.set)(data, config.html_variable, this.buildDirective(attributes));
      return data;
    }

    return WidgetDirective;
  }(_widgetDirectiveAbstract);

  return WidgetDirective;
});
//# sourceMappingURL=widget-directive.js.map
