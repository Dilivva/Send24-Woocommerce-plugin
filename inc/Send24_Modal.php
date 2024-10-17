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
				                    <a href='#' id='toggleHubsLinkclass='send24-hub-link'>
        								<i>See Nearby Hubs</i>
        							</a>
				                </h4>

								<!-- Nearby Hubs List -->
                				<div id='nearbyHubsList' style='display:none; max-height: 200px; overflow-y: auto; border: 1px solid #ccc; padding: 10px; margin-top: 10px;'>
                    				<form id='hubSelectionForm'>
                    				<ul style='list-style: none; padding-left: 0;'>";

                				// Loop through the recommended hubs
                				foreach ($details['recommended_hubs'] as $hub) {
                					$hub_name = $hub['name'];
                					$hub_address = $hub['address'];
                					$hub_phone = $hub['phone'];
                					$hub_distance = $hub['distance'];
									$hub_uuid = $hub['uuid'];

                					echo "
                    				<li style='border-bottom: 1px solid #ddd; padding: 10px 0; display: flex; align-items: center;'>
            							<label style='cursor: pointer; display: flex; align-items: center;'>
            								<input type='radio' name='selected_hub' value='$hub_uuid' style='margin-right: 10px;' onclick='highlightHub(this)'>
            								<div class='send24-description' style='display: inline-block;'>
            								    <strong>$hub_name</strong><br>
            								    Address: $hub_address<br>
            								    Distance from your Location{$hub_distance}km<br>
            								    Phone Number: $hub_phone
            								</div>
            							</label>
            						</li>";
                				}

                				echo "
                					</ul>
									</form>
                				</div>";
				            }
								
				            echo "</div>";
				        }
				    }
				}

				echo "
				<script>
				    document.getElementById('toggleHubsLink').addEventListener('click', function(event) {
				        event.preventDefault();
				        var hubDeliveryRadio = document.querySelector('input[name=send24_shipping_option][value=HUB_TO_HUB]');

				        // Check if the Hub Delivery radio button is checked
				        if (!hubDeliveryRadio.checked) {
				            alert('Please select Hub Delivery first to see nearby hubs.');
				            return;
				        }

				        var hubsList = document.getElementById('nearbyHubsList');
				        if (hubsList.style.display === 'none') {
				            hubsList.style.display = 'block';
				        } else {
				            hubsList.style.display = 'none';
				        }
				    });


				function highlightHub(selectedHub) {
					var hubs = document.getElementsByName('selected_hub');
					hubs.forEach(function(hub) {
						hub.closest('li').style.backgroundColor = '#fff';
					});

					// Highlight the selected hub
					selectedHub.closest('li').style.backgroundColor = '#F5F2DC';
				}

					document.querySelectorAll('input[name=send24_shipping_option]').forEach(function(radio) {
                	radio.addEventListener('change', function() {
            		    var hubsList = document.getElementById('nearbyHubsList');
            		    if (this.value === 'HUB_TO_DOOR' && hubsList.style.display === 'block') {
            		        hubsList.style.display = 'none';
            		    }
            		});
            	});
				</script>";
					
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
		</div>';

	
			// } else {
			// 	echo '<p>Unable to fetch Send24 shipping options at this time. Please try again later.</p>';
			// }


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