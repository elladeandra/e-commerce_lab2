<?php
require_once dirname(__FILE__) . '/../settings/db_connection.php';

class Cart extends DB_Connection
{
    private function resolveContext(?int $customer_id, ?string $ip_address): array
    {
        $ip_address = $ip_address ?: ($_SERVER['REMOTE_ADDR'] ?? null);

        if (empty($customer_id) && empty($ip_address)) {
            throw new InvalidArgumentException('Unable to resolve cart context.');
        }

        if (empty($ip_address)) {
            $ip_address = '0.0.0.0';
        }

        return [$customer_id, $ip_address];
    }

    private function buildContextClause(?int $customer_id, ?string $ip_address, array &$params, string $alias = 'c'): string
    {
        $prefix = $alias ? ($alias . '.') : '';
        $clause_parts = [];

        if (!empty($customer_id)) {
            $clause_parts[] = '(' . $prefix . 'c_id = ?)';
            $params[] = $customer_id;
        } elseif (!empty($ip_address)) {
            $clause_parts[] = '(' . $prefix . 'c_id IS NULL AND ' . $prefix . 'ip_add = ?)';
            $params[] = $ip_address;
        }

        if (empty($clause_parts)) {
            throw new InvalidArgumentException('No valid cart context provided.');
        }

        return '(' . implode(' OR ', $clause_parts) . ')';
    }

    public function add_product_to_cart(int $product_id, int $qty, ?int $customer_id, ?string $ip_address): array
    {
        [$customer_id, $ip_address] = $this->resolveContext($customer_id, $ip_address);
        $qty = max(1, $qty);

        $pdo = $this->connect();
        $pdo->beginTransaction();

        try {
            $contextParams = [];
            $context_clause = $this->buildContextClause($customer_id, $ip_address, $contextParams, 'c');

            $check_params = array_merge([$product_id], $contextParams);
            $check_sql = "SELECT qty FROM cart c WHERE c.p_id = ? AND $context_clause LIMIT 1";
            $stmt = $pdo->prepare($check_sql);
            $stmt->execute($check_params);
            $existing = $stmt->fetchColumn();

            if ($existing !== false) {
                $new_qty = (int)$existing + $qty;
                $update_params = array_merge([$new_qty, $product_id], $contextParams);
                $update_sql = "UPDATE cart c SET c.qty = ? WHERE c.p_id = ? AND $context_clause";
                $update_stmt = $pdo->prepare($update_sql);
                $update_stmt->execute($update_params);
                $pdo->commit();

                return [
                    'status' => 'updated',
                    'quantity' => $new_qty
                ];
            }

            $insert_sql = "INSERT INTO cart (p_id, ip_add, c_id, qty) VALUES (:product_id, :ip_add, :customer_id, :qty)";
            $insert_stmt = $pdo->prepare($insert_sql);
            $insert_stmt->execute([
                ':product_id' => $product_id,
                ':ip_add' => $ip_address,
                ':customer_id' => $customer_id,
                ':qty' => $qty
            ]);

            $pdo->commit();
            return [
                'status' => 'inserted',
                'quantity' => $qty
            ];
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function update_cart_item(int $product_id, int $qty, ?int $customer_id, ?string $ip_address): bool
    {
        [$customer_id, $ip_address] = $this->resolveContext($customer_id, $ip_address);
        $qty = max(1, $qty);

        $contextParams = [];
        $context_clause = $this->buildContextClause($customer_id, $ip_address, $contextParams, 'c');

        $params = array_merge([$qty, $product_id], $contextParams);

        $sql = "UPDATE cart c SET c.qty = ? WHERE c.p_id = ? AND $context_clause";
        $stmt = $this->connect()->prepare($sql);

        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    public function remove_cart_item(int $product_id, ?int $customer_id, ?string $ip_address): bool
    {
        [$customer_id, $ip_address] = $this->resolveContext($customer_id, $ip_address);

        $contextParams = [];
        $context_clause = $this->buildContextClause($customer_id, $ip_address, $contextParams, 'c');

        $params = array_merge([$product_id], $contextParams);

        // Correct MySQL/MariaDB syntax: DELETE alias FROM table alias WHERE ...
        $sql = "DELETE c FROM cart c WHERE c.p_id = ? AND $context_clause";
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount() > 0;
    }

    public function empty_cart(?int $customer_id, ?string $ip_address): bool
    {
        [$customer_id, $ip_address] = $this->resolveContext($customer_id, $ip_address);

        $contextParams = [];
        $context_clause = $this->buildContextClause($customer_id, $ip_address, $contextParams, 'c');

        // Correct MySQL/MariaDB syntax: DELETE alias FROM table alias WHERE ...
        $sql = "DELETE c FROM cart c WHERE $context_clause";
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute($contextParams);

        return true;
    }

    public function get_cart_items(?int $customer_id, ?string $ip_address): array
    {
        [$customer_id, $ip_address] = $this->resolveContext($customer_id, $ip_address);

        $contextParams = [];
        $context_clause = $this->buildContextClause($customer_id, $ip_address, $contextParams, 'c');

        $sql = "SELECT 
                    c.p_id AS product_id,
                    c.qty,
                    p.product_title,
                    p.product_price,
                    p.product_image,
                    p.product_keywords
                FROM cart c
                INNER JOIN products p ON p.product_id = c.p_id
                WHERE $context_clause
                ORDER BY c.p_id ASC";

        $stmt = $this->connect()->prepare($sql);
        $stmt->execute($contextParams);
        return $stmt->fetchAll();
    }

    public function get_cart_summary(?int $customer_id, ?string $ip_address): array
    {
        $items = $this->get_cart_items($customer_id, $ip_address);

        $total_items = 0;
        $subtotal = 0.0;

        foreach ($items as $item) {
            $total_items += (int) $item['qty'];
            $subtotal += (float) $item['product_price'] * (int) $item['qty'];
        }

        return [
            'items' => $items,
            'totals' => [
                'count' => $total_items,
                'subtotal' => $subtotal,
                'formatted_subtotal' => number_format($subtotal, 2),
            ],
        ];
    }
}

