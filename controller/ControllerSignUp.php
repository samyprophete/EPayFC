<?php

require_once "framework/Controller.php";
require_once "framework/View.php";
require_once "model/User.php";

class ControllerSignup extends Controller
{
    public function index(): void
    {
        $current_user = $this->get_user_or_false();
        if ($current_user) {
            $this->redirect("profile");
            return;
        }

        $errors = [
            'email' => [],
            'full_name' => [],
            'pseudo' => [],
            'password' => [],
            'password_confirm' => []
        ];

        $email = "";
        $full_name = "";
        $pseudo = "";

        if (!empty($_POST)) {
            $email = trim($_POST["email"] ?? "");
            $full_name = trim($_POST["full_name"] ?? "");
            $pseudo = trim($_POST["pseudo"] ?? "");
            $password = $_POST["password"] ?? "";
            $password_confirm = $_POST["password_confirm"] ?? "";

            $errors = User::validate_signup($email, $full_name, $pseudo, $password, $password_confirm);

            $has_errors = false;
            foreach ($errors as $field_errors) {
                if (!empty($field_errors)) {
                    $has_errors = true;
                    break;
                }
            }

            if (!$has_errors) {
                $user = User::signup($email, $full_name, $pseudo, $password);

                if ($user) {
                    $this->log_user($user);
                    $this->redirect("profile");
                    return;
                } else {
                    $errors['email'][] = "Registration failed. Please try again.";
                }
            }
        }

        (new View("signup"))->show([
            "no_header_footer" => true,
            "header_title" => "Sign Up",
            "page_css" => ["sign_up.css"],
            "page_js" => ["user_validation.js"],
            "email" => $email,
            "full_name" => $full_name,
            "pseudo" => $pseudo,
            "errors" => $errors
        ]);
    }

    public function register(): void
    {
        $this->index();
    }

    
    public function check_availability_service(): void {
        header('Content-Type: application/json');
        $email = trim($_POST['email'] ?? '');
        $pseudo = trim($_POST['pseudo'] ?? '');
        $full_name = trim($_POST['full_name'] ?? '');

        echo json_encode([
            'email_available' => $email === '' || !User::email_exists($email),
            'pseudo_available' => $pseudo === '' || !User::pseudo_exists($pseudo),
            'full_name_available' => $full_name === '' || !User::full_name_exists($full_name)
        ]);
    }
}