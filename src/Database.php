<?php
declare(strict_types=1);

namespace Api;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $dbPath = __DIR__ . '/../database/catalog.sqlite';
            $isNew = !file_exists($dbPath);

            try {
                self::$instance = new PDO('sqlite:' . $dbPath);
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

                if ($isNew || filesize($dbPath) === 0) {
                    self::setup(self::$instance);
                }
            } catch (PDOException $e) {
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode([
                    'status'  => 'error',
                    'message' => 'Failed to connect to SQLite database: ' . $e->getMessage()
                ]);
                exit;
            }
        }

        return self::$instance;
    }

    private static function setup(PDO $pdo): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                password_hash TEXT NOT NULL,
                api_token TEXT UNIQUE,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                sku TEXT NOT NULL UNIQUE,
                description TEXT,
                price REAL NOT NULL,
                stock_quantity INTEGER NOT NULL DEFAULT 0,
                category TEXT NOT NULL,
                is_active INTEGER NOT NULL DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            -- Default demo test user
            INSERT INTO users (name, email, password_hash, api_token) VALUES
            ('Admin Demo', 'admin@catalog.com', '" . password_hash('admin123', PASSWORD_BCRYPT) . "', 'demo_bearer_token_super_secret_123456');

            -- Seed sample products
            INSERT INTO products (name, sku, description, price, stock_quantity, category) VALUES
            ('Mechanical RGB Keyboard', 'KB-RGB-01', 'Custom blue switch mechanical keyboard with per-key RGB backlighting', 89.90, 45, 'Peripherals'),
            ('Gaming Mouse 16000 DPI', 'MS-GMR-02', 'Ergonomic optical gaming mouse with ultra-accurate sensor', 49.50, 80, 'Peripherals'),
            ('27-Inch UltraWide Monitor 144Hz', 'MN-UW27-03', 'IPS QHD display with 144Hz refresh rate and HDR400', 349.00, 18, 'Monitors'),
            ('Wireless Noise-Canceling Headset', 'HS-WRLS-04', 'Over-ear headphones with active noise cancellation and 30h battery', 129.00, 32, 'Audio'),
            ('Ergonomic Mesh Office Chair', 'CH-ERGO-05', 'High-back ergonomic task chair with adjustable 3D lumbar support', 219.00, 12, 'Furniture');
        ";

        $pdo->exec($sql);
    }
}
