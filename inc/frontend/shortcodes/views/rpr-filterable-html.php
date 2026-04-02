<?php
/**
 * @var \WP_Query $recipes An array of recipes
 * @var \Recipepress\Inc\Frontend\Shortcodes\Filterable $this This class
 */

$instance['show_count'] = false;
?>

<div data-controller="rpr-filterable" class="rpr-filterable-recipes">
    <div class="rpr-filterable-selectors">
        <?php foreach ( $this->taxonomies as $key => $values ) {
            // Add an empty option to the top of our select dropdown.
            array_unshift( $values, (object) array( 'name' => sprintf( __( 'Select a %s', 'recipepress-reloaded' ), $key ), 'term_id' => 0, 'count' => 0, 'slug' => '_' ) ); ?>
            <div class="rpr-filter-dropdown">
                <label for="rpr-filter-dropdown-<?php echo esc_html( $key ); ?>"><?php echo esc_html( ucfirst( $key ) ); ?></label>
                <select data-action="change->rpr-filterable#filterItems"
                        name="<?php echo esc_attr( $key ); ?>" class="rpr-filter-dropdown"
                        id="rpr-filter-dropdown-<?php echo esc_html( $key ); ?>">
                    <?php foreach ( $values as $i => $v ) { ?>
                        <?php $selected = in_array( (string) $v->term_id, get_query_var( esc_attr( $key ) . '_ids', array() ), true ) ? 'selected' : null; ?>
                        <option value="<?php echo $v->slug; ?>" <?php echo $selected ?>>
                            <?php echo esc_html( $v->name ) . ( ( $instance['show_count'] && $v->count ) ? ' (' . (int) $v->count . ')' : null ); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
        <?php } ?>
    </div>
    <div class="rpr-filterable-grid" id="rpr-filterable-grid">
        <?php if ( $recipes && $recipes->have_posts() ) { ?>
            <?php foreach ( $recipes->posts as $recipe ) { ?>
                <div class="rpr-filterable-item <?php echo $this->rpr_recipe_html_classes( $recipe, 'string', ['rpr_ingredient'] ); ?>">
                    <a href="<?php echo esc_url( get_the_permalink( $recipe->ID ) ); ?>">
                        <div class="rpr-filterable-image">
                            <?php echo get_the_post_thumbnail( $recipe->ID, 'thumbnail' ); ?>
                        </div>
                        <div class="rpr-filterable-title">
                            <p><?php echo esc_attr( $recipe->post_title ); ?></p>
                        </div>
                    </a>
                </div>
            <?php } ?>
        <?php } ?>
    </div>
    <div class="rpr-filterable-load">
        <button data-action="click->rpr-filterable#loadMoreItems">
            <?php echo esc_attr__( 'Load more', 'recipepress-reloaded' ); ?>
        </button>
        <div class="rpr-ellipsis"><div></div><div></div><div></div><div></div></div>
    </div>
</div>

<style type="text/css">
    .rpr-filterable-grid {display:grid; grid-template-columns:repeat(5, 1fr); grid-gap:1rem 1rem;}
    .rpr-filterable-item {position:relative;}
    .rpr-filterable-image img {width:100%;}
    .rpr-filterable-selectors {display:flex; flex-wrap:wrap; justify-content:space-evenly; margin:0 0 1rem 0;}
    .rpr-filterable-title {position:absolute; bottom:0; text-align:center; width:100%; background: linear-gradient(0deg, rgba(0,0,0,0.8) 0%, rgba(255,255,255,0) 100%);}
    .rpr-filterable-title p {text-align:center; line-height:1.4; font-size:0.8rem; margin:0; color:#fff}
    .rpr-filter-dropdown {flex:1 0 auto; margin:0 10px 10px 0;}
    .rpr-filter-dropdown:last-child {margin-right:0;}
    .rpr-filter-dropdown label {font-size:0.8rem;}
    .rpr-hidden {display:none}
    .rpr-filterable-load {display:flex; justify-content:center; margin:4rem 0 0 0;}

    .rpr-ellipsis{display:none;position:relative;width:80px;height:40px}
    .rpr-ellipsis div{position:absolute;top:16px;width:13px;height:13px;border-radius:50%;background:#000;animation-timing-function:cubic-bezier(0,1,1,0)}
    .rpr-ellipsis div:nth-child(1){left:8px;animation:rpr-ellipsis1 .6s infinite}
    .rpr-ellipsis div:nth-child(2){left:8px;animation:rpr-ellipsis2 .6s infinite}
    .rpr-ellipsis div:nth-child(3){left:32px;animation:rpr-ellipsis2 .6s infinite}
    .rpr-ellipsis div:nth-child(4){left:56px;animation:rpr-ellipsis3 .6s infinite}
    @keyframes rpr-ellipsis1{0%{transform:scale(0)}100%{transform:scale(1)}}
    @keyframes rpr-ellipsis3{0%{transform:scale(1)}100%{transform:scale(0)}}
    @keyframes rpr-ellipsis2{0%{transform:translate(0,0)}100%{transform:translate(24px,0)}}
</style>
