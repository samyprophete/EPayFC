<?php
require_once "framework/Controller.php";
require_once 'framework/View.php';
require_once 'utils/AppTime.php';
require_once 'model/Item.php';
require_once 'model/ItemPicture.php';
require_once 'model/User.php';
require_once 'model/Category.php';



class ControllerBrowser extends Controller
{

    public function index(): void
    {
        $current_user = $this->get_user_or_false();
        $current_user_id = $current_user ? $current_user->get_Id() : -1;
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

        $search_query = $initial_query;
        if ($current_user) {
            $participating_items_raw = $current_user->get_participating_items($now, $search_query, $category_id);
            $available_items_raw = $current_user->get_available_items($now, $search_query, $category_id);
        } else {
            $participating_items_raw = Item::get_Item_Participating(-1, $now, $search_query, $category_id);
            $available_items_raw = Item::get_Item_Available(-1, $now, $search_query, $category_id);
        }


        $participating_items = [];
        foreach ($participating_items_raw as $item) {
            if (!$item instanceof Item)
                continue;

            $main_picture = $item->get_main_picture();
            $seller_pseudo = $item->get_seller()->get_Pseudo();

            $participating_items[] = [
                'id' => $item->get_Id(),
                'title' => $item->get_Title(),
                'pic_path' => $main_picture?->picture_path,
                'picture_count' => $item->get_picture_count(),
                'seller_pseudo' => $seller_pseudo,
                'buy_now_price' => $item->get_Buy_Now_Price(),
                'starting_bid' => $item->get_Starting_Bid(),
                'max_bid' => $item->get_max_bid_time(),
                'end_at' => $item->get_End_At(),
                'time_remaining' => $this->calculate_time_remaining($item->get_End_At()),
                'is_auction' => $item->get_Is_Auction(),
                'has_buy_now' => $item->get_Has_buy_now_price(),
                'is_highest_bidder' => $item->is_user_highest_bidder($current_user_id),
                'has_bid' => $item->user_has_bid($current_user_id),
                'is_owner' => $item->get_owner(),
                'description' => $item->get_Description()
            ];
        }

        $available_items = [];
        foreach ($available_items_raw as $item) {
            if (!$item instanceof Item)
                continue;

            $mainPicture = $item->get_main_picture();
            $sellerPseudo = $item->get_seller()->get_Pseudo();

            $available_items[] = [
                'id' => $item->get_Id(),
                'title' => $item->get_Title(),
                'pic_path' => $mainPicture?->picture_path,
                'picture_count' => $item->get_picture_count(),
                'seller_pseudo' => $sellerPseudo,
                'buy_now_price' => $item->get_Buy_Now_Price(),
                'starting_bid' => $item->get_Starting_Bid(),
                'max_bid' => $item->get_max_bid_time(),
                'end_at' => $item->get_End_At(),
                'time_remaining' => $this->calculate_time_remaining($item->get_End_At()),
                'is_auction' => $item->get_Is_Auction(),
                'has_buy_now' => $item->get_Has_buy_now_price(),
                'is_direct_sale' => $item->get_Is_Direct_Sale(),
                'is_owner' => $item->get_owner(),
                'description' => $item->get_Description()
            ];
        }


        usort($participating_items, fn($a, $b) => strcmp($a['end_at'], $b['end_at']));
        usort($available_items, fn($a, $b) => strcmp($a['end_at'], $b['end_at']));

        (new View("browser"))->show([
            'participating_items' => $participating_items,
            'available_items' => $available_items,
            'current_user_id' => $current_user_id,
            'search_state' => $search_state,
            'initial_query' => $initial_query,
            'category_id' => $category_id,
            'categories' => Category::get_all(),
            'currentUser' => $current_user,
            'list_origin' => 'browser',
            'header_title' => 'Browser',
            'header_icon' => 'bi-cart-fill',
            'page_css' => ['browser.css'],
            'page_js' => ['search_filter.js']
        ]);

    }

    private function calculate_time_remaining(string $end_at): string
    {
        $now = new DateTime(AppTime::get_current_datetime());
        $end = new DateTime($end_at);

        if ($end <= $now) {
            return "0d 0h";
        }

        $diff = $now->diff($end);

        $days = $diff->days;
        $hours = $diff->h;

        return $days . "d " . $hours . "h";
    }
}