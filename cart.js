document.addEventListener('DOMContentLoaded', function() {
    const cartItems = [];
    const cartCountElement = document.getElementById('cartCount');
    const cartModal = document.getElementById('cartModal');
    const cartList = document.querySelector('.cart-list');
    const emptyCart = document.querySelector('.empty-cart');

   
    document.querySelectorAll('.quick-view-btn').forEach(button => {
        button.addEventListener('click', function() {
            const title = this.getAttribute('data-title');
            const price = this.getAttribute('data-price');
            const description = this.getAttribute('data-description');
            const emoji = this.getAttribute('data-emoji');

            const quickViewModal = document.getElementById('quickViewModal');
            quickViewModal.querySelector('.product-title').textContent = title;
            quickViewModal.querySelector('.product-price-large').textContent = `R$ ${price}`;
            quickViewModal.querySelector('.product-description').textContent = description;
            quickViewModal.querySelector('.product-gallery').textContent = emoji; 
            quickViewModal.querySelector('.qty-input').value = 1; 

          
            quickViewModal.style.display = 'block';
        });
    });

    
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const productTitle = this.parentElement.querySelector('.product-name').textContent;
            const productPrice = parseFloat(this.parentElement.querySelector('.product-price').textContent.replace('R$ ', '').replace(',', '.'));
            const quantity = 1; 

           
            let itemIndex = cartItems.findIndex(item => item.title === productTitle);
            if (itemIndex > -1) {
               
                cartItems[itemIndex].quantity += quantity;
            } else {
               
                cartItems.push({
                    title: productTitle,
                    price: productPrice,
                    quantity: quantity,
                    image: '🍎' 
                });
            }

            updateCart();
            showToast(`Produto "${productTitle}" adicionado ao carrinho!`);
        });
    });

    function updateCart() {
        cartList.innerHTML = '';
        if (cartItems.length === 0) {
            emptyCart.style.display = 'block';
            cartList.style.display = 'none';
            cartCountElement.textContent = '0';
        } else {
            emptyCart.style.display = 'none';
            cartList.style.display = 'block';
            cartCountElement.textContent = cartItems.length;

            cartItems.forEach((item, index) => {
                const li = document.createElement('li');
                li.className = 'cart-item';

                li.innerHTML = `
                    <div class="cart-item-image">${item.image}</div>
                    <div class="cart-item-details">
                        <div class="cart-item-name">${item.title}</div>
                        <div class="cart-item-price">R$ ${item.price.toFixed(2)}</div>
                    </div>
                    <div class="cart-item-quantity">
                        <span>Quantidade: ${item.quantity}</span>
                    </div>
                    <button class="remove-item" data-index="${index}"><i class="fas fa-trash"></i></button>
                `;

                cartList.appendChild(li);
            });

            
            const removeButtons = document.querySelectorAll('.remove-item');
            removeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const index = this.getAttribute('data-index');
                    cartItems.splice(index, 1); 
                    updateCart(); 
                });
            });
        }
    }

    function showToast(message) {
        const toast = document.querySelector('.toast');
        const toastMessage = document.querySelector('.toast-message');
        toastMessage.textContent = message;
        toast.classList.add('show');

        setTimeout(() => {
            toast.classList.remove('show');
        }, 3000);
    }

   
    const closeButtons = document.querySelectorAll('.close-modal');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = button.closest('.modal');
            modal.style.display = 'none';
        });
    });

    window.addEventListener('click', function(event) {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    });
});