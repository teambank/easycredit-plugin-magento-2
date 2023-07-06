var onHydrated = function (selector, cb) {    
    window.setTimeout(function() {
        if (!document.querySelector(selector) || !document.querySelector(selector).classList.contains('hydrated')) {
            return onHydrated(selector, cb);
        }
        cb();
    }, 50)
};

var moveCardToListing = function () {
    var card = document.querySelector('easycredit-box-listing');

    if ( card ) {
        var list = document.querySelector('.products.list');

        if ( list ) {
            list.prepend(card);

            styleCardListing();
            styleCardListingHydrated();
            positionCardInListing();
        }
    }
}

var styleCardListing = function () {
    var card = document.querySelector('easycredit-box-listing');

    if ( card ) {
        var siblings = n => [...n.parentElement.children].filter(c=>c!=n);
        var siblingsCard = siblings(card);

        var cardWidth = siblingsCard[0].clientWidth;
        var cardHeight = siblingsCard[0].clientHeight;
        var cardClasses = siblingsCard[0].classList;

        card.style.width = cardWidth + 'px';
        card.style.height = cardHeight + 'px';
        card.style.visibility = 'hidden';
        card.classList = card.classList + ' ' + cardClasses;

        if ( siblingsCard[0].tagName === 'LI' ) {
            card.style.listStyle = 'none';

            if ( card.parentElement.tagName === 'UL' || card.parentElement.tagName === 'OL' ) {
                card.parentElement.classList = card.parentElement.classList + ' easycredit-card-columns-adjusted';
            }
        }
    }
}

var styleCardListingHydrated = function () {
    var card = document.querySelector('easycredit-box-listing');

    if ( card ) {
        card.shadowRoot.querySelector('.ec-box-listing').style.maxWidth = '100%';
        card.shadowRoot.querySelector('.ec-box-listing').style.height = '100%';
        card.shadowRoot.querySelector('.ec-box-listing__image').style.minHeight = '100%';
        card.style.visibility = '';
    }
}

var positionCardInListing = function () {
    var card = document.querySelector('easycredit-box-listing');

    if ( card ) {
        var siblings = n => [...n.parentElement.children].filter(c=>c!=n);
        var siblingsCard = siblings(card);

        var position = card.getAttribute('position');
        var previousPosition = ( typeof position === undefined ) ? null : Number( position - 1 );
        var appendAfterPosition = ( typeof position === undefined ) ? null : Number( position - 2 );

        if ( !position || previousPosition <= 0 ) {
            // do nothing
        } else if ( appendAfterPosition in siblingsCard ) {
            siblingsCard[appendAfterPosition].after(card);
        } else {
            card.parentElement.append(card);
        }
    }
}

onHydrated('easycredit-box-listing', moveCardToListing);
