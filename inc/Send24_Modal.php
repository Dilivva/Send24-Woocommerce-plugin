<?php

class Send24_Modal {

	public static function show_send24_modal(){
		\inc\Send24_Logger::write_log("Showing modal");
		$data =  WC()->session->get( 'send24_user_cart_response' );
		$response = json_decode($data);
		$chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
		$chosen_shipping = $chosen_methods[0];

		\inc\Send24_Logger::write_log(is_checkout());
		\inc\Send24_Logger::write_log(str_contains($chosen_shipping, 'send24_logistics'));
		\inc\Send24_Logger::write_log($response);

		if (is_checkout() && isset($response)){
			Send24_Modal::send24_modal($response);
		}
		Send24_Modal::send24_modal($response);
	}
	private static function send24_modal($response){
		echo '
<div id="send24Modal" class="send24-modal" style="display: block;">
	<div class="send24-modal-content">
		<span id="closeSend24Modal" class="send24-close">&times;</span>
		<img src="' . plugin_dir_url(__FILE__) . 'send24-logo.png" alt="Send24 Logo" class="send24-logo" />';

		// Loop through the data to find HUB_TO_HUB and HUB_TO_DOOR
		foreach ($response->data as $option) {
			foreach ($option as $shipping_type => $details) {
				if ($shipping_type === 'HUB_TO_DOOR' || $shipping_type === 'HUB_TO_HUB') {
					$formatted_price = $details->formatted_price;
					$price = $details->price;
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


<!-- Modal Script -->
<script>
// document.getElementById("openSend24Modal").addEventListener("click", function() {
//	 document.getElementById("send24Modal").style.display = "block";
// });


    document.getElementById("closeSend24Modal").addEventListener("click", function() {
        console.log("Close");
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
                
                close(price);

			    // Define the AJAX URL and nonce
			    //var ajaxUrl = wc_cart_params.wc_ajax_url;
			    //var nonce = wc_cart_params.wc_ajax_nonce;

			    // Make the AJAX request
			    const options = {
  						method: "POST",
                        headers: { "Content-Type": "application/x-www-form-urlencoded" },
                        body: new URLSearchParams({action: "send24_get_selected_variant", shipping_price: price} )
				};
                
                fetch(ajax_object.ajax_url, options)
                .then(response => response.json())
                .then(response => {  // Add curly braces and semicolons
                    document.getElementById("send24Modal").style.display = "none"
                    location.reload();
                })
  				.catch(err => console.error(err));
            }
	    });

    });
</script>';
	}


}