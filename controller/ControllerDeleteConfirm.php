<?php
require_once "framework/Controller.php";
require_once "framework/View.php";
require_once  "model/Item.php";
require_once "model/User.php";
require_once "model/ItemPicture.php";
class ControllerDeleteConfirm extends Controller {

    public function index(): void {
        $user = $this->get_user_or_redirect();
        $search_state = $_GET['param2'] ?? null;
        
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
            $this->redirect('browser');
            return;
        }
        
   
        if ($item->has_bids_time()) {
            if ($search_state) {
                $this->redirect('open_item', 'index', (string)$item_id, $search_state);
            } else {
                $this->redirect('open_item', 'index', (string)$item_id);
            }
            return;
        }
        $seller = User::get_User_By_Id($item->get_Owner());
        
        (new View("delete_confirm"))->show([
            'header_title' => 'Delete Item',
            'header_icon' => 'bi-trash',
            'item' => $item,
            'current_user' => $user,
            'seller' => $seller,
            'item_title' => $item->get_Title(),
            'seller_full_name' => $seller->get_FullName(),
            'item_id' => $item_id,
            'page_css' => ['delete_item.css'],
            'back_url' => $search_state
            ? 'open_item/index/' . $item_id . '/' . urlencode($search_state)
            : 'open_item/index/' . $item_id,
            'search_state' =>$search_state 
        ]);
    }
    
    public function confirm(): void {
        $user = $this->get_user_or_redirect();
        
        $item_id = $_POST['item_id'] ?? null;
        $search_state = $_POST['search_state'] ?? null;
        
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
        if (in_array($search_state, ['sales', 'purchases', 'browser', 'my_items'], true)) {
            $this->redirect($search_state, 'index');
            return;
        }
        $state = Tools::url_safe_decode($search_state);
        $from = is_array($state) && isset($state['from']) ? $state['from'] : 'my_items';
        $this->redirect($from, 'index', $search_state);
    }
}