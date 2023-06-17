<?php
\authorization\Authorization::isLogin();

get_header();
?>

<div class="login-page">
    <form class="auth-form" id="auth-form">
        <span class="alert-message"></span>
        <ul>
            <li class="container-input container-username-and-email">
                <label for="usernameAndEmail">Имя пользователя или E-mail</label>
                <input class="username"
                       name="usernameAndEmail"
                       maxlength="140"
                       type="text"
                       id="usernameAndEmail"
                       value=""
                >
                <span class="message-error"></span>
            </li>
            <li class="container-input container-password">
                <label for="password">Пароль</label>
                <input class="password"
                       name="password"
                       maxlength="70"
                       type="password"
                       id="password"
                       value=""
                >
                <span class="message-error"></span>
            </li>
        </ul>
        <div class="buttons-wrapper">
            <button class="btn login-btn" type="button">Войти</button>
            <a href="/registration" class="btn">Регистрация</a>
            <button class="btn password-recovery" type="button">Забыли пароль?</button>
        </div>
    </form>
</div>

<?php
get_footer();
?>