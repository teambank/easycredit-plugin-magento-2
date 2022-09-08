(function (factory) {
if (typeof define === 'function' && define.amd) {
    // AMD. Register as an anonymous module depending on jQuery.
    define(['jquery'], factory);
} else {
    // No AMD. Register plugin with global jQuery object.
    factory(jQuery);
}
}(function ($) {

    var verifyStyle = function(selector) {
        var rules;
        var haveRule = false;
        if (typeof document.styleSheets != "undefined") {
            var cssSheets = document.styleSheets;
            outerloop:
            for (var i = 0; i < cssSheets.length; i++) {
                rules =  (typeof cssSheets[i].cssRules != "undefined") ? cssSheets[i].cssRules : cssSheets[i].rules;
                if (rules) {
                    for (var j = 0; j < rules.length; j++) {
                        if (rules[j].selectorText == selector) {
                             haveRule = true;
                            break outerloop;
                        }
                    }
                }
            }
        }
        return haveRule;
    }

    window.easycreditBootstrapLoaded = false;
    var bootstrapModal = {
        bootstrapJs: 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js',
        template: ['<div class="modal fade" tabindex="-1" role="dialog">',
              '<div class="modal-dialog" role="document">',
                '<div class="modal-content">',
                  '<div class="modal-header">',
                    '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>',
                    '<h4 class="modal-title" style="color:#0066b3;">easyCredit-Ratenkauf</h4>',
                  '</div>',
                  '<div class="modal-body easycredit-embed-responsive"></div>',
                '</div>',
              '</div>',
            '</div>'
        ].join('\n'),
        checkBootstrap: function() {
            return (typeof $().modal == 'function');
        },
        ensureBootstrap: function(cb) {
            if (this.checkBootstrap() || window.easycreditBootstrapLoaded) {
                return cb();
            }

            window.easycreditBootstrapLoaded = true;
            jQuery.ajax({
                url: this.bootstrapJs,
                dataType: 'script',
                success: cb,
                async: true
            });

            if (!verifyStyle('modal-sm')) {
                var link = document.createElement('link');
                link.setAttribute("rel", "stylesheet");
                link.setAttribute("type", "text/css");
                link.setAttribute("href", 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css');
                document.getElementsByTagName("head")[0].appendChild(link);
            }
        },
        handleModal: function(element, content) {
            var modal = $(this.template);
            modal.find('.modal-body').css({
                'height': '700px',
                'max-height':'100%'
            }).append(content);
            $(element).append(modal);

            modal.modal({
                keyboard: false,
                backdrop : 'static'
            }).on('hidden.bs.modal', function(){
                modal.remove();
            });
        },
        init: function(element, content) {
            this.ensureBootstrap(function(){
                this.handleModal(element,content);
            }.bind(this));
        }
    }

    var defaults = {
        hostname: 'https://ratenkauf.easycredit.de',
        endpoint: '/ratenkauf-ws/rest/v1/modellrechnung/guenstigsterRatenplan',
        iframeSrc: '/widget/app/#/ratenwunsch',
        modal: bootstrapModal.init.bind(bootstrapModal),
        webshopId: null,
        amount: null,
        debug: false,
        currencySymbol: '&euro;', //"\u25B2",
        installmentTemplate: '%amount% %currency_symbol% / Monat',
        widgetTemplate: [
            '<div class="easycredit-widget">',
                '<span class="easycredit-suffix">%suffix% </span>',
                '<span class="easycredit-rate">%installmentTemplate%</span>',
                '<br />',
                '<a class="easycredit-link">%link_text%</a>',
            '</div>'
        ].join("\n"),
        suffix: 'Finanzieren ab',
        linkText: 'mehr Infos zum Ratenkauf'
    }

    var getApiUri = function(opts){
        return [
            opts.hostname+opts.endpoint,
            $.param({
                webshopId: opts.webshopId,
                finanzierungsbetrag: opts.amount
            })
        ].join('?');
    }
    var getIframeUri = function(opts){
        return [
            opts.hostname+opts.iframeSrc,
            $.param({
                'shopKennung': opts.webshopId,
                'bestellwert': opts.amount
            })
        ].join('?');
    }
    var getMinimumInstallment = function(uri,cb) {
        $.ajax({
            type : 'GET',
            url : uri,
            contentType : 'application/json; charset=utf-8',
            dataType : 'jsonp',
            success: cb
        });
    };
    var formatAmount = function( amount ) {
        return Number(Math.round(amount+'e2')+'e-2').toFixed(2).replace('.',',');
    }
    var template = function( template, data ){
        return template
          .replace(
            /%(\w*)%/g,
            function( m, key ){
              return data.hasOwnProperty( key ) ? data[ key ] : "";
            }
          );
    }
    var loadStyles = function(uri) {
        var bs = document.createElement('link');
        bs.rel   = 'stylesheet';
        bs.media ="screen";
        bs.href  = uri;
        document.head.appendChild(bs);
    }
    var getModalContent = function(uri) {
        return '<iframe class="embed-responsive-item" src="' + uri + '"></iframe>';
    }
    var showModal = function(element, opts) {
        var content = getModalContent(
            getIframeUri(opts)
        );
        opts.modal(element, content);
    }

    var rkPaymentPage = function(opts) {
        var opts = $.extend({}, defaults, opts);
        var me = $(this);

        if ($(this).data('easycredit-amount')) {
            opts.amount = $(this).data('easycredit-amount');
        }

        if (isNaN(opts.amount) || opts.amount < 200 || opts.amount > 10000) {
            if (opts.debug) {
                console.log(opts.amount+' is not within allowed range');
            }
            return;
        }

        if (opts.webshopId == null
            || opts.webshopId == ''
        ) {
            throw new Error('webshopId must be set for easycredit widget');
        }

        var uri = getApiUri(opts);
        getMinimumInstallment(uri, function(res){
            if (!res || res.wsMessages.messages.length > 0) {
                return;
            }

            var data = {
                number_of_installments:   res.anzahlRaten,
                amount:                   formatAmount(res.betragRate),
                currency_symbol:          opts.currencySymbol,
                suffix:                   opts.suffix,
                link_text:                opts.linkText
            };
            data.installmentTemplate =    template(opts.installmentTemplate, data);

            var widget = $(template(opts.widgetTemplate,data));
            $(me).append(widget);
            widget.find('a').click(
                showModal.bind(this, me, opts)
            );
        });
    }

    $.fn.rkPaymentPage = function(opts) {
        return this.each(function(index,element){
            rkPaymentPage.apply(element,[opts]);
        });
    };

    window.rkPlugin = {};
    window.rkPlugin.anzeige = function(componentID, options) {
        $('#' + componentID).rkPaymentPage(options);
    };
}));
