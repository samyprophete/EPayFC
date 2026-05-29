<?php
require_once "framework/Controller.php";
require_once "framework/View.php";
require_once "model/Item.php";
require_once "model/User.php";
require_once "utils/AppTime.php";
require_once "utils/format.php";

class ControllerOpenItem extends Controller {

    
    public function index(): void {

        $item = $this->load_item_or_fail();

         if ($item === null) return;
        $context = $this->get_user_context($item);
        
        if (!$this->check_access($item, $context)) {
            return;
        }
        
        $data = $this->prepare_view_data($item, $context);
        (new View("open_item"))->show($data);
    }
    
private function load_item_or_fail(): ?Item {
    $item_id = $_GET['param1'] ?? null;
    
    if (!$item_id || !ctype_digit($item_id)) {
        $this->show_error("Invalid url : '$item_id'", "Invalid Request");
        return null;
    }
    
    $item = Item::get_by_id((int)$item_id);
    if ($item === false) {
        $this->show_error("Item #$item_id not found.", "Item Not Found");
        return null;
    }
    
    return $item;
}
    
    private function get_user_context(Item $item): array {
        $current_user = $this->get_user_or_false();
        $current_user_id = $current_user ? $current_user->get_Id() : null;
        $is_open = $item->is_open();
        
        $is_owner = $current_user_id && $item->get_owner() == $current_user_id;
        $is_highest_bidder = $current_user_id 
            ? $item->is_user_highest_bidder($current_user_id) 
            : false;
        
        $has_bids_time = $item->has_bids_time();
        $max_bid_time = $item->get_max_bid_time();
        $is_sold = $has_bids_time || $item->has_buy_now_reached_time();
        
        return [
            'current_user' => $current_user,
            'current_user_id' => $current_user_id,
            'is_owner' => $is_owner,
            'is_open' => $is_open,
            'is_highest_bidder' => $is_highest_bidder,
            'has_bids_time' => $has_bids_time,
            'max_bid_time' => $max_bid_time,
            'is_sold' => $is_sold,
        ];
    }
    
    private function check_access(Item $item, array $context): bool {
        /*
        if (!$context['is_open'] && !$context['is_owner'] && !$context['is_highest_bidder']) {
            $this->show_error("This item is only available to the owner or the winner.", "Access denied");
            return false;
        }
        */
        return true;
        
    }
    private function get_button_state(array $context): array {
        $show_buttons = true;
        $buttons_disabled = false;
        
        if (!$context['is_open']) {
            $show_buttons = false;
        } elseif ($context['is_owner'] || !$context['current_user']) {
            $buttons_disabled = true;
        }
        
        return [
            'show_buttons' => $show_buttons,
            'buttons_disabled' => $buttons_disabled,
        ];
    }
    
    private function get_status_message(Item $item, array $context): string {
        if ($context['is_open']) {
            if ($context['is_owner']) {
                return "You cannot bid on your own listing.";
            }
            if (!$context['current_user']) {
                return "Please log in";
            }
            return '';
        }
        
        if ($context['is_highest_bidder']) {
            $final_price = $context['max_bid_time'] ?? 0;
            return "Congratulations! You purchased this item for " . format_euro($final_price);
        }
        
        if ($context['is_owner']) {
            if ($context['is_sold']) {
                $pseudo = $item->get_highest_bidder_pseudo();
                return "$pseudo won this item for " . format_euro($context['max_bid_time']);
            }
            return "This listing ended without a buyer.";
        }

        if ($context['is_sold'])
            return "This item is sold.";
        
        return '';
    }
    
    private function get_picture_data(Item $item): array {
        $pictures = ItemPicture::get_all_by_item($item->get_Id());
        $selected_img = isset($_GET['param3']) && $_GET['param3'] !== ''
            ? (int) $_GET['param3']
            : 0;
        
        if ($selected_img < 0 || (count($pictures) > 0 && $selected_img >= count($pictures))) {
            $selected_img = 0;
        }
        
        $main_picture_path = null;
        if (!empty($pictures)) {
            if ($selected_img === 0) {
                $main_pic = ItemPicture::get_main_picture($item->get_Id());
                $main_picture_path = $main_pic !== null 
                    ? $main_pic->picture_path 
                    : $pictures[0]->picture_path;
            } else {
                $main_picture_path = $pictures[$selected_img]->picture_path 
                    ?? $pictures[0]->picture_path;
            }
        }
        
        return [
            'pictures' => $pictures,
            'selected_img' => $selected_img,
            'main_picture_path' => $main_picture_path,
        ];
    }
    public function pictures_service(): void {
        $item_id = $_GET['param1'] ?? null;
        if (!$item_id || !ctype_digit($item_id)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid item ID']);
            return;
        }


        $item = Item::get_by_id((int)$item_id);
        if ($item === false) {
            http_response_code(404);
            echo json_encode(['error' => 'Item not found']);
            return;
        }
   
        $pictures = ItemPicture::get_all_by_item($item->get_Id());
        $result = [];
        foreach ($pictures as $pic) {
            $result[] = [
                'path' => $pic->picture_path,
                'thumbnail' => str_replace('.jpg', '_thumbnail.jpg', $pic->picture_path),
                'priority' => $pic->priority
            ];
        }
    
        header('Content-Type: application/json');
        echo json_encode($result);
    }
    
    private function prepare_view_data(Item $item, array $context): array {
        $button_state = $this->get_button_state($context);
        $picture_data = $this->get_picture_data($item);
        $status_message = $this->get_status_message($item, $context);
        
        $raw_state = (string) ($_GET['param2'] ?? '');
        if ($raw_state === '0') {
            $raw_state = '';
        }
        $known_origins = ['browser', 'my_items', 'sales', 'purchases'];
        $decoded = [];
        $from = 'browser';
        $search_state = '';
        if ($raw_state !== '' && in_array($raw_state, $known_origins, true)) {
            // literal origin passed in URL: /open_item/index/{id}/sales
            $from = $raw_state;
        } elseif ($raw_state !== '') {
            // encoded state (browser/my_items search token)
            $tmp = Tools::url_safe_decode($raw_state);
            if (is_array($tmp)) {
                $decoded = $tmp;
                $from = (string) ($decoded['from'] ?? 'browser');
                $search_state = $raw_state; // keep token for propagation
            }
        }
        switch ($from) {
            case 'my_items':
                $back_url = 'my_items/index' . ($search_state !== '' ? '/' . $search_state : '');
                break;
            case 'sales':
                $back_url = 'sales/index';
                break;
            case 'purchases':
                $back_url = 'purchases/index';
                break;
            default:
                $back_url = 'browser/index' . ($search_state !== '' ? '/' . $search_state : '');
                break;
        }
        
        $state_suffix = ($search_state !== '') ? '/' . urlencode($search_state) : '';

         $is_open = $context['is_open'];
        $has_bids_time = $context['has_bids_time'];
        $is_sold = $context['is_sold'];
         $is_direct_sale_only = $item->get_Is_Direct_Sale() && !$item->get_Is_Auction();
         $btn_class = $is_direct_sale_only ? 'btn-place-bid' : 'btn-buy-now';
        $btn_text = $is_direct_sale_only ? 'BUY NOW' : 'Buy Now at ' . format_euro($item->get_Buy_Now_Price());


        return [
            'header_title' => 'Item open',
            'header_icon' => 'bi-cart-fill',
            'back_url' => $back_url,
            'is_open' => $is_open,
            'item' => $item,
            'item_id' => $item->get_Id(),
            'item_title' => $item->get_Title(),
            'item_description' => $item->get_Description(),
            'item_created_at' => $item->get_Created_At(),
            'item_end_at' => $item->get_End_At(),
            'item_categories' => $item->get_categories(),

            'pictures' => $picture_data['pictures'],
            'selected_img' => $picture_data['selected_img'],
            'main_picture_path' => $picture_data['main_picture_path'],
            'seller' => $item->get_seller(),
            'seller_pseudo' => $item->get_seller()->get_Pseudo(),
            'seller_thumbnail_path' => $item->get_seller()->get_Thumbnail_Path(),
            'seller_has_picture' => $item->get_seller()->has_Picture(),
            'bids' => $item->get_bids(),

               'search_state' => $search_state,
            'state_suffix' => $state_suffix,

            'is_owner' => $context['is_owner'],
            'current_user' => $context['current_user'],
            'is_highest_bidder' => $context['is_highest_bidder'],
            'max_bid_time' => $context['max_bid_time'],
            'min_bid_amount' => $item->get_min_bid_amount(),
            'has_bids_time' => $has_bids_time,
            'show_buttons' => $button_state['show_buttons'],
            'buttons_disabled' => $button_state['buttons_disabled'],
            'has_buy_now_price' => $item->get_Has_buy_now_price(),
            'buy_now_price' => $item->get_Buy_Now_Price(),
            'status_message' => $status_message,
            'item_purchased' => !$is_open && $is_sold,
            'is_auction' => $item->get_Is_Auction(),
            'starting_bid' => $item->get_Starting_Bid(),
            'auction_ended' => !$is_open && $item->get_Is_Auction(),
            'can_modify' => !$has_bids_time,




            'buy_now_btn_class' => $btn_class,
            'buy_now_btn_text'  =>  $btn_text,
     
         
            'page_css' => ['open_item.css'],
            'page_js' => ['open_item.js']
        ];
    }
}
