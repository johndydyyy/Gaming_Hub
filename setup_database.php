<?php
require_once 'config.php';

header('Content-Type: text/plain');

try {
    // Drop tables if they exist
    $tables = ['order_items', 'orders', 'products', 'users'];
    foreach ($tables as $table) {
        try {
            $pdo->exec("DROP TABLE IF EXISTS `$table`");
            echo "- Dropped table $table\n";
        } catch (PDOException $e) {
            echo "- Could not drop table $table: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n";
    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        is_admin BOOLEAN DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "- Users table created successfully\n";
    
    // Create products table
    $sql = "CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        cpu VARCHAR(100) NOT NULL,
        gpu VARCHAR(100) NOT NULL,
        ram VARCHAR(50) NOT NULL,
        storage VARCHAR(100) NOT NULL,
        motherboard VARCHAR(100) NOT NULL,
        cooler VARCHAR(100) NOT NULL,
        psu VARCHAR(100) NOT NULL,
        case_type VARCHAR(100) NOT NULL,
        price DECIMAL(10, 2) NOT NULL,
        stock_quantity INT DEFAULT 10,
        image_url VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FULLTEXT(name, description)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "- Products table created successfully\n";
    
    // Create orders table
    $sql = "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        total_amount DECIMAL(10, 2) NOT NULL,
        status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
        shipping_address TEXT NOT NULL,
        payment_method VARCHAR(50) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "- Orders table created successfully\n";
    
    // Create order_items table
    $sql = "CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL,
        price DECIMAL(10, 2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "- Order items table created successfully\n";
    
    // Insert sample products if they don't exist
    $stmt = $pdo->query("SELECT COUNT(*) FROM products");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        $products = [
            [
                'name' => 'Pulsar Build-1',
                'description' => 'High-performance gaming PC with top-tier components',
                'cpu' => 'Intel Core i9-13900K',
                'gpu' => 'NVIDIA RTX 4090 24GB',
                'ram' => '32GB DDR5 6000MHz',
                'storage' => '2TB NVMe SSD',
                'motherboard' => 'ASUS ROG Maximus Z790',
                'cooler' => 'NZXT Kraken X73',
                'psu' => 'Corsair RM1000x',
                'case_type' => 'Lian Li PC-O11 Dynamic',
                'price' => 123499.99,
                'image_url' => 'images/pulsar.gif'
            ],

            [
                'name' => 'Aurora Pro',
                'description' => 'VR-ready gaming PC with excellent price-to-performance ratio',
                'cpu' => 'AMD Ryzen 9 7950X',
                'gpu' => 'NVIDIA RTX 4080 16GB',
                'ram' => '32GB DDR5 5600MHz',
                'storage' => '1TB NVMe SSD + 2TB HDD',
                'motherboard' => 'ASUS ROG Crosshair X670E',
                'cooler' => 'Corsair iCUE H150i',
                'psu' => 'Seasonic Focus GX-850',
                'case_type' => 'Fractal Design Meshify C',
                'price' => 182799.99,
                'image_url' => 'images/aurora.gif'
            ],
            [
                'name' => 'Nova Plus',
                'description' => 'Great for 1440p gaming and content creation',
                'cpu' => 'Intel Core i7-13700K',
                'gpu' => 'NVIDIA RTX 4070 Ti 12GB',
                'ram' => '32GB DDR4 3600MHz',
                'storage' => '1TB NVMe SSD',
                'motherboard' => 'MSI MPG Z790',
                'cooler' => 'Noctua NH-D15',
                'psu' => 'EVGA SuperNOVA 750W',
                'case_type' => 'NZXT H510 Flow',
                'price' => 202199.99,
                'image_url' => 'images/nova.gif'
            ]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO products 
            (name, description, cpu, gpu, ram, storage, motherboard, cooler, psu, case_type, price, image_url) 
            VALUES (:name, :description, :cpu, :gpu, :ram, :storage, :motherboard, :cooler, :psu, :case_type, :price, :image_url)");
        
        foreach ($products as $product) {
            $stmt->execute($product);
            echo "- Added product: " . $product['name'] . "\n";
        }
    }
    
    // Create admin user if not exists
    $adminEmail = 'admin@gaminghub.com';
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$adminEmail]);
    
    if (!$stmt->fetch()) {
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, is_admin) VALUES (?, ?, ?, 1)");
        $stmt->execute(['admin', $adminEmail, $password]);
        echo "- Admin user created (email: admin@gaminghub.com, password: admin123)\n";
    }
    
    echo "\nDatabase setup completed successfully!\n";
    echo "You can now access the admin panel at: " . BASE_URL . "/login.php\n";
    
} catch(PDOException $e) {
    die("\nError setting up database: " . $e->getMessage() . "\n");
}
?>
