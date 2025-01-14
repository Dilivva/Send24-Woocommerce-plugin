<?php

class Send24_Modal {
    public static function show_send24_modal() {
        \send24_inc\Send24_Logger::write_log("Showing modal");
        $data = WC()->session->get('send24_user_cart_response');
        $variant = WC()->session->get('send24_selected_variant');
        $selected_hub_id = WC()->session->get('send24_selected_hub_id');
        $response = json_decode($data);

        wp_enqueue_script(
            'send24-modal-script',
            plugin_dir_url(__FILE__) . 'js/send24-modal.js',
            array('jquery'),
            '1.0.0',
            true
        );

        wp_localize_script(
            'send24-modal-script',
            'ajax_object',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('send24_nonce')
            )
        );

        self::send24_modal($response, $variant, $selected_hub_id);
    }

    private static function send24_modal($response, $variant, $selected_hub_id) {
        ?>
        <div id="send24Modal" class="send24-modal" style="display: block;">
            <div class="send24-modal-content">
                <span id="closeSend24Modal" class="send24-close">&times;</span>
                <img src="<?php echo esc_url(plugin_dir_url(__FILE__)); ?>send24-logo.png" alt="Send24 Logo" class="send24-logo" />

                <?php foreach ($response->data as $option) :
                    foreach ($option as $shipping_type => $details) :
                        if (in_array($shipping_type, ['HUB_TO_DOOR', 'HUB_TO_HUB'])) :
                            $checked = ($variant === $shipping_type) ? 'checked' : '';
                            $formatted_price = $details->formatted_price;
                            $price = $details->price;
                            $delivery_type = ($shipping_type === 'HUB_TO_DOOR') ? 'Door Delivery' : 'Hub Delivery';
                            $description = ($shipping_type === 'HUB_TO_HUB')
                                ? 'Easily pick up your packages at a Send24 hub closest to you.'
                                : 'Have a Send24 agent deliver your packages to your doorstep.';
                ?>
                            <div class="send24-shipping-option">
                                <label class="send24-shipping-label">
                                    <input type="radio" name="send24_shipping_option" 
                                        data-price="<?php echo esc_attr($price); ?>" 
                                        value="<?php echo esc_attr($shipping_type); ?>" 
                                        <?php echo esc_attr($checked); ?>>
                                    <?php echo esc_html($delivery_type); ?>
                                </label>
                                <span class="send24-shipping-price">₦<?php echo esc_html($formatted_price); ?></span>
                                <p class="send24-description"><?php echo esc_html($description); ?></p>

                                <?php if ($shipping_type === 'HUB_TO_HUB') : ?>
                                    <h4 class="send24-hub-text">
                                        <a href="#" id="toggleHubsLink" class="send24-hub-link">
                                            <i><?php echo esc_html__('See Nearby Hubs', 'send24-logistics'); ?></i>
                                        </a>
                                    </h4>
                                    <div id='nearbyHubsList' style='display:none; max-height: 200px; overflow-y: auto; border: 1px solid #ccc; padding: 10px; margin-top: 10px;'>
                                        <ul style='list-style: none; padding-left: 0;'>
                                            <?php foreach ($details->recommended_hubs as $hub) :
                                                $checked_hub = ($selected_hub_id === $hub->uuid) ? 'checked' : '';
                                            ?>
                                                <li style='border-bottom: 1px solid #ddd; padding: 10px 0; display: flex; align-items: center;'>
                                                    <label style='cursor: pointer; display: flex; align-items: center;'>
                                                        <input type="radio" name="selected_hub" 
                                                            value="<?php echo esc_attr($hub->uuid); ?>" 
                                                            <?php echo esc_attr($checked_hub); ?>>
                                                        <div class='send24-description' style='display: inline-block;'>
                                                            <strong><?php echo esc_html($hub->name); ?></strong><br>
                                                            <?php echo esc_html__('Address', 'send24-logistics'); ?>: <?php echo esc_html($hub->address); ?><br>
                                                            <?php echo esc_html__('Distance', 'send24-logistics'); ?>: <?php echo esc_html($hub->distance); ?> km<br>
                                                            <?php echo esc_html__('Phone', 'send24-logistics'); ?>: <?php echo esc_html($hub->phone); ?>
                                                        </div>
                                                    </label>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </div>
                <?php
                        endif;
                    endforeach;
                endforeach;
                ?>
                <div class="send24-confirm">
                    <button id="confirmSend24Shipping" class="send24-confirm-button">
                        <span class="button-text"><?php echo esc_html__('Confirm Shipping Option', 'send24-logistics'); ?></span>
                        <span class="button-loader" style="display:none;">
                            <div class="loader"></div>
                        </span>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }
}