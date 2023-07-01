const fileInput = document.querySelector('.wrapper-leftovers .upload-file');

fileInput && fileInput.addEventListener('change', async function(event) {
  const target = event.target;
  const file =target.files[0];
  const {url, urlGoogleApi} = target.dataset;

  if (file && !checkFileFormat(file)) {
    alert('Неверный формат файла. Пожалуйста, выберите файл Excel.');
  } else {
    const dataUpload = await uploadFileAjax(file, url);

    if (dataUpload.status) {
      location.reload();
      await updateGoogleApiExel(urlGoogleApi)
    }
  }
});

function checkFileFormat(file) {
  const allowedExtensions = ['.xlsx', '.xls'];
  const allowedMimeTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'];
  const fileExtension = '.' + file.name.split('.').pop();
  const fileMimeType = file.type;

  return allowedExtensions.includes(fileExtension) && allowedMimeTypes.includes(fileMimeType);
}

async function uploadFileAjax(
  file,
  url
) {
  const formData = new FormData();
  formData.append('file', file);

  const response = await fetch(url, {
    method: 'POST',
    body: formData
  });

  return await response.json();
}

async function updateGoogleApiExel(
  url
) {
  await fetch(url, {
    method: 'POST',
  });
}