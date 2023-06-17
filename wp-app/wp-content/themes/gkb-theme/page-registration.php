<?php
\authorization\Authorization::isLogin();
get_header();
?>

<div class="registration-page">
    <form class="auth-form" id="auth-form">
        <span class="alert-message"></span>
        <ul>
            <li class="container-input container-username">
                <label for="username">Имя пользователя</label>
                <input class="username"
                       name="username"
                       maxlength="70"
                       type="text"
                       id="username"
                       value=""
                >
                <span class="message-error"></span>
            </li>
            <li class="container-input container-email">
                <label for="email">E-mail</label>
                <input class="email"
                       name="email"
                       maxlength="70"
                       type="email"
                       id="email"
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
        <button class="btn" type="button">Зарегистрироваться</button>
    </form>
</div>

<?php
get_footer();
?>
