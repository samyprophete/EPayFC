<?php

require_once "framework/Controller.php";
require_once "framework/View.php";
require_once "model/Item.php";
require_once "framework/Configuration.php";
require_once "model/Category.php";

class ControllerItem extends Controller {


    public function index(): void {

    $user = $this->get_user_or_false();

    if (!$user) {
        $this->redirect("login");
        return;
    }

    $this->redirect("item", "add_edit_item");

    }



    public function my_items(): void {

        $user = $this->get_user_or_false();
        
        if (!$user) {
            $this->redirect("login");
            return;
        }

        $this->redirect("my_items", "index");
    }




    public function add_edit_item(): void {
        $user = $this->get_user_or_redirect();
        $owner_id = $user->get_id();

        $p2 = $_GET['param2'] ?? $_POST['search_state'] ?? null;
        $search_state = (is_string($p2) && $p2 !== '' && $p2 !== '0') ? $p2 : null;

        $param1 = $_GET['param1'] ?? null;

        $item_id = null;
        if (is_string($param1) && $param1 !== '' && ctype_digit($param1)) {
            $item_id = (int) $param1;
        }


        $item = $this->load_item_for_edit_or_redirect($item_id, $owner_id, $search_state);
        if ($item_id !== null && $item === null) {
            return; 
        }

        $back_url = $this->build_item_back_url($search_state, $item_id);

        if (!empty($errors)) {
    
            $all_categories = Category::get_all_categories();
            (new View("add_edit_item"))->show([
            "errors" => $errors,
            "all_categories" => $all_categories,
        ]);
    
}

        if (!empty($_POST)) {
            [$new_item, $view_data] = $this->build_item_from_post($item, $item_id, $owner_id);

            $errors = [];

            $has_auction = ($view_data["starting_bid"] !== "" || $view_data["buy_now_price"] !== "");
            $has_direct = ($view_data["sale_price"] !== "");

            if ($has_auction && $has_direct) {
                $errors["starting_bid"] = "You cannot select both auction and direct sale options.";
                $errors["buy_now_price"] = "Please choose only one sale option.";
            } else {
                $errors = $new_item->persist();
            }
            if (!empty($errors)) {
                $view_data["all_categories"]       = Category::get_all();
                $view_data["errors"]               = $errors;
                $view_data["currentUser"]          = $user;
                $view_data["current_page"]         = "add_item";
                $view_data["header_title"]         = $item_id ? "Edit item" : "Add item";
                $view_data["back_url"]             = $back_url;

                $view_data["search_state"]        = $search_state;
                $view_data["header_right_icon"]    = "bi-floppy";
                $view_data["header_right_form_id"] = "item-form";
                $view_data["page_css"]             = ["add_edit_item.css"];
                $view_data["page_js"]              = ["item_validation.js"];
                (new View("add_edit_item"))->show($view_data);
                return;
            }

            $is_edit = ($item_id !== null && $item_id > 0);
           
            $this->redirect_after_item_save(
                $is_edit,
                $item_id,
                trim((string) ($_POST['search_state'] ?? '')) ?: $search_state
            );
            return;
        }


        $view_data = $this->get_add_edit_view_data($item, $item_id);
        $view_data["errors"]               = [];
        $view_data["currentUser"]          = $user;
        $view_data["current_page"]         = "add_item";
        $view_data["header_title"]         = $item_id ? "Edit item" : "Add item";
        $view_data["back_url"]             = $back_url;
        $view_data["search_state"]        = $search_state;
        $view_data["header_right_icon"]    = "bi-floppy";
        $view_data["header_right_form_id"] = "item-form";
        $view_data["page_css"]             = ["add_edit_item.css"];
        $view_data["page_js"]              = ["item_validation.js"];

        (new View("add_edit_item"))->show($view_data);
    }



    public function check_title_service(): void
    {
        $user = $this->get_user_or_false();
        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Not authenticated']);
            return;
        }

        header('Content-Type: application/json');

        $title   = trim($_POST['title'] ?? '');
        $item_id = isset($_POST['item_id']) && $_POST['item_id'] !== ''
            ? (int) $_POST['item_id']
            : null;

        if ($title === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Title is required']);
            return;
        }

        $taken = Item::title_exists_for_owner($title, $user->get_Id(), $item_id);
        echo json_encode(['available' => !$taken]);
    }



    private function load_item_for_edit_or_redirect(?int $item_id, int $owner_id,  ?string $search_state = null): ?Item {

        if ($item_id === null) {
            return null; 
        }

        $item = Item::get_by_id_for_edit($item_id);

        if ($item === null || $item->get_owner() !== $owner_id) {
            $this->redirect('my_items');
            return null;
        }

       
        if ($item->has_bids_time()) {
            if ($search_state) {
                $this->redirect('open_item', 'index', (string) $item_id, $search_state);
            } else {
                $this->redirect('open_item', 'index', (string) $item_id);
            }
            return null;
        }

        return $item;
    }



    private function build_item_from_post(?Item $existing_item, ?int $item_id, int $owner_id): array {

        $title = $_POST["title"] ?? "";
        $description = $_POST["description"] ?? null;

        $duration_days = (int)(
            $_POST["duration_days"] ?? Configuration::get("default_duration_days")
        );

        $starting_bid_raw = trim($_POST["starting_bid"] ?? "");
        $buy_now_raw = trim($_POST["buy_now_price"] ?? "");
        $sale_price_raw = trim($_POST["sale_price"] ?? "");
        $is_direct_sale = ($sale_price_raw !== "");


        if ($is_direct_sale) {
            $starting_bid = 0.0;
            $buy_now_price = (float)$sale_price_raw;
        } else {
            $starting_bid = $starting_bid_raw !== "" ? (float)$starting_bid_raw : null;
            $buy_now_price = $buy_now_raw !== "" ? (float)$buy_now_raw : null;
        }


        $created_at = $existing_item === null
            ? AppTime::get_current_datetime()
            : $existing_item->get_created_at();

        $item = new Item(
            $item_id,
            $title,
            $description,
            $owner_id,
            $created_at,
            $buy_now_price,
            $duration_days,
            $starting_bid,
  
        );

        $item -> category_ids = $_POST['categories'] ?? [];

        return [$item, [
            "item_id" => $item_id,
            "title" => $title,
            "description" => $description ?? "",
            "duration_days" => $duration_days,
            "starting_bid" => $starting_bid_raw,
            "buy_now_price" => $buy_now_raw,
            "sale_price" => $sale_price_raw,
            "all_categories" => Category::get_all(),
            "selected_categories" => $item -> category_ids
        ]];
    }

        

    private function get_add_edit_view_data(?Item $item, ?int $item_id): array {

        $starting_bid = "";
        $buy_now_price = "";
        $sale_price = "";

        if ($item !== null) {
            $bn = $item->get_Buy_Now_Price();
            $sb = $item->get_Starting_Bid() ?? 0.0;
        
            $is_persisted_direct = $bn !== null && (float) $sb === 0.0;

            if ($is_persisted_direct) {
                $starting_bid = "";
                $buy_now_price = "";
                $sale_price = (string) $bn;
            } else {
                $starting_bid = (float) $sb === 0.0 ? "" : (string) (float) $sb;
                $buy_now_price = $bn !== null ? (string) (float) $bn : "";
            }
        }

        $selected_cats = [];
        if ($item !== null) {
            foreach ($item->get_categories() as $c) {
                $selected_cats[] = $c->id;
            }
        }

        return [
            "item_id" => $item_id,
            "title" => $item ? $item->get_title() : "",
            "description" => $item ? ($item->get_description() ?? "") : "",
            "duration_days" => $item
                ? $item->get_duration_days()
                : Configuration::get("default_duration_days"),
            "starting_bid" => $starting_bid,
            "buy_now_price" => $buy_now_price,
            "sale_price" => $sale_price,
            "all_categories" => Category::get_all(),
            "selected_categories" => $selected_cats
        ];
    }
    private function build_item_back_url( ?string $search_state, ?int $item_id): string{

      
        if ($item_id !== null) {
            if ($search_state !== null && $search_state !== '') {
                return 'open_item/index/' . $item_id . '/' . rawurlencode($search_state);
            }
            return 'open_item/index/' . $item_id;
        }

   
        if ($search_state) {
            return 'my_items/index/' . rawurlencode($search_state);
        }

        return 'my_items/index';
}


    private function redirect_after_item_save(
        bool $is_edit,
        ?int $item_id,
        ?string $search_state
    ): void {
        $es = ($search_state !== null && $search_state !== '') ? $search_state : '';

        // Nouveau item
        if (!$is_edit) {
            if ($es !== '') {
                $this->redirect('my_items', 'index', $es);
            } else {
                $this->redirect('my_items', 'index');
            }
            return;
        }

        // Edit depuis open_item
        if ($is_edit && $item_id !== null) {
            if ($es !== '') {
                $this->redirect('open_item', 'index', (string) $item_id, $es);
            } else {
                $this->redirect('open_item', 'index', (string) $item_id);
            }
            return;
        }
/*
        // Edit depuis my_items 
        if ($es !== '') {
            $this->redirect('my_items', 'index', $es);
        } else {
            $this->redirect('my_items', 'index');
        }
            */
    }
}
