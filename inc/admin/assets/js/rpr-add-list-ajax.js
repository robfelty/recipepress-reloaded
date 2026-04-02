/**
 * The jquery class handling the shortcode insertion for recipe lists
 *
 * @since   1.0.0
 *
 * @package Recipepress
 * @author  wzyMedia <wzy@outlook.com>
 */

var rprRecipeLs;

(function ($) {
    var editor, searchTimer, River, Query,
        inputs = {},
        rivers = {},
        isTouch = ( 'ontouchend' in document );

    rprRecipeLs = {
        timeToTriggerRiver: 150,
        minRiverAJAXDuration: 200,
        riverBottomThreshold: 5,
        keySensitivity: 100,
        lastSearch: '',
        textarea: '',

        init: function () {
            inputs.wrap = $('#rpr-modal-wrap-scl');
            inputs.dialog = $('#rpr-modal-form-scl');
            inputs.backdrop = $('#rpr-modal-backdrop-scl');
            inputs.submit = $('#rpr-modal-submit-scl');
            inputs.close = $('#rpr-modal-close-scl');
            // URL
            inputs.id = $('#recipe-id-field');
            inputs.nonce = $('#rpr_list_nonce');
            // Secondary options
            inputs.title = $('#retitle-title-field');
            // Advanced Options
            inputs.optlink = $('#rpr-modal-scl-options-link');
            inputs.opticon = $('#rpr-modal-scl-options-link i');
            inputs.optpanel = $('#rpr-modal-scl-options-panel');
            inputs.taxonomy = $('#recipe-taxonomy');
            inputs.openInNewTab = $('#link-target-checkbox');
            inputs.search = $('#rpr-search-field');
            inputs.excerpt = $('#rpr-embed-excerpt');
            inputs.excerpt.prop("checked", false);
            inputs.nodesc = $('#rpr-embed-nodesc');
            inputs.nodesc.prop("checked", false);

            // Build Rivers
            rivers.search = new River($('#rpr-search-results'));
            rivers.recent = new River($('#rpr-most-recent-results'));
            rivers.elements = inputs.dialog.find('.query-results');

            // Get search notice text
            inputs.queryNotice = $('#query-notice-message');
            inputs.queryNoticeTextDefault = inputs.queryNotice.find('.query-notice-default');
            inputs.queryNoticeTextHint = inputs.queryNotice.find('.query-notice-hint');

            // Bind event handlers
            inputs.dialog.keydown(rprRecipeLs.keydown);
            inputs.dialog.keyup(rprRecipeLs.keyup);
            inputs.submit.click(function (e) {
                e.preventDefault();
                rprRecipeLs.update();
            });
            inputs.close.add(inputs.backdrop).add('#rpr-modal-cancel-scr a').click(function (event) {
                event.preventDefault();
                rprRecipeLs.close();
            });

            /* Button to open dialog */
            $('#rpr-add-recipe-list').on('click', function (e) {
                e.preventDefault();
                var editor_id = jQuery('#rpr-add-recipe-list').attr('data-editor');
                window.rprRecipeLs.open(editor_id);
            });
        },

        open: function (editorId) {
            var ed;

            rprRecipeLs.range = null;

            if (editorId) {
                window.wpActiveEditor = editorId;
            }

            if (!window.wpActiveEditor) {
                return;
            }

            this.textarea = $('#' + window.wpActiveEditor).get(0);

            if (typeof tinymce !== 'undefined') {
                ed = tinymce.get(wpActiveEditor);

                if (ed && !ed.isHidden()) {
                    editor = ed;
                } else {
                    editor = null;
                }

                if (editor && tinymce.isIE) {
                    editor.windowManager.bookmark = editor.selection.getBookmark();
                }
            }

            if (!rprRecipeLs.isMCE() && document.selection) {
                this.textarea.focus();
                this.range = document.selection.createRange();
            }

            inputs.wrap.show();
            inputs.backdrop.show();

            rprRecipeLs.refresh();
            $(document).trigger('rprRecipeLs-open', inputs.wrap);
        },

        isMCE: function () {
            return editor && !editor.isHidden();
        },

        refresh: function () {

            if (rprRecipeLs.isMCE()) {
                rprRecipeLs.mceRefresh();
            } else {
                rprRecipeLs.setDefaultValues();
            }

            if (isTouch) {
                // Close the onscreen keyboard
                inputs.id.focus().blur();
            } else {
                // Focus the URL field and highlight its contents.
                // If this is moved above the selection changes,
                // IE will show a flashing cursor over the dialog.
                inputs.id.focus()[0].select();
            }
        },

        mceRefresh: function () {
            var e;

            // If link exists, select proper values.
            if (e = editor.dom.getParent(editor.selection.getNode(), 'A')) {
                // Set URL and description.
                inputs.id.val(editor.dom.getAttrib(e, 'href'));
                inputs.title.val(editor.dom.getAttrib(e, 'title'));
                // Set open in new tab.
                inputs.openInNewTab.prop('checked', ( '_blank' === editor.dom.getAttrib(e, 'target') ));
                // Update save prompt.
                inputs.submit.val(rprRecipeScL10n.update);

                // If there's no link, set the default values.
            } else {
                rprRecipeLs.setDefaultValues();
            }
        },

        close: function () {
            if (!rprRecipeLs.isMCE()) {
                rprRecipeLs.textarea.focus();

                if (rprRecipeLs.range) {
                    rprRecipeLs.range.moveToBookmark(rprRecipeLs.range.getBookmark());
                    rprRecipeLs.range.select();
                }
            } else {
                editor.focus();
            }

            inputs.backdrop.hide();
            inputs.wrap.hide();
            $(document).trigger('rprRecipeLs-close', inputs.wrap);
        },

        update: function () {
            if (rprRecipeLs.isMCE()){
                rprRecipeLs.mceUpdate();
            } else {
                rprRecipeLs.htmlUpdate();
            }
        },

        //Build the shortcode here!
        htmlUpdate: function () {
            var attrs, html, begin, end, cursor, title, selection,
                textarea = rprRecipeLs.textarea;

            if (!textarea) {
                return;
            }


            var out = "[";
            var sel = $("input[name='rpr-modal-scl-mode']:checked");
            switch (sel.val()) {
                case 'rpr-tax-list':
                    out += 'rpr-tax-list ';
                    out += 'taxonomy="' + $('#rpr-modal-form-scl select option:selected').val().replace('rpr_', '') + '" ';
                    out += 'count="9"';
                    break;
                case 'rpr-index':
                    out += 'rpr-index ';
                    out += 'header="true" ' ;
                    out += 'thumbnail="false"' ;
                    break;
                case 'rpr-ingredients':
                    out += 'rpr-ingredients ';
                    out += 'header="true"' ;
                    out += 'counts="true"' ;
                    break;
                default:
                    alert('Error: Please select one of the available choices');
                    return;
            }
            out += "]";

            // Insert shortcode
            if (document.selection && rprRecipeLs.range) {
                // IE
                // Note: If no text is selected, IE will not place the cursor
                //       inside the closing tag.
                textarea.focus();
                rprRecipeLs.range.text = out;
            } else if (typeof textarea.selectionStart !== 'undefined') {
                // W3C
                begin = textarea.selectionStart;
                end = textarea.selectionEnd;
                selection = textarea.value.substring(begin, end);
                cursor = begin + out.length;
                textarea.value = textarea.value.substring(0, begin) + out +
                textarea.value.substring(end, textarea.value.length);
                // Update cursor position
                textarea.selectionStart = textarea.selectionEnd = cursor;
            }

            rprRecipeLs.close();
            textarea.focus();
        },

        mceUpdate: function () {
            var out = "[";
            var sel = $("input[name='rpr-modal-scl-mode']:checked");
            switch (sel.val()) {
                case 'rpr-tax-list':
                    out += 'rpr-tax-list ';
                    out += 'taxonomy="' + $('#rpr-modal-form-scl select option:selected').val().replace('rpr_', '') + '" ';
                    out += 'count="9"';
                    break;
                case 'rpr-index':
                    out += 'rpr-index ';
                    out += 'header="true" ' ;
                    out += 'thumbnail="false"' ;
                    break;
                case 'rpr-ingredients':
                    out += 'rpr-ingredients ';
                    out += 'header="true" ' ;
                    out += 'counts="true"' ;
                    break;
                default:
                    alert('Error: Please select one of the available choices');
                    return;
            }
            out += "]<br/>";

            rprRecipeLs.close();
            editor.focus();

            tinyMCE.activeEditor.execCommand('mceReplaceContent', false, out);
        },

        updateFields: function (e, li) {
            inputs.id.val(li.children('.item-id').val());
            inputs.title.val(li.hasClass('no-title') ? '' : li.children('.item-title').text());
        },

        setDefaultValues: function () {
            // Set id to default
            inputs.id.val('');
            // Set description to default.
            inputs.title.val('');

            // Update save prompt.
            inputs.submit.val(rprRecipeScL10n.save);
        },

    };

    River = function (element, search) {
        var self = this;
        this.element = element;
        this.ul = element.children('ul');
        this.contentHeight = element.children('#link-selector-height');
        this.waiting = element.find('.river-waiting');

        this.change(search);
        this.refresh();

        $('#rpr-link .query-results, #rpr-link #link-selector').scroll(function () {
            self.maybeLoad();
        });
        element.on('click', 'li', function (event) {
            self.select($(this), event);
        });
    };

    $.extend(River.prototype, {
        refresh: function () {
            this.deselect();
            this.visible = this.element.is(':visible');
        },
        show: function () {
            if (!this.visible) {
                this.deselect();
                this.element.show();
                this.visible = true;
            }
        },
        hide: function () {
            this.element.hide();
            this.visible = false;
        },
        // Selects a list item and triggers the river-select event.
        select: function (li, event) {
            var liHeight, elHeight, liTop, elTop;

            if (li.hasClass('unselectable') || li == this.selected)
                return;

            this.deselect();
            this.selected = li.addClass('selected');
            // Make sure the element is visible
            liHeight = li.outerHeight();
            elHeight = this.element.height();
            liTop = li.position().top;
            elTop = this.element.scrollTop();

            if (liTop < 0) // Make first visible element
                this.element.scrollTop(elTop + liTop);
            else if (liTop + liHeight > elHeight) // Make last visible element
                this.element.scrollTop(elTop + liTop - elHeight + liHeight);

            // Trigger the river-select event
            this.element.trigger('river-select', [li, event, this]);
        },
        deselect: function () {
            if (this.selected)
                this.selected.removeClass('selected');
            this.selected = false;
        },
        prev: function () {
            if (!this.visible)
                return;

            var to;
            if (this.selected) {
                to = this.selected.prev('li');
                if (to.length)
                    this.select(to);
            }
        },
        next: function () {
            if (!this.visible)
                return;

            var to = this.selected ? this.selected.next('li') : $('li:not(.unselectable):first', this.element);
            if (to.length)
                this.select(to);
        },
        ajax: function (callback) {
            var self = this,
                delay = this.query.page == 1 ? 0 : rprRecipeLs.minRiverAJAXDuration,
                response = rprRecipeLs.delayedCallback(function(results, params) {
                    self.process(results, params);
                    if (callback)
                        callback(results, params);
                }, delay);

            this.query.ajax(response);
        },
        change: function (search) {
            if (this.query && this._search == search)
                return;

            this._search = search;
            this.query = new Query(search);
            this.element.scrollTop(0);
        },
        process: function (results, params) {
            var list = '', alt = true, classes = '',
                firstPage = params.page == 1;

            if (!results) {
                if (firstPage) {
                    list += '<li class="unselectable no-matches-found"><span class="item-title"><em>' +
                        rprRecipeScL10n.noMatchesFound + '</em></span></li>';
                }
            } else {
                $.each(results, function () {
                    classes = alt ? 'alternate' : '';
                    classes += this.title ? '' : ' no-title';
                    list += classes ? '<li class="' + classes + '">' : '<li>';
                    list += '<input type="hidden" class="item-id" value="' + this.id + '" />';
                    list += '<span class="item-title">';
                    list += this.title ? this.title : rprRecipeScL10n.noTitle;
                    list += '</span><span class="item-info">' + rprRecipeScL10n.recipe + '</span></li>';
                    alt = !alt;
                });
            }

            this.ul[firstPage ? 'html' : 'append'](list);
        },
        maybeLoad: function () {
            var self = this,
                el = this.element,
                bottom = el.scrollTop() + el.height();

            if (!this.query.ready() || bottom < this.contentHeight.height() - rprRecipeLs.riverBottomThreshold)
                return;

            setTimeout(function () {
                var newTop = el.scrollTop(),
                    newBottom = newTop + el.height();

                if (!self.query.ready() || newBottom < self.contentHeight.height() - rprRecipeLs.riverBottomThreshold)
                    return;

                self.waiting.show();
                el.scrollTop(newTop + self.waiting.outerHeight());

                self.ajax(function () {
                    self.waiting.hide();
                });
            }, rprRecipeLs.timeToTriggerRiver);
        }
    });


    Query = function (search) {
        this.page = 1;
        this.allLoaded = false;
        this.querying = false;
        this.search = search;
    };

    $.extend(Query.prototype, {
        ready: function () {
            return !( this.querying || this.allLoaded );
        },
        ajax: function (callback) {
            var self = this,
                query = {
                    action: 'rpr_get_list',
                    page: this.page,
                    rpr_list_nonce: inputs.nonce.val()
                };

            if (this.search)
                query.search = this.search;

            this.querying = true;

            $.post(
                ajaxurl,
                query,
                function (r) {
                    self.page++;
                    self.querying = false;
                    self.allLoaded = !r;
                    callback(r, query);
                },
                'json'
            );
        }
    });

    $(document).ready(rprRecipeLs.init);

})(jQuery);
