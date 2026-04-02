<?php
/**
 * The link modal used by our plugin
 *
 * @since 2.0.0
 *
 * @var \WP_Post $recipe
 * @var \Recipepress\Inc\Admin\Metaboxes\Link $this
 *
 * @package Recipepress
 */
?>
<div data-controller="rpr-link"
     data-action="rpr-equipment:open@window->rpr-link#getLinkData rpr-source:open@window->rpr-link#getLinkData rpr-ingredients:open@window->rpr-link#getLinkData"
     id="rpr-link-modal"
     class="rpr-link-modal wp-core-ui has-text-field" style="" role="dialog" aria-labelledby="rpr-modal-title">
    <form id="rpr-link" tabindex="-1" class="rpr-modal-content">
        <div id="rpr-modal-header">
            <h1 id="rpr-modal-title"><?php _e( 'Insert/edit link', 'recipepress-reloaded' ); ?></h1>
            <div class="rpr-modal-close" data-action="click->rpr-link#closeModal">
                <span class="screen-reader-text"><?php _e( 'Close', 'recipepress-reloaded' ); ?></span>
            </div>
        </div>
        <div id="rpr-link-selector">
            <p class="howto" style="text-align:center"><?php _e( 'Enter the destination URL', 'recipepress-reloaded' ); ?></p>
            <div class="rpr-link-data">
                <div class="rpr-link-url" style="margin-top:0;">
                    <label for="rpr-link-url"><?php _e( 'URL', 'recipepress-reloaded' ); ?></label>
                    <input data-rpr-link-target="linkUrl" id="rpr-link-url" type="text">
                </div>
                <div class="rpr-link-text" style="margin-top:2px; display:none;">
                    <label for="rpr-link-text"><?php _e( 'Link Text', 'recipepress-reloaded' ); ?></label>
                    <input data-rpr-link-target="linkText" id="rpr-link-text" type="text">
                </div>
                <div class="rpr-link-target" style="margin-top:6px">
                    <input data-rpr-link-target="linkBehaviour" type="checkbox" id="rpr-link-target">
                    <label for="rpr-link-target"><?php _e( 'Open link in a new tab', 'recipepress-reloaded' ); ?></label>
                </div>
            </div>
            <div id="rpr-search-panel">
                <p id="rpr-existing-content"><?php _e( 'Or link to existing content', 'recipepress-reloaded' ); ?></p>
                <div class="link-search-wrapper">
                    <label for="rpr-link-search"><?php _e( 'Search', 'recipepress-reloaded' ); ?></label>
                    <input data-action="input->rpr-link#searchPosts focus->rpr-link#searchSelect blur->rpr-link#searchSelect"
                           data-rpr-link-target="searchField"
                           type="search" id="rpr-link-search"
                           class="link-search-field" autocomplete="off"
                           aria-describedby="wplink-link-existing-content">
                    <span class="spinner"></span>
                </div>
            </div>
            <div id="rpr-recent-results" class="query-results" tabindex="0">
                <div class="rpr-query-notice" id="rpr-query-notice">
                    <em class="rpr-notice-default"><?php _e( 'No search term specified. Showing recent items.', 'recipepress-reloaded' ); ?></em>
                    <em class="rpr-notice-hint screen-reader-text"><?php _e( 'Type in your search term, then select from the results.', 'recipepress-reloaded' ); ?></em>
                </div>
                <ul data-rpr-link-target="linkList" id="rpr-post-list"></ul>
            </div>

        </div>
        <div id="modal-footer">
            <div id="rpr-link-cancel" data-action="click->rpr-link#closeModal">
                <button type="button" class="button"><?php _e( 'Cancel', 'recipepress-reloaded' ); ?></button>
            </div>
            <div id="rpr-link-update" data-action="click->rpr-link#addLink">
                <button class="button button-primary" id="rpr-link-submit"><?php _e( 'Add Link', 'recipepress-reloaded' ); ?></button>
            </div>
        </div>
    </form>
</div>
<div id="rpr-link-modal-backdrop" class="rpr-modal-backdrop" style="" onclick="rpr.rprLinkController.closeModal(); return false;"></div>

<style>
    .rpr-link-modal {
        background-color: #fff;
        box-shadow: 0 3px 6px rgb(0 0 0);
        width: 424px;
        min-width: 280px;
        overflow: hidden;
        transform: translateX(-50%) translateY(-50%);
        position: fixed;
        top: 50%;
        left: 50%;
        z-index: 100120;
        transition: height .2s,margin-top .2s;
        display: none;
    }
    .rpr-modal-show {
        display: block;
    }
    .rpr-modal-backdrop {
        position: fixed;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        background: #000;
        opacity: 0;
        z-index: 100100;
        transition: all 0.3s;
        display: none;
    }
    .rpr-modal-show ~ .rpr-modal-backdrop {
        display: block;
        opacity: .8;
    }
    #rpr-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: nowrap;
        background: #fcfcfc;
        border-bottom: 1px solid #ddd;
        height: 36px;
        padding: 0 0 0 1rem;
    }
    #rpr-modal-title {
        margin: 0;
        line-height: 1;
        font-size: 1.4rem;
    }
    .rpr-modal-close {
        width: 36px;
        height: 36px;
        cursor: pointer;
        display: inline-flex;
        justify-content: center;
        align-items: center;
        background-color: #eaeaea;
    }
    .rpr-modal-close:before {
        font: normal 20px/36px dashicons;
        vertical-align: top;
        speak: never;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        content: "\f158";
    }
    .rpr-link-data {
        display: flex;
        flex-direction: column;
        margin: 0 1rem 1rem;
        align-items: center;
    }
    .rpr-link-data input[type=text] {
        width: 100%;
    }
    .rpr-link-url,
    .rpr-link-text,
    .rpr-link-target {
        width: 95%;
    }
    .rpr-link-url label,
    .rpr-link-text label {
        display: block;
    }
    #modal-footer {
        display: flex;
        flex-wrap: nowrap;
        justify-content: space-between;
        padding: 7px 1rem;
        border-top: 1px solid #dcdcde;
    }
    #rpr-recent-results ul {
        background-color: #f9f9f9;
        max-height: 8rem;
        overflow-y: scroll;
        margin: 0 1rem 1rem;
        min-height: 128px;
    }
    #rpr-recent-results li {
        padding: 5px 10px;
        display: flex;
        justify-content: space-between;
        flex-wrap: nowrap;
        cursor: pointer;
        margin: 0;
    }
    #rpr-recent-results li:nth-child(2n+1) {
        background-color: #ffffff;
    }
    #rpr-recent-results li:hover {
        background-color: #eaeaea;
    }
    #rpr-query-notice {
        padding: 8px 1rem;
        border-left: 5px solid;
        background-color: #f5f5f5;
        margin: 0 1rem;
        border-bottom: 1px solid #ccc;
    }
    #rpr-search-panel {
        margin: 0 1rem 1rem;
        border-top: 1px solid #ccc;
    }
    .link-search-wrapper label {
        display: block;
    }
    #rpr-existing-content {
        text-align: center;
    }
    .link-search-wrapper {
        position: relative;
        margin: 0 3rem;
    }
    .link-search-wrapper input {
        width: 100%;
    }
    div#rpr-search-panel .spinner {
        position: absolute;
        right: 0;
        top: 18px;
    }
    div#rpr-search-panel .spinner.busy {
        visibility: visible;
    }

    @media screen and (max-width: 600px) {
        #rpr-link-modal {
            width: 320px;
        }
    }

    @media screen and (max-width: 320px) {
        #rpr-link-modal {
            width: 300px;
        }
    }
</style>