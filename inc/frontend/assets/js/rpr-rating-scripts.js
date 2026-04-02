(function ($) {
    "use strict";

    var karmaId = 'comment_karma';

    function doHover(e) {
        $(e.currentTarget).prevAll().addBack()[(e.type === 'mouseenter' ? 'add' : 'remove') + 'Class']('rover');
    }

    function setUI(elem) {
        elem.prevAll().addBack().addClass('whole').removeClass('empty half rover');

        elem.nextAll().addClass('empty').removeClass('whole half rover');
    }

    function doFormRating(e) {
        var elem = $(e.currentTarget),
            indx,
            ctx,
            field,
            $karma;

        $karma = $('#comment_karma');

        setUI(elem);

        ctx = elem.parent();
        indx = ctx.find('li').index(e.currentTarget) + 1;

        if (!$karma.length) {
            field = $('<input type="hidden" />').attr({
                name: karmaId,
                id: karmaId,
                value: indx
            });
            ctx.after(field);
        } else {
            $karma.val(indx);
        }
    }

    function doRating(e) {
        var elem = $(e.currentTarget), indx, ctx;

        setUI(elem);

        ctx = elem.parent();
        indx = ctx.find('li').index(e.currentTarget) + 1;

        $.ajax({
            type: 'post',
            url: rpr_public_vars.ajax_url,
            data: {
                action: 'rate_recipe',
                rating: indx,
                comment_post_ID: ctx.data('id'),
                comment_ID: ctx.data('comment-id'),
                security_nonce: rpr_public_vars.ratings_nonce
            }
        });

        return false;
    }

    $(document).ready(function () {
        var hoverEvents = 'mouseenter mouseleave';

        $('.form-rating li')
            .bind(hoverEvents, doHover)
            .click(doFormRating);

        $('.needs-rating li')
            .bind(hoverEvents, doHover)
            .click(doRating);
    });
}(jQuery));
