(function(){
  const addButtons = document.querySelectorAll('.btn.add');
  const cartEl = document.getElementById('cart-items');
  const totalEl = document.getElementById('cart-total');
  const checkoutBtn = document.getElementById('checkout-btn');
  const modal = document.getElementById('checkout-modal');
  const cancel = document.getElementById('cancel-checkout');
  const checkoutForm = document.getElementById('checkout-form');
  const orderResult = document.getElementById('order-result');

  let cart = JSON.parse(localStorage.getItem('ff_cart')||'[]');

  function save(){
    localStorage.setItem('ff_cart', JSON.stringify(cart));
    renderCart();
  }

  function renderCart(){
    cartEl.innerHTML = '';
    let total = 0;
    if(cart.length===0){ cartEl.innerHTML = '<div class="muted">Your cart is empty</div>'; }
    cart.forEach(item=>{
      const div = document.createElement('div'); div.className='cart-item';
      div.innerHTML = `<div>${item.name} x ${item.qty}</div><div>₹${(item.price*item.qty).toFixed(2)}</div>`;
      cartEl.appendChild(div);
      total += item.price*item.qty;
    });
    totalEl.textContent = '₹'+total.toFixed(2);
  }

  function addToCart(id,name,price){
    const existing = cart.find(c=>c.id==id);
    if(existing){ existing.qty += 1; }
    else cart.push({id:parseInt(id),name,price:parseFloat(price),qty:1});
    save();
  }

  addButtons.forEach(b=>{
    b.addEventListener('click',()=>{
      addToCart(b.dataset.id,b.dataset.name,b.dataset.price);
    });
  });

  checkoutBtn.addEventListener('click',()=>{
    modal.classList.remove('hidden');
    document.getElementById('cart_data').value = JSON.stringify(cart);
  });
  cancel.addEventListener('click',()=>{ modal.classList.add('hidden'); });

  checkoutForm.addEventListener('submit', async (e)=>{
    e.preventDefault();
    const form = new FormData(checkoutForm);
    const payload = {
      name: form.get('name'),
      phone: form.get('phone'),
      address: form.get('address'),
      cart: JSON.parse(form.get('cart_data')||'[]')
    };

    if(payload.cart.length===0){ alert('Cart is empty'); return; }

    const res = await fetch('order.php',{
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body:JSON.stringify(payload)
    });
    const data = await res.json();
    if(data.success){
      orderResult.textContent = 'Order placed! #' + data.order_id;
      orderResult.classList.remove('hidden');
      cart = [];
      save();
      modal.classList.add('hidden');
      setTimeout(()=>orderResult.classList.add('hidden'),4000);
    } else {
      orderResult.textContent = 'Failed: ' + (data.message||'Unknown');
      orderResult.classList.remove('hidden');
      setTimeout(()=>orderResult.classList.add('hidden'),4000);
    }
  });

  // initial render
  renderCart();
})();
