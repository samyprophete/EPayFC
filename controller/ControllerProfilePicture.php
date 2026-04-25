<?php

require_once "framework/Controller.php";
require_once "framework/View.php";
require_once "model/User.php";

class ControllerProfilePicture extends Controller {

    public function index(): void {
        $user = $this->get_user_or_redirect();

        
        (new View("profile_picture"))->show([
            "currentUser"  => $user,
            "header_title" => "Manage profile picture",
            "header_icon"  => "bi-person-circle",
            "back_url"     => "profile",
            "page_css"     => ["profile_picture.css"],
        ]);

    }

    public function upload(): void {
        $user = $this->get_user_or_redirect();

        if (!isset($_FILES["picture"]) || $_FILES["picture"]["error"] !== UPLOAD_ERR_OK) {
            $this->redirect("profile_picture");
        }

        $file = $_FILES["picture"];

        
        $max_size = (int) Configuration::get("max_profile_picture_size");

        if ($file["size"] > $max_size) {
            $this->redirect("profile_picture");
        }


        
        $allowed = ["image/jpeg", "image/png", "image/webp", "image/gif"];
        $mime = mime_content_type($file["tmp_name"]);

        if (!in_array($mime, $allowed)) {
            $this->redirect("profile_picture");
        }

      
        $ext = match ($mime) {
            "image/jpeg" => "jpg",
            "image/png"  => "png",
            "image/webp" => "webp",
            "image/gif"  => "gif",
        };

       
        $user_dir = "uploads/users/" . $user->get_id();
        if (!is_dir($user_dir)) {
            mkdir($user_dir, 0777, true);
        }

        $path = "$user_dir/profile.$ext";
        $thumb_path = "$user_dir/profile_thumbnail.$ext";

      
        if ($user->get_picture_path()) {
            @unlink($user->get_picture_path());
            @unlink(str_replace("profile.", "profile_thumbnail.", $user->get_picture_path()));
        }

       
        if (!move_uploaded_file($file["tmp_name"], $path)) {
            $this->redirect("profile_picture");
        }

        copy($path, $thumb_path);

        if ($user->get_picture_path()) {
            @unlink($user->get_picture_path());
            @unlink(str_replace("profile.", "profile_thumbnail.", $user->get_picture_path()));
        }

        $user->set_picture_path($path);
        $user->save_picture();

        $_SESSION["user"] = User::get_User_By_Id($user->get_Id());

        $this->redirect("profile_picture");
    }

    public function delete(): void {
        $user = $this->get_user_or_redirect();

        $path = $user->get_picture_path();

        if ($path) {
            @unlink($path);
            $thumb = str_replace("profile.", "profile_thumbnail.", $path);
            @unlink($thumb);
        }

        $user->set_picture_path(null);
        $user->save_picture();

        $this->redirect("profile_picture");
    }

}