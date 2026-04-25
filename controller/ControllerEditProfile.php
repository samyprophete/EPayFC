<?php
require_once 'Model/User.php';
require_once "framework/Controller.php";
require_once 'framework/View.php';
require_once 'framework/Model.php';

class ControllerEditProfile extends Controller
{
    public function index(): void
    {
        $current_user = $this->get_user_or_false();
        if (!$current_user) {
            $this->redirect("profile");
            return;
        }

        $current_user_id = $current_user->get_Id();

        (new View("edit_profile"))->show([
            'current_user_id' => $current_user_id,
            'currentUser' => $current_user,
            'user' => [
                'full_name' => $current_user->full_name,
                'pseudo' => $current_user->pseudo,
                'email' => $current_user->get_email(),
                'iban' => $current_user->iban ?? ''
            ],
            'header_title' => 'Edit Profile',
            'header_icon' => 'bi-person-fill',
            'back_url' => 'profile',
            'header_right_icon' => 'bi-floppy',
            'header_right_form_id' => 'edit-form',
            'errors' => [],
            'success' => false,
            'page_css' => ['edit_Profile.css'],
            'page_js' => ['user_validation.js'],
        ]);
    }

    public function save(): void
    {
        $current_user = $this->get_user_or_false();
        if (!$current_user) {
            $this->redirect("profile");
            return;
        }

        $user_id = $current_user->get_Id();
        $full_name = $_POST['full_name'] ?? '';
        $pseudo = $_POST['pseudo'] ?? '';
        $email = $_POST['email'] ?? '';
        $iban = $_POST['iban'] ?? '';

        $errors = [];

        if (empty(trim($full_name))) {
            $errors[] = "Full name is required";
        }
        if (empty(trim($pseudo))) {
            $errors[] = "Username is required";
        }
        if (empty(trim($email))) {
            $errors[] = "Email is required";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }
         if (!empty(trim($full_name)) && ModelEditProfile::is_full_name_taken(trim($full_name), $user_id)) {
            $errors[] = "Full name is already taken by another user.";
        }
        if (!empty(trim($pseudo)) && ModelEditProfile::is_pseudo_taken(trim($pseudo), $user_id)) {
            $errors[] = "Username is already taken by another user.";
        }
       
        if (!empty(trim($email)) && ModelEditProfile::is_email_taken(trim($email), $user_id)) {
            $errors[] = "Email is already used by another user.";
        }
        if (empty($errors)) {
            ModelEditProfile::update_user($user_id, $full_name, $pseudo, $email, $iban);
            $this->redirect("profile");
            return;
        }

        (new View("edit_profile"))->show([
            'user' => [
                'id' => $user_id,
                'full_name' => $full_name,
                'pseudo' => $pseudo,
                'email' => $email,
                'iban' => $iban
            ],
            'errors' => $errors,
            'success' => false,
            'header_title' => 'Edit Profile',
            'header_icon' => 'bi-person-fill',
        ]);
    }

    public function check_availability_service(): void {
        $user = $this->get_user_or_redirect();
        header('Content-Type: application/json');
        
        $email = trim($_POST['email'] ?? '');
        $pseudo = trim($_POST['pseudo'] ?? '');
        $full_name = trim($_POST['full_name'] ?? '');

        echo json_encode([
            'email_available' => $email === '' || !ModelEditProfile::is_email_taken($email, $user->id),
            'pseudo_available' => $pseudo === '' || !ModelEditProfile::is_pseudo_taken($pseudo, $user->id),
            'full_name_available' => $full_name === '' || !ModelEditProfile::is_full_name_taken($full_name, $user->id)
        ]);
    }
}

class ModelEditProfile extends \Model
{
    public static function get_user_data(int $user_id): ?array
    {
        $query = self::execute("SELECT * FROM users WHERE id = :id", ['id' => $user_id]);
        $data = $query->fetch();
        return $data ?: null;
    }

    public static function is_pseudo_taken(string $pseudo, int $user_id): bool
    {
        $query = self::execute("SELECT COUNT(*) FROM users WHERE pseudo = :pseudo AND id != :id", ['pseudo' => $pseudo, 'id' => $user_id]);
        return (int)$query->fetchColumn() > 0;
    }

    public static function is_email_taken(string $email, int $user_id): bool
    {
        $query = self::execute("SELECT COUNT(*) FROM users WHERE email = :email AND id != :id", ['email' => $email, 'id' => $user_id]);
        return (int)$query->fetchColumn() > 0;
    }

    public static function is_full_name_taken(string $full_name, int $user_id): bool
    {
        $query = self::execute("SELECT COUNT(*) FROM users WHERE full_name = :full_name AND id != :id", ['full_name' => $full_name, 'id' => $user_id]);
        return (int)$query->fetchColumn() > 0;
    }

    public static function update_user(int $user_id, string $full_name, string $pseudo, string $email, string $iban): bool
    {
        $query = self::execute(
            "UPDATE users SET full_name = :full_name, pseudo = :pseudo, email = :email, iban = :iban WHERE id = :id",
            [
                'id' => $user_id,
                'full_name' => $full_name,
                'pseudo' => $pseudo,
                'email' => $email,
                'iban' => $iban ?: null
            ]
        );
        return $query !== false;
    }
}