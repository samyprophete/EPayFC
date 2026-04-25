<?php
require_once "framework/Controller.php";
require_once "framework/View.php";
require_once  "model/Item.php";
require_once "model/User.php";
require_once "model/ItemPicture.php";
class ControllerDeleteConfirm extends Controller {

    public function index(): void {
        $user = $this->get_user_or_redirect();
        $encoded_state = $_GET['param2'] ?? null;
        
        $item_id = $_GET['param1'] ?? null;
        
        if (!$item_id || !is_numeric($item_id)) {
            $this->redirect('my_items');
            return;
        }
        
        $item = Item::get_by_id((int)$item_id);
        
        if (!$item) {
            $this->redirect('my_items');
            return;
        }
        
    
        if ($item->get_Owner() != $user->get_Id()) {
            $this->redirect('browse_items');
            return;
        }
        
   
        if ($item->has_bids_time()) {
            $this->redirect('open_item', 'index', (string)$item_id);
            return;
        }
        $seller = User::get_User_By_Id($item->get_Owner());
        
        (new View("delete_confirm"))->show([
            'header_title' => 'Delete Item',
            'header_icon' => 'bi-trash',
            'back_url' => 'open_item/index/' . $item_id,
            'item' => $item,
            'current_user' => $user,
            'seller' => $seller,
            'item_title' => $item->get_Title(),
            'seller_full_name' => $seller->get_FullName(),
            'item_id' => $item_id,
            'page_css' => ['delete_item.css'],
            'back_url' => $encoded_state
            ? 'open_item/index/' . $item_id . '/0/' . urlencode($encoded_state)
            : 'open_item/index/' . $item_id,
        'encoded_state' => $encoded_state,
        ]);
    }
    
    public function confirm(): void {
        $user = $this->get_user_or_redirect();
        
        $item_id = $_POST['item_id'] ?? null;
        $encoded_state = $_POST['encoded_state'] ?? null;
        
        if (!$item_id) {
            $this->redirect('my_items');
            return;
        }
        
        $item = Item::get_by_id((int)$item_id);


        if (!$item || $item->get_owner() != $user->get_Id() || $item->has_bids_time()) {
            $this->redirect('my_items');
            return;
        }
        
        //cascade
        ItemPicture::delete_all_by_item($item->get_Id());

        $item->delete();

        if ($encoded_state) {
            $state = Tools::url_safe_decode($encoded_state);
            $from = is_array($state) && isset($state['from']) ? $state['from'] : 'my_items';
            $this->redirect($from, 'index', $encoded_state);
        } else {
            $this->redirect('my_items');
        }
    }
}