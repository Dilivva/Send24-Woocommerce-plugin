// send24-shipping-widget.js
jQuery(document).ready(function ($) {
  $('#openSend24Modal').on('click', function () {
    $('#send24Modal').show()
  })

  $('#closeSend24Modal').on('click', function () {
    $('#send24Modal').hide()
  })

  $(window).on('click', function (event) {
    if ($(event.target).is('#send24Modal')) {
      $('#send24Modal').hide()
    }
  })

  $('#toggleHubsLink').on('click', function (event) {
    event.preventDefault()
    var hubDeliveryRadio = $(
      'input[name="send24_shipping_option"][value="HUB_TO_HUB"]'
    )

    if (!hubDeliveryRadio.is(':checked')) {
      alert('Please select Hub Delivery first to see nearby hubs.')
      return
    }

    $('#nearbyHubsList').toggle()
  })

  $('input[name="send24_shipping_option"]').on('change', function () {
    if ($(this).val() === 'HUB_TO_DOOR') {
      $('#nearbyHubsList').hide()
    }
  })

  window.highlightHub = function (selectedHub) {
    $('input[name="selected_hub"]')
      .closest('li')
      .css('background-color', '#fff')
    $(selectedHub).closest('li').css('background-color', '#F5F2DC')
  }

  $('#confirmSend24Shipping').on('click', function () {
    var selectedOption = $('input[name="send24_shipping_option"]:checked')
    if (selectedOption.length === 0) {
      alert('Please select a shipping option.')
      return
    }

    var price = selectedOption.data('price')
    var shippingOption = selectedOption.val()
    var selectedHub = $('input[name="selected_hub"]:checked').val() || ''

    var loader = $('.button-loader')
    var buttonText = $('.button-text')
    loader.show()
    buttonText.hide()

    $.ajax({
      url: send24_ajax_object.ajax_url,
      type: 'POST',
      data: {
        action: 'send24_update_shipping_price',
        shipping_option: shippingOption,
        price: price,
        hub_uuid: selectedHub,
        nonce: send24_ajax_object.nonce,
      },
      success: function (response) {
        loader.hide()
        buttonText.show()

        if (response.success) {
          console.log('Shipping price updated successfully:', response)
          $('body').trigger('update_checkout')
          $('#send24Modal').hide()
        } else {
          console.log('Error in response:', response)
          alert('Failed to update shipping option. Please try again.')
        }
      },
      error: function (xhr, status, error) {
        loader.hide()
        buttonText.show()
        console.log('Error updating shipping price:', error)
        alert('Error updating shipping option. Please try again.')
      },
    })
  })
})
