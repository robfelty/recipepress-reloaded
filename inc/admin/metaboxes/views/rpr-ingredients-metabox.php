<?php
/**
 * The ingredient metabox view of the plugin.
 *
 * @since 1.0.0
 *
 * @var \Post $recipe
 * @var \Recipepress\Inc\Admin\Metaboxes\Ingredients $this
 *
 * @package    recipepress-reloaded
 */

use Recipepress\Inc\Core\Options;

$ingredients = get_post_meta( $recipe->ID, 'rpr_recipe_ingredients', true ) ?: array();

$this->create_nonce();
?>

<script type="text/javascript">
    window.rpr = window.rpr || {}
	rpr.rprIngredientList = <?php echo wp_json_encode( $this->ingredients_list() ); ?>;
	rpr.rprIngredientsUnitList = <?php echo wp_json_encode( explode( ',', Options::get_option( 'rpr_ingredient_unit_list', '' ) ) ); ?>;
</script>

<table
    data-controller="rpr-ingredients"
    data-action="rpr-link:data@window->rpr-ingredients#getLinkData"
    data-rpr-ingredients-ingredients-importer-value="<?php echo $this->key_exists_and_truthy( 'line', $ingredients ) ? 'true' : 'false'; ?>"
    data-rpr-ingredients-unit-list-value="<?php echo Options::get_option( 'rpr_use_ingredient_unit_list' ) ? 'true' : 'false'; ?>"
    class="rpr-metabox-table ingredients"
    id="recipe-ingredients"
>
	<thead>
        <tr>
            <th class="rpr-ing-sort"><div class="dashicons dashicons-sort"></div></th>
            <th class="rpr-ing-amount"><?php _e( 'Amount', 'recipepress-reloaded' ); ?></th>
            <th class="rpr-ing-unit"><?php _e( 'Unit', 'recipepress-reloaded' ); ?></th>
            <th class="rpr-ing-ingredient"><?php _e( 'Ingredient', 'recipepress-reloaded' ); ?></th>
            <th class="rpr-ing-note"><?php _e( 'Note', 'recipepress-reloaded' ); ?></th>
            <th class="rpr-ing-link"><div></div></th>
            <th class="rpr-ing-del"><div></div></th>
        </tr>
	</thead>

	<tbody>
	<!-- hidden group name row to copy heading lines from -->
	<tr class="ingredient-group-stub rpr-hidden">
		<td class="rpr-ing-sort">
			<div class="sort-handle dashicons dashicons-sort"></div>
			<input
                type="hidden"
                name="rpr_recipe_ingredients[0][sort]"
                class="ingredients_sort"
                id="ingredients_sort_0"
            />
		</td>
		<td colspan="5" class="rpr-ing-group">
			<label
                for="rpr_recipe_ingredients[0][grouptitle]"
                class="ingredient-group-label screen-reader-text"
            >
				<?php esc_attr_e( 'Ingredient Group Title', 'recipepress-reloaded' ); ?>
			</label>
			<input
                data-action="keydown->rpr-ingredients#tabToDuplicate"
                type="text"
                class="ingredient-group-input"
                name="rpr_recipe_ingredients[0][grouptitle]"
                id="ingredients_grouptitle_0"
                placeholder="<?php esc_attr_e( 'Ingredient group title', 'recipepress-reloaded' ); ?>"
            />
		</td>
		<td class="rpr-ing-add">
			<button
                data-action="click->rpr-ingredients#addHeading"
                class="rpr-ing-add-heading-inline button dashicons dashicons-plus"
                title="<?php esc_attr_e( 'Add group title', 'recipepress-reloaded' ); ?>"
            ></button>
		</td>
		<td class="rpr-ing-del">
			<button
                data-action="click->rpr-ingredients#removeHeading"
                class="rpr-ing-remove-heading-inline button dashicons dashicons-no"
                title="<?php esc_attr_e( 'Remove group title', 'recipepress-reloaded' ); ?>"
            ></button>
		</td>
	</tr>

<?php $i = 1; ?>

<?php
	if ( ! empty( $ingredients ) && ! $this->key_exists_and_truthy( 'line', $ingredients ) ) {

		foreach ( $ingredients as $ing ) {
			$ing_custom_meta = ! empty( $ing['ingredient_id'] ) ? get_term_meta( $ing['ingredient_id'], 'ingredient_custom_meta', true ) : null;
			$global_link     = ! empty( $ing_custom_meta['link'] ) ? $ing_custom_meta['link'] : '';
			$data_key        = isset( $ing['key'] ) ? 'data-key="' . esc_attr( $ing['key'] ) . '"' : '';
			$has_link        = '';

			// Check if we have a ingredients group or a ingredient.
			if ( ! empty( $ing['grouptitle'] ) ) { ?>
                <tr class="ingredient-group row-<?php echo $i; ?>" <?php echo $data_key; ?>>
                    <td class="rpr-ing-sort">
                        <div class="sort-handle dashicons dashicons-sort"></div>
                        <input
                            type="hidden"
                            name="rpr_recipe_ingredients[<?php echo $i; ?>][sort]"
                            class="ingredients_sort" id="ingredients_sort_<?php echo $i; ?>"
                            value="<?php echo $i; ?>"
                        />
                    </td>
                    <td colspan="5" class="rpr-ing-group">
                        <label
                            for="rpr_recipe_ingredients[<?php echo $i; ?>][grouptitle]"
                            class="ingredient-group-label screen-reader-text"
                        >
                            <?php esc_attr_e( 'Ingredient Group Title', 'recipepress-reloaded' ); ?>:
                        </label>
                        <input
                            data-action="keydown->rpr-ingredients#tabToDuplicate"
                            type="text" name="rpr_recipe_ingredients[<?php echo $i; ?>][grouptitle]"
                            class="ingredients_grouptitle ingredient-group-input"
                            id="ingredients_grouptitle_<?php echo $i; ?>"
                            value="<?php echo esc_attr( $ing['grouptitle'] ); ?>"
                        />
                        <input
                            type="hidden"
                            name="rpr_recipe_ingredients[<?php echo $i; ?>][key]"
                            class="ingredient-group-input-key"
                            id="ingredients_grouptitle_key_<?php echo $i; ?>"
                            value="<?php echo isset( $ing['key'] ) ? esc_attr( $ing['key'] ) : ''; ?>"
                        />
                    </td>
                    <td class="rpr-ing-add">
                        <button
                            data-action="click->rpr-ingredients#addHeading"
                            class="rpr-ing-add-heading-inline button dashicons dashicons-plus"
                            title="<?php esc_attr_e( 'Add group title', 'recipepress-reloaded' ); ?>"
                        ></button>
                    </td>
                    <td class="rpr-ing-del">
                        <button
                            data-action="click->rpr-ingredients#removeHeading"
                            class="rpr-ing-remove-heading-inline button dashicons dashicons-no"
                            title="<?php esc_attr_e( 'Remove group title', 'recipepress-reloaded' ); ?>"
                        ></button>
                    </td>
                </tr>
			<?php } else {
				// We have a single ingredient line, get the term name from the term_id just in case it has changed.
				$term = get_term( $ing['ingredient_id'], 'rpr_ingredient' );
				if ( null !== $term && ! is_wp_error( $term ) ) {
					$ing['ingredient'] = $term->name;
				}

				if ( '' !== $ing['link'] || '' !== $global_link ) {
					$has_link = 'has-link';
				}
				// Add single ingredient line.
				?>
				<tr class="rpr-ing-row row-<?php echo $i; ?>" <?php echo $data_key; ?>>
                    <td class="rpr-ing-sort">
                        <div class="sort-handle dashicons dashicons-sort"></div>
                        <input
                            type="hidden"
                            name="rpr_recipe_ingredients[<?php echo $i; ?>][sort]"
                            class="ingredients_sort"
                            id="ingredients_sort_<?php echo $i; ?>"
                            value="<?php echo $i; ?>"
                        />
                        <input
                            type="hidden"
                            name="rpr_recipe_ingredients[<?php echo $i; ?>][key]"
                            class="ingredients_key"
                            id="ingredients_sort_key_<?php echo $i; ?>"
                            value="<?php echo isset( $ing['key'] ) ? esc_attr( $ing['key'] ) : ''; ?>"
                        />
                    </td>
                    <td class="rpr-ing-amount">
                        <input
                            type="text"
                            name="rpr_recipe_ingredients[<?php echo $i; ?>][amount]"
                            class="ingredients_amount"
                            id="ingredients_amount_<?php echo $i; ?>"
                            value="<?php echo esc_attr( $ing['amount'] ); ?>"
                        />
                    </td>
                    <td class="rpr-ing-unit">
                        <input
                            type="text"
                            name="rpr_recipe_ingredients[<?php echo $i; ?>][unit]"
                            class="ingredients_unit"
                            id="ingredient_unit_<?php echo $i; ?>"
                            value="<?php echo esc_attr( $ing['unit'] ); ?>"
                            data-action="focusin->rpr-ingredients#fetchUnitsList focusout->rpr-ingredients#destroyUnitsList"
                        />
                    </td>
                    <td class="rpr-ing-name">
                        <input
                            data-action="focusin->rpr-ingredients#fetchIngredients focusout->rpr-ingredients#destroyFetchIngredients"
                            type="text"
                            class="rpr-ing-name-input ingredients_name"
                            data-ingredient-id="<?php echo esc_attr( $ing['ingredient_id'] ); ?>"
                            name="rpr_recipe_ingredients[<?php echo $i; ?>][ingredient]"
                            id="ingredients_<?php echo $i; ?>"
                            value="<?php echo esc_attr( $ing['ingredient'] ); ?>"
                        />
                    </td>
                    <td class="rpr-ing-note">
                        <input
                            data-action="keydown->rpr-ingredients#tabToDuplicate"
                            type="text"
                            name="rpr_recipe_ingredients[<?php echo $i; ?>][notes]"
                            class="ingredients_notes"
                            id="ingredient_notes_<?php echo $i; ?>"
                            value="<?php echo esc_attr( $ing['notes'] ); ?>"
                        />
                    </td>
                    <td class="rpr-ing-link">
                    <input name="rpr_recipe_ingredients[<?php echo $i; ?>][link]"
                           class="rpr_recipe_ingredients_link" type="hidden"
                           id="ingredient_link_<?php echo $i; ?>" value="<?php echo esc_attr( ( '' !== $ing['link'] ) ? $ing['link'] : $global_link ); ?>"/>
                    <input
                        name="rpr_recipe_ingredients[<?php echo $i; ?>][target]"
                        class="rpr_recipe_ingredients_target"
                        type="hidden"
                        id="ingredient_target_<?php echo $i; ?>"
                        value="<?php echo isset( $ing['target'] ) ? esc_attr( $ing['target'] ) : ''; ?>"
                    />
                    <button
                        data-action="click->rpr-ingredients#addLink"
                        class="rpr-ing-add-link button dashicons dashicons-admin-links <?php echo $has_link; ?>"
                        title="<?php echo $has_link ? esc_attr( '' !== $ing['link'] ? $ing['link'] : $global_link ) : esc_attr__( 'Add custom link', 'recipepress-reloaded' ); ?>"
                    ></button>
                    <button
                        data-action="click->rpr-ingredients#removeLink"
                        class="rpr-ing-del-link button dashicons dashicons-editor-unlink <?php echo ( '' === $has_link ) ? 'rpr-hidden' : ''; ?>"
                        title="<?php _e( 'Remove custom link', 'recipepress-reloaded' ) ?>"
                    ></button>
                </td>
                <td class="rpr-ing-add">
                    <button
                        data-action="click->rpr-ingredients#addIngredient"
                        class="rpr-ing-add-row-inline button dashicons dashicons-plus"
                        title="<?php esc_attr_e( 'Add ingredient', 'recipepress-reloaded' ) ?>"
                    ></button>
                </td>
                <td class="rpr-ing-del">
                    <button
                        data-action="click->rpr-ingredients#removeIngredient"
                        class="rpr-ing-remove-row-inline button dashicons dashicons-no"
                        title="<?php esc_attr_e( 'Remove ingredient', 'recipepress-reloaded' ) ?>"
                    ></button>
                </td>
			</tr>
			<?php }

			$i ++;

		}
	} elseif( $this->key_exists_and_truthy( 'line', $ingredients ) ) { ?>
        <tr class="rpr-ing-bulk-import">
            <td colspan="7">
                <textarea
                    name="rpr_recipe_ingredients[bulk_import]"
                    id="rpr-ingredients-bulk-import"
                    class="rpr-ingredients-bulk-import"
                    data-rpr-ingredients-target="bulkIngredientsImportTextarea"
                    data-action="paste->rpr-ingredients#stripUnwantedCharacters keydown.leftBracket->rpr-ingredients#wrapIngredientSelection"
                    style="width:100%; height:auto"><?php $this->print_parsed_bulk_import( $ingredients ); ?></textarea>
            </td>
        </tr>
	<?php } else { ?>
        <!-- 3 empty rows for new recipes -->
		<?php for ($x = 1; $x <= 3; $x++) { ?>
			<tr class="rpr-ing-row row-<?php echo $x; ?>">
				<td class="rpr-ing-sort">
					<div class="sort-handle dashicons dashicons-sort"></div>
					<input
                        type="hidden" 
                        name="rpr_recipe_ingredients[<?php echo $x; ?>][sort]"
                        class="ingredients_sort"
                        id="ingredients_sort_<?php echo $x; ?>"
                        value="<?php echo $x; ?>"
                    />
				</td>
				<td class="rpr-ing-amount">
					<input
                        type="text"
                        name="rpr_recipe_ingredients[<?php echo $x; ?>][amount]"
                        class="ingredients_amount"
                        id="ingredients_amount_<?php echo $x; ?>"
                        placeholder="1"
                    />
				</td>
				<td class="rpr-ing-unit">
                    <input
                        type="text"
                        name="rpr_recipe_ingredients[<?php echo $x; ?>][unit]"
                        class="ingredients_unit"
                        id="ingredient_unit_<?php echo $x; ?>" value=""
                        placeholder="<?php _e( 'teaspoon', 'recipepress-reloaded' ); ?>"
                        data-action="focusin->rpr-ingredients#fetchUnitsList focusout->rpr-ingredients#destroyUnitsList"
                    />
				</td>
				<td class="rpr-ing-name">
					<input
                        data-action="focusin->rpr-ingredients#fetchIngredients focusout->rpr-ingredients#destroyFetchIngredients"
                        type="text"
                        class="rpr-ing-name-input ingredients_name"
                        name="rpr_recipe_ingredients[<?php echo $x; ?>][ingredient]"
                        id="ingredients_<?php echo $x; ?>"
                        placeholder="<?php _e( 'olive oil', 'recipepress-reloaded' ); ?>"
                    />
				</td>
				<td class="rpr-ing-note">
					<input
                        data-action="keydown->rpr-ingredients#tabToDuplicate"
                        type="text"
                        name="rpr_recipe_ingredients[<?php echo $x; ?>][notes]"
                        class="ingredients_notes"
                        id="ingredient_notes_<?php echo $x; ?>"
                        placeholder="<?php _e( 'extra virgin', 'recipepress-reloaded' ); ?>"
                    />
				</td>
				<td class="rpr-ing-link">
					<input
                        name="rpr_recipe_ingredients[<?php echo $x; ?>][link]"
                        class="rpr_recipe_ingredients_link"
                        type="hidden"
                        id="ingredient_link_<?php echo $x; ?>"
                        value=""
                    />
					<input
                        name="rpr_recipe_ingredients[<?php echo $x; ?>][target]"
                        class="rpr_recipe_ingredients_target"
                        type="hidden"
                        id="ingredient_target_<?php echo $x; ?>"
                        value=""
                    />
					<button
                        data-action="click->rpr-ingredients#addLink"
                        class="rpr-ing-add-link button dashicons dashicons-admin-links"
                        title="<?php _e( 'Add custom link', 'recipepress-reloaded' ); ?>"
                    ></button>
					<button
                        data-action="click->rpr-ingredients#removeLink"
                        class="rpr-ing-del-link button dashicons dashicons-editor-unlink rpr-hidden"
                        title="<?php _e( 'Remove custom link', 'recipepress-reloaded' ); ?>"
                    ></button>
				</td>
				<td class="rpr-ing-add">
					<button
                        data-action="click->rpr-ingredients#addIngredient"
                        class="rpr-ing-add-row-inline button dashicons dashicons-plus"
                        title="<?php _e( 'Add ingredient', 'recipepress-reloaded' ) ?>"
                    ></button>
				</td>
				<td class="rpr-ing-del">
					<button
                        data-action="click->rpr-ingredients#removeIngredient"
                        class="rpr-ing-remove-row-inline button dashicons dashicons-no"
                        title="<?php _e( 'Remove ingredient', 'recipepress-reloaded' ) ?>"
                    ></button>
				</td>
			</tr>
		<?php } ?>
        <tr class="rpr-ing-bulk-import" hidden="">
            <td colspan="7">
                <textarea
                    name="rpr_recipe_ingredients[bulk_import]"
                    data-rpr-ingredients-target="bulkIngredientsImportTextarea"
                    data-action="paste->rpr-ingredients#stripUnwantedCharacters keydown.leftBracket->rpr-ingredients#wrapIngredientSelection"
                    id="rpr-ingredients-bulk-import"
                    class="rpr-ingredients-bulk-import"
                    placeholder="Paste your ingredient list here, 1 item per line, e.g: &#10;&#10;1 cup baking flour&#10;1/2 cup cane sugar&#10;2 cups ripe bananas, mashed"
                    style="width:100%; height:180px;"></textarea>
            </td>
        </tr>
	<?php } ?>

	<?php if ( $ingredients && ! $this->key_exists_and_truthy( 'line', $ingredients ) ) : ?>
	<!-- the last row is always empty, in case you want to add some -->
	<tr class="rpr-ing-row row-<?php echo $i; ?>">
		<td class="rpr-ing-sort">
			<div class="sort-handle dashicons dashicons-sort"></div>
			<input
                type="hidden"
                name="rpr_recipe_ingredients[<?php echo $i; ?>][sort]"
                class="ingredients_sort"
                id="ingredients_sort_<?php echo $i; ?>"
                value="<?php echo $i; ?>"
            />
		</td>
		<td class="rpr-ing-amount">
			<input
                type="text"
                name="rpr_recipe_ingredients[<?php echo $i; ?>][amount]"
                class="ingredients_amount"
                id="ingredients_amount_<?php echo $i; ?>"
                placeholder="1"
            />
		</td>
		<td class="rpr-ing-unit">
            <input
                type="text"
                name="rpr_recipe_ingredients[<?php echo $i; ?>][unit]"
                class="ingredients_unit"
                id="ingredient_unit_<?php echo $i; ?>"
                value=""
                placeholder="<?php _e( 'teaspoon', 'recipepress-reloaded' ); ?>"
                data-action="focusin->rpr-ingredients#fetchUnitsList focusout->rpr-ingredients#destroyUnitsList"
            />
		</td>
		<td class="rpr-ing-name">
			<input
                data-action="focusin->rpr-ingredients#fetchIngredients focusout->rpr-ingredients#destroyFetchIngredients"
                type="text"
                class="rpr-ing-name-input ingredients_name"
                name="rpr_recipe_ingredients[<?php echo $i; ?>][ingredient]"
                id="ingredients_<?php echo $i; ?>"
                placeholder="<?php _e( 'olive oil', 'recipepress-reloaded' ); ?>"
            />
		</td>
		<td class="rpr-ing-note">
			<input
                data-action="keydown->rpr-ingredients#tabToDuplicate"
                type="text"
                name="rpr_recipe_ingredients[<?php echo $i; ?>][notes]"
                class="ingredients_notes"
                id="ingredient_notes_<?php echo $i; ?>"
                placeholder="<?php _e( 'extra virgin', 'recipepress-reloaded' ); ?>"
            />
		</td>
		<td class="rpr-ing-link">
			<input
                name="rpr_recipe_ingredients[<?php echo $i; ?>][link]"
                class="rpr_recipe_ingredients_link"
                type="hidden"
                id="ingredient_link_<?php echo $i; ?>"
                value=""
            />
			<input
                name="rpr_recipe_ingredients[<?php echo $i; ?>][target]"
                class="rpr_recipe_ingredients_target"
                type="hidden"
                id="ingredient_target_<?php echo $i; ?>"
                value=""
            />
			<button
                data-action="click->rpr-ingredients#addLink"
                class="rpr-ing-add-link button dashicons dashicons-admin-links"
                title="<?php _e( 'Add custom link', 'recipepress-reloaded' ); ?>"
            ></button>
			<button
                data-action="click->rpr-ingredients#removeLink"
                class="rpr-ing-del-link button dashicons dashicons-editor-unlink rpr-hidden"
                title="<?php _e( 'Remove custom link', 'recipepress-reloaded' ); ?>"
            ></button>
		</td>
		<td class="rpr-ing-add">
			<button
                data-action="click->rpr-ingredients#addIngredient"
                class="rpr-ing-add-row-inline button dashicons dashicons-plus"
                title="<?php _e( 'Add ingredient', 'recipepress-reloaded' ) ?>"
            ></button>
		</td>
		<td class="rpr-ing-del">
			<button
                data-action="click->rpr-ingredients#removeIngredient"
                class="rpr-ing-remove-row-inline button dashicons dashicons-no"
                title="<?php _e( 'Remove ingredient', 'recipepress-reloaded' ) ?>"
            ></button>
		</td>
	</tr>
	<?php endif; ?>

	</tbody>
	<tfoot>
	<tr>
		<td colspan="7" style="padding: 3em 0 0 0;">
			<?php if ( ! $this->key_exists_and_truthy( 'line', $ingredients ) ) : ?>
                <button
                    data-action="click->rpr-ingredients#addIngredient"
                    data-rpr-ingredients-target="addIngredientRowButton"
                    id="rpr-ing-add-row-ing"
                    class="rpr rpr-ing-add-row-bottom button"
                >
                    <span class="dashicons dashicons-plus"></span>
                    <?php _e( 'Add an ingredient', 'recipepress-reloaded' ); ?>
                </button>
                <button
                    data-action="click->rpr-ingredients#addHeading"
                    data-rpr-ingredients-target="addIngredientGroupButton"
                    id="rpr-ing-add-row-grp"
                    class="rpr rpr-ing-remove-heading-bottom button"
                >
                    <span class="dashicons dashicons-plus"></span>
                    <?php _e( 'Add a group', 'recipepress-reloaded' ); ?>
                </button>
            <?php endif; ?>
            <?php if ( empty( $ingredients ) && ! $this->key_exists_and_truthy( 'line', $ingredients ) ) : ?>
                <button
                    data-action="click->rpr-ingredients#switchToBulkImporter"
                    data-rpr-ingredients-target="bulkIngredientImportButton"
                    id="rpr-ing-switch-bulk-import"
                    class="rpr rpr-ing-bulk-import-bottom button"
                >
                    <span class="dashicons dashicons-download"></span>
		            <?php _e( 'Bulk ingredients', 'recipepress-reloaded' ); ?>
                </button>
            <?php endif; ?>
		</td>
	</tr>
	</tfoot>
</table>

<?php do_action( 'rpr/admin/metabox/ingredients', $recipe, $this ); ?>

