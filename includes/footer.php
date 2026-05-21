</main>

    <!-- Premium Editorial Footer -->
    <footer class="mt-auto bg-white border-top border-light pt-5">

        <!-- Main footer body -->
        <div class="container pb-5" style="max-width: 1280px;">
            <div class="row gx-5 gy-5">
                
                <!-- Brand & Newsletter Column -->
                <div class="col-lg-5 col-md-12 pe-lg-5">
                    <!-- Brand Name -->
                    <a href="index.php" class="text-dark text-decoration-none d-inline-block mb-3">
                        <span class="serif-title d-block" style="font-size: 1.8rem; letter-spacing: 0.3em; font-weight: 300;">EXALTIA</span>
                    </a>
                    <p class="mb-4 text-muted small" style="line-height: 1.8; max-width: 380px; letter-spacing: 0.03em;">
                        Sign up to receive updates on new arrivals, exclusive collections, and seasonal campaigns.
                    </p>

                    <!-- Minimal Newsletter Form -->
                    <div style="max-width: 400px; margin-top: 2rem;">
                        <form action="index.php" method="POST">
                            <div class="d-flex align-items-center pb-2 border-bottom border-dark transition-colors">
                                <input 
                                    type="email" 
                                    autocomplete="off"
                                    placeholder="YOUR EMAIL ADDRESS" 
                                    required
                                    class="flex-grow-1 bg-transparent border-0 text-dark pe-3"
                                    style="font-size: 0.75rem; font-weight: 500; letter-spacing: 0.15em; outline: none; box-shadow: none;"
                                >
                                <button 
                                    type="submit" 
                                    class="border-0 bg-transparent text-dark fw-bold p-0 d-flex align-items-center gap-2 hover:opacity-75 transition-opacity"
                                    style="font-size: 0.75rem; letter-spacing: 0.15em;"
                                >
                                    SUBSCRIBE
                                    <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                    </svg>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Social Links -->
                    <div class="d-flex gap-4 mt-4">
                        <a href="#" class="text-decoration-none text-dark d-flex align-items-center gap-2 hover:opacity-50 transition-opacity" style="font-size: 0.65rem; font-weight: 600; letter-spacing: 0.12em;">
                            INSTAGRAM
                        </a>
                        <a href="#" class="text-decoration-none text-dark d-flex align-items-center gap-2 hover:opacity-50 transition-opacity" style="font-size: 0.65rem; font-weight: 600; letter-spacing: 0.12em;">
                            PINTEREST
                        </a>
                        <a href="#" class="text-decoration-none text-dark d-flex align-items-center gap-2 hover:opacity-50 transition-opacity" style="font-size: 0.65rem; font-weight: 600; letter-spacing: 0.12em;">
                            JOURNAL
                        </a>
                    </div>
                </div>

                <!-- Spacer -->
                <div class="col-lg-1 d-none d-lg-block ms-auto"></div>

                <!-- Shop Directory -->
                <div class="col-sm-6 col-lg-3">
                    <h4 class="fw-bold text-dark text-uppercase mb-4" style="font-size: 0.7rem; letter-spacing: 0.2em;">COLLECTIONS</h4>
                    <ul class="list-unstyled mb-0 space-y-3">
                        <li class="mb-3"><a href="index.php#must-haves" class="text-decoration-none text-muted hover:text-dark transition-colors" style="font-size: 0.85rem; letter-spacing: 0.04em;">Artisanal Polo Knit</a></li>
                        <li class="mb-3"><a href="index.php#denim" class="text-decoration-none text-muted hover:text-dark transition-colors" style="font-size: 0.85rem; letter-spacing: 0.04em;">Denim Wardrobe</a></li>
                        <li class="mb-3"><a href="index.php#must-haves" class="text-decoration-none text-muted hover:text-dark transition-colors" style="font-size: 0.85rem; letter-spacing: 0.04em;">Must-Have Basics</a></li>
                        <li class="mb-3"><a href="index.php" class="text-decoration-none text-muted hover:text-dark transition-colors" style="font-size: 0.85rem; letter-spacing: 0.04em;">New Arrivals</a></li>
                    </ul>
                </div>

                <!-- Customer Service -->
                <div class="col-sm-6 col-lg-3">
                    <h4 class="fw-bold text-dark text-uppercase mb-4" style="font-size: 0.7rem; letter-spacing: 0.2em;">ASSISTANCE</h4>
                    <ul class="list-unstyled mb-0 space-y-3">
                        <li class="mb-3"><a href="#" class="text-decoration-none text-muted hover:text-dark transition-colors" style="font-size: 0.85rem; letter-spacing: 0.04em;">Contact Support</a></li>
                        <li class="mb-3"><a href="#" class="text-decoration-none text-muted hover:text-dark transition-colors" style="font-size: 0.85rem; letter-spacing: 0.04em;">Shipping &amp; Returns</a></li>
                        <li class="mb-3"><a href="#" class="text-decoration-none text-muted hover:text-dark transition-colors" style="font-size: 0.85rem; letter-spacing: 0.04em;">Sizing Guide</a></li>
                        <li class="mb-3"><a href="#" class="text-decoration-none text-muted hover:text-dark transition-colors" style="font-size: 0.85rem; letter-spacing: 0.04em;">Privacy Policy</a></li>
                    </ul>
                </div>

            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="bg-light border-top border-secondary border-opacity-10 py-4">
            <div class="container d-flex flex-column flex-md-row align-items-center justify-content-between gap-3" style="max-width: 1280px;">
                <p class="mb-0 text-muted" style="font-size: 0.65rem; font-weight: 600; letter-spacing: 0.12em;">
                    © <?= date('Y') ?> EXALTIA STUDIO. ALL RIGHTS RESERVED.
                </p>
                <div class="d-flex align-items-center gap-2 text-muted" style="font-size: 0.65rem; font-weight: 600; letter-spacing: 0.08em;">
                    <span>DESIGNED FOR THOSE WHO DRESS WITH INTENTION</span>
                </div>
            </div>
        </div>

    </footer>

    <!-- Bootstrap 5 JS Bundle CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
