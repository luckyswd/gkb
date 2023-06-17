class Authorization {
  constructor() {
    this.init()
  }

  init() {
    this.registration()
  }

  registration() {
    const registerPage = document.querySelector('.registration-page');
    const registrationForm = document.querySelector('.registration-page form');
    if (!registrationForm) {
      return;
    }
    const buttonSend = registrationForm.querySelector('button');
    const containerUserName = registrationForm.querySelector('.container-username')
    const containerEmail = registrationForm.querySelector('.container-email')
    const containerPassword = registrationForm.querySelector('.container-password')

      buttonSend.addEventListener('click', async () => {
      const formData = new FormData(registrationForm);
      formData.append('action', 'registration_user');
      const response = await fetch(window.ajaxUrl,
        {
          method: 'POST',
          body: formData
        }
      );
      const resp = await response.json();
      if (resp.status) {
        registerPage.innerHTML = resp.messageAfterRegister;
      }

      this.validInput(containerUserName, resp.username);
      this.validInput(containerEmail, resp.email);
      this.validInput(containerPassword, resp.password);
    })
  }

  validInput(
    inputContainer,
    response
  ) {
    const spanMessage = inputContainer.querySelector('.message-error');

    if (response.status) {
      inputContainer.classList.remove('error-input')
      spanMessage.textContent = '';
      return;
    }

    inputContainer.classList.add('error-input')
    spanMessage.textContent = response.message;
  }
}

new Authorization();