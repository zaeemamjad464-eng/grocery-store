// Grocery Store Product Display Script
// Fetches products from products.php and displays them with fade-in animation

document.addEventListener('DOMContentLoaded', function() {
    loadProducts();
});

async function loadProducts() {
    const productList = document.getElementById('product-list');
    
    if (!productList) {
        console.error('Product list container not found');
        return;
    }

    try {
        // Show loading message
        productList.innerHTML = '<p class="loading">Loading products...</p>';
        
        // Fetch product data from PHP backend
        const response = await fetch('products.php');
        
        // Check if response is ok
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Response is not JSON');
        }
        
        // Parse JSON data
        const products = await response.json();
        
        // Check if products is an array
        if (!Array.isArray(products)) {
            throw new Error('Invalid data format received');
        }
        
        // Display products
        displayProducts(products);
        
    } catch (error) {
        console.error('Error fetching products:', error);
        displayErrorMessage();
    }
}

function displayProducts(products) {
    const productList = document.getElementById('product-list');
    
    if (products.length === 0) {
        productList.innerHTML = '<p class="no-products">No products available at the moment.</p>';
        return;
    }
    
    // Map products to HTML divs
    const productHTML = products.map(product => {
        // Ensure price is a number and format it
        const price = parseFloat(product.price) || 0;
        const formattedPrice = price.toFixed(2);
        
        // Sanitize product data
        const name = escapeHtml(product.name || 'Unknown Product');
        const image = escapeHtml(product.image || 'placeholder.jpg');
        
        return `
            <div class="product fade-in">
                <img src="images/${image}" 
                     alt="${name}" 
                     onerror="this.src='images/placeholder.jpg'" />
                <h3>${name}</h3>
                <p class="price">$${formattedPrice}</p>
            </div>
        `;
    }).join('');
    
    // Set innerHTML of product list container
    productList.innerHTML = productHTML;
    
    // Trigger fade-in animation with staggered timing
    setTimeout(() => {
        const productElements = document.querySelectorAll('.product');
        productElements.forEach((element, index) => {
            setTimeout(() => {
                element.classList.add('visible');
            }, index * 100); // Stagger the animations
        });
    }, 50);
}

function displayErrorMessage() {
    const productList = document.getElementById('product-list');
    if (productList) {
        productList.innerHTML = `
            <div class="error-message">
                <p>Sorry, we couldn't load the products at this time.</p>
                <button onclick="refreshProducts()" class="btn">Try Again</button>
            </div>
        `;
    }
}

// Utility function to escape HTML characters
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Refresh products function
function refreshProducts() {
    loadProducts();
}

// Handle contact form submission with AJAX
document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.querySelector('.contact-form');
    
    if (contactForm) {
        contactForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(contactForm);
            const submitButton = contactForm.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            
            try {
                submitButton.textContent = 'Sending...';
                submitButton.disabled = true;
                
                const response = await fetch('submit.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.text();
                
                if (response.ok) {
                    alert('Message sent successfully!');
                    contactForm.reset();
                } else {
                    throw new Error('Failed to send message');
                }
                
            } catch (error) {
                console.error('Error submitting form:', error);
                alert('Failed to send message. Please try again.');
            } finally {
                submitButton.textContent = originalText;
                submitButton.disabled = false;
            }
        });
    }
});