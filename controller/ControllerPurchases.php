<?php

require_once "framework/Controller.php";
require_once "framework/View.php";
require_once "model/Item.php";
require_once "model/ItemPicture.php";
require_once "utils/AppTime.php";


class ControllerPurchases extends Controller {

    public function index(): void {

        $user = $this->get_user_or_redirect();
        $now = AppTime::get_current_datetime();

        $items = $user->get_purchased_items($now);
        $stats = $user->get_purchases_statistics($now);

        (new View("purchases"))->show([
            "items" => $items,
            "stats" => $stats,
            "currentUser" => $user,

            'page_css' => ['purchases.css'],
            "header_title"        => "My purchases",
            "header_icon"         => "bi-cart4",
            "header_subtitle"     => "Track the gear you've successfully secured.",
            "back_url" => "profile",
            "header_right_icon"   => "bi-bag-check",
            "header_right_text"   => $stats["count"] . " purchases"
        ]);


    }
}

