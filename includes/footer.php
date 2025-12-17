    </main>
    <footer class="bg-dark text-white mt-5">
        <div class="container py-5">
            <div class="row">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <h5 class="text-uppercase mb-4">GamingHub</h5>
                    <p>Your one-stop shop for high-performance gaming PCs and components. We build the best gaming rigs for all types of gamers.</p>
                    <div class="social-icons mt-4">
                        <a href="#" class="me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="me-3"><i class="fab fa-youtube"></i></a>
                        <a href="#" class="me-3"><i class="fab fa-discord"></i></a>
                    </div>
                </div>
                
                <div class="col-md-4 col-lg-2 mb-4 mb-md-0">
                    <h6 class="text-uppercase fw-bold mb-4">Shop</h6>
                    <ul class="list-unstyled footer-links">
                        <li class="mb-2"><a href="<?php echo BASE_URL; ?>/products.php">All Products</a></li>
                        <li class="mb-2"><a href="#">Pre-built PCs</a></li>
                        <li class="mb-2"><a href="#">Custom Builds</a></li>
                        <li class="mb-2"><a href="#">Gaming Laptops</a></li>
                        <li class="mb-2"><a href="#">Components</a></li>
                        <li class="mb-2"><a href="#">Accessories</a></li>
                    </ul>
                </div>
                
                <div class="col-md-4 col-lg-2 mb-4 mb-md-0">
                    <h6 class="text-uppercase fw-bold mb-4">Support</h6>
                    <ul class="list-unstyled footer-links">
                        <li class="mb-2"><a href="<?php echo BASE_URL; ?>/contact.php">Contact Us</a></li>
                        <li class="mb-2"><a href="#">FAQs</a></li>
                        <li class="mb-2"><a href="#">Shipping Info</a></li>
                        <li class="mb-2"><a href="#">Returns & Exchanges</a></li>
                        <li class="mb-2"><a href="#">Warranty</a></li>
                        <li class="mb-2"><a href="#">Track Order</a></li>
                    </ul>
                </div>
                
                <div class="col-md-4 col-lg-2">
                    <h6 class="text-uppercase fw-bold mb-4">Company</h6>
                    <ul class="list-unstyled footer-links">
                        <li class="mb-2"><a href="<?php echo BASE_URL; ?>/about.php">About Us</a></li>
                        <li class="mb-2"><a href="#">Our Story</a></li>
                        <li class="mb-2"><a href="#">Careers</a></li>
                        <li class="mb-2"><a href="#">Blog</a></li>
                        <li class="mb-2"><a href="#">Press</a></li>
                        <li class="mb-2"><a href="#">Privacy Policy</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2">
                    <h6 class="text-uppercase fw-bold mb-4">Newsletter</h6>
                    <p>Subscribe to get special offers, free giveaways, and once-in-a-lifetime deals.</p>
                    <form class="mb-3">
                        <div class="input-group">
                            <input type="email" class="form-control" placeholder="Your email" aria-label="Your email" required>
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                    <div class="payment-methods">
                        <i class="fab fa-cc-visa fa-2x me-2"></i>
                        <i class="fab fa-cc-mastercard fa-2x me-2"></i>
                        <i class="fab fa-cc-paypal fa-2x me-2"></i>
                        <i class="fab fa-cc-amex fa-2x"></i>
                    </div>
                </div>
            </div>
            
            <hr class="my-4">
            
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> GamingHub. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <a href="#" class="text-white me-3">Terms of Service</a>
                    <a href="#" class="text-white">Privacy Policy</a>
                </div>
            </div>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Enable tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Enable popovers
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
        
        // Auto-hide alerts after 5 seconds
        window.setTimeout(function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
        // Add smooth scrolling to all links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    targetElement.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        // Add active class to current nav link
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = location.pathname.split('/').pop() || 'index.php';
            const navLinks = document.querySelectorAll('.nav-link');
            
            navLinks.forEach(link => {
                const linkHref = link.getAttribute('href');
                if (linkHref && linkHref.includes(currentPage) && linkHref !== '#') {
                    link.classList.add('active');
                    link.setAttribute('aria-current', 'page');
                }
            });
        });
    </script>
</body>
</html>
