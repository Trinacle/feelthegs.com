<?php
/**
 * ACF field groups for Feel The G's.
 *
 * The headline field group is the per-product-category "Collection Filter Config" —
 * the WooCommerce equivalent of Fantasies Boutique's collection.metafields.custom.*
 * fields. It controls which filter groups appear on each category, the price-slider
 * bounds, headings, and the available terms per group.
 *
 * Guarded: the theme loads even if ACF is inactive (config helpers fall back to
 * defaults), so a missing plugin never whitescreens the shop.
 *
 * @package FeelTheGs
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ---------- Register per-category Collection Filter Config ---------- */
add_action( 'acf/init', 'ftgs_register_filter_config_fields' );
function ftgs_register_filter_config_fields() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

    acf_add_local_field_group( array(
        'key'      => 'group_ftgs_filter_config',
        'title'    => 'Collection Filter Config',
        'fields'   => array(

            // ---- Master toggle -------------------------------------------------
            array(
                'key'           => 'field_ftgs_filter_enable',
                'label'         => 'Enable custom filter sidebar',
                'name'          => 'ftgs_filter_enable',
                'type'          => 'true_false',
                'instructions'  => 'Show the Fantasies-Boutique-style filter sidebar on this category. When off, only sort + the native grid render.',
                'default_value' => 1,
                'ui'            => 1,
            ),

            // ---- Price slider -------------------------------------------------
            array(
                'key'           => 'field_ftgs_price_display',
                'label'         => 'Show price slider',
                'name'          => 'ftgs_price_display',
                'type'          => 'true_false',
                'default_value' => 1,
                'ui'            => 1,
            ),
            array(
                'key'           => 'field_ftgs_price_heading',
                'label'         => 'Price heading',
                'name'          => 'ftgs_price_heading',
                'type'          => 'text',
                'default_value' => 'Price',
                'placeholder'   => 'Price',
                'conditional_logic' => array(
                    array( array( 'field' => 'field_ftgs_price_display', 'operator' => '==', 'value' => 1 ) ),
                ),
            ),
            array(
                'key'           => 'field_ftgs_price_min',
                'label'         => 'Minimum price',
                'name'          => 'ftgs_price_min',
                'type'          => 'number',
                'instructions'  => 'Leave blank to auto-detect from the category\'s products.',
                'default_value' => 0,
                'conditional_logic' => array(
                    array( array( 'field' => 'field_ftgs_price_display', 'operator' => '==', 'value' => 1 ) ),
                ),
            ),
            array(
                'key'           => 'field_ftgs_price_max',
                'label'         => 'Maximum price',
                'name'          => 'ftgs_price_max',
                'type'          => 'number',
                'instructions'  => 'Leave 0 to auto-detect from the category\'s products.',
                'default_value' => 0,
                'conditional_logic' => array(
                    array( array( 'field' => 'field_ftgs_price_display', 'operator' => '==', 'value' => 1 ) ),
                ),
            ),
            array(
                'key'           => 'field_ftgs_price_step',
                'label'         => 'Price step',
                'name'          => 'ftgs_price_step',
                'type'          => 'number',
                'default_value' => 5,
                'step'          => 0.01,
                'conditional_logic' => array(
                    array( array( 'field' => 'field_ftgs_price_display', 'operator' => '==', 'value' => 1 ) ),
                ),
            ),

            // ---- Attribute filter groups (Color, Size, Material, Type, Brand, Features) ----
            // Each group: enable toggle + heading + optional term allowlist (slugs, comma-separated).
            array(
                'key'           => 'field_ftgs_attr_groups',
                'label'         => 'Attribute filter groups',
                'name'          => 'ftgs_attr_groups',
                'type'          => 'repeater',
                'instructions'  => 'One row per WC attribute you want to expose as a filter on this category. Use the attribute slug (e.g. color, size, material).',
                'layout'        => 'row',
                'button_label'  => 'Add attribute group',
                'sub_fields'    => array(
                    array(
                        'key'   => 'field_ftgs_attr_slug',
                        'label' => 'Attribute slug',
                        'name'  => 'slug',
                        'type'  => 'text',
                        'instructions' => 'e.g. color, size, material, type, brand, features. Must match an existing WC product attribute slug.',
                        'required' => 1,
                    ),
                    array(
                        'key'   => 'field_ftgs_attr_heading',
                        'label' => 'Heading',
                        'name'  => 'heading',
                        'type'  => 'text',
                        'instructions' => 'Optional. Defaults to the attribute name.',
                    ),
                    array(
                        'key'   => 'field_ftgs_attr_style',
                        'label' => 'Display style',
                        'name'  => 'style',
                        'type'  => 'select',
                        'choices' => array(
                            'checkbox' => 'Checkbox list',
                            'swatch'   => 'Color swatch (for color attributes)',
                        ),
                        'default_value' => 'checkbox',
                    ),
                    array(
                        'key'   => 'field_ftgs_attr_terms',
                        'label' => 'Term allowlist (optional)',
                        'name'  => 'terms',
                        'type'  => 'text',
                        'instructions' => 'Comma-separated term slugs to show. Leave blank to show all terms that have products in this category.',
                    ),
                ),
            ),

            // ---- Deals (sale-status) toggle ----------------------------------
            array(
                'key'           => 'field_ftgs_deals_display',
                'label'         => 'Show "On sale" filter',
                'name'          => 'ftgs_deals_display',
                'type'          => 'true_false',
                'instructions'  => 'Adds an "On sale" checkbox (WooCommerce sale-status — no manual tagging required).',
                'default_value' => 0,
                'ui'            => 1,
            ),

            // ---- Related category menus (navigational, like Fantasies' custom menus) ----
            array(
                'key'           => 'field_ftgs_related_cats',
                'label'         => 'Related category menu',
                'name'          => 'ftgs_related_cats',
                'type'          => 'taxonomy',
                'taxonomy'      => 'product_cat',
                'field_type'    => 'multi_select',
                'add_term'      => 0,
                'instructions'  => 'Optional: show anchor links to related categories (replaces Fantasies Boutique\'s custom menu blocks).',
                'return_format' => 'object',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param'    => 'taxonomy',
                    'operator' => '==',
                    'value'    => 'product_cat',
                ),
            ),
        ),
        'menu_order'            => 0,
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'instruction_placement' => 'field',
        'active'                => true,
    ) );
}
