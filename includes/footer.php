    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="row">
                    <div class="col-lg-4 mb-4">
                        <h5 class="text-white mb-3">
                            <i class="bi bi-calendar-check"></i> <?php echo APP_NAME; ?>
                        </h5>
                        <p class="text-muted">
                            Streamline your appointment booking process with our modern, user-friendly system. 
                            Manage schedules efficiently and provide excellent service to your clients.
                        </p>
                        <div class="d-flex gap-3">
                            <a href="#" class="text-muted fs-5"><i class="bi bi-facebook"></i></a>
                            <a href="#" class="text-muted fs-5"><i class="bi bi-twitter"></i></a>
                            <a href="#" class="text-muted fs-5"><i class="bi bi-linkedin"></i></a>
                            <a href="#" class="text-muted fs-5"><i class="bi bi-instagram"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-6 mb-4">
                        <h6 class="text-white mb-3">Quick Links</h6>
                        <ul class="list-unstyled">
                            <li><a href="index.php" class="text-muted text-decoration-none">Home</a></li>
                            <li><a href="about.php" class="text-muted text-decoration-none">About</a></li>
                            <li><a href="contact.php" class="text-muted text-decoration-none">Contact</a></li>
                            <li><a href="login.php" class="text-muted text-decoration-none">Login</a></li>
                            <li><a href="admin/login.php" class="text-muted text-decoration-none">Admin Login</a></li>
                        </ul>
                    </div>
                    <div class="col-lg-2 col-md-6 mb-4">
                        <h6 class="text-white mb-3">Services</h6>
                        <ul class="list-unstyled">
                            <li><a href="book-appointment.php" class="text-muted text-decoration-none">Book Appointment</a></li>
                            <li><a href="my-appointments.php" class="text-muted text-decoration-none">My Appointments</a></li>
                            <li><a href="dashboard.php" class="text-muted text-decoration-none">Dashboard</a></li>
                            <li><a href="register.php" class="text-muted text-decoration-none">Register</a></li>
                        </ul>
                    </div>
                    <div class="col-lg-4 mb-4">
                        <h6 class="text-white mb-3">Contact Info</h6>
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-geo-alt text-primary me-2"></i>
                            <span class="text-muted">123 Business Street, City, State 12345</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-telephone text-primary me-2"></i>
                            <span class="text-muted">+1 (555) 123-4567</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-envelope text-primary me-2"></i>
                            <span class="text-muted">info@appointmentbooking.com</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-clock text-primary me-2"></i>
                            <span class="text-muted">Mon-Fri: 9AM-5PM</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="footer-copyright">
                <div class="text-center my-2">
                    <div>
                        <span>© 2025 . </span>
                        <span class="text-muted">Developed by </span>
                        <a href="https://rivertheme.com" class="fw-semibold text-decoration-none" target="_blank" rel="noopener">RiverTheme</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Add animation classes to elements when they come into view
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in');
                }
            });
        }, observerOptions);

        // Observe all cards and sections
        document.querySelectorAll('.card, .feature-card, .stats-card, .hero-section').forEach(el => {
            observer.observe(el);
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Add loading states to forms
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
                }
            });
        });
    </script>
</body>
</html> 