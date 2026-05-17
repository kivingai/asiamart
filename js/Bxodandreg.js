  let isLogin = true;

  const loginToggle = document.getElementById('loginToggle');
  const registerToggle = document.getElementById('registerToggle');
  const nameGroup = document.getElementById('nameGroup');
  const forgotPassword = document.getElementById('forgotPassword');
  const termsGroup = document.getElementById('termsGroup');
  const submitBtn = document.querySelector('.submit-btn');
  const btnText = submitBtn.querySelector('.btn-text');
  const switchText = document.getElementById('switchText');
  const switchBtn = document.getElementById('switchBtn');
  const form = document.getElementById('authForm');
  const logo = document.getElementById('logo');


  function updateMode(login) {
    isLogin = login;
    if (login) {
      loginToggle.classList.add('active');
      registerToggle.classList.remove('active');
    } else {
      loginToggle.classList.remove('active');
      registerToggle.classList.add('active');
    }
    if (login) {
      nameGroup.classList.add('hidden');
      termsGroup.classList.add('hidden');
      forgotPassword.classList.remove('hidden');
      btnText.textContent = 'Войти';
      switchText.textContent = 'Нет аккаунта?';
      switchBtn.textContent = 'Зарегистрироваться';
    } else {
      nameGroup.classList.remove('hidden');
      termsGroup.classList.remove('hidden');
      forgotPassword.classList.add('hidden');
      btnText.textContent = 'Создать аккаунт';
      switchText.textContent = 'Уже есть аккаунт?';
      switchBtn.textContent = 'Войти';
    }
  }

  loginToggle.addEventListener('click', () => {
    if (!isLogin) {
      updateMode(true);
    }
  });
  registerToggle.addEventListener('click', () => {
    if (isLogin) {
      updateMode(false);
    }
  });

  switchBtn.addEventListener('click', () => {
    updateMode(!isLogin);
  });

  btnText.addEventListener('click', () => {
    form.submit();
  });

     
    submitBtn.addEventListener('click', () => {
      window.location.href = 'profile.html';
    });
  if (logo) {
    logo.addEventListener('click', () => {
      window.location.href = 'index.html';
    });
  }

  form.addEventListener('submit', (e) => {
    e.preventDefault();
  });

