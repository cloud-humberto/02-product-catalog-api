<?php
declare(strict_types=1);

namespace Api\Models;

use Api\Database;
use PDO;

class Product
{
    public static function all(array $filters = [], int $page = 1, int $perPage = 10): array
    {
        $db = Database::getConnection();
        $conditions = ['is_active = 1'];
        $params = [];

        if (!empty($filters['search'])) {
            $conditions[] = "(name LIKE :search OR description LIKE :search OR sku LIKE :search)";
            $params['search'] = '%' . trim($filters['search']) . '%';
        }

        if (!empty($filters['category'])) {
            $conditions[] = "category = :category";
            $params['category'] = trim($filters['category']);
        }

        $whereClause = implode(' AND ', $conditions);

        // Contagem total para metadados de paginação
        $countStmt = $db->prepare("SELECT COUNT(*) as total FROM products WHERE {$whereClause}");
        $countStmt->execute($params);
        $totalItems = (int) $countStmt->fetch()['total'];

        $offset = ($page - 1) * $perPage;

        $sql = "SELECT * FROM products WHERE {$whereClause} ORDER BY id DESC LIMIT :limit OFFSET :offset";
        $stmt = $db->prepare($sql);

        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $items = $stmt->fetchAll();

        return [
            'items' => $items,
            'meta'  => [
                'current_page' => $page,
                'per_page'     => $perPage,
                'total_items'  => $totalItems,
                'total_pages'  => (int) ceil($totalItems / $perPage)
            ]
        ];
    }

    public static function findById(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM products WHERE id = :id AND is_active = 1 LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findBySku(string $sku, ?int $exceptId = null): ?array
    {
        $db = Database::getConnection();
        $sql = "SELECT * FROM products WHERE sku = :sku";
        $params = ['sku' => strtoupper(trim($sku))];

        if ($exceptId !== null) {
            $sql .= " AND id != :except_id";
            $params['except_id'] = $exceptId;
        }

        $sql .= " LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO products (name, sku, description, price, stock_quantity, category)
            VALUES (:name, :sku, :description, :price, :stock_quantity, :category)
        ");

        $stmt->execute([
            'name'           => trim($data['name']),
            'sku'            => strtoupper(trim($data['sku'])),
            'description'    => trim($data['description'] ?? ''),
            'price'          => (float) $data['price'],
            'stock_quantity' => (int) ($data['stock_quantity'] ?? 0),
            'category'       => trim($data['category'])
        ]);

        return (int) $db->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $db = Database::getConnection();
        $fields = [];
        $params = ['id' => $id];

        $allowedFields = ['name', 'sku', 'description', 'price', 'stock_quantity', 'category', 'is_active'];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = :{$field}";
                $params[$field] = $data[$field];
                if ($field === 'sku') {
                    $params[$field] = strtoupper(trim($data[$field]));
                }
            }
        }

        if (empty($fields)) {
            return false;
        }

        $fields[] = "updated_at = CURRENT_TIMESTAMP";
        $sql = "UPDATE products SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    }

    public static function delete(int $id): bool
    {
        $db = Database::getConnection();
        // Soft delete ou delete físico
        $stmt = $db->prepare("DELETE FROM products WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
