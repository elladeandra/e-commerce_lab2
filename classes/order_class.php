<?php
require_once dirname(__FILE__) . '/../settings/db_connection.php';

use PDO;

class Order extends DB_Connection
{
    private ?PDO $pdo = null;

    private function getPdo(): PDO
    {
        if ($this->pdo === null) {
            $this->pdo = $this->connect();
        }

        return $this->pdo;
    }

    public function beginTransaction(): void
    {
        $this->getPdo()->beginTransaction();
    }

    public function commit(): void
    {
        $this->getPdo()->commit();
    }

    public function rollBack(): void
    {
        if ($this->getPdo()->inTransaction()) {
            $this->getPdo()->rollBack();
        }
    }

    public function create_order(int $customer_id, string $invoice_no, string $status = 'processing'): int
    {
        $pdo = $this->getPdo();

        $sql = "INSERT INTO orders (customer_id, invoice_no, order_date, order_status) 
                VALUES (:customer_id, :invoice_no, NOW(), :status)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':customer_id' => $customer_id,
            ':invoice_no' => $invoice_no,
            ':status' => $status,
        ]);

        return (int)$pdo->lastInsertId();
    }

    public function add_order_detail(int $order_id, int $product_id, int $qty, float $unit_price): bool
    {
        $columns = $this->getOrderDetailColumns();

        if (in_array('unit_price', $columns, true)) {
            $pdo = $this->getPdo();
            $sql = "INSERT INTO orderdetails (order_id, product_id, qty, unit_price)
                    VALUES (:order_id, :product_id, :qty, :unit_price)";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([
                ':order_id' => $order_id,
                ':product_id' => $product_id,
                ':qty' => $qty,
                ':unit_price' => $unit_price,
            ]);
        }

        $pdo = $this->getPdo();
        $sql = "INSERT INTO orderdetails (order_id, product_id, qty)
                VALUES (:order_id, :product_id, :qty)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':order_id' => $order_id,
            ':product_id' => $product_id,
            ':qty' => $qty,
        ]);
    }

    public function record_payment(float $amount, int $customer_id, int $order_id, string $currency = 'USD'): bool
    {
        $pdo = $this->getPdo();
        $sql = "INSERT INTO payment (amt, customer_id, order_id, currency, payment_date)
                VALUES (:amount, :customer_id, :order_id, :currency, NOW())";

        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':amount' => $amount,
            ':customer_id' => $customer_id,
            ':order_id' => $order_id,
            ':currency' => $currency,
        ]);
    }

    public function get_orders_by_customer(int $customer_id): array
    {
        $pdo = $this->getPdo();
        $sql = "SELECT 
                    o.order_id,
                    o.invoice_no,
                    o.order_status,
                    o.order_date,
                    p.amt AS payment_amount,
                    p.currency,
                    p.payment_date
                FROM orders o
                LEFT JOIN payment p ON p.order_id = o.order_id
                WHERE o.customer_id = :customer_id
                ORDER BY o.order_date DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':customer_id' => $customer_id]);
        return $stmt->fetchAll();
    }

    public function get_order_details(int $order_id): array
    {
        $pdo = $this->getPdo();
        $sql = "SELECT 
                    od.product_id,
                    od.qty,
                    od.unit_price,
                    p.product_title,
                    p.product_image
                FROM orderdetails od
                INNER JOIN products p ON p.product_id = od.product_id
                WHERE od.order_id = :order_id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':order_id' => $order_id]);
        return $stmt->fetchAll();
    }

    private function getOrderDetailColumns(): array
    {
        static $columns = null;

        if ($columns !== null) {
            return $columns;
        }

        $pdo = $this->getPdo();
        $stmt = $pdo->query("SHOW COLUMNS FROM orderdetails");
        $columns = array_column($stmt->fetchAll(), 'Field');

        return $columns;
    }
}

