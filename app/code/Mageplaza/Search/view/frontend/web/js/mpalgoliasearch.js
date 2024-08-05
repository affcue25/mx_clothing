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

requirejs(['algoliaBundle', 'Magento_Catalog/js/price-utils'], function (algoliaBundle, priceUtils) {
        algoliaBundle.$(function ($) {
            if (!algoliaConfig.instant.enabled) {
                return;
            }

            /** BC of old hooks **/
            if (typeof algoliaHookBeforeInstantsearchInit === 'function') {
                algolia.registerHook('beforeInstantsearchInit', algoliaHookBeforeInstantsearchInit);
            }

            if (typeof algoliaHookBeforeWidgetInitialization === 'function') {
                algolia.registerHook('beforeWidgetInitialization', algoliaHookBeforeWidgetInitialization);
            }

            if (typeof algoliaHookBeforeInstantsearchStart === 'function') {
                algolia.registerHook('beforeInstantsearchStart', algoliaHookBeforeInstantsearchStart);
            }

            if (typeof algoliaHookAfterInstantsearchStart === 'function') {
                algolia.registerHook('afterInstantsearchStart', algoliaHookAfterInstantsearchStart);
            }

            var instant_selector = "#mp-algoliasearch";
            var query            = '',
                categorySearch   = $('#mpsearch-category'),
                s                = $(".autocomplete-suggestions");

            var searchClient = algoliaBundle.algoliasearch(algoliaConfig.algoliaApiKey.applicationId, algoliaConfig.algoliaApiKey.apiKey);
            var indexName    = algoliaConfig.indexName;

            var instantsearchOptions = {
                searchClient: searchClient,
                indexName: indexName,
                searchFunction: function (e) {
                    query = e.state.query;
                    if ("" === query) {
                        return s.hide();
                    }
                    e.search();
                    s.show();
                }
            };

            instantsearchOptions = algolia.triggerHooks('beforeInstantsearchInit', instantsearchOptions, algoliaBundle);

            var search = algoliaBundle.instantsearch(instantsearchOptions);

            var allWidgetConfiguration = {
                hits: {},
                configure: {
                    hitsPerPage: algoliaConfig.lookupLimit
                }
            };

            allWidgetConfiguration.searchBox = {
                container: instant_selector,
                placeholder: "Search for products, categories...",
                showSubmit: false,
                showLoadingIndicator: false,
                queryHook: function (inputValue, search) {
                    return search(inputValue);
                },
                cssClasses: {
                    form: 'mpSearch-form',
                    input: 'mpSearch-input'
                }
            }

            allWidgetConfiguration.hits = {
                container: ".autocomplete-suggestions",
                templates: {
                    empty: function () {
                        return 'No products for query "' + query + '"';
                    },
                    item: $('#instant-hit-template').html()
                },
                cssClasses: {
                    emptyRoot: 'no-results',
                    list: 'mpsearch-list',
                    item: 'mpsearch-list-item'
                },
                transformItems: function (items) {
                    var displayInfo  = algoliaConfig.displayInfo,
                        currencyRate = parseFloat(algoliaConfig.currencyRate.replace(",", "")),
                        priceFormat  = algoliaConfig.priceFormat;

                    $('.autocomplete-suggestions').html('');
                    return items.map(function (item) {
                        var cateId = categorySearch.val();

                        if (cateId && cateId !== '0') {
                            if ($.inArray(cateId, item.categories) === -1) {
                                return [];
                            }
                        }

                        if ($.inArray('price', displayInfo) !== -1) {
                            var priceByCurrency = item.price * currencyRate;
                            item.price          = priceUtils.formatPrice(priceByCurrency, priceFormat)
                        } else {
                            item.price = false;
                        }

                        item.name        = $("<textarea/>").html(item.name).html();
                        item.description = $("<textarea/>").html(item.description).html();

                        return item;
                    }).filter(x => x.name);
                }
            };

            allWidgetConfiguration = algolia.triggerHooks('beforeWidgetInitialization', allWidgetConfiguration, algoliaBundle);

            $.each(allWidgetConfiguration, function (widgetType, widgetConfig) {
                if (Array.isArray(widgetConfig) === true) {
                    $.each(widgetConfig, function (i, widgetConfig) {
                        addWidget(search, widgetType, widgetConfig);
                    });
                } else {
                    addWidget(search, widgetType, widgetConfig);
                }
            });

            var isStarted = false;

            function startInstantSearch () {
                if (isStarted === true) {
                    return;
                }

                search = algolia.triggerHooks('beforeInstantsearchStart', search, algoliaBundle);
                search.start();
                search = algolia.triggerHooks('afterInstantsearchStart', search, algoliaBundle);

                isStarted = true;
            }

            categorySearch.on('change', function (e) {
                search.refresh();
            });

            /** Initialise searching **/
            startInstantSearch();
            $('.search-category').show();

            function addWidget (search, type, config) {
                if (type === 'custom') {
                    search.addWidgets([config]);
                    return;
                }
                var widget = algoliaBundle.instantsearch.widgets[type];
                if (config.panelOptions) {
                    widget = algoliaBundle.instantsearch.widgets.panel(config.panelOptions)(widget);
                    delete config.panelOptions;
                }

                search.addWidgets([widget(config)]);
            }

            handleClickOutside();

            function handleClickOutside () {

                var resetBtn     = $('.ais-SearchBox-reset'),
                    submitButton = $('.ais-SearchBox-submit'),
                    searchInput  = $('.mpSearch-input');

                if (searchInput.val()) {
                    resetBtn.show();
                    submitButton.hide();
                } else {
                    resetBtn.hide();
                    submitButton.show();
                }

                searchInput.on("change paste keyup", function () {
                    if (this.value) {
                        resetBtn.show();
                        submitButton.hide();
                    } else {
                        resetBtn.hide();
                        submitButton.show();
                    }
                })

                resetBtn.on("click", function () {
                    resetBtn.hide();
                    submitButton.show();
                })

                searchInput.on("keydown", function (event) {
                    if (event.which === 13) {
                        if (query.length >= algoliaConfig.minQueryLength) {
                            window.location.href = algoliaConfig.baseUrl + "catalogsearch/result/?q=" + query;
                        }
                    }
                });

                $(window).click(function (e) {
                    0 === $(e.target).closest(".autocomplete-suggestions").length && 0 === $(e.target).closest(".ais-SearchBox-input").length && 0 === $(e.target).closest("#mpsearch-category").length && $(".autocomplete-suggestions").hide()
                });
            }
        });
    }
);
