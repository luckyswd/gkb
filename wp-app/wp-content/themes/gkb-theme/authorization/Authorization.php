<?php

namespace authorization;

use helpers\Helpers;

class Authorization
{
    private Helpers $helpers;
    private string $language;

    public function __construct()
    {
        $this->helpers = new Helpers();
        $this->language = $this->helpers->getLang();
    }

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
        $messages = $this->getMessages('login');

        $user = get_user_by('login', $usernameAndEmail);
        $statusVerifiedEmail = get_user_meta($user->ID, 'email_confirmation_status', 'verified');
        if ($user && $statusVerifiedEmail !== 'verified' && $user->ID !== 1) {
            wp_send_json([
                'status' => false,
                'message' => $messages['email_confirm_text'],
            ]);

            wp_die();
        }

        $user = wp_authenticate($usernameAndEmail, $password);

        if (is_wp_error($user)) {
            wp_send_json([
                'status' => false,
                'message' => $messages['login_error_text'],
            ]);

            wp_die();
        }

        wp_set_auth_cookie( $user->ID );
        wp_send_json([
            'status' => true,
            'redirectAfterLogin' => home_url(),
        ]);

        wp_die();
    }

    public function logout(): never {
        wp_logout();

        wp_send_json([
            'status' => true,
        ]);

        wp_die();
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

            wp_die();
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

            wp_die();
        }

        $this->sendConfirmEmail($userId, $email);
        $messages = $this->getMessages('registration');

        wp_send_json([
            'status' => true,
            'username' => $existUserName,
            'email' => $existEmail,
            'password' => $validPassword,
            'messageAfterRegister' => '<div class="message-after-register">' . $messages['success_registration'] . '</div>',
        ]);

        wp_die();
    }

    public function passwordRecovery(): never {
        $request = Helpers::getRequest(Helpers::METHOD_POST, $_POST);
        $usernameAndEmail = $request->get_param('usernameAndEmail');

        $user = get_user_by('login', $usernameAndEmail);
        if (!$user) {
            $user = get_user_by('email', $usernameAndEmail);
        }
        $messages = $this->getMessages('password_recovery');

        if (!$user) {
            wp_send_json([
                'status' => false,
                'message' => $messages['user_not_exist'],
            ]);
            wp_die();
        }

        $resetPasswordUrl = wp_lostpassword_url();
        $resetPasswordEmailSent = wp_mail(
            $user->user_email,
            $messages['subject_email_password'],
            $messages['email_password_text'] . $resetPasswordUrl
        );

        if ($resetPasswordEmailSent) {
            wp_send_json([
                'status' => true,
                'message' => $messages['email_password_success'],
            ]);

            wp_die();
        }

        wp_send_json([
            'status' => false,
            'message' => $messages['email_error'],
        ]);

        wp_die();
    }

    private function existUserName(
        string $username,
    ): array {
        $messages = $this->getMessages('registration');

        if (empty($username)) {
            return [
                'status' => false,
                'message' =>  $messages['empty_field'],
            ];
        }

        if (!validate_username($username)) {
            return [
                'status' => false,
                'message' => $messages['validate_username'],
            ];
        }

        if (username_exists($username)) {
            return [
                'status' => false,
                'message' => $messages['username_exists'],
            ];
        }

        return [
            'status' => true,
        ];
    }

    private function existEmail(
        string $email,
    ): array {
        $messages = $this->getMessages('registration');

        if (empty($email)) {
            return [
                'status' => false,
                'message' =>  $messages['empty_field'],
            ];
        }

        if (!is_email($email)) {
            return [
                'status' => false,
                'message' => $messages['email_invalid'],
            ];
        }

        if (email_exists($email)) {
            return [
                'status' => false,
                'message' => $messages['email_exists'],
            ];
        }

        return [
            'status' => true,
        ];
    }

    private function checkValidPassword(
        string $password,
    ): array {
        $messages = $this->getMessages('registration');

        if (strlen($password) < 6) {
            return [
                'status' => false,
                'message' => $messages['invalid_long_password'],
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
        $messages = $this->getMessages('registration');
        $activation_key = wp_generate_password(20, false);
        update_user_meta($userId, 'activation_key', $activation_key);
        $activation_link = add_query_arg([
            'key' => $activation_key,
            'user' => $userId
        ], wp_login_url());

        wp_mail($email, $messages['subject_email_active'], sprintf('%s %s', $messages['email_body_active_text'], $activation_link));
    }

    private function getMessages(
        string $field,
    ): array {
       return $this->helpers->get_field_multi_lang($this->language, $field, 'option');
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