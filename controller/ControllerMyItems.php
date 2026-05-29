<?php

require_once "framework/Controller.php";
require_once "framework/View.php";
require_once "model/Item.php";
require_once "utils/AppTime.php";
require_once "model/ItemPicture.php";
require_once "model/Category.php";


class ControllerMyItems extends Controller
{

    public function index(): void
    {
        $user = $this->get_user_or_redirect();
        $now = AppTime::get_current_datetime();

        $search_state = (string) ($_GET['param1'] ?? '');
        $initial_query = '';
        $category_id = 0;
        
        if ($search_state !== '') {
            $decoded = Tools::url_safe_decode($search_state);
            if (is_array($decoded)) {
                $initial_query = trim((string) ($decoded['q'] ?? $decoded['query'] ?? ''));
                $category_id = (int) ($decoded['category'] ?? 0);
            }
        }

        $grouped = $user->get_my_items_grouped($now, $initial_query, $category_id);

        (new View("my_items"))->show([
            "active_items"        => $grouped['active'],
            "closed_unsold_items" => $grouped['closed_unsold'],
            "sold_items"          => $grouped['sold'],
            "currentUser"         => $user,
            "current_page"        => "my_items",
            "header_title"        => "My items",
            "back_url"            => "browser",
            "list_origin" => "my_items",
            "search_state" => $search_state,
            "initial_query" => $initial_query,
            "category_id" => $category_id,
            "categories" => Category::get_all(),
            "page_css"            => ["my_items.css"],
            "page_js"             => ["search_filter.js"],
        ]);
    }

    private function format_items_for_json(array $raw_items): array
    {
        $result = [];
        foreach ($raw_items as $item) {
            if (!$item instanceof Item)
                continue;
            $pic = $item->get_main_picture();
            $result[] = [
                'id' => $item->get_Id(),
                'title' => $item->get_Title(),
                'seller' => $item->get_seller()->get_Pseudo(),
                'pic' => $pic ? $pic->picture_path : null,
                'price' => $item->get_Buy_Now_Price() ?? $item->get_Starting_Bid(),
                'time' => $this->calculate_time_remaining($item->get_End_At()),
            ];
        }
        return $result;
    }

    private function calculate_time_remaining(string $end_at): string
    {
        $now = new DateTime(AppTime::get_current_datetime());
        $end = new DateTime($end_at);
        if ($end <= $now)
            return "0d 0h";
        $diff = $now->diff($end);
        return $diff->days . "d " . $diff->h . "h";
    }



}
