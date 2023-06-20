const productText = document.querySelectorAll('.product_text');

productText && productText.forEach((text) => {
  const width = text.offsetWidth;
  const halfWidth = Math.ceil(width / 2);
  text.style.left = '-' + (halfWidth - 16) + 'px';
})
