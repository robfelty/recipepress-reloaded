<?php
/**
 * The equipment metabox view of the plugin.
 *
 * @since 2.0.0
 *
 * @var \WP_Post                                   $recipe
 * @var \Recipepress\Inc\Admin\Metaboxes\Equipment $this
 *
 * @package recipepress-reloaded
 */
$this->create_nonce();
?>

<script type="text/javascript">
  window.rpr = window.rpr || {}
  rpr.rprEquipmentList = <?php echo wp_json_encode( $this->equipment_list() ); ?>;
</script>

<table data-controller="rpr-equipment"
       data-action="rpr-link:data@window->rpr-equipment#getLinkData"
       class="rpr-metabox-table equipment" id="recipe-equipment">
    <thead>
        <tr>
            <th class="rpr-equip-sort"><div class="dashicons dashicons-sort"></div></th>
            <th class="rpr-equip-name"><?php _e( 'Name', 'recipepress-reloaded' ); ?></th>
            <th class="rpr-equip-note"><?php _e( 'Note', 'recipepress-reloaded' ); ?></th>
            <th class="rpr-equip-link"><div class=""></div></th>
            <th class="rpr-equip-del"><div class=""></div></th>
        </tr>
    </thead>

    <tbody>

    <!-- Existing equipment rows -->
    <?php
    $equipments = get_post_meta( $recipe->ID, 'rpr_recipe_equipment', true ) ?: null;
    $i          = 1;
    ?>

    <?php
    if ( $equipments ) {
        foreach ( $equipments as $equipment ) {

            $equip_meta  = ! empty( $equipment['equipment_id'] ) ? get_term_meta( $equipment['equipment_id'], 'equipment_custom_meta', true ) : null;
            $global_link = ! empty( $equip_meta['link'] ) ? $equip_meta['link'] : '';
	        $data_key    = isset( $equipment['key'] ) ? 'data-key="' . $equipment['key'] . '"' : '';
            $has_link    = '';

            // Check if we have a equipments group or a equipment.
            if ( isset( $equipment['grouptitle'] ) ) {
                // If we have a equipment group title line add a group heading line.
                if ( '' !== $equipment['grouptitle'] ) {
                    ?>
                    <tr class="equipment-group">
                        <td class="rpr-equip-sort">
                            <div class="sort-handle dashicons dashicons-sort"></div>
                            <input type="hidden" name="rpr_recipe_equipment[<?php echo $i; ?>][sort]"
                                   class="equipment_sort" id="equipment_sort_<?php echo $i; ?>"
                                   value="<?php echo $i; ?>" />
                        </td>
                        <td colspan="5" class="rpr-equip-group">
                            <label for="rpr_recipe_equipment[<?php echo $i; ?>][grouptitle]"
                                   class="equipment-group-label screen-reader-text">
                                <?php _e( 'Equipment Group Title', 'recipepress-reloaded' ); ?>:
                            </label>
                            <input type="text" name="rpr_recipe_equipment[<?php echo $i; ?>][grouptitle]"
                                   class="equipment_grouptitle" id="equipment_grouptitle_<?php echo $i; ?>"
                                   value="<?php echo $equipment['grouptitle']; ?>"/>
                        </td>
                        <td class="rpr-equip-add">
                            <button class="rpr-equip-add-row button dashicons dashicons-plus"
                                    title="<?php _e( 'Add group title', 'recipepress-reloaded' ); ?>"></button>
                        </td>
                        <td class="rpr-equip-del">
                            <button class="rpr-equip-remove-row button dashicons dashicons-no"
                                    title="<?php _e( 'Remove group title', 'recipepress-reloaded' ); ?>"></button>
                        </td>
                    </tr>
                    <?php
                }
            } else {
                // We have a single equipment line, get the term name from the term_id just in case it has changed.
                $term = get_term( $equipment['equipment_id'], 'rpr_equipment' );
                if ( null !== $term && ! is_wp_error( $term ) ) {
                    $equipment['name'] = $term->name;
                }
                if ( '' !== $equipment['link'] || '' !== $global_link ) {
                    $has_link = 'has-link';
                }
                // Add single equipment line.
                ?>
                <tr class="rpr-equip-row row-<?php echo $i; ?>" <?php echo $data_key; ?>>
                    <td class="rpr-equip-sort">
                        <div class="sort-handle dashicons dashicons-sort"></div>
                        <input type="hidden" name="rpr_recipe_equipment[<?php echo $i; ?>][sort]"
                               class="equipment_sort" id="equipment_sort_<?php echo $i; ?>"
                               value="<?php echo $i; ?>"/>
                        <input type="hidden" name="rpr_recipe_equipment[<?php echo $i; ?>][key]"
                               class="equipment_key" id="equipment_key_<?php echo $i; ?>"
                               value="<?php echo ! empty( $equipment['key'] ) ? $equipment['key'] : ''; ?>" />
                    </td>
                    <td class="rpr-equip-name">
                        <input type="text" class="rpr-equip-name-input"
                               data-equipment-id="<?php echo $equipment['equipment_id']; ?>"
                               name="rpr_recipe_equipment[<?php echo $i; ?>][name]"
                               id="equipment_<?php echo $i; ?>" value="<?php echo $equipment['name']; ?>"/>
                    </td>
                    <td class="rpr-equip-note">
                        <input data-action="keydown->rpr-equipment#tabToDuplicate"
                               type="text" name="rpr_recipe_equipment[<?php echo $i; ?>][notes]"
                               class="equipment_notes" id="equipment_notes_<?php echo $i; ?>"
                               value="<?php echo $equipment['notes']; ?>"/>
                    </td>
                    <td class="rpr-equip-link">
                        <input name="rpr_recipe_equipment[<?php echo $i; ?>][link]"
                               class="rpr_recipe_equipment_link" type="hidden"
                               id="equipment_link_<?php echo $i; ?>" value="<?php echo ( '' !== $equipment['link'] ) ? $equipment['link'] : $global_link; ?>"/>
                        <input name="rpr_recipe_equipment[<?php echo $i; ?>][target]"
                               class="rpr_recipe_equipment_target" type="hidden"
                               id="equipment_target_<?php echo $i; ?>" value="<?php echo isset( $equipment['target'] ) ? $equipment['target'] : null; ?>"/>
                        <button data-action="click->rpr-equipment#addLink"
                                class="rpr-equip-add-link button dashicons dashicons-admin-links <?php echo $has_link; ?>"
                                title="<?php echo $has_link ? ( '' !== $equipment['link'] ? $equipment['link'] : $global_link ) : __( 'Add custom link', 'recipepress-reloaded' ); ?>">
                        </button>
                        <button data-action="click->rpr-equipment#removeLink"
                                class="rpr-equip-del-link button dashicons dashicons-editor-unlink <?php echo ( '' === $has_link ) ? 'rpr-hidden' : ''; ?>"
                                title="<?php _e( 'Remove custom link', 'recipepress-reloaded' ) ?>">
                        </button>
                    </td>
                    <td class="rpr-equip-add">
                        <button data-action="click->rpr-equipment#duplicateRow"
                                class="rpr-equip-add-row button dashicons dashicons-plus"
                                title="<?php _e( 'Add equipment', 'recipepress-reloaded' ) ?>"></button>
                    </td>
                    <td class="rpr-equip-del">
                        <button data-action="click->rpr-equipment#deleteRow"
                                class="rpr-equip-remove-row button dashicons dashicons-no"
                                title="<?php _e( 'Remove equipment', 'recipepress-reloaded' ) ?>"></button>
                    </td>
                </tr>
                <?php
            }
            $i ++;
        }
    } else { ?>
        <?php for ($x = 1; $x <= 2; $x++) { ?>
            <tr class="rpr-equip-row row-<?php echo $x; ?>">
                <td class="rpr-equip-sort">
                    <div class="sort-handle dashicons dashicons-sort"></div>
                    <input type="hidden" name="rpr_recipe_equipment[<?php echo $x; ?>][sort]" class="equipment_sort"
                           id="equipment_sort_<?php echo $x; ?>"/>
                </td>
                <td class="rpr-equip-name">
                    <input type="text" class="rpr-equip-name-input" name="rpr_recipe_equipment[<?php echo $x; ?>][name]"
                           id="equipment_<?php echo $x; ?>"
                           placeholder="<?php _e( 'Food processor', 'recipepress-reloaded' ); ?>" />
                </td>
                <td class="rpr-equip-note">
                    <input data-action="keydown->rpr-equipment#tabToDuplicate"
                           type="text" name="rpr_recipe_equipment[<?php echo $x; ?>][notes]" class="equipment_notes"
                           id="equipment_notes_<?php echo $x; ?>"
                           placeholder="<?php _e( 'for processing food', 'recipepress-reloaded' ); ?>"/>
                </td>
                <td class="rpr-equip-link">
                    <input name="rpr_recipe_equipment[<?php echo $x; ?>][link]" class="rpr_recipe_equipment_link"
                           type="hidden" id="equipment_link_<?php echo $x; ?>" value=""/>
                    <input name="rpr_recipe_equipment[<?php echo $x; ?>][target]" class="rpr_recipe_equipment_target"
                           type="hidden" id="equipment_target_<?php echo $x; ?>" value=""/>
                    <button data-action="click->rpr-equipment#addLink"
                            class="rpr-equip-add-link button dashicons dashicons-admin-links"
                            title="<?php _e( 'Add custom link', 'recipepress-reloaded' ); ?>"></button>
                    <button data-action="click->rpr-equipment#removeLink"
                            class="rpr-equip-del-link button dashicons dashicons-editor-unlink rpr-hidden"
                            title="<?php _e( 'Remove custom link', 'recipepress-reloaded' ); ?>"></button>
                </td>
                <td class="rpr-equip-add">
                    <button data-action="click->rpr-equipment#duplicateRow"
                            class="rpr-equip-add-row button dashicons dashicons-plus"
                            title="<?php _e( 'Add equipment', 'recipepress-reloaded' ) ?>"></button>
                </td>
                <td class="rpr-equip-del">
                    <button data-action="click->rpr-equipment#deleteRow"
                            class="rpr-equip-remove-row button dashicons dashicons-no"
                            title="<?php _e( 'Remove equipment', 'recipepress-reloaded' ) ?>"></button>
                </td>
            </tr>
        <?php } ?>
    <?php } ?>

    <?php if ( is_array( $equipments ) ) : ?>
        <!-- the last row is always empty, in case you want to add some -->
        <tr class="rpr-equip-row row-<?php echo $i; ?>">
            <td class="rpr-equip-sort">
                <div class="sort-handle dashicons dashicons-sort"></div>
                <input type="hidden" name="rpr_recipe_equipment[<?php echo $i; ?>][sort]" class="equipment_sort"
                       id="equipment_sort_<?php echo $i; ?>"/>
            </td>
            <td class="rpr-equip-name">
                <input type="text" class="rpr-equip-name-input" name="rpr_recipe_equipment[<?php echo $i; ?>][name]"
                       id="equipment_<?php echo $i; ?>" placeholder="<?php _e( 'Food processor', 'recipepress-reloaded' ); ?>" />
            </td>
            <td class="rpr-equip-note">
                <input data-action="keydown->rpr-equipment#tabToDuplicate"
                       type="text" name="rpr_recipe_equipment[<?php echo $i; ?>][notes]" class="equipment_notes"
                       id="equipment_notes_<?php echo $i; ?>"
                       placeholder="<?php _e( 'for processing food', 'recipepress-reloaded' ); ?>"/>
            </td>
            <td class="rpr-equip-link">
                <input name="rpr_recipe_equipment[<?php echo $i; ?>][link]" class="rpr_recipe_equipment_link"
                       type="hidden" id="equipment_link_<?php echo $i; ?>" value=""/>
                <input name="rpr_recipe_equipment[<?php echo $i; ?>][target]" class="rpr_recipe_equipment_target"
                       type="hidden" id="equipment_target_<?php echo $i; ?>" value=""/>
                <button data-action="click->rpr-equipment#addLink"
                        class="rpr-equip-add-link button dashicons dashicons-admin-links"
                        title="<?php _e( 'Add custom link', 'recipepress-reloaded' ); ?>"></button>
                <button data-action="click->rpr-equipment#removeLink"
                        class="rpr-equip-del-link button dashicons dashicons-editor-unlink rpr-hidden"
                        title="<?php _e( 'Remove custom link', 'recipepress-reloaded' ); ?>"></button>
            </td>
            <td class="rpr-equip-add">
                <button data-action="click->rpr-equipment#duplicateRow"
                        class="rpr-equip-add-row button dashicons dashicons-plus"
                        title="<?php _e( 'Add equipment', 'recipepress-reloaded' ) ?>"></button>
            </td>
            <td class="rpr-equip-del">
                <button data-action="click->rpr-equipment#deleteRow"
                        class="rpr-equip-remove-row button dashicons dashicons-no"
                        title="<?php _e( 'Remove equipment', 'recipepress-reloaded' ) ?>"></button>
            </td>
        </tr>
    <?php endif; ?>

    </tbody>
    <tfoot>
    <tr>
        <td colspan="7" style="padding: 3em 0 0 0;">
            <button data-action="click->rpr-equipment#duplicateRow"
                    id="rpr-equip-add-row-equip" class="rpr-equip-add-row-equip button">
                <span class="dashicons dashicons-plus"></span>
                <?php _e( 'Add equipment', 'recipepress-reloaded' ); ?>
            </button>
        </td>
    </tr>
    </tfoot>
</table>

<style>
    #recipe-equipment {
        width: 100%;
        border-spacing: 2px;
        table-layout: auto;
        vertical-align: middle;
    }

    td.rpr-equip-sort {
        width: 32px;
        cursor: grab;
    }

    td.rpr-equip-name {
        width: 30%;
    }

    td.rpr-equip-note {
        width: 55%;
    }

    td.rpr-equip-link {
        width: 8%;
        min-width: 65px;
    }

    td.rpr-equip-add,
    td.rpr-equip-del {
        width: 32px;
    }

    td.rpr-equip-name input,
    td.rpr-equip-note input {
        width: 100%;
    }

    .rpr-equip-add-link,
    .rpr-equip-remove-row,
    .rpr-equip-del-link,
    .rpr-equip-add-row-equip,
    .rpr-equip-add-row {
        width: 30px;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
    }
    .rpr-equip-add-row-equip {
        width: 130px;
    }
</style>

