<?php

require_once "framework/Controller.php";
require_once "framework/View.php";
require_once "model/User.php";

class ControllerLogin extends Controller {

    public function index() : void {

        if ($this->user_logged()) {
            $this->redirect("browser");
        }

        (new View("login"))->show([
            "no_header_footer" => true,
            "header_title" => "Login",
            "page_css" => ["login.css"],
            "mail" => "",
            "errors" => []
        ]);
    }

    public function login(): void {

        if ($this->user_logged()) {
            $this->redirect("browser");
        }

        $mail = $_POST["mail"] ?? "";
        $password = $_POST["password"] ?? "";

        if ($mail === "" || $password === "") {
            (new View("login"))->show([
                "no_header_footer" => true,
                "header_title" => "Login",
                "page_css" => ["login.css"],
                "mail" => $mail,
                "errors" => ["Mail and Password are required."]
            ]);
            return;
        }

        $user = User::get_user_by_mail($mail);
        if (!$user) {
            (new View("login"))->show([
                "no_header_footer" => true,
                "header_title" => "Login",
                "page_css" => ["login.css"],
                "mail" => $mail,
                "errors" => ["Unknown user."]
            ]);
            return;
        }

        if (!$user->check_password($password)) {
            (new View("login"))->show([
                "no_header_footer" => true,
                "header_title" => "Login",
                "page_css" => ["login.css"],
                "mail" => $mail,
                "errors" => ["Incorrect Pasword."]
            ]);
            return;
        }

        $this->log_user($user, "browser");
        return;
    }

    public function login_as(): void {

        if (!Configuration::is_dev()) {
            $this->redirect("login");
        }

        if ($this->user_logged()) {
            $this->redirect("browser");
        }

        $mail = $_GET['param1'] ?? "";

        if ($mail) {
            $user = User::get_user_by_mail($mail);
            if ($user) {
                $this->log_user($user, "browser");
            }
        }

        $this->redirect("login");
    }

}
