<?php
require_once "framework/Controller.php";
require_once "framework/View.php";
require_once "model/Item.php";
require_once "model/ItemPicture.php";
require_once "utils/AppTime.php";

class ControllerSales extends Controller {

    public function index(): void {
        $user = $this->get_user_or_redirect();
        $now  = AppTime::get_current_datetime();

        $items      = $user->get_sold_items($now);
        $statistics = $user->get_sales_statistics($now);

        $stats = [
            "count"        => $statistics['sales_count'],
            "total"        => $statistics['total_revenue'],
            "average"      => $statistics['average_ticket'],
            "loyal_bidder" => $statistics['loyal_bidder'],
        ];

        (new View("sales"))->show([
            "items"              => $items,
            "stats"              => $stats,
            "currentUser"        => $user,
            "page_css"           => ["sales.css"],
            "header_title"       => "My Sales",
            "header_icon"        => "bi-shop",
            "back_url"           => "profile",
            "header_right_icon"  => "bi-bag-check",
            "header_right_text"  => $stats["count"] . " sale" . ($stats["count"] !== 1 ? "s" : ""),
        ]);
    }
}
