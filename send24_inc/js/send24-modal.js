;(function () {
  document.addEventListener('DOMContentLoaded', function () {
    const toggleHubsLink = document.getElementById('toggleHubsLink')
    if (toggleHubsLink) {
      toggleHubsLink.addEventListener('click', function (event) {
        event.preventDefault()
        const hubDeliveryRadio = document.querySelector(
          'input[name=send24_shipping_option][value=HUB_TO_HUB]'
        )
        if (!hubDeliveryRadio.checked) {
          alert('Please select Hub Delivery first to see nearby hubs.')
          return
        }
        const hubsList = document.getElementById('nearbyHubsList')
        hubsList.style.display =
          hubsList.style.display === 'none' ? 'block' : 'none'
      })
    }

    const hubRadios = document.querySelectorAll('input[name="selected_hub"]')
    hubRadios.forEach(function (hubRadio) {
      hubRadio.addEventListener('change', function () {
        document.querySelectorAll('li').forEach(function (li) {
          li.style.backgroundColor = ''
        })
        this.closest('li').style.backgroundColor = '#F5F2DC'
      })
    })

    const shippingOptions = document.querySelectorAll(
      'input[name=send24_shipping_option]'
    )
    shippingOptions.forEach(function (radio) {
      radio.addEventListener('change', function () {
        console.log('Shipping option selected:', this.value)
        const hubsList = document.getElementById('nearbyHubsList')
        if (
          this.value === 'HUB_TO_DOOR' &&
          hubsList.style.display === 'block'
        ) {
          hubsList.style.display = 'none'
        }
      })
    })

    // Close modal
    const closeButton = document.getElementById('closeSend24Modal')
    if (closeButton) {
      closeButton.addEventListener('click', function () {
        document.getElementById('send24Modal').style.display = 'none'
      })
    }

    // Close modal when clicking outside
    window.onclick = function (event) {
      const modal = document.getElementById('send24Modal')
      if (event.target === modal) {
        modal.style.display = 'none'
      }
    }

    // Confirm shipping option
    const confirmButton = document.getElementById('confirmSend24Shipping')
    if (confirmButton) {
      confirmButton.addEventListener('click', function () {
        console.log('Confirm button clicked')
        const selectedOption = document.querySelector(
          'input[name="send24_shipping_option"]:checked'
        )

        if (selectedOption) {
          const price = selectedOption.getAttribute('data-price')
          console.log('Selected shipping price: ' + price)
          const selectedVariant = selectedOption.value
          const selectedHubId =
            selectedVariant === 'HUB_TO_DOOR'
              ? null
              : document.querySelector('input[name="selected_hub"]:checked')
                  ?.value

          const loader = document.querySelector('.button-loader')
          const buttonText = document.querySelector('.button-text')
          loader.style.display = 'inline-block'
          buttonText.style.display = 'none'

          const options = {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({
              action: 'send24_get_selected_variant',
              shipping_price: price,
              selected_hub: selectedHubId,
              selected_variant: selectedVariant,
              security: ajax_object.nonce,
              // nonce: ajax_object.nonce,
            }),
          }

          fetch(ajax_object.ajax_url, options)
            .then((response) => response.json())
            .then((response) => {
              console.log('Response:', response)
              location.reload()
            })
            .catch((err) => console.error(err))
        } else {
          console.log('No shipping option selected')
        }
      })
    }
  })
})()
