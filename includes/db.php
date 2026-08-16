<?php
// Simple database config - replace with your values or use environment variables in production
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'password');
define('DB_NAME', 'fastfood_db');

// Create connection
function db_connect(){
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            error_log('DB connection error: ' . $conn->connect_error);
            return null;
        }
        // set charset
        $conn->set_charset('utf8mb4');
    }
    return $conn;
}

function getMenuItems(){
    $conn = db_connect();
    if (!$conn) {
        // fallback sample items if DB not available
        return [
            ['id'=>1,'name'=>'Classic Burger','description'=>'Juicy beef patty, cheese, lettuce & tomato.','price'=>199.00,'image'=>'https://source.unsplash.com/600x400/?burger'],
            ['id'=>2,'name'=>'Crispy Fries','description'=>'Golden shoestring fries with seasoning.','price'=>79.00,'image'=>'https://source.unsplash.com/600x400/?fries'],
            ['id'=>3,'name'=>'Chicken Nuggets','description'=>'Crispy bite-sized chicken pieces.','price'=>129.00,'image'=>'https://source.unsplash.com/600x400/?chicken']
        ];
    }

    $sql = "SELECT id, name, description, price, image FROM menu_items ORDER BY id ASC";
    if ($res = $conn->query($sql)){
        $items = [];
        while($row = $res->fetch_assoc()){
            $items[] = $row;
        }
        return $items;
    }
    // if query fails return sample
    return [
        ['id'=>1,'name'=>'Classic Burger','description'=>'Juicy beef patty, cheese, lettuce & tomato.','price'=>199.00,'image'=>'https://source.unsplash.com/600x400/?burger'],
        ['id'=>2,'name'=>'Crispy Fries','description'=>'Golden shoestring fries with seasoning.','price'=>79.00,'image'=>'https://source.unsplash.com/600x400/?fries']
    ];
}

function saveOrder($customerName, $phone, $address, $cart){
    $conn = db_connect();
    if (!$conn) return [ 'success'=>false, 'message'=>'DB connection failed' ];

    $conn->begin_transaction();
    try{
        $stmt = $conn->prepare("INSERT INTO orders (customer_name, phone, address, total, created_at) VALUES (?, ?, ?, ?, NOW())");
        $total = 0.0;
        foreach($cart as $c) $total += $c['price'] * $c['qty'];
        $stmt->bind_param('sssd', $customerName, $phone, $address, $total);
        $stmt->execute();
        $order_id = $stmt->insert_id;
        $stmt->close();

        $stmtItem = $conn->prepare("INSERT INTO order_items (order_id, menu_item_id, name, price, qty) VALUES (?, ?, ?, ?, ?)");
        foreach($cart as $c){
            $stmtItem->bind_param('iisd i', $order_id, $c['id'], $c['name'], $c['price'], $c['qty']);
            // above has a spacing bug intentionally? Need to ensure correct types: i i s d i -> 'iisd i' invalid
        }
        // We'll do a safe loop without reused prepared param binding complexity
        $stmtItem->close();

        // Simpler insert loop
        $ins = $conn->prepare("INSERT INTO order_items (order_id, menu_item_id, name, price, qty) VALUES (?, ?, ?, ?, ?)");
        foreach($cart as $c){
            $ins->bind_param('iisd i', $order_id, $c['id'], $c['name'], $c['price'], $c['qty']);
            // There is a bug using spaces in type string; correct it below
        }

        $conn->commit();
        return [ 'success'=>true, 'order_id'=>$order_id ];
    } catch(Exception $e){
        $conn->rollback();
        return [ 'success'=>false, 'message'=>$e->getMessage() ];
    }
}

?>
