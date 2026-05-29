<?php
require_once "framework/Model.php";
require_once "framework/Configuration.php";
require_once "model/Item.php";

class User extends Model {

    public int $id;
    public string $full_name;
    public string $pseudo;
    private string $email;
    public string $role;
    public ?string $picture_path;
    public ?string $iban;
    private ?string $hashed_password;

    public function __construct(
        int $id,
        string $full_name,
        string $pseudo,
        string $email,
        string $role,
        ?string $picture_path = null,
        ?string $iban = null,
        ?string $hashed_password = null
    ) {
        $this->id = $id;
        $this->full_name = $full_name;
        $this->pseudo = $pseudo;
        $this->email = $email;
        $this->role = $role;
        $this->picture_path = $picture_path;
        $this->iban = $iban;
        $this->hashed_password = $hashed_password;
    }


    public static function get_all_users(): array {
        $query = self::execute("SELECT * FROM users ORDER BY id", []);
        $rows = $query->fetchAll();
        return array_map(fn($row) => new User(
            $row["id"], $row["full_name"], $row["pseudo"], $row["email"],
            $row["role"], $row["picture_path"] ?? null, $row["iban"] ?? null, $row["password"] ?? null
        ), $rows);
    }

    public static function get_user_by_mail(string $email): ?User {
    $query = self::execute(
        "SELECT * FROM users WHERE email = :email",
        ["email" => $email]
    );
    $row = $query->fetch();

    if (!$row) {
        return null;
    }

    return new User(
        $row["id"],
        $row["full_name"],
        $row["pseudo"],
        $row["email"],
        $row["role"],
        $row["picture_path"] ?? null,
        $row["iban"] ?? null,
        $row["password"] ?? null
    );
    }


    
     public function check_password(string $password): bool {
        return $this->hashed_password !== null
            && password_verify($password, $this->hashed_password);
    }

    
    
    public function get_email(): string {
            return $this->email;
    }
    

    public function get_Id(): int {
        return $this->id;
    }

    public static function get_User_By_Id(int $user_id): User|false {
    $query = self::execute("SELECT * FROM users WHERE id = :id", ['id' => $user_id]);
    $data = $query->fetch();
    if ($data === false) { 
        return false;
    } else {
        return new User($data["id"],
            $data["full_name"],
            $data["pseudo"],
            $data["email"],
            $data["role"], 
            $data["picture_path"], 
            $data["iban"],
            $data["password"] ?? null);
    }
}

    public function get_Pseudo(): string {
        return $this->pseudo;
    }


    public function get_FullName(): string{
        return $this->full_name;
    }
public function get_Thumbnail_Path(): ?string {
    if (!$this->picture_path) {
        return null;
    }
    return str_replace('.jpg', '_thumbnail.jpg', $this->picture_path);
}

public function has_Picture(): bool {
    return !empty($this->picture_path);
}



    private static function validate_password(string $password): array {
        $errors = [];
        $min = (int) Configuration::get('password_min_length', '8');
        $max = (int) Configuration::get('password_max_length', '16');
        if (strlen($password) < $min || strlen($password) > $max) {
            $errors[] = "Password length must be between $min and $max.";
        }

        if (!preg_match("/[A-Z]/", $password)) {
            $errors[] = "Password must contain at least one uppercase letter.";
        }

        if (!preg_match("/[a-z]/", $password)) {
            $errors[] = "Password must contain at least one lowercase letter.";
        }

        if (!preg_match("/\d/", $password)) {
            $errors[] = "Password must contain at least one number.";
        }

        if (!preg_match("/[^a-zA-Z0-9]/", $password)) {
            $errors[] = "Password must contain at least one non-alphanumeric character.";
        }

        return $errors;
    }

    public static function validate_passwords(string $password, string $password_confirm): array {
        $errors = self::validate_password($password);
        if ($password !== $password_confirm) {
            $errors[] = "You have to enter twice the same password.";
        }
        return $errors;
    }


public function get_hashed_password(): ?string {
    if ($this->hashed_password) {
        return $this->hashed_password;
    }
    $query = self::execute("SELECT password FROM users WHERE id = :id", ['id' => $this->id]);
    $data = $query->fetch();
    return $data ? $data['password'] : null;
}

    public static function update_password(int $user_id, string $hashed_password): void {
        self::execute(
            "UPDATE users SET password = :password WHERE id = :id",
            ['password' => $hashed_password, 'id' => $user_id]
        );
    }
    public function set_password(string $new_password): void {
        self::execute(
            "UPDATE users SET password = :password WHERE id = :id",
            ['password' => password_hash($new_password, PASSWORD_DEFAULT), 'id' => $this->id]
        );
    }


    public function validate_new_password(
        ?string $current,
        ?string $new,
        ?string $confirm
    ): array {
        $field_errors = [
            'current_password' => [],
            'new_password' => [],
            'confirm_password' => []
        ];

        $field_errors['current_password'] = self::validate_current_password($this, $current);

        if ($new !== null && $new !== '' && $confirm !== null && $confirm !== '') {
            self::categorize_password_errors(
                self::validate_passwords($new, $confirm),
                $field_errors
            );
        } else {
            if ($new === null || $new === '') {
                $field_errors['new_password'][] = "Please enter a new password.";
            }
            if ($confirm === null || $confirm === '') {
                $field_errors['confirm_password'][] = "Please confirm your new password.";
            }
        }

        return $field_errors;
    }

    private static function validate_current_password(self $user, ?string $current_password): array {
        $errors = [];
        $hashed_password = $user->get_hashed_password();
        if (!$current_password || !$hashed_password || !password_verify($current_password, $hashed_password)) {
            $errors[] = "Current password is incorrect.";
        }
        return $errors;
    }

    private static function categorize_password_errors(array $validation_errors, array &$field_errors): void {
        foreach ($validation_errors as $error) {
            if (strpos($error, 'twice the same') !== false) {
                $field_errors['confirm_password'][] = $error;
            } else {
                $field_errors['new_password'][] = $error;
            }
        }
    }
    public static function email_exists(string $email): bool
    {
        $query = self::execute(
            "SELECT COUNT(*) as count FROM users WHERE email = :email",
            ["email" => $email]
        );
        $row = $query->fetch();
        return $row['count'] > 0;
    }

    public static function pseudo_exists(string $pseudo): bool
    {
        $query = self::execute(
            "SELECT COUNT(*) as count FROM users WHERE pseudo = :pseudo",
            ["pseudo" => $pseudo]
        );
        $row = $query->fetch();
        return $row['count'] > 0;
    }

    public static function full_name_exists(string $full_name): bool
    {
        $query = self::execute(
            "SELECT COUNT(*) as count FROM users WHERE full_name = :full_name",
            ["full_name" => $full_name]
        );
        $row = $query->fetch();
        return $row['count'] > 0;
    }

    public static function validate_signup(
        string $email,
        string $full_name,
        string $pseudo,
        string $password,
        string $password_confirm
    ): array {
        $errors = [
            'email' => [],
            'full_name' => [],
            'pseudo' => [],
            'password' => [],
            'password_confirm' => []
        ];

        if (empty($email)) {
            $errors['email'][] = "Email is required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'][] = "Invalid email format.";
        } elseif (self::email_exists($email)) {
            $errors['email'][] = "This email is already registered.";
        }

        if (empty($full_name)) {
            $errors['full_name'][] = "Full name is required.";
        } elseif (self::full_name_exists($full_name)) {
            $errors['full_name'][] = "This name is already taken.";
        }

        if (empty($pseudo)) {
            $errors['pseudo'][] = "Pseudo is required.";
        } elseif (self::pseudo_exists($pseudo)) {
            $errors['pseudo'][] = "This pseudo is already taken.";
        }

        $passwordErrors = self::validate_passwords($password, $password_confirm);
        foreach ($passwordErrors as $error) {
            if (strpos($error, 'twice the same') !== false) {
                $errors['password_confirm'][] = $error;
            } else {
                $errors['password'][] = $error;
            }
        }

        return $errors;
    }

    public static function signup(
        string $email,
        string $full_name,
        string $pseudo,
        string $password
    ): ?User {
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        self::execute(
            "INSERT INTO users (email, full_name, pseudo, password, role) 
             VALUES (:email, :full_name, :pseudo, :password, 'user')",
            [
                'email' => $email,
                'full_name' => $full_name,
                'pseudo' => $pseudo,
                'password' => $hashed
            ]
        );

        return self::get_user_by_mail($email);
    }

    public function get_picture_path(): ?string {
        return $this->picture_path;
    }

    public function set_picture_path(?string $path): void {
        $this->picture_path = $path;
    }

    public function save_picture(): void {
        self::execute(
            "UPDATE users SET picture_path = :path WHERE id = :id",
            [
                "path" => $this->picture_path,
                "id" => $this->id
            ]
        );
    }

    public static function is_pseudo_taken_by_other(string $pseudo, int $current_id): bool {
        $query = self::execute(
            "SELECT COUNT(*) FROM users WHERE pseudo = :pseudo AND id != :id",
            ["pseudo" => $pseudo, "id" => $current_id]
        );
        return (int)$query->fetchColumn() > 0;
    }

    public static function is_email_taken_by_other(string $email, int $current_id): bool {
        $query = self::execute(
            "SELECT COUNT(*) FROM users WHERE email = :email AND id != :id",
            ["email" => $email, "id" => $current_id]
        );
        return (int)$query->fetchColumn() > 0;
    }

   
    public static function is_full_name_taken_by_other(string $full_name, int $current_id): bool {
        $query = self::execute(
            "SELECT COUNT(*) FROM users WHERE full_name = :full_name AND id != :id",
            ["full_name" => $full_name, "id" => $current_id]
        );
        return (int)$query->fetchColumn() > 0;
    }

    public static function get_user_data(int $user_id): ?array
    {
        $query = self::execute("SELECT * FROM users WHERE id = :id", ['id' => $user_id]);
        $data = $query->fetch();
        return $data ?: null;
    }

    public static function is_pseudo_taken(string $pseudo, int $user_id): bool
    {
        $query = self::execute("SELECT COUNT(*) FROM users WHERE pseudo = :pseudo AND id != :id", ['pseudo' => $pseudo, 'id' => $user_id]);
        return (int)$query->fetchColumn() > 0;
    }

    public static function is_email_taken(string $email, int $user_id): bool
    {
        $query = self::execute("SELECT COUNT(*) FROM users WHERE email = :email AND id != :id", ['email' => $email, 'id' => $user_id]);
        return (int)$query->fetchColumn() > 0;
    }

    public static function is_full_name_taken(string $full_name, int $user_id): bool
    {
        $query = self::execute("SELECT COUNT(*) FROM users WHERE full_name = :full_name AND id != :id", ['full_name' => $full_name, 'id' => $user_id]);
        return (int)$query->fetchColumn() > 0;
    }

    public static function update_user(int $user_id, string $full_name, string $pseudo, string $email, string $iban): bool
    {
        $query = self::execute(
            "UPDATE users SET full_name = :full_name, pseudo = :pseudo, email = :email, iban = :iban WHERE id = :id",
            [
                'id' => $user_id,
                'full_name' => $full_name,
                'pseudo' => $pseudo,
                'email' => $email,
                'iban' => $iban ?: null
            ]
        );
        return $query !== false;
    }

    public function get_my_items(string $search_query = "", int $category_id = 0): array
    {
        return Item::get_items_by_owner($this->id, $search_query, $category_id);
    }

    /**
     * @return array{active: Item[], closed_unsold: Item[], sold: Item[]}
     */
    public function get_my_items_grouped(string $now, string $search_query = "", int $category_id = 0): array
    {
        $active = [];
        $closed_unsold = [];
        $sold = [];

        foreach ($this->get_my_items($search_query, $category_id) as $item) {
            if ($item->is_open()) {
                $active[] = $item;
            } elseif ($item->has_bids_time() && ($item->has_buy_now_reached_time() || $item->get_end_at() <= $now)) {
                $sold[] = $item;
            } else {
                $closed_unsold[] = $item;
            }
        }

        $sortByEnd = fn(Item $a, Item $b) => strcmp($a->get_end_at(), $b->get_end_at());
        usort($active, $sortByEnd);
        usort($closed_unsold, $sortByEnd);
        usort($sold, $sortByEnd);

        return [
            'active' => $active,
            'closed_unsold' => $closed_unsold,
            'sold' => $sold,
        ];
    }

    public function get_participating_items(string $now, string $search_query = "", int $category_id = 0): array
    {
        return Item::get_Item_Participating($this->id, $now, $search_query, $category_id);
    }

    public function get_available_items(string $now, string $search_query = "", int $category_id = 0): array
    {
        return Item::get_Item_Available($this->id, $now, $search_query, $category_id);
    }

    public function get_sold_items(string $now): array
    {
        return Item::get_sold_items_by_owner($this->id, $now);
    }

    public function get_sales_statistics(string $now): array
    {
        return Item::get_sales_statistics($this->id, $now);
    }

    public function get_purchased_items(string $now, string $search_query = ""): array
    {
        return Item::get_purchased_items_by_user($this->id, $now, $search_query);
    }

    /**
     * @return array{count: int, total: float|int, average: float|int, top_seller: string|null}
     */
    public function get_purchases_statistics(string $now, string $search_query = ""): array
    {
        $items = $this->get_purchased_items($now, $search_query);
        $total = 0;
        $count = count($items);
        $sellers = [];

        foreach ($items as $item) {
            $price = $item->get_max_bid_time();
            if ($price) {
                $total += $price;
            }

            $seller = $item->get_seller()->get_Pseudo();
            $sellers[$seller] = ($sellers[$seller] ?? 0) + 1;
        }

        arsort($sellers);

        return [
            'count' => $count,
            'total' => $total,
            'average' => $count > 0 ? $total / $count : 0,
            'top_seller' => array_key_first($sellers),
        ];
    }


}