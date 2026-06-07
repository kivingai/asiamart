document.addEventListener('DOMContentLoaded', () => {
  let isLoginMode = true;

  const loginToggle = document.getElementById('loginToggle');
  const registerToggle = document.getElementById('registerToggle');
  const nameGroup = document.getElementById('nameGroup');
  const forgotPassword = document.getElementById('forgotPassword');
  const termsGroup = document.getElementById('termsGroup');
  const submitBtnText = document.querySelector('.submit-btn .btn-text');
  const switchText = document.getElementById('switchText');
  const switchBtn = document.getElementById('switchBtn');
  const form = document.getElementById('authForm');
  const logo = document.getElementById('logo');

  if (!form || !loginToggle || !registerToggle || !switchBtn || !submitBtnText) {
    return;
  }

  const updateMode = (loginMode) => {
    isLoginMode = loginMode;
    loginToggle.classList.toggle('active', loginMode);
    registerToggle.classList.toggle('active', !loginMode);

    nameGroup?.classList.toggle('hidden', loginMode);
    termsGroup?.classList.toggle('hidden', loginMode);
    forgotPassword?.classList.toggle('hidden', !loginMode);

    if (loginMode) {
      submitBtnText.textContent = 'Войти';
      if (switchText) switchText.textContent = 'Нет аккаунта?';
      switchBtn.textContent = 'Зарегистрироваться';
      return;
    }

    submitBtnText.textContent = 'Создать аккаунт';
    if (switchText) switchText.textContent = 'Уже есть аккаунт?';
    switchBtn.textContent = 'Войти';
  };

  loginToggle.addEventListener('click', () => updateMode(true));
  registerToggle.addEventListener('click', () => updateMode(false));
  switchBtn.addEventListener('click', () => updateMode(!isLoginMode));

  logo?.addEventListener('click', () => {
    window.location.href = 'index.html';
  });

  form.addEventListener('submit', (event) => {
    event.preventDefault();
    window.location.href = 'profile.html';
  });

  updateMode(true);
});
