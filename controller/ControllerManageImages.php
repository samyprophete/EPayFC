<?php
require_once "framework/Controller.php";
require_once 'framework/View.php';
require_once 'utils/AppTime.php';
require_once 'model/Item.php';
require_once 'model/ItemPicture.php';
require_once 'model/User.php';
require_once 'utils/ImageProcessor.php';
require_once 'framework/Configuration.php';

class ControllerManageImages extends Controller
{
    public function index(): void
    {
        $current_user = $this->get_user_or_redirect_login();


        $item_id = $_GET['param1'] ?? null;
        if (!$item_id || !ctype_digit((string) $item_id)) {
            $this->redirect("profile");
            return;
        }


        $search_state = $_GET['param2'] ?? null;
        if ($search_state === '0' || $search_state === '') {
            $search_state = null;
        }

        $item = $this->get_owner_item_or_reject((int) $item_id, $current_user,$search_state);
        if ($item === null) {
            return;
        }

        $pictures = ItemPicture::get_all_by_item((int) $item_id);
        (new View("manage_images"))->show([
            'item' => $item,
            'pictures' => $pictures,
            'picture_count' => count($pictures),
            'current_user' => $current_user,
            'header_title' => 'Manage Images',
            'header_icon' => 'bi-images',
            'page_css' => ['manage.css'],
            'page_js' => ['manage_images.js'],
            'search_state'=> $search_state,
            'back_url' => $search_state
                ? "open_item/index/$item_id/" . urlencode($search_state)
                : "open_item/index/$item_id",
                    ]);
    }
    private function get_user_or_redirect_login(): User
    {
        $user = $this->get_user_or_false();
        if (!$user) {
            $this->redirect("login");
        }
        return $user;
    }

    private function get_search_state(bool $forIndexAction = false): ?string {
        $fromPost = $_POST['search_state'] ?? null;
        if (is_string($fromPost) && $fromPost !== '') {
            return $fromPost;
        }
        if ($forIndexAction) {
            $s = $_GET['param2'] ?? null;
        } else {
            $s = $_GET['param3'] ?? null;
        }
        return (is_string($s) && $s !== '') ? $s : null;
    }
    
    private function redirect_manage_images_with_state(int|string $item_id, ?string $search_state): void {
        $this->redirect(
            "manage_images",
            "index",
            (string)$item_id,
            $search_state ?? '0'
        );
    }

    public function upload(): void
    {
        $current_user = $this->get_user_or_redirect_login();

        $item_id =$_GET['param1'] ?? $_POST['item_id'] ?? null;
        if (!$item_id || !ctype_digit((string) $item_id)) {
            $this->redirect("my_items");
            return;
        }
        $search_state= $this->get_search_state(false);

        $item = $this->get_owner_item_or_reject((int) $item_id, $current_user, $search_state);
            if ($item === null) {
                return;
            }

        if (!empty($_FILES['images']['name'][0])) {
            $upload_dir = 'uploads/items/'. $item_id . '/';
  
            $max_size   = (int) Configuration::get('max_item_picture_size', '5242880');

            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                if (($_FILES['images']['error'][$key] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                    continue;
                }

                if (!is_uploaded_file($tmp_name)) {
                    continue;
                }
                if (($_FILES['images']['size'][$key] ?? 0) > $max_size) {
                    continue;
                }
    

                try {

                    $base_name = bin2hex(random_bytes(8)) . '_' . AppTime::get_current_timestamp();
                    $main_path = ImageProcessor::process_item_upload($tmp_name, $upload_dir, $base_name);
                    ItemPicture::add((int) $item_id, $main_path);

                } catch (InvalidArgumentException $e) {

                    continue;
                }
            }
        }
        $this->redirect_manage_images_with_state($item_id ,$search_state);
    }

    private function get_owner_item_or_reject(int $item_id, User $current_user, ?string $search_state = null): ?Item
    {
        $item = Item::get_by_id($item_id);

        if (!$item) {
            $this->redirect('my_items');
            return null;
        }

        if ($item->get_seller()->get_Id() !== $current_user->get_Id()) {
            $this->redirect('browse_items');
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
    private function get_owner_item_or_json_error(int $item_id, User $current_user): ?Item
    {
        $item = Item::get_by_id($item_id);
        if (!$item) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Item not found']);
            return null;
        }
        if ($item->get_seller()->get_Id() !== $current_user->get_Id()) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Access denied']);
            return null;
        }
        if ($item->has_bids_time()) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Item can no longer be modified']);
            return null;
        }
        return $item;
    }
    public function delete(): void
    {

        $current_user = $this->get_user_or_false();
        if (!$current_user) {
            if ($this->necessite_json_response()) {
                $this->json_response(['error' => 'Not authenticated'], 401);
                return;
            }
            $this->redirect("login");
            return;
        }

        $item_id = $_GET['param1'] ?? null;
        $priority = $_GET['param2'] ?? null;
        $search_state = $this->get_search_state();

        if (!$item_id || !ctype_digit((string)$item_id) || $priority === null || !ctype_digit((string)$priority)) {

            if ($this->necessite_json_response()) {
                $this->json_response(['error' => 'Invalid parameters'], 400);
            return;
            }
            $this->redirect("my_items");
            return;
        }

    if ($this->necessite_json_response()) {
        $item = $this->get_owner_item_or_json_error((int)$item_id, $current_user);
        if ($item === null) {
            return;
        }
        ItemPicture::delete((int)$item_id, (int)$priority);
        $this->json_response(['success' => true]);
        return;
    }



        $item = $this->get_owner_item_or_reject((int)$item_id, $current_user,$search_state);
        if ($item === null) {
            return;
        }

        ItemPicture::delete((int)$item_id, (int)$priority);
        $this->redirect_manage_images_with_state($item_id,$search_state);
    }


    public function move_left(): void
    {
        $current_user = $this->get_user_or_false();
        if (!$current_user) {
            if ($this->necessite_json_response()) {
                $this->json_response(['error' => 'Not authenticated'], 401);
                return;
            }
            $this->redirect("login");
            return;
        }
        $item_id = $_GET['param1'] ?? null;
        $priority = $_GET['param2'] ?? null;
        $search_state = $this->get_search_state();

        if (!$item_id || !ctype_digit((string)$item_id) || $priority === null || !ctype_digit((string)$priority)) {
            if ($this->necessite_json_response()) {
                $this->json_response(['success' => false, 'error' => 'Invalid parameters'], 400);
                return;
            }
            $this->redirect("my_items");
            return;
        }

                
       if ($this->necessite_json_response()) {
        $item = $this->get_owner_item_or_json_error((int)$item_id, $current_user);
        if ($item === null) {
            return;
        }
        ItemPicture::move_left((int)$item_id, (int)$priority);
        $this->json_response(['success' => true]);
        return;
    }


        $item = $this->get_owner_item_or_reject((int)$item_id, $current_user,$search_state);
        if ($item === null) {
            return;

        }

        ItemPicture::move_left((int)$item_id, (int)$priority);


        $this->redirect_manage_images_with_state($item_id,$search_state);
    }





    public function move_right(): void
    {
        $current_user = $this->get_user_or_false();
        if (!$current_user) {
            if ($this->necessite_json_response()) {
                $this->json_response(['error' => 'Not authenticated'], 401);
                return;
            }
            $this->redirect("login");
            return;
        }
        
        $item_id = $_GET['param1'] ?? null;
        $priority = $_GET['param2'] ?? null;
        $search_state = $this->get_search_state();

        if (!$item_id || !ctype_digit((string)$item_id) || $priority === null || !ctype_digit((string)$priority)) {
                 if ($this->necessite_json_response()) {
                $this->json_response(['success' => false, 'error' => 'Invalid parameters'], 400);
                return;
            }
            $this->redirect("my_items");
            return;
        }

  
    if ($this->necessite_json_response()) {
        $item = $this->get_owner_item_or_json_error((int)$item_id, $current_user);
        if ($item === null) {
            return;
        }
        ItemPicture::move_right((int)$item_id, (int)$priority);
        $this->json_response(['success' => true]);
        return;
    }

        $item = $this->get_owner_item_or_reject((int)$item_id, $current_user,$search_state);
        if ($item === null) {
            return;
        }

        ItemPicture::move_right((int)$item_id, (int)$priority);



        $this->redirect_manage_images_with_state($item_id,$search_state);
    }




    private function necessite_json_response(): bool {
        return !((bool) Configuration::get("disable_js"));
    }

    private function json_response(array $payload, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($payload);
    }
    public function update_order(): void {


        if (!$this->necessite_json_response()) {
            $this->redirect('my_items');
            return;
        }

            $current_user =  $this->get_user_or_json_error();

            
        if (!$current_user) {
            return;
        }

        $item_id = $_POST['item_id'] ?? null;
        $order = $_POST['order'] ?? [];

        if (!is_array($order)) {
            $order = [];
        }

        if (!$item_id || !ctype_digit(($item_id)) || empty($order)) {
            $this->json_response(['success' => false, 'error' => 'Invalid data'], 400);
            return;
        }
        $item = $this->get_owner_item_or_json_error((int)$item_id, $current_user);
        if ($item === null) {
            return;
        }
        ItemPicture::reorder((int)$item_id, $order);
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    }



        private function get_user_or_json_error(): ?User {

        $user = $this->get_user_or_false();
        if (!$user) {
            $this->json_response(['success' => false, 'error' => 'Not logged in'], 401);
            return null;
        }
        return $user;
    }
   
    private function get_user_for_action(): ?User
    {
        if ($this->necessite_json_response()) {
            return $this->get_user_or_json_error();
        }
        return $this->get_user_or_redirect_login();
    } 

}