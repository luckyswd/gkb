class Authorization {
  constructor() {
    this.init()
  }

  init() {
    this.registration();
    this.login();
    this.passwordRecovery();
    this.logout()
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
      const response = await this.getResponse('registration_user', registrationForm)

      if (response.status) {
        registerPage.innerHTML = response.messageAfterRegister;
      }

      this.validInput(containerUserName, response.username);
      this.validInput(containerEmail, response.email);
      this.validInput(containerPassword, response.password);
    })
  }

  login() {
    const loginForm = document.querySelector('.login-page form');
    if (!loginForm) {
      return;
    }
    const buttonLogin = loginForm.querySelector('.login-btn');
    const alertMessage = loginForm.querySelector('.alert-message')

    buttonLogin.addEventListener('click', async () => {
      const response = await this.getResponse('login_user', loginForm)

      if (response.status) {
        window.location.href = response.redirectAfterLogin;

        return;
      }

      alertMessage.textContent = response.message;
    })
  }

  logout() {
    window.addEventListener('click', async (e) => {
      const target = e.target;

      if (!target.classList.contains('user-logout')) {
        return;
      }

      const response = await this.getResponse('logout_user')

      if (response.status) {
        location.reload();
      }
    })
  }

  passwordRecovery() {
    const loginForm = document.querySelector('.login-page form');
    if (!loginForm) {
      return;
    }
    const buttonPasswordRecovery = loginForm.querySelector('.password-recovery');
    const alertMessage = loginForm.querySelector('.alert-message')

    buttonPasswordRecovery.addEventListener('click', async () => {
      const response = await this.getResponse('password_recovery', loginForm)

      if (response.status) {
        alertMessage.textContent = response.message;

        return;
      }

      alertMessage.textContent = response.message;
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

  /**
   *  @param {string} action
   *  @param {HTMLElement} form
   */
  async getResponse(
    action,
    form = null,
  ) {
    const formData = form ? new FormData(form) : new FormData();
    console.log(action)
    formData.append('action', action);
    const response = await fetch(window.ajaxUrl,
      {
        method: 'POST',
        body: formData
      }
    );

    return await response.json();
  }
}

new Authorization();