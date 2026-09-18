<footer class="site-footer">
    <div class="footer-container">
        <!-- Simple Grid -->
        <div class="footer-simple">
            <!-- Brand -->
            <div class="footer-brand">
                <i class="fas fa-leaf"></i>
                <div>
                    <h3>Local City Market</h3>
                    <p>Fresh from local farms to your table</p>
                </div>
            </div>

            <!-- Links -->
            <div class="footer-links-simple">
                <a href="products.php">Shop</a>
                <a href="#">Contact</a>
                <a href="#">FAQ</a>
            </div>

            <!-- Social -->
            <div class="footer-social-simple">
                <a href="#"><i class="fab fa-facebook"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
            </div>
        </div>

        <!-- Bottom Line -->
        <div class="footer-bottom-simple">
            <p>&copy; <?php echo date("Y"); ?> Local City Market</p>
            <p>Made by Ghina</p>
        </div>
    </div>
</footer>

<style>
/* Super Minimal Footer */
.site-footer {
    background: #2e7d32;
    color: #fff;
    padding: 2rem 0;
    margin-top: 3rem;
}

.footer-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
}

/* Simple Grid */
.footer-simple {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 2rem;
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

/* Brand */
.footer-brand {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.footer-brand i {
    font-size: 2rem;
    color: #4CAF50;
}

.footer-brand h3 {
    margin: 0;
    font-size: 1.3rem;
}

.footer-brand p {
    margin: 0.25rem 0 0;
    color: #b0bec5;
    font-size: 0.9rem;
}

/* Links */
.footer-links-simple {
    display: flex;
    gap: 2rem;
    flex-wrap: wrap;
}

.footer-links-simple a {
    color: #b0bec5;
    text-decoration: none;
    font-size: 0.95rem;
    transition: color 0.3s ease;
}

.footer-links-simple a:hover {
    color: #4CAF50;
}

/* Social */
.footer-social-simple {
    display: flex;
    gap: 1rem;
}

.footer-social-simple a {
    color: #fff;
    text-decoration: none;
    font-size: 1.1rem;
    opacity: 0.8;
    transition: opacity 0.3s ease;
}

.footer-social-simple a:hover {
    opacity: 1;
}

/* Bottom */
.footer-bottom-simple {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    color: #b0bec5;
    font-size: 0.9rem;
}

.footer-bottom-simple p {
    margin: 0;
}

/* Responsive */
@media (max-width: 768px) {
    .footer-simple {
        flex-direction: column;
        text-align: center;
        gap: 1.5rem;
    }
    
    .footer-bottom-simple {
        flex-direction: column;
        text-align: center;
    }
}
</style>