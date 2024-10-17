<div id="send24-shipping-options" style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); margin-top: 10px; padding: 20px; border-radius: 10px; background-color: #f8f8f8; max-width: 600px; z-index: 999;">
	<button id="openSend24Modal" style="padding: 10px 20px; background-color: #D2691E; color: white; border: none; border-radius: 4px; font-size: 14px; cursor: pointer; animation: blink 1s infinite;">
		<strong>Send24 Shipping!</strong>
	</button>
</div>

<!-- Modal HTML -->
<div id="send24Modal" class="send24-modal" style="display: none;">
	<div class="send24-modal-content">
		<span id="closeSend24Modal" class="send24-close">&times;</span>
		<img src="' . plugin_dir_url(__FILE__) . 'send24-logo.png" alt="Send24 Logo" class="send24-logo" />';

		// Loop through the data to find HUB_TO_HUB and HUB_TO_DOOR
		foreach ($response_data['data'] as $option) {
		foreach ($option as $shipping_type => $details) {
		if ($shipping_type === 'HUB_TO_DOOR' || $shipping_type === 'HUB_TO_HUB') {
		$formatted_price = $details['formatted_price'];
		$price = $details['price'];
		$delivery_type = ($shipping_type === 'HUB_TO_DOOR') ? 'Door Delivery' : 'Hub Delivery';
		$description = ($shipping_type === 'HUB_TO_HUB')
		? 'Easily pick up your packages at a Send24 hub closest to you.'
		: 'Have a Send24 agent deliver your packages to your doorstep.';

		// Display each option in a card-like structure
		echo "
		<div class='send24-shipping-option'>
			<label class='send24-shipping-label'>
				<input type='radio' name='send24_shipping_option' data-price='$price' value='$shipping_type'>
				$delivery_type
			</label>
			<span class='send24-shipping-price'>₦$formatted_price</span>
			<p class='send24-description'>$description</p>";

			// Button for HUB_TO_HUB shipping type
			if ($shipping_type === 'HUB_TO_HUB') {
			echo "
			<h4 class='send24-hub-text'>
				<a href='https://send24.co/hubs' class='send24-hub-link' target=blank>
					<i>See Nearby Hubs</i>
				</a>
			</h4>";
			}

			echo "</div>";
		}
		}
		}

		echo '
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

<!-- Modal Styles -->
<style>
    @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap");

    body {
        font-family: "Poppins", sans-serif;
    }

    @keyframes blink {
        0% {
            opacity: 1;
        }
        50% {
            opacity: 0.5;
        }
        100% {
            opacity: 1;
        }
    }

    .send24-modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .send24-modal-content {
        background-color: #fff;
        padding: 20px;
        border-radius: 15px;
        width: 400px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    .send24-close {
        position: absolute;
        top: 10px;
        right: 20px;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    .send24-logo {
        width: 150px;
        margin-bottom: 20px;
        display: block;
        margin-left: auto;
        margin-right: auto;
    }

    .send24-shipping-option {
        padding: 15px;
        border: 1px solid #ddd;
        margin-bottom: 15px;
        border-radius: 10px;
        background-color: #f9f9f9;
        position: relative;
    }

    .send24-shipping-label {
        font-weight: 600;
        color: #333;
        display: inline-block;
        margin-right: 10px;
        font-family: "Arial", sans-serif;
    }

    .send24-shipping-price {
        float: right;
        font-weight: 600;
        color: #D2691E; /* Orange color */
        font-size: 16px;
        font-family: "Arial", sans-serif;
    }

    .send24-description {
        margin-top: 5px;
        font-size: 14px;
        color: #777;
        font-family: "Arial", sans-serif;
    }

    .send24-hub-button,
    .send24-confirm-button {
        padding: 10px 20px;
        background-color: #D2691E;
        color: white;
        border: none;
        border-radius: 30px;
        font-size: 16px;
        cursor: pointer;
        margin-top: 10px;
        font-weight: 600;
        font-family: "Arial", sans-serif;
    }

    .send24-hub-button:hover,
    .send24-confirm-button:hover {
        background-color: #e64a19;
    }

    .loader {
        border: 4px solid #f3f3f3; /* Light grey */
        border-top: 4px solid #D2691E; /* Orange */
        border-radius: 50%;
        width: 18px;
        height: 18px;
        animation: spin 1s linear infinite;
        display: inline-block;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }

    .send24-hub-text {
        font-style: italic;
        font-weight: bold;
        font-size: 14px;
        color: #ff5722;
        margin-top: 10px;
        font-family: "Arial", sans-serif;
        font-family: "Arial", sans-serif;
    }

    .send24-hub-link {
        font-style: italic;
        font-weight: bold;
        font-size: 14px;
        color: #ff5722;
        text-decoration: none; /* Removes the underline */
        cursor: pointer;
        font-family: "Arial", sans-serif; /* Or any appealing font */
    }

    .send24-confirm {
        text-align: center;
    }
</style>


<!-- Modal Script -->
<script>
    document.getElementById("openSend24Modal").addEventListener("click", function() {
        document.getElementById("send24Modal").style.display = "block";
    });

    document.getElementById("closeSend24Modal").addEventListener("click", function() {
        document.getElementById("send24Modal").style.display = "none";
    });

    window.onclick = function(event) {
        if (event.target === document.getElementById("send24Modal")) {
            document.getElementById("send24Modal").style.display = "none";
        }
    };

    document.getElementById("confirmSend24Shipping").addEventListener("click", function() {
        jQuery(document).on("click", function() {
            var selectedOption = document.querySelector("input[name=\"send24_shipping_option\"]:checked");
            if (selectedOption) {
                var price = selectedOption.getAttribute("data-price");
                console.log("Selected shipping price: " + price);

                var loader = document.querySelector(".button-loader");
                var buttonText = document.querySelector(".button-text");
                loader.style.display = "inline-block";
                buttonText.style.display = "none";

                // Define the AJAX URL and nonce
                var ajaxUrl = wc_cart_params.wc_ajax_url;
                var nonce = wc_cart_params.wc_ajax_nonce;

                // Make the AJAX request
                jQuery.ajax({
                    url: ajaxUrl, // WordPress AJAX URL
                    type: "POST", // Request method
                    data: {
                        action: "send24_update_shipping_price", // WordPress AJAX action hook
                        shipping_price: price, // Shipping price selected by the user
                        _wpnonce: nonce // Security nonce
                    },
                    success: function(response) {
                        // On success: hide the loader, show the button text, and update the checkout
                        loader.style.display = "none";
                        buttonText.style.display = "inline-block";

                        if (response.success) {
                            console.log("Shipping price updated successfully:", response);
                            // Trigger the update_checkout action to refresh the checkout
                            jQuery("body").trigger("update_checkout");
                        } else {
                            console.log("Error in response:", response);
                        }
                    },
                    error: function(xhr, status, error) {
                        // On error: hide the loader, show the button text, and log the error
                        loader.style.display = "none";
                        buttonText.style.display = "inline-block";
                        console.log("Error updating shipping price:", error);
                    }
                });
            }
        });

    });
</script>';