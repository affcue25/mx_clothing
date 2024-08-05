var config = {
    paths: {
        'imagesloaded': 'Smartwave_Porto/js/imagesloaded',
        'packery': 'Smartwave_Porto/js/packery.pkgd',
        'themeSticky': 'js/jquery.sticky.min',
        'pt_appear': 'Smartwave_Porto/js/apear',
        'pt_animate': 'Smartwave_Porto/js/animate',
        'lazyload': 'js/jquery.lazyload',
        'owlcarousel': 'owl.carousel/owl.carousel',
        'parallax': 'js/jquery.parallax.min',
        'floatelement': 'js/jquery.floatelement',
        'marquee': 'Smartwave_Porto/js/marquee.min'
    },
    shim: {
        'packery': {
            deps: ['jquery','imagesloaded']
        },
        'themeSticky': {
            deps: ['jquery']
        },
        'pt_animate': {
          deps: ['jquery','pt_appear']
        },
        'owlcarousel': {
            deps: ['jquery']
        },
        'lazyload': {
            deps: ['jquery']
        },
        'floatelement': {
          deps: ['jquery']
        },
        'marquee': {
          deps: ['jquery']
        }
    }
};
