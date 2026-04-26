<?php
require_once "framework/Controller.php";
require_once "model/Item.php";
require_once "model/Bid.php";
require_once "model/User.php"; 
require_once "utils/AppTime.php";
require_once "utils/format.php";
class ControllerBid extends Controller {


    // Si quelqu'un accède à bid sans action, rediriger
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
        
        $this->process_bid($item, $current_user, $params['amount'], $params['encoded_state'] ?? '');
    }
    
    // ============ AUTHENTIFICATION ============
    
    private function get_user_or_redirect_login(): ?object {
        $current_user = $this->get_user_or_false();
        if (!$current_user) {
            $this->redirect("login");
            return null;
        }
        return $current_user;
    }
    
    // ============ EXTRACTION PARAMÈTRES ============
    
    private function extract_bid_params(): ?array {
        $item_id = isset($_POST['item_id']) ? (int)$_POST['item_id'] : null;
        $amount_str = $_POST['amount'] ?? null;
        
        $amount = null;
        if ($amount_str) {
            $amount_str = str_replace(',', '.', $amount_str);
            $amount = (float)$amount_str;
        }
        $encoded_state = trim((string) ($_POST['encoded_state'] ?? ''));
       
        if (!$item_id ||  $amount === null) {
            if ($item_id) {
                if ($encoded_state !== '') {
                    $this->redirect("open_item", "index", (string) $item_id, $encoded_state, "0");
                } else {
                    $this->redirect("open_item", "index", (string) $item_id);
                }
            } else {
                $this->redirect("browser");
            }
            return null;
        }
        
        
        return ['item_id' => $item_id, 'amount' => $amount, 'encoded_state' => $encoded_state];
    }
    
    // ============ CHARGEMENT ITEM ============
    
    private function load_item_or_redirect(int $item_id): ?Item {
        $item = Item::get_by_id($item_id);
        if ($item === false) {
            $es = trim((string) ($_POST['encoded_state'] ?? ''));
            if ($es !== '') {
                $this->redirect("open_item", "index", (string) $item_id, $es, "0");
            } else {
                $this->redirect("open_item", "index", (string) $item_id);
            }
            return null;
        }
        return $item;
    }
    
    // ============ TRAITEMENT ENCHÈRE ============
    
    private function process_bid(Item $item, object $user, float $amount, string $encoded_state = ''): void {

        $bid = new Bid($item->get_Id(), $user->get_id(), $amount);
        $errors = $bid->persist($item);

        $is_ajax = ($_POST['format'] ?? '') === 'json';

        if ($is_ajax) {
            header('Content-Type: application/json');
            if (!empty($errors)) {
                http_response_code(422);
                echo json_encode(['success' => false, 'errors' => $errors]);
            } else {
                echo json_encode([
                    'success' => true, 
                    'message' => 'You successfully bidded ' . format_euro($amount)
                ]);
            }
            return;
        }

        $es = $encoded_state !== '' ? $encoded_state : trim((string) ($_POST['encoded_state'] ?? ''));
        if ($es !== '') {
            $this->redirect("open_item", "index", (string) $item->get_Id(), $es, "0");
        } else {
            $this->redirect("open_item", "index", (string) $item->get_Id());
        }
    }

}