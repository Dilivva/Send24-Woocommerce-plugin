jQuery(document.body).on( 'click', '.wc-block-components-radio-control__input', function() {
    let partialValue = "send24"; // The partial value you're searching for

    let radioInput = jQuery('input[type="radio"][value*="' + partialValue + '"]');
    if (radioInput.length > 0) { //Check if it found any matching input
        //radioInput.prop('checked', true);  // Select the radio button
        let value = radioInput.val();
        console.log("Radio button with partial value '" + value + "' found and selected.");
        const options = {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({action: "send24_show_send24_modal"} )
        };
        fetch("http://localhost/wordpress/wp-admin/admin-ajax.php", options)
            .then(response => response.text())
            .then(response => {  // Add curly braces and semicolons
                jQuery('#send24Modal_hidden').html(response).show();
            })
            .catch(err => console.log(err));

    } else {
        console.log("No radio button found with the partial value: " + partialValue)
    }
    console.log("Chosen");
});

jQuery(document).on('change','', function() {
    console.log('Cart totals updated!'); // More specific to cart total updates.
    //Update cart totals in a custom display:
    // let updatedCartTotals = jQuery('.cart_totals').html();
    // jQuery('#my-cart-totals-display').html(updatedCartTotals);
});