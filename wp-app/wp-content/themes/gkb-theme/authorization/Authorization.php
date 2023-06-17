<?php

namespace authorization;

use helpers\Helpers;

class Authorization
{
    public function register(): void
    {
        add_action('wp_ajax_registration_user', [$this, 'registrationUser']);
        add_action('wp_ajax_nopriv_registratio_user', [$this, 'registrationUser']);
    }

    public function registrationUser(): void {
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
        wp_send_json([
            'status' => true,
            'username' => $existUserName,
            'email' => $existEmail,
            'password' => $validPassword,
            'redirectUrl' => sprintf('%s/registration-confirm-email', get_home_url()),
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


    public function isLoginUser(): void
    {
        if (is_user_logged_in()) {
            wp_redirect(home_url());
            exit;
        }
    }
}