<!-- send24-shipping-widget.php -->
<div id="send24-shipping-options" class="send24-button-alpha">
    <button id="openSend24Modal" class="send24-button">
        <strong>Send24 Shipping!</strong>
    </button>
</div>

<!-- Modal HTML -->
<div id="send24Modal" class="send24-modal">
    <div class="send24-modal-content">
        <span id="closeSend24Modal" class="send24-close">&times;</span>
        <img src="<?php echo plugin_dir_url(__FILE__); ?>send24-logo.png" alt="Send24 Logo" class="send24-logo" />

        <?php foreach ($send24_response_data as $option): ?>
            <?php foreach ($option as $shipping_type => $details): ?>
                <?php if ($shipping_type === 'HUB_TO_DOOR' || $shipping_type === 'HUB_TO_HUB'): ?>
                    <?php
                        $formatted_price = $details['formatted_price'];
                        $price = $details['price'];
                        $delivery_type = ($shipping_type === 'HUB_TO_DOOR') ? 'Door Delivery' : 'Hub Delivery';
                        $description = ($shipping_type === 'HUB_TO_HUB')
                            ? 'Easily pick up your packages at a Send24 hub closest to you.'
                            : 'Have a Send24 agent deliver your packages to your doorstep.';
                    ?>
                    <div class="send24-shipping-option">
                        <label class="send24-shipping-label">
                            <input type="radio" name="send24_shipping_option" data-price="<?php echo $price; ?>" value="<?php echo $shipping_type; ?>">
                            <?php echo $delivery_type; ?>
                        </label>
                        <span class="send24-shipping-price">₦<?php echo $formatted_price; ?></span>
                        <p class="send24-description"><?php echo $description; ?></p>

                        <?php if ($shipping_type === 'HUB_TO_HUB'): ?>
                            <h4 class="send24-hub-text">
                                <a href="#" id="toggleHubsLink" class="send24-hub-link">
                                    <i>See Nearby Hubs</i>
                                </a>
                            </h4>

                            <!-- Nearby Hubs List -->
                            <div id="nearbyHubsList" class="send24-hub">
                                <form id="hubSelectionForm">
                                    <ul style='list-style: none; padding-left: 0;'>
                                        <?php foreach ($details['recommended_hubs'] as $hub): ?>
                                            <?php
                                                $hub_name = $hub['name'];
                                                $hub_address = $hub['address'];
                                                $hub_phone = $hub['phone'];
                                                $hub_distance = $hub['distance'];
                                                $hub_uuid = $hub['uuid'];
                                            ?>
                                            <li style='border-bottom: 1px solid #ddd; padding: 10px 0; display: flex; align-items: center;'> 
                                                <label style='cursor: pointer; display: flex; align-items: center;'>
                                                    <input type="radio" name="selected_hub" value="<?php echo $hub_uuid; ?>" onclick="highlightHub(this)">
                                                    <div class="send24-description">
                                                        <strong><?php echo $hub_name; ?></strong><br>
                                                        Address: <?php echo $hub_address; ?><br>
                                                        Distance: <?php echo $hub_distance; ?>km<br>
                                                        Phone: <?php echo $hub_phone; ?>
                                                    </div>
                                                </label>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endforeach; ?>

        <!-- Confirm Button -->
        <div class="send24-confirm">
            <button id="confirmSend24Shipping" class="send24-confirm-button">
                <span class="button-text">Confirm Shipping Option</span>
                <span class="button-loader" style="display:none;">
                    <div class="loader"></div>
                </span>
            </button>
        </div>
    </div>
</div>
