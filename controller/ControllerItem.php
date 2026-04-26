<?php

require_once "framework/Controller.php";
require_once "framework/View.php";
require_once "model/Item.php";
require_once "framework/Configuration.php";

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

        $item_id = isset($_GET["param1"]) ? (int)$_GET["param1"] : null;
        $item = $this->load_item_for_edit_or_redirect($item_id, $owner_id);

        $p2 = $_GET['param2'] ?? $_POST['encoded_state'] ?? null;
        $encoded_state = (is_string($p2) && $p2 !== '' && $p2 !== '0') ? $p2 : null;

        $from = $_GET['from'] ?? $_POST['from'] ?? 'my_items';

        if (!empty($_POST)) {
            [$new_item, $view_data] = $this->build_item_from_post($item, $item_id, $owner_id);

            $errors = $new_item->persist();
            if (!empty($errors)) {
                $view_data["errors"]               = $errors;
                $view_data["currentUser"]          = $user;
                $view_data["current_page"]         = "add_item";
                $view_data["header_title"]         = $item_id ? "Edit item" : "Add item";
                $view_data["back_url"]             = $from;
                $view_data["from"]                 = $from;
                $view_data["encoded_state"]        = $encoded_state;
                $view_data["header_right_icon"]    = "bi-floppy";
                $view_data["header_right_form_id"] = "item-form";
                $view_data["page_css"]             = ["add_edit_item.css"];
                $view_data["page_js"]              = ["item_validation.js"];
                (new View("add_edit_item"))->show($view_data);
                return;
            }

            $is_edit = ($item_id !== null && (int) $item_id > 0);
            $fromPost = (string) ($_POST['from'] ?? '');

            if (!$is_edit) {
                $this->redirect("my_items", "index");
            } elseif (strpos($fromPost, "open_item") !== false) {
                $es = trim((string) ($_POST['encoded_state'] ?? ""));
                if ($es !== "") {
                    $this->redirect("open_item", "index", (string) $item_id, $es, "0");
                } else {
                    $this->redirect("open_item", "index", (string) $item_id, "0", "0");
                }
            } else {
                $this->redirect("my_items", "index");
            }
        }

        $view_data = $this->get_add_edit_view_data($item, $item_id);
        $view_data["errors"]               = [];
        $view_data["currentUser"]          = $user;
        $view_data["current_page"]         = "add_item";
        $view_data["header_title"]         = $item_id ? "Edit item" : "Add item";
        $view_data["back_url"]             = $from;
        $view_data["from"]                 = $from;
        $view_data["encoded_state"]        = $encoded_state;
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



    private function load_item_for_edit_or_redirect(?int $item_id, int $owner_id): ?Item {

        if ($item_id === null) {
            return null; 
        }

        $item = Item::get_by_id_for_edit($item_id);

        if ($item === null || $item->get_owner() !== $owner_id) {
            $this->redirect();
        }

       
        if ($item->has_bids_time()) {
            $this->redirect( "open_item", "index", (string)$item_id);
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
            $starting_bid
        );

        return [$item, [
            "item_id" => $item_id,
            "title" => $title,
            "description" => $description ?? "",
            "duration_days" => $duration_days,
            "starting_bid" => $starting_bid_raw,
            "buy_now_price" => $buy_now_raw,
            "sale_price" => $sale_price_raw,
        ]];
    }

        

    private function get_add_edit_view_data(?Item $item, ?int $item_id): array {

        $sale_price = "";
        if ($item && (($item->get_starting_bid() ?? 0) <= 0)) {
            $sale_price = (string)($item->get_buy_now_price() ?? "");
        }

        return [
            "item_id" => $item_id,
            "title" => $item ? $item->get_title() : "",
            "description" => $item ? ($item->get_description() ?? "") : "",
            "duration_days" => $item
                ? $item->get_duration_days()
                : Configuration::get("default_duration_days"),
            "starting_bid" => $item ? (string)($item->get_starting_bid() ?? "") : "",
            "buy_now_price" => $item ? (string)($item->get_buy_now_price() ?? "") : "",
            "sale_price" => $sale_price,
        ];
    }
}
