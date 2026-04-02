<?php
/**
 * The instructions metabox view of the plugin.
 *
 * @link       http://tech.cbjck.de/wp/rpr
 * @since      0.8.0
 *
 * @var \WP_Post $recipe The recipe post object.
 * @var \Recipepress\Inc\Admin\Metaboxes\Instructions $this
 *
 * @package    recipepress-reloaded
 * @subpackage recipepress-reloaded/admin/views
 */

$instructions = get_post_meta( $recipe->ID, 'rpr_recipe_instructions', true ) ?: array();
$this->create_nonce();
?>

<table
    data-controller="rpr-instructions"
    data-rpr-instructions-instructions-importer-value="<?php echo $this->key_exists_and_truthy( 'line', $instructions ) ? 'true' : 'false'; ?>"
    class="rpr-metabox-table instructions"
    id="recipe-instructions"
>
	<thead>
        <tr>
            <th class="rpr-ins-sort"><div class="dashicons dashicons-sort"></div></th>
            <th class="rpr-ins-instruction"><?php _e( 'Description', 'recipepress-reloaded' ); ?></th>
            <th class="rpr-ins-image"><?php _e( 'Image', 'recipepress-reloaded' ); ?></th>
            <th class="rpr-ing-del"></th>
        </tr>
	</thead>
	<tbody>
	<!-- hidden row to copy heading lines from -->
	<tr class="instruction-group-stub rpr-hidden">
		<td class="rpr-ins-sort">
			<div class="sort-handle dashicons dashicons-sort"></div>
			<input
                type="hidden"
                name="rpr_recipe_instructions[0][sort]"
                class="instructions_sort"
                id="instructions_sort_0"
            />
		</td>
		<td colspan="2" class="rpr-ins-group">
			<label
                for="instructions_grouptitle_0"
                class="screen-reader-text"
            >
				<?php _e( 'Instruction Group', 'recipepress-reloaded' ); ?>
			</label>
			<input
                data-action="keydown->rpr-instructions#tabToDuplicate"
                type="text"
                class="instructions-group-label instructions_grouptitle rpr-instructions__input-heading"
                name="rpr_recipe_instructions[0][grouptitle]"
                id="instructions_grouptitle_0"
                placeholder="Instruction group title"
            />
		</td>
		<td class="rpr-ins-del">
			<button
                data-action="click->rpr-instructions#addHeading"
                class="rpr-ins-add-heading-inline button dashicons dashicons-plus"
                title="<?php esc_attr_e( 'Add row', 'recipepress-reloaded' ); ?>"
            ></button>
			<button
                data-action="click->rpr-instructions#removeHeading"
                class="rpr-ins-remove-heading-inline button dashicons dashicons-no"
                title="<?php esc_attr_e( 'Remove row', 'recipepress-reloaded' ); ?>"
            ></button>
		</td>
	</tr>

	<?php
	$i = 1;

	if ( ! empty( $instructions ) && ! $this->key_exists_and_truthy( 'line', $instructions ) ) {
		foreach ( $instructions as $ins ) {
			$has_image = '';
            $data_key  = isset( $ins['key'] ) ? 'data-key="' . $ins['key'] . '"' : '';
			// Check if we have an instruction group, or an instruction line.
			if ( ! empty( $ins['grouptitle'] ) ) { // we have an instruction group title line ?>
                <!-- Existing group title rows -->
                <tr class="instruction-group row-<?php echo $i; ?>" <?php echo $data_key; ?>>
                    <td class="rpr-ins-sort">
                        <div class="sort-handle dashicons dashicons-sort"></div>
                        <input
                            type="hidden"
                            name="rpr_recipe_instructions[<?php echo $i; ?>][sort]"
                            class="instructions_sort"
                            id="instructions_sort_<?php echo $i; ?>"
                            value="<?php echo $ins['sort']; ?>"
                        />
                        <input
                            type="hidden"
                            name="rpr_recipe_instructions[<?php echo $i; ?>][key]"
                            class="instructions_key"
                            id="instructions_key_<?php echo $i; ?>"
                            value="<?php echo isset( $ins['key'] ) ? $ins['key'] : ''; ?>"
                        />
                    </td>
                    <td colspan="2" class="rpr-ins-group">
                        <label
                            for="instructions_grouptitle_<?php echo $i; ?>"
                            class="screen-reader-text"
                        >
                            <?php _e( 'Instruction Group', 'recipepress-reloaded' ); ?>
                        </label>
                        <input
                            data-action="keydown->rpr-instructions#tabToDuplicate"
                            type="text" class="instructions-group-label instructions_grouptitle rpr-instructions__input-heading"
                            name="rpr_recipe_instructions[<?php echo $i; ?>][grouptitle]"
                            id="instructions_grouptitle_<?php echo $i; ?>"
                            value="<?php echo $ins['grouptitle']; ?>"
                        />
                    </td>
                    <td class="rpr-ins-del">
                        <button
                            data-action="click->rpr-instructions#addHeading"
                            class="rpr-ins-add-heading-inline button dashicons dashicons-plus"
                            title="<?php esc_attr_e( 'Add group', 'recipepress-reloaded' ); ?>"
                        ></button>
                        <button
                            data-action="click->rpr-instructions#removeHeading"
                            class="rpr-ins-remove-heading-inline button dashicons dashicons-no"
                            title="<?php esc_attr_e( 'Remove group', 'recipepress-reloaded' ); ?>"
                        ></button>
                    </td>
                </tr>
			    <?php
			} else {
				// We have an instruction line.
				$image = '';

				if ( ! empty( $ins['image'] ) ) {
                    $image = wp_get_attachment_image_src( (int) $ins['image'] );
					$image = $image ? $image[0] : '';
				}
				?>
                <!-- Existing instruction rows -->
				<tr class="rpr-ins-row row-<?php echo $i; ?>" <?php echo $data_key; ?>>
					<td class="rpr-ins-sort">
						<div class="sort-handle dashicons dashicons-sort"></div>
						<input
                            type="hidden"
                            name="rpr_recipe_instructions[<?php echo $i; ?>][sort]"
                            class="instructions_sort"
                            id="instructions_sort_<?php echo $i; ?>"
                            value="<?php echo $ins['sort']; ?>"
                        />
                        <input
                            type="hidden"
                            name="rpr_recipe_instructions[<?php echo $i; ?>][key]"
                            class="instructions_key"
                            id="instructions_key_<?php echo $i; ?>"
                            value="<?php echo isset( $ins['key'] ) ? $ins['key'] : ''; ?>"
                        />
					</td>
					<td class="rpr-ins-instruction">
						<label
                            class="screen-reader-text"
                            for="instruction_description_<?php echo $i; ?>"
                        >
							<?php printf( '%s %d', __( 'Step', 'recipepress-reloaded' ), $i ); ?>
						</label>
						<textarea
                            data-action="keydown->rpr-instructions#tabToDuplicate"
                            class="rpr-instructions__textarea"
                            name="rpr_recipe_instructions[<?php echo $i; ?>][description]"
                            rows="4"
                            id="instruction_description_<?php echo $i; ?>"
                        ><?php echo $ins['description']; ?></textarea>
					</td>
					<td class="rpr-ins-image">
						<div
                            class="rpr-ins-image-wrapper"
                            id="rpr_recipe_instructions_image_set_<?php echo $i; ?>"
                            data-recipe-id="<?php echo $recipe->ID; ?>"
						>
							<span
                                data-action="click->rpr-instructions#removeImage"
                                title="<?php esc_attr_e( 'Remove instruction image' ) ?>"
                                id="rpr_recipe_instructions_image_del_<?php echo $i; ?>"
                                style="<?php printf( 'display: %s', ( $image ? 'inline' : 'none' ) ); ?>"
                                class="rpr-ins-image-del dashicons dashicons-trash"
                            ></span>
							<span
                                data-action="click->rpr-instructions#addImage"
                                title="<?php esc_attr_e( 'Add instruction image', 'recipepress-reloaded' ) ?>"
                                id="rpr_recipe_instructions_image_set_<?php echo $i; ?>"
                                style="<?php printf( 'display: %s', ( $image ? 'none' : 'inline' ) ); ?>"
                                class="rpr-ins-image-set dashicons dashicons-format-image"
                                rel="<?php echo $recipe->ID; ?>"
                            ></span>
                            <img
                                class="rpr_recipe_instructions_thumbnail"
                                id="rpr_recipe_instructions_thumbnail_<?php echo $i; ?>"
                                src="<?php echo $image; ?>"
                                style="<?php printf( 'display: %s', ( $image ? 'block' : 'none' ) ); ?>"
                                alt="<?php printf( '%s %d', __( 'Process photograph for step', 'recipepress-reloaded' ), $i ); ?>"
                            />
							<input
                                name="rpr_recipe_instructions[<?php echo $i; ?>][image]"
                                id="instruction_image_<?php echo $i; ?>"
                                class="rpr_recipe_instructions_image"
                                type="hidden"
                                value="<?php echo $ins['image']; ?>"
                            />
						</div>
					</td>
					<td class="rpr-ins-del">
						<button
                            data-action="click->rpr-instructions#addInstruction"
                            class="rpr-ins-add-row-inline button dashicons dashicons-plus"
                            title="<?php esc_attr_e( 'Add instruction', 'recipepress-reloaded' ); ?>"
                        ></button>
						<button
                            data-action="click->rpr-instructions#removeInstruction"
                            class="rpr-ins-remove-row-inline button dashicons dashicons-no"
                            title="<?php esc_attr_e( 'Remove instruction', 'recipepress-reloaded' ); ?>"
                        ></button>
					</td>
				</tr>
				<?php
			}
			$i++;
		}
	} elseif ( $this->key_exists_and_truthy( 'line', $instructions ) ) { ?>
        <tr class="rpr-ins-bulk-importer-row">
            <td colspan="15">
                <label
                    class="screen-reader-text"
                    for="rpr-instruction-bulk-importer"
                >
					<?php _e( 'Bulk instructions importer', 'recipepress-reloaded' ); ?>
                </label>
                <textarea
                    name="rpr_recipe_instructions[bulk_import]"
                    data-rpr-instructions-target="bulkInstructionsImportTextarea"
                    data-action="paste->rpr-instructions#stripUnwantedCharacters"
                    id="rpr-instruction-bulk-importer"
                    placeholder="<?php esc_attr_e( 'Paste your instructions here, placing each instruction on a new line.', 'recipepress-reloaded' ); ?>"
                    rows="10"
                    style="width:100%;"><?php $this->print_parsed_bulk_import( $instructions ); ?></textarea>
            </td>
        </tr>
	<?php } else { ?>
        <tr class="rpr-ins-row row-0">
            <td class="rpr-ins-sort">
                <div class="sort-handle dashicons dashicons-sort"></div>
                <input
                    type="hidden"
                    name="rpr_recipe_instructions[<?php echo $i; ?>][sort]"
                    class="instructions_sort"
                    id="instructions_sort_<?php echo $i; ?>"
                />
            </td>
            <td class="rpr-ins-instruction">
                <label
                    class="screen-reader-text"
                    for="instruction_description_<?php echo $i; ?>"
                >
					<?php printf( '%s %d', __( 'Step', 'recipepress-reloaded' ), $i ); ?>
                </label>
                <textarea
                    data-action="keydown->rpr-instructions#tabToDuplicate"
                    class="rpr-instructions__textarea"
                    name="rpr_recipe_instructions[<?php echo $i; ?>][description]"
                    rows="4"
                    id="instruction_description_<?php echo $i; ?>"
                ></textarea>
            </td>
            <td class="rpr-ins-image">
                <div
                    class="rpr-ins-image-wrapper"
                    id="rpr_recipe_instructions_image_set_<?php echo $i; ?>"
                    data-recipe-id="<?php echo $recipe->ID; ?>"
                >
				<span
                    data-action="click->rpr-instructions#addImage"
                    class="rpr-ins-image-set dashicons dashicons-format-image"
                ></span>
                    <span
                        data-action="click->rpr-instructions#removeImage"
                        title="<?php esc_attr_e( 'Remove instruction image', 'recipepress-reloaded' ); ?>"
                        id="rpr_recipe_instructions_image_del_<?php echo $i; ?>"
                        style="display:none"
                        class="rpr-ins-image-del dashicons dashicons-trash"
                    ></span>
                    <img
                        class="rpr_recipe_instructions_thumbnail"
                        id="rpr_recipe_instructions_thumbnail_<?php echo $i; ?>"
                        src=""
                        style="display:none;"
                    />
                    <input
                        name="rpr_recipe_instructions[<?php echo $i; ?>][image]"
                        id="instruction_image_<?php echo $i; ?>"
                        class="rpr_recipe_instructions_image"
                        type="hidden"
                        value=""
                    />
                </div>
            </td>
            <td class="rpr-ins-del">
                <button
                    data-action="click->rpr-instructions#addInstruction"
                    class="rpr-ins-add-row-inline button dashicons dashicons-plus"
                    title="<?php esc_attr_e( 'Add instruction', 'recipepress-reloaded' ); ?>"
                ></button>
                <button
                    data-action="click->rpr-instructions#removeInstruction"
                    class="rpr-ins-remove-row-inline button dashicons dashicons-no"
                    title="<?php esc_attr_e( 'Remove instruction', 'recipepress-reloaded' ); ?>"
                ></button>
            </td>
        </tr>

        <tr class="rpr-ins-bulk-import" hidden="">
            <td colspan="15">
                <label
                    class="screen-reader-text"
                    for="rpr-instruction-bulk-importer"
                >
					<?php _e( 'Bulk instructions importer', 'recipepress-reloaded' ); ?>
                </label>
                <textarea
                    name="rpr_recipe_instructions[bulk_import]"
                    data-rpr-instructions-target="bulkInstructionsImportTextarea"
                    data-action="paste->rpr-instructions#stripUnwantedCharacters"
                    id="rpr-instruction-bulk-importer"
                    placeholder="<?php esc_attr_e( "Paste your instructions here, placing each instruction on a new line, e.g: &#10;&#10;Preheat oven to 360 °F&#10;Mix baking flour and cane sugar in bowl&#10;Mix in mashed ripe bananas to bowl", 'recipepress-reloaded' ); ?>"
                    rows="10"
                    style="width:100%;"
                ></textarea>
            </td>
        </tr>
	<?php } ?>

	<?php if ( $instructions && ! $this->key_exists_and_truthy( 'line', $instructions ) ) : ?>
	<!-- the last row is always empty, in case you want to add some -->
	<tr class="rpr-ins-row row-0">
		<td class="rpr-ins-sort">
			<div class="sort-handle dashicons dashicons-sort"></div>
			<input
                type="hidden"
                name="rpr_recipe_instructions[<?php echo $i; ?>][sort]"
                class="instructions_sort"
                id="instructions_sort_<?php echo $i; ?>"
            />
		</td>
		<td class="rpr-ins-instruction">
			<label
                class="screen-reader-text"
                for="instruction_description_<?php echo $i; ?>"
            >
				<?php printf( '%s %d', __( 'Step', 'recipepress-reloaded' ), $i ); ?>
			</label>
			<textarea
                data-action="keydown->rpr-instructions#tabToDuplicate"
                class="rpr-instructions__textarea"
                name="rpr_recipe_instructions[<?php echo $i; ?>][description]"
                rows="4"
                id="instruction_description_<?php echo $i; ?>"
            ></textarea>
		</td>
		<td class="rpr-ins-image">
			<div
                class="rpr-ins-image-wrapper"
                id="rpr_recipe_instructions_image_set_<?php echo $i; ?>"
                data-recipe-id="<?php echo $recipe->ID; ?>"
			>
				<span
                    data-action="click->rpr-instructions#addImage"
                    class="rpr-ins-image-set dashicons dashicons-format-image"
                ></span>
				<span
                    data-action="click->rpr-instructions#removeImage"
                    title="<?php esc_attr_e( 'Remove instruction image', 'recipepress-reloaded' ); ?>"
                    id="rpr_recipe_instructions_image_del_<?php echo $i; ?>"
                    style="display:none"
                    class="rpr-ins-image-del dashicons dashicons-trash"
                ></span>
				<img
                    class="rpr_recipe_instructions_thumbnail"
                    id="rpr_recipe_instructions_thumbnail_<?php echo $i; ?>"
                    src="" style="display:none;"
                />
				<input
                    name="rpr_recipe_instructions[<?php echo $i; ?>][image]"
                    id="instruction_image_<?php echo $i; ?>"
                    class="rpr_recipe_instructions_image"
                    type="hidden"
                    value=""
                />
			</div>
		</td>
		<td class="rpr-ins-del">
			<button
                data-action="click->rpr-instructions#addInstruction"
                class="rpr-ins-add-row-inline button dashicons dashicons-plus"
                title="<?php esc_attr_e( 'Add instruction', 'recipepress-reloaded' ); ?>"
            ></button>
			<button
                data-action="click->rpr-instructions#removeInstruction"
                class="rpr-ins-remove-row-inline button dashicons dashicons-no"
                title="<?php esc_attr_e( 'Remove instruction', 'recipepress-reloaded' ); ?>"
            ></button>
		</td>
	</tr>
    <?php endif; ?>

	</tbody>

	<tfoot>
	<tr>
		<td colspan="7" style="padding: 3em 0 0 0">
			<?php if ( ! $this->key_exists_and_truthy( 'line', $instructions ) ) : ?>
                <button
                    data-action="click->rpr-instructions#addInstruction"
                    data-rpr-instructions-target="addInstructionRowButton"
                    id="rpr-ins-add-row-ins"
                    class="rpr-ins-add-row-bottom button"
                >
                    <span class="dashicons dashicons-plus"></span>
                    <?php _e( 'Add an instruction', 'recipepress-reloaded' ); ?>
                </button>
                <button
                    data-action="click->rpr-instructions#addHeading"
                    data-rpr-instructions-target="addInstructionGroupButton"
                    id="rpr-ins-add-row-grp"
                    class="rpr-ins-add-heading-bottom button"
                >
                    <span class="dashicons dashicons-plus"></span>
                    <?php _e( 'Add a group', 'recipepress-reloaded' ); ?>
                </button>
            <?php endif; ?>
			<?php if ( empty( $instructions ) && ! $this->key_exists_and_truthy( 'line', $instructions ) ) : ?>
                <button
                    data-action="click->rpr-instructions#switchToBulkImporter"
                    data-rpr-instructions-target="bulkInstructionImportButton"
                    id="rpr-ins-bulk-import"
                    class="rpr-ins-bulk-import button"
                >
                    <span class="dashicons dashicons-download"></span>
                    <?php _e( 'Bulk instructions', 'recipepress-reloaded' ); ?>
                </button>
            <?php endif; ?>
		</td>
	</tr>
	</tfoot>
</table>

<?php do_action( 'rpr/admin/metabox/instructions', $recipe, $this ); ?>
