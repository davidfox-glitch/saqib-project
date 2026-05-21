<?php
// Simple JSON-based database wrapper for Exaltia storefront

class JsonDB {
    private static $usersFile = __DIR__ . '/../data/users.json';
    private static $productsFile = __DIR__ . '/../data/products.json';
    private static $ordersFile = __DIR__ . '/../data/orders.json';

    // Core helper to read JSON
    private static function read($file) {
        if (!file_exists($file)) {
            file_put_contents($file, json_encode([]));
            return [];
        }
        $content = file_get_contents($file);
        $data = json_decode($content, true);
        return is_array($data) ? $data : [];
    }

    // Core helper to write JSON
    private static function write($file, $data) {
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    // Initialize Database with Defaults if empty
    public static function init() {
        // Ensure data dir exists
        $dir = __DIR__ . '/../data';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        // Initialize Users if empty
        $users = self::read(self::$usersFile);
        if (empty($users)) {
            $users = [
                [
                    "id" => 1,
                    "username" => "Admin",
                    "email" => "admin@exaltia.com",
                    "password" => password_hash("admin123", PASSWORD_DEFAULT),
                    "role" => "admin"
                ],
                [
                    "id" => 2,
                    "username" => "John Doe",
                    "email" => "user@exaltia.com",
                    "password" => password_hash("user123", PASSWORD_DEFAULT),
                    "role" => "user"
                ]
            ];
            self::write(self::$usersFile, $users);
        }

        // Initialize Products if empty (fallback)
        if (!file_exists(self::$productsFile) || empty(self::read(self::$productsFile))) {
            $defaultProducts = [
                [
                    "id" => 1,
                    "name" => "Artisanal Polo Knit",
                    "price" => 89.00,
                    "category" => "Polos",
                    "description" => "Crafted from a premium cotton-linen blend, this short-sleeve polo knit shirt features a clean open collar, ribbed trims, and a relaxed silhouette perfect for warm weather.",
                    "image" => "public/images/polo_knit.jpg",
                    "sizes" => ["S", "M", "L", "XL"],
                    "colors" => ["#8F9779", "#D2C9B1", "#EFEFEF"],
                    "stock" => 15
                ],
                [
                    "id" => 2,
                    "name" => "Relaxed Cotton Tee",
                    "price" => 45.00,
                    "category" => "Shirts",
                    "description" => "An everyday essential. Made from heavyweight 100% organic cotton, this t-shirt offers a structured drape, dropped shoulders, and a clean crew neck.",
                    "image" => "public/images/white_tee.jpg",
                    "sizes" => ["S", "M", "L", "XL"],
                    "colors" => ["#EFEFEF", "#1A1A1A", "#8F9779"],
                    "stock" => 25
                ],
                [
                    "id" => 3,
                    "name" => "Artistic Print Tee",
                    "price" => 55.00,
                    "category" => "Shirts",
                    "description" => "Featuring a subtle abstract screenprint across the chest, this relaxed-fit tee is cut from soft, breathable cotton single jersey.",
                    "image" => "public/images/print_tee.jpg",
                    "sizes" => ["S", "M", "L", "XL"],
                    "colors" => ["#A2B5CD", "#EFEFEF"],
                    "stock" => 10
                ]
            ];
            self::write(self::$productsFile, $defaultProducts);
        }

        // Initialize Orders if empty
        if (!file_exists(self::$ordersFile)) {
            self::write(self::$ordersFile, []);
        }
    }

    // --- USERS ACCESS ---
    
    public static function getUsers() {
        return self::read(self::$usersFile);
    }

    public static function findUserByEmail($email) {
        $users = self::getUsers();
        foreach ($users as $user) {
            if (strtolower($user['email']) === strtolower($email)) {
                return $user;
            }
        }
        return null;
    }

    public static function createUser($username, $email, $password, $role = 'user') {
        $users = self::getUsers();
        $newId = 1;
        if (!empty($users)) {
            $ids = array_column($users, 'id');
            $newId = max($ids) + 1;
        }

        $newUser = [
            "id" => $newId,
            "username" => $username,
            "email" => $email,
            "password" => password_hash($password, PASSWORD_DEFAULT),
            "role" => $role
        ];

        $users[] = $newUser;
        self::write(self::$usersFile, $users);
        return $newUser;
    }

    // --- PRODUCTS ACCESS ---

    public static function getProducts() {
        return self::read(self::$productsFile);
    }

    public static function findProductById($id) {
        $products = self::getProducts();
        foreach ($products as $product) {
            if ($product['id'] == $id) {
                return $product;
            }
        }
        return null;
    }

    public static function createProduct($data) {
        $products = self::getProducts();
        $newId = 1;
        if (!empty($products)) {
            $ids = array_column($products, 'id');
            $newId = max($ids) + 1;
        }

        $newProduct = [
            "id" => $newId,
            "name" => $data['name'],
            "price" => floatval($data['price']),
            "category" => $data['category'],
            "description" => $data['description'] ?? '',
            "image" => $data['image'] ?? '',
            "sizes" => is_array($data['sizes']) ? $data['sizes'] : explode(',', $data['sizes']),
            "colors" => is_array($data['colors']) ? $data['colors'] : explode(',', $data['colors']),
            "stock" => intval($data['stock']),
            "created_at" => date('Y-m-d H:i:s'),
            "created_at" => date('Y-m-d H:i:s')
        ];

        $products[] = $newProduct;
        self::write(self::$productsFile, $products);
        return $newProduct;
    }

    public static function updateProduct($id, $data) {
        $products = self::getProducts();
        foreach ($products as &$product) {
            if ($product['id'] == $id) {
                $product['name'] = $data['name'] ?? $product['name'];
                $product['price'] = isset($data['price']) ? floatval($data['price']) : $product['price'];
                $product['category'] = $data['category'] ?? $product['category'];
                $product['description'] = $data['description'] ?? $product['description'];
                if (isset($data['image'])) {
                    $product['image'] = $data['image'];
                }
                if (isset($data['sizes'])) {
                    $product['sizes'] = is_array($data['sizes']) ? $data['sizes'] : explode(',', $data['sizes']);
                }
                if (isset($data['colors'])) {
                    $product['colors'] = is_array($data['colors']) ? $data['colors'] : explode(',', $data['colors']);
                }
                $product['stock'] = isset($data['stock']) ? intval($data['stock']) : $product['stock'];
                
                self::write(self::$productsFile, $products);
                return $product;
            }
        }
        return null;
    }

    public static function deleteProduct($id) {
        $products = self::getProducts();
        $filtered = [];
        $found = false;
        foreach ($products as $product) {
            if ($product['id'] == $id) {
                $found = true;
            } else {
                $filtered[] = $product;
            }
        }
        if ($found) {
            self::write(self::$productsFile, $filtered);
        }
        return $found;
    }

    // --- ORDERS ACCESS ---

    public static function getOrders() {
        return self::read(self::$ordersFile);
    }

    public static function findOrderById($id) {
        $orders = self::getOrders();
        foreach ($orders as $order) {
            if ($order['id'] == $id) {
                return $order;
            }
        }
        return null;
    }

    public static function createOrder($userId, $items, $total, $shippingInfo) {
        $orders = self::getOrders();
        $newId = 1000 + count($orders) + 1; // start order numbers from 1001

        $newOrder = [
            "id" => $newId,
            "user_id" => $userId,
            "items" => $items,
            "total" => floatval($total),
            "status" => "pending",
            "shipping_info" => $shippingInfo,
            "created_at" => date('Y-m-d H:i:s')
        ];

        // Deduct stock for products
        foreach ($items as $item) {
            $product = self::findProductById($item['id']);
            if ($product) {
                $newStock = max(0, $product['stock'] - $item['quantity']);
                self::updateProduct($item['id'], ['stock' => $newStock]);
            }
        }

        $orders[] = $newOrder;
        self::write(self::$ordersFile, $orders);
        return $newOrder;
    }

    public static function updateOrderStatus($id, $status) {
        $orders = self::getOrders();
        foreach ($orders as &$order) {
            if ($order['id'] == $id) {
                $order['status'] = $status;
                self::write(self::$ordersFile, $orders);
                return $order;
            }
        }
        return null;
    }
}

// Auto-run initialization
JsonDB::init();
?>
