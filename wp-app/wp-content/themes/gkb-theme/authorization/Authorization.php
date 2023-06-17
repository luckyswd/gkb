<?php

namespace authorization;

use helpers\Helpers;

class Authorization
{
    public function register(): void
    {
        add_action('wp_ajax_registration_user', [$this, 'registrationUser']);
        add_action('wp_ajax_nopriv_registration_user', [$this, 'registrationUser']);
        add_action('wp_ajax_login_user', [$this, 'login']);
        add_action('wp_ajax_nopriv_login_user', [$this, 'login']);
        add_action('wp_ajax_password_recovery', [$this, 'passwordRecovery']);
        add_action('wp_ajax_nopriv_password_recovery', [$this, 'passwordRecovery']);
        add_action('wp_ajax_logout_user', [$this, 'logout']);
        add_action('wp_ajax_nopriv_logout_user', [$this, 'logout']);
        add_action('admin_init', [$this, 'adminAccess']);
    }

    public function login(): never  {
        $request = Helpers::getRequest(Helpers::METHOD_POST, $_POST);
        $usernameAndEmail = $request->get_param('usernameAndEmail');
        $password = $request->get_param('password');

        $user = get_user_by('login', $usernameAndEmail);
        $statusVerifiedEmail = get_user_meta($user->ID, 'email_confirmation_status', 'verified');
        if ($user && $statusVerifiedEmail !== 'verified' && $user->ID !== 1) {
            wp_send_json([
                'status' => false,
                'message' => 'Подтвердите адрес электронной почты, чтобы войти.',
            ]);

            exit();
        }

        $user = wp_authenticate($usernameAndEmail, $password);

        if (is_wp_error($user)) {
            wp_send_json([
                'status' => false,
                'message' => 'Неправильное имя пользователя или email или пароль.',
            ]);

            exit();
        }

        wp_set_auth_cookie( $user->ID );
        wp_send_json([
            'status' => true,
            'redirectAfterLogin' => home_url(),
        ]);

        exit();
    }

    public function logout(): never {
        wp_logout();

        wp_send_json([
            'status' => true,
        ]);

        exit();
    }

    public function registrationUser(): never {
        $request = Helpers::getRequest(Helpers::METHOD_POST, $_POST);
        $username = $request->get_param('username');
        $email = $request->get_param('email');
        $password = $request->get_param('password');
        $existUserName = $this->existUserName($username);
        $existEmail = $this->existEmail($email);
        $validPassword = $this->checkValidPassword($password);
        $status = !(!$existUserName['status'] || !$existEmail['status'] || !$validPassword['status']);

        if (!$status) {
            wp_send_json([
                'status' => false,
                'username' => $existUserName,
                'email' => $existEmail,
                'password' => $validPassword,
            ]);

            exit();
        }

        $userId = wp_create_user($username, $password, $email);
        wp_logout();
        update_user_meta($userId, 'email_confirmation_status', 'pending');

        if (is_wp_error($userId)) {
            wp_send_json([
                'status' => false,
                'username' => $existUserName,
                'email' => $existEmail,
                'password' => $validPassword,
            ]);

            exit();
        }

        $this->sendConfirmEmail($userId, $email);

        $messageAfterRegister = 'Спасибо за регистрацию! Для активации вашего аккаунта, пожалуйста, проверьте вашу электронную почту и следуйте инструкциям, указанным в письме активации.';
        wp_send_json([
            'status' => true,
            'username' => $existUserName,
            'email' => $existEmail,
            'password' => $validPassword,
            'messageAfterRegister' => '<div class="message-after-register">' . $messageAfterRegister . '</div>',
        ]);

        exit();
    }

    public function passwordRecovery(): never {
        $request = Helpers::getRequest(Helpers::METHOD_POST, $_POST);
        $usernameAndEmail = $request->get_param('usernameAndEmail');

        $user = get_user_by('login', $usernameAndEmail);
        if (!$user) {
            $user = get_user_by('email', $usernameAndEmail);
        }

        if (!$user) {
            wp_send_json([
                'status' => false,
                'message' => 'Пользователь с указанным логином или email не существует.',
            ]);
            exit();
        }

        $resetPasswordUrl = wp_lostpassword_url();
        $resetPasswordEmailSent = wp_mail(
            $user->user_email,
            'Сброс пароля',
            'Для сброса пароля пройдите по ссылке: ' . $resetPasswordUrl
        );

        if ($resetPasswordEmailSent) {
            wp_send_json([
                'status' => true,
                'message' => 'Письмо с инструкциями по сбросу пароля отправлено на ваш email.',
            ]);

            exit();
        }

        wp_send_json([
            'status' => false,
            'message' => 'Ошибка при отправке письма с инструкциями по сбросу пароля.',
        ]);

        exit();
    }

    private function existUserName(
        string $username,
    ): array {
        if (empty($username)) {
            return [
                'status' => false,
                'message' => 'Поля не может быть пустым.',
            ];
        }

        if (!validate_username($username)) {
            return [
                'status' => false,
                'message' => 'Имя пользователя должно содержать только латинские буквы или цифры.',
            ];
        }

        if (username_exists($username)) {
            return [
                'status' => false,
                'message' => 'Пользователь с таким именем уже существует.',
            ];
        }

        return [
            'status' => true,
        ];
    }

    private function existEmail(
        string $email,
    ): array {
        if (empty($email)) {
            return [
                'status' => false,
                'message' => 'Поля не может быть пустым.',
            ];
        }

        if (!is_email($email)) {
            return [
                'status' => false,
                'message' => 'Неправильный формат email.',
            ];
        }

        if (email_exists($email)) {
            return [
                'status' => false,
                'message' => 'Пользователь с таким email уже существует.',
            ];
        }

        return [
            'status' => true,
        ];
    }

    private function checkValidPassword(
        string $password,
    ): array {
        if (strlen($password) < 6) {
            return [
                'status' => false,
                'message' => 'Пароль должен содержать не менее 6 символов.',
            ];
        }

        return [
            'status' => true,
        ];
    }

    private function sendConfirmEmail(
        int $userId,
        string $email,
    ): void {
        $activation_key = wp_generate_password(20, false);
        update_user_meta($userId, 'activation_key', $activation_key);
        $activation_link = add_query_arg([
            'key' => $activation_key,
            'user' => $userId
        ], wp_login_url());
        $message = "Добро пожаловать!\n\nПожалуйста, активируйте ваш аккаунт, перейдя по следующей ссылке:\n\n$activation_link";
        wp_mail($email, 'Активация аккаунта', $message);
    }

    public static function isLogin(): void
    {
        if (is_user_logged_in()) {
            wp_redirect(home_url());
            exit;
        }
    }

    public function adminAccess(): void
    {
        if (current_user_can('subscriber') && !(defined('DOING_AJAX') && DOING_AJAX)) {
            wp_redirect(home_url());
            exit;
        }
    }
}