/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Search
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define(
    [
        'jquery',
        'Magento_Catalog/js/price-utils',
        'Mageplaza_Core/js/jquery.autocomplete.min'
    ], function ($, priceUtils) {
        'use strict';

        $.widget('mpsearch.autocomplete', {
            _create: function () {
                if (algoliaConfig.autocomplete.enabled === "1") {
                    this.searchSuggestion()
                }
            },

            searchSuggestion: function () {
                var self              = this,
                    searchInput       = $('.mpSearch-form .mpSearch-input'),
                    searchVar         = [],
                    suggestionElement = $('.autocomplete-suggestions');

                if (algoliaConfig.isEnableSuggestion === '1') {
                    searchVar = this.sortBy(searchVar);
                }

                searchInput.on('click', function () {
                    var searchValue = searchInput.val();

                    if (!searchValue.length) {
                        var resultHtml = '<div><div class="ais-Hits"><ol class="ais-Hits-list mpsearch-list">',
                            cateId     = $('#mpsearch-category').val(),
                            count      = 0;
                        searchVar.map(function (val) {
                            if (cateId && cateId !== "0") {
                                if ($.inArray(cateId, val.c) !== -1) {
                                    resultHtml += self.handleItemHtml(val);
                                    count++;
                                }
                            } else {
                                resultHtml += self.handleItemHtml(val);
                                count++;
                            }
                        });
                        if (!count) {
                            resultHtml += '<div class="ais-Hits ais-Hits--empty no-results">There are no recommended products</div>';
                        }
                        resultHtml += '</ol></div></div>';

                        suggestionElement.html(resultHtml).show();
                    }
                });
            },

            handleItemHtml: function (suggestion) {
                var self         = this,
                    html         = '<li class="ais-Hits-item mpsearch-list-item"><div class="result-wrapper" itemProp="item">',
                    displayInfo  = algoliaConfig.displayInfo,
                    currencyRate = parseFloat(algoliaConfig.currencyRate.replace(",", "")),
                    priceFormat  = algoliaConfig.priceFormat,
                    priceByCurrency;

                html += '<a class="result" href="' + self.correctProductUrl(suggestion.u) + '"><div class="result-content">';
                html += '<div class="result-thumbnail">';
                if ($.inArray('image', displayInfo) !== -1) {
                    if (suggestion.i) {
                        html += '<img class="img-responsive" src="' + self.correctProductUrl(suggestion.i, true) + '" alt="" />';
                    } else {
                        html += '<span class="no-image"></span>';
                    }
                }
                html += '</div>';

                html += '<div class="result-sub-content">';
                html += '<div class="product-line product-name">' + suggestion.value + '</div>';

                if ($.inArray('price', displayInfo) !== -1) {
                    if (suggestion.p.toString().indexOf('-') == -1) {
                        priceByCurrency = suggestion.p * currencyRate;
                        html += '<div class="product-line product-price">' + $.mage.__('Price ') + priceUtils.formatPrice(priceByCurrency, priceFormat) + '</div>';
                    } else {
                        priceByCurrency = suggestion.p.split('-');
                        html += '<div class="product-line product-price">' + $.mage.__('Price ') + priceUtils.formatPrice(parseFloat(priceByCurrency[0]) * currencyRate, priceFormat) + ' - ' + priceUtils.formatPrice(parseFloat(priceByCurrency[1]) * currencyRate, priceFormat) + '</div>';
                    }
                }

                if ($.inArray('description', displayInfo) !== -1 && suggestion.d && suggestion.d.replace('""', '')) {
                    html += '<div class="result-description text-ellipsis">' + suggestion.d + '</div>';
                }

                html += '</div></div></a></div></li>';

                return html;
            }
            ,

            correctProductUrl: function (urlKey, isImage) {
                var baseUrl      = algoliaConfig.baseUrl,
                    baseImageUrl = algoliaConfig.baseImageUrl;

                if (urlKey.search('http') !== -1) {
                    return urlKey;
                }

                return ((typeof isImage !== 'undefined') ? baseImageUrl : baseUrl) + urlKey;
            }
            ,

            sortBy: function (searchVar) {
                var sortBy = algoliaConfig.sortBy;

                if (sortBy === 'new_products') {
                    searchVar = mp_new_product_search;
                } else if (sortBy === 'most_viewed_products') {
                    searchVar = mp_most_viewed_products;
                } else {
                    searchVar = mp_bestsellers;
                }

                return searchVar;
            }
        });

        return $.mpsearch.autocomplete;
    }
);
