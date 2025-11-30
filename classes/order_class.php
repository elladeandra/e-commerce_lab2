<?php
require_once dirname(__FILE__) . '/../settings/db_connection.php';

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

    public function create_order(int $customer_id, int $invoice_no, string $status = 'processing'): int
    {
        $pdo = $this->getPdo();

        $sql = "INSERT INTO orders (customer_id, invoice_no, order_date, order_status) 
                VALUES (:customer_id, :invoice_no, NOW(), :status)";

        $stmt = $pdo->prepare($sql);
        
        try {
            $stmt->execute([
                ':customer_id' => $customer_id,
                ':invoice_no' => $invoice_no,
                ':status' => $status,
            ]);
        } catch (PDOException $e) {
            error_log("PDO Exception in create_order: " . $e->getMessage());
            error_log("SQL: $sql");
            error_log("Params: customer_id=$customer_id, invoice_no=$invoice_no, status=$status");
            throw $e;
        }

        $last_id = (int)$pdo->lastInsertId();
        
        if ($last_id <= 0) {
            error_log("ERROR: lastInsertId returned 0 or negative value");
            $error_info = $pdo->errorInfo();
            error_log("PDO Error Info: " . json_encode($error_info));
            error_log("SQL State: " . ($error_info[0] ?? 'N/A'));
            error_log("Error Code: " . ($error_info[1] ?? 'N/A'));
            error_log("Error Message: " . ($error_info[2] ?? 'N/A'));
            
            // Check if the insert actually happened
            $check_sql = "SELECT order_id FROM orders WHERE customer_id = :customer_id AND invoice_no = :invoice_no ORDER BY order_id DESC LIMIT 1";
            $check_stmt = $pdo->prepare($check_sql);
            $check_stmt->execute([
                ':customer_id' => $customer_id,
                ':invoice_no' => $invoice_no
            ]);
            $existing_order = $check_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing_order) {
                error_log("Order was created but lastInsertId failed. Found order_id: " . $existing_order['order_id']);
                return (int)$existing_order['order_id'];
            }
        } else {
            error_log("Order created successfully with ID: $last_id");
        }
        
        return $last_id;
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

    public function record_payment(float $amount, int $customer_id, int $order_id, string $currency = 'GHS', string $payment_method = 'direct', ?string $transaction_ref = null, ?string $authorization_code = null, ?string $payment_channel = null): bool
    {
        $pdo = $this->getPdo();
        
        // Check if payment table has extended columns
        $columns = $this->getPaymentColumns();
        
        // Build SQL dynamically based on available columns
        $sqlColumns = ['amt', 'customer_id', 'order_id', 'currency', 'payment_date'];
        $sqlValues = [':amount', ':customer_id', ':order_id', ':currency', 'NOW()'];
        $params = [
            ':amount' => $amount,
            ':customer_id' => $customer_id,
            ':order_id' => $order_id,
            ':currency' => $currency,
        ];
        
        // Add optional fields if columns exist
        if (in_array('payment_method', $columns) && $payment_method) {
            $sqlColumns[] = 'payment_method';
            $sqlValues[] = ':payment_method';
            $params[':payment_method'] = $payment_method;
        }
        
        if (in_array('transaction_ref', $columns) && $transaction_ref) {
            $sqlColumns[] = 'transaction_ref';
            $sqlValues[] = ':transaction_ref';
            $params[':transaction_ref'] = $transaction_ref;
        }
        
        if (in_array('authorization_code', $columns) && $authorization_code) {
            $sqlColumns[] = 'authorization_code';
            $sqlValues[] = ':authorization_code';
            $params[':authorization_code'] = $authorization_code;
        }
        
        if (in_array('payment_channel', $columns) && $payment_channel) {
            $sqlColumns[] = 'payment_channel';
            $sqlValues[] = ':payment_channel';
            $params[':payment_channel'] = $payment_channel;
        }
        
        $sql = "INSERT INTO payment (" . implode(', ', $sqlColumns) . ") VALUES (" . implode(', ', $sqlValues) . ")";
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }
    
    private function getPaymentColumns(): array
    {
        static $columns = null;
        
        if ($columns !== null) {
            return $columns;
        }
        
        $pdo = $this->getPdo();
        $stmt = $pdo->query("SHOW COLUMNS FROM payment");
        $columns = array_column($stmt->fetchAll(), 'Field');
        
        return $columns;
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
        
        // Check if orderdetails table has unit_price column
        $columns = $this->getOrderDetailColumns();
        $has_unit_price = in_array('unit_price', $columns, true);
        
        if ($has_unit_price) {
            // If unit_price exists in orderdetails, use it
            $sql = "SELECT 
                        od.product_id,
                        od.qty,
                        od.unit_price,
                        p.product_title,
                        p.product_image
                    FROM orderdetails od
                    INNER JOIN products p ON p.product_id = od.product_id
                    WHERE od.order_id = :order_id";
        } else {
            // If unit_price doesn't exist, get price from products table
            $sql = "SELECT 
                        od.product_id,
                        od.qty,
                        p.product_price AS unit_price,
                        p.product_title,
                        p.product_image
                    FROM orderdetails od
                    INNER JOIN products p ON p.product_id = od.product_id
                    WHERE od.order_id = :order_id";
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':order_id' => $order_id]);
        return $stmt->fetchAll();
    }

    public function verify_order_exists(int $order_id): ?array
    {
        $pdo = $this->getPdo();
        $sql = "SELECT order_id, invoice_no, customer_id, order_date, order_status 
                FROM orders 
                WHERE order_id = :order_id 
                LIMIT 1";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':order_id' => $order_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
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

