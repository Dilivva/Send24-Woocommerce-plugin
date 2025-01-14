var selectedHubId = ''

function elementInViewport(element, callback) {
  const observer = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
      if (
        mutation.type === 'attributes' &&
        mutation.attributeName === 'style'
      ) {
        const isVisible = window.getComputedStyle(element).display !== 'none'
        if (isVisible) {
          console.log('Element is now visible!', element)
          // Perform your actions here, e.g.,
          callback()
          // Stop observing if you only need to detect the first time it becomes visible
          observer.disconnect()
        }
      }
    })
  })

  const config = {attributes: true}

  observer.observe(element, config)
}

jQuery(document).ready(function () {
  const parent = document.getElementById('send24Modal_hidden')
  elementInViewport(parent, function (element) {
    document
      .getElementById('toggleHubsLink')
      .addEventListener('click', function (event) {
        event.preventDefault()
        var hubDeliveryRadio = document.querySelector(
          'input[name=send24_shipping_option][value=HUB_TO_HUB]'
        )
        if (!hubDeliveryRadio.checked) {
          alert('Please select Hub Delivery first to see nearby hubs.')
          return
        }
        var hubsList = document.getElementById('nearbyHubsList')
        hubsList.style.display =
          hubsList.style.display === 'none' ? 'block' : 'none'
      })

    document
      .querySelectorAll('input[name="selected_hub"]')
      .forEach(function (hubRadio) {
        hubRadio.addEventListener('change', function () {
          document.querySelectorAll('li').forEach(function (li) {
            li.style.backgroundColor = ''
          })
          this.closest('li').style.backgroundColor = '#F5F2DC'
          selectedHubId = hubRadio.value
        })
      })

    // Handle shipping option selection
    document
      .querySelectorAll('input[name=send24_shipping_option]')
      .forEach(function (radio) {
        radio.addEventListener('change', function () {
          console.log('Shipping option selected:', this.value)
          var hubsList = document.getElementById('nearbyHubsList')
          if (
            this.value === 'HUB_TO_DOOR' &&
            hubsList.style.display === 'block'
          ) {
            hubsList.style.display = 'none'
          }
        })
      })

    // Close modal
    document
      .getElementById('closeSend24Modal')
      .addEventListener('click', function () {
        document.getElementById('send24Modal').style.display = 'none'
      })

    window.onclick = function (event) {
      if (event.target === document.getElementById('send24Modal')) {
        document.getElementById('send24Modal').style.display = 'none'
      }
    }

    // Confirm shipping option
    document
      .getElementById('confirmSend24Shipping')
      .addEventListener('click', function () {
        console.log('Confirm button clicked')
        var selectedOption = document.querySelector(
          'input[name="send24_shipping_option"]:checked'
        )
        if (selectedOption) {
          var price = selectedOption.getAttribute('data-price')
          console.log('Selected shipping price: ' + price)
          var selectedVariant = selectedOption.value
          var selectedHubId =
            selectedVariant === 'HUB_TO_DOOR'
              ? null
              : document.querySelector("input[name='selected_hub']:checked")
                  ?.value

          var loader = document.querySelector('.button-loader')
          var buttonText = document.querySelector('.button-text')
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

          // fetch('ajax_object.ajax_url', options)
          // fetch('http://localhost/wordpress/wp-admin/admin-ajax.php', options)
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
  })
})

// Highlight the selected hub
