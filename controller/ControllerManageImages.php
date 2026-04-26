<?php
require_once "framework/Controller.php";
require_once 'framework/View.php';
require_once 'utils/AppTime.php';
require_once 'model/Item.php';
require_once 'model/ItemPicture.php';
require_once 'model/User.php';

class ControllerManageImages extends Controller
{
    public function index(): void
    {
        $current_user = $this->get_user_or_false();
        if (!$current_user) {
            $this->redirect("login");
            return;
        }

        $item_id = $_GET['param1'] ?? null;
        if (!$item_id) {
            $this->redirect("profile");
            return;
        }

        $item = Item::get_by_id((int) $item_id);
        if (!$item || $item->get_seller()->get_Id() !== $current_user->get_Id()) {
            $this->redirect("profile");
            return;
        }
        $encoded_state = $_GET['param2'] ?? null;

        $pictures = ItemPicture::get_all_by_item((int) $item_id);
        (new View("manage_images"))->show([
            'item' => $item,
            'pictures' => $pictures,
            'picture_count' => count($pictures),
            'current_user' => $current_user,
            'back_url' => 'my_items',
            'header_title' => 'Manage Images',
            'header_icon' => 'bi-images',
            'page_css' => ['manage.css'],
            'page_js' => ['manage_images.js'],
            'encoded_state' => $encoded_state,
            'back_url' => $encoded_state
                ? "open_item/index/$item_id/" . urlencode($encoded_state) . "/0"
                : "open_item/index/$item_id",
                    ]);
    }
    private function get_encoded_state(bool $forIndexAction = false): ?string {
        $fromPost = $_POST['encoded_state'] ?? null;
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
    
    private function redirect_manage_images_with_state(int|string $item_id, ?string $encoded_state): void {
        $this->redirect(
            "manage_images",
            "index",
            (string)$item_id,
            $encoded_state ?? ""
        );
    }

    public function upload(): void
    {
        $current_user = $this->get_user_or_false();
        if (!$current_user){
            $this->redirect("login");
            return;
        }

        $item_id =$_GET['param1'] ?? $_POST['item_id'] ?? null;
        if (!$item_id)
            return;

        $encoded_state = $this->get_encoded_state(false);

        if (!empty($_FILES['images']['name'][0])) {
            $upload_dir = 'uploads/items/'. $item_id . '/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                $extension = pathinfo($_FILES['images']['name'][$key], PATHINFO_EXTENSION);
                $new_file_name = bin2hex(random_bytes(8)) . '_' . AppTime::get_current_timestamp() . '.' . $extension;
                $file_path = $upload_dir . $new_file_name;

                if (move_uploaded_file($tmp_name, $file_path)) {
                    ItemPicture::add((int) $item_id, $file_path);
                }
            }
        }
        $this->redirect_manage_images_with_state($item_id , $encoded_state);
    }

    public function delete(): void
    {
        $current_user = $this->get_user_or_false();
        $item_id = $_GET['param1'] ?? null;
        $priority = $_GET['param2'] ?? null;
        $encoded_state = $this->get_encoded_state();
        if ($current_user && $item_id && $priority !== null) {
            ItemPicture::delete((int) $item_id, (int) $priority);
        }
        $this->redirect_manage_images_with_state($item_id, $encoded_state);
    }

    public function move_left(): void
    {
        $current_user = $this->get_user_or_false();
        $item_id = $_GET['param1'] ?? null;
        $priority = $_GET['param2'] ?? null;
        $encoded_state = $this->get_encoded_state();

        if ($current_user && $item_id && $priority !== null) {
            ItemPicture::move_left((int) $item_id, (int) $priority);
        }
        $this->redirect_manage_images_with_state($item_id, $encoded_state);
    }

    public function move_right(): void
    {
        $current_user = $this->get_user_or_false();
        $item_id = $_GET['param1'] ?? null;
        $priority = $_GET['param2'] ?? null;
        $encoded_state = $this->get_encoded_state();

        if ($current_user && $item_id && $priority !== null) {
            ItemPicture::move_right((int) $item_id, (int) $priority);
        }
        $this->redirect_manage_images_with_state($item_id, $encoded_state);
    }

    public function update_order(): void {
        $input = json_decode(file_get_contents('php://input'), true);

        $current_user = $this->get_user_or_false();
        if (!$current_user) {
            http_response_code(401);
            echo json_encode(['error' => 'Not logged in']);
            return;
        }

        $item_id = $input['item_id'] ?? null;
        $order = $input['order'] ?? [];

        if (!$item_id || empty($order)){
            http_response_code(400);
            echo json_encode(['error' => 'Invalid data']);
            return;
        }
        $item = Item::get_by_id((int) $item_id);
        if (!$item || $item->get_seller()->get_Id() !== $current_user->get_Id()) {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied']);
            return;
        }

        ItemPicture::reorder((int) $item_id, $order);

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    } 
}