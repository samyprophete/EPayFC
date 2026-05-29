<?php
require_once "framework/Controller.php";
require_once "model/Item.php";
require_once "model/Bid.php";
require_once "model/User.php"; 
require_once "utils/AppTime.php";
require_once "utils/format.php";
class ControllerBid extends Controller {


    public function index(): void {
        $this->redirect("browser");
    }




    public function create(): void {
        $current_user = $this->get_user_or_redirect_login();
        if (!$current_user) return;
        
        $params = $this->extract_bid_params();
        if (!$params) return;
        
        $item = $this->load_item_or_redirect($params['item_id']);
        if (!$item) return;
        
        $this->process_bid($item, $current_user, $params['amount'], $params['search_state'] ?? '');
    }
    
    
    private function get_user_or_redirect_login(): ?object {
        $current_user = $this->get_user_or_false();
        if (!$current_user) {
            $is_ajax = ($_POST['format'] ?? '') === 'json';
            if ($is_ajax) {
                header('Content-Type: application/json');
                http_response_code(401);
                echo json_encode(['success' => false, 'errors' => ['auth' => 'Not authenticated']]);
                exit;
            }
            $this->redirect("login");
            return null;
        }
        return $current_user;
    }
    
    private function extract_bid_params(): ?array {
        $item_id = isset($_POST['item_id']) ? (int)$_POST['item_id'] : null;
        $amount_str = $_POST['amount'] ?? null;
        
        $amount = null;
        if ($amount_str) {
            $amount_str = str_replace(',', '.', $amount_str);
            $amount = (float)$amount_str;
        }
        $search_state = trim((string) ($_POST['search_state'] ?? ''));

        if (!$item_id ||  $amount === null) {
            if ($item_id) {
                if ($search_state !== '') {
                    $this->redirect("open_item", "index", (string) $item_id, $search_state);
                } else {
                    $this->redirect("open_item", "index", (string) $item_id);
                }
            } else {
                $this->redirect("browser");
            }
            return null;
        }
        
        
        return ['item_id' => $item_id, 'amount' => $amount,  'search_state' => $search_state];
    }
    
    private function load_item_or_redirect(int $item_id): ?Item {
        $item = Item::get_by_id($item_id);
        if ($item === false) {
            $es = trim((string) ($_POST['search_state'] ?? ''));
            if ($es !== '') {
                $this->redirect("open_item", "index", (string) $item_id, $es);
            } else {
                $this->redirect("open_item", "index", (string) $item_id);
            }
            return null;
        }
        return $item;
    }
    
    private function process_bid(Item $item, object $user, float $amount, string  $search_state = ''): void {

        $is_ajax = (($_POST['format'] ?? '') === 'json');
        $search_state = trim((string) ($_POST['search_state'] ?? ''));

        if ($this->is_duplicate_bid($item->get_Id(), $user->get_id(), $amount)) {
            if ($is_ajax) {
                header('Content-Type: application/json');
                http_response_code(429);
                echo json_encode(['success' => false, 'errors' => ['Please wait before bidding again.']]);
                return;
            }
            if ($search_state !== '') {
                $this->redirect('open_item', 'index', (string) $item->get_Id(), $search_state);
            } else {
                $this->redirect('open_item', 'index', (string) $item->get_Id());
            }
            return;
        }
        $bid = new Bid($item->get_Id(), $user->get_id(), $amount);
        $errors = $bid->persist($item);
        if ($is_ajax) {
            header('Content-Type: application/json');
            if (!empty($errors)) {
                http_response_code(422);
                echo json_encode(['success' => false, 'errors' => $errors]);
                return;
            }
            echo json_encode([
                'success' => true,
                'message' => 'You successfully bidded ' . format_euro($amount)
            ]);
            return;
        }

            //sans Js --> redirect
        if ($search_state !== '') {
            $this->redirect("open_item", "index", (string) $item->get_Id(), $search_state);
        } else {
            $this->redirect("open_item", "index", (string) $item->get_Id());
        }
        return;
        
    }

    private function is_duplicate_bid(int $item_id, int $user_id, float $amount): bool {
        return Bid::exists_recent_duplicate($item_id, $user_id,1);
    }



}