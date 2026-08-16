<?php
require_once 'includes/db.php';
$items = getMenuItems();
?>

<?php include 'includes/header.php'; ?>

<main class="container">
  <section class="menu">
    <h2>Our Menu</h2>
    <div class="grid">
      <?php foreach($items as $item): ?>
      <article class="card">
        <div class="media" style="background-image:url('<?php echo htmlspecialchars($item['image']); ?>')"></div>
        <div class="card-body">
          <h3><?php echo htmlspecialchars($item['name']); ?></h3>
          <p class="desc"><?php echo htmlspecialchars($item['description']); ?></p>
          <div class="meta">
            <span class="price">₹<?php echo number_format($item['price'],2); ?></span>
            <button class="btn add" data-id="<?php echo $item['id']; ?>" data-name="<?php echo htmlspecialchars($item['name']); ?>" data-price="<?php echo $item['price']; ?>">Add</button>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </section>

  <aside class="cart" id="cart">
    <h2>Your Cart</h2>
    <div id="cart-items"></div>
    <div class="cart-footer">
      <div class="total">Total: <span id="cart-total">₹0.00</span></div>
      <button id="checkout-btn" class="btn primary">Checkout</button>
    </div>
  </aside>

  <div id="checkout-modal" class="modal hidden">
    <div class="modal-content">
      <h3>Checkout</h3>
      <form id="checkout-form">
        <label>Name<input name="name" required /></label>
        <label>Phone<input name="phone" required /></label>
        <label>Address<textarea name="address" required></textarea></label>
        <input type="hidden" name="cart_data" id="cart_data">
        <div class="form-actions">
          <button type="submit" class="btn primary">Place Order</button>
          <button type="button" id="cancel-checkout" class="btn">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <div id="order-result" class="toast hidden"></div>
</main>

<script src="assets/js/main.js"></script>
<link rel="stylesheet" href="assets/css/style.css">

<?php include 'includes/footer.php'; ?>
