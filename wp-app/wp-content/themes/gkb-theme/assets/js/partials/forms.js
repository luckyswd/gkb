document.addEventListener('wpcf7mailsent', function (event) {
  const closeBtn = document.querySelector('.carousel__button.is-close');

  if (closeBtn) {
    closeBtn.click()
  }
}, false);